<?php

class Profile extends Controller {
    private $userModel;
    private $projectModel;
    private $taskModel;
    
    /**
     * Constructor - initializes any needed models
     */
    public function __construct() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth');
        }
        
        $this->userModel = $this->model('User');
        $this->projectModel = $this->model('Project');
        $this->taskModel = $this->model('Task');
        
        // Update tables for profile functionality
        $this->userModel->updateUsersTable();
        $this->userModel->createSkillsTables();
        $this->userModel->initializeExampleSkills();
    }
    
    /**
     * Display the user profile
     *
     * @return void
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);
        $userSettings = $this->userModel->getUserSettings($userId);
        
        // Get user's projects and tasks stats
        $projectsCount = $this->projectModel->getProjectsCountByUser($userId);
        $tasksStats = $this->taskModel->getTasksStatsByUser($userId);
        $recentActivity = $this->userModel->getUserRecentActivity($userId);
        
        $data = [
            'title' => 'My Profile',
            'user' => $user,
            'user_settings' => $userSettings,
            'projects_count' => $projectsCount,
            'tasks_stats' => $tasksStats,
            'recent_activity' => $recentActivity
        ];
        
        // Check for any success or error messages in the session
        if (isset($_SESSION['profile_success'])) {
            $data['success_message'] = $_SESSION['profile_success'];
            unset($_SESSION['profile_success']);
        }
        
        if (isset($_SESSION['profile_error'])) {
            $data['error_message'] = $_SESSION['profile_error'];
            unset($_SESSION['profile_error']);
        }
        
        $this->view('profile/index', $data);
    }
    
    /**
     * Edit user profile information
     *
     * @return void
     */
    public function edit() {
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $data = [
                'title' => 'Edit Profile',
                'user_id' => $userId,
                'full_name' => trim($_POST['full_name']),
                'email' => trim($_POST['email']),
                'position' => trim($_POST['position']),
                'bio' => trim($_POST['bio']),
                'full_name_err' => '',
                'email_err' => '',
                'position_err' => ''
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
                    $_SESSION['profile_success'] = 'Profile updated successfully';
                    redirect('profile');
                } else {
                    $_SESSION['profile_error'] = 'Something went wrong. Please try again.';
                    redirect('profile');
                }
            } else {
                // Load view with errors
                $user = $this->userModel->getUserById($userId);
                $data['user'] = $user;
                $this->view('profile/edit', $data);
            }
        } else {
            // GET request - show edit form
            $user = $this->userModel->getUserById($userId);
            
            $data = [
                'title' => 'Edit Profile',
                'user' => $user
            ];
            
            $this->view('profile/edit', $data);
        }
    }
    
    /**
     * Change user's profile picture
     *
     * @return void
     */
    public function picture() {
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if file was uploaded
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                // Define allowed file types and max size
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = 2 * 1024 * 1024; // 2MB
                
                $file = $_FILES['profile_picture'];
                
                // Check file type
                if (!in_array($file['type'], $allowedTypes)) {
                    $_SESSION['profile_error'] = 'Invalid file type. Please upload a JPG, PNG, or GIF image.';
                    redirect('profile');
                }
                
                // Check file size
                if ($file['size'] > $maxSize) {
                    $_SESSION['profile_error'] = 'File too large. Maximum size is 2MB.';
                    redirect('profile');
                }
                
                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newFilename = 'user_' . $userId . '_' . time() . '.' . $extension;
                
                // Define upload path
                $uploadDir = '../public/uploads/profile_pictures/';
                
                // Create directory if it doesn't exist
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $uploadPath = $uploadDir . $newFilename;
                
                // Move the uploaded file
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    // Update user profile with new picture
                    if ($this->userModel->updateProfilePicture($userId, $newFilename)) {
                        $_SESSION['profile_success'] = 'Profile picture updated successfully';
                    } else {
                        $_SESSION['profile_error'] = 'Failed to update profile picture in database';
                    }
                } else {
                    $_SESSION['profile_error'] = 'Failed to upload profile picture';
                }
            } else {
                $_SESSION['profile_error'] = 'Please select a file to upload';
            }
            
            redirect('profile');
        } else {
            redirect('profile');
        }
    }
    
    /**
     * Remove user's profile picture
     *
     * @return void
     */
    public function removePicture() {
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get current profile picture filename
            $user = $this->userModel->getUserById($userId);
            $currentPicture = $user['profile_picture'] ?? null;
            
            // Update database to remove profile picture reference
            if ($this->userModel->updateProfilePicture($userId, null)) {
                // If database update successful, try to remove the file
                if ($currentPicture) {
                    $filePath = '../public/uploads/profile_pictures/' . $currentPicture;
                    if (file_exists($filePath)) {
                        @unlink($filePath); // Try to delete file (suppressing errors with @)
                    }
                }
                
                $_SESSION['profile_success'] = 'Profile picture removed successfully';
            } else {
                $_SESSION['profile_error'] = 'Failed to remove profile picture';
            }
            
            redirect('profile');
        } else {
            redirect('profile');
        }
    }
    
    /**
     * Change user's password
     *
     * @return void
     */
    public function changePassword() {
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get POST data
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validate inputs
            if (empty($currentPassword)) {
                $_SESSION['profile_error'] = 'Please enter your current password';
                redirect('profile');
                return;
            }
            
            if (empty($newPassword)) {
                $_SESSION['profile_error'] = 'Please enter a new password';
                redirect('profile');
                return;
            }
            
            if (strlen($newPassword) < 6) {
                $_SESSION['profile_error'] = 'New password must be at least 6 characters';
                redirect('profile');
                return;
            }
            
            if ($newPassword !== $confirmPassword) {
                $_SESSION['profile_error'] = 'New passwords do not match';
                redirect('profile');
                return;
            }
            
            if ($currentPassword === $newPassword) {
                $_SESSION['profile_error'] = 'New password must be different from current password';
                redirect('profile');
                return;
            }
            
            // Attempt to update password (method verifies current password)
            if ($this->userModel->updatePassword($userId, $newPassword, $currentPassword)) {
                $_SESSION['profile_success'] = 'Password changed successfully';
            } else {
                $_SESSION['profile_error'] = 'Current password is incorrect';
            }
            
            redirect('profile');
        } else {
            redirect('profile');
        }
    }
    
    /**
     * Display and update user's skills
     *
     * @return void
     */
    public function skills() {
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Get skills array
            $skills = isset($_POST['skills']) ? $_POST['skills'] : [];
            
            // Update user skills
            if ($this->userModel->updateUserSkills($userId, $skills)) {
                $_SESSION['profile_success'] = 'Skills updated successfully';
            } else {
                $_SESSION['profile_error'] = 'Failed to update skills';
            }
            
            redirect('profile');
        } else {
            // GET request - show skills form
            $user = $this->userModel->getUserById($userId);
            $userSkills = $this->userModel->getUserSkills($userId);
            $allSkills = $this->userModel->getAllSkills();
            
            $data = [
                'title' => 'Edit Skills',
                'user' => $user,
                'user_skills' => $userSkills,
                'all_skills' => $allSkills
            ];
            
            $this->view('profile/skills', $data);
        }
    }

    /**
     * Update user's theme settings
     *
     * @return void
     */
    public function updateTheme() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];

            $action = strtolower(trim((string)($_POST['theme_action'] ?? 'apply')));
            if ($action === '') {
                $action = 'apply';
            }
            
            // Tool picker: allow presets + safe custom gradient (built from color pickers)
            $selection = trim((string)($_POST['nav_background'] ?? ''));
            $themeCardHeaders = !empty($_POST['theme_card_headers']) ? 1 : 0;
            // Project card headers (separate toggle)
            // Use a hidden marker so we can tell "unchecked" vs "old form didn't have this field"
            if (isset($_POST['theme_project_card_headers_present'])) {
                $themeProjectCardHeaders = !empty($_POST['theme_project_card_headers']) ? 1 : 0;
            } else {
                // Backwards-compatible fallback
                $themeProjectCardHeaders = $themeCardHeaders ? 1 : 0;
            }
            
            // Header text color (auto/custom)
            $headerTextMode = trim((string)($_POST['header_text_mode'] ?? 'auto'));
            $headerTextColor = '';
            if ($headerTextMode === 'custom') {
                $c = strtoupper(trim((string)($_POST['header_text_color'] ?? '')));
                if (!preg_match('/^#[0-9A-F]{6}$/', $c)) {
                    $_SESSION['profile_error'] = 'Invalid header text color selected.';
                    redirect('profile');
                    return;
                }
                $headerTextColor = $c;
            }
            $allowedPresets = [
                '',
                'linear-gradient(135deg, #0061f2 0%, rgba(105, 0, 199, 0.8) 100%)',
                'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                'linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%)',
                'linear-gradient(to top, #09203f 0%, #537895 100%)',
                'linear-gradient(to right, #654ea3, #eaafc8)',
            ];

            // Save custom theme (without applying)
            if ($action === 'save_custom') {
                $start = strtoupper(trim((string)($_POST['custom_color_start'] ?? '')));
                $end = strtoupper(trim((string)($_POST['custom_color_end'] ?? '')));
                $angle = (int)($_POST['custom_angle'] ?? 135);
                if (!preg_match('/^#[0-9A-F]{6}$/', $start) || !preg_match('/^#[0-9A-F]{6}$/', $end) || $angle < 0 || $angle > 360) {
                    $_SESSION['profile_error'] = 'Invalid custom gradient values.';
                    redirect('profile');
                    return;
                }

                $gradient = "linear-gradient({$angle}deg, {$start} 0%, {$end} 100%)";
                $payload = [
                    'start' => $start,
                    'end' => $end,
                    'angle' => $angle,
                    'gradient' => $gradient,
                    'saved_at' => date('Y-m-d H:i:s')
                ];

                $ok = $this->userModel->updateUserSettings((int)$userId, [
                    'saved_custom_theme' => json_encode($payload)
                ]);
                if ($ok) {
                    $_SESSION['profile_success'] = 'Custom theme saved. You can apply it later by selecting “Custom” and clicking Apply.';
                } else {
                    $_SESSION['profile_error'] = 'Failed to save custom theme.';
                }
                redirect('profile');
                return;
            }
            
            $navBackground = '';
            $savedCustomThemeJson = null;
            if ($selection === 'custom') {
                $start = strtoupper(trim((string)($_POST['custom_color_start'] ?? '')));
                $end = strtoupper(trim((string)($_POST['custom_color_end'] ?? '')));
                $angle = (int)($_POST['custom_angle'] ?? 135);
                
                if (!preg_match('/^#[0-9A-F]{6}$/', $start) || !preg_match('/^#[0-9A-F]{6}$/', $end) || $angle < 0 || $angle > 360) {
                    $_SESSION['profile_error'] = 'Invalid custom gradient values.';
                    redirect('profile');
                    return;
                }
                
                // Build a safe gradient string server-side (prevents CSS injection)
                $navBackground = "linear-gradient({$angle}deg, {$start} 0%, {$end} 100%)";

                // Also store this custom theme so it can be reused later
                $savedCustomThemeJson = json_encode([
                    'start' => $start,
                    'end' => $end,
                    'angle' => $angle,
                    'gradient' => $navBackground,
                    'saved_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $navBackground = $selection;
                if (!in_array($navBackground, $allowedPresets, true)) {
                    $_SESSION['profile_error'] = 'Invalid theme option selected.';
                    redirect('profile');
                    return;
                }
            }
            
            // Update settings
            $update = [
                'nav_background' => $navBackground,
                'theme_card_headers' => $themeCardHeaders,
                'theme_project_card_headers' => $themeProjectCardHeaders,
                'theme_header_text_color' => $headerTextColor
            ];
            if (!empty($savedCustomThemeJson)) {
                $update['saved_custom_theme'] = $savedCustomThemeJson;
            }

            if ($this->userModel->updateUserSettings($userId, $update)) {
                 $_SESSION['profile_success'] = 'Theme updated successfully';
            } else {
                 $_SESSION['profile_error'] = 'Failed to update theme';
            }
            
            redirect('profile');
        } else {
            redirect('profile');
        }
    }
} 