<?php

/**
 * Email Attachment Model
 * Handles database operations for email attachments
 */
class EmailAttachment {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }
    
    /**
     * Get all attachments for an email
     */
    public function getByEmailId($emailInboxId) {
        try {
            $query = "SELECT * FROM EmailAttachments 
                     WHERE email_inbox_id = :email_id 
                     ORDER BY filename ASC";
            
            return $this->db->select($query, ['email_id' => $emailInboxId]);
        } catch (Exception $e) {
            error_log('EmailAttachment GetByEmailId Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get attachment by ID
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM EmailAttachments WHERE id = :id";
            $result = $this->db->select($query, ['id' => $id]);
            return !empty($result) ? $result[0] : false;
        } catch (Exception $e) {
            error_log('EmailAttachment GetById Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get attachment by EmailInbox ID and Microsoft attachment ID (upsert helper)
     */
    public function getByEmailAndMsId($emailInboxId, $msAttachmentId) {
        try {
            $query = "SELECT TOP 1 * FROM EmailAttachments 
                      WHERE email_inbox_id = :email_id AND ms_attachment_id = :ms_id 
                      ORDER BY id DESC";
            $result = $this->db->select($query, ['email_id' => $emailInboxId, 'ms_id' => $msAttachmentId]);
            return !empty($result) ? $result[0] : false;
        } catch (Exception $e) {
            error_log('EmailAttachment getByEmailAndMsId Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new attachment record
     */
    public function create($data) {
        try {
            $query = "INSERT INTO EmailAttachments 
                     (email_inbox_id, ms_attachment_id, content_id, filename, original_filename, 
                      file_path, file_size, mime_type, file_hash, is_inline, is_downloaded, 
                      download_error, created_at, downloaded_at) 
                     VALUES 
                     (:email_inbox_id, :ms_attachment_id, :content_id, :filename, :original_filename,
                      :file_path, :file_size, :mime_type, :file_hash, :is_inline, :is_downloaded,
                      :download_error, GETDATE(), :downloaded_at)";
            
            $params = [
                'email_inbox_id' => $data['email_inbox_id'],
                'ms_attachment_id' => $data['ms_attachment_id'] ?? null,
                'content_id' => $data['content_id'] ?? null,
                'filename' => $data['filename'],
                'original_filename' => $data['original_filename'],
                'file_path' => $data['file_path'] ?? null,
                'file_size' => $data['file_size'],
                'mime_type' => $data['mime_type'],
                'file_hash' => $data['file_hash'] ?? null,
                'is_inline' => $data['is_inline'] ?? 0,
                'is_downloaded' => $data['is_downloaded'] ?? 0,
                'download_error' => $data['download_error'] ?? null,
                'downloaded_at' => $data['downloaded_at'] ?? null
            ];
            
            return $this->db->insert($query, $params);
        } catch (Exception $e) {
            error_log('EmailAttachment Create Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update attachment record
     */
    public function update($id, $data) {
        try {
            $setClause = [];
            $params = ['id' => $id];
            
            foreach ($data as $key => $value) {
                if ($key != 'id') {
                    $setClause[] = "$key = :$key";
                    $params[$key] = $value;
                }
            }
            
            if (empty($setClause)) {
                return false;
            }
            
            $query = "UPDATE EmailAttachments SET " . implode(', ', $setClause) . " WHERE id = :id";
            
            return $this->db->update($query, $params);
        } catch (Exception $e) {
            error_log('EmailAttachment Update Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark attachment as downloaded
     */
    public function markAsDownloaded($id, $filePath, $fileHash = null) {
        return $this->update($id, [
            'is_downloaded' => 1,
            'downloaded_at' => date('Y-m-d H:i:s'),
            'file_path' => $filePath,
            'file_hash' => $fileHash,
            'download_error' => null
        ]);
    }
    
    /**
     * Mark attachment download as failed
     */
    public function markDownloadFailed($id, $error) {
        return $this->update($id, [
            'is_downloaded' => 0,
            'download_error' => substr($error, 0, 500)
        ]);
    }
    
    /**
     * Get attachment statistics for an email
     */
    public function getStatsByEmailId($emailInboxId) {
        try {
            $query = "SELECT 
                        COUNT(*) as total_count,
                        SUM(file_size) as total_size,
                        SUM(CASE WHEN is_downloaded = 1 THEN 1 ELSE 0 END) as downloaded_count,
                        SUM(CASE WHEN is_inline = 1 THEN 1 ELSE 0 END) as inline_count
                     FROM EmailAttachments 
                     WHERE email_inbox_id = :email_id";
            
            $result = $this->db->select($query, ['email_id' => $emailInboxId]);
            return !empty($result) ? $result[0] : [
                'total_count' => 0,
                'total_size' => 0,
                'downloaded_count' => 0,
                'inline_count' => 0
            ];
        } catch (Exception $e) {
            error_log('EmailAttachment GetStatsByEmailId Error: ' . $e->getMessage());
            return [
                'total_count' => 0,
                'total_size' => 0,
                'downloaded_count' => 0,
                'inline_count' => 0
            ];
        }
    }
    
    /**
     * Delete attachment and its file
     */
    public function delete($id) {
        try {
            // Get attachment info first
            $attachment = $this->getById($id);
            if (!$attachment) {
                return false;
            }
            
            // Delete physical file if exists
            if (!empty($attachment['file_path']) && file_exists(APPROOT . '/' . $attachment['file_path'])) {
                unlink(APPROOT . '/' . $attachment['file_path']);
            }
            
            // Delete database record
            $query = "DELETE FROM EmailAttachments WHERE id = :id";
            return $this->db->delete($query, ['id' => $id]);
        } catch (Exception $e) {
            error_log('EmailAttachment Delete Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if attachment exists by MS attachment ID
     */
    public function existsByMsId($msAttachmentId) {
        try {
            $query = "SELECT COUNT(*) as count FROM EmailAttachments 
                     WHERE ms_attachment_id = :ms_id";
            
            $result = $this->db->select($query, ['ms_id' => $msAttachmentId]);
            return !empty($result) && $result[0]['count'] > 0;
        } catch (Exception $e) {
            error_log('EmailAttachment ExistsByMsId Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get safe filename for storage
     */
    public function getSafeFilename($originalFilename) {
        // Get file extension
        $ext = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $name = pathinfo($originalFilename, PATHINFO_FILENAME);
        
        // Clean filename
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        $name = substr($name, 0, 100); // Limit length
        
        // Generate unique filename
        $uniqueId = uniqid();
        $timestamp = date('YmdHis');
        
        return "{$timestamp}_{$uniqueId}_{$name}.{$ext}";
    }
    
    /**
     * Format file size for display
     */
    public static function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Get icon class based on MIME type
     */
    public static function getFileIcon($mimeType) {
        $iconMap = [
            'application/pdf' => 'bi-file-earmark-pdf text-danger',
            'application/msword' => 'bi-file-earmark-word text-primary',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'bi-file-earmark-word text-primary',
            'application/vnd.ms-excel' => 'bi-file-earmark-excel text-success',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'bi-file-earmark-excel text-success',
            'application/vnd.ms-powerpoint' => 'bi-file-earmark-ppt text-warning',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'bi-file-earmark-ppt text-warning',
            'text/plain' => 'bi-file-earmark-text',
            'text/html' => 'bi-file-earmark-code',
            'application/zip' => 'bi-file-earmark-zip text-secondary',
            'application/x-rar-compressed' => 'bi-file-earmark-zip text-secondary',
            'application/x-7z-compressed' => 'bi-file-earmark-zip text-secondary'
        ];
        
        // Check for image types
        if (strpos($mimeType, 'image/') === 0) {
            return 'bi-file-earmark-image text-info';
        }
        
        // Check for video types
        if (strpos($mimeType, 'video/') === 0) {
            return 'bi-file-earmark-play text-purple';
        }
        
        // Check for audio types
        if (strpos($mimeType, 'audio/') === 0) {
            return 'bi-file-earmark-music text-pink';
        }
        
        // Return specific icon or default
        return $iconMap[$mimeType] ?? 'bi-file-earmark';
    }
}
?>



