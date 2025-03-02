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
     * Add a new note
     */
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $data = [
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content']),
                'type' => trim($_POST['type']),
                'reference_id' => (int)$_POST['reference_id'],
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
            if (!in_array($data['type'], ['project', 'task'])) {
                $data['type_err'] = 'Invalid note type';
            }
            
            // Validate reference exists
            if ($data['type'] === 'project') {
                $project = $this->projectModel->getProjectById($data['reference_id']);
                if (!$project) {
                    $data['reference_id_err'] = 'Project not found';
                }
            } else {
                $task = $this->taskModel->getTaskById($data['reference_id']);
                if (!$task) {
                    $data['reference_id_err'] = 'Task not found';
                }
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
     * Edit a note
     */
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $data = [
                'id' => $id,
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content']),
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
                    $this->view('notes/edit', $data);
                }
            } else {
                // Load view with errors
                $this->view('notes/edit', $data);
            }
        } else {
            // Get note
            $note = $this->noteModel->getNoteById($id);
            
            // Check for note ownership
            if ($note && $note['created_by'] === $_SESSION['user_id']) {
                $data = [
                    'title' => 'Edit Note',
                    'note' => $note
                ];
                
                $this->view('notes/edit', $data);
            } else {
                flash('note_error', 'You are not authorized to edit this note');
                redirect('notes');
            }
        }
    }
    
    /**
     * Delete a note
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Delete note
            if ($this->noteModel->delete($id, $_SESSION['user_id'])) {
                flash('note_success', 'Note deleted successfully');
            } else {
                flash('note_error', 'Something went wrong. Please try again.');
            }
        }
        
        redirect('notes');
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