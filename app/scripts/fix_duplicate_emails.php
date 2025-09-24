<?php
/**
 * Script to identify and fix duplicate email processing issues
 */

// Bootstrap the application
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/EasySQL.php';

$db = new EasySQL(DB1);

echo "=== Duplicate Email Processing Fix ===\n";
echo date('Y-m-d H:i:s') . "\n\n";

try {
    // Find duplicate emails in EmailInbox
    echo "1. Checking for duplicate emails in EmailInbox...\n";
    $duplicateQuery = "
        SELECT message_id, COUNT(*) as count
        FROM EmailInbox
        GROUP BY message_id
        HAVING COUNT(*) > 1
    ";
    
    $duplicates = $db->select($duplicateQuery);
    
    if (!empty($duplicates)) {
        echo "Found " . count($duplicates) . " duplicate message IDs.\n";
        
        foreach ($duplicates as $dup) {
            echo "  - Message ID: {$dup['message_id']} ({$dup['count']} copies)\n";
            
            // Keep only the processed one or the oldest one
            $allCopies = $db->select(
                "SELECT id, processing_status, ticket_id, email_date 
                 FROM EmailInbox 
                 WHERE message_id = :message_id 
                 ORDER BY 
                    CASE WHEN processing_status = 'processed' THEN 0 ELSE 1 END,
                    email_date ASC",
                ['message_id' => $dup['message_id']]
            );
            
            if (count($allCopies) > 1) {
                $keepId = $allCopies[0]['id'];
                echo "    Keeping ID: $keepId (status: {$allCopies[0]['processing_status']})\n";
                
                // Mark others as duplicates
                for ($i = 1; $i < count($allCopies); $i++) {
                    $db->update(
                        "UPDATE EmailInbox 
                         SET processing_status = 'duplicate', 
                             processing_error = 'Duplicate of message ' || :keep_id
                         WHERE id = :id",
                        ['keep_id' => $keepId, 'id' => $allCopies[$i]['id']]
                    );
                    echo "    Marked ID {$allCopies[$i]['id']} as duplicate\n";
                }
            }
        }
    } else {
        echo "No duplicate emails found.\n";
    }
    
    echo "\n2. Checking for duplicate tickets with same subject/email...\n";
    
    // Find tickets with duplicate subjects from same sender
    $duplicateTickets = $db->select("
        SELECT subject, inbound_email_address, COUNT(*) as count
        FROM Tickets
        WHERE source = 'email'
        AND inbound_email_address IS NOT NULL
        AND created_at >= DATEADD(day, -7, GETDATE())
        GROUP BY subject, inbound_email_address
        HAVING COUNT(*) > 1
        ORDER BY count DESC
    ");
    
    if (!empty($duplicateTickets)) {
        echo "Found " . count($duplicateTickets) . " duplicate ticket patterns.\n\n";
        
        foreach ($duplicateTickets as $pattern) {
            echo "  Subject: " . substr($pattern['subject'], 0, 50) . "...\n";
            echo "  From: {$pattern['inbound_email_address']}\n";
            echo "  Count: {$pattern['count']} duplicates\n";
            
            // Get all tickets matching this pattern
            $tickets = $db->select(
                "SELECT id, ticket_number, created_at 
                 FROM Tickets 
                 WHERE subject = :subject 
                 AND inbound_email_address = :email
                 AND source = 'email'
                 ORDER BY created_at ASC",
                ['subject' => $pattern['subject'], 'email' => $pattern['inbound_email_address']]
            );
            
            if (!empty($tickets)) {
                echo "  Tickets: ";
                foreach ($tickets as $t) {
                    echo $t['ticket_number'] . " ";
                }
                echo "\n";
                echo "  (Keeping first: {$tickets[0]['ticket_number']})\n\n";
            }
        }
    } else {
        echo "No duplicate tickets found.\n";
    }
    
    echo "\n3. Preventing future duplicates...\n";
    
    // Add unique constraint suggestion
    echo "Consider adding unique constraints to prevent duplicates:\n";
    echo "  - EmailInbox: UNIQUE(message_id)\n";
    echo "  - Add duplicate detection in email processing logic\n";
    
    echo "\n=== Complete ===\n";
    echo "Duplicate processing fixed.\n";
    echo "Run send_missing_acknowledgments.php to send acknowledgments for valid tickets.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nDone!\n";

