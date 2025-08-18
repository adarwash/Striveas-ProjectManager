<?php

/**
 * Comprehensive Ticket Model for Ticketing System
 * Handles tickets, messages, attachments, and email integration
 */
class Ticket {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }

    /**
     * Create a new ticket
     * 
     * @param array $data Ticket data
     * @return int|false Ticket ID or false on failure
     */
    public function create($data) {
        try {
            $query = "INSERT INTO Tickets (
                subject, description, status_id, priority_id, category_id,
                created_by, assigned_to, client_id, source, tags, is_internal,
                project_id, task_id, due_date, inbound_email_address, 
                email_thread_id, original_message_id
            ) VALUES (
                :subject, :description, :status_id, :priority_id, :category_id,
                :created_by, :assigned_to, :client_id, :source, :tags, :is_internal,
                :project_id, :task_id, :due_date, :inbound_email_address,
                :email_thread_id, :original_message_id
            )";
            
            $params = [
                'subject' => $data['subject'],
                'description' => $data['description'] ?? null,
                'status_id' => $data['status_id'] ?? 1, // Default to 'new'
                'priority_id' => $data['priority_id'] ?? 3, // Default to 'normal'
                'category_id' => $data['category_id'] ?? null,
                'created_by' => $data['created_by'],
                'assigned_to' => $data['assigned_to'] ?? null,
                'client_id' => $data['client_id'] ?? null,
                'source' => $data['source'] ?? 'web',
                'tags' => $data['tags'] ?? null,
                'is_internal' => $data['is_internal'] ?? 0,
                'project_id' => $data['project_id'] ?? null,
                'task_id' => $data['task_id'] ?? null,
                // If no due_date provided, derive from SLA policy (category-based) when available
                'due_date' => $data['due_date'] ?? $this->calculateDueDateFromSla($data['category_id'] ?? null),
                'inbound_email_address' => $data['inbound_email_address'] ?? null,
                'email_thread_id' => $data['email_thread_id'] ?? null,
                'original_message_id' => $data['original_message_id'] ?? null
            ];
            
            $ticketId = $this->db->insert($query, $params);
            
            // Add initial message if description provided
            if ($ticketId && !empty($data['description'])) {
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
                $whereClause .= " AND assigned_to = :assigned_to";
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
            $query = "INSERT INTO TicketMessages (
                ticket_id, user_id, message_type, subject, content, content_format,
                email_message_id, email_from, email_to, email_cc, email_headers,
                is_public, is_system_message
            ) VALUES (
                :ticket_id, :user_id, :message_type, :subject, :content, :content_format,
                :email_message_id, :email_from, :email_to, :email_cc, :email_headers,
                :is_public, :is_system_message
            )";
            
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
            
            $messageId = $this->db->insert($query, $params);
            
            // Update ticket's updated_at timestamp
            if ($messageId) {
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
                      ORDER BY tm.created_at ASC";
            
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
            // Extract or find user by email
            $userId = $this->getUserByEmail($emailData['from_address']);
            
            $ticketData = [
                'subject' => $emailData['subject'],
                'description' => $emailData['body_text'] ?: $emailData['body_html'],
                'created_by' => $userId ?: 1, // Default to system user if not found
                'source' => 'email',
                'inbound_email_address' => $emailData['from_address'],
                'email_thread_id' => $emailData['message_id'],
                'original_message_id' => $emailData['message_id']
            ];
            
            $ticketId = $this->create($ticketData);
            
            if ($ticketId) {
                // Add email message to ticket
                $this->addMessage($ticketId, [
                    'user_id' => $userId,
                    'message_type' => 'email_inbound',
                    'subject' => $emailData['subject'],
                    'content' => $emailData['body_text'] ?: $emailData['body_html'],
                    'content_format' => $emailData['body_html'] ? 'html' : 'text',
                    'email_message_id' => $emailData['message_id'],
                    'email_from' => $emailData['from_address'],
                    'email_to' => $emailData['to_address'],
                    'email_cc' => $emailData['cc_address'],
                    'email_headers' => json_encode($emailData['headers'] ?? []),
                    'is_public' => 1
                ]);
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
} 