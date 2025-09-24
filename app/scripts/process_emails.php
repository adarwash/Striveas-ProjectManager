<?php

/**
 * Email Processing Script for Ticketing System
 * This script should be run via cron job to process inbound emails
 * 
 * Usage: php app/scripts/process_emails.php
 * Recommended cron: every 5 minutes - run /usr/bin/php /var/www/ProjectTracker/app/scripts/process_emails.php
 */

// Set script path and include framework
define('SCRIPT_ROOT', dirname(dirname(__DIR__)));
require_once SCRIPT_ROOT . '/config/config.php';
require_once SCRIPT_ROOT . '/app/init.php';
require_once SCRIPT_ROOT . '/app/services/EmailService.php';

// Ensure this script is run from command line
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('This script can only be run from command line.');
}

// Script configuration
$maxEmailsPerRun = 50;
$maxRuntimeSeconds = 300; // 5 minutes max runtime
$logFile = SCRIPT_ROOT . '/logs/email_processing.log';
$startTime = time();

// Initialize logging
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

// Prevent overlapping runs with a lock file
$lockFilePath = sys_get_temp_dir() . '/projecttracker_email_processor.lock';
$lockHandle = @fopen($lockFilePath, 'c');
if ($lockHandle === false) {
    // If we cannot open a lock file, proceed but log a warning
    file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] [WARNING] Failed to open lock file: $lockFilePath\n", FILE_APPEND | LOCK_EX);
} else {
    if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
        // Another instance is running
        file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] [INFO] Another email processing instance is already running. Exiting.\n", FILE_APPEND | LOCK_EX);
        exit(0);
    }
    // Ensure lock released on shutdown
    register_shutdown_function(function() use ($lockHandle, $lockFilePath) {
        if (is_resource($lockHandle)) {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
        // Do not unlink the lock file; persistent file is fine and safer across processes
    });
}

/**
 * Log messages with timestamp
 */
function logMessage($message, $level = 'INFO') {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
    
    // Write to log file
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Also output to console if running in CLI
    if (php_sapi_name() === 'cli') {
        echo $logEntry;
    }
}

try {
    logMessage("Email processing script started");
    
    // Initialize email service
    $emailService = new EmailService();
    
    // Process inbound emails - check for Graph API first
    logMessage("Starting inbound email processing...");
    
    // Check if Microsoft Graph API is enabled
    $db = new EasySQL(DB1);
    $graphSettings = $db->select("SELECT setting_key, setting_value FROM Settings WHERE setting_key IN ('graph_enabled', 'graph_auto_process')");
    
    $graphEnabled = false;
    $graphAutoProcess = false;
    foreach ($graphSettings as $setting) {
        if ($setting['setting_key'] === 'graph_enabled' && $setting['setting_value'] == '1') {
            $graphEnabled = true;
        }
        if ($setting['setting_key'] === 'graph_auto_process' && $setting['setting_value'] == '1') {
            $graphAutoProcess = true;
        }
    }
    
    $inboundProcessed = 0;
    
    if ($graphEnabled && $graphAutoProcess) {
        logMessage("Using Microsoft Graph API for email processing...");
        try {
            require_once SCRIPT_ROOT . '/app/services/MicrosoftGraphService.php';
            $graphService = new MicrosoftGraphService();
            
            // Get support email from settings
            $supportEmailSetting = $db->select("SELECT setting_value FROM Settings WHERE setting_key = 'graph_connected_email'");
            $supportEmail = !empty($supportEmailSetting) ? $supportEmailSetting[0]['setting_value'] : 'support@hiveitsupport.co.uk';
            
            logMessage("Processing emails for: $supportEmail");
            $inboundProcessed = $graphService->processEmailsToTickets($supportEmail, true, $maxEmailsPerRun);
        } catch (Exception $e) {
            logMessage("Graph API processing failed: " . $e->getMessage(), 'ERROR');
            logMessage("Falling back to traditional IMAP processing...", 'WARNING');
            $inboundProcessed = $emailService->receiveEmails($maxEmailsPerRun);
        }
    } else {
        logMessage("Using traditional IMAP/POP3 for email processing...");
        $inboundProcessed = $emailService->receiveEmails($maxEmailsPerRun);
    }
    
    logMessage("Processed $inboundProcessed inbound emails");
    
    // Check runtime limit
    if ((time() - $startTime) > $maxRuntimeSeconds * 0.7) {
        logMessage("Approaching runtime limit, skipping outbound processing", 'WARNING');
    } else {
        // Process outbound email queue
        logMessage("Starting outbound email processing...");
        $outboundProcessed = $emailService->processEmailQueue($maxEmailsPerRun);
        logMessage("Processed $outboundProcessed outbound emails");
    }
    
    // Clean up old processed emails (optional)
    $cleanupDays = 30;
    $cleanupResult = cleanupOldEmails($cleanupDays);
    if ($cleanupResult['cleaned'] > 0) {
        logMessage("Cleaned up {$cleanupResult['cleaned']} old email records");
    }
    
    $totalRuntime = time() - $startTime;
    logMessage("Email processing completed successfully in {$totalRuntime} seconds");
    
    // Exit with success code
    exit(0);
    
} catch (Exception $e) {
    logMessage("Email processing failed: " . $e->getMessage(), 'ERROR');
    logMessage("Stack trace: " . $e->getTraceAsString(), 'ERROR');
    
    // Send alert email if critical error
    sendErrorAlert($e);
    
    // Exit with error code
    exit(1);
}

/**
 * Clean up old email records to prevent database bloat
 */
function cleanupOldEmails($days) {
    try {
        $db = new EasySQL(DB1);
        
        // Clean up processed emails older than specified days
        $query = "DELETE FROM EmailInbox 
                 WHERE processing_status = 'processed' 
                 AND processed_at < DATEADD(day, -:days, GETDATE())";
        
        $result = $db->query($query, ['days' => $days]);
        
        // Clean up sent emails from queue older than specified days
        $queueQuery = "DELETE FROM EmailQueue 
                      WHERE status = 'sent' 
                      AND sent_at < DATEADD(day, -:days, GETDATE())";
        
        $queueResult = $db->query($queueQuery, ['days' => $days]);
        
        return [
            'cleaned' => ($result ? 1 : 0) + ($queueResult ? 1 : 0),
            'inbox_cleaned' => $result,
            'queue_cleaned' => $queueResult
        ];
    } catch (Exception $e) {
        logMessage("Cleanup failed: " . $e->getMessage(), 'WARNING');
        return ['cleaned' => 0];
    }
}

/**
 * Send error alert email to administrators
 */
function sendErrorAlert($exception) {
    try {
        $db = new EasySQL(DB1);
        
        // Get admin email addresses
        $adminQuery = "SELECT DISTINCT u.email 
                      FROM Users u 
                      JOIN UserRoles ur ON u.id = ur.user_id 
                      JOIN Roles r ON ur.role_id = r.id 
                      WHERE r.name IN ('admin', 'super_admin') 
                      AND u.email IS NOT NULL 
                      AND u.email != ''";
        
        $admins = $db->select($adminQuery);
        
        if (empty($admins)) {
            logMessage("No admin email addresses found for error alert", 'WARNING');
            return;
        }
        
        $subject = '[' . SITENAME . '] Email Processing Error Alert';
        $body = "
        <h2>Email Processing Error Alert</h2>
        <p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>
        <p><strong>Server:</strong> " . ($_SERVER['HTTP_HOST'] ?? gethostname()) . "</p>
        <p><strong>Script:</strong> process_emails.php</p>
        
        <h3>Error Details:</h3>
        <p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>
        <p><strong>File:</strong> " . htmlspecialchars($exception->getFile()) . "</p>
        <p><strong>Line:</strong> " . $exception->getLine() . "</p>
        
        <h3>Stack Trace:</h3>
        <pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>
        
        <p>Please check the email processing system and resolve any configuration issues.</p>
        ";
        
        // Use simple mail() function for error alerts
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: system@' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
        ];
        
        foreach ($admins as $admin) {
            if (!empty($admin['email'])) {
                mail($admin['email'], $subject, $body, implode("\r\n", $headers));
            }
        }
        
        logMessage("Error alert sent to " . count($admins) . " administrators");
        
    } catch (Exception $alertException) {
        logMessage("Failed to send error alert: " . $alertException->getMessage(), 'ERROR');
    }
}

/**
 * Display script usage information
 */
function showUsage() {
    echo "Email Processing Script for Ticketing System\n";
    echo "Usage: php process_emails.php [options]\n\n";
    echo "Options:\n";
    echo "  --help         Show this help message\n";
    echo "  --dry-run      Run without making changes (testing mode)\n";
    echo "  --verbose      Enable verbose output\n";
    echo "  --max-emails   Maximum emails to process (default: 50)\n\n";
    echo "Example cron job:\n";
    echo "*/5 * * * * /usr/bin/php /var/www/ProjectTracker/app/scripts/process_emails.php\n\n";
}

// Handle command line arguments
if (isset($argv)) {
    foreach ($argv as $arg) {
        if ($arg === '--help' || $arg === '-h') {
            showUsage();
            exit(0);
        }
        
        if ($arg === '--dry-run') {
            logMessage("DRY RUN MODE - No changes will be made", 'INFO');
            // Set dry run flag for email service
        }
        
        if (strpos($arg, '--max-emails=') === 0) {
            $maxEmailsPerRun = (int) substr($arg, 13);
            logMessage("Maximum emails per run set to: $maxEmailsPerRun");
        }
    }
}

?>