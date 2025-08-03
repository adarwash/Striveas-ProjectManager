<?php
/**
 * Migration: Add site_id column to TimeEntries table
 * This allows tracking which site/location an employee clocked in from
 */

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../core/EasySQL.php';

try {
    $db = new EasySQL(DB1);
    
    echo "Starting migration: Add site_id to TimeEntries table...\n";
    
    // Check if the column already exists
    $checkColumn = "SELECT COUNT(*) as column_exists 
                   FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_NAME = 'TimeEntries' 
                   AND COLUMN_NAME = 'site_id'";
    
    $result = $db->select($checkColumn);
    $columnExists = $result[0]['column_exists'] > 0;
    
    if ($columnExists) {
        echo "✓ site_id column already exists in TimeEntries table\n";
    } else {
        // Add the site_id column
        $addColumnSql = "ALTER TABLE [dbo].[TimeEntries] 
                        ADD site_id INT NULL,
                        CONSTRAINT FK_TimeEntries_Sites 
                        FOREIGN KEY (site_id) REFERENCES [dbo].[Sites](id)";
        
        $db->query($addColumnSql);
        echo "✓ Added site_id column to TimeEntries table\n";
        
        // Create index for better performance
        $createIndexSql = "CREATE INDEX idx_time_entries_site ON [dbo].[TimeEntries](site_id)";
        $db->query($createIndexSql);
        echo "✓ Created index on site_id column\n";
    }
    
    // Check if Sites table has sample data
    $sitesCount = $db->select("SELECT COUNT(*) as count FROM [dbo].[Sites]");
    if ($sitesCount[0]['count'] == 0) {
        echo "⚠ Sites table is empty. Adding sample site data...\n";
        
        // Insert sample sites
        $sampleSites = [
            ['Main Office', 'New York, NY', '123 Main St, New York, NY 10001', 'NYC-HQ', 'Headquarters'],
            ['Branch Office', 'Chicago, IL', '456 Business Ave, Chicago, IL 60601', 'CHI-BR', 'Branch'],
            ['Remote Work Hub', 'Austin, TX', '789 Tech Blvd, Austin, TX 78701', 'ATX-HUB', 'Remote Hub'],
            ['Client Site', 'Boston, MA', '321 Client Way, Boston, MA 02116', 'CLI-MAIN', 'Client']
        ];
        
        foreach ($sampleSites as $site) {
            $insertSql = "INSERT INTO [dbo].[Sites] (name, location, address, site_code, type, status) 
                         VALUES (?, ?, ?, ?, ?, 'Active')";
            $db->insert($insertSql, $site);
        }
        echo "✓ Added sample site data\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    echo "TimeEntries table now tracks site_id for location-based time tracking.\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
} 