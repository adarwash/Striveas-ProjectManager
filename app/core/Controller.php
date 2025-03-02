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
        require_once '../app/views/' . $view . '.php';

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

        // Start output buffering for navbar
        ob_start();

        // Include navbar if not on login page and user is logged in
        if (!$isLoginPage && isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in']) {
            // Check if navbar partial exists
            if (file_exists('../app/views/partials/navbar.php')) {
                require_once '../app/views/partials/navbar.php';
            }
        }

        // Capture the navbar content
        $navbar = ob_get_clean();

        // Output the layout with navbar and content injected
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . ($data['title'] ?? DEFAULT_TITLE) . '</title>

            <!-- Load CSS -->
            ' . Helper::getfiles(path:'css',type:'css') . '
        </head>
        <body' . ($isLoginPage ? ' class="login-page"' : '') . '>
            ' . $navbar . '
            <div class="container' . ($isLoginPage ? '-fluid p-0' : ' mt-4') . '">
                ' . $content . '
            </div>

            <!-- Load JS -->
            ' . Helper::getfiles(path:'js',type:'js')  . '
        </body>
        </html>';
    }
}
?>
