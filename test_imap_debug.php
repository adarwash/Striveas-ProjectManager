<?php
// Enhanced Microsoft 365 IMAP Debug Script
echo "=== Microsoft 365 IMAP Connection Debugger ===\n\n";

// Configuration
$username = 'support@yourdomain.com';  // UPDATE THIS
$password = 'YOUR_APP_PASSWORD';       // UPDATE THIS with app password

echo "1. Checking PHP IMAP extension...\n";
if (!extension_loaded('imap')) {
    die("❌ ERROR: PHP IMAP extension is not installed!\n");
}
echo "✅ PHP IMAP extension is loaded\n\n";

echo "2. Testing DNS resolution...\n";
$dns = gethostbyname('outlook.office365.com');
echo "   outlook.office365.com resolves to: $dns\n\n";

echo "3. Testing network connectivity...\n";
$fp = @fsockopen('outlook.office365.com', 993, $errno, $errstr, 10);
if (!$fp) {
    echo "❌ Cannot connect to outlook.office365.com:993\n";
    echo "   Error: $errstr ($errno)\n";
} else {
    echo "✅ Network connection successful\n";
    fclose($fp);
}
echo "\n";

echo "4. Attempting IMAP connection...\n";
// Clear any previous errors
imap_errors();
imap_alerts();

// Try different connection strings
$connection_strings = [
    '{outlook.office365.com:993/imap/ssl/novalidate-cert}INBOX',
    '{outlook.office365.com:993/imap/ssl}INBOX',
    '{outlook.office365.com:993/ssl/novalidate-cert}INBOX'
];

foreach ($connection_strings as $conn_str) {
    echo "\n   Trying: $conn_str\n";
    $mbox = @imap_open($conn_str, $username, $password);
    
    if ($mbox) {
        echo "   ✅ SUCCESS with this connection string!\n";
        $check = imap_check($mbox);
        echo "   📧 Mailbox has {$check->Nmsgs} messages\n";
        imap_close($mbox);
        break;
    } else {
        echo "   ❌ Failed\n";
        $errors = imap_errors();
        if ($errors) {
            echo "   Errors: " . implode("\n           ", $errors) . "\n";
        }
    }
}

echo "\n5. Common Issues to Check:\n";
echo "   [ ] IMAP enabled in Microsoft 365 admin center?\n";
echo "   [ ] Using App Password (not regular password)?\n";
echo "   [ ] Security Defaults disabled in Azure AD?\n";
echo "   [ ] Basic Authentication enabled for IMAP?\n";
echo "   [ ] No Conditional Access policies blocking?\n";
echo "   [ ] Account has Exchange Online license?\n";

echo "\n6. Next Steps:\n";
echo "   - If all connection strings failed, IMAP is likely disabled\n";
echo "   - Check https://admin.microsoft.com → Users → [Your User] → Mail → Manage email apps\n";
echo "   - Enable IMAP and wait 15-30 minutes\n";
