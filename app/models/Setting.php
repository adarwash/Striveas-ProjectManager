<?php
/**
 * Settings Model
 * 
 * Handles system-wide settings including currency configuration
 */
class Setting {
    private $db;

    /**
     * Constructor - initialize database connection
     */
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }

    /**
     * Create settings table if it doesn't exist
     * 
     * @return bool True if successful or table already exists, false on error
     */
    public function createSettingsTable() {
        try {
            // Check if settings table exists - SQL Server syntax
            $query = "SELECT COUNT(*) AS table_count FROM INFORMATION_SCHEMA.TABLES 
                     WHERE TABLE_NAME = 'settings'";
            $result = $this->db->select($query);
            $tableExists = ($result && isset($result[0]['table_count']) && $result[0]['table_count'] > 0);
            
            if (!$tableExists) {
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
                
                $this->db->query($sql);
                
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
                
                // Insert default currency settings
                $insertSql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)";
                $this->db->insert($insertSql, ['currency', $currencyJSON]);
                
                error_log('Settings table created successfully');
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Error creating settings table: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a setting by key
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if setting not found
     * @return mixed Setting value
     */
    public function get(string $key, $default = null) {
        try {
            $query = "SELECT setting_value FROM settings WHERE setting_key = ?";
            $result = $this->db->select($query, [$key]);
            
            if ($result && isset($result[0]['setting_value'])) {
                // Check if the value is serialized JSON - for complex settings
                if ($this->isJson($result[0]['setting_value'])) {
                    return json_decode($result[0]['setting_value'], true);
                }
                return $result[0]['setting_value'];
            }
            
            return $default;
        } catch (Exception $e) {
            error_log('Get Setting Error: ' . $e->getMessage());
            return $default;
        }
    }
    
    /**
     * Set a setting value
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool True if successful, false otherwise
     */
    public function set(string $key, $value): bool {
        try {
            // Convert arrays or objects to JSON for storage
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            
            // Check if setting exists first
            $query = "SELECT COUNT(*) as count FROM settings WHERE setting_key = ?";
            $result = $this->db->select($query, [$key]);
            
            if (isset($result[0]['count']) && $result[0]['count'] > 0) {
                // Update existing setting
                $updateQuery = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
                $this->db->update($updateQuery, [$value, $key]);
            } else {
                // Insert new setting
                $insertQuery = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)";
                $this->db->insert($insertQuery, [$key, $value]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Set Setting Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all settings
     * 
     * @return array Array of all settings as key-value pairs
     */
    public function getAll(): array {
        try {
            $query = "SELECT setting_key, setting_value FROM settings";
            $results = $this->db->select($query);
            
            $settings = [];
            foreach ($results as $row) {
                $value = $row['setting_value'];
                
                // Convert JSON values to arrays
                if ($this->isJson($value)) {
                    $value = json_decode($value, true);
                }
                
                $settings[$row['setting_key']] = $value;
            }
            
            return $settings;
        } catch (Exception $e) {
            error_log('Get All Settings Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get system settings
     * 
     * @return array System settings
     */
    public function getSystemSettings(): array {
        // Get maintenance mode setting
        $maintenanceMode = $this->get('maintenance_mode', false);
        
        return [
            'maintenance_mode' => $maintenanceMode,
            'version' => $this->get('system_version', '1.0.0'),
            'last_backup' => $this->get('last_backup_date', null),
            'allow_registration' => $this->get('allow_registration', true)
        ];
    }
    
    /**
     * Update system settings
     * 
     * @param array $settings Array of system settings to update
     * @return bool True if successful, false otherwise
     */
    public function updateSystemSettings(array $settings): bool {
        try {
            // Valid system settings that can be updated
            $validSettings = [
                'maintenance_mode',
                'system_version',
                'last_backup_date',
                'allow_registration'
            ];
            
            // Update each provided setting if it's valid
            foreach ($settings as $key => $value) {
                if (in_array($key, $validSettings)) {
                    // Convert boolean strings to actual booleans
                    if ($value === 'true') {
                        $value = true;
                    } elseif ($value === 'false') {
                        $value = false;
                    }
                    
                    // Save the setting
                    $this->set($key, $value);
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Update System Settings Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get currency settings
     * 
     * @return array Currency settings with defaults
     */
    public function getCurrency(): array {
        $defaults = [
            'code' => 'USD',
            'symbol' => '$',
            'position' => 'before',
            'decimals' => 2,
            'thousands_separator' => ',',
            'decimal_separator' => '.'
        ];
        
        $currency = $this->get('currency', $defaults);
        
        // Map stored keys to returned array format
        return [
            'code' => $currency['currency_code'] ?? $defaults['code'],
            'symbol' => $currency['currency_symbol'] ?? $defaults['symbol'],
            'position' => $currency['currency_position'] ?? $defaults['position'],
            'decimals' => (int)($currency['currency_decimals'] ?? $defaults['decimals']),
            'thousands_separator' => $currency['currency_thousands_separator'] ?? $defaults['thousands_separator'],
            'decimal_separator' => $currency['currency_decimal_separator'] ?? $defaults['decimal_separator']
        ];
    }
    
    /**
     * Save currency settings
     * 
     * @param array $currency Currency settings
     * @return bool True if successful, false otherwise
     */
    public function setCurrency(array $currency): bool {
        // Ensure all required fields are present
        $required = ['code', 'symbol', 'position', 'decimals', 'thousands_separator', 'decimal_separator'];
        foreach ($required as $field) {
            if (!isset($currency[$field])) {
                error_log("Missing required currency field: {$field}");
                return false;
            }
        }
        
        // Map to storage format
        $data = [
            'currency_code' => $currency['code'],
            'currency_symbol' => $currency['symbol'],
            'currency_position' => $currency['position'],
            'currency_decimals' => (int)$currency['decimals'],
            'currency_thousands_separator' => $currency['thousands_separator'],
            'currency_decimal_separator' => $currency['decimal_separator']
        ];
        
        return $this->set('currency', $data);
    }
    
    /**
     * Format a number as currency
     * 
     * @param float $amount The amount to format
     * @return string Formatted currency string
     */
    public function formatCurrency(float $amount): string {
        $currency = $this->getCurrency();
        
        // Format the number with the correct number of decimals and separators
        $formatted = number_format(
            $amount,
            $currency['decimals'],
            $currency['decimal_separator'],
            $currency['thousands_separator']
        );
        
        // Add the currency symbol in the right position
        if ($currency['position'] === 'before') {
            return $currency['symbol'] . $formatted;
        } else {
            return $formatted . ' ' . $currency['symbol'];
        }
    }
    
    /**
     * Get currency symbol for a currency code
     * 
     * @param string $code Currency code (USD, EUR, etc)
     * @return string Currency symbol
     */
    public function getCurrencySymbol(string $code): string {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'JPY' => '¥',
            'CNY' => '¥',
            'INR' => '₹',
            'BRL' => 'R$',
            'ZAR' => 'R'
        ];
        
        return $symbols[$code] ?? $code;
    }
    
    /**
     * Check if a string is valid JSON
     * 
     * @param string $string String to check
     * @return bool True if valid JSON, false otherwise
     */
    private function isJson($string) {
        if (!is_string($string)) return false;
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
} 