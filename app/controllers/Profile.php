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
        
        // Get user's projects and tasks stats
        $projectsCount = $this->projectModel->getProjectsCountByUser($userId);
        $tasksStats = $this->taskModel->getTasksStatsByUser($userId);
        $recentActivity = $this->userModel->getUserRecentActivity($userId);
        
        $data = [
            'title' => 'My Profile',
            'user' => $user,
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
} 