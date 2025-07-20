<?php

class Users extends Controller {
    private $userModel;
    
    public function __construct() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth/login');
        }
        
        $this->userModel = $this->model('User');
    }
    
    /**
     * Users index page - shows user listing or redirects based on permissions
     */
    public function index() {
        // Check for role filter in query parameters
        $role = $_GET['role'] ?? null;
        
        // Check if user has admin permissions
        if (isAdmin()) {
            // Redirect admins to the full admin users management page
            $adminUrl = 'admin/users';
            if ($role) {
                $adminUrl .= '?role=' . urlencode($role);
            }
            redirect($adminUrl);
            return;
        }
        
        // For non-admin users, show a basic user directory
        $users = $this->userModel->getAllUsers();
        
        // Filter users by role if specified
        if ($role) {
            $users = array_filter($users, function($user) use ($role) {
                return strtolower($user['role']) === strtolower($role);
            });
            
            $data = [
                'title' => ucfirst($role) . ' Directory',
                'users' => $users,
                'filtered_role' => $role
            ];
        } else {
            $data = [
                'title' => 'User Directory',
                'users' => $users
            ];
        }
        
        $this->view('users/index', $data);
    }
    
    /**
     * Handle role filtering (e.g., /users?role=technician)
     */
    public function filter() {
        $role = $_GET['role'] ?? null;
        
        if ($role) {
            if (isAdmin()) {
                // Redirect to admin users page with role filter
                redirect('admin/users?role=' . urlencode($role));
                return;
            }
            
            // For non-admin users, show filtered directory
            $users = $this->userModel->getAllUsers();
            
            // Filter users by role
            $filteredUsers = array_filter($users, function($user) use ($role) {
                return strtolower($user['role']) === strtolower($role);
            });
            
            $data = [
                'title' => ucfirst($role) . ' Directory',
                'users' => $filteredUsers,
                'filtered_role' => $role
            ];
            
            $this->view('users/index', $data);
        } else {
            $this->index();
        }
    }
} 