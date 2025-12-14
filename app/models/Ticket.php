<?php

/**
 * Comprehensive Ticket Model for Ticketing System
 * Handles tickets, messages, attachments, and email integration
 */
class Ticket {
    private $db;
    private static $lookupsEnsured = false;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }

    /**
     * Ensure default TicketStatuses/TicketPriorities exist (idempotent).
     * Some installs run schema without seeding lookup tables, which breaks ticket creation via FK constraints.
     */
    private function ensureDefaultTicketLookups(): void {
        if (self::$lookupsEnsured) {
            return;
        }
        self::$lookupsEnsured = true;

        // TicketStatuses
        try {
            $r = $this->db->select("SELECT COUNT(*) AS c FROM TicketStatuses");
            $count = isset($r[0]['c']) ? (int)$r[0]['c'] : 0;
            if ($count <= 0) {
                $this->db->insert(
                    "INSERT INTO TicketStatuses (name, display_name, color_code, is_closed, sort_order, is_active, created_at)
                     VALUES
                        ('new', 'New', CHAR(35) + '007bff', 0, 1, 1, GETDATE()),
                        ('open', 'Open', CHAR(35) + '28a745', 0, 2, 1, GETDATE()),
                        ('in_progress', 'In Progress', CHAR(35) + 'ffc107', 0, 3, 1, GETDATE()),
                        ('pending', 'Pending', CHAR(35) + '6f42c1', 0, 4, 1, GETDATE()),
                        ('resolved', 'Resolved', CHAR(35) + '17a2b8', 1, 5, 1, GETDATE()),
                        ('closed', 'Closed', CHAR(35) + '6c757d', 1, 6, 1, GETDATE())"
                );
            }
        } catch (Exception $e) {
            // non-fatal
            error_log('ensureDefaultTicketLookups: TicketStatuses seed failed: ' . $e->getMessage());
        }

        // TicketPriorities
        try {
            $r = $this->db->select("SELECT COUNT(*) AS c FROM TicketPriorities");
            $count = isset($r[0]['c']) ? (int)$r[0]['c'] : 0;
            if ($count <= 0) {
                $this->db->insert(
                    "INSERT INTO TicketPriorities (name, display_name, color_code, level, sort_order, is_active, created_at, response_time_hours, resolution_time_hours)
                     VALUES
                        ('low', 'Low Priority', CHAR(35) + '6c757d', 1, 1, 1, GETDATE(), 48, 168),
                        ('normal', 'Normal Priority', CHAR(35) + '007bff', 2, 2, 1, GETDATE(), 24, 72),
                        ('medium', 'Medium Priority', CHAR(35) + 'ffc107', 3, 3, 1, GETDATE(), 12, 48),
                        ('high', 'High Priority', CHAR(35) + 'fd7e14', 4, 4, 1, GETDATE(), 4, 24),
                        ('critical', 'Critical Priority', CHAR(35) + 'dc3545', 5, 5, 1, GETDATE(), 1, 8)"
                );
            }
        } catch (Exception $e) {
            // non-fatal
            error_log('ensureDefaultTicketLookups: TicketPriorities seed failed: ' . $e->getMessage());
        }
    }

    private function getDefaultStatusId(): int {
        $this->ensureDefaultTicketLookups();
        try {
            $r = $this->db->select(
                "SELECT TOP 1 id FROM TicketStatuses WHERE name = :n OR display_name = :d ORDER BY sort_order ASC, id ASC",
                ['n' => 'new', 'd' => 'New']
            );
            if (!empty($r[0]['id'])) {
                return (int)$r[0]['id'];
            }
        } catch (Exception $e) {
            // ignore
        }
        return 1;
    }

    private function normalizePriorityName(string $priority): string {
        $p = strtolower(trim($priority));
        $p = str_replace([' ', '-'], '_', $p);
        // Map common UI values
        $map = [
            'low' => 'low',
            'low_priority' => 'low',
            'normal' => 'normal',
            'normal_priority' => 'normal',
            'medium' => 'medium',
            'medium_priority' => 'medium',
            'high' => 'high',
            'high_priority' => 'high',
            'critical' => 'critical',
            'critical_priority' => 'critical',
        ];
        return $map[$p] ?? $p;
    }

    private function getDefaultPriorityId(): int {
        $this->ensureDefaultTicketLookups();

        $desired = 'medium';
        try {
            if (!class_exists('Setting')) {
                require_once APPROOT . '/app/models/Setting.php';
            }
            $settingModel = new Setting();
            $ui = (string)$settingModel->get('default_priority', 'Medium');
            $desired = $this->normalizePriorityName($ui);
        } catch (Exception $e) {
            $desired = 'medium';
        }

        try {
            $r = $this->db->select(
                "SELECT TOP 1 id FROM TicketPriorities WHERE name = :n ORDER BY sort_order ASC, id ASC",
                ['n' => $desired]
            );
            if (!empty($r[0]['id'])) {
                return (int)$r[0]['id'];
            }
        } catch (Exception $e) {
            // ignore
        }

        // Fallback to first active priority
        try {
            $r = $this->db->select(
                "SELECT TOP 1 id FROM TicketPriorities WHERE is_active = 1 ORDER BY sort_order ASC, id ASC"
            );
            if (!empty($r[0]['id'])) {
                return (int)$r[0]['id'];
            }
        } catch (Exception $e) {
            // ignore
        }

        return 3;
    }

    /**
     * Create a new ticket
     * 
     * @param array $data Ticket data
     * @return int|false Ticket ID or false on failure
     */
    public function create($data) {
        try {
            $this->ensureDefaultTicketLookups();

            // Generate ticket number
            $ticketNumber = $this->generateTicketNumber();
            
            // Build insert dynamically to optionally include created_at/updated_at overrides
            $columns = [
                'ticket_number', 'subject', 'description', 'status_id', 'priority_id', 'category_id',
                'created_by', 'assigned_to', 'client_id', 'source', 'tags',
                'project_id', 'task_id', 'due_date', 'inbound_email_address',
                'email_thread_id', 'original_message_id'
            ];
            $placeholders = [
                ':ticket_number', ':subject', ':description', ':status_id', ':priority_id', ':category_id',
                ':created_by', ':assigned_to', ':client_id', ':source', ':tags',
                ':project_id', ':task_id', ':due_date', ':inbound_email_address',
                ':email_thread_id', ':original_message_id'
            ];
            
            $params = [
                'ticket_number' => $ticketNumber,
                'subject' => $data['subject'],
                'description' => $data['description'] ?? null,
                'status_id' => $data['status_id'] ?? $this->getDefaultStatusId(), // Default to 'new'
                'priority_id' => $data['priority_id'] ?? $this->getDefaultPriorityId(), // Default from settings
                'category_id' => $data['category_id'] ?? null,
                'created_by' => $data['created_by'],
                'assigned_to' => $data['assigned_to'] ?? null,
                'client_id' => $data['client_id'] ?? null,
                'source' => $data['source'] ?? 'web',
                'tags' => $data['tags'] ?? null,
                'project_id' => $data['project_id'] ?? null,
                'task_id' => $data['task_id'] ?? null,
                // If no due_date provided, derive from SLA policy (category-based) when available
                'due_date' => $data['due_date'] ?? $this->calculateDueDateFromSla($data['category_id'] ?? null),
                'inbound_email_address' => $data['inbound_email_address'] ?? null,
                'email_thread_id' => $data['email_thread_id'] ?? null,
                'original_message_id' => $data['original_message_id'] ?? null
            ];
            
            if (!empty($data['created_at'])) {
                $columns[] = 'created_at';
                $placeholders[] = ':created_at';
                $params['created_at'] = $data['created_at'];
            }
            if (!empty($data['updated_at'])) {
                $columns[] = 'updated_at';
                $placeholders[] = ':updated_at';
                $params['updated_at'] = $data['updated_at'];
            }
            
            $query = 'INSERT INTO Tickets (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
            
            error_log("TICKET: About to execute SQL insert with params: " . json_encode($params));
            $ticketId = $this->db->insert($query, $params);
            // Fallback: some SQL Server trigger setups (e.g. INSTEAD OF triggers) can prevent reliable identity retrieval.
            // In that case, look up the ticket id by the unique ticket number we just generated.
            if (empty($ticketId) && !empty($ticketNumber)) {
                try {
                    $row = $this->db->select(
                        "SELECT TOP 1 id FROM Tickets WHERE ticket_number = :tn ORDER BY id DESC",
                        ['tn' => $ticketNumber]
                    );
                    if (!empty($row[0]['id'])) {
                        $ticketId = (int)$row[0]['id'];
                    }
                } catch (Exception $e) {
                    // ignore; handled below
                }
            }
            error_log("TICKET: SQL insert returned: " . ($ticketId ?: 'false'));
            
            // Add initial message if description provided
            $source = strtolower((string)($data['source'] ?? 'web'));
            $suppressInitial = !empty($data['suppress_initial_message']);
            // For email-created tickets we add a dedicated email_inbound message elsewhere
            if ($ticketId && !empty($data['description']) && !$suppressInitial && $source !== 'email') {
                $this->addMessage($ticketId, [
                    'user_id' => $data['created_by'],
                    'content' => $data['description'],
                    'message_type' => 'comment',
                    'is_public' => 1
                ]);
            }
            
            return $ticketId;
        } catch (Exception $e) {
            error_log('Ticket Create Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate next ticket number
     * Format: TKT-YYYY-NNNNNN
     */
    private function generateTicketNumber() {
        try {
            $year = date('Y');
            
            // Get the highest ticket number for current year
            $result = $this->db->select(
                "SELECT TOP 1 ticket_number FROM Tickets WHERE ticket_number LIKE :pattern ORDER BY id DESC",
                ['pattern' => "TKT-{$year}-%"]
            );
            
            if ($result && !empty($result[0]['ticket_number'])) {
                $lastNumber = $result[0]['ticket_number'];
                if (preg_match("/TKT-(\d{4})-(\d{6})/", $lastNumber, $matches)) {
                    $number = intval($matches[2]) + 1;
                } else {
                    $number = 1;
                }
            } else {
                $number = 1;
            }
            
            return sprintf("TKT-%s-%06d", $year, $number);
        } catch (Exception $e) {
            error_log('generateTicketNumber Error: ' . $e->getMessage());
            return 'TKT-' . date('Y') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Calculate due date from SLA policy based on category
     * Uses TicketCategories.sla_hours when present
     */
    private function calculateDueDateFromSla($categoryId) {
        try {
            if (empty($categoryId)) {
                return null;
            }
            $row = $this->db->select("SELECT sla_hours FROM TicketCategories WHERE id = :id", ['id' => $categoryId]);
            $slaHours = isset($row[0]['sla_hours']) ? (int)$row[0]['sla_hours'] : 0;
            if ($slaHours <= 0) {
                return null;
            }
            // SQL Server GETDATE is used in DB; for param, provide formatted timestamp
            $dueTs = (new DateTime('now'))->modify("+{$slaHours} hours")->format('Y-m-d H:i:s');
            return $dueTs;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Permanently delete a ticket and related records
     *
     * @param int $id Ticket ID
     * @return bool Success
     */
    public function delete($id) {
        try {
            // Remove queued outbound emails tied to this ticket (no cascade)
            $this->db->remove("DELETE FROM EmailQueue WHERE ticket_id = :id", ['id' => $id]);
            // TicketMessages, TicketAttachments, TicketAssignments are set with ON DELETE CASCADE
            $this->db->remove("DELETE FROM Tickets WHERE id = :id", ['id' => $id]);
            return true;
        } catch (Exception $e) {
            error_log('Ticket Delete Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get ticket by ID with full details
     * 
     * @param int $id Ticket ID
     * @return array|false Ticket data or false if not found
     */
    public function getById($id) {
        try {
            $query = "SELECT td.*, t.inbound_email_address, t.source FROM TicketDashboard td 
                     LEFT JOIN Tickets t ON td.id = t.id 
                     WHERE td.id = :id";
            $result = $this->db->select($query, ['id' => $id]);
            return $result[0] ?? false;
        } catch (Exception $e) {
            error_log('Ticket GetById Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get ticket by ticket number
     * 
     * @param string $ticketNumber Ticket number (e.g., TKT-2024-000001)
     * @return array|false Ticket data or false if not found
     */
    public function getByNumber($ticketNumber) {
        try {
            $query = "SELECT * FROM TicketDashboard WHERE ticket_number = :ticket_number";
            $result = $this->db->select($query, ['ticket_number' => $ticketNumber]);
            return $result[0] ?? false;
        } catch (Exception $e) {
            error_log('Ticket GetByNumber Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all tickets with filters and pagination
     * 
     * @param array $filters Filter criteria
     * @param int $page Page number
     * @param int $limit Records per page
     * @return array Tickets data with pagination info
     */
    public function getAll($filters = [], $page = 1, $limit = 25) {
        try {
            $whereClause = "WHERE 1=1";
            $params = [];
            
            // Apply filters
            if (!empty($filters['status_id'])) {
                $whereClause .= " AND status_id = :status_id";
                $params['status_id'] = $filters['status_id'];
            }
            
            if (!empty($filters['priority_id'])) {
                $whereClause .= " AND priority_id = :priority_id";
                $params['priority_id'] = $filters['priority_id'];
            }
            
            if (!empty($filters['category_id'])) {
                $whereClause .= " AND category_id = :category_id";
                $params['category_id'] = $filters['category_id'];
            }
            
            if (!empty($filters['assigned_to'])) {
                if (!empty($filters['include_unassigned'])) {
                    $whereClause .= " AND (assigned_to = :assigned_to OR assigned_to IS NULL)";
                } else {
                    $whereClause .= " AND assigned_to = :assigned_to";
                }
                $params['assigned_to'] = $filters['assigned_to'];
            } elseif (!empty($filters['assigned_is_null'])) {
                $whereClause .= " AND assigned_to IS NULL";
            }
            
            if (!empty($filters['created_by'])) {
                $whereClause .= " AND created_by = :created_by";
                $params['created_by'] = $filters['created_by'];
            }
            
            if (!empty($filters['search'])) {
                $whereClause .= " AND (subject LIKE :search OR ticket_number LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }
            
            if (isset($filters['is_closed'])) {
                $whereClause .= " AND is_closed = :is_closed";
                $params['is_closed'] = $filters['is_closed'];
            }

            if (!empty($filters['client_id'])) {
                $whereClause .= " AND client_id = :client_id";
                $params['client_id'] = $filters['client_id'];
            }
            
            // Count total records
            $countQuery = "SELECT COUNT(*) as total FROM TicketDashboard $whereClause";
            $countResult = $this->db->select($countQuery, $params);
            $total = $countResult[0]['total'] ?? 0;
            
            // Get paginated results
            $offset = ($page - 1) * $limit;
            $orderBy = $filters['order_by'] ?? 'created_at DESC';
            
            $query = "SELECT * FROM TicketDashboard $whereClause 
                     ORDER BY $orderBy 
                     OFFSET $offset ROWS FETCH NEXT $limit ROWS ONLY";
            
            $tickets = $this->db->select($query, $params);
            
            return [
                'tickets' => $tickets,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ];
        } catch (Exception $e) {
            error_log('Ticket GetAll Error: ' . $e->getMessage());
            return ['tickets' => [], 'pagination' => ['current_page' => 1, 'per_page' => $limit, 'total' => 0, 'total_pages' => 0]];
        }
    }

    /**
     * Update ticket
     * 
     * @param int $id Ticket ID
     * @param array $data Updated data
     * @return bool Success status
     */
    public function update($id, $data) {
        try {
            $setClauses = [];
            $params = ['id' => $id];
            
            $allowedFields = [
                'subject', 'description', 'status_id', 'priority_id', 'category_id',
                'assigned_to', 'due_date', 'tags', 'resolved_at', 'closed_at'
            ];
            
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $setClauses[] = "$field = :$field";
                    $params[$field] = $data[$field];
                }
            }
            
            if (empty($setClauses)) {
                return true; // Nothing to update
            }
            
            $query = "UPDATE Tickets SET " . implode(', ', $setClauses) . " WHERE id = :id";
            $this->db->update($query, $params);
            return true;
        } catch (Exception $e) {
            error_log('Ticket Update Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Add a message to a ticket
     * 
     * @param int $ticketId Ticket ID
     * @param array $data Message data
     * @return int|false Message ID or false on failure
     */
    public function addMessage($ticketId, $data) {
        try {
            // Build insert for TicketMessages with optional created_at
            $columns = [
                'ticket_id', 'user_id', 'message_type', 'subject', 'content', 'content_format',
                'email_message_id', 'email_from', 'email_to', 'email_cc', 'email_headers',
                'is_public', 'is_system_message'
            ];
            $placeholders = [
                ':ticket_id', ':user_id', ':message_type', ':subject', ':content', ':content_format',
                ':email_message_id', ':email_from', ':email_to', ':email_cc', ':email_headers',
                ':is_public', ':is_system_message'
            ];
            $params = [
                'ticket_id' => $ticketId,
                'user_id' => $data['user_id'] ?? null,
                'message_type' => $data['message_type'] ?? 'comment',
                'subject' => $data['subject'] ?? null,
                'content' => $data['content'],
                'content_format' => $data['content_format'] ?? 'text',
                'email_message_id' => $data['email_message_id'] ?? null,
                'email_from' => $data['email_from'] ?? null,
                'email_to' => $data['email_to'] ?? null,
                'email_cc' => $data['email_cc'] ?? null,
                'email_headers' => $data['email_headers'] ?? null,
                'is_public' => $data['is_public'] ?? 1,
                'is_system_message' => $data['is_system_message'] ?? 0
            ];

            // Optional: store full email chain separately (for toggle in UI)
            if (array_key_exists('content_full', $data)) {
                $columns[] = 'content_full';
                $placeholders[] = ':content_full';
                $params['content_full'] = $data['content_full'];
            }
            if (array_key_exists('content_full_format', $data)) {
                $columns[] = 'content_full_format';
                $placeholders[] = ':content_full_format';
                $params['content_full_format'] = $data['content_full_format'];
            }
            if (!empty($data['created_at'])) {
                $columns[] = 'created_at';
                $placeholders[] = ':created_at';
                $params['created_at'] = $data['created_at'];
            }
            $query = 'INSERT INTO TicketMessages (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
            
            $messageId = $this->db->insert($query, $params);
            
            // Update ticket's updated_at timestamp unless suppressed
            $suppressTouch = !empty($data['suppress_ticket_touch']);
            if ($messageId && !$suppressTouch) {
                $this->db->update("UPDATE Tickets SET updated_at = GETDATE() WHERE id = :id", ['id' => $ticketId]);
            }
            
            return $messageId;
        } catch (Exception $e) {
            error_log('Ticket AddMessage Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update first response time for a ticket
     * 
     * @param int $ticketId Ticket ID
     * @return bool Success status
     */
    public function updateFirstResponse($ticketId) {
        try {
            $query = "UPDATE Tickets SET first_response_at = GETDATE() WHERE id = :id AND first_response_at IS NULL";
            $result = $this->db->update($query, ['id' => $ticketId]);
            return $result !== false;
        } catch (Exception $e) {
            error_log('Ticket UpdateFirstResponse Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get messages for a ticket
     * 
     * @param int $ticketId Ticket ID
     * @param bool $includePrivate Include private/internal messages
     * @return array Messages
     */
    public function getMessages($ticketId, $includePrivate = true) {
        try {
            $whereClause = "WHERE tm.ticket_id = :ticket_id";
            $params = ['ticket_id' => $ticketId];
            
            if (!$includePrivate) {
                $whereClause .= " AND tm.is_public = 1";
            }
            
            $query = "SELECT tm.*, 
                        u.username, u.full_name, u.email
                      FROM TicketMessages tm
                      LEFT JOIN Users u ON tm.user_id = u.id
                      $whereClause
                      ORDER BY tm.created_at DESC, tm.id DESC";
            
            return $this->db->select($query, $params);
        } catch (Exception $e) {
            error_log('Ticket GetMessages Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get open tickets count
     * 
     * @return int Number of open tickets
     */
    public function getOpenTicketsCount() {
        try {
            // Check if Tickets table exists
            $checkQuery = "SELECT COUNT(*) as table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'Tickets'";
            $result = $this->db->select($checkQuery);
            
            if (empty($result) || $result[0]['table_count'] == 0) {
                return 0; // Table doesn't exist yet
            }
            
            $query = "SELECT COUNT(*) as count FROM TicketDashboard WHERE is_closed = 0";
            $result = $this->db->select($query);
            return $result[0]['count'] ?? 0;
        } catch (Exception $e) {
            error_log('GetOpenTicketsCount Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get ticket statistics
     * 
     * @return array Various ticket statistics
     */
    public function getStatistics() {
        try {
            // Check if table exists first
            $checkQuery = "SELECT COUNT(*) as table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'Tickets'";
            $result = $this->db->select($checkQuery);
            
            if (empty($result) || $result[0]['table_count'] == 0) {
                return [
                    'total' => 0,
                    'open' => 0,
                    'closed' => 0,
                    'overdue' => 0,
                    'high_priority' => 0,
                    'unassigned' => 0
                ];
            }
            
            $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_closed = 0 THEN 1 ELSE 0 END) as [open],
                SUM(CASE WHEN is_closed = 1 THEN 1 ELSE 0 END) as closed,
                SUM(CASE WHEN is_overdue = 1 THEN 1 ELSE 0 END) as overdue,
                SUM(CASE WHEN priority_level >= 4 AND is_closed = 0 THEN 1 ELSE 0 END) as high_priority,
                SUM(CASE WHEN assigned_to_username IS NULL AND is_closed = 0 THEN 1 ELSE 0 END) as unassigned
                FROM TicketDashboard";
            
            $result = $this->db->select($query);
            return $result[0] ?? [];
        } catch (Exception $e) {
            error_log('Ticket GetStatistics Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get lookup data for forms
     * 
     * @return array Statuses, priorities, and categories
     */
    public function getLookupData() {
        try {
            $statuses = $this->db->select("SELECT * FROM TicketStatuses WHERE is_active = 1 ORDER BY sort_order");
            $priorities = $this->db->select("SELECT * FROM TicketPriorities WHERE is_active = 1 ORDER BY sort_order");
            $categories = $this->db->select("SELECT * FROM TicketCategories WHERE is_active = 1 ORDER BY name");
            
            return [
                'statuses' => $statuses,
                'priorities' => $priorities,
                'categories' => $categories
            ];
        } catch (Exception $e) {
            error_log('Ticket GetLookupData Error: ' . $e->getMessage());
            return ['statuses' => [], 'priorities' => [], 'categories' => []];
        }
    }

    /**
     * Assign ticket to user(s)
     * 
     * @param int $ticketId Ticket ID
     * @param int|array $userIds User ID(s) to assign
     * @param int $assignedBy Who is making the assignment
     * @param string $role Assignment role
     * @return bool Success status
     */
    public function assignTo($ticketId, $userIds, $assignedBy, $role = 'assignee') {
        try {
            if (!is_array($userIds)) {
                $userIds = [$userIds];
            }
            
            // Update primary assignee if role is 'assignee'
            if ($role === 'assignee' && count($userIds) === 1) {
                $this->update($ticketId, ['assigned_to' => $userIds[0]]);
            }
            
            // Add to assignments table
            foreach ($userIds as $userId) {
                $query = "INSERT INTO TicketAssignments (ticket_id, user_id, assigned_by, role) 
                         VALUES (:ticket_id, :user_id, :assigned_by, :role)";
                
                $this->db->insert($query, [
                    'ticket_id' => $ticketId,
                    'user_id' => $userId,
                    'assigned_by' => $assignedBy,
                    'role' => $role
                ]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Ticket AssignTo Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create ticket from email
     * 
     * @param array $emailData Email data
     * @return int|false Ticket ID or false on failure
     */
    public function createFromEmail($emailData) {
        try {
            $this->ensureDefaultTicketLookups();

            // Extract or find user by email
            $userId = $this->getUserByEmail($emailData['from_address']);
            $emailDate = $emailData['email_date'] ?? null;
            
            // Prefer Graph conversation/thread id when available
            $threadId = $emailData['conversation_id'] ?? ($emailData['conversationId'] ?? null);
            if (empty($threadId)) {
                $threadId = $emailData['message_id'];
            }
            
            $ticketData = [
                'subject' => $emailData['subject'],
                'description' => $emailData['body_text'] ?: $emailData['body_html'],
                'created_by' => $userId ?: 1, // Default to system user if not found
                'source' => 'email',
                // Prevent Ticket::create() from adding a duplicate "comment" message
                'suppress_initial_message' => true,
                'status_id' => $this->getDefaultStatusId(),
                'priority_id' => $this->getDefaultPriorityId(),
                'inbound_email_address' => $emailData['from_address'],
                'email_thread_id' => $threadId,
                'original_message_id' => $emailData['message_id']
            ];
            // Auto-link client by sender email domain when mapping exists
            try {
                if (!class_exists('ClientDomain')) {
                    require_once APPROOT . '/app/models/ClientDomain.php';
                }
                $clientDomainModel = new ClientDomain();
                $mappedClientId = $clientDomainModel->getClientIdByEmail($emailData['from_address']);
                if (!empty($mappedClientId)) {
                    $ticketData['client_id'] = (int)$mappedClientId;
                }
            } catch (Exception $e) {
                // ignore mapping errors
            }
            if ($emailDate) {
                $ticketData['created_at'] = $emailDate;
                $ticketData['updated_at'] = $emailDate;
            }
            
            $ticketId = $this->create($ticketData);
            
            if ($ticketId) {
                $contentHtml = $emailData['body_html'] ?? '';
                $contentText = $emailData['body_text'] ?? '';
                $useHtml = is_string($contentHtml) && trim($contentHtml) !== '';
                $msgContent = $useHtml ? $contentHtml : $contentText;
                $msgFormat = $useHtml ? 'html' : 'text';

                // Add email message to ticket
                $this->addMessage($ticketId, [
                    'user_id' => $userId,
                    'message_type' => 'email_inbound',
                    'subject' => $emailData['subject'],
                    'content' => $msgContent,
                    'content_format' => $msgFormat,
                    'email_message_id' => $emailData['message_id'],
                    'email_from' => $emailData['from_address'],
                    'email_to' => $emailData['to_address'],
                    'email_cc' => $emailData['cc_address'],
                    'email_headers' => json_encode($emailData['headers'] ?? []),
                    'is_public' => 1,
                    'created_at' => $emailDate ?: null,
                    // Do not bump ticket updated_at to now; already set to email date above
                    'suppress_ticket_touch' => true
                ]);
                
                // Send auto-acknowledgment email if enabled
                $this->sendAutoAcknowledgmentEmail($ticketId, $emailData['from_address']);
            }
            
            return $ticketId;
        } catch (Exception $e) {
            error_log('Ticket CreateFromEmail Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Find user by email address
     * 
     * @param string $email Email address
     * @return int|false User ID or false if not found
     */
    private function getUserByEmail($email) {
        try {
            $query = "SELECT id, email, full_name, username FROM Users WHERE email = :email";
            $result = $this->db->select($query, ['email' => $email]);
            return $result[0]['id'] ?? false;
        } catch (Exception $e) {
            error_log('GetUserByEmail Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a customer has any tickets
     * 
     * @param string $customerEmail Customer email address
     * @return bool True if customer has tickets, false otherwise
     */
    public function hasCustomerTickets($customerEmail) {
        try {
            $query = "SELECT COUNT(*) as count 
                     FROM Tickets t
                     WHERE t.inbound_email_address = ? 
                     OR t.created_by IN (SELECT id FROM Users WHERE email = ?)";
            
            $result = $this->db->select($query, [$customerEmail, $customerEmail]);
            
            return $result && (int)$result[0]['count'] > 0;
            
        } catch (Exception $e) {
            error_log('HasCustomerTickets Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get ticket messages for a specific ticket
     * 
     * @param int $ticketId Ticket ID
     * @return array Array of ticket messages
     */
    public function getTicketMessages($ticketId) {
        try {
            $query = "SELECT 
                        tm.*,
                        u.full_name as user_name,
                        u.email as user_email
                      FROM TicketMessages tm
                      LEFT JOIN Users u ON tm.user_id = u.id
                      WHERE tm.ticket_id = ?
                      ORDER BY tm.created_at DESC, tm.id DESC";
            
            $result = $this->db->select($query, [$ticketId]);
            
            return $result ?: [];
            
        } catch (Exception $e) {
            error_log('GetTicketMessages Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Send auto-acknowledgment email for new ticket created from email
     */
    private function sendAutoAcknowledgmentEmail($ticketId, $requesterEmail) {
        error_log("sendAutoAcknowledgmentEmail called for ticket $ticketId, email $requesterEmail");
        try {
            // Validate inputs
            if (empty($ticketId) || empty($requesterEmail)) {
                error_log("Invalid parameters: ticketId=$ticketId, email=$requesterEmail");
                return false;
            }
            
            // Validate email format
            if (!filter_var($requesterEmail, FILTER_VALIDATE_EMAIL)) {
                error_log("Invalid email format: $requesterEmail");
                return false;
            }
            
            // Check if auto-acknowledgment is enabled
            require_once APPROOT . '/app/models/Setting.php';
            $settingModel = new Setting();
            $autoAcknowledge = $settingModel->get('auto_acknowledge_tickets', true);
            
            error_log("Auto acknowledge setting: " . ($autoAcknowledge ? 'enabled' : 'disabled'));
            
            if (!$autoAcknowledge) {
                error_log("Auto acknowledgment is disabled, skipping email");
                return false;
            }
            
            // Get ticket details
            $ticket = $this->getById($ticketId);
            if (!$ticket) {
                error_log("Ticket not found: $ticketId");
                return false;
            }
            
            // Check if acknowledgment was already sent for this ticket
            $existingAck = $this->db->select(
                "SELECT TOP 1 id, status FROM EmailQueue 
                 WHERE ticket_id = :ticket_id 
                 AND subject LIKE '%Thank you%' 
                 AND to_address = :email",
                ['ticket_id' => $ticketId, 'email' => $requesterEmail]
            );
            
            if (!empty($existingAck)) {
                error_log("Acknowledgment already queued/sent for ticket {$ticket['ticket_number']} to {$requesterEmail} (Status: {$existingAck[0]['status']})");
                return true;
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
                error_log("Auto-acknowledgment queued successfully (Queue ID: $result) for ticket {$ticket['ticket_number']} to {$requesterEmail}");
                
                // Process email queue immediately to send the acknowledgment
                try {
                    $processed = $emailService->processEmailQueue(1); // Process just one email
                    if ($processed > 0) {
                        error_log("Auto-acknowledgment sent immediately for ticket {$ticket['ticket_number']}");
                    } else {
                        error_log("Auto-acknowledgment queued for later processing for ticket {$ticket['ticket_number']}");
                    }
                } catch (Exception $e) {
                    error_log('Failed to process acknowledgment email queue immediately: ' . $e->getMessage());
                    // Still success as email is queued
                }
                return true;
            } else {
                error_log("Failed to queue auto-acknowledgment for ticket {$ticket['ticket_number']}");
                return false;
            }
            
        } catch (Exception $e) {
            error_log('SendAutoAcknowledgmentEmail Error: ' . $e->getMessage());
            error_log('SendAutoAcknowledgmentEmail Stack: ' . $e->getTraceAsString());
            return false;
        }
    }
} 