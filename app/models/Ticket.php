<?php

class Ticket {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }
    
    /**
     * Get open tickets count
     * 
     * @return int Number of open tickets
     */
    public function getOpenTicketsCount() {
        try {
            // Check if Tickets table exists
            $checkQuery = "SELECT COUNT(*) as table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'Tickets'";
            $result = $this->db->select($checkQuery);
            
            if (empty($result) || $result[0]['table_count'] == 0) {
                return 0; // Table doesn't exist yet
            }
            
            $query = "SELECT COUNT(*) as count FROM Tickets WHERE status IN ('Open', 'In Progress', 'Pending')";
            $result = $this->db->select($query);
            return $result[0]['count'] ?? 0;
        } catch (Exception $e) {
            error_log('GetOpenTicketsCount Error: ' . $e->getMessage());
            return 0;
        }
    }
} 