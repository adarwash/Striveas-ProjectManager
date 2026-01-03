<?php

/**
 * Ticket Controller for Ticketing System
 * Handles all ticket-related web operations
 */
class Tickets extends Controller {
    private $ticketModel;
    private $emailService;
    private $slaService;

    /**
     * Basic sanitization for staff-entered rich text.
     * We keep a small safe subset and strip obvious XSS vectors.
     */
    private function sanitizeReplyHtml(string $html): string {
        $html = (string)$html;
        if ($html === '') return '';

        // Remove scripts/iframes/objects
        $html = preg_replace('#<\s*(script|iframe|object|embed)[^>]*>.*?<\s*/\s*\\1\s*>#is', '', $html);
        // Remove event handler attrs (onclick=, onload=, etc.)
        $html = preg_replace('/\\son\\w+\\s*=\\s*(\"[^\"]*\"|\\\'[^\\\']*\\\'|[^\\s>]+)/i', '', $html);
        // Remove javascript: urls
        $html = preg_replace('/(href|src)\\s*=\\s*(\"|\\\')\\s*javascript:[^\"\\\']*(\"|\\\')/i', '$1=\"#\"', $html);

        // Allow a safe subset of tags (include img for inline images)
        $allowed = '<p><br><b><strong><i><em><u><s><strike><a><ul><ol><li><blockquote><code><pre><h1><h2><h3><h4><h5><h6><span><div><img>';
        $html = strip_tags($html, $allowed);

        return $html;
    }
    
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
     * Upload .eml/.msg and create or append to tickets.
     */
    public function importEmail() {
        if (!hasPermission('tickets.create')) {
            flash('error', 'You do not have permission to import emails.');
            redirect('tickets');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tickets');
        }

        require_once APPROOT . '/app/services/EmailUploadService.php';
        $service = new EmailUploadService();

        $settingModel = $this->model('Setting');
        $sendAck = !empty($_POST['send_ack']);
        $ticketIdHint = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : null;

        $maxBytes = (int)$settingModel->get('max_attachment_size', 10485760);
        $files = $this->normalizeUploads($_FILES['email_files'] ?? []);

        $results = [];
        foreach ($files as $file) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $results[] = ['status' => 'error', 'message' => 'Upload failed for ' . ($file['name'] ?? 'file')];
                continue;
            }
            if (!empty($file['size']) && $file['size'] > $maxBytes) {
                $results[] = ['status' => 'error', 'message' => ($file['name'] ?? 'file') . ' exceeds size limit'];
                continue;
            }
            $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
            if (!in_array($ext, ['eml', 'msg'], true)) {
                $results[] = ['status' => 'error', 'message' => ($file['name'] ?? 'file') . ' must be .eml or .msg'];
                continue;
            }

            $results[] = $service->processUploadedFile($file, [
                'ticket_id' => $ticketIdHint,
                'send_ack' => $sendAck
            ]);
        }

        $created = array_filter($results, fn($r) => ($r['status'] ?? '') === 'created');
        $appended = array_filter($results, fn($r) => ($r['status'] ?? '') === 'appended');
        $errors = array_filter($results, fn($r) => ($r['status'] ?? '') === 'error');
        $skipped = array_filter($results, fn($r) => ($r['status'] ?? '') === 'skipped');

        $summary = [];
        if (!empty($created)) { $summary[] = count($created) . ' created'; }
        if (!empty($appended)) { $summary[] = count($appended) . ' appended'; }
        if (!empty($skipped)) { $summary[] = count($skipped) . ' skipped'; }
        if (!empty($errors)) { $summary[] = count($errors) . ' error(s)'; }

        if (!empty($errors)) {
            $messages = array_map(fn($r) => $r['message'] ?? 'Import error', $errors);
            flash('error', 'Import finished: ' . implode(', ', $summary) . '. Details: ' . implode('; ', $messages));
        } else {
            flash('success', 'Import finished: ' . implode(', ', $summary));
        }

        // Redirect to ticket view if a single ticket was created/appended and ID known
        $firstSuccess = $created[0] ?? ($appended[0] ?? null);
        if ($firstSuccess && !empty($firstSuccess['ticket_id'])) {
            $tid = (int)$firstSuccess['ticket_id'];
            // If client isn't linked, prompt staff to link/create (popup)
            try {
                $t = $this->ticketModel->getById($tid);
                if ($t && empty($t['client_id']) && !empty($t['inbound_email_address'])) {
                    redirect('tickets/show/' . $tid . '?link_client=1');
                }
            } catch (Exception $e) {
                // ignore
            }
            redirect('tickets/show/' . $tid);
        }

        // Otherwise return to list or specific ticket if hint provided
        if (!empty($ticketIdHint)) {
            redirect('tickets/show/' . $ticketIdHint);
        }
        redirect('tickets');
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
            // Also show unassigned tickets so new inbound items are visible to agents
            $filters['include_unassigned'] = true;
        }
        
        // Remove empty filters
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });
        
        $data = $this->ticketModel->getAll($filters, $page, $limit);
        $lookupData = $this->ticketModel->getLookupData();
        $statistics = $this->ticketModel->getStatistics();

        // Timezone context (DB timestamps vs display)
        $displayTz = date_default_timezone_get();
        $dbTz = $displayTz;
        try {
            $settingModel = $this->model('Setting');
            $displayTz = $settingModel->get('display_timezone', $displayTz) ?: $displayTz;
            $dbTz = $settingModel->get('db_timezone', $dbTz) ?: $dbTz;
        } catch (Exception $e) {
            // ignore
        }
        
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
            'statistics' => $statistics,
            'display_timezone' => $displayTz,
            'db_timezone' => $dbTz
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

        // Timezone context (DB timestamps vs display)
        $displayTz = date_default_timezone_get();
        $dbTz = $displayTz;
        try {
            $settingModel = $this->model('Setting');
            $displayTz = $settingModel->get('display_timezone', $displayTz) ?: $displayTz;
            $dbTz = $settingModel->get('db_timezone', $dbTz) ?: $dbTz;
        } catch (Exception $e) {
            // ignore
        }

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

        // Suggested client match (by requester email) when ticket not linked
        $suggestedClient = null;
        if (empty($ticket['client_id']) && !empty($ticket['inbound_email_address']) && hasPermission('clients.read')) {
            try {
                $clientModel = $clientModel ?? $this->model('Client');
                if (method_exists($clientModel, 'getClientByEmail')) {
                    $suggestedClient = $clientModel->getClientByEmail((string)$ticket['inbound_email_address']);
                }
                // Fallback: domain mapping (ClientDomains)
                if (!$suggestedClient && !class_exists('ClientDomain')) {
                    require_once APPROOT . '/app/models/ClientDomain.php';
                }
                if (!$suggestedClient && class_exists('ClientDomain')) {
                    $cd = new ClientDomain();
                    $mappedId = $cd->getClientIdByEmail((string)$ticket['inbound_email_address']);
                    if (!empty($mappedId)) {
                        $suggestedClient = $clientModel->getClientById((int)$mappedId);
                    }
                }
            } catch (Exception $e) {
                $suggestedClient = null;
            }
        }

        // Client list for link/create modal (only when needed)
        $allClients = [];
        if (empty($ticket['client_id']) && hasPermission('clients.read')) {
            try {
                $clientModel = $clientModel ?? $this->model('Client');
                $allClients = $clientModel->getAllClients();
                // Apply visibility restrictions if applicable
                if (method_exists($clientModel, 'filterClientsForRole')) {
                    $roleId = isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : null;
                    $isAdmin = hasPermission('admin.access');
                    $allClients = $clientModel->filterClientsForRole($allClients, $roleId, $isAdmin);
                }
            } catch (Exception $e) {
                $allClients = [];
            }
        }
        $lookupData = $this->ticketModel->getLookupData();

        // Outbound email delivery / queue (troubleshooting)
        $emailQueue = [];
        try {
            if (method_exists($this->ticketModel, 'getEmailQueueForTicket')) {
                $emailQueue = $this->ticketModel->getEmailQueueForTicket((int)$id, 10);
            }
        } catch (Exception $e) {
            $emailQueue = [];
        }

        // Load ticket attachments (stored directly on the ticket, not via EmailInbox)
        $attachments = [];
        $pendingAttachmentCount = 0;
        try {
            if (!class_exists('TicketAttachment')) {
                require_once APPROOT . '/app/models/TicketAttachment.php';
            }
            $attachmentModel = new TicketAttachment();
            $attachments = $attachmentModel->getByTicketId((int)$id);
            if (method_exists($attachmentModel, 'countPendingByTicketId')) {
                $pendingAttachmentCount = (int)$attachmentModel->countPendingByTicketId((int)$id);
            }
        } catch (Exception $e) {
            $attachments = [];
            $pendingAttachmentCount = 0;
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
            'pending_attachments' => $pendingAttachmentCount,
            'email_queue' => $emailQueue,
            'all_clients' => $allClients,
            'suggested_client' => $suggestedClient,
            'can_edit' => hasPermission('tickets.update') || $this->canEditTicket($ticket, $_SESSION['user_id']),
            'can_assign' => hasPermission('tickets.assign'),
            'can_close' => hasPermission('tickets.close') || $this->canCloseTicket($ticket, $_SESSION['user_id']),
            'original_email' => null, // This will be set if original email is loaded
            'sla_status' => $slaStatus,
            'client' => $client,
            'display_timezone' => $displayTz,
            'db_timezone' => $dbTz
        ];
        
        $this->view('tickets/view', $viewData);
    }

    /**
     * Return conversation + attachments fragments (HTML) or status for AJAX refresh.
     */
    public function fragment($id) {
        header('Content-Type: application/json');

        $ticket = $this->ticketModel->getById($id);
        
        if (!$ticket) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Ticket not found']);
            return;
        }
        
        if (!hasPermission('tickets.read') && 
            !$this->canAccessTicket($ticket, $_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Forbidden']);
            return;
        }

        // Timezone context
        $displayTz = date_default_timezone_get();
        $dbTz = $displayTz;
        try {
            $settingModel = $this->model('Setting');
            $displayTz = $settingModel->get('display_timezone', $displayTz) ?: $displayTz;
            $dbTz = $settingModel->get('db_timezone', $dbTz) ?: $dbTz;
        } catch (Exception $e) {
            // ignore
        }

        $messages = $this->ticketModel->getMessages($id, hasPermission('tickets.view_private'));

        // Attachments + pending count
        $attachments = [];
        $pendingAttachmentCount = 0;
        try {
            if (!class_exists('TicketAttachment')) {
                require_once APPROOT . '/app/models/TicketAttachment.php';
            }
            $attachmentModel = new TicketAttachment();
            $attachments = $attachmentModel->getByTicketId((int)$id);
            if (method_exists($attachmentModel, 'countPendingByTicketId')) {
                $pendingAttachmentCount = (int)$attachmentModel->countPendingByTicketId((int)$id);
            }
        } catch (Exception $e) {
            $attachments = [];
            $pendingAttachmentCount = 0;
        }

        // Status-only mode
        if (isset($_GET['mode']) && $_GET['mode'] === 'status') {
            echo json_encode([
                'ok' => true,
                'pending' => $pendingAttachmentCount
            ]);
            return;
        }

        // Lookup data (for badges, names)
        $lookupData = $this->ticketModel->getLookupData();

        // Outbound email delivery / queue
        $emailQueue = [];
        try {
            if (method_exists($this->ticketModel, 'getEmailQueueForTicket')) {
                $emailQueue = $this->ticketModel->getEmailQueueForTicket((int)$id, 10);
            }
        } catch (Exception $e) {
            $emailQueue = [];
        }

        // Suggested client match (by requester email) when ticket not linked
        $suggestedClient = null;
        if (empty($ticket['client_id']) && !empty($ticket['inbound_email_address']) && hasPermission('clients.read')) {
            try {
                $clientModel = $this->model('Client');
                if (method_exists($clientModel, 'getClientByEmail')) {
                    $suggestedClient = $clientModel->getClientByEmail((string)$ticket['inbound_email_address']);
                }
                // Fallback: domain mapping (ClientDomains)
                if (!$suggestedClient && !class_exists('ClientDomain')) {
                    require_once APPROOT . '/app/models/ClientDomain.php';
                }
                if (!$suggestedClient && class_exists('ClientDomain')) {
                    $cd = new ClientDomain();
                    $mappedId = $cd->getClientIdByEmail((string)$ticket['inbound_email_address']);
                    if (!empty($mappedId)) {
                        $suggestedClient = $clientModel->getClientById((int)$mappedId);
                    }
                }
            } catch (Exception $e) {
                $suggestedClient = null;
            }
        }

        $viewData = [
            'ticket' => $ticket,
            'messages' => $messages,
            'attachments' => $attachments,
            'pending_attachments' => $pendingAttachmentCount,
            'email_queue' => $emailQueue,
            'suggested_client' => $suggestedClient,
            'statuses' => $lookupData['statuses'],
            'priorities' => $lookupData['priorities'],
            'categories' => $lookupData['categories'],
            'display_timezone' => $displayTz,
            'db_timezone' => $dbTz
        ];

        // Render partials
        $conversationHtml = $this->renderPartial(APPROOT . '/app/views/tickets/partials/conversation_fragment.php', $viewData);
        $sidebarHtml = $this->renderPartial(APPROOT . '/app/views/tickets/partials/sidebar_fragment.php', $viewData);

        echo json_encode([
            'ok' => true,
            'conversation' => $conversationHtml,
            'sidebar' => $sidebarHtml,
            'pending' => $pendingAttachmentCount
        ]);
    }

    /**
     * Helper to render a partial and return its HTML.
     */
    private function renderPartial(string $path, array $data): string {
        extract(['data' => $data]);
        ob_start();
        include $path;
        return ob_get_clean();
    }

    /**
     * Kick off async attachment downloads for a ticket (non-blocking).
     * Called by the UI (manual button / optional on-load).
     */
    public function kickoffAttachments($id) {
        header('Content-Type: application/json');

        $ticket = $this->ticketModel->getById($id);
        if (!$ticket) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Ticket not found']);
            return;
        }

        if (!hasPermission('tickets.read') && !$this->canAccessTicket($ticket, $_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Forbidden']);
            return;
        }

        try {
            if (!class_exists('TicketAttachment')) {
                require_once APPROOT . '/app/models/TicketAttachment.php';
            }
            $attachmentModel = new TicketAttachment();
            $pending = method_exists($attachmentModel, 'countPendingByTicketId')
                ? (int)$attachmentModel->countPendingByTicketId((int)$id)
                : 0;

            if ($pending <= 0) {
                echo json_encode(['ok' => true, 'started' => false, 'pending' => 0]);
                return;
            }

            // Spawn a background worker to download just this ticket's pending attachments.
            $script = APPROOT . '/app/scripts/process_ticket_attachments.php';
            $limit = 200;
            $cmd = 'php ' . escapeshellarg($script) . ' ' . (int)$limit . ' ' . (int)$id . ' > /dev/null 2>&1 &';
            @exec($cmd);

            echo json_encode(['ok' => true, 'started' => true, 'pending' => $pending]);
            return;
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
            return;
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
                'inbound_email_address' => trim($_POST['contact_email'] ?? ''),
                'created_by' => $_SESSION['user_id'],
                'source' => 'web'
            ];
            
            // Validation
            $errors = [];
            
            // Auto-fetch client email if not provided but client is selected
            if (empty($data['inbound_email_address']) && !empty($data['client_id'])) {
                $clientModel = $this->model('Client');
                $client = $clientModel->getClientById($data['client_id']);
                if ($client && !empty($client['email'])) {
                    $data['inbound_email_address'] = $client['email'];
                    error_log('Auto-filled contact email from client: ' . $client['email']);
                }
            }

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

                    // Send auto-acknowledgment if contact email is provided
                    if (!empty($data['inbound_email_address'])) {
                        // We use the same auto-acknowledgment logic as inbound emails
                        $this->ticketModel->sendAutoAcknowledgmentEmail($ticketId, $data['inbound_email_address']);
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
        
        $format = ($_POST['content_format'] ?? 'text') === 'html' ? 'html' : 'text';
        $rawMessage = (string)($_POST['message'] ?? '');
        $content = $format === 'html' ? $this->sanitizeReplyHtml($rawMessage) : trim($rawMessage);

        $msgType = $_POST['message_type'] ?? 'comment';
        $isPublic = ($msgType !== 'internal_note') ? 1 : 0;

        $messageData = [
            'user_id' => $_SESSION['user_id'],
            'content' => $content,
            'message_type' => $msgType,
            'is_public' => $isPublic,
            'content_format' => $format
        ];
        
        if (empty(trim(strip_tags($messageData['content'])))) {
            flash('error', 'Message content is required.');
            redirect('tickets/show/' . $id);
        }
        
        $messageId = $this->ticketModel->addMessage($id, $messageData);
        error_log('Tickets@addMessage result ID: ' . var_export($messageId, true));
        
        if ($messageId) {
            // Handle file attachments (manual uploads on reply)
            if (!empty($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
                try {
                    if (!class_exists('TicketAttachment')) {
                        require_once APPROOT . '/app/models/TicketAttachment.php';
                    }
                    $attModel = new TicketAttachment();
                    $uploadDir = APPROOT . '/public/uploads/ticket_attachments/' . date('Y/m');
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $count = count($_FILES['attachments']['name']);
                    for ($i = 0; $i < $count; $i++) {
                        $err = $_FILES['attachments']['error'][$i];
                        if ($err !== UPLOAD_ERR_OK) {
                            continue;
                        }
                        $tmp = $_FILES['attachments']['tmp_name'][$i];
                        $orig = $_FILES['attachments']['name'][$i];
                        $size = (int)$_FILES['attachments']['size'][$i];
                        $type = $_FILES['attachments']['type'][$i] ?? 'application/octet-stream';

                        $safeName = $attModel->getSafeFilename($orig);
                        $finalName = $messageId . '_' . uniqid('', true) . '_' . $safeName;
                        $dest = $uploadDir . '/' . $finalName;
                        if (move_uploaded_file($tmp, $dest)) {
                            $relative = 'uploads/ticket_attachments/' . date('Y/m') . '/' . $finalName;
                            $attModel->create([
                                'ticket_id' => (int)$id,
                                'ticket_message_id' => (int)$messageId,
                                'ms_message_id' => null,
                                'ms_attachment_id' => null,
                                'content_id' => null,
                                'filename' => $safeName,
                                'original_filename' => $orig,
                                'file_path' => $relative,
                                'file_size' => $size,
                                'mime_type' => $type,
                                'is_inline' => 0,
                                'is_downloaded' => 1,
                                'download_error' => null,
                                'downloaded_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                } catch (Exception $e) {
                    error_log('Tickets@addMessage attachment upload error: ' . $e->getMessage());
                }
            }

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
            
            // Send notification email only for public, non-internal messages
            $shouldNotify = ($messageData['is_public'] ?? 0) == 1
                && ($messageData['message_type'] ?? 'comment') !== 'internal_note';
            if ($shouldNotify) {
                $this->sendTicketNotification($id, 'ticket_updated', $messageData['content'], $messageData['content_format'], null, $messageId);
                
                // Process email queue immediately to send the notification right away
                try {
                    require_once APPROOT . '/app/services/EmailService.php';
                    $emailService = new EmailService();
                    $emailService->processEmailQueue();
                } catch (Exception $e) {
                    error_log('Failed to process email queue immediately: ' . $e->getMessage());
                }
            }
        } else {
            flash('error', 'Failed to add message. Please try again.');
        }
        
        redirect('tickets/show/' . $id);
    }

    /**
     * Resend the last ticket update email to the requester (manual troubleshooting).
     */
    public function resendLastUpdate($id) {
        $id = (int)$id;
        if ($id <= 0) {
            redirect('tickets');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tickets/show/' . $id);
        }

        if (!hasPermission('tickets.comment')) {
            flash('error', 'You do not have permission to resend ticket emails.');
            redirect('tickets/show/' . $id);
        }

        // CSRF check
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $posted = (string)($_POST['csrf_token'] ?? '');
        if ($posted === '' || !hash_equals((string)$_SESSION['csrf_token'], $posted)) {
            flash('error', 'Security check failed. Please try again.');
            redirect('tickets/show/' . $id);
        }

        $ticket = $this->ticketModel->getById($id);
        if (!$ticket) {
            flash('error', 'Ticket not found.');
            redirect('tickets');
        }
        if (!hasPermission('tickets.read') && !$this->canAccessTicket($ticket, $_SESSION['user_id'])) {
            flash('error', 'You do not have permission to view this ticket.');
            redirect('tickets');
        }

        // Get last queued ticket_updated email for this ticket
        $rows = [];
        if (method_exists($this->ticketModel, 'getEmailQueueForTicket')) {
            $rows = $this->ticketModel->getEmailQueueForTicket($id, 25);
        }
        $last = null;
        foreach ($rows as $r) {
            if (!empty($r['template_name']) && (string)$r['template_name'] === 'ticket_updated') {
                $last = $r;
                break;
            }
        }
        if (!$last && !empty($rows)) {
            $last = $rows[0];
        }

        if (empty($last) || empty($last['to_address']) || empty($last['subject'])) {
            flash('error', 'No previous outbound ticket update email found to resend.');
            redirect('tickets/show/' . $id);
        }

        // Re-queue the same email (keeps message_id so attachments can re-send too)
        $emailData = [
            'template' => $last['template_name'] ?? 'ticket_updated',
            'ticket_id' => $id,
            'message_id' => $last['message_id'] ?? null,
            'to' => (string)$last['to_address'],
            'cc' => !empty($last['cc_address']) ? (string)$last['cc_address'] : null,
            'bcc' => !empty($last['bcc_address']) ? (string)$last['bcc_address'] : null,
            'subject' => (string)$last['subject'],
            'body' => $last['body_text'] ?? null,
            'html_body' => $last['body_html'] ?? null,
        ];

        $queueId = $this->emailService->queueEmail($emailData, 4);
        if (!$queueId) {
            flash('error', 'Failed to queue resend. Please try again.');
            redirect('tickets/show/' . $id);
        }

        // Attempt immediate send
        try {
            $this->emailService->processEmailQueue(5);
        } catch (Exception $e) {
            // ignore; queue row will show error if it fails
        }

        flash('success', 'Resend queued. Check “Outbound Email Delivery” for status.');
        redirect('tickets/show/' . $id);
    }

    /**
     * Link a ticket to an existing client, or create a new client and link it.
     */
    public function linkClient($id) {
        $id = (int)$id;
        if ($id <= 0) {
            redirect('tickets');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tickets/show/' . $id);
        }

        $ticket = $this->ticketModel->getById($id);
        if (!$ticket) {
            flash('error', 'Ticket not found.');
            redirect('tickets');
        }
        $canEdit = hasPermission('tickets.update') || $this->canEditTicket($ticket, $_SESSION['user_id']);
        if (!$canEdit) {
            flash('error', 'You do not have permission to update this ticket.');
            redirect('tickets/show/' . $id);
        }

        // CSRF
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $posted = (string)($_POST['csrf_token'] ?? '');
        if ($posted === '' || !hash_equals((string)$_SESSION['csrf_token'], $posted)) {
            flash('error', 'Security check failed. Please try again.');
            redirect('tickets/show/' . $id);
        }

        $clientModel = $this->model('Client');

        $action = (string)($_POST['client_action'] ?? 'link');
        $clientId = null;

        if ($action === 'create') {
            if (!hasPermission('clients.create')) {
                flash('error', 'You do not have permission to create clients.');
                redirect('tickets/show/' . $id);
            }

            $name = trim((string)($_POST['new_client_name'] ?? ''));
            $email = trim((string)($_POST['new_client_email'] ?? ''));
            $contact = trim((string)($_POST['new_client_contact_person'] ?? ''));
            $phone = trim((string)($_POST['new_client_phone'] ?? ''));
            $address = trim((string)($_POST['new_client_address'] ?? ''));
            $industry = trim((string)($_POST['new_client_industry'] ?? ''));
            $status = trim((string)($_POST['new_client_status'] ?? 'Prospect'));
            $notes = trim((string)($_POST['new_client_notes'] ?? ''));

            if ($name === '') {
                flash('error', 'Client name is required.');
                redirect('tickets/show/' . $id . '?link_client=1');
            }
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', 'Please enter a valid client email.');
                redirect('tickets/show/' . $id . '?link_client=1');
            }

            $newId = $clientModel->addClient([
                'name' => $name,
                'contact_person' => $contact ?: null,
                'email' => $email ?: null,
                'phone' => $phone ?: null,
                'address' => $address ?: null,
                'industry' => $industry ?: null,
                'status' => in_array($status, ['Active','Inactive','Prospect'], true) ? $status : 'Prospect',
                'notes' => $notes ?: null
            ]);

            if (!$newId) {
                flash('error', 'Failed to create client.');
                redirect('tickets/show/' . $id . '?link_client=1');
            }
            $clientId = (int)$newId;
        } else {
            // link existing
            if (!hasPermission('clients.read')) {
                flash('error', 'You do not have permission to view clients.');
                redirect('tickets/show/' . $id);
            }
            $existingId = (int)($_POST['existing_client_id'] ?? 0);
            if ($existingId <= 0) {
                flash('error', 'Please select a client to link.');
                redirect('tickets/show/' . $id . '?link_client=1');
            }
            $existing = $clientModel->getClientById($existingId);
            if (!$existing) {
                flash('error', 'Selected client not found.');
                redirect('tickets/show/' . $id . '?link_client=1');
            }
            $clientId = $existingId;
        }

        if (!$clientId) {
            flash('error', 'Could not link client.');
            redirect('tickets/show/' . $id);
        }

        // Link ticket to client
        $ok = $this->ticketModel->update($id, ['client_id' => $clientId]);
        if (!$ok) {
            flash('error', 'Failed to link ticket to client.');
            redirect('tickets/show/' . $id);
        }

        flash('success', 'Ticket linked to client successfully.');

        // Optionally go to client edit page
        $redirectToClient = !empty($_POST['redirect_to_client']);
        if ($action === 'create' && $redirectToClient && hasPermission('clients.update')) {
            redirect('clients/edit/' . $clientId);
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
     * Email inbox integration removed.
     * This endpoint is kept as a no-op to avoid breaking older links.
     */
    public function refresh($id) {
        if (!hasPermission('tickets.read')) {
            flash('error', 'You do not have permission to view this ticket.');
            redirect('tickets');
        }
        flash('info', 'Email inbox integration has been removed.');
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
     * Archive a ticket (POST only)
     */
    public function archive($id) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            redirect('tickets/show/' . $id);
        }

        if (!hasPermission('tickets.delete')) { // reuse delete perm for archive
            flash('error', 'You do not have permission to archive tickets.');
            redirect('tickets/show/' . $id);
        }

        $ticket = $this->ticketModel->getById($id);
        if (!$ticket) {
            flash('error', 'Ticket not found.');
            redirect('tickets');
        }

        $success = $this->ticketModel->update($id, [
            'is_archived' => 1,
            'archived_at' => date('Y-m-d H:i:s')
        ]);

        if ($success) {
            flash('success', 'Ticket archived.');
        } else {
            flash('error', 'Failed to archive ticket.');
        }
        redirect('tickets');
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
    
    /**
     * Normalize $_FILES entries (supports multi-file).
     */
    private function normalizeUploads($files): array {
        $normalized = [];
        if (empty($files) || !isset($files['name'])) {
            return $normalized;
        }
        if (is_array($files['name'])) {
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                $normalized[] = [
                    'name' => $files['name'][$i] ?? '',
                    'type' => $files['type'][$i] ?? '',
                    'tmp_name' => $files['tmp_name'][$i] ?? '',
                    'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $files['size'][$i] ?? 0
                ];
            }
        } else {
            $normalized[] = $files;
        }
        return $normalized;
    }
    
    private function sendTicketNotification($ticketId, $template, $message = null, $messageFormat = 'text', $recipientUserId = null, $ticketMessageId = null) {
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

            $messageFormat = ($messageFormat === 'html') ? 'html' : 'text';
            $updateMessageText = $messageFormat === 'html' ? trim(strip_tags((string)$message)) : (string)$message;
            $updateMessageHtml = $messageFormat === 'html' ? (string)$message : null;
            
            // Prepare attachment links (not binary attach) for the specific ticket message
            $attachmentLinksHtml = '';
            if (!empty($ticketMessageId)) {
                try {
                    if (!class_exists('TicketAttachment')) {
                        require_once APPROOT . '/app/models/TicketAttachment.php';
                    }
                    $attModel = new TicketAttachment();
                    $atts = $attModel->getByMessageId((int)$ticketMessageId);
                    $downloadable = array_filter($atts, function($a) {
                        return empty($a['is_inline']) || (int)$a['is_inline'] !== 1;
                    });
                    if (!empty($downloadable)) {
                        $items = array_map(function($a) {
                            $name = $a['original_filename'] ?? $a['filename'] ?? 'attachment';
                            if (!empty($a['file_path'])) {
                                $url = URLROOT . '/' . ltrim($a['file_path'], '/');
                                return '<li><a href="' . htmlspecialchars($url) . '" target="_blank">' . htmlspecialchars($name) . '</a></li>';
                            }
                            return '<li>' . htmlspecialchars($name) . ' (pending)</li>';
                        }, $downloadable);
                        $attachmentLinksHtml = '<p><strong>Attachments:</strong></p><ul>' . implode('', $items) . '</ul>';
                        // append to message HTML if present
                        if ($messageFormat === 'html') {
                            $message = (string)$message . $attachmentLinksHtml;
                        }
                    }
                } catch (Exception $e) {
                    error_log('sendTicketNotification: attachment link build failed: ' . $e->getMessage());
                }
            }

            $emailData = $this->emailService->createTicketEmail($template, [
                'ticket_id' => $ticket['id'],
                'ticket_number' => $ticket['ticket_number'],
                'subject' => $ticket['subject'],
                'priority' => $ticket['priority_display'],
                'category' => $ticket['category_name'],
                'created_by_name' => $ticket['created_by_name'],
                'assigned_to_name' => $ticket['assigned_to_name'],
                'description' => $ticket['description'] ?? '',
                'update_message' => $updateMessageText ?? '',
                'update_message_html' => $updateMessageHtml,
                'assignee_email' => $ticket['assigned_to_email'] ?? '',
                'created_by_email' => $ticket['created_by_email'] ?? '',
                'inbound_email_address' => $recipient['email'],
                'due_date' => $ticket['due_date'] ?? 'Not set',
                'resolution' => $updateMessageText ?? ''
            ]);
            if ($ticketMessageId) {
                $emailData['message_id'] = $ticketMessageId;
            }
            
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