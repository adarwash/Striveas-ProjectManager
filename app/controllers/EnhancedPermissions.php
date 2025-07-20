<?php

require_once __DIR__ . '/../models/EnhancedPermission.php';
require_once __DIR__ . '/../helpers/enhanced_permissions_helper.php';

class EnhancedPermissions extends Controller {
    private $enhancedPermissionModel;
    private $permissionModel;
    private $userModel;
    private $projectModel;
    
    public function __construct() {
        // Check if user is logged in and has admin permissions
        if (!isLoggedIn()) {
            redirect('users/login');
        }
        
        // Check if user has permission to manage permissions
        if (!hasPermission('admin.permissions')) {
            flash('access_denied', 'You do not have permission to access enhanced permission management', 'alert alert-danger');
            redirect('dashboard');
        }
        
        $this->enhancedPermissionModel = new EnhancedPermission();
        $this->permissionModel = $this->model('Permission');
        $this->userModel = $this->model('User');
        $this->projectModel = $this->model('Project');
    }
    
    /**
     * Enhanced permissions dashboard
     */
    public function index() {
        $data = [
            'title' => 'Enhanced Permission Management',
            'permission_groups' => $this->enhancedPermissionModel->getPermissionGroups(),
            'total_users' => count($this->userModel->getAllUsers()),
            'total_projects' => count($this->projectModel->getAllProjects()),
            'granular_permissions' => $this->getGranularPermissionStats()
        ];
        
        $this->view('admin/enhanced_permissions/index', $data);
    }
    
    /**
     * Resource-specific permissions management
     */
    public function resourcePermissions($resourceType = null, $resourceId = null) {
        if (!$resourceType || !$resourceId) {
            flash('error', 'Resource type and ID are required', 'alert alert-danger');
            redirect('enhanced_permissions');
        }
        
        // Get resource details
        $resource = $this->getResourceDetails($resourceType, $resourceId);
        if (!$resource) {
            flash('error', 'Resource not found', 'alert alert-danger');
            redirect('enhanced_permissions');
        }
        
        // Get users with permissions for this resource
        $usersWithPermissions = $this->getUsersWithResourcePermissions($resourceType, $resourceId);
        
        // Get available permissions for this resource type
        $availablePermissions = $this->getAvailableResourcePermissions($resourceType);
        
        $data = [
            'title' => "Resource Permissions - {$resource['name']}",
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'resource' => $resource,
            'users_with_permissions' => $usersWithPermissions,
            'available_permissions' => $availablePermissions,
            'all_users' => $this->userModel->getAllUsers()
        ];
        
        $this->view('admin/enhanced_permissions/resource_permissions', $data);
    }
    
    /**
     * Field-level permissions management
     */
    public function fieldPermissions($module = null) {
        if (!$module) {
            // Show all modules
            $modules = $this->getAvailableModules();
            $data = [
                'title' => 'Field-Level Permissions',
                'modules' => $modules
            ];
            $this->view('admin/enhanced_permissions/field_modules', $data);
            return;
        }
        
        // Get field permissions for specific module
        $moduleFields = $this->getModuleFields($module);
        $usersWithFieldPermissions = $this->getUsersWithFieldPermissions($module);
        
        $data = [
            'title' => "Field Permissions - " . ucfirst($module),
            'module' => $module,
            'module_fields' => $moduleFields,
            'users_with_permissions' => $usersWithFieldPermissions,
            'all_users' => $this->userModel->getAllUsers(),
            'access_levels' => ['read', 'write', 'hidden']
        ];
        
        $this->view('admin/enhanced_permissions/field_permissions', $data);
    }
    
    /**
     * Contextual permissions management
     */
    public function contextualPermissions() {
        $contextTypes = [
            'project_member' => 'Project Team Members',
            'task_assignee' => 'Task Assignees', 
            'department_member' => 'Department Members'
        ];
        
        $contextualPermissions = $this->getContextualPermissions();
        
        $data = [
            'title' => 'Contextual Permissions',
            'context_types' => $contextTypes,
            'contextual_permissions' => $contextualPermissions,
            'all_users' => $this->userModel->getAllUsers()
        ];
        
        $this->view('admin/enhanced_permissions/contextual_permissions', $data);
    }
    
    /**
     * Permission groups management
     */
    public function permissionGroups() {
        $groups = $this->enhancedPermissionModel->getPermissionGroups();
        
        $data = [
            'title' => 'Permission Groups',
            'permission_groups' => $groups
        ];
        
        $this->view('admin/enhanced_permissions/permission_groups', $data);
    }
    
    /**
     * Grant resource permission
     */
    public function grantResourcePermission() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userId = $_POST['user_id'] ?? null;
            $permissionName = $_POST['permission_name'] ?? null;
            $resourceType = $_POST['resource_type'] ?? null;
            $resourceId = $_POST['resource_id'] ?? null;
            $expiresAt = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
            
            if ($userId && $permissionName && $resourceType && $resourceId) {
                $success = $this->enhancedPermissionModel->grantResourcePermission(
                    $userId, $permissionName, $resourceType, $resourceId, 
                    $_SESSION['user_id'], $expiresAt
                );
                
                if ($success) {
                    flash('success', 'Resource permission granted successfully', 'alert alert-success');
                } else {
                    flash('error', 'Failed to grant resource permission', 'alert alert-danger');
                }
            } else {
                flash('error', 'All fields are required', 'alert alert-danger');
            }
            
            redirect("enhanced_permissions/resourcePermissions/$resourceType/$resourceId");
        }
    }
    
    /**
     * Grant field permission
     */
    public function grantFieldPermission() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userId = $_POST['user_id'] ?? null;
            $module = $_POST['module'] ?? null;
            $fieldName = $_POST['field_name'] ?? null;
            $accessLevel = $_POST['access_level'] ?? null;
            $resourceId = !empty($_POST['resource_id']) ? $_POST['resource_id'] : null;
            
            if ($userId && $module && $fieldName && $accessLevel) {
                $success = $this->enhancedPermissionModel->grantFieldPermission(
                    $userId, $module, $fieldName, $accessLevel, $resourceId
                );
                
                if ($success) {
                    flash('success', 'Field permission granted successfully', 'alert alert-success');
                } else {
                    flash('error', 'Failed to grant field permission', 'alert alert-danger');
                }
            } else {
                flash('error', 'All fields are required', 'alert alert-danger');
            }
            
            redirect("enhanced_permissions/fieldPermissions/$module");
        }
    }
    
    /**
     * Grant contextual permission
     */
    public function grantContextualPermission() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userId = $_POST['user_id'] ?? null;
            $contextType = $_POST['context_type'] ?? null;
            $contextId = $_POST['context_id'] ?? null;
            $permissionName = $_POST['permission_name'] ?? null;
            
            if ($userId && $contextType && $contextId && $permissionName) {
                $success = $this->enhancedPermissionModel->grantContextualPermission(
                    $userId, $contextType, $contextId, $permissionName
                );
                
                if ($success) {
                    flash('success', 'Contextual permission granted successfully', 'alert alert-success');
                } else {
                    flash('error', 'Failed to grant contextual permission', 'alert alert-danger');
                }
            } else {
                flash('error', 'All fields are required', 'alert alert-danger');
            }
            
            redirect('enhanced_permissions/contextualPermissions');
        }
    }
    
    /**
     * Assign permission group to user
     */
    public function assignPermissionGroup() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userId = $_POST['user_id'] ?? null;
            $groupId = $_POST['group_id'] ?? null;
            
            if ($userId && $groupId) {
                $success = $this->enhancedPermissionModel->assignPermissionGroupToUser($userId, $groupId);
                
                if ($success) {
                    flash('success', 'Permission group assigned successfully', 'alert alert-success');
                } else {
                    flash('error', 'Failed to assign permission group', 'alert alert-danger');
                }
            } else {
                flash('error', 'User and group are required', 'alert alert-danger');
            }
            
            redirect('enhanced_permissions/permissionGroups');
        }
    }
    
    /**
     * User permission summary
     */
    public function userSummary($userId = null) {
        if (!$userId) {
            flash('error', 'User ID is required', 'alert alert-danger');
            redirect('enhanced_permissions');
        }
        
        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            flash('error', 'User not found', 'alert alert-danger');
            redirect('enhanced_permissions');
        }
        
        $permissionsSummary = $this->enhancedPermissionModel->getUserPermissionsSummary($userId);
        $resourcePermissions = $this->getUserResourcePermissionsSummary($userId);
        $fieldPermissions = $this->getUserFieldPermissionsSummary($userId);
        
        $data = [
            'title' => "Permission Summary - {$user['username']}",
            'user' => $user,
            'permissions_summary' => $permissionsSummary,
            'resource_permissions' => $resourcePermissions,
            'field_permissions' => $fieldPermissions
        ];
        
        $this->view('admin/enhanced_permissions/user_summary', $data);
    }
    
    /**
     * Cleanup expired permissions
     */
    public function cleanupExpired() {
        $success = $this->enhancedPermissionModel->cleanupExpiredPermissions();
        
        if ($success) {
            flash('success', 'Expired permissions cleaned up successfully', 'alert alert-success');
        } else {
            flash('error', 'Failed to cleanup expired permissions', 'alert alert-danger');
        }
        
        redirect('enhanced_permissions');
    }
    
    // Helper methods
    
    private function getGranularPermissionStats() {
        try {
            $db = new EasySQL(DB1);
            
            $stats = [];
            
            // Count resource-specific permissions
            $result = $db->select("SELECT COUNT(*) as count FROM dbo.ResourcePermissions WHERE granted = 1");
            $stats['resource_permissions'] = $result[0]['count'] ?? 0;
            
            // Count field-level permissions
            $result = $db->select("SELECT COUNT(*) as count FROM dbo.FieldPermissions");
            $stats['field_permissions'] = $result[0]['count'] ?? 0;
            
            // Count contextual permissions
            $result = $db->select("SELECT COUNT(*) as count FROM dbo.ContextualPermissions WHERE granted = 1");
            $stats['contextual_permissions'] = $result[0]['count'] ?? 0;
            
            return $stats;
        } catch (Exception $e) {
            error_log('getGranularPermissionStats Error: ' . $e->getMessage());
            return ['resource_permissions' => 0, 'field_permissions' => 0, 'contextual_permissions' => 0];
        }
    }
    
    private function getResourceDetails($resourceType, $resourceId) {
        try {
            $db = new EasySQL(DB1);
            
            switch ($resourceType) {
                case 'project':
                    $result = $db->select("SELECT id, title as name, description FROM dbo.Projects WHERE id = ?", [$resourceId]);
                    break;
                case 'task':
                    $result = $db->select("SELECT id, title as name, description FROM dbo.Tasks WHERE id = ?", [$resourceId]);
                    break;
                case 'client':
                    $result = $db->select("SELECT id, name, description FROM dbo.Clients WHERE id = ?", [$resourceId]);
                    break;
                case 'site':
                    $result = $db->select("SELECT id, name, location as description FROM dbo.Sites WHERE id = ?", [$resourceId]);
                    break;
                default:
                    return null;
            }
            
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            error_log('getResourceDetails Error: ' . $e->getMessage());
            return null;
        }
    }
    
    private function getUsersWithResourcePermissions($resourceType, $resourceId) {
        try {
            $db = new EasySQL(DB1);
            $query = "SELECT u.id, u.username, u.email, rp.permission_name, rp.granted, rp.expires_at
                     FROM dbo.Users u
                     INNER JOIN dbo.ResourcePermissions rp ON u.id = rp.user_id
                     WHERE rp.resource_type = ? AND rp.resource_id = ?
                     ORDER BY u.username, rp.permission_name";
            
            return $db->select($query, [$resourceType, $resourceId]) ?: [];
        } catch (Exception $e) {
            error_log('getUsersWithResourcePermissions Error: ' . $e->getMessage());
            return [];
        }
    }
    
    private function getAvailableResourcePermissions($resourceType) {
        $permissionsByType = [
            'project' => [
                'projects.read', 'projects.update', 'projects.delete',
                'projects.view_budget', 'projects.edit_budget', 'projects.manage_team'
            ],
            'task' => [
                'tasks.read', 'tasks.update', 'tasks.delete',
                'tasks.view_time_tracking', 'tasks.edit_estimates'
            ],
            'client' => [
                'clients.read', 'clients.update', 'clients.delete',
                'clients.view_internal_notes', 'clients.manage_contracts'
            ],
            'site' => [
                'sites.read', 'sites.update', 'sites.delete',
                'sites.view_sensitive_info', 'sites.manage_access_codes'
            ]
        ];
        
        return $permissionsByType[$resourceType] ?? [];
    }
    
    private function getAvailableModules() {
        return [
            'projects' => 'Projects',
            'tasks' => 'Tasks', 
            'users' => 'Users',
            'clients' => 'Clients',
            'sites' => 'Sites',
            'invoices' => 'Invoices',
            'reports' => 'Reports'
        ];
    }
    
    private function getModuleFields($module) {
        $fieldsByModule = [
            'projects' => ['budget', 'description', 'start_date', 'end_date', 'status'],
            'tasks' => ['description', 'due_date', 'priority', 'estimated_hours', 'actual_hours'],
            'users' => ['salary', 'phone', 'address', 'emergency_contact', 'notes'],
            'clients' => ['internal_notes', 'contract_terms', 'payment_history'],
            'sites' => ['access_codes', 'security_info', 'emergency_contacts'],
            'invoices' => ['amount', 'payment_terms', 'internal_notes'],
            'reports' => ['financial_data', 'performance_metrics', 'sensitive_data']
        ];
        
        return $fieldsByModule[$module] ?? [];
    }
    
    private function getUsersWithFieldPermissions($module) {
        try {
            $db = new EasySQL(DB1);
            $query = "SELECT u.id, u.username, u.email, fp.field_name, fp.access_level, fp.resource_id
                     FROM dbo.Users u
                     INNER JOIN dbo.FieldPermissions fp ON u.id = fp.user_id
                     WHERE fp.module = ?
                     ORDER BY u.username, fp.field_name";
            
            return $db->select($query, [$module]) ?: [];
        } catch (Exception $e) {
            error_log('getUsersWithFieldPermissions Error: ' . $e->getMessage());
            return [];
        }
    }
    
    private function getContextualPermissions() {
        try {
            $db = new EasySQL(DB1);
            $query = "SELECT u.id, u.username, u.email, cp.context_type, cp.context_id, cp.permission_name, cp.granted
                     FROM dbo.Users u
                     INNER JOIN dbo.ContextualPermissions cp ON u.id = cp.user_id
                     ORDER BY u.username, cp.context_type";
            
            return $db->select($query) ?: [];
        } catch (Exception $e) {
            error_log('getContextualPermissions Error: ' . $e->getMessage());
            return [];
        }
    }
    
    private function getUserResourcePermissionsSummary($userId) {
        try {
            $db = new EasySQL(DB1);
            $query = "SELECT resource_type, resource_id, permission_name, granted, expires_at
                     FROM dbo.ResourcePermissions
                     WHERE user_id = ? AND granted = 1
                     ORDER BY resource_type, resource_id";
            
            return $db->select($query, [$userId]) ?: [];
        } catch (Exception $e) {
            error_log('getUserResourcePermissionsSummary Error: ' . $e->getMessage());
            return [];
        }
    }
    
    private function getUserFieldPermissionsSummary($userId) {
        try {
            $db = new EasySQL(DB1);
            $query = "SELECT module, field_name, access_level, resource_id
                     FROM dbo.FieldPermissions
                     WHERE user_id = ?
                     ORDER BY module, field_name";
            
            return $db->select($query, [$userId]) ?: [];
        } catch (Exception $e) {
            error_log('getUserFieldPermissionsSummary Error: ' . $e->getMessage());
            return [];
        }
    }
} 