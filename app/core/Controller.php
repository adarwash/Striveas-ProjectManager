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

        // Output the layout with content injected
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . ($data['title'] ?? DEFAULT_TITLE) . '</title>

            <!-- Load CSS -->
            ' . Helper::getfiles(path:'css',type:'css') . '
        </head>
        <body>
            <div class="container mt-4">
                ' . $content . '
            </div>

            <!-- Load JS -->
            ' . Helper::getfiles(path:'js',type:'js')  . '
        </body>
        </html>';
    }
}
?>
