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
    
    // Enhanced fallback for admin users - check both session role and username
    if ((isset($_SESSION['role']) && $_SESSION['role'] === 'admin') || 
        (isset($_SESSION['user_name']) && $_SESSION['user_name'] === 'admin') ||
        (isset($_SESSION['username']) && $_SESSION['username'] === 'admin')) {
        return true; // Admin users have all permissions
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

/**
 * Debug permission information for a user
 * Useful for troubleshooting permission issues
 * 
 * @param int $userId User ID (optional, defaults to current user)
 * @return array Debug information
 */
function debugUserPermissions($userId = null) {
    if (!$userId && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    if (!$userId) {
        return ['error' => 'No user ID provided'];
    }
    
    $userModel = new User();
    $permissionModel = new Permission();
    $roleModel = new Role();
    
    try {
        // Get user details
        $user = $userModel->getUserById($userId);
        if (!$user) {
            return ['error' => 'User not found'];
        }
        
        // Get user's role information
        $roleInfo = null;
        if (!empty($user['role_id'])) {
            $roleInfo = $roleModel->getRoleById($user['role_id']);
        } elseif (!empty($user['role'])) {
            $roleInfo = $roleModel->getRoleByName($user['role']);
        }
        
        // Get user's effective permissions
        $permissions = $userModel->getUserPermissions($userId);
        
        // Get role permissions if user has a role
        $rolePermissions = [];
        if ($roleInfo) {
            $rolePermissions = $roleModel->getRolePermissions($roleInfo['id']);
        }
        
        return [
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'] ?? $user['name'],
                'email' => $user['email'],
                'role_field' => $user['role'] ?? null,
                'role_id_field' => $user['role_id'] ?? null,
                'is_active' => $user['is_active'] ?? 1
            ],
            'role' => $roleInfo,
            'permissions' => [
                'count' => count($permissions),
                'list' => $permissions
            ],
            'role_permissions' => [
                'count' => count($rolePermissions),
                'list' => array_column($rolePermissions, 'name')
            ],
            'migration_needed' => empty($user['role_id']) && !empty($user['role'])
        ];
    } catch (Exception $e) {
        return ['error' => 'Debug failed: ' . $e->getMessage()];
    }
}

/**
 * Test if a specific permission is working for current user
 * 
 * @param string $permissionName Permission to test
 * @return array Test results
 */
function testPermission($permissionName) {
    if (!isset($_SESSION['user_id'])) {
        return ['error' => 'No user logged in'];
    }
    
    $userId = $_SESSION['user_id'];
    $userModel = new User();
    
    try {
        $hasPermission = $userModel->hasPermission($userId, $permissionName);
        $debug = debugUserPermissions($userId);
        
        return [
            'permission' => $permissionName,
            'has_permission' => $hasPermission,
            'user_id' => $userId,
            'user_debug' => $debug
        ];
    } catch (Exception $e) {
        return ['error' => 'Test failed: ' . $e->getMessage()];
    }
}
?> 