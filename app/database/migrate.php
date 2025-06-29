<?php
/**
 * Database Migration Runner
 * 
 * This script runs all migrations in the migrations directory
 */

// Include the database configuration and any required core files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/core/EasySQL.php';
require_once __DIR__ . '/../../app/core/Database.php';

echo "Starting database migrations...\n";

// Path to migrations folder
$migrationsDir = __DIR__ . '/migrations/';

// Get all PHP files in the migrations directory
$migrationFiles = glob($migrationsDir . '*.php');

if (empty($migrationFiles)) {
    echo "No migration files found in {$migrationsDir}\n";
    exit(1);
}

// Sort files to ensure they run in order
sort($migrationFiles);

$totalMigrations = count($migrationFiles);
$successCount = 0;
$failCount = 0;

echo "Found {$totalMigrations} migration files.\n";

// Run each migration
foreach ($migrationFiles as $file) {
    $filename = basename($file);
    echo "Processing migration: {$filename}...\n";
    
    try {
        // Include the migration file
        require_once $file;
        
        // Extract the class name from the filename (assuming it matches)
        $className = pathinfo($filename, PATHINFO_FILENAME);
        
        if (class_exists($className)) {
            // Instantiate the migration class
            $migration = new $className();
            
            // Run the migration
            if (method_exists($migration, 'up')) {
                $migration->up();
                $successCount++;
                echo "Migration {$filename} completed successfully.\n";
            } else {
                echo "Migration {$filename} does not have an 'up' method.\n";
                $failCount++;
            }
        } else {
            echo "Migration class '{$className}' not found in {$filename}.\n";
            $failCount++;
        }
    } catch (Exception $e) {
        echo "Error running migration {$filename}: " . $e->getMessage() . "\n";
        $failCount++;
    }
    
    echo "\n";
}

echo "Migration Summary:\n";
echo "Total: {$totalMigrations}\n";
echo "Successful: {$successCount}\n";
echo "Failed: {$failCount}\n";

if ($failCount > 0) {
    echo "\nSome migrations failed. Please check the output above for details.\n";
    exit(1);
} else {
    echo "\nAll migrations completed successfully.\n";
    exit(0);
} 