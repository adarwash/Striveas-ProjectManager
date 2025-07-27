<?php

/**
 * Search-specific permission helper functions
 * These provide fallback permission checks for search functionality
 */

/**
 * Check if search permissions are properly configured
 * @return bool
 */
function areSearchPermissionsConfigured() {
    try {
        $permissionModel = new Permission();
        $searchPermissions = [
            'projects.read',
            'tasks.read', 
            'users.read',
            'clients.read',
            'notes.read',
            'reports_read'
        ];
        
        foreach ($searchPermissions as $permission) {
            if (!$permissionModel->getPermissionByName($permission)) {
                return false;
            }
        }
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Fallback permission check that works even without full permission system
 * @param string $permission Permission name
 * @return bool
 */
function hasSearchPermission($permission) {
    // Try the full permission system first
    if (function_exists('hasPermission')) {
        try {
            return hasPermission($permission);
        } catch (Exception $e) {
            // Fall back to role-based check
        }
    }
    
    // Fallback: check user role
    if (!isset($_SESSION['role'])) {
        return false;
    }
    
    $userRole = $_SESSION['role'];
    
    // Admin and manager can access everything
    if (in_array($userRole, ['admin', 'manager'])) {
        return true;
    }
    
    // Basic permission mapping for common roles
    // NOTE: notes.read allows viewing ALL notes, so it should be restricted to admin/manager roles only
    $rolePermissions = [
        'user' => ['tasks.read'],
        'employee' => ['projects.read', 'tasks.read'],
        'supervisor' => ['projects.read', 'tasks.read', 'users.read', 'clients.read', 'reports_read'],
        'team_lead' => ['projects.read', 'tasks.read', 'users.read']
    ];
    
    return isset($rolePermissions[$userRole]) && in_array($permission, $rolePermissions[$userRole]);
}

/**
 * Get allowed search entity types for current user
 * @return array
 */
function getAllowedSearchTypes() {
    $types = [];
    
    if (hasSearchPermission('projects.read')) {
        $types[] = 'projects';
    }
    
    if (hasSearchPermission('tasks.read')) {
        $types[] = 'tasks';
    }
    
    if (hasSearchPermission('users.read') || hasSearchPermission('reports_read')) {
        $types[] = 'users';
    }
    
    if (hasSearchPermission('clients.read')) {
        $types[] = 'clients';
    }
    
    if (hasSearchPermission('notes.read')) {
        $types[] = 'notes';
    }
    
    return $types;
}

/**
 * Check if user can view specific item based on ownership
 * @param array $item The item data
 * @param string $entityType The entity type
 * @param int $userId Current user ID
 * @return bool
 */
function canViewSearchItem($item, $entityType, $userId) {
    // Admins and managers can view everything
    if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])) {
        return true;
    }
    
    switch ($entityType) {
        case 'projects':
            return hasSearchPermission('projects.read') || 
                   (isset($item['user_id']) && $item['user_id'] == $userId) ||
                   (isset($item['created_by']) && $item['created_by'] == $userId);
                   
        case 'tasks':
            return hasSearchPermission('tasks.read') || 
                   (isset($item['assigned_to']) && $item['assigned_to'] == $userId) ||
                   (isset($item['created_by']) && $item['created_by'] == $userId);
                   
        case 'users':
            return hasSearchPermission('users.read') || hasSearchPermission('reports_read');
            
        case 'clients':
            return hasSearchPermission('clients.read');
            
        case 'notes':
            return hasSearchPermission('notes.read') || 
                   (isset($item['created_by']) && $item['created_by'] == $userId);
                   
        default:
            return false;
    }
}

?> 