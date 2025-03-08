<?php
class Tasks extends Controller {
    private $taskModel;
    private $projectModel;
    private $userModel;
    private $noteModel;
    
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
        $this->noteModel = $this->model('Note');
        
        // Create task_users table if it doesn't exist
        $this->taskModel->createTaskUsersTable();
    }
    
    // List all tasks (can be filtered by project)
    public function index() {
        // Get filter parameters from query string
        $projectId = isset($_GET['project_id']) ? trim($_GET['project_id']) : null;
        $status = isset($_GET['status']) ? trim($_GET['status']) : null;
        $priority = isset($_GET['priority']) ? trim($_GET['priority']) : null;
        
        // Build filters array
        $filters = [];
        if ($projectId) {
            $filters['project_id'] = $projectId;
        }
        if ($status) {
            $filters['status'] = $status;
        }
        if ($priority) {
            $filters['priority'] = $priority;
        }
        
        // Get tasks based on filters
        if (!empty($filters)) {
            $tasks = $this->taskModel->getTasksByFilters($filters);
            
            // Build title based on filters
            $title = 'Filtered Tasks';
            if ($projectId) {
                $project = $this->projectModel->getProjectById($projectId);
                if ($project) {
                    $title = 'Tasks for ' . $project->title;
                }
            }
        } else {
            // Get all tasks if no filters
            $tasks = $this->taskModel->getAllTasks();
            $title = 'All Tasks';
        }
        
        // Get all projects for the project filter dropdown
        $projects = $this->projectModel->getAllProjects();
        
        $this->view('tasks/index', [
            'title' => $title,
            'tasks' => $tasks,
            'projects' => $projects
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
                flash('task_message', 'Task created successfully');
                
                // Redirect to the project details page
                header('Location: /projects/viewProject/' . $data['project_id']);
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
        $task = $this->taskModel->getTaskById($id);
        
        if (!$task) {
            flash('task_error', 'Task not found', 'alert-danger');
            redirect('tasks');
        }
        
        // Get project details
        $project = $this->projectModel->getProjectById($task->project_id);
        
        // Get notes for this task
        $notes = $this->noteModel->getNotesByReference('task', $id);
        
        // Get users assigned to this task
        $assignedUsers = $this->taskModel->getTaskUsers($id);
        
        $data = [
            'task' => $task,
            'project' => $project,
            'notes' => $notes,
            'type' => 'task',
            'reference_id' => $id,
            'assigned_users' => $assignedUsers
        ];
        
        $this->view('tasks/show', $data);
    }
    
    // Show form to edit task
    public function edit($id) {
        // Get task by ID
        $this->taskModel = $this->model('Task');
        $task = $this->taskModel->getTaskById($id);
        
        if (!$task) {
            flash('task_error', 'Task not found', 'alert alert-danger');
            redirect('tasks');
        }
        
        // Get all projects for dropdown
        $this->projectModel = $this->model('Project');
        $projects = $this->projectModel->getAllProjects();
        
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
    
    /**
     * Show form to manage task assignments
     * 
     * @param int $id Task ID
     * @return void
     */
    public function manageAssignments($id) {
        $task = $this->taskModel->getTaskById($id);
        
        if (!$task) {
            flash('task_error', 'Task not found', 'alert-danger');
            redirect('tasks');
        }
        
        // Get the project to find users that can be assigned
        $project = $this->projectModel->getProjectById($task->project_id);
        
        // Get users already assigned to this task
        $assignedUsers = $this->taskModel->getTaskUsers($id);
        
        // Get users assigned to the project
        $projectUsers = $this->projectModel->getProjectUsers($project->id);
        
        // Create a map of assigned user IDs for easy lookup
        $assignedUserIds = [];
        foreach ($assignedUsers as $user) {
            $assignedUserIds[] = $user->user_id;
        }
        
        $this->view('tasks/assignments', [
            'title' => 'Manage Assignments - ' . $task->title,
            'task' => $task,
            'project' => $project,
            'project_users' => $projectUsers,
            'assigned_users' => $assignedUsers,
            'assigned_user_ids' => $assignedUserIds
        ]);
    }
    
    /**
     * Process form to assign users to a task
     * 
     * @param int $id Task ID
     * @return void
     */
    public function assignUsers($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tasks/manageAssignments/' . $id);
        }
        
        $task = $this->taskModel->getTaskById($id);
        
        if (!$task) {
            flash('task_error', 'Task not found', 'alert-danger');
            redirect('tasks');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Get the selected user IDs
        $userIds = isset($_POST['user_ids']) ? $_POST['user_ids'] : [];
        
        // Start transaction
        $success = true;
        
        // First, remove all existing assignments
        $currentlyAssigned = $this->taskModel->getTaskUsers($id);
        $currentlyAssignedIds = array_map(function($user) {
            return $user->user_id;
        }, $currentlyAssigned);
        
        // Find users to remove (those who were assigned but not in the new selection)
        $usersToRemove = array_diff($currentlyAssignedIds, $userIds);
        foreach ($usersToRemove as $userId) {
            $success = $success && $this->taskModel->removeUserFromTask($id, $userId);
        }
        
        // Now assign selected users
        foreach ($userIds as $userId) {
            $success = $success && $this->taskModel->assignUserToTask($id, $userId);
        }
        
        if ($success) {
            flash('task_message', 'Task assignments updated successfully');
        } else {
            flash('task_error', 'Error updating task assignments', 'alert-danger');
        }
        
        redirect('tasks/manageAssignments/' . $id);
    }
    
    /**
     * Remove a user from a task
     * 
     * @param int $taskId Task ID
     * @param int $userId User ID
     * @return void
     */
    public function removeUser($taskId, $userId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tasks/manageAssignments/' . $taskId);
        }
        
        $task = $this->taskModel->getTaskById($taskId);
        
        if (!$task) {
            flash('task_error', 'Task not found', 'alert-danger');
            redirect('tasks');
        }
        
        if ($this->taskModel->removeUserFromTask($taskId, $userId)) {
            flash('task_message', 'User removed from task successfully');
        } else {
            flash('task_error', 'Error removing user from task', 'alert-danger');
        }
        
        redirect('tasks/manageAssignments/' . $taskId);
    }
} 