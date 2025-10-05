<?php

class ClientContract {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists(): void {
        try {
            $check = $this->db->select("SELECT COUNT(*) AS table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'ClientContracts'");
            $exists = $check && isset($check[0]['table_count']) && (int)$check[0]['table_count'] > 0;
            if ($exists) {
                return;
            }

            $sql = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='ClientContracts' AND xtype='U')
            BEGIN
                CREATE TABLE ClientContracts (
                    id INT IDENTITY(1,1) PRIMARY KEY,
                    client_id INT NOT NULL,
                    file_name NVARCHAR(255) NOT NULL,
                    file_path NVARCHAR(1024) NOT NULL,
                    file_type NVARCHAR(255) NULL,
                    file_size BIGINT NULL,
                    uploaded_by INT NULL,
                    uploaded_at DATETIME DEFAULT GETDATE()
                )
            END";

            $this->db->query($sql);
        } catch (Exception $e) {
            error_log('ClientContracts table create error: ' . $e->getMessage());
        }
    }

    public function getContractsByClient(int $clientId): array {
        try {
            $rows = $this->db->select(
                "SELECT * FROM ClientContracts WHERE client_id = ? ORDER BY uploaded_at DESC",
                [$clientId]
            );
            return $rows ?: [];
        } catch (Exception $e) {
            error_log('GetContractsByClient error: ' . $e->getMessage());
            return [];
        }
    }

    public function addContract(int $clientId, array $fileData, ?int $uploadedBy = null): bool {
        try {
            $this->db->insert(
                "INSERT INTO ClientContracts (client_id, file_name, file_path, file_type, file_size, uploaded_by, uploaded_at)
                 VALUES (?, ?, ?, ?, ?, ?, GETDATE())",
                [
                    $clientId,
                    $fileData['name'] ?? '',
                    $fileData['path'] ?? '',
                    $fileData['type'] ?? null,
                    $fileData['size'] ?? null,
                    $uploadedBy
                ]
            );
            return true;
        } catch (Exception $e) {
            error_log('AddContract error: ' . $e->getMessage());
            return false;
        }
    }

    public function getContractById(int $id) {
        try {
            $rows = $this->db->select("SELECT * FROM ClientContracts WHERE id = ?", [$id]);
            return $rows ? $rows[0] : null;
        } catch (Exception $e) {
            error_log('GetContractById error: ' . $e->getMessage());
            return null;
        }
    }

    public function deleteContract(int $id): bool {
        try {
            $this->db->remove("DELETE FROM ClientContracts WHERE id = ?", [$id]);
            return true;
        } catch (Exception $e) {
            error_log('DeleteContract error: ' . $e->getMessage());
            return false;
        }
    }
}


