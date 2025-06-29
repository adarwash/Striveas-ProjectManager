<?php
/**
 * ActivityLog Model
 * Handles all activity logging and retrieval functionality
 */
class ActivityLog {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
        // Don't create the table in the constructor
        // The table should be created via SQL script separately
    }

    /**
     * Create activity_logs table if it doesn't exist
     * 
     * @return bool True if successful or table already exists, false on error
     */
    public function createActivityLogsTable() {
        try {
            // First check if the table already exists
            $checkTableQuery = "SELECT COUNT(*) AS table_exists FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'activity_logs'";
            $result = $this->db->select($checkTableQuery);
            
            // If table already exists, return true
            if (!empty($result) && isset($result[0]['table_exists']) && $result[0]['table_exists'] > 0) {
                return true;
            }

            // If not, execute the create table SQL file
            $sqlFile = file_get_contents(APPROOT . '/../sql/create_activity_logs_table.sql');
            if ($sqlFile === false) {
                error_log('Error: Unable to read create_activity_logs_table.sql file');
                return false;
            }

            // Execute the SQL script
            $this->db->query($sqlFile);
            return true;
        } catch (Exception $e) {
            error_log('Error creating activity_logs table: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log an activity
     * 
     * @param int $userId User ID who performed the action
     * @param string $entityType Type of entity (project, task, document, etc.)
     * @param int $entityId ID of the entity
     * @param string $action Action performed (created, updated, deleted, etc.)
     * @param string $description Optional description of the activity
     * @param array $metadata Optional additional data as an array (will be stored as JSON)
     * @return int|bool The ID of the new record or false on failure
     */
    public function log($userId, $entityType, $entityId, $action, $description = null, $metadata = null) {
        try {
            // Ensure the table exists
            $this->createActivityLogsTable();
            
            // Convert metadata to JSON if provided
            $metadataJson = null;
            if ($metadata !== null) {
                $metadataJson = json_encode($metadata);
            }
            
            // Get the user's IP address
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            
            $query = "INSERT INTO activity_logs (user_id, entity_type, entity_id, action, description, metadata, ip_address) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            return $this->db->insert($query, [
                $userId,
                $entityType,
                $entityId,
                $action,
                $description,
                $metadataJson,
                $ipAddress
            ]);
        } catch (Exception $e) {
            error_log('Error logging activity: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get activities for a specific entity
     * 
     * @param string $entityType Type of entity (project, task, document, etc.)
     * @param int $entityId ID of the entity
     * @param int $limit Maximum number of records to return (0 for all)
     * @param int $offset Offset for pagination
     * @return array Array of activity records
     */
    public function getActivitiesByEntity($entityType, $entityId, $limit = 0, $offset = 0) {
        try {
            $query = "SELECT a.*, u.username as username, u.full_name as user_full_name
                     FROM activity_logs a
                     LEFT JOIN users u ON a.user_id = u.id
                     WHERE a.entity_type = ? AND a.entity_id = ?
                     ORDER BY a.created_at DESC";
            
            $params = [$entityType, $entityId];
            
            // Add LIMIT and OFFSET for SQL Server
            if ($limit > 0) {
                if ($offset > 0) {
                    // For SQL Server, use OFFSET-FETCH
                    $query .= " OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
                    $params[] = $offset;
                    $params[] = $limit;
                } else {
                    // For SQL Server, when no offset is needed
                    $query = str_replace("SELECT", "SELECT TOP " . $limit, $query);
                }
            }
            
            return $this->db->select($query, $params);
        } catch (Exception $e) {
            error_log('Error getting activities by entity: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get activities for a specific user
     * 
     * @param int $userId User ID
     * @param int $limit Maximum number of records to return (0 for all)
     * @param int $offset Offset for pagination
     * @return array Array of activity records
     */
    public function getActivitiesByUser($userId, $limit = 0, $offset = 0) {
        try {
            $query = "SELECT a.*, u.username as username, u.full_name as user_full_name
                     FROM activity_logs a
                     LEFT JOIN users u ON a.user_id = u.id
                     WHERE a.user_id = ?
                     ORDER BY a.created_at DESC";
            
            $params = [$userId];
            
            // Add LIMIT and OFFSET for SQL Server
            if ($limit > 0) {
                if ($offset > 0) {
                    // For SQL Server, use OFFSET-FETCH
                    $query .= " OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
                    $params[] = $offset;
                    $params[] = $limit;
                } else {
                    // For SQL Server, when no offset is needed
                    $query = str_replace("SELECT", "SELECT TOP " . $limit, $query);
                }
            }
            
            return $this->db->select($query, $params);
        } catch (Exception $e) {
            error_log('Error getting activities by user: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get activities with filtering options
     * 
     * @param array $filters Associative array of filters
     * @param int $limit Maximum number of records to return (0 for all)
     * @param int $offset Offset for pagination
     * @return array Array of activity records
     */
    public function getActivitiesWithFilters($filters = [], $limit = 0, $offset = 0) {
        try {
            $query = "SELECT a.*, u.username as username, u.full_name as user_full_name
                     FROM activity_logs a
                     LEFT JOIN users u ON a.user_id = u.id
                     WHERE 1=1";
            
            $params = [];
            
            // Apply filters
            if (!empty($filters['entity_type'])) {
                $query .= " AND a.entity_type = ?";
                $params[] = $filters['entity_type'];
            }
            
            if (!empty($filters['entity_id'])) {
                $query .= " AND a.entity_id = ?";
                $params[] = $filters['entity_id'];
            }
            
            if (!empty($filters['user_id'])) {
                $query .= " AND a.user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            if (!empty($filters['action'])) {
                $query .= " AND a.action = ?";
                $params[] = $filters['action'];
            }
            
            if (!empty($filters['start_date'])) {
                $query .= " AND a.created_at >= ?";
                $params[] = $filters['start_date'] . ' 00:00:00';
            }
            
            if (!empty($filters['end_date'])) {
                $query .= " AND a.created_at <= ?";
                $params[] = $filters['end_date'] . ' 23:59:59';
            }
            
            $query .= " ORDER BY a.created_at DESC";
            
            // Add LIMIT and OFFSET for SQL Server
            if ($limit > 0) {
                if ($offset > 0) {
                    // For SQL Server, use OFFSET-FETCH
                    $query .= " OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
                    $params[] = $offset;
                    $params[] = $limit;
                } else {
                    // For SQL Server, when no offset is needed
                    $query = str_replace("SELECT", "SELECT TOP " . $limit, $query);
                }
            }
            
            return $this->db->select($query, $params);
        } catch (Exception $e) {
            error_log('Error getting activities with filters: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Format activity data for display with appropriate context
     * 
     * @param array $activity The activity log entry
     * @return array Formatted activity with additional context
     */
    public function formatActivityForDisplay($activity) {
        // Start with the base activity
        $formatted = $activity;
        
        // Add user display information
        $formatted['user_display_name'] = !empty($activity['user_full_name']) 
            ? $activity['user_full_name'] 
            : $activity['username'];
        
        // Parse metadata if it exists
        if (!empty($activity['metadata'])) {
            $formatted['metadata_obj'] = json_decode($activity['metadata'], true);
        }
        
        // Create a human-readable action description
        $actionVerb = $this->getActionVerb($activity['action']);
        $entityName = $this->getEntityName($activity['entity_type'], $activity['entity_id']);
        
        $formatted['action_text'] = sprintf(
            "%s %s %s", 
            $formatted['user_display_name'],
            $actionVerb,
            $entityName
        );
        
        // Add description if available
        if (!empty($activity['description'])) {
            $formatted['action_text'] .= ": " . $activity['description'];
        }
        
        return $formatted;
    }

    /**
     * Get human-readable verb for an action
     * 
     * @param string $action The action code
     * @return string Human-readable verb
     */
    private function getActionVerb($action) {
        $verbs = [
            'created' => 'created',
            'updated' => 'updated',
            'deleted' => 'deleted',
            'completed' => 'marked as complete',
            'assigned' => 'assigned',
            'unassigned' => 'unassigned',
            'commented' => 'commented on',
            'uploaded' => 'uploaded',
            'downloaded' => 'downloaded',
            'linked' => 'linked',
            'unlinked' => 'unlinked'
        ];
        
        return $verbs[$action] ?? $action;
    }

    /**
     * Get entity name for display
     * 
     * @param string $entityType Type of entity
     * @param int $entityId ID of the entity
     * @return string Entity name or default description
     */
    private function getEntityName($entityType, $entityId) {
        try {
            switch ($entityType) {
                case 'project':
                    $project = $this->db->select("SELECT title FROM projects WHERE id = ?", [$entityId]);
                    return !empty($project) ? "project '{$project[0]['title']}'" : "a project";
                
                case 'task':
                    $task = $this->db->select("SELECT title FROM tasks WHERE id = ?", [$entityId]);
                    return !empty($task) ? "task '{$task[0]['title']}'" : "a task";
                
                case 'document':
                    $doc = $this->db->select("SELECT file_name FROM project_documents WHERE id = ?", [$entityId]);
                    return !empty($doc) ? "document '{$doc[0]['file_name']}'" : "a document";
                
                default:
                    return "a {$entityType}";
            }
        } catch (Exception $e) {
            error_log('Error getting entity name: ' . $e->getMessage());
            return "a {$entityType}";
        }
    }
} 