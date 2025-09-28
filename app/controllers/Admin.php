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