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
            $recentEntries = $this->timeModel->getUserTimeEntries($userId, date('Y-m-d', strtotime('-7 days')), null, 10);
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
        
        $result = $this->timeModel->clockIn($userId, $notes);
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
     * View time history
     */
    public function history() {
        $userId = $_SESSION['user_id'];
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $timeEntries = $this->timeModel->getUserTimeEntries($userId, $startDate, $endDate);
        
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
     * Return JSON response
     */
    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}