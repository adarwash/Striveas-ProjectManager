<?php

class User {
    private $db;
    
    /**
     * Constructor - initializes the database connection
     */
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }
    
    /**
     * Authenticate a user by username and password
     *
     * @param string $username
     * @param string $password
     * @return array|bool User data if authenticated, false otherwise
     */
    public function authenticate(string $username, string $password): array|bool {
        try {
            // Query to find the user by username
            // Adjust this query based on your actual database schema
            $query = "SELECT * FROM [Users] WHERE username = ?";
            $result = $this->db->select($query, [$username]);
            
            if (empty($result)) {
                return false; // User not found
            }
            
            $user = $result[0];
            
            // Verify password - this assumes passwords are stored as hashes
            // In a real application, you should use password_hash() and password_verify()
            // For demo purposes, we're doing a simple comparison
            // IMPORTANT: In production, always use secure password hashing
            if ($user['password'] === $password) {
                // Remove password from the user data before returning
                unset($user['password']);
                return $user;
            }
            
            return false; // Invalid password
            
        } catch (Exception $e) {
            // Log the error
            error_log('Authentication Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get a user by their ID
     *
     * @param int $userId
     * @return array|bool User data if found, false otherwise
     */
    public function getUserById(int $userId): array|bool {
        try {
            $query = "SELECT * FROM [Users] WHERE id = ?";
            $result = $this->db->select($query, [$userId]);
            
            if (empty($result)) {
                return false;
            }
            
            $user = $result[0];
            unset($user['password']); // Don't return the password
            
            return $user;
        } catch (Exception $e) {
            error_log('GetUserById Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all users
     * 
     * @return array Array of user objects with needed fields for admin
     */
    public function getAllUsers(): array {
        try {
            $query = "SELECT id, username as name, email, full_name, 
                     role, created_at, last_login 
                     FROM [Users] 
                     WHERE is_active = 1 
                     ORDER BY created_at DESC";
            $result = $this->db->select($query);
            
            return $result ?: [];
        } catch (Exception $e) {
            error_log('GetAllUsers Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update user profile with extended information
     *
     * @param array $data The data to update
     * @return bool True if update successful, false otherwise
     */
    public function updateUserProfile(array $data): bool {
        try {
            $query = "UPDATE [Users] SET 
                      full_name = ?, 
                      email = ?, 
                      position = ?, 
                      bio = ? 
                      WHERE id = ?";
            $params = [
                $data['full_name'], 
                $data['email'], 
                $data['position'] ?? null, 
                $data['bio'] ?? null, 
                $data['user_id']
            ];
            
            $this->db->update($query, $params);
            return true;
        } catch (Exception $e) {
            error_log('UpdateUserProfile Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if the provided password matches the user's current password
     *
     * @param int $userId The user ID
     * @param string $password The password to check
     * @return bool True if password matches, false otherwise
     */
    public function checkPassword(int $userId, string $password): bool {
        try {
            $query = "SELECT password FROM [Users] WHERE id = ?";
            $result = $this->db->select($query, [$userId]);
            
            if (empty($result)) {
                return false;
            }
            
            // In a real application, you would use password_verify() here
            return $result[0]['password'] === $password;
        } catch (Exception $e) {
            error_log('CheckPassword Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user's password
     *
     * @param int $userId The user ID
     * @param string $newPassword The new password
     * @return bool True if update successful, false otherwise
     */
    public function updatePassword(int $userId, string $newPassword): bool {
        try {
            // In a real application, you would use password_hash() here
            $query = "UPDATE [Users] SET password = ? WHERE id = ?";
            $params = [$newPassword, $userId];
            
            $this->db->update($query, $params);
            return true;
        } catch (Exception $e) {
            error_log('UpdatePassword Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user's notification settings
     *
     * @param array $data The notification settings data
     * @return bool True if update successful, false otherwise
     */
    public function updateNotificationSettings(array $data): bool {
        try {
            // Check if user_settings table entry exists for this user
            $checkQuery = "SELECT COUNT(*) as count FROM UserSettings WHERE user_id = ?";
            $result = $this->db->select($checkQuery, [$data['user_id']]);
            
            if ($result[0]['count'] > 0) {
                // Update existing settings
                $query = "UPDATE UserSettings SET 
                         email_notifications = ?, 
                         task_reminders = ?, 
                         project_updates = ? 
                         WHERE user_id = ?";
                $params = [
                    $data['email_notifications'], 
                    $data['task_reminders'], 
                    $data['project_updates'], 
                    $data['user_id']
                ];
                
                $this->db->update($query, $params);
            } else {
                // Insert new settings
                $query = "INSERT INTO UserSettings 
                         (user_id, email_notifications, task_reminders, project_updates) 
                         VALUES (?, ?, ?, ?)";
                $params = [
                    $data['user_id'], 
                    $data['email_notifications'], 
                    $data['task_reminders'], 
                    $data['project_updates']
                ];
                
                $this->db->insert($query, $params);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('UpdateNotificationSettings Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's notification settings
     *
     * @param int $userId The user ID
     * @return array The notification settings
     */
    public function getNotificationSettings(int $userId): array {
        try {
            $query = "SELECT * FROM UserSettings WHERE user_id = ?";
            $result = $this->db->select($query, [$userId]);
            
            if (empty($result)) {
                // Return default settings if none exist
                return [
                    'email_notifications' => 1,
                    'task_reminders' => 1,
                    'project_updates' => 1
                ];
            }
            
            return $result[0];
        } catch (Exception $e) {
            error_log('GetNotificationSettings Error: ' . $e->getMessage());
            // Return default settings on error
            return [
                'email_notifications' => 1,
                'task_reminders' => 1,
                'project_updates' => 1
            ];
        }
    }
    
    /**
     * Create the UserSettings table if it doesn't exist
     * 
     * @return bool True if successful, false otherwise
     */
    public function createUserSettingsTable(): bool {
        try {
            // Get the SQL to create the UserSettings table
            $sql = file_get_contents('../app/sql/create_user_settings_table.sql');
            
            if (!$sql) {
                error_log('Could not read create_user_settings_table.sql file');
                return false;
            }
            
            // Use query() instead of execute()
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log('CreateUserSettingsTable Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's recent activity
     *
     * @param int $userId The user ID
     * @param int $limit The maximum number of activities to return (default 10)
     * @return array Array of recent activities
     */
    public function getUserRecentActivity(int $userId, int $limit = 10): array {
        try {
            // This is for MS SQL Server
            $query = "
                SELECT TOP $limit * FROM (
                    SELECT 'project' as type, p.title, p.created_at as activity_date, 'created' as action
                    FROM Projects p 
                    WHERE p.user_id = ?
                    
                    UNION ALL
                    
                    SELECT 'task' as type, t.title, t.created_at as activity_date, 'created' as action
                    FROM Tasks t 
                    WHERE t.created_by = ?
                    
                    UNION ALL
                    
                    SELECT 'task' as type, t.title, t.updated_at as activity_date, 'updated' as action
                    FROM Tasks t 
                    WHERE t.created_by = ? AND t.updated_at != t.created_at
                ) AS combined
                ORDER BY activity_date DESC";
                
            $params = [$userId, $userId, $userId];
            $result = $this->db->select($query, $params);
            
            return $result ?: [];
        } catch (Exception $e) {
            error_log('GetUserRecentActivity Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user's skills
     *
     * @param int $userId The user ID
     * @return array Array of user skills
     */
    public function getUserSkills(int $userId): array {
        try {
            $query = "SELECT s.* FROM Skills s
                     INNER JOIN UserSkills us ON s.id = us.skill_id
                     WHERE us.user_id = ?
                     ORDER BY s.name";
            $result = $this->db->select($query, [$userId]);
            
            return $result ?: [];
        } catch (Exception $e) {
            error_log('GetUserSkills Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update user skills
     * 
     * @param int $userId User ID
     * @param array $skills Array of skill IDs
     * @return bool True if update successful, false otherwise
     */
    public function updateUserSkills(int $userId, array $skills): bool {
        try {
            // Begin transaction
            $this->db->beginTransaction();
            
            // Delete all existing user skills
            $deleteQuery = "DELETE FROM UserSkills WHERE user_id = ?";
            $this->db->update($deleteQuery, [$userId]);
            
            // Insert new skills
            if (!empty($skills)) {
                foreach ($skills as $skillId) {
                    $insertQuery = "INSERT INTO UserSkills (user_id, skill_id) VALUES (?, ?)";
                    $this->db->insert($insertQuery, [$userId, $skillId]);
                }
            }
            
            // Commit transaction
            $this->db->commitTransaction();
            
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            if ($this->db->inTransaction()) {
                $this->db->rollbackTransaction();
            }
            error_log('UpdateUserSkills Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all available skills
     *
     * @return array Array of all skills
     */
    public function getAllSkills(): array {
        try {
            $query = "SELECT * FROM Skills ORDER BY name";
            $result = $this->db->select($query);
            
            return $result ?: [];
        } catch (Exception $e) {
            error_log('GetAllSkills Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create the Skills and UserSkills tables if they don't exist
     * 
     * @return bool True if successful, false otherwise
     */
    public function createSkillsTables(): bool {
        try {
            // Create Skills table
            $skillsTableSql = "
            IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[Skills]') AND type in (N'U'))
            BEGIN
                CREATE TABLE [dbo].[Skills] (
                    [id] INT IDENTITY(1,1) PRIMARY KEY,
                    [name] NVARCHAR(100) NOT NULL,
                    [description] NVARCHAR(255) NULL,
                    [category] NVARCHAR(50) NULL,
                    [created_at] DATETIME DEFAULT GETDATE()
                )
            END";
            
            $this->db->query($skillsTableSql);
            
            // Create UserSkills table
            $userSkillsTableSql = "
            IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[UserSkills]') AND type in (N'U'))
            BEGIN
                CREATE TABLE [dbo].[UserSkills] (
                    [id] INT IDENTITY(1,1) PRIMARY KEY,
                    [user_id] INT NOT NULL,
                    [skill_id] INT NOT NULL,
                    [proficiency_level] INT DEFAULT 1,
                    [created_at] DATETIME DEFAULT GETDATE(),
                    CONSTRAINT [FK_UserSkills_Users] FOREIGN KEY ([user_id]) REFERENCES [Users]([id]) ON DELETE CASCADE,
                    CONSTRAINT [FK_UserSkills_Skills] FOREIGN KEY ([skill_id]) REFERENCES [Skills]([id]) ON DELETE CASCADE
                )
            END";
            
            $this->db->query($userSkillsTableSql);
            
            return true;
        } catch (Exception $e) {
            error_log('CreateSkillsTables Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Initialize example skills
     * 
     * @return bool True if successful, false otherwise
     */
    public function initializeExampleSkills(): bool {
        try {
            // Get the SQL to add example skills
            $sql = file_get_contents('../app/sql/add_example_skills.sql');
            
            if (!$sql) {
                error_log('Could not read add_example_skills.sql file');
                return false;
            }
            
            // Use query() to execute the SQL
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log('InitializeExampleSkills Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update Users table with profile-related columns
     * 
     * @return bool True if successful, false otherwise
     */
    public function updateUsersTable(): bool {
        try {
            // First check if we have an SQL file for updating the users table
            $sqlFile = '../app/sql/update_users_table.sql';
            
            if (file_exists($sqlFile) && ($sql = file_get_contents($sqlFile))) {
                // If we have the SQL file, execute it
                $this->db->query($sql);
            }
            
            // Also ensure profile_picture column exists in the users table (MS SQL Server syntax)
            $sql = "IF NOT EXISTS (
                    SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME = 'users' 
                    AND COLUMN_NAME = 'profile_picture'
                )
                BEGIN
                    ALTER TABLE users ADD profile_picture VARCHAR(255) NULL
                END";
            
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log('UpdateUsersTable Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user's profile picture
     * 
     * @param int $userId User ID
     * @param string $filename New profile picture filename
     * @return bool True if successful, false otherwise
     */
    public function updateProfilePicture($userId, $filename) {
        try {
            $sql = "UPDATE [users] SET profile_picture = ? WHERE id = ?";
            return $this->db->update($sql, [$filename, $userId]);
            
        } catch (Exception $e) {
            error_log('UpdateProfilePicture Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get default profile picture URL
     * 
     * @param string $name User's name for initials
     * @return string URL for default profile picture
     */
    public function getDefaultProfilePicture($name) {
        // Use UI Avatars to generate a default profile picture based on initials
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random&size=256';
    }

    // Get total number of users
    public function getTotalUsers() {
        try {
            $result = $this->db->select("SELECT COUNT(*) as total FROM [users]");
            return $result[0]['total'] ?? 0;
        } catch (Exception $e) {
            error_log('GetTotalUsers Error: ' . $e->getMessage());
            return 0;
        }
    }

    // Get recent users for admin dashboard
    public function getRecentUsers($limit = 5) {
        try {
            return $this->db->select("SELECT id, name, email, role, created_at FROM [users] ORDER BY created_at DESC LIMIT ?", [$limit]);
        } catch (Exception $e) {
            error_log('GetRecentUsers Error: ' . $e->getMessage());
            return [];
        }
    }

    // Update user role (supports both old role field and new role_id)
    public function updateUserRole($userId, $role) {
        try {
            $sql = "UPDATE [users] SET role = ? WHERE id = ?";
            return $this->db->update($sql, [$role, $userId]);
        } catch (Exception $e) {
            error_log('UpdateUserRole Error: ' . $e->getMessage());
            return false;
        }
    }

    // Delete user
    public function deleteUser($userId) {
        try {
            $sql = "DELETE FROM [users] WHERE id = ?";
            return $this->db->remove($sql, [$userId]);
        } catch (Exception $e) {
            error_log('DeleteUser Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Register a new user
     * 
     * @param array $data User data including name, email, password (hashed), and role
     * @return bool True if successful, false otherwise
     */
    public function register(array $data): bool {
        try {
            $sql = "INSERT INTO [users] (username, password, email, full_name, role, created_at) 
                   VALUES (?, ?, ?, ?, ?, GETDATE())";
            
            $result = $this->db->insert($sql, [
                $data['name'], // This will be used as the username
                $data['password'],
                $data['email'],
                $data['name'], // Using the name as full_name as well since we don't have a separate field
                $data['role']
            ]);
            
            // Insert returns the inserted ID or null, we need to convert to boolean
            return $result !== null;
        } catch (Exception $e) {
            error_log('Register User Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a user without changing password
     * 
     * @param array $data User data including id, name, email, and role
     * @return bool True if successful, false otherwise
     */
    public function updateUser(array $data): bool {
        try {
            $sql = "UPDATE [users] SET 
                    username = ?, 
                    email = ?,
                    full_name = ?,
                    role = ? 
                    WHERE id = ?";
            
            $this->db->update($sql, [
                $data['name'],
                $data['email'],
                $data['name'], // Using name for full_name as well
                $data['role'],
                $data['id']
            ]);
            
            // If we get here, the update was successful (no exception was thrown)
            return true;
        } catch (Exception $e) {
            error_log('UpdateUser Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a user including password
     * 
     * @param array $data User data including id, name, email, password, and role
     * @return bool True if successful, false otherwise
     */
    public function updateUserWithPassword(array $data): bool {
        try {
            $sql = "UPDATE [users] SET 
                    username = ?, 
                    email = ?,
                    full_name = ?,
                    password = ?,
                    role = ? 
                    WHERE id = ?";
            
            $this->db->update($sql, [
                $data['name'],
                $data['email'],
                $data['name'], // Using name for full_name as well
                $data['password'], // This is already hashed in the controller
                $data['role'],
                $data['id']
            ]);
            
            // If we get here, the update was successful (no exception was thrown)
            return true;
        } catch (Exception $e) {
            error_log('UpdateUserWithPassword Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user's role_id (for new role-based permission system)
     * 
     * @param array $data User data with role_id
     * @return bool True if successful, false otherwise
     */
    public function updateUserRoleId(array $data): bool {
        try {
            $query = "UPDATE [Users] SET role_id = ? WHERE id = ?";
            $params = [$data['role_id'], $data['id']];
            
            $this->db->update($query, $params);
            return true;
        } catch (Exception $e) {
            error_log('UpdateUserRoleId Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user with role information
     * 
     * @param int $userId User ID
     * @return array|bool User data with role info, false if not found
     */
    public function getUserWithRole(int $userId): array|bool {
        try {
            $query = "SELECT u.*, r.name as role_name, r.display_name as role_display_name, r.description as role_description
                     FROM [Users] u
                     LEFT JOIN [Roles] r ON u.role_id = r.id
                     WHERE u.id = ?";
            $result = $this->db->select($query, [$userId]);
            
            if (empty($result)) {
                return false;
            }
            
            $user = $result[0];
            unset($user['password']); // Don't return the password
            
            return $user;
        } catch (Exception $e) {
            error_log('GetUserWithRole Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all users with their role information
     * 
     * @return array Array of users with role information
     */
    public function getAllUsersWithRoles(): array {
        try {
            $query = "SELECT u.id, u.username as name, u.email, u.full_name, 
                     u.role, u.role_id, u.created_at, u.last_login,
                     r.display_name as role_display_name, r.description as role_description
                     FROM [Users] u
                     LEFT JOIN [Roles] r ON u.role_id = r.id
                     WHERE u.is_active = 1 
                     ORDER BY u.created_at DESC";
            $result = $this->db->select($query);
            
            return $result ?: [];
        } catch (Exception $e) {
            error_log('GetAllUsersWithRoles Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if user has specific permission
     * 
     * @param int $userId User ID
     * @param string $permissionName Permission name
     * @return bool True if user has permission, false otherwise
     */
    public function hasPermission(int $userId, string $permissionName): bool {
        try {
            // Check direct user permissions first
            $query = "SELECT up.granted 
                     FROM [UserPermissions] up
                     INNER JOIN [Permissions] p ON up.permission_id = p.id
                     WHERE up.user_id = ? AND p.name = ?";
            $result = $this->db->select($query, [$userId, $permissionName]);
            
            if (!empty($result)) {
                return $result[0]['granted'] == 1;
            }
            
            // Check role-based permissions
            $query = "SELECT COUNT(*) as count
                     FROM [Users] u
                     INNER JOIN [Roles] r ON u.role_id = r.id
                     INNER JOIN [RolePermissions] rp ON r.id = rp.role_id
                     INNER JOIN [Permissions] p ON rp.permission_id = p.id
                     WHERE u.id = ? AND p.name = ? AND r.is_active = 1 AND p.is_active = 1";
            $result = $this->db->select($query, [$userId, $permissionName]);
            
            return !empty($result) && $result[0]['count'] > 0;
        } catch (Exception $e) {
            error_log('HasPermission Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's permissions
     * 
     * @param int $userId User ID
     * @return array Array of permission names
     */
    public function getUserPermissions(int $userId): array {
        try {
            // Get permissions from role and direct assignments
            $query = "SELECT DISTINCT p.name 
                     FROM [Permissions] p
                     WHERE p.id IN (
                         -- Role-based permissions
                         SELECT rp.permission_id 
                         FROM [RolePermissions] rp
                         INNER JOIN [Roles] r ON rp.role_id = r.id
                         INNER JOIN [Users] u ON r.id = u.role_id
                         WHERE u.id = ? AND r.is_active = 1 AND p.is_active = 1
                         
                         UNION
                         
                         -- Direct user permissions
                         SELECT up.permission_id
                         FROM [UserPermissions] up
                         WHERE up.user_id = ? AND up.granted = 1
                     )
                     AND p.is_active = 1";
            $result = $this->db->select($query, [$userId, $userId]);
            
            return array_column($result ?: [], 'name');
        } catch (Exception $e) {
            error_log('GetUserPermissions Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create permissions tables if they don't exist
     * 
     * @return bool True if successful, false otherwise
     */
    public function createPermissionTables(): bool {
        try {
            // Get the SQL to create permission tables
            $sql = file_get_contents('../sql/create_permissions_tables.sql');
            
            if (!$sql) {
                error_log('Could not read create_permissions_tables.sql file');
                return false;
            }
            
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log('CreatePermissionTables Error: ' . $e->getMessage());
            return false;
        }
    }
}

?> 