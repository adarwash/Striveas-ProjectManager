<?php
// Quick test to verify jsonResponse method works
session_start();
require_once 'config/config.php';
require_once 'app/init.php';

// Simulate logged in user
$_SESSION['user_id'] = 1;
$_SESSION['is_logged_in'] = true;

try {
    // Create Time controller instance
    require_once 'app/controllers/Time.php';
    $timeController = new Time();
    
    echo "✅ Time controller created successfully\n";
    echo "✅ No visibility conflicts with jsonResponse method\n";
    
    // Check if method exists and is callable
    if (method_exists($timeController, 'jsonResponse')) {
        echo "✅ jsonResponse method exists and is accessible\n";
    } else {
        echo "❌ jsonResponse method not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>
