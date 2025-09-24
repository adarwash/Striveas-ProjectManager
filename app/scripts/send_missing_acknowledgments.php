<?php
/**
 * Script to send missing acknowledgment emails for tickets created from emails
 * Run this to ensure all email-created tickets have acknowledgments sent
 */

// Bootstrap the application
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/EasySQL.php';
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../services/EmailService.php';

$db = new EasySQL(DB1);

echo "=== Missing Acknowledgment Email Sender ===\n";
echo "Checking for tickets created from email without acknowledgments...\n\n";

try {
    // Check if auto acknowledgment is enabled
    $settingModel = new Setting();
    $autoAcknowledge = $settingModel->get('auto_acknowledge_tickets', true);
    
    if (!$autoAcknowledge) {
        echo "Auto acknowledgment is disabled in settings. Enable it first.\n";
        exit(1);
    }
    
    // Find tickets created from email in the last 7 days that don't have acknowledgment emails
    $query = "
        SELECT t.id, t.ticket_number, t.subject, t.inbound_email_address, t.created_at
        FROM Tickets t
        WHERE t.source = 'email' 
        AND t.inbound_email_address IS NOT NULL
        AND t.inbound_email_address != ''
        AND t.created_at >= DATEADD(day, -7, GETDATE())
        AND NOT EXISTS (
            SELECT 1 FROM EmailQueue eq 
            WHERE eq.ticket_id = t.id 
            AND eq.subject LIKE '%Thank you%'
            AND eq.to_address = t.inbound_email_address
        )
        ORDER BY t.created_at DESC
    ";
    
    $tickets = $db->select($query);
    
    if (empty($tickets)) {
        echo "No tickets found that need acknowledgment emails.\n";
        exit(0);
    }
    
    echo "Found " . count($tickets) . " tickets without acknowledgment emails.\n\n";
    
    $ticketModel = new Ticket();
    $emailService = new EmailService();
    $sent = 0;
    $failed = 0;
    
    foreach ($tickets as $ticket) {
        echo "Processing ticket {$ticket['ticket_number']}...\n";
        echo "  - Subject: {$ticket['subject']}\n";
        echo "  - Email: {$ticket['inbound_email_address']}\n";
        echo "  - Created: {$ticket['created_at']}\n";
        
        // Extract customer name from email
        $requesterName = strstr($ticket['inbound_email_address'], '@', true);
        $requesterName = ucwords(str_replace(['.', '_', '-'], ' ', $requesterName));
        
        // Get ticket details for priority
        $fullTicket = $ticketModel->getById($ticket['id']);
        $priorityNames = [
            1 => 'Low',
            2 => 'Normal', 
            3 => 'High',
            4 => 'Critical'
        ];
        $priorityDisplay = $priorityNames[$fullTicket['priority_id']] ?? 'Normal';
        
        // Create acknowledgment email
        $emailData = $emailService->createTicketEmail('ticket_acknowledgment', [
            'ticket_id' => $ticket['id'],
            'ticket_number' => $ticket['ticket_number'],
            'subject' => $ticket['subject'],
            'priority' => $priorityDisplay,
            'requester_name' => $requesterName,
            'inbound_email_address' => $ticket['inbound_email_address'],
            'created_by_email' => $ticket['inbound_email_address']
        ]);
        
        // Queue the email
        $result = $emailService->queueEmail($emailData, 2); // High priority
        
        if ($result) {
            echo "  ✓ Acknowledgment email queued successfully (Queue ID: $result)\n";
            $sent++;
        } else {
            echo "  ✗ Failed to queue acknowledgment email\n";
            $failed++;
        }
        
        echo "\n";
    }
    
    echo "=== Summary ===\n";
    echo "Total tickets processed: " . count($tickets) . "\n";
    echo "Acknowledgments queued: $sent\n";
    echo "Failed: $failed\n\n";
    
    if ($sent > 0) {
        echo "Processing email queue to send acknowledgments...\n";
        $processed = $emailService->processEmailQueue($sent);
        echo "Sent $processed acknowledgment emails.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nDone!\n";

