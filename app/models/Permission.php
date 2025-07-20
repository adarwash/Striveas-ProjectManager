<?php
class Permission {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }
    
    // Get all permissions
    public function getAllPermissions() {
        $query = 'SELECT * FROM dbo.Permissions WHERE is_active = 1 ORDER BY module, action, display_name';
        return $this->db->select($query) ?: [];
    }
    
    // Get permissions by module
    public function getPermissionsByModule($module = null) {
        if ($module) {
            $query = 'SELECT * FROM dbo.Permissions WHERE module = ? AND is_active = 1 ORDER BY action, display_name';
            return $this->db->select($query, [$module]) ?: [];
        } else {
            $query = 'SELECT * FROM dbo.Permissions WHERE is_active = 1 ORDER BY module, action, display_name';
            return $this->db->select($query) ?: [];
        }
    }
    
    // Get permissions grouped by module
    public function getPermissionsGroupedByModule() {
        $permissions = $this->getAllPermissions();
        $grouped = [];
        
        foreach ($permissions as $permission) {
            $module = $permission['module'];
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = $permission;
        }
        
        return $grouped;
    }
    
    // Get permission by ID
    public function getPermissionById($id) {
        $query = 'SELECT * FROM dbo.Permissions WHERE id = ?';
        $result = $this->db->select($query, [$id]);
        return !empty($result) ? $result[0] : false;
    }
    
    // Get permission by name
    public function getPermissionByName($name) {
        $query = 'SELECT * FROM dbo.Permissions WHERE name = ?';
        $result = $this->db->select($query, [$name]);
        return !empty($result) ? $result[0] : false;
    }
    
    // Create new permission
    public function createPermission($data) {
        try {
            $query = 'INSERT INTO dbo.Permissions (name, display_name, description, module, action) VALUES (?, ?, ?, ?, ?)';
            $params = [
                $data['name'],
                $data['display_name'],
                $data['description'],
                $data['module'],
                $data['action']
            ];
            
            $this->db->insert($query, $params);
            return true;
        } catch (Exception $e) {
            error_log('CreatePermission Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Update permission
    public function updatePermission($data) {
        try {
            $query = 'UPDATE dbo.Permissions 
                     SET display_name = ?, description = ?, module = ?, action = ?, is_active = ?
                     WHERE id = ?';
            $params = [
                $data['display_name'],
                $data['description'],
                $data['module'],
                $data['action'],
                $data['is_active'] ?? 1,
                $data['id']
            ];
            
            $this->db->update($query, $params);
            return true;
        } catch (Exception $e) {
            error_log('UpdatePermission Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Delete permission
    public function deletePermission($id) {
        try {
        // First remove all role-permission associations
            $this->db->remove('DELETE FROM dbo.RolePermissions WHERE permission_id = ?', [$id]);
        
        // Then remove all user-permission associations
            $this->db->remove('DELETE FROM dbo.UserPermissions WHERE permission_id = ?', [$id]);
        
        // Finally delete the permission
            $this->db->remove('DELETE FROM dbo.Permissions WHERE id = ?', [$id]);
        
            return true;
        } catch (Exception $e) {
            error_log('DeletePermission Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Check if user has permission
    public function userHasPermission($userId, $permissionName) {
        try {
            // Check direct user permissions first
            $query = 'SELECT up.granted 
                     FROM dbo.UserPermissions up
                     INNER JOIN dbo.Permissions p ON up.permission_id = p.id
                     WHERE up.user_id = ? AND p.name = ?';
            $result = $this->db->select($query, [$userId, $permissionName]);
            
            if (!empty($result)) {
                return $result[0]['granted'] == 1;
            }
            
            // Check role-based permissions - support both new role_id and old role field
            $query = 'SELECT COUNT(*) as count
                     FROM dbo.Users u
                     INNER JOIN dbo.Roles r ON (u.role_id = r.id OR u.role = r.name)
                     INNER JOIN dbo.RolePermissions rp ON r.id = rp.role_id
                     INNER JOIN dbo.Permissions p ON rp.permission_id = p.id
                     WHERE u.id = ? AND p.name = ? AND r.is_active = 1 AND p.is_active = 1 AND u.is_active = 1';
            $result = $this->db->select($query, [$userId, $permissionName]);
            
            return !empty($result) && $result[0]['count'] > 0;
        } catch (Exception $e) {
            error_log('UserHasPermission Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Get user's permissions
    public function getUserPermissions($userId) {
        try {
            // Get permissions from both role and direct user assignments - support both role_id and role field
            $query = 'SELECT DISTINCT p.id, p.name, p.display_name, p.description, p.module, p.action, p.is_active, p.created_at
                         FROM dbo.Permissions p
                         INNER JOIN dbo.RolePermissions rp ON p.id = rp.permission_id
                         INNER JOIN dbo.Roles r ON rp.role_id = r.id
                         INNER JOIN dbo.Users u ON (r.id = u.role_id OR r.name = u.role)
                     WHERE u.id = ? AND r.is_active = 1 AND p.is_active = 1 AND u.is_active = 1
                         
                         UNION
                         
                     SELECT DISTINCT p.id, p.name, p.display_name, p.description, p.module, p.action, p.is_active, p.created_at
                         FROM dbo.Permissions p
                         INNER JOIN dbo.UserPermissions up ON p.id = up.permission_id
                     WHERE up.user_id = ? AND up.granted = 1 AND p.is_active = 1
                         
                     ORDER BY module, action, display_name';
            
            return $this->db->select($query, [$userId, $userId]) ?: [];
        } catch (Exception $e) {
            error_log('GetUserPermissions Error: ' . $e->getMessage());
            error_log('GetUserPermissions Query: ' . $query);
            error_log('GetUserPermissions UserId: ' . $userId);
            return [];
        }
    }
    
    // Get modules available for permissions
    public function getAvailableModules() {
        try {
            $query = 'SELECT DISTINCT module FROM dbo.Permissions ORDER BY module';
            $results = $this->db->select($query);
            return array_column($results ?: [], 'module');
        } catch (Exception $e) {
            error_log('GetAvailableModules Error: ' . $e->getMessage());
            return [];
        }
    }
    
    // Get actions available for permissions
    public function getAvailableActions() {
        try {
            $query = 'SELECT DISTINCT action FROM dbo.Permissions ORDER BY action';
            $results = $this->db->select($query);
            return array_column($results ?: [], 'action');
        } catch (Exception $e) {
            error_log('GetAvailableActions Error: ' . $e->getMessage());
            return [];
        }
    }
    
    // Grant permission to user
    public function grantPermissionToUser($userId, $permissionId) {
        try {
            // Check if permission already exists
            $existing = $this->db->select('SELECT id FROM dbo.UserPermissions WHERE user_id = ? AND permission_id = ?', [$userId, $permissionId]);
            
            if (empty($existing)) {
                // Insert new permission
                $this->db->insert('INSERT INTO dbo.UserPermissions (user_id, permission_id, granted) VALUES (?, ?, 1)', [$userId, $permissionId]);
            } else {
                // Update existing permission
                $this->db->update('UPDATE dbo.UserPermissions SET granted = 1 WHERE user_id = ? AND permission_id = ?', [$userId, $permissionId]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('GrantPermissionToUser Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Revoke permission from user
    public function revokePermissionFromUser($userId, $permissionId) {
        try {
            $this->db->remove('DELETE FROM dbo.UserPermissions WHERE user_id = ? AND permission_id = ?', [$userId, $permissionId]);
            return true;
        } catch (Exception $e) {
            error_log('RevokePermissionFromUser Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Sync user permissions (bulk update)
    public function syncUserPermissions($userId, $permissionIds) {
        try {
            // Start transaction for data consistency
            $this->db->beginTransaction();
            
        // First remove all existing permissions for this user
            $this->db->remove('DELETE FROM dbo.UserPermissions WHERE user_id = ?', [$userId]);
        
        // Then add the new permissions
        if (!empty($permissionIds)) {
                foreach ($permissionIds as $permissionId) {
                    // Validate permission ID is numeric
                    if (!is_numeric($permissionId)) {
                        error_log("SyncUserPermissions: Invalid permission ID: $permissionId");
                        continue;
                    }
                    
                    // Insert new permission
                    $this->db->insert('INSERT INTO dbo.UserPermissions (user_id, permission_id, granted) VALUES (?, ?, 1)', [$userId, $permissionId]);
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
            error_log('SyncUserPermissions Error: ' . $e->getMessage());
            return false;
        }
    }
} 