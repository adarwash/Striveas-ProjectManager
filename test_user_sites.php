<?php
// Test script to verify getUserSites works for different users
session_start();
require_once 'config/config.php';
require_once 'app/core/EasySQL.php';
require_once 'app/models/TimeTracking.php';

echo "=== Testing getUserSites for different users ===\n";

$timeModel = new TimeTracking();

// Test User ID 1 (no assignments - should get fallback)
echo "🔍 Testing User ID 1 (should get fallback to all active sites):\n";
$user1Sites = $timeModel->getUserSites(1);
if ($user1Sites && count($user1Sites) > 0) {
    echo "✅ User 1 sites: " . count($user1Sites) . "\n";
    foreach ($user1Sites as $site) {
        echo "  - ID: {$site['id']}, Name: {$site['name']}\n";
    }
} else {
    echo "❌ No sites for User 1\n";
}

// Test User ID 2 (has assignments)
echo "\n🔍 Testing User ID 2 (should get assigned sites):\n";
$user2Sites = $timeModel->getUserSites(2);
if ($user2Sites && count($user2Sites) > 0) {
    echo "✅ User 2 sites: " . count($user2Sites) . "\n";
    foreach ($user2Sites as $site) {
        echo "  - ID: {$site['id']}, Name: {$site['name']}\n";
    }
} else {
    echo "❌ No sites for User 2\n";
}

echo "\n=== End Test ===\n";
?>
