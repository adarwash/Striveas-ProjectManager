<?php

/**
 * Email Inbox Model
 * Handles database operations for email inbox functionality
 */
class EmailInboxModel {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }

    /**
     * Get all emails with filters and pagination
     * 
     * @param array $filters Filter criteria
     * @param int $page Page number
     * @param int $limit Records per page
     * @return array Emails data with pagination info
     */
    public function getAll($filters = [], $page = 1, $limit = 25) {
        try {
            $whereClause = "WHERE 1=1";
            $params = [];
            
            // Apply filters
            if (!empty($filters['status'])) {
                $whereClause .= " AND ei.processing_status = :status";
                $params['status'] = $filters['status'];
            }
            
            if (!empty($filters['search'])) {
                $whereClause .= " AND (ei.subject LIKE :search OR ei.from_address LIKE :search OR ei.to_address LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }
            
            if (!empty($filters['date_from'])) {
                $whereClause .= " AND ei.email_date >= :date_from";
                $params['date_from'] = $filters['date_from'] . ' 00:00:00';
            }
            
            if (!empty($filters['date_to'])) {
                $whereClause .= " AND ei.email_date <= :date_to";
                $params['date_to'] = $filters['date_to'] . ' 23:59:59';
            }
            
            if (isset($filters['has_ticket'])) {
                if ($filters['has_ticket'] === '1') {
                    $whereClause .= " AND ei.ticket_id IS NOT NULL";
                } else {
                    $whereClause .= " AND ei.ticket_id IS NULL";
                }
            }
            
            // Count total records
            $countQuery = "SELECT COUNT(*) as total FROM EmailInbox ei $whereClause";
            $countResult = $this->db->select($countQuery, $params);
            $total = $countResult[0]['total'] ?? 0;
            
            // Get paginated results
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT ei.*, 
                            t.ticket_number, t.subject as ticket_subject,
                            t.status_id as ticket_status_id
                     FROM EmailInbox ei
                     LEFT JOIN Tickets t ON ei.ticket_id = t.id
                     $whereClause
                     ORDER BY ei.email_date DESC
                     OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY";
            
            $params['offset'] = $offset;
            $params['limit'] = $limit;
            
            $emails = $this->db->select($query, $params);
            
            return [
                'emails' => $emails,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ];
        } catch (Exception $e) {
            error_log('EmailInbox GetAll Error: ' . $e->getMessage());
            return ['emails' => [], 'pagination' => ['current_page' => 1, 'per_page' => $limit, 'total' => 0, 'total_pages' => 0]];
        }
    }

    /**
     * Get email by ID
     * 
     * @param int $id Email ID
     * @return array|false Email data or false if not found
     */
    public function getById($id) {
        try {
            $query = "SELECT ei.*, 
                            t.ticket_number, t.subject as ticket_subject
                     FROM EmailInbox ei
                     LEFT JOIN Tickets t ON ei.ticket_id = t.id
                     WHERE ei.id = :id";
            
            $result = $this->db->select($query, ['id' => $id]);
            return $result[0] ?? false;
        } catch (Exception $e) {
            error_log('EmailInbox GetById Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get email statistics
     * 
     * @return array Various email statistics
     */
    public function getStatistics() {
        try {
            // Check if table exists first
            $checkQuery = "SELECT COUNT(*) as table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'EmailInbox'";
            $result = $this->db->select($checkQuery);
            
            if (empty($result) || $result[0]['table_count'] == 0) {
                return [
                    'total' => 0,
                    'pending' => 0,
                    'processed' => 0,
                    'error' => 0,
                    'ignored' => 0,
                    'with_tickets' => 0,
                    'without_tickets' => 0
                ];
            }
            
            $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN processing_status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN processing_status = 'processed' THEN 1 ELSE 0 END) as processed,
                SUM(CASE WHEN processing_status = 'error' THEN 1 ELSE 0 END) as error,
                SUM(CASE WHEN processing_status = 'ignored' THEN 1 ELSE 0 END) as ignored,
                SUM(CASE WHEN ticket_id IS NOT NULL THEN 1 ELSE 0 END) as with_tickets,
                SUM(CASE WHEN ticket_id IS NULL THEN 1 ELSE 0 END) as without_tickets
                FROM EmailInbox";
            
            $result = $this->db->select($query);
            return $result[0] ?? [];
        } catch (Exception $e) {
            error_log('EmailInbox GetStatistics Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Link email to ticket
     * 
     * @param int $emailId Email ID
     * @param int $ticketId Ticket ID
     * @return bool Success status
     */
    public function linkToTicket($emailId, $ticketId) {
        try {
            $query = "UPDATE EmailInbox 
                     SET ticket_id = :ticket_id, 
                         processing_status = 'processed',
                         processed_at = GETDATE()
                     WHERE id = :email_id";
            
            return $this->db->execute($query, [
                'ticket_id' => $ticketId,
                'email_id' => $emailId
            ]);
        } catch (Exception $e) {
            error_log('EmailInbox LinkToTicket Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update email processing status
     * 
     * @param int $emailId Email ID
     * @param string $status New status
     * @param string $error Error message (optional)
     * @return bool Success status
     */
    public function updateStatus($emailId, $status, $error = null) {
        try {
            $setClauses = ['processing_status = :status'];
            $params = ['email_id' => $emailId, 'status' => $status];
            
            if ($status === 'processed' || $status === 'ignored') {
                $setClauses[] = 'processed_at = GETDATE()';
            }
            
            if ($error !== null) {
                $setClauses[] = 'processing_error = :error';
                $params['error'] = $error;
            }
            
            $query = "UPDATE EmailInbox SET " . implode(', ', $setClauses) . " WHERE id = :email_id";
            return $this->db->execute($query, $params);
        } catch (Exception $e) {
            error_log('EmailInbox UpdateStatus Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get pending emails that need processing
     * 
     * @param int $limit Maximum emails to return
     * @return array Pending emails
     */
    public function getPendingEmails($limit = 50) {
        try {
            $query = "SELECT TOP :limit * FROM EmailInbox 
                     WHERE processing_status = 'pending'
                     ORDER BY email_date ASC";
            
            return $this->db->select($query, ['limit' => $limit]);
        } catch (Exception $e) {
            error_log('EmailInbox GetPendingEmails Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get emails by ticket ID
     * 
     * @param int $ticketId Ticket ID
     * @return array Emails linked to the ticket
     */
    public function getByTicketId($ticketId) {
        try {
            $query = "SELECT * FROM EmailInbox 
                     WHERE ticket_id = :ticket_id
                     ORDER BY email_date ASC";
            
            return $this->db->select($query, ['ticket_id' => $ticketId]);
        } catch (Exception $e) {
            error_log('EmailInbox GetByTicketId Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search emails by message ID
     * 
     * @param string $messageId Email message ID
     * @return array|false Email data or false if not found
     */
    public function getByMessageId($messageId) {
        try {
            $query = "SELECT * FROM EmailInbox WHERE message_id = :message_id";
            $result = $this->db->select($query, ['message_id' => $messageId]);
            return $result[0] ?? false;
        } catch (Exception $e) {
            error_log('EmailInbox GetByMessageId Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get emails from specific sender
     * 
     * @param string $fromAddress Sender email address
     * @param int $limit Maximum emails to return
     * @return array Emails from sender
     */
    public function getBySender($fromAddress, $limit = 20) {
        try {
            $query = "SELECT TOP :limit * FROM EmailInbox 
                     WHERE from_address = :from_address
                     ORDER BY email_date DESC";
            
            return $this->db->select($query, [
                'from_address' => $fromAddress,
                'limit' => $limit
            ]);
        } catch (Exception $e) {
            error_log('EmailInbox GetBySender Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent email activity
     * 
     * @param int $hours Hours to look back
     * @param int $limit Maximum emails to return
     * @return array Recent emails
     */
    public function getRecentActivity($hours = 24, $limit = 10) {
        try {
            $query = "SELECT TOP :limit ei.*, t.ticket_number
                     FROM EmailInbox ei
                     LEFT JOIN Tickets t ON ei.ticket_id = t.id
                     WHERE ei.received_at >= DATEADD(hour, -:hours, GETDATE())
                     ORDER BY ei.received_at DESC";
            
            return $this->db->select($query, [
                'hours' => $hours,
                'limit' => $limit
            ]);
        } catch (Exception $e) {
            error_log('EmailInbox GetRecentActivity Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete email by ID
     * 
     * @param int $emailId Email ID
     * @return bool Success status
     */
    public function delete($emailId) {
        try {
            $query = "DELETE FROM EmailInbox WHERE id = :email_id";
            return $this->db->execute($query, ['email_id' => $emailId]);
        } catch (Exception $e) {
            error_log('EmailInbox Delete Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk delete emails by IDs
     * 
     * @param array $emailIds Array of email IDs
     * @return int Number of emails deleted
     */
    public function bulkDelete($emailIds) {
        try {
            if (empty($emailIds)) {
                return 0;
            }
            
            $placeholders = implode(',', array_fill(0, count($emailIds), '?'));
            $query = "DELETE FROM EmailInbox WHERE id IN ($placeholders)";
            
            $success = $this->db->execute($query, $emailIds);
            return $success ? count($emailIds) : 0;
        } catch (Exception $e) {
            error_log('EmailInbox BulkDelete Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get emails that need escalation
     * 
     * @param int $hours Hours threshold for escalation
     * @return array Emails needing escalation
     */
    public function getEmailsNeedingEscalation($hours = 24) {
        try {
            $query = "SELECT ei.*, t.ticket_number
                     FROM EmailInbox ei
                     LEFT JOIN Tickets t ON ei.ticket_id = t.id
                     WHERE ei.processing_status = 'pending'
                     AND ei.received_at <= DATEADD(hour, -:hours, GETDATE())
                     ORDER BY ei.received_at ASC";
            
            return $this->db->select($query, ['hours' => $hours]);
        } catch (Exception $e) {
            error_log('EmailInbox GetEmailsNeedingEscalation Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create new email record
     * 
     * @param array $emailData Email data
     * @return int|false Email ID or false on failure
     */
    public function create($emailData) {
        try {
            $query = "INSERT INTO EmailInbox (
                message_id, subject, from_address, to_address, cc_address, bcc_address,
                reply_to, body_text, body_html, raw_headers, email_date, uid_validity, uid, flags
            ) VALUES (
                :message_id, :subject, :from_address, :to_address, :cc_address, :bcc_address,
                :reply_to, :body_text, :body_html, :raw_headers, :email_date, :uid_validity, :uid, :flags
            )";
            
            $params = [
                'message_id' => $emailData['message_id'],
                'subject' => $emailData['subject'],
                'from_address' => $emailData['from_address'],
                'to_address' => $emailData['to_address'],
                'cc_address' => $emailData['cc_address'] ?? null,
                'bcc_address' => $emailData['bcc_address'] ?? null,
                'reply_to' => $emailData['reply_to'] ?? null,
                'body_text' => $emailData['body_text'] ?? null,
                'body_html' => $emailData['body_html'] ?? null,
                'raw_headers' => $emailData['raw_headers'] ?? null,
                'email_date' => $emailData['email_date'],
                'uid_validity' => $emailData['uid_validity'] ?? null,
                'uid' => $emailData['uid'] ?? null,
                'flags' => $emailData['flags'] ?? null
            ];
            
            return $this->db->insert($query, $params);
        } catch (Exception $e) {
            error_log('EmailInbox Create Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get duplicate emails by message ID
     * 
     * @param string $messageId Message ID to check
     * @return bool True if duplicate exists
     */
    public function isDuplicate($messageId) {
        try {
            $query = "SELECT COUNT(*) as count FROM EmailInbox WHERE message_id = :message_id";
            $result = $this->db->select($query, ['message_id' => $messageId]);
            return ($result[0]['count'] ?? 0) > 0;
        } catch (Exception $e) {
            error_log('EmailInbox IsDuplicate Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get email thread by subject or message ID references
     * 
     * @param string $subject Email subject
     * @param string $messageId Message ID
     * @return array Email thread
     */
    public function getEmailThread($subject, $messageId) {
        try {
            // Clean subject of "Re:" and "Fwd:" prefixes
            $cleanSubject = preg_replace('/^(Re|Fwd?):\s*/i', '', $subject);
            
            $query = "SELECT * FROM EmailInbox 
                     WHERE (
                         subject LIKE :subject1 OR 
                         subject LIKE :subject2 OR
                         raw_headers LIKE :message_id_ref
                     )
                     ORDER BY email_date ASC";
            
            return $this->db->select($query, [
                'subject1' => '%' . $cleanSubject . '%',
                'subject2' => 'Re: %' . $cleanSubject . '%',
                'message_id_ref' => '%' . $messageId . '%'
            ]);
        } catch (Exception $e) {
            error_log('EmailInbox GetEmailThread Error: ' . $e->getMessage());
            return [];
        }
    }
}