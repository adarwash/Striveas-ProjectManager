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
        
        // Get connected calendars
        $calendarModel = $this->model('Calendar');
        $connected_calendars = $calendarModel->getCalendarsByUser($user_id);
        
        // Get shared calendar events
        $shared_events = [];
        if (!empty($connected_calendars)) {
            $shared_events = $calendarModel->getCalendarEvents($user_id);
        }
        
        $data = [
            'title' => 'Calendar View',
            'user' => $user,
            'tasks' => $tasks,
            'connected_calendars' => $connected_calendars,
            'shared_events' => $shared_events
        ];
        
        $this->view('dashboard/calendar', $data);
    }
    
    /**
     * Connect a new external calendar
     */
    public function connectCalendar() {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect('/users/login');
        }
        
        // Verify it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Get user info
        $user_id = $_SESSION['user_id'];
        
        // Get the calendar type
        $calendar_type = $_POST['calendar_type'] ?? '';
        
        // Load Calendar model
        $calendarModel = $this->model('Calendar');
        
        // Handle different calendar types
        switch ($calendar_type) {
            case 'google':
                // Typically, you would redirect to Google OAuth flow
                // For now, we'll simulate this with a success message
                $_SESSION['success'] = 'Google Calendar connection initiated. OAuth flow would start here.';
                redirect('/dashboard/calendar');
                break;
                
            case 'outlook':
                // Typically, you would redirect to Microsoft OAuth flow
                // For now, we'll simulate this with a success message
                $_SESSION['success'] = 'Microsoft Outlook connection initiated. OAuth flow would start here.';
                redirect('/dashboard/calendar');
                break;
                
            case 'ical':
                // For iCal URLs, we can directly process the form
                $calendar_name = filter_input(INPUT_POST, 'calendar_name', FILTER_SANITIZE_STRING);
                $calendar_url = filter_input(INPUT_POST, 'calendar_url', FILTER_SANITIZE_URL);
                $calendar_color = filter_input(INPUT_POST, 'calendar_color', FILTER_SANITIZE_STRING) ?? '#039be5';
                $auto_refresh = isset($_POST['calendar_refresh']) ? 1 : 0;
                
                // Validate inputs
                if (empty($calendar_name) || empty($calendar_url)) {
                    $_SESSION['error'] = 'Please provide both calendar name and URL';
                    redirect('/dashboard/calendar');
                    return;
                }
                
                // Basic URL validation
                if (!filter_var($calendar_url, FILTER_VALIDATE_URL)) {
                    $_SESSION['error'] = 'Please provide a valid URL';
                    redirect('/dashboard/calendar');
                    return;
                }
                
                // Prepare calendar data
                $calendarData = [
                    'user_id' => $user_id,
                    'name' => $calendar_name,
                    'source' => 'ical',
                    'source_id' => $calendar_url,
                    'color' => $calendar_color,
                    'auto_refresh' => $auto_refresh,
                    'access_token' => null,
                    'refresh_token' => null,
                    'active' => 1
                ];
                
                // Add the calendar
                $result = $calendarModel->addCalendar($calendarData);
                
                if ($result) {
                    // Sync the calendar immediately
                    $calendarModel->syncCalendar($result);
                    $_SESSION['success'] = 'Calendar added and synced successfully';
                } else {
                    $_SESSION['error'] = 'Failed to add calendar';
                }
                
                redirect('/dashboard/calendar');
                break;
                
            default:
                $_SESSION['error'] = 'Unknown calendar type';
                redirect('/dashboard/calendar');
                break;
        }
    }
    
    /**
     * Sync a connected calendar
     * 
     * @param int $id Calendar ID
     * @return void
     */
    public function syncCalendar($id = null) {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect('/users/login');
        }
        
        // Ensure we have an ID
        if (!$id) {
            if ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' === 'XMLHttpRequest') {
                // Respond with JSON for AJAX requests
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Calendar ID is required']);
                return;
            }
            
            $_SESSION['error'] = 'Calendar ID is required';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Get user info
        $user_id = $_SESSION['user_id'];
        
        // Load Calendar model
        $calendarModel = $this->model('Calendar');
        
        // Verify the calendar belongs to the current user
        $calendar = $calendarModel->getCalendarById($id);
        
        if (!$calendar || $calendar['user_id'] != $user_id) {
            if ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' === 'XMLHttpRequest') {
                // Respond with JSON for AJAX requests
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Calendar not found or access denied']);
                return;
            }
            
            $_SESSION['error'] = 'Calendar not found or access denied';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Perform the sync
        $result = $calendarModel->syncCalendar($id);
        
        if ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' === 'XMLHttpRequest') {
            // Respond with JSON for AJAX requests
            header('Content-Type: application/json');
            
            if ($result) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Calendar synced successfully',
                    'last_synced' => date('Y-m-d H:i:s')
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to sync calendar']);
            }
            
            return;
        }
        
        // For regular requests, set session message and redirect
        if ($result) {
            $_SESSION['success'] = 'Calendar synced successfully';
        } else {
            $_SESSION['error'] = 'Failed to sync calendar';
        }
        
        redirect('/dashboard/calendar');
    }
    
    /**
     * Display form to edit a connected calendar
     * 
     * @param int $id Calendar ID
     * @return void
     */
    public function editCalendar($id = null) {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect('/users/login');
        }
        
        // Ensure we have an ID
        if (!$id) {
            $_SESSION['error'] = 'Calendar ID is required';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Get user info
        $user_id = $_SESSION['user_id'];
        
        // Load Calendar model
        $calendarModel = $this->model('Calendar');
        
        // Verify the calendar belongs to the current user
        $calendar = $calendarModel->getCalendarById($id);
        
        if (!$calendar || $calendar['user_id'] != $user_id) {
            $_SESSION['error'] = 'Calendar not found or access denied';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $calendar_name = filter_input(INPUT_POST, 'calendar_name', FILTER_SANITIZE_STRING);
            $calendar_color = filter_input(INPUT_POST, 'calendar_color', FILTER_SANITIZE_STRING) ?? '#039be5';
            $auto_refresh = isset($_POST['calendar_refresh']) ? 1 : 0;
            $active = isset($_POST['calendar_active']) ? 1 : 0;
            
            // For iCal URLs, also update the URL
            $calendar_url = '';
            if ($calendar['source'] === 'ical') {
                $calendar_url = filter_input(INPUT_POST, 'calendar_url', FILTER_SANITIZE_URL);
                
                // Basic URL validation
                if (!filter_var($calendar_url, FILTER_VALIDATE_URL)) {
                    $_SESSION['error'] = 'Please provide a valid URL';
                    redirect('/dashboard/editCalendar/' . $id);
                    return;
                }
            }
            
            // Prepare calendar data
            $calendarData = [
                'id' => $id,
                'name' => $calendar_name,
                'color' => $calendar_color,
                'auto_refresh' => $auto_refresh,
                'active' => $active
            ];
            
            // Add source_id for iCal
            if ($calendar['source'] === 'ical' && !empty($calendar_url)) {
                $calendarData['source_id'] = $calendar_url;
            }
            
            // Update the calendar
            $result = $calendarModel->updateCalendar($calendarData);
            
            if ($result) {
                $_SESSION['success'] = 'Calendar updated successfully';
                redirect('/dashboard/calendar');
            } else {
                $_SESSION['error'] = 'Failed to update calendar';
                redirect('/dashboard/editCalendar/' . $id);
            }
            
            return;
        }
        
        // Prepare view data
        $data = [
            'title' => 'Edit Calendar',
            'calendar' => $calendar
        ];
        
        // Display edit form
        $this->view('dashboard/edit_calendar', $data);
    }
    
    /**
     * Remove a connected calendar
     * 
     * @param int $id Calendar ID
     * @return void
     */
    public function removeCalendar($id = null) {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect('/users/login');
        }
        
        // Ensure we have an ID
        if (!$id) {
            if ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' === 'XMLHttpRequest') {
                // Respond with JSON for AJAX requests
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Calendar ID is required']);
                return;
            }
            
            $_SESSION['error'] = 'Calendar ID is required';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Get user info
        $user_id = $_SESSION['user_id'];
        
        // Load Calendar model
        $calendarModel = $this->model('Calendar');
        
        // Verify the calendar belongs to the current user
        $calendar = $calendarModel->getCalendarById($id);
        
        if (!$calendar || $calendar['user_id'] != $user_id) {
            if ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' === 'XMLHttpRequest') {
                // Respond with JSON for AJAX requests
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Calendar not found or access denied']);
                return;
            }
            
            $_SESSION['error'] = 'Calendar not found or access denied';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Remove the calendar
        $result = $calendarModel->removeCalendar($id);
        
        if ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' === 'XMLHttpRequest') {
            // Respond with JSON for AJAX requests
            header('Content-Type: application/json');
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Calendar removed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove calendar']);
            }
            
            return;
        }
        
        // For regular requests, set session message and redirect
        if ($result) {
            $_SESSION['success'] = 'Calendar removed successfully';
        } else {
            $_SESSION['error'] = 'Failed to remove calendar';
        }
        
        redirect('/dashboard/calendar');
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