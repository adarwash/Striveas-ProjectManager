<?php

class App {
    protected $controller = 'Home'; // Setting the default controller
    protected $method = 'index';   // Setting the default method
    protected $params = [];        // URL parameters

    public function __construct() {
        // Enable error reporting for debugging
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Apply display timezone from settings (default America/Los_Angeles)
        try {
            require_once '../app/models/Setting.php';
            $settingModel = new Setting();
            $tz = $settingModel->get('display_timezone', 'America/Los_Angeles');
            if (is_string($tz) && $tz !== '') {
                @date_default_timezone_set($tz);
            }
        } catch (Exception $e) {
            @date_default_timezone_set('America/Los_Angeles');
        }
        
        $url = $this->parseUrl();
        
        // Debug output can be commented out in production
        // Uncomment for debugging
        /*
        echo '<div style="background:#f8f9fa; padding:10px; margin:10px; border:1px solid #ddd;">';
        echo '<h3>Debug Information</h3>';
        echo '<pre>URL Array: '; print_r($url); echo '</pre>';
        
        if (!empty($url) && is_array($url)) {
            $controllerFile = '../app/controllers/' . ucfirst($url[0]) . '.php';
            echo '<pre>Looking for controller file: ' . $controllerFile . '</pre>';
            
            if (file_exists($controllerFile)) {
                echo '<pre>Controller found: ' . ucfirst($url[0]) . '</pre>';
            } else {
                echo '<pre>Controller not found, using default: Home</pre>';
            }
        }
        
        echo '</div>';
        */
        
        // Check if user is trying to access Auth controller while already logged in
        if (!empty($url) && is_array($url) && 
            strtolower($url[0]) === 'auth' && 
            isset($_SESSION['is_logged_in']) && 
            $_SESSION['is_logged_in'] === true &&
            (!isset($url[1]) || $url[1] !== 'logout')) {
            
            // User is logged in and trying to access Auth controller (but not logout)
            // Redirect to home page
            header('Location: /home');
            exit;
        }
        
        // Customer-friendly alias: allow customers to use /tickets/show/{id}
        if (!empty($url) && is_array($url)
            && strtolower($url[0]) === 'tickets'
            && isset($url[1]) && strtolower($url[1]) === 'show'
            && isset($url[2])
            && function_exists('isCustomerLoggedIn') && isCustomerLoggedIn()
            && (!function_exists('isLoggedIn') || !isLoggedIn())) {
            // Route to Customer controller's ticket view
            require_once '../app/controllers/Customer.php';
            $this->controller = 'Customer';
            $this->method = 'ticket';
            $this->params = [$url[2]];
            $this->controller = new $this->controller;
            call_user_func_array([$this->controller, $this->method], $this->params);
            return;
        }

        // Handle customer portal routing
        if (!empty($url) && is_array($url) && strtolower($url[0]) === 'customer') {
            // Check if customer authentication is enabled (with safe fallback)
            $customerAuthEnabled = false;
            try {
                $customerAuthEnabled = isCustomerAuthEnabled();
            } catch (Exception $e) {
                error_log('Error checking customer auth status in routing: ' . $e->getMessage());
                $customerAuthEnabled = false;
            }
            
            if (!$customerAuthEnabled && (!isset($url[1]) || $url[1] !== 'auth')) {
                // Customer portal is disabled, show 404
                $this->controller = 'Home';
                $this->method = 'notFound';
                $this->params = [];
                require_once '../app/controllers/Home.php';
                $this->controller = new $this->controller;
                call_user_func_array([$this->controller, $this->method], $this->params);
                return;
            }
            
            if (isset($url[1])) {
                if ($url[1] === 'auth') {
                    // Customer authentication routes
                    $this->controller = 'CustomerAuth';
                    $this->method = isset($url[2]) ? $url[2] : 'index';
                    $this->params = array_slice($url, 3);
                } else {
                    // Other customer routes - require authentication
                    if (!isCustomerLoggedIn()) {
                        header('Location: ' . URLROOT . '/customer/auth');
                        exit;
                    }
                    
                    $this->controller = 'Customer';
                    $this->method = $url[1];
                    $this->params = array_slice($url, 2);
                }
            } else {
                // Default customer route
                if (!isCustomerLoggedIn()) {
                    header('Location: ' . URLROOT . '/customer/auth');
                    exit;
                }
                
                $this->controller = 'Customer';
                $this->method = 'index';
                $this->params = [];
            }
            
            // Load the appropriate controller
            $controllerFile = '../app/controllers/' . $this->controller . '.php';
            if (!file_exists($controllerFile)) {
                die('Customer controller file not found: ' . $controllerFile);
            }
            
            require_once $controllerFile;
            if (!class_exists($this->controller)) {
                die('Customer controller class not found: ' . $this->controller);
            }
            
            $this->controller = new $this->controller;
            
            // Validate session for authenticated customer routes
            if ($this->controller instanceof Customer && !validateCustomerSession()) {
                header('Location: ' . URLROOT . '/customer/auth');
                exit;
            }
            
            // Call the method
            if (method_exists($this->controller, $this->method)) {
                call_user_func_array([$this->controller, $this->method], $this->params);
            } else {
                // Method not found, try index
                if (method_exists($this->controller, 'index')) {
                    call_user_func_array([$this->controller, 'index'], [$this->method]);
                } else {
                    die('Customer method not found: ' . $this->method);
                }
            }
            return;
        }
        
        // Check if controller exists
        if (!empty($url) && is_array($url)) {
            // Only use the first URL segment as a controller if it's not numeric
            if (!is_numeric($url[0])) {
                $controllerFile = '../app/controllers/' . ucfirst($url[0]) . '.php';
                
                if (file_exists($controllerFile)) {
                    $this->controller = ucfirst($url[0]);
                    unset($url[0]);
                }
            }
        }
        
        // Load the controller file
        $controllerFile = '../app/controllers/' . $this->controller . '.php';
        if (!file_exists($controllerFile)) {
            die('Controller file not found: ' . $controllerFile);
        }
        
        require_once $controllerFile;
        if (!class_exists($this->controller)) {
            die('Controller class not found: ' . $this->controller);
        }
        
        $this->controller = new $this->controller;

        // Check if method exists
        if (isset($url[1]) && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }

        // Get parameters
        $this->params = $url ? array_values($url) : [];

        // Call the controller method with parameters
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function parseUrl() {
        // Get the request URI
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Remove query string if present
        $requestUri = strtok($requestUri, '?');
        
        // Remove the base directory path if needed (adjust if your app is in a subdirectory)
        $basePath = '/';
        if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        
        // Remove leading and trailing slashes
        $requestUri = trim($requestUri, '/');
        
        // Return empty array if no path
        if (empty($requestUri)) {
            return [];
        }
        
        // Split the path into segments
        return explode('/', filter_var($requestUri, FILTER_SANITIZE_URL));
    }
}
