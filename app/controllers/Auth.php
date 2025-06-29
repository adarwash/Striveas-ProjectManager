<?php

class Auth extends Controller {
    private $userModel;
    
    /**
     * Constructor - initializes any needed models
     */
    public function __construct() {
        $this->userModel = $this->model('User');
    }
    
    /**
     * Display the login form
     *
     * @param string $error Optional error message to display
     * @return void
     */
    public function index($error = '') {
        // Create login form using the new Boot elements
        $loginForm = Boot::loginForm(
            action: '/auth/login',
            error: urldecode($error),
            rememberMe: true,
            forgotPassLink: '/auth/forgot'
        );
        
        $loginContainer = Boot::loginContainer(
            title: 'Sign In',
            content: $loginForm,
            footerText: '&copy; ' . date('Y') . ' Your Company Name',
            icon: 'bi bi-shield-lock'
        );
        
        // Pass data to the view
        $data = [
            'title' => 'Login',
            'loginContainer' => $loginContainer
        ];
        
        // Render the view
        $this->view('auth/login', $data);
    }
    
    /**
     * Process login form submission
     */
    public function login() {
        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $password = $_POST['password']; // Don't sanitize password before verification
            
            // Remember me checkbox
            $rememberMe = isset($_POST['remember']);
            
            // Attempt authentication
            $user = $this->userModel->authenticate($username, $password);
            
            if ($user) {
                // Start a session if not already started
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                // Store user data in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'] ?? $user['username'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_picture'] = $user['profile_picture'] ?? null;
                $_SESSION['is_logged_in'] = true;
                
                // If remember me is checked, set a cookie
                if ($rememberMe) {
                    $token = bin2hex(random_bytes(16)); // Create a secure token
                    
                    // Store token in cookie (30 days expiry)
                    setcookie('remember_token', $token, time() + 60*60*24*30, '/');
                    
                    // You would typically store this token in the database
                    // associated with the user, but for simplicity we're not doing that here
                }
                
                // Redirect to the dashboard page
                header('Location: /dashboard');
                exit;
            } else {
                // Authentication failed, redirect back to login with error
                $error = urlencode('Invalid username or password');
                header('Location: /auth/index/' . $error);
                exit;
            }
        } else {
            // If not a POST request, redirect to the login form
            header('Location: /auth');
            exit;
        }
    }
    
    /**
     * Log the user out
     */
    public function logout() {
        // Start a session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear session data
        $_SESSION = [];
        
        // Destroy the session
        session_destroy();
        
        // Clear remember me cookie if it exists
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Redirect to the login page
        header('Location: /auth');
        exit;
    }
    
    /**
     * Forgot password page
     */
    public function forgot() {
        // This is a placeholder - implement actual forgot password functionality
        $this->view('auth/forgot', ['title' => 'Forgot Password']);
    }
}

?> 