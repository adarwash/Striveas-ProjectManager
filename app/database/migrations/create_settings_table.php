<?php
/**
 * Create settings table migration
 * 
 * This migration creates the settings table to store application-wide settings
 * including currency configuration.
 */

class create_settings_table {
    /**
     * Run the migration
     * Creates the settings table and inserts default values for currency
     * 
     * @return void
     */
    public function up() {
        // Get the database connection
        $db = new EasySQL(DB1);
        
        try {
            // Create settings table - SQL Server syntax
            $sql = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='settings' AND xtype='U')
            BEGIN
                CREATE TABLE settings (
                    id INT IDENTITY(1,1) PRIMARY KEY,
                    setting_key NVARCHAR(255) NOT NULL UNIQUE,
                    setting_value NVARCHAR(MAX) NULL,
                    created_at DATETIME DEFAULT GETDATE(),
                    updated_at DATETIME DEFAULT GETDATE()
                )
            END";
            
            $db->query($sql);
            
            // Insert default currency settings
            $currencyDefaults = [
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'currency_position' => 'before',
                'currency_decimals' => 2,
                'currency_thousands_separator' => ',',
                'currency_decimal_separator' => '.'
            ];
            
            // Convert to JSON for storage
            $currencyJSON = json_encode($currencyDefaults);
            
            // Check if currency settings already exist
            $checkSql = "SELECT COUNT(*) as count FROM settings WHERE setting_key = 'currency'";
            $result = $db->select($checkSql);
            
            if (isset($result[0]['count']) && $result[0]['count'] == 0) {
                // Insert default currency settings if they don't exist
                $insertSql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)";
                $db->insert($insertSql, ['currency', $currencyJSON]);
                
                echo "Default currency settings created.\n";
            } else {
                echo "Currency settings already exist.\n";
            }
            
            echo "Settings table created successfully.\n";
        } catch (Exception $e) {
            echo "Error creating settings table: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Reverse the migration
     * Drops the settings table
     * 
     * @return void
     */
    public function down() {
        $db = new EasySQL(DB1);
        
        try {
            $sql = "IF EXISTS (SELECT * FROM sysobjects WHERE name='settings' AND xtype='U')
            BEGIN
                DROP TABLE settings
            END";
            $db->query($sql);
            
            echo "Settings table dropped successfully.\n";
        } catch (Exception $e) {
            echo "Error dropping settings table: " . $e->getMessage() . "\n";
        }
    }
} 