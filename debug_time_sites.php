<?php
// Debug script to test TimeTracking getUserSites method
session_start();

require_once 'config/config.php';
require_once 'app/core/EasySQL.php';
require_once 'app/models/TimeTracking.php';

echo "=== Debug Time Tracking Sites ===\n";

// Simulate a logged-in user session
$_SESSION['user_id'] = 1; // Test with user ID 1
$_SESSION['user_name'] = 'Test User';

try {
    // Initialize TimeTracking model
    $timeModel = new TimeTracking();
    echo "✅ TimeTracking model initialized\n";
    
    // Test getUserSites method
    $sites = $timeModel->getUserSites($_SESSION['user_id']);
    echo "🔍 getUserSites called for user ID: " . $_SESSION['user_id'] . "\n";
    
    if ($sites && count($sites) > 0) {
        echo "✅ Sites found: " . count($sites) . "\n";
        foreach ($sites as $site) {
            echo "  - ID: {$site['id']}, Name: {$site['name']}, Location: " . ($site['location'] ?? 'N/A') . "\n";
        }
    } else {
        echo "❌ No sites returned from getUserSites method\n";
    }
    
    // Test the SQL query directly
    $db = new EasySQL(DB1);
    
    // Check EmployeeSites for this user
    $userSites = "SELECT es.*, s.name as site_name 
                 FROM EmployeeSites es 
                 INNER JOIN Sites s ON es.site_id = s.id 
                 WHERE es.user_id = ? AND es.status = 'Active'";
    $userSiteResult = $db->select($userSites, [$_SESSION['user_id']]);
    
    echo "\n🔍 Direct EmployeeSites query for user " . $_SESSION['user_id'] . ":\n";
    if ($userSiteResult && count($userSiteResult) > 0) {
        echo "✅ User has " . count($userSiteResult) . " site assignments:\n";
        foreach ($userSiteResult as $assignment) {
            echo "  - Site ID: {$assignment['site_id']}, Site Name: {$assignment['site_name']}, Status: {$assignment['status']}\n";
        }
    } else {
        echo "⚠️  No site assignments found for user, will fall back to all active sites\n";
        
        // Test fallback query
        $allActiveSites = "SELECT id, name, location, site_code, type FROM Sites WHERE status = 'Active' ORDER BY name ASC";
        $fallbackSites = $db->select($allActiveSites);
        
        if ($fallbackSites && count($fallbackSites) > 0) {
            echo "✅ Fallback - All active sites: " . count($fallbackSites) . "\n";
            foreach ($fallbackSites as $site) {
                echo "  - ID: {$site['id']}, Name: {$site['name']}, Location: " . ($site['location'] ?? 'N/A') . "\n";
            }
        } else {
            echo "❌ No active sites found in fallback\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== End Debug ===\n";
?>
