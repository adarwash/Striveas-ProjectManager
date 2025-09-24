<?php

/**
 * Reports Controller for System Analytics and Reporting
 */
class Reports extends Controller {
    
    public function __construct() {
        // Check if user is logged in and has admin permissions
        if (!isLoggedIn()) {
            redirect('users/login');
        }
        
        if (!hasPermission('admin.access')) {
            flash('error', 'You do not have permission to access reports.');
            redirect('dashboard');
        }
    }
    
    /**
     * Reports dashboard/index page
     */
    public function index() {
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
        
        $reportData = [
            // Overview Statistics
            'overview' => [
                'total_users' => $this->safeCount($userModel, 'getAllUsers'),
                'active_clients' => $this->safeCount($clientModel, 'getActiveClientsCount'),
                'total_projects' => $this->safeCount($projectModel, 'getAllProjects'),
                'open_tickets' => $this->safeCount($ticketModel, 'getOpenTicketsCount'),
                'completed_tasks' => $this->getTotalTasksByStatus($taskModel, 'completed'),
                'pending_tasks' => $this->safeCount($taskModel, 'getOpenTasksCount')
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
    private function getTotalTasksByStatus($taskModel, $status) {
        try {
            $tasks = $taskModel->getAllTasks();
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
}
