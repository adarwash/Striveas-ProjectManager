<?php

class Sites extends Controller {
    private $siteModel;
    private $userModel;
    private $clientModel;
    private $siteServiceModel;
    private $activityLogModel;
    
    /**
     * Initialize controller and load models
     */
    public function __construct() {
        // Make sure user is logged in
        if(!isLoggedIn()) {
            redirect('users/login');
        }
        
        // Load models
        $this->siteModel = $this->model('Site');
        $this->userModel = $this->model('User');
        $this->clientModel = $this->model('Client');
        $this->siteServiceModel = $this->model('SiteService');
        $this->activityLogModel = $this->model('ActivityLog');
        
        // Set the page for sidebar highlighting
        $_SESSION['page'] = 'sites';
    }
    
    /**
     * Sites index/list page
     */
    public function index() {
        // Check if Sites model exists
        if (!class_exists('Site')) {
            $data = [
                'title' => 'Sites - Not Available',
                'message' => 'The Sites module is not yet available. Please check back later.'
            ];
            
            $this->view('sites/not_available', $data);
            return;
        }
        
        // Get all sites
        $sites = $this->siteModel->getAllSites();
        
        $data = [
            'title' => 'Sites',
            'sites' => $sites
        ];
        
        $this->view('sites/index', $data);
    }
    
    /**
     * View a specific site
     * 
     * @param int $id Site ID
     */
    public function viewSite($id = null) {
        if (!$id) {
            redirect('sites');
            return;
        }
        
        // Get site details
        $site = $this->siteModel->getSiteById($id);
        
        if (!$site) {
            flash('site_error', 'Site not found', 'alert-danger');
            redirect('sites');
            return;
        }
        
        // Get employees assigned to this site
        $employees = $this->siteModel->getSiteEmployees($id);
        
        // Get clients assigned to this site
        $clients = $this->siteModel->getSiteClients($id);
        
        // Get projects linked to this site
        $linkedProjects = $this->siteModel->getLinkedProjects($id);
        
        // Get technician visits
        try {
            $siteVisitModel = $this->model('SiteVisit');
            $visits = $siteVisitModel->getVisitsBySite($id, 100);
        } catch (Exception $e) {
            $visits = [];
        }

        // Get services for this site
        try {
            $services = $this->siteServiceModel->listBySite((int)$id);
        } catch (Exception $e) {
            $services = [];
        }
        
        $data = [
            'title' => $site['name'],
            'site' => $site,
            'employees' => $employees,
            'clients' => $clients,
            'linked_projects' => $linkedProjects,
            'visits' => $visits,
            'services' => $services
        ];
        
        $this->view('sites/view', $data);
    }

    /**
     * Add a service for a site
     */
    public function addService($siteId = null) {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
            flash('site_error', 'You do not have permission to manage services', 'alert-danger');
            redirect('sites');
            return;
        }
        if (!$siteId) { redirect('sites'); return; }
        $site = $this->siteModel->getSiteById((int)$siteId);
        if (!$site) { flash('site_error', 'Site not found', 'alert-danger'); redirect('sites'); return; }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $payload = [
                'service_name' => trim($_POST['service_name'] ?? ''),
                'service_type' => trim($_POST['service_type'] ?? ''),
                'start_date' => !empty($_POST['start_date']) ? date('Y-m-d', strtotime($_POST['start_date'])) : null,
                'end_date' => !empty($_POST['end_date']) ? date('Y-m-d', strtotime($_POST['end_date'])) : null,
                'notes' => trim($_POST['notes'] ?? '')
            ];
            if ($payload['service_name'] === '') {
                flash('site_error', 'Service name is required', 'alert-danger');
                redirect('/sites/viewSite/' . (int)$siteId);
                return;
            }
            if ($this->siteServiceModel->add((int)$siteId, $payload)) {
                log_activity('site', (int)$siteId, 'updated', 'Added service "' . ($payload['service_name'] ?? '') . '"', [
                    'service_name' => $payload['service_name'] ?? null,
                    'service_type' => $payload['service_type'] ?? null
                ]);
                flash('site_success', 'Service added');
            } else {
                flash('site_error', 'Failed to add service', 'alert-danger');
            }
            redirect('/sites/viewSite/' . (int)$siteId);
            return;
        }
        redirect('/sites/viewSite/' . (int)$siteId);
    }

    /**
     * Delete a service record
     */
    public function deleteService($serviceId = null) {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
            flash('site_error', 'You do not have permission to manage services', 'alert-danger');
            redirect('sites');
            return;
        }
        if (!$serviceId) { redirect('sites'); return; }
        // Optional: find site by service to redirect back properly
        $siteId = isset($_GET['site_id']) ? (int)$_GET['site_id'] : null;
        if ($this->siteServiceModel->delete((int)$serviceId)) {
            if ($siteId) {
                log_activity('site', (int)$siteId, 'updated', 'Deleted a site service', [
                    'service_id' => (int)$serviceId
                ]);
            }
            flash('site_success', 'Service deleted');
        } else {
            flash('site_error', 'Failed to delete service', 'alert-danger');
        }
        if ($siteId) {
            redirect('/sites/viewSite/' . $siteId);
        } else {
            redirect('sites');
        }
    }
    
    /**
     * Create a new site
     */
    public function create() {
        // Check if user has permission to create sites
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
            flash('site_error', 'You do not have permission to create sites', 'alert-danger');
            redirect('sites');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form
            
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Init data
            $data = [
                'title' => 'Create Site',
                'name' => trim($_POST['name']),
                'location' => trim($_POST['location']),
                'address' => trim($_POST['address']),
                'site_code' => trim($_POST['site_code']),
                'type' => trim($_POST['type']),
                'status' => trim($_POST['status']),
                'name_err' => '',
                'location_err' => '',
                'address_err' => '',
                'site_code_err' => '',
                'type_err' => '',
                'status_err' => ''
            ];
            
            // Validate name
            if (empty($data['name'])) {
                $data['name_err'] = 'Please enter site name';
            }
            
            // Make sure no errors
            if (empty($data['name_err']) && empty($data['location_err']) &&
                empty($data['address_err']) && empty($data['site_code_err']) &&
                empty($data['type_err']) && empty($data['status_err'])) {
                
                // Validated
                $newSiteId = $this->siteModel->addSite($data);
                if ($newSiteId) {
                    $siteId = is_int($newSiteId) ? (int)$newSiteId : 0;
                    log_activity('site', $siteId, 'created', 'Created site "' . ($data['name'] ?? '') . '"', [
                        'site_name' => $data['name'] ?? null,
                        'location' => $data['location'] ?? null,
                        'status' => $data['status'] ?? null
                    ]);
                    flash('site_success', 'Site added successfully');
                    redirect('sites');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Load view with errors
                $this->view('sites/create', $data);
            }
        } else {
            // Init data
            $data = [
                'title' => 'Create Site',
                'name' => '',
                'location' => '',
                'address' => '',
                'site_code' => '',
                'type' => '',
                'status' => 'Active',
                'name_err' => '',
                'location_err' => '',
                'address_err' => '',
                'site_code_err' => '',
                'type_err' => '',
                'status_err' => ''
            ];
            
            // Load view
            $this->view('sites/create', $data);
        }
    }
    
    /**
     * Edit existing site
     * 
     * @param int $id Site ID
     */
    public function edit($id = null) {
        // Check if user has permission to edit sites
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
            flash('site_error', 'You do not have permission to edit sites', 'alert-danger');
            redirect('sites');
            return;
        }
        
        if (!$id) {
            redirect('sites');
            return;
        }
        
        // Get site details
        $site = $this->siteModel->getSiteById($id);
        
        if (!$site) {
            flash('site_error', 'Site not found', 'alert-danger');
            redirect('sites');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form
            
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Init data
            $data = [
                'title' => 'Edit Site',
                'id' => $id,
                'name' => trim($_POST['name']),
                'location' => trim($_POST['location']),
                'address' => trim($_POST['address']),
                'site_code' => trim($_POST['site_code']),
                'type' => trim($_POST['type']),
                'status' => trim($_POST['status']),
                'name_err' => '',
                'location_err' => '',
                'address_err' => '',
                'site_code_err' => '',
                'type_err' => '',
                'status_err' => ''
            ];
            
            // Validate name
            if (empty($data['name'])) {
                $data['name_err'] = 'Please enter site name';
            }
            
            // Make sure no errors
            if (empty($data['name_err']) && empty($data['location_err']) &&
                empty($data['address_err']) && empty($data['site_code_err']) &&
                empty($data['type_err']) && empty($data['status_err'])) {
                
                // Validated
                if ($this->siteModel->updateSite($data)) {
                    $changes = [];
                    foreach (['name','location','address','site_code','type','status'] as $field) {
                        $old = $site[$field] ?? null;
                        $new = $data[$field] ?? null;
                        if ((string)$old !== (string)$new) {
                            $changes[$field] = ['old' => $old, 'new' => $new];
                        }
                    }
                    log_activity('site', (int)$id, 'updated', 'Updated site "' . ($data['name'] ?? ($site['name'] ?? '')) . '"', [
                        'site_name' => $data['name'] ?? ($site['name'] ?? null),
                        'changes' => $changes
                    ]);
                    flash('site_success', 'Site updated successfully');
                    redirect('sites');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Load view with errors
                $this->view('sites/edit', $data);
            }
        } else {
            // Init data with existing site info
            $data = [
                'title' => 'Edit Site',
                'id' => $id,
                'name' => $site['name'],
                'location' => $site['location'],
                'address' => $site['address'],
                'site_code' => $site['site_code'],
                'type' => $site['type'],
                'status' => $site['status'],
                'name_err' => '',
                'location_err' => '',
                'address_err' => '',
                'site_code_err' => '',
                'type_err' => '',
                'status_err' => ''
            ];
            
            // Load view
            $this->view('sites/edit', $data);
        }
    }
    
    /**
     * Delete site
     * 
     * @param int $id Site ID
     */
    public function delete($id = null) {
        // Check if user has permission to delete sites
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            flash('site_error', 'You do not have permission to delete sites', 'alert-danger');
            redirect('sites');
            return;
        }
        
        if (!$id) {
            redirect('sites');
            return;
        }
        
        // Get site details
        $site = $this->siteModel->getSiteById($id);
        
        if (!$site) {
            flash('site_error', 'Site not found', 'alert-danger');
            redirect('sites');
            return;
        }
        
        // Check if any employees are assigned to this site
        $employees = $this->siteModel->getSiteEmployees($id);
        
        if (!empty($employees)) {
            flash('site_error', 'Cannot delete site with assigned employees. Please reassign employees first.', 'alert-danger');
            redirect('sites/viewSite/' . $id);
            return;
        }
        
        // Delete the site
        if ($this->siteModel->deleteSite($id)) {
            log_activity('site', (int)$id, 'deleted', 'Deleted site "' . ($site['name'] ?? '') . '"', [
                'site_name' => $site['name'] ?? null
            ]);
            flash('site_success', 'Site deleted successfully');
        } else {
            flash('site_error', 'Failed to delete site', 'alert-danger');
        }
        
        redirect('sites');
    }
    
    /**
     * Assign employees to site
     * 
     * @param int $id Site ID
     */
    public function assignEmployees($id = null) {
        // Check if user has permission
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
            flash('site_error', 'You do not have permission to assign employees', 'alert-danger');
            redirect('sites');
            return;
        }
        
        if (!$id) {
            redirect('sites');
            return;
        }
        
        // Get site details
        $site = $this->siteModel->getSiteById($id);
        
        if (!$site) {
            flash('site_error', 'Site not found', 'alert-danger');
            redirect('sites');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form
            
            // Get selected employees from POST
            $selectedEmployees = isset($_POST['employees']) ? $_POST['employees'] : [];
            
            // Update site assignments
            if ($this->siteModel->updateSiteAssignments($id, $selectedEmployees)) {
                flash('site_success', 'Employee assignments updated successfully');
            } else {
                flash('site_error', 'Failed to update employee assignments', 'alert-danger');
            }
            
            redirect('/sites/viewSite/' . $id);
        } else {
            // Get all employees
            $employees = $this->userModel->getAllUsers();
            
            // Get employees currently assigned to this site
            $assignedEmployees = $this->siteModel->getSiteEmployees($id);
            
            // Create array of assigned employee IDs for easy checking
            $assignedIds = [];
            foreach ($assignedEmployees as $employee) {
                $assignedIds[] = $employee['user_id'];
            }
            
            $data = [
                'title' => 'Assign Employees to Site',
                'site' => $site,
                'employees' => $employees,
                'assignedIds' => $assignedIds
            ];
            
            $this->view('sites/assign_employees', $data);
        }
    }

    /**
     * Assign clients to a site
     * 
     * @param int $id Site ID
     */
    public function assignClients($id = null) {
        // Check if user has permission
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
            flash('site_error', 'You do not have permission to assign clients', 'alert-danger');
            redirect('sites');
            return;
        }
        
        if (!$id) {
            redirect('sites');
            return;
        }
        
        // Get site details
        $site = $this->siteModel->getSiteById($id);
        
        if (!$site) {
            flash('site_error', 'Site not found', 'alert-danger');
            redirect('sites');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Debug - Log raw POST data
            error_log('POST data received: ' . print_r($_POST, true));
            
            // Check if form was actually submitted (not just a GET request with POST in URL)
            if (!isset($_POST['form_submitted'])) {
                error_log('Form was not actually submitted - missing form_submitted field');
                flash('site_error', 'No form data submitted', 'alert-danger');
                redirect('/sites/assignClients/' . $id);
                return;
            }
            
            // Initialize empty arrays for clients and relationship types
            $selectedClients = [];
            $relationshipTypes = [];
            
            // Get selected clients from POST (empty array if none selected)
            if (isset($_POST['clients']) && is_array($_POST['clients'])) {
                foreach ($_POST['clients'] as $clientId) {
                    $selectedClients[] = intval($clientId);
                }
            }
            
            // Get relationship types from POST (empty array if none specified)
            if (isset($_POST['relationship_types']) && is_array($_POST['relationship_types'])) {
                foreach ($_POST['relationship_types'] as $clientId => $type) {
                    $relationshipTypes[intval($clientId)] = $type;
                }
            }
            
            // Debug log
            error_log('Processed data: ' . count($selectedClients) . ' clients selected');
            error_log('Selected clients (processed): ' . print_r($selectedClients, true));
            error_log('Relationship types (processed): ' . print_r($relationshipTypes, true));
            
            // Update the client assignments
            $result = false;
            
            try {
                // Try to update client assignments
                $result = $this->siteModel->updateSiteClientAssignments($id, $selectedClients, $relationshipTypes);
                error_log('Assignment result: ' . ($result ? 'success' : 'failed'));
                
                if ($result) {
                    flash('site_success', 'Client assignments updated successfully');
                } else {
                    flash('site_error', 'Failed to update client assignments. Please try again.', 'alert-danger');
                }
            } catch (Exception $e) {
                error_log('Exception during client assignment: ' . $e->getMessage());
                flash('site_error', 'An error occurred while updating client assignments.', 'alert-danger');
            }
            
            // Redirect back to the site view
            redirect('/sites/viewSite/' . $id);
            return;
        } else {
            // Get all clients
            $clients = $this->clientModel->getAllClients();
            
            // Get clients currently assigned to this site
            $assignedClients = $this->siteModel->getSiteClients($id);
            
            // Create array of assigned client IDs for easy checking
            $assignedIds = [];
            $relationships = [];
            foreach ($assignedClients as $client) {
                $assignedIds[] = $client['id'];
                $relationships[$client['id']] = $client['relationship_type'];
            }
            
            $data = [
                'title' => 'Assign Clients to Site',
                'site' => $site,
                'clients' => $clients,
                'assignedIds' => $assignedIds,
                'relationships' => $relationships
            ];
            
            $this->view('sites/assign_clients', $data);
        }
    }

    /**
     * Show form to link existing projects to this site
     * 
     * @param int $id Site ID
     */
    public function linkProjects($id = null) {
        // Check if user has permission
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
            flash('site_error', 'You do not have permission to link projects', 'alert-danger');
            redirect('sites');
            return;
        }
        
        if (!$id) {
            redirect('sites');
            return;
        }
        
        // Get site details
        $site = $this->siteModel->getSiteById($id);
        
        if (!$site) {
            flash('site_error', 'Site not found', 'alert-danger');
            redirect('sites');
            return;
        }
        
        // Load project model
        $projectModel = $this->model('Project');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form
            
            // Get selected projects from POST
            $selectedProjects = isset($_POST['projects']) ? $_POST['projects'] : [];
            $notes = isset($_POST['notes']) ? $_POST['notes'] : [];
            
            // Ensure table exists
            $projectModel->createProjectSitesTable();
            
            // Link each selected project
            $successCount = 0;
            foreach ($selectedProjects as $projectId) {
                $projectId = intval($projectId);
                $note = isset($notes[$projectId]) ? trim($notes[$projectId]) : '';
                
                if ($projectModel->linkProjectToSite($projectId, $id, $note)) {
                    $successCount++;
                }
            }
            
            if ($successCount > 0) {
                flash('site_success', "$successCount project(s) linked to site successfully");
            } else {
                flash('site_error', 'Failed to link projects to site', 'alert-danger');
            }
            
            redirect('/sites/viewSite/' . $id);
        } else {
            // Get all projects
            $projects = $projectModel->getAllProjects();
            
            // Get projects already linked to this site
            $linkedProjects = $this->siteModel->getLinkedProjects($id);
            
            // Create array of linked project IDs for easy checking
            $linkedIds = [];
            foreach ($linkedProjects as $project) {
                $linkedIds[] = $project['id'];
            }
            
            $data = [
                'title' => 'Link Projects to Site',
                'site' => $site,
                'projects' => $projects,
                'linkedIds' => $linkedIds
            ];
            
            $this->view('sites/link_projects', $data);
        }
    }
}
?> 
