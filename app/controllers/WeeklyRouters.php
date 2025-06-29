<?php
/**
 * WeeklyRouters Controller
 * Handles weekly router schedules for IT managers and technicians
 */
class WeeklyRouters extends Controller {
    private $weeklyRouterModel;
    private $userModel;
    private $activityLogModel;
    
    public function __construct() {
        // Check if user is logged in
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
            redirect('auth/login');
        }
        
        // Load models
        $this->weeklyRouterModel = $this->model('WeeklyRouter');
        $this->userModel = $this->model('User');
        $this->activityLogModel = $this->model('ActivityLog');
    }
    
    /**
     * Main dashboard - shows different views based on user role
     */
    public function index() {
        $userRole = $_SESSION['role'] ?? 'user';
        
        if ($userRole === 'technician' || $userRole === 'Technician') {
            // Redirect technicians to their dashboard
            $this->technicianDashboard();
        } else {
            // Show manager view for IT managers and admins
            $this->managerDashboard();
        }
    }
    
    /**
     * Technician dashboard - shows assigned router schedules
     */
    public function technicianDashboard() {
        $userId = $_SESSION['user_id'];
        
        // Get current week schedules
        $currentWeekSchedules = $this->weeklyRouterModel->getCurrentWeekSchedulesForTechnician($userId);
        
        // Get upcoming schedules (next 4 weeks)
        $nextMonth = date('Y-m-d', strtotime('+4 weeks'));
        $upcomingSchedules = $this->weeklyRouterModel->getRouterSchedulesForTechnician($userId, date('Y-m-d'), $nextMonth);
        
        // Get statistics
        $stats = $this->weeklyRouterModel->getRouterScheduleStats($userId);
        
        $this->view('weekly_routers/technician_dashboard', [
            'title' => 'My Router Schedules',
            'current_week_schedules' => $currentWeekSchedules,
            'upcoming_schedules' => $upcomingSchedules,
            'stats' => $stats
        ]);
    }
    
    /**
     * Manager dashboard - shows all router schedules and management options
     */
    public function managerDashboard() {
        // Check if user has manager/admin privileges
        $userRole = $_SESSION['role'] ?? 'user';
        if (!in_array($userRole, ['admin', 'manager', 'it_manager', 'IT Manager'])) {
            flash('error', 'You do not have permission to access this page', 'alert-danger');
            redirect('dashboard');
        }
        
        // Get filters from request
        $filters = [];
        if (!empty($_GET['technician_id'])) {
            $filters['technician_id'] = $_GET['technician_id'];
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (!empty($_GET['week_start'])) {
            $filters['week_start'] = $_GET['week_start'];
        }
        if (!empty($_GET['week_end'])) {
            $filters['week_end'] = $_GET['week_end'];
        }
        
        // Get all router schedules with filters
        $routerSchedules = $this->weeklyRouterModel->getAllRouterSchedules($filters);
        
        // Get technicians for filter dropdown
        $technicians = $this->weeklyRouterModel->getTechnicians();
        
        // Get overall statistics
        $stats = $this->weeklyRouterModel->getRouterScheduleStats();
        
        $this->view('weekly_routers/manager_dashboard', [
            'title' => 'Router Schedule Management',
            'router_schedules' => $routerSchedules,
            'technicians' => $technicians,
            'stats' => $stats,
            'filters' => $filters
        ]);
    }
    
    /**
     * Show form to create a new router schedule (IT Manager only)
     */
    public function create() {
        // Check if user has manager/admin privileges
        $userRole = $_SESSION['role'] ?? 'user';
        if (!in_array($userRole, ['admin', 'manager', 'it_manager', 'IT Manager'])) {
            flash('error', 'You do not have permission to access this page', 'alert-danger');
            redirect('weekly_routers');
        }
        
        // Get technicians for assignment dropdown
        $technicians = $this->weeklyRouterModel->getTechnicians();
        
        $this->view('weekly_routers/create', [
            'title' => 'Create Router Schedule',
            'technicians' => $technicians
        ]);
    }
    
    /**
     * Process the create router schedule form
     */
    public function store() {
        // Check if user has manager/admin privileges
        $userRole = $_SESSION['role'] ?? 'user';
        if (!in_array($userRole, ['admin', 'manager', 'it_manager', 'IT Manager'])) {
            flash('error', 'You do not have permission to perform this action', 'alert-danger');
            redirect('weekly_routers');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('weekly_routers/create');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Initialize data array
        $data = [
            'router_name' => trim($_POST['router_name']),
            'router_ip' => trim($_POST['router_ip']),
            'location' => trim($_POST['location']),
            'assigned_technician_id' => !empty($_POST['assigned_technician_id']) ? intval($_POST['assigned_technician_id']) : null,
            'week_start_date' => trim($_POST['week_start_date']),
            'week_end_date' => trim($_POST['week_end_date']),
            'maintenance_type' => trim($_POST['maintenance_type']),
            'priority' => trim($_POST['priority']),
            'description' => trim($_POST['description']),
            'estimated_hours' => !empty($_POST['estimated_hours']) ? floatval($_POST['estimated_hours']) : null,
            'created_by' => $_SESSION['user_id']
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['router_name'])) {
            $errors['router_name'] = 'Router name is required';
        }
        
        if (empty($data['router_ip'])) {
            $errors['router_ip'] = 'Router IP is required';
        } elseif (!filter_var($data['router_ip'], FILTER_VALIDATE_IP)) {
            $errors['router_ip'] = 'Please enter a valid IP address';
        }
        
        if (empty($data['location'])) {
            $errors['location'] = 'Location is required';
        }
        
        if (empty($data['week_start_date'])) {
            $errors['week_start_date'] = 'Week start date is required';
        }
        
        if (empty($data['week_end_date'])) {
            $errors['week_end_date'] = 'Week end date is required';
        } elseif (!empty($data['week_start_date']) && $data['week_end_date'] < $data['week_start_date']) {
            $errors['week_end_date'] = 'End date cannot be before start date';
        }
        
        if (empty($data['maintenance_type'])) {
            $errors['maintenance_type'] = 'Maintenance type is required';
        }
        
        if (empty($data['priority'])) {
            $errors['priority'] = 'Priority is required';
        }
        
        // If no errors, create the router schedule
        if (empty($errors)) {
            $routerScheduleId = $this->weeklyRouterModel->create($data);
            
            if ($routerScheduleId) {
                // Log the activity
                $description = sprintf(
                    'Created router schedule for "%s" (%s) at %s for week %s to %s',
                    $data['router_name'],
                    $data['router_ip'],
                    $data['location'],
                    $data['week_start_date'],
                    $data['week_end_date']
                );
                
                $metadata = [
                    'router_schedule_id' => $routerScheduleId,
                    'router_name' => $data['router_name'],
                    'router_ip' => $data['router_ip'],
                    'location' => $data['location'],
                    'assigned_technician_id' => $data['assigned_technician_id'],
                    'maintenance_type' => $data['maintenance_type'],
                    'priority' => $data['priority']
                ];
                
                $this->activityLogModel->log(
                    $_SESSION['user_id'],
                    'router_schedule',
                    $routerScheduleId,
                    'created',
                    $description,
                    $metadata
                );
                
                flash('success', 'Router schedule created successfully');
                redirect('weekly_routers');
            } else {
                flash('error', 'Failed to create router schedule', 'alert-danger');
                redirect('weekly_routers/create');
            }
        } else {
            // Show form with errors
            $technicians = $this->weeklyRouterModel->getTechnicians();
            
            $this->view('weekly_routers/create', [
                'title' => 'Create Router Schedule',
                'technicians' => $technicians,
                'data' => $data,
                'errors' => $errors
            ]);
        }
    }
    
    /**
     * Show router schedule details
     */
    public function view($id) {
        if (!$id) {
            flash('error', 'No router schedule ID provided', 'alert-danger');
            redirect('weekly_routers');
        }
        
        $routerSchedule = $this->weeklyRouterModel->getRouterScheduleById($id);
        
        if (!$routerSchedule) {
            flash('error', 'Router schedule not found', 'alert-danger');
            redirect('weekly_routers');
        }
        
        // Check if user can view this schedule
        $userRole = $_SESSION['role'] ?? 'user';
        $userId = $_SESSION['user_id'];
        
        if (!in_array($userRole, ['admin', 'manager', 'it_manager', 'IT Manager']) && 
            $routerSchedule['assigned_technician_id'] != $userId) {
            flash('error', 'You do not have permission to view this schedule', 'alert-danger');
            redirect('weekly_routers');
        }
        
        $this->view('weekly_routers/view', [
            'title' => 'Router Schedule Details',
            'router_schedule' => $routerSchedule
        ]);
    }
    
    /**
     * Update router schedule status (for technicians)
     */
    public function updateStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('weekly_routers');
        }
        
        if (!$id) {
            flash('error', 'No router schedule ID provided', 'alert-danger');
            redirect('weekly_routers');
        }
        
        $routerSchedule = $this->weeklyRouterModel->getRouterScheduleById($id);
        
        if (!$routerSchedule) {
            flash('error', 'Router schedule not found', 'alert-danger');
            redirect('weekly_routers');
        }
        
        // Check if user can update this schedule
        $userRole = $_SESSION['role'] ?? 'user';
        $userId = $_SESSION['user_id'];
        
        if (!in_array($userRole, ['admin', 'manager', 'it_manager', 'IT Manager']) && 
            $routerSchedule['assigned_technician_id'] != $userId) {
            flash('error', 'You do not have permission to update this schedule', 'alert-danger');
            redirect('weekly_routers');
        }
        
        // Get form data
        $status = trim($_POST['status']);
        $notes = trim($_POST['notes']) ?: null;
        $actualHours = !empty($_POST['actual_hours']) ? floatval($_POST['actual_hours']) : null;
        
        // Validate status
        $validStatuses = ['Scheduled', 'In Progress', 'Completed', 'Cancelled'];
        if (!in_array($status, $validStatuses)) {
            flash('error', 'Invalid status', 'alert-danger');
            redirect('weekly_routers/view/' . $id);
        }
        
        // Update the status
        $success = $this->weeklyRouterModel->updateStatus($id, $status, $notes, $actualHours);
        
        if ($success) {
            // Log the activity
            $description = sprintf(
                'Updated router schedule status to "%s" for "%s" (%s)',
                $status,
                $routerSchedule['router_name'],
                $routerSchedule['router_ip']
            );
            
            $metadata = [
                'router_schedule_id' => $id,
                'old_status' => $routerSchedule['status'],
                'new_status' => $status,
                'notes' => $notes,
                'actual_hours' => $actualHours
            ];
            
            $this->activityLogModel->log(
                $_SESSION['user_id'],
                'router_schedule',
                $id,
                'updated',
                $description,
                $metadata
            );
            
            flash('success', 'Router schedule status updated successfully');
        } else {
            flash('error', 'Failed to update router schedule status', 'alert-danger');
        }
        
        redirect('weekly_routers/view/' . $id);
    }
    
    /**
     * Show edit form for router schedule (IT Manager only)
     */
    public function edit($id) {
        // Check if user has manager/admin privileges
        $userRole = $_SESSION['role'] ?? 'user';
        if (!in_array($userRole, ['admin', 'manager', 'it_manager', 'IT Manager'])) {
            flash('error', 'You do not have permission to access this page', 'alert-danger');
            redirect('weekly_routers');
        }
        
        if (!$id) {
            flash('error', 'No router schedule ID provided', 'alert-danger');
            redirect('weekly_routers');
        }
        
        $routerSchedule = $this->weeklyRouterModel->getRouterScheduleById($id);
        
        if (!$routerSchedule) {
            flash('error', 'Router schedule not found', 'alert-danger');
            redirect('weekly_routers');
        }
        
        // Get technicians for assignment dropdown
        $technicians = $this->weeklyRouterModel->getTechnicians();
        
        $this->view('weekly_routers/edit', [
            'title' => 'Edit Router Schedule',
            'router_schedule' => $routerSchedule,
            'technicians' => $technicians
        ]);
    }
    
    /**
     * Process the edit router schedule form
     */
    public function update($id) {
        // Check if user has manager/admin privileges
        $userRole = $_SESSION['role'] ?? 'user';
        if (!in_array($userRole, ['admin', 'manager', 'it_manager', 'IT Manager'])) {
            flash('error', 'You do not have permission to perform this action', 'alert-danger');
            redirect('weekly_routers');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('weekly_routers/edit/' . $id);
        }
        
        if (!$id) {
            flash('error', 'No router schedule ID provided', 'alert-danger');
            redirect('weekly_routers');
        }
        
        $routerSchedule = $this->weeklyRouterModel->getRouterScheduleById($id);
        
        if (!$routerSchedule) {
            flash('error', 'Router schedule not found', 'alert-danger');
            redirect('weekly_routers');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Initialize data array
        $data = [
            'router_name' => trim($_POST['router_name']),
            'router_ip' => trim($_POST['router_ip']),
            'location' => trim($_POST['location']),
            'assigned_technician_id' => !empty($_POST['assigned_technician_id']) ? intval($_POST['assigned_technician_id']) : null,
            'week_start_date' => trim($_POST['week_start_date']),
            'week_end_date' => trim($_POST['week_end_date']),
            'maintenance_type' => trim($_POST['maintenance_type']),
            'priority' => trim($_POST['priority']),
            'description' => trim($_POST['description']),
            'estimated_hours' => !empty($_POST['estimated_hours']) ? floatval($_POST['estimated_hours']) : null
        ];
        
        // Validation (same as create)
        $errors = [];
        
        if (empty($data['router_name'])) {
            $errors['router_name'] = 'Router name is required';
        }
        
        if (empty($data['router_ip'])) {
            $errors['router_ip'] = 'Router IP is required';
        } elseif (!filter_var($data['router_ip'], FILTER_VALIDATE_IP)) {
            $errors['router_ip'] = 'Please enter a valid IP address';
        }
        
        if (empty($data['location'])) {
            $errors['location'] = 'Location is required';
        }
        
        if (empty($data['week_start_date'])) {
            $errors['week_start_date'] = 'Week start date is required';
        }
        
        if (empty($data['week_end_date'])) {
            $errors['week_end_date'] = 'Week end date is required';
        } elseif (!empty($data['week_start_date']) && $data['week_end_date'] < $data['week_start_date']) {
            $errors['week_end_date'] = 'End date cannot be before start date';
        }
        
        if (empty($data['maintenance_type'])) {
            $errors['maintenance_type'] = 'Maintenance type is required';
        }
        
        if (empty($data['priority'])) {
            $errors['priority'] = 'Priority is required';
        }
        
        // If no errors, update the router schedule
        if (empty($errors)) {
            $success = $this->weeklyRouterModel->update($id, $data);
            
            if ($success) {
                // Log the activity
                $description = sprintf(
                    'Updated router schedule for "%s" (%s) at %s',
                    $data['router_name'],
                    $data['router_ip'],
                    $data['location']
                );
                
                $metadata = [
                    'router_schedule_id' => $id,
                    'changes' => $this->getChanges($routerSchedule, $data)
                ];
                
                $this->activityLogModel->log(
                    $_SESSION['user_id'],
                    'router_schedule',
                    $id,
                    'updated',
                    $description,
                    $metadata
                );
                
                flash('success', 'Router schedule updated successfully');
                redirect('weekly_routers/view/' . $id);
            } else {
                flash('error', 'Failed to update router schedule', 'alert-danger');
                redirect('weekly_routers/edit/' . $id);
            }
        } else {
            // Show form with errors
            $technicians = $this->weeklyRouterModel->getTechnicians();
            
            $this->view('weekly_routers/edit', [
                'title' => 'Edit Router Schedule',
                'router_schedule' => array_merge($routerSchedule, $data),
                'technicians' => $technicians,
                'errors' => $errors
            ]);
        }
    }
    
    /**
     * Delete a router schedule (IT Manager only)
     */
    public function delete($id) {
        // Check if user has manager/admin privileges
        $userRole = $_SESSION['role'] ?? 'user';
        if (!in_array($userRole, ['admin', 'manager', 'it_manager', 'IT Manager'])) {
            flash('error', 'You do not have permission to perform this action', 'alert-danger');
            redirect('weekly_routers');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('weekly_routers');
        }
        
        if (!$id) {
            flash('error', 'No router schedule ID provided', 'alert-danger');
            redirect('weekly_routers');
        }
        
        $routerSchedule = $this->weeklyRouterModel->getRouterScheduleById($id);
        
        if (!$routerSchedule) {
            flash('error', 'Router schedule not found', 'alert-danger');
            redirect('weekly_routers');
        }
        
        $success = $this->weeklyRouterModel->delete($id);
        
        if ($success) {
            // Log the activity
            $description = sprintf(
                'Deleted router schedule for "%s" (%s) at %s',
                $routerSchedule['router_name'],
                $routerSchedule['router_ip'],
                $routerSchedule['location']
            );
            
            $metadata = [
                'router_schedule_id' => $id,
                'router_name' => $routerSchedule['router_name'],
                'router_ip' => $routerSchedule['router_ip'],
                'location' => $routerSchedule['location']
            ];
            
            $this->activityLogModel->log(
                $_SESSION['user_id'],
                'router_schedule',
                $id,
                'deleted',
                $description,
                $metadata
            );
            
            flash('success', 'Router schedule deleted successfully');
        } else {
            flash('error', 'Failed to delete router schedule', 'alert-danger');
        }
        
        redirect('weekly_routers');
    }
    
    /**
     * Helper method to get changes between old and new data
     */
    private function getChanges($oldData, $newData) {
        $changes = [];
        
        foreach ($newData as $key => $newValue) {
            if (isset($oldData[$key]) && $oldData[$key] != $newValue) {
                $changes[$key] = [
                    'from' => $oldData[$key],
                    'to' => $newValue
                ];
            }
        }
        
        return $changes;
    }
} 