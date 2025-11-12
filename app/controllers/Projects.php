<?php
// Include PermissionHelper for page access control
require_once __DIR__ . '/../core/PermissionHelper.php';

class Projects extends Controller {
    private $projectModel;
    private $taskModel;
    private $noteModel;
    private $departmentModel;
    private $userModel;
    private $siteModel;
    private $settingModel;
    private $clientModel;
    
    public function __construct() {
        // Check if user is logged in
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
            header('Location: /auth');
            exit;
        }
        
        // Check page access permission
        PermissionHelper::requirePageAccess('projects');
        
        // Load the project model
        $this->projectModel = $this->model('Project');
        $this->taskModel = $this->model('Task');
        $this->noteModel = $this->model('Note');
        $this->departmentModel = $this->model('Department');
        $this->userModel = $this->model('User');
        $this->siteModel = $this->model('Site');
        $this->settingModel = $this->model('Setting');
        $this->clientModel = $this->model('Client');
        
        // Create project_users table if it doesn't exist
        $this->projectModel->createProjectUsersTable();
        
        // Create project_sites table if it doesn't exist
        $this->projectModel->createProjectSitesTable();

        // Ensure projects table has client_id column
        $this->projectModel->ensureClientColumn();
    }
    
    // List all projects
    public function index() {
        // Get all projects
        $projects = $this->projectModel->getAllProjects(); // Get real projects from database
        
        // Get currency settings
        $currency = $this->settingModel->getCurrency();
        
        $this->view('projects/index', [
            'title' => 'Projects',
            'projects' => $projects,
            'currency' => $currency
        ]);
    }
    
    // Show the form to create a new project
    public function create() {
        // Check create permission
        PermissionHelper::requirePermission('projects.create');
        
        // Get all departments for the dropdown
        $departments = $this->departmentModel->getAllDepartments();
        
        // Get all sites for the dropdown
        $sites = $this->siteModel->getAllSites();
        
        // Check if a site_id was passed in the URL (for pre-selecting)
        $selectedSiteId = isset($_GET['site_id']) ? intval($_GET['site_id']) : null;
        
        // Get currency settings
        $currency = $this->settingModel->getCurrency();
        
        // Get all clients for the dropdown
        $clients = $this->clientModel->getAllClients();
        
        $this->view('projects/create', [
            'title' => 'Create Project',
            'departments' => $departments,
            'sites' => $sites,
            'selected_site_id' => $selectedSiteId,
            'currency' => $currency,
            'clients' => $clients
        ]);
    }
    
    // Process the new project form
    public function store() {
        // Check create permission
        PermissionHelper::requirePermission('projects.create');
        
        // Process form data if POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW) ?? $_POST;
            
            // Initialize data array
            $data = [
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'start_date' => trim($_POST['start_date']),
                'end_date' => trim($_POST['end_date']),
                'status' => trim($_POST['status']),
                'department_id' => (isset($_POST['department_id']) && trim($_POST['department_id']) !== '') ? intval($_POST['department_id']) : null,
                'budget' => (isset($_POST['budget']) && trim($_POST['budget']) !== '') ? floatval(str_replace(['$', ','], '', $_POST['budget'])) : null,
                'client_id' => (isset($_POST['client_id']) && trim($_POST['client_id']) !== '') ? intval($_POST['client_id']) : null,
                'title_err' => '',
                'description_err' => '',
                'start_date_err' => '',
                'end_date_err' => '',
                'status_err' => '',
                'department_id_err' => '',
                'budget_err' => ''
            ];
            
            // Validate title
            if (empty($data['title'])) {
                $data['title_err'] = 'Please enter project title';
            }
            
            // Validate description
            if (empty($data['description'])) {
                $data['description_err'] = 'Please enter project description';
            }
            
            // Validate dates
            if (empty($data['start_date'])) {
                $data['start_date_err'] = 'Please select a start date';
            }
            
            if (empty($data['end_date'])) {
                $data['end_date_err'] = 'Please select an end date';
            } elseif ($data['end_date'] < $data['start_date']) {
                $data['end_date_err'] = 'End date cannot be before start date';
            }
            
            // Validate status
            if (empty($data['status'])) {
                $data['status_err'] = 'Please select a status';
            }
            
            // Department is optional
            
            // Validate budget (optional; if provided, it must not be negative)
            if ($data['budget'] !== null && $data['budget'] < 0) {
                $data['budget_err'] = 'Budget cannot be negative';
            }
            
            // Check if there are no errors
            if (empty($data['title_err']) && empty($data['description_err']) && 
                empty($data['start_date_err']) && empty($data['end_date_err']) && 
                empty($data['status_err']) && empty($data['department_id_err']) && 
                empty($data['budget_err'])) {
                
                // Add the user_id from the session
                $data['user_id'] = $_SESSION['user_id'];

                // Default budget to 0 if not provided (DB column is NOT NULL)
                if ($data['budget'] === null) {
                    $data['budget'] = 0;
                }
                
                // Create project
                $projectId = $this->projectModel->create($data);
                
                if ($projectId) {
                    // If a site was selected, link it to the project
                    if (!empty($_POST['site_id'])) {
                        $siteId = intval($_POST['site_id']);
                        $notes = !empty($_POST['site_notes']) ? trim($_POST['site_notes']) : '';
                        $this->projectModel->linkProjectToSite($projectId, $siteId, $notes);
                    }
                    
                    // Log the activity
                    $activityLogModel = $this->model('ActivityLog');
                    
                    // Get department name for the description
                    $departmentName = '';
                    if (!empty($data['department_id'])) {
                        $department = $this->departmentModel->getDepartmentById($data['department_id']);
                        if ($department) {
                            $departmentName = $department->name;
                        }
                    }
                    
                    // Format budget for description
                    $formattedBudget = '$' . number_format($data['budget'], 2);
                    
                    // Create description
                    $description = sprintf(
                        'Created new project "%s" in department "%s" with budget %s, status "%s"',
                        $data['title'],
                        $departmentName,
                        $formattedBudget,
                        $data['status']
                    );
                    
                    // Log the activity with additional metadata
                    $metadata = [
                        'title' => $data['title'],
                        'department_id' => $data['department_id'],
                        'department_name' => $departmentName,
                        'budget' => $data['budget'],
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                        'status' => $data['status']
                    ];
                    
                    $activityLogModel->log(
                        $_SESSION['user_id'],
                        'project',
                        $projectId,
                        'created',
                        $description,
                        $metadata
                    );
                    
                    // Set flash message
                    flash('project_message', 'Project created successfully');
                    
                    // Redirect to projects index
                    redirect('projects');
                } else {
                    // Set error flash message
                    flash('project_error', 'Error creating project', 'alert-danger');
                    
                    // Redirect back to create form
                    redirect('projects/create');
                }
            } else {
                // Load view with errors
                $this->view('projects/create', [
                    'title' => 'Create Project',
                    'data' => $data,
                    'departments' => $this->departmentModel->getAllDepartments(),
                    'sites' => $this->siteModel->getAllSites(),
                    // Ensure currency is always available to the view
                    'currency' => $this->settingModel->getCurrency(),
                    'clients' => $this->clientModel->getAllClients()
                ]);
            }
        } else {
            // If not POST request, redirect to create form
            redirect('projects/create');
        }
    }
    
    // Show a single project
    public function viewProject($id = null) {
        // Check if id is provided
        if (!$id) {
            flash('project_error', 'No project id provided');
            redirect('projects');
        }
        
        // Get project
        $project = $this->projectModel->getProjectById($id);
        
        // Check if project exists
        if (!$project) {
            flash('project_error', 'Project not found');
            redirect('projects');
        }
        
        // Get all tasks for this project
        $tasks = $this->taskModel->getTasksByProject($id);
        
        // Get all notes for this project
        $notes = $this->noteModel->getNotesByReference('project', $id);
        
        // Get recent task activities for this project
        $taskActivities = $this->taskModel->getRecentTaskActivityByProject($id, 10);
        
        // Get project risk assessment
        $risks = $this->projectModel->getProjectRisks($id);
        
        // Get project documents
        $documents = $this->projectModel->getProjectDocuments($id);
        
        // Format file sizes
        if (!empty($documents)) {
            foreach ($documents as &$document) {
                $document['formatted_size'] = $this->formatFileSize($document['file_size']);
                
                // Get uploader name
                if (!empty($document['uploaded_by'])) {
                    $uploader = $this->userModel->getUserById($document['uploaded_by']);
                    $document['uploaded_by_name'] = $uploader ? $uploader->username : 'Unknown';
                } else {
                    $document['uploaded_by_name'] = 'System';
                }
            }
        }
        
        // Get all users assigned to this project
        $assignedUsers = $this->projectModel->getProjectUsers($id);
        
        // Get all sites linked to this project
        $linkedSites = $this->projectModel->getLinkedSites($id);
        
        // Get currency settings
        $currency = $this->settingModel->getCurrency();
        
        // Get client info if linked
        $client = null;
        if (!empty($project->client_id)) {
            try {
                $client = $this->clientModel->getClientById((int)$project->client_id);
            } catch (Exception $e) {
                $client = null;
            }
        }
        
        $this->view('projects/view', [
            'title' => $project->title,
            'project' => $project,
            'tasks' => $tasks,
            'notes' => $notes,
            'task_activities' => $taskActivities,
            'assigned_users' => $assignedUsers,
            'risks' => $risks,
            'documents' => $documents,
            'linked_sites' => $linkedSites,
            'currency' => $currency,
            'client' => $client
        ]);
    }
    
    // Show form to edit project
    public function edit($id) {
        // Check update permission
        PermissionHelper::requirePermission('projects.update');
        
        // Get project by ID
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            // If project not found, redirect to projects index
            header('Location: /projects');
            exit;
        }
        
        // Get all departments for the dropdown
        $departments = $this->departmentModel->getAllDepartments();
        
        // Get currency settings
        $currency = $this->settingModel->getCurrency();
        
        // Get all clients for the dropdown
        $clients = $this->clientModel->getAllClients();
        
        $this->view('projects/edit', [
            'title' => 'Edit Project',
            'project' => $project,
            'departments' => $departments,
            'currency' => $currency,
            'clients' => $clients
        ]);
    }
    
    // Process the edit form
    public function update($id) {
        // Check update permission
        PermissionHelper::requirePermission('projects.update');
        
        // Process form data if POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW) ?? $_POST;
            
            // Initialize data array
            $data = [
                'id' => $id,
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'start_date' => trim($_POST['start_date']),
                'end_date' => trim($_POST['end_date']),
                'status' => trim($_POST['status']),
                'department_id' => (isset($_POST['department_id']) && trim($_POST['department_id']) !== '') ? intval($_POST['department_id']) : null,
                'budget' => (isset($_POST['budget']) && trim($_POST['budget']) !== '') ? floatval(str_replace(['$', ','], '', $_POST['budget'])) : null,
                'client_id' => (isset($_POST['client_id']) && trim($_POST['client_id']) !== '') ? intval($_POST['client_id']) : null,
                'title_err' => '',
                'description_err' => '',
                'start_date_err' => '',
                'end_date_err' => '',
                'status_err' => '',
                'department_id_err' => '',
                'budget_err' => ''
            ];
            
            // Validate title
            if (empty($data['title'])) {
                $data['title_err'] = 'Please enter project title';
            }
            
            // Validate description
            if (empty($data['description'])) {
                $data['description_err'] = 'Please enter project description';
            }
            
            // Validate dates
            if (empty($data['start_date'])) {
                $data['start_date_err'] = 'Please select a start date';
            }
            
            if (empty($data['end_date'])) {
                $data['end_date_err'] = 'Please select an end date';
            } elseif ($data['end_date'] < $data['start_date']) {
                $data['end_date_err'] = 'End date cannot be before start date';
            }
            
            // Validate status
            if (empty($data['status'])) {
                $data['status_err'] = 'Please select a status';
            }
            
            // Department is optional
            
            // Validate budget (optional; if provided, it must not be negative)
            if ($data['budget'] !== null && $data['budget'] < 0) {
                $data['budget_err'] = 'Budget cannot be negative';
            }
            
            // Check if there are no errors
            if (empty($data['title_err']) && empty($data['description_err']) && 
                empty($data['start_date_err']) && empty($data['end_date_err']) && 
                empty($data['status_err']) && empty($data['department_id_err']) && 
                empty($data['budget_err'])) {
                
                // Default budget to 0 if not provided (DB column is NOT NULL)
                if ($data['budget'] === null) {
                    $data['budget'] = 0;
                }

                // Update project
                $this->projectModel->update($data);
                
                // Set flash message
                flash('project_message', 'Project updated successfully');
                
                // Redirect to project details
                header('Location: /projects/viewProject/' . $id);
                exit;
            } else {
                // Load view with errors
                $this->view('projects/edit', [
                    'title' => 'Edit Project',
                    'project' => (object)$data,
                    'departments' => $this->departmentModel->getAllDepartments(),
                    // Ensure currency is always available to the view
                    'currency' => $this->settingModel->getCurrency(),
                    'clients' => $this->clientModel->getAllClients()
                ]);
            }
        } else {
            // If not POST request, redirect to edit form
            header('Location: /projects/edit/' . $id);
            exit;
        }
    }
    
    // Delete a project
    public function delete($id) {
        // Check delete permission
        PermissionHelper::requirePermission('projects.delete');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Delete project
            $this->projectModel->delete($id);
            
            // Set flash message
            flash('project_message', 'Project deleted successfully');
            
            // Redirect to projects index
            header('Location: /projects');
            exit;
        } else {
            // If not POST request, redirect to projects index
            header('Location: /projects');
            exit;
        }
    }
    
    /**
     * Show form to manage team members for a project
     * 
     * @param int $id Project ID
     * @return void
     */
    public function manageTeam($id) {
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            flash('project_error', 'Project not found', 'alert-danger');
            redirect(URLROOT . '/projects');
        }
        
        // Get all available users
        $allUsers = $this->userModel->getAllUsers();
        
        // Get users already assigned to this project
        $assignedUsers = $this->projectModel->getProjectUsers($id);
        
        // Create a map of assigned user IDs for easy lookup
        $assignedUserIds = [];
        foreach ($assignedUsers as $user) {
            $assignedUserIds[$user->user_id] = $user->role;
        }
        
        $this->view('projects/team', [
            'title' => 'Manage Team - ' . $project->title,
            'project' => $project,
            'all_users' => $allUsers,
            'assigned_users' => $assignedUsers,
            'assigned_user_ids' => $assignedUserIds
        ]);
    }
    
    /**
     * Process form to assign users to a project
     * 
     * @param int $id Project ID
     * @return void
     */
    public function assignUsers($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(URLROOT . '/projects/manageTeam/' . $id);
        }
        
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            flash('project_error', 'Project not found', 'alert-danger');
            redirect(URLROOT . '/projects');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW) ?? $_POST;
        
        // Get the selected user IDs and roles
        $userIds = isset($_POST['user_ids']) ? $_POST['user_ids'] : [];
        $roles = isset($_POST['roles']) ? $_POST['roles'] : [];
        
        // Check if we have valid data
        if (empty($userIds)) {
            flash('project_error', 'No users selected', 'alert-danger');
            redirect(URLROOT . '/projects/manageTeam/' . $id);
        }
        
        // Start transaction
        $success = true;
        
        // First, remove all existing assignments
        $currentlyAssigned = $this->projectModel->getProjectUsers($id);
        $currentlyAssignedIds = array_map(function($user) {
            return $user->user_id;
        }, $currentlyAssigned);
        
        // Find users to remove (those who were assigned but not in the new selection)
        $usersToRemove = array_diff($currentlyAssignedIds, $userIds);
        foreach ($usersToRemove as $userId) {
            $success = $success && $this->projectModel->removeUserFromProject($id, $userId);
        }
        
        // Now assign or update roles for selected users
        foreach ($userIds as $i => $userId) {
            $role = isset($roles[$i]) ? $roles[$i] : 'Member';
            $success = $success && $this->projectModel->assignUsersToProject($id, [$userId], $role);
        }
        
        if ($success) {
            flash('project_message', 'Team members updated successfully');
        } else {
            flash('project_error', 'Error updating team members', 'alert-danger');
        }
        
        redirect(URLROOT . '/projects/manageTeam/' . $id);
    }
    
    /**
     * Remove a user from a project
     * 
     * @param int $projectId Project ID
     * @param int $userId User ID
     * @return void
     */
    public function removeUser($projectId, $userId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(URLROOT . '/projects/manageTeam/' . $projectId);
        }
        
        $project = $this->projectModel->getProjectById($projectId);
        
        if (!$project) {
            flash('project_error', 'Project not found', 'alert-danger');
            redirect(URLROOT . '/projects');
        }
        
        if ($this->projectModel->removeUserFromProject($projectId, $userId)) {
            flash('project_message', 'Team member removed successfully');
        } else {
            flash('project_error', 'Error removing team member', 'alert-danger');
        }
        
        redirect(URLROOT . '/projects/manageTeam/' . $projectId);
    }
    
    /**
     * Show form to manage sites linked to this project
     * 
     * @param int $id Project ID
     * @return void
     */
    public function manageSites($id) {
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            flash('project_error', 'Project not found', 'alert-danger');
            redirect(URLROOT . '/projects');
        }
        
        // Get all available sites
        $allSites = $this->siteModel->getAllSites();
        
        // Get sites already linked to this project
        $linkedSites = $this->projectModel->getLinkedSites($id);
        
        // Create a map of linked site IDs for easy lookup
        $linkedSiteIds = [];
        foreach ($linkedSites as $site) {
            $linkedSiteIds[] = $site['id'];
        }
        
        $this->view('projects/sites', [
            'title' => 'Manage Sites - ' . $project->title,
            'project' => $project,
            'all_sites' => $allSites,
            'linked_sites' => $linkedSites,
            'linked_site_ids' => $linkedSiteIds
        ]);
    }
    
    /**
     * Link a project to a site
     * 
     * @param int $id Project ID
     * @return void
     */
    public function linkSite($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/projects/viewProject/' . $id);
        }
        
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            flash('project_error', 'Project not found', 'alert-danger');
            redirect('/projects');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW) ?? $_POST;
        
        $siteId = isset($_POST['site_id']) ? (int)$_POST['site_id'] : 0;
        $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
        
        if (empty($siteId)) {
            flash('project_error', 'No site selected', 'alert-danger');
            redirect('/projects/manageSites/' . $id);
        }
        
        // Link the project to the site
        $result = $this->projectModel->linkProjectToSite($id, $siteId, $notes);
        
        if ($result) {
            flash('project_message', 'Project linked to site successfully');
            redirect('/projects/manageSites/' . $id);
        } else {
            flash('project_error', 'Failed to link project to site', 'alert-danger');
            redirect('/projects/manageSites/' . $id);
        }
    }
    
    /**
     * Unlink a project from a site
     * 
     * @param int $id Project ID
     * @param int $siteId Site ID
     * @return void
     */
    public function unlinkSite($id, $siteId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/projects/viewProject/' . $id);
        }
        
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            flash('project_error', 'Project not found', 'alert-danger');
            redirect('/projects');
        }
        
        // Unlink the project from the site
        $result = $this->projectModel->unlinkProjectFromSite($id, $siteId);
        
        if ($result) {
            flash('project_message', 'Project unlinked from site successfully');
            redirect('/projects/manageSites/' . $id);
        } else {
            flash('project_error', 'Failed to unlink project from site', 'alert-danger');
            redirect('/projects/manageSites/' . $id);
        }
    }
    
    /**
     * Display project activity history
     * 
     * @param int $id Project ID
     * @return void
     */
    public function activity($id) {
        // Get project details
        $project = $this->projectModel->getProjectById($id);
        
        if (!$project) {
            flash('project_error', 'Project not found', 'alert-danger');
            redirect(URLROOT . '/projects');
        }
        
        // Load the ActivityLog model
        $activityLogModel = $this->model('ActivityLog');
        
        // Process filters if submitted
        $filters = [
            'entity_type' => 'project',
            'entity_id' => $id
        ];
        
        // Handle filter form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Apply date filters if provided
            if (!empty($_POST['start_date'])) {
                $filters['start_date'] = $_POST['start_date'];
            }
            
            if (!empty($_POST['end_date'])) {
                $filters['end_date'] = $_POST['end_date'];
            }
            
            // Apply action filter if provided
            if (!empty($_POST['action_type']) && $_POST['action_type'] !== 'all') {
                $filters['action'] = $_POST['action_type'];
            }
            
            // Apply user filter if provided
            if (!empty($_POST['user_id']) && $_POST['user_id'] !== 'all') {
                $filters['user_id'] = $_POST['user_id'];
            }
        }
        
        // Get activities based on filters
        $activities = $activityLogModel->getActivitiesWithFilters($filters);
        
        // Format activities for display
        $formattedActivities = [];
        foreach ($activities as $activity) {
            $formattedActivities[] = $activityLogModel->formatActivityForDisplay($activity);
        }
        
        // Get users for the filter dropdown
        $users = $this->userModel->getAllUsers();
        
        // For backward compatibility, also get notes and task activities
        // These will be shown only if there are no new-style activity logs yet
        $notes = $this->noteModel->getNotesByReference('project', $id);
        $taskActivities = $this->taskModel->getRecentTaskActivityByProject($id, 100);
        
        // Combine legacy activities
        $legacyActivities = [];
        
        // Add notes to activities
        foreach ($notes as $note) {
            $legacyActivities[] = [
                'type' => 'note',
                'data' => $note,
                'date' => $note['created_at']
            ];
        }
        
        // Add task activities to activities
        foreach ($taskActivities as $task) {
            $legacyActivities[] = [
                'type' => 'task',
                'data' => $task,
                'date' => ($task->activity_type === 'updated' && !empty($task->updated_at)) ? $task->updated_at : $task->created_at
            ];
        }
        
        // Sort legacy activities by date (newest first)
        usort($legacyActivities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        // Get action types for filter dropdown
        $actionTypes = [
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'completed' => 'Completed',
            'assigned' => 'Assigned',
            'uploaded' => 'Uploaded',
            'downloaded' => 'Downloaded',
            'linked' => 'Linked',
            'unlinked' => 'Unlinked'
        ];
        
        $this->view('projects/activity', [
            'title' => 'Activity - ' . $project->title,
            'project' => $project,
            'activities' => $formattedActivities,
            'legacy_activities' => $legacyActivities,
            'users' => $users,
            'action_types' => $actionTypes,
            'filters' => $filters
        ]);
    }
    
    /**
     * Upload a document for a project
     * 
     * @param int $projectId Project ID
     * @return void
     */
    public function uploadDocument($projectId = null) {
        // Check if project ID is provided
        if (!$projectId) {
            flash('project_error', 'No project id provided', 'alert-danger');
            redirect('projects');
            return;
        }
        
        // Check if project exists
        $project = $this->projectModel->getProjectById($projectId);
        if (!$project) {
            flash('project_error', 'Project not found', 'alert-danger');
            redirect('projects');
            return;
        }
        
        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('projects/viewProject/' . $projectId);
            return;
        }
        
        // Check if a file was uploaded
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            flash('project_error', 'No file uploaded or upload error', 'alert-danger');
            redirect('projects/viewProject/' . $projectId);
            return;
        }
        
        // Get file details
        $file = $_FILES['document'];
        $fileName = $file['name'];
        $fileTmpPath = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];
        
        // Validate file size (max 10MB)
        $maxSize = 10 * 1024 * 1024; // 10MB in bytes
        if ($fileSize > $maxSize) {
            flash('project_error', 'File size exceeds the maximum limit (10MB)', 'alert-danger');
            redirect('projects/viewProject/' . $projectId);
            return;
        }
        
        // Get file extension
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Allowed file types
        $allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt', 'zip'];
        
        // Check if file type is allowed
        if (!in_array($fileExt, $allowedTypes)) {
            flash('project_error', 'File type not allowed', 'alert-danger');
            redirect('projects/viewProject/' . $projectId);
            return;
        }
        
        // Create the upload directory if it doesn't exist
        $uploadDir = APPROOT . '/../uploads/projects/' . $projectId . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate a unique filename to avoid conflicts
        $newFileName = uniqid() . '_' . $fileName;
        $uploadPath = $uploadDir . $newFileName;
        
        // Move the uploaded file to the destination
        if (move_uploaded_file($fileTmpPath, $uploadPath)) {
            // Get document type and description from POST
            $documentType = isset($_POST['document_type']) ? trim($_POST['document_type']) : null;
            $description = isset($_POST['description']) ? trim($_POST['description']) : null;
            
            // Save document info to database
            $fileData = [
                'file_name' => $fileName,
                'file_path' => 'uploads/projects/' . $projectId . '/' . $newFileName,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'uploaded_by' => $_SESSION['user_id']
            ];
            
            $documentId = $this->projectModel->uploadDocument($projectId, $fileData, $documentType, $description);
            
            if ($documentId) {
                // Log the activity
                $activityLogModel = $this->model('ActivityLog');
                
                // Format file size for readable description
                $formattedFileSize = $this->formatFileSize($fileSize);
                
                // Create description
                $activityDescription = sprintf(
                    'Uploaded document "%s" (%s) to project "%s"',
                    $fileName,
                    $formattedFileSize,
                    $project->title
                );
                
                if ($documentType) {
                    $activityDescription .= sprintf(' - Type: %s', $documentType);
                }
                
                // Prepare metadata
                $metadata = [
                    'document_id' => $documentId,
                    'file_name' => $fileName,
                    'file_type' => $fileType,
                    'file_size' => $fileSize,
                    'document_type' => $documentType,
                    'description' => $description,
                    'project_title' => $project->title
                ];
                
                // Log the activity
                $activityLogModel->log(
                    $_SESSION['user_id'],
                    'project',
                    $projectId,
                    'uploaded',
                    $activityDescription,
                    $metadata
                );
                
                flash('project_message', 'Document uploaded successfully');
            } else {
                // Delete the file if database insert failed
                unlink($uploadPath);
                flash('project_error', 'Failed to save document information', 'alert-danger');
            }
        } else {
            flash('project_error', 'Failed to upload document', 'alert-danger');
        }
        
        redirect('projects/viewProject/' . $projectId);
    }
    
    /**
     * Download a document
     * 
     * @param int $documentId Document ID
     * @return void
     */
    public function downloadDocument($documentId = null) {
        // Check if document ID is provided
        if (!$documentId) {
            flash('project_error', 'No document id provided', 'alert-danger');
            redirect('projects');
            return;
        }
        
        // Get document information
        $document = $this->projectModel->getDocumentById($documentId);
        
        if (!$document) {
            flash('project_error', 'Document not found', 'alert-danger');
            redirect('projects');
            return;
        }
        
        // Get project information for logging
        $project = $this->projectModel->getProjectById($document['project_id']);
        
        // Log the download activity before sending the file
        if (isset($_SESSION['user_id']) && $project) {
            $activityLogModel = $this->model('ActivityLog');
            
            // Format file size for readable description
            $formattedFileSize = $this->formatFileSize($document['file_size']);
            
            // Create description
            $activityDescription = sprintf(
                'Downloaded document "%s" (%s) from project "%s"',
                $document['file_name'],
                $formattedFileSize,
                $project->title
            );
            
            // Prepare metadata
            $metadata = [
                'document_id' => $documentId,
                'file_name' => $document['file_name'],
                'file_type' => $document['file_type'],
                'file_size' => $document['file_size'],
                'project_id' => $document['project_id'],
                'project_title' => $project->title
            ];
            
            // Log the activity
            $activityLogModel->log(
                $_SESSION['user_id'],
                'project',
                $document['project_id'],
                'downloaded',
                $activityDescription,
                $metadata
            );
        }
        
        // File path
        $filePath = APPROOT . '/../' . $document['file_path'];
        
        // Check if file exists
        if (!file_exists($filePath)) {
            flash('project_error', 'Document file not found', 'alert-danger');
            redirect('projects/viewProject/' . $document['project_id']);
            return;
        }
        
        // Clear all output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers for download
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $document['file_type']);
        header('Content-Disposition: attachment; filename="' . $document['file_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output file
        readfile($filePath);
        exit;
    }
    
    /**
     * Delete a document
     * 
     * @param int $documentId Document ID
     * @param int $projectId Project ID
     * @return void
     */
    public function deleteDocument($documentId = null, $projectId = null) {
        // Check if document ID is provided
        if (!$documentId) {
            flash('project_error', 'No document id provided', 'alert-danger');
            redirect('projects');
            return;
        }
        
        // Get document information
        $document = $this->projectModel->getDocumentById($documentId);
        
        if (!$document) {
            flash('project_error', 'Document not found', 'alert-danger');
            redirect('projects');
            return;
        }
        
        // Check if project ID is set, if not, use from document
        if (!$projectId) {
            $projectId = $document['project_id'];
        }
        
        // File path
        $filePath = APPROOT . '/../' . $document['file_path'];
        
        // Delete file from storage
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Delete document record from database
        $result = $this->projectModel->deleteDocument($documentId);
        
        if ($result) {
            flash('project_message', 'Document deleted successfully');
        } else {
            flash('project_error', 'Failed to delete document', 'alert-danger');
        }
        
        redirect('projects/viewProject/' . $projectId);
    }
    
    /**
     * Format file size for display
     * 
     * @param int $bytes File size in bytes
     * @param int $precision Decimal precision 
     * @return string Formatted file size
     */
    public function formatFileSize($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
} 