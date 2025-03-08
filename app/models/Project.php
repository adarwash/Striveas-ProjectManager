<?php
class Project {
    private $db;
    
    public function __construct() {
        // Initialize database connection
        $this->db = new EasySQL(DB1);
    }
    
    // Get all projects
    public function getAllProjects() {
        $query = "SELECT p.*, u.username as created_by, d.name as department_name,
                 (SELECT COUNT(*) FROM tasks WHERE project_id = p.id) as task_count,
                 (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND status = 'Completed') as completed_tasks
                 FROM projects p
                 LEFT JOIN users u ON p.user_id = u.id
                 LEFT JOIN departments d ON p.department_id = d.id
                 ORDER BY p.created_at DESC";
        
        $results = $this->db->select($query);
        
        // Convert arrays to objects
        $projects = [];
        if (!empty($results)) {
            foreach ($results as $result) {
                $projects[] = (object)$result;
            }
        }
        
        return $projects;
    }
    
    // Get projects by user ID
    public function getProjectsByUser($userId) {
        $query = "SELECT p.*, u.username as created_by, d.name as department_name,
                 (SELECT COUNT(*) FROM tasks WHERE project_id = p.id) as task_count,
                 (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND status = 'Completed') as completed_tasks
                 FROM projects p
                 LEFT JOIN users u ON p.user_id = u.id
                 LEFT JOIN departments d ON p.department_id = d.id
                 WHERE p.user_id = ?
                 ORDER BY p.created_at DESC";
        
        $results = $this->db->select($query, [$userId]);
        
        // Convert arrays to objects
        $projects = [];
        if (!empty($results)) {
            foreach ($results as $result) {
                $projects[] = (object)$result;
            }
        }
        
        return $projects;
    }
    
    // Get project by ID
    public function getProjectById($id) {
        $query = "SELECT p.*, u.username as created_by, d.name as department_name, d.budget as department_budget
                 FROM projects p
                 LEFT JOIN users u ON p.user_id = u.id
                 LEFT JOIN departments d ON p.department_id = d.id
                 WHERE p.id = ?";
        
        $result = $this->db->select($query, [$id]);
        return !empty($result) ? (object)$result[0] : null;
    }
    
    // Create new project
    public function create($data) {
        $query = "INSERT INTO projects (title, description, start_date, end_date, status, user_id, department_id, budget, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, GETDATE())";
        
        return $this->db->insert($query, [
            $data['title'],
            $data['description'],
            $data['start_date'],
            $data['end_date'],
            $data['status'],
            $data['user_id'],
            $data['department_id'],
            $data['budget']
        ]);
    }
    
    // Update project
    public function update($data) {
        $query = "UPDATE projects 
                 SET title = ?, description = ?, start_date = ?, end_date = ?, status = ?, 
                 department_id = ?, budget = ?, updated_at = GETDATE()
                 WHERE id = ?";
        
        return $this->db->update($query, [
            $data['title'],
            $data['description'],
            $data['start_date'],
            $data['end_date'],
            $data['status'],
            $data['department_id'],
            $data['budget'],
            $data['id']
        ]);
    }
    
    // Delete project
    public function delete($id) {
        $query = "DELETE FROM projects WHERE id = ?";
        
        return $this->db->remove($query, [$id]);
    }
    
    // Get tasks associated with a project
    public function getProjectTasks($projectId) {
        $query = "SELECT t.*, u.username as assigned_to
                 FROM tasks t
                 LEFT JOIN users u ON t.assigned_to = u.id
                 WHERE t.project_id = ?
                 ORDER BY t.due_date ASC";
        
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
    
    // Get project summary statistics
    public function getProjectStats() {
        $query = "SELECT 
                 SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_count,
                 SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_count,
                 SUM(CASE WHEN status = 'On Hold' THEN 1 ELSE 0 END) as on_hold_count,
                 SUM(budget) as total_budget
                 FROM projects";
        
        $result = $this->db->select($query);
        return !empty($result) ? (object)$result[0] : (object)[
            'active_count' => 0, 
            'completed_count' => 0, 
            'on_hold_count' => 0,
            'total_budget' => 0
        ];
    }
    
    // Get department budget usage
    public function getDepartmentBudgetUsage() {
        $query = "SELECT d.id, d.name, d.budget as total_budget,
                 SUM(ISNULL(p.budget, 0)) as used_budget,
                 (SUM(ISNULL(p.budget, 0)) / NULLIF(d.budget, 0) * 100) as percentage
                 FROM departments d
                 LEFT JOIN projects p ON d.id = p.department_id
                 GROUP BY d.id, d.name, d.budget
                 ORDER BY d.name";
        
        $results = $this->db->select($query);
        
        // Convert arrays to objects
        $budgetUsage = [];
        if (!empty($results)) {
            foreach ($results as $result) {
                $budgetUsage[] = (object)$result;
            }
        }
        
        return $budgetUsage;
    }
    
    /**
     * Get all projects with their tasks
     */
    public function getProjectsWithTasks($userId = null) {
        // First get the projects
        $query = "SELECT p.*, u.username as created_by, d.name as department_name, d.budget as department_budget
                  FROM projects p
                  LEFT JOIN users u ON p.user_id = u.id
                  LEFT JOIN departments d ON p.department_id = d.id";
        
        $params = [];
        
        if($userId) {
            $query .= " WHERE p.user_id = ?";
            $params = [$userId];
        }
        
        $query .= " ORDER BY p.end_date ASC, p.start_date ASC";
        
        $results = $this->db->select($query, $params);
        
        // Convert arrays to objects
        $projects = [];
        if (!empty($results)) {
            foreach ($results as $result) {
                $project = (object)$result;
                
                // Get tasks for this project
                $query = "SELECT t.*, u.username as created_by_name, u2.username as assigned_to_name
                         FROM tasks t
                         LEFT JOIN users u ON t.created_by = u.id
                         LEFT JOIN users u2 ON t.assigned_to = u2.id
                         WHERE t.project_id = ?
                         ORDER BY t.due_date ASC, t.created_at ASC";
                
                $taskResults = $this->db->select($query, [$project->id]);
                
                // Convert task arrays to objects
                $tasks = [];
                if (!empty($taskResults)) {
                    foreach ($taskResults as $taskResult) {
                        $tasks[] = (object)$taskResult;
                    }
                }
                
                // Add tasks to project
                $project->tasks = $tasks;
                
                $projects[] = $project;
            }
        }
        
        return $projects;
    }
    
    // Get projects count by user
    public function getProjectsCountByUser($userId) {
        $query = "SELECT 
                  COUNT(*) as total,
                  SUM(CASE WHEN status = 'Planning' THEN 1 ELSE 0 END) as planning,
                  SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
                  SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                  SUM(CASE WHEN status = 'On Hold' THEN 1 ELSE 0 END) as on_hold
                  FROM projects
                  WHERE user_id = ?";
        
        $results = $this->db->select($query, [$userId]);
        
        if (empty($results)) {
            return [
                'total' => 0,
                'planning' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'on_hold' => 0
            ];
        }
        
        return $results[0];
    }
    
    /**
     * Create the project_users table if it doesn't exist 
     * (for MS SQL Server)
     * 
     * @return bool True if successful, false otherwise
     */
    public function createProjectUsersTable() {
        $sql = "
        IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='project_users' AND xtype='U')
        BEGIN
            CREATE TABLE project_users (
                id INT IDENTITY(1,1) PRIMARY KEY,
                project_id INT NOT NULL,
                user_id INT NOT NULL,
                role NVARCHAR(50) DEFAULT 'Member',
                created_at DATETIME DEFAULT GETDATE(),
                CONSTRAINT fk_project_users_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
                CONSTRAINT fk_project_users_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT uk_project_user UNIQUE (project_id, user_id)
            )
        END";
        
        try {
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log('Error creating project_users table: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get counts of projects by status
     * 
     * @return array Array with counts of projects by status
     */
    public function getProjectCountsByStatus() {
        $statuses = ['Planning', 'In Progress', 'Completed', 'On Hold'];
        $result = [];
        
        try {
            // Query for each status separately to handle empty counts properly
            foreach ($statuses as $status) {
                $query = "SELECT COUNT(*) as count FROM projects WHERE status = ?";
                $data = $this->db->select($query, [$status]);
                $result[$status] = !empty($data) ? (int)$data[0]['count'] : 0;
            }
            
            // Add total count
            $query = "SELECT COUNT(*) as count FROM projects";
            $data = $this->db->select($query);
            $result['Total'] = !empty($data) ? (int)$data[0]['count'] : 0;
            
            return $result;
        } catch (Exception $e) {
            error_log('Error in getProjectCountsByStatus: ' . $e->getMessage());
            return [
                'Planning' => 0,
                'In Progress' => 0,
                'Completed' => 0,
                'On Hold' => 0,
                'Total' => 0
            ];
        }
    }
    
    /**
     * Get users assigned to a project
     * 
     * @param int $projectId Project ID
     * @return array Array of users assigned to the project
     */
    public function getProjectUsers($projectId) {
        $sql = "SELECT u.id, u.username, u.full_name as name, u.username as email, pu.role, pu.user_id 
                FROM users u
                JOIN project_users pu ON u.id = pu.user_id
                WHERE pu.project_id = ?";
                
        try {
            $results = $this->db->select($sql, [$projectId]);
            
            // Convert arrays to objects
            $users = [];
            if (!empty($results)) {
                foreach ($results as $result) {
                    $users[] = (object)$result;
                }
            }
            
            return $users;
        } catch (Exception $e) {
            error_log('Error in getProjectUsers: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Assign a user to a project
     * 
     * @param int $projectId Project ID
     * @param int $userId User ID
     * @param string $role User's role in the project
     * @return bool True if successful, false otherwise
     */
    public function assignUsersToProject($projectId, $userIds, $role = 'Member') {
        try {
            // Begin transaction to handle multiple inserts
            $this->db->beginTransaction();
            
            foreach ((array)$userIds as $userId) {
                // Check if user is already assigned
                if ($this->isUserAssignedToProject($projectId, $userId)) {
                    // Update the role if the user is already assigned
                    $this->updateUserRole($projectId, $userId, $role);
                    continue;
                }
                
                // Add new assignment
                $sql = "INSERT INTO project_users (project_id, user_id, role) VALUES (?, ?, ?)";
                $this->db->insert($sql, [$projectId, $userId, $role]);
            }
            
            // Commit transaction
            $this->db->commitTransaction();
            return true;
        } catch (Exception $e) {
            // Rollback on error
            if ($this->db->inTransaction()) {
                $this->db->rollbackTransaction();
            }
            error_log('Error assigning users to project: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove a user from a project
     * 
     * @param int $projectId Project ID
     * @param int $userId User ID
     * @return bool True if successful, false otherwise
     */
    public function removeUserFromProject($projectId, $userId) {
        try {
            $sql = "DELETE FROM project_users WHERE project_id = ? AND user_id = ?";
            $this->db->remove($sql, [$projectId, $userId]);
            return true;
        } catch (Exception $e) {
            error_log('Error removing user from project: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a user's role in a project
     * 
     * @param int $projectId Project ID
     * @param int $userId User ID
     * @param string $role New role
     * @return bool True if successful, false otherwise
     */
    public function updateUserRole($projectId, $userId, $role) {
        try {
            $sql = "UPDATE project_users SET role = ? WHERE project_id = ? AND user_id = ?";
            $this->db->update($sql, [$role, $projectId, $userId]);
            return true;
        } catch (Exception $e) {
            error_log('Error updating user role: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a user is assigned to a project
     * 
     * @param int $projectId Project ID
     * @param int $userId User ID
     * @return bool True if assigned, false otherwise
     */
    public function isUserAssignedToProject($projectId, $userId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM project_users WHERE project_id = ? AND user_id = ?";
            $results = $this->db->select($sql, [$projectId, $userId]);
            
            if (!empty($results) && isset($results[0]['count'])) {
                return (int)$results[0]['count'] > 0;
            }
            
            return false;
        } catch (Exception $e) {
            error_log('Error checking user assignment: ' . $e->getMessage());
            return false;
        }
    }

    // Get total number of projects (for admin dashboard)
    public function getTotalProjects() {
        try {
            $result = $this->db->select("SELECT COUNT(*) as total FROM projects");
            return $result[0]['total'] ?? 0;
        } catch (Exception $e) {
            error_log('GetTotalProjects Error: ' . $e->getMessage());
            return 0;
        }
    }

    // Get projects stats by status (for admin dashboard)
    public function getProjectStatsByStatus() {
        try {
            return $this->db->select("SELECT status, COUNT(*) as count FROM projects GROUP BY status");
        } catch (Exception $e) {
            error_log('GetProjectStatsByStatus Error: ' . $e->getMessage());
            return [];
        }
    }

    // Get recent projects (for admin dashboard)
    public function getRecentProjects($limit = 5) {
        try {
            $query = "SELECT p.*, u.name as owner_name 
                    FROM projects p 
                    LEFT JOIN users u ON p.user_id = u.id 
                    ORDER BY p.created_at DESC 
                    LIMIT ?";
            return $this->db->select($query, [$limit]);
        } catch (Exception $e) {
            error_log('GetRecentProjects Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all projects for a specific employee (both created and assigned)
     * 
     * @param int $userId User ID
     * @return array Array of projects
     */
    public function getProjectsForEmployee($userId) {
        try {
            $query = "SELECT DISTINCT p.*, 
                     u.username as created_by, 
                     d.name as department_name,
                     (SELECT COUNT(*) FROM tasks WHERE project_id = p.id) as task_count,
                     (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND status = 'Completed') as completed_tasks,
                     CASE 
                        WHEN p.user_id = ? THEN 'Owner' 
                        ELSE ISNULL(pu.role, 'Member') 
                     END as user_role
                     FROM projects p
                     LEFT JOIN users u ON p.user_id = u.id
                     LEFT JOIN departments d ON p.department_id = d.id
                     LEFT JOIN project_users pu ON p.id = pu.project_id AND pu.user_id = ?
                     WHERE p.user_id = ? OR pu.user_id = ?
                     ORDER BY p.end_date ASC, p.start_date ASC";
            
            $results = $this->db->select($query, [$userId, $userId, $userId, $userId]);
            
            // Convert arrays to objects
            $projects = [];
            if (!empty($results)) {
                foreach ($results as $result) {
                    $projects[] = (object)$result;
                }
            }
            
            return $projects;
        } catch (Exception $e) {
            error_log('Error in getProjectsForEmployee: ' . $e->getMessage());
            return [];
        }
    }
} 