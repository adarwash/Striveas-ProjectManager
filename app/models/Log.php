<?php
class Log {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
    }

    // Get logs with optional filtering
    public function getLogs($filters = [], $page = 1, $limit = 20) {
        try {
            // Ensure table exists
            $this->createLogsTable();
            
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT * FROM system_logs WHERE 1=1";
            $params = [];
            
            // Apply filters
            if (!empty($filters['type'])) {
                $query .= " AND type = ?";
                $params[] = $filters['type'];
            }
            
            if (!empty($filters['user'])) {
                $query .= " AND (user LIKE ? OR user_id = ?)";
                $params[] = "%{$filters['user']}%";
                $params[] = $filters['user']; // In case it's a user ID
            }
            
            if (!empty($filters['from_date'])) {
                $query .= " AND timestamp >= ?";
                $params[] = $filters['from_date'] . ' 00:00:00';
            }
            
            if (!empty($filters['to_date'])) {
                $query .= " AND timestamp <= ?";
                $params[] = $filters['to_date'] . ' 23:59:59';
            }
            
            // Add ordering and pagination
            $query .= " ORDER BY timestamp DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            // Execute the query
            $result = $this->db->select($query, $params);
            
            return $result ?: [];
        } catch (Exception $e) {
            error_log('GetLogs Error: ' . $e->getMessage());
            return [];
        }
    }

    // Get total count of logs (for pagination)
    public function getTotalLogs($filters = []) {
        try {
            // Ensure table exists
            $this->createLogsTable();
            
            $query = "SELECT COUNT(*) as total FROM system_logs WHERE 1=1";
            $params = [];
            
            // Apply filters
            if (!empty($filters['type'])) {
                $query .= " AND type = ?";
                $params[] = $filters['type'];
            }
            
            if (!empty($filters['user'])) {
                $query .= " AND (user LIKE ? OR user_id = ?)";
                $params[] = "%{$filters['user']}%";
                $params[] = $filters['user']; // In case it's a user ID
            }
            
            if (!empty($filters['from_date'])) {
                $query .= " AND timestamp >= ?";
                $params[] = $filters['from_date'] . ' 00:00:00';
            }
            
            if (!empty($filters['to_date'])) {
                $query .= " AND timestamp <= ?";
                $params[] = $filters['to_date'] . ' 23:59:59';
            }
            
            // Execute the query
            $result = $this->db->select($query, $params);
            
            return $result[0]['total'] ?? 0;
        } catch (Exception $e) {
            error_log('GetTotalLogs Error: ' . $e->getMessage());
            return 0;
        }
    }

    // Add a new log entry
    public function addLog($type, $message, $userId = null, $user = null, $data = null) {
        try {
            // Ensure table exists
            $this->createLogsTable();
            
            $query = "INSERT INTO system_logs (type, message, user_id, [user], ip_address, user_agent, additional_data) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $jsonData = $data ? json_encode($data) : null;
            
            $params = [
                $type,
                $message,
                $userId,
                $user,
                $ip,
                $userAgent,
                $jsonData
            ];
            
            $this->db->insert($query, $params);
            return true;
        } catch (Exception $e) {
            error_log('AddLog Error: ' . $e->getMessage());
            return false;
        }
    }

    // Get log details by ID
    public function getLogById($id) {
        try {
            // Ensure table exists
            $this->createLogsTable();
            
            $query = "SELECT * FROM system_logs WHERE id = ?";
            $result = $this->db->select($query, [$id]);
            
            return $result[0] ?? null;
        } catch (Exception $e) {
            error_log('GetLogById Error: ' . $e->getMessage());
            return null;
        }
    }

    // Create logs table if it doesn't exist
    public function createLogsTable() {
        try {
            $query = "IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[system_logs]') AND type in (N'U'))
            BEGIN
                CREATE TABLE [dbo].[system_logs] (
                    [id] INT IDENTITY(1,1) PRIMARY KEY,
                    [type] NVARCHAR(50) NOT NULL,
                    [message] NVARCHAR(MAX) NOT NULL,
                    [user_id] INT NULL,
                    [user] NVARCHAR(255) NULL,
                    [ip_address] NVARCHAR(45) NULL,
                    [user_agent] NVARCHAR(MAX) NULL,
                    [additional_data] NVARCHAR(MAX) NULL,
                    [timestamp] DATETIME DEFAULT GETDATE()
                )
            END";
            
            // Use a direct query instead of execute()
            return $this->db->query($query);
        } catch (Exception $e) {
            error_log('CreateLogsTable Error: ' . $e->getMessage());
            return false;
        }
    }
} 