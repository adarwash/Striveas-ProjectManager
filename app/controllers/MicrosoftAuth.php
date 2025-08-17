<?php

class MicrosoftAuth extends Controller
{
    private $graphService;
    private $settingModel;
    
    public function __construct()
    {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('users/login');
            return;
        }
        
        // Check if user is admin
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            flash('access_denied', 'You do not have permission to access this area', 'alert alert-danger');
            redirect('dashboard');
            return;
        }
        
        $this->settingModel = $this->model('Setting');
    }
    
    /**
     * Initiate Microsoft OAuth2 flow
     */
    public function connect()
    {
        // Debug logging
        error_log('MicrosoftAuth::connect() called');
        error_log('Session: ' . print_r($_SESSION, true));
        
        // Initialize MicrosoftGraphService if needed
        require_once APPROOT . '/app/services/MicrosoftGraphService.php';
        
        // Generate a random state for CSRF protection
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;
        
        // Get tenant ID from settings or use 'common' for multi-tenant
        $tenantId = $this->settingModel->get('graph_tenant_id') ?: 'common';
        $clientId = $this->settingModel->get('graph_client_id');
        
        if (!$clientId) {
            flash('settings_error', 'Please configure your Microsoft App ID first', 'alert alert-danger');
            redirect('admin/emailSettings');
            return;
        }
        
        // Build redirect URI
        $redirectUri = URLROOT . '/microsoftAuth/callback';
        
        // Debug: Log the redirect URI being used
        error_log('Redirect URI being used: ' . $redirectUri);
        
        // Build authorization URL
        $params = [
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'response_mode' => 'query',
            'scope' => 'offline_access Mail.Read Mail.Send Mail.ReadWrite User.Read',
            'state' => $state,
            'prompt' => 'consent' // Force consent to ensure we get all permissions
        ];
        
        $authUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/authorize?" . http_build_query($params);
        
        // Debug: Log the full auth URL
        error_log('Full auth URL: ' . $authUrl);
        
        // Redirect to Microsoft login
        header('Location: ' . $authUrl);
        exit;
    }
    
    /**
     * Handle OAuth2 callback from Microsoft
     */
    public function callback()
    {
        // Verify state for CSRF protection
        if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
            flash('settings_error', 'Invalid state parameter. Please try again.', 'alert alert-danger');
            redirect('admin/emailSettings');
            return;
        }
        
        // Clear the state
        unset($_SESSION['oauth_state']);
        
        // Check for errors
        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            $errorDesc = isset($_GET['error_description']) ? $_GET['error_description'] : 'Unknown error';
            flash('settings_error', "Microsoft authentication failed: {$errorDesc}", 'alert alert-danger');
            redirect('admin/emailSettings');
            return;
        }
        
        // Get authorization code
        if (!isset($_GET['code'])) {
            flash('settings_error', 'No authorization code received', 'alert alert-danger');
            redirect('admin/emailSettings');
            return;
        }
        
        $code = $_GET['code'];
        
        // Exchange code for tokens
        $tokens = $this->exchangeCodeForTokens($code);
        
        if ($tokens) {
            // Store tokens securely
            $this->storeTokens($tokens);
            
            // Get user info to confirm connection
            $userInfo = $this->getUserInfo($tokens['access_token']);
            
            if ($userInfo) {
                $connectedEmail = isset($userInfo['mail']) ? $userInfo['mail'] : $userInfo['userPrincipalName'];
                $this->settingModel->set('graph_connected_email', $connectedEmail);
                $this->settingModel->set('graph_connected_name', $userInfo['displayName']);
                $this->settingModel->set('graph_connection_status', 'connected');
                $this->settingModel->set('graph_connected_at', date('Y-m-d H:i:s'));
                
                $email = isset($userInfo['mail']) ? $userInfo['mail'] : $userInfo['userPrincipalName'];
                flash('settings_success', "Successfully connected to Microsoft 365 as {$userInfo['displayName']} ({$email})", 'alert alert-success');
            } else {
                flash('settings_success', 'Successfully connected to Microsoft 365', 'alert alert-success');
            }
        } else {
            flash('settings_error', 'Failed to obtain access tokens. Please try again.', 'alert alert-danger');
        }
        
        redirect('admin/emailSettings');
    }
    
    /**
     * Disconnect Microsoft account
     */
    public function disconnect()
    {
        // Clear all stored tokens and connection info
        $this->settingModel->set('graph_access_token', '');
        $this->settingModel->set('graph_refresh_token', '');
        $this->settingModel->set('graph_token_expires', '');
        $this->settingModel->set('graph_connected_email', '');
        $this->settingModel->set('graph_connected_name', '');
        $this->settingModel->set('graph_connection_status', 'disconnected');
        $this->settingModel->set('graph_disconnected_at', date('Y-m-d H:i:s'));
        
        flash('settings_success', 'Successfully disconnected from Microsoft 365', 'alert alert-success');
        redirect('admin/emailSettings');
    }
    
    /**
     * Exchange authorization code for tokens
     */
    private function exchangeCodeForTokens($code)
    {
        $tenantId = $this->settingModel->get('graph_tenant_id') ?: 'common';
        $clientId = $this->settingModel->get('graph_client_id');
        $clientSecret = $this->settingModel->get('graph_client_secret');
        
        if (!$clientId || !$clientSecret) {
            error_log('MicrosoftAuth: Missing client ID or secret');
            return false;
        }
        
        $tokenUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";
        
        $postData = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'redirect_uri' => URLROOT . '/microsoftAuth/callback',
            'grant_type' => 'authorization_code'
        ];
        
        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            error_log('MicrosoftAuth: Token exchange failed - HTTP ' . $httpCode);
            error_log('Response: ' . $response);
            return false;
        }
        
        $tokens = json_decode($response, true);
        
        if (!isset($tokens['access_token'])) {
            error_log('MicrosoftAuth: No access token in response');
            return false;
        }
        
        return $tokens;
    }
    
    /**
     * Store tokens securely in database
     */
    private function storeTokens($tokens)
    {
        // Encrypt tokens before storing (in production, use proper encryption)
        $this->settingModel->set('graph_access_token', base64_encode($tokens['access_token']));
        $refreshToken = isset($tokens['refresh_token']) ? $tokens['refresh_token'] : '';
        $this->settingModel->set('graph_refresh_token', base64_encode($refreshToken));
        
        // Calculate and store expiration time
        $expiresIn = isset($tokens['expires_in']) ? $tokens['expires_in'] : 3600;
        $expiresAt = time() + $expiresIn;
        $this->settingModel->set('graph_token_expires', $expiresAt);
        
        // Store token type
        $tokenType = isset($tokens['token_type']) ? $tokens['token_type'] : 'Bearer';
        $this->settingModel->set('graph_token_type', $tokenType);
    }
    
    /**
     * Get user information from Microsoft Graph
     */
    private function getUserInfo($accessToken)
    {
        $url = 'https://graph.microsoft.com/v1.0/me';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            error_log('MicrosoftAuth: Failed to get user info - HTTP ' . $httpCode);
            return null;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Refresh access token using refresh token
     */
    public function refreshToken()
    {
        $refreshToken = base64_decode($this->settingModel->get('graph_refresh_token'));
        
        if (!$refreshToken) {
            return false;
        }
        
        $tenantId = $this->settingModel->get('graph_tenant_id') ?: 'common';
        $clientId = $this->settingModel->get('graph_client_id');
        $clientSecret = $this->settingModel->get('graph_client_secret');
        
        $tokenUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";
        
        $postData = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
            'scope' => 'offline_access Mail.Read Mail.Send Mail.ReadWrite User.Read'
        ];
        
        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $tokens = json_decode($response, true);
            $this->storeTokens($tokens);
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if tokens need refreshing
     */
    public function checkTokenExpiry()
    {
        $expiresAt = $this->settingModel->get('graph_token_expires');
        
        if (!$expiresAt) {
            return false;
        }
        
        // Refresh if token expires in less than 5 minutes
        if ($expiresAt - time() < 300) {
            return $this->refreshToken();
        }
        
        return true;
    }
}
