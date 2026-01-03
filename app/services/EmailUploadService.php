<?php

/**
 * EmailUploadService
 * 
 * Parses uploaded .eml/.msg files and ingests them into the ticketing system
 * (create or append) reusing the same message/attachment semantics as Graph ingestion.
 */
class EmailUploadService {
    private Setting $settingModel;
    private Ticket $ticketModel;
    private TicketAttachment $attachmentModel;
    private Client $clientModel;
    private ?ClientDomain $clientDomainModel = null;

    public function __construct() {
        if (!class_exists('Setting')) {
            require_once APPROOT . '/app/models/Setting.php';
        }
        if (!class_exists('Ticket')) {
            require_once APPROOT . '/app/models/Ticket.php';
        }
        if (!class_exists('TicketAttachment')) {
            require_once APPROOT . '/app/models/TicketAttachment.php';
        }
        if (!class_exists('Client')) {
            require_once APPROOT . '/app/models/Client.php';
        }
        if (!class_exists('ClientDomain')) {
            require_once APPROOT . '/app/models/ClientDomain.php';
        }

        $this->settingModel = new Setting();
        $this->ticketModel = new Ticket();
        $this->attachmentModel = new TicketAttachment();
        $this->clientModel = new Client();
        $this->clientDomainModel = new ClientDomain();
    }

    /**
     * Entry point: process a single uploaded email file.
     *
     * @param array $file Single file entry from $_FILES (already normalized)
     * @param array $options ['ticket_id'=>int|null, 'send_ack'=>bool]
     * @return array result info
     */
    public function processUploadedFile(array $file, array $options = []): array {
        $result = [
            'filename' => $file['name'] ?? '',
            'status' => 'error',
            'message' => '',
            'ticket_id' => null,
            'ticket_number' => null,
            'message_id' => null,
            'parsed' => null,
            'raw_path' => null
        ];

        // Read raw content first (before any move)
        $rawContent = @file_get_contents($file['tmp_name']);
        if ($rawContent === false || $rawContent === '') {
            $result['message'] = 'Unable to read uploaded file.';
            return $result;
        }

        // Save raw file for audit
        $rawPath = $this->saveRawFile($file, $rawContent);
        $result['raw_path'] = $rawPath;

        $parsed = $this->parseRawEmail($rawContent, $file['name']);
        $result['parsed'] = $parsed;
        if (!empty($parsed['errors'])) {
            $result['message'] = implode('; ', $parsed['errors']);
            // Continue to attempt ingestion even with minor parse warnings
        }

        // Ingest into tickets
        $ingested = $this->ingestParsedEmail($parsed, $options);
        $result = array_merge($result, $ingested);

        return $result;
    }

    /**
     * Persist raw upload to disk.
     */
    private function saveRawFile(array $file, ?string $rawContent = null): ?string {
        $dir = APPROOT . '/public/uploads/email_imports/' . date('Y/m');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $safeName = $this->attachmentModel->getSafeFilename($file['name'] ?? 'email.eml');
        $path = $dir . '/' . time() . '_' . uniqid('', true) . '_' . $safeName;
        $bytes = $rawContent ?? @file_get_contents($file['tmp_name']);
        @file_put_contents($path, $bytes);
        // Return relative path for storing with tickets if needed
        $relative = 'uploads/email_imports/' . date('Y/m') . '/' . basename($path);
        return $relative;
    }

    /**
     * Parse raw RFC822 email content (.eml). For .msg, best-effort: treat as text if it contains headers.
     */
    public function parseRawEmail(string $raw, string $filename = ''): array {
        $parsed = [
            'message_id' => null,
            'in_reply_to' => null,
            'references' => [],
            'subject' => '',
            'from' => [],
            'reply_to' => [],
            'to' => [],
            'cc' => [],
            'sent_at' => null,
            'body_html' => '',
            'body_text' => '',
            'full_body_html' => '',
            'attachments' => [],
            'headers' => [],
            'errors' => []
        ];

        // Split headers/body
        if (preg_match("/\r?\n\r?\n/", $raw, $m, PREG_OFFSET_CAPTURE)) {
            $pos = $m[0][1];
            $rawHeaders = substr($raw, 0, $pos);
            $rawBody = substr($raw, $pos + strlen($m[0][0]));
        } else {
            // Not a valid RFC822 message; treat entire content as body
            $rawHeaders = '';
            $rawBody = $raw;
            $parsed['errors'][] = 'Could not find email headers; treating file as plain text.';
        }

        // Parse headers via IMAP helper if available
        $headerObj = null;
        if (function_exists('imap_rfc822_parse_headers') && $rawHeaders !== '') {
            $headerObj = @imap_rfc822_parse_headers($rawHeaders);
        }

        $headerArray = $this->headersToArray($rawHeaders);
        $parsed['headers'] = $headerArray;

        // Basic header fields
        $parsed['message_id'] = $this->firstNonEmpty([
            $headerArray['message-id'] ?? null,
            $headerObj->message_id ?? null
        ]);
        $parsed['in_reply_to'] = $this->firstNonEmpty([
            $headerArray['in-reply-to'] ?? null,
            $headerObj->in_reply_to ?? null
        ]);

        // References: split on space
        $refs = [];
        $refHeader = $this->firstNonEmpty([$headerArray['references'] ?? null, $headerObj->references ?? null]);
        if (!empty($refHeader)) {
            $refs = preg_split('/\s+/', trim(str_replace([',', ';'], ' ', $refHeader)));
            $refs = array_values(array_filter(array_map('trim', $refs)));
        }
        $parsed['references'] = $refs;

        $parsed['subject'] = $this->decodeHeader($this->firstNonEmpty([$headerArray['subject'] ?? null, $headerObj->subject ?? null]));

        $parsed['from'] = $this->normalizeAddresses($headerObj->from ?? [], $headerArray['from'] ?? null);
        $parsed['reply_to'] = $this->normalizeAddresses($headerObj->reply_to ?? [], $headerArray['reply-to'] ?? null);
        $parsed['to'] = $this->normalizeAddresses($headerObj->to ?? [], $headerArray['to'] ?? null);
        $parsed['cc'] = $this->normalizeAddresses($headerObj->cc ?? [], $headerArray['cc'] ?? null);

        $dateStr = $this->firstNonEmpty([$headerArray['date'] ?? null, $headerObj->date ?? null]);
        if (!empty($dateStr)) {
            try {
                $parsed['sent_at'] = (new DateTime($dateStr))->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                $parsed['sent_at'] = null;
            }
        }

        // Detect content type from headers
        $contentType = $headerArray['content-type'] ?? 'text/plain';
        [$mime, $params] = $this->parseContentType($contentType);

        if (str_starts_with($mime, 'multipart/')) {
            $parsedParts = $this->parseMultipart($rawBody, $mime, $params);
        } else {
            $parsedParts = $this->parseSinglePart($rawBody, $headerArray);
        }

        // Merge parsed parts into final payload
        $parsed['body_html'] = $parsedParts['body_html'] ?? '';
        $parsed['body_text'] = $parsedParts['body_text'] ?? '';
        $parsed['full_body_html'] = $parsedParts['full_body_html'] ?? $parsed['body_html'];
        $parsed['attachments'] = $parsedParts['attachments'] ?? [];
        if (!empty($parsedParts['errors'])) {
            $parsed['errors'] = array_merge($parsed['errors'], $parsedParts['errors']);
        }

        // If message-id missing, create conservative fallback hash
        if (empty($parsed['message_id'])) {
            $hashInput = strtolower(trim($parsed['subject'])) . '|' . ($parsed['sent_at'] ?? '') . '|' . ($parsed['from'][0] ?? '') . '|' . substr(trim($parsed['body_text']), 0, 200);
            $parsed['message_id'] = 'upload-' . hash('sha256', $hashInput);
        }

        return $parsed;
    }

    private function parseMultipart(string $body, string $mime, array $params): array {
        $result = [
            'body_html' => '',
            'body_text' => '',
            'full_body_html' => '',
            'attachments' => [],
            'errors' => []
        ];

        $boundary = $params['boundary'] ?? null;
        if (empty($boundary)) {
            return $this->parseSinglePart($body, ['content-type' => $mime]);
        }

        $parts = preg_split('/--' . preg_quote($boundary, '/') . '(?:--)?\s*\r?\n?/', $body);
        if (empty($parts)) {
            return $this->parseSinglePart($body, ['content-type' => $mime]);
        }

        foreach ($parts as $part) {
            $part = ltrim($part);
            if ($part === '' || $part === "--") {
                continue;
            }
            [$pHeadersRaw, $pBody] = $this->splitHeadersBody($part);
            $pHeaders = $this->headersToArray($pHeadersRaw);
            [$pMime, $pParams] = $this->parseContentType($pHeaders['content-type'] ?? 'text/plain');

            if (str_starts_with($pMime, 'multipart/')) {
                $sub = $this->parseMultipart($pBody, $pMime, $pParams);
                $result['attachments'] = array_merge($result['attachments'], $sub['attachments']);
                $result['errors'] = array_merge($result['errors'], $sub['errors']);
                // Prefer HTML/text from the first alternative found
                if (empty($result['body_html']) && !empty($sub['body_html'])) {
                    $result['body_html'] = $sub['body_html'];
                }
                if (empty($result['body_text']) && !empty($sub['body_text'])) {
                    $result['body_text'] = $sub['body_text'];
                }
                if (empty($result['full_body_html']) && !empty($sub['full_body_html'])) {
                    $result['full_body_html'] = $sub['full_body_html'];
                }
                continue;
            }

            $disp = strtolower($pHeaders['content-disposition'] ?? '');
            $isAttachment = str_contains($disp, 'attachment');
            $isInline = str_contains($disp, 'inline');
            $filename = $this->extractFilename($pHeaders);
            $contentId = $this->extractContentId($pHeaders);

            $decoded = $this->decodeBody($pBody, $pHeaders['content-transfer-encoding'] ?? '', $pParams['charset'] ?? null);

            if ($isAttachment || $filename || ($isInline && $contentId)) {
                $result['attachments'][] = [
                    'filename' => $filename ?: ($contentId ?: 'attachment'),
                    'content' => $decoded,
                    'mime_type' => $pMime ?: 'application/octet-stream',
                    'is_inline' => $isInline,
                    'content_id' => $contentId,
                    'size' => strlen((string)$decoded)
                ];
                continue;
            }

            // Body candidates
            if ($pMime === 'text/html') {
                if (empty($result['body_html'])) {
                    $result['body_html'] = $decoded;
                }
                if (empty($result['full_body_html'])) {
                    $result['full_body_html'] = $decoded;
                }
            } elseif ($pMime === 'text/plain') {
                if (empty($result['body_text'])) {
                    $result['body_text'] = $decoded;
                }
            }
        }

        return $result;
    }

    private function parseSinglePart(string $body, array $headers): array {
        [$mime, $params] = $this->parseContentType($headers['content-type'] ?? 'text/plain');
        $decoded = $this->decodeBody($body, $headers['content-transfer-encoding'] ?? '', $params['charset'] ?? null);
        $result = [
            'body_html' => '',
            'body_text' => '',
            'full_body_html' => '',
            'attachments' => [],
            'errors' => []
        ];
        if ($mime === 'text/html') {
            $result['body_html'] = $decoded;
            $result['full_body_html'] = $decoded;
        } else {
            $result['body_text'] = $decoded;
        }
        return $result;
    }

    private function splitHeadersBody(string $rawPart): array {
        if (preg_match("/\r?\n\r?\n/", $rawPart, $m, PREG_OFFSET_CAPTURE)) {
            $pos = $m[0][1];
            $h = substr($rawPart, 0, $pos);
            $b = substr($rawPart, $pos + strlen($m[0][0]));
            return [$h, $b];
        }
        return [$rawPart, ''];
    }

    private function decodeBody(string $body, string $encoding, ?string $charset): string {
        $encoding = strtolower(trim($encoding));
        if ($encoding === 'base64') {
            $body = base64_decode($body, true) ?: '';
        } elseif ($encoding === 'quoted-printable') {
            $body = quoted_printable_decode($body);
        }
        $charset = $charset ? strtoupper($charset) : null;
        if ($charset && function_exists('mb_convert_encoding')) {
            $body = @mb_convert_encoding($body, 'UTF-8', $charset);
        }
        return $body;
    }

    private function parseContentType(string $header): array {
        $parts = preg_split('/;\s*/', $header);
        $mime = strtolower(trim(array_shift($parts)));
        $params = [];
        foreach ($parts as $p) {
            if (strpos($p, '=') !== false) {
                [$k, $v] = array_map('trim', explode('=', $p, 2));
                $v = trim($v, "\"' ");
                $params[strtolower($k)] = $v;
            }
        }
        return [$mime, $params];
    }

    private function headersToArray(string $raw): array {
        $lines = preg_split('/\r?\n/', $raw);
        $headers = [];
        $current = '';
        foreach ($lines as $line) {
            if (preg_match('/^\s+/', $line) && $current !== '') {
                // Continuation
                $headers[$current][count($headers[$current]) - 1] .= ' ' . trim($line);
                continue;
            }
            if (strpos($line, ':') !== false) {
                [$k, $v] = explode(':', $line, 2);
                $key = strtolower(trim($k));
                $val = trim($v);
                if (!isset($headers[$key])) {
                    $headers[$key] = [];
                }
                $headers[$key][] = $val;
                $current = $key;
            }
        }
        // Flatten single-valued headers
        foreach ($headers as $k => $vals) {
            if (count($vals) === 1) {
                $headers[$k] = $vals[0];
            }
        }
        return $headers;
    }

    private function normalizeAddresses($addrObjList, ?string $rawHeader = null): array {
        $emails = [];
        if (is_array($addrObjList)) {
            foreach ($addrObjList as $addr) {
                if (!empty($addr->mailbox) && !empty($addr->host)) {
                    $emails[] = strtolower(trim($addr->mailbox . '@' . $addr->host));
                }
            }
        }
        if (!empty($rawHeader) && empty($emails)) {
            // fallback: split by comma
            $parts = explode(',', $rawHeader);
            foreach ($parts as $p) {
                if (preg_match('/<([^>]+)>/', $p, $m)) {
                    $emails[] = strtolower(trim($m[1]));
                } else {
                    $emails[] = strtolower(trim($p));
                }
            }
        }
        // Deduplicate and strip empties
        $emails = array_values(array_filter(array_unique(array_map('trim', $emails))));
        return $emails;
    }

    private function extractFilename(array $headers): ?string {
        $ct = $headers['content-type'] ?? '';
        if (is_array($ct)) { $ct = implode(';', $ct); }
        if (preg_match('/name="?([^\";]+)"?/i', $ct, $m)) {
            return $this->decodeHeader($m[1]);
        }
        $cd = $headers['content-disposition'] ?? '';
        if (is_array($cd)) { $cd = implode(';', $cd); }
        if (preg_match('/filename="?([^\";]+)"?/i', $cd, $m)) {
            return $this->decodeHeader($m[1]);
        }
        return null;
    }

    private function extractContentId(array $headers): ?string {
        $cid = $headers['content-id'] ?? null;
        if (is_array($cid)) { $cid = $cid[0] ?? null; }
        if ($cid) {
            return trim($cid, "<> \t\r\n");
        }
        return null;
    }

    private function decodeHeader(?string $value): string {
        if (empty($value)) return '';
        $decoded = '';
        if (function_exists('imap_mime_header_decode')) {
            $parts = imap_mime_header_decode($value);
            foreach ($parts as $p) {
                $decoded .= $p->text;
            }
        } else {
            $decoded = $value;
        }
        return trim($decoded);
    }

    private function firstNonEmpty(array $values) {
        foreach ($values as $v) {
            if (!empty($v)) {
                return $v;
            }
        }
        return null;
    }

    /**
     * Ingest parsed email into tickets (create or append).
     */
    private function ingestParsedEmail(array $parsed, array $options): array {
        $sendAck = !empty($options['send_ack']);
        $ticketIdHint = isset($options['ticket_id']) ? (int)$options['ticket_id'] : null;

        $result = [
            'status' => 'error',
            'message' => '',
            'ticket_id' => null,
            'ticket_number' => null,
            'message_id' => $parsed['message_id'] ?? null
        ];

        // Dedup by message-id
        $existingMsg = $this->ticketModel->findMessageByMessageId($parsed['message_id']);
        if ($existingMsg) {
            $result['status'] = 'skipped';
            $result['message'] = 'Duplicate (Message-ID already ingested).';
            $result['ticket_id'] = $existingMsg['ticket_id'] ?? null;
            return $result;
        }

        $ticketId = $ticketIdHint ?: $this->findExistingTicketForEmail($parsed);
        $isNewTicket = false;

        if (!$ticketId) {
            $isNewTicket = true;
            $ticketId = $this->createTicketFromEmail($parsed, $sendAck);
        } else {
            $this->appendMessageToTicket($ticketId, $parsed);
        }

        if (!$ticketId) {
            $result['status'] = 'error';
            $result['message'] = 'Failed to create or append ticket.';
            return $result;
        }

        $ticket = $this->ticketModel->getById($ticketId);
        $result['ticket_id'] = $ticketId;
        $result['ticket_number'] = $ticket['ticket_number'] ?? null;
        $result['status'] = $isNewTicket ? 'created' : 'appended';
        $result['message'] = $isNewTicket ? 'Ticket created from uploaded email.' : 'Email appended to existing ticket.';

        // Save attachments (if any)
        $msgRow = $this->ticketModel->findMessageByMessageId($parsed['message_id']);
        $ticketMessageId = $msgRow['id'] ?? null;
        if (!empty($parsed['attachments']) && $ticketMessageId) {
            $this->saveAttachments($ticketId, $ticketMessageId, $parsed['attachments']);
        }

        return $result;
    }

    private function createTicketFromEmail(array $parsed, bool $sendAck): ?int {
        $clientId = $this->resolveClient($parsed);
        // Prefer parsed external address; fallback to client email if we resolved one
        $requesterEmail = $this->firstExternalAddress($parsed);
        if (!$requesterEmail && $clientId) {
            try {
                $client = $this->clientModel->getClientById($clientId);
                if (!empty($client['email'])) {
                    $requesterEmail = $client['email'];
                }
            } catch (Exception $e) {
                // ignore
            }
        }

        $createdBy = 1; // system user default
        $ticketId = $this->ticketModel->create([
            'subject' => $parsed['subject'] ?: '(no subject)',
            'description' => $parsed['body_text'] ?: strip_tags($parsed['body_html'] ?? ''),
            'created_by' => $createdBy,
            'client_id' => $clientId,
            'source' => 'email-upload',
            'inbound_email_address' => $requesterEmail,
            'email_thread_id' => $parsed['in_reply_to'] ?: ($parsed['references'][0] ?? $parsed['message_id']),
            'original_message_id' => $parsed['message_id'],
            'suppress_initial_message' => true,
            'created_at' => $parsed['sent_at'] ?? null,
            'updated_at' => $parsed['sent_at'] ?? null
        ]);

        if (!$ticketId) {
            return null;
        }

        $this->appendMessageToTicket($ticketId, $parsed, $sendAck);
        return $ticketId;
    }

    private function appendMessageToTicket(int $ticketId, array $parsed, bool $sendAck = false): void {
        $useHtml = !empty($parsed['body_html']);
        $content = $useHtml ? $parsed['body_html'] : ($parsed['body_text'] ?? '');
        $content = $content ?: '(no content)';
        $format = $useHtml ? 'html' : 'text';

        $this->ticketModel->addMessage($ticketId, [
            'user_id' => null,
            'message_type' => 'email_inbound',
            'subject' => $parsed['subject'] ?? null,
            'content' => $content,
            'content_format' => $format,
            'content_full' => $parsed['full_body_html'] ?? $content,
            'content_full_format' => 'html',
            'email_message_id' => $parsed['message_id'],
            'email_from' => $parsed['from'][0] ?? null,
            'email_to' => !empty($parsed['to']) ? implode(',', $parsed['to']) : null,
            'email_cc' => !empty($parsed['cc']) ? implode(',', $parsed['cc']) : null,
            'email_headers' => json_encode($parsed['headers']),
            'is_public' => 1,
            'created_at' => $parsed['sent_at'] ?? null,
            'suppress_ticket_touch' => false
        ]);

        if ($sendAck) {
            $this->ticketModel->sendAutoAcknowledgmentEmail($ticketId, $parsed['from'][0] ?? '');
        }
    }

    private function findExistingTicketForEmail(array $parsed): ?int {
        // 1) In-Reply-To
        if (!empty($parsed['in_reply_to'])) {
            $msg = $this->ticketModel->findMessageByMessageId($parsed['in_reply_to']);
            if (!empty($msg['ticket_id'])) {
                return (int)$msg['ticket_id'];
            }
        }
        // 2) References
        if (!empty($parsed['references'])) {
            foreach ($parsed['references'] as $ref) {
                $msg = $this->ticketModel->findMessageByMessageId($ref);
                if (!empty($msg['ticket_id'])) {
                    return (int)$msg['ticket_id'];
                }
            }
        }
        // 3) Subject tag [TKT-XXXX-XXXXXX]
        if (preg_match('/\\[TKT-\\d{4}-\\d{6}\\]/', $parsed['subject'], $m)) {
            $ticketNumber = trim($m[0], '[]');
            $existing = $this->ticketModel->getByNumber($ticketNumber);
            if (!empty($existing['id'])) {
                return (int)$existing['id'];
            }
        }
        return null;
    }

    private function resolveClient(array $parsed): ?int {
        $domains = $this->settingModel->getTechnicianEmailDomains();
        $isInternal = function($email) use ($domains) {
            $at = strpos($email, '@');
            if ($at === false) return false;
            $domain = strtolower(substr($email, $at + 1));
            return in_array($domain, $domains, true);
        };

        $candidates = [];
        if (!empty($parsed['reply_to'])) { $candidates = array_merge($candidates, $parsed['reply_to']); }
        if (!empty($parsed['from'])) { $candidates = array_merge($candidates, $parsed['from']); }
        if (!empty($parsed['to'])) { $candidates = array_merge($candidates, $parsed['to']); }
        if (!empty($parsed['cc'])) { $candidates = array_merge($candidates, $parsed['cc']); }
        $candidates = array_values(array_unique($candidates));

        $clientEmail = null;
        foreach ($candidates as $addr) {
            if (!$isInternal($addr)) {
                $clientEmail = $addr;
                break;
            }
        }
        if (!$clientEmail && !empty($parsed['from'][0])) {
            $clientEmail = $parsed['from'][0];
        }
        if (!$clientEmail) {
            return null;
        }

        // 1) direct client email match
        $clientRow = $this->clientModel->getClientByEmail($clientEmail);
        if (!empty($clientRow['id'])) {
            return (int)$clientRow['id'];
        }

        // 2) ClientDomain mapping
        $mapped = $this->clientDomainModel->getClientIdByEmail($clientEmail);
        if (!empty($mapped)) {
            return (int)$mapped;
        }

        // 3) No match: do NOT auto-create clients.
        // We will prompt staff to link/create a client on the ticket UI instead.
        return null;
    }

    private function deriveNameFromEmail(string $email, ?string $subject = ''): string {
        $local = substr($email, 0, strpos($email, '@'));
        $local = str_replace(['.', '_', '-'], ' ', $local);
        $local = ucwords($local);
        if (!empty($subject)) {
            $subject = trim($subject);
            if ($subject !== '') {
                return $local ?: $subject;
            }
        }
        return $local ?: $email;
    }

    private function firstExternalAddress(array $parsed): ?string {
        $domains = $this->settingModel->getTechnicianEmailDomains();
        foreach (['from','reply_to','to','cc'] as $key) {
            if (!empty($parsed[$key])) {
                foreach ($parsed[$key] as $addr) {
                    $domain = substr($addr, strpos($addr, '@') + 1);
                    if (!in_array(strtolower($domain), $domains, true)) {
                        return $addr;
                    }
                }
            }
        }
        return $parsed['from'][0] ?? null;
    }

    private function saveAttachments(int $ticketId, int $ticketMessageId, array $attachments): void {
        $uploadDir = APPROOT . '/public/uploads/ticket_attachments/' . date('Y/m');
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        foreach ($attachments as $att) {
            $filename = $this->attachmentModel->getSafeFilename($att['filename'] ?? 'attachment');
            $relative = 'uploads/ticket_attachments/' . date('Y/m') . '/' . uniqid('upload_', true) . '_' . $filename;
            $fullPath = APPROOT . '/public/' . $relative;

            @file_put_contents($fullPath, $att['content']);

            $this->attachmentModel->create([
                'ticket_id' => $ticketId,
                'ticket_message_id' => $ticketMessageId,
                'ms_message_id' => $att['content_id'] ? ($att['content_id']) : null,
                'ms_attachment_id' => null,
                'content_id' => $att['content_id'] ?? null,
                'filename' => $filename,
                'original_filename' => $filename,
                'file_path' => $relative,
                'file_size' => $att['size'] ?? strlen((string)$att['content']),
                'mime_type' => $att['mime_type'] ?? 'application/octet-stream',
                'is_inline' => !empty($att['is_inline']),
                'is_downloaded' => 1,
                'download_error' => null,
                'downloaded_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
