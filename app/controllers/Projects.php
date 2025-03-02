<?php
class Projects extends Controller {
    private $projectModel;
    private $taskModel;
    private $noteModel;
    private $departmentModel;
    
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
        $this->taskModel = $this->model('Task');
        $this->noteModel = $this->model('Note');
        $this->departmentModel = $this->model('Department');
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
        // Get all departments for the dropdown
        $departments = $this->departmentModel->getAllDepartments();
        
        $this->view('projects/create', [
            'title' => 'Create Project',
            'departments' => $departments
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
                'department_id' => isset($_POST['department_id']) ? intval($_POST['department_id']) : null,
                'budget' => floatval(str_replace(['$', ','], '', $_POST['budget'])),
                'title_err' => '',
                'description_err' => '',
                'start_date_err' => '',
                'end_date_err' => '',
                'status_err' => '',
                'department_id_err' => '',
                'budget_err' => ''
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
            
            // Validate department
            if (empty($data['department_id'])) {
                $data['department_id_err'] = 'Please select a department';
            }
            
            // Validate budget
            if (empty($data['budget'])) {
                $data['budget_err'] = 'Please enter a budget amount';
            } elseif ($data['budget'] <= 0) {
                $data['budget_err'] = 'Budget must be greater than zero';
            }
            
            // Check if there are no errors
            if (empty($data['title_err']) && empty($data['description_err']) && 
                empty($data['start_date_err']) && empty($data['end_date_err']) && 
                empty($data['status_err']) && empty($data['department_id_err']) && 
                empty($data['budget_err'])) {
                
                // Add the user_id from the session
                $data['user_id'] = $_SESSION['user_id'];
                
                // Create project
                $this->projectModel->create($data);
                
                // Set flash message
                flash('project_message', 'Project created successfully');
                
                // Redirect to projects index
                header('Location: /projects');
                exit;
            } else {
                // Load view with errors
                $this->view('projects/create', [
                    'title' => 'Create Project',
                    'data' => $data,
                    'departments' => $this->departmentModel->getAllDepartments()
                ]);
            }
        } else {
            // If not POST request, redirect to create form
            header('Location: /projects/create');
            exit;
        }
    }
    
    // Show a single project
    public function viewProject($id) {
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            flash('project_error', 'Project not found', 'alert-danger');
            redirect('projects');
        }
        
        // Get tasks for this project
        $tasks = $this->taskModel->getTasksByProject($id);
        
        // Get notes for this project
        $notes = $this->noteModel->getNotesByReference('project', $id);
        
        $data = [
            'project' => $project,
            'tasks' => $tasks,
            'notes' => $notes,
            'type' => 'project',
            'reference_id' => $id
        ];
        
        $this->view('projects/view', $data);
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
        
        // Get all departments for the dropdown
        $departments = $this->departmentModel->getAllDepartments();
        
        $this->view('projects/edit', [
            'title' => 'Edit Project',
            'project' => $project,
            'departments' => $departments
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
                'department_id' => isset($_POST['department_id']) ? intval($_POST['department_id']) : null,
                'budget' => floatval(str_replace(['$', ','], '', $_POST['budget'])),
                'title_err' => '',
                'description_err' => '',
                'start_date_err' => '',
                'end_date_err' => '',
                'status_err' => '',
                'department_id_err' => '',
                'budget_err' => ''
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
            
            // Validate department
            if (empty($data['department_id'])) {
                $data['department_id_err'] = 'Please select a department';
            }
            
            // Validate budget
            if (empty($data['budget'])) {
                $data['budget_err'] = 'Please enter a budget amount';
            } elseif ($data['budget'] <= 0) {
                $data['budget_err'] = 'Budget must be greater than zero';
            }
            
            // Check if there are no errors
            if (empty($data['title_err']) && empty($data['description_err']) && 
                empty($data['start_date_err']) && empty($data['end_date_err']) && 
                empty($data['status_err']) && empty($data['department_id_err']) && 
                empty($data['budget_err'])) {
                
                // Update project
                $this->projectModel->update($data);
                
                // Set flash message
                flash('project_message', 'Project updated successfully');
                
                // Redirect to project details
                header('Location: /projects/viewProject/' . $id);
                exit;
            } else {
                // Load view with errors
                $this->view('projects/edit', [
                    'title' => 'Edit Project',
                    'project' => (object)$data,
                    'departments' => $this->departmentModel->getAllDepartments()
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
            flash('project_message', 'Project deleted successfully');
            
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