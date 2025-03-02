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
} 