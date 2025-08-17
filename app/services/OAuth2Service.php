<?php

/**
 * OAuth2 Service for Email Authentication
 * Supports Microsoft 365, Google, and other OAuth2 providers
 */
class OAuth2Service {
    
    private $config;
    private $db;
    
    // OAuth2 Provider configurations
    private $providers = [
        'microsoft' => [
            'name' => 'Microsoft 365',
            'auth_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'token_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'scope' => 'https://outlook.office365.com/IMAP.AccessAsUser.All https://outlook.office365.com/POP.AccessAsUser.All offline_access',
            'imap_host' => 'outlook.office365.com',
            'imap_port' => 993
        ],
        'google' => [
            'name' => 'Google Workspace',
            'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'scope' => 'https://mail.google.com/',
            'imap_host' => 'imap.gmail.com',
            'imap_port' => 993
        ]
    ];
    
    public function __construct($config = null, $db = null) {
        $this->config = $config ?: [];
        $this->db = $db ?: Database::getInstance();
    }
    
    /**
     * Get available OAuth2 providers
     */
    public function getProviders() {
        return $this->providers;
    }
    
    /**
     * Generate OAuth2 authorization URL
     */
    public function getAuthorizationUrl($provider, $clientId, $redirectUri, $state = null) {
        if (!isset($this->providers[$provider])) {
            throw new Exception("Unsupported OAuth2 provider: $provider");
        }
        
        $config = $this->providers[$provider];
        $state = $state ?: bin2hex(random_bytes(16));
        
        $params = [
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'scope' => $config['scope'],
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];
        
        // Store state in session for security
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['oauth2_state'] = $state;
        $_SESSION['oauth2_provider'] = $provider;
        
        return $config['auth_url'] . '?' . http_build_query($params);
    }
    
    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken($provider, $code, $clientId, $clientSecret, $redirectUri) {
        if (!isset($this->providers[$provider])) {
            throw new Exception("Unsupported OAuth2 provider: $provider");
        }
        
        $config = $this->providers[$provider];
        
        $postData = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri
        ];
        
        $response = $this->makeHttpRequest($config['token_url'], $postData);
        
        if (!$response || !isset($response['access_token'])) {
            throw new Exception('Failed to obtain access token');
        }
        
        // Store tokens securely
        $tokenData = [
            'provider' => $provider,
            'access_token' => $response['access_token'],
            'refresh_token' => $response['refresh_token'] ?? null,
            'expires_at' => time() + ($response['expires_in'] ?? 3600),
            'scope' => $response['scope'] ?? $config['scope'],
            'created_at' => time()
        ];
        
        $this->storeTokens($tokenData);
        
        return $tokenData;
    }
    
    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken($provider, $refreshToken, $clientId, $clientSecret) {
        if (!isset($this->providers[$provider])) {
            throw new Exception("Unsupported OAuth2 provider: $provider");
        }
        
        $config = $this->providers[$provider];
        
        $postData = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token'
        ];
        
        $response = $this->makeHttpRequest($config['token_url'], $postData);
        
        if (!$response || !isset($response['access_token'])) {
            throw new Exception('Failed to refresh access token');
        }
        
        // Update stored tokens
        $tokenData = [
            'provider' => $provider,
            'access_token' => $response['access_token'],
            'refresh_token' => $response['refresh_token'] ?? $refreshToken,
            'expires_at' => time() + ($response['expires_in'] ?? 3600),
            'scope' => $response['scope'] ?? $config['scope'],
            'updated_at' => time()
        ];
        
        $this->updateTokens($tokenData);
        
        return $tokenData;
    }
    
    /**
     * Get valid access token (refresh if needed)
     */
    public function getValidAccessToken($provider, $clientId, $clientSecret) {
        $tokens = $this->getStoredTokens($provider);
        
        if (!$tokens) {
            throw new Exception('No stored tokens found. Please re-authenticate.');
        }
        
        // Check if token is still valid (with 5 minute buffer)
        if ($tokens['expires_at'] > (time() + 300)) {
            return $tokens['access_token'];
        }
        
        // Token expired, try to refresh
        if (empty($tokens['refresh_token'])) {
            throw new Exception('Access token expired and no refresh token available. Please re-authenticate.');
        }
        
        try {
            $newTokens = $this->refreshAccessToken($provider, $tokens['refresh_token'], $clientId, $clientSecret);
            return $newTokens['access_token'];
        } catch (Exception $e) {
            throw new Exception('Failed to refresh access token: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate XOAUTH2 string for IMAP authentication
     */
    public function generateXOAUTH2String($username, $accessToken) {
        $authString = "user=$username\x01auth=Bearer $accessToken\x01\x01";
        return base64_encode($authString);
    }
    
    /**
     * Store OAuth2 tokens securely in database
     */
    private function storeTokens($tokenData) {
        try {
            // First, delete any existing tokens for this provider
            $deleteQuery = "DELETE FROM oauth2_tokens WHERE provider = ?";
            $this->db->remove($deleteQuery, [$tokenData['provider']]);
            
            // Insert new tokens
            $insertQuery = "INSERT INTO oauth2_tokens (provider, access_token, refresh_token, expires_at, scope, created_at) VALUES (?, ?, ?, ?, ?, ?)";
            $this->db->insert($insertQuery, [
                $tokenData['provider'],
                $this->encryptToken($tokenData['access_token']),
                $this->encryptToken($tokenData['refresh_token'] ?? ''),
                $tokenData['expires_at'],
                $tokenData['scope'],
                $tokenData['created_at']
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log('OAuth2Service StoreTokens Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update OAuth2 tokens in database
     */
    private function updateTokens($tokenData) {
        try {
            $updateQuery = "UPDATE oauth2_tokens SET access_token = ?, refresh_token = ?, expires_at = ?, updated_at = ? WHERE provider = ?";
            $this->db->update($updateQuery, [
                $this->encryptToken($tokenData['access_token']),
                $this->encryptToken($tokenData['refresh_token']),
                $tokenData['expires_at'],
                $tokenData['updated_at'],
                $tokenData['provider']
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log('OAuth2Service UpdateTokens Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get stored OAuth2 tokens from database
     */
    private function getStoredTokens($provider) {
        try {
            $query = "SELECT * FROM oauth2_tokens WHERE provider = ? ORDER BY created_at DESC LIMIT 1";
            $result = $this->db->select($query, [$provider]);
            
            if (empty($result)) {
                return null;
            }
            
            $tokens = $result[0];
            
            // Decrypt tokens
            $tokens['access_token'] = $this->decryptToken($tokens['access_token']);
            $tokens['refresh_token'] = $this->decryptToken($tokens['refresh_token']);
            
            return $tokens;
        } catch (Exception $e) {
            error_log('OAuth2Service GetStoredTokens Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Make HTTP request for OAuth2 operations
     */
    private function makeHttpRequest($url, $postData = null) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'ProjectTracker OAuth2 Client/1.0'
        ]);
        
        if ($postData) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json'
            ]);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL error: $error");
        }
        
        if ($httpCode >= 400) {
            throw new Exception("HTTP error $httpCode: $response");
        }
        
        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: $response");
        }
        
        return $decodedResponse;
    }
    
    /**
     * Encrypt token for secure storage
     */
    private function encryptToken($token) {
        if (empty($token)) {
            return '';
        }
        
        $key = $this->getEncryptionKey();
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($token, 'AES-256-CBC', $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt token from storage
     */
    private function decryptToken($encryptedToken) {
        if (empty($encryptedToken)) {
            return '';
        }
        
        $key = $this->getEncryptionKey();
        $data = base64_decode($encryptedToken);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
    
    /**
     * Get encryption key for token storage
     */
    private function getEncryptionKey() {
        // Use a combination of server-specific values to create encryption key
        $serverKey = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $appKey = defined('APP_KEY') ? APP_KEY : 'default-key';
        
        return hash('sha256', $serverKey . $appKey . 'oauth2-tokens');
    }
    
    /**
     * Create OAuth2 tokens table if it doesn't exist
     */
    public function createTokensTable() {
        try {
            $createTableQuery = "
                CREATE TABLE IF NOT EXISTS oauth2_tokens (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    provider VARCHAR(50) NOT NULL,
                    access_token TEXT NOT NULL,
                    refresh_token TEXT,
                    expires_at INT NOT NULL,
                    scope TEXT,
                    created_at INT NOT NULL,
                    updated_at INT DEFAULT NULL,
                    INDEX idx_provider (provider),
                    INDEX idx_expires_at (expires_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            
            $this->db->query($createTableQuery);
            return true;
        } catch (Exception $e) {
            error_log('OAuth2Service CreateTokensTable Error: ' . $e->getMessage());
            return false;
        }
    }
}