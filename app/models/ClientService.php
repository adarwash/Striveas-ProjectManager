<?php

class ClientService {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
        $this->ensureTable();
    }

    /**
     * Ensure ClientServices table exists.
     */
    private function ensureTable(): void {
        try {
            $sql = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='ClientServices' AND xtype='U')
            BEGIN
                CREATE TABLE ClientServices (
                    id INT IDENTITY(1,1) PRIMARY KEY,
                    client_id INT NOT NULL,
                    service_name NVARCHAR(255) NOT NULL,
                    service_type NVARCHAR(100) NULL,
                    quantity INT NOT NULL CONSTRAINT DF_ClientServices_Quantity DEFAULT(1),
                    start_date DATETIME NULL,
                    end_date DATETIME NULL,
                    notes NVARCHAR(MAX) NULL,
                    created_at DATETIME DEFAULT GETDATE(),
                    updated_at DATETIME NULL,
                    CONSTRAINT FK_ClientServices_Clients FOREIGN KEY (client_id) REFERENCES Clients(id) ON DELETE CASCADE
                )
            END";
            $this->db->query($sql);

            // Ensure quantity column exists if table already created
            $sqlQuantity = "IF COL_LENGTH('ClientServices', 'quantity') IS NULL
                            BEGIN
                                ALTER TABLE ClientServices ADD quantity INT NOT NULL CONSTRAINT DF_ClientServices_Quantity DEFAULT(1);
                            END";
            $this->db->query($sqlQuantity);
        } catch (Exception $e) {
            error_log('Ensure ClientServices table error: ' . $e->getMessage());
        }
    }

    /**
     * List services for a client.
     */
    public function listByClient(int $clientId): array {
        try {
            $rows = $this->db->select(
                "SELECT * FROM ClientServices WHERE client_id = ? ORDER BY service_name",
                [$clientId]
            );
            return $rows ?: [];
        } catch (Exception $e) {
            error_log('List client services error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Add a service for a client.
     */
    public function add(int $clientId, array $data): bool {
        try {
            $quantity = isset($data['quantity']) ? max(1, (int)$data['quantity']) : 1;
            $this->db->insert(
                "INSERT INTO ClientServices (client_id, service_name, service_type, quantity, start_date, end_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $clientId,
                    trim($data['service_name'] ?? ''),
                    $data['service_type'] ?? null,
                    $quantity,
                    !empty($data['start_date']) ? $data['start_date'] : null,
                    !empty($data['end_date']) ? $data['end_date'] : null,
                    $data['notes'] ?? null
                ]
            );
            return true;
        } catch (Exception $e) {
            error_log('Add client service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a client service.
     */
    public function delete(int $id): bool {
        try {
            $this->db->remove("DELETE FROM ClientServices WHERE id = ?", [$id]);
            return true;
        } catch (Exception $e) {
            error_log('Delete client service error: ' . $e->getMessage());
            return false;
        }
    }
}

