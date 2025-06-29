<?php
class Calendar {
    private $db;

    public function __construct() {
        // Initialize database connection
        $this->db = new EasySQL(DB1);
    }
    
    /**
     * Get all calendars for a user
     * 
     * @param int $user_id User ID
     * @return array Calendars
     */
    public function getCalendarsByUser($user_id) {
        $query = "SELECT * FROM external_calendars WHERE user_id = :user_id";
        return $this->db->select($query, [':user_id' => $user_id]);
    }
    
    /**
     * Get a calendar by ID
     * 
     * @param int $id Calendar ID
     * @return object|false Calendar object or false
     */
    public function getCalendarById($id) {
        $query = "SELECT * FROM external_calendars WHERE id = :id";
        $result = $this->db->select($query, [':id' => $id]);
        return !empty($result) ? $result[0] : false;
    }
    
    /**
     * Add a new calendar
     * 
     * @param array $data Calendar data
     * @return int|false Last insert ID or false
     */
    public function addCalendar($data) {
        // Prepare the query
        $query = "INSERT INTO external_calendars (user_id, name, source, source_id, color, auto_refresh, access_token, refresh_token, active) 
                 VALUES (:user_id, :name, :source, :source_id, :color, :auto_refresh, :access_token, :refresh_token, :active)";
        
        // Prepare params
        $params = [
            ':user_id' => $data['user_id'],
            ':name' => $data['name'],
            ':source' => $data['source'],
            ':source_id' => $data['source_id'],
            ':color' => $data['color'],
            ':auto_refresh' => $data['auto_refresh'],
            ':access_token' => $data['access_token'],
            ':refresh_token' => $data['refresh_token'],
            ':active' => $data['active']
        ];
        
        // Execute
        $result = $this->db->insert($query, $params);
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Update a calendar
     * 
     * @param array $data Calendar data
     * @return bool True on success, false on failure
     */
    public function updateCalendar($data) {
        // Start building the query
        $query = 'UPDATE external_calendars SET name = :name, color = :color, auto_refresh = :auto_refresh, active = :active';
        
        // Prepare params
        $params = [
            ':id' => $data['id'],
            ':name' => $data['name'],
            ':color' => $data['color'],
            ':auto_refresh' => $data['auto_refresh'],
            ':active' => $data['active']
        ];
        
        // Add source_id if provided
        if (isset($data['source_id'])) {
            $query .= ', source_id = :source_id';
            $params[':source_id'] = $data['source_id'];
        }
        
        // Add access_token and refresh_token if provided
        if (isset($data['access_token'])) {
            $query .= ', access_token = :access_token';
            $params[':access_token'] = $data['access_token'];
        }
        
        if (isset($data['refresh_token'])) {
            $query .= ', refresh_token = :refresh_token';
            $params[':refresh_token'] = $data['refresh_token'];
        }
        
        // Add the WHERE clause
        $query .= ' WHERE id = :id';
        
        // Execute
        return $this->db->update($query, $params);
    }
    
    /**
     * Remove a calendar
     * 
     * @param int $id Calendar ID
     * @return bool True on success, false on failure
     */
    public function removeCalendar($id) {
        // First delete all events from this calendar
        $query1 = "DELETE FROM calendar_events WHERE calendar_id = :calendar_id";
        $this->db->delete($query1, [':calendar_id' => $id]);
        
        // Then delete the calendar
        $query2 = "DELETE FROM external_calendars WHERE id = :id";
        return $this->db->delete($query2, [':id' => $id]);
    }
    
    /**
     * Sync a calendar
     * 
     * @param int $id Calendar ID
     * @return bool True on success, false on failure
     */
    public function syncCalendar($id) {
        // Get the calendar
        $calendar = $this->getCalendarById($id);
        
        if (!$calendar) {
            return false;
        }
        
        // Different sync process based on calendar source
        switch ($calendar->source) {
            case 'google':
                return $this->syncGoogleCalendar($calendar);
                
            case 'outlook':
                return $this->syncOutlookCalendar($calendar);
                
            case 'ical':
                return $this->syncIcalCalendar($calendar);
                
            default:
                return false;
        }
    }
    
    /**
     * Sync a Google calendar
     * 
     * @param object $calendar Calendar object
     * @return bool True on success, false on failure
     */
    private function syncGoogleCalendar($calendar) {
        // In a real implementation, this would use the Google Calendar API
        // For now, we'll just return true
        
        // Clear old events
        $this->clearCalendarEvents($calendar->id);
        
        // Update last synced time
        $this->updateLastSynced($calendar->id);
        
        return true;
    }
    
    /**
     * Sync a Microsoft Outlook calendar
     * 
     * @param object $calendar Calendar object
     * @return bool True on success, false on failure
     */
    private function syncOutlookCalendar($calendar) {
        // In a real implementation, this would use the Microsoft Graph API
        // For now, we'll just return true
        
        // Clear old events
        $this->clearCalendarEvents($calendar->id);
        
        // Update last synced time
        $this->updateLastSynced($calendar->id);
        
        return true;
    }
    
    /**
     * Sync an iCal calendar
     * 
     * @param object $calendar Calendar object
     * @return bool True on success, false on failure
     */
    private function syncIcalCalendar($calendar) {
        // In a real implementation, this would fetch and parse the iCal feed
        // For now, we'll just return true
        
        // Clear old events
        $this->clearCalendarEvents($calendar->id);
        
        // Update last synced time
        $this->updateLastSynced($calendar->id);
        
        return true;
    }
    
    /**
     * Clear all events for a calendar
     * 
     * @param int $calendar_id Calendar ID
     * @return bool True on success, false on failure
     */
    private function clearCalendarEvents($calendar_id) {
        $query = "DELETE FROM calendar_events WHERE calendar_id = :calendar_id";
        return $this->db->delete($query, [':calendar_id' => $calendar_id]);
    }
    
    /**
     * Update the last synced time for a calendar
     * 
     * @param int $calendar_id Calendar ID
     * @return bool True on success, false on failure
     */
    private function updateLastSynced($calendar_id) {
        $query = "UPDATE external_calendars SET last_synced = GETDATE() WHERE id = :id";
        return $this->db->update($query, [':id' => $calendar_id]);
    }
    
    /**
     * Get all events from external calendars for a user
     * 
     * @param int $user_id User ID
     * @return array Events
     */
    public function getCalendarEvents($user_id) {
        $query = "SELECT ce.*, ec.name as calendar_name, ec.color as calendar_color 
                 FROM calendar_events ce
                 JOIN external_calendars ec ON ce.calendar_id = ec.id
                 WHERE ec.user_id = :user_id AND ec.active = 1";
        return $this->db->select($query, [':user_id' => $user_id]);
    }
} 