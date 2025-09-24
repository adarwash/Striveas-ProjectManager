<?php

// Include permission helper functions
require_once '../app/helpers/permissions_helper.php';
require_once '../app/helpers/search_permissions.php';

class Search extends Controller {
    
    private $projectModel;
    private $taskModel;
    private $userModel;
    private $clientModel;
    private $timeModel;
    private $noteModel;
    private $ticketModel;
    private $db;
    
    public function __construct() {
        // Basic access check
        if (!isLoggedIn()) {
            redirect('auth/login');
        }
        
        // Initialize database
        $this->db = new EasySQL(DB1);
        
        // Initialize models
        $this->projectModel = $this->model('Project');
        $this->taskModel = $this->model('Task');
        $this->userModel = $this->model('User');
        
        // Initialize optional models with error handling
        try {
            $this->clientModel = $this->model('Client');
        } catch (Exception $e) {
            $this->clientModel = null;
        }
        
        try {
            $this->timeModel = $this->model('TimeTracking');
        } catch (Exception $e) {
            $this->timeModel = null;
        }
        
        try {
            $this->noteModel = $this->model('Note');
        } catch (Exception $e) {
            $this->noteModel = null;
        }
        
        try {
            $this->ticketModel = $this->model('Ticket');
        } catch (Exception $e) {
            $this->ticketModel = null;
        }
    }
    
    /**
     * Check if user has permission to search a specific entity type
     */
    private function canSearchEntity($entityType) {
        switch ($entityType) {
            case 'projects':
                return hasSearchPermission('projects.read') || isManager();
            case 'tasks':
                return hasSearchPermission('tasks.read') || isManager();
            case 'users':
                return hasSearchPermission('users.read') || hasSearchPermission('reports_read') || isManager();
            case 'clients':
                return hasSearchPermission('clients.read') || isManager();
            case 'notes':
                // Users can always search notes (they'll only see their own unless they have notes.read permission)
                return true;
            default:
                return isManager(); // Default fallback for admin-only access
        }
    }
    
    /**
     * Check if user can view a specific item based on ownership and permissions
     */
    private function canViewItem($item, $entityType) {
        $userId = $_SESSION['user_id'];
        return canViewSearchItem($item, $entityType, $userId);
    }
    
    /**
     * Test endpoint to verify controller is working
     */
    public function test() {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Search controller is working!',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    /**
     * Debug endpoint to check user permissions for search
     */
    public function permissions() {
        header('Content-Type: application/json');
        
        if (!isLoggedIn()) {
            echo json_encode(['error' => 'Not logged in']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $permissions = [];
        $searchTypes = ['projects', 'tasks', 'users', 'clients', 'notes'];
        
        foreach ($searchTypes as $type) {
            $permissions[$type] = [
                'can_search' => $this->canSearchEntity($type),
                'permission_check' => hasSearchPermission($type . '.read'),
                'is_manager' => isManager()
            ];
        }
        
        echo json_encode([
            'success' => true,
            'user_id' => $userId,
            'role' => $_SESSION['role'] ?? 'unknown',
            'permissions' => $permissions,
            'allowed_types' => getAllowedSearchTypes(),
            'permission_system_available' => function_exists('hasPermission')
        ]);
    }
    
    /**
     * Main search endpoint for AJAX requests
     */
    public function index() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $query = trim($_GET['q'] ?? '');
        $type = $_GET['type'] ?? 'all';
        $limit = min((int)($_GET['limit'] ?? 10), 50); // Max 50 results
        
        if (empty($query) || strlen($query) < 2) {
            echo json_encode([
                'success' => true,
                'results' => [],
                'total' => 0,
                'message' => 'Enter at least 2 characters to search'
            ]);
            return;
        }
        
        // Check if user has permission to search at least one entity type
        $canSearchAny = false;
        $searchableTypes = ['projects', 'tasks', 'users', 'clients', 'notes', 'tickets'];
        
        if ($type === 'all') {
            foreach ($searchableTypes as $entityType) {
                if ($this->canSearchEntity($entityType)) {
                    $canSearchAny = true;
                    break;
                }
            }
        } else {
            $canSearchAny = $this->canSearchEntity($type);
        }
        
        if (!$canSearchAny) {
            echo json_encode([
                'success' => false,
                'results' => [],
                'total' => 0,
                'message' => 'You do not have permission to search this content type.',
                'error' => 'insufficient_permissions'
            ]);
            return;
        }
        
        try {
            // Log search for security audit
            error_log("User {$_SESSION['user_id']} searched for: '$query' (type: $type)");
            
            $results = $this->performSearch($query, $type, $limit);
            
            echo json_encode([
                'success' => true,
                'results' => $results,
                'total' => count($results),
                'query' => $query,
                'type' => $type,
                'permissions' => [
                    'projects' => $this->canSearchEntity('projects'),
                    'tasks' => $this->canSearchEntity('tasks'),
                    'users' => $this->canSearchEntity('users'),
                    'clients' => $this->canSearchEntity('clients'),
                    'notes' => $this->canSearchEntity('notes'),
                    'tickets' => $this->canSearchEntity('tickets')
                ],
                'debug' => [
                    'models_loaded' => [
                        'project' => ($this->projectModel !== null),
                        'task' => ($this->taskModel !== null),
                        'user' => ($this->userModel !== null),
                        'client' => ($this->clientModel !== null),
                        'note' => ($this->noteModel !== null),
                        'ticket' => ($this->ticketModel !== null)
                    ]
                ]
            ]);
        } catch (Exception $e) {
            error_log('Search Error: ' . $e->getMessage());
            error_log('Search Error Stack: ' . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'error' => 'Search failed',
                'message' => 'An error occurred while searching',
                'debug_error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Search suggestions endpoint for autocomplete
     */
    public function suggestions() {
        header('Content-Type: application/json');
        
        $query = trim($_GET['q'] ?? '');
        if (strlen($query) < 2) {
            echo json_encode(['suggestions' => []]);
            return;
        }
        
        try {
            $suggestions = $this->getSearchSuggestions($query);
            echo json_encode(['suggestions' => $suggestions]);
        } catch (Exception $e) {
            echo json_encode(['suggestions' => []]);
        }
    }
    
    /**
     * Perform the actual search across multiple entities
     */
    private function performSearch($query, $type = 'all', $limit = 10) {
        $results = [];
        $searchQuery = '%' . $query . '%';
        
        // Search Projects (with permission checks)
        if (($type === 'all' || $type === 'projects') && $this->canSearchEntity('projects')) {
            try {
                $projects = $this->projectModel->searchProjects($searchQuery, $limit);
                foreach ($projects as $project) {
                    // Check if user can view this specific project
                    if ($this->canViewItem($project, 'projects')) {
                        $results[] = [
                            'type' => 'project',
                            'id' => $project['id'],
                            'title' => $project['title'],
                            'description' => $this->truncateText($project['description'] ?? '', 100),
                            'url' => '/projects/viewProject/' . $project['id'],
                            'icon' => 'bi bi-folder',
                            'status' => $project['status'] ?? 'Active',
                            'meta' => [
                                'Client' => $project['client_name'] ?? 'No Client',
                                'Created' => date('M j, Y', strtotime($project['created_at'] ?? 'now'))
                            ]
                        ];
                    }
                }
            } catch (Exception $e) {
                error_log('Project search error: ' . $e->getMessage());
            }
        }
        
        // Search Tasks (with permission checks)
        if (($type === 'all' || $type === 'tasks') && $this->canSearchEntity('tasks')) {
            try {
                $tasks = $this->taskModel->searchTasks($searchQuery, $limit);
                foreach ($tasks as $task) {
                    // Check if user can view this specific task
                    if ($this->canViewItem($task, 'tasks')) {
                        $results[] = [
                            'type' => 'task',
                            'id' => $task['id'],
                            'title' => $task['title'],
                            'description' => $this->truncateText($task['description'] ?? '', 100),
                            'url' => '/tasks/show/' . $task['id'],
                            'icon' => 'bi bi-check-square',
                            'status' => $task['status'] ?? 'Open',
                            'meta' => [
                                'Project' => $task['project_title'] ?? 'No Project',
                                'Priority' => $task['priority'] ?? 'Normal',
                                'Due Date' => $task['due_date'] ? date('M j, Y', strtotime($task['due_date'])) : 'No due date'
                            ]
                        ];
                    }
                }
            } catch (Exception $e) {
                error_log('Task search error: ' . $e->getMessage());
            }
        }
        
        // Search Users (with permission checks)
        if (($type === 'all' || $type === 'users') && $this->canSearchEntity('users')) {
            try {
                $users = $this->userModel->searchUsers($searchQuery, $limit);
                foreach ($users as $user) {
                    // Check if user can view this specific user
                    if ($this->canViewItem($user, 'users')) {
                        $results[] = [
                            'type' => 'user',
                            'id' => $user['id'],
                            'title' => $user['name'] ?? $user['username'],
                            'description' => $user['email'] ?? '',
                            'url' => '/users/profile/' . $user['id'],
                            'icon' => 'bi bi-person-circle',
                            'status' => $user['is_active'] ? 'Active' : 'Inactive',
                            'meta' => [
                                'Role' => $user['role'] ?? 'User',
                                'Email' => $user['email'] ?? ''
                            ]
                        ];
                    }
                }
            } catch (Exception $e) {
                error_log('User search error: ' . $e->getMessage());
            }
        }
        
        // Search Clients (with permission checks)
        if (($type === 'all' || $type === 'clients') && $this->clientModel && $this->canSearchEntity('clients')) {
            try {
                $clients = $this->clientModel->searchClients($searchQuery, $limit);
                foreach ($clients as $client) {
                    // Check if user can view this specific client
                    if ($this->canViewItem($client, 'clients')) {
                        $results[] = [
                            'type' => 'client',
                            'id' => $client['id'],
                            'title' => $client['name'],
                            'description' => $client['email'] ?? '',
                            'url' => '/clients/view/' . $client['id'],
                            'icon' => 'bi bi-building',
                            'status' => 'Active',
                            'meta' => [
                                'Contact' => $client['contact_person'] ?? 'N/A',
                                'Phone' => $client['phone'] ?? 'N/A'
                            ]
                        ];
                    }
                }
            } catch (Exception $e) {
                error_log('Client search error: ' . $e->getMessage());
            }
        }
        
        // Search Notes (with permission checks)
        if (($type === 'all' || $type === 'notes') && $this->noteModel && $this->canSearchEntity('notes')) {
            try {
                $userId = $_SESSION['user_id'];
                // Only admins and managers can see all notes
                // Regular users can ONLY see their own notes
                $hasFullNotesAccess = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager']);
                
                // Use secure search method that filters at database level
                $notes = $this->noteModel->searchNotesSecure($searchQuery, $userId, $hasFullNotesAccess, $limit);
                
                foreach ($notes as $note) {
                    // For now, determine basic access level
                    if ($note['created_by'] == $userId) {
                        $note['access_level'] = 'owner';
                    } else {
                        $note['access_level'] = 'viewer'; // Assume shared if it appears in results
                    }
                    
                    // Double-check permission (defense in depth)
                    if ($this->canViewItem($note, 'notes')) {
                        $results[] = [
                            'type' => 'note',
                            'id' => $note['id'],
                            'title' => $note['title'],
                            'description' => $this->truncateText($note['content'] ?? '', 100),
                            'url' => '/notes/show/' . $note['id'],
                            'icon' => 'bi bi-journal-text',
                            'status' => 'Note',
                            'meta' => [
                                'Created' => date('M j, Y', strtotime($note['created_at'] ?? 'now')),
                                'Author' => $note['author_name'] ?? 'Unknown'
                            ]
                        ];
                    }
                }
            } catch (Exception $e) {
                error_log('Note search error: ' . $e->getMessage());
            }
        }
        
        // Sort results by relevance (title matches first, then description matches)
        usort($results, function($a, $b) use ($query) {
            $queryLower = strtolower($query);
            
            // Check if query appears in title
            $aTitleMatch = stripos($a['title'], $queryLower) !== false;
            $bTitleMatch = stripos($b['title'], $queryLower) !== false;
            
            if ($aTitleMatch && !$bTitleMatch) return -1;
            if (!$aTitleMatch && $bTitleMatch) return 1;
            
            // If both or neither match title, sort by type priority
            $typePriority = ['project' => 1, 'task' => 2, 'user' => 3, 'client' => 4, 'note' => 5];
            return ($typePriority[$a['type']] ?? 10) - ($typePriority[$b['type']] ?? 10);
        });
        
        return array_slice($results, 0, $limit);
    }
    
    /**
     * Get search suggestions for autocomplete
     */
    private function getSearchSuggestions($query) {
        $suggestions = [];
        $searchQuery = '%' . $query . '%';
        
        try {
            // Get project suggestions
            $projects = $this->projectModel->searchProjects($searchQuery, 5);
            foreach ($projects as $project) {
                $suggestions[] = [
                    'text' => $project['title'],
                    'type' => 'project',
                    'url' => '/projects/viewProject/' . $project['id']
                ];
            }
            
            // Get task suggestions
            $tasks = $this->taskModel->searchTasks($searchQuery, 5);
            foreach ($tasks as $task) {
                $suggestions[] = [
                    'text' => $task['title'],
                    'type' => 'task',
                    'url' => '/tasks/show/' . $task['id']
                ];
            }
            
            // Get user suggestions
            $users = $this->userModel->searchUsers($searchQuery, 3);
            foreach ($users as $user) {
                $suggestions[] = [
                    'text' => $user['name'] ?? $user['username'],
                    'type' => 'user',
                    'url' => '/users/profile/' . $user['id']
                ];
            }
            
        } catch (Exception $e) {
            error_log('Search suggestions error: ' . $e->getMessage());
        }
        
        return array_slice($suggestions, 0, 10);
    }
    
    /**
     * Truncate text to specified length
     */
    private function truncateText($text, $length = 100) {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . '...';
    }
} 