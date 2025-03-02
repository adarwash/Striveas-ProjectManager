<?php
class Project {
    private $db;
    
    public function __construct() {
        $this->db = new Database;
        
        // Create projects table if not exists
        $sql = file_get_contents('../app/sql/create_projects_table.sql');
        $this->db->query($sql);
        $this->db->execute();
        
        // Create project members table if not exists
        $sql = file_get_contents('../app/sql/create_project_members_table.sql');
        $this->db->query($sql);
        $this->db->execute();
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

    public function getProjectMembers($projectId)
    {
        $this->db->query('SELECT u.* FROM users u 
                          JOIN project_members pm ON u.id = pm.user_id 
                          WHERE pm.project_id = :project_id');
        $this->db->bind(':project_id', $projectId);
        return $this->db->resultSet();
    }
} 