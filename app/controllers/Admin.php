<?php

/**
 * Admin Controller for System Administration
 */
class Admin extends Controller {
    
    public function __construct() {
        // Check if user is logged in and has admin permissions
        if (!isLoggedIn()) {
            redirect('users/login');
        }
        
        if (!hasPermission('admin.access')) {
            flash('error', 'You do not have permission to access admin functions.');
            redirect('dashboard');
        }
    }
    
    /**
     * Admin dashboard/index page
     */
    public function index() {
        $viewData = [
            'title' => 'Admin Dashboard',
            'description' => 'System administration and configuration'
        ];
        
        $this->view('admin/index', $viewData);
    }
    
    /**
     * SLA Settings Management
     */
    public function slaSettings() {
        $db = new EasySQL(DB1);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Update SLA settings
            $priorities = $_POST['priorities'] ?? [];
            
            foreach ($priorities as $priorityId => $settings) {
                $db->update(
                    "UPDATE TicketPriorities SET 
                     response_time_hours = :response_hours,
                     resolution_time_hours = :resolution_hours
                     WHERE id = :id",
                    [
                        'response_hours' => (int)$settings['response_hours'],
                        'resolution_hours' => (int)$settings['resolution_hours'],
                        'id' => $priorityId
                    ]
                );
            }
            
            flash('success', 'SLA settings updated successfully.');
            redirect('admin/slaSettings');
        }
        
        // Get current priorities with SLA settings
        $priorities = $db->select(
            "SELECT id, name, display_name, response_time_hours, resolution_time_hours, sort_order 
             FROM TicketPriorities 
             WHERE is_active = 1 
             ORDER BY sort_order"
        );
        
        $viewData = [
            'priorities' => $priorities
        ];
        
        $this->view('admin/sla_settings', $viewData);
    }
} 