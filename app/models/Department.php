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
        try {
            // Check if currency column exists before using it
            require_once APPROOT . '/app/helpers/db_helper.php';
            $hasCurrency = column_exists($this->db, 'departments', 'currency');
            
            if ($hasCurrency) {
                $query = "INSERT INTO departments (name, description, budget, currency, created_at) 
                         VALUES (?, ?, ?, ?, GETDATE())";
                
                return $this->db->insert($query, [
                    $data['name'],
                    $data['description'],
                    $data['budget'],
                    $data['currency'] ?? 'USD'
                ]);
            } else {
                // Fallback for when currency column doesn't exist
                $query = "INSERT INTO departments (name, description, budget, created_at) 
                         VALUES (?, ?, ?, GETDATE())";
                
                return $this->db->insert($query, [
                    $data['name'],
                    $data['description'],
                    $data['budget']
                ]);
            }
        } catch (Exception $e) {
            error_log('Error creating department: ' . $e->getMessage());
            
            // Try again without currency if that's the issue
            if (strpos($e->getMessage(), 'currency') !== false) {
                $query = "INSERT INTO departments (name, description, budget, created_at) 
                         VALUES (?, ?, ?, GETDATE())";
                
                return $this->db->insert($query, [
                    $data['name'],
                    $data['description'],
                    $data['budget']
                ]);
            }
            
            return false;
        }
    }
    
    // Update department
    public function update($data) {
        try {
            // Check if currency column exists before using it
            require_once APPROOT . '/app/helpers/db_helper.php';
            $hasCurrency = column_exists($this->db, 'departments', 'currency');
            
            if ($hasCurrency) {
                $query = "UPDATE departments 
                         SET name = ?, description = ?, budget = ?, currency = ?, updated_at = GETDATE()
                         WHERE id = ?";
                
                return $this->db->update($query, [
                    $data['name'],
                    $data['description'],
                    $data['budget'],
                    $data['currency'] ?? 'USD',
                    $data['id']
                ]);
            } else {
                // Fallback for when currency column doesn't exist
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
        } catch (Exception $e) {
            error_log('Error updating department: ' . $e->getMessage());
            
            // Try again without currency if that's the issue
            if (strpos($e->getMessage(), 'currency') !== false) {
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
            
            return false;
        }
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
    
    // Get currency symbols
    public function getCurrencySymbols() {
        return [
            'USD' => '$',
            'GBP' => '£',
            'EUR' => '€'
        ];
    }
    
    // Get currency name
    public function getCurrencyName($code) {
        $currencies = [
            'USD' => 'US Dollar',
            'GBP' => 'British Pound',
            'EUR' => 'Euro'
        ];
        
        return $currencies[$code] ?? $code;
    }
    
    // Get currency symbol
    public function getCurrencySymbol($code) {
        $symbols = $this->getCurrencySymbols();
        return $symbols[$code] ?? $code;
    }
}
?> 