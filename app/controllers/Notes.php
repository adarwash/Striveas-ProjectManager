<?php

class Notes extends Controller {
    private $noteModel;
    private $projectModel;
    private $taskModel;
    private $clientModel;
    
    public function __construct() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth');
        }
        
        $this->noteModel = $this->model('Note');
        $this->projectModel = $this->model('Project');
        $this->taskModel = $this->model('Task');
        $this->clientModel = $this->model('Client');
        
        // Create Notes table if it doesn't exist
        $this->noteModel->createNotesTable();
    }
    
    /**
     * Display all notes for the logged-in user
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        
        // Optional filter: type + reference_id (e.g., client)
        $type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : '';
        $referenceId = isset($_GET['reference_id']) ? (int)$_GET['reference_id'] : 0;
        
        if (in_array($type, ['project', 'task', 'client']) && $referenceId > 0) {
            // Fetch notes for this reference visible to the user
            $notes = $this->noteModel->getNotesByReferenceForUser($type, $referenceId, $userId);
            $title = ucfirst($type) . ' Notes';
        } else {
            // Default: all notes visible to user
            $notes = $this->noteModel->getNotesByUser($userId);
            $title = 'My Notes';
        }
        
        $data = [
            'title' => $title,
            'notes' => $notes,
            'filter_type' => $type,
            'filter_reference_id' => $referenceId
        ];
        
        $this->view('notes/index', $data);
    }
    
    /**
     * View a specific note
     */
    public function show($id = null) {
        if (!$id) {
            redirect('notes');
        }
        
        $userId = $_SESSION['user_id'];
        $note = $this->noteModel->getNoteById($id);
        
        if (!$note) {
            // Clear any previous success messages before setting error
            unset($_SESSION['flash']['note_success']);
            flash('note_error', 'Note not found.');
            redirect('notes');
            return;
        }
        
        if (!$this->noteModel->hasAccess($id, $userId, 'view')) {
            // Clear any previous success messages before setting error
            unset($_SESSION['flash']['note_success']);
            flash('note_error', 'You do not have permission to view this note.');
            redirect('notes');
            return;
        }
        
        // Get related project or task info if applicable
        $relatedInfo = null;
        if ($note['type'] === 'project' && $note['reference_id']) {
            try {
                $projectObj = $this->projectModel->getProjectById($note['reference_id']);
                if ($projectObj) {
                    $relatedInfo = (array)$projectObj;
                    $relatedInfo['type'] = 'project';
                }
            } catch (Exception $e) {
                // Project might not exist anymore
            }
        } elseif ($note['type'] === 'task' && $note['reference_id']) {
            try {
                $taskObj = $this->taskModel->getTaskById($note['reference_id']);
                if ($taskObj) {
                    $relatedInfo = (array)$taskObj;
                    $relatedInfo['type'] = 'task';
                }
            } catch (Exception $e) {
                // Task might not exist anymore
            }
        }
        
        // Get shared users if the current user is the owner
        $sharedUsers = [];
        if ($note['created_by'] == $userId) {
            $sharedUsers = $this->noteModel->getSharedUsers($id);
        }
        
        $data = [
            'title' => 'View Note - ' . $note['title'],
            'note' => $note,
            'related_info' => $relatedInfo,
            'shared_users' => $sharedUsers
        ];
        
        $this->view('notes/show', $data);
    }
    
    /**
     * Add a new note
     */
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if it's an AJAX request
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                return $this->addAjax();
            }
            
            // Process regular form submission
            // Sanitize POST data - using a modern approach instead of deprecated FILTER_SANITIZE_STRING
            $title = isset($_POST['title']) ? htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8') : '';
            $content = isset($_POST['content']) ? htmlspecialchars(trim($_POST['content']), ENT_QUOTES, 'UTF-8') : '';
            $type = isset($_POST['type']) ? htmlspecialchars(trim($_POST['type']), ENT_QUOTES, 'UTF-8') : '';
            $reference_id = isset($_POST['reference_id']) ? (int)$_POST['reference_id'] : null;
			$tags = isset($_POST['tags']) ? trim($_POST['tags']) : '';
            
            $data = [
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'reference_id' => $reference_id,
				'tags' => $tags,
                'created_by' => $_SESSION['user_id'],
                'title_err' => '',
                'content_err' => '',
                'type_err' => '',
				'reference_id_err' => '',
				'tags_err' => ''
            ];
            
            // Validate title
            if (empty($data['title'])) {
                $data['title_err'] = 'Please enter a title';
            }
            
            // Validate content
            if (empty($data['content'])) {
                $data['content_err'] = 'Please enter note content';
            }
            
            // Validate type
            if (!in_array($data['type'], ['project', 'task', 'client', 'personal'])) {
                $data['type_err'] = 'Invalid note type';
            }
            
            // Validate reference exists (only for project and task types)
            if ($data['type'] === 'project') {
                $project = $this->projectModel->getProjectById($data['reference_id']);
                if (!$project) {
                    $data['reference_id_err'] = 'Project not found';
                }
            } elseif ($data['type'] === 'task') {
                $task = $this->taskModel->getTaskById($data['reference_id']);
                if (!$task) {
                    $data['reference_id_err'] = 'Task not found';
                }
            } elseif ($data['type'] === 'client') {
                $client = $this->clientModel->getClientById($data['reference_id']);
                if (!$client) {
                    $data['reference_id_err'] = 'Client not found';
                }
            } else {
                // For personal notes, no reference needed
                $data['reference_id_err'] = '';
            }
			
			// Validate tags length
			if (!empty($data['tags']) && strlen($data['tags']) > 255) {
				$data['tags_err'] = 'Tags must be 255 characters or fewer';
			}
            
            // Make sure no errors
            if (empty($data['title_err']) && empty($data['content_err']) && 
				empty($data['type_err']) && empty($data['reference_id_err']) && empty($data['tags_err'])) {
                
                // Create note
                if ($this->noteModel->create($data)) {
                    flash('note_success', 'Note added successfully');
                    redirect('notes');
                } else {
                    flash('note_error', 'Something went wrong. Please try again.');
                    $this->view('notes/add', $data);
                }
            } else {
                // Load view with errors
                $this->view('notes/add', $data);
            }
        } else {
            // Get projects and tasks for selection
            $userId = $_SESSION['user_id'];
            $projects = $this->projectModel->getProjectsByUser($userId);
            $tasks = $this->taskModel->getTasksByUser($userId);
            $clients = $this->clientModel->getAllClients();
            
            // Prefill from query params
            $preType = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : '';
            if (!in_array($preType, ['project', 'task', 'client', 'personal'])) {
                $preType = '';
            }
            $preRefId = isset($_GET['reference_id']) ? (int)$_GET['reference_id'] : (isset($_GET['client_id']) ? (int)$_GET['client_id'] : null);
            
            $data = [
                'title' => 'Add Note',
                'projects' => $projects,
                'tasks' => $tasks,
                'clients' => $clients,
                'type' => $preType,
				'reference_id' => $preRefId,
				'tags' => '',
				'tags_err' => ''
            ];
            
            $this->view('notes/add', $data);
        }
    }
    
    /**
     * Handle AJAX request to add a note
     */
    private function addAjax() {
        // Prevent any output by starting output buffering
        ob_start();
        
        try {
            // Clear any previous output that might have occurred
            if (ob_get_length()) ob_clean();
            
            // Set header for JSON response
            header('Content-Type: application/json');
            
            // Sanitize POST data - using modern alternatives to deprecated FILTER_SANITIZE_STRING
            $title = isset($_POST['title']) ? htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8') : '';
            $content = isset($_POST['content']) ? htmlspecialchars(trim($_POST['content']), ENT_QUOTES, 'UTF-8') : '';
			$type = isset($_POST['type']) ? htmlspecialchars(trim($_POST['type']), ENT_QUOTES, 'UTF-8') : '';
            $reference_id = isset($_POST['reference_id']) ? (int)$_POST['reference_id'] : null;
			$tags = isset($_POST['tags']) ? trim($_POST['tags']) : '';
            
            $data = [
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'reference_id' => $reference_id,
				'tags' => $tags,
				'created_by' => $_SESSION['user_id'] ?? 0
            ];
            
            error_log('AJAX Note Add - Data: ' . json_encode($data));
            
            // Check for missing required data
			if (empty($title) || empty($content) || empty($type)) {
                error_log('AJAX Note Add - Missing form fields: ' . json_encode($_POST));
                echo json_encode([
                    'success' => false,
                    'message' => 'Missing required form fields',
                    'debug' => [
                        'provided_data' => $_POST,
                        'processed_data' => $data
                    ]
                ]);
                ob_end_flush();
                return;
            }
            
            $errors = [];
            
            // Validate title
			if (empty($data['title'])) {
                $errors['title'] = 'Please enter a title';
            }
            
            // Validate content
            if (empty($data['content'])) {
                $errors['content'] = 'Please enter note content';
            }
            
            // Validate type
			if (!in_array($data['type'], ['project', 'task', 'client', 'personal'])) {
                $errors['type'] = 'Invalid note type';
                error_log('AJAX Note Add - Invalid type: ' . $data['type']);
            }
			
			if (!empty($data['tags']) && strlen($data['tags']) > 255) {
				$errors['tags'] = 'Tags must be 255 characters or fewer';
			}
            
            // Validate reference exists (only for project and task types)
            if ($data['type'] === 'project') {
                if (empty($data['reference_id'])) {
                    $errors['reference_id'] = 'Project ID is required';
                    error_log('AJAX Note Add - Missing project ID');
                } else {
                    $project = $this->projectModel->getProjectById($data['reference_id']);
                    if (!$project) {
                        $errors['reference_id'] = 'Project not found';
                        error_log('AJAX Note Add - Invalid project ID: ' . $data['reference_id']);
                    }
                }
            } elseif ($data['type'] === 'task') {
                if (empty($data['reference_id'])) {
                    $errors['reference_id'] = 'Task ID is required';
                    error_log('AJAX Note Add - Missing task ID');
                } else {
                    $task = $this->taskModel->getTaskById($data['reference_id']);
                    if (!$task) {
                        $errors['reference_id'] = 'Task not found';
                        error_log('AJAX Note Add - Invalid task ID: ' . $data['reference_id']);
                    }
                }
            } elseif ($data['type'] === 'client') {
                if (empty($data['reference_id'])) {
                    $errors['reference_id'] = 'Client ID is required';
                    error_log('AJAX Note Add - Missing client ID');
                } else {
                    $client = $this->clientModel->getClientById($data['reference_id']);
                    if (!$client) {
                        $errors['reference_id'] = 'Client not found';
                        error_log('AJAX Note Add - Invalid client ID: ' . $data['reference_id']);
                    }
                }
            }
            
            // Return errors if any
            if (!empty($errors)) {
                echo json_encode([
                    'success' => false,
                    'errors' => $errors,
                    'message' => 'Please fix the errors and try again.'
                ]);
                ob_end_flush();
                return;
            }
            
            // Log before attempting to create note
            error_log('AJAX Note Add - About to create note: ' . json_encode($data));
            
            // For personal notes, set reference_id to null
            if ($data['type'] === 'personal') {
                $data['reference_id'] = null;
            }
            
            // Create note
            $noteId = $this->noteModel->create($data);
            
            if ($noteId) {
                error_log('AJAX Note Add - Note created successfully with ID/result: ' . (is_int($noteId) ? $noteId : 'true'));
                
                // Try to get the note details if we have an ID
                $note = is_int($noteId) ? $this->noteModel->getNoteById($noteId) : null;
                
                if ($note) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Note added successfully',
                        'note' => $note
                    ]);
                } else {
                    // We don't have the note details, but the creation was successful
                    echo json_encode([
                        'success' => true,
                        'message' => 'Note added successfully',
                        'note_created' => true
                    ]);
                }
            } else {
                error_log('AJAX Note Add - Failed to create note');
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to add note. Please try again.'
                ]);
            }
        } catch (Exception $e) {
            $errorMessage = 'Error in Notes::addAjax: ' . $e->getMessage();
            error_log($errorMessage);
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            // In case headers haven't been sent yet
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            
            echo json_encode([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'debug' => [
                    'error' => $errorMessage,
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
        }
        
        // Ensure output buffer is flushed
        ob_end_flush();
    }
    
    /**
     * Edit a note
     */
    public function edit($id) {
        // Check if user is authorized to edit this note
        $note = $this->noteModel->getNoteById($id);
        
        if (!$note) {
            flash('note_error', 'Note not found', 'alert alert-danger');
            redirect('notes');
        }
        
        // Check if user owns this note
        if ($note['created_by'] != $_SESSION['user_id']) {
            flash('note_error', 'You are not authorized to edit this note', 'alert alert-danger');
            redirect('notes');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data using modern approach instead of deprecated FILTER_SANITIZE_STRING
            $title = isset($_POST['title']) ? htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8') : '';
            $content = isset($_POST['content']) ? htmlspecialchars(trim($_POST['content']), ENT_QUOTES, 'UTF-8') : '';
            
			$tags = isset($_POST['tags']) ? trim($_POST['tags']) : '';
			
			$data = [
				'id' => $id,
				'title' => $title,
				'content' => $content,
				'tags' => $tags,
				'created_by' => $_SESSION['user_id'],
				'title_err' => '',
				'content_err' => '',
				'tags_err' => ''
			];
            
            // Validate title
            if (empty($data['title'])) {
                $data['title_err'] = 'Please enter a title';
            }
            
            // Validate content
			if (empty($data['content'])) {
                $data['content_err'] = 'Please enter note content';
            }
			
			if (!empty($data['tags']) && strlen($data['tags']) > 255) {
				$data['tags_err'] = 'Tags must be 255 characters or fewer';
			}
            
            // Make sure no errors
			if (empty($data['title_err']) && empty($data['content_err']) && empty($data['tags_err'])) {
                // Update note
                if ($this->noteModel->update($data)) {
                    flash('note_success', 'Note updated successfully');
                    redirect('notes');
                } else {
                    flash('note_error', 'Something went wrong. Please try again.');
                }
            }
            
			$note['title'] = $data['title'];
			$note['content'] = $data['content'];
			$note['tags'] = $data['tags'];
			
			$projects = [];
			$tasks = [];
			if ($note['type'] === 'project') {
				$project = $this->projectModel->getProjectById($note['reference_id']);
				if ($project) {
					$projects[$project->id] = [
						'id' => $project->id,
						'title' => $project->title
					];
				}
			} elseif ($note['type'] === 'task') {
				$task = $this->taskModel->getTaskById($note['reference_id']);
				if ($task) {
					$tasks[$task->id] = [
						'id' => $task->id,
						'title' => $task->title
					];
				}
			}

			$this->view('notes/edit', [
                'title' => 'Edit Note',
                'note' => $note,
				'projects' => $projects,
				'tasks' => $tasks,
				'title_err' => $data['title_err'],
				'content_err' => $data['content_err'],
				'tags_err' => $data['tags_err']
            ]);
        } else {
            // Get related projects and tasks for display
            $this->projectModel = $this->model('Project');
            $this->taskModel = $this->model('Task');
            
            $projects = [];
            $tasks = [];
            
            // Get project info if the note is for a project
            if ($note['type'] === 'project') {
                $project = $this->projectModel->getProjectById($note['reference_id']);
                if ($project) {
                    $projects[$project->id] = [
                        'id' => $project->id,
                        'title' => $project->title
                    ];
                }
            } else if ($note['type'] === 'task') {
                // Get task info if the note is for a task
                $task = $this->taskModel->getTaskById($note['reference_id']);
                if ($task) {
                    $tasks[$task->id] = [
                        'id' => $task->id,
                        'title' => $task->title
                    ];
                }
            }
            
            // Load view with note data
			$this->view('notes/edit', [
				'title' => 'Edit Note',
				'note' => $note,
				'projects' => $projects,
				'tasks' => $tasks,
				'title_err' => '',
				'content_err' => '',
				'tags_err' => ''
			]);
        }
    }
    
    /**
     * Delete a note
     */
    public function delete($id) {
        // Handle AJAX request
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get note to verify it exists
            $note = $this->noteModel->getNoteById($id);
            
            if (!$note) {
                if ($isAjax) {
                    echo json_encode(['success' => false, 'message' => 'Note not found']);
                    return;
                } else {
                    flash('note_error', 'Note not found', 'alert alert-danger');
                    redirect('notes');
                    return;
                }
            }
            
            // Check if user owns this note
            if ($note['created_by'] != $_SESSION['user_id']) {
                if ($isAjax) {
                    echo json_encode(['success' => false, 'message' => 'You are not authorized to delete this note']);
                    return;
                } else {
                    flash('note_error', 'You are not authorized to delete this note', 'alert alert-danger');
                    redirect('notes');
                    return;
                }
            }
            
            // Delete note
            if ($this->noteModel->delete($id, $_SESSION['user_id'])) {
                if ($isAjax) {
                    echo json_encode(['success' => true, 'message' => 'Note deleted successfully']);
                    return;
                } else {
                    flash('note_success', 'Note deleted successfully');
                }
            } else {
                if ($isAjax) {
                    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
                    return;
                } else {
                    flash('note_error', 'Something went wrong. Please try again.');
                }
            }
        } else if ($isAjax) {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        if (!$isAjax) {
            redirect('notes');
        }
    }
    
    /**
     * Get notes for a specific project or task (AJAX endpoint)
     */
    public function get($type, $id) {
        if (!in_array($type, ['project', 'task', 'client'])) {
            echo json_encode(['error' => 'Invalid type']);
            return;
        }
        
        $notes = $this->noteModel->getNotesByReference($type, $id);
        echo json_encode($notes);
    }
    
    /**
     * Share a note with another user
     */
    public function share($id = null) {
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('notes');
        }
        
        $userId = $_SESSION['user_id'];
        $shareWithUserId = $_POST['user_id'] ?? 0;
        $permission = $_POST['permission'] ?? 'view';
        

        // Validate inputs
        if (empty($shareWithUserId) || $shareWithUserId == 0) {
            flash('note_message', 'Please select a user to share with.', 'alert-warning');
            redirect('notes/show/' . $id);
            return;
        }
        
        // Validate permission
        if (!in_array($permission, ['view', 'edit'])) {
            $permission = 'view';
        }
        
        // Check where we're going to redirect
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $isFromDetailPage = strpos($referer, '/notes/show/') !== false;
        
        if ($this->noteModel->shareNote((int)$id, (int)$userId, (int)$shareWithUserId, $permission)) {
            if ($isFromDetailPage) {
                flash('note_message', 'Note shared successfully!', 'alert-success');
            } else {
                flash('note_success', 'Note shared successfully!');
            }
        } else {
            if ($isFromDetailPage) {
                flash('note_message', 'Failed to share note. Make sure you own the note.', 'alert-danger');
            } else {
                flash('note_error', 'Failed to share note. Make sure you own the note.');
            }
        }
        
        // Redirect based on where we came from
        if ($isFromDetailPage) {
            redirect('notes/show/' . $id);
        } else {
            redirect('notes');
        }
    }
    
    /**
     * Remove sharing for a note
     */
    public function unshare($id = null) {
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('notes');
        }
        
        $userId = $_SESSION['user_id'];
        $unshareUserId = $_POST['user_id'] ?? 0;
        
        // Check where we're going to redirect
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $isFromDetailPage = strpos($referer, '/notes/show/') !== false;
        
        if ($this->noteModel->unshareNote($id, $userId, $unshareUserId)) {
            if ($isFromDetailPage) {
                flash('note_message', 'Sharing removed successfully!', 'alert-success');
            } else {
                flash('note_success', 'Sharing removed successfully!');
            }
        } else {
            if ($isFromDetailPage) {
                flash('note_message', 'Failed to remove sharing. Make sure you own the note.', 'alert-danger');
            } else {
                flash('note_error', 'Failed to remove sharing. Make sure you own the note.');
            }
        }
        
        // Redirect based on where we came from
        if ($isFromDetailPage) {
            redirect('notes/show/' . $id);
        } else {
            redirect('notes');
        }
    }
    
    /**
     * Get shared users for a note (AJAX)
     */
    public function getSharedUsers($id = null) {
        header('Content-Type: application/json');
        
        if (!$id) {
            echo json_encode(['error' => 'Invalid note ID']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $note = $this->noteModel->getNoteById($id);
        
        // Only the owner can see who has access
        if (!$note || $note['created_by'] != $userId) {
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        $sharedUsers = $this->noteModel->getSharedUsers($id);
        echo json_encode(['success' => true, 'users' => $sharedUsers]);
    }
} 