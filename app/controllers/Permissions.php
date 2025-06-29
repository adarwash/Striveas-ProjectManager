<?php
class Permissions extends Controller {
    private $permissionModel;
    private $roleModel;
    private $userModel;
    
    public function __construct() {
        // Check if user is logged in and has permission management access
        if (!isLoggedIn()) {
            redirect('users/login');
        }
        
        $this->permissionModel = $this->model('Permission');
        $this->roleModel = $this->model('Role');
        $this->userModel = $this->model('User');
        
        // Check if user has permission to manage permissions
        // Allow admin users to access during initial setup
        $hasPermission = false;
        try {
            $hasPermission = $this->permissionModel->userHasPermission($_SESSION['user_id'], 'admin.permissions');
        } catch (Exception $e) {
            // If permission tables don't exist yet, allow admin users
            $hasPermission = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
        }
        
        if (!$hasPermission) {
            flash('access_denied', 'You do not have permission to access permission management', 'alert alert-danger');
            redirect('dashboard');
        }
    }
    
    // Main permissions dashboard
    public function index() {
        $roles = $this->roleModel->getAllRolesWithStats();
        $totalPermissions = count($this->permissionModel->getAllPermissions());
        $permissions = $this->permissionModel->getPermissionsGroupedByModule();
        
        $data = [
            'title' => 'Permission Management',
            'roles' => $roles,
            'totalPermissions' => $totalPermissions,
            'permissions' => $permissions
        ];
        
        $this->view('admin/permissions/index', $data);
    }
    
    // Roles management
    public function roles() {
        $roles = $this->roleModel->getAllRolesWithStats();
        
        $data = [
            'title' => 'Role Management',
            'roles' => $roles
        ];
        
        $this->view('admin/permissions/roles', $data);
    }
    
    // Create new role
    public function create_role() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $sanitizedPost = [];
            foreach ($_POST as $key => $value) {
                $sanitizedPost[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
            
            $data = [
                'name' => strtolower(str_replace(' ', '_', $sanitizedPost['display_name'])),
                'display_name' => $sanitizedPost['display_name'],
                'description' => $sanitizedPost['description']
            ];
            
            if ($this->roleModel->createRole($data)) {
                flash('permissions_message', 'Role created successfully', 'alert alert-success');
            } else {
                flash('permissions_message', 'Error creating role', 'alert alert-danger');
            }
            
            redirect('permissions/roles');
        } else {
            redirect('permissions/roles');
        }
    }
    
    // Edit role
    public function edit_role($id = null) {
        if (!$id) {
            redirect('permissions/roles');
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $sanitizedPost = [];
            foreach ($_POST as $key => $value) {
                $sanitizedPost[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
            
            $data = [
                'id' => $id,
                'display_name' => $sanitizedPost['display_name'],
                'description' => $sanitizedPost['description'],
                'is_active' => isset($sanitizedPost['is_active']) ? 1 : 0
            ];
            
            if ($this->roleModel->updateRole($data)) {
                flash('permissions_message', 'Role updated successfully', 'alert alert-success');
            } else {
                flash('permissions_message', 'Error updating role', 'alert alert-danger');
            }
            
            redirect('permissions/roles');
        } else {
            $role = $this->roleModel->getRoleById($id);
            if (!$role) {
                redirect('permissions/roles');
            }
            
            $data = [
                'title' => 'Edit Role',
                'role' => $role
            ];
            
            $this->view('admin/permissions/edit_role', $data);
        }
    }
    
    // Delete role
    public function delete_role() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            
            if ($this->roleModel->deleteRole($id)) {
                flash('permissions_message', 'Role deleted successfully', 'alert alert-success');
            } else {
                flash('permissions_message', 'Cannot delete role - it may have assigned users', 'alert alert-danger');
            }
            
            redirect('permissions/roles');
        } else {
            redirect('permissions/roles');
        }
    }
    
    // Manage role permissions
    public function role_permissions($roleId = null) {
        if (!$roleId) {
            redirect('permissions/roles');
        }
        
        $role = $this->roleModel->getRoleById($roleId);
        if (!$role) {
            redirect('permissions/roles');
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $permissionIds = $_POST['permissions'] ?? [];
            
            if ($this->roleModel->syncRolePermissions($roleId, $permissionIds)) {
                flash('permissions_message', 'Role permissions updated successfully', 'alert alert-success');
            } else {
                flash('permissions_message', 'Error updating role permissions', 'alert alert-danger');
            }
            
            redirect('permissions/role_permissions/' . $roleId);
        } else {
            $allPermissions = $this->permissionModel->getPermissionsGroupedByModule();
            $rolePermissionIds = $this->roleModel->getRolePermissionIds($roleId);
            
            $data = [
                'title' => 'Manage Role Permissions',
                'role' => $role,
                'allPermissions' => $allPermissions,
                'rolePermissionIds' => $rolePermissionIds
            ];
            
            $this->view('admin/permissions/role_permissions', $data);
        }
    }
    
    // User permissions management
    public function user_permissions($userId = null) {
        if (!$userId) {
            // Show list of users for permission management
            $users = $this->userModel->getAllUsers();
            
            $data = [
                'title' => 'User Permission Management',
                'users' => $users
            ];
            
            $this->view('admin/permissions/user_list', $data);
        } else {
            $user = $this->userModel->getUserById($userId);
            if (!$user) {
                redirect('permissions/user_permissions');
            }
            
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $permissionIds = $_POST['permissions'] ?? [];
                
                if ($this->permissionModel->syncUserPermissions($userId, $permissionIds)) {
                    flash('permissions_message', 'User permissions updated successfully', 'alert alert-success');
                } else {
                    flash('permissions_message', 'Error updating user permissions', 'alert alert-danger');
                }
                
                redirect('permissions/user_permissions/' . $userId);
            } else {
                $allPermissions = $this->permissionModel->getPermissionsGroupedByModule();
                $userPermissions = $this->permissionModel->getUserPermissions($userId);
                $userPermissionIds = array_column($userPermissions, 'id');
                
                // Get user's role permissions for comparison
                $userRole = null;
                if ($user['role_id']) {
                    $userRole = $this->roleModel->getRoleById($user['role_id']);
                    $rolePermissionIds = $this->roleModel->getRolePermissionIds($user['role_id']);
                } else {
                    $rolePermissionIds = [];
                }
                
                $data = [
                    'title' => 'Manage User Permissions',
                    'user' => $user,
                    'userRole' => $userRole,
                    'allPermissions' => $allPermissions,
                    'userPermissionIds' => $userPermissionIds,
                    'rolePermissionIds' => $rolePermissionIds
                ];
                
                $this->view('admin/permissions/user_permissions', $data);
            }
        }
    }
} 