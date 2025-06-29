<?php

class TimeTracking {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }
    
    /**
     * Clock in user - start time tracking
     */
    public function clockIn($userId, $notes = null) {
        try {
            // Check if user is already clocked in
            $activeEntry = $this->getActiveTimeEntry($userId);
            if ($activeEntry) {
                return ['success' => false, 'message' => 'User is already clocked in'];
            }
            
            $clockInTime = date('Y-m-d H:i:s');
            
            $sql = "INSERT INTO dbo.TimeEntries (user_id, clock_in_time, notes, status) VALUES (?, ?, ?, 'active')";
            $result = $this->db->insert($sql, [$userId, $clockInTime, $notes]);
            
            // If no exception was thrown, the insert was successful
            return [
                'success' => true, 
                'message' => 'Clocked in successfully',
                'clock_in_time' => $clockInTime,
                'time_entry_id' => $result ?: 'created'
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Clock out user - end time tracking
     */
    public function clockOut($userId, $notes = null) {
        try {
            $activeEntry = $this->getActiveTimeEntry($userId);
            if (!$activeEntry) {
                return ['success' => false, 'message' => 'No active time entry found'];
            }
            
            $clockOutTime = date('Y-m-d H:i:s');
            
            // End any active breaks
            $this->endActiveBreaks($activeEntry['id']);
            
            // Calculate total hours
            $totalMinutes = $this->calculateTotalMinutes($activeEntry['clock_in_time'], $clockOutTime);
            $totalBreakMinutes = $this->getTotalBreakMinutes($activeEntry['id']);
            $netMinutes = $totalMinutes - $totalBreakMinutes;
            $totalHours = round($netMinutes / 60, 2);
            
            $updateNotes = $notes ? ($activeEntry['notes'] ? $activeEntry['notes'] . ' | ' . $notes : $notes) : $activeEntry['notes'];
            $sql = "UPDATE dbo.TimeEntries SET clock_out_time = ?, total_hours = ?, total_break_minutes = ?, status = 'completed', notes = ?, updated_at = ? WHERE id = ?";
            $this->db->update($sql, [$clockOutTime, $totalHours, $totalBreakMinutes, $updateNotes, date('Y-m-d H:i:s'), $activeEntry['id']]);
            
            // If no exception was thrown, the update was successful
            return [
                'success' => true,
                'message' => 'Clocked out successfully',
                'clock_out_time' => $clockOutTime,
                'total_hours' => $totalHours,
                'total_break_minutes' => $totalBreakMinutes
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Start a break
     */
    public function startBreak($userId, $breakType = 'regular', $notes = null) {
        try {
            $activeEntry = $this->getActiveTimeEntry($userId);
            if (!$activeEntry) {
                return ['success' => false, 'message' => 'No active time entry found'];
            }
            
            // Check if user is already on break
            $activeBreak = $this->getActiveBreak($activeEntry['id']);
            if ($activeBreak) {
                return ['success' => false, 'message' => 'User is already on break'];
            }
            
            $breakStart = date('Y-m-d H:i:s');
            
            $sql = "INSERT INTO dbo.TimeBreaks (time_entry_id, break_start, break_type, notes) VALUES (?, ?, ?, ?)";
            $result = $this->db->insert($sql, [$activeEntry['id'], $breakStart, $breakType, $notes]);
            
            // If no exception was thrown, the insert was successful
            return [
                'success' => true,
                'message' => 'Break started successfully',
                'break_start' => $breakStart,
                'break_type' => $breakType,
                'break_id' => $result ?: 'created'
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * End a break
     */
    public function endBreak($userId, $notes = null) {
        try {
            $activeEntry = $this->getActiveTimeEntry($userId);
            if (!$activeEntry) {
                return ['success' => false, 'message' => 'No active time entry found'];
            }
            
            $activeBreak = $this->getActiveBreak($activeEntry['id']);
            if (!$activeBreak) {
                return ['success' => false, 'message' => 'No active break found'];
            }
            
            $breakEnd = date('Y-m-d H:i:s');
            $breakDuration = $this->calculateTotalMinutes($activeBreak['break_start'], $breakEnd);
            
            $updateNotes = $notes ? ($activeBreak['notes'] ? $activeBreak['notes'] . ' | ' . $notes : $notes) : $activeBreak['notes'];
            $sql = "UPDATE dbo.TimeBreaks SET break_end = ?, break_duration_minutes = ?, notes = ? WHERE id = ?";
            $this->db->update($sql, [$breakEnd, $breakDuration, $updateNotes, $activeBreak['id']]);
            
            // Update total break minutes in time entry
            $totalBreakMinutes = $this->getTotalBreakMinutes($activeEntry['id']);
            $sql = "UPDATE dbo.TimeEntries SET total_break_minutes = ?, updated_at = ? WHERE id = ?";
            $this->db->update($sql, [$totalBreakMinutes, date('Y-m-d H:i:s'), $activeEntry['id']]);
            
            // If no exception was thrown, the updates were successful
            return [
                'success' => true,
                'message' => 'Break ended successfully',
                'break_end' => $breakEnd,
                'break_duration' => $breakDuration
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get user's current status
     */
    public function getUserStatus($userId) {
        $activeEntry = $this->getActiveTimeEntry($userId);
        if (!$activeEntry) {
            return [
                'status' => 'clocked_out',
                'message' => 'Not clocked in',
                'time_entry' => null,
                'active_break' => null
            ];
        }
        
        $activeBreak = $this->getActiveBreak($activeEntry['id']);
        
        if ($activeBreak) {
            return [
                'status' => 'on_break',
                'message' => 'On break',
                'time_entry' => $activeEntry,
                'active_break' => $activeBreak,
                'elapsed_work_time' => $this->getElapsedWorkTime($activeEntry),
                'break_duration' => $this->calculateTotalMinutes($activeBreak['break_start'], date('Y-m-d H:i:s'))
            ];
        }
        
        return [
            'status' => 'clocked_in',
            'message' => 'Clocked in and working',
            'time_entry' => $activeEntry,
            'active_break' => null,
            'elapsed_work_time' => $this->getElapsedWorkTime($activeEntry)
        ];
    }
    
    /**
     * Get active time entry for user
     */
    public function getActiveTimeEntry($userId) {
        $sql = "SELECT * FROM dbo.TimeEntries WHERE user_id = ? AND status = 'active'";
        $result = $this->db->select($sql, [$userId]);
        return $result ? $result[0] : null;
    }
    
    /**
     * Get active break for time entry
     */
    public function getActiveBreak($timeEntryId) {
        $sql = "SELECT * FROM dbo.TimeBreaks WHERE time_entry_id = ? AND break_end IS NULL";
        $result = $this->db->select($sql, [$timeEntryId]);
        return $result ? $result[0] : null;
    }
    
    /**
     * Get user's time entries for a date range
     */
    public function getUserTimeEntries($userId, $startDate = null, $endDate = null, $limit = 50) {
        // For SQL Server, use TOP instead of LIMIT
        $sql = "SELECT TOP $limit * FROM dbo.TimeEntries WHERE user_id = ?";
        $params = [$userId];
        
        if ($startDate) {
            $sql .= " AND clock_in_time >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND clock_in_time <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        
        $sql .= " ORDER BY clock_in_time DESC";
        
        $result = $this->db->select($sql, $params);
        
        // Add break information for each entry
        if ($result) {
            foreach ($result as &$entry) {
                $entry['breaks'] = $this->getTimeEntryBreaks($entry['id']);
            }
        }
        
        return $result ?: [];
    }
    
    /**
     * Get breaks for a time entry
     */
    public function getTimeEntryBreaks($timeEntryId) {
        $sql = "SELECT * FROM dbo.TimeBreaks WHERE time_entry_id = ? ORDER BY break_start ASC";
        return $this->db->select($sql, [$timeEntryId]) ?: [];
    }
    
    /**
     * Get break types
     */
    public function getBreakTypes() {
        $sql = "SELECT * FROM dbo.BreakTypes WHERE is_active = 1 ORDER BY name ASC";
        return $this->db->select($sql, []) ?: [];
    }
    
    /**
     * Get daily summary for user
     */
    public function getDailySummary($userId, $date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $sql = "SELECT * FROM dbo.DailyTimeSummary WHERE user_id = ? AND work_date = ?";
        $result = $this->db->select($sql, [$userId, $date]);
        return $result ? $result[0] : null;
    }
    
    /**
     * Get time report for date range
     */
    public function getTimeReport($startDate, $endDate, $userId = null) {
        $sql = "SELECT * FROM dbo.DailyTimeSummary WHERE work_date >= ? AND work_date <= ?";
        $params = [$startDate, $endDate];
        
        if ($userId) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY work_date DESC, username ASC";
        
        return $this->db->select($sql, $params) ?: [];
    }
    
    /**
     * Calculate total minutes between two times
     */
    private function calculateTotalMinutes($startTime, $endTime) {
        $start = new DateTime($startTime);
        $end = new DateTime($endTime);
        return round(($end->getTimestamp() - $start->getTimestamp()) / 60);
    }
    
    /**
     * Get total break minutes for a time entry
     */
    private function getTotalBreakMinutes($timeEntryId) {
        $sql = "SELECT SUM(break_duration_minutes) as total FROM dbo.TimeBreaks WHERE time_entry_id = ? AND break_end IS NOT NULL";
        $result = $this->db->select($sql, [$timeEntryId]);
        return $result ? (int)$result[0]['total'] : 0;
    }
    
    /**
     * End all active breaks for a time entry
     */
    private function endActiveBreaks($timeEntryId) {
        $sql = "SELECT * FROM dbo.TimeBreaks WHERE time_entry_id = ? AND break_end IS NULL";
        $activeBreaks = $this->db->select($sql, [$timeEntryId]);
        
        if ($activeBreaks) {
            $breakEnd = date('Y-m-d H:i:s');
            foreach ($activeBreaks as $break) {
                $breakDuration = $this->calculateTotalMinutes($break['break_start'], $breakEnd);
                $updateSql = "UPDATE dbo.TimeBreaks SET break_end = ?, break_duration_minutes = ? WHERE id = ?";
                $this->db->update($updateSql, [$breakEnd, $breakDuration, $break['id']]);
            }
        }
    }
    
    /**
     * Get elapsed work time (excluding breaks)
     */
    private function getElapsedWorkTime($timeEntry) {
        $currentTime = date('Y-m-d H:i:s');
        $totalMinutes = $this->calculateTotalMinutes($timeEntry['clock_in_time'], $currentTime);
        $breakMinutes = $this->getTotalBreakMinutes($timeEntry['id']);
        
        // Add current break time if on break
        $activeBreak = $this->getActiveBreak($timeEntry['id']);
        if ($activeBreak) {
            $currentBreakMinutes = $this->calculateTotalMinutes($activeBreak['break_start'], $currentTime);
            $breakMinutes += $currentBreakMinutes;
        }
        
        return max(0, $totalMinutes - $breakMinutes);
    }
    
    /**
     * Format minutes to hours and minutes
     */
    public function formatMinutes($minutes) {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }
    
    /**
     * Get team time tracking summary
     */
    public function getTeamSummary($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $sql = "SELECT * FROM dbo.DailyTimeSummary WHERE work_date = ? ORDER BY full_name ASC";
        return $this->db->select($sql, [$date]) ?: [];
    }
    
    /**
     * Get all users with time data for admin view
     */
    public function getAllUsersWithTimeData($startDate, $endDate, $userId = null, $department = null) {
        $sql = "SELECT DISTINCT u.id as user_id, u.username, u.full_name,
                u.email, u.role
                FROM dbo.Users u";
        
        $params = [];
        if ($userId) {
            $sql .= " WHERE u.id = ?";
            $params[] = $userId;
        }
        if ($department) {
            $sql .= $userId ? " AND" : " WHERE";
            $sql .= " u.role = ?";
            $params[] = $department;
        }
        
        $allUsers = $this->db->select($sql, $params);
        
        // For each user, get their time data
        $usersWithTimeData = [];
        foreach ($allUsers as $user) {
            $userStatus = $this->getUserStatus($user['user_id']);
            $todaySummary = $this->getDailySummary($user['user_id']);
            $periodSummary = $this->getPeriodSummary($user['user_id'], $startDate, $endDate);
            $lastActivity = $this->getLastActivity($user['user_id']);
            
            $usersWithTimeData[] = array_merge($user, [
                'current_status' => $userStatus['status'],
                'today_hours' => $todaySummary['total_hours'] ?? 0,
                'period_total_hours' => $periodSummary['total_hours'] ?? 0,
                'total_break_minutes' => $periodSummary['total_break_minutes'] ?? 0,
                'last_activity' => $lastActivity
            ]);
        }
        
        return $usersWithTimeData;
    }
    
    /**
     * Get currently active users
     */
    public function getCurrentlyActiveUsers() {
        $sql = "SELECT u.id as user_id, u.username, u.full_name,
                te.clock_in_time, te.id as time_entry_id,
                CASE 
                    WHEN tb.break_start IS NOT NULL AND tb.break_end IS NULL THEN 'on_break'
                    ELSE 'working'
                END as status,
                DATEDIFF(minute, te.clock_in_time, GETDATE()) as elapsed_minutes,
                tb.break_start
                FROM dbo.Users u
                INNER JOIN dbo.TimeEntries te ON u.id = te.user_id
                LEFT JOIN dbo.TimeBreaks tb ON te.id = tb.time_entry_id AND tb.break_end IS NULL
                WHERE te.status = 'active'
                ORDER BY te.clock_in_time";
        
        return $this->db->select($sql) ?: [];
    }
    
    /**
     * Get overall statistics for admin dashboard
     */
    public function getOverallStatistics($startDate, $endDate) {
        $sql = "SELECT 
                COUNT(DISTINCT te.user_id) as active_users,
                SUM(te.total_hours) as total_hours,
                SUM(te.total_break_minutes) as total_break_minutes,
                AVG(te.total_hours) as avg_hours_per_user,
                COUNT(te.id) as total_entries
                FROM dbo.TimeEntries te
                WHERE CAST(te.clock_in_time AS DATE) BETWEEN ? AND ?
                AND te.status = 'completed'";
        
        $result = $this->db->select($sql, [$startDate, $endDate]);
        return $result ? $result[0] : [
            'active_users' => 0,
            'total_hours' => 0,
            'total_break_minutes' => 0,
            'avg_hours_per_user' => 0,
            'total_entries' => 0
        ];
    }
    
    /**
     * Get recent activity for admin dashboard
     */
    public function getRecentActivity($limit = 20) {
        $sql = "SELECT 
                u.username,
                u.full_name as user_name,
                'clock_in' as action,
                te.clock_in_time as timestamp
                FROM dbo.TimeEntries te
                INNER JOIN dbo.Users u ON te.user_id = u.id
                WHERE te.clock_in_time IS NOT NULL
                
                UNION ALL
                
                SELECT 
                u.username,
                u.full_name as user_name,
                'clock_out' as action,
                te.clock_out_time as timestamp
                FROM dbo.TimeEntries te
                INNER JOIN dbo.Users u ON te.user_id = u.id
                WHERE te.clock_out_time IS NOT NULL
                
                UNION ALL
                
                SELECT 
                u.username,
                u.full_name as user_name,
                'break_start' as action,
                tb.break_start as timestamp
                FROM dbo.TimeBreaks tb
                INNER JOIN dbo.TimeEntries te ON tb.time_entry_id = te.id
                INNER JOIN dbo.Users u ON te.user_id = u.id
                WHERE tb.break_start IS NOT NULL
                
                UNION ALL
                
                SELECT 
                u.username,
                u.full_name as user_name,
                'break_end' as action,
                tb.break_end as timestamp
                FROM dbo.TimeBreaks tb
                INNER JOIN dbo.TimeEntries te ON tb.time_entry_id = te.id
                INNER JOIN dbo.Users u ON te.user_id = u.id
                WHERE tb.break_end IS NOT NULL
                
                ORDER BY timestamp DESC";
        
        $result = $this->db->select($sql);
        return array_slice($result ?: [], 0, $limit);
    }
    
    /**
     * Get departments for filtering (using roles since no department column exists)
     */
    public function getDepartments() {
        $sql = "SELECT DISTINCT role FROM dbo.Users WHERE role IS NOT NULL ORDER BY role";
        $result = $this->db->select($sql);
        return array_column($result ?: [], 'role');
    }
    
    /**
     * Get user detailed summary for a specific date
     */
    public function getUserDetailedSummary($userId, $date) {
        $dailySummary = $this->getDailySummary($userId, $date);
        
        // Get detailed entries for the date
        $sql = "SELECT te.*, 
                COUNT(tb.id) as break_count,
                SUM(tb.break_duration_minutes) as total_break_minutes_detailed
                FROM dbo.TimeEntries te
                LEFT JOIN dbo.TimeBreaks tb ON te.id = tb.time_entry_id
                WHERE te.user_id = ? AND CAST(te.clock_in_time AS DATE) = ?
                GROUP BY te.id, te.user_id, te.clock_in_time, te.clock_out_time, 
                         te.total_hours, te.total_break_minutes, te.status, te.notes, te.created_at, te.updated_at
                ORDER BY te.clock_in_time";
        
        $entries = $this->db->select($sql, [$userId, $date]);
        
        return array_merge($dailySummary ?: [], [
            'entries' => $entries ?: []
        ]);
    }
    
    /**
     * Admin force clock out user
     */
    public function adminClockOut($userId, $adminId, $reason = 'Admin override') {
        try {
            $activeEntry = $this->getActiveTimeEntry($userId);
            if (!$activeEntry) {
                return ['success' => false, 'message' => 'User is not currently clocked in'];
            }
            
            $clockOutTime = date('Y-m-d H:i:s');
            
            // End any active breaks
            $this->endActiveBreaks($activeEntry['id']);
            
            // Calculate total hours
            $totalMinutes = $this->calculateTotalMinutes($activeEntry['clock_in_time'], $clockOutTime);
            $totalBreakMinutes = $this->getTotalBreakMinutes($activeEntry['id']);
            $netMinutes = $totalMinutes - $totalBreakMinutes;
            $totalHours = round($netMinutes / 60, 2);
            
            $notes = ($activeEntry['notes'] ? $activeEntry['notes'] . ' | ' : '') . 'Admin force clock out: ' . $reason;
            $sql = "UPDATE dbo.TimeEntries SET clock_out_time = ?, total_hours = ?, total_break_minutes = ?, status = 'completed', notes = ?, updated_at = ? WHERE id = ?";
            $this->db->update($sql, [$clockOutTime, $totalHours, $totalBreakMinutes, $notes, date('Y-m-d H:i:s'), $activeEntry['id']]);
            
            return [
                'success' => true,
                'message' => 'User clocked out successfully by admin',
                'clock_out_time' => $clockOutTime,
                'total_hours' => $totalHours
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get period summary for user
     */
    private function getPeriodSummary($userId, $startDate, $endDate) {
        $sql = "SELECT 
                SUM(total_hours) as total_hours,
                SUM(total_break_minutes) as total_break_minutes,
                COUNT(*) as total_entries
                FROM dbo.TimeEntries 
                WHERE user_id = ? AND CAST(clock_in_time AS DATE) BETWEEN ? AND ? AND status = 'completed'";
        
        $result = $this->db->select($sql, [$userId, $startDate, $endDate]);
        return $result ? $result[0] : [
            'total_hours' => 0,
            'total_break_minutes' => 0,
            'total_entries' => 0
        ];
    }
    
    /**
     * Get last activity for user
     */
    private function getLastActivity($userId) {
        $sql = "SELECT TOP 1 
                CASE 
                    WHEN clock_out_time IS NOT NULL THEN clock_out_time
                    WHEN clock_in_time IS NOT NULL THEN clock_in_time
                    ELSE created_at
                END as last_activity
                FROM dbo.TimeEntries 
                WHERE user_id = ? 
                ORDER BY created_at DESC";
        
        $result = $this->db->select($sql, [$userId]);
        return $result ? $result[0]['last_activity'] : null;
    }
    
    /**
     * Get analytics data for admin
     */
    public function getAnalyticsData($period = 'month', $department = null) {
        // This would contain complex analytics queries
        // For now, return basic structure
        return [
            'period' => $period,
            'department' => $department,
            'productivity_trends' => [],
            'user_comparisons' => [],
            'break_patterns' => [],
            'peak_hours' => []
        ];
    }
}
?> 