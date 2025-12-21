<?php

class Note {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }
	
	private function ensureNoteColumns() {
		$columns = [
			'tags' => "NVARCHAR(255) NULL",
			'content_html' => "NVARCHAR(MAX) NULL"
		];
		
		foreach ($columns as $column => $definition) {
			$sql = "
			IF COL_LENGTH('dbo.Notes', '{$column}') IS NULL
			BEGIN
				ALTER TABLE [dbo].[Notes] ADD [{$column}] {$definition};
			END";
			try {
				$this->db->query($sql);
			} catch (Exception $e) {
				error_log('ensureNoteColumn ' . $column . ' error: ' . $e->getMessage());
			}
		}
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
			$this->ensureNoteColumns();
            
            // For personal notes, ensure reference_id is null
            if ($data['type'] === 'personal') {
                $data['reference_id'] = null;
            }
			
			$data['tags'] = isset($data['tags']) && $data['tags'] !== '' ? $data['tags'] : null;
            
            // Validate reference_id based on type
            if (in_array($data['type'], ['project', 'task', 'client']) && empty($data['reference_id'])) {
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
            $contentHtml = $data['content_html'] ?? null;
			$query = "INSERT INTO Notes (title, content, content_html, type, reference_id, created_by, tags) 
			         VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            // Fix the error logging to properly show all parameters
			error_log("Note::create - Executing query with parameters: " . json_encode([
				'title' => $data['title'], 
				'content' => (strlen($data['content']) > 30) ? substr($data['content'], 0, 30) . '...' : $data['content'],
				'content_html' => is_string($contentHtml) ? ((strlen($contentHtml) > 30) ? substr($contentHtml, 0, 30) . '...' : $contentHtml) : null,
				'type' => $data['type'], 
				'reference_id' => $data['reference_id'], 
				'created_by' => $data['created_by'],
				'tags' => $data['tags']
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
                if ($contentHtml === null || $contentHtml === '') {
                    $stmt->bindValue(3, null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(3, $contentHtml, PDO::PARAM_STR);
                }
                $stmt->bindParam(4, $data['type'], PDO::PARAM_STR);
                
                // Handle reference_id properly for NULL values
                if ($data['reference_id'] === null) {
                    $stmt->bindValue(5, null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindParam(5, $data['reference_id'], PDO::PARAM_INT);
                }
                
				$stmt->bindParam(6, $data['created_by'], PDO::PARAM_INT);
				if ($data['tags'] === null || $data['tags'] === '') {
					$stmt->bindValue(7, null, PDO::PARAM_NULL);
				} else {
					$stmt->bindParam(7, $data['tags'], PDO::PARAM_STR);
				}
                
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
						$contentHtml,
						$data['type'],
						$data['reference_id'],  // Will be null for personal notes
						$data['created_by'],
						$data['tags']
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
     * Get notes by type/reference that the given user can see (owner or shared)
     */
    public function getNotesByReferenceForUser(string $type, int $referenceId, int $userId): array {
        try {
            $query = "SELECT DISTINCT n.*, u.username as created_by_name
                     FROM Notes n
                     LEFT JOIN Users u ON n.created_by = u.id
                     LEFT JOIN note_shares ns ON ns.note_id = n.id AND ns.shared_with_user_id = ?
                     WHERE n.type = ? AND n.reference_id = ?
                       AND (n.created_by = ? OR ns.shared_with_user_id = ?)
                     ORDER BY n.created_at DESC";
            $params = [$userId, $type, $referenceId, $userId, $userId];
            return $this->db->select($query, $params) ?: [];
        } catch (Exception $e) {
            error_log('Get Notes By Reference For User Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent notes by type/reference (no permission filter)
     */
    public function getRecentByReference(string $type, int $referenceId, int $limit = 10): array {
        try {
            $top = $limit > 0 ? "TOP {$limit} " : '';
            $query = "SELECT {$top} n.*, u.username as created_by_name
                     FROM Notes n
                     LEFT JOIN Users u ON n.created_by = u.id
                     WHERE n.type = ? AND n.reference_id = ?
                     ORDER BY n.created_at DESC";
            return $this->db->select($query, [$type, $referenceId]) ?: [];
        } catch (Exception $e) {
            error_log('Get Recent Notes By Reference Error: ' . $e->getMessage());
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
            $query = "SELECT DISTINCT n.*, u.username as created_by_name,
                     CASE 
                         WHEN n.type = 'project' THEN p.title 
                         WHEN n.type = 'task' THEN t.title 
                         WHEN n.type = 'client' THEN c.name
                     END as reference_title,
                     CASE
                         WHEN n.created_by = ? THEN 'owner'
                         WHEN ns.permission = 'edit' THEN 'editor'
                         ELSE 'viewer'
                     END as access_level,
                     ns.shared_at,
                     su.username as shared_by_name
                     FROM Notes n
                     LEFT JOIN Users u ON n.created_by = u.id
                     LEFT JOIN Projects p ON n.type = 'project' AND n.reference_id = p.id
                     LEFT JOIN Tasks t ON n.type = 'task' AND n.reference_id = t.id
                     LEFT JOIN Clients c ON n.type = 'client' AND n.reference_id = c.id
                     LEFT JOIN note_shares ns ON n.id = ns.note_id AND ns.shared_with_user_id = ?
                     LEFT JOIN Users su ON ns.shared_by_user_id = su.id
                     WHERE n.created_by = ? OR ns.shared_with_user_id = ?
                     ORDER BY n.created_at DESC";
            
            $result = $this->db->select($query, [
                $userId, // for owner check in CASE
                $userId, // for note_shares join
                $userId, // for created_by WHERE
                $userId  // for shared_with WHERE
            ]);
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
			$this->ensureNoteColumns();
			$query = "UPDATE Notes SET 
			         title = ?, 
			         content = ?,
			         content_html = ?,
			         tags = ?,
			         updated_at = GETDATE()
			         WHERE id = ? AND created_by = ?";
			
			$this->db->update($query, [
				$data['title'],
				$data['content'],
				$data['content_html'] ?? null,
			    $data['tags'] ?? null,
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
     * This method returns ALL matching notes - permission filtering happens in the controller
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
     * Search notes by title or content with user permission filtering
     * More secure method that filters at the database level
     */
    public function searchNotesSecure($searchQuery, $userId, $hasFullAccess = false, $limit = 10) {
        try {
            if ($hasFullAccess) {
                // Admin/Manager can see all notes
                $query = "SELECT n.*, u.username as author_name
                         FROM [Notes] n
                         LEFT JOIN [Users] u ON n.created_by = u.id
                         WHERE (n.title LIKE ? OR n.content LIKE ?)
                         ORDER BY n.created_at DESC";
                
                $params = [$searchQuery, $searchQuery];
            } else {
                // Regular users: only their own notes for now (simplified)
                $query = "SELECT n.*, u.username as author_name
                         FROM [Notes] n
                         LEFT JOIN [Users] u ON n.created_by = u.id
                         WHERE (n.title LIKE ? OR n.content LIKE ?)
                           AND n.created_by = ?
                         ORDER BY n.created_at DESC";
                
                $params = [$searchQuery, $searchQuery, $userId];
            }
            
            // Apply limit if specified
            if ($limit > 0) {
                $query = str_replace("SELECT n.*", "SELECT TOP $limit n.*", $query);
            }
            
            $results = $this->db->select($query, $params);
            return $results ?: [];
        } catch (Exception $e) {
            error_log('Note secure search error: ' . $e->getMessage());
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
            
			$this->ensureNoteColumns();
			return true;
        } catch (Exception $e) {
            error_log('Create Notes Table Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Share a note with another user
     */
    public function shareNote(int $noteId, int $ownerId, int $shareWithUserId, string $permission = 'view'): bool {
        try {

            // First verify the user owns the note
            $checkQuery = "SELECT id, created_by FROM Notes WHERE id = ?";
            $result = $this->db->select($checkQuery, [$noteId]);
            
            if (empty($result)) {
                return false;
            }
            
            $actualOwner = $result[0]['created_by'];
            if ($actualOwner != $ownerId) {
                return false;
            }
            
            // Check if share already exists
            $existsQuery = "SELECT id FROM note_shares WHERE note_id = ? AND shared_with_user_id = ?";
            $existing = $this->db->select($existsQuery, [$noteId, $shareWithUserId]);
            
            if (!empty($existing)) {
                // Update existing share
                $updateQuery = "UPDATE note_shares 
                               SET permission = ?, shared_by_user_id = ?, shared_at = GETDATE()
                               WHERE note_id = ? AND shared_with_user_id = ?";
                $this->db->update($updateQuery, [$permission, $ownerId, $noteId, $shareWithUserId]);
            } else {
                // Insert new share
                $insertQuery = "INSERT INTO note_shares (note_id, shared_with_user_id, shared_by_user_id, permission)
                               VALUES (?, ?, ?, ?)";
                $this->db->insert($insertQuery, [$noteId, $shareWithUserId, $ownerId, $permission]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Share Note Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove sharing for a note
     */
    public function unshareNote(int $noteId, int $ownerId, int $unshareUserId): bool {
        try {
            // Verify ownership first
            $checkQuery = "SELECT id FROM Notes WHERE id = ? AND created_by = ?";
            $result = $this->db->select($checkQuery, [$noteId, $ownerId]);
            
            if (empty($result)) {
                error_log('Unshare Note Error: User does not own this note');
                return false;
            }
            
            $query = "DELETE FROM note_shares WHERE note_id = ? AND shared_with_user_id = ?";
            $this->db->remove($query, [$noteId, $unshareUserId]);
            
            return true;
        } catch (Exception $e) {
            error_log('Unshare Note Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get users who have access to a note
     */
    public function getSharedUsers(int $noteId): array {
        try {
            $query = "SELECT ns.*, u.username, u.name, u.email
                     FROM note_shares ns
                     INNER JOIN Users u ON ns.shared_with_user_id = u.id
                     WHERE ns.note_id = ?
                     ORDER BY ns.shared_at DESC";
            
            $result = $this->db->select($query, [$noteId]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log('Get Shared Users Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if a user has access to a note
     */
    public function hasAccess(int $noteId, int $userId, string $requiredPermission = 'view'): bool {
        try {
            // Get note details first
            $noteQuery = "SELECT id, type, reference_id, created_by FROM Notes WHERE id = ?";
            $noteResult = $this->db->select($noteQuery, [$noteId]);
            
            if (empty($noteResult)) {
                return false; // Note doesn't exist
            }
            
            $note = $noteResult[0];
            
            // Check if user owns the note
            if ($note['created_by'] == $userId) {
                return true; // Owner has full access
            }
            
            // Check if note is explicitly shared with user
            $shareQuery = "SELECT permission FROM note_shares WHERE note_id = ? AND shared_with_user_id = ?";
            $shareResult = $this->db->select($shareQuery, [$noteId, $userId]);
            
            if (!empty($shareResult)) {
                $permission = $shareResult[0]['permission'];
                if ($requiredPermission === 'view') {
                    return true; // Any share grants view access
                }
                return $permission === 'edit'; // Edit permission required
            }
            
            // For view permission only, check project/task access
            if ($requiredPermission === 'view') {
                // Check if user has access to the project
                if ($note['type'] === 'project' && $note['reference_id']) {
                    $projectQuery = "SELECT 1 FROM project_team_members 
                                   WHERE project_id = ? AND user_id = ?";
                    $projectResult = $this->db->select($projectQuery, [$note['reference_id'], $userId]);
                    if (!empty($projectResult)) {
                        return true;
                    }
                }
                
                // Check if user has access to the task
                if ($note['type'] === 'task' && $note['reference_id']) {
                    $taskQuery = "SELECT 1 FROM Tasks 
                                WHERE id = ? AND (assigned_to = ? OR created_by = ?)";
                    $taskResult = $this->db->select($taskQuery, [$note['reference_id'], $userId, $userId]);
                    if (!empty($taskResult)) {
                        return true;
                    }
                }
            }
            
            return false;
        } catch (Exception $e) {
            error_log('Check Note Access Error: ' . $e->getMessage());
            return false;
        }
    }
} 
