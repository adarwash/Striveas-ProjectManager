<?php
/**
 * Activities Controller
 * Handles daily activity logging and time tracking
 */
class Activities extends Controller {
    private $activityModel;
    private $userModel;
    
    public function __construct() {
        // Ensure user is logged in for all actions in this controller
        if (!isLoggedIn()) {
            redirect('users/login');
        }
        
        // Load models
        $this->activityModel = $this->model('DailyActivity');
        $this->userModel = $this->model('User');
    }
    
    /**
     * Daily activity dashboard - main view
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);
        
        // Get current active check-in if exists
        $activeCheckIn = $this->activityModel->getActiveCheckIn($userId);
        
        // Get recent activities (last 7 days)
        $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
        $activities = $this->activityModel->getActivitiesByUser($userId, $sevenDaysAgo);
        
        // Get activity summary for the current month
        $firstDayOfMonth = date('Y-m-01');
        $lastDayOfMonth = date('Y-m-t');
        $monthlySummary = $this->activityModel->getUserActivitySummary($userId, $firstDayOfMonth, $lastDayOfMonth);
        
        // Calculate total hours for the month
        $totalMonthlyHours = 0;
        foreach ($monthlySummary as $day) {
            $totalMonthlyHours += floatval($day['total_hours']);
        }
        
        $data = [
            'title' => 'Daily Activity Tracker',
            'user' => $user,
            'active_check_in' => $activeCheckIn,
            'activities' => $activities,
            'monthly_summary' => $monthlySummary,
            'total_monthly_hours' => $totalMonthlyHours
        ];
        
        $this->view('activities/index', $data);
    }
    
    /**
     * Handle check in action
     */
    public function checkIn() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('activities');
        }
        
        $userId = $_SESSION['user_id'];
        
        // Check if already checked in
        $activeCheckIn = $this->activityModel->getActiveCheckIn($userId);
        if ($activeCheckIn) {
            flash('activity_error', 'You are already checked in', 'alert-warning');
            redirect('activities');
        }
        
        // Get description from form
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        
        // Perform check in
        $checkInId = $this->activityModel->checkIn($userId, $description);
        
        if ($checkInId) {
            flash('activity_message', 'Successfully checked in');
        } else {
            flash('activity_error', 'Failed to check in', 'alert-danger');
        }
        
        redirect('activities');
    }
    
    /**
     * Handle check out action
     */
    public function checkOut() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('activities');
        }
        
        $userId = $_SESSION['user_id'];
        
        // Get active check in
        $activeCheckIn = $this->activityModel->getActiveCheckIn($userId);
        if (!$activeCheckIn) {
            flash('activity_error', 'No active check-in found', 'alert-warning');
            redirect('activities');
        }
        
        // Get updated description from form
        $description = isset($_POST['description']) ? trim($_POST['description']) : null;
        
        // Perform check out
        $success = $this->activityModel->checkOut($activeCheckIn['id'], $description);
        
        if ($success) {
            flash('activity_message', 'Successfully checked out');
        } else {
            flash('activity_error', 'Failed to check out', 'alert-danger');
        }
        
        redirect('activities');
    }
    
    /**
     * View detailed history
     * 
     * @param string $startDate Optional start date (YYYY-MM-DD)
     * @param string $endDate Optional end date (YYYY-MM-DD)
     */
    public function history($startDate = null, $endDate = null) {
        $userId = $_SESSION['user_id'];
        
        // Set default date range if not provided (last 30 days)
        if (!$startDate) {
            $startDate = date('Y-m-d', strtotime('-30 days'));
        }
        
        if (!$endDate) {
            $endDate = date('Y-m-d');
        }
        
        // Handle date filter form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['start_date']) && !empty($_POST['start_date'])) {
                $startDate = $_POST['start_date'];
            }
            
            if (isset($_POST['end_date']) && !empty($_POST['end_date'])) {
                $endDate = $_POST['end_date'];
            }
            
            redirect('activities/history/' . $startDate . '/' . $endDate);
        }
        
        // Get activities for the date range
        $activities = $this->activityModel->getActivitiesByUser($userId, $startDate, $endDate);
        
        // Get summary for the date range
        $summary = $this->activityModel->getUserActivitySummary($userId, $startDate, $endDate);
        
        // Calculate total hours for the period
        $totalHours = 0;
        foreach ($summary as $day) {
            $totalHours += floatval($day['total_hours']);
        }
        
        $data = [
            'title' => 'Activity History',
            'activities' => $activities,
            'summary' => $summary,
            'total_hours' => $totalHours,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        
        $this->view('activities/history', $data);
    }
    
    /**
     * Update activity description
     * 
     * @param int $id Activity ID
     */
    public function updateDescription($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('activities/history');
        }
        
        $userId = $_SESSION['user_id'];
        
        // Get the activity
        $activity = $this->activityModel->getActivityById($id);
        
        // Ensure activity exists and belongs to the user
        if (!$activity || $activity['user_id'] != $userId) {
            flash('activity_error', 'Activity not found or you are not authorized', 'alert-danger');
            redirect('activities/history');
        }
        
        // Get description from form
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        
        // Update description
        $success = $this->activityModel->updateDescription($id, $description);
        
        if ($success) {
            flash('activity_message', 'Activity description updated');
        } else {
            flash('activity_error', 'Failed to update activity description', 'alert-danger');
        }
        
        redirect('activities/history');
    }
    
    /**
     * Manager view - only accessible to managers/admins
     */
    public function manage() {
        // Check if user is a manager or admin
        if (!isAdmin() && !isManager()) {
            flash('activity_error', 'You do not have permission to access this page', 'alert-danger');
            redirect('activities');
        }
        
        // Set default date range (current month)
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        $status = null;
        
        // Handle filter form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['start_date']) && !empty($_POST['start_date'])) {
                $startDate = $_POST['start_date'];
            }
            
            if (isset($_POST['end_date']) && !empty($_POST['end_date'])) {
                $endDate = $_POST['end_date'];
            }
            
            if (isset($_POST['status']) && !empty($_POST['status'])) {
                $status = $_POST['status'];
            }
        }
        
        // Get all activities for the selected period
        $activities = $this->activityModel->getAllActivities($startDate, $endDate, $status);
        
        // Get all users for filtering
        $users = $this->userModel->getAllUsers();
        
        $data = [
            'title' => 'Manage Activities',
            'activities' => $activities,
            'users' => $users,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status
        ];
        
        $this->view('activities/manage', $data);
    }
    
    /**
     * Approve or reject an activity (manager only)
     * 
     * @param int $id Activity ID
     * @param string $status New status ('Approved' or 'Rejected')
     */
    public function updateStatus($id, $status) {
        // Check if user is a manager or admin
        if (!isAdmin() && !isManager()) {
            flash('activity_error', 'You do not have permission to perform this action', 'alert-danger');
            redirect('activities');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('activities/manage');
        }
        
        // Validate status
        $validStatuses = ['Approved', 'Rejected', 'Pending'];
        if (!in_array($status, $validStatuses)) {
            flash('activity_error', 'Invalid status', 'alert-danger');
            redirect('activities/manage');
        }
        
        // Update status
        $success = $this->activityModel->updateStatus($id, $status);
        
        if ($success) {
            flash('activity_message', 'Activity status updated to ' . $status);
        } else {
            flash('activity_error', 'Failed to update activity status', 'alert-danger');
        }
        
        redirect('activities/manage');
    }
    
    /**
     * Generate activity report
     */
    public function report() {
        // Check if user is a manager or admin
        if (!isAdmin() && !isManager()) {
            flash('activity_error', 'You do not have permission to access this page', 'alert-danger');
            redirect('activities');
        }
        
        // Set default date range (current month)
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        $userId = null;
        
        // Handle filter form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['start_date']) && !empty($_POST['start_date'])) {
                $startDate = $_POST['start_date'];
            }
            
            if (isset($_POST['end_date']) && !empty($_POST['end_date'])) {
                $endDate = $_POST['end_date'];
            }
            
            if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
                $userId = $_POST['user_id'];
            }
        }
        
        // Get all users for the filter dropdown
        $users = $this->userModel->getAllUsers();
        
        // Get activities based on filters
        $activities = [];
        if ($userId) {
            // Get activities for specific user
            $activities = $this->activityModel->getActivitiesByUser($userId, $startDate, $endDate);
            $summaries = $this->activityModel->getUserActivitySummary($userId, $startDate, $endDate);
        } else {
            // Get all activities
            $activities = $this->activityModel->getAllActivities($startDate, $endDate);
            // We would need to create a summary report for all users here
            // For now, we'll just pass the activities
            $summaries = [];
        }
        
        $data = [
            'title' => 'Activity Report',
            'activities' => $activities,
            'summaries' => $summaries,
            'users' => $users,
            'selected_user' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        
        $this->view('activities/report', $data);
    }
    
    /**
     * Setup database tables required for activities
     * This is an admin-only function used for initial setup
     */
    public function setupDatabase() {
        // Check if user is admin
        if (!isAdmin()) {
            flash('activity_error', 'You do not have permission to perform this action', 'alert-danger');
            redirect('dashboard');
            return;
        }
        
        // Create the table
        try {
            $success = $this->activityModel->createDailyActivitiesTable();
            
            if ($success) {
                flash('activity_message', 'Database tables for activities have been created successfully');
            } else {
                flash('activity_error', 'Failed to create database tables for activities', 'alert-danger');
            }
        } catch (Exception $e) {
            flash('activity_error', 'Error: ' . $e->getMessage(), 'alert-danger');
        }
        
        redirect('dashboard');
    }
} 