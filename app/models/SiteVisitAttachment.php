<?php

class SiteVisitAttachment {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
        $this->ensureTable();
    }

    private function ensureTable(): void {
        try {
            $sql = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='SiteVisitAttachments' AND xtype='U')
            BEGIN
                CREATE TABLE SiteVisitAttachments (
                    id INT IDENTITY(1,1) PRIMARY KEY,
                    visit_id INT NOT NULL,
                    file_name NVARCHAR(255) NOT NULL,
                    file_path NVARCHAR(1024) NOT NULL,
                    file_type NVARCHAR(255) NULL,
                    file_size BIGINT NULL,
                    uploaded_by INT NULL,
                    uploaded_at DATETIME DEFAULT GETDATE(),
                    CONSTRAINT FK_SiteVisitAttachments_Visits FOREIGN KEY (visit_id) REFERENCES SiteVisits(id) ON DELETE CASCADE
                )
            END";
            $this->db->query($sql);
        } catch (Exception $e) {
            error_log('Ensure SiteVisitAttachments table error: ' . $e->getMessage());
        }
    }

    public function getByVisit(int $visitId): array {
        try {
            $rows = $this->db->select(
                "SELECT * FROM SiteVisitAttachments WHERE visit_id = ? ORDER BY uploaded_at DESC",
                [$visitId]
            );
            return $rows ?: [];
        } catch (Exception $e) {
            error_log('Get attachments by visit error: ' . $e->getMessage());
            return [];
        }
    }

    public function add(int $visitId, array $fileData, ?int $uploadedBy = null): bool {
        try {
            $this->db->insert(
                "INSERT INTO SiteVisitAttachments (visit_id, file_name, file_path, file_type, file_size, uploaded_by, uploaded_at)
                 VALUES (?, ?, ?, ?, ?, ?, GETDATE())",
                [
                    $visitId,
                    $fileData['name'] ?? '',
                    $fileData['path'] ?? '',
                    $fileData['type'] ?? null,
                    $fileData['size'] ?? null,
                    $uploadedBy
                ]
            );
            return true;
        } catch (Exception $e) {
            error_log('Add attachment error: ' . $e->getMessage());
            return false;
        }
    }

    public function getById(int $id) {
        try {
            $rows = $this->db->select("SELECT * FROM SiteVisitAttachments WHERE id = ?", [$id]);
            return $rows ? $rows[0] : null;
        } catch (Exception $e) {
            error_log('Get attachment by id error: ' . $e->getMessage());
            return null;
        }
    }

    public function delete(int $id): bool {
        try {
            $this->db->remove("DELETE FROM SiteVisitAttachments WHERE id = ?", [$id]);
            return true;
        } catch (Exception $e) {
            error_log('Delete attachment error: ' . $e->getMessage());
            return false;
        }
    }
}


