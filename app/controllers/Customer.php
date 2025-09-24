<?php

/**
 * Customer Portal Controller
 * Handles customer ticket viewing and management
 */
class Customer extends Controller {
    private $ticketModel;
    private $settingModel;
    
    public function __construct() {
        // Check if customer authentication is enabled
        $this->settingModel = $this->model('Setting');
        if (!$this->settingModel->get('customer_auth_enabled')) {
            $this->view('errors/404', ['title' => 'Not Found']);
            return;
        }
        
        // Check if customer is logged in
        if (!$this->isCustomerLoggedIn()) {
            redirect('customer/auth');
            return;
        }
        
        $this->ticketModel = $this->model('Ticket');
    }
    
    /**
     * Customer dashboard
     */
    public function index() {
        $this->dashboard();
    }
    
    /**
     * Customer dashboard with ticket overview
     */
    public function dashboard() {
        $customerEmail = $_SESSION['customer_email'];
        $ticketVisibility = $this->settingModel->get('ticket_visibility') ?? 'email_match';
        
        // Get ticket statistics
        $stats = $this->getCustomerTicketStats($customerEmail, $ticketVisibility);
        
        // Get recent tickets
        $recentTickets = $this->getCustomerTickets($customerEmail, $ticketVisibility, 5);
        
        $data = [
            'title' => 'Customer Dashboard',
            'customer_name' => $_SESSION['customer_name'],
            'customer_email' => $customerEmail,
            'stats' => $stats,
            'recent_tickets' => $recentTickets,
            'ticket_visibility' => $ticketVisibility
        ];
        
        $this->view('customer/dashboard', $data);
    }
    
    /**
     * View all customer tickets
     */
    public function tickets($status = 'all') {
        $customerEmail = $_SESSION['customer_email'];
        $ticketVisibility = $this->settingModel->get('ticket_visibility') ?? 'email_match';
        
        // Validate status parameter
        $validStatuses = ['all', 'open', 'closed'];
        if (!in_array($status, $validStatuses)) {
            $status = 'all';
        }
        
        // Get tickets based on status
        $tickets = $this->getCustomerTickets($customerEmail, $ticketVisibility, null, $status);
        
        // Get ticket statistics
        $stats = $this->getCustomerTicketStats($customerEmail, $ticketVisibility);
        
        $data = [
            'title' => 'My Tickets',
            'tickets' => $tickets,
            'stats' => $stats,
            'current_status' => $status,
            'customer_name' => $_SESSION['customer_name']
        ];
        
        $this->view('customer/tickets/index', $data);
    }
    
    /**
     * View specific ticket details
     */
    public function ticket($ticketId = null) {
        if (!$ticketId) {
            redirect('customer/tickets');
            return;
        }
        
        $customerEmail = $_SESSION['customer_email'];
        $ticketVisibility = $this->settingModel->get('ticket_visibility') ?? 'email_match';
        
        // Get ticket details
        $ticket = $this->getCustomerTicket($ticketId, $customerEmail, $ticketVisibility);
        
        if (!$ticket) {
            flash('ticket_error', 'Ticket not found or you do not have permission to view it.', 'alert alert-danger');
            redirect('customer/tickets');
            return;
        }
        
        // Get ticket messages/conversation
        $messages = $this->ticketModel->getTicketMessages($ticketId);

        // Filter messages to only show public ones
        $messages = array_values(array_filter($messages, function($message) {
            return (int)($message['is_public'] ?? 0) === 1;
        }));

        // If the ticket came from email, prepend the original email content
        try {
            if (isset($ticket['source']) && $ticket['source'] === 'email') {
                $db = new EasySQL(DB1);
                $originalEmail = $db->select(
                    "SELECT TOP 1 id, body_html, body_text, from_address, to_address, cc_address, subject, email_date \n" .
                    "FROM EmailInbox WHERE ticket_id = :ticket_id ORDER BY email_date ASC",
                    ['ticket_id' => $ticketId]
                );
                if (!empty($originalEmail)) {
                    $email = $originalEmail[0];
                    $content = $email['body_html'] ?: ($email['body_text'] ?? '');
                    $originalMessage = [
                        'id' => 'original_email',
                        'created_at' => $email['email_date'] ?: $ticket['created_at'],
                        'message_type' => 'email_inbound',
                        'subject' => $email['subject'] ?: $ticket['subject'],
                        'content' => $content,
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
                    array_unshift($messages, $originalMessage);
                }
            }
        } catch (Exception $e) {
            // ignore, conversation will still render
        }

        // If still no messages, attempt to pull thread from EmailInbox by subject/message id
        if (empty($messages)) {
            try {
                $emailInboxModel = $this->model('EmailInboxModel');
                $threadEmails = $emailInboxModel->getEmailThread($ticket['subject'] ?? '', $ticket['original_message_id'] ?? '');
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
                if (!empty($threadEmails)) {
                    usort($messages, function($a, $b) {
                        return strtotime($a['created_at']) <=> strtotime($b['created_at']);
                    });
                }
            } catch (Exception $e) {
                // ignore
            }
        }

        // Rewrite inline CID images to served URLs using EmailInbox::inline and attachment name mapping
        try {
            $db = new EasySQL(DB1);
            $inlineAttachments = $db->select(
                "SELECT ea.content_id, ea.file_path, ea.filename, ea.original_filename \n" .
                "FROM EmailAttachments ea \n" .
                "JOIN EmailInbox ei ON ea.email_inbox_id = ei.id \n" .
                "WHERE ei.ticket_id = :ticket_id AND ea.is_downloaded = 1",
                ['ticket_id' => $ticketId]
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
                        $html = preg_replace_callback('/src\s*=\s*("|\')cid:([^"\']+)(\1)/i', function($m) use ($cidMap, $nameMap) {
                            $q = $m[1];
                            $cidRaw = trim($m[2], '<>');
                            $cidKey = strtolower($cidRaw);
                            if (isset($cidMap[$cidKey])) {
                                return 'src=' . $q . htmlspecialchars($cidMap[$cidKey], ENT_QUOTES, 'UTF-8') . $q;
                            }
                            $baseAlt = strtolower(basename($cidRaw));
                            if (isset($nameMap[$baseAlt])) {
                                return 'src=' . $q . htmlspecialchars($nameMap[$baseAlt], ENT_QUOTES, 'UTF-8') . $q;
                            }
                            return $m[0];
                        }, $html);
                    }
                    if (!empty($nameMap)) {
                        $html = preg_replace_callback('/src\s*=\s*("|\')(?!https?:|data:|cid:)([^"\']+)(\1)/i', function($m) use ($nameMap) {
                            $q = $m[1];
                            $src = strtolower(basename($m[2]));
                            return isset($nameMap[$src]) ? 'src=' . $q . htmlspecialchars($nameMap[$src], ENT_QUOTES, 'UTF-8') . $q : $m[0];
                        }, $html);
                    }
                    $messages[$idx]['content'] = $html;
                }
            }
        } catch (Exception $e) {
            // ignore rewrite errors
        }
        
        // CSRF token for reply form
        $csrfToken = bin2hex(random_bytes(16));
        $_SESSION['customer_reply_csrf_' . $ticketId] = $csrfToken;

        $data = [
            'title' => 'Ticket #' . $ticket['ticket_number'],
            'ticket' => $ticket,
            'messages' => $messages,
            'customer_name' => $_SESSION['customer_name'],
            'csrf_token' => $csrfToken
        ];
        
        $this->view('customer/tickets/view', $data);
    }

    /**
     * Handle customer reply to a ticket (public message)
     */
    public function reply($ticketId = null) {
        if (!$ticketId) {
            redirect('customer/tickets');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tickets/show/' . $ticketId);
            return;
        }

        // CSRF validation
        $csrf = $_POST['csrf_token'] ?? '';
        $expected = $_SESSION['customer_reply_csrf_' . $ticketId] ?? '';
        $isAjax = (
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
        );

        if (empty($csrf) || empty($expected) || !hash_equals($expected, $csrf)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Security check failed. Please refresh and try again.']);
                return;
            }
            flash('ticket_error', 'Security check failed. Please try again.', 'alert alert-danger');
            redirect('tickets/show/' . $ticketId);
            return;
        }
        unset($_SESSION['customer_reply_csrf_' . $ticketId]);

        // Ensure ticket belongs to this customer (email/domain visibility)
        $customerEmail = $_SESSION['customer_email'];
        $ticketVisibility = $this->settingModel->get('ticket_visibility') ?? 'email_match';
        $ticket = $this->getCustomerTicket($ticketId, $customerEmail, $ticketVisibility);
        if (!$ticket) {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Not authorized to reply to this ticket.']);
                return;
            }
            flash('ticket_error', 'You do not have permission to reply to this ticket.', 'alert alert-danger');
            redirect('customer/tickets');
            return;
        }

        // Validate message
        $content = trim($_POST['content'] ?? '');
        if ($content === '') {
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Message cannot be empty.']);
                return;
            }
            flash('ticket_error', 'Message cannot be empty.', 'alert alert-warning');
            redirect('tickets/show/' . $ticketId);
            return;
        }

        // Store as public comment
        $ok = $this->ticketModel->addMessage($ticketId, [
            'user_id' => null,
            'message_type' => 'comment',
            'subject' => null,
            'content' => $content,
            'content_format' => 'text',
            'email_message_id' => null,
            'email_from' => $_SESSION['customer_email'] ?? null,
            'email_to' => null,
            'email_cc' => null,
            'email_headers' => null,
            'is_public' => 1,
            'is_system_message' => 0
        ]);

        if ($isAjax) {
            header('Content-Type: application/json');
            if ($ok) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to post your reply.']);
            }
            return;
        } else {
            if ($ok) {
                flash('ticket_success', 'Your reply has been posted.', 'alert alert-success');
            } else {
                flash('ticket_error', 'Failed to post your reply. Please try again.', 'alert alert-danger');
            }
            redirect('tickets/show/' . $ticketId);
        }
    }
    
    /**
     * Customer profile/account page
     */
    public function profile() {
        $data = [
            'title' => 'My Profile',
            'customer_name' => $_SESSION['customer_name'],
            'customer_email' => $_SESSION['customer_email'],
            'customer_domain' => $_SESSION['customer_domain'],
            'login_time' => $_SESSION['customer_login_time']
        ];
        
        $this->view('customer/profile', $data);
    }
    
    /**
     * Check if customer is logged in
     */
    private function isCustomerLoggedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['customer_logged_in']) && 
               $_SESSION['customer_logged_in'] === true &&
               isset($_SESSION['customer_email']);
    }
    
    /**
     * Get customer ticket statistics
     */
    private function getCustomerTicketStats($customerEmail, $ticketVisibility): array {
        try {
            if ($ticketVisibility === 'domain_match') {
                $domain = substr(strrchr($customerEmail, '@'), 1);
                $whereClause = "AND (t.inbound_email_address LIKE ? OR t.created_by IN (SELECT id FROM Users WHERE email LIKE ?))";
                $params = ["%@{$domain}", "%@{$domain}"];
            } else {
                $whereClause = "AND (t.inbound_email_address = ? OR t.created_by IN (SELECT id FROM Users WHERE email = ?))";
                $params = [$customerEmail, $customerEmail];
            }
            
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN ts.is_closed = 0 THEN 1 ELSE 0 END) as [open],
                        SUM(CASE WHEN ts.is_closed = 1 THEN 1 ELSE 0 END) as closed,
                        SUM(CASE WHEN t.priority_id = 1 THEN 1 ELSE 0 END) as high_priority,
                        AVG(DATEDIFF(day, t.created_at, COALESCE(t.resolved_at, GETDATE()))) as avg_resolution_days
                      FROM Tickets t
                      LEFT JOIN TicketStatuses ts ON t.status_id = ts.id
                      WHERE 1=1 {$whereClause}";
            
            $db = new EasySQL(DB1);
            $result = $db->select($query, $params);
            
            if (empty($result)) {
                return [
                    'total' => 0,
                    'open' => 0,
                    'closed' => 0,
                    'high_priority' => 0,
                    'avg_resolution_days' => 0
                ];
            }
            
            return [
                'total' => (int)$result[0]['total'],
                'open' => (int)$result[0]['open'],
                'closed' => (int)$result[0]['closed'],
                'high_priority' => (int)$result[0]['high_priority'],
                'avg_resolution_days' => round((float)$result[0]['avg_resolution_days'], 1)
            ];
            
        } catch (Exception $e) {
            error_log('Customer ticket stats error: ' . $e->getMessage());
            return [
                'total' => 0,
                'open' => 0,
                'closed' => 0,
                'high_priority' => 0,
                'avg_resolution_days' => 0
            ];
        }
    }
    
    /**
     * Get customer tickets
     */
    private function getCustomerTickets($customerEmail, $ticketVisibility, $limit = null, $status = 'all'): array {
        try {
            if ($ticketVisibility === 'domain_match') {
                $domain = substr(strrchr($customerEmail, '@'), 1);
                $whereClause = "AND (t.inbound_email_address LIKE ? OR t.created_by IN (SELECT id FROM Users WHERE email LIKE ?))";
                $params = ["%@{$domain}", "%@{$domain}"];
            } else {
                $whereClause = "AND (t.inbound_email_address = ? OR t.created_by IN (SELECT id FROM Users WHERE email = ?))";
                $params = [$customerEmail, $customerEmail];
            }
            
            // Add status filter
            if ($status === 'open') {
                $whereClause .= " AND ts.is_closed = 0";
            } elseif ($status === 'closed') {
                $whereClause .= " AND ts.is_closed = 1";
            }
            
            $limitClause = $limit ? "TOP {$limit}" : "";
            
            $query = "SELECT {$limitClause} 
                        t.id, t.ticket_number, t.subject, t.created_at, t.updated_at,
                        t.due_date, t.resolved_at, t.closed_at,
                        ts.name as status, ts.display_name as status_display, ts.color_code as status_color,
                        tp.name as priority, tp.display_name as priority_display, tp.color_code as priority_color,
                        tc.name as category, tc.description as category_display, tc.color_code as category_color,
                        u.full_name as assigned_to_name,
                        (SELECT COUNT(*) FROM TicketMessages tm WHERE tm.ticket_id = t.id AND tm.is_public = 1) as message_count,
                        (SELECT TOP 1 tm.created_at FROM TicketMessages tm WHERE tm.ticket_id = t.id ORDER BY tm.created_at DESC) as last_activity
                      FROM Tickets t
                      LEFT JOIN TicketStatuses ts ON t.status_id = ts.id
                      LEFT JOIN TicketPriorities tp ON t.priority_id = tp.id
                      LEFT JOIN TicketCategories tc ON t.category_id = tc.id
                      LEFT JOIN Users u ON t.assigned_to = u.id
                      WHERE 1=1 {$whereClause}
                      ORDER BY t.updated_at DESC";
            
            $db = new EasySQL(DB1);
            $result = $db->select($query, $params);
            
            return $result ?: [];
            
        } catch (Exception $e) {
            error_log('Customer tickets query error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get specific customer ticket
     */
    private function getCustomerTicket($ticketId, $customerEmail, $ticketVisibility) {
        try {
            if ($ticketVisibility === 'domain_match') {
                $domain = substr(strrchr($customerEmail, '@'), 1);
                $whereClause = "AND (t.inbound_email_address LIKE ? OR t.created_by IN (SELECT id FROM Users WHERE email LIKE ?))";
                $params = [$ticketId, "%@{$domain}", "%@{$domain}"];
            } else {
                $whereClause = "AND (t.inbound_email_address = ? OR t.created_by IN (SELECT id FROM Users WHERE email = ?))";
                $params = [$ticketId, $customerEmail, $customerEmail];
            }
            
            $query = "SELECT 
                        t.*, 
                        ts.name as status, ts.display_name as status_display, ts.color_code as status_color,
                        tp.name as priority, tp.display_name as priority_display, tp.color_code as priority_color,
                        tc.name as category, tc.description as category_display, tc.color_code as category_color,
                        u.full_name as assigned_to_name, u.email as assigned_to_email
                      FROM Tickets t
                      LEFT JOIN TicketStatuses ts ON t.status_id = ts.id
                      LEFT JOIN TicketPriorities tp ON t.priority_id = tp.id
                      LEFT JOIN TicketCategories tc ON t.category_id = tc.id
                      LEFT JOIN Users u ON t.assigned_to = u.id
                      WHERE t.id = ? {$whereClause}";
            
            $db = new EasySQL(DB1);
            $result = $db->select($query, $params);
            
            return $result ? $result[0] : null;
            
        } catch (Exception $e) {
            error_log('Customer ticket query error: ' . $e->getMessage());
            return null;
        }
    }
}
