<?php
/**
 * Database Setup Script for Ticketing System
 * This script creates the ticketing system database schema
 */

// Include configuration
require_once 'config/config.php';

// Set longer execution time for database operations
set_time_limit(300); // 5 minutes

class DatabaseSetup {
    private $connection = null;
    
    public function __construct($config) {
        try {
            // Build DSN for SQL Server
            $dsn = "sqlsrv:Server={$config['host']};Database={$config['dbname']};TrustServerCertificate=true";
            
            $this->connection = new PDO($dsn, $config['user'], $config['pass']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "✅ Database connection established successfully!<br><br>";
        } catch (PDOException $e) {
            throw new Exception('Database Connection Error: ' . $e->getMessage());
        }
    }
    
    public function executeSQLFile($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception("SQL file not found: $filePath");
        }
        
        echo "📄 Reading SQL file: $filePath<br>";
        $sqlContent = file_get_contents($filePath);
        
        if ($sqlContent === false) {
            throw new Exception("Failed to read SQL file: $filePath");
        }
        
        // Split SQL content by GO statements (SQL Server batch separator)
        $batches = $this->splitSQLBatches($sqlContent);
        
        echo "🔄 Found " . count($batches) . " SQL batches to execute<br><br>";
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($batches as $index => $batch) {
            $batch = trim($batch);
            if (empty($batch)) {
                continue;
            }
            
            try {
                echo "⚡ Executing batch " . ($index + 1) . "...<br>";
                $this->connection->exec($batch);
                $successCount++;
                echo "✅ Batch " . ($index + 1) . " completed successfully<br>";
            } catch (PDOException $e) {
                $errorCount++;
                echo "❌ Error in batch " . ($index + 1) . ": " . $e->getMessage() . "<br>";
                echo "📝 SQL: " . substr($batch, 0, 100) . "...<br>";
            }
            
            echo "<br>";
        }
        
        echo "<hr>";
        echo "<h3>📊 Execution Summary:</h3>";
        echo "✅ Successful batches: $successCount<br>";
        echo "❌ Failed batches: $errorCount<br>";
        echo "📈 Total batches: " . count($batches) . "<br>";
        
        if ($errorCount === 0) {
            echo "<br><h2 style='color: green;'>🎉 Database setup completed successfully!</h2>";
        } else {
            echo "<br><h2 style='color: orange;'>⚠️ Database setup completed with some errors</h2>";
        }
    }
    
    private function splitSQLBatches($sqlContent) {
        // Remove comments and normalize line endings
        $sqlContent = preg_replace('/--.*$/m', '', $sqlContent);
        $sqlContent = str_replace(["\r\n", "\r"], "\n", $sqlContent);
        
        // Split by GO statements (case insensitive, on its own line)
        $batches = preg_split('/^\s*GO\s*$/im', $sqlContent);
        
        // Filter out empty batches
        $batches = array_filter($batches, function($batch) {
            return !empty(trim($batch));
        });
        
        return array_values($batches);
    }
    
    public function testConnection() {
        try {
            $result = $this->connection->query("SELECT 1 as test")->fetch();
            echo "🔍 Database test query successful<br>";
            return true;
        } catch (PDOException $e) {
            echo "❌ Database test failed: " . $e->getMessage() . "<br>";
            return false;
        }
    }
    
    public function checkExistingTables() {
        try {
            echo "<h3>🔍 Checking for existing ticketing tables...</h3>";
            
            $tables = [
                'TicketStatuses', 'TicketPriorities', 'TicketCategories', 
                'Tickets', 'TicketMessages', 'EmailInbox', 'EmailQueue'
            ];
            
            $existingTables = [];
            
            foreach ($tables as $table) {
                $query = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES 
                         WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = ?";
                $stmt = $this->connection->prepare($query);
                $stmt->execute([$table]);
                $result = $stmt->fetch();
                
                if ($result['count'] > 0) {
                    $existingTables[] = $table;
                    echo "⚠️ Table '$table' already exists<br>";
                } else {
                    echo "✅ Table '$table' does not exist<br>";
                }
            }
            
            if (!empty($existingTables)) {
                echo "<br><strong>⚠️ Warning:</strong> Some tables already exist and will be dropped and recreated.<br>";
            }
            
            echo "<br>";
            return $existingTables;
        } catch (PDOException $e) {
            echo "❌ Error checking tables: " . $e->getMessage() . "<br>";
            return [];
        }
    }
    
    public function disconnect() {
        $this->connection = null;
        echo "🔌 Database connection closed<br>";
    }
}

// HTML Header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Ticketing System</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 0 auto; 
            padding: 20px; 
            background-color: #f5f5f5; 
        }
        .container { 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .header { 
            text-align: center; 
            color: #333; 
            border-bottom: 2px solid #007bff; 
            padding-bottom: 20px; 
            margin-bottom: 30px; 
        }
        .log { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 5px; 
            font-family: monospace; 
            border-left: 4px solid #007bff; 
            max-height: 600px; 
            overflow-y: auto; 
        }
        .button { 
            background: #007bff; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            text-decoration: none; 
            display: inline-block; 
        }
        .button:hover { 
            background: #0056b3; 
        }
        .button-success { 
            background: #28a745; 
        }
        .button-warning { 
            background: #ffc107; 
            color: #212529; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎫 Ticketing System Database Setup</h1>
            <p>This will create the complete ticketing system database schema</p>
        </div>

<?php

try {
    echo "<div class='log'>";
    
    echo "<h3>🚀 Starting Database Setup Process...</h3>";
    echo "⏰ " . date('Y-m-d H:i:s') . "<br><br>";
    
    // Initialize database setup
    $dbSetup = new DatabaseSetup(DB1);
    
    // Test connection
    if (!$dbSetup->testConnection()) {
        throw new Exception("Database connection test failed");
    }
    
    // Check existing tables
    $existingTables = $dbSetup->checkExistingTables();
    
    // Determine which SQL file to use
    $sqlFiles = [
        'sql/create_ticketing_system_simple.sql' => 'Simplified Ticketing System Schema',
        'sql/create_ticketing_system.sql' => 'Full Ticketing System Schema'
    ];
    
    $sqlFile = null;
    foreach ($sqlFiles as $file => $description) {
        if (file_exists($file)) {
            $sqlFile = $file;
            echo "📄 Found SQL file: $description ($file)<br>";
            break;
        }
    }
    
    if (!$sqlFile) {
        throw new Exception("No SQL schema file found. Please ensure the SQL file exists.");
    }
    
    echo "<br>";
    
    // Execute the SQL file
    $dbSetup->executeSQLFile($sqlFile);
    
    // Disconnect
    $dbSetup->disconnect();
    
    echo "</div>";
    
    echo "<br><div style='text-align: center; margin-top: 20px;'>";
    echo "<a href='app/views/tickets/index.php' class='button button-success'>View Tickets</a> ";
    echo "<a href='app/views/email_inbox/index.php' class='button'>Email Inbox</a> ";
    echo "<a href='dashboard.php' class='button'>Dashboard</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 20px; background: #ffe6e6; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Setup Failed</h3>";
    echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>Time:</strong> " . date('Y-m-d H:i:s') . "<br>";
    echo "</div>";
    
    echo "</div>"; // Close log div
    
    echo "<div style='text-align: center; margin-top: 20px;'>";
    echo "<a href='#' onclick='location.reload()' class='button button-warning'>Retry Setup</a> ";
    echo "<a href='dashboard.php' class='button'>Back to Dashboard</a>";
    echo "</div>";
}

?>

        <div style="margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 5px;">
            <h4>📋 What This Setup Does:</h4>
            <ul>
                <li>✅ Creates all ticketing system database tables</li>
                <li>✅ Sets up foreign key relationships</li>
                <li>✅ Inserts default statuses, priorities, and categories</li>
                <li>✅ Creates database indexes for performance</li>
                <li>✅ Sets up triggers for auto-generating ticket numbers</li>
                <li>✅ Creates the TicketDashboard view</li>
                <li>✅ Prepares email integration tables</li>
            </ul>
            
            <h4>🔧 Next Steps After Setup:</h4>
            <ol>
                <li>Configure email settings in admin panel</li>
                <li>Set up SMTP credentials for outbound emails</li>
                <li>Configure IMAP/POP3 for inbound email processing</li>
                <li>Set up cron job for email processing: <code>*/5 * * * * php /path/to/app/scripts/process_emails.php</code></li>
                <li>Configure user permissions for ticketing system</li>
            </ol>
        </div>
    </div>
</body>
</html>