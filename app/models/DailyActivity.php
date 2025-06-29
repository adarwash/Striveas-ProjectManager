<?php
/**
 * Daily Activity Model
 * Handles daily activities, check-ins and check-outs
 */
class DailyActivity {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
        // Don't create the table in the constructor
        // The table should be created via SQL script separately
    }

    /**
     * Create daily_activities table if it doesn't exist
     * 
     * @return bool True if successful or table already exists, false on error
     */
    public function createDailyActivitiesTable() {
        try {
            // First check if the table already exists
            $checkTableQuery = "SELECT COUNT(*) AS table_exists FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'daily_activities'";
            $result = $this->db->select($checkTableQuery);
            
            // If table already exists, return true
            if (!empty($result) && isset($result[0]['table_exists']) && $result[0]['table_exists'] > 0) {
                return true;
            }

            // Create the table if it doesn't exist
            $createTableQuery = "CREATE TABLE daily_activities (
                id INT IDENTITY(1,1) PRIMARY KEY,
                user_id INT NOT NULL,
                activity_date DATE NOT NULL DEFAULT GETDATE(),
                description NVARCHAR(MAX) NULL,
                check_in DATETIME NULL,
                check_out DATETIME NULL,
                total_hours DECIMAL(5,2) NULL,
                status NVARCHAR(20) DEFAULT 'Pending',
                created_at DATETIME DEFAULT GETDATE(),
                updated_at DATETIME NULL,
                CONSTRAINT fk_daily_activities_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";
            
            // Execute the create table query
            $this->db->query($createTableQuery);
            
            // Add check constraint
            $checkConstraintQuery = "ALTER TABLE daily_activities ADD CONSTRAINT chk_activity_status CHECK (status IN ('Pending', 'Approved', 'Rejected'))";
            $this->db->query($checkConstraintQuery);
            
            // Add indexes in separate statements
            $indexQueries = [
                "CREATE INDEX idx_daily_activities_user ON daily_activities (user_id)",
                "CREATE INDEX idx_daily_activities_date ON daily_activities (activity_date)",
                "CREATE INDEX idx_daily_activities_status ON daily_activities (status)"
            ];
            
            foreach ($indexQueries as $indexQuery) {
                $this->db->query($indexQuery);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Error creating daily_activities table: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Record a new daily activity entry with check-in
     * 
     * @param int $userId User ID
     * @param string $description Activity description
     * @return int|bool The ID of the new record or false on failure
     */
    public function checkIn($userId, $description = '') {
        try {
            // Check if user already has an active check-in for today
            $existingRecord = $this->getActiveCheckIn($userId);
            if ($existingRecord) {
                return false; // User already checked in
            }

            $currentTime = date('Y-m-d H:i:s');
            $currentDate = date('Y-m-d');

            $query = "INSERT INTO daily_activities (user_id, activity_date, description, check_in, created_at) 
                     VALUES (?, ?, ?, ?, ?)";
            
            return $this->db->insert($query, [
                $userId,
                $currentDate,
                $description,
                $currentTime,
                $currentTime
            ]);
        } catch (Exception $e) {
            error_log('Error recording check-in: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update daily activity entry with check-out and calculate total hours
     * 
     * @param int $activityId Activity ID
     * @param string $description Updated description
     * @return bool True if successful, false otherwise
     */
    public function checkOut($activityId, $description = null) {
        try {
            $currentTime = date('Y-m-d H:i:s');
            
            // Get current record to calculate hours
            $record = $this->getActivityById($activityId);
            if (!$record || $record['check_out'] !== null) {
                return false; // Record not found or already checked out
            }

            $checkIn = new DateTime($record['check_in']);
            $checkOut = new DateTime($currentTime);
            $interval = $checkIn->diff($checkOut);
            $totalHours = round(($interval->h + ($interval->i / 60)), 2);

            // Update fields
            $updateFields = ["check_out = ?, total_hours = ?, updated_at = ?"];
            $params = [$currentTime, $totalHours, $currentTime];
            
            // Include description update if provided
            if ($description !== null) {
                $updateFields[] = "description = ?";
                $params[] = $description;
            }
            
            $query = "UPDATE daily_activities SET " . implode(", ", $updateFields) . " WHERE id = ?";
            $params[] = $activityId;
            
            $this->db->update($query, $params);
            return true;
        } catch (Exception $e) {
            error_log('Error recording check-out: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active check-in for a user (no check-out recorded)
     * 
     * @param int $userId User ID
     * @return array|bool Activity record or false if none found
     */
    public function getActiveCheckIn($userId) {
        try {
            $query = "SELECT * FROM daily_activities 
                     WHERE user_id = ? AND check_out IS NULL 
                     ORDER BY check_in DESC";
            
            $result = $this->db->select($query, [$userId]);
            return !empty($result) ? $result[0] : false;
        } catch (Exception $e) {
            error_log('Error getting active check-in: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a daily activity record by ID
     * 
     * @param int $id Activity ID
     * @return array|bool Activity record or false if not found
     */
    public function getActivityById($id) {
        try {
            $query = "SELECT * FROM daily_activities WHERE id = ?";
            $result = $this->db->select($query, [$id]);
            return !empty($result) ? $result[0] : false;
        } catch (Exception $e) {
            error_log('Error getting activity by ID: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get daily activities for a user
     * 
     * @param int $userId User ID
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array Array of activity records
     */
    public function getActivitiesByUser($userId, $startDate = null, $endDate = null) {
        try {
            $query = "SELECT da.*, u.username as user_name 
                     FROM daily_activities da
                     LEFT JOIN users u ON da.user_id = u.id
                     WHERE da.user_id = ?";
            
            $params = [$userId];
            
            if ($startDate) {
                $query .= " AND da.activity_date >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $query .= " AND da.activity_date <= ?";
                $params[] = $endDate;
            }
            
            $query .= " ORDER BY da.activity_date DESC, da.check_in DESC";
            
            return $this->db->select($query, $params);
        } catch (Exception $e) {
            error_log('Error getting activities by user: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all daily activities (for managers/admins)
     * 
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @param string $status Filter by status
     * @return array Array of activity records
     */
    public function getAllActivities($startDate = null, $endDate = null, $status = null) {
        try {
            $query = "SELECT da.*, u.username as user_name, u.full_name
                     FROM daily_activities da
                     LEFT JOIN users u ON da.user_id = u.id
                     WHERE 1=1";
            
            $params = [];
            
            if ($startDate) {
                $query .= " AND da.activity_date >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $query .= " AND da.activity_date <= ?";
                $params[] = $endDate;
            }
            
            if ($status) {
                $query .= " AND da.status = ?";
                $params[] = $status;
            }
            
            $query .= " ORDER BY da.activity_date DESC, u.username ASC, da.check_in DESC";
            
            return $this->db->select($query, $params);
        } catch (Exception $e) {
            error_log('Error getting all activities: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Update activity description
     * 
     * @param int $id Activity ID
     * @param string $description Updated description
     * @return bool True if successful, false otherwise
     */
    public function updateDescription($id, $description) {
        try {
            $query = "UPDATE daily_activities SET description = ?, updated_at = GETDATE() WHERE id = ?";
            $this->db->update($query, [$description, $id]);
            return true;
        } catch (Exception $e) {
            error_log('Error updating activity description: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update activity status (for managers to approve/reject)
     * 
     * @param int $id Activity ID
     * @param string $status New status ('Pending', 'Approved', 'Rejected')
     * @return bool True if successful, false otherwise
     */
    public function updateStatus($id, $status) {
        try {
            $validStatuses = ['Pending', 'Approved', 'Rejected'];
            if (!in_array($status, $validStatuses)) {
                return false;
            }
            
            $query = "UPDATE daily_activities SET status = ?, updated_at = GETDATE() WHERE id = ?";
            $this->db->update($query, [$status, $id]);
            return true;
        } catch (Exception $e) {
            error_log('Error updating activity status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get daily activity summary for a user (total hours per day)
     * 
     * @param int $userId User ID
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array Array of daily summaries
     */
    public function getUserActivitySummary($userId, $startDate = null, $endDate = null) {
        try {
            $query = "SELECT activity_date, 
                     COUNT(*) as total_activities,
                     SUM(total_hours) as total_hours,
                     MIN(check_in) as first_check_in,
                     MAX(check_out) as last_check_out
                     FROM daily_activities
                     WHERE user_id = ?
                     AND check_out IS NOT NULL";
            
            $params = [$userId];
            
            if ($startDate) {
                $query .= " AND activity_date >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $query .= " AND activity_date <= ?";
                $params[] = $endDate;
            }
            
            $query .= " GROUP BY activity_date ORDER BY activity_date DESC";
            
            return $this->db->select($query, $params);
        } catch (Exception $e) {
            error_log('Error getting user activity summary: ' . $e->getMessage());
            return [];
        }
    }
} 