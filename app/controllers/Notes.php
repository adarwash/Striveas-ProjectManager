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