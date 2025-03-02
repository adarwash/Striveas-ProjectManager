<?php
class Task {
    private $db;
    
    public function __construct() {
        $this->db = new Database;
        
        // Create tasks table if not exists
        $sql = file_get_contents('../app/sql/create_tasks_table.sql');
        $this->db->query($sql);
        $this->db->execute();
        
        // Create task assignments table if not exists
        $sql = file_get_contents('../app/sql/create_task_assignments_table.sql');
        $this->db->query($sql);
        $this->db->execute();
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

    public function getAssignedUsers($taskId)
    {
        $this->db->query('SELECT u.* FROM users u 
                          JOIN task_assignments ta ON u.id = ta.user_id 
                          WHERE ta.task_id = :task_id');
        $this->db->bind(':task_id', $taskId);
        return $this->db->resultSet();
    }
} 