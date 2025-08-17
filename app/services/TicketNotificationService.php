<?php

/**
 * Ticket Notification Service
 * Handles all email notifications related to ticket events
 */
class TicketNotificationService {
    private $db;
    private $emailService;
    private $config;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
        require_once APPROOT . '/app/services/EmailService.php';
        $this->emailService = new EmailService();
        $this->config = $this->loadNotificationConfig();
    }

    /**
     * Load notification configuration from database
     */
    private function loadNotificationConfig() {
        try {
            $query = "SELECT setting_key, setting_value FROM Settings WHERE setting_key LIKE 'notification_%'";
            $settings = $this->db->select($query);
            
            $config = [
                'enabled' => true,
                'notify_on_create' => true,
                'notify_on_assign' => true,
                'notify_on_update' => true,
                'notify_on_resolve' => true,
                'notify_on_close' => false,
                'notify_watchers' => true,
                'notify_creator' => true,
                'notify_assignee' => true,
                'daily_digest' => false,
                'escalation_hours' => 24,
                'escalation_enabled' => false
            ];
            
            // Override with database settings
            foreach ($settings as $setting) {
                $config[$setting['setting_key']] = $setting['setting_value'];
            }
            
            return $config;
        } catch (Exception $e) {
            error_log('NotificationService Config Error: ' . $e->getMessage());
            return ['enabled' => true]; // Default fallback
        }
    }

    /**
     * Send notification for ticket creation
     */
    public function notifyTicketCreated($ticketId) {
        if (!$this->config['notify_on_create']) {
            return true;
        }
        
        try {
            $ticket = $this->getTicketData($ticketId);
            if (!$ticket) return false;
            
            $recipients = $this->getNotificationRecipients($ticket, 'create');
            $sent = 0;
            
            foreach ($recipients as $recipient) {
                $emailData = $this->buildTicketCreatedEmail($ticket, $recipient);
                if ($this->emailService->queueEmail($emailData, 5)) {
                    $sent++;
                }
            }
            
            $this->logNotification($ticketId, 'ticket_created', $sent . ' notifications queued');
            return $sent > 0;
        } catch (Exception $e) {
            error_log('NotifyTicketCreated Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification for ticket assignment
     */
    public function notifyTicketAssigned($ticketId, $assigneeId = null, $assignedBy = null) {
        if (!$this->config['notify_on_assign']) {
            return true;
        }
        
        try {
            $ticket = $this->getTicketData($ticketId);
            if (!$ticket) return false;
            
            $recipients = $this->getNotificationRecipients($ticket, 'assign', $assigneeId);
            $sent = 0;
            
            foreach ($recipients as $recipient) {
                $emailData = $this->buildTicketAssignedEmail($ticket, $recipient, $assignedBy);
                if ($this->emailService->queueEmail($emailData, 4)) {
                    $sent++;
                }
            }
            
            $this->logNotification($ticketId, 'ticket_assigned', $sent . ' notifications queued');
            return $sent > 0;
        } catch (Exception $e) {
            error_log('NotifyTicketAssigned Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification for ticket updates
     */
    public function notifyTicketUpdated($ticketId, $updateMessage, $updatedBy = null) {
        if (!$this->config['notify_on_update']) {
            return true;
        }
        
        try {
            $ticket = $this->getTicketData($ticketId);
            if (!$ticket) return false;
            
            $recipients = $this->getNotificationRecipients($ticket, 'update', null, $updatedBy);
            $sent = 0;
            
            foreach ($recipients as $recipient) {
                $emailData = $this->buildTicketUpdatedEmail($ticket, $recipient, $updateMessage, $updatedBy);
                if ($this->emailService->queueEmail($emailData, 5)) {
                    $sent++;
                }
            }
            
            $this->logNotification($ticketId, 'ticket_updated', $sent . ' notifications queued');
            return $sent > 0;
        } catch (Exception $e) {
            error_log('NotifyTicketUpdated Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification for ticket resolution
     */
    public function notifyTicketResolved($ticketId, $resolution = null, $resolvedBy = null) {
        if (!$this->config['notify_on_resolve']) {
            return true;
        }
        
        try {
            $ticket = $this->getTicketData($ticketId);
            if (!$ticket) return false;
            
            $recipients = $this->getNotificationRecipients($ticket, 'resolve');
            $sent = 0;
            
            foreach ($recipients as $recipient) {
                $emailData = $this->buildTicketResolvedEmail($ticket, $recipient, $resolution, $resolvedBy);
                if ($this->emailService->queueEmail($emailData, 3)) {
                    $sent++;
                }
            }
            
            $this->logNotification($ticketId, 'ticket_resolved', $sent . ' notifications queued');
            return $sent > 0;
        } catch (Exception $e) {
            error_log('NotifyTicketResolved Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send escalation notification for overdue tickets
     */
    public function notifyTicketEscalation($ticketId, $escalationLevel = 1) {
        if (!$this->config['escalation_enabled']) {
            return true;
        }
        
        try {
            $ticket = $this->getTicketData($ticketId);
            if (!$ticket) return false;
            
            $recipients = $this->getEscalationRecipients($ticket, $escalationLevel);
            $sent = 0;
            
            foreach ($recipients as $recipient) {
                $emailData = $this->buildEscalationEmail($ticket, $recipient, $escalationLevel);
                if ($this->emailService->queueEmail($emailData, 2)) {
                    $sent++;
                }
            }
            
            $this->logNotification($ticketId, 'ticket_escalation', "Level $escalationLevel escalation - $sent notifications queued");
            return $sent > 0;
        } catch (Exception $e) {
            error_log('NotifyTicketEscalation Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send daily digest emails
     */
    public function sendDailyDigest() {
        if (!$this->config['daily_digest']) {
            return true;
        }
        
        try {
            $users = $this->getUsersForDigest();
            $sent = 0;
            
            foreach ($users as $user) {
                $digestData = $this->buildUserDigest($user);
                if (!empty($digestData['tickets'])) {
                    $emailData = $this->buildDigestEmail($user, $digestData);
                    if ($this->emailService->queueEmail($emailData, 7)) {
                        $sent++;
                    }
                }
            }
            
            error_log("Daily digest: $sent digest emails queued");
            return $sent > 0;
        } catch (Exception $e) {
            error_log('SendDailyDigest Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Process escalations for overdue tickets
     */
    public function processEscalations() {
        if (!$this->config['escalation_enabled']) {
            return 0;
        }
        
        try {
            $escalationHours = (int) $this->config['escalation_hours'];
            
            $query = "SELECT t.*, td.* FROM Tickets t
                     LEFT JOIN TicketDashboard td ON t.id = td.id
                     WHERE td.is_closed = 0 
                     AND DATEDIFF(hour, t.created_at, GETDATE()) > :hours
                     AND (t.last_escalation IS NULL OR DATEDIFF(hour, t.last_escalation, GETDATE()) > :hours)";
            
            $overdueTickets = $this->db->select($query, ['hours' => $escalationHours]);
            $processed = 0;
            
            foreach ($overdueTickets as $ticket) {
                // Determine escalation level
                $level = $this->calculateEscalationLevel($ticket);
                
                if ($this->notifyTicketEscalation($ticket['id'], $level)) {
                    // Update last escalation time
                    $this->db->execute(
                        "UPDATE Tickets SET last_escalation = GETDATE() WHERE id = :id",
                        ['id' => $ticket['id']]
                    );
                    $processed++;
                }
            }
            
            return $processed;
        } catch (Exception $e) {
            error_log('ProcessEscalations Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get ticket data with all necessary information
     */
    private function getTicketData($ticketId) {
        try {
            $query = "SELECT * FROM TicketDashboard WHERE id = :id";
            $result = $this->db->select($query, ['id' => $ticketId]);
            return $result[0] ?? null;
        } catch (Exception $e) {
            error_log('GetTicketData Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get notification recipients based on event type
     */
    private function getNotificationRecipients($ticket, $eventType, $specificUserId = null, $excludeUserId = null) {
        $recipients = [];
        
        try {
            // Always include assigned user (unless they're the one making the change)
            if ($ticket['assigned_to'] && $ticket['assigned_to'] != $excludeUserId) {
                if ($this->config['notify_assignee']) {
                    $assignee = $this->getUserData($ticket['assigned_to']);
                    if ($assignee && $assignee['email']) {
                        $recipients[] = $assignee;
                    }
                }
            }
            
            // Include creator (unless they're the one making the change)
            if ($ticket['created_by'] && $ticket['created_by'] != $excludeUserId) {
                if ($this->config['notify_creator']) {
                    $creator = $this->getUserData($ticket['created_by']);
                    if ($creator && $creator['email'] && !$this->userInRecipients($creator, $recipients)) {
                        $recipients[] = $creator;
                    }
                }
            }
            
            // Include watchers if enabled
            if ($this->config['notify_watchers']) {
                $watchers = $this->getTicketWatchers($ticket['id'], $excludeUserId);
                foreach ($watchers as $watcher) {
                    if (!$this->userInRecipients($watcher, $recipients)) {
                        $recipients[] = $watcher;
                    }
                }
            }
            
            // For specific assignment notifications
            if ($eventType === 'assign' && $specificUserId && $specificUserId != $excludeUserId) {
                $assignee = $this->getUserData($specificUserId);
                if ($assignee && $assignee['email'] && !$this->userInRecipients($assignee, $recipients)) {
                    $recipients[] = $assignee;
                }
            }
            
            // Filter recipients based on notification preferences
            return $this->filterByNotificationPreferences($recipients, $eventType);
        } catch (Exception $e) {
            error_log('GetNotificationRecipients Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get escalation recipients (managers, admins)
     */
    private function getEscalationRecipients($ticket, $level) {
        try {
            // Get users with escalation permissions
            $query = "SELECT DISTINCT u.id, u.email, u.full_name, u.username
                     FROM Users u
                     WHERE u.role IN ('admin', 'manager', 'super_admin')
                     AND u.email IS NOT NULL AND u.email != ''
                     AND u.is_active = 1";
            
            return $this->db->select($query);
        } catch (Exception $e) {
            error_log('GetEscalationRecipients Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get ticket watchers
     */
    private function getTicketWatchers($ticketId, $excludeUserId = null) {
        try {
            $whereClause = "ta.ticket_id = :ticket_id AND ta.role = 'watcher' AND ta.removed_at IS NULL";
            $params = ['ticket_id' => $ticketId];
            
            if ($excludeUserId) {
                $whereClause .= " AND ta.user_id != :exclude_user";
                $params['exclude_user'] = $excludeUserId;
            }
            
            $query = "SELECT u.id, u.email, u.full_name, u.username
                     FROM TicketAssignments ta
                     JOIN Users u ON ta.user_id = u.id
                     WHERE $whereClause
                     AND u.email IS NOT NULL AND u.email != ''";
            
            return $this->db->select($query, $params);
        } catch (Exception $e) {
            error_log('GetTicketWatchers Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user data by ID
     */
    private function getUserData($userId) {
        try {
            $query = "SELECT id, email, full_name, username FROM Users WHERE id = :id";
            $result = $this->db->select($query, ['id' => $userId]);
            return $result[0] ?? null;
        } catch (Exception $e) {
            error_log('GetUserData Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if user is already in recipients list
     */
    private function userInRecipients($user, $recipients) {
        foreach ($recipients as $recipient) {
            if ($recipient['id'] == $user['id']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Filter recipients by notification preferences
     */
    private function filterByNotificationPreferences($recipients, $eventType) {
        // This could be extended to check user-specific notification preferences
        // For now, return all recipients
        return $recipients;
    }

    /**
     * Build email data for ticket creation
     */
    private function buildTicketCreatedEmail($ticket, $recipient) {
        $subject = '[' . $ticket['ticket_number'] . '] New Ticket: ' . $ticket['subject'];
        
        $body = $this->renderEmailTemplate('ticket_created', [
            'recipient_name' => $recipient['full_name'] ?? $recipient['username'],
            'ticket' => $ticket,
            'ticket_url' => URLROOT . '/tickets/view/' . $ticket['id']
        ]);
        
        return [
            'to' => $recipient['email'],
            'subject' => $subject,
            'html_body' => $body,
            'ticket_id' => $ticket['id'],
            'template' => 'ticket_created'
        ];
    }

    /**
     * Build email data for ticket assignment
     */
    private function buildTicketAssignedEmail($ticket, $recipient, $assignedBy) {
        $subject = '[' . $ticket['ticket_number'] . '] Ticket Assigned: ' . $ticket['subject'];
        
        $assignedByData = $assignedBy ? $this->getUserData($assignedBy) : null;
        $assignedByName = $assignedByData ? ($assignedByData['full_name'] ?? $assignedByData['username']) : 'System';
        
        $body = $this->renderEmailTemplate('ticket_assigned', [
            'recipient_name' => $recipient['full_name'] ?? $recipient['username'],
            'ticket' => $ticket,
            'assigned_by' => $assignedByName,
            'ticket_url' => URLROOT . '/tickets/view/' . $ticket['id']
        ]);
        
        return [
            'to' => $recipient['email'],
            'subject' => $subject,
            'html_body' => $body,
            'ticket_id' => $ticket['id'],
            'template' => 'ticket_assigned'
        ];
    }

    /**
     * Build email data for ticket updates
     */
    private function buildTicketUpdatedEmail($ticket, $recipient, $updateMessage, $updatedBy) {
        $subject = '[' . $ticket['ticket_number'] . '] Ticket Updated: ' . $ticket['subject'];
        
        $updatedByData = $updatedBy ? $this->getUserData($updatedBy) : null;
        $updatedByName = $updatedByData ? ($updatedByData['full_name'] ?? $updatedByData['username']) : 'System';
        
        $body = $this->renderEmailTemplate('ticket_updated', [
            'recipient_name' => $recipient['full_name'] ?? $recipient['username'],
            'ticket' => $ticket,
            'update_message' => $updateMessage,
            'updated_by' => $updatedByName,
            'ticket_url' => URLROOT . '/tickets/view/' . $ticket['id']
        ]);
        
        return [
            'to' => $recipient['email'],
            'subject' => $subject,
            'html_body' => $body,
            'ticket_id' => $ticket['id'],
            'template' => 'ticket_updated'
        ];
    }

    /**
     * Build email data for ticket resolution
     */
    private function buildTicketResolvedEmail($ticket, $recipient, $resolution, $resolvedBy) {
        $subject = '[' . $ticket['ticket_number'] . '] Ticket Resolved: ' . $ticket['subject'];
        
        $resolvedByData = $resolvedBy ? $this->getUserData($resolvedBy) : null;
        $resolvedByName = $resolvedByData ? ($resolvedByData['full_name'] ?? $resolvedByData['username']) : 'System';
        
        $body = $this->renderEmailTemplate('ticket_resolved', [
            'recipient_name' => $recipient['full_name'] ?? $recipient['username'],
            'ticket' => $ticket,
            'resolution' => $resolution,
            'resolved_by' => $resolvedByName,
            'ticket_url' => URLROOT . '/tickets/view/' . $ticket['id']
        ]);
        
        return [
            'to' => $recipient['email'],
            'subject' => $subject,
            'html_body' => $body,
            'ticket_id' => $ticket['id'],
            'template' => 'ticket_resolved'
        ];
    }

    /**
     * Build escalation email
     */
    private function buildEscalationEmail($ticket, $recipient, $level) {
        $subject = '[ESCALATION Level ' . $level . '] [' . $ticket['ticket_number'] . '] ' . $ticket['subject'];
        
        $body = $this->renderEmailTemplate('ticket_escalation', [
            'recipient_name' => $recipient['full_name'] ?? $recipient['username'],
            'ticket' => $ticket,
            'escalation_level' => $level,
            'ticket_url' => URLROOT . '/tickets/view/' . $ticket['id']
        ]);
        
        return [
            'to' => $recipient['email'],
            'subject' => $subject,
            'html_body' => $body,
            'ticket_id' => $ticket['id'],
            'template' => 'ticket_escalation'
        ];
    }

    /**
     * Simple email template renderer
     */
    private function renderEmailTemplate($template, $data) {
        $baseTemplate = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>{subject}</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
                .content { background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; }
                .footer { background: #e9ecef; padding: 15px; border-radius: 0 0 5px 5px; text-align: center; font-size: 12px; }
                .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
                .ticket-info { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #007bff; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>" . SITENAME . " - Ticket Notification</h2>
                </div>
                <div class='content'>
                    {content}
                </div>
                <div class='footer'>
                    <p>This is an automated notification from " . SITENAME . ". Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>";
        
        switch ($template) {
            case 'ticket_created':
                $content = "
                <p>Hello {$data['recipient_name']},</p>
                <p>A new ticket has been created:</p>
                <div class='ticket-info'>
                    <strong>Ticket:</strong> {$data['ticket']['ticket_number']}<br>
                    <strong>Subject:</strong> {$data['ticket']['subject']}<br>
                    <strong>Priority:</strong> {$data['ticket']['priority_display']}<br>
                    <strong>Created by:</strong> {$data['ticket']['created_by_name']}<br>
                    <strong>Created:</strong> " . date('M j, Y g:i A', strtotime($data['ticket']['created_at'])) . "
                </div>
                <p><a href='{$data['ticket_url']}' class='btn'>View Ticket</a></p>";
                break;
                
            case 'ticket_assigned':
                $content = "
                <p>Hello {$data['recipient_name']},</p>
                <p>A ticket has been assigned to you by {$data['assigned_by']}:</p>
                <div class='ticket-info'>
                    <strong>Ticket:</strong> {$data['ticket']['ticket_number']}<br>
                    <strong>Subject:</strong> {$data['ticket']['subject']}<br>
                    <strong>Priority:</strong> {$data['ticket']['priority_display']}<br>
                    <strong>Due Date:</strong> " . ($data['ticket']['due_date'] ? date('M j, Y', strtotime($data['ticket']['due_date'])) : 'Not set') . "
                </div>
                <p><a href='{$data['ticket_url']}' class='btn'>View Ticket</a></p>";
                break;
                
            case 'ticket_updated':
                $content = "
                <p>Hello {$data['recipient_name']},</p>
                <p>Ticket {$data['ticket']['ticket_number']} has been updated by {$data['updated_by']}:</p>
                <div class='ticket-info'>
                    <strong>Subject:</strong> {$data['ticket']['subject']}<br>
                    <strong>Update:</strong><br>" . nl2br(htmlspecialchars($data['update_message'])) . "
                </div>
                <p><a href='{$data['ticket_url']}' class='btn'>View Ticket</a></p>";
                break;
                
            case 'ticket_resolved':
                $content = "
                <p>Hello {$data['recipient_name']},</p>
                <p>Ticket {$data['ticket']['ticket_number']} has been resolved by {$data['resolved_by']}:</p>
                <div class='ticket-info'>
                    <strong>Subject:</strong> {$data['ticket']['subject']}<br>
                    <strong>Resolution:</strong><br>" . nl2br(htmlspecialchars($data['resolution'] ?? 'Ticket marked as resolved.')) . "
                </div>
                <p><a href='{$data['ticket_url']}' class='btn'>View Ticket</a></p>";
                break;
                
            case 'ticket_escalation':
                $content = "
                <p>Hello {$data['recipient_name']},</p>
                <p><strong>ESCALATION NOTICE:</strong> Ticket {$data['ticket']['ticket_number']} requires attention (Level {$data['escalation_level']}):</p>
                <div class='ticket-info' style='border-left-color: #dc3545;'>
                    <strong>Subject:</strong> {$data['ticket']['subject']}<br>
                    <strong>Priority:</strong> {$data['ticket']['priority_display']}<br>
                    <strong>Age:</strong> {$data['ticket']['age_hours']} hours<br>
                    <strong>Status:</strong> {$data['ticket']['status_display']}
                </div>
                <p><a href='{$data['ticket_url']}' class='btn' style='background: #dc3545;'>Take Action</a></p>";
                break;
                
            default:
                $content = "<p>Ticket notification</p>";
        }
        
        return str_replace('{content}', $content, $baseTemplate);
    }

    /**
     * Calculate escalation level based on ticket age and priority
     */
    private function calculateEscalationLevel($ticket) {
        $hours = $ticket['age_hours'];
        $priority = $ticket['priority_level'];
        
        // Higher priority tickets escalate faster
        $multiplier = (6 - $priority) * 0.5; // Higher priority = lower multiplier
        
        if ($hours > (48 * $multiplier)) return 3; // Critical
        if ($hours > (24 * $multiplier)) return 2; // High
        return 1; // Standard
    }

    /**
     * Log notification events
     */
    private function logNotification($ticketId, $eventType, $details) {
        try {
            $query = "INSERT INTO TicketMessages (ticket_id, message_type, content, is_system_message, is_public)
                     VALUES (:ticket_id, 'system', :content, 1, 0)";
            
            $this->db->execute($query, [
                'ticket_id' => $ticketId,
                'content' => "Notification: $eventType - $details"
            ]);
        } catch (Exception $e) {
            error_log('LogNotification Error: ' . $e->getMessage());
        }
    }

    /**
     * Get users for daily digest
     */
    private function getUsersForDigest() {
        try {
            // Get users who have assigned tickets or are managers/admins
            $query = "SELECT DISTINCT u.id, u.email, u.full_name, u.username
                     FROM Users u
                     WHERE u.email IS NOT NULL AND u.email != ''
                     AND (
                         EXISTS (SELECT 1 FROM Tickets t WHERE t.assigned_to = u.id AND t.status_id IN (1,2,3,4))
                         OR u.role IN ('admin', 'manager', 'super_admin')
                     )";
            
            return $this->db->select($query);
        } catch (Exception $e) {
            error_log('GetUsersForDigest Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Build digest data for user
     */
    private function buildUserDigest($user) {
        try {
            // Get user's assigned open tickets
            $query = "SELECT * FROM TicketDashboard 
                     WHERE assigned_to = :user_id AND is_closed = 0
                     ORDER BY priority_level DESC, created_at ASC";
            
            $assignedTickets = $this->db->select($query, ['user_id' => $user['id']]);
            
            // Get recent activity on user's tickets
            $activityQuery = "SELECT tm.*, t.ticket_number, t.subject
                             FROM TicketMessages tm
                             JOIN Tickets t ON tm.ticket_id = t.id
                             WHERE t.assigned_to = :user_id
                             AND tm.created_at >= DATEADD(day, -1, GETDATE())
                             ORDER BY tm.created_at DESC";
            
            $recentActivity = $this->db->select($activityQuery, ['user_id' => $user['id']]);
            
            return [
                'tickets' => $assignedTickets,
                'activity' => $recentActivity
            ];
        } catch (Exception $e) {
            error_log('BuildUserDigest Error: ' . $e->getMessage());
            return ['tickets' => [], 'activity' => []];
        }
    }

    /**
     * Build digest email
     */
    private function buildDigestEmail($user, $digestData) {
        $subject = '[' . SITENAME . '] Daily Ticket Digest - ' . date('M j, Y');
        
        $body = $this->renderEmailTemplate('daily_digest', [
            'recipient_name' => $user['full_name'] ?? $user['username'],
            'assigned_tickets' => $digestData['tickets'],
            'recent_activity' => $digestData['activity'],
            'digest_date' => date('M j, Y')
        ]);
        
        return [
            'to' => $user['email'],
            'subject' => $subject,
            'html_body' => $body,
            'template' => 'daily_digest'
        ];
    }
}