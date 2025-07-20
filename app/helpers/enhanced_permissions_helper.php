<?php

require_once __DIR__ . '/../models/EnhancedPermission.php';

/**
 * Enhanced Permissions Helper
 * Provides convenient functions for the new granular permission system
 */

/**
 * Check if user has enhanced permission with context
 * 
 * @param string $permissionName Permission name
 * @param array $context Context for permission checking
 * @return bool True if user has permission, false otherwise
 */
function hasEnhancedPermission($permissionName, $context = []) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    try {
        $enhancedPermission = new EnhancedPermission();
        return $enhancedPermission->userHasEnhancedPermission($_SESSION['user_id'], $permissionName, $context);
    } catch (Exception $e) {
        error_log('hasEnhancedPermission Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Check if user can access a specific project
 * 
 * @param int $projectId Project ID
 * @param string $action Action to perform (read, update, delete)
 * @return bool True if user has access, false otherwise
 */
function canAccessProject($projectId, $action = 'read') {
    return hasEnhancedPermission("projects.$action", [
        'resource_type' => 'project',
        'resource_id' => $projectId,
        'context_type' => 'project_member',
        'context_id' => $projectId
    ]);
}

/**
 * Check if user can access a specific task
 * 
 * @param int $taskId Task ID
 * @param string $action Action to perform (read, update, delete)
 * @return bool True if user has access, false otherwise
 */
function canAccessTask($taskId, $action = 'read') {
    return hasEnhancedPermission("tasks.$action", [
        'resource_type' => 'task',
        'resource_id' => $taskId,
        'context_type' => 'task_assignee',
        'context_id' => $taskId
    ]);
}

/**
 * Check if user can view/edit a specific field
 * 
 * @param string $module Module name (projects, tasks, users)
 * @param string $fieldName Field name (budget, salary, notes)
 * @param string $accessLevel Access level needed (read, write)
 * @param int $resourceId Specific resource ID (optional)
 * @return bool True if user has access, false otherwise
 */
function canAccessField($module, $fieldName, $accessLevel = 'read', $resourceId = null) {
    return hasEnhancedPermission("$module.field_access", [
        'module' => $module,
        'field_name' => $fieldName,
        'access_level' => $accessLevel,
        'resource_id' => $resourceId
    ]);
}

/**
 * Check if user can view sensitive budget information
 * 
 * @param int $projectId Project ID (optional)
 * @return bool True if user can view budget, false otherwise
 */
function canViewBudget($projectId = null) {
    $context = ['field_name' => 'budget', 'module' => 'projects', 'access_level' => 'read'];
    if ($projectId) {
        $context['resource_id'] = $projectId;
        $context['resource_type'] = 'project';
    }
    
    return hasEnhancedPermission('projects.view_budget', $context);
}

/**
 * Check if user can edit budget information
 * 
 * @param int $projectId Project ID (optional)
 * @return bool True if user can edit budget, false otherwise
 */
function canEditBudget($projectId = null) {
    $context = ['field_name' => 'budget', 'module' => 'projects', 'access_level' => 'write'];
    if ($projectId) {
        $context['resource_id'] = $projectId;
        $context['resource_type'] = 'project';
    }
    
    return hasEnhancedPermission('projects.edit_budget', $context);
}

/**
 * Check if user can view salary information
 * 
 * @param int $userId User ID (optional)
 * @return bool True if user can view salary, false otherwise
 */
function canViewSalary($userId = null) {
    $context = ['field_name' => 'salary', 'module' => 'users', 'access_level' => 'read'];
    if ($userId) {
        $context['resource_id'] = $userId;
        $context['resource_type'] = 'user';
    }
    
    return hasEnhancedPermission('users.view_salary', $context);
}

/**
 * Check if user is a project team member
 * 
 * @param int $projectId Project ID
 * @return bool True if user is team member, false otherwise
 */
function isProjectTeamMember($projectId) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    try {
        $db = new EasySQL(DB1);
        $query = "SELECT COUNT(*) as count FROM dbo.ProjectUsers WHERE project_id = ? AND user_id = ?";
        $result = $db->select($query, [$projectId, $_SESSION['user_id']]);
        return !empty($result) && $result[0]['count'] > 0;
    } catch (Exception $e) {
        error_log('isProjectTeamMember Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Check if user is assigned to a task
 * 
 * @param int $taskId Task ID
 * @return bool True if user is assigned, false otherwise
 */
function isTaskAssignee($taskId) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    try {
        $db = new EasySQL(DB1);
        $query = "SELECT COUNT(*) as count FROM dbo.Tasks WHERE id = ? AND assigned_to_id = ?";
        $result = $db->select($query, [$taskId, $_SESSION['user_id']]);
        return !empty($result) && $result[0]['count'] > 0;
    } catch (Exception $e) {
        error_log('isTaskAssignee Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get user's field permissions for a module
 * 
 * @param string $module Module name
 * @param int $resourceId Resource ID (optional)
 * @return array Array of field permissions
 */
function getUserFieldPermissions($module, $resourceId = null) {
    if (!isset($_SESSION['user_id'])) {
        return [];
    }
    
    try {
        $enhancedPermission = new EnhancedPermission();
        return $enhancedPermission->getUserFieldPermissions($_SESSION['user_id'], $module, $resourceId);
    } catch (Exception $e) {
        error_log('getUserFieldPermissions Error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get user's resource permissions for a specific resource
 * 
 * @param string $resourceType Resource type (project, task, etc.)
 * @param int $resourceId Resource ID
 * @return array Array of resource permissions
 */
function getUserResourcePermissions($resourceType, $resourceId) {
    if (!isset($_SESSION['user_id'])) {
        return [];
    }
    
    try {
        $enhancedPermission = new EnhancedPermission();
        return $enhancedPermission->getUserResourcePermissions($_SESSION['user_id'], $resourceType, $resourceId);
    } catch (Exception $e) {
        error_log('getUserResourcePermissions Error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Check if user has any permission from a list
 * 
 * @param array $permissions Array of permission names
 * @param array $context Context for permission checking
 * @return bool True if user has any permission, false otherwise
 */
function hasAnyEnhancedPermission($permissions, $context = []) {
    foreach ($permissions as $permission) {
        if (hasEnhancedPermission($permission, $context)) {
            return true;
        }
    }
    return false;
}

/**
 * Check if user has all permissions from a list
 * 
 * @param array $permissions Array of permission names
 * @param array $context Context for permission checking
 * @return bool True if user has all permissions, false otherwise
 */
function hasAllEnhancedPermissions($permissions, $context = []) {
    foreach ($permissions as $permission) {
        if (!hasEnhancedPermission($permission, $context)) {
            return false;
        }
    }
    return true;
}

/**
 * Check if user can perform financial operations
 * 
 * @return bool True if user has financial permissions, false otherwise
 */
function canAccessFinancials() {
    return hasAnyEnhancedPermission([
        'projects.view_financials',
        'departments.view_budget',
        'reports.financial',
        'invoices.read'
    ]);
}

/**
 * Check if user can manage team members
 * 
 * @param int $projectId Project ID (optional)
 * @return bool True if user can manage team, false otherwise
 */
function canManageTeam($projectId = null) {
    $context = [];
    if ($projectId) {
        $context = [
            'resource_type' => 'project',
            'resource_id' => $projectId,
            'context_type' => 'project_member',
            'context_id' => $projectId
        ];
    }
    
    return hasEnhancedPermission('projects.manage_team', $context);
}

/**
 * Check if user can approve items (timesheets, invoices, etc.)
 * 
 * @param string $itemType Type of item to approve
 * @return bool True if user can approve, false otherwise
 */
function canApprove($itemType) {
    $approvalPermissions = [
        'timesheet' => 'time.approve_timesheets',
        'invoice' => 'invoices.approve',
        'task' => 'tasks.approve',
        'project' => 'projects.approve'
    ];
    
    $permission = $approvalPermissions[$itemType] ?? null;
    return $permission ? hasEnhancedPermission($permission) : false;
}

/**
 * Check if user can export sensitive data
 * 
 * @return bool True if user can export sensitive data, false otherwise
 */
function canExportSensitiveData() {
    return hasEnhancedPermission('reports.export_sensitive');
}

/**
 * Get filtered fields for a form based on user permissions
 * 
 * @param string $module Module name
 * @param array $allFields Array of all available fields
 * @param int $resourceId Resource ID (optional)
 * @return array Array of fields the user can access
 */
function getFilteredFields($module, $allFields, $resourceId = null) {
    $fieldPermissions = getUserFieldPermissions($module, $resourceId);
    $accessibleFields = [];
    
    // Create a map of field permissions
    $permissionMap = [];
    foreach ($fieldPermissions as $permission) {
        $permissionMap[$permission['field_name']] = $permission['access_level'];
    }
    
    foreach ($allFields as $field) {
        $fieldName = is_array($field) ? $field['name'] : $field;
        
        // Check if user has access to this field
        if (isset($permissionMap[$fieldName])) {
            $accessLevel = $permissionMap[$fieldName];
            if ($accessLevel !== 'hidden') {
                $accessibleFields[] = $field;
            }
        } else {
            // Default behavior: include field if no specific restriction
            $accessibleFields[] = $field;
        }
    }
    
    return $accessibleFields;
}

/**
 * Get field access level for a specific field
 * 
 * @param string $module Module name
 * @param string $fieldName Field name
 * @param int $resourceId Resource ID (optional)
 * @return string Access level (read, write, hidden)
 */
function getFieldAccessLevel($module, $fieldName, $resourceId = null) {
    if (canAccessField($module, $fieldName, 'write', $resourceId)) {
        return 'write';
    } elseif (canAccessField($module, $fieldName, 'read', $resourceId)) {
        return 'read';
    } else {
        return 'hidden';
    }
}

/**
 * Clean up expired permissions (should be called periodically)
 */
function cleanupExpiredPermissions() {
    try {
        $enhancedPermission = new EnhancedPermission();
        return $enhancedPermission->cleanupExpiredPermissions();
    } catch (Exception $e) {
        error_log('cleanupExpiredPermissions Error: ' . $e->getMessage());
        return false;
    }
} 