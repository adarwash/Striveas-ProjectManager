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
        
        // Get dashboard statistics
        $dashboardStats = $this->getDashboardStats();
        
        $data = [
            'title' => 'Dashboard',
            'projects' => $projects,
            'tasks' => $tasks,
            'assigned_tasks' => $assignedTasks,
            'departments' => $departments,
            'project_counts' => $projectCounts,
            'task_counts' => $taskCounts,
            'user_projects' => $userProjects,
            'stats' => $dashboardStats
        ];
        
        $this->view('dashboard/index', $data);
    }
    
    /**
     * Get dashboard statistics
     */
    private function getDashboardStats() {
        $stats = [];
        
        try {
            // Get total users
            $stats['total_users'] = $this->userModel->getTotalUsers() ?? 0;
            
            // Get technicians count
            $stats['technicians'] = $this->userModel->getUsersByRole('technician') ?? 0;
            
            // Get active clients count
            $clientModel = $this->model('Client');
            $stats['active_clients'] = $clientModel->getActiveClientsCount() ?? 0;
        } catch (Exception $e) {
            // If clients table doesn't exist, default to 0
            $stats['active_clients'] = 0;
        }
        
        try {
            // Get active sites count
            $siteModel = $this->model('Site');
            $stats['active_sites'] = $siteModel->getActiveSitesCount() ?? 0;
        } catch (Exception $e) {
            $stats['active_sites'] = 0;
        }
        
        try {
            // Get open tickets count
            $ticketModel = $this->model('Ticket');
            $stats['open_tickets'] = $ticketModel->getOpenTicketsCount() ?? 0;
        } catch (Exception $e) {
            $stats['open_tickets'] = 0;
        }
        
        // Get open tasks count from existing task counts
        $stats['open_tasks'] = $this->taskModel->getOpenTasksCount() ?? 0;
        
        try {
            // Get pending requests count
            $requestModel = $this->model('Request');
            $stats['pending_requests'] = $requestModel->getPendingRequestsCount() ?? 0;
        } catch (Exception $e) {
            $stats['pending_requests'] = 0;
        }
        
        try {
            // Get currently working count (from time tracking)
            $timeModel = $this->model('TimeTracking');
            $stats['currently_working'] = $timeModel->getCurrentlyWorkingCount() ?? 0;
        } catch (Exception $e) {
            $stats['currently_working'] = 0;
        }
        
        return $stats;
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
        
        // Check if Microsoft 365 OAuth is configured
        $clientId = $_ENV['MICROSOFT_CLIENT_ID'] ?? (defined('MICROSOFT_CLIENT_ID') ? MICROSOFT_CLIENT_ID : '');
        $clientSecret = $_ENV['MICROSOFT_CLIENT_SECRET'] ?? (defined('MICROSOFT_CLIENT_SECRET') ? MICROSOFT_CLIENT_SECRET : '');
        $microsoft365_configured = !empty($clientId) && !empty($clientSecret);
        
        $data = [
            'title' => 'Calendar View',
            'user' => $user,
            'tasks' => $tasks,
            'connected_calendars' => $connected_calendars,
            'shared_events' => $shared_events,
            'microsoft365_configured' => $microsoft365_configured
        ];
        
        $this->view('dashboard/calendar', $data);
    }
    
    /**
     * Connect a new external calendar or test iCal URL
     */
    public function connectCalendar() {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect('/users/login');
        }
        
        // Handle iCal URL testing (AJAX request)
        if ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' === 'XMLHttpRequest') {
            return $this->handleIcalUrlTest();
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
                // Legacy Outlook handling - redirect to microsoft365
                $_SESSION['info'] = 'Redirecting to Microsoft 365 integration...';
                redirect('/dashboard/calendar');
                break;
                
            case 'microsoft365':
                // Check if Microsoft OAuth is configured before proceeding
                $clientId = $_ENV['MICROSOFT_CLIENT_ID'] ?? (defined('MICROSOFT_CLIENT_ID') ? MICROSOFT_CLIENT_ID : '');
                $clientSecret = $_ENV['MICROSOFT_CLIENT_SECRET'] ?? (defined('MICROSOFT_CLIENT_SECRET') ? MICROSOFT_CLIENT_SECRET : '');
                
                if (empty($clientId) || empty($clientSecret)) {
                    $_SESSION['error'] = 'Microsoft 365 integration is not configured. Please contact your administrator to set up Azure app credentials.';
                    redirect('/dashboard/calendar');
                    return;
                }
                
                // Store form data in session for after OAuth
                $_SESSION['microsoft365_form_data'] = [
                    'calendar_name' => filter_input(INPUT_POST, 'calendar_name', FILTER_SANITIZE_STRING) ?? 'Microsoft 365 Calendar',
                    'calendar_color' => filter_input(INPUT_POST, 'calendar_color', FILTER_SANITIZE_STRING) ?? '#0078d4',
                    'auto_refresh' => isset($_POST['auto_refresh']) ? 1 : 0,
                    'calendar_scope' => $_POST['calendar_scope'] ?? 'primary'
                ];
                
                // Redirect to Microsoft OAuth
                $this->initiateMicrosoftOAuth();
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
                
                // Additional iCal URL validation
                if (!preg_match('/\.(ics|ical)(\?.*)?$/i', $calendar_url) && 
                    !strpos($calendar_url, 'calendar') && 
                    !strpos($calendar_url, 'ical')) {
                    $_SESSION['warning'] = 'The URL doesn\'t appear to be an iCal feed. Make sure it ends with .ics or contains calendar/ical in the path.';
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
        // For AJAX requests, handle everything in try-catch to ensure clean JSON responses
        if ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' === 'XMLHttpRequest') {
            try {
                // Clean any previous output
                if (ob_get_level()) {
                    ob_clean();
                }
                
                // Set JSON header immediately
                header('Content-Type: application/json');
                
                // Check if user is logged in
                if(!isLoggedIn()) {
                    error_log("Sync: User not logged in");
                    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
                    exit;
                }
                
                // Ensure we have an ID
                if (!$id) {
                    error_log("Sync: No calendar ID provided");
                    echo json_encode(['success' => false, 'message' => 'Calendar ID is required']);
                    exit;
                }
                
                // Get user info
                $user_id = $_SESSION['user_id'];
                
                // Load Calendar model
                $calendarModel = $this->model('Calendar');
                
                // Verify the calendar belongs to the current user
                $calendar = $calendarModel->getCalendarById($id);
                
                if (!$calendar || $calendar['user_id'] != $user_id) {
                    error_log("Sync: Calendar not found or access denied for ID: {$id}");
                    echo json_encode(['success' => false, 'message' => 'Calendar not found or access denied']);
                    exit;
                }
                
                // Perform the sync
                error_log("Sync: Attempting to sync calendar ID: {$id}");
                $result = $calendarModel->syncCalendar($id);
                error_log("Sync: Calendar sync result: " . ($result ? 'success' : 'failed'));
                
                if ($result) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Calendar synced successfully',
                        'last_synced' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    // Provide more specific error messages
                    $calendar = $calendarModel->getCalendarById($id);
                    $errorMessage = 'Failed to sync calendar';
                    
                    if ($calendar && $calendar['source'] === 'ical') {
                        $errorMessage = 'Failed to sync iCal calendar. Please check: 1) The URL is accessible, 2) It returns iCal content (not HTML), 3) The calendar is publicly shared';
                    }
                    
                    echo json_encode(['success' => false, 'message' => $errorMessage]);
                }
                
                exit;
                
            } catch (Exception $e) {
                error_log('Exception in syncCalendar AJAX: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
                exit;
            }
        }
        
        // Handle non-AJAX requests (fallback)
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
        
        // Perform the sync (non-AJAX)
        try {
            $result = $calendarModel->syncCalendar($id);
            
            if ($result) {
                $_SESSION['success'] = 'Calendar synced successfully';
            } else {
                $_SESSION['error'] = 'Failed to sync calendar';
            }
        } catch (Exception $e) {
            error_log('Error syncing calendar: ' . $e->getMessage());
            $_SESSION['error'] = 'Sync error occurred';
        }
        
        redirect('/dashboard/calendar');
    }
    
    /**
     * Handle iCal URL testing (called from connectCalendar for AJAX requests)
     * 
     * @return void
     */
    private function handleIcalUrlTest() {
        try {
            // Clean any previous output
            if (ob_get_level()) {
                ob_clean();
            }
            
            // Set JSON header
            header('Content-Type: application/json');
            
            // Get the JSON input
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data || !isset($data['url'])) {
                echo json_encode(['success' => false, 'message' => 'URL is required']);
                exit;
            }
            
            $url = trim($data['url']);
            
            // Validate URL format
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                echo json_encode(['success' => false, 'message' => 'Invalid URL format']);
                exit;
            }
            
            // Load Calendar model and test the URL
            $calendarModel = $this->model('Calendar');
            
            // Use reflection to access the private method for testing
            $reflection = new ReflectionClass($calendarModel);
            $fetchMethod = $reflection->getMethod('fetchIcalContent');
            $fetchMethod->setAccessible(true);
            $validateMethod = $reflection->getMethod('isValidIcalContent');
            $validateMethod->setAccessible(true);
            
            // Test fetching the content
            $content = $fetchMethod->invoke($calendarModel, $url);
            
            if ($content === false) {
                echo json_encode(['success' => false, 'message' => 'Cannot access the URL. It may be down, require authentication, or force downloads.']);
                exit;
            }
            
            // Validate the content
            if (!$validateMethod->invoke($calendarModel, $content)) {
                echo json_encode(['success' => false, 'message' => 'URL does not return valid iCal content. It may return HTML or require different access.']);
                exit;
            }
            
            // Count events to give feedback
            $eventCount = substr_count($content, 'BEGIN:VEVENT');
            
            echo json_encode([
                'success' => true, 
                'message' => "✅ Valid iCal URL! Found {$eventCount} event(s). This URL should work for calendar sync."
            ]);
            exit;
            
        } catch (Exception $e) {
            error_log('handleIcalUrlTest error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error testing URL: ' . $e->getMessage()]);
            exit;
        }
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
        // For AJAX requests, handle everything in try-catch to ensure clean JSON responses
        if ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' === 'XMLHttpRequest') {
            try {
                // Clean any previous output
                if (ob_get_level()) {
                    ob_clean();
                }
                
                // Set JSON header immediately
                header('Content-Type: application/json');
                
                // Debug log
                error_log("removeCalendar AJAX called with ID: " . ($id ?? 'null'));
                
                // Check if user is logged in
                if(!isLoggedIn()) {
                    error_log("User not logged in");
                    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
                    exit;
                }
                
                // Ensure we have an ID
                if (!$id) {
                    error_log("No calendar ID provided");
                    echo json_encode(['success' => false, 'message' => 'Calendar ID is required']);
                    exit;
                }
                
                // Get user info
                $user_id = $_SESSION['user_id'];
                
                // Load Calendar model
                $calendarModel = $this->model('Calendar');
                
                // Verify the calendar belongs to the current user
                $calendar = $calendarModel->getCalendarById($id);
                error_log("Calendar found: " . ($calendar ? 'yes' : 'no'));
                
                if (!$calendar || $calendar['user_id'] != $user_id) {
                    error_log("Calendar not found or access denied");
                    echo json_encode(['success' => false, 'message' => 'Calendar not found or access denied']);
                    exit;
                }
                
                // Remove the calendar
                error_log("Attempting to remove calendar ID: " . $id);
                $result = $calendarModel->removeCalendar($id);
                error_log("Remove calendar result: " . ($result ? 'success' : 'failed'));
                
                if ($result) {
                    error_log("Sending success response");
                    echo json_encode(['success' => true, 'message' => 'Calendar removed successfully']);
                } else {
                    error_log("Sending failure response");
                    echo json_encode(['success' => false, 'message' => 'Failed to remove calendar from database']);
                }
                
                exit;
                
            } catch (Exception $e) {
                error_log('Exception in removeCalendar AJAX: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
                exit;
            }
        }
        
        // Handle non-AJAX requests (fallback)
        error_log("removeCalendar non-AJAX called with ID: " . ($id ?? 'null'));
        
        // Check if user is logged in
        if(!isLoggedIn()) {
            error_log("User not logged in, redirecting");
            redirect('/users/login');
        }
        
        // Ensure we have an ID
        if (!$id) {
            error_log("No calendar ID provided");
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
        
        // Remove the calendar (non-AJAX)
        try {
            $result = $calendarModel->removeCalendar($id);
            
            if ($result) {
                $_SESSION['success'] = 'Calendar removed successfully';
            } else {
                $_SESSION['error'] = 'Failed to remove calendar';
            }
        } catch (Exception $e) {
            error_log('Error removing calendar: ' . $e->getMessage());
            $_SESSION['error'] = 'Database error occurred while removing calendar';
        }
        
        redirect('/dashboard/calendar');
    }
    
    /**
     * Initiate Microsoft OAuth flow
     */
    private function initiateMicrosoftOAuth() {
        // Microsoft Graph OAuth configuration
        // Try $_ENV first, then fall back to defined constants or config
        $clientId = $_ENV['MICROSOFT_CLIENT_ID'] ?? (defined('MICROSOFT_CLIENT_ID') ? MICROSOFT_CLIENT_ID : '');
        $clientSecret = $_ENV['MICROSOFT_CLIENT_SECRET'] ?? (defined('MICROSOFT_CLIENT_SECRET') ? MICROSOFT_CLIENT_SECRET : '');
        $appUrl = $_ENV['APP_URL'] ?? (defined('URLROOT') ? URLROOT : 'http://192.168.2.12');
        $redirectUri = $appUrl . '/dashboard/microsoftCallback';
        $scope = 'https://graph.microsoft.com/calendars.read offline_access';
        
        // Check if all required config is present
        if (empty($clientId) || empty($clientSecret)) {
            $_SESSION['error'] = 'Microsoft 365 integration is not configured yet. Please add your Azure app credentials to the configuration.';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Generate state parameter for security
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;
        
        // Build OAuth URL
        $params = http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'state' => $state,
            'response_mode' => 'query'
        ]);
        
        $authUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?' . $params;
        
        // Redirect to Microsoft OAuth
        header('Location: ' . $authUrl);
        exit;
    }
    
    /**
     * Handle Microsoft OAuth callback
     */
    public function microsoftCallback() {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect('/users/login');
        }
        
        // Verify state parameter
        $state = $_GET['state'] ?? '';
        $sessionState = $_SESSION['oauth_state'] ?? '';
        
        if (empty($state) || $state !== $sessionState) {
            $_SESSION['error'] = 'Invalid OAuth state. Please try again.';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Clear state
        unset($_SESSION['oauth_state']);
        
        // Check for error
        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            $errorDescription = $_GET['error_description'] ?? 'Unknown error';
            $_SESSION['error'] = 'Microsoft 365 authorization failed: ' . $errorDescription;
            redirect('/dashboard/calendar');
            return;
        }
        
        // Get authorization code
        $code = $_GET['code'] ?? '';
        if (empty($code)) {
            $_SESSION['error'] = 'No authorization code received from Microsoft.';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Exchange code for tokens
        $tokenData = $this->exchangeCodeForTokens($code);
        
        if (!$tokenData) {
            $_SESSION['error'] = 'Failed to obtain access token from Microsoft.';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Get form data from session
        $formData = $_SESSION['microsoft365_form_data'] ?? [];
        unset($_SESSION['microsoft365_form_data']);
        
        // Create calendar connection
        $this->createMicrosoftCalendarConnection($tokenData, $formData);
    }
    
    /**
     * Exchange authorization code for access tokens
     */
    private function exchangeCodeForTokens($code) {
        $clientId = $_ENV['MICROSOFT_CLIENT_ID'] ?? (defined('MICROSOFT_CLIENT_ID') ? MICROSOFT_CLIENT_ID : '');
        $clientSecret = $_ENV['MICROSOFT_CLIENT_SECRET'] ?? (defined('MICROSOFT_CLIENT_SECRET') ? MICROSOFT_CLIENT_SECRET : '');
        $appUrl = $_ENV['APP_URL'] ?? (defined('URLROOT') ? URLROOT : 'http://192.168.2.12');
        $redirectUri = $appUrl . '/dashboard/microsoftCallback';
        
        if (empty($clientId) || empty($clientSecret)) {
            return false;
        }
        
        $tokenUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
        
        $postData = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
            'scope' => 'https://graph.microsoft.com/calendars.read offline_access'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return false;
    }
    
    /**
     * Create Microsoft calendar connection
     */
    private function createMicrosoftCalendarConnection($tokenData, $formData) {
        $user_id = $_SESSION['user_id'];
        $calendarModel = $this->model('Calendar');
        
        // Calculate token expiration
        $expiresIn = $tokenData['expires_in'] ?? 3600;
        $tokenExpires = date('Y-m-d H:i:s', time() + $expiresIn);
        
        // Prepare calendar data
        $calendarData = [
            'user_id' => $user_id,
            'name' => $formData['calendar_name'] ?? 'Microsoft 365 Calendar',
            'source' => 'microsoft365',
            'source_id' => 'primary', // Will be updated after getting calendar details
            'color' => $formData['calendar_color'] ?? '#0078d4',
            'auto_refresh' => $formData['auto_refresh'] ?? 1,
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'active' => 1
        ];
        
        // Add the calendar
        $calendarId = $calendarModel->addCalendar($calendarData);
        
        if ($calendarId) {
            // Store token expiration separately if needed
            $this->storeMicrosoftTokenExpiration($calendarId, $tokenExpires);
            
            // Try to sync immediately to get calendar details
            $syncResult = $calendarModel->syncCalendar($calendarId);
            
            if ($syncResult) {
                $_SESSION['success'] = 'Microsoft 365 calendar connected and synced successfully!';
            } else {
                $_SESSION['success'] = 'Microsoft 365 calendar connected! Sync will happen automatically.';
            }
        } else {
            $_SESSION['error'] = 'Failed to save Microsoft 365 calendar connection.';
        }
        
        redirect('/dashboard/calendar');
    }
    
    /**
     * Store Microsoft token expiration
     */
    private function storeMicrosoftTokenExpiration($calendarId, $tokenExpires) {
        // Update the calendar with token expiration
        $calendarModel = $this->model('Calendar');
        $calendarModel->updateCalendar([
            'id' => $calendarId,
            'token_expires' => $tokenExpires
        ]);
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