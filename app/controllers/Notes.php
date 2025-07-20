<?php

class Notes extends Controller {
    private $noteModel;
    private $projectModel;
    private $taskModel;
    
    public function __construct() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('auth');
        }
        
        $this->noteModel = $this->model('Note');
        $this->projectModel = $this->model('Project');
        $this->taskModel = $this->model('Task');
        
        // Create Notes table if it doesn't exist
        $this->noteModel->createNotesTable();
    }
    
    /**
     * Display all notes for the logged-in user
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $notes = $this->noteModel->getNotesByUser($userId);
        
        $data = [
            'title' => 'My Notes',
            'notes' => $notes
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
        
        if (!$note || $note['created_by'] != $userId) {
            flash('note_message', 'Note not found or you do not have permission to view it.', 'alert-danger');
            redirect('notes');
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
        
        $data = [
            'title' => 'View Note - ' . $note['title'],
            'note' => $note,
            'related_info' => $relatedInfo
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
            
            $data = [
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'reference_id' => $reference_id,
                'created_by' => $_SESSION['user_id'],
                'title_err' => '',
                'content_err' => '',
                'type_err' => '',
                'reference_id_err' => ''
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
            if (!in_array($data['type'], ['project', 'task', 'personal'])) {
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
            } else {
                // For personal notes, no reference needed
                $data['reference_id_err'] = '';
            }
            
            // Make sure no errors
            if (empty($data['title_err']) && empty($data['content_err']) && 
                empty($data['type_err']) && empty($data['reference_id_err'])) {
                
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
            
            $data = [
                'title' => 'Add Note',
                'projects' => $projects,
                'tasks' => $tasks
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
            
            $data = [
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'reference_id' => $reference_id,
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
            if (!in_array($data['type'], ['project', 'task', 'personal'])) {
                $errors['type'] = 'Invalid note type';
                error_log('AJAX Note Add - Invalid type: ' . $data['type']);
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
            
            $data = [
                'id' => $id,
                'title' => $title,
                'content' => $content,
                'created_by' => $_SESSION['user_id'],
                'title_err' => '',
                'content_err' => ''
            ];
            
            // Validate title
            if (empty($data['title'])) {
                $data['title_err'] = 'Please enter a title';
            }
            
            // Validate content
            if (empty($data['content'])) {
                $data['content_err'] = 'Please enter note content';
            }
            
            // Make sure no errors
            if (empty($data['title_err']) && empty($data['content_err'])) {
                // Update note
                if ($this->noteModel->update($data)) {
                    flash('note_success', 'Note updated successfully');
                    redirect('notes');
                } else {
                    flash('note_error', 'Something went wrong. Please try again.');
                }
            }
            
            // Load view with errors
            $this->view('notes/edit', [
                'title' => 'Edit Note',
                'note' => $note,
                'title_err' => $data['title_err'],
                'content_err' => $data['content_err']
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
                'content_err' => ''
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
        if (!in_array($type, ['project', 'task'])) {
            echo json_encode(['error' => 'Invalid type']);
            return;
        }
        
        $notes = $this->noteModel->getNotesByReference($type, $id);
        echo json_encode($notes);
    }
} 