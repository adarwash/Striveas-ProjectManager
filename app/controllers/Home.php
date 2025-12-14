<?php 
    class Home extends Controller{
        private $projectModel;
        private $taskModel;
        private $clientModel;
        
        public function __construct() {
            // Load models
            $this->projectModel = $this->model('Project');
            $this->taskModel = $this->model('Task');
            $this->clientModel = $this->model('Client');
        }

        private function currentRoleId(): ?int {
            return isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : null;
        }

        private function isAdminRole(): bool {
            return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        }

        private function blockedClientIds(): array {
            return $this->clientModel->getBlockedClientIdsForRole(
                $this->currentRoleId(),
                $this->isAdminRole()
            );
        }
        
        public function index(){
            // Check if user is logged in
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in']) {
                // Redirect logged-in users to dashboard
                redirect('dashboard');
            } else {
                // User is not logged in, redirect to login page
                header('Location: /auth');
                exit;
            }
        }
        
        /**
         * Show 404 error page
         */
        public function notFound() {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Page Not Found']);
        }
    }