<!DOCTYPE html>
<html>
<head>
    <title>Redirect URI Configuration</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .uri-box { background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 10px 0; font-family: monospace; font-size: 16px; }
        .important { background: #fffbcc; padding: 15px; border-left: 4px solid #ff9800; margin: 20px 0; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0; }
        h2 { color: #333; }
        .copy-btn { background: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer; margin-left: 10px; }
        .copy-btn:hover { background: #0056b3; }
    </style>
</head>
<body>
<?php
require_once 'config/config.php';

// Get the actual host being used to access this page
$actualHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$actualScheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$actualUrl = $actualScheme . '://' . $actualHost;

echo "<h1>🔧 Microsoft OAuth Redirect URI Configuration</h1>";

echo "<div class='important'>";
echo "<strong>⚠️ Important:</strong> You are accessing this site from: <strong>$actualUrl</strong>";
echo "</div>";

echo "<h2>📋 Redirect URIs to Add in Azure AD:</h2>";

// Show all possible redirect URIs
$redirectUris = [
    $actualUrl . '/microsoftAuth/callback',
    'http://localhost/microsoftAuth/callback',
    'http://127.0.0.1/microsoftAuth/callback'
];

// Add HTTPS version if currently using HTTP
if ($actualScheme === 'http') {
    $redirectUris[] = 'https://' . $actualHost . '/microsoftAuth/callback';
}

// Remove duplicates
$redirectUris = array_unique($redirectUris);

echo "<p>Add ALL of these redirect URIs to your Azure AD app registration:</p>";

foreach ($redirectUris as $i => $uri) {
    echo "<div class='uri-box'>";
    echo "<span id='uri$i'>" . htmlspecialchars($uri) . "</span>";
    echo "<button class='copy-btn' onclick='copyToClipboard(\"uri$i\")'>Copy</button>";
    if ($i === 0) {
        echo " <strong>← This is your current URL</strong>";
    }
    echo "</div>";
}

echo "<h2>📝 How to Add in Azure AD:</h2>";
echo "<ol>";
echo "<li>Go to <a href='https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade' target='_blank'>Azure AD App Registrations</a></li>";
echo "<li>Click on your app: <strong>HivePortal</strong> (ID: 6cc0f229-a986-47c8-921f-f5e9a0fa6407)</li>";
echo "<li>Click <strong>Authentication</strong> in the left menu</li>";
echo "<li>Under <strong>Web</strong> platform, click <strong>Add URI</strong></li>";
echo "<li>Add each redirect URI from above</li>";
echo "<li>Click <strong>Save</strong></li>";
echo "</ol>";

echo "<div class='success'>";
echo "<strong>✅ After adding these URIs:</strong><br>";
echo "1. Clear your browser cache<br>";
echo "2. Try the 'Connect with Microsoft' button again<br>";
echo "3. The error should be resolved!";
echo "</div>";

// Debug info
echo "<h2>🔍 Debug Information:</h2>";
echo "<pre style='background: #f5f5f5; padding: 10px; overflow-x: auto;'>";
echo "URLROOT (from config): " . URLROOT . "\n";
echo "Actual Host: " . $actualHost . "\n";
echo "Actual Scheme: " . $actualScheme . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n";
echo "</pre>";
?>

<script>
function copyToClipboard(elementId) {
    const text = document.getElementById(elementId).textContent;
    navigator.clipboard.writeText(text).then(() => {
        event.target.textContent = 'Copied!';
        setTimeout(() => {
            event.target.textContent = 'Copy';
        }, 2000);
    });
}
</script>
</body>
</html>




