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
    
    /**
     * Get comprehensive employee performance data including time tracking
     * 
     * @param int $userId User ID
     * @param int $days Number of days to analyze (default 30)
     * @return array Comprehensive performance data
     */
    public function getEmployeePerformanceWithTimeTracking($userId, $days = 30) {
        try {
            // Get basic employee data
            $employee = $this->getEmployeeById($userId);
            if (!$employee) {
                return false;
            }
            
            // Get time tracking model
            require_once __DIR__ . '/TimeTracking.php';
            $timeModel = new TimeTracking();
            
            $startDate = date('Y-m-d', strtotime("-{$days} days"));
            $endDate = date('Y-m-d');
            
            // Get time tracking performance metrics
            $timePerformance = $this->calculateTimePerformanceMetrics($userId, $startDate, $endDate);
            
            // Get recent time entries
            $recentEntries = $timeModel->getUserTimeEntries($userId, $startDate, $endDate, 10);
            
            // Get current status
            $currentStatus = $timeModel->getUserStatus($userId);
            
            $activityStats = $this->getEmployeeActivityStats($userId, $startDate, $endDate);
            
            // Combine all data
            return array_merge($employee, [
                'time_performance' => $timePerformance,
                'recent_time_entries' => $recentEntries,
                'current_status' => $currentStatus,
                'analysis_period' => $days,
                'activity_stats' => $activityStats
            ]);
            
        } catch (Exception $e) {
            error_log('Error getting employee performance with time tracking: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Calculate time-based performance metrics
     * 
     * @param int $userId User ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Performance metrics
     */
    private function calculateTimePerformanceMetrics($userId, $startDate, $endDate) {
        try {
            $metrics = [
                'total_hours' => 0,
                'avg_hours_per_day' => 0,
                'punctuality_score' => 0,
                'consistency_score' => 0,
                'break_efficiency' => 0,
                'attendance_rate' => 0,
                'productivity_rating' => 'Good',
                'trends' => []
            ];
            
            // Get time entries for the period
            $query = "SELECT 
                        COUNT(*) as total_days,
                        SUM(total_hours) as total_hours,
                        AVG(total_hours) as avg_hours,
                        MIN(total_hours) as min_hours,
                        MAX(total_hours) as max_hours,
                        SUM(total_break_minutes) as total_break_minutes,
                        AVG(total_break_minutes) as avg_break_minutes,
                        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_days,
                        COUNT(CASE WHEN DATEPART(hour, clock_in_time) <= 9 THEN 1 END) as on_time_days
                      FROM TimeEntries 
                      WHERE user_id = ? AND CAST(clock_in_time AS DATE) BETWEEN ? AND ? AND status = 'completed'";
                      
            $result = $this->db->select($query, [$userId, $startDate, $endDate]);
            
            if (!empty($result)) {
                $data = $result[0];
                
                $metrics['total_hours'] = round($data['total_hours'] ?? 0, 2);
                $metrics['avg_hours_per_day'] = round($data['avg_hours'] ?? 0, 2);
                $metrics['total_break_minutes'] = $data['total_break_minutes'] ?? 0;
                $metrics['avg_break_minutes'] = round($data['avg_break_minutes'] ?? 0, 1);
                
                // Calculate punctuality score (percentage of on-time arrivals)
                $totalDays = $data['total_days'] ?? 0;
                $onTimeDays = $data['on_time_days'] ?? 0;
                $metrics['punctuality_score'] = $totalDays > 0 ? round(($onTimeDays / $totalDays) * 100, 1) : 0;
                
                // Calculate consistency score (how consistent are the working hours)
                $avgHours = $data['avg_hours'] ?? 0;
                $minHours = $data['min_hours'] ?? 0;
                $maxHours = $data['max_hours'] ?? 0;
                
                if ($avgHours > 0) {
                    $variance = (($maxHours - $minHours) / $avgHours) * 100;
                    $metrics['consistency_score'] = max(0, round(100 - $variance, 1));
                }
                
                // Calculate attendance rate
                $expectedDays = $this->getWorkingDaysInPeriod($startDate, $endDate);
                $metrics['attendance_rate'] = $expectedDays > 0 ? round(($totalDays / $expectedDays) * 100, 1) : 0;
                
                // Calculate break efficiency (reasonable break time vs total time)
                if ($metrics['total_hours'] > 0) {
                    $breakHours = $metrics['total_break_minutes'] / 60;
                    $breakRatio = ($breakHours / $metrics['total_hours']) * 100;
                    $metrics['break_efficiency'] = $breakRatio <= 15 ? 'Excellent' : ($breakRatio <= 25 ? 'Good' : 'Needs Improvement');
                }
                
                // Overall productivity rating
                $metrics['productivity_rating'] = $this->calculateProductivityRating($metrics);
            }
            
            // Get daily trends
            $metrics['trends'] = $this->getDailyTimeTracking($userId, $startDate, $endDate);
            
            return $metrics;
            
        } catch (Exception $e) {
            error_log('Error calculating time performance metrics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Aggregate activity metrics (projects, tickets, logins) for an employee
     *
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getEmployeeActivityStats($userId, $startDate, $endDate) {
        $stats = [
            'project_updates' => 0,
            'ticket_replies' => 0,
            'login_count' => 0,
            'task_updates' => 0,
            'task_completions' => 0,
            'note_updates' => 0,
            'client_updates' => 0,
            'site_updates' => 0,
            'request_actions' => 0,
            'recent_activity' => [],
            'task_activity' => []
        ];

        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';

        try {
            // Aggregate activity log counts
            $activityRows = $this->db->select(
                "SELECT entity_type, action, COUNT(*) as total
                 FROM activity_logs
                 WHERE user_id = ? AND created_at BETWEEN ? AND ? AND entity_type <> 'login'
                 GROUP BY entity_type, action",
                [$userId, $startDateTime, $endDateTime]
            ) ?: [];

            foreach ($activityRows as $row) {
                $entity = strtolower($row['entity_type'] ?? '');
                $action = strtolower($row['action'] ?? '');
                $count = (int)($row['total'] ?? 0);

                if ($entity === 'project' && in_array($action, ['created','updated','uploaded','linked','unlinked'], true)) {
                    $stats['project_updates'] += $count;
                }
                if ($entity === 'ticket' && in_array($action, ['commented','updated','status_changed'], true)) {
                    $stats['ticket_replies'] += $count;
                }
                if ($entity === 'task') {
                    if (in_array($action, ['updated','status_changed','progress_updated'], true)) {
                        $stats['task_updates'] += $count;
                    }
                    if ($action === 'completed') {
                        $stats['task_completions'] += $count;
                    }
                }
                if ($entity === 'note' && in_array($action, ['created','updated','deleted'], true)) {
                    $stats['note_updates'] += $count;
                }
                if ($entity === 'client' && in_array($action, ['created','updated','deleted'], true)) {
                    $stats['client_updates'] += $count;
                }
                if ($entity === 'site' && in_array($action, ['created','updated','deleted'], true)) {
                    $stats['site_updates'] += $count;
                }
                if ($entity === 'request' && in_array($action, ['called','viewed'], true)) {
                    $stats['request_actions'] += $count;
                }
            }

            // Recent activity from activity log
            $recentActivityRows = $this->db->select(
                "SELECT TOP 15 entity_type, entity_id, action, description, metadata, created_at
                 FROM activity_logs
                 WHERE user_id = ? AND created_at BETWEEN ? AND ? AND entity_type <> 'login'
                 ORDER BY created_at DESC",
                [$userId, $startDateTime, $endDateTime]
            ) ?: [];

            $recent = [];
            foreach ($recentActivityRows as $row) {
                $metadata = [];
                if (!empty($row['metadata'])) {
                    $decoded = json_decode($row['metadata'], true);
                    if (is_array($decoded)) {
                        $metadata = $decoded;
                    }
                }
                $recent[] = [
                    'entity_type' => $row['entity_type'],
                    'entity_id' => $row['entity_id'],
                    'action' => $row['action'],
                    'description' => $row['description'],
                    'metadata' => $metadata,
                    'created_at' => $row['created_at']
                ];
                if ($row['entity_type'] === 'task') {
                    $stats['task_activity'][] = end($recent);
                }
            }

            // Login counts and recent logins
            $loginTableExists = false;
            try {
                $loginTableCheck = $this->db->select(
                    "SELECT COUNT(*) AS table_exists FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'UserLoginAudit'"
                );
                $loginTableExists = !empty($loginTableCheck) && (int)($loginTableCheck[0]['table_exists'] ?? 0) > 0;
            } catch (Exception $e) {
                $loginTableExists = false;
            }

            if ($loginTableExists) {
                $loginCountRow = $this->db->select(
                    "SELECT COUNT(*) as total
                     FROM UserLoginAudit
                     WHERE user_id = ? AND success = 1 AND created_at BETWEEN ? AND ?",
                    [$userId, $startDateTime, $endDateTime]
                );
                $stats['login_count'] = (int)($loginCountRow[0]['total'] ?? 0);

                $recentLogins = $this->db->select(
                    "SELECT TOP 10 created_at
                     FROM UserLoginAudit
                     WHERE user_id = ? AND success = 1 AND created_at BETWEEN ? AND ?
                     ORDER BY created_at DESC",
                    [$userId, $startDateTime, $endDateTime]
                ) ?: [];
            } else {
                // Fallback: use activity_logs login events (added in Auth controller)
                $loginCountRow = $this->db->select(
                    "SELECT COUNT(*) as total
                     FROM activity_logs
                     WHERE user_id = ? AND entity_type = 'login' AND action = 'login' AND created_at BETWEEN ? AND ?",
                    [$userId, $startDateTime, $endDateTime]
                );
                $stats['login_count'] = (int)($loginCountRow[0]['total'] ?? 0);

                $recentLogins = $this->db->select(
                    "SELECT TOP 10 created_at
                     FROM activity_logs
                     WHERE user_id = ? AND entity_type = 'login' AND action = 'login' AND created_at BETWEEN ? AND ?
                     ORDER BY created_at DESC",
                    [$userId, $startDateTime, $endDateTime]
                ) ?: [];
            }

            foreach ($recentLogins as $loginRow) {
                $entry = [
                    'entity_type' => 'login',
                    'entity_id' => null,
                    'action' => 'login',
                    'description' => 'Successful login',
                    'metadata' => [],
                    'created_at' => $loginRow['created_at']
                ];
                $recent[] = $entry;
            }

            // Sort and limit recent activity feed
            usort($recent, function ($a, $b) {
                return strtotime($b['created_at']) <=> strtotime($a['created_at']);
            });
            $stats['recent_activity'] = array_slice($recent, 0, 10);
            $stats['task_activity'] = array_slice($stats['task_activity'], 0, 10);

        } catch (Exception $e) {
            error_log('Error getting employee activity stats: ' . $e->getMessage());
        }

        return $stats;
    }
    
    /**
     * Get daily time tracking trends
     * 
     * @param int $userId User ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Daily trends
     */
    private function getDailyTimeTracking($userId, $startDate, $endDate) {
        try {
            $query = "SELECT 
                        CAST(clock_in_time AS DATE) as work_date,
                        MIN(clock_in_time) as first_clock_in,
                        MAX(clock_out_time) as last_clock_out,
                        SUM(total_hours) as daily_hours,
                        SUM(total_break_minutes) as daily_break_minutes,
                        COUNT(*) as entries_count
                      FROM TimeEntries 
                      WHERE user_id = ? AND CAST(clock_in_time AS DATE) BETWEEN ? AND ? AND status = 'completed'
                      GROUP BY CAST(clock_in_time AS DATE)
                      ORDER BY work_date DESC";
                      
            return $this->db->select($query, [$userId, $startDate, $endDate]) ?: [];
            
        } catch (Exception $e) {
            error_log('Error getting daily time tracking: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calculate productivity rating based on metrics
     * 
     * @param array $metrics Performance metrics
     * @return string Productivity rating
     */
    private function calculateProductivityRating($metrics) {
        $score = 0;
        
        // Hours per day score (assuming 8 hours is ideal)
        if ($metrics['avg_hours_per_day'] >= 7.5) $score += 25;
        elseif ($metrics['avg_hours_per_day'] >= 6) $score += 20;
        elseif ($metrics['avg_hours_per_day'] >= 4) $score += 15;
        else $score += 10;
        
        // Punctuality score
        if ($metrics['punctuality_score'] >= 90) $score += 25;
        elseif ($metrics['punctuality_score'] >= 80) $score += 20;
        elseif ($metrics['punctuality_score'] >= 70) $score += 15;
        else $score += 10;
        
        // Consistency score
        if ($metrics['consistency_score'] >= 80) $score += 25;
        elseif ($metrics['consistency_score'] >= 60) $score += 20;
        else $score += 15;
        
        // Attendance score
        if ($metrics['attendance_rate'] >= 95) $score += 25;
        elseif ($metrics['attendance_rate'] >= 85) $score += 20;
        elseif ($metrics['attendance_rate'] >= 75) $score += 15;
        else $score += 10;
        
        // Return rating based on total score
        if ($score >= 90) return 'Excellent';
        elseif ($score >= 75) return 'Very Good';
        elseif ($score >= 60) return 'Good';
        elseif ($score >= 45) return 'Fair';
        else return 'Needs Improvement';
    }
    
    /**
     * Get working days in period (excluding weekends)
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return int Number of working days
     */
    private function getWorkingDaysInPeriod($startDate, $endDate) {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $workingDays = 0;
        
        while ($start <= $end) {
            $dayOfWeek = $start->format('N'); // 1 = Monday, 7 = Sunday
            if ($dayOfWeek < 6) { // Monday to Friday
                $workingDays++;
            }
            $start->modify('+1 day');
        }
        
        return $workingDays;
    }
    
    /**
     * Get all employees with comprehensive performance data including time tracking
     * 
     * @param int $days Number of days to analyze
     * @param string $sortBy Sort field (performance_rating, productivity_rating, total_hours, etc.)
     * @return array List of employees with comprehensive data
     */
    public function getAllEmployeesWithTimeTrackingPerformance($days = 30, $sortBy = 'performance_rating') {
        try {
            $employees = $this->getAllEmployees();
            $enhancedEmployees = [];
            
            foreach ($employees as $employee) {
                $performanceData = $this->getEmployeePerformanceWithTimeTracking($employee['user_id'], $days);
                if ($performanceData) {
                    $enhancedEmployees[] = $performanceData;
                }
            }
            
            // Sort by specified field
            if ($sortBy === 'productivity_rating') {
                // Custom sort for productivity rating
                $ratingOrder = ['Excellent' => 5, 'Very Good' => 4, 'Good' => 3, 'Fair' => 2, 'Needs Improvement' => 1];
                usort($enhancedEmployees, function($a, $b) use ($ratingOrder) {
                    $aRating = $ratingOrder[$a['time_performance']['productivity_rating']] ?? 0;
                    $bRating = $ratingOrder[$b['time_performance']['productivity_rating']] ?? 0;
                    return $bRating <=> $aRating;
                });
            } else {
                // Standard sort
                usort($enhancedEmployees, function($a, $b) use ($sortBy) {
                    if (isset($a['time_performance'][$sortBy]) && isset($b['time_performance'][$sortBy])) {
                        return $b['time_performance'][$sortBy] <=> $a['time_performance'][$sortBy];
                    }
                    return ($b[$sortBy] ?? 0) <=> ($a[$sortBy] ?? 0);
                });
            }
            
            return $enhancedEmployees;
            
        } catch (Exception $e) {
            error_log('Error getting employees with time tracking performance: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get time tracking performance summary for all employees
     * 
     * @param int $days Number of days to analyze
     * @return array Summary statistics
     */
    public function getTimeTrackingPerformanceSummary($days = 30) {
        try {
            $startDate = date('Y-m-d', strtotime("-{$days} days"));
            $endDate = date('Y-m-d');
            
            $query = "SELECT 
                        COUNT(DISTINCT te.user_id) as active_employees,
                        AVG(te.total_hours) as avg_hours_per_entry,
                        SUM(te.total_hours) as total_company_hours,
                        AVG(te.total_break_minutes) as avg_break_minutes,
                        COUNT(CASE WHEN DATEPART(hour, te.clock_in_time) <= 9 THEN 1 END) as on_time_entries,
                        COUNT(te.id) as total_entries,
                        COUNT(CASE WHEN te.status = 'completed' THEN 1 END) as completed_entries
                      FROM TimeEntries te
                      INNER JOIN EmployeeManagement em ON te.user_id = em.user_id
                      WHERE CAST(te.clock_in_time AS DATE) BETWEEN ? AND ?";
                      
            $result = $this->db->select($query, [$startDate, $endDate]);
            
            if (!empty($result)) {
                $data = $result[0];
                return [
                    'active_employees' => $data['active_employees'] ?? 0,
                    'avg_hours_per_entry' => round($data['avg_hours_per_entry'] ?? 0, 2),
                    'total_company_hours' => round($data['total_company_hours'] ?? 0, 2),
                    'avg_break_minutes' => round($data['avg_break_minutes'] ?? 0, 1),
                    'punctuality_rate' => $data['total_entries'] > 0 ? round(($data['on_time_entries'] / $data['total_entries']) * 100, 1) : 0,
                    'completion_rate' => $data['total_entries'] > 0 ? round(($data['completed_entries'] / $data['total_entries']) * 100, 1) : 0,
                    'period_days' => $days
                ];
            }
            
            return [];
            
        } catch (Exception $e) {
            error_log('Error getting time tracking performance summary: ' . $e->getMessage());
            return [];
        }
    }
}
?> 
