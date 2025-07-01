<?php

/**
 * Check if the current logged-in user has a specific permission
 * 
 * @param string $permissionName Permission name to check
 * @return bool True if user has permission, false otherwise
 */
function hasPermission($permissionName) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    try {
        $userModel = new User();
        return $userModel->hasPermission($_SESSION['user_id'], $permissionName);
    } catch (Exception $e) {
        // If there's an error (like permission tables don't exist), 
        // check if user is admin for backward compatibility
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

/**
 * Check if the current user is a manager
 * 
 * @return bool True if user is manager, false otherwise
 */
function isManager() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager']);
}

/**
 * Get current user's permissions
 * 
 * @return array Array of permission names
 */
function getCurrentUserPermissions() {
    if (!isset($_SESSION['user_id'])) {
        return [];
    }
    
    try {
        $userModel = new User();
        return $userModel->getUserPermissions($_SESSION['user_id']);
    } catch (Exception $e) {
        error_log('getCurrentUserPermissions Error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Check if user has any of the given permissions
 * 
 * @param array $permissions Array of permission names
 * @return bool True if user has at least one permission, false otherwise
 */
function hasAnyPermission($permissions) {
    foreach ($permissions as $permission) {
        if (hasPermission($permission)) {
            return true;
        }
    }
    return false;
}

/**
 * Check if user has all of the given permissions
 * 
 * @param array $permissions Array of permission names
 * @return bool True if user has all permissions, false otherwise
 */
function hasAllPermissions($permissions) {
    foreach ($permissions as $permission) {
        if (!hasPermission($permission)) {
            return false;
        }
    }
    return true;
}
?> 