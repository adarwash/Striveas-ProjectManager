<?php

/**
 * Microsoft Graph API Service for Email Integration
 * Replaces IMAP/SMTP with modern OAuth2-based email handling
 */
class MicrosoftGraphService {
    private $tenantId;
    private $clientId;
    private $clientSecret;
    private $accessToken;
    private $tokenExpiry;
    private $db;
    private $dbTimezone;
    
    public function __construct() {
        // Load configuration from database or config file
        $this->db = new EasySQL(DB1);
        $this->loadConfiguration();
    }
    
    /**
     * Load Graph API configuration from settings
     */
    private function loadConfiguration() {
        // Try to load from database first
        try {
            $query = "SELECT setting_key, setting_value FROM Settings 
                      WHERE setting_key IN ('graph_tenant_id', 'graph_client_id', 'graph_client_secret', 'graph_support_email', 'db_timezone')";
            $settings = $this->db->select($query);
            
            if ($settings) {
                foreach ($settings as $setting) {
                    switch ($setting['setting_key']) {
                        case 'graph_tenant_id':
                            $this->tenantId = $setting['setting_value'];
                            break;
                        case 'graph_client_id':
                            $this->clientId = $setting['setting_value'];
                            break;
                        case 'graph_client_secret':
                            $this->clientSecret = $setting['setting_value'];
                            break;
                        case 'db_timezone':
                            $this->dbTimezone = $setting['setting_value'];
                            break;
                    }
                }
            }
        } catch (Exception $e) {
            // Settings table might not exist yet
            error_log('Failed to load Graph settings from database: ' . $e->getMessage());
        }
        
        // Fallback to config file if not in database
        if (!$this->tenantId) {
            $this->tenantId = defined('GRAPH_TENANT_ID') ? GRAPH_TENANT_ID : '';
            $this->clientId = defined('GRAPH_CLIENT_ID') ? GRAPH_CLIENT_ID : '';
            $this->clientSecret = defined('GRAPH_CLIENT_SECRET') ? GRAPH_CLIENT_SECRET : '';
        }
    }

    /**
     * Convert Graph's receivedDateTime (ISO8601, usually UTC) into the DB timestamp timezone.
     * This avoids "future" created_at values when DB server timezone differs from PHP/default.
     */
    private function formatGraphDateForDb(?string $iso): string {
        try {
            if (empty($iso)) {
                return date('Y-m-d H:i:s');
            }
            $dbTz = !empty($this->dbTimezone) ? (string)$this->dbTimezone : 'UTC';
            $dt = new DateTimeImmutable($iso);
            $dt = $dt->setTimezone(new DateTimeZone($dbTz));
            return $dt->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return date('Y-m-d H:i:s');
        }
    }
    
    /**
     * Get OAuth2 access token - first try delegated OAuth, then client credentials
     */
    public function getAccessToken() {
        // First, try to use the delegated OAuth token if available
        $oauthToken = $this->getDelegatedOAuthToken();
        if ($oauthToken) {
            return $oauthToken;
        }
        
        // Check if we have a valid cached token from client credentials
        if ($this->accessToken && $this->tokenExpiry && time() < $this->tokenExpiry) {
            return $this->accessToken;
        }
        
        // Fall back to client credentials flow
        $url = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";
        
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => 'https://graph.microsoft.com/.default',
            'grant_type' => 'client_credentials'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to get access token: ' . $response);
        }
        
        $result = json_decode($response, true);
        $this->accessToken = isset($result['access_token']) ? $result['access_token'] : null;
        $expiresIn = isset($result['expires_in']) ? $result['expires_in'] : 3600;
        $this->tokenExpiry = time() + $expiresIn - 60; // Refresh 1 minute early
        
        if (!$this->accessToken) {
            throw new Exception('No access token in response');
        }
        
        return $this->accessToken;
    }
    
    /**
     * Get delegated OAuth token from database if available and valid
     */
    private function getDelegatedOAuthToken() {
        if (!class_exists('Setting')) {
            require_once APPROOT . '/app/models/Setting.php';
        }
        
        $settingModel = new Setting();
        
        // Check if we have a stored OAuth token
        $accessToken = $settingModel->get('graph_access_token');
        $tokenExpires = $settingModel->get('graph_token_expires');
        
        if (!$accessToken || !$tokenExpires) {
            return null;
        }
        
        // Decode the token (it's base64 encoded for storage)
        $accessToken = base64_decode($accessToken);
        
        // Check if token is expired or about to expire (within 5 minutes)
        if ($tokenExpires - time() < 300) {
            // Try to refresh the token
            if ($this->refreshDelegatedToken()) {
                // Get the new token
                $accessToken = base64_decode($settingModel->get('graph_access_token'));
            } else {
                return null;
            }
        }
        
        return $accessToken;
    }
    
    /**
     * Refresh delegated OAuth token using refresh token
     */
    private function refreshDelegatedToken() {
        if (!class_exists('Setting')) {
            require_once APPROOT . '/app/models/Setting.php';
        }
        
        $settingModel = new Setting();
        $refreshToken = base64_decode($settingModel->get('graph_refresh_token'));
        
        if (!$refreshToken) {
            return false;
        }
        
        $url = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";
        
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
            'scope' => 'offline_access Mail.Read Mail.Send Mail.ReadWrite User.Read'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $tokens = json_decode($response, true);
            
            // Store the new tokens
            $settingModel->set('graph_access_token', base64_encode($tokens['access_token']));
            if (isset($tokens['refresh_token'])) {
                $settingModel->set('graph_refresh_token', base64_encode($tokens['refresh_token']));
            }
            
            $expiresIn = isset($tokens['expires_in']) ? $tokens['expires_in'] : 3600;
            $settingModel->set('graph_token_expires', time() + $expiresIn);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Download and save ticket attachment to disk
     * @param string $userEmail Email address
     * @param string $messageId Message ID
     * @param array $attachmentInfo Attachment metadata
     * @param int $attachmentRecordId Database record ID
     * @return bool Success
     */
    private function downloadAndSaveAttachment($userEmail, $messageId, $attachmentInfo, $attachmentRecordId) {
        try {
            $attachmentId = $attachmentInfo['id'] ?? null;
            if (empty($attachmentId)) {
                throw new Exception('Missing attachment id');
            }

            // Try raw bytes endpoint ($value) first
            $content = null;
            try {
                $rawResponse = $this->downloadAttachmentBytes($userEmail, $messageId, (string)$attachmentId);
                if ($rawResponse !== '' && $rawResponse !== false) {
                    // Check if response is JSON (Graph sometimes returns JSON on $value endpoint)
                    $firstChar = substr(trim($rawResponse), 0, 1);
                    if ($firstChar === '{' || $firstChar === '[') {
                        // It's JSON - extract contentBytes
                        $decoded = json_decode($rawResponse, true);
                        if (json_last_error() === JSON_ERROR_NONE && isset($decoded['contentBytes'])) {
                            $content = base64_decode($decoded['contentBytes']);
                            if ($content === false) {
                                $content = null;
                            }
                        }
                    } else {
                        // It's actual raw bytes
                        $content = $rawResponse;
                    }
                }
            } catch (Exception $e) {
                // $value endpoint failed, will fallback below
            }
            
            // Fallback to JSON attachment payload (contentBytes)
            if ($content === null || $content === '' || $content === false) {
                $attachmentData = $this->downloadAttachment($userEmail, $messageId, (string)$attachmentId);
                if (!isset($attachmentData['contentBytes'])) {
                    throw new Exception('No content in attachment response');
                }
                $content = base64_decode($attachmentData['contentBytes']);
                if ($content === false) {
                    throw new Exception('Failed to decode attachment content');
                }
            }
            
            // Create directory structure
            $uploadDir = APPROOT . '/public/uploads/ticket_attachments/' . date('Y/m');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate safe filename
            if (!class_exists('TicketAttachment')) {
                require_once APPROOT . '/app/models/TicketAttachment.php';
            }
            $attachmentModel = new TicketAttachment();
            $safeFilename = $attachmentModel->getSafeFilename($attachmentInfo['name'] ?? 'attachment');
            // Ensure uniqueness to avoid overwriting (prefix with record id)
            $safeFilename = $attachmentRecordId . '_' . $safeFilename;
            
            // Save file
            $filePath = $uploadDir . '/' . $safeFilename;
            $relativePath = 'uploads/ticket_attachments/' . date('Y/m') . '/' . $safeFilename;
            
            if (file_exists($filePath)) {
                $safeFilename = $attachmentRecordId . '_' . uniqid('', true) . '_' . $attachmentModel->getSafeFilename($attachmentInfo['name'] ?? 'attachment');
                $filePath = $uploadDir . '/' . $safeFilename;
                $relativePath = 'uploads/ticket_attachments/' . date('Y/m') . '/' . $safeFilename;
            }

            if (file_put_contents($filePath, $content) === false) {
                throw new Exception('Failed to save attachment to disk');
            }
            
            // Update database record
            $attachmentModel->markAsDownloaded($attachmentRecordId, $relativePath);
            
            return true;
        } catch (Exception $e) {
            error_log('downloadAndSaveAttachment Error: ' . $e->getMessage());
            
            // Mark download as failed
            try {
                if (!class_exists('TicketAttachment')) {
                    require_once APPROOT . '/app/models/TicketAttachment.php';
                }
                $attachmentModel = new TicketAttachment();
                $attachmentModel->markDownloadFailed($attachmentRecordId, $e->getMessage());
            } catch (Exception $ignored) {
                // ignore
            }
            
            return false;
        }
    }
    
    /**
     * Get attachments for an email
     * @param string $userEmail Email address
     * @param string $messageId Message ID
     * @return array Attachments data
     */
    public function getEmailAttachments($userEmail, $messageId) {
        $token = $this->getAccessToken();
        
        $encodedEmail = urlencode($userEmail);
        $encodedMsg = rawurlencode((string)$messageId);
        $url = "https://graph.microsoft.com/v1.0/users/{$encodedEmail}/messages/{$encodedMsg}/attachments";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log('Graph API Get Attachments CURL Error: ' . $curlError);
            throw new Exception('CURL Error: ' . $curlError);
        }
        
        if ($httpCode !== 200) {
            error_log('Graph API Get Attachments HTTP Error ' . $httpCode . ': ' . $response);
            return [];
        }
        
        $result = json_decode($response, true);
        return isset($result['value']) ? $result['value'] : [];
    }

    /**
     * Backfill/download attachments for a specific email message into TicketAttachments.
     * Useful for inline images or missed downloads.
     *
     * @return int number of attachments saved (records created)
     */
    public function backfillAttachmentsForEmailMessage(int $ticketId, string $supportEmail, string $messageId): int {
        $count = 0;
        try {
            if (!class_exists('TicketAttachment')) {
                require_once APPROOT . '/app/models/TicketAttachment.php';
            }
            $ticketAttachmentModel = new TicketAttachment();

            // Determine message row id (TicketMessages) for linkage
            $msgRow = $this->db->select(
                "SELECT TOP 1 id FROM TicketMessages WHERE email_message_id COLLATE Latin1_General_100_BIN2 = :mid ORDER BY id DESC",
                ['mid' => $messageId]
            );
            $ticketMessageId = !empty($msgRow) ? (int)$msgRow[0]['id'] : null;

            $attachments = $this->getEmailAttachments($supportEmail, $messageId);
            foreach ($attachments as $att) {
                $attId = $att['id'] ?? null;
                if (empty($attId)) { continue; }

                $existing = $ticketAttachmentModel->getByMsIds($messageId, $attId);
                if ($existing) { continue; }

                $recordId = $ticketAttachmentModel->create([
                    'ticket_id' => (int)$ticketId,
                    'ticket_message_id' => $ticketMessageId,
                    'ms_message_id' => $messageId,
                    'ms_attachment_id' => $attId,
                    'content_id' => $att['contentId'] ?? null,
                    'filename' => $att['name'] ?? ('attachment_' . $attId),
                    'original_filename' => $att['name'] ?? ('attachment_' . $attId),
                    'file_size' => $att['size'] ?? 0,
                    'mime_type' => $att['contentType'] ?? 'application/octet-stream',
                    'is_inline' => !empty($att['isInline']) ? 1 : 0,
                    'is_downloaded' => 0
                ]);

                if ($recordId) {
                    $this->downloadAndSaveAttachment($supportEmail, $messageId, $att, (int)$recordId);
                    $count++;
                }
            }
        } catch (Exception $e) {
            error_log('backfillAttachmentsForEmailMessage error: ' . $e->getMessage());
        }
        return $count;
    }
    
    /**
     * Download attachment content
     * @param string $userEmail Email address
     * @param string $messageId Message ID
     * @param string $attachmentId Attachment ID
     * @return array Attachment data with content
     */
    public function downloadAttachment($userEmail, $messageId, $attachmentId) {
        $token = $this->getAccessToken();
        
        $encodedEmail = urlencode($userEmail);
        $encodedMsg = rawurlencode((string)$messageId);
        $encodedAtt = rawurlencode((string)$attachmentId);
        $url = "https://graph.microsoft.com/v1.0/users/{$encodedEmail}/messages/{$encodedMsg}/attachments/{$encodedAtt}";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log('Graph API Download Attachment CURL Error: ' . $curlError);
            throw new Exception('CURL Error: ' . $curlError);
        }
        
        if ($httpCode !== 200) {
            error_log('Graph API Download Attachment HTTP Error ' . $httpCode . ': ' . $response);
            throw new Exception('Failed to download attachment (HTTP ' . $httpCode . ')');
        }
        
        return json_decode($response, true);
    }

    /**
     * Download raw attachment bytes via $value endpoint (works for file attachments reliably).
     */
    public function downloadAttachmentBytes($userEmail, $messageId, $attachmentId): string {
        $token = $this->getAccessToken();

        $encodedEmail = urlencode($userEmail);
        $encodedMsg = rawurlencode((string)$messageId);
        $encodedAtt = rawurlencode((string)$attachmentId);
        $url = "https://graph.microsoft.com/v1.0/users/{$encodedEmail}/messages/{$encodedMsg}/attachments/{$encodedAtt}/\$value";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception('CURL Error: ' . $curlError);
        }
        if ($httpCode !== 200) {
            throw new Exception('Failed to download attachment bytes (HTTP ' . $httpCode . ')');
        }

        return (string)$response;
    }
    
    /**
     * Get emails from inbox
     * @param string $userEmail Email address to check
     * @param int $limit Number of emails to retrieve
     * @param bool $unreadOnly Only get unread emails
     */
    public function getEmails($userEmail, $limit = 50, $unreadOnly = true) {
        error_log('Graph API getEmails called for: ' . $userEmail);
        $token = $this->getAccessToken();
        error_log('Graph API token obtained: ' . (strlen($token) > 10 ? 'Yes (' . strlen($token) . ' chars)' : 'No'));
        
        // URL encode the email address to handle special characters
        $encodedEmail = urlencode($userEmail);
        $url = "https://graph.microsoft.com/v1.0/users/{$encodedEmail}/messages";
        
        // Build query parameters separately
        $params = [];
        $params[] = '$top=' . $limit;
        $params[] = '$orderby=' . urlencode('receivedDateTime desc');
        $params[] = '$select=' . urlencode('id,conversationId,subject,from,toRecipients,receivedDateTime,body,bodyPreview,isRead,hasAttachments');
        
        if ($unreadOnly) {
            $params[] = '$filter=' . urlencode('isRead eq false');
        }
        
        $url .= '?' . implode('&', $params);
        
        error_log('Graph API URL: ' . $url);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log('Graph API CURL Error: ' . $curlError);
            throw new Exception('CURL Error: ' . $curlError);
        }
        
        if ($httpCode !== 200) {
            error_log('Graph API HTTP Error ' . $httpCode . ': ' . $response);
            $errorData = json_decode($response, true);
            $errorMsg = isset($errorData['error']['message']) ? $errorData['error']['message'] : $response;
            throw new Exception('Failed to get emails (HTTP ' . $httpCode . '): ' . $errorMsg);
        }
        
        $result = json_decode($response, true);
        return isset($result['value']) ? $result['value'] : [];
    }
    
    /**
     * Mark email as read
     */
    public function markAsRead($userEmail, $messageId) {
        $token = $this->getAccessToken();
        
        $encodedEmail = urlencode($userEmail);
        $url = "https://graph.microsoft.com/v1.0/users/{$encodedEmail}/messages/{$messageId}";
        
        $data = json_encode(['isRead' => true]);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }
    
    /**
     * Send email and return message ID
     * Uses createMessage + send to capture the sent message ID for deduplication
     * 
     * @return string|bool Message ID on success, false on failure
     */
    public function sendEmail($from, $to, $subject, $body, $cc = [], $bcc = [], $replyTo = null) {
        $token = $this->getAccessToken();
        
        $encodedFrom = urlencode($from);
        
        // Build recipients arrays
        $toRecipients = [];
        foreach ((array)$to as $email) {
            $toRecipients[] = ['emailAddress' => ['address' => $email]];
        }
        
        $ccRecipients = [];
        foreach ($cc as $email) {
            $ccRecipients[] = ['emailAddress' => ['address' => $email]];
        }
        
        $bccRecipients = [];
        foreach ($bcc as $email) {
            $bccRecipients[] = ['emailAddress' => ['address' => $email]];
        }
        
        $messageData = [
            'subject' => $subject,
            'body' => [
                'contentType' => 'HTML',
                'content' => $body
            ],
            'toRecipients' => $toRecipients
        ];
        
        if (!empty($ccRecipients)) {
            $messageData['ccRecipients'] = $ccRecipients;
        }
        
        if (!empty($bccRecipients)) {
            $messageData['bccRecipients'] = $bccRecipients;
        }
        
        if ($replyTo) {
            $messageData['replyTo'] = [
                ['emailAddress' => ['address' => $replyTo]]
            ];
        }
        
        // Step 1: Create draft message (returns message ID)
        $createUrl = "https://graph.microsoft.com/v1.0/users/{$encodedFrom}/messages";
        $ch = curl_init($createUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 201) {
            error_log('Graph API create message failed (HTTP ' . $httpCode . '): ' . $response);
            throw new Exception('Failed to create email message: ' . $response);
        }
        
        $messageResult = json_decode($response, true);
        $messageId = $messageResult['id'] ?? null;
        
        if (!$messageId) {
            throw new Exception('No message ID returned from Graph API');
        }
        
        // Step 2: Send the created message
        $encodedMessageId = rawurlencode($messageId);
        $sendUrl = "https://graph.microsoft.com/v1.0/users/{$encodedFrom}/messages/{$encodedMessageId}/send";
        
        $ch = curl_init($sendUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Length: 0'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 202) {
            error_log('Graph API send message failed (HTTP ' . $httpCode . '): ' . $response);
            throw new Exception('Failed to send email: ' . $response);
        }
        
        return $messageId;
    }

    /**
     * Get a single message (used for repairs / diagnostics)
     */
    public function getMessage($userEmail, $messageId) {
        $token = $this->getAccessToken();
        $encodedEmail = urlencode($userEmail);
        $encodedId = rawurlencode((string)$messageId);
        $url = "https://graph.microsoft.com/v1.0/users/{$encodedEmail}/messages/{$encodedId}?\$select=id,conversationId,subject,from,toRecipients,ccRecipients,receivedDateTime,body";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Prefer: outlook.body-content-type="html"'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception('CURL Error: ' . $curlError);
        }
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMsg = isset($errorData['error']['message']) ? $errorData['error']['message'] : $response;
            throw new Exception('Failed to get message (HTTP ' . $httpCode . '): ' . $errorMsg);
        }

        return json_decode($response, true);
    }

    /**
     * Public wrapper for inbound HTML sanitization (for repairs).
     */
    public function sanitizeInboundHtml(string $html): string {
        return $this->sanitizeEmailHtml($html);
    }
    
    /**
     * Delete email
     */
    public function deleteEmail($userEmail, $messageId) {
        $token = $this->getAccessToken();
        
        $encodedEmail = urlencode($userEmail);
        $url = "https://graph.microsoft.com/v1.0/users/{$encodedEmail}/messages/{$messageId}";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 204;
    }
    
    /**
     * Move email to folder
     */
    public function moveToFolder($userEmail, $messageId, $destinationFolderId) {
        $token = $this->getAccessToken();
        
        $encodedEmail = urlencode($userEmail);
        $url = "https://graph.microsoft.com/v1.0/users/{$encodedEmail}/messages/{$messageId}/move";
        
        $data = json_encode(['destinationId' => $destinationFolderId]);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 201;
    }
    
    /**
     * Get email folders
     */
    public function getFolders($userEmail) {
        $token = $this->getAccessToken();
        
        $encodedEmail = urlencode($userEmail);
        $url = "https://graph.microsoft.com/v1.0/users/{$encodedEmail}/mailFolders";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to get folders: ' . $response);
        }
        
        $result = json_decode($response, true);
        return isset($result['value']) ? $result['value'] : [];
    }
    
    /**
     * Process emails and create tickets
     *
     * @param string $supportEmail Mailbox address to read
     * @param bool $unreadOnly If true, only process unread messages
     * @param int $limit Number of messages to fetch
     * @return int|false Number of processed messages or false on error
     */
    public function processEmailsToTickets($supportEmail = 'support@yourdomain.com', $unreadOnly = true, $limit = 50) {
        try {
            error_log('processEmailsToTickets: Starting for ' . $supportEmail);
            
            // Get emails from Microsoft Graph
            $emails = $this->getEmails($supportEmail, $limit, $unreadOnly);
            error_log('processEmailsToTickets: Retrieved ' . count($emails) . ' emails');
            
            // Check if Ticket model exists
            if (!class_exists('Ticket')) {
                require_once APPROOT . '/app/models/Ticket.php';
            }
            $ticketModel = new Ticket();
            $processedCount = 0;
            

            $parseEmailBody = function(array $email) {
                $bodyType = strtolower((string)($email['body']['contentType'] ?? 'html'));
                $rawBody = (string)($email['body']['content'] ?? '');
                $bodyHtml = '';
                $bodyText = '';
                $fullHtml = '';
                if ($bodyType === 'text') {
                    $fullHtml = nl2br(htmlspecialchars($rawBody));
                    $bodyText = $this->extractLatestReplyText($rawBody);
                    $bodyHtml = nl2br(htmlspecialchars($bodyText));
                } else {
                    $fullHtml = $this->sanitizeEmailHtml($rawBody);
                    $bodyHtml = $this->extractLatestReplyHtml($fullHtml);
                    $bodyText = trim(strip_tags($bodyHtml));
                }
                return [$bodyType, $bodyHtml, $bodyText, $fullHtml];
            };

            foreach ($emails as $email) {
                error_log('processEmailsToTickets: Processing email: ' . $email['subject']);
                $messageId = $email['id'] ?? '';
                if (empty($messageId)) {
                    continue;
                }
                
                // Skip emails FROM the support address (these are our own sent emails bouncing back)
                $fromAddress = $email['from']['emailAddress']['address'] ?? '';
                if (strtolower($fromAddress) === strtolower($supportEmail)) {
                    error_log('processEmailsToTickets: Skipping outbound email from ' . $fromAddress . ' (same as support email)');
                    $this->markAsRead($supportEmail, $messageId);
                    continue;
                }

                // Dedupe: if a TicketMessage already exists with this message id, skip
                $dup = $this->db->select(
                    "SELECT TOP 1 id FROM TicketMessages WHERE email_message_id COLLATE Latin1_General_100_BIN2 = :mid",
                    ['mid' => $messageId]
                );
                if (!empty($dup)) {
                    // Still mark as read in Microsoft 365 so it doesn't reappear on next poll
                    $this->markAsRead($supportEmail, $messageId);
                    continue;
                }

                // Dedupe fallback: if a Ticket already exists for this exact message, attach it as message + mark read
                $existingByMsg = $this->db->select(
                    "SELECT TOP 1 id FROM Tickets WHERE original_message_id COLLATE Latin1_General_100_BIN2 = :mid ORDER BY id DESC",
                    ['mid' => $messageId]
                );
                if (!empty($existingByMsg) && !empty($existingByMsg[0]['id'])) {
                    $ticketId = (int)$existingByMsg[0]['id'];
                    $subjectOriginal = $email['subject'] ?? '';
                    $fromAddress = $email['from']['emailAddress']['address'] ?? '';
                    $emailDate = $this->formatGraphDateForDb($email['receivedDateTime'] ?? null);

                    $bodyType = strtolower((string)($email['body']['contentType'] ?? 'html'));
                    $rawBody = (string)($email['body']['content'] ?? '');
                    $bodyHtml = '';
                    $bodyText = '';
                    $fullHtml = '';
                    if ($bodyType === 'text') {
                        $fullHtml = nl2br(htmlspecialchars($rawBody));
                        $bodyText = $this->extractLatestReplyText($rawBody);
                        $bodyHtml = nl2br(htmlspecialchars($bodyText));
                    } else {
                        $fullHtml = $this->sanitizeEmailHtml($rawBody);
                        $bodyHtml = $this->extractLatestReplyHtml($fullHtml);
                        $bodyText = trim(strip_tags($bodyHtml));
                    }

                    // Create missing inbound message row so UI + dedupe work correctly
                    $ticketModel->addMessage($ticketId, [
                        'user_id' => null,
                        'message_type' => 'email_inbound',
                        'subject' => $subjectOriginal,
                        'content' => !empty($bodyHtml) ? $bodyHtml : $bodyText,
                        'content_format' => !empty($bodyHtml) ? 'html' : 'text',
                        'content_full' => $fullHtml,
                        'content_full_format' => ($bodyType === 'text') ? 'html' : 'html',
                        'email_message_id' => $messageId,
                        'email_from' => $fromAddress,
                        'email_to' => $supportEmail,
                        'email_cc' => null,
                        'email_headers' => null,
                        'is_public' => 1,
                        'created_at' => $emailDate,
                        'suppress_ticket_touch' => false
                    ]);

                    $this->markAsRead($supportEmail, $messageId);
                    $processedCount++;
                    continue;
                }
                
                $subjectOriginal = $email['subject'] ?? '';
                $fromAddress = $email['from']['emailAddress']['address'] ?? '';
                $emailDate = $this->formatGraphDateForDb($email['receivedDateTime'] ?? null);
                $conversationId = $email['conversationId'] ?? null;
                
                $bodyType = strtolower((string)($email['body']['contentType'] ?? 'html'));
                $rawBody = (string)($email['body']['content'] ?? '');
                $bodyHtml = '';
                $bodyText = '';
                $fullHtml = '';
                if ($bodyType === 'text') {
                    $fullHtml = nl2br(htmlspecialchars($rawBody));
                    $bodyText = $this->extractLatestReplyText($rawBody);
                    $bodyHtml = nl2br(htmlspecialchars($bodyText));
                } else {
                    $fullHtml = $this->sanitizeEmailHtml($rawBody);
                    $bodyHtml = $this->extractLatestReplyHtml($fullHtml);
                    $bodyText = trim(strip_tags($bodyHtml));
                }
                
                // Check if this is a reply to existing ticket
                $ticketId = null;
                
                if (preg_match('/\[TKT-\d{4}-\d{6}\]/', $subjectOriginal, $matches)) {
                    $ticketNumber = trim($matches[0], '[]');
                    $existingTicket = $ticketModel->getByNumber($ticketNumber);
                    
                    if ($existingTicket) {
                        $ticketId = $existingTicket['id'];
                        
                        // Add as message to existing ticket
                        $ticketModel->addMessage($ticketId, [
                            'user_id' => null,
                            'message_type' => 'email_inbound',
                            'subject' => $subjectOriginal,
                            'content' => !empty($bodyHtml) ? $bodyHtml : $bodyText,
                            'content_format' => !empty($bodyHtml) ? 'html' : 'text',
                            'content_full' => $fullHtml,
                            'content_full_format' => ($bodyType === 'text') ? 'html' : 'html',
                            'email_message_id' => $messageId,
                            'email_from' => $fromAddress,
                            'email_to' => $supportEmail,
                            'email_cc' => null,
                            'email_headers' => null,
                            'is_public' => 1,
                            'created_at' => $emailDate,
                            'suppress_ticket_touch' => false
                        ]);
                    }
                }

                // If no explicit ticket number, try to match by Graph conversationId -> Tickets.email_thread_id
                if (!$ticketId && !empty($conversationId)) {
                    $foundTicket = $this->db->select(
                        "SELECT TOP 1 id FROM Tickets WHERE email_thread_id = :tid ORDER BY created_at DESC",
                        ['tid' => $conversationId]
                    );
                    if (!empty($foundTicket) && !empty($foundTicket[0]['id'])) {
                        $ticketId = (int)$foundTicket[0]['id'];
                        $ticketModel->addMessage($ticketId, [
                            'user_id' => null,
                            'message_type' => 'email_inbound',
                            'subject' => $subjectOriginal,
                            'content' => !empty($bodyHtml) ? $bodyHtml : $bodyText,
                            'content_format' => !empty($bodyHtml) ? 'html' : 'text',
                            'content_full' => $fullHtml,
                            'content_full_format' => ($bodyType === 'text') ? 'html' : 'html',
                            'email_message_id' => $messageId,
                            'email_from' => $fromAddress,
                            'email_to' => $supportEmail,
                            'email_cc' => null,
                            'email_headers' => null,
                            'is_public' => 1,
                            'created_at' => $emailDate,
                            'suppress_ticket_touch' => false
                        ]);
                    }
                }
                
                // Create new ticket if not a reply (also create an inbound email message)
                if (!$ticketId) {
                    $ticketId = $ticketModel->createFromEmail([
                        'message_id' => $messageId,
                        'conversation_id' => $conversationId,
                        'subject' => $subjectOriginal,
                        'from_address' => $fromAddress,
                        'to_address' => $supportEmail,
                        'body_text' => $bodyText,
                        'body_html' => $bodyHtml,
                        'headers' => [],
                        'email_date' => $emailDate
                    ]);
                }

                if (!$ticketId) {
                    continue;
                }

                // Attachments: save non-inline and inline files to TicketAttachments
                try {
                    $hasAttachmentsFlag = !empty($email['hasAttachments']);
                    $mayHaveInlineCid = (stripos((string)$bodyHtml, 'cid:') !== false);
                    if ($hasAttachmentsFlag || $mayHaveInlineCid) {
                        if (!class_exists('TicketAttachment')) {
                            require_once APPROOT . '/app/models/TicketAttachment.php';
                        }
                        $ticketAttachmentModel = new TicketAttachment();

                        // Determine message row id (TicketMessages) for linkage
                        $msgRow = $this->db->select(
                            "SELECT TOP 1 id FROM TicketMessages WHERE email_message_id COLLATE Latin1_General_100_BIN2 = :mid ORDER BY id DESC",
                            ['mid' => $messageId]
                        );
                        $ticketMessageId = !empty($msgRow) ? (int)$msgRow[0]['id'] : null;

                        $attachments = $this->getEmailAttachments($supportEmail, $messageId);
                        foreach ($attachments as $att) {
                            $attId = $att['id'] ?? null;
                            if (empty($attId)) { continue; }

                            $existing = $ticketAttachmentModel->getByMsIds($messageId, $attId);
                            if ($existing) { continue; }

                            $recordId = $ticketAttachmentModel->create([
                                'ticket_id' => (int)$ticketId,
                                'ticket_message_id' => $ticketMessageId,
                                'ms_message_id' => $messageId,
                                'ms_attachment_id' => $attId,
                                'content_id' => $att['contentId'] ?? null,
                                'filename' => $att['name'] ?? ('attachment_' . $attId),
                                'original_filename' => $att['name'] ?? ('attachment_' . $attId),
                                'file_size' => $att['size'] ?? 0,
                                'mime_type' => $att['contentType'] ?? 'application/octet-stream',
                                'is_inline' => !empty($att['isInline']) ? 1 : 0,
                                'is_downloaded' => 0
                            ]);

                            // Do NOT download bytes inline here (keeps polling fast).
                            // A separate cron (process_ticket_attachments.php) will download pending attachments asynchronously.
                        }
                    }
                } catch (Exception $e) {
                    error_log('processEmailsToTickets: attachment processing failed: ' . $e->getMessage());
                }

                // Mark email as read
                $this->markAsRead($supportEmail, $messageId);
                $processedCount++;
            }
            

            // Backfill: some mail clients mark messages as read immediately.
            // When running unread-only, also scan recent messages and import READ replies
            // that belong to existing tickets (by [TKT-...] subject or conversationId).
            if ($unreadOnly) {
                try {
                    $recent = $this->getEmails($supportEmail, $limit, false);
                    foreach ($recent as $email) {
                        if (empty($email['isRead'])) { continue; }
                        $messageId = $email['id'] ?? '';
                        if (empty($messageId)) { continue; }
                        
                        // Skip emails FROM the support address (our own sent emails)
                        $fromAddress = $email['from']['emailAddress']['address'] ?? '';
                        if (strtolower($fromAddress) === strtolower($supportEmail)) {
                            continue;
                        }

                        // Case-sensitive dedupe (Graph IDs are case-sensitive)
                        $dup = $this->db->select(
                            "SELECT TOP 1 id FROM TicketMessages WHERE email_message_id COLLATE Latin1_General_100_BIN2 = :mid",
                            ['mid' => $messageId]
                        );
                        if (!empty($dup)) { continue; }

                        $subjectOriginal = $email['subject'] ?? '';
                        $emailDate = $this->formatGraphDateForDb($email['receivedDateTime'] ?? null);
                        $conversationId = $email['conversationId'] ?? null;

                        // Only attach to existing tickets; do NOT create new tickets from already-read mail
                        $ticketId = null;
                        if (preg_match('/\[TKT-\d{4}-\d{6}\]/', (string)$subjectOriginal, $matches)) {
                            $ticketNumber = trim($matches[0], '[]');
                            $existingTicket = $ticketModel->getByNumber($ticketNumber);
                            if ($existingTicket) {
                                $ticketId = (int)$existingTicket['id'];
                            }
                        }
                        if (!$ticketId && !empty($conversationId)) {
                            $foundTicket = $this->db->select(
                                "SELECT TOP 1 id FROM Tickets WHERE email_thread_id = :tid ORDER BY created_at DESC",
                                ['tid' => $conversationId]
                            );
                            if (!empty($foundTicket) && !empty($foundTicket[0]['id'])) {
                                $ticketId = (int)$foundTicket[0]['id'];
                            }
                        }
                        if (!$ticketId) { continue; }

                        [$bodyType, $bodyHtml, $bodyText, $fullHtml] = $parseEmailBody($email);

                        $ticketModel->addMessage($ticketId, [
                            'user_id' => null,
                            'message_type' => 'email_inbound',
                            'subject' => $subjectOriginal,
                            'content' => !empty($bodyHtml) ? $bodyHtml : $bodyText,
                            'content_format' => !empty($bodyHtml) ? 'html' : 'text',
                            'content_full' => $fullHtml,
                            'content_full_format' => 'html',
                            'email_message_id' => $messageId,
                            'email_from' => $fromAddress,
                            'email_to' => $supportEmail,
                            'email_cc' => null,
                            'email_headers' => null,
                            'is_public' => 1,
                            'created_at' => $emailDate,
                            'suppress_ticket_touch' => false
                        ]);

                        // Queue attachment records (async download)
                        try {
                            $hasAttachmentsFlag = !empty($email['hasAttachments']);
                            $mayHaveInlineCid = (stripos((string)$bodyHtml, 'cid:') !== false);
                            if ($hasAttachmentsFlag || $mayHaveInlineCid) {
                                if ($hasAttachmentsFlag || $mayHaveInlineCid) {
                                if (!class_exists('TicketAttachment')) {
                                    require_once APPROOT . '/app/models/TicketAttachment.php';
                                }
                                $ticketAttachmentModel = new TicketAttachment();

                                // Determine message row id (TicketMessages) for linkage
                                $msgRow = $this->db->select(
                                    "SELECT TOP 1 id FROM TicketMessages WHERE email_message_id COLLATE Latin1_General_100_BIN2 = :mid ORDER BY id DESC",
                                    ['mid' => $messageId]
                                );
                                $ticketMessageId = !empty($msgRow) ? (int)$msgRow[0]['id'] : null;

                                $attachments = $this->getEmailAttachments($supportEmail, $messageId);
                                foreach ($attachments as $att) {
                                    $attId = $att['id'] ?? null;
                                    if (empty($attId)) { continue; }

                                    $existing = $ticketAttachmentModel->getByMsIds($messageId, $attId);
                                    if ($existing) { continue; }

                                    $ticketAttachmentModel->create([
                                        'ticket_id' => (int)$ticketId,
                                        'ticket_message_id' => $ticketMessageId,
                                        'ms_message_id' => $messageId,
                                        'ms_attachment_id' => $attId,
                                        'content_id' => $att['contentId'] ?? null,
                                        'filename' => $att['name'] ?? ('attachment_' . $attId),
                                        'original_filename' => $att['name'] ?? ('attachment_' . $attId),
                                        'file_size' => $att['size'] ?? 0,
                                        'mime_type' => $att['contentType'] ?? 'application/octet-stream',
                                        'is_inline' => !empty($att['isInline']) ? 1 : 0,
                                        'is_downloaded' => 0
                                    ]);
                                    // Async download via process_ticket_attachments.php
                                }
                            }
                            }
                        } catch (Exception $e) {
                            error_log('processEmailsToTickets: backfill attachment processing failed: ' . $e->getMessage());
                        }

                        $processedCount++;
                    }
                } catch (Exception $e) {
                    error_log('processEmailsToTickets: backfill read-replies failed: ' . $e->getMessage());
                }
            }

            return $processedCount;
            
        } catch (Exception $e) {
            error_log('Graph API Process Emails Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Minimal sanitization for HTML emails before storing/rendering.
     */
    private function sanitizeEmailHtml(string $html): string {
        $html = (string)$html;
        if ($html === '') return $html;

        // Drop scripts/iframes
        $html = preg_replace('#<\s*(script|iframe)[^>]*>.*?<\s*/\s*\\1\s*>#is', '', $html);
        // Drop event handler attrs (onload=, onclick=, etc.)
        $html = preg_replace('/\\son\\w+\\s*=\\s*(\"[^\"]*\"|\\\'[^\\\']*\\\'|[^\\s>]+)/i', '', $html);
        // Drop javascript: URLs
        $html = preg_replace('/(href|src)\\s*=\\s*(\"|\\\')\\s*javascript:[^\"\\\']*(\"|\\\')/i', '$1=\"#\"', $html);

        return $html;
    }

    /**
     * Best-effort extraction of the latest reply content (strip quoted history).
     * Input should already be sanitized if HTML.
     */
    private function extractLatestReplyHtml(string $html): string {
        $html = (string)$html;
        if ($html === '') return $html;

        // Remove quoted blocks first
        $html = preg_replace('#<blockquote\\b[^>]*>.*?</blockquote>#is', '', $html);

        // Cut at common containers/separators used by clients
        $cutPatterns = [
            // Outlook / OWA reply/forward container
            '#<div[^>]+id\\s*=\\s*([\"\\\'])divRplyFwdMsg\\1[^>]*>.*$#is',
            // Gmail quoted section
            '#<div[^>]+class\\s*=\\s*([\"\\\'])gmail_quote\\1[^>]*>.*$#is',
            // Generic original message separator
            '#<div[^>]*>\\s*-{2,}\\s*Original Message\\s*-{2,}.*$#is',
            // "On ... wrote:" line wrapped in a div (best-effort)
            '#<div[^>]*>\\s*On\\s+.*?wrote:\\s*</div>.*$#is',
        ];

        foreach ($cutPatterns as $re) {
            $trimmed = preg_replace($re, '', $html);
            if (is_string($trimmed) && $trimmed !== $html) {
                $html = $trimmed;
                break;
            }
        }

        return trim($html);
    }

    private function extractLatestReplyText(string $text): string {
        $text = (string)$text;
        if ($text === '') return $text;

        $patterns = [
            // Outlook
            "/\\R-{2,}\\s*Original Message\\s*-{2,}\\R/i",
            "/\\RFrom:\\s.*\\RSent:\\s.*\\RTo:\\s.*\\R/i",
            // Gmail / generic
            "/\\ROn\\s.+?wrote:\\R/i",
        ];
        foreach ($patterns as $p) {
            if (preg_match($p, $text, $m, PREG_OFFSET_CAPTURE)) {
                $pos = $m[0][1];
                $text = substr($text, 0, $pos);
                break;
            }
        }

        return trim($text);
    }

    // Helper to rewrite cid: image references to public URLs
    private function rewriteCidImages($html, $cidMap) {
        if (empty($html) || empty($cidMap)) return $html;
        // Replace src='cid:...' or src="cid:..." with served file URL (case-insensitive)
        $pattern = '/src\s*=\s*("|\')cid:([^"\']+)(\1)/i';
        return preg_replace_callback($pattern, function($m) use ($cidMap) {
            $quote = $m[1];
            $cid = trim($m[2], '<>');
            if (isset($cidMap[$cid])) {
                $url = htmlspecialchars($cidMap[$cid], ENT_QUOTES, 'UTF-8');
                return 'src=' . $quote . $url . $quote;
            }
            return $m[0];
        }, $html);
    }

    /**
     * Normalize subject by removing leading RE:/FW:/FWD: prefixes.
     */
    private function normalizeSubject($subject) {
        try {
            $s = trim((string)$subject);
            // Remove repeated prefixes at start, e.g., "Re: Re: Fwd: Subject"
            $s = preg_replace('/^((\s*)(re|fw|fwd)(\s*):(\s*))+/i', '', $s);
            return trim($s);
        } catch (Exception $e) {
            return trim((string)$subject);
        }
    }
}
?>
