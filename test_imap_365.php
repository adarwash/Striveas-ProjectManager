<?php
/**
 * Microsoft 365 IMAP Connection Test
 * Run this script to test your Office 365 IMAP connection
 */

// Configuration - UPDATE THESE VALUES
$host = 'outlook.office365.com';
$port = 993;
$username = 'support@yourdomain.com'; // UPDATE THIS with your real email
$password = 'YOUR_APP_PASSWORD';      // UPDATE THIS with the App Password from Microsoft
$folder = 'INBOX';

echo "Testing Microsoft 365 IMAP Connection...\n";
echo "Host: $host:$port\n";
echo "Username: $username\n";
echo "Folder: $folder\n\n";

// Check if IMAP extension is loaded
if (!extension_loaded('imap')) {
    die("ERROR: PHP IMAP extension is not installed!\n");
}

// Build connection string
$connectionString = "{{$host}:{$port}/imap/ssl/novalidate-cert}$folder";
echo "Connection String: $connectionString\n\n";

// Attempt connection
echo "Attempting connection...\n";
$connection = @imap_open($connectionString, $username, $password);

if ($connection) {
    echo "✅ SUCCESS: Connected to Microsoft 365 IMAP!\n";
    
    // Get mailbox info
    $mailboxInfo = imap_status($connection, $connectionString, SA_ALL);
    if ($mailboxInfo) {
        echo "📧 Messages: " . $mailboxInfo->messages . "\n";
        echo "📬 Recent: " . $mailboxInfo->recent . "\n";
        echo "📭 Unseen: " . $mailboxInfo->unseen . "\n";
    }
    
    // Close connection
    imap_close($connection);
    echo "\n🎉 Test completed successfully!\n";
    echo "You can now use these settings in your ProjectTracker admin panel.\n";
    
} else {
    echo "❌ FAILED: Could not connect to Microsoft 365 IMAP\n";
    
    // Get detailed error information
    $errors = imap_errors();
    $alerts = imap_alerts();
    
    if ($errors) {
        echo "\n🔴 IMAP Errors:\n";
        foreach ($errors as $error) {
            echo "  - $error\n";
        }
    }
    
    if ($alerts) {
        echo "\n🟡 IMAP Alerts:\n";
        foreach ($alerts as $alert) {
            echo "  - $alert\n";
        }
    }
    
    echo "\n💡 Troubleshooting Tips:\n";
    echo "1. Make sure you're using an App Password, not your regular password\n";
    echo "2. Verify IMAP is enabled in your Office 365 mailbox settings\n";
    echo "3. Check with your IT admin about conditional access policies\n";
    echo "4. Ensure your email address is correct: $username\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
?>