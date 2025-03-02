<?php
class Tasks extends Controller {
    private $taskModel;
    private $projectModel;
    private $userModel;
    
    public function __construct() {
        // Check if user is logged in
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
            header('Location: /auth');
            exit;
        }
        
        // Load models
        $this->taskModel = $this->model('Task');
        $this->projectModel = $this->model('Project');
        $this->userModel = $this->model('User');
    }
    
    // List all tasks (can be filtered by project)
    public function index() {
        // Get project_id from query string if provided
        $projectId = isset($_GET['project_id']) ? $_GET['project_id'] : null;
        
        if ($projectId) {
            // Get tasks for specific project
            $tasks = $this->taskModel->getTasksByProject($projectId);
            
            // Get project details
            $project = $this->projectModel->getProjectById($projectId);
            
            $title = 'Tasks for ' . $project->title;
        } else {
            // Get all tasks
            $tasks = $this->taskModel->getAllTasks();
            $title = 'All Tasks';
        }
        
        $this->view('tasks/index', [
            'title' => $title,
            'tasks' => $tasks,
            'project_id' => $projectId
        ]);
    }
    
    // Show form to create a new task (optionally with project pre-selected)
    public function create($projectId = null) {
        // Get all projects for dropdown
        $projects = $this->projectModel->getAllProjects();
        
        // Get all users for assignments
        $users = $this->userModel->getAllUsers();
        
        $this->view('tasks/create', [
            'title' => 'Create Task',
            'projects' => $projects,
            'users' => $users,
            'project_id' => $projectId
        ]);
    }
    
    // Process the new task form
    public function store() {
        // Process form data if POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Initialize data array
            $data = [
                'project_id' => trim($_POST['project_id']),
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'status' => trim($_POST['status']),
                'priority' => trim($_POST['priority']),
                'due_date' => !empty($_POST['due_date']) ? trim($_POST['due_date']) : null,
                'assigned_to' => !empty($_POST['assigned_to']) ? trim($_POST['assigned_to']) : null,
                'project_id_err' => '',
                'title_err' => '',
                'status_err' => '',
                'priority_err' => ''
            ];
            
            // Validate project_id
            if (empty($data['project_id'])) {
                $data['project_id_err'] = 'Please select a project';
            }
            
            // Validate title
            if (empty($data['title'])) {
                $data['title_err'] = 'Please enter task title';
            }
            
            // Validate status
            if (empty($data['status'])) {
                $data['status_err'] = 'Please select a status';
            }
            
            // Validate priority
            if (empty($data['priority'])) {
                $data['priority_err'] = 'Please select a priority';
            }
            
            // Check if there are no errors
            if (empty($data['project_id_err']) && empty($data['title_err']) && 
                empty($data['status_err']) && empty($data['priority_err'])) {
                
                // Add the created_by from the session
                $data['created_by'] = $_SESSION['user_id'];
                
                // Create task
                $this->taskModel->create($data);
                
                // Set flash message
                // Placeholder: flash('task_message', 'Task created successfully');
                
                // Redirect to the project details page
                header('Location: /projects/show/' . $data['project_id']);
                exit;
            } else {
                // Get all projects for dropdown
                // Placeholder: $projects = $this->projectModel->getAllProjects();
                $projects = [];
                
                // Get all users for assignments
                // Placeholder: $users = $this->userModel->getAllUsers();
                $users = [];
                
                // Load view with errors
                $this->view('tasks/create', [
                    'title' => 'Create Task',
                    'projects' => $projects,
                    'users' => $users,
                    'data' => $data
                ]);
            }
        } else {
            // If not POST request, redirect to create form
            header('Location: /tasks/create');
            exit;
        }
    }
    
    // Show a single task
    public function show($id) {
        // Get task by ID
        // Placeholder: $task = $this->taskModel->getTaskById($id);
        $task = (object)[
            'id' => $id,
            'project_id' => 1,
            'project_title' => 'Sample Project',
            'title' => 'Sample Task',
            'description' => 'This is a sample task description.',
            'status' => 'In Progress',
            'priority' => 'High',
            'due_date' => date('Y-m-d', strtotime('+7 days')),
            'assigned_to' => 'John Doe',
            'created_by' => 'Admin',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->view('tasks/show', [
            'title' => $task->title,
            'task' => $task
        ]);
    }
    
    // Show form to edit task
    public function edit($id) {
        // Get task by ID
        // Placeholder: $task = $this->taskModel->getTaskById($id);
        $task = (object)[
            'id' => $id,
            'project_id' => 1,
            'title' => 'Sample Task',
            'description' => 'This is a sample task description.',
            'status' => 'In Progress',
            'priority' => 'High',
            'due_date' => date('Y-m-d', strtotime('+7 days')),
            'assigned_to' => 1
        ];
        
        // Get all projects for dropdown
        // Placeholder: $projects = $this->projectModel->getAllProjects();
        $projects = [];
        
        // Get all users for assignments
        // Placeholder: $users = $this->userModel->getAllUsers();
        $users = [];
        
        $this->view('tasks/edit', [
            'title' => 'Edit Task',
            'task' => $task,
            'projects' => $projects,
            'users' => $users
        ]);
    }
    
    // Process the edit form
    public function update($id) {
        // Process form data if POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Initialize data array
            $data = [
                'id' => $id,
                'project_id' => trim($_POST['project_id']),
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'status' => trim($_POST['status']),
                'priority' => trim($_POST['priority']),
                'due_date' => !empty($_POST['due_date']) ? trim($_POST['due_date']) : null,
                'assigned_to' => !empty($_POST['assigned_to']) ? trim($_POST['assigned_to']) : null,
                'project_id_err' => '',
                'title_err' => '',
                'status_err' => '',
                'priority_err' => ''
            ];
            
            // Validate project_id
            if (empty($data['project_id'])) {
                $data['project_id_err'] = 'Please select a project';
            }
            
            // Validate title
            if (empty($data['title'])) {
                $data['title_err'] = 'Please enter task title';
            }
            
            // Validate status
            if (empty($data['status'])) {
                $data['status_err'] = 'Please select a status';
            }
            
            // Validate priority
            if (empty($data['priority'])) {
                $data['priority_err'] = 'Please select a priority';
            }
            
            // Check if there are no errors
            if (empty($data['project_id_err']) && empty($data['title_err']) && 
                empty($data['status_err']) && empty($data['priority_err'])) {
                
                // Update task
                // Placeholder: $this->taskModel->update($data);
                
                // Set flash message
                // Placeholder: flash('task_message', 'Task updated successfully');
                
                // Redirect to the task details page
                header('Location: /tasks/show/' . $id);
                exit;
            } else {
                // Get all projects for dropdown
                // Placeholder: $projects = $this->projectModel->getAllProjects();
                $projects = [];
                
                // Get all users for assignments
                // Placeholder: $users = $this->userModel->getAllUsers();
                $users = [];
                
                // Load view with errors
                $this->view('tasks/edit', [
                    'title' => 'Edit Task',
                    'task' => (object)$data,
                    'projects' => $projects,
                    'users' => $users
                ]);
            }
        } else {
            // If not POST request, redirect to edit form
            header('Location: /tasks/edit/' . $id);
            exit;
        }
    }
    
    // Delete a task
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the task to find project_id for redirection
            // Placeholder: $task = $this->taskModel->getTaskById($id);
            $task = (object)[
                'project_id' => 1
            ];
            
            // Delete task
            // Placeholder: $this->taskModel->delete($id);
            
            // Set flash message
            // Placeholder: flash('task_message', 'Task deleted successfully');
            
            // Redirect to the project details page
            header('Location: /projects/show/' . $task->project_id);
            exit;
        } else {
            // If not POST request, redirect to tasks index
            header('Location: /tasks');
            exit;
        }
    }
} 