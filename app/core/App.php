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
