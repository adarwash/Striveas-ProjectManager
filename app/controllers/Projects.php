<?php
class Projects extends Controller {
    private $projectModel;
    
    public function __construct() {
        // Check if user is logged in
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
            header('Location: /auth');
            exit;
        }
        
        // Load the project model
        $this->projectModel = $this->model('Project');
    }
    
    // List all projects
    public function index() {
        // Get all projects
        $projects = $this->projectModel->getAllProjects(); // Get real projects from database
        
        $this->view('projects/index', [
            'title' => 'Projects',
            'projects' => $projects
        ]);
    }
    
    // Show the form to create a new project
    public function create() {
        $this->view('projects/create', [
            'title' => 'Create Project'
        ]);
    }
    
    // Process the new project form
    public function store() {
        // Process form data if POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Initialize data array
            $data = [
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'start_date' => trim($_POST['start_date']),
                'end_date' => trim($_POST['end_date']),
                'status' => trim($_POST['status']),
                'title_err' => '',
                'description_err' => '',
                'start_date_err' => '',
                'end_date_err' => '',
                'status_err' => ''
            ];
            
            // Validate title
            if (empty($data['title'])) {
                $data['title_err'] = 'Please enter project title';
            }
            
            // Validate description
            if (empty($data['description'])) {
                $data['description_err'] = 'Please enter project description';
            }
            
            // Validate dates
            if (empty($data['start_date'])) {
                $data['start_date_err'] = 'Please select a start date';
            }
            
            if (empty($data['end_date'])) {
                $data['end_date_err'] = 'Please select an end date';
            } elseif ($data['end_date'] < $data['start_date']) {
                $data['end_date_err'] = 'End date cannot be before start date';
            }
            
            // Validate status
            if (empty($data['status'])) {
                $data['status_err'] = 'Please select a status';
            }
            
            // Check if there are no errors
            if (empty($data['title_err']) && empty($data['description_err']) && 
                empty($data['start_date_err']) && empty($data['end_date_err']) && 
                empty($data['status_err'])) {
                
                // Add the user_id from the session
                $data['user_id'] = $_SESSION['user_id'];
                
                // Create project
                $this->projectModel->create($data);
                
                // Set flash message
                // Placeholder: flash('project_message', 'Project created successfully');
                
                // Redirect to projects index
                header('Location: /projects');
                exit;
            } else {
                // Load view with errors
                $this->view('projects/create', [
                    'title' => 'Create Project',
                    'data' => $data
                ]);
            }
        } else {
            // If not POST request, redirect to create form
            header('Location: /projects/create');
            exit;
        }
    }
    
    // Show a single project
    public function show($id) {
        // Get project by ID
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            // If project not found, redirect to projects index
            header('Location: /projects');
            exit;
        }
        
        // Get tasks associated with this project
        $tasks = $this->projectModel->getProjectTasks($id);
        
        $this->view('projects/show', [
            'title' => $project->title,
            'project' => $project,
            'tasks' => $tasks
        ]);
    }
    
    // Show form to edit project
    public function edit($id) {
        // Get project by ID
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            // If project not found, redirect to projects index
            header('Location: /projects');
            exit;
        }
        
        $this->view('projects/edit', [
            'title' => 'Edit Project',
            'project' => $project
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
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'start_date' => trim($_POST['start_date']),
                'end_date' => trim($_POST['end_date']),
                'status' => trim($_POST['status']),
                'title_err' => '',
                'description_err' => '',
                'start_date_err' => '',
                'end_date_err' => '',
                'status_err' => ''
            ];
            
            // Validate title
            if (empty($data['title'])) {
                $data['title_err'] = 'Please enter project title';
            }
            
            // Validate description
            if (empty($data['description'])) {
                $data['description_err'] = 'Please enter project description';
            }
            
            // Validate dates
            if (empty($data['start_date'])) {
                $data['start_date_err'] = 'Please select a start date';
            }
            
            if (empty($data['end_date'])) {
                $data['end_date_err'] = 'Please select an end date';
            } elseif ($data['end_date'] < $data['start_date']) {
                $data['end_date_err'] = 'End date cannot be before start date';
            }
            
            // Validate status
            if (empty($data['status'])) {
                $data['status_err'] = 'Please select a status';
            }
            
            // Check if there are no errors
            if (empty($data['title_err']) && empty($data['description_err']) && 
                empty($data['start_date_err']) && empty($data['end_date_err']) && 
                empty($data['status_err'])) {
                
                // Update project
                $this->projectModel->update($data);
                
                // Set flash message
                // Placeholder: flash('project_message', 'Project updated successfully');
                
                // Redirect to project details
                header('Location: /projects/show/' . $id);
                exit;
            } else {
                // Load view with errors
                $this->view('projects/edit', [
                    'title' => 'Edit Project',
                    'project' => (object)$data
                ]);
            }
        } else {
            // If not POST request, redirect to edit form
            header('Location: /projects/edit/' . $id);
            exit;
        }
    }
    
    // Delete a project
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Delete project
            $this->projectModel->delete($id);
            
            // Set flash message
            // Placeholder: flash('project_message', 'Project deleted successfully');
            
            // Redirect to projects index
            header('Location: /projects');
            exit;
        } else {
            // If not POST request, redirect to projects index
            header('Location: /projects');
            exit;
        }
    }
} 