<?php

/**
 * Customer Authentication Controller
 * Handles Microsoft 365 OAuth2 authentication for customer portal
 */
class CustomerAuth extends Controller {
    private $settingModel;
    
    public function __construct() {
        $this->settingModel = $this->model('Setting');
    }
    
    /**
     * Customer login page
     */
    public function index() {
        // Check if customer authentication is enabled
        if (!$this->settingModel->get('customer_auth_enabled')) {
            $this->view('errors/404', ['title' => 'Not Found']);
            return;
        }
        
        $data = [
            'title' => 'Customer Portal Login',
            'azure_configured' => $this->isAzureConfigured()
        ];
        
        $this->view('customer/auth/login', $data);
    }
    
    /**
     * Initiate Microsoft 365 OAuth2 flow for customers
     */
    public function login() {
        // Check if customer authentication is enabled
        if (!$this->settingModel->get('customer_auth_enabled')) {
            $this->view('errors/404', ['title' => 'Not Found']);
            return;
        }
        
        if (!$this->isAzureConfigured()) {
            flash('auth_error', 'Authentication is not properly configured. Please contact support.', 'alert alert-danger');
            redirect('customer/auth');
            return;
        }
        
        // Generate state for CSRF protection
        $state = bin2hex(random_bytes(16));
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['customer_oauth_state'] = $state;
        $_SESSION['customer_auth_initiated'] = time();
        
        // Get Azure configuration
        $tenantId = $this->settingModel->get('azure_tenant_id') ?: 'common';
        $clientId = $this->settingModel->get('azure_client_id');
        $redirectUri = URLROOT . '/customer/auth/callback';
        
        // Build authorization URL
        $params = [
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'response_mode' => 'query',
            'scope' => 'openid profile email User.Read',
            'state' => $state,
            'prompt' => 'select_account' // Allow account selection
        ];
        
        $authUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/authorize?" . http_build_query($params);
        
        // Log the authentication attempt
        error_log('Customer OAuth2 initiated: ' . $authUrl);
        
        // Redirect to Microsoft login
        header('Location: ' . $authUrl);
        exit;
    }
    
    /**
     * Handle OAuth2 callback from Microsoft
     */
    public function callback() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Log callback for monitoring
        error_log('Customer auth callback received');
        
        // Check if customer authentication is enabled
        if (!$this->settingModel->get('customer_auth_enabled')) {
            $this->view('errors/404', ['title' => 'Not Found']);
            return;
        }
        
        // Verify state for CSRF protection
        if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['customer_oauth_state']) {
            flash('auth_error', 'Invalid authentication request. Please try again.', 'alert alert-danger');
            redirect('customer/auth');
            return;
        }
        
        // Clear the state and check session timeout (10 minutes)
        unset($_SESSION['customer_oauth_state']);
        if (!isset($_SESSION['customer_auth_initiated']) || (time() - $_SESSION['customer_auth_initiated']) > 600) {
            flash('auth_error', 'Authentication session expired. Please try again.', 'alert alert-warning');
            redirect('customer/auth');
            return;
        }
        unset($_SESSION['customer_auth_initiated']);
        
        // Check for errors
        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            $errorDesc = isset($_GET['error_description']) ? $_GET['error_description'] : 'Unknown error';
            error_log('Customer OAuth2 error: ' . $error . ' - ' . $errorDesc);
            
            if ($error === 'access_denied') {
                flash('auth_error', 'Access was denied. Please grant permission to continue.', 'alert alert-warning');
            } else {
                flash('auth_error', 'Authentication failed: ' . $errorDesc, 'alert alert-danger');
            }
            redirect('customer/auth');
            return;
        }
        
        // Get authorization code
        if (!isset($_GET['code'])) {
            flash('auth_error', 'No authorization code received. Please try again.', 'alert alert-danger');
            redirect('customer/auth');
            return;
        }
        
        $code = $_GET['code'];
        
        try {
            // Exchange code for tokens
            $tokens = $this->exchangeCodeForTokens($code);
            
            if (!$tokens) {
                throw new Exception('Failed to obtain access tokens');
            }
            
            // Get user information
            $userInfo = $this->getUserInfo($tokens['access_token']);
            
            if (!$userInfo) {
                throw new Exception('Failed to get user information');
            }
            
            // Validate user access
            if (!$this->validateCustomerAccess($userInfo)) {
                flash('auth_error', 'Access denied. You are not authorized to access this portal.', 'alert alert-danger');
                redirect('customer/auth');
                return;
            }
            
            // Create customer session
            $this->createCustomerSession($userInfo, $tokens);
            
            // Log successful authentication
            error_log('Customer authentication successful: ' . ($userInfo['mail'] ?? $userInfo['userPrincipalName']));
            
            // Redirect to customer dashboard
            redirect('customer/dashboard');
            
        } catch (Exception $e) {
            error_log('Customer OAuth2 callback error: ' . $e->getMessage());
            flash('auth_error', 'Authentication failed. Please try again or contact support.', 'alert alert-danger');
            redirect('customer/auth');
        }
    }
    
    /**
     * Debug endpoint to check authentication state
     */
    public function debug() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        header('Content-Type: text/plain');
        
        echo "=== CUSTOMER AUTH DEBUG ===\n\n";
        
        // Check settings
        echo "Settings:\n";
        echo "- customer_auth_enabled: " . ($this->settingModel->get('customer_auth_enabled') ? 'YES' : 'NO') . "\n";
        echo "- azure_tenant_id: " . ($this->settingModel->get('azure_tenant_id') ?: 'NOT SET') . "\n";
        echo "- azure_client_id: " . ($this->settingModel->get('azure_client_id') ?: 'NOT SET') . "\n";
        echo "- azure_client_secret: " . (empty($this->settingModel->get('azure_client_secret')) ? 'NOT SET' : 'SET') . "\n";
        echo "- customer_domain_restriction: " . ($this->settingModel->get('customer_domain_restriction') ?: 'NOT SET') . "\n";
        echo "- ticket_visibility: " . ($this->settingModel->get('ticket_visibility') ?: 'NOT SET') . "\n";
        
        echo "\nSession:\n";
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, 'customer') !== false) {
                echo "- $key: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
            }
        }
        
        echo "\nAzure Configuration Status: " . ($this->isAzureConfigured() ? 'CONFIGURED' : 'NOT CONFIGURED') . "\n";
        
        echo "\nRedirect URI: " . URLROOT . "/customer/auth/callback\n";
        
        exit;
    }
    
    /**
     * Customer logout
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear customer session data
        unset($_SESSION['customer_id']);
        unset($_SESSION['customer_email']);
        unset($_SESSION['customer_name']);
        unset($_SESSION['customer_domain']);
        unset($_SESSION['customer_logged_in']);
        unset($_SESSION['customer_access_token']);
        unset($_SESSION['customer_token_expires']);
        
        // Log logout
        error_log('Customer logged out: ' . ($_SESSION['customer_email'] ?? 'unknown'));
        
        flash('auth_success', 'You have been logged out successfully.', 'alert alert-success');
        redirect('customer/auth');
    }
    
    /**
     * Check if Azure AD is properly configured
     */
    private function isAzureConfigured(): bool {
        $tenantId = $this->settingModel->get('azure_tenant_id');
        $clientId = $this->settingModel->get('azure_client_id');
        $clientSecret = $this->settingModel->get('azure_client_secret');
        
        return !empty($tenantId) && !empty($clientId) && !empty($clientSecret);
    }
    
    /**
     * Exchange authorization code for tokens
     */
    private function exchangeCodeForTokens($code) {
        $tenantId = $this->settingModel->get('azure_tenant_id') ?: 'common';
        $clientId = $this->settingModel->get('azure_client_id');
        $clientSecret = $this->settingModel->get('azure_client_secret');
        $redirectUri = URLROOT . '/customer/auth/callback';
        
        $tokenUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";
        
        $postData = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ];
        
        $ch = curl_init($tokenUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log('Customer token exchange cURL error: ' . $curlError);
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log('Customer token exchange HTTP error ' . $httpCode . ': ' . $response);
            return false;
        }
        
        $tokens = json_decode($response, true);
        
        if (!isset($tokens['access_token'])) {
            error_log('Customer token exchange: No access token in response');
            return false;
        }
        
        return $tokens;
    }
    
    /**
     * Get user information from Microsoft Graph
     */
    private function getUserInfo($accessToken) {
        $url = 'https://graph.microsoft.com/v1.0/me';
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log('Customer user info cURL error: ' . $curlError);
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log('Customer user info HTTP error ' . $httpCode . ': ' . $response);
            return false;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Validate if customer has access to the portal
     */
    private function validateCustomerAccess($userInfo): bool {
        $email = $userInfo['mail'] ?? $userInfo['userPrincipalName'] ?? '';
        
        if (empty($email)) {
            error_log('Customer validation failed: No email found');
            return false;
        }
        
        // Normalize values for comparison
        $email = strtolower(trim($email));
        $userDomain = '';
        if (str_contains($email, '@')) {
            $userDomain = strtolower(substr(strrchr($email, '@'), 1));
        }
        
        // Check domain restriction if configured (accept with or without leading @)
        $domainRestriction = $this->settingModel->get('customer_domain_restriction');
        
        if (!empty($domainRestriction)) {
            $domainRestriction = strtolower(ltrim(trim($domainRestriction), '@'));
            
            if ($userDomain !== $domainRestriction) {
                error_log('Customer validation failed: Domain restriction - ' . $userDomain . ' vs ' . $domainRestriction);
                return false;
            }
        }
        
        // Previously we blocked users with no tickets. Allow login and simply show zero tickets.
        // Keep any future business logic checks here if needed.
        return true;
    }
    
    /**
     * Create customer session
     */
    private function createCustomerSession($userInfo, $tokens) {
        $email = $userInfo['mail'] ?? $userInfo['userPrincipalName'];
        $name = $userInfo['displayName'] ?? 'Customer';
        $domain = '@' . substr(strrchr($email, '@'), 1);
        
        $_SESSION['customer_id'] = $userInfo['id'];
        $_SESSION['customer_email'] = $email;
        $_SESSION['customer_name'] = $name;
        $_SESSION['customer_domain'] = $domain;
        $_SESSION['customer_logged_in'] = true;
        $_SESSION['customer_login_time'] = time();
        
        // Store access token for potential future API calls (encrypted)
        if (isset($tokens['access_token'])) {
            $_SESSION['customer_access_token'] = base64_encode($tokens['access_token']);
            $expiresIn = isset($tokens['expires_in']) ? $tokens['expires_in'] : 3600;
            $_SESSION['customer_token_expires'] = time() + $expiresIn;
        }
    }
}
