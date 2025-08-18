<?php

/**
 * SLA Service for Ticket System
 * Handles SLA calculations, deadline tracking, and breach detection
 */
class SLAService {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }
    
    /**
     * Calculate SLA deadlines for a ticket based on its priority
     */
    public function calculateSLADeadlines($ticketId) {
        try {
            // Get ticket with priority information
            $ticket = $this->db->select(
                "SELECT t.*, tp.response_time_hours, tp.resolution_time_hours 
                 FROM Tickets t 
                 JOIN TicketPriorities tp ON t.priority_id = tp.id 
                 WHERE t.id = :ticket_id",
                ['ticket_id' => $ticketId]
            );
            
            if (empty($ticket)) {
                throw new Exception('Ticket not found');
            }
            
            $ticket = $ticket[0];
            $createdAt = new DateTime($ticket['created_at']);
            
            // Calculate response deadline (first response)
            $responseDeadline = clone $createdAt;
            $responseDeadline->add(new DateInterval('PT' . $ticket['response_time_hours'] . 'H'));
            
            // Calculate resolution deadline
            $resolutionDeadline = clone $createdAt;
            $resolutionDeadline->add(new DateInterval('PT' . $ticket['resolution_time_hours'] . 'H'));
            
            // Update ticket with SLA deadlines
            $this->db->update(
                "UPDATE Tickets SET 
                 sla_response_deadline = :response_deadline,
                 sla_resolution_deadline = :resolution_deadline
                 WHERE id = :ticket_id",
                [
                    'response_deadline' => $responseDeadline->format('Y-m-d H:i:s'),
                    'resolution_deadline' => $resolutionDeadline->format('Y-m-d H:i:s'),
                    'ticket_id' => $ticketId
                ]
            );
            
            return [
                'response_deadline' => $responseDeadline->format('Y-m-d H:i:s'),
                'resolution_deadline' => $resolutionDeadline->format('Y-m-d H:i:s'),
                'response_hours' => $ticket['response_time_hours'],
                'resolution_hours' => $ticket['resolution_time_hours']
            ];
            
        } catch (Exception $e) {
            error_log('SLA Service - Calculate deadlines error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Check if SLA deadlines have been breached
     */
    public function checkSLABreaches($ticketId) {
        try {
            $now = new DateTime();
            $breaches = [];
            
            // Get ticket with current SLA status
            $ticket = $this->db->select(
                "SELECT * FROM Tickets WHERE id = :ticket_id",
                ['ticket_id' => $ticketId]
            );
            
            if (empty($ticket)) {
                throw new Exception('Ticket not found');
            }
            
            $ticket = $ticket[0];
            
            // Check response SLA breach
            if (!empty($ticket['sla_response_deadline']) && !$ticket['sla_response_breached']) {
                $responseDeadline = new DateTime($ticket['sla_response_deadline']);
                
                if ($now > $responseDeadline && empty($ticket['first_response_at'])) {
                    $this->logSLABreach($ticketId, 'response', $responseDeadline, $now);
                    $this->markResponseBreached($ticketId, $now);
                    $breaches[] = 'response';
                }
            }
            
            // Check resolution SLA breach
            if (!empty($ticket['sla_resolution_deadline']) && !$ticket['sla_resolution_breached']) {
                $resolutionDeadline = new DateTime($ticket['sla_resolution_deadline']);
                
                if ($now > $resolutionDeadline && empty($ticket['resolved_at'])) {
                    $this->logSLABreach($ticketId, 'resolution', $resolutionDeadline, $now);
                    $this->markResolutionBreached($ticketId, $now);
                    $breaches[] = 'resolution';
                }
            }
            
            return $breaches;
            
        } catch (Exception $e) {
            error_log('SLA Service - Check breaches error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Log an SLA breach
     */
    private function logSLABreach($ticketId, $breachType, $originalDeadline, $actualTime) {
        $hoursOverdue = ($actualTime->getTimestamp() - $originalDeadline->getTimestamp()) / 3600;
        
        $this->db->insert(
            "INSERT INTO SLABreachLog (ticket_id, breach_type, breached_at, original_deadline, actual_time, hours_overdue) 
             VALUES (:ticket_id, :breach_type, :breached_at, :original_deadline, :actual_time, :hours_overdue)",
            [
                'ticket_id' => $ticketId,
                'breach_type' => $breachType,
                'breached_at' => $actualTime->format('Y-m-d H:i:s'),
                'original_deadline' => $originalDeadline->format('Y-m-d H:i:s'),
                'actual_time' => $actualTime->format('Y-m-d H:i:s'),
                'hours_overdue' => round($hoursOverdue, 2)
            ]
        );
    }
    
    /**
     * Mark response SLA as breached
     */
    private function markResponseBreached($ticketId, $breachedAt) {
        $this->db->update(
            "UPDATE Tickets SET 
             sla_response_breached = 1,
             sla_response_breached_at = :breached_at
             WHERE id = :ticket_id",
            [
                'breached_at' => $breachedAt->format('Y-m-d H:i:s'),
                'ticket_id' => $ticketId
            ]
        );
    }
    
    /**
     * Mark resolution SLA as breached
     */
    private function markResolutionBreached($ticketId, $breachedAt) {
        $this->db->update(
            "UPDATE Tickets SET 
             sla_resolution_breached = 1,
             sla_resolution_breached_at = :breached_at
             WHERE id = :ticket_id",
            [
                'breached_at' => $breachedAt->format('Y-m-d H:i:s'),
                'ticket_id' => $ticketId
            ]
        );
    }
    
    /**
     * Get SLA status for a ticket
     */
    public function getSLAStatus($ticketId) {
        try {
            $ticket = $this->db->select(
                "SELECT t.*, tp.name as priority_name, tp.response_time_hours, tp.resolution_time_hours
                 FROM Tickets t 
                 JOIN TicketPriorities tp ON t.priority_id = tp.id 
                 WHERE t.id = :ticket_id",
                ['ticket_id' => $ticketId]
            );
            
            if (empty($ticket)) {
                throw new Exception('Ticket not found');
            }
            
            $ticket = $ticket[0];
            $now = new DateTime();
            
            $status = [
                'priority' => $ticket['priority_name'],
                'response_hours' => $ticket['response_time_hours'],
                'resolution_hours' => $ticket['resolution_time_hours'],
                'response_deadline' => $ticket['sla_response_deadline'],
                'resolution_deadline' => $ticket['sla_resolution_deadline'],
                'response_breached' => (bool)$ticket['sla_response_breached'],
                'resolution_breached' => (bool)$ticket['sla_resolution_breached'],
                'first_response_at' => $ticket['first_response_at'],
                'resolved_at' => $ticket['resolved_at']
            ];
            
            // Calculate time remaining
            if (!empty($ticket['sla_response_deadline']) && empty($ticket['first_response_at'])) {
                $responseDeadline = new DateTime($ticket['sla_response_deadline']);
                $status['response_time_remaining'] = $responseDeadline->getTimestamp() - $now->getTimestamp();
                $status['response_time_remaining_hours'] = round($status['response_time_remaining'] / 3600, 1);
            }
            
            if (!empty($ticket['sla_resolution_deadline']) && empty($ticket['resolved_at'])) {
                $resolutionDeadline = new DateTime($ticket['sla_resolution_deadline']);
                $status['resolution_time_remaining'] = $resolutionDeadline->getTimestamp() - $now->getTimestamp();
                $status['resolution_time_remaining_hours'] = round($status['resolution_time_remaining'] / 3600, 1);
            }
            
            return $status;
            
        } catch (Exception $e) {
            error_log('SLA Service - Get status error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update SLA deadlines when priority changes
     */
    public function updateSLADeadlines($ticketId) {
        return $this->calculateSLADeadlines($ticketId);
    }
    
    /**
     * Get SLA breach history for a ticket
     */
    public function getSLABreachHistory($ticketId) {
        return $this->db->select(
            "SELECT * FROM SLABreachLog WHERE ticket_id = :ticket_id ORDER BY breached_at DESC",
            ['ticket_id' => $ticketId]
        );
    }
}
