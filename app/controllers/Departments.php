<?php
class Departments extends Controller {
    private $departmentModel;
    private $projectModel;
    private $settingModel;
    
    public function __construct() {
        // Ensure user is logged in
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
            header('Location: /auth/login');
            exit;
        }
        
        // Load db helper for currency functionality
        require_once APPROOT . '/app/helpers/db_helper.php';
        
        // Check and add currency column if needed
        add_currency_column();
        
        // Load models
        $this->departmentModel = $this->model('Department');
        $this->projectModel = $this->model('Project');
        $this->settingModel = $this->model('Setting');
    }
    
    // Display all departments
    public function index() {
        $departments = $this->departmentModel->getAllDepartments();
        $budgetStats = $this->departmentModel->getBudgetStats();
        
        // Get currency settings
        $currency = $this->settingModel->getCurrency();
        
        $this->view('departments/index', [
            'title' => 'Departments',
            'departments' => $departments,
            'budgetStats' => $budgetStats,
            'currency' => $currency
        ]);
    }
    
    // Show single department
    public function show($id) {
        $department = $this->departmentModel->getDepartmentById($id);
        
        if (!$department) {
            // If department not found, redirect to departments index
            header('Location: /departments');
            exit;
        }
        
        $projects = $this->departmentModel->getDepartmentProjects($id);
        
        // Get currency settings
        $currency = $this->settingModel->getCurrency();
        
        $this->view('departments/show', [
            'title' => $department->name,
            'department' => $department,
            'projects' => $projects,
            'currency' => $currency
        ]);
    }
    
    // Display create form
    public function create() {
        // Get available currencies
        $currencies = $this->departmentModel->getCurrencySymbols();
        
        // Get currency settings
        $currency = $this->settingModel->getCurrency();
        
        // Default values
        $data = [
            'name' => '',
            'description' => '',
            'budget' => '',
            'currency' => 'USD',
            'currencies' => $currencies,
            'name_err' => '',
            'description_err' => '',
            'budget_err' => ''
        ];
        
        // Load view
        $this->view('departments/create', [
            'title' => 'Create Department',
            'data' => $data,
            'currency' => $currency
        ]);
    }
    
    // Process the create form
    public function store() {
        // Process form data if POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Get available currencies
            $currencies = $this->departmentModel->getCurrencySymbols();
            
            // Initialize data array
            $data = [
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description']),
                'budget' => floatval(str_replace(['$', '£', '€', ','], '', $_POST['budget'])),
                'currency' => $_POST['currency'] ?? 'USD',
                'currencies' => $currencies,
                'name_err' => '',
                'description_err' => '',
                'budget_err' => ''
            ];
            
            // Validate name
            if (empty($data['name'])) {
                $data['name_err'] = 'Please enter department name';
            }
            
            // Validate description
            if (empty($data['description'])) {
                $data['description_err'] = 'Please enter department description';
            }
            
            // Validate budget
            if (empty($data['budget']) || $data['budget'] <= 0) {
                $data['budget_err'] = 'Please enter a valid budget amount';
            }
            
            // Validate currency
            if (!array_key_exists($data['currency'], $currencies)) {
                $data['currency'] = 'USD'; // Default to USD if invalid
            }
            
            // Check if there are no errors
            if (empty($data['name_err']) && empty($data['description_err']) && empty($data['budget_err'])) {
                // Create department
                $this->departmentModel->create($data);
                
                // Set flash message
                // Placeholder: flash('department_message', 'Department created successfully');
                
                // Redirect to departments index
                header('Location: /departments');
                exit;
            } else {
                // Load view with errors
                $this->view('departments/create', [
                    'title' => 'Create Department',
                    'data' => $data
                ]);
            }
        } else {
            $this->create();
        }
    }
    
    // Show edit form
    public function edit($id) {
        $department = $this->departmentModel->getDepartmentById($id);
        
        if (!$department) {
            // If department not found, redirect to departments index
            header('Location: /departments');
            exit;
        }
        
        // Get available currencies
        $currencies = $this->departmentModel->getCurrencySymbols();
        
        // Get currency settings
        $currency = $this->settingModel->getCurrency();
        
        // Add currencies to department object
        $department->currencies = $currencies;
        
        $this->view('departments/edit', [
            'title' => 'Edit Department',
            'department' => $department,
            'currency' => $currency
        ]);
    }
    
    // Process the edit form
    public function update($id) {
        // Process form data if POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Get available currencies
            $currencies = $this->departmentModel->getCurrencySymbols();
            
            // Initialize data array
            $data = [
                'id' => $id,
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description']),
                'budget' => floatval(str_replace(['$', '£', '€', ','], '', $_POST['budget'])),
                'currency' => $_POST['currency'] ?? 'USD',
                'currencies' => $currencies,
                'name_err' => '',
                'description_err' => '',
                'budget_err' => ''
            ];
            
            // Validate name
            if (empty($data['name'])) {
                $data['name_err'] = 'Please enter department name';
            }
            
            // Validate description
            if (empty($data['description'])) {
                $data['description_err'] = 'Please enter department description';
            }
            
            // Validate budget
            if (empty($data['budget']) || $data['budget'] <= 0) {
                $data['budget_err'] = 'Please enter a valid budget amount';
            }
            
            // Validate currency
            if (!array_key_exists($data['currency'], $currencies)) {
                $data['currency'] = 'USD'; // Default to USD if invalid
            }
            
            // Check if there are no errors
            if (empty($data['name_err']) && empty($data['description_err']) && empty($data['budget_err'])) {
                // Update department
                $this->departmentModel->update($data);
                
                // Set flash message
                // Placeholder: flash('department_message', 'Department updated successfully');
                
                // Redirect to department details
                header('Location: /departments/show/' . $id);
                exit;
            } else {
                // Load view with errors
                $this->view('departments/edit', [
                    'title' => 'Edit Department',
                    'department' => (object)$data
                ]);
            }
        } else {
            // If not POST request, redirect to edit form
            header('Location: /departments/edit/' . $id);
            exit;
        }
    }
    
    // Delete a department
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if department has associated projects
            $projects = $this->departmentModel->getDepartmentProjects($id);
            
            if (!empty($projects)) {
                // Department has projects, don't delete
                // Placeholder: flash('department_message', 'Cannot delete department with associated projects', 'alert alert-danger');
                header('Location: /departments/show/' . $id);
                exit;
            }
            
            // Delete department
            $this->departmentModel->delete($id);
            
            // Set flash message
            // Placeholder: flash('department_message', 'Department deleted successfully');
            
            // Redirect to departments index
            header('Location: /departments');
            exit;
        } else {
            // If not POST request, redirect to departments index
            header('Location: /departments');
            exit;
        }
    }
}
?> 