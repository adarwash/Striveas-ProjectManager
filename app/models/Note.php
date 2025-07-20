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
     * @return int|bool Note ID if successful, false otherwise
     */
    public function create(array $data): int|bool {
        try {
            // Ensure the Notes table exists
            $this->createNotesTable();
            
            // For personal notes, ensure reference_id is null
            if ($data['type'] === 'personal') {
                $data['reference_id'] = null;
            }
            
            // Validate reference_id based on type
            if (in_array($data['type'], ['project', 'task']) && empty($data['reference_id'])) {
                error_log("Note::create - Error: reference_id is required for type: " . $data['type']);
                return false;
            }
            
            // Add detailed logging
            error_log("Note::create - Preparing to insert note with data: " . json_encode([
                'title' => $data['title'],
                'content' => strlen($data['content']) > 100 ? substr($data['content'], 0, 100) . '...' : $data['content'],
                'type' => $data['type'],
                'reference_id' => $data['reference_id'],
                'created_by' => $data['created_by']
            ]));
            
            // Prepare the query with proper parameter handling
            $query = "INSERT INTO Notes (title, content, type, reference_id, created_by) 
                     VALUES (?, ?, ?, ?, ?)";
            
            // Fix the error logging to properly show all parameters
            error_log("Note::create - Executing query with parameters: " . json_encode([
                'title' => $data['title'], 
                'content' => (strlen($data['content']) > 30) ? substr($data['content'], 0, 30) . '...' : $data['content'],
                'type' => $data['type'], 
                'reference_id' => $data['reference_id'], 
                'created_by' => $data['created_by']
            ]));
            
            // Handle the insertion manually to ensure proper NULL handling for SQL Server
            try {
                // Get a direct connection to the database
                $pdo = new PDO(
                    "sqlsrv:Server=" . DB1['host'] . ";Database=" . DB1['dbname'] . ";TrustServerCertificate=true", 
                    DB1['user'], 
                    DB1['pass']
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare($query);
                
                // Bind parameters individually to handle NULL values correctly in SQL Server
                $stmt->bindParam(1, $data['title'], PDO::PARAM_STR);
                $stmt->bindParam(2, $data['content'], PDO::PARAM_STR);
                $stmt->bindParam(3, $data['type'], PDO::PARAM_STR);
                
                // Handle reference_id properly for NULL values
                if ($data['reference_id'] === null) {
                    $stmt->bindValue(4, null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindParam(4, $data['reference_id'], PDO::PARAM_INT);
                }
                
                $stmt->bindParam(5, $data['created_by'], PDO::PARAM_INT);
                
                // Execute the statement
                $result = $stmt->execute();
                error_log("Note::create - Statement execution result: " . ($result ? "true" : "false"));
                
                if ($result) {
                    // Try multiple methods to retrieve the last insert ID for SQL Server
                    error_log("Note::create - Trying SCOPE_IDENTITY()");
                    $idStmt = $pdo->query("SELECT SCOPE_IDENTITY() as id");
                    $row = $idStmt->fetch(PDO::FETCH_ASSOC);
                    error_log("Note::create - SCOPE_IDENTITY() result: " . json_encode($row));
                    
                    // Try an alternative approach with @@IDENTITY
                    $altIdStmt = $pdo->query("SELECT @@IDENTITY as id");
                    $altRow = $altIdStmt->fetch(PDO::FETCH_ASSOC);
                    error_log("Note::create - @@IDENTITY result: " . json_encode($altRow));
                    
                    // Try yet another approach to get the last insert ID
                    try {
                        $lastId = $pdo->lastInsertId();
                        error_log("Note::create - PDO lastInsertId() result: " . ($lastId ?: "empty"));
                    } catch (Exception $e) {
                        error_log("Note::create - PDO lastInsertId() error: " . $e->getMessage());
                    }
                    
                    // Make sure we have a valid ID to return
                    if ($row && isset($row['id']) && !empty($row['id'])) {
                        $id = (int)$row['id'];
                        error_log("Note::create - Successfully created note with ID: " . $id);
                        return $id;
                    } 
                    // Try the alternative ID if the primary method failed
                    else if ($altRow && isset($altRow['id']) && !empty($altRow['id'])) {
                        $id = (int)$altRow['id'];
                        error_log("Note::create - Successfully created note with @@IDENTITY ID: " . $id);
                        return $id;
                    }
                    // Fallback to PDO's lastInsertId if that's available
                    else if (!empty($lastId)) {
                        $id = (int)$lastId;
                        error_log("Note::create - Successfully created note with lastInsertId: " . $id);
                        return $id;
                    }
                    else {
                        // Log the issue but return a boolean instead of null
                        error_log("Note::create - Insertion successful but could not retrieve ID through any method");
                        
                        // Try a query to get the most recently created note
                        try {
                            $retrieveStmt = $pdo->prepare("SELECT TOP 1 id FROM Notes WHERE title = ? AND type = ? AND created_by = ? ORDER BY created_at DESC");
                            $retrieveStmt->execute([$data['title'], $data['type'], $data['created_by']]);
                            $retrieveRow = $retrieveStmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($retrieveRow && isset($retrieveRow['id'])) {
                                $id = (int)$retrieveRow['id'];
                                error_log("Note::create - Retrieved note ID through query: " . $id);
                                return $id;
                            }
                        } catch (Exception $ex) {
                            error_log("Note::create - Error trying to retrieve note: " . $ex->getMessage());
                        }
                        
                        return true; // Return true since note was created even if we couldn't get the ID
                    }
                } else {
                    error_log("Note::create - Failed to execute the insert query");
                    return false;
                }
            } catch (PDOException $e) {
                error_log("Note::create - PDO Exception: " . $e->getMessage());
                error_log("Note::create - SQL State: " . $e->getCode());
                
                // Try again using the normal approach in case the direct approach fails
                try {
                    // Execute the insert query using the EasySQL class
                    $result = $this->db->insert($query, [
                        $data['title'],
                        $data['content'],
                        $data['type'],
                        $data['reference_id'],  // Will be null for personal notes
                        $data['created_by']
                    ]);
                    
                    if ($result !== null) {
                        error_log("Note::create - Successfully created note with ID: " . $result);
                        return $result;
                    }
                } catch (Exception $innerEx) {
                    error_log("Note::create - Inner exception: " . $innerEx->getMessage());
                }
                
                return false;
            }
        } catch (Exception $e) {
            error_log('Create Note Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
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
     * Search notes by title or content
     */
    public function searchNotes($searchQuery, $limit = 10) {
        try {
            $query = "SELECT n.*, u.username as author_name
                     FROM [Notes] n
                     LEFT JOIN [Users] u ON n.created_by = u.id
                     WHERE (n.title LIKE ? OR n.content LIKE ?)
                     ORDER BY 
                         CASE 
                             WHEN n.title LIKE ? THEN 1
                             WHEN n.content LIKE ? THEN 2
                             ELSE 3
                         END,
                         n.created_at DESC";
            
            $params = [
                $searchQuery, $searchQuery,
                $searchQuery, $searchQuery
            ];
            
            // SQL Server uses TOP instead of LIMIT
            if ($limit > 0) {
                $query = str_replace("SELECT n.*", "SELECT TOP $limit n.*", $query);
            }
            
            $results = $this->db->select($query, $params);
            return $results ?: [];
        } catch (Exception $e) {
            error_log('Note search error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create the Notes table if it doesn't exist
     * 
     * @return bool True if successful, false otherwise
     */
    public function createNotesTable(): bool {
        try {
            // First check if table exists
            $tableExists = false;
            
            try {
                $checkQuery = "SELECT COUNT(*) as table_count FROM INFORMATION_SCHEMA.TABLES 
                              WHERE TABLE_NAME = 'Notes'";
                $result = $this->db->select($checkQuery);
                $tableExists = ($result && isset($result[0]['table_count']) && $result[0]['table_count'] > 0);
                
                error_log('Notes table exists check: ' . ($tableExists ? 'Yes' : 'No'));
            } catch (Exception $e) {
                error_log('Error checking for Notes table: ' . $e->getMessage());
                // Continue with creation attempt
            }
            
            if (!$tableExists) {
                // Create table from scratch
                $sql = "
                IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[Notes]') AND type in (N'U'))
                BEGIN
                    CREATE TABLE [dbo].[Notes] (
                        [id] INT IDENTITY(1,1) PRIMARY KEY,
                        [title] NVARCHAR(200) NOT NULL,
                        [content] NVARCHAR(MAX) NOT NULL,
                        [type] NVARCHAR(50) NOT NULL, -- 'project', 'task', or 'personal'
                        [reference_id] INT NULL,  -- project_id or task_id, NULL for personal notes
                        [created_by] INT NOT NULL,
                        [created_at] DATETIME DEFAULT GETDATE(),
                        [updated_at] DATETIME DEFAULT GETDATE()
                    )
                END";
                
                $this->db->query($sql);
                error_log('Notes table created successfully');
                
                // Add foreign key constraint in a separate statement to avoid errors
                try {
                    $fkSql = "
                    IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = 'FK_Notes_Users')
                    BEGIN
                        ALTER TABLE [dbo].[Notes] ADD CONSTRAINT [FK_Notes_Users] 
                        FOREIGN KEY ([created_by]) REFERENCES [Users]([id]) ON DELETE CASCADE
                    END";
                    
                    $this->db->query($fkSql);
                    error_log('Notes table foreign key added successfully');
                } catch (Exception $e) {
                    error_log('Error adding foreign key constraint to Notes table: ' . $e->getMessage());
                    // Continue anyway, table should still be usable
                }
                
                // Add indexes for better performance
                try {
                    $indexSql = "
                    IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_Notes_Type_Reference' AND object_id = OBJECT_ID('Notes'))
                    BEGIN
                        CREATE INDEX [IX_Notes_Type_Reference] ON [Notes]([type], [reference_id])
                    END";
                    
                    $this->db->query($indexSql);
                    error_log('Notes table index added successfully');
                } catch (Exception $e) {
                    error_log('Error adding index to Notes table: ' . $e->getMessage());
                    // Continue anyway, table should still be usable
                }
            } else {
                // Check if reference_id column needs to be updated to allow NULL
                try {
                    $columnQuery = "SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS 
                                   WHERE TABLE_NAME = 'Notes' AND COLUMN_NAME = 'reference_id'";
                    $result = $this->db->select($columnQuery);
                    
                    if ($result && isset($result[0]['IS_NULLABLE']) && $result[0]['IS_NULLABLE'] === 'NO') {
                        // Need to alter the column to allow NULL
                        $alterSql = "ALTER TABLE Notes ALTER COLUMN reference_id INT NULL";
                        $this->db->query($alterSql);
                        error_log('Notes table altered to allow NULL reference_id');
                    }
                } catch (Exception $e) {
                    error_log('Error checking/updating Notes table structure: ' . $e->getMessage());
                    // Continue anyway
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Create Notes Table Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }
} 