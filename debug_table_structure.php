<?php
// Debug script to check table structures
require_once 'config/config.php';
require_once 'app/core/EasySQL.php';

echo "=== Debug Table Structures ===\n";

try {
    $db = new EasySQL(DB1);
    
    // Check EmployeeSites table structure
    echo "🔍 EmployeeSites table structure:\n";
    $empSitesColumns = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE 
                       FROM INFORMATION_SCHEMA.COLUMNS 
                       WHERE TABLE_NAME = 'EmployeeSites' 
                       ORDER BY ORDINAL_POSITION";
    $columns = $db->select($empSitesColumns);
    
    if ($columns) {
        foreach ($columns as $column) {
            echo "  - {$column['COLUMN_NAME']} ({$column['DATA_TYPE']}) - Nullable: {$column['IS_NULLABLE']}\n";
        }
    } else {
        echo "  ❌ Could not retrieve column information\n";
    }
    
    // Check Sites table structure
    echo "\n🔍 Sites table structure:\n";
    $sitesColumns = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME = 'Sites' 
                    ORDER BY ORDINAL_POSITION";
    $siteColumns = $db->select($sitesColumns);
    
    if ($siteColumns) {
        foreach ($siteColumns as $column) {
            echo "  - {$column['COLUMN_NAME']} ({$column['DATA_TYPE']}) - Nullable: {$column['IS_NULLABLE']}\n";
        }
    } else {
        echo "  ❌ Could not retrieve Sites column information\n";
    }
    
    // Check actual data in EmployeeSites
    echo "\n📊 EmployeeSites table data:\n";
    $empSitesData = "SELECT * FROM EmployeeSites";
    $data = $db->select($empSitesData);
    
    if ($data) {
        foreach ($data as $row) {
            echo "  - User ID: {$row['user_id']}, Site ID: {$row['site_id']}\n";
            foreach ($row as $key => $value) {
                if (!in_array($key, ['user_id', 'site_id'])) {
                    echo "    {$key}: {$value}\n";
                }
            }
        }
    } else {
        echo "  ❌ No data found in EmployeeSites\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== End Debug ===\n";
?>
