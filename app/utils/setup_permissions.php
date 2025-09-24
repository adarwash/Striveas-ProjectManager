<?php
/**
 * Permission Setup Utility
 * 
 * This script adds missing page access permissions to the database
 * Run this from the web interface or command line to set up permissions
 */

// Include necessary files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/Permission.php';
require_once __DIR__ . '/../models/Role.php';

class PermissionSetup {
    private $permissionModel;
    private $roleModel;
    
    public function __construct() {
        $this->permissionModel = new Permission();
        $this->roleModel = new Role();
    }
    
    public function setupPageAccessPermissions() {
        $results = [];
        
        // Define the permissions to add
        $permissions = [
            // Dashboard and UI
            ['name' => 'dashboard.access', 'display_name' => 'Access Dashboard', 'description' => 'Access to main dashboard', 'module' => 'dashboard', 'action' => 'access'],
            ['name' => 'calendar.access', 'display_name' => 'Access Calendar', 'description' => 'Access to calendar view', 'module' => 'calendar', 'action' => 'access'],
            ['name' => 'gantt.access', 'display_name' => 'Access Gantt Chart', 'description' => 'Access to Gantt chart view', 'module' => 'gantt', 'action' => 'access'],
            
            // Notes Module
            ['name' => 'notes.create', 'display_name' => 'Create Notes', 'description' => 'Create new notes', 'module' => 'notes', 'action' => 'create'],
            ['name' => 'notes.read', 'display_name' => 'View Notes', 'description' => 'View notes', 'module' => 'notes', 'action' => 'read'],
            ['name' => 'notes.update', 'display_name' => 'Update Notes', 'description' => 'Edit existing notes', 'module' => 'notes', 'action' => 'update'],
            ['name' => 'notes.delete', 'display_name' => 'Delete Notes', 'description' => 'Delete notes', 'module' => 'notes', 'action' => 'delete'],
            
            // Time Tracking Enhanced
            ['name' => 'time.access', 'display_name' => 'Access Time Tracking', 'description' => 'Access to time tracking system', 'module' => 'time', 'action' => 'access'],
            ['name' => 'time.admin', 'display_name' => 'Time Tracking Admin', 'description' => 'Administrative access to time tracking', 'module' => 'time', 'action' => 'admin'],
            ['name' => 'time.reports', 'display_name' => 'Time Tracking Reports', 'description' => 'Access to time tracking reports and analytics', 'module' => 'time', 'action' => 'reports'],
            
            // Suppliers Module
            ['name' => 'suppliers.create', 'display_name' => 'Create Suppliers', 'description' => 'Create new suppliers', 'module' => 'suppliers', 'action' => 'create'],
            ['name' => 'suppliers.read', 'display_name' => 'View Suppliers', 'description' => 'View supplier information', 'module' => 'suppliers', 'action' => 'read'],
            ['name' => 'suppliers.update', 'display_name' => 'Update Suppliers', 'description' => 'Edit supplier information', 'module' => 'suppliers', 'action' => 'update'],
            ['name' => 'suppliers.delete', 'display_name' => 'Delete Suppliers', 'description' => 'Delete suppliers', 'module' => 'suppliers', 'action' => 'delete'],
            
            // Sites Module
            ['name' => 'sites.create', 'display_name' => 'Create Sites', 'description' => 'Create new sites', 'module' => 'sites', 'action' => 'create'],
            ['name' => 'sites.read', 'display_name' => 'View Sites', 'description' => 'View site information', 'module' => 'sites', 'action' => 'read'],
            ['name' => 'sites.update', 'display_name' => 'Update Sites', 'description' => 'Edit site information', 'module' => 'sites', 'action' => 'update'],
            ['name' => 'sites.delete', 'display_name' => 'Delete Sites', 'description' => 'Delete sites', 'module' => 'sites', 'action' => 'delete'],

            // Clients Module
            ['name' => 'clients.create', 'display_name' => 'Create Clients', 'description' => 'Create new clients', 'module' => 'clients', 'action' => 'create'],
            ['name' => 'clients.read', 'display_name' => 'View Clients', 'description' => 'View client information', 'module' => 'clients', 'action' => 'read'],
            ['name' => 'clients.update', 'display_name' => 'Update Clients', 'description' => 'Edit client information', 'module' => 'clients', 'action' => 'update'],
            ['name' => 'clients.delete', 'display_name' => 'Delete Clients', 'description' => 'Delete clients', 'module' => 'clients', 'action' => 'delete'],
            ['name' => 'clients.assign_sites', 'display_name' => 'Assign Sites to Clients', 'description' => 'Assign and manage client-site relationships', 'module' => 'clients', 'action' => 'assign'],
            
            // Employee Management
            ['name' => 'employees.create', 'display_name' => 'Create Employees', 'description' => 'Create new employee records', 'module' => 'employees', 'action' => 'create'],
            ['name' => 'employees.read', 'display_name' => 'View Employees', 'description' => 'View employee information', 'module' => 'employees', 'action' => 'read'],
            ['name' => 'employees.update', 'display_name' => 'Update Employees', 'description' => 'Edit employee information', 'module' => 'employees', 'action' => 'update'],
            ['name' => 'employees.delete', 'display_name' => 'Delete Employees', 'description' => 'Delete employee records', 'module' => 'employees', 'action' => 'delete'],
            
            // Enhanced Admin Permissions
            ['name' => 'admin.access', 'display_name' => 'Access Admin Panel', 'description' => 'Access to administrative panel', 'module' => 'admin', 'action' => 'access'],
            ['name' => 'users.manage', 'display_name' => 'Manage Users', 'description' => 'Full user management capabilities', 'module' => 'users', 'action' => 'manage'],
            
            // Additional Task Permissions
            ['name' => 'tasks.assign', 'display_name' => 'Assign Tasks', 'description' => 'Assign tasks to users', 'module' => 'tasks', 'action' => 'assign'],
            ['name' => 'tasks.manage', 'display_name' => 'Manage Tasks', 'description' => 'Full task management capabilities', 'module' => 'tasks', 'action' => 'manage']
        ];
        
        $addedCount = 0;
        $skippedCount = 0;
        
        foreach ($permissions as $permissionData) {
            try {
                // Check if permission already exists
                $existing = $this->permissionModel->getPermissionByName($permissionData['name']);
                
                if (!$existing) {
                    // Create the permission
                    if ($this->permissionModel->createPermission($permissionData)) {
                        $addedCount++;
                        $results[] = "✓ Added permission: {$permissionData['name']}";
                    } else {
                        $results[] = "✗ Failed to add permission: {$permissionData['name']}";
                    }
                } else {
                    $skippedCount++;
                    $results[] = "- Skipped existing permission: {$permissionData['name']}";
                }
            } catch (Exception $e) {
                $results[] = "✗ Error with permission {$permissionData['name']}: " . $e->getMessage();
            }
        }
        
        $results[] = "";
        $results[] = "Summary: Added $addedCount permissions, skipped $skippedCount existing permissions";
        
        return $results;
    }
    
    public function assignPermissionsToRoles() {
        $results = [];
        
        // Define role permission mappings
        $rolePermissions = [
            'super_admin' => 'ALL', // Gets all permissions
            'admin' => [
                'dashboard.access', 'calendar.access', 'gantt.access',
                'notes.create', 'notes.read', 'notes.update', 'notes.delete',
                'time.access', 'time.admin', 'time.reports',
                'suppliers.create', 'suppliers.read', 'suppliers.update', 'suppliers.delete',
                'sites.create', 'sites.read', 'sites.update', 'sites.delete',
                'clients.create', 'clients.read', 'clients.update', 'clients.delete', 'clients.assign_sites',
                'employees.create', 'employees.read', 'employees.update', 'employees.delete',
                'admin.access', 'admin.system_settings', 'users.manage', 'tasks.assign', 'tasks.manage'
            ],
            'manager' => [
                'dashboard.access', 'calendar.access', 'gantt.access',
                'notes.create', 'notes.read', 'notes.update', 'notes.delete',
                'time.access', 'time.reports',
                'suppliers.read', 'sites.read',
                'clients.create', 'clients.read', 'clients.update', 'clients.assign_sites',
                'employees.read', 'employees.update',
                'tasks.assign', 'tasks.manage'
            ],
            'employee' => [
                'dashboard.access', 'calendar.access', 'gantt.access',
                'notes.create', 'notes.read', 'notes.update',
                'time.access',
                'suppliers.read', 'sites.read', 'clients.read'
            ],
            'client' => [
                'dashboard.access', 'calendar.access',
                'notes.read',
                'suppliers.read', 'sites.read', 'clients.read'
            ],
            'viewer' => [
                'dashboard.access', 'calendar.access', 'gantt.access',
                'notes.read',
                'suppliers.read', 'sites.read', 'clients.read'
            ]
        ];
        
        try {
            // Get all roles
            $roles = $this->roleModel->getAllRoles();
            $roleMap = [];
            foreach ($roles as $role) {
                $roleMap[$role['name']] = $role['id'];
            }
            
            // Get all permissions
            $permissions = $this->permissionModel->getAllPermissions();
            $permissionMap = [];
            foreach ($permissions as $permission) {
                $permissionMap[$permission['name']] = $permission['id'];
            }
            
            foreach ($rolePermissions as $roleName => $rolePermissionList) {
                if (!isset($roleMap[$roleName])) {
                    $results[] = "✗ Role not found: $roleName";
                    continue;
                }
                
                $roleId = $roleMap[$roleName];
                $permissionIds = [];
                
                if ($rolePermissionList === 'ALL') {
                    // Super admin gets all permissions
                    $permissionIds = array_values($permissionMap);
                } else {
                    // Get specific permission IDs
                    foreach ($rolePermissionList as $permissionName) {
                        if (isset($permissionMap[$permissionName])) {
                            $permissionIds[] = $permissionMap[$permissionName];
                        }
                    }
                }
                
                if (!empty($permissionIds)) {
                    if ($this->roleModel->syncRolePermissions($roleId, $permissionIds)) {
                        $results[] = "✓ Updated permissions for role: $roleName (" . count($permissionIds) . " permissions)";
                    } else {
                        $results[] = "✗ Failed to update permissions for role: $roleName";
                    }
                } else {
                    $results[] = "✗ No valid permissions found for role: $roleName";
                }
            }
        } catch (Exception $e) {
            $results[] = "✗ Error assigning permissions to roles: " . $e->getMessage();
        }
        
        return $results;
    }
    
    public function runFullSetup() {
        $results = [];
        
        $results[] = "=== Setting up Page Access Permissions ===";
        $permissionResults = $this->setupPageAccessPermissions();
        $results = array_merge($results, $permissionResults);
        
        $results[] = "";
        $results[] = "=== Assigning Permissions to Roles ===";
        $roleResults = $this->assignPermissionsToRoles();
        $results = array_merge($results, $roleResults);
        
        $results[] = "";
        $results[] = "=== Setup Complete ===";
        $results[] = "The permission system is now configured for page access control.";
        $results[] = "Users will see menu items based on their assigned permissions.";
        
        return $results;
    }
}

// If running directly (not included)
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in and is admin
    if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in'] || 
        !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        die('Access denied. Admin login required.');
    }
    
    echo "<h2>Permission Setup Utility</h2>";
    echo "<pre>";
    
    try {
        $setup = new PermissionSetup();
        $results = $setup->runFullSetup();
        
        foreach ($results as $result) {
            echo $result . "\n";
        }
    } catch (Exception $e) {
        echo "Setup failed: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString();
    }
    
    echo "</pre>";
    echo "<p><a href='/permissions'>Go to Permission Management</a></p>";
}
?> 