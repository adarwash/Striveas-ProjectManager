<?php
class Dashboard extends Controller {
    private $userModel;
    private $projectModel;
    private $taskModel;
    private $departmentModel;
    
    public function __construct() {
        // Load models
        $this->userModel = $this->model('User');
        $this->projectModel = $this->model('Project');
        $this->taskModel = $this->model('Task');
        $this->departmentModel = $this->model('Department');
    }
    
    public function index() {
        // Get user ID from session
        $userId = $_SESSION['user_id'];
        
        // Get projects
        $projects = $this->projectModel->getAllProjects();
        
        // Get tasks
        $tasks = $this->taskModel->getAllTasks();
        
        // Get tasks assigned to the current user
        $assignedTasks = $this->taskModel->getTasksByUserId($userId);
        
        // Get departments
        $departments = $this->departmentModel->getAllDepartments();
        
        // Get project counts by status
        $projectCounts = $this->projectModel->getProjectCountsByStatus();
        
        // Get task counts by status
        $taskCounts = $this->taskModel->getTaskCountsByStatus();
        
        // Get projects assigned to the current user
        $userProjects = $this->projectModel->getProjectsCountByUser($userId);
        
        $data = [
            'title' => 'Dashboard',
            'projects' => $projects,
            'tasks' => $tasks,
            'assigned_tasks' => $assignedTasks,
            'departments' => $departments,
            'project_counts' => $projectCounts,
            'task_counts' => $taskCounts,
            'user_projects' => $userProjects
        ];
        
        $this->view('dashboard/index', $data);
    }
    
    public function calendar() {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect('/users/login');
        }
        
        // Get user info
        $user_id = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($user_id);
        
        // Get all tasks with project details
        $tasks = $this->taskModel->getAllTasksWithProjects($user_id);
        
        $data = [
            'title' => 'Calendar View',
            'user' => $user,
            'tasks' => $tasks
        ];
        
        $this->view('dashboard/calendar', $data);
    }
    
    public function gantt() {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect('/users/login');
        }
        
        // Get user info
        $user_id = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($user_id);
        
        // Get all projects with tasks
        $projects = $this->projectModel->getProjectsWithTasks($user_id);
        
        $data = [
            'title' => 'Gantt Chart',
            'user' => $user,
            'projects' => $projects
        ];
        
        $this->view('dashboard/gantt', $data);
    }
}
?> 