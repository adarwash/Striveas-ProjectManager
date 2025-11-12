<?php

/**
 * Admin Controller for System Administration
 */
class Admin extends Controller {
    
    public function __construct() {
        // Check if user is logged in and has admin permissions
        if (!isLoggedIn()) {
            redirect('users/login');
        }
        
        if (!hasPermission('admin.access')) {
            flash('error', 'You do not have permission to access admin functions.');
            redirect('dashboard');
        }
    }
    
    /**
     * Admin dashboard/index page
     */
    public function index() {
        $viewData = [
            'title' => 'Admin Dashboard',
            'description' => 'System administration and configuration'
        ];
        
        $this->view('admin/index', $viewData);
    }
    
    /**
     * User Management
     */
    public function users() {
        if (!hasPermission('users.manage')) {
            flash('error', 'You do not have permission to manage users.');
            redirect('admin');
        }
        
        $userModel = $this->model('User');
        $roleModel = $this->model('Role');
        
        // Handle user actions (create, update, delete, etc.)
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Handle POST actions here if needed
            // For now, just redirect back to the page
            redirect('admin/users');
        }
        
        // Get all users with role information
        $users = $userModel->getAllUsersWithRoles();
        
        // If the enhanced users method doesn't exist, fall back to regular method
        if (empty($users)) {
            $users = $userModel->getAllUsers();
        }
        
        // Get all available roles
        $available_roles = $roleModel->getAllRoles();
        
        // If no roles exist, create default ones
        if (empty($available_roles)) {
            $roleModel->createDefaultRolesIfNotExist();
            $available_roles = $roleModel->getAllRoles();
        }
        
        $viewData = [
            'title' => 'User Management',
            'users' => $users,
            'available_roles' => $available_roles
        ];
        
        $this->view('admin/users', $viewData);
    }

    /**
     * Create a new user (POST)
     */
    public function add_user() {
        if (!hasPermission('users.manage')) {
            flash('admin_message', 'Permission denied.', 'alert-danger');
            redirect('admin/users');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/users');
        }
        $userModel = $this->model('User');

        // Sanitize inputs
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $role = trim((string)($_POST['role'] ?? 'user'));

        if ($name === '' || $email === '' || $password === '' || $role === '') {
            flash('admin_message', 'Please fill all required fields.', 'alert-danger');
            redirect('admin/users');
        }

        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $created = $userModel->register([
                'name' => $name,
                'email' => $email,
                'password' => $hashed,
                'role' => $role
            ]);

            if ($created) {
                flash('admin_message', 'User created successfully.');
            } else {
                flash('admin_message', 'Failed to create user.', 'alert-danger');
            }
        } catch (Exception $e) {
            flash('admin_message', 'Error creating user: ' . $e->getMessage(), 'alert-danger');
        }

        redirect('admin/users');
    }

    /**
     * Update existing user (POST)
     */
    public function update_user() {
        if (!hasPermission('users.manage')) {
            flash('admin_message', 'Permission denied.', 'alert-danger');
            redirect('admin/users');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/users');
        }
        $userModel = $this->model('User');

        $id = (int)($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $role = trim((string)($_POST['role'] ?? ''));

        if ($id <= 0 || $name === '' || $email === '' || $role === '') {
            flash('admin_message', 'Missing required fields.', 'alert-danger');
            redirect('admin/users');
        }

        try {
            $ok = true;
            if ($password !== '') {
                $ok = $userModel->updateUserWithPassword([
                    'id' => $id,
                    'name' => $name,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => $role
                ]);
            } else {
                $ok = $userModel->updateUser([
                    'id' => $id,
                    'name' => $name,
                    'email' => $email,
                    'role' => $role
                ]);
            }

            if ($ok) {
                flash('admin_message', 'User updated successfully.');
            } else {
                flash('admin_message', 'Failed to update user.', 'alert-danger');
            }
        } catch (Exception $e) {
            flash('admin_message', 'Error updating user: ' . $e->getMessage(), 'alert-danger');
        }

        redirect('admin/users');
    }

    /**
     * Delete user (POST)
     */
    public function delete_user() {
        if (!hasPermission('users.manage')) {
            flash('admin_message', 'Permission denied.', 'alert-danger');
            redirect('admin/users');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/users');
        }
        $userModel = $this->model('User');
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('admin_message', 'Invalid user id.', 'alert-danger');
            redirect('admin/users');
        }
        try {
            $deleted = $userModel->deleteUser($id);
            if ($deleted) {
                flash('admin_message', 'User deleted successfully.');
            } else {
                flash('admin_message', 'Failed to delete user.', 'alert-danger');
            }
        } catch (Exception $e) {
            flash('admin_message', 'Error deleting user: ' . $e->getMessage(), 'alert-danger');
        }
        redirect('admin/users');
    }
    
    /**
     * System Settings Management
     */
    public function settings() {
        if (!hasPermission('admin.system_settings')) {
            flash('error', 'You do not have permission to access system settings.');
            redirect('admin');
        }
        
        $settingModel = $this->model('Setting');
        
        // Handle settings update
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process settings update
            try {
                // Prepare settings array (exclude submit button and other non-setting fields)
                $settings = $_POST;
                unset($settings['submit']);
                
                // Use the updateSystemSettings method which handles validation
                $success = $settingModel->updateSystemSettings($settings);

                // Also handle currency settings explicitly (they are stored as a JSON blob under the 'currency' key)
                $hasCurrencyPayload = (
                    isset($_POST['currency_code']) ||
                    isset($_POST['currency_symbol']) ||
                    isset($_POST['currency_position']) ||
                    isset($_POST['decimals']) ||
                    isset($_POST['thousands_separator']) ||
                    isset($_POST['decimal_separator'])
                );

                if ($hasCurrencyPayload) {
                    $currencyPayload = [
                        'code' => sanitize_input($_POST['currency_code'] ?? 'USD'),
                        'symbol' => sanitize_input($_POST['currency_symbol'] ?? '$'),
                        'position' => sanitize_input($_POST['currency_position'] ?? 'before'),
                        'decimals' => (int)($_POST['decimals'] ?? 2),
                        'thousands_separator' => sanitize_input($_POST['thousands_separator'] ?? ','),
                        'decimal_separator' => sanitize_input($_POST['decimal_separator'] ?? '.')
                    ];
                    $currencySaved = $settingModel->setCurrency($currencyPayload);
                    $success = $success && $currencySaved;
                }
                
                if ($success) {
                    flash('settings_success', 'Settings updated successfully.');
                } else {
                    flash('settings_error', 'Error updating some settings. Please check the logs.');
                }
            } catch (Exception $e) {
                flash('settings_error', 'Error updating settings: ' . $e->getMessage());
            }
            
            redirect('admin/settings');
        }
        
        // Get current settings
        $systemSettings = $settingModel->getSystemSettings();
        $currency = $settingModel->getCurrency();
        
        $viewData = [
            'title' => 'System Settings',
            'systemSettings' => $systemSettings,
            'currency' => $currency,
            // Provide a nested $data array for views that expect $data[...] structure
            'data' => [
                'systemSettings' => $systemSettings,
                'currency' => $currency
            ]
        ];
        
        $this->view('admin/settings_clean', $viewData);
    }
    
    /**
     * Test OAuth Configuration
     */
    public function testOAuth() {
        header('Content-Type: application/json');
        
        try {
            $settingsModel = $this->model('Settings');
            $result = $settingsModel->testOAuthConfig();
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Test SMTP Configuration
     */
    public function testSmtp() {
        header('Content-Type: application/json');
        
        try {
            $settingsModel = $this->model('Settings');
            $result = $settingsModel->testSmtpConfig();
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Email Settings OAuth Configuration
     */
    public function emailSettings() {
        if (!hasPermission('admin.system_settings')) {
            flash('error', 'You do not have permission to access email settings.');
            redirect('admin');
        }
        
        $settingModel = $this->model('Setting');
        
        // Get current settings
        $settings = $settingModel->getSystemSettings();
        
        // Check OAuth connection status
        $connectionStatus = $settingModel->get('graph_connection_status');
        $isConnected = ($connectionStatus === 'connected');
        
        $connectedEmail = $settingModel->get('graph_connected_email', '');
        $connectedName = $settingModel->get('graph_connected_name', '');
        $connectedAt = $settingModel->get('graph_connected_at', '');
        
        // Prepare settings data for the view (including graph settings)
        $allSettings = $settings;
        $allSettings['graph_connection_status'] = $connectionStatus;
        $allSettings['graph_connected_email'] = $connectedEmail;
        $allSettings['graph_connected_name'] = $connectedName;
        $allSettings['graph_connected_at'] = $connectedAt;
        $allSettings['graph_client_id'] = $settingModel->get('graph_client_id', '');
        $allSettings['graph_client_secret'] = $settingModel->get('graph_client_secret', '');
        
        // Include additional processing settings not returned by getSystemSettings()
        $allSettings['email_folder'] = $settingModel->get('email_folder', 'Inbox');
        $allSettings['check_interval'] = $settingModel->get('check_interval', 5);
        $allSettings['auto_reply'] = $settingModel->get('auto_reply', '1');
        $allSettings['default_priority'] = $settingModel->get('default_priority', 'Medium');
        $allSettings['allowed_domains'] = $settingModel->get('allowed_domains', '');

        $viewData = [
            'title' => 'Email Settings',
            'settings' => $allSettings,
            'isConnected' => $isConnected,
            'connectedEmail' => $connectedEmail,
            'connectedName' => $connectedName,
            'connectedAt' => $connectedAt,
            'data' => ['settings' => $allSettings] // For compatibility with the view
        ];
        
        $this->view('admin/email_settings_oauth', $viewData);
    }

    /**
     * Save Microsoft 365/SMTP email settings
     */
    public function saveEmailSettings() {
        if (!hasPermission('admin.system_settings')) {
            flash('settings_error', 'You do not have permission to update email settings.');
            redirect('admin/emailSettings');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/emailSettings');
        }

        $settingModel = $this->model('Setting');

        try {
            // Sanitize inputs
            $graphClientId = trim((string)($_POST['graph_client_id'] ?? ''));
            $graphTenantId = trim((string)($_POST['graph_tenant_id'] ?? ''));
            $graphClientSecret = trim((string)($_POST['graph_client_secret'] ?? ''));
            $graphSupportEmail = trim((string)($_POST['graph_support_email'] ?? ''));

            // Optional legacy SMTP fields
            $smtpHost = trim((string)($_POST['smtp_host'] ?? ''));
            $smtpPort = trim((string)($_POST['smtp_port'] ?? ''));
            $smtpUsername = trim((string)($_POST['smtp_username'] ?? ''));
            $smtpPassword = trim((string)($_POST['smtp_password'] ?? ''));
            $smtpEncryption = trim((string)($_POST['smtp_encryption'] ?? ''));

            // Feature flags (checkboxes)
            $graphEnabled = isset($_POST['graph_enabled']);
            $graphAutoProcess = isset($_POST['graph_auto_process']);

            $updatedAny = false;

            // Save Graph settings if provided
            if ($graphClientId !== '') {
                $updatedAny = $settingModel->set('graph_client_id', $graphClientId) || $updatedAny;
            }
            if ($graphTenantId !== '') {
                $updatedAny = $settingModel->set('graph_tenant_id', $graphTenantId) || $updatedAny;
            }
            // Only update client secret if a new value is posted (avoid overwriting with empty)
            if ($graphClientSecret !== '') {
                $updatedAny = $settingModel->set('graph_client_secret', $graphClientSecret) || $updatedAny;
            }
            if ($graphSupportEmail !== '') {
                $updatedAny = $settingModel->set('graph_support_email', $graphSupportEmail) || $updatedAny;
            }

            // Save Graph feature toggles
            $updatedAny = $settingModel->set('graph_enabled', $graphEnabled ? '1' : '0') || $updatedAny;
            $updatedAny = $settingModel->set('graph_auto_process', $graphAutoProcess ? '1' : '0') || $updatedAny;

            // Save SMTP settings when present (legacy tab)
            if ($smtpHost !== '') { $updatedAny = $settingModel->set('smtp_host', $smtpHost) || $updatedAny; }
            if ($smtpPort !== '') { $updatedAny = $settingModel->set('smtp_port', $smtpPort) || $updatedAny; }
            if ($smtpUsername !== '') { $updatedAny = $settingModel->set('smtp_username', $smtpUsername) || $updatedAny; }
            if ($smtpPassword !== '') { $updatedAny = $settingModel->set('smtp_password', $smtpPassword) || $updatedAny; }
            if ($smtpEncryption !== '') { $updatedAny = $settingModel->set('smtp_encryption', $smtpEncryption) || $updatedAny; }

            if ($updatedAny) {
                flash('settings_success', 'Email settings updated successfully.');
            } else {
                flash('settings_success', 'No changes detected.');
            }
        } catch (Exception $e) {
            error_log('saveEmailSettings error: ' . $e->getMessage());
            flash('settings_error', 'Failed to save email settings: ' . $e->getMessage());
        }

        redirect('admin/emailSettings');
    }

    /**
     * Save email processing preferences
     */
    public function saveEmailProcessingSettings() {
        if (!hasPermission('admin.system_settings')) {
            flash('settings_error', 'You do not have permission to update processing settings.');
            redirect('admin/emailSettings');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/emailSettings');
        }

        $settingModel = $this->model('Setting');

        try {
            $emailFolder = trim((string)($_POST['email_folder'] ?? 'Inbox'));
            $checkInterval = (int)($_POST['check_interval'] ?? 5);
            $autoReply = (string)($_POST['auto_reply'] ?? '1');
            $defaultPriority = trim((string)($_POST['default_priority'] ?? 'Medium'));
            $allowedDomains = (string)($_POST['allowed_domains'] ?? '');

            $settingModel->set('email_folder', $emailFolder);
            $settingModel->set('check_interval', $checkInterval);
            $settingModel->set('auto_reply', $autoReply);
            $settingModel->set('default_priority', $defaultPriority);
            $settingModel->set('allowed_domains', $allowedDomains);

            flash('settings_success', 'Email processing settings updated successfully.');
        } catch (Exception $e) {
            error_log('saveEmailProcessingSettings error: ' . $e->getMessage());
            flash('settings_error', 'Failed to save processing settings: ' . $e->getMessage());
        }

        redirect('admin/emailSettings');
    }

    /**
     * Lightweight Graph connection test
     * Uses stored settings and verifies Microsoft endpoints are reachable
     */
    public function testGraphConnection() {
        header('Content-Type: application/json');

        if (!hasPermission('admin.system_settings')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            return;
        }

        try {
            $settingModel = $this->model('Setting');
            $tenantId = $settingModel->get('graph_tenant_id') ?: 'common';
            $clientId = $settingModel->get('graph_client_id');
            $clientSecret = $settingModel->get('graph_client_secret');

            if (empty($clientId)) {
                echo json_encode(['success' => false, 'error' => 'Client ID is not configured']);
                return;
            }

            // Check Microsoft OpenID configuration endpoint reachability
            $url = "https://login.microsoftonline.com/{$tenantId}/.well-known/openid-configuration";
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'ProjectTracker/1.0'
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($curlErr) {
                echo json_encode(['success' => false, 'error' => 'Network error: ' . $curlErr]);
                return;
            }

            if ($httpCode !== 200) {
                echo json_encode(['success' => false, 'error' => 'HTTP error ' . $httpCode]);
                return;
            }

            // Basic validation passed
            echo json_encode([
                'success' => true,
                'message' => 'Microsoft endpoints reachable',
                'tenant' => $tenantId,
                'has_client_secret' => !empty($clientSecret)
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * SLA Settings Management
     */
    public function slaSettings() {
        $db = new EasySQL(DB1);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Update SLA settings
            $priorities = $_POST['priorities'] ?? [];
            
            foreach ($priorities as $priorityId => $settings) {
                $db->update(
                    "UPDATE TicketPriorities SET 
                     response_time_hours = :response_hours,
                     resolution_time_hours = :resolution_hours
                     WHERE id = :id",
                    [
                        'response_hours' => (int)$settings['response_hours'],
                        'resolution_hours' => (int)$settings['resolution_hours'],
                        'id' => $priorityId
                    ]
                );
            }
            
            flash('success', 'SLA settings updated successfully.');
            redirect('admin/slaSettings');
        }
        
        // Get current priorities with SLA settings
        $priorities = $db->select(
            "SELECT id, name, display_name, response_time_hours, resolution_time_hours, sort_order 
             FROM TicketPriorities 
             WHERE is_active = 1 
             ORDER BY sort_order"
        );
        
        $viewData = [
            'priorities' => $priorities
        ];
        
        $this->view('admin/sla_settings', $viewData);
    }
    
    /**
     * Test Azure AD connection
     */
    public function testAzureConnection() {
        // Ensure only AJAX requests
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            return;
        }
        
        // Check permissions
        if (!hasPermission('admin.system_settings')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            return;
        }
        
        // Get posted data
        $tenantId = $_POST['azure_tenant_id'] ?? '';
        $clientId = $_POST['azure_client_id'] ?? '';
        $clientSecret = $_POST['azure_client_secret'] ?? '';
        
        if (empty($tenantId) || empty($clientId) || empty($clientSecret)) {
            echo json_encode(['success' => false, 'error' => 'Missing required Azure AD configuration']);
            return;
        }
        
        try {
            // Test the configuration by attempting to get an authorization URL
            $redirectUri = URLROOT . '/customer/auth/callback';
            $state = 'test_' . bin2hex(random_bytes(8));
            
            $params = [
                'client_id' => $clientId,
                'response_type' => 'code',
                'redirect_uri' => $redirectUri,
                'response_mode' => 'query',
                'scope' => 'openid profile email User.Read',
                'state' => $state
            ];
            
            $authUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/authorize?" . http_build_query($params);
            
            // Test if we can reach the Microsoft endpoint
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://login.microsoftonline.com/{$tenantId}/.well-known/openid-configuration",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'ProjectTracker/1.0'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                throw new Exception('Network error: ' . $curlError);
            }
            
            if ($httpCode !== 200) {
                throw new Exception('HTTP error: ' . $httpCode . ' - Invalid tenant ID or network issue');
            }
            
            $config = json_decode($response, true);
            if (!$config || !isset($config['authorization_endpoint'])) {
                throw new Exception('Invalid response from Microsoft - tenant ID may be incorrect');
            }
            
            // If we get here, the configuration is valid
            echo json_encode([
                'success' => true, 
                'message' => 'Azure AD configuration is valid and reachable',
                'tenant_id' => $tenantId,
                'authorization_endpoint' => $config['authorization_endpoint']
            ]);
            
        } catch (Exception $e) {
            error_log('Azure connection test error: ' . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'error' => $e->getMessage()
            ]);
        }
    }
} 