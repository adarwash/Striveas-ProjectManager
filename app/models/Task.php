<?php
class Task {
    private $db;
    
    public function __construct() {
        // Initialize database connection
        $this->db = new EasySQL(DB1);
    }
    
    // Get all tasks
    public function getAllTasks() {
        $query = "SELECT t.*, p.title as project_title, u1.username as assigned_to_name, u2.username as created_by_name
                 FROM tasks t
                 LEFT JOIN projects p ON t.project_id = p.id
                 LEFT JOIN users u1 ON t.assigned_to = u1.id
                 LEFT JOIN users u2 ON t.created_by = u2.id
                 ORDER BY t.due_date ASC, t.priority DESC";
        
        $results = $this->db->select($query);
        
        // Convert arrays to objects
        $tasks = [];
        if (!empty($results)) {
            foreach ($results as $result) {
                $tasks[] = (object)$result;
            }
        }
        
        return $tasks;
    }
    
    // Get tasks by project ID
    public function getTasksByProject($projectId) {
        $query = "SELECT t.*, p.title as project_title, u1.username as assigned_to_name, u2.username as created_by_name
                 FROM tasks t
                 LEFT JOIN projects p ON t.project_id = p.id
                 LEFT JOIN users u1 ON t.assigned_to = u1.id
                 LEFT JOIN users u2 ON t.created_by = u2.id
                 WHERE t.project_id = ?
                 ORDER BY t.due_date ASC, t.priority DESC";
        
        $results = $this->db->select($query, [$projectId]);
        
        // Convert arrays to objects
        $tasks = [];
        if (!empty($results)) {
            foreach ($results as $result) {
                $tasks[] = (object)$result;
            }
        }
        
        return $tasks;
    }
    
    // Get task by ID
    public function getTaskById($id) {
        $query = "SELECT t.*, p.title as project_title, u1.username as assigned_to_name, u2.username as created_by_name
                 FROM tasks t
                 LEFT JOIN projects p ON t.project_id = p.id
                 LEFT JOIN users u1 ON t.assigned_to = u1.id
                 LEFT JOIN users u2 ON t.created_by = u2.id
                 WHERE t.id = ?";
        
        $result = $this->db->select($query, [$id]);
        return !empty($result) ? (object)$result[0] : null;
    }
    
    // Create new task
    public function create($data) {
        $query = "INSERT INTO tasks (project_id, title, description, status, priority, due_date, assigned_to, created_by, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, GETDATE())";
        
        return $this->db->insert($query, [
            $data['project_id'],
            $data['title'],
            $data['description'],
            $data['status'],
            $data['priority'],
            $data['due_date'],
            $data['assigned_to'],
            $data['created_by']
        ]);
    }
    
    // Update task
    public function update($data) {
        $query = "UPDATE tasks 
                 SET project_id = ?, title = ?, description = ?, status = ?, priority = ?, due_date = ?, assigned_to = ?, updated_at = GETDATE()
                 WHERE id = ?";
        
        return $this->db->update($query, [
            $data['project_id'],
            $data['title'],
            $data['description'],
            $data['status'],
            $data['priority'],
            $data['due_date'],
            $data['assigned_to'],
            $data['id']
        ]);
    }
    
    // Delete task
    public function delete($id) {
        $query = "DELETE FROM tasks WHERE id = ?";
        
        return $this->db->remove($query, [$id]);
    }
    
    // Get task statistics
    public function getTaskStats() {
        $query = "SELECT 
                 SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
                 SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_count,
                 SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_count,
                 SUM(CASE WHEN priority = 'High' OR priority = 'Critical' THEN 1 ELSE 0 END) as high_priority_count,
                 COUNT(*) as total_count
                 FROM tasks";
        
        $result = $this->db->select($query);
        return !empty($result) ? (object)$result[0] : (object)[
            'pending_count' => 0, 
            'in_progress_count' => 0, 
            'completed_count' => 0,
            'high_priority_count' => 0,
            'total_count' => 0
        ];
    }
    
    // Get tasks assigned to specific user
    public function getTasksByUser($userId) {
        $query = "SELECT t.*, p.title as project_title, u1.username as assigned_to_name, u2.username as created_by_name
                 FROM tasks t
                 LEFT JOIN projects p ON t.project_id = p.id
                 LEFT JOIN users u1 ON t.assigned_to = u1.id
                 LEFT JOIN users u2 ON t.created_by = u2.id
                 WHERE t.assigned_to = ?
                 ORDER BY t.due_date ASC, t.priority DESC";
        
        $results = $this->db->select($query, [$userId]);
        
        // Convert arrays to objects
        $tasks = [];
        if (!empty($results)) {
            foreach ($results as $result) {
                $tasks[] = (object)$result;
            }
        }
        
        return $tasks;
    }
    
    // Get recent activity (last 10 task updates)
    public function getRecentActivity() {
        $query = "SELECT TOP 10 t.id, t.title, t.status, 
                 COALESCE(t.updated_at, t.created_at, GETDATE()) as updated_at, 
                 p.id as project_id, p.title as project_title
                 FROM tasks t
                 LEFT JOIN projects p ON t.project_id = p.id
                 ORDER BY t.updated_at DESC, t.created_at DESC";
        
        $results = $this->db->select($query);
        
        // Convert arrays to objects
        $activityItems = [];
        if (!empty($results)) {
            foreach ($results as $result) {
                $activityItems[] = (object)$result;
            }
        }
        
        return $activityItems;
    }

    /**
     * Get all tasks with project details
     */
    public function getAllTasksWithProjects($userId = null) {
        $query = "SELECT t.*, p.title as project_title, p.id as project_id, u.username as created_by
                  FROM tasks t
                  LEFT JOIN projects p ON t.project_id = p.id
                  LEFT JOIN users u ON t.created_by = u.id";
        
        $params = [];
        
        if($userId) {
            $query .= " WHERE t.created_by = ? OR t.assigned_to = (SELECT username FROM users WHERE id = ?)";
            $params = [$userId, $userId];
        }
        
        $query .= " ORDER BY t.due_date ASC, t.created_at DESC";
        
        $results = $this->db->select($query, $params);
        
        // Convert arrays to objects
        $tasks = [];
        if (!empty($results)) {
            foreach ($results as $result) {
                $tasks[] = (object)$result;
            }
        }
        
        return $tasks;
    }

    // Get tasks statistics by user
    public function getTasksStatsByUser($userId) {
        $query = "SELECT 
                  COUNT(*) as total,
                  SUM(CASE WHEN status = 'Not Started' THEN 1 ELSE 0 END) as not_started,
                  SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
                  SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                  SUM(CASE WHEN status = 'Blocked' THEN 1 ELSE 0 END) as blocked,
                  SUM(CASE WHEN due_date < GETDATE() AND status != 'Completed' THEN 1 ELSE 0 END) as overdue
                  FROM tasks
                  WHERE created_by = ? OR assigned_to = ?";
        
        $results = $this->db->select($query, [$userId, $userId]);
        
        if (empty($results)) {
            return [
                'total' => 0,
                'not_started' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'blocked' => 0,
                'overdue' => 0
            ];
        }
        
        return $results[0];
    }

    /**
     * Create the task_users table if it doesn't exist 
     * (for MS SQL Server)
     * 
     * @return bool True if successful, false otherwise
     */
    public function createTaskUsersTable() {
        $sql = "
        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='task_users' AND xtype='U')
        BEGIN
            CREATE TABLE task_users (
                id INT IDENTITY(1,1) PRIMARY KEY,
                task_id INT NOT NULL,
                user_id INT NOT NULL,
                created_at DATETIME DEFAULT GETDATE(),
                CONSTRAINT fk_task_users_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
                CONSTRAINT fk_task_users_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT uk_task_user UNIQUE (task_id, user_id)
            )
        END";
        
        try {
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log('Error creating task_users table: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get assigned users for a task
     * 
     * @param int $taskId Task ID
     * @return array Array of assigned users
     */
    public function getTaskUsers($taskId) {
        $sql = "SELECT u.id as user_id, u.name, u.email, u.username 
                FROM users u
                JOIN task_users tu ON u.id = tu.user_id
                WHERE tu.task_id = ?";
                
        try {
            $results = $this->db->select($sql, [$taskId]);
            
            // Convert arrays to objects
            $users = [];
            if (!empty($results)) {
                foreach ($results as $result) {
                    $users[] = (object)$result;
                }
            }
            
            return $users;
        } catch (Exception $e) {
            error_log('Error in getTaskUsers: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Assign a user to a task
     * 
     * @param int $taskId Task ID
     * @param int $userId User ID
     * @return bool True if assigned, false otherwise
     */
    public function assignUserToTask($taskId, $userId) {
        // First check if the user is already assigned
        if ($this->isUserAssignedToTask($taskId, $userId)) {
            return true; // Already assigned
        }
        
        // Insert new assignment
        $sql = "INSERT INTO task_users (task_id, user_id) VALUES (?, ?)";
        
        try {
            $this->db->insert($sql, [$taskId, $userId]);
            return true;
        } catch (Exception $e) {
            error_log('Error in assignUserToTask: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove a user from a task
     * 
     * @param int $taskId Task ID
     * @param int $userId User ID
     * @return bool True if removed, false otherwise
     */
    public function removeUserFromTask($taskId, $userId) {
        $sql = "DELETE FROM task_users WHERE task_id = ? AND user_id = ?";
        
        try {
            $this->db->remove($sql, [$taskId, $userId]);
            return true;
        } catch (Exception $e) {
            error_log('Error in removeUserFromTask: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a user is assigned to a task
     * 
     * @param int $taskId Task ID
     * @param int $userId User ID
     * @return bool True if assigned, false otherwise
     */
    public function isUserAssignedToTask($taskId, $userId) {
        $sql = "SELECT COUNT(*) as count 
                FROM task_users 
                WHERE task_id = ? AND user_id = ?";
                
        try {
            $results = $this->db->select($sql, [$taskId, $userId]);
            
            if (!empty($results) && isset($results[0]['count'])) {
                return (int)$results[0]['count'] > 0;
            }
            
            return false;
        } catch (Exception $e) {
            error_log('Error in isUserAssignedToTask: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all tasks assigned to a user
     * 
     * @param int $userId User ID
     * @return array Array of tasks
     */
    public function getTasksByUserId($userId) {
        $sql = "SELECT t.*, p.title as project_title
                FROM tasks t
                JOIN task_users tu ON t.id = tu.task_id
                JOIN projects p ON t.project_id = p.id
                WHERE tu.user_id = ?
                ORDER BY t.due_date ASC";
                
        try {
            $results = $this->db->select($sql, [$userId]);
            
            // Convert arrays to objects
            $tasks = [];
            if (!empty($results)) {
                foreach ($results as $result) {
                    $tasks[] = (object)$result;
                }
            }
            
            return $tasks;
        } catch (Exception $e) {
            error_log('Error in getTasksByUserId: ' . $e->getMessage());
            return [];
        }
    }

    // Get tasks by filters (project_id, status, priority)
    public function getTasksByFilters($filters = []) {
        $query = "SELECT t.*, p.title as project_title, u1.username as assigned_to_name, u2.username as created_by_name
                 FROM tasks t
                 LEFT JOIN projects p ON t.project_id = p.id
                 LEFT JOIN users u1 ON t.assigned_to = u1.id
                 LEFT JOIN users u2 ON t.created_by = u2.id
                 WHERE 1=1"; // Always true condition to simplify adding filters
        
        $params = [];
        
        // Add project filter if provided
        if (!empty($filters['project_id'])) {
            $query .= " AND t.project_id = ?";
            $params[] = $filters['project_id'];
        }
        
        // Add status filter if provided
        if (!empty($filters['status'])) {
            $query .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        
        // Add priority filter if provided
        if (!empty($filters['priority'])) {
            $query .= " AND t.priority = ?";
            $params[] = $filters['priority'];
        }
        
        $query .= " ORDER BY t.due_date ASC, t.priority DESC";
        
        try {
            $results = $this->db->select($query, $params);
            
            // Convert arrays to objects
            $tasks = [];
            if (!empty($results)) {
                foreach ($results as $result) {
                    $tasks[] = (object)$result;
                }
            }
            
            return $tasks;
        } catch (Exception $e) {
            error_log('Error in getTasksByFilters: ' . $e->getMessage());
            // Return empty array on error to avoid breaking the application
            return [];
        }
    }

    /**
     * Get counts of tasks by status
     * 
     * @return array Array with counts of tasks by status
     */
    public function getTaskCountsByStatus() {
        $statuses = ['Pending', 'In Progress', 'Testing', 'Completed', 'Blocked'];
        $result = [];
        
        try {
            // Query for each status separately to handle empty counts properly
            foreach ($statuses as $status) {
                $query = "SELECT COUNT(*) as count FROM tasks WHERE status = ?";
                $data = $this->db->select($query, [$status]);
                $result[$status] = !empty($data) ? (int)$data[0]['count'] : 0;
            }
            
            // Add total count
            $query = "SELECT COUNT(*) as count FROM tasks";
            $data = $this->db->select($query);
            $result['Total'] = !empty($data) ? (int)$data[0]['count'] : 0;
            
            return $result;
        } catch (Exception $e) {
            error_log('Error in getTaskCountsByStatus: ' . $e->getMessage());
            return [
                'Pending' => 0,
                'In Progress' => 0,
                'Testing' => 0,
                'Completed' => 0,
                'Blocked' => 0,
                'Total' => 0
            ];
        }
    }

    // Get total number of tasks (for admin dashboard)
    public function getTotalTasks() {
        try {
            $result = $this->db->select("SELECT COUNT(*) as total FROM tasks");
            return $result[0]['total'] ?? 0;
        } catch (Exception $e) {
            error_log('GetTotalTasks Error: ' . $e->getMessage());
            return 0;
        }
    }

    // Get tasks stats by status (for admin dashboard)
    public function getTaskStatsByStatus() {
        try {
            return $this->db->select("SELECT status, COUNT(*) as count FROM tasks GROUP BY status");
        } catch (Exception $e) {
            error_log('GetTaskStatsByStatus Error: ' . $e->getMessage());
            return [];
        }
    }

    // Get tasks stats by priority (for admin dashboard)
    public function getTaskStatsByPriority() {
        try {
            return $this->db->select("SELECT priority, COUNT(*) as count FROM tasks GROUP BY priority");
        } catch (Exception $e) {
            error_log('GetTaskStatsByPriority Error: ' . $e->getMessage());
            return [];
        }
    }

    // Get recent tasks (for admin dashboard)
    public function getRecentTasks($limit = 5) {
        try {
            $query = "SELECT t.*, p.title as project_title, u.name as assigned_to_name 
                    FROM tasks t 
                    LEFT JOIN projects p ON t.project_id = p.id 
                    LEFT JOIN users u ON t.assigned_to = u.id 
                    ORDER BY t.created_at DESC 
                    LIMIT ?";
            return $this->db->select($query, [$limit]);
        } catch (Exception $e) {
            error_log('GetRecentTasks Error: ' . $e->getMessage());
            return [];
        }
    }
} 