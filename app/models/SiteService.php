<?php

class SiteService {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
        $this->ensureTable();
    }

    private function ensureTable(): void {
        try {
            $sql = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='SiteServices' AND xtype='U')
            BEGIN
                CREATE TABLE SiteServices (
                    id INT IDENTITY(1,1) PRIMARY KEY,
                    site_id INT NOT NULL,
                    service_name NVARCHAR(255) NOT NULL,
                    service_type NVARCHAR(100) NULL,
                    start_date DATETIME NULL,
                    end_date DATETIME NULL,
                    notes NVARCHAR(MAX) NULL,
                    created_at DATETIME DEFAULT GETDATE(),
                    updated_at DATETIME NULL,
                    CONSTRAINT FK_SiteServices_Sites FOREIGN KEY (site_id) REFERENCES Sites(id) ON DELETE CASCADE
                )
            END";
            $this->db->query($sql);
        } catch (Exception $e) {
            error_log('Ensure SiteServices table error: ' . $e->getMessage());
        }
    }

    public function listBySite(int $siteId): array {
        try {
            $rows = $this->db->select("SELECT * FROM SiteServices WHERE site_id = ? ORDER BY service_name", [$siteId]);
            return $rows ?: [];
        } catch (Exception $e) {
            error_log('List site services error: ' . $e->getMessage());
            return [];
        }
    }

    public function add(int $siteId, array $data): bool {
        try {
            $this->db->insert(
                "INSERT INTO SiteServices (site_id, service_name, service_type, start_date, end_date, notes) VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $siteId,
                    trim($data['service_name'] ?? ''),
                    $data['service_type'] ?? null,
                    !empty($data['start_date']) ? $data['start_date'] : null,
                    !empty($data['end_date']) ? $data['end_date'] : null,
                    $data['notes'] ?? null
                ]
            );
            return true;
        } catch (Exception $e) {
            error_log('Add site service error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool {
        try {
            $this->db->remove("DELETE FROM SiteServices WHERE id = ?", [$id]);
            return true;
        } catch (Exception $e) {
            error_log('Delete site service error: ' . $e->getMessage());
            return false;
        }
    }
}


