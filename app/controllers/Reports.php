<?php

/**
 * Reports Controller for System Analytics and Reporting
 */
class Reports extends Controller {
    
    public function __construct() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('users/login');
        }
    }

    private function requireAdminAccess(): void {
        if (!hasPermission('admin.access')) {
            flash('error', 'You do not have permission to access reports.');
            redirect('dashboard');
        }
    }

    private function currentRoleId(): ?int {
        return isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : null;
    }

    private function isAdminRole(): bool {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    private function blockedClientIds(): array {
        $clientModel = $this->model('Client');
        return $clientModel->getBlockedClientIdsForRole($this->currentRoleId(), $this->isAdminRole());
    }
    
    /**
     * Reports dashboard/index page
     */
    public function index() {
        $this->requireAdminAccess();
        $reportData = $this->generateReportData();
        
        $viewData = [
            'title' => 'System Reports & Analytics',
            'description' => 'Comprehensive system reports and analytics',
            'reportData' => $reportData
        ];
        
        $this->view('reports/index', $viewData);
    }
    
    /**
     * Generate comprehensive report data
     */
    private function generateReportData() {
        $userModel = $this->model('User');
        $projectModel = $this->model('Project');
        $taskModel = $this->model('Task');
        $ticketModel = $this->model('Ticket');
        $clientModel = $this->model('Client');
        $timeModel = $this->model('TimeTracking');
        
        // Get date ranges
        $today = date('Y-m-d');
        $lastWeek = date('Y-m-d', strtotime('-7 days'));
        $lastMonth = date('Y-m-d', strtotime('-30 days'));
        $lastYear = date('Y-m-d', strtotime('-365 days'));
        
        $blockedClientIds = $this->blockedClientIds();

        $allProjects = $projectModel->getAllProjects();
        if (!empty($blockedClientIds)) {
            $allProjects = array_values(array_filter($allProjects, function($project) use ($blockedClientIds) {
                $clientId = isset($project->client_id) ? (int)$project->client_id : null;
                return empty($clientId) || !in_array($clientId, $blockedClientIds, true);
            }));
        }

        $reportData = [
            // Overview Statistics
            'overview' => [
                'total_users' => $this->safeCount($userModel, 'getAllUsers'),
                'active_clients' => $this->safeCount($clientModel, 'getActiveClientsCount'),
                'total_projects' => is_array($allProjects) ? count($allProjects) : 0,
                'open_tickets' => $this->safeCount($ticketModel, 'getOpenTicketsCount'),
                'completed_tasks' => $this->getTotalTasksByStatus($taskModel, 'completed', $blockedClientIds),
                'pending_tasks' => $taskModel->getOpenTasksCount($blockedClientIds)
            ],
            
            // User Statistics
            'users' => [
                'by_role' => $this->getUsersByRole($userModel),
                'recent_registrations' => $this->getRecentRegistrations($userModel, $lastMonth),
                'active_users' => $this->getActiveUsers($userModel, $lastWeek)
            ],
            
            // Project Statistics
            'projects' => [
                'by_status' => $this->getProjectsByStatus($projectModel),
                'completed_this_month' => $this->getProjectsCompletedInPeriod($projectModel, $lastMonth),
                'overdue' => $this->getOverdueProjects($projectModel)
            ],
            
            // Ticket Statistics
            'tickets' => [
                'by_status' => $this->getTicketsByStatus($ticketModel),
                'by_priority' => $this->getTicketsByPriority($ticketModel),
                'resolved_this_week' => $this->getTicketsResolvedInPeriod($ticketModel, $lastWeek),
                'avg_resolution_time' => $this->getAverageResolutionTime($ticketModel)
            ],
            
            // Time Tracking
            'time_tracking' => [
                'total_hours_logged' => $this->getTotalHoursLogged($timeModel),
                'hours_this_month' => $this->getHoursInPeriod($timeModel, $lastMonth),
                'top_time_loggers' => $this->getTopTimeLoggers($timeModel, $lastMonth)
            ],
            
            // Performance Metrics
            'performance' => [
                'project_completion_rate' => $this->getProjectCompletionRate($projectModel),
                'ticket_resolution_rate' => $this->getTicketResolutionRate($ticketModel),
                'user_productivity' => $this->getUserProductivity($userModel, $taskModel, $lastMonth)
            ]
        ];
        
        return $reportData;
    }
    
    /**
     * Safe count method to handle potential errors
     */
    private function safeCount($model, $method) {
        try {
            if (method_exists($model, $method)) {
                $result = $model->$method();
                return is_array($result) ? count($result) : (int)$result;
            }
            return 0;
        } catch (Exception $e) {
            error_log("Report error in $method: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get total tasks by status
     */
    private function getTotalTasksByStatus($taskModel, $status, array $blockedClientIds = []) {
        try {
            $tasks = $taskModel->getAllTasks($blockedClientIds);
            if (!is_array($tasks)) {
                return 0;
            }
            
            $count = 0;
            foreach ($tasks as $task) {
                if (isset($task['status']) && strtolower($task['status']) === strtolower($status)) {
                    $count++;
                }
            }
            return $count;
        } catch (Exception $e) {
            error_log("getTotalTasksByStatus Error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get users by role
     */
    private function getUsersByRole($userModel) {
        try {
            // Get all users and group by role since getUsersByRole expects a parameter
            $users = $userModel->getAllUsers();
            $byRole = [];
            
            if (is_array($users)) {
                foreach ($users as $user) {
                    $role = $user['role'] ?? 'User';
                    $byRole[$role] = ($byRole[$role] ?? 0) + 1;
                }
            }
            
            // Ensure we have at least the basic roles
            if (empty($byRole)) {
                $byRole = ['Admin' => 0, 'User' => 0, 'Technician' => 0];
            }
            
            return $byRole;
        } catch (Exception $e) {
            error_log('getUsersByRole Error: ' . $e->getMessage());
            return ['Admin' => 1, 'User' => 0, 'Technician' => 0];
        }
    }
    
    /**
     * Get recent user registrations
     */
    private function getRecentRegistrations($userModel, $since) {
        try {
            if (method_exists($userModel, 'getUsersRegisteredSince')) {
                return count($userModel->getUsersRegisteredSince($since));
            }
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get active users
     */
    private function getActiveUsers($userModel, $since) {
        try {
            if (method_exists($userModel, 'getActiveUsersSince')) {
                return count($userModel->getActiveUsersSince($since));
            }
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get projects by status
     */
    private function getProjectsByStatus($projectModel) {
        try {
            // Get all projects and group by status
            $projects = $projectModel->getAllProjects();
            $byStatus = [];
            
            if (is_array($projects)) {
                foreach ($projects as $project) {
                    $status = $project['status'] ?? 'Unknown';
                    $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;
                }
            }
            
            // Ensure we have at least the basic statuses
            if (empty($byStatus)) {
                $byStatus = ['Planning' => 0, 'In Progress' => 0, 'Completed' => 0, 'On Hold' => 0];
            }
            
            return $byStatus;
        } catch (Exception $e) {
            error_log('getProjectsByStatus Error: ' . $e->getMessage());
            return ['Planning' => 0, 'In Progress' => 0, 'Completed' => 0, 'On Hold' => 0];
        }
    }
    
    /**
     * Get projects completed in period
     */
    private function getProjectsCompletedInPeriod($projectModel, $since) {
        try {
            if (method_exists($projectModel, 'getProjectsCompletedSince')) {
                return count($projectModel->getProjectsCompletedSince($since));
            }
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get overdue projects
     */
    private function getOverdueProjects($projectModel) {
        try {
            if (method_exists($projectModel, 'getOverdueProjects')) {
                return count($projectModel->getOverdueProjects());
            }
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get tickets by status
     */
    private function getTicketsByStatus($ticketModel) {
        try {
            // Try to get all tickets and group by status
            $tickets = $this->safeGetAll($ticketModel, 'getAllTickets');
            $byStatus = [];
            
            if (is_array($tickets)) {
                foreach ($tickets as $ticket) {
                    $status = $ticket['status'] ?? 'Open';
                    $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;
                }
            }
            
            // Ensure we have basic statuses
            if (empty($byStatus)) {
                $byStatus = ['Open' => 0, 'In Progress' => 0, 'Resolved' => 0, 'Closed' => 0];
            }
            
            return $byStatus;
        } catch (Exception $e) {
            error_log('getTicketsByStatus Error: ' . $e->getMessage());
            return ['Open' => 0, 'In Progress' => 0, 'Resolved' => 0, 'Closed' => 0];
        }
    }
    
    /**
     * Get tickets by priority
     */
    private function getTicketsByPriority($ticketModel) {
        try {
            // Try to get all tickets and group by priority
            $tickets = $this->safeGetAll($ticketModel, 'getAllTickets');
            $byPriority = [];
            
            if (is_array($tickets)) {
                foreach ($tickets as $ticket) {
                    $priority = $ticket['priority'] ?? 'Medium';
                    $byPriority[$priority] = ($byPriority[$priority] ?? 0) + 1;
                }
            }
            
            // Ensure we have basic priorities
            if (empty($byPriority)) {
                $byPriority = ['Low' => 0, 'Medium' => 0, 'High' => 0, 'Critical' => 0];
            }
            
            return $byPriority;
        } catch (Exception $e) {
            error_log('getTicketsByPriority Error: ' . $e->getMessage());
            return ['Low' => 0, 'Medium' => 0, 'High' => 0, 'Critical' => 0];
        }
    }
    
    /**
     * Get tickets resolved in period
     */
    private function getTicketsResolvedInPeriod($ticketModel, $since) {
        try {
            if (method_exists($ticketModel, 'getTicketsResolvedSince')) {
                return count($ticketModel->getTicketsResolvedSince($since));
            }
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get average resolution time
     */
    private function getAverageResolutionTime($ticketModel) {
        try {
            if (method_exists($ticketModel, 'getAverageResolutionTime')) {
                return $ticketModel->getAverageResolutionTime();
            }
            return '0 hours';
        } catch (Exception $e) {
            return '0 hours';
        }
    }
    
    /**
     * Get total hours logged
     */
    private function getTotalHoursLogged($timeModel) {
        try {
            if (method_exists($timeModel, 'getTotalHoursLogged')) {
                return $timeModel->getTotalHoursLogged();
            }
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get hours in period
     */
    private function getHoursInPeriod($timeModel, $since) {
        try {
            if (method_exists($timeModel, 'getHoursLoggedSince')) {
                return $timeModel->getHoursLoggedSince($since);
            }
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get top time loggers
     */
    private function getTopTimeLoggers($timeModel, $since) {
        try {
            if (method_exists($timeModel, 'getTopTimeLoggersSince')) {
                return $timeModel->getTopTimeLoggersSince($since, 5);
            }
            return [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get project completion rate
     */
    private function getProjectCompletionRate($projectModel) {
        try {
            if (method_exists($projectModel, 'getCompletionRate')) {
                return $projectModel->getCompletionRate();
            }
            return '0%';
        } catch (Exception $e) {
            return '0%';
        }
    }
    
    /**
     * Get ticket resolution rate
     */
    private function getTicketResolutionRate($ticketModel) {
        try {
            if (method_exists($ticketModel, 'getResolutionRate')) {
                return $ticketModel->getResolutionRate();
            }
            return '0%';
        } catch (Exception $e) {
            return '0%';
        }
    }
    
    /**
     * Get user productivity metrics
     */
    private function getUserProductivity($userModel, $taskModel, $since) {
        try {
            if (method_exists($taskModel, 'getUserProductivitySince')) {
                return $taskModel->getUserProductivitySince($since);
            }
            return [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Generate specific report type
     */
    public function generate($type = null) {
        $this->requireAdminAccess();
        if (!$type) {
            redirect('reports');
        }
        
        // Generate specific report based on type
        switch ($type) {
            case 'users':
                $this->generateUserReport();
                break;
            case 'projects':
                $this->generateProjectReport();
                break;
            case 'tickets':
                $this->generateTicketReport();
                break;
            case 'time':
                $this->generateTimeReport();
                break;
            default:
                redirect('reports');
        }
    }
    
    /**
     * Export report as CSV
     */
    public function export($type = null) {
        $this->requireAdminAccess();
        if (!$type) {
            redirect('reports');
        }
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Generate CSV based on type
        switch ($type) {
            case 'users':
                $this->exportUserReport($output);
                break;
            case 'projects':
                $this->exportProjectReport($output);
                break;
            case 'tickets':
                $this->exportTicketReport($output);
                break;
            default:
                fputcsv($output, ['Error', 'Invalid report type']);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export user report
     */
    private function exportUserReport($output) {
        fputcsv($output, ['User ID', 'Name', 'Email', 'Role', 'Created Date', 'Last Login']);
        
        $userModel = $this->model('User');
        $users = $userModel->getAllUsers();
        
        foreach ($users as $user) {
            fputcsv($output, [
                $user['id'] ?? '',
                $user['full_name'] ?? $user['name'] ?? '',
                $user['email'] ?? '',
                $user['role'] ?? '',
                $user['created_at'] ?? '',
                $user['last_login'] ?? ''
            ]);
        }
    }
    
    /**
     * Export project report
     */
    private function exportProjectReport($output) {
        fputcsv($output, ['Project ID', 'Name', 'Status', 'Progress', 'Start Date', 'End Date', 'Client']);
        
        $projectModel = $this->model('Project');
        $projects = $projectModel->getAllProjects();
        
        foreach ($projects as $project) {
            fputcsv($output, [
                $project['id'] ?? '',
                $project['title'] ?? $project['name'] ?? '',
                $project['status'] ?? '',
                ($project['progress'] ?? 0) . '%',
                $project['start_date'] ?? '',
                $project['end_date'] ?? '',
                $project['client_name'] ?? ''
            ]);
        }
    }
    
    /**
     * Export ticket report
     */
    private function exportTicketReport($output) {
        fputcsv($output, ['Ticket ID', 'Subject', 'Status', 'Priority', 'Created Date', 'Resolved Date', 'Assigned To']);
        
        $ticketModel = $this->model('Ticket');
        $tickets = $this->safeGetAll($ticketModel, 'getAllTickets');
        
        foreach ($tickets as $ticket) {
            fputcsv($output, [
                $ticket['id'] ?? '',
                $ticket['subject'] ?? $ticket['title'] ?? '',
                $ticket['status'] ?? '',
                $ticket['priority'] ?? '',
                $ticket['created_at'] ?? '',
                $ticket['resolved_at'] ?? '',
                $ticket['assigned_to'] ?? ''
            ]);
        }
    }
    
    /**
     * Safe method to get all records
     */
    private function safeGetAll($model, $method) {
        try {
            if (method_exists($model, $method)) {
                return $model->$method();
            }
            return [];
        } catch (Exception $e) {
            error_log("Report export error in $method: " . $e->getMessage());
            return [];
        }
    }

    private function ensureCsrfToken(): void {
        if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === '') {
            try {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            } catch (Exception $e) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(8));
            }
        }
    }

    private function validateCsrf(array $payload): bool {
        $session = (string)($_SESSION['csrf_token'] ?? '');
        if ($session === '') {
            return true; // permissive fallback
        }
        $csrf = (string)($payload['csrf_token'] ?? '');
        return hash_equals($session, $csrf);
    }

    /**
     * Dynamic Report Builder (admin-only)
     * URL: /reports/builder or /reports/builder/{id}
     */
    public function builder($id = null) {
        $this->requireAdminAccess();
        $this->ensureCsrfToken();

        require_once APPROOT . '/app/services/DynamicReportService.php';
        $svc = new DynamicReportService();
        $datasets = $svc->getDatasetPresets();

        $roles = [];
        try {
            $roleModel = $this->model('Role');
            $roles = $roleModel->getAllRoles();
        } catch (Exception $e) {
            $roles = [];
        }

        $report = null;
        $reportDefinition = null;
        if (!empty($id)) {
            try {
                $rid = (int)$id;
                $repModel = $this->model('ReportDefinition');
                $row = $repModel->getById($rid);
                if (!empty($row)) {
                    $report = $row;
                    $decoded = json_decode((string)($row['definition_json'] ?? ''), true);
                    if (is_array($decoded)) {
                        $reportDefinition = $decoded;
                    }
                }
            } catch (Exception $e) {
                $report = null;
                $reportDefinition = null;
            }
        }

        $this->view('reports/builder', [
            'title' => 'Report Builder',
            'datasets' => $datasets,
            'roles' => $roles,
            'report' => $report,
            'report_definition' => $reportDefinition,
            'csrf_token' => (string)($_SESSION['csrf_token'] ?? ''),
        ]);
    }

    /**
     * Saved reports library (admin + permitted viewers).
     * URL: /reports/saved
     */
    public function saved() {
        $this->ensureCsrfToken();

        $isAdmin = hasPermission('admin.access');
        $roleId = $this->currentRoleId();

        $reports = [];
        try {
            $repModel = $this->model('ReportDefinition');
            $reports = $repModel->listForUser($roleId, $isAdmin);
        } catch (Exception $e) {
            $reports = [];
        }

        require_once APPROOT . '/app/services/DynamicReportService.php';
        $svc = new DynamicReportService();
        $datasets = $svc->getDatasetPresets();
        $datasetLabels = [];
        foreach ($datasets as $d) {
            $datasetLabels[(string)($d['key'] ?? '')] = (string)($d['label'] ?? '');
        }

        $this->view('reports/saved', [
            'title' => 'Saved Reports',
            'reports' => $reports,
            'dataset_labels' => $datasetLabels,
            'is_admin' => $isAdmin,
            'csrf_token' => (string)($_SESSION['csrf_token'] ?? ''),
        ]);
    }

    /**
     * Run a saved report (admin + permitted viewers).
     * URL: /reports/run/{id}
     */
    public function run($id = null) {
        $this->ensureCsrfToken();

        $rid = (int)($id ?? 0);
        if ($rid <= 0) {
            redirect('reports/saved');
        }

        $isAdmin = hasPermission('admin.access');
        $roleId = $this->currentRoleId();

        $repModel = $this->model('ReportDefinition');
        $report = $repModel->getById($rid);
        if (empty($report) || empty($report['is_active'])) {
            flash('error', 'Report not found.');
            redirect('reports/saved');
        }
        if (!$repModel->userCanView($report, $roleId, $isAdmin)) {
            flash('error', 'You do not have access to this report.');
            redirect('dashboard');
        }

        $this->view('reports/run', [
            'title' => 'Run Report',
            'report' => $report,
            'csrf_token' => (string)($_SESSION['csrf_token'] ?? ''),
        ]);
    }

    /**
     * AJAX: get available fields for a dataset.
     * GET /reports/ajaxFields?dataset=tickets
     */
    public function ajaxFields() {
        header('Content-Type: application/json');
        $dataset = strtolower(trim((string)($_GET['dataset'] ?? '')));
        if ($dataset === '') {
            echo json_encode(['success' => false, 'message' => 'Missing dataset']);
            exit;
        }

        // Admin-only: field discovery is part of the builder
        $this->requireAdminAccess();

        require_once APPROOT . '/app/services/DynamicReportService.php';
        $svc = new DynamicReportService();
        $fields = $svc->getFieldsForDataset($dataset);
        if (empty($fields)) {
            echo json_encode(['success' => false, 'message' => 'Unknown dataset']);
            exit;
        }
        echo json_encode(['success' => true, 'dataset' => $dataset, 'fields' => $fields]);
        exit;
    }

    /**
     * AJAX: preview run.
     * POST JSON to /reports/ajaxPreview
     * Either provide report_id (saved) OR a full definition (builder/admin).
     */
    public function ajaxPreview() {
        header('Content-Type: application/json');

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            $payload = $_POST ?? [];
        }

        $this->ensureCsrfToken();
        if (!$this->validateCsrf($payload)) {
            echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
            exit;
        }

        $isAdmin = hasPermission('admin.access');
        $roleId = $this->currentRoleId();

        $definition = null;
        $reportId = isset($payload['report_id']) ? (int)$payload['report_id'] : 0;
        if ($reportId > 0) {
            try {
                $repModel = $this->model('ReportDefinition');
                $report = $repModel->getById($reportId);
                if (empty($report) || empty($report['is_active'])) {
                    echo json_encode(['success' => false, 'message' => 'Report not found']);
                    exit;
                }
                if (!$repModel->userCanView($report, $roleId, $isAdmin)) {
                    echo json_encode(['success' => false, 'message' => 'Not permitted']);
                    exit;
                }
                $decoded = json_decode((string)($report['definition_json'] ?? ''), true);
                if (!is_array($decoded)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid report definition']);
                    exit;
                }
                $definition = $decoded;
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Failed to load report']);
                exit;
            }
        } else {
            // Builder preview requires admin access
            $this->requireAdminAccess();
            $definition = is_array($payload['definition'] ?? null) ? $payload['definition'] : $payload;
        }

        $page = max(1, (int)($payload['page'] ?? 1));
        $perPage = (int)($payload['per_page'] ?? 25);

        // Apply current paging to definition (non-destructive)
        if (is_array($definition)) {
            $definition['page'] = $page;
            $definition['per_page'] = $perPage;
        }

        require_once APPROOT . '/app/services/DynamicReportService.php';
        $svc = new DynamicReportService();
        $blocked = $this->blockedClientIds();
        $res = $svc->run(
            is_array($definition) ? $definition : [],
            [
                'role_id' => $roleId,
                'is_admin' => $isAdmin,
                'blocked_client_ids' => $blocked,
            ],
            [
                'page' => $page,
                'per_page' => $perPage,
            ]
        );

        echo json_encode($res);
        exit;
    }

    /**
     * AJAX: save report definition (admin-only).
     * POST JSON to /reports/ajaxSaveReport
     */
    public function ajaxSaveReport() {
        header('Content-Type: application/json');
        $this->requireAdminAccess();

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            $payload = $_POST ?? [];
        }

        $this->ensureCsrfToken();
        if (!$this->validateCsrf($payload)) {
            echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
            exit;
        }

        $name = trim((string)($payload['name'] ?? ''));
        $description = (string)($payload['description'] ?? '');
        $visibility = strtolower(trim((string)($payload['visibility'] ?? 'admin')));
        $allowedRoleIds = $payload['allowed_role_ids'] ?? null;
        $definition = is_array($payload['definition'] ?? null) ? $payload['definition'] : null;

        if ($name === '' || !is_array($definition)) {
            echo json_encode(['success' => false, 'message' => 'Missing name or definition']);
            exit;
        }

        $dataset = strtolower(trim((string)($definition['dataset'] ?? '')));
        if ($dataset === '') {
            echo json_encode(['success' => false, 'message' => 'Missing dataset']);
            exit;
        }
        if ($visibility === 'roles') {
            $ids = is_array($allowedRoleIds) ? $allowedRoleIds : [];
            $ids = array_values(array_filter(array_map('intval', $ids), function($v){ return $v > 0; }));
            if (empty($ids)) {
                echo json_encode(['success' => false, 'message' => 'Select at least one allowed role']);
                exit;
            }
        }

        // Validate dataset is supported
        try {
            require_once APPROOT . '/app/services/DynamicReportService.php';
            $svc = new DynamicReportService();
            $supported = array_map(function($d){ return (string)($d['key'] ?? ''); }, $svc->getDatasetPresets());
            if (!in_array($dataset, $supported, true)) {
                echo json_encode(['success' => false, 'message' => 'Unsupported dataset']);
                exit;
            }
        } catch (Exception $e) {
            // ignore
        }

        // Store a normalized JSON definition
        $definitionJson = json_encode($definition);
        if (!is_string($definitionJson) || $definitionJson === '' || $definitionJson === 'null') {
            echo json_encode(['success' => false, 'message' => 'Invalid definition']);
            exit;
        }

        $repModel = $this->model('ReportDefinition');
        $reportId = isset($payload['report_id']) ? (int)$payload['report_id'] : 0;

        if ($reportId > 0) {
            $existing = $repModel->getById($reportId);
            if (empty($existing)) {
                echo json_encode(['success' => false, 'message' => 'Report not found']);
                exit;
            }
            if (!$repModel->userCanEdit($existing, (int)($_SESSION['user_id'] ?? 0), true)) {
                echo json_encode(['success' => false, 'message' => 'Not permitted']);
                exit;
            }
            $ok = $repModel->update($reportId, [
                'name' => $name,
                'description' => $description,
                'dataset' => $dataset,
                'definition_json' => $definitionJson,
                'visibility' => $visibility,
                'allowed_role_ids' => $allowedRoleIds,
            ]);
            echo json_encode(['success' => (bool)$ok, 'report_id' => $reportId]);
            exit;
        }

        $newId = $repModel->create([
            'name' => $name,
            'description' => $description,
            'dataset' => $dataset,
            'definition_json' => $definitionJson,
            'visibility' => $visibility,
            'allowed_role_ids' => $allowedRoleIds,
            'created_by' => (int)($_SESSION['user_id'] ?? 0),
        ]);

        echo json_encode(['success' => !empty($newId), 'report_id' => (int)$newId]);
        exit;
    }

    /**
     * Export CSV for a saved report (permitted) or builder definition (admin-only).
     * POST JSON to /reports/exportDynamicCsv
     */
    public function exportDynamicCsv() {
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            $payload = $_POST ?? [];
        }

        $this->ensureCsrfToken();
        if (!$this->validateCsrf($payload)) {
            header('Content-Type: text/plain');
            http_response_code(403);
            echo 'CSRF validation failed';
            exit;
        }

        $isAdmin = hasPermission('admin.access');
        $roleId = $this->currentRoleId();

        $definition = null;
        $reportId = isset($payload['report_id']) ? (int)$payload['report_id'] : 0;
        $reportName = 'dynamic_report';
        if ($reportId > 0) {
            $repModel = $this->model('ReportDefinition');
            $report = $repModel->getById($reportId);
            if (empty($report) || empty($report['is_active'])) {
                header('Content-Type: text/plain');
                http_response_code(404);
                echo 'Report not found';
                exit;
            }
            if (!$repModel->userCanView($report, $roleId, $isAdmin)) {
                header('Content-Type: text/plain');
                http_response_code(403);
                echo 'Not permitted';
                exit;
            }
            $reportName = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string)($report['name'] ?? 'report')) ?: 'report';
            $decoded = json_decode((string)($report['definition_json'] ?? ''), true);
            if (!is_array($decoded)) {
                header('Content-Type: text/plain');
                http_response_code(400);
                echo 'Invalid report definition';
                exit;
            }
            $definition = $decoded;
        } else {
            // Builder export requires admin
            $this->requireAdminAccess();
            $definition = is_array($payload['definition'] ?? null) ? $payload['definition'] : $payload;
        }

        require_once APPROOT . '/app/services/DynamicReportService.php';
        $svc = new DynamicReportService();
        $blocked = $this->blockedClientIds();

        $maxExport = 5000;
        $defLimit = isset($definition['limit']) ? (int)$definition['limit'] : 0;
        $exportLimit = $defLimit > 0 ? min($maxExport, $defLimit) : $maxExport;

        $filename = $reportName . '_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('X-Content-Type-Options: nosniff');

        // UTF-8 BOM for Excel
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');
        if (!$out) {
            exit;
        }

        $page = 1;
        $perPage = 500;
        $writtenHeader = false;
        $written = 0;

        while (true) {
            $definition['page'] = $page;
            $definition['per_page'] = $perPage;
            $definition['limit'] = $exportLimit;

            $res = $svc->run(
                $definition,
                [
                    'role_id' => $roleId,
                    'is_admin' => $isAdmin,
                    'blocked_client_ids' => $blocked,
                ],
                [
                    'page' => $page,
                    'per_page' => $perPage,
                ]
            );

            if (empty($res['success'])) {
                // output error row
                if (!$writtenHeader) {
                    fputcsv($out, ['Error']);
                }
                fputcsv($out, [(string)($res['message'] ?? 'Failed to export')]);
                break;
            }

            $cols = $res['columns'] ?? [];
            $rows = $res['rows'] ?? [];

            if (!$writtenHeader) {
                $headers = [];
                foreach ($cols as $c) {
                    $headers[] = (string)($c['label'] ?? $c['key'] ?? '');
                }
                fputcsv($out, $headers);
                $writtenHeader = true;
            }

            if (empty($rows)) {
                break;
            }

            foreach ($rows as $r) {
                $line = [];
                foreach ($cols as $c) {
                    $k = (string)($c['key'] ?? '');
                    $v = $k !== '' ? ($r[$k] ?? '') : '';
                    if (is_bool($v)) {
                        $v = $v ? '1' : '0';
                    } else if (is_array($v)) {
                        $v = json_encode($v);
                    }
                    $line[] = (string)$v;
                }
                fputcsv($out, $line);
                $written++;
                if ($written >= $exportLimit) {
                    break 2;
                }
            }

            $page++;
            if ($written >= $exportLimit) {
                break;
            }
        }

        fclose($out);
        exit;
    }
}
