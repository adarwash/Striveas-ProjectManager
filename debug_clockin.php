<?php
// Debug script to test clock-in functionality
session_start();

require_once 'config/config.php';
require_once 'app/core/EasySQL.php';
require_once 'app/models/TimeTracking.php';

echo "=== Debug Clock-In Process ===\n";

// Simulate a logged-in user session
$_SESSION['user_id'] = 1; // Test with user ID 1
$_SESSION['user_name'] = 'Test User';

try {
    $timeModel = new TimeTracking();
    echo "✅ TimeTracking model initialized\n";
    
    // First, let's check the current status
    $userStatus = $timeModel->getUserStatus($_SESSION['user_id']);
    echo "📊 Current status: " . ($userStatus['status'] ?? 'unknown') . "\n";
    
    // If already clocked in, clock out first
    if (isset($userStatus['status']) && $userStatus['status'] !== 'clocked_out') {
        echo "⚠️  User is currently " . $userStatus['status'] . ", clocking out first...\n";
        $clockOutResult = $timeModel->clockOut($_SESSION['user_id'], 'Debug test');
        echo "Clock out result: " . ($clockOutResult['success'] ? 'Success' : 'Failed: ' . $clockOutResult['message']) . "\n";
    }
    
    // Test the clock-in process with site ID 3 (we know this exists)
    echo "\n🔍 Testing clock-in with site ID 3...\n";
    $clockInResult = $timeModel->clockIn($_SESSION['user_id'], 'Debug test clock-in', 3);
    
    if ($clockInResult['success']) {
        echo "✅ Clock-in successful!\n";
        echo "  - Message: " . $clockInResult['message'] . "\n";
        echo "  - Time: " . $clockInResult['clock_in_time'] . "\n";
        echo "  - Entry ID: " . $clockInResult['time_entry_id'] . "\n";
        echo "  - Site ID: " . $clockInResult['site_id'] . "\n";
        if (isset($clockInResult['site_info'])) {
            echo "  - Site Info: " . json_encode($clockInResult['site_info']) . "\n";
        }
    } else {
        echo "❌ Clock-in failed: " . $clockInResult['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== End Debug ===\n";
?>
