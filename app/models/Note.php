<?php

class Note {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }
    
    /**
     * Create a new note
     *
     * @param array $data Note data
     * @return int|bool The new note ID if successful, false otherwise
     */
    public function create(array $data): int|bool {
        try {
            $query = "INSERT INTO Notes (title, content, type, reference_id, created_by) 
                     VALUES (?, ?, ?, ?, ?)";
            
            $result = $this->db->insert($query, [
                $data['title'],
                $data['content'],
                $data['type'],
                $data['reference_id'],
                $data['created_by']
            ]);
            
            return $result !== null ? $result : false;
        } catch (Exception $e) {
            error_log('Create Note Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get a note by ID
     *
     * @param int $id Note ID
     * @return array|bool Note data if found, false otherwise
     */
    public function getNoteById(int $id): array|bool {
        try {
            $query = "SELECT n.*, u.username as created_by_name 
                     FROM Notes n
                     LEFT JOIN Users u ON n.created_by = u.id
                     WHERE n.id = ?";
            
            $result = $this->db->select($query, [$id]);
            return !empty($result) ? $result[0] : false;
        } catch (Exception $e) {
            error_log('Get Note Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get notes by type and reference ID
     *
     * @param string $type Note type ('project' or 'task')
     * @param int $referenceId Project or Task ID
     * @return array Array of notes
     */
    public function getNotesByReference(string $type, int $referenceId): array {
        try {
            $query = "SELECT n.*, u.username as created_by_name 
                     FROM Notes n
                     LEFT JOIN Users u ON n.created_by = u.id
                     WHERE n.type = ? AND n.reference_id = ?
                     ORDER BY n.created_at DESC";
            
            $result = $this->db->select($query, [$type, $referenceId]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log('Get Notes Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all notes for a user
     *
     * @param int $userId User ID
     * @return array Array of notes
     */
    public function getNotesByUser(int $userId): array {
        try {
            $query = "SELECT n.*, u.username as created_by_name,
                     CASE 
                         WHEN n.type = 'project' THEN p.title 
                         WHEN n.type = 'task' THEN t.title 
                     END as reference_title
                     FROM Notes n
                     LEFT JOIN Users u ON n.created_by = u.id
                     LEFT JOIN Projects p ON n.type = 'project' AND n.reference_id = p.id
                     LEFT JOIN Tasks t ON n.type = 'task' AND n.reference_id = t.id
                     WHERE n.created_by = ?
                     ORDER BY n.created_at DESC";
            
            $result = $this->db->select($query, [$userId]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log('Get User Notes Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update a note
     *
     * @param array $data Note data
     * @return bool True if successful, false otherwise
     */
    public function update(array $data): bool {
        try {
            $query = "UPDATE Notes SET 
                     title = ?, 
                     content = ?,
                     updated_at = GETDATE()
                     WHERE id = ? AND created_by = ?";
            
            $this->db->update($query, [
                $data['title'],
                $data['content'],
                $data['id'],
                $data['created_by']
            ]);
            return true;
        } catch (Exception $e) {
            error_log('Update Note Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a note
     *
     * @param int $id Note ID
     * @param int $userId User ID (for security)
     * @return bool True if successful, false otherwise
     */
    public function delete(int $id, int $userId): bool {
        try {
            $query = "DELETE FROM Notes WHERE id = ? AND created_by = ?";
            $this->db->remove($query, [$id, $userId]);
            return true;
        } catch (Exception $e) {
            error_log('Delete Note Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create the Notes table if it doesn't exist
     *
     * @return bool True if successful, false otherwise
     */
    public function createNotesTable(): bool {
        try {
            $sql = file_get_contents('../app/sql/create_notes_table.sql');
            
            if (!$sql) {
                error_log('Could not read create_notes_table.sql file');
                return false;
            }
            
            $this->db->query($sql);
            return true;
        } catch (Exception $e) {
            error_log('Create Notes Table Error: ' . $e->getMessage());
            return false;
        }
    }
} 