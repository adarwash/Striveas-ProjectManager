<?php 
    class Home extends Controller{
        private $projectModel;
        private $taskModel;
        
        public function __construct() {
            // Load models
            $this->projectModel = $this->model('Project');
            $this->taskModel = $this->model('Task');
        }
        
        public function index(){
            // Check if user is logged in
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in']) {
                // User is logged in, show dashboard
                $username = $_SESSION['username'] ?? 'User';
                $userId = $_SESSION['user_id'] ?? null;
                
                // Get project stats
                $projectStats = $this->projectModel->getProjectStats();
                
                // Get task stats
                $taskStats = $this->taskModel->getTaskStats();
                
                // Get recent activity
                $recentActivity = $this->taskModel->getRecentActivity();
                
                // Get budget usage by department
                $budgetUsage = $this->projectModel->getDepartmentBudgetUsage();
                
                // Get user's assigned tasks (if user_id is set)
                // $userTasks = $userId ? $this->taskModel->getTasksByUser($userId) : [];
                
                $this->view('home/dashboard', [
                    'title' => 'Dashboard',
                    'username' => $username,
                    'project_stats' => $projectStats,
                    'task_stats' => $taskStats,
                    'recent_activity' => $recentActivity,
                    'budget_usage' => $budgetUsage
                ]);
            } else {
                // User is not logged in, show welcome page
                $this->view('home/index', [
                    'title' => 'Welcome'
                ]);
            }
        }
    }