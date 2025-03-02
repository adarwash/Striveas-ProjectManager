<?php
class Department {
    private $db;
    
    public function __construct() {
        // Initialize database connection
        $this->db = new EasySQL(DB1);
    }
    
    // Get all departments
    public function getAllDepartments() {
        $query = "SELECT d.*, 
                 ISNULL((SELECT SUM(p.budget) FROM projects p WHERE p.department_id = d.id), 0) as used_budget
                 FROM departments d
                 ORDER BY d.name ASC";
        
        $results = $this->db->select($query);
        
        // Convert arrays to objects
        $departments = [];
        if (!empty($results)) {
            foreach ($results as $result) {
                $departments[] = (object)$result;
            }
        }
        
        return $departments;
    }
    
    // Get department by ID
    public function getDepartmentById($id) {
        $query = "SELECT d.*, 
                 ISNULL((SELECT SUM(p.budget) FROM projects p WHERE p.department_id = d.id), 0) as used_budget
                 FROM departments d
                 WHERE d.id = ?";
        
        $result = $this->db->select($query, [$id]);
        return !empty($result) ? (object)$result[0] : null;
    }
    
    // Create new department
    public function create($data) {
        $query = "INSERT INTO departments (name, description, budget, created_at) 
                 VALUES (?, ?, ?, GETDATE())";
        
        return $this->db->insert($query, [
            $data['name'],
            $data['description'],
            $data['budget']
        ]);
    }
    
    // Update department
    public function update($data) {
        $query = "UPDATE departments 
                 SET name = ?, description = ?, budget = ?, updated_at = GETDATE()
                 WHERE id = ?";
        
        return $this->db->update($query, [
            $data['name'],
            $data['description'],
            $data['budget'],
            $data['id']
        ]);
    }
    
    // Delete department
    public function delete($id) {
        $query = "DELETE FROM departments WHERE id = ?";
        
        return $this->db->remove($query, [$id]);
    }
    
    // Get budget statistics
    public function getBudgetStats() {
        $query = "SELECT 
                 SUM(budget) as total_budget,
                 ISNULL((SELECT SUM(budget) FROM projects), 0) as total_used_budget
                 FROM departments";
        
        $result = $this->db->select($query);
        return !empty($result) ? (object)$result[0] : (object)[
            'total_budget' => 0, 
            'total_used_budget' => 0
        ];
    }
    
    // Get all projects for a department
    public function getDepartmentProjects($departmentId) {
        $query = "SELECT p.*, u.username as created_by
                 FROM projects p
                 LEFT JOIN users u ON p.user_id = u.id
                 WHERE p.department_id = ?
                 ORDER BY p.created_at DESC";
        
        $results = $this->db->select($query, [$departmentId]);
        
        // Convert arrays to objects
        $projects = [];
        if (!empty($results)) {
            foreach ($results as $result) {
                $projects[] = (object)$result;
            }
        }
        
        return $projects;
    }
}
?> 