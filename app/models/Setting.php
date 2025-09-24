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
     * Get all settings as key-value pairs
     * 
     * @return array Settings array
     */
    public function getAllSettings() {
        try {
            $query = "SELECT setting_key, setting_value FROM settings";
            $results = $this->db->select($query);
            
            $settings = [];
            if ($results) {
                foreach ($results as $row) {
                    $key = $row['setting_key'];
                    $value = $row['setting_value'];
                    
                    // Check if the value is JSON
                    if ($this->isJson($value)) {
                        $settings[$key] = json_decode($value, true);
                    } else {
                        $settings[$key] = $value;
                    }
                }
            }
            
            return $settings;
        } catch (Exception $e) {
            error_log('GetAllSettings Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Set a setting value (alias for set method)
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool Success status
     */
    public function setSetting(string $key, $value) {
        return $this->set($key, $value);
    }
    
    /**
     * Get a setting value (alias for get method)
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed Setting value
     */
    public function getSetting(string $key, $default = null) {
        return $this->get($key, $default);
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
            'allow_registration' => $this->get('allow_registration', true),
            'enable_registration' => $this->get('enable_registration', true),
            'enable_api' => $this->get('enable_api', false),
            'default_project_category' => $this->get('default_project_category', ''),
            'default_project_status' => $this->get('default_project_status', ''),
            'default_task_priority' => $this->get('default_task_priority', ''),
            'default_date_format' => $this->get('default_date_format', 'Y-m-d'),
            'max_upload_size' => $this->get('max_upload_size', 10),
            'max_projects' => $this->get('max_projects', 100),
            
            // Email Configuration Settings
            'from_email' => $this->get('from_email', ''),
            'from_name' => $this->get('from_name', SITENAME),
            'smtp_host' => $this->get('smtp_host', ''),
            'smtp_port' => $this->get('smtp_port', 587),
            'smtp_username' => $this->get('smtp_username', ''),
            'smtp_password' => $this->get('smtp_password', ''),
            'smtp_encryption' => $this->get('smtp_encryption', 'tls'),
            
            'inbound_protocol' => $this->get('inbound_protocol', 'imap'),
            'inbound_auth_type' => $this->get('inbound_auth_type', 'password'),
            'inbound_host' => $this->get('inbound_host', ''),
            'inbound_port' => $this->get('inbound_port', 993),
            'inbound_username' => $this->get('inbound_username', ''),
            'inbound_password' => $this->get('inbound_password', ''),
            'inbound_encryption' => $this->get('inbound_encryption', 'ssl'),
            'imap_folder' => $this->get('imap_folder', 'INBOX'),
            
            // OAuth2 Settings
            'oauth2_provider' => $this->get('oauth2_provider', 'microsoft'),
            'oauth2_client_id' => $this->get('oauth2_client_id', ''),
            'oauth2_client_secret' => $this->get('oauth2_client_secret', ''),
            'oauth2_redirect_uri' => $this->get('oauth2_redirect_uri', ''),
            
            'auto_process_emails' => $this->get('auto_process_emails', true),
            'delete_processed_emails' => $this->get('delete_processed_emails', false),
            'ticket_email_pattern' => $this->get('ticket_email_pattern', '/\[TKT-\d{4}-\d{6}\]/'),
            'max_attachment_size' => $this->get('max_attachment_size', 10485760), // 10MB in bytes
            'allowed_file_types' => $this->get('allowed_file_types', 'pdf,doc,docx,txt,png,jpg,jpeg,gif'),
            
            // Email Auto-Acknowledgment
            'auto_acknowledge_tickets' => $this->get('auto_acknowledge_tickets', true),
            
            // Customer Authentication Settings
            'customer_auth_enabled' => $this->get('customer_auth_enabled', false),
            'azure_tenant_id' => $this->get('azure_tenant_id', ''),
            'azure_client_id' => $this->get('azure_client_id', ''),
            'azure_client_secret' => $this->get('azure_client_secret', ''),
            'azure_connection_status' => $this->get('azure_connection_status', 'not_connected'),
            'azure_connected_at' => $this->get('azure_connected_at', ''),
            'customer_domain_restriction' => $this->get('customer_domain_restriction', ''),
            'ticket_visibility' => $this->get('ticket_visibility', 'email_match'),
            'allow_ticket_creation' => $this->get('allow_ticket_creation', false)
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
                'allow_registration',
                'enable_registration',
                'enable_api',
                'default_project_category',
                'default_project_status',
                'default_task_priority',
                'default_date_format',
                'max_upload_size',
                'max_projects',
                
                // Email Configuration Settings
                'from_email',
                'from_name',
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password',
                'smtp_encryption',
                
                // Inbound Email Settings (supports both IMAP and POP3)
                'inbound_protocol',
                'inbound_auth_type',
                'inbound_host',
                'inbound_port',
                'inbound_username',
                'inbound_password',
                'inbound_encryption',
                'imap_folder',
                
                // OAuth2 Settings
                'oauth2_provider',
                'oauth2_client_id',
                'oauth2_client_secret',
                'oauth2_redirect_uri',
                'auto_process_emails',
                'delete_processed_emails',
                'ticket_email_pattern',
                'max_attachment_size',
                'allowed_file_types',
                'auto_acknowledge_tickets',
                
                // Customer Portal Authentication (Azure AD)
                'customer_auth_enabled',
                'azure_tenant_id',
                'azure_client_id',
                'azure_client_secret',
                'azure_connection_status',
                'azure_connected_at',
                'customer_domain_restriction',
                'ticket_visibility',
                'allow_ticket_creation'
            ];
            
            // Update each provided setting if it's valid
            foreach ($settings as $key => $value) {
                if (in_array($key, $validSettings)) {
                    // Normalize typical checkbox and boolean string values
                    $normalized = null;
                    if (is_string($value)) {
                        $lower = strtolower(trim($value));
                        if (in_array($lower, ['true','1','on','yes'], true)) {
                            $normalized = true;
                        } elseif (in_array($lower, ['false','0','off','no'], true)) {
                            $normalized = false;
                        }
                    }
                    if ($normalized !== null) {
                        $value = $normalized;
                    }
                    
                    // Do not overwrite existing client secret with empty value
                    if ($key === 'azure_client_secret') {
                        $existing = $this->get('azure_client_secret', '');
                        if ((string)$value === '' && (string)$existing !== '') {
                            continue; // skip update to preserve current secret
                        }
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