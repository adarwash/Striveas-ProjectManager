<?php
class Role {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }
    
    // Get all roles
    public function getAllRoles() {
        $query = 'SELECT * FROM dbo.Roles WHERE is_active = 1 ORDER BY display_name';
        return $this->db->select($query) ?: [];
    }
    
    // Get role by ID
    public function getRoleById($id) {
        $query = 'SELECT * FROM dbo.Roles WHERE id = ?';
        $result = $this->db->select($query, [$id]);
        return !empty($result) ? $result[0] : false;
    }
    
    // Get role by name
    public function getRoleByName($name) {
        $query = 'SELECT * FROM dbo.Roles WHERE name = ?';
        $result = $this->db->select($query, [$name]);
        return !empty($result) ? $result[0] : false;
    }
    
    // Create new role
    public function createRole($data) {
        try {
            $query = 'INSERT INTO dbo.Roles (name, display_name, description) VALUES (?, ?, ?)';
            $result = $this->db->insert($query, [$data['name'], $data['display_name'], $data['description']]);
            return $result !== null ? $result : false;
        } catch (Exception $e) {
            error_log('CreateRole Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Update role
    public function updateRole($data) {
        try {
            $query = 'UPDATE dbo.Roles 
                     SET display_name = ?, 
                         description = ?,
                         is_active = ?,
                         updated_at = GETDATE()
                     WHERE id = ?';
            
            $this->db->update($query, [
                $data['display_name'],
                $data['description'],
                $data['is_active'] ?? 1,
                $data['id']
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log('UpdateRole Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Delete role
    public function deleteRole($id) {
        try {
        // First check if any users are assigned to this role
            $result = $this->db->select('SELECT COUNT(*) as user_count FROM dbo.Users WHERE role_id = ?', [$id]);
        
            if (!empty($result) && $result[0]['user_count'] > 0) {
            return false; // Cannot delete role with assigned users
        }
        
        // Remove all role-permission associations
            $this->db->remove('DELETE FROM dbo.RolePermissions WHERE role_id = ?', [$id]);
        
        // Delete the role
            $this->db->remove('DELETE FROM dbo.Roles WHERE id = ?', [$id]);
        
            return true;
        } catch (Exception $e) {
            error_log('DeleteRole Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Get role permissions
    public function getRolePermissions($roleId) {
        try {
            $query = 'SELECT p.* 
                         FROM dbo.Permissions p
                         INNER JOIN dbo.RolePermissions rp ON p.id = rp.permission_id
                     WHERE rp.role_id = ? AND p.is_active = 1
                     ORDER BY p.module, p.action, p.display_name';
            return $this->db->select($query, [$roleId]) ?: [];
        } catch (Exception $e) {
            error_log('GetRolePermissions Error: ' . $e->getMessage());
            return [];
        }
    }
    
    // Get role permissions grouped by module
    public function getRolePermissionsGrouped($roleId) {
        $permissions = $this->getRolePermissions($roleId);
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
    
    // Assign permission to role
    public function assignPermissionToRole($roleId, $permissionId) {
        try {
            $this->db->insert('INSERT INTO dbo.RolePermissions (role_id, permission_id) VALUES (?, ?)', [$roleId, $permissionId]);
            return true;
        } catch (Exception $e) {
            error_log('AssignPermissionToRole Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Remove permission from role
    public function removePermissionFromRole($roleId, $permissionId) {
        try {
            $this->db->remove('DELETE FROM dbo.RolePermissions WHERE role_id = ? AND permission_id = ?', [$roleId, $permissionId]);
            return true;
        } catch (Exception $e) {
            error_log('RemovePermissionFromRole Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Sync role permissions (bulk update)
    public function syncRolePermissions($roleId, $permissionIds) {
        try {
            // First remove all existing permissions for this role
            $this->db->remove('DELETE FROM dbo.RolePermissions WHERE role_id = ?', [$roleId]);
            
            // Then add the new permissions
            if (!empty($permissionIds)) {
                foreach ($permissionIds as $permissionId) {
                    $this->db->insert('INSERT INTO dbo.RolePermissions (role_id, permission_id) VALUES (?, ?)', [$roleId, $permissionId]);
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log('SyncRolePermissions Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Get users with specific role
    public function getUsersByRole($roleId) {
        try {
            $query = 'SELECT u.* FROM dbo.Users u WHERE u.role_id = ?';
            return $this->db->select($query, [$roleId]) ?: [];
        } catch (Exception $e) {
            error_log('GetUsersByRole Error: ' . $e->getMessage());
            return [];
        }
    }
    
    // Get role statistics
    public function getRoleStats($roleId) {
        try {
        // Get user count
            $userResult = $this->db->select('SELECT COUNT(*) as user_count FROM dbo.Users WHERE role_id = ?', [$roleId]);
            $userCount = !empty($userResult) ? $userResult[0]['user_count'] : 0;
        
        // Get permission count
            $permResult = $this->db->select('SELECT COUNT(*) as permission_count FROM dbo.RolePermissions WHERE role_id = ?', [$roleId]);
            $permissionCount = !empty($permResult) ? $permResult[0]['permission_count'] : 0;
        
        return [
            'user_count' => $userCount,
            'permission_count' => $permissionCount
        ];
        } catch (Exception $e) {
            error_log('GetRoleStats Error: ' . $e->getMessage());
            return ['user_count' => 0, 'permission_count' => 0];
        }
    }
    
    // Get all roles with their stats
    public function getAllRolesWithStats() {
        $query = 'SELECT r.*, 
                        COUNT(DISTINCT u.id) as user_count,
                        COUNT(DISTINCT rp.permission_id) as permission_count
                 FROM dbo.Roles r
                 LEFT JOIN dbo.Users u ON r.id = u.role_id
                 LEFT JOIN dbo.RolePermissions rp ON r.id = rp.role_id
                 WHERE r.is_active = 1
                 GROUP BY r.id, r.name, r.display_name, r.description, r.is_active, r.created_at, r.updated_at
                 ORDER BY r.display_name';
        return $this->db->select($query) ?: [];
    }
    
    // Check if role has permission
    public function roleHasPermission($roleId, $permissionName) {
        try {
            $query = 'SELECT COUNT(*) as count
                         FROM dbo.RolePermissions rp
                         INNER JOIN dbo.Permissions p ON rp.permission_id = p.id
                     WHERE rp.role_id = ? AND p.name = ? AND p.is_active = 1';
            $result = $this->db->select($query, [$roleId, $permissionName]);
            return !empty($result) && $result[0]['count'] > 0;
        } catch (Exception $e) {
            error_log('RoleHasPermission Error: ' . $e->getMessage());
            return false;
        }
    }
    
    // Get permission IDs for a role (helper for forms)
    public function getRolePermissionIds($roleId) {
        try {
        $query = 'SELECT permission_id FROM dbo.RolePermissions WHERE role_id = ?';
            $results = $this->db->select($query, [$roleId]);
            return array_column($results ?: [], 'permission_id');
        } catch (Exception $e) {
            error_log('GetRolePermissionIds Error: ' . $e->getMessage());
            return [];
        }
    }
    
    // Create default roles if they don't exist (for setup)
    public function createDefaultRolesIfNotExist() {
        $defaultRoles = [
            ['name' => 'super_admin', 'display_name' => 'Super Administrator', 'description' => 'Full system access'],
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Administrative access'],
            ['name' => 'manager', 'display_name' => 'Manager', 'description' => 'Project management access'],
            ['name' => 'employee', 'display_name' => 'Employee', 'description' => 'Standard employee access'],
            ['name' => 'client', 'display_name' => 'Client', 'description' => 'Client access'],
            ['name' => 'viewer', 'display_name' => 'Viewer', 'description' => 'Read-only access']
        ];
        
        foreach ($defaultRoles as $roleData) {
            $existing = $this->getRoleByName($roleData['name']);
            if (!$existing) {
                $this->createRole($roleData);
            }
        }
    }
} 