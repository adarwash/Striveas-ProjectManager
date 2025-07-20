<?php

class Request {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }
    
    /**
     * Get pending requests count
     * 
     * @return int Number of pending requests
     */
    public function getPendingRequestsCount() {
        try {
            // Check if Requests table exists
            $checkQuery = "SELECT COUNT(*) as table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'Requests'";
            $result = $this->db->select($checkQuery);
            
            if (empty($result) || $result[0]['table_count'] == 0) {
                return 0; // Table doesn't exist yet
            }
            
            $query = "SELECT COUNT(*) as count FROM Requests WHERE status = 'Pending'";
            $result = $this->db->select($query);
            return $result[0]['count'] ?? 0;
        } catch (Exception $e) {
            error_log('GetPendingRequestsCount Error: ' . $e->getMessage());
            return 0;
        }
    }
} 