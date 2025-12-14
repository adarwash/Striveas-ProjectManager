<?php
class Task {
    private $db;
    
    public function __construct() {
        // Initialize database connection
        $this->db = new EasySQL(DB1);
    }
    
    /**
     * Ensure optional columns exist
     */
    private function ensureOptionalColumns() {
        $columns = [
            'references_text' => "NVARCHAR(1024) NULL",
            'progress_percent' => "INT NOT NULL CONSTRAINT DF_tasks_progress DEFAULT 0",
            'start_date' => "DATE NULL",
            'tags' => "NVARCHAR(500) NULL",
            'estimated_hours' => "DECIMAL(8,2) NULL"
        ];
        foreach ($columns as $column => $definition) {
            $sql = "
            IF COL_LENGTH('dbo.tasks', '{$column}') IS NULL
            BEGIN
                ALTER TABLE [dbo].[tasks] ADD [{$column}] {$definition};
            END";
            try {
                $this->db->query($sql);
            } catch (Exception $e) {
                error_log("ensureColumn {$column} error: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Ensure progress column exists
     */
    private function ensureProgressColumn() {
        $sql = "
        IF COL_LENGTH('dbo.tasks', 'progress_percent') IS NULL
        BEGIN
            ALTER TABLE [dbo].[tasks] ADD [progress_percent] INT NOT NULL CONSTRAINT DF_tasks_progress DEFAULT 0;
        END";
        try {
            $this->db->query($sql);
        } catch (Exception $e) {
            error_log('ensureProgressColumn error: ' . $e->getMessage());
        }
    }
		
    /**
     * Ensure parent_task_id column exists for subtasks
     */
    private function ensureParentTaskColumn() {
        $sql = "
        IF COL_LENGTH('dbo.tasks', 'parent_task_id') IS NULL
        BEGIN
            ALTER TABLE [dbo].[tasks] ADD [parent_task_id] INT NULL;
            IF NOT EXISTS (
                SELECT 1 FROM sys.foreign_keys WHERE name = 'fk_tasks_parent'
            )
            BEGIN
                ALTER TABLE [dbo].[tasks] 
                ADD CONSTRAINT fk_tasks_parent FOREIGN KEY (parent_task_id) REFERENCES tasks(id) ON DELETE SET NULL;
            END
        END";
        try {
            $this->db->query($sql);
        } catch (Exception $e) {
            error_log('ensureParentTaskColumn error: ' . $e->getMessage());
        }
    }
    
    // Get all tasks (optionally excluding blocked client IDs)
    public function getAllTasks(array $blockedClientIds = []) {
        $query = "SELECT t.*, p.title as project_title, p.client_id, u1.username as assigned_to_name, u2.username as created_by_name
                 FROM tasks t
                 LEFT JOIN projects p ON t.project_id = p.id
                 LEFT JOIN users u1 ON t.assigned_to = u1.id
                 LEFT JOIN users u2 ON t.created_by = u2.id";

        $params = [];
        if (!empty($blockedClientIds)) {
            $placeholders = implode(',', array_fill(0, count($blockedClientIds), '?'));
            $query .= " WHERE (p.client_id IS NULL OR p.client_id NOT IN ($placeholders))";
            $params = array_map('intval', $blockedClientIds);
        }

        $query .= " ORDER BY t.due_date ASC, t.priority DESC";
        
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
    
    // Get tasks by project ID
    public function getTasksByProject($projectId, array $blockedClientIds = []) {
        $query = "SELECT t.*, p.title as project_title, p.client_id, u1.username as assigned_to_name, u2.username as created_by_name
                 FROM tasks t
                 LEFT JOIN projects p ON t.project_id = p.id
                 LEFT JOIN users u1 ON t.assigned_to = u1.id
                 LEFT JOIN users u2 ON t.created_by = u2.id
                 WHERE t.project_id = ?";

        $params = [$projectId];
        if (!empty($blockedClientIds)) {
            $placeholders = implode(',', array_fill(0, count($blockedClientIds), '?'));
            $query .= " AND (p.client_id IS NULL OR p.client_id NOT IN ($placeholders))";
            $params = array_merge($params, array_map('intval', $blockedClientIds));
        }

        $query .= " ORDER BY t.due_date ASC, t.priority DESC";
        
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
    
    // Get task by ID
    public function getTaskById($id) {
        $query = "SELECT t.*, p.title as project_title, p.client_id, u1.username as assigned_to_name, u2.username as created_by_name
                 FROM tasks t
                 LEFT JOIN projects p ON t.project_id = p.id
                 LEFT JOIN users u1 ON t.assigned_to = u1.id
                 LEFT JOIN users u2 ON t.created_by = u2.id
                 WHERE t.id = ?";
        
        $result = $this->db->select($query, [$id]);
        return !empty($result) ? (object)$result[0] : null;
    }
    
    /**
     * Search tasks by title, description, or project name
     */
    public function searchTasks($searchQuery, $limit = 10, array $blockedClientIds = []) {
        try {
            $query = "SELECT t.*, p.title as project_title, p.client_id, u1.username as assigned_to_name, u2.username as created_by_name
                     FROM [tasks] t
                     LEFT JOIN [projects] p ON t.project_id = p.id
                     LEFT JOIN [users] u1 ON t.assigned_to = u1.id
                     LEFT JOIN [users] u2 ON t.created_by = u2.id
                     WHERE (t.title LIKE ? OR t.description LIKE ? OR p.title LIKE ?)";
            
            $params = [
                $searchQuery, $searchQuery, $searchQuery,
                $searchQuery, $searchQuery, $searchQuery
            ];

            if (!empty($blockedClientIds)) {
                $placeholders = implode(',', array_fill(0, count($blockedClientIds), '?'));
                $query .= " AND (p.client_id IS NULL OR p.client_id NOT IN ($placeholders))";
                $params = array_merge($params, array_map('intval', $blockedClientIds));
            }

            $query .= " ORDER BY 
                         CASE 
                             WHEN t.title LIKE ? THEN 1
                             WHEN t.description LIKE ? THEN 2
                             WHEN p.title LIKE ? THEN 3
                             ELSE 4
                         END,
                         t.due_date ASC, t.priority DESC";
            
            // SQL Server uses TOP instead of LIMIT
            if ($limit > 0) {
                $query = preg_replace('/^SELECT\\s+/i', 'SELECT TOP ' . (int)$limit . ' ', $query);
            }
            
            $results = $this->db->select($query, $params);
            return $results ?: [];
        } catch (Exception $e) {
            error_log('Task search error: ' . $e->getMessage());
            return [];
        }
    }
    
    // Create new task
    public function create($data) {
        $this->ensureParentTaskColumn();
        $this->ensureOptionalColumns();
        $query = "INSERT INTO tasks (project_id, title, description, status, priority, start_date, due_date, assigned_to, created_by, created_at, parent_task_id, references_text, tags, estimated_hours, progress_percent) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE(), ?, ?, ?, ?, ?)";
        
        return $this->db->insert($query, [
            $data['project_id'],
            $data['title'],
            $data['description'],
            $data['status'],
            $data['priority'],
            $data['start_date'] ?? null,
            $data['due_date'],
            $data['assigned_to'],
            $data['created_by'],
            $data['parent_task_id'] ?? null,
            $data['references_text'] ?? null,
            $data['tags'] ?? null,
            isset($data['estimated_hours']) ? $data['estimated_hours'] : null,
            isset($data['progress_percent']) ? (int)$data['progress_percent'] : 0
        ]);
    }

    /**
     * Ensure task_sites table exists (MS SQL Server)
     */
    public function createTaskSitesTable() {
        $sql = "
        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='task_sites' AND xtype='U')
        BEGIN
            CREATE TABLE task_sites (
                id INT IDENTITY(1,1) PRIMARY KEY,
                task_id INT NOT NULL,
                site_id INT NOT NULL,
                created_at DATETIME DEFAULT GETDATE(),
                CONSTRAINT fk_task_sites_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
                CONSTRAINT fk_task_sites_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
                CONSTRAINT uk_task_site UNIQUE (task_id, site_id)
            )
        END";
        try {
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log('Error creating task_sites table: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Link a task to multiple sites
     *
     * @param int $taskId
     * @param array<int,int> $siteIds
     * @return bool
     */
    public function linkTaskToSites($taskId, $siteIds) {
        $this->createTaskSitesTable();
        if (empty($siteIds)) {
            return true;
        }
        $ok = true;
        foreach (array_unique(array_map('intval', (array)$siteIds)) as $siteId) {
            if ($siteId <= 0) continue;
            try {
                $this->db->insert("INSERT INTO task_sites (task_id, site_id) VALUES (?, ?)", [$taskId, $siteId]);
            } catch (Exception $e) {
                // Ignore duplicates or FK errors silently in this context, but record logs
                error_log('linkTaskToSites insert error: task=' . (int)$taskId . ' site=' . (int)$siteId . ' err=' . $e->getMessage());
                $ok = false; // mark if any failure
            }
        }
        return $ok;
    }

    /**
     * Get sites linked to a task
     *
     * @param int $taskId
     * @return array<int, array>
     */
    public function getTaskSites($taskId) {
        try {
            $this->createTaskSitesTable();
            $query = "SELECT s.* FROM sites s JOIN task_sites ts ON s.id = ts.site_id WHERE ts.task_id = ? ORDER BY s.name";
            return $this->db->select($query, [$taskId]) ?: [];
        } catch (Exception $e) {
            error_log('getTaskSites error: ' . $e->getMessage());
            return [];
        }
    }
    
    // Update task
    public function update($data) {
        $this->ensureParentTaskColumn();
        $this->ensureOptionalColumns();
        $query = "UPDATE tasks 
                 SET project_id = ?, title = ?, description = ?, status = ?, priority = ?, start_date = ?, due_date = ?, assigned_to = ?, tags = ?, estimated_hours = ?, parent_task_id = ?, references_text = ?, progress_percent = ?, updated_at = GETDATE()
                 WHERE id = ?";
        
        return $this->db->update($query, [
            $data['project_id'],
            $data['title'],
            $data['description'],
            $data['status'],
            $data['priority'],
            $data['start_date'] ?? null,
            $data['due_date'],
            $data['assigned_to'],
            $data['tags'] ?? null,
            isset($data['estimated_hours']) ? $data['estimated_hours'] : null,
            $data['parent_task_id'] ?? null,
            $data['references_text'] ?? null,
            isset($data['progress_percent']) ? (int)$data['progress_percent'] : 0,
            $data['id']
        ]);
    }
    
    /**
     * Update the status of a task
     * 
     * @param int $id Task ID
     * @param string $status New status
     * @return bool True if successful, false otherwise
     */
    public function updateStatus($id, $status) {
        $query = "UPDATE tasks SET status = ?, updated_at = GETDATE() WHERE id = ?";
        
        return $this->db->update($query, [$status, $id]);
    }
		
		/**
		 * Update the primary assignee (tasks.assigned_to)
		 */
		public function updateAssignedTo($taskId, $userIdOrNull) {
			$query = "UPDATE tasks SET assigned_to = ?, updated_at = GETDATE() WHERE id = ?";
			return $this->db->update($query, [$userIdOrNull, $taskId]);
		}
    
	public function updateProgress($taskId, $progress) {
        $this->ensureOptionalColumns();
		$progress = max(0, min(100, (int)$progress));
		$query = "UPDATE tasks SET progress_percent = ?, updated_at = GETDATE() WHERE id = ?";
		return $this->db->update($query, [$progress, $taskId]);
	}
	
    // Delete task
    public function delete($id) {
        $query = "DELETE FROM tasks WHERE id = ?";
        
        return $this->db->remove($query, [$id]);
    }
    
    // Get task statistics
    public function getTaskStats(array $blockedClientIds = []) {
        $params = [];
        $query = "SELECT 
                 SUM(CASE WHEN t.status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
                 SUM(CASE WHEN t.status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_count,
                 SUM(CASE WHEN t.status = 'Completed' THEN 1 ELSE 0 END) as completed_count,
                 SUM(CASE WHEN t.priority = 'High' OR t.priority = 'Critical' THEN 1 ELSE 0 END) as high_priority_count,
                 COUNT(*) as total_count
                 FROM tasks t";

        if (!empty($blockedClientIds)) {
            $placeholders = implode(',', array_fill(0, count($blockedClientIds), '?'));
            $query .= " LEFT JOIN projects p ON t.project_id = p.id
                        WHERE (p.client_id IS NULL OR p.client_id NOT IN ($placeholders))";
            $params = array_map('intval', $blockedClientIds);
        }
        
        $result = $this->db->select($query, $params);
        return !empty($result) ? (object)$result[0] : (object)[
            'pending_count' => 0, 
            'in_progress_count' => 0, 
            'completed_count' => 0,
            'high_priority_count' => 0,
            'total_count' => 0
        ];
    }
    
    // Get tasks assigned to specific user
    public function getTasksByUser($userId, array $blockedClientIds = []) {
        $query = "SELECT t.*, p.title as project_title, p.client_id, u1.username as assigned_to_name, u2.username as created_by_name
                 FROM tasks t
                 LEFT JOIN projects p ON t.project_id = p.id
                 LEFT JOIN users u1 ON t.assigned_to = u1.id
                 LEFT JOIN users u2 ON t.created_by = u2.id
                 WHERE t.assigned_to = ?";

        $params = [$userId];
        if (!empty($blockedClientIds)) {
            $placeholders = implode(',', array_fill(0, count($blockedClientIds), '?'));
            $query .= " AND (p.client_id IS NULL OR p.client_id NOT IN ($placeholders))";
            $params = array_merge($params, array_map('intval', $blockedClientIds));
        }

        $query .= " ORDER BY t.due_date ASC, t.priority DESC";
        
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
    
    // Get recent activity (last 10 task updates)
    public function getRecentActivity(array $blockedClientIds = []) {
        $query = "SELECT TOP 10 t.id, t.title, t.status, 
                 COALESCE(t.updated_at, t.created_at, GETDATE()) as updated_at, 
                 p.id as project_id, p.title as project_title, p.client_id
                 FROM tasks t
                 LEFT JOIN projects p ON t.project_id = p.id";

        $params = [];
        if (!empty($blockedClientIds)) {
            $placeholders = implode(',', array_fill(0, count($blockedClientIds), '?'));
            $query .= " WHERE (p.client_id IS NULL OR p.client_id NOT IN ($placeholders))";
            $params = array_map('intval', $blockedClientIds);
        }

        $query .= " ORDER BY t.updated_at DESC, t.created_at DESC";
        
        $results = $this->db->select($query, $params);
        
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
    public function getAllTasksWithProjects($userId = null, array $blockedClientIds = []) {
        $query = "SELECT t.*, p.title as project_title, p.id as project_id, p.client_id,
                  u1.username as created_by, u2.username as assigned_to_name
                  FROM tasks t
                  LEFT JOIN projects p ON t.project_id = p.id
                  LEFT JOIN users u1 ON t.created_by = u1.id
                  LEFT JOIN users u2 ON t.assigned_to = u2.id";
        
        $params = [];
        
        if($userId) {
            $query .= " WHERE t.created_by = ? OR t.assigned_to = ?";
            $params = [$userId, $userId];
        }

        if (!empty($blockedClientIds)) {
            $placeholders = implode(',', array_fill(0, count($blockedClientIds), '?'));
            $query .= $userId ? " AND" : " WHERE";
            $query .= " (p.client_id IS NULL OR p.client_id NOT IN ($placeholders))";
            $params = array_merge($params, array_map('intval', $blockedClientIds));
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
    public function getTasksStatsByUser($userId, array $blockedClientIds = []) {
        $query = "SELECT 
                  COUNT(*) as total,
                  SUM(CASE WHEN t.status = 'Not Started' THEN 1 ELSE 0 END) as not_started,
                  SUM(CASE WHEN t.status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
                  SUM(CASE WHEN t.status = 'Completed' THEN 1 ELSE 0 END) as completed,
                  SUM(CASE WHEN t.status = 'Blocked' THEN 1 ELSE 0 END) as blocked,
                  SUM(CASE WHEN t.due_date < GETDATE() AND t.status != 'Completed' THEN 1 ELSE 0 END) as overdue
                  FROM tasks t
                  LEFT JOIN projects p ON t.project_id = p.id
                  WHERE (t.created_by = ? OR t.assigned_to = ?)";

        $params = [$userId, $userId];
        if (!empty($blockedClientIds)) {
            $placeholders = implode(',', array_fill(0, count($blockedClientIds), '?'));
            $query .= " AND (p.client_id IS NULL OR p.client_id NOT IN ($placeholders))";
            $params = array_merge($params, array_map('intval', $blockedClientIds));
        }
        
        $results = $this->db->select($query, $params);
        
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
		$sql = "SELECT u.id as user_id, 
		                COALESCE(u.full_name, u.username) AS name, 
		                COALESCE(u.email, u.username) AS email, 
		                u.username 
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
		$this->createTaskUsersTable();
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
		$this->createTaskUsersTable();
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
    public function getTasksByUserId($userId, array $blockedClientIds = []) {
        $sql = "SELECT t.*, p.title as project_title, p.client_id
                FROM tasks t
                JOIN task_users tu ON t.id = tu.task_id
                JOIN projects p ON t.project_id = p.id
                WHERE tu.user_id = ?";

        $params = [$userId];
        if (!empty($blockedClientIds)) {
            $placeholders = implode(',', array_fill(0, count($blockedClientIds), '?'));
            $sql .= " AND (p.client_id IS NULL OR p.client_id NOT IN ($placeholders))";
            $params = array_merge($params, array_map('intval', $blockedClientIds));
        }

        $sql .= " ORDER BY t.due_date ASC";
                
        try {
            $results = $this->db->select($sql, $params);
            
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
    public function getTasksByFilters($filters = [], array $blockedClientIds = []) {
        $query = "SELECT t.*, p.title as project_title, p.client_id, u1.username as assigned_to_name, u2.username as created_by_name
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

        // Exclude blocked client tasks
        if (!empty($blockedClientIds)) {
            $placeholders = implode(',', array_fill(0, count($blockedClientIds), '?'));
            $query .= " AND (p.client_id IS NULL OR p.client_id NOT IN ($placeholders))";
            $params = array_merge($params, array_map('intval', $blockedClientIds));
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
    public function getTaskCountsByStatus(array $blockedClientIds = []) {
        $statuses = ['Pending', 'In Progress', 'Testing', 'Completed', 'Blocked'];
        $result = [];
        
        try {
            // Query for each status separately to handle empty counts properly
            foreach ($statuses as $status) {
                $params = [$status];
                if (!empty($blockedClientIds)) {
                    $placeholders = implode(',', array_fill(0, count($blockedClientIds), '?'));
                    $query = "SELECT COUNT(*) as count FROM tasks t
                              LEFT JOIN projects p ON t.project_id = p.id
                              WHERE t.status = ? AND (p.client_id IS NULL OR p.client_id NOT IN ($placeholders))";
                    $params = array_merge($params, array_map('intval', $blockedClientIds));
                } else {
                    $query = "SELECT COUNT(*) as count FROM tasks WHERE status = ?";
                }

                $data = $this->db->select($query, $params);
                $result[$status] = !empty($data) ? (int)$data[0]['count'] : 0;
            }
            
            // Add total count
            if (!empty($blockedClientIds)) {
                $placeholders = implode(',', array_fill(0, count($blockedClientIds), '?'));
                $query = "SELECT COUNT(*) as count FROM tasks t
                          LEFT JOIN projects p ON t.project_id = p.id
                          WHERE (p.client_id IS NULL OR p.client_id NOT IN ($placeholders))";
                $data = $this->db->select($query, array_map('intval', $blockedClientIds));
            } else {
                $query = "SELECT COUNT(*) as count FROM tasks";
                $data = $this->db->select($query);
            }
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
    
    // Get count of open tasks
    public function getOpenTasksCount(array $blockedClientIds = []) {
        try {
            if (!empty($blockedClientIds)) {
                $placeholders = implode(',', array_fill(0, count($blockedClientIds), '?'));
                $query = "SELECT COUNT(*) as total FROM tasks t
                          LEFT JOIN projects p ON t.project_id = p.id
                          WHERE t.status IN ('Pending', 'In Progress', 'Not Started')
                          AND (p.client_id IS NULL OR p.client_id NOT IN ($placeholders))";
                $result = $this->db->select($query, array_map('intval', $blockedClientIds));
            } else {
                $result = $this->db->select("SELECT COUNT(*) as total FROM tasks WHERE status IN ('Pending', 'In Progress', 'Not Started')");
            }
            return $result[0]['total'] ?? 0;
        } catch (Exception $e) {
            error_log('GetOpenTasksCount Error: ' . $e->getMessage());
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
    
    /**
     * Get recent task activity for a specific project
     * 
     * @param int $projectId The project ID
     * @param int $limit The maximum number of activities to return
     * @return array Array of recent task activities
     */
    public function getRecentTaskActivityByProject($projectId, $limit = 5) {
        try {
            // For MS SQL Server, use TOP instead of LIMIT
            $query = "SELECT TOP $limit t.id, t.title, t.status, t.assigned_to, 
                     u1.username as assigned_to_name, u2.username as created_by_name,
                     t.created_at, t.updated_at,
                     CASE 
                         WHEN t.updated_at IS NOT NULL AND t.updated_at <> t.created_at THEN 'updated'
                         ELSE 'created'
                     END as activity_type
                     FROM tasks t
                     LEFT JOIN users u1 ON t.assigned_to = u1.id
                     LEFT JOIN users u2 ON t.created_by = u2.id
                     WHERE t.project_id = ?
                     ORDER BY 
                         CASE 
                             WHEN t.updated_at IS NOT NULL AND t.updated_at <> t.created_at THEN t.updated_at
                             ELSE t.created_at
                         END DESC";
            
            $results = $this->db->select($query, [$projectId]);
            
            // Convert arrays to objects for consistency
            $activities = [];
            if (!empty($results)) {
                foreach ($results as $result) {
                    $activities[] = (object)$result;
                }
            }
            
            return $activities;
        } catch (Exception $e) {
            error_log('GetRecentTaskActivityByProject Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get subtasks for a given task
     *
     * @param int $taskId
     * @return array
     */
    public function getSubtasks($taskId) {
        try {
            $this->ensureParentTaskColumn();
            $query = "SELECT t.*, u.username as assigned_to_name
                      FROM tasks t
                      LEFT JOIN users u ON t.assigned_to = u.id
                      WHERE t.parent_task_id = ?
                      ORDER BY t.due_date ASC, t.priority DESC, t.created_at ASC";
            $rows = $this->db->select($query, [$taskId]) ?: [];
            $subtasks = [];
            foreach ($rows as $r) {
                $subtasks[] = (object)$r;
            }
            return $subtasks;
        } catch (Exception $e) {
            error_log('getSubtasks error: ' . $e->getMessage());
            return [];
        }
    }
} 