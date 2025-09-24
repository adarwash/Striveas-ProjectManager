<?php

class Clients extends Controller {
    private $clientModel;
    private $siteModel;
    
    /**
     * Initialize controller and load models
     */
    public function __construct() {
        // Make sure user is logged in
        if(!isLoggedIn()) {
            redirect('users/login');
        }
        
        // Load models
        $this->clientModel = $this->model('Client');
        $this->siteModel = $this->model('Site');
        
        // Set the page for sidebar highlighting
        $_SESSION['page'] = 'clients';
    }
    
    /**
     * Clients index/list page
     */
    public function index() {
        // Check permission
        if (!hasPermission('clients.read')) {
            flash('client_error', 'You do not have permission to view clients', 'alert-danger');
            redirect('dashboard');
            return;
        }
        
        // Get all clients
        $clients = $this->clientModel->getAllClients();
        
        $data = [
            'title' => 'Clients',
            'clients' => $clients
        ];
        
        $this->view('clients/index', $data);
    }
    
    /**
     * View a specific client
     * 
     * @param int $id Client ID
     */
    public function viewClient($id = null) {
        // Check permission
        if (!hasPermission('clients.read')) {
            flash('client_error', 'You do not have permission to view clients', 'alert-danger');
            redirect('dashboard');
            return;
        }
        
        if (!$id) {
            redirect('clients');
            return;
        }
        
        // Get client details
        $client = $this->clientModel->getClientById($id);
        
        if (!$client) {
            flash('client_error', 'Client not found', 'alert-danger');
            redirect('clients');
            return;
        }
        
        // Get sites assigned to this client
        $sites = $this->clientModel->getSiteClients($id);

        // Get client domains
        try {
            if (!class_exists('ClientDomain')) {
                require_once APPROOT . '/app/models/ClientDomain.php';
            }
            $clientDomainModel = new ClientDomain();
            $domains = $clientDomainModel->getDomainsByClient($id);
        } catch (Exception $e) {
            $domains = [];
        }
        
        $data = [
            'title' => $client['name'],
            'client' => $client,
            'sites' => $sites,
            'domains' => $domains
        ];
        
        $this->view('clients/view', $data);
    }

    /**
     * Manage client domains (add/remove)
     */
    public function manageDomains($id = null) {
        // Permissions: update clients
        if (!hasPermission('clients.update')) {
            flash('client_error', 'You do not have permission to manage client domains', 'alert-danger');
            redirect('clients');
            return;
        }
        if (!$id) {
            redirect('clients');
            return;
        }
        // Load client
        $client = $this->clientModel->getClientById($id);
        if (!$client) {
            flash('client_error', 'Client not found', 'alert-danger');
            redirect('clients');
            return;
        }
        if (!class_exists('ClientDomain')) {
            require_once APPROOT . '/app/models/ClientDomain.php';
        }
        $clientDomainModel = new ClientDomain();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'add') {
                $domain = trim($_POST['domain'] ?? '');
                $isPrimary = !empty($_POST['is_primary']);
                if ($domain && $clientDomainModel->addDomain($id, $domain, $isPrimary)) {
                    flash('client_success', 'Domain added successfully');
                } else {
                    flash('client_error', 'Failed to add domain', 'alert-danger');
                }
            } elseif ($action === 'remove') {
                $domainId = (int)($_POST['domain_id'] ?? 0);
                if ($domainId && $clientDomainModel->removeById($domainId)) {
                    flash('client_success', 'Domain removed successfully');
                } else {
                    flash('client_error', 'Failed to remove domain', 'alert-danger');
                }
            }
            redirect('clients/viewClient/' . $id);
            return;
        }

        // GET fallback shows the view page
        redirect('clients/viewClient/' . $id);
    }
    
    /**
     * Create a new client
     */
    public function create() {
        // Check permission
        if (!hasPermission('clients.create')) {
            flash('client_error', 'You do not have permission to create clients', 'alert-danger');
            redirect('clients');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form
            
            // Sanitize POST data using modern approach
            $sanitizedPost = [];
            foreach ($_POST as $key => $value) {
                $sanitizedPost[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
            
            // Init data
            $data = [
                'title' => 'Create Client',
                'name' => $sanitizedPost['name'] ?? '',
                'contact_person' => $sanitizedPost['contact_person'] ?? '',
                'email' => $sanitizedPost['email'] ?? '',
                'phone' => $sanitizedPost['phone'] ?? '',
                'address' => $sanitizedPost['address'] ?? '',
                'industry' => $sanitizedPost['industry'] ?? '',
                'status' => $sanitizedPost['status'] ?? '',
                'notes' => $sanitizedPost['notes'] ?? '',
                'name_err' => '',
                'email_err' => ''
            ];
            
            // Validate name
            if (empty($data['name'])) {
                $data['name_err'] = 'Please enter client name';
            }
            
            // Validate email if provided
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['email_err'] = 'Please enter a valid email address';
            }
            
            // Make sure no errors
            if (empty($data['name_err']) && empty($data['email_err'])) {
                
                // Validated
                if ($this->clientModel->addClient($data)) {
                    flash('client_success', 'Client added successfully');
                    redirect('clients');
                } else {
                    flash('client_error', 'Failed to add client. Please check the form data and try again.', 'alert-danger');
                    $this->view('clients/create', $data);
                }
            } else {
                // Load view with errors
                $this->view('clients/create', $data);
            }
        } else {
            // Init data
            $data = [
                'title' => 'Create Client',
                'name' => '',
                'contact_person' => '',
                'email' => '',
                'phone' => '',
                'address' => '',
                'industry' => '',
                'status' => 'Active',
                'notes' => '',
                'name_err' => '',
                'email_err' => ''
            ];
            
            // Load view
            $this->view('clients/create', $data);
        }
    }
    
    /**
     * Edit existing client
     * 
     * @param int $id Client ID
     */
    public function edit($id = null) {
        // Check permission
        if (!hasPermission('clients.update')) {
            flash('client_error', 'You do not have permission to edit clients', 'alert-danger');
            redirect('clients');
            return;
        }
        
        if (!$id) {
            redirect('clients');
            return;
        }
        
        // Get client details
        $client = $this->clientModel->getClientById($id);
        
        if (!$client) {
            flash('client_error', 'Client not found', 'alert-danger');
            redirect('clients');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form
            
            // Sanitize POST data using modern approach
            $sanitizedPost = [];
            foreach ($_POST as $key => $value) {
                $sanitizedPost[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
            
            // Init data
            $data = [
                'id' => $id,
                'title' => 'Edit Client',
                'name' => $sanitizedPost['name'] ?? '',
                'contact_person' => $sanitizedPost['contact_person'] ?? '',
                'email' => $sanitizedPost['email'] ?? '',
                'phone' => $sanitizedPost['phone'] ?? '',
                'address' => $sanitizedPost['address'] ?? '',
                'industry' => $sanitizedPost['industry'] ?? '',
                'status' => $sanitizedPost['status'] ?? '',
                'notes' => $sanitizedPost['notes'] ?? '',
                'name_err' => '',
                'email_err' => ''
            ];
            
            // Validate name
            if (empty($data['name'])) {
                $data['name_err'] = 'Please enter client name';
            }
            
            // Validate email if provided
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['email_err'] = 'Please enter a valid email address';
            }
            
            // Make sure no errors
            if (empty($data['name_err']) && empty($data['email_err'])) {
                
                // Validated
                if ($this->clientModel->updateClient($data)) {
                    flash('client_success', 'Client updated successfully');
                    redirect('clients');
                } else {
                    flash('client_error', 'Failed to update client. Please check the form data and try again.', 'alert-danger');
                    $this->view('clients/edit', $data);
                }
            } else {
                // Load view with errors
                $this->view('clients/edit', $data);
            }
        } else {
            // Init data with client details
            $data = [
                'id' => $client['id'],
                'title' => 'Edit Client',
                'name' => $client['name'],
                'contact_person' => $client['contact_person'],
                'email' => $client['email'],
                'phone' => $client['phone'],
                'address' => $client['address'],
                'industry' => $client['industry'],
                'status' => $client['status'],
                'notes' => $client['notes'],
                'name_err' => '',
                'email_err' => ''
            ];
            
            // Load view
            $this->view('clients/edit', $data);
        }
    }
    
    /**
     * Delete client
     * 
     * @param int $id Client ID
     */
    public function delete($id = null) {
        // Check permission
        if (!hasPermission('clients.delete')) {
            flash('client_error', 'You do not have permission to delete clients', 'alert-danger');
            redirect('clients');
            return;
        }
        
        if (!$id) {
            redirect('clients');
            return;
        }
        
        // Get client details
        $client = $this->clientModel->getClientById($id);
        
        if (!$client) {
            flash('client_error', 'Client not found', 'alert-danger');
            redirect('clients');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Delete the client
            if ($this->clientModel->deleteClient($id)) {
                flash('client_success', 'Client deleted successfully');
                redirect('clients');
            } else {
                flash('client_error', 'Failed to delete client. The client may have associated data that prevents deletion.', 'alert-danger');
                redirect('clients/viewClient/' . $id);
            }
        } else {
            $data = [
                'title' => 'Delete Client',
                'client' => $client
            ];
            
            $this->view('clients/delete', $data);
        }
    }
    
    /**
     * Assign sites to client
     * 
     * @param int $id Client ID
     */
    public function assignSites($id = null) {
        // Check permission
        if (!hasPermission('clients.assign_sites')) {
            flash('client_error', 'You do not have permission to assign sites to clients', 'alert-danger');
            redirect('clients');
            return;
        }
        
        if (!$id) {
            redirect('clients');
            return;
        }
        
        // Get client details
        $client = $this->clientModel->getClientById($id);
        
        if (!$client) {
            flash('client_error', 'Client not found', 'alert-danger');
            redirect('clients');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process site assignments
            $siteIds = isset($_POST['site_ids']) ? $_POST['site_ids'] : [];
            $relationshipTypes = isset($_POST['relationship_types']) ? $_POST['relationship_types'] : [];
            
            if ($this->clientModel->updateSiteClientAssignments($id, $siteIds, $relationshipTypes)) {
                flash('client_success', 'Site assignments updated successfully');
                redirect('clients/viewClient/' . $id);
            } else {
                flash('client_error', 'Failed to update site assignments', 'alert-danger');
                redirect('clients/viewClient/' . $id);
            }
        } else {
            // Get all sites and current assignments
            $allSites = $this->siteModel->getAllSites();
            $assignedSites = $this->clientModel->getSiteClients($id);
            
            // Create array of assigned site IDs for easy checking
            $assignedSiteIds = [];
            foreach ($assignedSites as $site) {
                $assignedSiteIds[$site['id']] = $site['relationship_type'];
            }
            
            $data = [
                'title' => 'Assign Sites - ' . $client['name'],
                'client' => $client,
                'all_sites' => $allSites,
                'assigned_sites' => $assignedSiteIds
            ];
            
            $this->view('clients/assign_sites', $data);
        }
    }
}
?> 