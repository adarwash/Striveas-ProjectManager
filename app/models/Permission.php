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
        $this->db->query('SELECT * FROM dbo.Permissions WHERE name = :name');
        $this->db->bind(':name', $name);
        return $this->db->single();
    }
    
    // Create new permission
    public function createPermission($data) {
        $this->db->query('INSERT INTO dbo.Permissions (name, display_name, description, module, action) 
                         VALUES (:name, :display_name, :description, :module, :action)');
        
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':display_name', $data['display_name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':module', $data['module']);
        $this->db->bind(':action', $data['action']);
        
        return $this->db->execute();
    }
    
    // Update permission
    public function updatePermission($data) {
        $this->db->query('UPDATE dbo.Permissions 
                         SET display_name = :display_name, 
                             description = :description, 
                             module = :module, 
                             action = :action,
                             is_active = :is_active
                         WHERE id = :id');
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':display_name', $data['display_name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':module', $data['module']);
        $this->db->bind(':action', $data['action']);
        $this->db->bind(':is_active', $data['is_active'] ?? 1);
        
        return $this->db->execute();
    }
    
    // Delete permission
    public function deletePermission($id) {
        // First remove all role-permission associations
        $this->db->query('DELETE FROM dbo.RolePermissions WHERE permission_id = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();
        
        // Then remove all user-permission associations
        $this->db->query('DELETE FROM dbo.UserPermissions WHERE permission_id = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();
        
        // Finally delete the permission
        $this->db->query('DELETE FROM dbo.Permissions WHERE id = :id');
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
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
            
            // Check role-based permissions
            $query = 'SELECT COUNT(*) as count
                     FROM dbo.Users u
                     INNER JOIN dbo.Roles r ON u.role_id = r.id
                     INNER JOIN dbo.RolePermissions rp ON r.id = rp.role_id
                     INNER JOIN dbo.Permissions p ON rp.permission_id = p.id
                     WHERE u.id = ? AND p.name = ? AND r.is_active = 1 AND p.is_active = 1';
            $result = $this->db->select($query, [$userId, $permissionName]);
            
            return !empty($result) && $result[0]['count'] > 0;
        } catch (Exception $e) {
            error_log('UserHasPermission Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Get user's permissions
    public function getUserPermissions($userId) {
        // Get permissions from role
        $this->db->query('SELECT DISTINCT p.* 
                         FROM dbo.Permissions p
                         INNER JOIN dbo.RolePermissions rp ON p.id = rp.permission_id
                         INNER JOIN dbo.Roles r ON rp.role_id = r.id
                         INNER JOIN dbo.Users u ON r.id = u.role_id
                         WHERE u.id = :user_id AND r.is_active = 1 AND p.is_active = 1
                         
                         UNION
                         
                         SELECT DISTINCT p.*
                         FROM dbo.Permissions p
                         INNER JOIN dbo.UserPermissions up ON p.id = up.permission_id
                         WHERE up.user_id = :user_id AND up.granted = 1 AND p.is_active = 1
                         
                         ORDER BY module, action, display_name');
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }
    
    // Get modules available for permissions
    public function getAvailableModules() {
        $this->db->query('SELECT DISTINCT module FROM dbo.Permissions ORDER BY module');
        $results = $this->db->resultSet();
        return array_column($results, 'module');
    }
    
    // Get actions available for permissions
    public function getAvailableActions() {
        $this->db->query('SELECT DISTINCT action FROM dbo.Permissions ORDER BY action');
        $results = $this->db->resultSet();
        return array_column($results, 'action');
    }
    
    // Grant permission to user
    public function grantPermissionToUser($userId, $permissionId) {
        $this->db->query('INSERT INTO dbo.UserPermissions (user_id, permission_id, granted) 
                         VALUES (:user_id, :permission_id, 1)
                         ON DUPLICATE KEY UPDATE granted = 1');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':permission_id', $permissionId);
        return $this->db->execute();
    }
    
    // Revoke permission from user
    public function revokePermissionFromUser($userId, $permissionId) {
        $this->db->query('DELETE FROM dbo.UserPermissions 
                         WHERE user_id = :user_id AND permission_id = :permission_id');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':permission_id', $permissionId);
        return $this->db->execute();
    }
    
    // Sync user permissions (bulk update)
    public function syncUserPermissions($userId, $permissionIds) {
        // First remove all existing permissions for this user
        $this->db->query('DELETE FROM dbo.UserPermissions WHERE user_id = :user_id');
        $this->db->bind(':user_id', $userId);
        $this->db->execute();
        
        // Then add the new permissions
        if (!empty($permissionIds)) {
            $placeholders = str_repeat('(?,?,1),', count($permissionIds));
            $placeholders = rtrim($placeholders, ',');
            
            $this->db->query("INSERT INTO dbo.UserPermissions (user_id, permission_id, granted) VALUES $placeholders");
            
            $bindIndex = 1;
            foreach ($permissionIds as $permissionId) {
                $this->db->bind($bindIndex++, $userId);
                $this->db->bind($bindIndex++, $permissionId);
            }
            
            return $this->db->execute();
        }
        
        return true;
    }
} 