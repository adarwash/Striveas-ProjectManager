<?php
// Debug script to check sites and database connectivity
require_once 'config/config.php';
require_once 'app/core/EasySQL.php';

echo "=== Debug Sites and Database ===\n";

try {
    // Initialize database connection
    $db = new EasySQL(DB1);
    echo "✅ Database connection successful\n";
    
    // Check if Sites table exists
    $checkTable = "SELECT COUNT(*) as table_exists 
                  FROM INFORMATION_SCHEMA.TABLES 
                  WHERE TABLE_NAME = 'Sites'";
    $result = $db->select($checkTable);
    
    if ($result && $result[0]['table_exists'] > 0) {
        echo "✅ Sites table exists\n";
        
        // Count sites
        $countSites = "SELECT COUNT(*) as site_count FROM Sites";
        $siteCount = $db->select($countSites);
        echo "📊 Total sites in database: " . $siteCount[0]['site_count'] . "\n";
        
        // Get all sites
        $allSites = "SELECT id, name, location, status FROM Sites ORDER BY name ASC";
        $sites = $db->select($allSites);
        
        if ($sites && count($sites) > 0) {
            echo "🏢 Sites found:\n";
            foreach ($sites as $site) {
                echo "  - ID: {$site['id']}, Name: {$site['name']}, Location: {$site['location']}, Status: {$site['status']}\n";
            }
        } else {
            echo "❌ No sites found in database\n";
        }
        
        // Check active sites specifically
        $activeSites = "SELECT COUNT(*) as active_count FROM Sites WHERE status = 'Active'";
        $activeCount = $db->select($activeSites);
        echo "🟢 Active sites: " . $activeCount[0]['active_count'] . "\n";
        
        // Check EmployeeSites table
        $checkEmployeeSites = "SELECT COUNT(*) as table_exists 
                              FROM INFORMATION_SCHEMA.TABLES 
                              WHERE TABLE_NAME = 'EmployeeSites'";
        $empSitesResult = $db->select($checkEmployeeSites);
        
        if ($empSitesResult && $empSitesResult[0]['table_exists'] > 0) {
            echo "✅ EmployeeSites table exists\n";
            
            $countEmpSites = "SELECT COUNT(*) as count FROM EmployeeSites";
            $empSiteCount = $db->select($countEmpSites);
            echo "👥 Employee-Site assignments: " . $empSiteCount[0]['count'] . "\n";
        } else {
            echo "⚠️  EmployeeSites table does not exist\n";
        }
        
    } else {
        echo "❌ Sites table does not exist\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== End Debug ===\n";
?>
