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
    
    /**
     * Email Settings Page
     */
    public function emailSettings() {
        // Load current settings
        $settings = $this->settingsModel->getAllSettings();
        
        $data = [
            'title' => 'Email Settings',
            'settings' => $settings
        ];
        
        // Use the new OAuth-enabled view
        $this->view('admin/email_settings_oauth', $data);
    }
    
    /**
     * Save Email Settings
     */
    public function saveEmailSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/emailSettings');
        }
        
        $settingsType = $_POST['settings_type'] ?? 'graph_api';
        
        if ($settingsType === 'graph_api') {
            // Save Graph API settings
            $settings = [
                'graph_tenant_id' => trim($_POST['graph_tenant_id'] ?? ''),
                'graph_client_id' => trim($_POST['graph_client_id'] ?? ''),
                'graph_client_secret' => trim($_POST['graph_client_secret'] ?? ''),
                'graph_support_email' => trim($_POST['graph_support_email'] ?? ''),
                'graph_enabled' => isset($_POST['graph_enabled']) ? '1' : '0',
                'graph_auto_process' => isset($_POST['graph_auto_process']) ? '1' : '0'
            ];
            
            foreach ($settings as $key => $value) {
                $this->settingsModel->setSetting($key, $value);
            }
            
            flash('success', 'Graph API settings saved successfully!', 'alert alert-success');
        } elseif ($settingsType === 'smtp') {
            // Save SMTP settings
            $settings = [
                'smtp_host' => trim($_POST['smtp_host'] ?? ''),
                'smtp_port' => trim($_POST['smtp_port'] ?? '587'),
                'smtp_username' => trim($_POST['smtp_username'] ?? ''),
                'smtp_password' => trim($_POST['smtp_password'] ?? ''),
                'smtp_encryption' => trim($_POST['smtp_encryption'] ?? 'tls')
            ];
            
            foreach ($settings as $key => $value) {
                $this->settingsModel->setSetting($key, $value);
            }
            
            flash('success', 'SMTP settings saved successfully!', 'alert alert-success');
        }
        
        redirect('admin/emailSettings');
    }
    
    /**
     * Save Email Processing Settings
     */
    public function saveEmailProcessingSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/emailSettings');
        }
        
        // Save email processing settings
        $settings = [
            'email_folder' => trim($_POST['email_folder'] ?? 'Inbox'),
            'check_interval' => (int)($_POST['check_interval'] ?? 5),
            'auto_reply' => $_POST['auto_reply'] ?? '1',
            'default_priority' => $_POST['default_priority'] ?? 'Medium',
            'allowed_domains' => trim($_POST['allowed_domains'] ?? '')
        ];
        
        foreach ($settings as $key => $value) {
            $this->settingsModel->setSetting($key, $value);
        }
        
        flash('settings_success', 'Email processing settings saved successfully!', 'alert alert-success');
        redirect('admin/emailSettings');
    }
    
    /**
     * Test Graph API Connection (AJAX)
     */
    public function testGraphConnection() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            return;
        }
        
        // Get settings from database
        $settings = $this->settingsModel->getAllSettings();
        
        // Check if required settings exist
        if (empty($settings['graph_client_id']) || empty($settings['graph_client_secret'])) {
            echo json_encode([
                'success' => false, 
                'message' => 'Please save your Microsoft App credentials first'
            ]);
            return;
        }
        
        // Test the connection
        require_once APPROOT . '/app/services/MicrosoftGraphService.php';
        
        // Temporarily set the config for testing
        if (!defined('GRAPH_TENANT_ID')) {
            $tenantId = isset($settings['graph_tenant_id']) && !empty($settings['graph_tenant_id']) 
                        ? $settings['graph_tenant_id'] : 'common';
            define('GRAPH_TENANT_ID', $tenantId);
            define('GRAPH_CLIENT_ID', $settings['graph_client_id']);
            define('GRAPH_CLIENT_SECRET', $settings['graph_client_secret']);
        }
        
        try {
            $graph = new MicrosoftGraphService();
            
            // Test getting token
            $token = $graph->getAccessToken();
            
            // For testing, we don't need to check emails yet
            // Just verify we can get a token
            
            echo json_encode([
                'success' => true,
                'message' => 'Connection successful! Microsoft Graph API is configured correctly.'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'details' => 'Check your Azure AD app configuration and permissions'
            ]);
        }
    }
    
    /**
     * Send Test Email (AJAX)
     */
    public function sendTestEmail() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $to = $input['to'] ?? '';
        
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Invalid email address']);
            return;
        }
        
        try {
            require_once APPROOT . '/app/services/MicrosoftGraphService.php';
            $graph = new MicrosoftGraphService();
            
            $supportEmail = $this->settingsModel->getSetting('graph_support_email') ?: 'support@yourdomain.com';
            
            $graph->sendEmail(
                $supportEmail,
                $to,
                'Test Email from ProjectTracker',
                '<p>This is a test email from your ProjectTracker ticketing system.</p>
                 <p>If you received this, your email configuration is working correctly!</p>'
            );
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
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
            
            // Email configuration settings
            $emailSettings = [
                // SMTP (Outbound) Settings
                'from_email' => trim($_POST['from_email'] ?? ''),
                'from_name' => trim($_POST['from_name'] ?? 'Hive IT Portal'),
                'smtp_host' => trim($_POST['smtp_host'] ?? ''),
                'smtp_port' => (int)trim($_POST['smtp_port'] ?? 587),
                'smtp_username' => trim($_POST['smtp_username'] ?? ''),
                'smtp_password' => trim($_POST['smtp_password'] ?? ''),
                'smtp_encryption' => trim($_POST['smtp_encryption'] ?? 'tls'),
                
                // Inbound Email Settings (supports both POP3 and IMAP)
                'inbound_protocol' => trim($_POST['inbound_protocol'] ?? 'imap'),
                'inbound_auth_type' => trim($_POST['inbound_auth_type'] ?? 'password'),
                'inbound_host' => trim($_POST['inbound_host'] ?? ''),
                'inbound_port' => (int)trim($_POST['inbound_port'] ?? 993),
                'inbound_username' => trim($_POST['inbound_username'] ?? ''),
                'inbound_password' => trim($_POST['inbound_password'] ?? ''),
                'inbound_encryption' => trim($_POST['inbound_encryption'] ?? 'ssl'),
                'imap_folder' => trim($_POST['imap_folder'] ?? 'INBOX'),
                
                // OAuth2 Settings
                'oauth2_provider' => trim($_POST['oauth2_provider'] ?? 'microsoft'),
                'oauth2_client_id' => trim($_POST['oauth2_client_id'] ?? ''),
                'oauth2_client_secret' => trim($_POST['oauth2_client_secret'] ?? ''),
                'oauth2_redirect_uri' => trim($_POST['oauth2_redirect_uri'] ?? ''),
                
                // Processing Settings
                'auto_process_emails' => isset($_POST['auto_process_emails']) ? 1 : 0,
                'delete_processed_emails' => isset($_POST['delete_processed_emails']) ? 1 : 0,
                'ticket_email_pattern' => trim($_POST['ticket_email_pattern'] ?? '/\[TKT-\d{4}-\d{6}\]/'),
                'max_attachment_size' => (int)trim($_POST['max_attachment_size'] ?? 10) * 1048576, // Convert MB to bytes
                'allowed_file_types' => trim($_POST['allowed_file_types'] ?? 'pdf,doc,docx,txt,png,jpg,jpeg,gif')
            ];
            
            // Combine with system settings
            $data = array_merge($data, $emailSettings);
            
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
    
    // Test email configuration
    public function testEmail() {
        // Ensure this is a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        // Clear any previous output to ensure clean JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Set JSON response header
        header('Content-Type: application/json');
        
        // Suppress notices and warnings to prevent JSON corruption
        $oldErrorReporting = error_reporting(E_ERROR | E_PARSE);
        
        try {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'test_smtp') {
                $this->testSMTP();
            } elseif ($action === 'test_inbound') {
                $this->testInboundEmail();
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } finally {
            // Restore original error reporting
            error_reporting($oldErrorReporting);
        }
    }
    
    // Test SMTP configuration
    private function testSMTP() {
        try {
            // Get SMTP settings from form
            $smtpConfig = [
                'host' => trim($_POST['smtp_host'] ?? ''),
                'port' => (int)($_POST['smtp_port'] ?? 587),
                'username' => trim($_POST['smtp_username'] ?? ''),
                'password' => trim($_POST['smtp_password'] ?? ''),
                'encryption' => trim($_POST['smtp_encryption'] ?? 'tls'),
                'from_email' => trim($_POST['from_email'] ?? ''),
                'from_name' => trim($_POST['from_name'] ?? 'Hive IT Portal')
            ];
            
            // Validate required fields
            if (empty($smtpConfig['host']) || empty($smtpConfig['from_email'])) {
                echo json_encode(['success' => false, 'error' => 'SMTP host and from email are required']);
                return;
            }
            
            // Load SimpleMailer
            require_once '../app/libraries/SimpleMailer.php';
            
            // Create mailer instance
            $mailer = new SimpleMailer($smtpConfig);
            
            // Prepare test email
            $emailData = [
                'to' => $smtpConfig['from_email'], // Send test email to sender
                'subject' => 'SMTP Configuration Test - ' . date('Y-m-d H:i:s'),
                'body' => 'This is a test email to verify your SMTP configuration is working correctly.<br><br>If you received this email, your SMTP settings are properly configured!<br><br>Sent from: Hive IT Portal<br>Time: ' . date('Y-m-d H:i:s'),
                'is_html' => true
            ];
            
            // Send test email
            $result = $mailer->send($emailData);
            
            if ($result) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Test email sent successfully to ' . $smtpConfig['from_email']
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'error' => 'Failed to send test email. Please check your SMTP settings.'
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'SMTP Error: ' . $e->getMessage()]);
        }
    }
    
    // Test inbound email configuration (supports both IMAP and POP3)
    private function testInboundEmail() {
        // Clear any previous output and start clean
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Suppress PHP notices and warnings for IMAP functions (still used for POP3)
        $oldErrorReporting = error_reporting(E_ERROR | E_PARSE);
        
        try {
            // Get inbound email settings from form
            $protocol = trim($_POST['inbound_protocol'] ?? 'imap');
            $inboundConfig = [
                'protocol' => $protocol,
                'host' => trim($_POST['inbound_host'] ?? ''),
                'port' => (int)($_POST['inbound_port'] ?? ($protocol === 'pop3' ? 995 : 993)),
                'username' => trim($_POST['inbound_username'] ?? ''),
                'password' => trim($_POST['inbound_password'] ?? ''),
                'encryption' => trim($_POST['inbound_encryption'] ?? 'ssl'),
                'folder' => trim($_POST['imap_folder'] ?? 'INBOX') // Only used for IMAP
            ];
            
            // Validate required fields
            if (empty($inboundConfig['host']) || empty($inboundConfig['username']) || empty($inboundConfig['password'])) {
                echo json_encode(['success' => false, 'error' => ucfirst($protocol) . ' host, username, and password are required']);
                return;
            }
            
            // Check if IMAP extension is loaded (needed for POP3 too)
            if (!extension_loaded('imap')) {
                echo json_encode(['success' => false, 'error' => 'PHP IMAP extension is not installed']);
                return;
            }
            
            // Clear any IMAP errors from previous attempts
            @imap_errors();
            @imap_alerts();
            
            // Build connection string with improved error handling
            $encryption = strtolower($inboundConfig['encryption']);
            $flags = '/' . $protocol;
            
            if ($encryption === 'ssl') {
                $flags .= '/ssl';
            } elseif ($encryption === 'tls') {
                $flags .= '/tls';
            }
            
            // Add additional flags for better connection handling
            $flags .= '/novalidate-cert'; // Skip certificate validation for testing
            $flags .= '/norsh'; // Disable rsh fallback
            
            // Build server string based on protocol
            if ($protocol === 'imap') {
                $server = '{' . $inboundConfig['host'] . ':' . $inboundConfig['port'] . $flags . '}' . $inboundConfig['folder'];
            } else {
                $server = '{' . $inboundConfig['host'] . ':' . $inboundConfig['port'] . $flags . '}';
            }
            
            // First, try a basic connection test with timeout
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);
            
            // Test basic connectivity first
            $testSocket = @fsockopen($inboundConfig['host'], $inboundConfig['port'], $errno, $errstr, 10);
            if (!$testSocket) {
                echo json_encode([
                    'success' => false, 
                    'error' => 'Cannot connect to ' . strtoupper($protocol) . ' server: ' . $errstr . ' (Error: ' . $errno . ')'
                ]);
                return;
            }
            fclose($testSocket);
            
            // Attempt connection with multiple retry attempts
            $connection = false;
            $maxRetries = 3;
            $lastError = '';
            
            for ($retry = 0; $retry < $maxRetries; $retry++) {
                // Clear previous errors
                @imap_errors();
                @imap_alerts();
                
                // Try different connection flags on each retry
                $retryFlags = $flags;
                if ($retry == 1) {
                    // Second attempt: try without SSL/TLS if encryption was enabled
                    $retryFlags = '/' . $protocol . '/novalidate-cert/norsh';
                    if ($protocol === 'imap') {
                        $retryServer = '{' . $inboundConfig['host'] . ':' . $inboundConfig['port'] . $retryFlags . '}' . $inboundConfig['folder'];
                    } else {
                        $retryServer = '{' . $inboundConfig['host'] . ':' . $inboundConfig['port'] . $retryFlags . '}';
                    }
                } else if ($retry == 2) {
                    // Third attempt: try with different auth method
                    $retryFlags = $flags . '/notls';
                    if ($protocol === 'imap') {
                        $retryServer = '{' . $inboundConfig['host'] . ':' . $inboundConfig['port'] . $retryFlags . '}' . $inboundConfig['folder'];
                    } else {
                        $retryServer = '{' . $inboundConfig['host'] . ':' . $inboundConfig['port'] . $retryFlags . '}';
                    }
                } else {
                    $retryServer = $server;
                }
                
                $connection = @imap_open($retryServer, $inboundConfig['username'], $inboundConfig['password'], OP_HALFOPEN);
                
                if ($connection) {
                    break; // Success, break out of retry loop
                } else {
                    // Capture error for this attempt
                    $errors = @imap_errors();
                    if (is_array($errors) && !empty($errors)) {
                        $lastError = end($errors);
                    }
                    
                    // Wait a bit before retrying
                    if ($retry < $maxRetries - 1) {
                        usleep(500000); // 0.5 second delay
                    }
                }
            }
            
            if ($connection) {
                // Get message count based on protocol
                if ($protocol === 'pop3') {
                    $messageCount = @imap_num_msg($connection);
                } else {
                    // For IMAP, get mailbox info
                    $mailboxInfo = @imap_status($connection, $server, SA_ALL);
                    $messageCount = isset($mailboxInfo->messages) ? $mailboxInfo->messages : 0;
                }
                
                // Close connection
                @imap_close($connection);
                
                // Clear any IMAP errors/alerts that might have been generated
                @imap_errors();
                @imap_alerts();
                
                echo json_encode([
                    'success' => true, 
                    'message' => strtoupper($protocol) . ' connection successful',
                    'message_count' => $messageCount,
                    'server_used' => $server,
                    'protocol' => strtoupper($protocol)
                ]);
            } else {
                // Connection failed after all retries
                $finalError = !empty($lastError) ? $lastError : 'Unknown connection error';
                
                // Clear errors to prevent them from appearing in output
                @imap_errors();
                @imap_alerts();
                
                // Provide more specific error guidance
                $errorMessage = strtoupper($protocol) . ' connection failed: ' . $finalError;
                
                if (strpos($finalError, 'certificate') !== false) {
                    $errorMessage .= ' (Try disabling SSL certificate verification in your email client)';
                } elseif (strpos($finalError, 'authenticate') !== false || strpos($finalError, 'login') !== false) {
                    // Check if this is Microsoft 365
                    $host = strtolower($inboundConfig['host']);
                    if (strpos($host, 'outlook.office365.com') !== false || strpos($host, 'outlook.com') !== false) {
                        $errorMessage .= ' (Microsoft 365 requires an App Password. Go to Microsoft 365 Security settings to create one. Also ensure IMAP is enabled in your mailbox settings)';
                    } else {
                        $errorMessage .= ' (Please check your username and password. For Gmail, use an App Password instead of your regular password)';
                    }
                } elseif (strpos($finalError, 'CLOSED') !== false) {
                    $errorMessage .= ' (Server closed connection. This may be due to security restrictions, incorrect settings, or server-side firewall rules)';
                }
                
                echo json_encode([
                    'success' => false, 
                    'error' => $errorMessage
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => ucfirst($protocol) . ' Error: ' . $e->getMessage()]);
        } finally {
            // Restore original error reporting
            error_reporting($oldErrorReporting);
            
            // Clear any remaining IMAP errors/alerts
            @imap_errors();
            @imap_alerts();
        }
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