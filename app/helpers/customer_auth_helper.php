<?php

/**
 * Customer Authentication Helper Functions
 * Provides helper functions for customer portal authentication
 */

/**
 * Check if customer is logged in
 * @return bool
 */
function isCustomerLoggedIn(): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['customer_logged_in']) && 
           $_SESSION['customer_logged_in'] === true &&
           isset($_SESSION['customer_email']) &&
           !empty($_SESSION['customer_email']);
}

/**
 * Get current customer information
 * @return array|null Customer info or null if not logged in
 */
function getCurrentCustomer(): ?array {
    if (!isCustomerLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['customer_id'] ?? null,
        'email' => $_SESSION['customer_email'] ?? '',
        'name' => $_SESSION['customer_name'] ?? '',
        'domain' => $_SESSION['customer_domain'] ?? '',
        'login_time' => $_SESSION['customer_login_time'] ?? null
    ];
}

/**
 * Redirect customer to login if not authenticated
 * @param string $returnUrl Optional return URL after login
 */
function requireCustomerAuth(string $returnUrl = ''): void {
    if (!isCustomerLoggedIn()) {
        if (!empty($returnUrl)) {
            $_SESSION['customer_return_url'] = $returnUrl;
        }
        
        // Use direct header redirect instead of redirect function
        $redirectUrl = defined('URLROOT') ? URLROOT . '/customer/auth' : '/customer/auth';
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Check if customer authentication is enabled in settings
 * @return bool
 */
function isCustomerAuthEnabled(): bool {
    try {
        // Load required dependencies if not already loaded
        if (!defined('DB1')) {
            require_once __DIR__ . '/../../config/config.php';
        }
        if (!class_exists('EasySQL')) {
            require_once __DIR__ . '/../core/EasySQL.php';
        }
        if (!class_exists('Setting')) {
            require_once __DIR__ . '/../models/Setting.php';
        }
        
        $settingModel = new Setting();
        return (bool)$settingModel->get('customer_auth_enabled');
    } catch (Exception $e) {
        error_log('Error checking customer auth status: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get customer's ticket visibility setting
 * @return string 'email_match' or 'domain_match'
 */
function getCustomerTicketVisibility(): string {
    try {
        // Load required dependencies if not already loaded
        if (!defined('DB1')) {
            require_once __DIR__ . '/../../config/config.php';
        }
        if (!class_exists('EasySQL')) {
            require_once __DIR__ . '/../core/EasySQL.php';
        }
        if (!class_exists('Setting')) {
            require_once __DIR__ . '/../models/Setting.php';
        }
        
        $settingModel = new Setting();
        return $settingModel->get('ticket_visibility') ?? 'email_match';
    } catch (Exception $e) {
        error_log('Error getting ticket visibility setting: ' . $e->getMessage());
        return 'email_match';
    }
}

/**
 * Log customer activity
 * @param string $action Action performed
 * @param array $data Additional data to log
 */
function logCustomerActivity(string $action, array $data = []): void {
    $customer = getCurrentCustomer();
    if (!$customer) {
        return;
    }
    
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'customer_email' => $customer['email'],
        'action' => $action,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'data' => $data
    ];
    
    error_log('Customer Activity: ' . json_encode($logData));
}

/**
 * Validate customer session and refresh if needed
 * @return bool True if session is valid, false if expired
 */
function validateCustomerSession(): bool {
    if (!isCustomerLoggedIn()) {
        return false;
    }
    
    // Check session timeout (8 hours)
    $loginTime = $_SESSION['customer_login_time'] ?? 0;
    $sessionTimeout = 8 * 3600; // 8 hours in seconds
    
    if ((time() - $loginTime) > $sessionTimeout) {
        // Session expired
        clearCustomerSession();
        return false;
    }
    
    // Update last activity time
    $_SESSION['customer_last_activity'] = time();
    
    return true;
}

/**
 * Clear customer session data
 */
function clearCustomerSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $sessionKeys = [
        'customer_id',
        'customer_email', 
        'customer_name',
        'customer_domain',
        'customer_logged_in',
        'customer_login_time',
        'customer_last_activity',
        'customer_access_token',
        'customer_token_expires',
        'customer_return_url'
    ];
    
    foreach ($sessionKeys as $key) {
        unset($_SESSION[$key]);
    }
}

/**
 * Check if customer has access to a specific ticket
 * @param array $ticket Ticket data
 * @param string $customerEmail Customer email
 * @param string $visibility Visibility setting ('email_match' or 'domain_match')
 * @return bool
 */
function customerCanAccessTicket(array $ticket, string $customerEmail, string $visibility = 'email_match'): bool {
    if ($visibility === 'domain_match') {
        $customerDomain = substr(strrchr($customerEmail, '@'), 1);
        $ticketEmail = $ticket['inbound_email_address'] ?? '';
        
        if (!empty($ticketEmail)) {
            $ticketDomain = substr(strrchr($ticketEmail, '@'), 1);
            return $ticketDomain === $customerDomain;
        }
        
        // Check if ticket was created by someone from the same domain
        // This would require additional database queries in practice
        return false;
    } else {
        // Email match - customer can only see their own tickets
        return $ticket['inbound_email_address'] === $customerEmail;
    }
}

/**
 * Generate secure CSRF token for customer forms
 * @return string
 */
function generateCustomerCSRFToken(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $token = bin2hex(random_bytes(32));
    $_SESSION['customer_csrf_token'] = $token;
    $_SESSION['customer_csrf_time'] = time();
    
    return $token;
}

/**
 * Verify CSRF token for customer forms
 * @param string $token Token to verify
 * @return bool
 */
function verifyCustomerCSRFToken(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $sessionToken = $_SESSION['customer_csrf_token'] ?? '';
    $tokenTime = $_SESSION['customer_csrf_time'] ?? 0;
    
    // Token expires after 1 hour
    if ((time() - $tokenTime) > 3600) {
        return false;
    }
    
    return hash_equals($sessionToken, $token);
}

/**
 * Flash message helper for customer portal
 * @param string $name Message name
 * @param string $message Message content
 * @param string $class CSS class for styling
 */
function customerFlash(string $name, string $message, string $class = 'alert alert-info'): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['flash_' . $name] = $message;
    $_SESSION['flash_' . $name . '_class'] = $class;
}
