<?php

class EnhancedPermission {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }
    
    /**
     * Check if user has permission with enhanced granular controls
     * 
     * @param int $userId User ID
     * @param string $permissionName Permission name
     * @param array $context Context for permission checking (resource_type, resource_id, field_name, etc.)
     * @return bool True if user has permission, false otherwise
     */
    public function userHasEnhancedPermission($userId, $permissionName, $context = []) {
        try {
            // 1. Check resource-specific permissions first (highest priority)
            if (isset($context['resource_type']) && isset($context['resource_id'])) {
                $resourcePermission = $this->checkResourcePermission($userId, $permissionName, $context['resource_type'], $context['resource_id']);
                if ($resourcePermission !== null) {
                    return $resourcePermission;
                }
            }
            
            // 2. Check contextual permissions (e.g., project team member, task assignee)
            if (isset($context['context_type'])) {
                $contextualPermission = $this->checkContextualPermission($userId, $permissionName, $context);
                if ($contextualPermission !== null) {
                    return $contextualPermission;
                }
            }
            
            // 3. Check field-level permissions
            if (isset($context['field_name'])) {
                $fieldPermission = $this->checkFieldPermission($userId, $context['module'] ?? '', $context['field_name'], $context['access_level'] ?? 'read', $context['resource_id'] ?? null);
                if ($fieldPermission !== null) {
                    return $fieldPermission;
                }
            }
            
            // 4. Fall back to standard permission checking
            return $this->checkStandardPermission($userId, $permissionName);
            
        } catch (Exception $e) {
            error_log('UserHasEnhancedPermission Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check resource-specific permissions
     */
    private function checkResourcePermission($userId, $permissionName, $resourceType, $resourceId) {
        try {
            $query = "SELECT granted FROM dbo.ResourcePermissions 
                     WHERE user_id = ? AND permission_name = ? AND resource_type = ? AND resource_id = ? 
                     AND (expires_at IS NULL OR expires_at > GETDATE())
                     ORDER BY granted DESC";
            
            $result = $this->db->select($query, [$userId, $permissionName, $resourceType, $resourceId]);
            
            if (!empty($result)) {
                return $result[0]['granted'] == 1;
            }
            
            return null; // No specific resource permission found
        } catch (Exception $e) {
            error_log('CheckResourcePermission Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check contextual permissions based on user relationships
     */
    private function checkContextualPermission($userId, $permissionName, $context) {
        try {
            // Check if user has contextual permission
            $query = "SELECT granted FROM dbo.ContextualPermissions 
                     WHERE user_id = ? AND permission_name = ? AND context_type = ? AND context_id = ?";
            
            $result = $this->db->select($query, [
                $userId, 
                $permissionName, 
                $context['context_type'], 
                $context['context_id']
            ]);
            
            if (!empty($result)) {
                return $result[0]['granted'] == 1;
            }
            
            // Check automatic contextual permissions based on relationships
            return $this->checkAutomaticContextualPermission($userId, $permissionName, $context);
            
        } catch (Exception $e) {
            error_log('CheckContextualPermission Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check automatic contextual permissions based on user relationships
     */
    private function checkAutomaticContextualPermission($userId, $permissionName, $context) {
        try {
            $contextType = $context['context_type'];
            $contextId = $context['context_id'];
            
            switch ($contextType) {
                case 'project_member':
                    // Check if user is a member of the project
                    $query = "SELECT COUNT(*) as count FROM dbo.ProjectUsers WHERE project_id = ? AND user_id = ?";
                    $result = $this->db->select($query, [$contextId, $userId]);
                    $isMember = !empty($result) && $result[0]['count'] > 0;
                    
                    // Define permissions for project members
                    $memberPermissions = [
                        'projects.read', 'tasks.read', 'tasks.update', 'notes.create', 'notes.read'
                    ];
                    
                    return $isMember && in_array($permissionName, $memberPermissions);
                    
                case 'task_assignee':
                    // Check if user is assigned to the task
                    $query = "SELECT COUNT(*) as count FROM dbo.Tasks WHERE id = ? AND assigned_to_id = ?";
                    $result = $this->db->select($query, [$contextId, $userId]);
                    $isAssignee = !empty($result) && $result[0]['count'] > 0;
                    
                    // Define permissions for task assignees
                    $assigneePermissions = [
                        'tasks.read', 'tasks.update', 'time.create', 'notes.create'
                    ];
                    
                    return $isAssignee && in_array($permissionName, $assigneePermissions);
                    
                case 'department_member':
                    // Check if user belongs to the department
                    $query = "SELECT COUNT(*) as count FROM dbo.Users WHERE id = ? AND department_id = ?";
                    $result = $this->db->select($query, [$userId, $contextId]);
                    $isDepartmentMember = !empty($result) && $result[0]['count'] > 0;
                    
                    // Define permissions for department members
                    $departmentPermissions = [
                        'departments.read', 'projects.read', 'reports.basic'
                    ];
                    
                    return $isDepartmentMember && in_array($permissionName, $departmentPermissions);
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log('CheckAutomaticContextualPermission Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check field-level permissions
     */
    private function checkFieldPermission($userId, $module, $fieldName, $accessLevel, $resourceId = null) {
        try {
            $query = "SELECT access_level FROM dbo.FieldPermissions 
                     WHERE user_id = ? AND module = ? AND field_name = ? 
                     AND (resource_id IS NULL OR resource_id = ?)";
            
            $result = $this->db->select($query, [$userId, $module, $fieldName, $resourceId]);
            
            if (!empty($result)) {
                $userAccessLevel = $result[0]['access_level'];
                
                // Check if user's access level allows the requested action
                $accessLevels = ['hidden' => 0, 'read' => 1, 'write' => 2];
                $userLevel = $accessLevels[$userAccessLevel] ?? 0;
                $requiredLevel = $accessLevels[$accessLevel] ?? 1;
                
                return $userLevel >= $requiredLevel;
            }
            
            return null; // No specific field permission found
            
        } catch (Exception $e) {
            error_log('CheckFieldPermission Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Standard permission checking (falls back to existing system)
     */
    private function checkStandardPermission($userId, $permissionName) {
        try {
            // Check direct user permissions first
            $query = "SELECT up.granted 
                     FROM dbo.UserPermissions up
                     INNER JOIN dbo.Permissions p ON up.permission_id = p.id
                     WHERE up.user_id = ? AND p.name = ?";
            $result = $this->db->select($query, [$userId, $permissionName]);
            
            if (!empty($result)) {
                return $result[0]['granted'] == 1;
            }
            
            // Check role-based permissions
            $query = "SELECT COUNT(*) as count
                     FROM dbo.Users u
                     INNER JOIN dbo.Roles r ON u.role_id = r.id
                     INNER JOIN dbo.RolePermissions rp ON r.id = rp.role_id
                     INNER JOIN dbo.Permissions p ON rp.permission_id = p.id
                     WHERE u.id = ? AND p.name = ? AND r.is_active = 1 AND p.is_active = 1";
            $result = $this->db->select($query, [$userId, $permissionName]);
            
            return !empty($result) && $result[0]['count'] > 0;
            
        } catch (Exception $e) {
            error_log('CheckStandardPermission Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Grant resource-specific permission to user
     */
    public function grantResourcePermission($userId, $permissionName, $resourceType, $resourceId, $grantedBy = null, $expiresAt = null, $conditions = null) {
        try {
            // Check if permission already exists
            $existing = $this->db->select(
                "SELECT id FROM dbo.ResourcePermissions WHERE user_id = ? AND permission_name = ? AND resource_type = ? AND resource_id = ?",
                [$userId, $permissionName, $resourceType, $resourceId]
            );
            
            if (empty($existing)) {
                // Insert new permission
                $query = "INSERT INTO dbo.ResourcePermissions 
                         (user_id, permission_name, resource_type, resource_id, granted, expires_at, conditions, granted_by) 
                         VALUES (?, ?, ?, ?, 1, ?, ?, ?)";
                $this->db->insert($query, [$userId, $permissionName, $resourceType, $resourceId, $expiresAt, $conditions, $grantedBy]);
            } else {
                // Update existing permission
                $query = "UPDATE dbo.ResourcePermissions 
                         SET granted = 1, expires_at = ?, conditions = ?, granted_by = ?, updated_at = GETDATE() 
                         WHERE user_id = ? AND permission_name = ? AND resource_type = ? AND resource_id = ?";
                $this->db->update($query, [$expiresAt, $conditions, $grantedBy, $userId, $permissionName, $resourceType, $resourceId]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('GrantResourcePermission Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Grant field-level permission to user
     */
    public function grantFieldPermission($userId, $module, $fieldName, $accessLevel, $resourceId = null) {
        try {
            // Check if permission already exists
            $existing = $this->db->select(
                "SELECT id FROM dbo.FieldPermissions WHERE user_id = ? AND module = ? AND field_name = ? AND (resource_id IS NULL OR resource_id = ?)",
                [$userId, $module, $fieldName, $resourceId]
            );
            
            if (empty($existing)) {
                // Insert new permission
                $query = "INSERT INTO dbo.FieldPermissions (user_id, module, field_name, access_level, resource_id) VALUES (?, ?, ?, ?, ?)";
                $this->db->insert($query, [$userId, $module, $fieldName, $accessLevel, $resourceId]);
            } else {
                // Update existing permission
                $query = "UPDATE dbo.FieldPermissions SET access_level = ? WHERE user_id = ? AND module = ? AND field_name = ? AND (resource_id IS NULL OR resource_id = ?)";
                $this->db->update($query, [$accessLevel, $userId, $module, $fieldName, $resourceId]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('GrantFieldPermission Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Grant contextual permission to user
     */
    public function grantContextualPermission($userId, $contextType, $contextId, $permissionName) {
        try {
            // Check if permission already exists
            $existing = $this->db->select(
                "SELECT id FROM dbo.ContextualPermissions WHERE user_id = ? AND context_type = ? AND context_id = ? AND permission_name = ?",
                [$userId, $contextType, $contextId, $permissionName]
            );
            
            if (empty($existing)) {
                // Insert new permission
                $query = "INSERT INTO dbo.ContextualPermissions (user_id, context_type, context_id, permission_name, granted) VALUES (?, ?, ?, ?, 1)";
                $this->db->insert($query, [$userId, $contextType, $contextId, $permissionName]);
            } else {
                // Update existing permission
                $query = "UPDATE dbo.ContextualPermissions SET granted = 1 WHERE user_id = ? AND context_type = ? AND context_id = ? AND permission_name = ?";
                $this->db->update($query, [$userId, $contextType, $contextId, $permissionName]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('GrantContextualPermission Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's effective permissions for a specific resource
     */
    public function getUserResourcePermissions($userId, $resourceType, $resourceId) {
        try {
            $query = "SELECT DISTINCT permission_name, granted, expires_at 
                     FROM dbo.ResourcePermissions 
                     WHERE user_id = ? AND resource_type = ? AND resource_id = ? 
                     AND (expires_at IS NULL OR expires_at > GETDATE())
                     ORDER BY permission_name";
            
            return $this->db->select($query, [$userId, $resourceType, $resourceId]) ?: [];
        } catch (Exception $e) {
            error_log('GetUserResourcePermissions Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user's field permissions for a module
     */
    public function getUserFieldPermissions($userId, $module, $resourceId = null) {
        try {
            $query = "SELECT field_name, access_level, resource_id 
                     FROM dbo.FieldPermissions 
                     WHERE user_id = ? AND module = ? 
                     AND (resource_id IS NULL OR resource_id = ?)
                     ORDER BY field_name";
            
            return $this->db->select($query, [$userId, $module, $resourceId]) ?: [];
        } catch (Exception $e) {
            error_log('GetUserFieldPermissions Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get permission groups
     */
    public function getPermissionGroups() {
        try {
            $query = "SELECT * FROM dbo.PermissionGroups WHERE is_active = 1 ORDER BY module, display_name";
            return $this->db->select($query) ?: [];
        } catch (Exception $e) {
            error_log('GetPermissionGroups Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Assign permission group to user
     */
    public function assignPermissionGroupToUser($userId, $groupId) {
        try {
            // Get all permissions in the group
            $query = "SELECT p.id FROM dbo.Permissions p 
                     INNER JOIN dbo.PermissionGroupMembers pgm ON p.id = pgm.permission_id 
                     WHERE pgm.group_id = ?";
            $permissions = $this->db->select($query, [$groupId]);
            
            // Assign each permission to the user
            foreach ($permissions as $permission) {
                // Check if user already has this permission
                $existing = $this->db->select(
                    "SELECT id FROM dbo.UserPermissions WHERE user_id = ? AND permission_id = ?",
                    [$userId, $permission['id']]
                );
                
                if (empty($existing)) {
                    $this->db->insert(
                        "INSERT INTO dbo.UserPermissions (user_id, permission_id, granted) VALUES (?, ?, 1)",
                        [$userId, $permission['id']]
                    );
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log('AssignPermissionGroupToUser Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean up expired permissions
     */
    public function cleanupExpiredPermissions() {
        try {
            $query = "DELETE FROM dbo.ResourcePermissions WHERE expires_at IS NOT NULL AND expires_at <= GETDATE()";
            $this->db->remove($query);
            return true;
        } catch (Exception $e) {
            error_log('CleanupExpiredPermissions Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's effective permissions summary
     */
    public function getUserPermissionsSummary($userId) {
        try {
            $query = "SELECT * FROM dbo.vw_UserEffectivePermissions WHERE user_id = ? ORDER BY module, permission_name";
            return $this->db->select($query, [$userId]) ?: [];
        } catch (Exception $e) {
            error_log('GetUserPermissionsSummary Error: ' . $e->getMessage());
            return [];
        }
    }
} 