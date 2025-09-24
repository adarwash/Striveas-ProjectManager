<?php

/**
 * Email Inbox Controller
 * Handles email inbox interface for viewing ticket-related communications
 */
class Emailinbox extends Controller {
    private $emailInboxModel;
    private $ticketModel;
    
    public function __construct() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('users/login');
        }
        
        $this->emailInboxModel = $this->model('EmailInboxModel');
        $this->ticketModel = $this->model('Ticket');
    }
    
    /**
     * Main inbox view
     */
    public function index() {
        // Permission-based access
        if (!hasPermission('email.inbox')) {
            flash('error', 'You do not have permission to view the email inbox.');
            redirect('dashboard');
        }
        
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 25;
        
        // Build filters from GET parameters
        $filters = [
            'status' => $_GET['status'] ?? null,
            'search' => $_GET['search'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'has_ticket' => $_GET['has_ticket'] ?? null
        ];
        
        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });
        
        $data = $this->emailInboxModel->getAll($filters, $page, $limit);
        $statistics = $this->emailInboxModel->getStatistics();
        
        $viewData = [
            'emails' => $data['emails'],
            'pagination' => $data['pagination'],
            'filters' => $filters,
            'statistics' => $statistics
        ];
        
        $this->view('email_inbox/index', $viewData);
    }
    
    /**
     * View individual email
     */
    public function show($id) {
        $email = $this->emailInboxModel->getById($id);
        
        if (!$email) {
            flash('error', 'Email not found.');
            redirect('emailinbox');
        }
        
        // Permission-based access
        if (!hasPermission('email.inbox')) {
            flash('error', 'You do not have permission to view this email.');
            redirect('emailinbox');
        }
        
        // Get related ticket if exists
        $ticket = null;
        if ($email['ticket_id']) {
            $ticket = $this->ticketModel->getById($email['ticket_id']);
        }
        
        // Get attachments
        require_once APPROOT . '/app/models/EmailAttachment.php';
        $attachmentModel = new EmailAttachment();
        $attachments = $attachmentModel->getByEmailId($id);
        
        $viewData = [
            'email' => $email,
            'ticket' => $ticket,
            'attachments' => $attachments,
            'can_create_ticket' => hasPermission('tickets.create'),
            'can_link_ticket' => hasPermission('tickets.update')
        ];
        
        $this->view('email_inbox/view', $viewData);
    }
    
    /**
     * Create ticket from email
     */
    public function createTicket($emailId) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('emailinbox/show/' . $emailId);
        }
        
        if (!hasPermission('tickets.create')) {
            flash('error', 'You do not have permission to create tickets.');
            redirect('emailinbox/show/' . $emailId);
        }
        
        $email = $this->emailInboxModel->getById($emailId);
        if (!$email) {
            flash('error', 'Email not found.');
            redirect('emailinbox');
        }
        
        // Create ticket from email
        $ticketData = [
            'subject' => $email['subject'],
            'description' => $email['body_text'] ?: $email['body_html'],
            'created_by' => $_SESSION['user_id'],
            'source' => 'email',
            'priority_id' => $_POST['priority_id'] ?? 3,
            'category_id' => $_POST['category_id'] ?: null,
            'assigned_to' => $_POST['assigned_to'] ?: null,
            'inbound_email_address' => $email['from_address'],
            'email_thread_id' => $email['message_id'],
            'original_message_id' => $email['message_id']
        ];
        // Auto-link client by sender email domain when mapping exists
        try {
            if (!class_exists('ClientDomain')) {
                require_once APPROOT . '/app/models/ClientDomain.php';
            }
            $clientDomainModel = new ClientDomain();
            $mappedClientId = $clientDomainModel->getClientIdByEmail($email['from_address']);
            if (!empty($mappedClientId)) {
                $ticketData['client_id'] = (int)$mappedClientId;
            }
        } catch (Exception $e) {
            // ignore mapping errors
        }
        
        $ticketId = $this->ticketModel->create($ticketData);
        
        if ($ticketId) {
            // Link email to ticket
            $this->emailInboxModel->linkToTicket($emailId, $ticketId);
            
            // Send auto-acknowledgment for tickets created from email
            $this->sendAutoAcknowledgment($ticketId, $email['from_address']);
            
            // Add email as first message
            $this->ticketModel->addMessage($ticketId, [
                'user_id' => null, // External email
                'message_type' => 'email_inbound',
                'subject' => $email['subject'],
                'content' => $email['body_text'] ?: $email['body_html'],
                'content_format' => $email['body_html'] ? 'html' : 'text',
                'email_message_id' => $email['message_id'],
                'email_from' => $email['from_address'],
                'email_to' => $email['to_address'],
                'email_cc' => $email['cc_address'],
                'email_headers' => $email['raw_headers'],
                'is_public' => 1
            ]);
            
            flash('success', 'Ticket created successfully from email.');
            redirect('tickets/view/' . $ticketId);
        } else {
            flash('error', 'Failed to create ticket from email.');
            redirect('emailinbox/show/' . $emailId);
        }
    }
    
    /**
     * Link email to existing ticket
     */
    public function linkToTicket($emailId) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('emailinbox/show/' . $emailId);
        }
        
        if (!hasPermission('tickets.update')) {
            flash('error', 'You do not have permission to link emails to tickets.');
            redirect('emailinbox/show/' . $emailId);
        }
        
        $email = $this->emailInboxModel->getById($emailId);
        if (!$email) {
            flash('error', 'Email not found.');
            redirect('emailinbox');
        }
        
        $ticketNumber = trim($_POST['ticket_number']);
        if (empty($ticketNumber)) {
            flash('error', 'Ticket number is required.');
            redirect('emailinbox/show/' . $emailId);
        }
        
        // Find ticket by number
        $ticket = $this->ticketModel->getByNumber($ticketNumber);
        if (!$ticket) {
            flash('error', 'Ticket not found: ' . htmlspecialchars($ticketNumber));
            redirect('emailinbox/show/' . $emailId);
        }
        
        // Link email to ticket
        $success = $this->emailInboxModel->linkToTicket($emailId, $ticket['id']);
        
        if ($success) {
            // Add email as message to ticket
            $this->ticketModel->addMessage($ticket['id'], [
                'user_id' => null,
                'message_type' => 'email_inbound',
                'subject' => $email['subject'],
                'content' => $email['body_text'] ?: $email['body_html'],
                'content_format' => $email['body_html'] ? 'html' : 'text',
                'email_message_id' => $email['message_id'],
                'email_from' => $email['from_address'],
                'email_to' => $email['to_address'],
                'email_cc' => $email['cc_address'],
                'email_headers' => $email['raw_headers'],
                'is_public' => 1
            ]);
            
            flash('success', 'Email linked to ticket successfully.');
            redirect('tickets/view/' . $ticket['id']);
        } else {
            flash('error', 'Failed to link email to ticket.');
            redirect('emailinbox/show/' . $emailId);
        }
    }
    
    /**
     * Mark email as processed
     */
    public function markProcessed($emailId) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('emailinbox/show/' . $emailId);
        }
        
        if (!hasPermission('email.manage')) {
            flash('error', 'You do not have permission to manage emails.');
            redirect('emailinbox/show/' . $emailId);
        }
        
        $success = $this->emailInboxModel->updateStatus($emailId, 'processed');
        
        if ($success) {
            flash('success', 'Email marked as processed.');
        } else {
            flash('error', 'Failed to update email status.');
        }
        
        redirect('emailinbox/view/' . $emailId);
    }
    
    /**
     * Mark email as ignored
     */
    public function markIgnored($emailId) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('emailinbox/show/' . $emailId);
        }
        
        if (!hasPermission('email.manage')) {
            flash('error', 'You do not have permission to manage emails.');
            redirect('emailinbox/show/' . $emailId);
        }
        
        $success = $this->emailInboxModel->updateStatus($emailId, 'ignored');
        
        if ($success) {
            flash('success', 'Email marked as ignored.');
        } else {
            flash('error', 'Failed to update email status.');
        }
        
        redirect('emailinbox');
    }

    /**
     * Redownload all attachments for a specific email and fix CID image links
     */
    public function redownloadAttachments($emailId) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('emailinbox/show/' . $emailId);
        }

        // Permissions: admin/manager/technician
        $allowedRoles = ['admin', 'manager', 'technician'];
        $userRole = $_SESSION['role'] ?? '';
        if (!in_array($userRole, $allowedRoles)) {
            flash('error', 'You do not have permission to redownload attachments.');
            redirect('emailinbox/show/' . $emailId);
        }

        try {
            $email = $this->emailInboxModel->getById($emailId);
            if (!$email) {
                flash('error', 'Email not found.');
                redirect('emailinbox');
            }

            // Prefer Graph if configured; otherwise no-op (IMAP would need a fetch by UID implementation)
            require_once APPROOT . '/app/models/Setting.php';
            $settingModel = new Setting();
            $supportEmail = $settingModel->get('graph_support_email') ?: $settingModel->get('graph_connected_email');

            $downloaded = 0;
            if (!empty($supportEmail)) {
                require_once APPROOT . '/app/services/MicrosoftGraphService.php';
                $graph = new MicrosoftGraphService();
                // Get attachments for this message and re-save them
                $attachments = $graph->getEmailAttachments($supportEmail, $email['message_id']);
                if (!class_exists('EmailAttachment')) {
                    require_once APPROOT . '/app/models/EmailAttachment.php';
                }
                $attachmentModel = new EmailAttachment();

                foreach ($attachments as $att) {
                    // De-duplicate per email by ms_attachment_id
                    $existing = $attachmentModel->getByEmailAndMsId($email['id'], $att['id']);
                    if ($existing) {
                        $attachmentId = $existing['id'];
                    } else {
                        // Create record
                        $data = [
                            'email_inbox_id' => $email['id'],
                            'ms_attachment_id' => $att['id'],
                            'content_id' => $att['contentId'] ?? null,
                            'filename' => $att['name'],
                            'original_filename' => $att['name'],
                            'file_size' => $att['size'],
                            'mime_type' => $att['contentType'],
                            'is_inline' => isset($att['isInline']) && $att['isInline'] ? 1 : 0,
                            'is_downloaded' => 0
                        ];
                        $attachmentId = $attachmentModel->create($data);
                    }
                    if ($attachmentId) {
                        // Download content if not downloaded or file missing
                        $needDownload = true;
                        if ($existing && !empty($existing['is_downloaded']) && !empty($existing['file_path'])) {
                            $full = APPROOT . '/' . ltrim($existing['file_path'], '/');
                            $needDownload = !is_file($full);
                        }
                        $refClass = new ReflectionClass('MicrosoftGraphService');
                        $method = $refClass->getMethod('downloadAndSaveAttachment');
                        $method->setAccessible(true);
                        if ($needDownload && $method->invoke($graph, $supportEmail, $email['message_id'], $att, $attachmentId)) {
                            $downloaded++;
                        }
                    }
                }

                // Rebuild CID map and update email HTML
                if ($downloaded > 0) {
                    $attachmentsSaved = $attachmentModel->getByEmailId($email['id']);
                    $cidMap = [];
                    foreach ($attachmentsSaved as $saved) {
                        if (!empty($saved['is_inline']) && !empty($saved['content_id']) && !empty($saved['file_path'])) {
                            $cid = trim($saved['content_id'], '<>');
                            $cidMap[$cid] = URLROOT . '/' . ltrim($saved['file_path'], '/');
                        }
                    }
                    if (!empty($cidMap) && !empty($email['body_html'])) {
                        $rewrittenHtml = preg_replace_callback('/src\s*=\s*(\"|\')cid:([^\"\']+)(\1)/i', function($m) use ($cidMap) {
                            $q = $m[1];
                            $cid = trim($m[2], '<>');
                            return isset($cidMap[$cid]) ? 'src=' . $q . htmlspecialchars($cidMap[$cid], ENT_QUOTES, 'UTF-8') . $q : $m[0];
                        }, $email['body_html']);
                        $this->emailInboxModel->updateStatus($emailId, $email['processing_status']); // touch
                        $db = new EasySQL(DB1);
                        $db->update("UPDATE EmailInbox SET body_html = :html WHERE id = :id", ['html' => $rewrittenHtml, 'id' => $emailId]);
                    }
                }
            }

            flash('success', 'Redownload complete. Attachments processed: ' . $downloaded);
        } catch (Exception $e) {
            flash('error', 'Redownload failed: ' . $e->getMessage());
        }

        redirect('emailinbox/show/' . $emailId);
    }
    
    /**
     * Bulk operations on emails
     */
    public function bulkAction() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('emailinbox');
        }
        
        if (!hasPermission('email.manage')) {
            flash('error', 'You do not have permission to manage emails.');
            redirect('emailinbox');
        }
        
        $emailIds = $_POST['email_ids'] ?? [];
        $action = $_POST['bulk_action'] ?? '';
        
        if (empty($emailIds) || empty($action)) {
            flash('error', 'Please select emails and an action.');
            redirect('emailinbox');
        }
        
        $processed = 0;
        
        switch ($action) {
            case 'mark_processed':
                foreach ($emailIds as $emailId) {
                    if ($this->emailInboxModel->updateStatus($emailId, 'processed')) {
                        $processed++;
                    }
                }
                flash('success', "Marked $processed emails as processed.");
                break;
                
            case 'mark_ignored':
                foreach ($emailIds as $emailId) {
                    if ($this->emailInboxModel->updateStatus($emailId, 'ignored')) {
                        $processed++;
                    }
                }
                flash('success', "Marked $processed emails as ignored.");
                break;
                
            case 'delete':
                if (hasPermission('email.delete')) {
                    foreach ($emailIds as $emailId) {
                        if ($this->emailInboxModel->delete($emailId)) {
                            $processed++;
                        }
                    }
                    flash('success', "Deleted $processed emails.");
                } else {
                    flash('error', 'You do not have permission to delete emails.');
                }
                break;
                
            default:
                flash('error', 'Invalid action selected.');
        }
        
        redirect('emailinbox');
    }
    
    /**
     * Process pending emails manually
     */
    public function processPending() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('emailinbox');
        }
        
        if (!hasPermission('email.manage')) {
            flash('error', 'You do not have permission to process emails.');
            redirect('emailinbox');
        }
        
        try {
            require_once APPROOT . '/app/services/EmailService.php';
            $emailService = new EmailService();
            
            // Get pending emails
            $pendingEmails = $this->emailInboxModel->getPendingEmails();
            $processed = 0;
            
            foreach ($pendingEmails as $email) {
                // Try to process the email
                if ($this->processEmailToTicket($email)) {
                    $processed++;
                }
            }
            
            flash('success', "Processed $processed pending emails.");
        } catch (Exception $e) {
            flash('error', 'Error processing emails: ' . $e->getMessage());
        }
        
        redirect('emailinbox');
    }

    /**
     * Serve inline attachment by CID for a specific email
     * Route: /emailinbox/inline/{emailId}/{cid}
     */
    public function inline($emailId, $cidEncoded = '') {
        try {
            error_log('Emailinbox::inline start emailId=' . $emailId . ' cidEncoded=' . $cidEncoded);
            // Access control: allow inbox permission OR ticket viewers for linked tickets
            $hasInboxPerm = hasPermission('email.inbox');

            $cid = urldecode($cidEncoded);
            $cid = trim($cid, "<>");
            if (empty($cid) || empty($emailId)) {
                http_response_code(400);
                echo 'Bad Request';
                return;
            }

            $db = new EasySQL(DB1);
            // If no inbox permission, verify the email links to a ticket the user can view
            if (!$hasInboxPerm) {
                $emailRow = $this->emailInboxModel->getById($emailId);
                if (empty($emailRow) || empty($emailRow['ticket_id'])) {
                    http_response_code(403);
                    echo 'Forbidden';
                    return;
                }
                // Load ticket and verify access for current user
                $userId = $_SESSION['user_id'] ?? 0;
                require_once APPROOT . '/app/models/Ticket.php';
                $ticketModel = new Ticket();
                $ticket = $ticketModel->getById($emailRow['ticket_id']);
                if (!$ticket) {
                    http_response_code(403);
                    echo 'Forbidden';
                    return;
                }
                $canViewTicket = ($ticket['created_by'] == $userId) || ($ticket['assigned_to'] == $userId) || hasPermission('tickets.view_all') || hasPermission('tickets.read');
                if (!$canViewTicket) {
                    http_response_code(403);
                    echo 'Forbidden';
                    return;
                }
            }
            // Find attachment by content-id for this email
            $rows = $db->select(
                "SELECT TOP 1 file_path, mime_type, is_downloaded FROM EmailAttachments \n" .
                "WHERE email_inbox_id = :email_id AND (REPLACE(REPLACE(content_id,'<',''),'>','') = :cid OR content_id = :cid)",
                ['email_id' => $emailId, 'cid' => $cid]
            );

            if (!empty($rows)) {
                $att = $rows[0];
                if (!empty($att['is_downloaded']) && !empty($att['file_path'])) {
                    $filePath = APPROOT . '/' . ltrim($att['file_path'], '/');
                    error_log('Emailinbox::inline found local file ' . $filePath);
                    if (is_file($filePath)) {
                        header('Content-Type: ' . ($att['mime_type'] ?: 'application/octet-stream'));
                        header('Content-Length: ' . filesize($filePath));
                        header('Cache-Control: public, max-age=31536000, immutable');
                        readfile($filePath);
                        return;
                    }
                }
            }

            // Fallback: try to fetch from Microsoft Graph and stream directly
            $email = $this->emailInboxModel->getById($emailId);
            if ($email) {
                error_log('Emailinbox::inline fallback to Graph for message_id=' . ($email['message_id'] ?? '')); 
                require_once APPROOT . '/app/models/Setting.php';
                $settingModel = new Setting();
                $supportEmail = $settingModel->get('graph_support_email') ?: $settingModel->get('graph_connected_email');
                if (!empty($supportEmail) && !empty($email['message_id'])) {
                    require_once APPROOT . '/app/services/MicrosoftGraphService.php';
                    $graph = new MicrosoftGraphService();
                    $attachments = $graph->getEmailAttachments($supportEmail, $email['message_id']);
                    error_log('Emailinbox::inline graph attachments count=' . count($attachments));
                    foreach ($attachments as $ga) {
                        $gaCid = isset($ga['contentId']) ? trim($ga['contentId'], '<>') : '';
                        error_log('Emailinbox::inline graph attachment cid=' . $gaCid . ' match=' . (strcasecmp($gaCid, $cid) === 0 ? 'yes' : 'no'));
                        if ($gaCid && strcasecmp($gaCid, $cid) === 0) {
                            $ref = new ReflectionClass('MicrosoftGraphService');
                            $m = $ref->getMethod('downloadAttachment');
                            $m->setAccessible(true);
                            $data = $m->invoke($graph, $supportEmail, $email['message_id'], $ga['id']);
                            if (isset($data['contentBytes'])) {
                                $bytes = base64_decode($data['contentBytes']);
                                header('Content-Type: ' . ($ga['contentType'] ?? 'application/octet-stream'));
                                header('Content-Length: ' . strlen($bytes));
                                header('Cache-Control: public, max-age=600');
                                echo $bytes;
                                return;
                            }
                        }
                    }
                }
            }

            // If all fails, render a transparent pixel
            error_log('Emailinbox::inline fall-through transparent pixel for cid=' . $cid);
            $this->renderTransparentPixel();
        } catch (Exception $e) {
            $this->renderTransparentPixel();
        }
    }

    private function renderTransparentPixel() {
        // 1x1 transparent GIF
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
        header('Content-Type: image/gif');
        header('Content-Length: ' . strlen($gif));
        header('Cache-Control: public, max-age=600');
        echo $gif;
    }

    /**
     * Sync emails from Microsoft 365 into EmailInbox via Graph API
     */
    public function syncFromGraph() {
        // Allow POST or GET with explicit run flag to accommodate environments blocking POST
        $isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');
        $isGetExplicit = ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['run']));
        if (!$isPost && !$isGetExplicit) {
            redirect('emailinbox');
        }

        // Allow admin, manager, technician
        $allowedRoles = ['admin', 'manager', 'technician'];
        $userRole = $_SESSION['role'] ?? '';
        if (!in_array($userRole, $allowedRoles)) {
            flash('error', 'You do not have permission to sync emails.');
            redirect('emailinbox');
        }

        try {
            require_once APPROOT . '/app/services/MicrosoftGraphService.php';
            require_once APPROOT . '/app/models/Setting.php';

            $settings = new Setting();
            $supportEmail = $settings->get('graph_support_email');
            // Fallback to the connected account email if support mailbox not explicitly set
            if (empty($supportEmail)) {
                $supportEmail = $settings->get('graph_connected_email');
                error_log('Using connected email as support email: ' . $supportEmail);
            } else {
                error_log('Using configured support email: ' . $supportEmail);
            }
            if (empty($supportEmail)) {
                flash('error', 'Support mailbox email address is not configured in Email Settings.');
                redirect('emailinbox');
            }

            $graph = new MicrosoftGraphService();
            // For initial sync pull recent emails (read and unread)
            $processed = $graph->processEmailsToTickets($supportEmail, false, 50);

            if ($processed === false) {
                flash('error', 'Failed to sync emails from Microsoft 365. Check logs for details.');
            } else {
                flash('success', 'Synced ' . intval($processed) . ' emails from Microsoft 365.');
            }
        } catch (Exception $e) {
            error_log('Emailinbox syncFromGraph error: ' . $e->getMessage());
            flash('error', 'Sync failed: ' . $e->getMessage());
        }

        redirect('emailinbox');
    }
    
    /**
     * Download attachment
     */
    public function downloadAttachment($attachmentId) {
        // Check permissions
        $allowedRoles = ['admin', 'manager', 'technician'];
        $userRole = $_SESSION['role'] ?? '';
        
        if (!in_array($userRole, $allowedRoles)) {
            flash('error', 'You do not have permission to download attachments.');
            redirect('emailinbox');
        }
        
        require_once APPROOT . '/app/models/EmailAttachment.php';
        $attachmentModel = new EmailAttachment();
        $attachment = $attachmentModel->getById($attachmentId);
        
        if (!$attachment) {
            flash('error', 'Attachment not found.');
            redirect('emailinbox');
        }
        
        // Check if file exists
        $filePath = APPROOT . '/' . $attachment['file_path'];
        if (!$attachment['is_downloaded'] || !file_exists($filePath)) {
            flash('error', 'Attachment file not available.');
            redirect('emailinbox/show/' . $attachment['email_inbox_id']);
        }
        
        // Set headers for file download
        header('Content-Type: ' . $attachment['mime_type']);
        header('Content-Disposition: attachment; filename="' . $attachment['original_filename'] . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        // Output file
        readfile($filePath);
        exit;
    }
    
    /**
     * Helper method to process email to ticket
     */
    private function processEmailToTicket($email) {
        try {
            // Check if email is already linked
            if ($email['ticket_id']) {
                return false;
            }
            
            // Check if subject contains ticket number
            if (preg_match('/\[TKT-\d{4}-\d{6}\]/', $email['subject'], $matches)) {
                $ticketNumber = trim($matches[0], '[]');
                $ticket = $this->ticketModel->getByNumber($ticketNumber);
                
                if ($ticket) {
                    // Link to existing ticket
                    $this->emailInboxModel->linkToTicket($email['id'], $ticket['id']);
                    
                    // Add as message
                    $this->ticketModel->addMessage($ticket['id'], [
                        'user_id' => null,
                        'message_type' => 'email_inbound',
                        'subject' => $email['subject'],
                        'content' => $email['body_text'] ?: $email['body_html'],
                        'content_format' => $email['body_html'] ? 'html' : 'text',
                        'email_message_id' => $email['message_id'],
                        'email_from' => $email['from_address'],
                        'email_to' => $email['to_address'],
                        'email_cc' => $email['cc_address'],
                        'email_headers' => $email['raw_headers'],
                        'is_public' => 1
                    ]);
                    
                    return true;
                }
            }
            
            // Create new ticket if no existing ticket found
            $ticketData = [
                'subject' => $email['subject'],
                'description' => $email['body_text'] ?: $email['body_html'],
                'created_by' => 1, // System user
                'source' => 'email',
                'priority_id' => 3, // Normal priority
                'inbound_email_address' => $email['from_address'],
                'email_thread_id' => $email['message_id'],
                'original_message_id' => $email['message_id']
            ];
            // Auto-link client by sender email domain when mapping exists
            try {
                if (!class_exists('ClientDomain')) {
                    require_once APPROOT . '/app/models/ClientDomain.php';
                }
                $clientDomainModel = new ClientDomain();
                $mappedClientId = $clientDomainModel->getClientIdByEmail($email['from_address']);
                if (!empty($mappedClientId)) {
                    $ticketData['client_id'] = (int)$mappedClientId;
                }
            } catch (Exception $e) {
                // ignore mapping errors
            }
            
            $ticketId = $this->ticketModel->create($ticketData);
            
            if ($ticketId) {
                $this->emailInboxModel->linkToTicket($email['id'], $ticketId);
                
                // Send auto-acknowledgment email if enabled
                $this->sendAutoAcknowledgment($ticketId, $email['from_address']);
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log('ProcessEmailToTicket Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send auto-acknowledgment email for new ticket
     */
    private function sendAutoAcknowledgment($ticketId, $requesterEmail) {
        error_log("Emailinbox::sendAutoAcknowledgment called for ticket $ticketId, email $requesterEmail");
        try {
            // Basic validation and loop prevention
            if (empty($ticketId) || empty($requesterEmail)) {
                return;
            }
            // Avoid acknowledging to the support mailbox itself
            try {
                require_once APPROOT . '/app/models/Setting.php';
                $settingModel = new Setting();
                $supportEmail = $settingModel->get('graph_connected_email') ?: $settingModel->get('from_email');
                if (!empty($supportEmail) && strcasecmp(trim($supportEmail), trim($requesterEmail)) === 0) {
                    error_log('Skipping acknowledgment to support mailbox to avoid loops: ' . $requesterEmail);
                    return;
                }
            } catch (Exception $e) {
                // ignore and continue
            }
            
            // Check if auto-acknowledgment is enabled
            require_once APPROOT . '/app/models/Setting.php';
            $settingModel = new Setting();
            $autoAcknowledge = $settingModel->get('auto_acknowledge_tickets', true);
            
            if (!$autoAcknowledge) {
                return;
            }
            
            // Get ticket details
            $ticket = $this->ticketModel->getById($ticketId);
            if (!$ticket) {
                return;
            }
            
            // Prevent duplicate acknowledgments for same ticket+recipient
            try {
                $existing = $this->ticketModel->db->select(
                    "SELECT TOP 1 id FROM EmailQueue WHERE ticket_id = :tid AND to_address = :to AND subject LIKE '%Thank you%'",
                    ['tid' => $ticketId, 'to' => $requesterEmail]
                );
                if (!empty($existing)) {
                    error_log("Acknowledgment already queued/sent for ticket {$ticket['ticket_number']} to {$requesterEmail}");
                    return;
                }
            } catch (Exception $e) {
                // ignore and proceed
            }
            
            // Extract customer name from email
            $requesterName = strstr($requesterEmail, '@', true);
            $requesterName = ucwords(str_replace(['.', '_', '-'], ' ', $requesterName));
            
            // Get priority display name
            $priorityNames = [
                1 => 'Low',
                2 => 'Normal', 
                3 => 'High',
                4 => 'Critical'
            ];
            $priorityDisplay = $priorityNames[$ticket['priority_id']] ?? 'Normal';
            
            // Prepare email data
            require_once APPROOT . '/app/services/EmailService.php';
            $emailService = new EmailService();
            
            $emailData = $emailService->createTicketEmail('ticket_acknowledgment', [
                'ticket_id' => $ticket['id'],
                'ticket_number' => $ticket['ticket_number'],
                'subject' => $ticket['subject'],
                'priority' => $priorityDisplay,
                'requester_name' => $requesterName,
                'inbound_email_address' => $requesterEmail,
                'created_by_email' => $requesterEmail
            ]);
            
            // Queue the acknowledgment email
            $result = $emailService->queueEmail($emailData, 2); // High priority
            
            if ($result) {
                error_log("Auto-acknowledgment queued for ticket {$ticket['ticket_number']} to {$requesterEmail}");
                
                // Process email queue immediately to send the acknowledgment
                try {
                    $emailService->processEmailQueue();
                } catch (Exception $e) {
                    error_log('Failed to process acknowledgment email queue immediately: ' . $e->getMessage());
                }
            } else {
                error_log("Failed to queue auto-acknowledgment for ticket {$ticket['ticket_number']}");
            }
            
        } catch (Exception $e) {
            error_log('SendAutoAcknowledgment Error: ' . $e->getMessage());
        }
    }
}