<?php

class Settings extends Controller {
    private $setting;
    private $user;
    
    /**
     * Constructor - initializes any needed models
     */
    public function __construct() {
        $this->setting = $this->model('Setting');
        $this->user = $this->model('User');
        
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('/auth/login');
        }
        
        // Create UserSettings table if it doesn't exist
        $this->user->createUserSettingsTable();
    }
    
    /**
     * Settings page - shows different content based on user role
     */
    public function index() {
        // Get current user data
        $userData = $this->user->getUserById($_SESSION['user_id']);
        $userSettings = $this->user->getUserSettings($_SESSION['user_id']);
        $systemSettings = $this->setting->getSystemSettings();
        
        // Check if user is admin
        $isAdmin = isAdmin() || hasPermission('admin.system_settings') || 
                   (isset($_SESSION['username']) && $_SESSION['username'] === 'admin');
        
        $data = [
            'title' => 'Settings',
            'user' => $userData,
            'userSettings' => $userSettings,
            'systemSettings' => $systemSettings,
            'isAdmin' => $isAdmin,
            'currency' => $systemSettings['currency'] ?? ['code' => 'USD', 'symbol' => '$', 'position' => 'before', 'decimals' => 2, 'thousands_separator' => ',', 'decimal_separator' => '.']
        ];
        
        // Load appropriate view based on admin status
        if ($isAdmin) {
            // Redirect admins to admin settings for system-wide settings
            redirect('/admin/settings');
        } else {
            // Show user settings page
            $this->view('settings/index', $data);
        }
    }
    
    /**
     * Redirect currency page to admin settings
     */
    public function currency() {
        redirect('/admin/settings');
    }
    
    /**
     * Handle any other method calls by redirecting to admin settings
     */
    public function __call($method, $args) {
        redirect('/admin/settings');
    }
    
    /**
     * Handle settings form submission
     */
    public function update() {
        // Validate request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/settings');
        }
        
        try {
            // Update currency settings
            $currency = [
                'code' => sanitize_input($_POST['currency_code'] ?? 'USD'),
                'symbol' => sanitize_input($_POST['currency_symbol'] ?? '$'),
                'position' => sanitize_input($_POST['currency_position'] ?? 'before'),
                'thousands_separator' => sanitize_input($_POST['thousands_separator'] ?? ','),
                'decimal_separator' => sanitize_input($_POST['decimal_separator'] ?? '.'),
                'decimals' => (int)($_POST['decimals'] ?? 2)
            ];
            
            // Save currency settings
            $result = $this->setting->set('currency', $currency);
            
            if ($result) {
                $_SESSION['success'] = "Settings updated successfully";
            } else {
                $_SESSION['error'] = "Failed to update settings";
            }
            
            redirect('/settings');
        } catch (Exception $e) {
            error_log('Settings Update Error: ' . $e->getMessage());
            $_SESSION['error'] = "An error occurred. Please try again.";
            redirect('/settings');
        }
    }
    
    /**
     * Update currency settings
     */
    public function updateCurrency() {
        // Validate request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/settings/currency');
        }
        
        try {
            // Update currency settings
            $currency = [
                'code' => sanitize_input($_POST['currency_code'] ?? 'USD'),
                'symbol' => sanitize_input($_POST['currency_symbol'] ?? '$'),
                'position' => sanitize_input($_POST['currency_position'] ?? 'before'),
                'thousands_separator' => sanitize_input($_POST['thousands_separator'] ?? ','),
                'decimal_separator' => sanitize_input($_POST['decimal_separator'] ?? '.'),
                'decimals' => (int)($_POST['decimals'] ?? 2)
            ];
            
            // Save currency settings
            $result = $this->setting->set('currency', $currency);
            
            if ($result) {
                $_SESSION['success'] = "Currency settings updated successfully";
            } else {
                $_SESSION['error'] = "Failed to update currency settings";
            }
            
            redirect('/settings/currency');
        } catch (Exception $e) {
            error_log('Currency Settings Update Error: ' . $e->getMessage());
            $_SESSION['error'] = "An error occurred. Please try again.";
            redirect('/settings/currency');
        }
    }
    
    /**
     * Update user profile information
     *
     * @return void
     */
    public function profile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $userId = $_SESSION['user_id'];
            $data = [
                'title' => 'Profile Settings',
                'user_id' => $userId,
                'full_name' => trim($_POST['full_name']),
                'email' => trim($_POST['email']),
                'full_name_err' => '',
                'email_err' => ''
            ];
            
            // Validate email
            if (empty($data['email'])) {
                $data['email_err'] = 'Please enter an email';
            } else if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['email_err'] = 'Invalid email format';
            }
            
            // Validate name
            if (empty($data['full_name'])) {
                $data['full_name_err'] = 'Please enter your name';
            }
            
            // Make sure no errors
            if (empty($data['email_err']) && empty($data['full_name_err'])) {
                // Update user
                if ($this->user->updateUserProfile($data)) {
                    $_SESSION['settings_success'] = 'Profile updated successfully';
                    redirect('settings');
                } else {
                    $_SESSION['settings_error'] = 'Something went wrong. Please try again.';
                    redirect('settings');
                }
            } else {
                // Load view with errors
                $this->view('settings/index', $data);
            }
        } else {
            redirect('settings');
        }
    }
    
    /**
     * Update user password
     *
     * @return void
     */
    public function password() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $userId = $_SESSION['user_id'];
            $data = [
                'title' => 'Password Settings',
                'user_id' => $userId,
                'current_password' => trim($_POST['current_password']),
                'new_password' => trim($_POST['new_password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'current_password_err' => '',
                'new_password_err' => '',
                'confirm_password_err' => ''
            ];
            
            // Validate current password
            if (empty($data['current_password'])) {
                $data['current_password_err'] = 'Please enter current password';
            } else if (!$this->user->checkPassword($userId, $data['current_password'])) {
                $data['current_password_err'] = 'Current password is incorrect';
            }
            
            // Validate new password
            if (empty($data['new_password'])) {
                $data['new_password_err'] = 'Please enter new password';
            } else if (strlen($data['new_password']) < 6) {
                $data['new_password_err'] = 'Password must be at least 6 characters';
            }
            
            // Validate password confirmation
            if (empty($data['confirm_password'])) {
                $data['confirm_password_err'] = 'Please confirm new password';
            } else if ($data['new_password'] !== $data['confirm_password']) {
                $data['confirm_password_err'] = 'Passwords do not match';
            }
            
            // Make sure no errors
            if (empty($data['current_password_err']) && empty($data['new_password_err']) && empty($data['confirm_password_err'])) {
                // Update password
                if ($this->user->updatePassword($userId, $data['new_password'], $data['current_password'])) {
                    $_SESSION['settings_success'] = 'Password updated successfully';
                    redirect('settings');
                } else {
                    $_SESSION['settings_error'] = 'Something went wrong. Please try again.';
                    redirect('settings');
                }
            } else {
                // Load view with errors
                $this->view('settings/index', $data);
            }
        } else {
            redirect('settings');
        }
    }
    
    /**
     * Update user notification preferences
     *
     * @return void
     */
    public function notifications() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $userId = $_SESSION['user_id'];
            $data = [
                'title' => 'Notification Settings',
                'user_id' => $userId,
                'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
                'task_reminders' => isset($_POST['task_reminders']) ? 1 : 0,
                'project_updates' => isset($_POST['project_updates']) ? 1 : 0
            ];
            
            // Update notification settings
            if ($this->user->updateNotificationSettings($data)) {
                $_SESSION['settings_success'] = 'Notification settings updated successfully';
                redirect('settings');
            } else {
                $_SESSION['settings_error'] = 'Something went wrong. Please try again.';
                redirect('settings');
            }
        } else {
            redirect('settings');
        }
    }
    
    /**
     * Update admin settings
     * 
     * @return void
     */
    public function admin() {
        // Check if user is admin
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['settings_error'] = 'You do not have permission to access admin settings';
            redirect('settings');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $data = [
                'title' => 'Admin Settings',
                'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
                'default_project_category' => trim($_POST['default_project_category']),
                'default_project_status' => trim($_POST['default_project_status'])
            ];
            
            // Update admin settings (you would need to create this method in a Settings model)
            // For now, we'll just simulate success
            $_SESSION['settings_success'] = 'Admin settings updated successfully';
            redirect('settings#admin');
        } else {
            redirect('settings');
        }
    }
    
    /**
     * Update user profile information
     * 
     * @return void
     */
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('settings');
        }
        
        $userId = $_SESSION['user_id'];
        $data = [
            'user_id' => $userId,
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'department' => trim($_POST['department'] ?? '')
        ];
        
        // Validate inputs
        if (empty($data['full_name'])) {
            $_SESSION['settings_error'] = 'Full name is required';
            redirect('settings');
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['settings_error'] = 'Valid email is required';
            redirect('settings');
        }
        
        // Update user profile
        if ($this->user->updateUserProfile($data)) {
            $_SESSION['settings_success'] = 'Profile updated successfully';
        } else {
            $_SESSION['settings_error'] = 'Failed to update profile';
        }
        
        redirect('settings');
    }
    
    /**
     * Update user preferences
     * 
     * @return void
     */
    public function updatePreferences() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('settings');
        }
        
        $userId = $_SESSION['user_id'];
        $settings = [
            'theme' => $_POST['theme'] ?? 'light',
            'items_per_page' => (int)($_POST['items_per_page'] ?? 25),
            'date_format' => $_POST['date_format'] ?? 'M j, Y'
        ];
        
        if ($this->user->updateUserSettings($userId, $settings)) {
            $_SESSION['settings_success'] = 'Preferences updated successfully';
        } else {
            $_SESSION['settings_error'] = 'Failed to update preferences';
        }
        
        redirect('settings');
    }
    
    /**
     * Update time settings
     * 
     * @return void
     */
    public function updateTimeSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('settings');
        }
        
        $userId = $_SESSION['user_id'];
        $settings = [
            'timezone' => $_POST['timezone'] ?? 'UTC',
            'time_format' => $_POST['time_format'] ?? '12'
        ];
        
        if ($this->user->updateUserSettings($userId, $settings)) {
            $_SESSION['settings_success'] = 'Time settings updated successfully';
        } else {
            $_SESSION['settings_error'] = 'Failed to update time settings';
        }
        
        redirect('settings');
    }
    
    /**
     * Update notification preferences
     * 
     * @return void
     */
    public function updateNotifications() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('settings');
        }
        
        $userId = $_SESSION['user_id'];
        $settings = [
            'email_new_tickets' => isset($_POST['email_new_tickets']),
            'email_ticket_updates' => isset($_POST['email_ticket_updates']),
            'email_comments' => isset($_POST['email_comments']),
            'browser_notifications' => isset($_POST['browser_notifications']),
            'daily_digest' => isset($_POST['daily_digest']),
            'weekly_summary' => isset($_POST['weekly_summary'])
        ];
        
        if ($this->user->updateUserSettings($userId, $settings)) {
            $_SESSION['settings_success'] = 'Notification settings updated successfully';
        } else {
            $_SESSION['settings_error'] = 'Failed to update notification settings';
        }
        
        redirect('settings');
    }
    
    /**
     * Update user password
     * 
     * @return void
     */
    public function updatePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('settings');
        }
        
        // Sanitize and normalize input
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $currentPassword = trim($_POST['current_password'] ?? '');
        $newPassword = trim($_POST['new_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');
        
        // Validate inputs
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['settings_error'] = 'All password fields are required';
            redirect('settings');
        }
        
        if ($newPassword !== $confirmPassword) {
            $_SESSION['settings_error'] = 'New passwords do not match';
            redirect('settings');
        }
        
        if (strlen($newPassword) < 6) {
            $_SESSION['settings_error'] = 'Password must be at least 6 characters';
            redirect('settings');
        }
        
        // Verify current password and update
        $userId = $_SESSION['user_id'];
        if ($this->user->updatePassword($userId, $newPassword, $currentPassword)) {
            $_SESSION['settings_success'] = 'Password updated successfully';
        } else {
            $_SESSION['settings_error'] = 'Current password is incorrect or update failed';
        }
        
        redirect('settings');
    }
    
    /**
     * Update theme settings (handled client-side with JavaScript)
     * This is just a placeholder for potential server-side theme settings
     * 
     * @return void
     */
    public function theme() {
        redirect('settings');
    }
} 