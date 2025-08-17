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
                      WHERE setting_key IN ('graph_tenant_id', 'graph_client_id', 'graph_client_secret', 'graph_support_email')";
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
     * Download and save attachment to disk
     * @param string $userEmail Email address
     * @param string $messageId Message ID
     * @param array $attachmentInfo Attachment metadata
     * @param int $attachmentRecordId Database record ID
     * @return bool Success
     */
    private function downloadAndSaveAttachment($userEmail, $messageId, $attachmentInfo, $attachmentRecordId) {
        try {
            // Download attachment content
            $attachmentData = $this->downloadAttachment($userEmail, $messageId, $attachmentInfo['id']);
            
            if (!isset($attachmentData['contentBytes'])) {
                throw new Exception('No content in attachment response');
            }
            
            // Decode base64 content
            $content = base64_decode($attachmentData['contentBytes']);
            if ($content === false) {
                throw new Exception('Failed to decode attachment content');
            }
            
            // Create directory structure
            $uploadDir = APPROOT . '/uploads/email_attachments/' . date('Y/m');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate safe filename
            if (!class_exists('EmailAttachment')) {
                require_once APPROOT . '/app/models/EmailAttachment.php';
            }
            $attachmentModel = new EmailAttachment();
            $safeFilename = $attachmentModel->getSafeFilename($attachmentInfo['name']);
            
            // Save file
            $filePath = $uploadDir . '/' . $safeFilename;
            $relativePath = 'uploads/email_attachments/' . date('Y/m') . '/' . $safeFilename;
            
            if (file_put_contents($filePath, $content) === false) {
                throw new Exception('Failed to save attachment to disk');
            }
            
            // Calculate file hash
            $fileHash = hash_file('sha256', $filePath);
            
            // Update database record
            $attachmentModel->markAsDownloaded($attachmentRecordId, $relativePath, $fileHash);
            
            return true;
        } catch (Exception $e) {
            error_log('downloadAndSaveAttachment Error: ' . $e->getMessage());
            
            // Mark download as failed
            if (!class_exists('EmailAttachment')) {
                require_once APPROOT . '/app/models/EmailAttachment.php';
            }
            $attachmentModel = new EmailAttachment();
            $attachmentModel->markDownloadFailed($attachmentRecordId, $e->getMessage());
            
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
        $url = "https://graph.microsoft.com/v1.0/users/{$encodedEmail}/messages/{$messageId}/attachments";
        
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
     * Download attachment content
     * @param string $userEmail Email address
     * @param string $messageId Message ID
     * @param string $attachmentId Attachment ID
     * @return array Attachment data with content
     */
    public function downloadAttachment($userEmail, $messageId, $attachmentId) {
        $token = $this->getAccessToken();
        
        $encodedEmail = urlencode($userEmail);
        $url = "https://graph.microsoft.com/v1.0/users/{$encodedEmail}/messages/{$messageId}/attachments/{$attachmentId}";
        
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
     * Send email
     */
    public function sendEmail($from, $to, $subject, $body, $cc = [], $bcc = [], $replyTo = null) {
        $token = $this->getAccessToken();
        
        $encodedFrom = urlencode($from);
        $url = "https://graph.microsoft.com/v1.0/users/{$encodedFrom}/sendMail";
        
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
        
        $message = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $body
                ],
                'toRecipients' => $toRecipients
            ],
            'saveToSentItems' => true
        ];
        
        if (!empty($ccRecipients)) {
            $message['message']['ccRecipients'] = $ccRecipients;
        }
        
        if (!empty($bccRecipients)) {
            $message['message']['bccRecipients'] = $bccRecipients;
        }
        
        if ($replyTo) {
            $message['message']['replyTo'] = [
                ['emailAddress' => ['address' => $replyTo]]
            ];
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 202) {
            throw new Exception('Failed to send email: ' . $response);
        }
        
        return true;
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
            
            foreach ($emails as $email) {
                error_log('processEmailsToTickets: Processing email: ' . $email['subject']);
                // Check if email already processed (by message ID)
                $existingCheck = $this->db->select(
                    "SELECT id FROM EmailInbox WHERE message_id = :message_id",
                    ['message_id' => $email['id']]
                );
                
                if (!empty($existingCheck)) {
                    error_log('processEmailsToTickets: Email already exists, skipping');
                    continue; // Already processed
                }
                
                // Store in EmailInbox
                $emailData = [
                    'message_id' => $email['id'],
                    'subject' => $email['subject'],
                    'from_address' => $email['from']['emailAddress']['address'],
                    'to_address' => $supportEmail,
                    'body_text' => strip_tags($email['body']['content']),
                    'body_html' => $email['body']['content'],
                    'email_date' => date('Y-m-d H:i:s', strtotime($email['receivedDateTime'])),
                    'processing_status' => 'pending'
                ];
                
                // Check for attachments
                $hasAttachments = isset($email['hasAttachments']) && $email['hasAttachments'] === true;
                $attachmentCount = 0;
                
                error_log('processEmailsToTickets: Inserting email into EmailInbox');
                $emailInboxId = $this->db->insert(
                    "INSERT INTO EmailInbox (message_id, subject, from_address, to_address, body_text, body_html, email_date, processing_status, has_attachments, attachment_count) 
                     VALUES (:message_id, :subject, :from_address, :to_address, :body_text, :body_html, :email_date, :processing_status, :has_attachments, :attachment_count)",
                    array_merge($emailData, [
                        'has_attachments' => $hasAttachments ? 1 : 0,
                        'attachment_count' => $attachmentCount
                    ])
                );
                error_log('processEmailsToTickets: Insert result: ' . ($emailInboxId ? 'ID=' . $emailInboxId : 'Failed'));
                
                // Process attachments if email was inserted successfully
                if ($emailInboxId && $hasAttachments) {
                    try {
                        $attachments = $this->getEmailAttachments($supportEmail, $email['id']);
                        error_log('processEmailsToTickets: Found ' . count($attachments) . ' attachments');
                        
                        if (!class_exists('EmailAttachment')) {
                            require_once APPROOT . '/app/models/EmailAttachment.php';
                        }
                        $attachmentModel = new EmailAttachment();
                        $cidMap = [];
                        
                        foreach ($attachments as $attachment) {
                            // De-duplicate by ms_attachment_id per email
                            $existing = $attachmentModel->getByEmailAndMsId($emailInboxId, $attachment['id']);
                            if ($existing) {
                                $attachmentId = $existing['id'];
                            } else {
                                // Store attachment metadata
                                $attachmentData = [
                                    'email_inbox_id' => $emailInboxId,
                                    'ms_attachment_id' => $attachment['id'],
                                    'content_id' => $attachment['contentId'] ?? null,
                                    'filename' => $attachment['name'],
                                    'original_filename' => $attachment['name'],
                                    'file_size' => $attachment['size'],
                                    'mime_type' => $attachment['contentType'],
                                    'is_inline' => isset($attachment['isInline']) && $attachment['isInline'] ? 1 : 0,
                                    'is_downloaded' => 0
                                ];
                                $attachmentId = $attachmentModel->create($attachmentData);
                                if ($attachmentId) {
                                    $attachmentCount++;
                                    error_log('processEmailsToTickets: Created attachment record ID=' . $attachmentId);
                                }
                            }

                            if ($attachmentId) {
                                $attachmentCount++;
                                // If not yet downloaded or file missing, (re)download
                                $needDownload = true;
                                if ($existing && !empty($existing['is_downloaded']) && !empty($existing['file_path'])) {
                                    $full = APPROOT . '/' . ltrim($existing['file_path'], '/');
                                    $needDownload = !is_file($full);
                                }
                                
                                // Download and save attachment if needed
                                if ($needDownload && $this->downloadAndSaveAttachment($supportEmail, $email['id'], $attachment, $attachmentId)) {
                                    error_log('processEmailsToTickets: Downloaded attachment ' . $attachment['name']);
                                    // Build CID map for inline replacements
                                    $saved = $attachmentModel->getById($attachmentId);
                                    if ($saved && !empty($saved['is_inline']) && !empty($saved['content_id']) && !empty($saved['file_path'])) {
                                        $cid = trim($saved['content_id'], '<>');
                                        // saved file_path already includes the 'uploads/' prefix
                                        $cidMap[$cid] = URLROOT . '/' . ltrim($saved['file_path'], '/');
                                    }
                                }
                            }
                        }
                        
                        // If we have inline images, rewrite cid: URLs in stored HTML
                        if (!empty($cidMap)) {
                            $rewrittenHtml = $this->rewriteCidImages($emailData['body_html'], $cidMap);
                            $this->db->update(
                                "UPDATE EmailInbox SET body_html = :html WHERE id = :id",
                                ['html' => $rewrittenHtml, 'id' => $emailInboxId]
                            );
                            $emailData['body_html'] = $rewrittenHtml;
                        }
                        
                        // Update attachment count
                        if ($attachmentCount > 0) {
                            $this->db->update(
                                "UPDATE EmailInbox SET attachment_count = :count WHERE id = :id",
                                ['count' => $attachmentCount, 'id' => $emailInboxId]
                            );
                        }
                    } catch (Exception $e) {
                        error_log('processEmailsToTickets: Error processing attachments: ' . $e->getMessage());
                    }
                }
                
                // Check if this is a reply to existing ticket
                $ticketId = null;
                $subjectOriginal = $email['subject'];
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
                            'content' => !empty($emailData['body_html']) ? $emailData['body_html'] : $emailData['body_text'],
                            'content_format' => !empty($emailData['body_html']) ? 'html' : 'text',
                            'email_message_id' => $email['id'],
                            'email_from' => $email['from']['emailAddress']['address'],
                            'email_to' => $supportEmail,
                            'email_cc' => null,
                            'email_headers' => null,
                            'is_public' => 1
                        ]);
                    }
                }

                // If no explicit ticket number, try normalized-subject linking for replies/forwards
                if (!$ticketId) {
                    $normalizedSubject = $this->normalizeSubject($subjectOriginal);
                    if ($normalizedSubject !== trim($subjectOriginal)) {
                        $normLower = strtolower($normalizedSubject);
                        // First try: prior processed email with same normalized subject and a linked ticket (in same support mailbox)
                        $found = $this->db->select(
                            "SELECT TOP 1 ticket_id FROM EmailInbox \
                             WHERE ticket_id IS NOT NULL AND to_address = :support \
                               AND (LOWER(subject) = :norm \
                                 OR LOWER(subject) = :re \
                                 OR LOWER(subject) = :fw \
                                 OR LOWER(subject) = :fwd \
                                 OR LOWER(subject) = :re2 \
                                 OR LOWER(subject) = :fw2 \
                                 OR LOWER(subject) = :fwd2 \
                                 OR LOWER(subject) LIKE :likeSuffix) \
                             ORDER BY email_date DESC",
                            [
                                'support' => $supportEmail,
                                'norm' => $normLower,
                                're' => 're: ' . $normLower,
                                'fw' => 'fw: ' . $normLower,
                                'fwd' => 'fwd: ' . $normLower,
                                're2' => 're: re: ' . $normLower,
                                'fw2' => 'fw: fw: ' . $normLower,
                                'fwd2' => 'fwd: fwd: ' . $normLower,
                                'likeSuffix' => '%' . $normLower
                            ]
                        );
                        if (!empty($found) && !empty($found[0]['ticket_id'])) {
                            $ticketId = (int)$found[0]['ticket_id'];
                        } else {
                            // Fallback: a ticket created with that exact subject (also try common prefixes)
                            $foundTicket = $this->db->select(
                                "SELECT TOP 1 id FROM Tickets \
                                 WHERE (LOWER(subject) = :norm \
                                     OR LOWER(subject) = :re \
                                     OR LOWER(subject) = :fw \
                                     OR LOWER(subject) = :fwd \
                                     OR LOWER(subject) = :re2 \
                                     OR LOWER(subject) = :fw2 \
                                     OR LOWER(subject) = :fwd2 \
                                     OR LOWER(subject) LIKE :likeSuffix) \
                                 ORDER BY created_at DESC",
                                [
                                    'norm' => $normLower,
                                    're' => 're: ' . $normLower,
                                    'fw' => 'fw: ' . $normLower,
                                    'fwd' => 'fwd: ' . $normLower,
                                    're2' => 're: re: ' . $normLower,
                                    'fw2' => 'fw: fw: ' . $normLower,
                                    'fwd2' => 'fwd: fwd: ' . $normLower,
                                    'likeSuffix' => '%' . $normLower
                                ]
                            );
                            if (!empty($foundTicket) && !empty($foundTicket[0]['id'])) {
                                $ticketId = (int)$foundTicket[0]['id'];
                            }
                        }

                        if ($ticketId) {
                            $ticketModel->addMessage($ticketId, [
                                'user_id' => null,
                                'message_type' => 'email_inbound',
                                'subject' => $subjectOriginal,
                                'content' => !empty($emailData['body_html']) ? $emailData['body_html'] : $emailData['body_text'],
                                'content_format' => !empty($emailData['body_html']) ? 'html' : 'text',
                                'email_message_id' => $email['id'],
                                'email_from' => $email['from']['emailAddress']['address'],
                                'email_to' => $supportEmail,
                                'email_cc' => null,
                                'email_headers' => null,
                                'is_public' => 1
                            ]);
                        }
                    }
                }
                
                // Create new ticket if not a reply (also create an inbound email message)
                if (!$ticketId) {
                    $ticketId = $ticketModel->createFromEmail([
                        'message_id' => $email['id'],
                        'subject' => $email['subject'],
                        'from_address' => $email['from']['emailAddress']['address'],
                        'to_address' => $supportEmail,
                        'body_text' => $emailData['body_text'],
                        'body_html' => $emailData['body_html'],
                        'headers' => [],
                        'email_date' => date('Y-m-d H:i:s', strtotime($email['receivedDateTime']))
                    ]);
                }
                
                // Update EmailInbox with ticket link
                if ($ticketId) {
                    $this->db->update(
                        "UPDATE EmailInbox SET ticket_id = :ticket_id, processing_status = 'processed' WHERE id = :id",
                        ['ticket_id' => $ticketId, 'id' => $emailInboxId]
                    );
                    
                    // Mark email as read
                    $this->markAsRead($supportEmail, $email['id']);
                    
                    $processedCount++;
                }
            }
            
            return $processedCount;
            
        } catch (Exception $e) {
            error_log('Graph API Process Emails Error: ' . $e->getMessage());
            return false;
        }
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
