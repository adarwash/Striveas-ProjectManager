<?php
class Admin extends Controller {
    private $userModel;
    private $projectModel;
    private $taskModel;
    private $settingsModel;
    private $logModel;
    
    public function __construct() {
        // Check if user is logged in and is an admin
        if (!isLoggedIn()) {
            redirect('users/login');
        }
        
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            flash('access_denied', 'You do not have permission to access the admin area', 'alert alert-danger');
            redirect('dashboard');
        }
        
        $this->userModel = $this->model('User');
        $this->projectModel = $this->model('Project');
        $this->taskModel = $this->model('Task');
        $this->settingsModel = $this->model('Setting');
        $this->logModel = $this->model('Log');
        
        // Ensure tables exist
        $this->settingsModel->createSettingsTable();
        $this->logModel->createLogsTable();
        
        // Log this admin access
        $this->logModel->addLog(
            'admin', 
            'Admin area accessed: ' . $_SERVER['REQUEST_URI'],
            $_SESSION['user_id'] ?? null,
            $_SESSION['user_name'] ?? null
        );
    }
    
    // Main admin dashboard
    public function index() {
        // Get some stats for the dashboard
        $totalUsers = $this->userModel->getTotalUsers();
        $totalProjects = $this->projectModel->getTotalProjects();
        $totalTasks = $this->taskModel->getTotalTasks();
        $recentUsers = $this->userModel->getRecentUsers(5);
        
        // Get system status
        $systemSettings = $this->settingsModel->getSystemSettings();
        
        $data = [
            'title' => 'Admin Dashboard',
            'totalUsers' => $totalUsers,
            'totalProjects' => $totalProjects,
            'totalTasks' => $totalTasks,
            'recentUsers' => $recentUsers,
            'systemSettings' => $systemSettings
        ];
        
        $this->view('admin/index', $data);
    }
    
    // User management
    public function users() {
        $users = $this->userModel->getAllUsers();
        
        // Load available roles for the dropdowns
        $roleModel = $this->model('Role');
        
        // Try to create default roles if they don't exist
        try {
            $roleModel->createDefaultRolesIfNotExist();
        } catch (Exception $e) {
            error_log('Could not create default roles: ' . $e->getMessage());
        }
        
        $availableRoles = $roleModel->getAllRoles();
        
        // If no roles exist, provide fallback basic roles
        if (empty($availableRoles)) {
            $availableRoles = [
                ['id' => null, 'name' => 'user', 'display_name' => 'User', 'description' => 'Standard user access'],
                ['id' => null, 'name' => 'admin', 'display_name' => 'Admin', 'description' => 'Administrator access']
            ];
        }
        
        $data = [
            'title' => 'User Management',
            'users' => $users,
            'available_roles' => $availableRoles
        ];
        
        $this->view('admin/users', $data);
    }
    
    // Add a new user
    public function add_user() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data - using a modern approach instead of the deprecated FILTER_SANITIZE_STRING
            $sanitizedPost = [];
            foreach ($_POST as $key => $value) {
                $sanitizedPost[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
            
            // Hash password
            $password = password_hash($sanitizedPost['password'], PASSWORD_DEFAULT);
            
            $data = [
                'name' => $sanitizedPost['name'],
                'email' => $sanitizedPost['email'],
                'password' => $password,
                'role' => $sanitizedPost['role']
            ];
            
            // Add user
            if ($this->userModel->register($data)) {
                flash('admin_message', 'User added successfully', 'alert alert-success');
            } else {
                flash('admin_message', 'Error adding user', 'alert alert-danger');
            }
            
            redirect('admin/users');
        } else {
            redirect('admin/users');
        }
    }
    
    // Update a user
    public function update_user() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data using a modern approach instead of the deprecated FILTER_SANITIZE_STRING
            $sanitizedPost = [];
            foreach ($_POST as $key => $value) {
                $sanitizedPost[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
            
            $data = [
                'id' => $sanitizedPost['id'],
                'name' => $sanitizedPost['name'],
                'email' => $sanitizedPost['email'],
                'role' => $sanitizedPost['role']
            ];
            
            // Check if password is being updated
            if (!empty($sanitizedPost['password'])) {
                $data['password'] = password_hash($sanitizedPost['password'], PASSWORD_DEFAULT);
                $result = $this->userModel->updateUserWithPassword($data);
            } else {
                $result = $this->userModel->updateUser($data);
            }
            
            if ($result) {
                flash('admin_message', 'User updated successfully', 'alert alert-success');
            } else {
                flash('admin_message', 'Error updating user', 'alert alert-danger');
            }
            
            redirect('admin/users');
        } else {
            redirect('admin/users');
        }
    }
    
    // Delete a user
    public function delete_user() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            
            if ($this->userModel->deleteUser($id)) {
                flash('admin_message', 'User deleted successfully', 'alert alert-success');
            } else {
                flash('admin_message', 'Error deleting user', 'alert alert-danger');
            }
            
            redirect('admin/users');
        } else {
            redirect('admin/users');
        }
    }
    
    // System logs
    public function logs() {
        // Get filters from GET parameters
        $filters = [
            'type' => $_GET['type'] ?? '',
            'user' => $_GET['user'] ?? '',
            'from_date' => $_GET['from_date'] ?? '',
            'to_date' => $_GET['to_date'] ?? ''
        ];
        
        // Get current page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = max(1, $page); // Ensure page is at least 1
        
        // Set items per page
        $limit = 15;
        
        // Get logs with pagination
        $logs = $this->logModel->getLogs($filters, $page, $limit);
        
        // Get total logs count for pagination
        $totalLogs = $this->logModel->getTotalLogs($filters);
        
        // Calculate pagination data
        $totalPages = ceil($totalLogs / $limit);
        
        // Prepare pagination object
        $pagination = (object)[
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'limit' => $limit,
            'totalItems' => $totalLogs
        ];
        
        $data = [
            'title' => 'System Logs',
            'logs' => $logs,
            'filters' => $filters,
            'pagination' => $pagination
        ];
        
        $this->view('admin/logs', $data);
    }
    
    // Get log details via AJAX
    public function log_details($id = null) {
        // Check if this is an AJAX request
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            redirect('admin/logs');
        }
        
        $id = filter_var($id, FILTER_VALIDATE_INT);
        
        if ($id) {
            $log = $this->logModel->getLogById($id);
            
            if ($log) {
                // Return JSON response
                header('Content-Type: application/json');
                echo json_encode($log);
                exit;
            }
        }
        
        // Return error
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Log not found']);
        exit;
    }
    
    // Export logs
    public function logs_export() {
        // Get filters from GET parameters
        $filters = [
            'type' => $_GET['type'] ?? '',
            'user' => $_GET['user'] ?? '',
            'from_date' => $_GET['from_date'] ?? '',
            'to_date' => $_GET['to_date'] ?? ''
        ];
        
        // Get all logs matching filters (no pagination)
        $logs = $this->logModel->getLogs($filters, 1, 1000);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="system_logs_export_' . date('Y-m-d') . '.csv"');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add CSV header row
        fputcsv($output, ['ID', 'Timestamp', 'Type', 'Message', 'User', 'IP Address']);
        
        // Add data rows
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['timestamp'],
                $log['type'],
                $log['message'],
                $log['user'] ?? 'System',
                $log['ip_address'] ?? '--'
            ]);
        }
        
        // Close output stream
        fclose($output);
        exit;
    }
    
    // System settings
    public function settings() {
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Prepare data for update
            $data = [
                'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
                'enable_registration' => isset($_POST['enable_registration']) ? 1 : 0,
                'enable_api' => isset($_POST['enable_api']) ? 1 : 0,
                'default_project_category' => trim($_POST['default_project_category']),
                'default_project_status' => trim($_POST['default_project_status']),
                'default_task_priority' => trim($_POST['default_task_priority']),
                'default_date_format' => trim($_POST['default_date_format']),
                'max_upload_size' => (int)trim($_POST['max_upload_size']),
                'max_projects' => (int)trim($_POST['max_projects'])
            ];
            
            // Update settings
            if ($this->settingsModel->updateSystemSettings($data)) {
                flash('settings_success', 'System settings updated successfully', 'alert alert-success');
            } else {
                flash('settings_error', 'Error updating system settings', 'alert alert-danger');
            }
            
            // Check if currency settings are being updated
            if (isset($_POST['currency_code'])) {
                $currency = [
                    'currency_code' => sanitize_input($_POST['currency_code'] ?? 'USD'),
                    'currency_symbol' => sanitize_input($_POST['currency_symbol'] ?? '$'),
                    'currency_position' => sanitize_input($_POST['currency_position'] ?? 'before'),
                    'currency_thousands_separator' => sanitize_input($_POST['thousands_separator'] ?? ','),
                    'currency_decimal_separator' => sanitize_input($_POST['decimal_separator'] ?? '.'),
                    'currency_decimals' => (int)($_POST['decimals'] ?? 2)
                ];
                
                // Save currency settings
                $this->settingsModel->set('currency', $currency);
            }
            
            redirect('admin/settings');
        }
        
        // Get current settings
        $systemSettings = $this->settingsModel->getSystemSettings();
        
        // Get currency settings
        $currency = $this->settingsModel->getCurrency();
        
        $data = [
            'title' => 'System Settings',
            'systemSettings' => $systemSettings,
            'currency' => $currency
        ];
        
        $this->view('admin/settings', $data);
    }
    
    // Toggle maintenance mode
    public function toggle_maintenance() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get maintenance mode status
            $maintenanceMode = isset($_POST['maintenance_mode']) ? 1 : 0;
            
            // Update setting
            if ($this->settingsModel->updateSystemSettings(['maintenance_mode' => $maintenanceMode])) {
                if ($maintenanceMode) {
                    flash('admin_message', 'Maintenance mode has been activated', 'alert alert-warning');
                } else {
                    flash('admin_message', 'Maintenance mode has been deactivated', 'alert alert-success');
                }
            } else {
                flash('admin_message', 'Error updating maintenance mode', 'alert alert-danger');
            }
            
            redirect('admin');
        } else {
            redirect('admin');
        }
    }
} 