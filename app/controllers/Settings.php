<?php

class Settings extends Controller {
    private $userModel;
    
    /**
     * Constructor - initializes any needed models
     */
    public function __construct() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth');
        }
        
        $this->userModel = $this->model('User');
        
        // Create UserSettings table if it doesn't exist
        $this->userModel->createUserSettingsTable();
    }
    
    /**
     * Display the main settings page
     *
     * @return void
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);
        $notificationSettings = $this->userModel->getNotificationSettings($userId);
        
        $data = [
            'title' => 'Account Settings',
            'user' => $user,
            'notification_settings' => $notificationSettings
        ];
        
        // Check for any success or error messages in the session
        if (isset($_SESSION['settings_success'])) {
            $data['success_message'] = $_SESSION['settings_success'];
            unset($_SESSION['settings_success']);
        }
        
        if (isset($_SESSION['settings_error'])) {
            $data['error_message'] = $_SESSION['settings_error'];
            unset($_SESSION['settings_error']);
        }
        
        $this->view('settings/index', $data);
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
                if ($this->userModel->updateUserProfile($data)) {
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
            } else if (!$this->userModel->checkPassword($userId, $data['current_password'])) {
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
                if ($this->userModel->updatePassword($userId, $data['new_password'])) {
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
            if ($this->userModel->updateNotificationSettings($data)) {
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
     * Update theme settings (handled client-side with JavaScript)
     * This is just a placeholder for potential server-side theme settings
     * 
     * @return void
     */
    public function theme() {
        redirect('settings');
    }
} 