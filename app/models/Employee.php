<?php

class Employee {
    private $db;
    
    /**
     * Constructor - initializes the database connection
     */
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }
    
    /**
     * Get all employees with their management data
     * 
     * @return array List of employees with management data
     */
    public function getAllEmployees() {
        try {
            $query = "SELECT e.*, u.username, u.full_name, u.email, u.role, u.is_active,
                        (SELECT COUNT(*) FROM EmployeeAbsence a WHERE a.user_id = e.user_id) AS absence_count
                     FROM EmployeeManagement e
                     JOIN Users u ON e.user_id = u.id
                     ORDER BY u.full_name ASC";
                     
            return $this->db->select($query);
        } catch (Exception $e) {
            error_log('Error fetching employees: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get employees without management data
     * 
     * @return array List of employees without management data
     */
    public function getEmployeesWithoutManagementData() {
        try {
            $query = "SELECT u.* 
                     FROM Users u
                     LEFT JOIN EmployeeManagement e ON u.id = e.user_id
                     WHERE e.id IS NULL
                     ORDER BY u.full_name ASC";
                     
            return $this->db->select($query);
        } catch (Exception $e) {
            error_log('Error fetching employees: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get employee management data by user ID
     * 
     * @param int $userId User ID
     * @return array|bool Employee data or false if not found
     */
    public function getEmployeeById($userId) {
        try {
            $query = "SELECT e.*, u.username, u.full_name, u.email, u.role, u.is_active
                     FROM EmployeeManagement e
                     JOIN Users u ON e.user_id = u.id
                     WHERE e.user_id = ?";
                     
            $result = $this->db->select($query, [$userId]);
            
            if (!empty($result)) {
                return $result[0];
            }
            
            return false;
        } catch (Exception $e) {
            error_log('Error fetching employee: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new employee management record
     * 
     * @param array $data Employee data
     * @return bool Success status
     */
    public function createEmployeeRecord($data) {
        try {
            $query = "INSERT INTO EmployeeManagement (
                        user_id, performance_rating, tasks_completed, 
                        tasks_pending, notes, last_review_date, next_review_date
                     ) VALUES (?, ?, ?, ?, ?, ?, ?)";
                     
            $params = [
                $data['user_id'],
                $data['performance_rating'] ?? 0,
                $data['tasks_completed'] ?? 0,
                $data['tasks_pending'] ?? 0,
                $data['notes'] ?? null,
                $data['last_review_date'] ?? null,
                $data['next_review_date'] ?? null
            ];
            
            // The insert method returns the last inserted ID, but we only care if it succeeded
            $result = $this->db->insert($query, $params);
            // If it didn't throw an exception, consider it successful
            return true;
        } catch (Exception $e) {
            error_log('Error creating employee record: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update employee management record and track rating changes
     * 
     * @param array $data Employee data
     * @param int $changedBy ID of the user making the change
     * @return bool Success status
     */
    public function updateEmployeeRecord($data, $changedBy = null) {
        try {
            // Get current rating if it exists
            $currentData = $this->getEmployeeById($data['user_id']);
            $oldRating = $currentData ? $currentData['performance_rating'] : null;
            $newRating = $data['performance_rating'];
            
            // Only track history if rating changed
            if ($oldRating !== null && $oldRating != $newRating && $changedBy) {
                $this->addRatingHistoryRecord([
                    'user_id' => $data['user_id'],
                    'old_rating' => $oldRating,
                    'new_rating' => $newRating,
                    'notes' => $data['notes'] ?? null,
                    'changed_by' => $changedBy
                ]);
            }
            
            $query = "UPDATE EmployeeManagement SET
                        performance_rating = ?,
                        tasks_completed = ?,
                        tasks_pending = ?,
                        notes = ?,
                        last_review_date = ?,
                        next_review_date = ?,
                        updated_at = GETDATE()
                     WHERE user_id = ?";
                     
            $params = [
                $data['performance_rating'],
                $data['tasks_completed'],
                $data['tasks_pending'],
                $data['notes'],
                $data['last_review_date'],
                $data['next_review_date'],
                $data['user_id']
            ];
            
            // The update method doesn't return anything
            // If it doesn't throw an exception, it was successful
            $this->db->update($query, $params);
            return true;
        } catch (Exception $e) {
            error_log('Error updating employee record: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add a rating history record
     * 
     * @param array $data Rating history data
     * @return bool Success status
     */
    public function addRatingHistoryRecord($data) {
        try {
            $query = "INSERT INTO EmployeeRatingHistory (
                        user_id, old_rating, new_rating, notes, changed_by
                     ) VALUES (?, ?, ?, ?, ?)";
                     
            $params = [
                $data['user_id'],
                $data['old_rating'],
                $data['new_rating'],
                $data['notes'],
                $data['changed_by']
            ];
            
            $this->db->insert($query, $params);
            return true;
        } catch (Exception $e) {
            error_log('Error adding rating history: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get rating history for an employee
     * 
     * @param int $userId User ID
     * @return array Rating history records
     */
    public function getRatingHistory($userId) {
        try {
            $query = "SELECT h.*, 
                        u.full_name as changed_by_name,
                        FORMAT(h.changed_at, 'MMM dd, yyyy') as formatted_date
                     FROM EmployeeRatingHistory h
                     JOIN Users u ON h.changed_by = u.id
                     WHERE h.user_id = ?
                     ORDER BY h.changed_at DESC";
                     
            return $this->db->select($query, [$userId]);
        } catch (Exception $e) {
            error_log('Error fetching rating history: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Delete employee management record
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function deleteEmployeeRecord($userId) {
        try {
            // First delete all absence records for this employee
            $this->deleteAllAbsenceRecords($userId);
            
            // Then delete the employee management record
            $query = "DELETE FROM EmployeeManagement WHERE user_id = ?";
            $this->db->remove($query, [$userId]);
            
            return true;
        } catch (Exception $e) {
            error_log('Error deleting employee record: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all absence records for an employee
     * 
     * @param int $userId User ID
     * @return array List of absence records
     */
    public function getAbsenceRecords($userId) {
        try {
            $query = "SELECT a.*, u.full_name as approved_by_name
                     FROM EmployeeAbsence a
                     LEFT JOIN Users u ON a.approved_by = u.id
                     WHERE a.user_id = ?
                     ORDER BY a.start_date DESC";
                     
            return $this->db->select($query, [$userId]);
        } catch (Exception $e) {
            error_log('Error fetching absence records: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Add new absence record
     * 
     * @param array $data Absence data
     * @return bool Success status
     */
    public function addAbsenceRecord($data) {
        try {
            $query = "INSERT INTO EmployeeAbsence (
                        user_id, start_date, end_date, reason, approved_by, approved_at
                     ) VALUES (?, ?, ?, ?, ?, ?)";
                     
            $params = [
                $data['user_id'],
                $data['start_date'],
                $data['end_date'],
                $data['reason'],
                $data['approved_by'],
                $data['approved_at'] ?? date('Y-m-d H:i:s')
            ];
            
            $result = $this->db->insert($query, $params);
            
            if ($result) {
                // Update the employee management record with the latest absence data
                $this->updateAbsenceSummary($data['user_id']);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log('Error adding absence record: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete absence record
     * 
     * @param int $absenceId Absence record ID
     * @param int $userId User ID (for updating summary)
     * @return bool Success status
     */
    public function deleteAbsenceRecord($absenceId, $userId) {
        try {
            $query = "DELETE FROM EmployeeAbsence WHERE id = ?";
            $this->db->remove($query, [$absenceId]);
            
            // If we get here, the removal was successful
            // Update the employee management record
            $this->updateAbsenceSummary($userId);
            
            return true;
        } catch (Exception $e) {
            error_log('Error deleting absence record: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete all absence records for a user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function deleteAllAbsenceRecords($userId) {
        try {
            $query = "DELETE FROM EmployeeAbsence WHERE user_id = ?";
            $this->db->remove($query, [$userId]);
            return true;
        } catch (Exception $e) {
            error_log('Error deleting absence records: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update the absence summary in employee management record
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    private function updateAbsenceSummary($userId) {
        try {
            // Get the most recent absence
            $query = "SELECT TOP 1 start_date, end_date 
                     FROM EmployeeAbsence 
                     WHERE user_id = ? 
                     ORDER BY start_date DESC";
                     
            $result = $this->db->select($query, [$userId]);
            
            // Calculate total absence days
            $query = "SELECT SUM(DATEDIFF(day, start_date, end_date) + 1) as total_days 
                     FROM EmployeeAbsence 
                     WHERE user_id = ?";
                     
            $totalDays = $this->db->select($query, [$userId]);
            
            // Update the summary
            $query = "UPDATE EmployeeManagement SET
                        last_absence_start = ?,
                        last_absence_end = ?,
                        total_absence_days = ?,
                        updated_at = GETDATE()
                     WHERE user_id = ?";
                     
            $params = [
                $result[0]['start_date'] ?? null,
                $result[0]['end_date'] ?? null,
                $totalDays[0]['total_days'] ?? 0,
                $userId
            ];
            
            return $this->db->update($query, $params);
        } catch (Exception $e) {
            error_log('Error updating absence summary: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update task counts for an employee
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function updateTaskCounts($userId) {
        try {
            // Get completed tasks count from the Tasks table (assumes it exists)
            $query = "SELECT 
                        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                        COUNT(CASE WHEN status != 'completed' THEN 1 END) as pending
                     FROM Tasks 
                     WHERE assigned_to = ?";
                     
            $result = $this->db->select($query, [$userId]);
            
            if (empty($result)) {
                return false;
            }
            
            // Update the employee record
            $query = "UPDATE EmployeeManagement SET
                        tasks_completed = ?,
                        tasks_pending = ?,
                        updated_at = GETDATE()
                     WHERE user_id = ?";
                     
            $params = [
                $result[0]['completed'] ?? 0,
                $result[0]['pending'] ?? 0,
                $userId
            ];
            
            // The update method doesn't return anything
            $this->db->update($query, $params);
            // If we get here, the update was successful
            return true;
        } catch (Exception $e) {
            error_log('Error updating task counts: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get employee performance statistics
     * 
     * @return array Performance statistics
     */
    public function getPerformanceStats() {
        try {
            $stats = [
                'top_performers' => [],
                'avg_rating' => 0,
                'total_absences' => 0,
                'recently_reviewed' => []
            ];
            
            // Get top performers
            $query = "SELECT TOP 5 e.*, u.full_name, u.username
                     FROM EmployeeManagement e
                     JOIN Users u ON e.user_id = u.id
                     ORDER BY e.performance_rating DESC";
                     
            $stats['top_performers'] = $this->db->select($query);
            
            // Get average rating
            $query = "SELECT AVG(performance_rating) as avg_rating
                     FROM EmployeeManagement";
                     
            $result = $this->db->select($query);
            $stats['avg_rating'] = $result[0]['avg_rating'] ?? 0;
            
            // Get total absences
            $query = "SELECT COUNT(*) as total
                     FROM EmployeeAbsence";
                     
            $result = $this->db->select($query);
            $stats['total_absences'] = $result[0]['total'] ?? 0;
            
            // Get recently reviewed employees
            $query = "SELECT TOP 5 e.*, u.full_name, u.username
                     FROM EmployeeManagement e
                     JOIN Users u ON e.user_id = u.id
                     WHERE e.last_review_date IS NOT NULL
                     ORDER BY e.last_review_date DESC";
                     
            $stats['recently_reviewed'] = $this->db->select($query);
            
            return $stats;
        } catch (Exception $e) {
            error_log('Error getting performance stats: ' . $e->getMessage());
            return [
                'top_performers' => [],
                'avg_rating' => 0,
                'total_absences' => 0,
                'recently_reviewed' => []
            ];
        }
    }
    
    /**
     * Get active team members for sidebar display
     * 
     * @param int $limit Maximum number of members to return
     * @return array List of team members
     */
    public function getTeamMembers($limit = 5) {
        try {
            $query = "SELECT TOP $limit u.id, u.username, u.full_name, u.email, u.role
                     FROM Users u
                     WHERE u.is_active = 1
                     ORDER BY u.full_name ASC";
                     
            $users = $this->db->select($query);
            
            $teamMembers = [];
            foreach ($users as $user) {
                // Create initial from first letter of first and last name
                $nameParts = explode(' ', $user['full_name'] ?? $user['username']);
                $initial = substr($nameParts[0], 0, 1);
                if (isset($nameParts[1])) {
                    $initial .= substr($nameParts[1], 0, 1);
                }
                
                // Map role to a title
                $title = 'Team Member';
                switch($user['role']) {
                    case 'admin':
                        $title = 'Administrator';
                        break;
                    case 'manager':
                        $title = 'Project Manager';
                        break;
                    case 'developer':
                        $title = 'Developer';
                        break;
                    case 'designer':
                        $title = 'Designer';
                        break;
                }
                
                $teamMembers[] = [
                    'id' => $user['id'],
                    'name' => $user['full_name'] ?? $user['username'],
                    'title' => $title,
                    'initial' => strtoupper($initial),
                    'email' => $user['email']
                ];
            }
            
            return $teamMembers;
        } catch (Exception $e) {
            error_log('Error fetching team members: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Add a performance note
     * 
     * @param array $data Note data (user_id, note_text, note_type, changed_by)
     * @return bool Success status
     */
    public function addPerformanceNote($data) {
        try {
            $query = "INSERT INTO EmployeePerformanceNotes (
                        user_id, note_text, note_type, created_by
                     ) VALUES (?, ?, ?, ?)";
                     
            $params = [
                $data['user_id'],
                $data['note_text'],
                $data['note_type'] ?? 'general',
                $data['changed_by']
            ];
            
            $this->db->insert($query, $params);
            
            // Also update the latest notes in the employee management record
            $query = "UPDATE EmployeeManagement SET
                        notes = CASE 
                            WHEN notes IS NULL OR notes = '' THEN ?
                            ELSE notes + CHAR(13) + CHAR(10) + CHAR(13) + CHAR(10) + ? 
                        END,
                        updated_at = GETDATE()
                     WHERE user_id = ?";
                     
            $noteWithDate = date('Y-m-d') . ': ' . $data['note_text'];
            
            $this->db->update($query, [
                $noteWithDate,
                $noteWithDate,
                $data['user_id']
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log('Error adding performance note: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all performance notes for an employee
     * 
     * @param int $userId User ID
     * @return array List of notes
     */
    public function getPerformanceNotes($userId) {
        try {
            $query = "SELECT n.*, 
                        u.full_name as created_by_name,
                        FORMAT(n.created_at, 'MMM dd, yyyy') as formatted_date
                     FROM EmployeePerformanceNotes n
                     JOIN Users u ON n.created_by = u.id
                     WHERE n.user_id = ?
                     ORDER BY n.created_at DESC";
                     
            return $this->db->select($query, [$userId]);
        } catch (Exception $e) {
            error_log('Error fetching performance notes: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all sites assigned to an employee
     * 
     * @param int $userId User ID
     * @return array List of sites
     */
    public function getEmployeeSites($userId) {
        try {
            // Check if the EmployeeSites table exists
            $checkTable = "IF EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'EmployeeSites')
                          BEGIN
                              SELECT 1 AS table_exists
                          END
                          ELSE
                          BEGIN
                              SELECT 0 AS table_exists
                          END";
                          
            $result = $this->db->select($checkTable);
            $tableExists = isset($result[0]['table_exists']) && $result[0]['table_exists'] == 1;
            
            if ($tableExists) {
                // If the table exists, get the sites
                $query = "SELECT es.*, s.name as site_name, s.location, s.status
                         FROM EmployeeSites es
                         JOIN Sites s ON es.site_id = s.id
                         WHERE es.user_id = ?
                         ORDER BY s.name ASC";
                         
                return $this->db->select($query, [$userId]);
            } else {
                // Return placeholder data for demonstration
                // In a production app, you would create the table first
                return [
                    [
                        'site_name' => 'Main Office',
                        'location' => 'New York, NY',
                        'role' => 'Regular Staff',
                        'status' => 'Active'
                    ],
                    [
                        'site_name' => 'Branch Office',
                        'location' => 'Chicago, IL',
                        'role' => 'Visiting',
                        'status' => 'Temporary'
                    ]
                ];
            }
        } catch (Exception $e) {
            error_log('Error fetching employee sites: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Upload document for an employee
     * 
     * @param int $userId User ID
     * @param array $fileData File data
     * @param string $documentType Document type (optional)
     * @param string $description Document description (optional)
     * @return bool Success status
     */
    public function uploadDocument($userId, $fileData, $documentType = null, $description = null) {
        try {
            // Use direct PDO connection to ensure compatibility
            $pdo = new PDO(
                "sqlsrv:Server=" . DB1['host'] . ";Database=" . DB1['dbname'] . ";TrustServerCertificate=true", 
                DB1['user'], 
                DB1['pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "INSERT INTO employee_documents 
                    (user_id, file_name, file_path, file_type, file_size, document_type, description, uploaded_by, uploaded_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, GETDATE())";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $userId,
                $fileData['name'],
                $fileData['path'],
                $fileData['type'],
                $fileData['size'],
                $documentType,
                $description,
                $_SESSION['user_id']
            ]);
            
            return $result;
        } catch (Exception $e) {
            error_log('Upload Employee Document Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get documents for an employee
     * 
     * @param int $userId User ID
     * @return array Documents
     */
    public function getEmployeeDocuments($userId) {
        try {
            // Use direct PDO connection to ensure compatibility
            $pdo = new PDO(
                "sqlsrv:Server=" . DB1['host'] . ";Database=" . DB1['dbname'] . ";TrustServerCertificate=true", 
                DB1['user'], 
                DB1['pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if the table exists
            $checkTable = "SELECT CASE WHEN EXISTS (
                            SELECT * FROM INFORMATION_SCHEMA.TABLES 
                            WHERE TABLE_NAME = 'employee_documents'
                          ) THEN 1 ELSE 0 END AS table_exists";
            
            $checkStmt = $pdo->query($checkTable);
            $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row || $row['table_exists'] != 1) {
                // Table doesn't exist, return empty array
                return [];
            }
            
            // If table exists, get documents
            $sql = "SELECT d.*, u.username as uploaded_by_name 
                    FROM employee_documents d
                    LEFT JOIN Users u ON d.uploaded_by = u.id
                    WHERE d.user_id = ? 
                    ORDER BY d.uploaded_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Get Employee Documents Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get document by ID
     * 
     * @param int $documentId Document ID
     * @return array|bool Document data or false
     */
    public function getDocumentById($documentId) {
        try {
            // Use direct PDO connection to ensure compatibility
            $pdo = new PDO(
                "sqlsrv:Server=" . DB1['host'] . ";Database=" . DB1['dbname'] . ";TrustServerCertificate=true", 
                DB1['user'], 
                DB1['pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if the table exists
            $checkTable = "SELECT CASE WHEN EXISTS (
                            SELECT * FROM INFORMATION_SCHEMA.TABLES 
                            WHERE TABLE_NAME = 'employee_documents'
                          ) THEN 1 ELSE 0 END AS table_exists";
            
            $checkStmt = $pdo->query($checkTable);
            $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row || $row['table_exists'] != 1) {
                // Table doesn't exist, return null
                return false;
            }
            
            // If table exists, get document
            $sql = "SELECT d.*, u.username as uploaded_by_name 
                    FROM employee_documents d
                    LEFT JOIN Users u ON d.uploaded_by = u.id
                    WHERE d.id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$documentId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (Exception $e) {
            error_log('Get Document By ID Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete document
     * 
     * @param int $documentId Document ID
     * @return bool Success status
     */
    public function deleteDocument($documentId) {
        try {
            // Use direct PDO connection to ensure compatibility
            $pdo = new PDO(
                "sqlsrv:Server=" . DB1['host'] . ";Database=" . DB1['dbname'] . ";TrustServerCertificate=true", 
                DB1['user'], 
                DB1['pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "DELETE FROM employee_documents WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$documentId]);
        } catch (Exception $e) {
            error_log('Delete Employee Document Error: ' . $e->getMessage());
            return false;
        }
    }
}
?> 