<?php

class Employees extends Controller {
    private $employeeModel;
    private $userModel;
    
    public function __construct() {
        // Check if user is logged in and has admin or manager role
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Redirect if not logged in or not an admin/manager
        if (!isset($_SESSION['is_logged_in']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
            header('Location: /home');
            exit;
        }
        
        // Load models
        $this->employeeModel = $this->model('Employee');
        $this->userModel = $this->model('User');
    }
    
    /**
     * Display list of employees with performance data
     */
    public function index() {
        // Get all employees with management data
        $employees = $this->employeeModel->getAllEmployees();
        
        // Get performance statistics
        $stats = $this->employeeModel->getPerformanceStats();
        
        $data = [
            'title' => 'Employee Management',
            'employees' => $employees,
            'stats' => $stats
        ];
        
        $this->view('employees/index', $data);
    }
    
    /**
     * View employee details
     * 
     * @param int $userId Employee/user ID
     */
    public function viewEmployee($userId = null) {
        if (!$userId) {
            flash('employee_error', 'Invalid employee ID', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Get employee data
        $employee = $this->employeeModel->getEmployeeById($userId);
        
        if (!$employee) {
            flash('employee_error', 'Employee not found', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Get absence records
        $absences = $this->employeeModel->getAbsenceRecords($userId);
        
        // Get projects the employee is working on
        $projectModel = $this->model('Project');
        $projects = $projectModel->getProjectsForEmployee($userId);
        
        // Get performance notes
        $notes = $this->employeeModel->getPerformanceNotes($userId);
        
        $data = [
            'title' => 'Employee Profile',
            'employee' => $employee,
            'absences' => $absences,
            'projects' => $projects,
            'notes' => $notes
        ];
        
        $this->view('employees/view', $data);
    }
    
    /**
     * Create new employee management record
     */
    public function create() {
        // Get users without management data
        $users = $this->employeeModel->getEmployeesWithoutManagementData();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form data
            $data = [
                'user_id' => filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT),
                'performance_rating' => filter_input(INPUT_POST, 'performance_rating', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'notes' => htmlspecialchars($_POST['notes'] ?? ''),
                'last_review_date' => $_POST['last_review_date'] ?? null,
                'next_review_date' => $_POST['next_review_date'] ?? null
            ];
            
            // Validate input
            if (empty($data['user_id'])) {
                flash('employee_error', 'Please select a user', 'alert alert-danger');
                redirect('/employees/create');
            }
            
            // Create record
            if ($this->employeeModel->createEmployeeRecord($data)) {
                // Update task counts
                $this->employeeModel->updateTaskCounts($data['user_id']);
                
                flash('employee_success', 'Employee record created successfully', 'alert alert-success');
                redirect('/employees');
            } else {
                flash('employee_error', 'Something went wrong', 'alert alert-danger');
                redirect('/employees/create');
            }
        } else {
            $data = [
                'title' => 'Create Employee Record',
                'users' => $users
            ];
            
            $this->view('employees/create', $data);
        }
    }
    
    /**
     * Edit employee management record
     * 
     * @param int $userId Employee/user ID
     */
    public function edit($userId = null) {
        if (!$userId) {
            flash('employee_error', 'Invalid employee ID', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Get employee data
        $employee = $this->employeeModel->getEmployeeById($userId);
        
        if (!$employee) {
            flash('employee_error', 'Employee not found', 'alert alert-danger');
            redirect('/employees');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form data
            $data = [
                'user_id' => $userId,
                'performance_rating' => filter_input(INPUT_POST, 'performance_rating', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'tasks_completed' => filter_input(INPUT_POST, 'tasks_completed', FILTER_SANITIZE_NUMBER_INT),
                'tasks_pending' => filter_input(INPUT_POST, 'tasks_pending', FILTER_SANITIZE_NUMBER_INT),
                'notes' => htmlspecialchars($_POST['notes'] ?? ''),
                'last_review_date' => $_POST['last_review_date'] ?? null,
                'next_review_date' => $_POST['next_review_date'] ?? null
            ];
            
            // Update record
            if ($this->employeeModel->updateEmployeeRecord($data, $_SESSION['user_id'] ?? null)) {
                flash('employee_success', 'Employee record updated successfully', 'alert alert-success');
                redirect('/employees/viewEmployee/' . $userId);
            } else {
                flash('employee_error', 'Something went wrong', 'alert alert-danger');
                $this->view('employees/edit', [
                    'title' => 'Edit Employee Record',
                    'employee' => $employee
                ]);
            }
        } else {
            $data = [
                'title' => 'Edit Employee Record',
                'employee' => $employee
            ];
            
            $this->view('employees/edit', $data);
        }
    }
    
    /**
     * Delete employee management record
     * 
     * @param int $userId Employee/user ID
     */
    public function delete($userId = null) {
        if (!$userId) {
            flash('employee_error', 'Invalid employee ID', 'alert alert-danger');
            redirect('/employees');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Delete record
            if ($this->employeeModel->deleteEmployeeRecord($userId)) {
                flash('employee_success', 'Employee record deleted successfully', 'alert alert-success');
            } else {
                flash('employee_error', 'Something went wrong', 'alert alert-danger');
            }
        }
        
        redirect('/employees');
    }
    
    /**
     * Add absence record
     * 
     * @param int $userId Employee/user ID
     */
    public function addAbsence($userId = null) {
        if (!$userId) {
            flash('employee_error', 'Invalid employee ID', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Get employee data
        $employee = $this->employeeModel->getEmployeeById($userId);
        
        if (!$employee) {
            flash('employee_error', 'Employee not found', 'alert alert-danger');
            redirect('/employees');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form data
            $data = [
                'user_id' => $userId,
                'start_date' => $_POST['start_date'] ?? null,
                'end_date' => $_POST['end_date'] ?? null,
                'reason' => htmlspecialchars($_POST['reason'] ?? ''),
                'approved_by' => $_SESSION['user_id'] ?? null,
                'approved_at' => date('Y-m-d H:i:s')
            ];
            
            // Validate input
            if (empty($data['start_date']) || empty($data['end_date'])) {
                flash('absence_error', 'Please provide start and end dates', 'alert alert-danger');
                redirect('/employees/viewEmployee/' . $userId);
            }
            
            // Add absence record
            if ($this->employeeModel->addAbsenceRecord($data)) {
                flash('absence_success', 'Absence record added successfully', 'alert alert-success');
            } else {
                flash('absence_error', 'Something went wrong', 'alert alert-danger');
            }
            
            redirect('/employees/viewEmployee/' . $userId);
        } else {
            $data = [
                'title' => 'Add Absence Record',
                'employee' => $employee
            ];
            
            $this->view('employees/add_absence', $data);
        }
    }
    
    /**
     * Delete absence record
     * 
     * @param int $absenceId Absence record ID
     * @param int $userId Employee/user ID
     */
    public function deleteAbsence($absenceId = null, $userId = null) {
        if (!$absenceId || !$userId) {
            flash('absence_error', 'Invalid absence record', 'alert alert-danger');
            redirect('/employees');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Delete absence record
            if ($this->employeeModel->deleteAbsenceRecord($absenceId, $userId)) {
                flash('absence_success', 'Absence record deleted successfully', 'alert alert-success');
            } else {
                flash('absence_error', 'Something went wrong', 'alert alert-danger');
            }
        }
        
        redirect('/employees/viewEmployee/' . $userId);
    }
    
    /**
     * Update task counts for an employee
     * 
     * @param int $userId Employee/user ID
     */
    public function updateTasks($userId = null) {
        if (!$userId) {
            flash('employee_error', 'Invalid employee ID', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Update task counts
        if ($this->employeeModel->updateTaskCounts($userId)) {
            flash('employee_success', 'Task counts updated successfully', 'alert alert-success');
        } else {
            flash('employee_error', 'Something went wrong', 'alert alert-danger');
        }
        
        redirect('/employees/viewEmployee/' . $userId);
    }
    
    /**
     * View rating history for an employee
     * 
     * @param int $userId Employee/user ID
     */
    public function ratingHistory($userId = null) {
        if (!$userId) {
            flash('employee_error', 'Invalid employee ID', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Get employee data
        $employee = $this->employeeModel->getEmployeeById($userId);
        
        if (!$employee) {
            flash('employee_error', 'Employee not found', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Get rating history
        $ratingHistory = $this->employeeModel->getRatingHistory($userId);
        
        $data = [
            'title' => 'Rating History',
            'employee' => $employee,
            'ratingHistory' => $ratingHistory
        ];
        
        $this->view('employees/rating_history', $data);
    }
    
    /**
     * Add performance notes for an employee
     * 
     * @param int $userId Employee/user ID
     */
    public function addNote($userId = null) {
        if (!$userId) {
            flash('employee_error', 'Invalid employee ID', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Get employee data
        $employee = $this->employeeModel->getEmployeeById($userId);
        
        if (!$employee) {
            flash('employee_error', 'Employee not found', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Process form if submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Get form data
            $data = [
                'user_id' => $userId,
                'note_text' => trim($_POST['note_text']),
                'note_type' => trim($_POST['note_type']),
                'changed_by' => $_SESSION['user_id']
            ];
            
            // Validate input
            if (empty($data['note_text'])) {
                flash('employee_error', 'Please enter a note', 'alert alert-danger');
                redirect('/employees/addNote/' . $userId);
            }
            
            // Add the note
            if ($this->employeeModel->addPerformanceNote($data)) {
                flash('employee_success', 'Performance note added successfully', 'alert alert-success');
                redirect('/employees/viewEmployee/' . $userId);
            } else {
                flash('employee_error', 'Error adding performance note', 'alert alert-danger');
                redirect('/employees/addNote/' . $userId);
            }
        }
        
        // Display add note form
        $data = [
            'title' => 'Add Performance Note',
            'employee' => $employee
        ];
        
        $this->view('employees/add_note', $data);
    }
}
?> 