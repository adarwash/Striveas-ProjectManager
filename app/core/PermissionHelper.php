<?php

require_once __DIR__ . '/../models/User.php';

class PermissionHelper {
    private static $userModel = null;
    
    /**
     * Initialize the user model
     */
    private static function initUserModel() {
        if (self::$userModel === null) {
            self::$userModel = new User();
        }
    }
    
    /**
     * Check if current user has permission
     * 
     * @param string $permission Permission name
     * @return bool True if user has permission, false otherwise
     */
    public static function hasPermission($permission) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        self::initUserModel();
        return self::$userModel->hasPermission($_SESSION['user_id'], $permission);
    }
    
    /**
     * Check if current user can access a page
     * 
     * @param string $page Page identifier (controller/action)
     * @return bool True if user can access page, false otherwise
     */
    public static function canAccessPage($page) {
        // Admin can access everything
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            return true;
        }
        
        // Define page permission mappings
        $pagePermissions = [
            // Dashboard
            'dashboard' => 'dashboard.access',
            'dashboard/calendar' => 'calendar.access',
            'dashboard/gantt' => 'gantt.access',
            
            // Projects
            'projects' => 'projects.read',
            'projects/create' => 'projects.create',
            'projects/edit' => 'projects.update',
            'projects/delete' => 'projects.delete',
            
            // Tasks
            'tasks' => 'tasks.read',
            'tasks/create' => 'tasks.create',
            'tasks/edit' => 'tasks.update',
            'tasks/delete' => 'tasks.delete',
            
            // Notes
            'notes' => 'notes.read',
            'notes/create' => 'notes.create',
            'notes/edit' => 'notes.update',
            'notes/delete' => 'notes.delete',
            
            // Time Tracking
            'time' => 'time.access',
            'time/admin' => 'time.admin',
            'time/analytics' => 'time.reports',
            
            // Invoices
            'invoices' => 'invoices.read',
            'invoices/create' => 'invoices.create',
            'invoices/edit' => 'invoices.update',
            'invoices/delete' => 'invoices.delete',
            
            // Suppliers
            'suppliers' => 'suppliers.read',
            'suppliers/create' => 'suppliers.create',
            'suppliers/edit' => 'suppliers.update',
            'suppliers/delete' => 'suppliers.delete',
            
            // Departments
            'departments' => 'departments.read',
            'departments/create' => 'departments.create',
            'departments/edit' => 'departments.update',
            'departments/delete' => 'departments.delete',
            
            // Sites
            'sites' => 'sites.read',
            'sites/create' => 'sites.create',
            'sites/edit' => 'sites.update',
            'sites/delete' => 'sites.delete',
            
            // Clients
            'clients' => 'clients.read',
            'clients/create' => 'clients.create',
            'clients/edit' => 'clients.update',
            'clients/delete' => 'clients.delete',
            
            // Employee Management
            'employees' => 'employees.read',
            'employees/create' => 'employees.create',
            'employees/edit' => 'employees.update',
            'employees/delete' => 'employees.delete',
            
            // Administration
            'admin' => 'admin.access',
            'admin/users' => 'users.manage',
            'admin/settings' => 'admin.system_settings',
            'permissions' => 'admin.permissions',
            
            // Reports
            'reports' => 'reports.view',
            'reports/advanced' => 'reports.advanced',
            'reports/export' => 'reports.export',
        ];
        
        // Check specific page permission
        if (isset($pagePermissions[$page])) {
            return self::hasPermission($pagePermissions[$page]);
        }
        
        // Default to true for basic pages if no specific permission defined
        $basicPages = ['dashboard', 'profile', 'settings'];
        $pageBase = explode('/', $page)[0];
        
        return in_array($pageBase, $basicPages);
    }
    
    /**
     * Check if current user can access module
     * 
     * @param string $module Module name
     * @return bool True if user can access module, false otherwise
     */
    public static function canAccessModule($module) {
        // Admin can access everything
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            return true;
        }
        
        // Check if user has any permission for this module
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        self::initUserModel();
        $userPermissions = self::$userModel->getUserPermissions($_SESSION['user_id']);
        
        // Check if any permission starts with the module name
        foreach ($userPermissions as $permission) {
            if (strpos($permission, $module . '.') === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get user's permissions
     * 
     * @return array Array of permission names
     */
    public static function getUserPermissions() {
        if (!isset($_SESSION['user_id'])) {
            return [];
        }
        
        self::initUserModel();
        return self::$userModel->getUserPermissions($_SESSION['user_id']);
    }
    
    /**
     * Require permission or redirect
     * 
     * @param string $permission Permission name
     * @param string $redirectTo URL to redirect to if permission denied
     */
    public static function requirePermission($permission, $redirectTo = '/dashboard') {
        if (!self::hasPermission($permission)) {
            header('Location: ' . $redirectTo);
            exit();
        }
    }
    
    /**
     * Require page access or redirect
     * 
     * @param string $page Page identifier
     * @param string $redirectTo URL to redirect to if access denied
     */
    public static function requirePageAccess($page, $redirectTo = '/dashboard') {
        if (!self::canAccessPage($page)) {
            header('Location: ' . $redirectTo);
            exit();
        }
    }
    
    /**
     * Check if user is admin
     * 
     * @return bool True if user is admin, false otherwise
     */
    public static function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    /**
     * Check if user is manager or admin
     * 
     * @return bool True if user is manager or admin, false otherwise
     */
    public static function isManagerOrAdmin() {
        return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager']);
    }
    
    /**
     * Generate navigation menu based on permissions
     * 
     * @return array Array of menu items user can access
     */
    public static function getAccessibleMenuItems() {
        $menuItems = [
            [
                'title' => 'Dashboard',
                'url' => '/dashboard',
                'icon' => 'bi bi-grid-1x2',
                'permission' => 'dashboard.access',
                'always_show' => true
            ],
            [
                'title' => 'Projects',
                'url' => '/projects',
                'icon' => 'bi bi-kanban',
                'permission' => 'projects.read'
            ],
            [
                'title' => 'Tasks',
                'url' => '/tasks',
                'icon' => 'bi bi-check2-square',
                'permission' => 'tasks.read'
            ],
            [
                'title' => 'Notes',
                'url' => '/notes',
                'icon' => 'bi bi-journal-text',
                'permission' => 'notes.read'
            ],
            [
                'title' => 'Time Tracking',
                'url' => '/time',
                'icon' => 'bi bi-stopwatch',
                'permission' => 'time.access'
            ],
            [
                'title' => 'Invoices',
                'url' => '/invoices',
                'icon' => 'bi bi-receipt',
                'permission' => 'invoices.read'
            ],
            [
                'title' => 'Suppliers',
                'url' => '/suppliers',
                'icon' => 'bi bi-shop',
                'permission' => 'suppliers.read'
            ],
            [
                'title' => 'Departments',
                'url' => '/departments',
                'icon' => 'bi bi-building',
                'permission' => 'departments.read'
            ],
            [
                'title' => 'Sites',
                'url' => '/sites',
                'icon' => 'bi bi-geo-alt',
                'permission' => 'sites.read'
            ],
            [
                'title' => 'Clients',
                'url' => '/clients',
                'icon' => 'bi bi-people',
                'permission' => 'clients.read'
            ],
            [
                'title' => 'Calendar',
                'url' => '/dashboard/calendar',
                'icon' => 'bi bi-calendar3',
                'permission' => 'calendar.access'
            ],
            [
                'title' => 'Gantt Chart',
                'url' => '/dashboard/gantt',
                'icon' => 'bi bi-bar-chart',
                'permission' => 'gantt.access'
            ]
        ];
        
        $adminItems = [
            [
                'title' => 'Admin Panel',
                'url' => '/admin',
                'icon' => 'bi bi-shield-lock',
                'permission' => 'admin.access'
            ],
            [
                'title' => 'Users',
                'url' => '/admin/users',
                'icon' => 'bi bi-people',
                'permission' => 'users.manage'
            ],
            [
                'title' => 'Settings',
                'url' => '/admin/settings',
                'icon' => 'bi bi-gear',
                'permission' => 'admin.system_settings'
            ],
            [
                'title' => 'Permissions',
                'url' => '/permissions',
                'icon' => 'bi bi-shield-check',
                'permission' => 'admin.permissions'
            ],
            [
                'title' => 'Time Tracking Admin',
                'url' => '/time/admin',
                'icon' => 'fas fa-users-cog',
                'permission' => 'time.admin'
            ],
            [
                'title' => 'Time Analytics',
                'url' => '/time/analytics',
                'icon' => 'fas fa-chart-pie',
                'permission' => 'time.reports'
            ],
            [
                'title' => 'Employee Management',
                'url' => '/employees',
                'icon' => 'bi bi-person-badge',
                'permission' => 'employees.read'
            ],
            [
                'title' => 'Employee Performance',
                'url' => '/employees/performance',
                'icon' => 'bi bi-graph-up-arrow',
                'permission' => 'employees.read'
            ]
        ];
        
        $accessibleItems = [];
        $accessibleAdminItems = [];
        
        // Filter main menu items
        foreach ($menuItems as $item) {
            if (isset($item['always_show']) && $item['always_show']) {
                $accessibleItems[] = $item;
            } elseif (self::hasPermission($item['permission'])) {
                $accessibleItems[] = $item;
            }
        }
        
        // Filter admin menu items
        foreach ($adminItems as $item) {
            if (self::hasPermission($item['permission'])) {
                $accessibleAdminItems[] = $item;
            }
        }
        
        return [
            'main' => $accessibleItems,
            'admin' => $accessibleAdminItems
        ];
    }
}
?> 