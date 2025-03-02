<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>URL Debug Information</h1>";
echo "<pre>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "\n";
echo "QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'not set') . "\n";
echo "</pre>";

// Parse the URL similar to our updated App.php
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$requestUri = strtok($requestUri, '?');
$basePath = '/';
if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}
$requestUri = trim($requestUri, '/');

echo "<h2>Parsed URL</h2>";
echo "<pre>";
echo "Cleaned request URI: " . $requestUri . "\n";
echo "URL segments: ";
print_r(empty($requestUri) ? [] : explode('/', $requestUri));
echo "</pre>";
?> 