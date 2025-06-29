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
        $this->db->query('SELECT * FROM dbo.Roles WHERE name = :name');
        $this->db->bind(':name', $name);
        return $this->db->single();
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
        // First check if any users are assigned to this role
        $this->db->query('SELECT COUNT(*) as user_count FROM dbo.Users WHERE role_id = :id');
        $this->db->bind(':id', $id);
        $result = $this->db->single();
        
        if ($result['user_count'] > 0) {
            return false; // Cannot delete role with assigned users
        }
        
        // Remove all role-permission associations
        $this->db->query('DELETE FROM dbo.RolePermissions WHERE role_id = :id');
        $this->db->bind(':id', $id);
        $this->db->execute();
        
        // Delete the role
        $this->db->query('DELETE FROM dbo.Roles WHERE id = :id');
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }
    
    // Get role permissions
    public function getRolePermissions($roleId) {
        $this->db->query('SELECT p.* 
                         FROM dbo.Permissions p
                         INNER JOIN dbo.RolePermissions rp ON p.id = rp.permission_id
                         WHERE rp.role_id = :role_id AND p.is_active = 1
                         ORDER BY p.module, p.action, p.display_name');
        $this->db->bind(':role_id', $roleId);
        return $this->db->resultSet();
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
        $this->db->query('INSERT INTO dbo.RolePermissions (role_id, permission_id) 
                         VALUES (:role_id, :permission_id)');
        $this->db->bind(':role_id', $roleId);
        $this->db->bind(':permission_id', $permissionId);
        return $this->db->execute();
    }
    
    // Remove permission from role
    public function removePermissionFromRole($roleId, $permissionId) {
        $this->db->query('DELETE FROM dbo.RolePermissions 
                         WHERE role_id = :role_id AND permission_id = :permission_id');
        $this->db->bind(':role_id', $roleId);
        $this->db->bind(':permission_id', $permissionId);
        return $this->db->execute();
    }
    
    // Sync role permissions (bulk update)
    public function syncRolePermissions($roleId, $permissionIds) {
        try {
            // First remove all existing permissions for this role
            $deleteQuery = 'DELETE FROM dbo.RolePermissions WHERE role_id = ?';
            $this->db->query($deleteQuery, [$roleId]);
            
            // Then add the new permissions
            if (!empty($permissionIds)) {
                foreach ($permissionIds as $permissionId) {
                    $insertQuery = 'INSERT INTO dbo.RolePermissions (role_id, permission_id) VALUES (?, ?)';
                    $this->db->insert($insertQuery, [$roleId, $permissionId]);
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
        $this->db->query('SELECT u.* FROM dbo.Users u WHERE u.role_id = :role_id');
        $this->db->bind(':role_id', $roleId);
        return $this->db->resultSet();
    }
    
    // Get role statistics
    public function getRoleStats($roleId) {
        // Get user count
        $this->db->query('SELECT COUNT(*) as user_count FROM dbo.Users WHERE role_id = :role_id');
        $this->db->bind(':role_id', $roleId);
        $userCount = $this->db->single()['user_count'];
        
        // Get permission count
        $this->db->query('SELECT COUNT(*) as permission_count FROM dbo.RolePermissions WHERE role_id = :role_id');
        $this->db->bind(':role_id', $roleId);
        $permissionCount = $this->db->single()['permission_count'];
        
        return [
            'user_count' => $userCount,
            'permission_count' => $permissionCount
        ];
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
        $this->db->query('SELECT COUNT(*) as count
                         FROM dbo.RolePermissions rp
                         INNER JOIN dbo.Permissions p ON rp.permission_id = p.id
                         WHERE rp.role_id = :role_id AND p.name = :permission_name AND p.is_active = 1');
        $this->db->bind(':role_id', $roleId);
        $this->db->bind(':permission_name', $permissionName);
        $result = $this->db->single();
        
        return $result['count'] > 0;
    }
    
    // Get all permission IDs for a role (useful for forms)
    public function getRolePermissionIds($roleId) {
        $query = 'SELECT permission_id FROM dbo.RolePermissions WHERE role_id = ?';
        $results = $this->db->select($query, [$roleId]) ?: [];
        return array_column($results, 'permission_id');
    }
    
    // Create default roles if they don't exist
    public function createDefaultRolesIfNotExist() {
        $defaultRoles = [
            ['name' => 'super_admin', 'display_name' => 'Super Administrator', 'description' => 'Full system access with all permissions'],
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Administrative access to most system features'],
            ['name' => 'manager', 'display_name' => 'Project Manager', 'description' => 'Can manage projects, tasks, and team members'],
            ['name' => 'employee', 'display_name' => 'Employee', 'description' => 'Standard employee access to assigned projects and tasks'],
            ['name' => 'client', 'display_name' => 'Client', 'description' => 'Limited access to view assigned projects and reports'],
            ['name' => 'viewer', 'display_name' => 'Viewer', 'description' => 'Read-only access to assigned content']
        ];
        
        foreach ($defaultRoles as $role) {
            $existing = $this->getRoleByName($role['name']);
            if (!$existing) {
                $this->createRole($role);
            }
        }
    }
} 