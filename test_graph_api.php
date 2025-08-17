<?php
/**
 * Microsoft Graph API Test Script
 * Run this after setting up your Azure AD app
 */

// Load required files
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/core/EasySQL.php';

// STEP 1: Enter your Azure AD app details here
$TENANT_ID = 'your-tenant-id';        // From Azure AD overview
$CLIENT_ID = 'your-client-id';        // From app registration overview  
$CLIENT_SECRET = 'your-client-secret'; // From certificates & secrets
$SUPPORT_EMAIL = 'support@yourdomain.com'; // Email to check

echo "===========================================\n";
echo "  Microsoft Graph API Email Test\n";
echo "===========================================\n\n";

// Test 1: Get Access Token
echo "1. Testing Authentication...\n";
echo "   Tenant ID: " . substr($TENANT_ID, 0, 8) . "...\n";
echo "   Client ID: " . substr($CLIENT_ID, 0, 8) . "...\n";

$tokenUrl = "https://login.microsoftonline.com/{$TENANT_ID}/oauth2/v2.0/token";
$tokenData = [
    'client_id' => $CLIENT_ID,
    'client_secret' => $CLIENT_SECRET,
    'scope' => 'https://graph.microsoft.com/.default',
    'grant_type' => 'client_credentials'
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "   ❌ FAILED to get access token\n";
    echo "   HTTP Code: $httpCode\n";
    echo "   Response: $response\n\n";
    echo "   Common issues:\n";
    echo "   - Wrong tenant ID, client ID, or secret\n";
    echo "   - Secret expired (create a new one)\n";
    echo "   - App not registered properly\n";
    exit(1);
}

$tokenData = json_decode($response, true);
$accessToken = $tokenData['access_token'] ?? null;

if (!$accessToken) {
    echo "   ❌ No access token in response\n";
    exit(1);
}

echo "   ✅ Access token obtained successfully!\n";
echo "   Token expires in: " . ($tokenData['expires_in'] ?? 0) . " seconds\n\n";

// Test 2: Read Emails
echo "2. Testing Email Read Access...\n";
echo "   Checking mailbox: $SUPPORT_EMAIL\n";

$emailUrl = "https://graph.microsoft.com/v1.0/users/{$SUPPORT_EMAIL}/messages?\$top=5&\$orderby=receivedDateTime desc";

$ch = curl_init($emailUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "   ❌ FAILED to read emails\n";
    echo "   HTTP Code: $httpCode\n";
    $error = json_decode($response, true);
    echo "   Error: " . ($error['error']['message'] ?? $response) . "\n\n";
    echo "   Common issues:\n";
    echo "   - Mail.Read permission not granted (check Azure AD)\n";
    echo "   - Admin consent not given\n";
    echo "   - Wrong email address\n";
    echo "   - Email account doesn't exist or not licensed\n";
    exit(1);
}

$emailData = json_decode($response, true);
$emails = $emailData['value'] ?? [];

echo "   ✅ Successfully connected to mailbox!\n";
echo "   Found " . count($emails) . " recent emails\n\n";

if (count($emails) > 0) {
    echo "   Recent emails:\n";
    foreach ($emails as $i => $email) {
        echo "   " . ($i + 1) . ". " . substr($email['subject'], 0, 50);
        if (strlen($email['subject']) > 50) echo "...";
        echo "\n";
        echo "      From: " . $email['from']['emailAddress']['address'] . "\n";
        echo "      Date: " . date('Y-m-d H:i:s', strtotime($email['receivedDateTime'])) . "\n";
        echo "      Read: " . ($email['isRead'] ? 'Yes' : 'No') . "\n";
    }
}

echo "\n";

// Test 3: Check Send Permission
echo "3. Testing Email Send Permission...\n";

$testEmailData = [
    'message' => [
        'subject' => 'Graph API Test - Please Ignore',
        'body' => [
            'contentType' => 'Text',
            'content' => 'This is a test email from ProjectTracker Graph API integration.'
        ],
        'toRecipients' => [
            ['emailAddress' => ['address' => $SUPPORT_EMAIL]]
        ]
    ],
    'saveToSentItems' => false
];

$sendUrl = "https://graph.microsoft.com/v1.0/users/{$SUPPORT_EMAIL}/sendMail";

$ch = curl_init($sendUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testEmailData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 202) {
    echo "   ✅ Email send permission verified!\n";
    echo "   (Test email sent to $SUPPORT_EMAIL)\n";
} else {
    echo "   ⚠️  Could not verify send permission\n";
    echo "   HTTP Code: $httpCode\n";
    if ($httpCode === 403) {
        echo "   Mail.Send permission might not be granted\n";
    }
}

echo "\n";

// Test 4: Save Configuration
echo "4. Configuration Summary:\n";
echo "   Add these to your config.php or database:\n\n";
echo "   define('GRAPH_TENANT_ID', '$TENANT_ID');\n";
echo "   define('GRAPH_CLIENT_ID', '$CLIENT_ID');\n";
echo "   define('GRAPH_CLIENT_SECRET', '$CLIENT_SECRET');\n";
echo "   define('GRAPH_SUPPORT_EMAIL', '$SUPPORT_EMAIL');\n";

echo "\n";
echo "===========================================\n";
echo "  ✅ Graph API Setup Complete!\n";
echo "===========================================\n";
echo "\n";
echo "Next steps:\n";
echo "1. Add the configuration to your config.php\n";
echo "2. Set up the cron job for automatic processing\n";
echo "3. Test creating a ticket by sending an email\n";
echo "\n";
echo "Your email integration is ready to use!\n";
?>
