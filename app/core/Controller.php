<?php
class Controller {
    // Load the model
    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }

    // Load a view with a layout
    public function view($view, $data = []) {
        // Extract data for use in the view
        extract($data);

        // Start output buffering for dynamic content
        ob_start();

        // Load the view file
        require_once VIEWSPATH . '/' . $view . '.php';

        // Capture the view content
        $content = ob_get_clean();

        // Determine if this is the login page
        $isLoginPage = (strpos($view, 'auth/login') !== false);

        // Store current controller and method in session for navbar highlighting
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get the current controller and method from the class name and calling method
        $reflection = new ReflectionClass($this);
        $_SESSION['current_controller'] = strtolower(str_replace('Controller', '', $reflection->getShortName()));

        // Get the calling method name if available
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $_SESSION['current_method'] = $backtrace[1]['function'] ?? 'index';

        // Set the current page for sidebar highlighting
        $_SESSION['page'] = strtolower(str_replace('Controller', '', $reflection->getShortName()));
        if ($_SESSION['current_method'] !== 'index') {
            $_SESSION['page'] .= '_' . $_SESSION['current_method'];
        }

        // Load the appropriate layout with the content
        if ($isLoginPage) {
            // Use the login layout for the login page
            require_once VIEWSPATH . '/layouts/login.php';
        } else if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in']) {
            // Use the default layout for authenticated pages
            require_once VIEWSPATH . '/layouts/default.php';
        } else {
            // For other unauthenticated pages, just output the content
            echo $content;
        }
    }

    /**
     * Load a partial view without a layout
     * 
     * @param string $view The view file to load
     * @param array $data Data to pass to the view
     * @return void
     */
    public function partial($view, $data = []) {
        extract($data);
        require_once VIEWSPATH . '/' . $view . '.php';
    }

    /**
     * Get a partial view as a string
     * 
     * @param string $view The view file to load
     * @param array $data Data to pass to the view
     * @return string The rendered view as a string
     */
    public function getPartial($view, $data = []) {
        extract($data);
        ob_start();
        require_once VIEWSPATH . '/' . $view . '.php';
        return ob_get_clean();
    }
}
?>
