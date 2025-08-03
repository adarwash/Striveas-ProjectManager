<?php 

class Time extends Controller{
    private $timeModel;
    private $userModel;
    
    public function __construct() {
        $this->timeModel = $this->model('TimeTracking');
        $this->userModel = $this->model('User');
        
        // Check if user is logged in
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
            header('Location: /auth');
            exit();
        }
    }
    
    /**
     * Display time tracking dashboard
     */
    public function index() {
        try {
            $userId = $_SESSION['user_id'];
            $userStatus = $this->timeModel->getUserStatus($userId);
            $todaySummary = $this->timeModel->getDailySummary($userId);
            $recentEntries = $this->timeModel->getTimeEntriesWithSites($userId, date('Y-m-d', strtotime('-7 days')), null, 10);
            $breakTypes = $this->timeModel->getBreakTypes();
            
            $data = [
                'title' => 'Time Tracking',
                'user_status' => $userStatus,
                'today_summary' => $todaySummary,
                'recent_entries' => $recentEntries,
                'break_types' => $breakTypes,
                'current_time' => date('Y-m-d H:i:s')
            ];
            
            $this->view('time/dashboard', $data);
        } catch (Exception $e) {
            $this->view('time/setup_required', [
                'title' => 'Time Tracking Setup Required',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Clock in user
     */
    public function clockIn() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $notes = $_POST['notes'] ?? null;
        $siteId = !empty($_POST['site_id']) ? (int)$_POST['site_id'] : null;
        
        $result = $this->timeModel->clockIn($userId, $notes, $siteId);
        $this->jsonResponse($result);
    }
    
    /**
     * Clock out user
     */
    public function clockOut() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $notes = $_POST['notes'] ?? null;
        
        $result = $this->timeModel->clockOut($userId, $notes);
        $this->jsonResponse($result);
    }
    
    /**
     * Start break
     */
    public function startBreak() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $breakType = $_POST['break_type'] ?? 'regular';
        $notes = $_POST['notes'] ?? null;
        
        $result = $this->timeModel->startBreak($userId, $breakType, $notes);
        $this->jsonResponse($result);
    }
    
    /**
     * End break
     */
    public function endBreak() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $notes = $_POST['notes'] ?? null;
        
        $result = $this->timeModel->endBreak($userId, $notes);
        $this->jsonResponse($result);
    }
    
    /**
     * Get current user status (AJAX)
     */
    public function getStatus() {
        $userId = $_SESSION['user_id'];
        $status = $this->timeModel->getUserStatus($userId);
        $this->jsonResponse($status);
    }
    
    /**
     * Get available sites for the user (AJAX)
     */
    public function getUserSites() {
        $userId = $_SESSION['user_id'];
        $sites = $this->timeModel->getUserSites($userId);
        $this->jsonResponse(['success' => true, 'sites' => $sites]);
    }
    
    /**
     * View time history
     */
    public function history() {
        $userId = $_SESSION['user_id'];
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $timeEntries = $this->timeModel->getTimeEntriesWithSites($userId, $startDate, $endDate, 0); // 0 = no limit for history
        
        $data = [
            'title' => 'Time History',
            'time_entries' => $timeEntries,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_hours' => array_sum(array_column($timeEntries, 'total_hours'))
        ];
        
        $this->view('time/history', $data);
    }
    
    /**
     * Team view (for managers/admins)
     */
    public function team() {
        // Check permissions
        if (!$this->userModel->hasPermission($_SESSION['user_id'], 'reports_read')) {
            header('Location: /dashboard');
            exit();
        }
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $teamSummary = $this->timeModel->getTeamSummary($date);
        
        $data = [
            'title' => 'Team Time Tracking',
            'team_summary' => $teamSummary,
            'selected_date' => $date
        ];
        
        $this->view('time/team', $data);
    }
    
    /**
     * Admin dashboard - comprehensive view for admins
     */
    public function admin() {
        // Check if user is admin
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /dashboard');
            exit();
        }
        
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $userId = $_GET['user_id'] ?? null;
        $department = $_GET['department'] ?? null;
        
        // Get all users for admin view
        $allUsers = $this->timeModel->getAllUsersWithTimeData($startDate, $endDate, $userId, $department);
        $departments = $this->timeModel->getDepartments();
        $timeStatistics = $this->timeModel->getOverallStatistics($startDate, $endDate);
        $currentlyActive = $this->timeModel->getCurrentlyActiveUsers();
        $recentActivity = $this->timeModel->getRecentActivity(20);
        
        $data = [
            'title' => 'Time Tracking Admin Dashboard',
            'all_users' => $allUsers,
            'departments' => $departments,
            'time_statistics' => $timeStatistics,
            'currently_active' => $currentlyActive,
            'recent_activity' => $recentActivity,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'selected_user_id' => $userId,
            'selected_department' => $department
        ];
        
        $this->view('time/admin', $data);
    }
    
    /**
     * Get member details for admin (AJAX)
     */
    public function getMemberDetails() {
        // Check permissions
        if (!$this->userModel->hasPermission($_SESSION['user_id'], 'reports_read')) {
            $this->jsonResponse(['success' => false, 'message' => 'Access denied']);
            return;
        }
        
        $userId = $_GET['user_id'] ?? null;
        $date = $_GET['date'] ?? date('Y-m-d');
        
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'User ID required']);
            return;
        }
        
        $memberDetails = $this->timeModel->getUserDetailedSummary($userId, $date);
        $userInfo = $this->userModel->getUserById($userId);
        
        // Generate HTML for the modal
        $html = $this->generateMemberDetailsHTML($memberDetails, $userInfo, $date);
        
        $this->jsonResponse([
            'success' => true,
            'html' => $html,
            'data' => $memberDetails
        ]);
    }
    
    /**
     * Admin action: Clock out user (emergency)
     */
    public function adminClockOut() {
        // Check if user is admin
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $this->jsonResponse(['success' => false, 'message' => 'Access denied']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $userId = $_POST['user_id'] ?? null;
        $reason = $_POST['reason'] ?? 'Admin override';
        
        if (!$userId) {
            $this->jsonResponse(['success' => false, 'message' => 'User ID required']);
            return;
        }
        
        $result = $this->timeModel->adminClockOut($userId, $_SESSION['user_id'], $reason);
        $this->jsonResponse($result);
    }
    
    /**
     * Generate detailed analytics for admin
     */
    public function analytics() {
        // Check if user is admin
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: /dashboard');
            exit();
        }
        
        $period = $_GET['period'] ?? 'month'; // week, month, quarter, year
        $department = $_GET['department'] ?? null;
        
        $analyticsData = $this->timeModel->getAnalyticsData($period, $department);
        $departments = $this->timeModel->getDepartments();
        
        $data = [
            'title' => 'Time Tracking Analytics',
            'analytics' => $analyticsData,
            'departments' => $departments,
            'selected_period' => $period,
            'selected_department' => $department
        ];
        
        $this->view('time/analytics', $data);
    }
    
    /**
     * Export time data to CSV
     */
    public function export() {
        // Check permissions
        if (!$this->userModel->hasPermission($_SESSION['user_id'], 'reports_read')) {
            header('Location: /dashboard');
            exit();
        }
        
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $userId = $_GET['user_id'] ?? null;
        
        $timeReport = $this->timeModel->getTimeReport($startDate, $endDate, $userId);
        
        // Set CSV headers
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="time_report_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, [
            'Date', 'Employee', 'Clock In', 'Clock Out', 
            'Total Hours', 'Break Minutes', 'Net Work Hours', 'Status'
        ]);
        
        // CSV data
        foreach ($timeReport as $entry) {
            fputcsv($output, [
                $entry['work_date'],
                $entry['full_name'],
                $entry['clock_in_time'],
                $entry['clock_out_time'] ?? 'Not clocked out',
                $entry['total_hours'] ?? 'N/A',
                $entry['total_break_minutes'],
                $entry['net_work_minutes'] ? round($entry['net_work_minutes'] / 60, 2) : 'N/A',
                ucfirst($entry['status'])
            ]);
        }
        
        fclose($output);
    }
    
    /**
     * Generate HTML for member details modal
     */
    private function generateMemberDetailsHTML($memberDetails, $userInfo, $date) {
        $html = '<div class="container-fluid">';
        
        // User Info Header
        $html .= '<div class="row mb-3">';
        $html .= '<div class="col-md-12">';
        $html .= '<div class="d-flex align-items-center mb-3">';
        $html .= '<div class="bg-primary rounded-circle p-3 me-3">';
        $html .= '<i class="fas fa-user text-white fa-lg"></i>';
        $html .= '</div>';
        $html .= '<div>';
        $html .= '<h5 class="mb-1">' . htmlspecialchars($userInfo['full_name'] ?? $userInfo['username']) . '</h5>';
        $html .= '<small class="text-muted">@' . htmlspecialchars($userInfo['username']) . ' • ' . date('M d, Y', strtotime($date)) . '</small>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Time Summary Cards
        $html .= '<div class="row mb-4">';
        $html .= '<div class="col-md-4">';
        $html .= '<div class="card bg-light">';
        $html .= '<div class="card-body text-center">';
        $html .= '<i class="fas fa-clock text-primary fa-2x mb-2"></i>';
        $html .= '<h6>Total Hours</h6>';
        $html .= '<h4 class="text-primary">' . number_format($memberDetails['total_hours'] ?? 0, 2) . '</h4>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="col-md-4">';
        $html .= '<div class="card bg-light">';
        $html .= '<div class="card-body text-center">';
        $html .= '<i class="fas fa-coffee text-warning fa-2x mb-2"></i>';
        $html .= '<h6>Break Time</h6>';
        $breakMins = $memberDetails['total_break_minutes'] ?? 0;
        $html .= '<h4 class="text-warning">' . sprintf('%02d:%02d', floor($breakMins / 60), $breakMins % 60) . '</h4>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="col-md-4">';
        $html .= '<div class="card bg-light">';
        $html .= '<div class="card-body text-center">';
        $html .= '<i class="fas fa-chart-line text-success fa-2x mb-2"></i>';
        $html .= '<h6>Net Work</h6>';
        $netHours = ($memberDetails['total_hours'] ?? 0) - (($memberDetails['total_break_minutes'] ?? 0) / 60);
        $html .= '<h4 class="text-success">' . number_format(max(0, $netHours), 2) . '</h4>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Time Entries Detail
        if (!empty($memberDetails['entries'])) {
            $html .= '<div class="row">';
            $html .= '<div class="col-md-12">';
            $html .= '<h6>Time Entries</h6>';
            $html .= '<div class="table-responsive">';
            $html .= '<table class="table table-sm">';
            $html .= '<thead><tr><th>Clock In</th><th>Clock Out</th><th>Duration</th><th>Breaks</th><th>Status</th></tr></thead>';
            $html .= '<tbody>';
            
            foreach ($memberDetails['entries'] as $entry) {
                $html .= '<tr>';
                $html .= '<td><span class="badge bg-success">' . date('H:i', strtotime($entry['clock_in_time'])) . '</span></td>';
                $html .= '<td>';
                if ($entry['clock_out_time']) {
                    $html .= '<span class="badge bg-danger">' . date('H:i', strtotime($entry['clock_out_time'])) . '</span>';
                } else {
                    $html .= '<span class="badge bg-warning">Active</span>';
                }
                $html .= '</td>';
                $html .= '<td>' . number_format($entry['total_hours'] ?? 0, 2) . ' hrs</td>';
                $html .= '<td>' . ($entry['break_count'] ?? 0) . '</td>';
                $html .= '<td><span class="badge bg-' . ($entry['status'] === 'completed' ? 'success' : 'primary') . '">' . ucfirst($entry['status']) . '</span></td>';
                $html .= '</tr>';
            }
            
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Return JSON response
     */
    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}