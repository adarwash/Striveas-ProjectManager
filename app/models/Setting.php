<?php
class Setting {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
    }

    // Get all system settings
    public function getSystemSettings() {
        try {
            // Always create the table first if it doesn't exist
            $this->createSettingsTable();
            
            $query = "SELECT * FROM settings WHERE setting_scope = 'system'";
            $result = $this->db->select($query);
            
            // Format into a key-value array
            $settings = [];
            if (!empty($result)) {
                foreach ($result as $row) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            }
            
            // Set default values for essential settings if they don't exist
            if (empty($settings['maintenance_mode'])) {
                $settings['maintenance_mode'] = '0';
            }
            if (empty($settings['default_project_category'])) {
                $settings['default_project_category'] = 'General';
            }
            if (empty($settings['default_project_status'])) {
                $settings['default_project_status'] = 'In Progress';
            }
            
            return $settings;
        } catch (Exception $e) {
            error_log('GetSystemSettings Error: ' . $e->getMessage());
            
            // Return default values on error
            return [
                'maintenance_mode' => '0',
                'default_project_category' => 'General',
                'default_project_status' => 'In Progress'
            ];
        }
    }
    
    // Update system settings
    public function updateSystemSettings($data) {
        try {
            // Ensure table exists
            $this->createSettingsTable();
            
            // Start transaction
            $this->db->beginTransaction();
            
            // Update each setting
            foreach ($data as $key => $value) {
                $updateQuery = "UPDATE settings SET setting_value = ? WHERE setting_key = ? AND setting_scope = 'system'";
                $affected = $this->db->update($updateQuery, [$value, $key]);
                
                // If setting doesn't exist, create it
                if ($affected === 0) {
                    $insertQuery = "INSERT INTO settings (setting_key, setting_value, setting_scope) VALUES (?, ?, 'system')";
                    $this->db->insert($insertQuery, [$key, $value]);
                }
            }
            
            // Commit transaction
            $this->db->commitTransaction();
            return true;
        } catch (Exception $e) {
            // Rollback on error
            if ($this->db->inTransaction()) {
                $this->db->rollbackTransaction();
            }
            error_log('UpdateSystemSettings Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Get user settings
    public function getUserSettings($userId) {
        try {
            // Ensure table exists
            $this->createSettingsTable();
            
            $query = "SELECT * FROM settings WHERE setting_scope = 'user' AND user_id = ?";
            $result = $this->db->select($query, [$userId]);
            
            // Format into a key-value array
            $settings = [];
            if (!empty($result)) {
                foreach ($result as $row) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            }
            
            // Set default user settings if they don't exist
            if (empty($settings['theme'])) {
                $settings['theme'] = 'light';
            }
            if (empty($settings['notifications_enabled'])) {
                $settings['notifications_enabled'] = '1';
            }
            
            return $settings;
        } catch (Exception $e) {
            error_log('GetUserSettings Error: ' . $e->getMessage());
            
            // Return default values on error
            return [
                'theme' => 'light',
                'notifications_enabled' => '1'
            ];
        }
    }
    
    // Update user settings
    public function updateUserSetting($userId, $key, $value) {
        try {
            // Ensure table exists
            $this->createSettingsTable();
            
            $updateQuery = "UPDATE settings SET setting_value = ? WHERE setting_key = ? AND setting_scope = 'user' AND user_id = ?";
            $affected = $this->db->update($updateQuery, [$value, $key, $userId]);
            
            // If setting doesn't exist, create it
            if ($affected === 0) {
                $insertQuery = "INSERT INTO settings (setting_key, setting_value, setting_scope, user_id) VALUES (?, ?, 'user', ?)";
                $this->db->insert($insertQuery, [$key, $value, $userId]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('UpdateUserSetting Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Create settings table if it doesn't exist
    public function createSettingsTable() {
        try {
            $query = "IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[settings]') AND type in (N'U'))
            BEGIN
                CREATE TABLE [dbo].[settings] (
                    [id] INT IDENTITY(1,1) PRIMARY KEY,
                    [setting_key] NVARCHAR(255) NOT NULL,
                    [setting_value] NVARCHAR(MAX),
                    [setting_scope] NVARCHAR(50) NOT NULL DEFAULT 'system',
                    [user_id] INT NULL,
                    [created_at] DATETIME DEFAULT GETDATE(),
                    [updated_at] DATETIME DEFAULT GETDATE(),
                    CONSTRAINT [uk_setting] UNIQUE ([setting_key], [setting_scope], [user_id])
                )
            END";
            
            // Use a direct query instead of execute()
            return $this->db->query($query);
        } catch (Exception $e) {
            error_log('CreateSettingsTable Error: ' . $e->getMessage());
            return false;
        }
    }
} 