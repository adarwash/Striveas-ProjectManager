<?php

require_once APPROOT . '/app/core/EasySQL.php';

/**
 * Comprehensive Email Service for Ticketing System
 * Handles SMTP sending, IMAP/POP3 receiving, email parsing, and queue management
 */
class EmailService {
    private $db;
    private $config;
    private $lastSendError = null;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
        $this->config = $this->loadEmailConfig();
    }

    /**
     * Load email configuration from database settings
     * 
     * @return array Email configuration
     */
    private function loadEmailConfig() {
        try {
            // Load from Settings table if it exists
            $query = "SELECT setting_key, setting_value FROM Settings
                      WHERE setting_key LIKE 'email_%'
                         OR setting_key LIKE 'smtp_%'
                         OR setting_key LIKE 'inbound_%'
                         OR setting_key LIKE 'from_%'
                         OR setting_key LIKE 'auto_%'
                         OR setting_key LIKE 'ticket_%'
                         OR setting_key LIKE 'graph_%'";
            $settings = $this->db->select($query);
            
            $config = [
                // SMTP settings for sending
                'smtp_host' => 'localhost',
                'smtp_port' => 587,
                'smtp_username' => '',
                'smtp_password' => '',
                'smtp_encryption' => 'tls',
                'smtp_auth' => true,
                'from_email' => 'noreply@example.com',
                'from_name' => SITENAME,
                
                // Inbound Email Settings (supports both POP3 and IMAP)
                'inbound_protocol' => 'imap', // 'imap' or 'pop3'
                'inbound_auth_type' => 'password', // 'password' or 'oauth2'
                'inbound_host' => '',
                'inbound_port' => 993,
                'inbound_username' => '',
                'inbound_password' => '',
                'inbound_encryption' => 'ssl',
                'imap_folder' => 'INBOX', // Only used for IMAP
                
                // OAuth2 Settings
                'oauth2_provider' => 'microsoft', // 'microsoft' or 'google'
                'oauth2_client_id' => '',
                'oauth2_client_secret' => '',
                'oauth2_redirect_uri' => '',
                
                // Processing settings
                'auto_process_emails' => true,
                'max_attachment_size' => 10485760, // 10MB
                'allowed_file_types' => ['pdf', 'doc', 'docx', 'txt', 'png', 'jpg', 'jpeg', 'gif'],
                'delete_processed_emails' => false,
                'ticket_email_pattern' => '/\[TKT-\d{4}-\d{6}\]/'
            ];
            
            // Override with database settings
            foreach ($settings as $setting) {
                $config[$setting['setting_key']] = $setting['setting_value'];
            }
            
            return $config;
        } catch (Exception $e) {
            error_log('EmailService Config Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Send email immediately using Microsoft Graph API
     * 
     * @param array $emailData Email data
     * @return bool Success status
     */
    public function sendEmail($emailData) {
        try {
            $this->lastSendError = null;
            require_once APPROOT . '/app/services/MicrosoftGraphService.php';
            
            $graphService = new MicrosoftGraphService();
            
            // Pick the best mailbox to send from:
            // - Prefer the monitored support mailbox (app-permissions scenario)
            // - Else fallback to a delegated connected mailbox
            // - Else fallback to configured from_email
            $fromEmail = $this->config['graph_support_email']
                ?? ($this->config['graph_connected_email'] ?? ($this->config['from_email'] ?? null));
            $replyTo = $emailData['reply_to'] ?? ($this->config['graph_support_email'] ?? null);
            if (empty($fromEmail)) {
                throw new Exception('No From email configured for sending (graph_support_email / graph_connected_email / from_email).');
            }
            
            // Prepare email data for Microsoft Graph
            $to = is_array($emailData['to']) ? $emailData['to'] : [$emailData['to']];
            $cc = isset($emailData['cc']) && is_array($emailData['cc']) ? $emailData['cc'] : [];
            $bcc = isset($emailData['bcc']) && is_array($emailData['bcc']) ? $emailData['bcc'] : [];
            
            // Use HTML body if available, otherwise render text as HTML preserving newlines
            $rawHtml = $emailData['html_body'] ?? null;
            $rawText = $emailData['body'] ?? $emailData['text_body'] ?? '';
            $body = '';
            $contentFormat = 'text';
            if (is_string($rawHtml) && trim($rawHtml) !== '') {
                $body = $rawHtml;
                $contentFormat = 'html';
            } else {
                $body = "<div style=\"white-space:pre-wrap; font-family: Arial, sans-serif;\">" . nl2br(htmlspecialchars((string)$rawText)) . "</div>";
                $contentFormat = 'html';
            }
            
            // Custom headers for ticket tracking (will be included in email body for Graph API)
            $customHeaders = '';
            if (!empty($emailData['ticket_id'])) {
                try {
                    require_once APPROOT . '/app/models/Ticket.php';
                    $ticket = new Ticket();
                    $ticketData = $ticket->getById($emailData['ticket_id']);
                    if ($ticketData) {
                        $customHeaders = '
                        <div style="display:none;">
                            <meta name="X-Ticket-ID" content="' . $ticketData['id'] . '">
                            <meta name="X-Ticket-Number" content="' . $ticketData['ticket_number'] . '">
                        </div>';
                    }
                } catch (Exception $e) {
                    error_log('EmailService: Error loading ticket data: ' . $e->getMessage());
                }
            }
            
            // Add custom headers to body
            $fullBody = $customHeaders . $body;
            
            $result = $graphService->sendEmail(
                $fromEmail,
                $to,
                $emailData['subject'],
                $fullBody,
                $cc,
                $bcc
                ,$replyTo
            );
            
            if ($result) {
                error_log('EmailService: Email sent successfully via Microsoft Graph. To: ' . implode(',', $to));
                
                // Log successful send
                if (!empty($emailData['ticket_id'])) {
                    $this->logOutboundEmail($emailData, 'graph-' . time(), $fromEmail, $to, $fullBody, $contentFormat);
                }
            }
            
            return $result;
        } catch (Exception $e) {
            $this->lastSendError = $e->getMessage();
            error_log('EmailService Send Error (Microsoft Graph): ' . $e->getMessage());
            error_log('EmailService Send Error Stack: ' . $e->getTraceAsString());
            return false;
        }
    }

    public function getLastSendError(): ?string {
        return $this->lastSendError ? (string)$this->lastSendError : null;
    }

    /**
     * Queue email for later sending
     * 
     * @param array $emailData Email data
     * @param int $priority Priority (1=highest, 10=lowest)
     * @param DateTime $sendAfter When to send (null = immediately)
     * @return int|false Queue ID or false on failure
     */
    public function queueEmail($emailData, $priority = 5, $sendAfter = null) {
        try {
            $query = "INSERT INTO EmailQueue (
                to_address, cc_address, bcc_address, subject, body_text, body_html,
                ticket_id, message_id, template_name, status, priority, send_after
            ) VALUES (
                :to_address, :cc_address, :bcc_address, :subject, :body_text, :body_html,
                :ticket_id, :message_id, :template_name, :status, :priority, :send_after
            )";
            
            $params = [
                'to_address' => is_array($emailData['to']) ? implode(',', $emailData['to']) : $emailData['to'],
                'cc_address' => isset($emailData['cc']) && is_array($emailData['cc']) ? implode(',', $emailData['cc']) : ($emailData['cc'] ?? null),
                'bcc_address' => isset($emailData['bcc']) && is_array($emailData['bcc']) ? implode(',', $emailData['bcc']) : ($emailData['bcc'] ?? null),
                'subject' => $emailData['subject'],
                'body_text' => $emailData['body'] ?? $emailData['text_body'] ?? null,
                'body_html' => $emailData['html_body'] ?? null,
                'ticket_id' => $emailData['ticket_id'] ?? null,
                'message_id' => $emailData['message_id'] ?? null,
                'template_name' => $emailData['template'] ?? null,
                'status' => 'pending',
                'priority' => $priority,
                'send_after' => $sendAfter ? $sendAfter->format('Y-m-d H:i:s') : null
            ];
            
            return $this->db->insert($query, $params);
        } catch (Exception $e) {
            error_log('EmailService Queue Error: ' . $e->getMessage());
            error_log('EmailService Queue Error Stack: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Process email queue - send pending emails
     * 
     * @param int $limit Maximum emails to process
     * @return int Number of emails processed
     */
    public function processEmailQueue($limit = 10) {
        try {
            // Recover stuck emails that are in 'sending' state for more than 2 minutes
            try {
                $this->db->update(
                    "UPDATE EmailQueue SET status = 'pending', attempts = 0 \n" .
                    "WHERE status = 'sending' \n" .
                    "AND (last_attempt_at IS NULL OR last_attempt_at < DATEADD(minute, -2, GETDATE()))"
                );
            } catch (Exception $e) {
                error_log('EmailService ProcessQueue: Failed to recover stuck emails: ' . $e->getMessage());
            }

            // Also reset any emails stuck in 'sending' for more than 30 seconds
            try {
                $this->db->update(
                    "UPDATE EmailQueue SET status = 'pending', attempts = 0 \n" .
                    "WHERE status = 'sending' \n" .
                    "AND (last_attempt_at IS NULL OR last_attempt_at < DATEADD(second, -30, GETDATE()))"
                );
            } catch (Exception $e) {
                error_log('EmailService ProcessQueue: Failed to recover recent stuck emails: ' . $e->getMessage());
            }

            $limit = (int)$limit;
            if ($limit <= 0) { $limit = 10; }
            if ($limit > 100) { $limit = 100; }

            $query = "SELECT TOP {$limit} * FROM EmailQueue 
                     WHERE status = 'pending' 
                     AND (send_after IS NULL OR send_after <= GETDATE())
                     AND attempts < max_attempts
                     ORDER BY priority ASC, created_at ASC";
            
            $emails = $this->db->select($query);
            $processed = 0;
            
            foreach ($emails as $email) {
                $this->updateEmailQueueStatus($email['id'], 'sending');
                
                $emailData = [
                    'to' => explode(',', $email['to_address']),
                    'cc' => $email['cc_address'] ? explode(',', $email['cc_address']) : null,
                    'bcc' => $email['bcc_address'] ? explode(',', $email['bcc_address']) : null,
                    'subject' => $email['subject'],
                    'body' => $email['body_text'],
                    'html_body' => $email['body_html'],
                    'ticket_id' => $email['ticket_id']
                ];
                
                $success = $this->sendEmail($emailData);
                
                if ($success) {
                    // Clear any previous error_message on success
                    $this->updateEmailQueueStatus($email['id'], 'sent', null, date('Y-m-d H:i:s'));
                    $processed++;
                } else {
                    $attempts = $email['attempts'] + 1;
                    $status = $attempts >= $email['max_attempts'] ? 'failed' : 'pending';
                    $err = $this->getLastSendError() ?: 'Failed to send email';
                    // Keep error_message reasonably small for NVARCHAR(500)
                    if (is_string($err) && strlen($err) > 450) {
                        $err = substr($err, 0, 450) . '...';
                    }
                    $this->updateEmailQueueStatus($email['id'], $status, $err, null, $attempts);
                }
            }
            
            return $processed;
        } catch (Exception $e) {
            error_log('EmailService ProcessQueue Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update email queue status
     * 
     * @param int $id Queue ID
     * @param string $status New status
     * @param string $error Error message
     * @param string $sentAt Sent timestamp
     * @param int $attempts Attempt count
     * @return bool Success status
     */
    private function updateEmailQueueStatus($id, $status, $error = null, $sentAt = null, $attempts = null) {
        try {
            $setClauses = ['status = :status', 'last_attempt_at = GETDATE()'];
            $params = ['id' => $id, 'status' => $status];
            
            // Always update error_message when provided, and clear it on successful send
            if ($error !== null || $status === 'sent') {
                $setClauses[] = 'error_message = :error_message';
                $params['error_message'] = $error;
            }
            
            if ($sentAt !== null) {
                $setClauses[] = 'sent_at = :sent_at';
                $params['sent_at'] = $sentAt;
            } elseif ($status === 'sent') {
                $setClauses[] = 'sent_at = GETDATE()';
            }
            
            if ($attempts !== null) {
                $setClauses[] = 'attempts = :attempts';
                $params['attempts'] = $attempts;
            }
            
            $query = "UPDATE EmailQueue SET " . implode(', ', $setClauses) . " WHERE id = :id";
            return $this->db->update($query, $params);
        } catch (Exception $e) {
            error_log('EmailService UpdateQueueStatus Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Receive emails using configured protocol (IMAP or POP3)
     * 
     * @param int $limit Maximum emails to fetch
     * @return int Number of emails processed
     */
    public function receiveEmails($limit = 50) {
        try {
            // Prefer Microsoft 365 via Graph when configured
            try {
                if (!class_exists('Setting')) {
                    require_once APPROOT . '/app/models/Setting.php';
                }
                $settingModel = new Setting();
                $supportEmail = $settingModel->get('graph_support_email') ?: $settingModel->get('graph_connected_email');
                if (!empty($supportEmail)) {
                    require_once APPROOT . '/app/services/MicrosoftGraphService.php';
                    $graph = new MicrosoftGraphService();
                    $processed = $graph->processEmailsToTickets($supportEmail, true, (int)$limit);
                    return (int)($processed ?: 0);
                }
            } catch (Exception $e) {
                error_log('EmailService receiveEmails: Graph path failed: ' . $e->getMessage());
            }

            // Legacy IMAP/POP3 inbox ingestion relied on EmailInbox tables, which have been removed.
            return 0;
        } catch (Exception $e) {
            error_log('EmailService ReceiveEmails Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Receive emails from POP3 server
     * 
     * @param int $limit Maximum emails to fetch
     * @return int Number of emails processed
     */
    private function receiveEmailsPOP3($limit = 50) {
        $connection = $this->connectPOP3();
        if (!$connection) {
            return 0;
        }
        
        // Get message count
        $messageCount = imap_num_msg($connection);
        $processed = 0;
        
        if ($messageCount > 0) {
            $limit = min($limit, $messageCount); // Don't exceed available messages
            
            for ($i = 1; $i <= $limit; $i++) {
                $emailData = $this->parseEmailPOP3($connection, $i);
                if ($emailData && $this->storeInboundEmail($emailData)) {
                    $processed++;
                    
                    // Mark for deletion if configured (POP3 typically deletes after download)
                    if ($this->config['delete_processed_emails']) {
                        imap_delete($connection, $i);
                    }
                }
            }
            
            if ($this->config['delete_processed_emails']) {
                imap_expunge($connection);
            }
        }
        
        imap_close($connection);
        return $processed;
    }

    /**
     * Receive emails from IMAP server
     * 
     * @param int $limit Maximum emails to fetch
     * @return int Number of emails processed
     */
    private function receiveEmailsIMAP($limit = 50) {
        $connection = $this->connectIMAP();
        if (!$connection) {
            return 0;
        }
        
        $folder = $this->config['imap_folder'] ?? 'INBOX';
        imap_reopen($connection, '{' . $this->config['inbound_host'] . ':' . $this->config['inbound_port'] . '/imap/ssl}' . $folder);
        
        $emails = imap_search($connection, 'UNSEEN', SE_UID);
        $processed = 0;
        
        if ($emails) {
            $emails = array_slice($emails, 0, $limit); // Limit processing
            
            foreach ($emails as $uid) {
                $emailData = $this->parseEmailIMAP($connection, $uid);
                if ($emailData && $this->storeInboundEmail($emailData)) {
                    $processed++;
                    
                    // Mark as read if configured
                    if ($this->config['delete_processed_emails']) {
                        imap_delete($connection, $uid, UL_UID);
                    } else {
                        imap_setflag_full($connection, $uid, '\\Seen', ST_UID);
                    }
                }
            }
            
            if ($this->config['delete_processed_emails']) {
                imap_expunge($connection);
            }
        }
        
        imap_close($connection);
        return $processed;
    }

    /**
     * Connect to POP3 server
     * 
     * @return resource|false POP3 connection or false on failure
     */
    private function connectPOP3() {
        try {
            $encryption = strtolower($this->config['inbound_encryption']);
            $flags = '/pop3';
            
            if ($encryption === 'ssl') {
                $flags .= '/ssl';
            } elseif ($encryption === 'tls') {
                $flags .= '/tls';
            }
            
            $flags .= '/novalidate-cert';
            
            $server = '{' . $this->config['inbound_host'] . ':' . $this->config['inbound_port'] . $flags . '}';
            $connection = imap_open($server, $this->config['inbound_username'], $this->config['inbound_password']);
            
            if (!$connection) {
                error_log('POP3 Connection Error: ' . imap_last_error());
                return false;
            }
            
            return $connection;
        } catch (Exception $e) {
            error_log('EmailService ConnectPOP3 Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Connect to IMAP server (enhanced for Microsoft 365 and OAuth2 support)
     * 
     * @return resource|false IMAP connection or false on failure
     */
    private function connectIMAP() {
        try {
            $encryption = strtolower($this->config['inbound_encryption']);
            $flags = '/imap';
            
            if ($encryption === 'ssl') {
                $flags .= '/ssl';
            } elseif ($encryption === 'tls') {
                $flags .= '/tls';
            }
            
            // Enhanced flags for Microsoft 365 compatibility
            $flags .= '/novalidate-cert';
            
            // Check if this is Microsoft 365 and add specific flags
            $host = strtolower($this->config['inbound_host']);
            if (strpos($host, 'outlook.office365.com') !== false || 
                strpos($host, 'outlook.com') !== false) {
                // Microsoft 365 specific flags
                $flags .= '/norsh'; // Disable rsh fallback
            }
            
            $server = '{' . $this->config['inbound_host'] . ':' . $this->config['inbound_port'] . $flags . '}';
            
            // Clear any previous IMAP errors
            @imap_errors();
            @imap_alerts();
            
            // Determine authentication method
            $authType = $this->config['inbound_auth_type'] ?? 'password';
            $connection = false;
            
            if ($authType === 'oauth2') {
                $connection = $this->connectIMAPWithOAuth2($server);
            } else {
                $connection = $this->connectIMAPWithPassword($server);
            }
            
            if (!$connection) {
                $finalError = imap_last_error();
                error_log('IMAP Connection Failed: ' . $finalError);
                
                // Provide specific guidance based on auth type
                if ($authType === 'oauth2') {
                    error_log('OAuth2 Authentication Help: Check your OAuth2 configuration and ensure tokens are valid.');
                } else if (strpos(strtolower($finalError), 'authenticate') !== false) {
                    error_log('Password Authentication Help: Make sure you are using an App Password for Microsoft 365, not your regular password.');
                }
                
                return false;
            }
            
            return $connection;
        } catch (Exception $e) {
            error_log('EmailService ConnectIMAP Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Connect to IMAP using OAuth2 authentication
     */
    private function connectIMAPWithOAuth2($server) {
        try {
            // Load OAuth2 service
            require_once __DIR__ . '/OAuth2Service.php';
            $oauth2Service = new OAuth2Service($this->config);
            
            // Get valid access token
            $accessToken = $oauth2Service->getValidAccessToken(
                $this->config['oauth2_provider'],
                $this->config['oauth2_client_id'],
                $this->config['oauth2_client_secret']
            );
            
            // Generate XOAUTH2 string
            $xoauth2String = $oauth2Service->generateXOAUTH2String(
                $this->config['inbound_username'],
                $accessToken
            );
            
            // Attempt connection with OAuth2
            $connection = @imap_open($server, $this->config['inbound_username'], $xoauth2String, OP_HALFOPEN, 1, [
                'DISABLE_AUTHENTICATOR' => 'PLAIN'
            ]);
            
            if (!$connection) {
                // Try alternative OAuth2 connection method
                $connection = $this->connectIMAPWithXOAUTH2Alternative($server, $xoauth2String);
            }
            
            return $connection;
        } catch (Exception $e) {
            error_log('OAuth2 IMAP Connection Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Alternative OAuth2 connection method for better compatibility
     */
    private function connectIMAPWithXOAUTH2Alternative($server, $xoauth2String) {
        try {
            // Use stream context for OAuth2
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);
            
            // Try to connect with XOAUTH2 mechanism
            $connection = @imap_open($server, $this->config['inbound_username'], $xoauth2String, OP_HALFOPEN, 1, [
                'DISABLE_AUTHENTICATOR' => 'LOGIN,PLAIN'
            ]);
            
            return $connection;
        } catch (Exception $e) {
            error_log('Alternative OAuth2 Connection Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Connect to IMAP using password authentication
     */
    private function connectIMAPWithPassword($server) {
        $connection = false;
        $maxRetries = 3;
        
        for ($retry = 0; $retry < $maxRetries; $retry++) {
            $connection = @imap_open($server, $this->config['inbound_username'], $this->config['inbound_password']);
            
            if ($connection) {
                break; // Success
            } else {
                $lastError = imap_last_error();
                error_log("IMAP Password Auth Attempt " . ($retry + 1) . " failed: " . $lastError);
                
                // If authentication failed, don't retry (likely wrong credentials)
                if (strpos(strtolower($lastError), 'authenticate') !== false) {
                    break;
                }
                
                // Wait before retry
                if ($retry < $maxRetries - 1) {
                    sleep(1);
                }
            }
        }
        
        return $connection;
    }

    /**
     * Parse email from IMAP connection
     * 
     * @param resource $connection IMAP connection
     * @param int $uid Email UID
     * @return array|false Email data or false on failure
     */
    private function parseEmailIMAP($connection, $uid) {
        try {
            $overview = imap_fetch_overview($connection, $uid, UL_UID);
            $header = imap_headerinfo($connection, imap_msgno($connection, $uid));
            $structure = imap_fetchstructure($connection, $uid, UL_UID);
            
            if (empty($overview)) {
                return false;
            }
            
            $overview = $overview[0];
            
            $emailData = [
                'message_id' => $this->cleanMessageId($overview->message_id ?? ''),
                'subject' => $this->decodeHeader($overview->subject ?? ''),
                'from_address' => $this->extractEmail($header->from[0] ?? ''),
                'to_address' => $this->extractEmails($header->to ?? []),
                'cc_address' => $this->extractEmails($header->cc ?? []),
                'bcc_address' => $this->extractEmails($header->bcc ?? []),
                'reply_to' => $this->extractEmail($header->reply_to[0] ?? ''),
                'email_date' => date('Y-m-d H:i:s', strtotime($overview->date ?? 'now')),
                'body_text' => '',
                'body_html' => '',
                'headers' => [],
                'uid' => $uid,
                'flags' => $overview->flags ?? ''
            ];
            
            // Extract email body
            $body = $this->extractEmailBodyIMAP($connection, $uid, $structure);
            $emailData['body_text'] = $body['text'];
            $emailData['body_html'] = $body['html'];
            
            // Extract headers
            $rawHeaders = imap_fetchheader($connection, $uid, UL_UID);
            $emailData['headers'] = $this->parseHeaders($rawHeaders);
            
            return $emailData;
        } catch (Exception $e) {
            error_log('EmailService ParseEmailIMAP Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parse email from POP3 connection
     * 
     * @param resource $connection POP3 connection
     * @param int $messageNumber Message number (not UID)
     * @return array|false Email data or false on failure
     */
    private function parseEmailPOP3($connection, $messageNumber) {
        try {
            $overview = imap_fetch_overview($connection, $messageNumber);
            $header = imap_headerinfo($connection, $messageNumber);
            $structure = imap_fetchstructure($connection, $messageNumber);
            
            if (empty($overview)) {
                return false;
            }
            
            $overview = $overview[0];
            
            $emailData = [
                'message_id' => $this->cleanMessageId($overview->message_id ?? ''),
                'subject' => $this->decodeHeader($overview->subject ?? ''),
                'from_address' => $this->extractEmail($header->from[0] ?? ''),
                'to_address' => $this->extractEmails($header->to ?? []),
                'cc_address' => $this->extractEmails($header->cc ?? []),
                'bcc_address' => $this->extractEmails($header->bcc ?? []),
                'reply_to' => $this->extractEmail($header->reply_to[0] ?? ''),
                'email_date' => date('Y-m-d H:i:s', strtotime($overview->date ?? 'now')),
                'body_text' => '',
                'body_html' => '',
                'headers' => [],
                'uid' => $messageNumber, // Use message number for POP3
                'flags' => $overview->flags ?? ''
            ];
            
            // Extract email body
            $body = $this->extractEmailBodyPOP3($connection, $messageNumber, $structure);
            $emailData['body_text'] = $body['text'];
            $emailData['body_html'] = $body['html'];
            
            // Extract headers
            $rawHeaders = imap_fetchheader($connection, $messageNumber);
            $emailData['headers'] = $this->parseHeaders($rawHeaders);
            
            return $emailData;
        } catch (Exception $e) {
            error_log('EmailService ParseEmailPOP3 Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract email body parts for POP3
     * 
     * @param resource $connection POP3 connection
     * @param int $messageNumber Message number
     * @param object $structure Email structure
     * @return array Text and HTML body parts
     */
    private function extractEmailBodyPOP3($connection, $messageNumber, $structure) {
        $body = ['text' => '', 'html' => ''];
        
        if (!isset($structure->parts)) {
            // Single part message
            $content = imap_fetchbody($connection, $messageNumber, '1');
            $content = $this->decodeEmailContent($content, $structure->encoding ?? 0);
            
            if (isset($structure->subtype) && strtolower($structure->subtype) === 'html') {
                $body['html'] = $content;
                $body['text'] = strip_tags($content);
            } else {
                $body['text'] = $content;
            }
        } else {
            // Multi-part message
            $this->extractMultipartBodyPOP3($connection, $messageNumber, $structure->parts, '', $body);
        }
        
        return $body;
    }

    /**
     * Extract email body parts for IMAP
     * 
     * @param resource $connection IMAP connection
     * @param int $uid Email UID
     * @param object $structure Email structure
     * @return array Text and HTML body parts
     */
    private function extractEmailBodyIMAP($connection, $uid, $structure) {
        $body = ['text' => '', 'html' => ''];
        
        if (!isset($structure->parts)) {
            // Single part message
            $content = imap_fetchbody($connection, $uid, '1', UL_UID);
            $content = $this->decodeEmailContent($content, $structure->encoding ?? 0);
            
            if (isset($structure->subtype) && strtolower($structure->subtype) === 'html') {
                $body['html'] = $content;
                $body['text'] = strip_tags($content);
            } else {
                $body['text'] = $content;
            }
        } else {
            // Multi-part message
            $this->extractMultipartBodyIMAP($connection, $uid, $structure->parts, '', $body);
        }
        
        return $body;
    }

    /**
     * Extract body from multipart email for IMAP
     * 
     * @param resource $connection IMAP connection
     * @param int $uid Email UID
     * @param array $parts Email parts
     * @param string $prefix Part prefix
     * @param array &$body Body array reference
     */
    private function extractMultipartBodyIMAP($connection, $uid, $parts, $prefix, &$body) {
        foreach ($parts as $partIndex => $part) {
            $section = $prefix . ($partIndex + 1);
            
            if (isset($part->parts)) {
                // Nested multipart
                $this->extractMultipartBodyIMAP($connection, $uid, $part->parts, $section . '.', $body);
            } else {
                $content = imap_fetchbody($connection, $uid, $section, UL_UID);
                $content = $this->decodeEmailContent($content, $part->encoding ?? 0);
                
                // Check content type
                if (isset($part->subtype)) {
                    $subtype = strtolower($part->subtype);
                    if ($subtype === 'plain' && empty($body['text'])) {
                        $body['text'] = $content;
                    } elseif ($subtype === 'html' && empty($body['html'])) {
                        $body['html'] = $content;
                        if (empty($body['text'])) {
                            $body['text'] = strip_tags($content);
                        }
                    }
                }
            }
        }
    }

    /**
     * Extract body from multipart email for POP3
     * 
     * @param resource $connection POP3 connection
     * @param int $messageNumber Message number
     * @param array $parts Email parts
     * @param string $prefix Part prefix
     * @param array &$body Body array reference
     */
    private function extractMultipartBodyPOP3($connection, $messageNumber, $parts, $prefix, &$body) {
        foreach ($parts as $partIndex => $part) {
            $section = $prefix . ($partIndex + 1);
            
            if (isset($part->parts)) {
                // Nested multipart
                $this->extractMultipartBodyPOP3($connection, $messageNumber, $part->parts, $section . '.', $body);
            } else {
                $content = imap_fetchbody($connection, $messageNumber, $section);
                $content = $this->decodeEmailContent($content, $part->encoding ?? 0);
                
                // Check content type
                if (isset($part->subtype)) {
                    $subtype = strtolower($part->subtype);
                    if ($subtype === 'plain' && empty($body['text'])) {
                        $body['text'] = $content;
                    } elseif ($subtype === 'html' && empty($body['html'])) {
                        $body['html'] = $content;
                        if (empty($body['text'])) {
                            $body['text'] = strip_tags($content);
                        }
                    }
                }
            }
        }
    }

    /**
     * Extract email body parts (original IMAP method kept for compatibility)
     * 
     * @param resource $connection IMAP connection
     * @param int $uid Email UID
     * @param object $structure Email structure
     * @return array Text and HTML body parts
     */
    private function extractEmailBody($connection, $uid, $structure) {
        $body = ['text' => '', 'html' => ''];
        
        if (!isset($structure->parts)) {
            // Single part message
            $content = imap_fetchbody($connection, $uid, '1', UL_UID);
            $content = $this->decodeEmailContent($content, $structure->encoding ?? 0);
            
            if (isset($structure->subtype) && strtolower($structure->subtype) === 'html') {
                $body['html'] = $content;
                $body['text'] = strip_tags($content);
            } else {
                $body['text'] = $content;
            }
        } else {
            // Multi-part message
            $this->extractMultipartBody($connection, $uid, $structure->parts, '', $body);
        }
        
        return $body;
    }

    /**
     * Extract body from multipart email
     * 
     * @param resource $connection IMAP connection
     * @param int $uid Email UID
     * @param array $parts Email parts
     * @param string $prefix Part prefix
     * @param array &$body Body array reference
     */
    private function extractMultipartBody($connection, $uid, $parts, $prefix, &$body) {
        foreach ($parts as $partIndex => $part) {
            $section = $prefix . ($partIndex + 1);
            
            if (isset($part->parts)) {
                // Nested multipart
                $this->extractMultipartBody($connection, $uid, $part->parts, $section . '.', $body);
            } else {
                $content = imap_fetchbody($connection, $uid, $section, UL_UID);
                $content = $this->decodeEmailContent($content, $part->encoding ?? 0);
                
                // Check content type
                if (isset($part->subtype)) {
                    $subtype = strtolower($part->subtype);
                    if ($subtype === 'plain' && empty($body['text'])) {
                        $body['text'] = $content;
                    } elseif ($subtype === 'html' && empty($body['html'])) {
                        $body['html'] = $content;
                    }
                }
            }
        }
    }

    /**
     * Decode email content based on encoding
     * 
     * @param string $content Encoded content
     * @param int $encoding Encoding type
     * @return string Decoded content
     */
    private function decodeEmailContent($content, $encoding) {
        switch ($encoding) {
            case 1: // 8BIT
                return $content;
            case 2: // BINARY
                return $content;
            case 3: // BASE64
                return base64_decode($content);
            case 4: // QUOTED-PRINTABLE
                return quoted_printable_decode($content);
            default:
                return $content;
        }
    }

    /**
     * Store inbound email in database
     * 
     * @param array $emailData Email data
     * @return bool Success status
     */
    private function storeInboundEmail($emailData) {
        try {
            // EmailInbox tables are removed. We ingest directly into Tickets/TicketMessages.
            if (empty($emailData['message_id'])) {
                return false;
            }

            // Dedupe by message id
            $dup = $this->db->select(
                "SELECT TOP 1 id FROM TicketMessages WHERE email_message_id = :mid",
                ['mid' => $emailData['message_id']]
            );
            if (!empty($dup)) {
                return false;
            }

            if (!class_exists('Ticket')) {
                require_once APPROOT . '/app/models/Ticket.php';
            }
            $ticket = new Ticket();

            $ticketId = null;

            // Try to match existing ticket by ticket number in subject
            if (!empty($emailData['subject']) && preg_match($this->config['ticket_email_pattern'], $emailData['subject'], $matches)) {
                $ticketNumber = trim($matches[0], '[]');
                $existingTicket = $ticket->getByNumber($ticketNumber);
                if ($existingTicket) {
                    $ticketId = (int)$existingTicket['id'];
                    $ticket->addMessage($ticketId, [
                        'user_id' => $this->getUserByEmail($emailData['from_address']),
                        'message_type' => 'email_inbound',
                        'subject' => $emailData['subject'],
                        'content' => $emailData['body_html'] ?: $emailData['body_text'],
                        'content_format' => !empty($emailData['body_html']) ? 'html' : 'text',
                        'email_message_id' => $emailData['message_id'],
                        'email_from' => $emailData['from_address'],
                        'email_to' => $emailData['to_address'],
                        'email_cc' => $emailData['cc_address'],
                        'email_headers' => json_encode($emailData['headers'] ?? []),
                        'is_public' => 1,
                        'created_at' => $emailData['email_date'] ?? null,
                        'suppress_ticket_touch' => true
                    ]);
                }
            }

            if (!$ticketId) {
                $ticketId = $ticket->createFromEmail($emailData);
            }

            return !empty($ticketId);
        } catch (Exception $e) {
            error_log('EmailService StoreInboundEmail Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log outbound email
     * 
     * @param array $emailData Email data
     * @param string $messageId Message ID from mail server
     * @return bool Success status
     */
    private function logOutboundEmail($emailData, $messageId, $fromEmail, $toRecipients, $sentBodyHtml, $contentFormat = 'html') {
        try {
            if (!empty($emailData['ticket_id'])) {
                $ticket = new Ticket();
                $ticket->addMessage($emailData['ticket_id'], [
                    'user_id' => $_SESSION['user_id'] ?? null,
                    'message_type' => 'email_outbound',
                    'subject' => $emailData['subject'],
                    'content' => (string)$sentBodyHtml,
                    'content_format' => ($contentFormat === 'html') ? 'html' : 'text',
                    'email_message_id' => $messageId,
                    'email_from' => $fromEmail,
                    'email_to' => is_array($toRecipients) ? implode(',', $toRecipients) : (string)$toRecipients,
                    'email_cc' => !empty($emailData['cc']) ? (is_array($emailData['cc']) ? implode(',', $emailData['cc']) : $emailData['cc']) : null,
                    'is_public' => 1
                ]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('EmailService LogOutboundEmail Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Utility functions
     */
    
    private function cleanMessageId($messageId) {
        return trim($messageId, '<>');
    }
    
    private function decodeHeader($header) {
        $decoded = imap_mime_header_decode($header);
        $result = '';
        foreach ($decoded as $part) {
            $result .= $part->text;
        }
        return $result;
    }
    
    private function extractEmail($addressObj) {
        if (is_object($addressObj)) {
            $email = $addressObj->mailbox . '@' . $addressObj->host;
            return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
        }
        return '';
    }
    
    private function extractEmails($addressArray) {
        if (!is_array($addressArray)) return null;
        
        $emails = [];
        foreach ($addressArray as $addr) {
            $email = $this->extractEmail($addr);
            if ($email) $emails[] = $email;
        }
        
        return implode(',', $emails);
    }
    
    private function parseHeaders($rawHeaders) {
        $headers = [];
        $lines = explode("\r\n", $rawHeaders);
        
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }
        
        return $headers;
    }
    
    private function getUserByEmail($email) {
        try {
            $query = "SELECT id FROM Users WHERE email = :email";
            $result = $this->db->select($query, ['email' => $email]);
            return $result[0]['id'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Create email templates for ticket notifications
     * 
     * @param string $template Template name
     * @param array $data Template data
     * @return array Email data ready to send
     */
    public function createTicketEmail($template, $data) {
        $emailData = [
            'template' => $template,
            'ticket_id' => $data['ticket_id'] ?? null
        ];
        
        switch ($template) {
            case 'ticket_created':
                $emailData['subject'] = '[' . $data['ticket_number'] . '] New Ticket: ' . $data['subject'];
                $emailData['to'] = $data['assignee_email'] ?? $data['created_by_email'];
                $emailData['html_body'] = $this->renderTicketCreatedTemplate($data);
                break;
                
            case 'ticket_updated':
                $emailData['subject'] = '[' . $data['ticket_number'] . '] Ticket Updated: ' . $data['subject'];
                // For updates/replies, notify the requester/customer (inbound address first, then created_by)
                $emailData['to'] = $data['inbound_email_address'] ?? ($data['created_by_email'] ?? null);
                $emailData['html_body'] = $this->renderTicketUpdatedTemplate($data);
                break;
                
            case 'ticket_assigned':
                $emailData['subject'] = '[' . $data['ticket_number'] . '] Ticket Assigned to You: ' . $data['subject'];
                $emailData['to'] = $data['assignee_email'];
                $emailData['html_body'] = $this->renderTicketAssignedTemplate($data);
                break;
                
            case 'ticket_resolved':
                $emailData['subject'] = '[' . $data['ticket_number'] . '] Ticket Resolved: ' . $data['subject'];
                $emailData['to'] = $data['created_by_email'];
                $emailData['html_body'] = $this->renderTicketResolvedTemplate($data);
                break;
                
            case 'ticket_acknowledgment':
                $emailData['subject'] = '[' . $data['ticket_number'] . '] Thank you - Your support request has been received';
                $emailData['to'] = $data['inbound_email_address'] ?? $data['created_by_email'];
                $emailData['html_body'] = $this->renderTicketAcknowledgmentTemplate($data);
                break;
        }
        
        // Generate text version from HTML
        if (!empty($emailData['html_body'])) {
            $emailData['text_body'] = strip_tags($emailData['html_body']);
        }
        
        return $emailData;
    }

    /**
     * Simple template rendering methods
     * In a full implementation, these would use a proper template engine
     */
    
    private function renderTicketCreatedTemplate($data) {
        return "
        <h2>New Ticket Created</h2>
        <p><strong>Ticket:</strong> {$data['ticket_number']}</p>
        <p><strong>Subject:</strong> {$data['subject']}</p>
        <p><strong>Priority:</strong> {$data['priority']}</p>
        <p><strong>Category:</strong> {$data['category']}</p>
        <p><strong>Created by:</strong> {$data['created_by_name']}</p>
        <p><strong>Description:</strong></p>
        <div>" . nl2br(htmlspecialchars($data['description'])) . "</div>
        <p><a href='" . URLROOT . "/tickets/view/{$data['ticket_id']}'>View Ticket</a></p>
        ";
    }
    
    private function renderTicketUpdatedTemplate($data) {
        $updateHtml = !empty($data['update_message_html'])
            ? $data['update_message_html']
            : nl2br(htmlspecialchars($data['update_message']));

        $portalLink = '';
        if (function_exists('isCustomerAuthEnabled') && isCustomerAuthEnabled()) {
            $portalLink = "<p><strong>Customer Portal:</strong> <a href='" . URLROOT . "/customer/auth'>Sign in with Microsoft 365</a> to view and track your tickets online.</p>";
        }

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;'>
                Ticket Update
            </h2>

            <p>Hello " . htmlspecialchars($data['recipient_name'] ?? 'Valued Customer') . ",</p>

            <p>We have posted an update on your support request.</p>

            <div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #3498db; margin: 20px 0;'>
                <p><strong>Ticket Number:</strong> {$data['ticket_number']}</p>
                <p><strong>Subject:</strong> " . htmlspecialchars($data['subject']) . "</p>
                <p><strong>Updated:</strong> " . date('F j, Y \a\t g:i A') . "</p>
            </div>

            <p><strong>Update:</strong></p>
            <div style='background: #ffffff; border: 1px solid #eee; padding: 15px; border-radius: 6px;'>
                {$updateHtml}
            </div>

            <p style='margin-top: 18px;'>
                <a href='" . URLROOT . "/tickets/view/{$data['ticket_id']}' style='display:inline-block; background:#3498db; color:#fff; padding:10px 16px; border-radius:6px; text-decoration:none;'>
                    View Ticket
                </a>
            </p>

            <p><strong>Need to add more information?</strong><br>
            Simply reply to this email and your message will be added to the ticket.</p>

            " . $portalLink . "

            <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 14px; color: #666;'>
                <p><strong>Important:</strong> Please do not remove the ticket number [" . htmlspecialchars($data['ticket_number']) . "] from the subject line when replying to ensure your response is properly linked to this ticket.</p>

                <p>Best regards,<br>
                " . htmlspecialchars(SITENAME ?? 'Support Team') . " Support Team</p>
            </div>
        </div>
        ";
    }
    
    private function renderTicketAssignedTemplate($data) {
        return "
        <h2>Ticket Assigned to You</h2>
        <p><strong>Ticket:</strong> {$data['ticket_number']}</p>
        <p><strong>Subject:</strong> {$data['subject']}</p>
        <p><strong>Priority:</strong> {$data['priority']}</p>
        <p><strong>Due Date:</strong> {$data['due_date']}</p>
        <p><a href='" . URLROOT . "/tickets/view/{$data['ticket_id']}'>View Ticket</a></p>
        ";
    }
    
    private function renderTicketResolvedTemplate($data) {
        return "
        <h2>Ticket Resolved</h2>
        <p><strong>Ticket:</strong> {$data['ticket_number']}</p>
        <p><strong>Subject:</strong> {$data['subject']}</p>
        <p><strong>Resolution:</strong></p>
        <div>" . nl2br(htmlspecialchars($data['resolution'])) . "</div>
        <p><a href='" . URLROOT . "/tickets/view/{$data['ticket_id']}'>View Ticket</a></p>
        ";
    }
    
    private function renderTicketAcknowledgmentTemplate($data) {
        $portalLink = '';
        if (function_exists('isCustomerAuthEnabled') && isCustomerAuthEnabled()) {
            $portalLink = "<p><strong>Customer Portal:</strong> <a href='" . URLROOT . "/customer/auth'>Sign in with Microsoft 365</a> to view and track your tickets online.</p>";
        }
        
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;'>
                Thank you for contacting us
            </h2>
            
            <p>Dear " . htmlspecialchars($data['requester_name'] ?? 'Valued Customer') . ",</p>
            
            <p>We have received your support request and a ticket has been created for you.</p>
            
            <div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #3498db; margin: 20px 0;'>
                <p><strong>Ticket Number:</strong> {$data['ticket_number']}</p>
                <p><strong>Subject:</strong> " . htmlspecialchars($data['subject']) . "</p>
                <p><strong>Priority:</strong> " . htmlspecialchars($data['priority'] ?? 'Normal') . "</p>
                <p><strong>Created:</strong> " . date('F j, Y \a\t g:i A') . "</p>
            </div>
            
            <p><strong>What happens next?</strong></p>
            <ul>
                <li>Our support team will review your request</li>
                <li>You will receive email updates when there are any changes to your ticket</li>
                <li>Please keep this ticket number for your reference</li>
                <li>If you need to add more information, simply reply to this email</li>
            </ul>
            
            " . $portalLink . "
            
            <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 14px; color: #666;'>
                <p><strong>Important:</strong> Please do not remove the ticket number [" . htmlspecialchars($data['ticket_number']) . "] from the subject line when replying to ensure your response is properly linked to this ticket.</p>
                
                <p>Best regards,<br>
                " . htmlspecialchars(SITENAME ?? 'Support Team') . " Support Team</p>
            </div>
        </div>
        ";
    }
}