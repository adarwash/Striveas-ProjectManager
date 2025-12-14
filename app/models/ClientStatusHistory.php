<?php

class ClientStatusHistory {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
        $this->ensureTable();
    }

    /**
     * Ensure history table exists
     */
    private function ensureTable(): void {
        try {
            $sql = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='ClientStatusHistory' AND xtype='U')
            BEGIN
                CREATE TABLE ClientStatusHistory (
                    id INT IDENTITY(1,1) PRIMARY KEY,
                    client_id INT NOT NULL,
                    old_status NVARCHAR(50) NULL,
                    new_status NVARCHAR(50) NOT NULL,
                    changed_by INT NULL,
                    changed_at DATETIME DEFAULT GETDATE(),
                    CONSTRAINT FK_ClientStatusHistory_Clients FOREIGN KEY (client_id) REFERENCES Clients(id) ON DELETE CASCADE
                )
            END";
            $this->db->query($sql);
        } catch (Exception $e) {
            error_log('Ensure ClientStatusHistory table error: ' . $e->getMessage());
        }
    }

    /**
     * Add a status change entry
     */
    public function add(int $clientId, ?string $oldStatus, string $newStatus, ?int $userId = null): bool {
        try {
            $this->db->insert(
                "INSERT INTO ClientStatusHistory (client_id, old_status, new_status, changed_by) VALUES (?, ?, ?, ?)",
                [$clientId, $oldStatus, $newStatus, $userId]
            );
            return true;
        } catch (Exception $e) {
            error_log('Add client status history error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * List history for a client (recent first)
     */
    public function listByClient(int $clientId, int $limit = 10): array {
        try {
            $rows = $this->db->select(
                "SELECT TOP $limit h.*, u.username 
                 FROM ClientStatusHistory h
                 LEFT JOIN users u ON h.changed_by = u.id
                 WHERE h.client_id = ?
                 ORDER BY h.changed_at DESC",
                [$clientId]
            );
            return $rows ?: [];
        } catch (Exception $e) {
            error_log('List client status history error: ' . $e->getMessage());
            return [];
        }
    }
}

