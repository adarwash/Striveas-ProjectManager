<?php
class Tasks extends Controller {
    private $taskModel;
    private $projectModel;
    private $userModel;
    private $noteModel;
    private $clientModel;
    private $reminderModel;
    
    public function __construct() {
        // Check if user is logged in
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
            header('Location: /auth');
            exit;
        }
        
        // Load models
        $this->taskModel = $this->model('Task');
        $this->projectModel = $this->model('Project');
        $this->userModel = $this->model('User');
        $this->noteModel = $this->model('Note');
        $this->clientModel = $this->model('Client');
        $this->reminderModel = $this->model('Reminder');
        
        // Create task_users table if it doesn't exist
        $this->taskModel->createTaskUsersTable();
        // Create task_sites table if it doesn't exist
        $this->taskModel->createTaskSitesTable();
    }
    
    // List all tasks (can be filtered by project)
    public function index() {
        // Get filter parameters from query string
        $projectId = isset($_GET['project_id']) ? trim($_GET['project_id']) : null;
        $status = isset($_GET['status']) ? trim($_GET['status']) : null;
        $priority = isset($_GET['priority']) ? trim($_GET['priority']) : null;
        
        // Build filters array
        $filters = [];
        if ($projectId) {
            $filters['project_id'] = $projectId;
        }
        if ($status) {
            $filters['status'] = $status;
        }
        if ($priority) {
            $filters['priority'] = $priority;
        }
        
        // Get tasks based on filters
        if (!empty($filters)) {
            $tasks = $this->taskModel->getTasksByFilters($filters);
            
            // Build title based on filters
            $title = 'Filtered Tasks';
            if ($projectId) {
                $project = $this->projectModel->getProjectById($projectId);
                if ($project) {
                    $title = 'Tasks for ' . $project->title;
                }
            }
        } else {
            // Get all tasks if no filters
            $tasks = $this->taskModel->getAllTasks();
            $title = 'All Tasks';
        }
        
        // Get all projects for the project filter dropdown
        $projects = $this->projectModel->getAllProjects();
        
        $this->view('tasks/index', [
            'title' => $title,
            'tasks' => $tasks,
            'projects' => $projects
        ]);
    }
    
    // Show form to create a new task (optionally with project pre-selected)
    public function create($projectId = null) {
        // Support preselect via query string: /tasks/create?project_id=123
        if ($projectId === null && isset($_GET['project_id']) && $_GET['project_id'] !== '') {
            $projectId = (int)$_GET['project_id'];
        }
        // Get all projects for dropdown
        $projects = $this->projectModel->getAllProjects();
        // Build a map of project_id => sites for the project's client (if any)
        $sitesByProjectId = [];
        foreach ($projects as $p) {
            $sitesByProjectId[$p->id] = [];
            if (!empty($p->client_id)) {
                try {
                    $sitesByProjectId[$p->id] = $this->clientModel->getSiteClients((int)$p->client_id);
                } catch (Exception $e) {
                    $sitesByProjectId[$p->id] = [];
                }
            }
        }
        
        // Get all users for assignments
        $users = $this->userModel->getAllUsers();
        
        $this->view('tasks/create', [
            'title' => 'Create Task',
            'projects' => $projects,
            'users' => $users,
            'project_id' => $projectId,
            'sitesByProjectId' => $sitesByProjectId
        ]);
    }
    
    // Process the new task form
    public function store() {
        // Process form data if POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW) ?? $_POST;
            
            // Initialize data array
            $data = [
                'project_id' => trim($_POST['project_id']),
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'status' => trim($_POST['status']),
                'priority' => trim($_POST['priority']),
                'due_date' => !empty($_POST['due_date']) ? trim($_POST['due_date']) : null,
                'assigned_to' => !empty($_POST['assigned_to']) ? trim($_POST['assigned_to']) : null,
				'references_text' => isset($_POST['references_text']) ? trim($_POST['references_text']) : null,
                'site_ids' => isset($_POST['site_ids']) ? (array)$_POST['site_ids'] : [],
                'project_id_err' => '',
                'title_err' => '',
                'status_err' => '',
                'priority_err' => ''
            ];
            
            // Validate project_id
            if (empty($data['project_id'])) {
                $data['project_id_err'] = 'Please select a project';
            }
            
            // Validate title
            if (empty($data['title'])) {
                $data['title_err'] = 'Please enter task title';
            }
            
            // Validate status
            if (empty($data['status'])) {
                $data['status_err'] = 'Please select a status';
            }
            
            // Validate priority
            if (empty($data['priority'])) {
                $data['priority_err'] = 'Please select a priority';
            }
            
            // Check if there are no errors
            if (empty($data['project_id_err']) && empty($data['title_err']) && 
                empty($data['status_err']) && empty($data['priority_err'])) {
                
                // Add the created_by from the session
                $data['created_by'] = $_SESSION['user_id'];
                
                // Create task
                $taskId = $this->taskModel->create($data);
                // Link selected client sites (optional)
                if (!empty($taskId) && !empty($data['site_ids'])) {
                    $siteIds = array_map('intval', $data['site_ids']);
                    $this->taskModel->linkTaskToSites((int)$taskId, $siteIds);
                }
                
                // Set flash message
                flash('task_message', 'Task created successfully');
                
                // Redirect to the project details page
                header('Location: /projects/viewProject/' . $data['project_id']);
                exit;
            } else {
                // Get all projects for dropdown
                $projects = $this->projectModel->getAllProjects();
                
                // Get all users for assignments
                $users = $this->userModel->getAllUsers();

                // Build sites map again for redisplay with errors
                $sitesByProjectId = [];
                foreach ($projects as $p) {
                    $sitesByProjectId[$p->id] = [];
                    if (!empty($p->client_id)) {
                        try {
                            $sitesByProjectId[$p->id] = $this->clientModel->getSiteClients((int)$p->client_id);
                        } catch (Exception $e) {
                            $sitesByProjectId[$p->id] = [];
                        }
                    }
                }
                
                // Load view with errors
                $this->view('tasks/create', [
                    'title' => 'Create Task',
                    'projects' => $projects,
                    'users' => $users,
                    'data' => $data,
                    'sitesByProjectId' => $sitesByProjectId
                ]);
            }
        } else {
            // If not POST request, redirect to create form
            header('Location: /tasks/create');
            exit;
        }
    }
    
    // Show a single task
    public function show($id) {
        $task = $this->taskModel->getTaskById($id);
        
        if (!$task) {
            flash('task_error', 'Task not found', 'alert-danger');
            redirect('tasks');
        }
        
        // Get project details
        $project = $this->projectModel->getProjectById($task->project_id);
        
        // Get notes for this task
        $notes = $this->noteModel->getNotesByReference('task', $id);
        
        // Get users assigned to this task
        $assignedUsers = $this->taskModel->getTaskUsers($id);
        
        // Get subtasks for this task
        $subtasks = $this->taskModel->getSubtasks((int)$id);
        // Users for subtask assignment dropdown
        $users = $this->userModel->getAllUsers();
        
        // Load reminders/follow-ups for this task
        $callbacks = [];
        try {
            $callbacks = $this->reminderModel->getByEntity('task', (int)$id);
        } catch (Exception $e) { $callbacks = []; }

        $data = [
            'task' => $task,
            'project' => $project,
            'notes' => $notes,
            'type' => 'task',
            'reference_id' => $id,
            'assigned_users' => $assignedUsers,
            'subtasks' => $subtasks,
            'users' => $users,
            'callbacks' => $callbacks
        ];
        
        $this->view('tasks/show', $data);
    }

    /**
     * Add a task follow-up/reminder
     */
    public function addCallback($taskId = null) {
        if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
            redirect('users/login');
            return;
        }
        if (function_exists('hasPermission') && !hasPermission('tasks.update')) {
            flash('task_error', 'You do not have permission to add follow-ups', 'alert-danger');
            redirect('tasks/show/' . (int)$taskId);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$taskId) {
            redirect('tasks/show/' . (int)$taskId);
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $remindAtRaw = trim($_POST['remind_at'] ?? '');

        if ($title === '' || $remindAtRaw === '') {
            flash('task_error', 'Title and reminder date/time are required.', 'alert-danger');
            redirect('tasks/show/' . (int)$taskId);
            return;
        }

        $remindTs = strtotime($remindAtRaw);
        if ($remindTs === false) {
            flash('task_error', 'Invalid reminder date/time.', 'alert-danger');
            redirect('tasks/show/' . (int)$taskId);
            return;
        }
        $remindAt = date('Y-m-d H:i:s', $remindTs);

        // Determine recipient (assigned user)
        $recipientUserId = null;
        try {
            $task = $this->taskModel->getTaskById((int)$taskId);
            if ($task && !empty($task->assigned_to)) {
                $recipientUserId = (int)$task->assigned_to;
            } else {
                $assignees = $this->taskModel->getTaskUsers((int)$taskId);
                if (!empty($assignees)) {
                    $recipientUserId = (int)($assignees[0]->user_id ?? null);
                }
            }
        } catch (Exception $e) { /* ignore */ }

        $insertId = $this->reminderModel->add([
            'entity_type' => 'task',
            'entity_id' => (int)$taskId,
            'title' => $title,
            'notes' => $notes,
            'remind_at' => $remindAt,
            'created_by' => (int)($_SESSION['user_id'] ?? 0),
            'recipient_user_id' => $recipientUserId,
            'notify_all' => !empty($_POST['notify_all']) ? 1 : 0,
        ]);

        if (!$insertId) {
            flash('task_error', 'Failed to create follow-up.', 'alert-danger');
            redirect('tasks/show/' . (int)$taskId);
            return;
        }

        // Queue reminder email to the recipient if available
        try {
            require_once APPROOT . '/app/services/EmailService.php';
            $emailService = new EmailService();

            $toEmail = null;
            if ($recipientUserId) {
                $user = $this->userModel->getUserById($recipientUserId);
                $toEmail = $user && !empty($user['email']) ? $user['email'] : null;
            }

            if ($toEmail) {
                $task = $this->taskModel->getTaskById((int)$taskId);
                $subject = '[Reminder] Task follow-up: ' . (($task && !empty($task->title)) ? $task->title : ('Task #' . (int)$taskId)) . ' - ' . $title;
                $link = URLROOT . '/tasks/show/' . (int)$taskId;
                $html = "
                <h2>Follow-up Reminder</h2>
                <p><strong>Task:</strong> " . htmlspecialchars(($task && !empty($task->title)) ? $task->title : ('Task #' . (int)$taskId)) . "</p>
                <p><strong>When:</strong> " . date('M j, Y g:i A', $remindTs) . "</p>
                <p><strong>Title:</strong> " . htmlspecialchars($title) . "</p>
                " . (!empty($notes) ? "<p><strong>Notes:</strong><br>" . nl2br(htmlspecialchars($notes)) . "</p>" : "") . "
                <p><a href=\"" . $link . "\">Open task</a></p>
                ";
                $emailData = [
                    'to' => $toEmail,
                    'subject' => $subject,
                    'html_body' => $html,
                    'body' => strip_tags($html),
                ];
                $queueId = $emailService->queueEmail($emailData, 3, new DateTime($remindAt));
                if ($queueId) {
                    $this->reminderModel->setReminderQueueId((int)$insertId, (int)$queueId);
                }
            }
        } catch (Exception $e) {
            error_log('Queue task follow-up reminder email failed: ' . $e->getMessage());
        }

        flash('task_success', 'Follow-up created. Reminder scheduled for ' . date('M j, Y g:i A', $remindTs) . '.');
        redirect('tasks/show/' . (int)$taskId);
    }

    /**
     * Mark a task follow-up as completed
     */
    public function completeCallback($callbackId = null) {
        if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
            redirect('users/login');
            return;
        }
        if (function_exists('hasPermission') && !hasPermission('tasks.update')) {
            flash('task_error', 'You do not have permission to update follow-ups', 'alert-danger');
            redirect('tasks');
            return;
        }
        if (!$callbackId) {
            redirect('tasks');
            return;
        }
        $cb = $this->reminderModel->getById((int)$callbackId);
        if (!$cb) {
            flash('task_error', 'Follow-up not found', 'alert-danger');
            redirect('tasks');
            return;
        }
        $this->reminderModel->markCompleted((int)$callbackId);
        flash('task_success', 'Follow-up marked as completed.');
        $redirectTask = (int)($cb['entity_id'] ?? 0);
        redirect('tasks/show/' . $redirectTask);
    }

    /**
     * View follow-ups history for a task
     */
    public function callbacksHistory($taskId = null) {
        if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
            redirect('users/login');
            return;
        }
        if (!$taskId) {
            redirect('tasks');
            return;
        }

        $task = $this->taskModel->getTaskById((int)$taskId);
        if (!$task) {
            flash('task_error', 'Task not found', 'alert-danger');
            redirect('tasks');
            return;
        }

        $all = [];
        try {
            $all = $this->reminderModel->getByEntity('task', (int)$taskId);
        } catch (Exception $e) {
            $all = [];
        }

        $now = time();
        $completed = [];
        $missed = [];
        $pending = [];
        foreach ($all as $cb) {
            $status = $cb['status'] ?? 'Pending';
            $rt = isset($cb['remind_at']) ? strtotime($cb['remind_at']) : null;
            if ($status === 'Completed') {
                $completed[] = $cb;
            } elseif ($status === 'Pending' && $rt !== false && $rt !== null && $rt < $now) {
                $missed[] = $cb;
            } else {
                $pending[] = $cb;
            }
        }

        $filter = strtolower(trim($_GET['status'] ?? 'history'));
        switch ($filter) {
            case 'completed': $list = $completed; break;
            case 'missed': $list = $missed; break;
            case 'pending': $list = $pending; break;
            case 'all': $list = $all; break;
            case 'history':
            default: $list = array_merge($completed, $missed); break;
        }

        usort($list, function($a, $b) {
            return strtotime($b['remind_at'] ?? '') <=> strtotime($a['remind_at'] ?? '');
        });

        $this->view('tasks/callbacks_history', [
            'title' => 'Follow-ups History - ' . ($task->title ?? ('Task #' . (int)$taskId)),
            'task' => $task,
            'callbacks' => $list,
            'counts' => [
                'all' => count($all),
                'completed' => count($completed),
                'missed' => count($missed),
                'pending' => count($pending)
            ],
            'active_filter' => $filter
        ]);
    }

    /**
     * Quick follow-up (task): creates a follow-up for the task assignee +24h
     */
    public function addQuickCallback($taskId = null) {
        if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
            redirect('users/login');
            return;
        }
        if (function_exists('hasPermission') && !hasPermission('tasks.update')) {
            flash('task_error', 'You do not have permission to add follow-ups', 'alert-danger');
            redirect('tasks/show/' . (int)$taskId);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$taskId) {
            redirect('tasks/show/' . (int)$taskId);
            return;
        }

        $title = 'Follow-up';
        $notes = '';
        $remindTs = time() + 24 * 3600; // +24 hours
        $remindAt = date('Y-m-d H:i:s', $remindTs);

        // Determine recipient (assigned user)
        $recipientUserId = null;
        try {
            $task = $this->taskModel->getTaskById((int)$taskId);
            if ($task && !empty($task->assigned_to)) {
                $recipientUserId = (int)$task->assigned_to;
            } else {
                $assignees = $this->taskModel->getTaskUsers((int)$taskId);
                if (!empty($assignees)) {
                    $recipientUserId = (int)($assignees[0]->user_id ?? null);
                }
            }
        } catch (Exception $e) { /* ignore */ }

        $insertId = $this->reminderModel->add([
            'entity_type' => 'task',
            'entity_id' => (int)$taskId,
            'title' => $title,
            'notes' => $notes,
            'remind_at' => $remindAt,
            'created_by' => (int)($_SESSION['user_id'] ?? 0),
            'recipient_user_id' => $recipientUserId,
            'notify_all' => 0,
        ]);

        if (!$insertId) {
            flash('task_error', 'Failed to create follow-up.', 'alert-danger');
            redirect('tasks/show/' . (int)$taskId);
            return;
        }

        // Queue reminder email to the recipient if available
        try {
            require_once APPROOT . '/app/services/EmailService.php';
            $emailService = new EmailService();

            $toEmail = null;
            if ($recipientUserId) {
                $user = $this->userModel->getUserById($recipientUserId);
                $toEmail = $user && !empty($user['email']) ? $user['email'] : null;
            }

            if ($toEmail) {
                $task = $this->taskModel->getTaskById((int)$taskId);
                $subject = '[Reminder] Task follow-up: ' . (($task && !empty($task->title)) ? $task->title : ('Task #' . (int)$taskId)) . ' - ' . $title;
                $link = URLROOT . '/tasks/show/' . (int)$taskId;
                $html = "
                <h2>Follow-up Reminder</h2>
                <p><strong>Task:</strong> " . htmlspecialchars(($task && !empty($task->title)) ? $task->title : ('Task #' . (int)$taskId)) . "</p>
                <p><strong>When:</strong> " . date('M j, Y g:i A', $remindTs) . "</p>
                <p><strong>Title:</strong> " . htmlspecialchars($title) . "</p>
                <p><a href=\"" . $link . "\">Open task</a></p>
                ";
                $emailData = [
                    'to' => $toEmail,
                    'subject' => $subject,
                    'html_body' => $html,
                    'body' => strip_tags($html),
                ];
                $queueId = $emailService->queueEmail($emailData, 3, new DateTime($remindAt));
                if ($queueId) {
                    $this->reminderModel->setReminderQueueId((int)$insertId, (int)$queueId);
                }
            }
        } catch (Exception $e) {
            error_log('Queue task quick follow-up email failed: ' . $e->getMessage());
        }

        flash('task_success', 'Quick follow-up created for ' . date('M j, Y g:i A', $remindTs) . '.');
        redirect('tasks/show/' . (int)$taskId);
    }

    /**
     * Delete a task follow-up (admin/authorized users)
     */
    public function deleteCallback($callbackId = null) {
        if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
            redirect('users/login');
            return;
        }
        if (function_exists('hasPermission') && !hasPermission('tasks.update')) {
            flash('task_error', 'You do not have permission to delete follow-ups', 'alert-danger');
            redirect('tasks');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$callbackId) {
            redirect('tasks');
            return;
        }
        $cb = $this->reminderModel->getById((int)$callbackId);
        if (!$cb || (strtolower($cb['entity_type'] ?? '') !== 'task')) {
            flash('task_error', 'Follow-up not found', 'alert-danger');
            redirect('tasks');
            return;
        }
        $taskId = (int)($cb['entity_id'] ?? 0);
        $ok = $this->reminderModel->delete((int)$callbackId);
        if ($ok) {
            flash('task_success', 'Follow-up deleted.');
        } else {
            flash('task_error', 'Failed to delete follow-up.', 'alert-danger');
        }
        redirect('tasks/callbacksHistory/' . $taskId);
    }
    
    /**
     * Add a subtask to a task
     */
    public function addSubtask($parentId = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$parentId) {
            redirect('tasks/show/' . (int)$parentId);
            return;
        }
        $task = $this->taskModel->getTaskById((int)$parentId);
        if (!$task) {
            flash('task_error', 'Parent task not found', 'alert-danger');
            redirect('tasks');
            return;
        }
        $_POST = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW) ?? $_POST;
        $title = trim($_POST['title'] ?? '');
        $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
        $dueDate = !empty($_POST['due_date']) ? trim($_POST['due_date']) : null;
        $priority = !empty($_POST['priority']) ? trim($_POST['priority']) : 'Medium';
        $status = !empty($_POST['status']) ? trim($_POST['status']) : 'Pending';
        $description = trim($_POST['description'] ?? '');
		$referencesText = trim($_POST['references_text'] ?? '');
		$progressPercent = isset($_POST['progress_percent']) ? (int)$_POST['progress_percent'] : 0;
		$progressPercent = max(0, min(100, $progressPercent));
        
        if ($title === '') {
            flash('task_error', 'Subtask title is required', 'alert-danger');
            redirect('tasks/show/' . (int)$parentId);
            return;
        }
        
        $data = [
            'project_id' => $task->project_id,
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'priority' => $priority,
            'due_date' => $dueDate,
            'assigned_to' => $assignedTo,
            'created_by' => $_SESSION['user_id'],
			'parent_task_id' => (int)$parentId,
			'references_text' => $referencesText !== '' ? $referencesText : null,
			'progress_percent' => $progressPercent
        ];
        
        $subtaskId = $this->taskModel->create($data);
        if ($subtaskId) {
            flash('task_message', 'Subtask created');
        } else {
            flash('task_error', 'Failed to create subtask', 'alert-danger');
        }
        redirect('tasks/show/' . (int)$parentId);
    }
    
    // Show form to edit task
    public function edit($id) {
        // Get task by ID
        $this->taskModel = $this->model('Task');
        $task = $this->taskModel->getTaskById($id);
        
        if (!$task) {
            flash('task_error', 'Task not found', 'alert alert-danger');
            redirect('tasks');
        }
        
        // Get all projects for dropdown
        $this->projectModel = $this->model('Project');
        $projects = $this->projectModel->getAllProjects();
        
        // Get all users for assignments
        // Placeholder: $users = $this->userModel->getAllUsers();
        $users = [];
        
        $this->view('tasks/edit', [
            'title' => 'Edit Task',
            'task' => $task,
            'projects' => $projects,
            'users' => $users
        ]);
    }
    
    // Process the edit form
    public function update($id) {
        // Process form data if POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW) ?? $_POST;
            
            // Initialize data array
            $data = [
                'id' => $id,
                'project_id' => trim($_POST['project_id']),
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'status' => trim($_POST['status']),
                'priority' => trim($_POST['priority']),
                'due_date' => !empty($_POST['due_date']) ? trim($_POST['due_date']) : null,
                'assigned_to' => !empty($_POST['assigned_to']) ? trim($_POST['assigned_to']) : null,
                'project_id_err' => '',
                'title_err' => '',
                'status_err' => '',
                'priority_err' => ''
            ];
            
            // Validate project_id
            if (empty($data['project_id'])) {
                $data['project_id_err'] = 'Please select a project';
            }
            
            // Validate title
            if (empty($data['title'])) {
                $data['title_err'] = 'Please enter task title';
            }
            
            // Validate status
            if (empty($data['status'])) {
                $data['status_err'] = 'Please select a status';
            }
            
            // Validate priority
            if (empty($data['priority'])) {
                $data['priority_err'] = 'Please select a priority';
            }
            
            // Check if there are no errors
            if (empty($data['project_id_err']) && empty($data['title_err']) && 
                empty($data['status_err']) && empty($data['priority_err'])) {
                
                // Update task
                // Placeholder: $this->taskModel->update($data);
                
                // Set flash message
                // Placeholder: flash('task_message', 'Task updated successfully');
                
                // Redirect to the task details page
                header('Location: /tasks/show/' . $id);
                exit;
            } else {
                // Get all projects for dropdown
                // Placeholder: $projects = $this->projectModel->getAllProjects();
                $projects = [];
                
                // Get all users for assignments
                // Placeholder: $users = $this->userModel->getAllUsers();
                $users = [];
                
                // Load view with errors
                $this->view('tasks/edit', [
                    'title' => 'Edit Task',
                    'task' => (object)$data,
                    'projects' => $projects,
                    'users' => $users
                ]);
            }
        } else {
            // If not POST request, redirect to edit form
            header('Location: /tasks/edit/' . $id);
            exit;
        }
    }
    
    // Delete a task
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the task to find project_id for redirection
            $task = $this->taskModel->getTaskById($id);
            
            if (!$task) {
                flash('task_error', 'Task not found', 'alert-danger');
                redirect('tasks');
                return;
            }
            
            // Delete task
            if ($this->taskModel->delete($id)) {
                // Set flash message
                flash('task_message', 'Task deleted successfully');
                
                // Redirect to the project details page
                redirect('projects/viewProject/' . $task->project_id);
            } else {
                // Set flash message for error
                flash('task_error', 'Error deleting task', 'alert-danger');
                redirect('tasks/show/' . $id);
            }
        } else {
            // If not POST request, redirect to tasks index
            redirect('tasks');
        }
    }
    
    /**
     * Show form to manage task assignments
     * 
     * @param int $id Task ID
     * @return void
     */
    public function manageAssignments($id) {
        $task = $this->taskModel->getTaskById($id);
        
        if (!$task) {
            flash('task_error', 'Task not found', 'alert-danger');
            redirect('tasks');
        }
        
        // Get the project to find users that can be assigned
        $project = $this->projectModel->getProjectById($task->project_id);
        
        // Get users already assigned to this task
        $assignedUsers = $this->taskModel->getTaskUsers($id);
        
        // Get users assigned to the project
        $projectUsers = $this->projectModel->getProjectUsers($project->id);
        
        // Create a map of assigned user IDs for easy lookup
        $assignedUserIds = [];
        foreach ($assignedUsers as $user) {
            $assignedUserIds[] = $user->user_id;
        }
        
        $this->view('tasks/assignments', [
            'title' => 'Manage Assignments - ' . $task->title,
            'task' => $task,
            'project' => $project,
            'project_users' => $projectUsers,
            'assigned_users' => $assignedUsers,
            'assigned_user_ids' => $assignedUserIds
        ]);
    }
    
    /**
     * Process form to assign users to a task
     * 
     * @param int $id Task ID
     * @return void
     */
    public function assignUsers($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tasks/manageAssignments/' . $id);
        }
        
        $task = $this->taskModel->getTaskById($id);
        
        if (!$task) {
            flash('task_error', 'Task not found', 'alert-danger');
            redirect('tasks');
        }
        
		// Get the selected user IDs (preserve array, then normalize to ints)
		$userIdsRaw = isset($_POST['user_ids']) ? (array)$_POST['user_ids'] : [];
		$userIds = array_values(array_filter(array_map('intval', $userIdsRaw), function($v){ return $v > 0; }));
        
        // Start transaction
        $success = true;
        
        // First, remove all existing assignments
        $currentlyAssigned = $this->taskModel->getTaskUsers($id);
		$currentlyAssignedIds = array_map(function($user) {
		 return (int)$user->user_id;
		}, $currentlyAssigned);
        
        // Find users to remove (those who were assigned but not in the new selection)
        $usersToRemove = array_diff($currentlyAssignedIds, $userIds);
        foreach ($usersToRemove as $userId) {
            $success = $success && $this->taskModel->removeUserFromTask($id, $userId);
        }
        
        // Now assign selected users
        foreach ($userIds as $userId) {
            $success = $success && $this->taskModel->assignUserToTask($id, $userId);
        }
		
		// Update primary assignee only if explicitly provided
		$primary = isset($_POST['primary_user_id']) ? (int)$_POST['primary_user_id'] : 0;
		if ($primary > 0) {
			// Ensure the chosen primary is included in assignments
			if (!in_array($primary, $userIds, true)) {
				$success = $success && $this->taskModel->assignUserToTask($id, $primary);
			}
			$this->taskModel->updateAssignedTo((int)$id, $primary);
		}
        
        if ($success) {
            flash('task_message', 'Task assignments updated successfully');
        } else {
            flash('task_error', 'Error updating task assignments', 'alert-danger');
        }
        
        redirect('tasks/manageAssignments/' . $id);
    }
    
    /**
     * Remove a user from a task
     * 
     * @param int $taskId Task ID
     * @param int $userId User ID
     * @return void
     */
    public function removeUser($taskId, $userId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tasks/manageAssignments/' . $taskId);
        }
        
        $task = $this->taskModel->getTaskById($taskId);
        
        if (!$task) {
            flash('task_error', 'Task not found', 'alert-danger');
            redirect('tasks');
        }
        
        if ($this->taskModel->removeUserFromTask($taskId, $userId)) {
            flash('task_message', 'User removed from task successfully');
        } else {
            flash('task_error', 'Error removing user from task', 'alert-danger');
        }
        
        redirect('tasks/manageAssignments/' . $taskId);
    }
    
    /**
     * Update the status of a task
     * 
     * @param int $id Task ID
     * @return void
     */
    public function updateStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tasks/show/' . $id);
            return;
        }
        
        // Get task by ID
        $task = $this->taskModel->getTaskById($id);
        
        if (!$task) {
            flash('task_error', 'Task not found', 'alert-danger');
            redirect('tasks');
            return;
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Get new status
        $newStatus = isset($_POST['status']) ? trim($_POST['status']) : '';
        
        if (empty($newStatus)) {
            flash('task_error', 'No status provided', 'alert-danger');
            redirect('tasks/show/' . $id);
            return;
        }
        
        // Update task status
        if ($this->taskModel->updateStatus($id, $newStatus)) {
            flash('task_message', 'Task status updated successfully');
        } else {
            flash('task_error', 'Error updating task status', 'alert-danger');
        }
        
        redirect('tasks/show/' . $id);
    }
	
	/**
	 * Update task/subtask progress (0-100)
	 */
	public function updateProgress($id) {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			redirect('tasks/show/' . (int)$id);
			return;
		}
		$task = $this->taskModel->getTaskById($id);
		if (!$task) {
			flash('task_error', 'Task not found', 'alert-danger');
			redirect('tasks');
			return;
		}
		$progress = isset($_POST['progress_percent']) ? (int)$_POST['progress_percent'] : 0;
		$progress = max(0, min(100, $progress));
		if ($this->taskModel->updateProgress((int)$id, $progress)) {
			flash('task_message', 'Progress updated to ' . $progress . '%');
		} else {
			flash('task_error', 'Failed to update progress', 'alert-danger');
		}
		$redirectId = $task->parent_task_id ?? $task->id;
		redirect('tasks/show/' . (int)$redirectId);
	}
    
    /**
     * Mark a task as complete (convenience method)
     * 
     * @param int $id Task ID
     * @return void
     */
    public function markComplete($id) {
        // Simulate a POST request with status = Completed
        $_POST['status'] = 'Completed';
        $this->updateStatus($id);
    }
} 