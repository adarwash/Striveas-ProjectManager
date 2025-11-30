<?php

/**
 * Ticket Controller for Ticketing System
 * Handles all ticket-related web operations
 */
class Tickets extends Controller {
    private $ticketModel;
    private $emailService;
    private $slaService;
    
    public function __construct() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('users/login');
        }
        
        $this->ticketModel = $this->model('Ticket');
        require_once APPROOT . '/app/services/EmailService.php';
        require_once APPROOT . '/app/services/SLAService.php';
        $this->emailService = new EmailService();
        $this->slaService = new SLAService();
    }
    
    /**
     * Ticket dashboard/listing page
     */
    public function index() {
        // Check permissions
        if (!hasPermission('tickets.read')) {
            flash('error', 'You do not have permission to view tickets.');
            redirect('dashboard');
        }
        
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 25;
        
        // Build filters from GET parameters
        $filters = [
            'status_id' => $_GET['status'] ?? null,
            'priority_id' => $_GET['priority'] ?? null,
            'category_id' => $_GET['category'] ?? null,
            'assigned_to' => $_GET['assigned_to'] ?? null,
            'search' => $_GET['search'] ?? null,
            // Only apply is_closed filter when the value is explicitly provided ("0" or "1"),
            // not when the query param is present but empty.
            'is_closed' => (isset($_GET['closed']) && $_GET['closed'] !== '') ? (int)$_GET['closed'] : null,
            'order_by' => $_GET['order_by'] ?? 'created_at DESC'
        ];

        // Support viewing unassigned tickets via assigned_to=unassigned
        if (isset($_GET['assigned_to']) && $_GET['assigned_to'] === 'unassigned') {
            $filters['assigned_is_null'] = true;
            unset($filters['assigned_to']);
        }
        
        // If user doesn't have admin permissions, limit to their tickets
        if (!hasPermission('tickets.view_all')) {
            $filters['assigned_to'] = $_SESSION['user_id'];
        }
        
        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });
        
        $data = $this->ticketModel->getAll($filters, $page, $limit);
        $lookupData = $this->ticketModel->getLookupData();
        $statistics = $this->ticketModel->getStatistics();
        
        // Get users for assignment filter
        $userModel = $this->model('User');
        $users = $userModel->getAllUsers();
        
        $viewData = [
            'tickets' => $data['tickets'],
            'pagination' => $data['pagination'],
            'filters' => $filters,
            'statuses' => $lookupData['statuses'],
            'priorities' => $lookupData['priorities'],
            'categories' => $lookupData['categories'],
            'users' => $users,
            'statistics' => $statistics
        ];
        
        $this->view('tickets/index', $viewData);
    }
    
    /**
     * View individual ticket
     */
    public function show($id) {
        $ticket = $this->ticketModel->getById($id);
        
        if (!$ticket) {
            flash('error', 'Ticket not found.');
            redirect('tickets');
        }
        
        // Check permissions
        if (!hasPermission('tickets.read') && 
            !$this->canAccessTicket($ticket, $_SESSION['user_id'])) {
            flash('error', 'You do not have permission to view this ticket.');
            redirect('tickets');
        }
        
        $messages = $this->ticketModel->getMessages($id, hasPermission('tickets.view_private'));

        // Load client info for display pill
        $client = null;
        if (!empty($ticket['client_id'])) {
            try {
                $clientModel = $this->model('Client');
                $client = $clientModel->getClientById($ticket['client_id']);
            } catch (Exception $e) {
                $client = null;
            }
        }
        $lookupData = $this->ticketModel->getLookupData();

        // Always include the original email content as the first message if ticket was created from email
        if (isset($ticket['source']) && $ticket['source'] === 'email') {
            // Try to get the original email content from EmailInbox
            $db = new EasySQL(DB1);
            $originalEmail = $db->select(
                "SELECT body_html, body_text, from_address, to_address, cc_address, subject, email_date 
                 FROM EmailInbox WHERE ticket_id = :ticket_id ORDER BY email_date ASC",
                ['ticket_id' => $id]
            );
            
            if (!empty($originalEmail)) {
                $email = $originalEmail[0];
                $content = $email['body_html'] ?: $email['body_text'] ?: $ticket['description'];
                
                $originalMessage = [
                    'id' => 'original_email',
                    'created_at' => $email['email_date'] ?: $ticket['created_at'],
                    'message_type' => 'email_inbound',
                    'subject' => $email['subject'] ?: $ticket['subject'],
                    'content' => $content,
                    'content_format' => $email['body_html'] ? 'html' : 'text',
                    'email_from' => $email['from_address'],
                    'email_to' => $email['to_address'],
                    'email_cc' => $email['cc_address'],
                    'is_public' => 1,
                    'is_system_message' => 0,
                    'username' => null,
                    'full_name' => $email['from_address'],
                    'user_id' => null
                ];
                
                // Always add the original email as the first message, regardless of existing messages
                array_unshift($messages, $originalMessage);
            } elseif (!empty($ticket['description'])) {
                // Fallback to ticket description if no email found
                $originalMessage = [
                    'id' => 'initial_description',
                    'created_at' => $ticket['created_at'],
                    'message_type' => 'email_inbound',
                    'subject' => $ticket['subject'],
                    'content' => $ticket['description'],
                    'content_format' => 'html',
                    'email_from' => $ticket['inbound_email_address'] ?? 'Unknown Sender',
                    'email_to' => '',
                    'email_cc' => '',
                    'is_public' => 1,
                    'is_system_message' => 0,
                    'username' => null,
                    'full_name' => $ticket['inbound_email_address'] ?? 'Unknown Sender',
                    'user_id' => null
                ];
                array_unshift($messages, $originalMessage);
            }
        }

        // If still no messages, try to pull related emails by subject from EmailInbox (helps when ticket was created via Web)
        if (empty($messages)) {
            try {
                $emailInboxModel = $this->model('EmailInboxModel');
                $threadEmails = $emailInboxModel->getEmailThread($ticket['subject'] ?? '', $ticket['original_message_id'] ?? '');
                if (!empty($threadEmails)) {
                    foreach ($threadEmails as $email) {
                        $messages[] = [
                            'id' => 'email_' . ($email['id'] ?? ''),
                            'created_at' => $email['email_date'] ?? ($email['received_at'] ?? date('Y-m-d H:i:s')),
                            'message_type' => 'email_inbound',
                            'subject' => $email['subject'] ?? '',
                            'content' => !empty($email['body_html']) ? $email['body_html'] : ($email['body_text'] ?? ''),
                            'content_format' => !empty($email['body_html']) ? 'html' : 'text',
                            'email_from' => $email['from_address'] ?? '',
                            'email_to' => $email['to_address'] ?? '',
                            'email_cc' => $email['cc_address'] ?? '',
                            'email_inbox_id' => $email['id'] ?? null,
                            'is_public' => 1,
                            'is_system_message' => 0,
                            'username' => null,
                            'full_name' => $email['from_address'] ?? '',
                            'user_id' => null
                        ];
                    }
                    // Sort by created time ascending to form a proper timeline
                    usort($messages, function($a, $b) {
                        return strtotime($a['created_at']) <=> strtotime($b['created_at']);
                    });
                }
            } catch (Exception $e) {
                error_log('Tickets@show: Failed to load related email thread: ' . $e->getMessage());
            }
        }
        
        // Rewrite inline CID images (cid:...) and filename-based embeds
        try {
            $db = new EasySQL(DB1);
            $inlineAttachments = $db->select(
                "SELECT ea.content_id, ea.file_path, ea.filename, ea.original_filename \n" .
                "FROM EmailAttachments ea \n" .
                "JOIN EmailInbox ei ON ea.email_inbox_id = ei.id \n" .
                "WHERE ei.ticket_id = :ticket_id AND ea.is_downloaded = 1",
                ['ticket_id' => $id]
            );
            $cidMap = [];
            $nameMap = [];
            foreach ($inlineAttachments as $att) {
                $url = URLROOT . '/' . ltrim($att['file_path'], '/');
                if (!empty($att['content_id'])) {
                    $cidMap[strtolower(trim($att['content_id'], '<>'))] = $url;
                }
                if (!empty($att['filename'])) {
                    $nameMap[strtolower($att['filename'])] = $url;
                }
                if (!empty($att['original_filename'])) {
                    $nameMap[strtolower($att['original_filename'])] = $url;
                }
            }
            if (!empty($cidMap) || !empty($nameMap)) {
                foreach ($messages as $idx => $msg) {
                    if (($msg['content_format'] ?? 'text') !== 'html' || empty($msg['content'])) continue;
                    $html = $msg['content'];
                    if (!empty($cidMap)) {
                        $html = preg_replace_callback('/src\s*=\s*(\"|\')cid:([^\"\']+)(\1)/i', function($m) use ($cidMap, $nameMap) {
                            $q = $m[1];
                            $cidRaw = trim($m[2], '<>');
                            $cidKey = strtolower($cidRaw);
                            if (isset($cidMap[$cidKey])) {
                                return 'src=' . $q . htmlspecialchars($cidMap[$cidKey], ENT_QUOTES, 'UTF-8') . $q;
                            }
                            $atPos = strpos($cidKey, '@');
                            if ($atPos !== false) {
                                $base = substr($cidKey, 0, $atPos);
                                if (isset($nameMap[$base])) {
                                    return 'src=' . $q . htmlspecialchars($nameMap[$base], ENT_QUOTES, 'UTF-8') . $q;
                                }
                            }
                            $baseAlt = strtolower(basename($cidRaw));
                            if (isset($nameMap[$baseAlt])) {
                                return 'src=' . $q . htmlspecialchars($nameMap[$baseAlt], ENT_QUOTES, 'UTF-8') . $q;
                            }
                            return $m[0];
                        }, $html);
                    }
                    if (!empty($nameMap)) {
                        // Replace relative filename references (not http(s) or data: or cid:)
                        $html = preg_replace_callback('/src\s*=\s*(\"|\')(?!https?:|data:|cid:)([^\"\']+)(\1)/i', function($m) use ($nameMap) {
                            $q = $m[1];
                            $src = strtolower(basename($m[2]));
                            $atPos = strpos($src, '@');
                            if ($atPos !== false) {
                                $src = substr($src, 0, $atPos);
                            }
                            return isset($nameMap[$src]) ? 'src=' . $q . htmlspecialchars($nameMap[$src], ENT_QUOTES, 'UTF-8') . $q : $m[0];
                        }, $html);
                    }
                    $messages[$idx]['content'] = $html;
                }
            }
        } catch (Exception $e) {
            // ignore rewrite errors
        }

        // Collect attachments for this ticket to show in sidebar
        $attachments = [];
        try {
            $db = new EasySQL(DB1);
            
            // Smart auto-download: only run if there are pending attachments or emails without attachment records
            $pendingAttachments = $db->select(
                "SELECT COUNT(*) as count FROM EmailAttachments ea "
                . "JOIN EmailInbox ei ON ea.email_inbox_id = ei.id "
                . "WHERE ei.ticket_id = :ticket_id AND ea.is_downloaded = 0",
                ['ticket_id' => $id]
            );
            
            // Check if there are emails with has_attachments=1 but no attachment records
            $emailsWithAttachmentsButNoRecords = $db->select(
                "SELECT COUNT(*) as count FROM EmailInbox ei "
                . "WHERE ei.ticket_id = :ticket_id "
                . "AND ei.has_attachments = 1 "
                . "AND NOT EXISTS (SELECT 1 FROM EmailAttachments ea WHERE ea.email_inbox_id = ei.id)",
                ['ticket_id' => $id]
            );
            
            // Only auto-download if there are pending attachments or emails that need attachment processing
            // Skip auto-download if we're in auto-reload mode to prevent infinite loops
            if (!isset($_GET['auto_reload']) && 
                ((!empty($pendingAttachments) && $pendingAttachments[0]['count'] > 0) || 
                 (!empty($emailsWithAttachmentsButNoRecords) && $emailsWithAttachmentsButNoRecords[0]['count'] > 0))) {
                
                try {
                    $attachmentsDownloaded = $this->downloadAttachmentsForTicket($id, true); // true = silent auto-download
                    
                    // If attachments were downloaded, redirect to reload the page
                    if ($attachmentsDownloaded) {
                        redirect('tickets/show/' . $id . '?auto_reload=1');
                    }
                } catch (Exception $e) {
                    error_log('Auto-download attachments failed: ' . $e->getMessage());
                }
            }
            
            $attachments = $db->select(
                "SELECT ea.id, ea.filename, ea.original_filename, ea.file_path, ea.file_size, ea.mime_type, ea.is_inline, ea.is_downloaded, ea.email_inbox_id, "
                . "       ei.subject, ei.email_date, "
                . "       ROW_NUMBER() OVER (PARTITION BY ea.original_filename, ea.file_size ORDER BY ea.id) as row_num \n"
                . "FROM EmailAttachments ea \n"
                . "JOIN EmailInbox ei ON ea.email_inbox_id = ei.id \n"
                . "WHERE ei.ticket_id = :ticket_id AND ea.is_downloaded = 1 \n"
                . "ORDER BY ei.email_date ASC, ea.filename ASC",
                ['ticket_id' => $id]
            );
            
            // Filter out duplicates (keep only the first occurrence of each unique file)
            $uniqueAttachments = [];
            foreach ($attachments as $att) {
                if ($att['row_num'] == 1) {
                    $uniqueAttachments[] = $att;
                }
            }
            $attachments = $uniqueAttachments;
        } catch (Exception $e) {
            $attachments = [];
        }

        // Get users for assignment
        $userModel = $this->model('User');
        $users = $userModel->getAllUsers();
        
        // Calculate SLA deadlines if not already set
        if (empty($ticket['sla_response_deadline']) || empty($ticket['sla_resolution_deadline'])) {
            try {
                $this->slaService->calculateSLADeadlines($id);
            } catch (Exception $e) {
                error_log('Failed to calculate SLA deadlines: ' . $e->getMessage());
            }
        }
        
        // Check for SLA breaches
        try {
            $slaBreaches = $this->slaService->checkSLABreaches($id);
            if (!empty($slaBreaches)) {
                $breachMessages = [];
                if (in_array('response', $slaBreaches)) {
                    $breachMessages[] = 'Response SLA breached';
                }
                if (in_array('resolution', $slaBreaches)) {
                    $breachMessages[] = 'Resolution SLA breached';
                }
                flash('warning', 'SLA Alert: ' . implode(', ', $breachMessages));
            }
        } catch (Exception $e) {
            error_log('Failed to check SLA breaches: ' . $e->getMessage());
        }
        
        // Get SLA status
        try {
            $slaStatus = $this->slaService->getSLAStatus($id);
        } catch (Exception $e) {
            error_log('Failed to get SLA status: ' . $e->getMessage());
            $slaStatus = null;
        }
        
        $viewData = [
            'ticket' => $ticket,
            'messages' => $messages,
            'statuses' => $lookupData['statuses'],
            'priorities' => $lookupData['priorities'],
            'categories' => $lookupData['categories'],
            'users' => $users,
            'attachments' => $attachments,
            'can_edit' => hasPermission('tickets.update') || $this->canEditTicket($ticket, $_SESSION['user_id']),
            'can_assign' => hasPermission('tickets.assign'),
            'can_close' => hasPermission('tickets.close') || $this->canCloseTicket($ticket, $_SESSION['user_id']),
            'original_email' => null, // This will be set if original email is loaded
            'sla_status' => $slaStatus,
            'client' => $client
        ];
        
        $this->view('tickets/view', $viewData);
    }

    /**
     * Helper method to download all attachments for a ticket
     * @param int $ticketId Ticket ID
     * @param bool $silent If true, don't show success messages (for auto-download)
     * @return bool True if attachments were downloaded, false otherwise
     */
    private function downloadAttachmentsForTicket($ticketId, $silent = false) {
        try {
            error_log('downloadAttachmentsForTicket: Starting for ticket ' . $ticketId);
            $db = new EasySQL(DB1);
            
            // First, get the ticket details
            $ticket = $this->ticketModel->getById($ticketId);
            if (!$ticket) {
                flash('error', 'Ticket not found.');
                return;
            }
            
            // Get all emails linked to this ticket
            $emails = $db->select(
                "SELECT id, message_id, subject FROM EmailInbox WHERE ticket_id = :ticket_id",
                ['ticket_id' => $ticketId]
            );
            
            error_log('downloadAttachmentsForTicket: Found ' . count($emails) . ' emails linked to ticket');
            
            // If no emails found, try to find and link emails by subject
            if (empty($emails) && !empty($ticket['subject'])) {
                error_log('downloadAttachmentsForTicket: No linked emails, searching by subject: ' . $ticket['subject']);
                
                // Search for emails with matching subject (remove Re:/Fw: prefixes)
                $cleanSubject = preg_replace('/^(re:|fw:|fwd:)\s*/i', '', trim($ticket['subject']));
                $emails = $db->select(
                    "SELECT id, message_id, subject, ticket_id FROM EmailInbox 
                     WHERE (subject LIKE :subject1 OR subject LIKE :subject2 OR subject LIKE :subject3 OR subject LIKE :subject4)
                     ORDER BY email_date DESC",
                    [
                        'subject1' => '%' . $cleanSubject . '%',
                        'subject2' => 'Re: ' . $cleanSubject,
                        'subject3' => 'RE: ' . $cleanSubject,
                        'subject4' => 'Fw: ' . $cleanSubject
                    ]
                );
                
                error_log('downloadAttachmentsForTicket: Found ' . count($emails) . ' emails by subject match');
                
                // Link unlinked emails to this ticket
                foreach ($emails as $email) {
                    if (empty($email['ticket_id'])) {
                        error_log('downloadAttachmentsForTicket: Linking email ' . $email['id'] . ' to ticket ' . $ticketId);
                        $db->update(
                            "UPDATE EmailInbox SET ticket_id = :ticket_id WHERE id = :id",
                            ['ticket_id' => $ticketId, 'id' => $email['id']]
                        );
                    }
                }
            }
            
            if (empty($emails)) {
                if (!$silent) {
                    flash('info', 'No emails found for this ticket. If you expect emails, try syncing from Graph first.');
                }
                return false;
            }
            
            $totalDownloaded = 0;
            $errors = [];
            
            foreach ($emails as $email) {
                try {
                    error_log('downloadAttachmentsForTicket: Processing email ' . $email['id']);
                    $downloaded = $this->redownloadAttachmentsForEmail($email['id']);
                    $totalDownloaded += $downloaded;
                    error_log('downloadAttachmentsForTicket: Downloaded ' . $downloaded . ' attachments from email ' . $email['id']);
                } catch (Exception $e) {
                    error_log('downloadAttachmentsForTicket: Error with email ' . $email['id'] . ': ' . $e->getMessage());
                    $errors[] = "Email '{$email['subject']}': " . $e->getMessage();
                }
            }
            
            if ($totalDownloaded > 0) {
                if (!$silent) {
                    flash('success', "Successfully downloaded $totalDownloaded attachments." . (count($errors) > 0 ? ' Some errors occurred.' : ''));
                }
                return true; // Return true if attachments were downloaded
            } elseif (count($errors) > 0) {
                flash('error', 'Failed to download attachments: ' . implode(', ', $errors));
                return false;
            } else {
                if (!$silent) {
                    flash('info', 'No attachments to download.');
                }
                return false; // Return false if no attachments were downloaded
            }
            
        } catch (Exception $e) {
            error_log('downloadAttachmentsForTicket: Exception: ' . $e->getMessage());
            if (!$silent) {
                flash('error', 'Download failed: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Download all attachments for a ticket
     */
    public function downloadAllAttachments($ticketId) {
        error_log('Tickets::downloadAllAttachments called for ticket ID: ' . $ticketId);
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            error_log('Tickets::downloadAllAttachments - Not POST method, redirecting');
            redirect('tickets/show/' . $ticketId);
        }

        // Check permissions
        if (!hasPermission('tickets.read')) {
            error_log('Tickets::downloadAllAttachments - Permission denied');
            flash('error', 'You do not have permission to download attachments.');
            redirect('tickets/show/' . $ticketId);
        }

        try {
            error_log('Tickets::downloadAllAttachments - Starting download process');
            
            // Use the silent download method but show messages for manual downloads
            $this->downloadAttachmentsForTicket($ticketId, false); // false = show messages for manual downloads

        } catch (Exception $e) {
            error_log('Tickets::downloadAllAttachments - Exception: ' . $e->getMessage());
            flash('error', 'Download failed: ' . $e->getMessage());
        }

        redirect('tickets/show/' . $ticketId);
    }

    /**
     * Helper method to redownload attachments for a specific email
     */
    private function redownloadAttachmentsForEmail($emailId) {
        try {
            $db = new EasySQL(DB1);
            $email = $db->select("SELECT * FROM EmailInbox WHERE id = :id", ['id' => $emailId]);
            
            if (empty($email)) {
                throw new Exception('Email not found');
            }
            $email = $email[0];

            // Get support email configuration
            require_once APPROOT . '/app/models/Setting.php';
            $settingModel = new Setting();
            $supportEmail = $settingModel->get('graph_support_email') ?: $settingModel->get('graph_connected_email');

            if (empty($supportEmail)) {
                throw new Exception('Support email not configured');
            }

            require_once APPROOT . '/app/services/MicrosoftGraphService.php';
            $graph = new MicrosoftGraphService();
            
            // Get attachments for this message and re-save them
            $attachments = $graph->getEmailAttachments($supportEmail, $email['message_id']);
            
            if (!class_exists('EmailAttachment')) {
                require_once APPROOT . '/app/models/EmailAttachment.php';
            }
            $attachmentModel = new EmailAttachment();

            $downloaded = 0;
            foreach ($attachments as $att) {
                // Create or update record
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
                if ($attachmentId) {
                    // Download content using reflection to access private method
                    $refClass = new ReflectionClass('MicrosoftGraphService');
                    $method = $refClass->getMethod('downloadAndSaveAttachment');
                    $method->setAccessible(true);
                    if ($method->invoke($graph, $supportEmail, $email['message_id'], $att, $attachmentId)) {
                        $downloaded++;
                    }
                }
            }

            return $downloaded;
        } catch (Exception $e) {
            error_log('redownloadAttachmentsForEmail error: ' . $e->getMessage());
            throw $e;
        }
    }

    // Note: Do not define a view() method here to avoid clashing with base Controller::view()
    
    /**
     * Create new ticket form
     */
    public function create() {
        if (!hasPermission('tickets.create')) {
            flash('error', 'You do not have permission to create tickets.');
            redirect('tickets');
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Debug logging
            error_log('Ticket creation POST received. Data: ' . print_r($_POST, true));
            
            // Validate input
            $data = [
                'subject' => trim($_POST['subject']),
                'description' => trim($_POST['description']),
                'priority_id' => $_POST['priority_id'],
                'category_id' => $_POST['category_id'] ?: null,
                'assigned_to' => $_POST['assigned_to'] ?: null,
                'due_date' => $_POST['due_date'] ?: null,
                'tags' => trim($_POST['tags']),
                'project_id' => $_POST['project_id'] ?: null,
                'client_id' => $_POST['client_id'] ?: null,
                'created_by' => $_SESSION['user_id'],
                'source' => 'web'
            ];
            
            // Validation
            $errors = [];
            if (empty($data['subject'])) {
                $errors[] = 'Subject is required.';
            }
            if (empty($data['description'])) {
                $errors[] = 'Description is required.';
            }
            if (empty($data['priority_id'])) {
                $errors[] = 'Priority is required.';
            }
            
            if (empty($errors)) {
                error_log('Creating ticket with data: ' . print_r($data, true));
                $ticketId = $this->ticketModel->create($data);
                error_log('Ticket creation result: ' . ($ticketId ? "Success (ID: $ticketId)" : "Failed"));
                
                if ($ticketId) {
                    flash('success', 'Ticket created successfully.');
                    
                    // Send notification email if assigned
                    if ($data['assigned_to']) {
                        $this->sendTicketNotification($ticketId, 'ticket_created');
                    }
                    
                    redirect('tickets/show/' . $ticketId);
                } else {
                    flash('error', 'Failed to create ticket.');
                }
            } else {
                error_log('Validation errors: ' . print_r($errors, true));
                foreach ($errors as $error) {
                    flash('error', $error);
                }
            }
        }
        
        $lookupData = $this->ticketModel->getLookupData();
        $userModel = $this->model('User');
        $users = $userModel->getAllUsers();
        $clientModel = $this->model('Client');
        $clients = $clientModel->getAllClients();
        
        // Get projects for linking
        $projectModel = $this->model('Project');
        $projects = $projectModel->getActiveProjects();
        
        $viewData = [
            'statuses' => $lookupData['statuses'],
            'priorities' => $lookupData['priorities'],
            'categories' => $lookupData['categories'],
            'users' => $users,
            'clients' => $clients,
            'projects' => $projects,
            'formData' => $_POST ?? []
        ];
        
        $this->view('tickets/create', $viewData);
    }
    
    /**
     * Edit ticket
     */
    public function edit($id) {
        $ticket = $this->ticketModel->getById($id);
        
        if (!$ticket) {
            flash('error', 'Ticket not found.');
            redirect('tickets');
        }
        
        if (!hasPermission('tickets.update') && !$this->canEditTicket($ticket, $_SESSION['user_id'])) {
            flash('error', 'You do not have permission to edit this ticket.');
            redirect('tickets/show/' . $id);
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'subject' => trim($_POST['subject']),
                'description' => trim($_POST['description']),
                'status_id' => $_POST['status_id'],
                'priority_id' => $_POST['priority_id'],
                'category_id' => $_POST['category_id'] ?: null,
                'assigned_to' => $_POST['assigned_to'] ?: null,
                'due_date' => $_POST['due_date'] ?: null,
                'tags' => trim($_POST['tags'])
            ];
            
            // Check if status changed to resolved/closed
            if ($data['status_id'] != $ticket['status_id']) {
                $lookupData = $this->ticketModel->getLookupData();
                $newStatus = array_filter($lookupData['statuses'], function($s) use ($data) {
                    return $s['id'] == $data['status_id'];
                });
                $newStatus = reset($newStatus);
                
                if ($newStatus && $newStatus['name'] === 'resolved') {
                    $data['resolved_at'] = date('Y-m-d H:i:s');
                } elseif ($newStatus && $newStatus['is_closed']) {
                    $data['closed_at'] = date('Y-m-d H:i:s');
                }
            }
            
            $success = $this->ticketModel->update($id, $data);
            
            if ($success) {
                flash('success', 'Ticket updated successfully.');
                
                // Send notification if assigned to different user
                if ($data['assigned_to'] != $ticket['assigned_to']) {
                    $this->sendTicketNotification($id, 'ticket_assigned');
                }
                
                redirect('tickets/show/' . $id);
            } else {
                flash('error', 'Failed to update ticket.');
            }
        }
        
        $lookupData = $this->ticketModel->getLookupData();
        $userModel = $this->model('User');
        $users = $userModel->getAllUsers();
        
        $viewData = [
            'ticket' => $ticket,
            'statuses' => $lookupData['statuses'],
            'priorities' => $lookupData['priorities'],
            'categories' => $lookupData['categories'],
            'users' => $users
        ];
        
        $this->view('tickets/edit', $viewData);
    }
    
    /**
     * Add message/comment to ticket
     */
    public function addMessage($id) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('tickets/show/' . $id);
        }
        
        // Debug logging for reply submissions
        error_log('Tickets@addMessage called for Ticket ID ' . $id . ' | Method: ' . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
        error_log('POST keys: ' . implode(',', array_keys($_POST ?? [])));
        error_log('Message length: ' . strlen($_POST['message'] ?? 'NULL'));

        $ticket = $this->ticketModel->getById($id);
        
        if (!$ticket) {
            flash('error', 'Ticket not found.');
            redirect('tickets');
        }
        
        if (!hasPermission('tickets.comment') && !$this->canAccessTicket($ticket, $_SESSION['user_id'])) {
            flash('error', 'You do not have permission to comment on this ticket.');
            redirect('tickets/show/' . $id);
        }
        
        $messageData = [
            'user_id' => $_SESSION['user_id'],
            'content' => trim($_POST['message']),
            'message_type' => $_POST['message_type'] ?? 'comment',
            'is_public' => isset($_POST['is_public']) ? 1 : 0
        ];
        
        if (empty($messageData['content'])) {
            flash('error', 'Message content is required.');
            redirect('tickets/show/' . $id);
        }
        
        $messageId = $this->ticketModel->addMessage($id, $messageData);
        error_log('Tickets@addMessage result ID: ' . var_export($messageId, true));
        
        if ($messageId) {
            flash('success', 'Message added successfully.');
            
            try {
                $activityLogModel = $this->model('ActivityLog');
                $activityLogModel->log(
                    $_SESSION['user_id'],
                    'ticket',
                    $id,
                    'commented',
                    sprintf(
                        'Added a %s reply to ticket #%d',
                        !empty($messageData['is_public']) ? 'public' : 'private',
                        $id
                    ),
                    [
                        'message_id' => $messageId,
                        'message_type' => $messageData['message_type'],
                        'is_public' => (bool)$messageData['is_public'],
                        'snippet' => mb_substr($messageData['content'], 0, 120)
                    ]
                );
            } catch (Exception $e) {
                error_log('Failed to log ticket reply activity: ' . $e->getMessage());
            }
            
            // Update first_response_at if this is the first response
            if (empty($ticket['first_response_at'])) {
                try {
                    $this->ticketModel->updateFirstResponse($id);
                } catch (Exception $e) {
                    error_log('Failed to update first response time: ' . $e->getMessage());
                }
            }
            
            // Send notification email
            $this->sendTicketNotification($id, 'ticket_updated', $messageData['content']);
            
            // Process email queue immediately to send the notification right away
            try {
                require_once APPROOT . '/app/services/EmailService.php';
                $emailService = new EmailService();
                $emailService->processEmailQueue();
            } catch (Exception $e) {
                error_log('Failed to process email queue immediately: ' . $e->getMessage());
            }
        } else {
            flash('error', 'Failed to add message. Please try again.');
        }
        
        redirect('tickets/show/' . $id);
    }
    
    /**
     * Assign ticket to user(s)
     */
    public function assign($id) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('tickets/show/' . $id);
        }
        
        if (!hasPermission('tickets.assign')) {
            flash('error', 'You do not have permission to assign tickets.');
            redirect('tickets/show/' . $id);
        }
        
        $ticket = $this->ticketModel->getById($id);
        if (!$ticket) {
            flash('error', 'Ticket not found.');
            redirect('tickets');
        }
        
        $assigneeIds = $_POST['assignee_ids'] ?? [];
        if (!is_array($assigneeIds)) {
            $assigneeIds = [$assigneeIds];
        }
        
        $success = $this->ticketModel->assignTo($id, $assigneeIds, $_SESSION['user_id']);
        
        if ($success) {
            flash('success', 'Ticket assigned successfully.');
            
            // Send notification emails
            foreach ($assigneeIds as $userId) {
                $this->sendTicketNotification($id, 'ticket_assigned', null, $userId);
            }
        } else {
            flash('error', 'Failed to assign ticket.');
        }
        
        redirect('tickets/show/' . $id);
    }
    
    /**
     * Quick status update via AJAX
     */
    public function updateStatus() {
        // Set JSON content type header
        header('Content-Type: application/json');
        
        // Debug logging
        error_log('updateStatus called - Method: ' . $_SERVER['REQUEST_METHOD']);
        error_log('POST data: ' . print_r($_POST, true));
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $ticketId = $_POST['ticket_id'] ?? null;
        $statusId = $_POST['status_id'] ?? null;
        
        error_log('Ticket ID: ' . $ticketId . ', Status ID: ' . $statusId);
        
        if (!$ticketId || !$statusId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required parameters', 'debug' => ['ticket_id' => $ticketId, 'status_id' => $statusId]]);
            return;
        }
        
        $ticket = $this->ticketModel->getById($ticketId);
        if (!$ticket) {
            http_response_code(404);
            echo json_encode(['error' => 'Ticket not found']);
            return;
        }
        
        error_log('Permission check - has tickets.update: ' . (hasPermission('tickets.update') ? 'yes' : 'no'));
        error_log('canEditTicket: ' . ($this->canEditTicket($ticket, $_SESSION['user_id']) ? 'yes' : 'no'));
        
        if (!hasPermission('tickets.update') && !$this->canEditTicket($ticket, $_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Permission denied']);
            return;
        }
        
        $updateData = ['status_id' => $statusId];
        
        // Check if status is resolved/closed
        $lookupData = $this->ticketModel->getLookupData();
        $newStatus = array_filter($lookupData['statuses'], function($s) use ($statusId) {
            return $s['id'] == $statusId;
        });
        $newStatus = reset($newStatus);
        
        if ($newStatus && $newStatus['name'] === 'resolved') {
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
        } elseif ($newStatus && $newStatus['is_closed']) {
            $updateData['closed_at'] = date('Y-m-d H:i:s');
        }
        
        $success = $this->ticketModel->update($ticketId, $updateData);
        
        if ($success) {
            // Add system message
            $this->ticketModel->addMessage($ticketId, [
                'user_id' => $_SESSION['user_id'],
                'content' => 'Status changed to: ' . $newStatus['display_name'],
                'message_type' => 'status_change',
                'is_system_message' => 1,
                'is_public' => 1
            ]);
            
            echo json_encode(['success' => true, 'status' => $newStatus]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update status']);
        }
    }
    
    /**
     * Reload ticket data from external email systems (Graph/IMAP) and refresh the page
     */
    public function refresh($id) {
        // Allow anyone who can read the ticket to request a refresh
        if (!hasPermission('tickets.read')) {
            flash('error', 'You do not have permission to refresh this ticket.');
            redirect('tickets');
        }
        
        // Check if this is a download attachments request
        if (isset($_GET['download']) && $_GET['download'] == '1') {
            error_log('Tickets::refresh - Download attachments requested for ticket ' . $id);
            $this->downloadAttachmentsForTicket($id);
            redirect('tickets/show/' . $id);
        }

        $processed = 0;
        try {
            $force = isset($_GET['force']) && ($_GET['force'] === '1' || strtolower($_GET['force']) === 'true');

            if ($force) {
                // Force reprocess: for all EmailInbox rows linked to this ticket, rebuild attachments and rewrite HTML
                $db = new EasySQL(DB1);
                $emails = $db->select("SELECT id, message_id, body_html FROM EmailInbox WHERE ticket_id = :tid ORDER BY id ASC", ['tid' => $id]);
                if (!empty($emails)) {
                    require_once APPROOT . '/app/models/Setting.php';
                    $settingModel = new Setting();
                    $supportEmail = $settingModel->get('graph_support_email') ?: $settingModel->get('graph_connected_email');
                    if (!empty($supportEmail)) {
                        require_once APPROOT . '/app/services/MicrosoftGraphService.php';
                        require_once APPROOT . '/app/models/EmailAttachment.php';
                        $graph = new MicrosoftGraphService();
                        $attachmentModel = new EmailAttachment();
                        $repaired = 0;
                        foreach ($emails as $em) {
                            if (empty($em['message_id'])) { continue; }
                            // Get attachments from Graph
                            $atts = $graph->getEmailAttachments($supportEmail, $em['message_id']);
                            $cidMap = [];
                            foreach ($atts as $att) {
                                // Create attachment record
                                $data = [
                                    'email_inbox_id' => $em['id'],
                                    'ms_attachment_id' => $att['id'] ?? null,
                                    'content_id' => $att['contentId'] ?? null,
                                    'filename' => $att['name'] ?? ('file_' . uniqid()),
                                    'original_filename' => $att['name'] ?? ('file_' . uniqid()),
                                    'file_size' => $att['size'] ?? 0,
                                    'mime_type' => $att['contentType'] ?? 'application/octet-stream',
                                    'is_inline' => !empty($att['isInline']) ? 1 : 0,
                                    'is_downloaded' => 0
                                ];
                                $attId = $attachmentModel->create($data);
                                if ($attId) {
                                    // Call private downloadAndSaveAttachment via reflection to save file and mark downloaded
                                    $ref = new ReflectionClass('MicrosoftGraphService');
                                    $method = $ref->getMethod('downloadAndSaveAttachment');
                                    $method->setAccessible(true);
                                    $ok = $method->invoke($graph, $supportEmail, $em['message_id'], $att, $attId);
                                    if ($ok) {
                                        $saved = $attachmentModel->getById($attId);
                                        if (!empty($saved['is_inline']) && !empty($saved['content_id']) && !empty($saved['file_path'])) {
                                            $cid = trim($saved['content_id'], '<>');
                                            $cidMap[$cid] = URLROOT . '/' . ltrim($saved['file_path'], '/');
                                        }
                                        $processed++;
                                    }
                                }
                            }
                            // Rewrite HTML body for this email using CID map
                            if (!empty($cidMap) && !empty($em['body_html'])) {
                                $html = preg_replace_callback('/src\s*=\s*(\"|\')cid:([^\"\']+)(\1)/i', function($m) use ($cidMap) {
                                    $q = $m[1];
                                    $cid = trim($m[2], '<>');
                                    if (isset($cidMap[$cid])) {
                                        return 'src=' . $q . htmlspecialchars($cidMap[$cid], ENT_QUOTES, 'UTF-8') . $q;
                                    }
                                    return $m[0];
                                }, $em['body_html']);
                                $db->update("UPDATE EmailInbox SET body_html = :html WHERE id = :id", ['html' => $html, 'id' => $em['id']]);
                                $repaired++;
                            }
                        }
                        flash('success', 'Forced reload completed. Updated ' . $processed . ' attachment(s) and rewrote HTML for ' . $repaired . ' email(s).');
                        redirect('tickets/show/' . $id);
                        return;
                    } else {
                        flash('error', 'Forced reload failed: Graph mailbox is not configured in Settings.');
                        redirect('tickets/show/' . $id);
                        return;
                    }
                } else {
                    flash('success', 'Forced reload: no linked emails for this ticket.');
                    redirect('tickets/show/' . $id);
                    return;
                }
            }

            // Prefer Microsoft Graph if configured
            try {
                require_once APPROOT . '/app/models/Setting.php';
                $settingModel = new Setting();
                $supportEmail = $settingModel->get('graph_support_email');
                if (empty($supportEmail)) {
                    $supportEmail = $settingModel->get('graph_connected_email');
                }
                if (!empty($supportEmail)) {
                    require_once APPROOT . '/app/services/MicrosoftGraphService.php';
                    $graph = new MicrosoftGraphService();
                    // true = only unread; process a small batch quickly
                    $processedGraph = $graph->processEmailsToTickets($supportEmail, true, 20);
                    if ($processedGraph !== false) {
                        $processed += (int)$processedGraph;
                    }
                }
            } catch (Exception $e) {
                // Ignore and fallback to traditional email processing
            }

            // Fallback to IMAP/POP3 receive if nothing processed (or Graph not set)
            if ($processed === 0) {
                try {
                    require_once APPROOT . '/app/services/EmailService.php';
                    $svc = new EmailService();
                    $processed += (int)$svc->receiveEmails(20);
                } catch (Exception $e) {
                    // Ignore errors; report below if nothing processed at all
                }
            }

            if ($processed > 0) {
                flash('success', 'Reloaded from system: ' . $processed . ' new email(s) processed.');
            } else {
                flash('success', 'Reload attempted. No new emails found.');
            }
        } catch (Exception $e) {
            flash('error', 'Failed to reload: ' . $e->getMessage());
        }

        redirect('tickets/show/' . $id);
    }
    
    /**
     * Test method to verify controller is reachable
     */
    public function test() {
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Tickets controller is working', 'timestamp' => date('Y-m-d H:i:s')]);
    }
    
    /**
     * Delete a ticket (POST only)
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('tickets');
        }
        
        if (!hasPermission('tickets.delete')) {
            flash('error', 'You do not have permission to delete tickets.');
            redirect('tickets');
        }
        
        $ticketId = $_POST['ticket_id'] ?? null;
        if (empty($ticketId)) {
            flash('error', 'Invalid request.');
            redirect('tickets');
        }
        
        // Optional: CSRF check if token provided in form
        if (isset($_POST['csrf_token'])) {
            $sessionToken = $_SESSION['csrf_token'] ?? '';
            if (empty($sessionToken) || !hash_equals($sessionToken, $_POST['csrf_token'])) {
                flash('error', 'Security token mismatch. Please try again.');
                redirect('tickets/show/' . $ticketId);
            }
        }
        
        $ticket = $this->ticketModel->getById($ticketId);
        if (!$ticket) {
            flash('error', 'Ticket not found.');
            redirect('tickets');
        }
        
        $ok = $this->ticketModel->delete($ticketId);
        if ($ok) {
            flash('success', 'Ticket deleted successfully.');
            redirect('tickets');
        } else {
            flash('error', 'Failed to delete ticket.');
            redirect('tickets/show/' . $ticketId);
        }
    }
    
    /**
     * Dashboard/statistics page
     */
    public function dashboard() {
        if (!hasPermission('tickets.read')) {
            flash('error', 'You do not have permission to view ticket dashboard.');
            redirect('dashboard');
        }
        
        $statistics = $this->ticketModel->getStatistics();
        
        // Get recent tickets
        $filters = hasPermission('tickets.view_all') ? [] : ['assigned_to' => $_SESSION['user_id']];
        $recentTickets = $this->ticketModel->getAll($filters, 1, 10);
        
        $viewData = [
            'statistics' => $statistics,
            'recent_tickets' => $recentTickets['tickets']
        ];
        
        $this->view('tickets/dashboard', $viewData);
    }
    
    /**
     * Search tickets (AJAX)
     */
    public function search() {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $query = $_GET['q'] ?? '';
        if (strlen($query) < 2) {
            echo json_encode(['tickets' => []]);
            return;
        }
        
        $filters = ['search' => $query];
        if (!hasPermission('tickets.view_all')) {
            $filters['assigned_to'] = $_SESSION['user_id'];
        }
        
        $data = $this->ticketModel->getAll($filters, 1, 20);
        
        echo json_encode(['tickets' => $data['tickets']]);
    }
    
    /**
     * Helper Methods
     */
    
    private function canAccessTicket($ticket, $userId) {
        return $ticket['created_by'] == $userId || 
               $ticket['assigned_to'] == $userId ||
               hasPermission('tickets.view_all');
    }
    
    private function canEditTicket($ticket, $userId) {
        return $ticket['created_by'] == $userId || 
               $ticket['assigned_to'] == $userId ||
               hasPermission('tickets.update');
    }
    
    private function canCloseTicket($ticket, $userId) {
        return $ticket['assigned_to'] == $userId ||
               hasPermission('tickets.close');
    }
    
    private function sendTicketNotification($ticketId, $template, $message = null, $recipientUserId = null) {
        try {
            $ticket = $this->ticketModel->getById($ticketId);
            if (!$ticket) {
                error_log("sendTicketNotification: Ticket $ticketId not found");
                return false;
            }
            
            // Determine recipient with improved logic
            $recipientEmail = null;
            
            if ($recipientUserId) {
                $userModel = $this->model('User');
                $user = $userModel->getUserById($recipientUserId);
                $recipientEmail = $user['email'] ?? null;
            } else {
                // Priority order: inbound_email_address (original requester), assigned_to_email, created_by_email
                $recipientEmail = $ticket['inbound_email_address'] ?? 
                                 $ticket['assigned_to_email'] ?? 
                                 $ticket['created_by_email'] ?? null;
            }
            
            error_log("sendTicketNotification: Determined recipient email: " . ($recipientEmail ?: 'NONE'));
            
            if (!$recipientEmail || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                error_log("sendTicketNotification: No valid recipient email found for ticket $ticketId");
                return false;
            }
            
            $recipient = ['email' => $recipientEmail];
            
            $emailData = $this->emailService->createTicketEmail($template, [
                'ticket_id' => $ticket['id'],
                'ticket_number' => $ticket['ticket_number'],
                'subject' => $ticket['subject'],
                'priority' => $ticket['priority_display'],
                'category' => $ticket['category_name'],
                'created_by_name' => $ticket['created_by_name'],
                'assigned_to_name' => $ticket['assigned_to_name'],
                'description' => $ticket['description'] ?? '',
                'update_message' => $message ?? '',
                'assignee_email' => $ticket['assigned_to_email'] ?? '',
                'created_by_email' => $ticket['created_by_email'] ?? '',
                'inbound_email_address' => $recipient['email'],
                'due_date' => $ticket['due_date'] ?? 'Not set',
                'resolution' => $message ?? ''
            ]);
            
            // Queue for sending
            $result = $this->emailService->queueEmail($emailData, 5);
            error_log("sendTicketNotification: Email queue result: " . ($result ? 'SUCCESS' : 'FAILED'));
            return $result;
        } catch (Exception $e) {
            error_log('Ticket Notification Error: ' . $e->getMessage());
            error_log('Ticket Notification Stack Trace: ' . $e->getTraceAsString());
            return false;
        }
    }
}