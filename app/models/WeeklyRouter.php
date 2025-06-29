<?php
/**
 * WeeklyRouter Model
 * Handles weekly router schedules and assignments for technicians
 */
class WeeklyRouter {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
        // Don't create the table in the constructor
        // The table should be created via SQL script separately
    }

    /**
     * Create weekly_routers table if it doesn't exist
     * 
     * @return bool True if successful or table already exists, false on error
     */
    public function createWeeklyRoutersTable() {
        try {
            // First check if the table already exists
            $checkTableQuery = "SELECT COUNT(*) AS table_exists FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'weekly_routers'";
            $result = $this->db->select($checkTableQuery);
            
            // If table already exists, return true
            if (!empty($result) && isset($result[0]['table_exists']) && $result[0]['table_exists'] > 0) {
                return true;
            }

            // If not, execute the create table SQL file
            $sqlFile = file_get_contents(APPROOT . '/../sql/create_weekly_routers_table.sql');
            if ($sqlFile === false) {
                error_log('Error: Unable to read create_weekly_routers_table.sql file');
                return false;
            }

            // Execute the SQL script
            $this->db->query($sqlFile);
            return true;
        } catch (Exception $e) {
            error_log('Error creating weekly_routers table: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new weekly router schedule
     * 
     * @param array $data Router schedule data
     * @return int|bool Router schedule ID if successful, false otherwise
     */
    public function create($data) {
        try {
            // Ensure the table exists
            $this->createWeeklyRoutersTable();
            
            $query = "INSERT INTO weekly_routers (router_name, router_ip, location, assigned_technician_id, 
                     week_start_date, week_end_date, maintenance_type, priority, description, 
                     estimated_hours, created_by) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            return $this->db->insert($query, [
                $data['router_name'],
                $data['router_ip'],
                $data['location'],
                $data['assigned_technician_id'],
                $data['week_start_date'],
                $data['week_end_date'],
                $data['maintenance_type'],
                $data['priority'],
                $data['description'],
                $data['estimated_hours'],
                $data['created_by']
            ]);
        } catch (Exception $e) {
            error_log('Error creating weekly router schedule: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all weekly router schedules
     * 
     * @param array $filters Optional filters
     * @return array Array of router schedules
     */
    public function getAllRouterSchedules($filters = []) {
        try {
            $query = "SELECT wr.*, 
                     u1.username as technician_name, u1.full_name as technician_full_name,
                     u2.username as created_by_name, u2.full_name as created_by_full_name
                     FROM weekly_routers wr
                     LEFT JOIN users u1 ON wr.assigned_technician_id = u1.id
                     LEFT JOIN users u2 ON wr.created_by = u2.id
                     WHERE 1=1";
            
            $params = [];
            
            // Apply filters
            if (!empty($filters['technician_id'])) {
                $query .= " AND wr.assigned_technician_id = ?";
                $params[] = $filters['technician_id'];
            }
            
            if (!empty($filters['week_start'])) {
                $query .= " AND wr.week_start_date >= ?";
                $params[] = $filters['week_start'];
            }
            
            if (!empty($filters['week_end'])) {
                $query .= " AND wr.week_end_date <= ?";
                $params[] = $filters['week_end'];
            }
            
            if (!empty($filters['status'])) {
                $query .= " AND wr.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['priority'])) {
                $query .= " AND wr.priority = ?";
                $params[] = $filters['priority'];
            }
            
            $query .= " ORDER BY wr.week_start_date DESC, wr.priority DESC, wr.created_at DESC";
            
            return $this->db->select($query, $params);
        } catch (Exception $e) {
            error_log('Error getting router schedules: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get router schedules for a specific technician
     * 
     * @param int $technicianId Technician user ID
     * @param string $weekStart Optional week start date filter
     * @param string $weekEnd Optional week end date filter
     * @return array Array of router schedules
     */
    public function getRouterSchedulesForTechnician($technicianId, $weekStart = null, $weekEnd = null) {
        try {
            $query = "SELECT wr.*, 
                     u1.username as technician_name, u1.full_name as technician_full_name,
                     u2.username as created_by_name, u2.full_name as created_by_full_name
                     FROM weekly_routers wr
                     LEFT JOIN users u1 ON wr.assigned_technician_id = u1.id
                     LEFT JOIN users u2 ON wr.created_by = u2.id
                     WHERE wr.assigned_technician_id = ?";
            
            $params = [$technicianId];
            
            if ($weekStart) {
                $query .= " AND wr.week_start_date >= ?";
                $params[] = $weekStart;
            }
            
            if ($weekEnd) {
                $query .= " AND wr.week_end_date <= ?";
                $params[] = $weekEnd;
            }
            
            $query .= " ORDER BY wr.week_start_date ASC, wr.priority DESC";
            
            return $this->db->select($query, $params);
        } catch (Exception $e) {
            error_log('Error getting technician router schedules: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get current week router schedules for a technician
     * 
     * @param int $technicianId Technician user ID
     * @return array Array of current week router schedules
     */
    public function getCurrentWeekSchedulesForTechnician($technicianId) {
        try {
            $currentDate = date('Y-m-d');
            
            $query = "SELECT wr.*, 
                     u1.username as technician_name, u1.full_name as technician_full_name,
                     u2.username as created_by_name, u2.full_name as created_by_full_name
                     FROM weekly_routers wr
                     LEFT JOIN users u1 ON wr.assigned_technician_id = u1.id
                     LEFT JOIN users u2 ON wr.created_by = u2.id
                     WHERE wr.assigned_technician_id = ? 
                     AND ? BETWEEN wr.week_start_date AND wr.week_end_date
                     ORDER BY wr.priority DESC, wr.created_at ASC";
            
            return $this->db->select($query, [$technicianId, $currentDate]);
        } catch (Exception $e) {
            error_log('Error getting current week schedules: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a router schedule by ID
     * 
     * @param int $id Router schedule ID
     * @return array|bool Router schedule data if found, false otherwise
     */
    public function getRouterScheduleById($id) {
        try {
            $query = "SELECT wr.*, 
                     u1.username as technician_name, u1.full_name as technician_full_name,
                     u2.username as created_by_name, u2.full_name as created_by_full_name
                     FROM weekly_routers wr
                     LEFT JOIN users u1 ON wr.assigned_technician_id = u1.id
                     LEFT JOIN users u2 ON wr.created_by = u2.id
                     WHERE wr.id = ?";
            
            $result = $this->db->select($query, [$id]);
            return !empty($result) ? $result[0] : false;
        } catch (Exception $e) {
            error_log('Error getting router schedule by ID: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a router schedule
     * 
     * @param int $id Router schedule ID
     * @param array $data Updated data
     * @return bool True if successful, false otherwise
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE weekly_routers SET 
                     router_name = ?, router_ip = ?, location = ?, assigned_technician_id = ?,
                     week_start_date = ?, week_end_date = ?, maintenance_type = ?, priority = ?,
                     description = ?, estimated_hours = ?, updated_at = GETDATE()
                     WHERE id = ?";
            
            $this->db->update($query, [
                $data['router_name'],
                $data['router_ip'],
                $data['location'],
                $data['assigned_technician_id'],
                $data['week_start_date'],
                $data['week_end_date'],
                $data['maintenance_type'],
                $data['priority'],
                $data['description'],
                $data['estimated_hours'],
                $id
            ]);
            return true;
        } catch (Exception $e) {
            error_log('Error updating router schedule: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update router schedule status
     * 
     * @param int $id Router schedule ID
     * @param string $status New status
     * @param string $notes Optional notes
     * @param float $actualHours Optional actual hours worked
     * @return bool True if successful, false otherwise
     */
    public function updateStatus($id, $status, $notes = null, $actualHours = null) {
        try {
            $query = "UPDATE weekly_routers SET status = ?, updated_at = GETDATE()";
            $params = [$status];
            
            if ($notes !== null) {
                $query .= ", notes = ?";
                $params[] = $notes;
            }
            
            if ($actualHours !== null) {
                $query .= ", actual_hours = ?";
                $params[] = $actualHours;
            }
            
            if ($status === 'Completed') {
                $query .= ", completed_at = GETDATE()";
            }
            
            $query .= " WHERE id = ?";
            $params[] = $id;
            
            $this->db->update($query, $params);
            return true;
        } catch (Exception $e) {
            error_log('Error updating router schedule status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a router schedule
     * 
     * @param int $id Router schedule ID
     * @return bool True if successful, false otherwise
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM weekly_routers WHERE id = ?";
            $this->db->remove($query, [$id]);
            return true;
        } catch (Exception $e) {
            error_log('Error deleting router schedule: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get technicians (users with technician role)
     * 
     * @return array Array of technician users
     */
    public function getTechnicians() {
        try {
            $query = "SELECT id, username, full_name, email 
                     FROM users 
                     WHERE role IN ('technician', 'Technician') 
                     AND is_active = 1
                     ORDER BY full_name ASC, username ASC";
            
            return $this->db->select($query);
        } catch (Exception $e) {
            error_log('Error getting technicians: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get router schedule statistics
     * 
     * @param int $technicianId Optional technician ID to filter by
     * @return array Statistics array
     */
    public function getRouterScheduleStats($technicianId = null) {
        try {
            $query = "SELECT 
                     COUNT(*) as total,
                     SUM(CASE WHEN status = 'Scheduled' THEN 1 ELSE 0 END) as scheduled,
                     SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
                     SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                     SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
                     AVG(CASE WHEN actual_hours IS NOT NULL THEN actual_hours ELSE estimated_hours END) as avg_hours
                     FROM weekly_routers";
            
            $params = [];
            
            if ($technicianId) {
                $query .= " WHERE assigned_technician_id = ?";
                $params[] = $technicianId;
            }
            
            $result = $this->db->select($query, $params);
            return !empty($result) ? $result[0] : [
                'total' => 0,
                'scheduled' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'avg_hours' => 0
            ];
        } catch (Exception $e) {
            error_log('Error getting router schedule stats: ' . $e->getMessage());
            return [
                'total' => 0,
                'scheduled' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'avg_hours' => 0
            ];
        }
    }
} 