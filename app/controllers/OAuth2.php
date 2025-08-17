<?php

/**
 * OAuth2 Controller
 * Handles OAuth2 authentication flow for email providers
 */
class OAuth2 extends Controller {
    
    private $oauth2Service;
    private $settingsModel;
    
    public function __construct() {
        parent::__construct();
        
        // Check if user is logged in and is admin
        if (!isLoggedIn() || !isAdmin()) {
            redirect('auth/login');
        }
        
        require_once APPPATH . '/services/OAuth2Service.php';
        require_once APPPATH . '/models/Setting.php';
        
        $this->oauth2Service = new OAuth2Service();
        $this->settingsModel = new Setting();
        
        // Create OAuth2 tokens table if it doesn't exist
        $this->oauth2Service->createTokensTable();
    }
    
    /**
     * Start OAuth2 authorization flow
     */
    public function authorize() {
        try {
            // Get current settings
            $settings = $this->settingsModel->getSystemSettings();
            
            $provider = $_GET['provider'] ?? $settings['oauth2_provider'] ?? 'microsoft';
            $clientId = $settings['oauth2_client_id'] ?? '';
            $redirectUri = $settings['oauth2_redirect_uri'] ?? '';
            
            // Validate required settings
            if (empty($clientId) || empty($redirectUri)) {
                flash('settings_error', 'OAuth2 Client ID and Redirect URI must be configured first', 'alert alert-danger');
                redirect('admin/settings');
                return;
            }
            
            // Generate authorization URL
            $authUrl = $this->oauth2Service->getAuthorizationUrl($provider, $clientId, $redirectUri);
            
            // Redirect to provider's authorization server
            header("Location: $authUrl");
            exit;
            
        } catch (Exception $e) {
            error_log('OAuth2 Authorization Error: ' . $e->getMessage());
            flash('settings_error', 'OAuth2 authorization failed: ' . $e->getMessage(), 'alert alert-danger');
            redirect('admin/settings');
        }
    }
    
    /**
     * Handle OAuth2 callback
     */
    public function callback() {
        try {
            // Check for error in callback
            if (isset($_GET['error'])) {
                $error = $_GET['error'];
                $errorDescription = $_GET['error_description'] ?? '';
                throw new Exception("OAuth2 Error: $error - $errorDescription");
            }
            
            // Validate state parameter
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $receivedState = $_GET['state'] ?? '';
            $sessionState = $_SESSION['oauth2_state'] ?? '';
            
            if (empty($receivedState) || $receivedState !== $sessionState) {
                throw new Exception('Invalid state parameter. Possible CSRF attack.');
            }
            
            // Get authorization code
            $code = $_GET['code'] ?? '';
            if (empty($code)) {
                throw new Exception('No authorization code received');
            }
            
            // Get provider from session
            $provider = $_SESSION['oauth2_provider'] ?? 'microsoft';
            
            // Get current settings
            $settings = $this->settingsModel->getSystemSettings();
            
            $clientId = $settings['oauth2_client_id'] ?? '';
            $clientSecret = $settings['oauth2_client_secret'] ?? '';
            $redirectUri = $settings['oauth2_redirect_uri'] ?? '';
            
            // Validate required settings
            if (empty($clientId) || empty($clientSecret) || empty($redirectUri)) {
                throw new Exception('OAuth2 configuration incomplete. Please check Client ID, Client Secret, and Redirect URI.');
            }
            
            // Exchange code for tokens
            $tokens = $this->oauth2Service->exchangeCodeForToken(
                $provider,
                $code,
                $clientId,
                $clientSecret,
                $redirectUri
            );
            
            // Clean up session
            unset($_SESSION['oauth2_state']);
            unset($_SESSION['oauth2_provider']);
            
            // Success message
            flash('settings_success', 'OAuth2 authentication successful! Your email account is now connected.', 'alert alert-success');
            redirect('admin/settings');
            
        } catch (Exception $e) {
            error_log('OAuth2 Callback Error: ' . $e->getMessage());
            flash('settings_error', 'OAuth2 authentication failed: ' . $e->getMessage(), 'alert alert-danger');
            redirect('admin/settings');
        }
    }
    
    /**
     * Test OAuth2 connection
     */
    public function test() {
        try {
            // Set JSON response header
            header('Content-Type: application/json');
            
            // Get current settings
            $settings = $this->settingsModel->getSystemSettings();
            
            $provider = $settings['oauth2_provider'] ?? 'microsoft';
            $clientId = $settings['oauth2_client_id'] ?? '';
            $clientSecret = $settings['oauth2_client_secret'] ?? '';
            
            // Validate OAuth2 configuration
            if (empty($clientId) || empty($clientSecret)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'OAuth2 Client ID and Client Secret must be configured'
                ]);
                return;
            }
            
            // Try to get valid access token
            $accessToken = $this->oauth2Service->getValidAccessToken($provider, $clientId, $clientSecret);
            
            if ($accessToken) {
                echo json_encode([
                    'success' => true,
                    'message' => 'OAuth2 authentication is working correctly',
                    'provider' => ucfirst($provider)
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'No valid access token found. Please re-authenticate.'
                ]);
            }
            
        } catch (Exception $e) {
            error_log('OAuth2 Test Error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'OAuth2 test failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Revoke OAuth2 tokens
     */
    public function revoke() {
        try {
            $provider = $_POST['provider'] ?? 'microsoft';
            
            // Delete stored tokens
            $db = Database::getInstance();
            $deleteQuery = "DELETE FROM oauth2_tokens WHERE provider = ?";
            $db->remove($deleteQuery, [$provider]);
            
            flash('settings_success', 'OAuth2 tokens revoked successfully', 'alert alert-success');
            
        } catch (Exception $e) {
            error_log('OAuth2 Revoke Error: ' . $e->getMessage());
            flash('settings_error', 'Failed to revoke OAuth2 tokens: ' . $e->getMessage(), 'alert alert-danger');
        }
        
        redirect('admin/settings');
    }
    
    /**
     * Get OAuth2 status
     */
    public function status() {
        try {
            header('Content-Type: application/json');
            
            $provider = $_GET['provider'] ?? 'microsoft';
            
            // Check if tokens exist
            $db = Database::getInstance();
            $query = "SELECT expires_at, created_at FROM oauth2_tokens WHERE provider = ? ORDER BY created_at DESC LIMIT 1";
            $result = $db->select($query, [$provider]);
            
            if (empty($result)) {
                echo json_encode([
                    'authenticated' => false,
                    'message' => 'Not authenticated'
                ]);
                return;
            }
            
            $token = $result[0];
            $isExpired = $token['expires_at'] < time();
            
            echo json_encode([
                'authenticated' => true,
                'expired' => $isExpired,
                'expires_at' => date('Y-m-d H:i:s', $token['expires_at']),
                'created_at' => date('Y-m-d H:i:s', $token['created_at']),
                'message' => $isExpired ? 'Token expired' : 'Authenticated'
            ]);
            
        } catch (Exception $e) {
            error_log('OAuth2 Status Error: ' . $e->getMessage());
            echo json_encode([
                'authenticated' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}