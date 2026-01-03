<?php

/**
 * ClientContact model
 *
 * Stores additional contacts for a client (many-to-one).
 */
class ClientContact {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
        $this->ensureTable();
    }

    /**
     * Ensure the ClientContacts table exists
     */
    private function ensureTable(): void {
        try {
            $this->db->query("
                IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[ClientContacts]') AND type in (N'U'))
                BEGIN
                    CREATE TABLE ClientContacts (
                        id INT IDENTITY(1,1) PRIMARY KEY,
                        client_id INT NOT NULL,
                        first_name NVARCHAR(100) NULL,
                        last_name NVARCHAR(100) NULL,
                        full_name NVARCHAR(200) NULL,
                        email NVARCHAR(255) NULL,
                        phone NVARCHAR(50) NULL,
                        mobile NVARCHAR(50) NULL,
                        created_at DATETIME DEFAULT GETDATE(),
                        updated_at DATETIME NULL,
                        CONSTRAINT FK_ClientContacts_Clients FOREIGN KEY (client_id) REFERENCES Clients(id) ON DELETE CASCADE
                    );
                END
            ");

            // Helpful indexes (best-effort)
            $this->db->query("
                IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_ClientContacts_Client' AND object_id = OBJECT_ID('ClientContacts'))
                BEGIN
                    CREATE INDEX IX_ClientContacts_Client ON ClientContacts(client_id);
                END
            ");
            $this->db->query("
                IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_ClientContacts_Email' AND object_id = OBJECT_ID('ClientContacts'))
                BEGIN
                    CREATE INDEX IX_ClientContacts_Email ON ClientContacts(email);
                END
            ");
        } catch (Exception $e) {
            error_log('ClientContact ensureTable error: ' . $e->getMessage());
        }
    }

    public function listByClient(int $clientId): array {
        try {
            return $this->db->select(
                "SELECT id, client_id, first_name, last_name, full_name, email, phone, mobile, created_at, updated_at
                 FROM ClientContacts
                 WHERE client_id = :client_id
                 ORDER BY COALESCE(NULLIF(full_name, ''), NULLIF(LTRIM(RTRIM(CONCAT(first_name, ' ', last_name))), ''), email) ASC, id ASC",
                ['client_id' => (int)$clientId]
            ) ?: [];
        } catch (Exception $e) {
            error_log('ClientContact listByClient error: ' . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): array|false {
        try {
            $rows = $this->db->select(
                "SELECT TOP 1 * FROM ClientContacts WHERE id = :id",
                ['id' => (int)$id]
            );
            return $rows[0] ?? false;
        } catch (Exception $e) {
            error_log('ClientContact getById error: ' . $e->getMessage());
            return false;
        }
    }

    public function getByClientAndEmail(int $clientId, string $email): array|false {
        try {
            $email = $this->normalizeEmail($email);
            if ($email === '') {
                return false;
            }
            $rows = $this->db->select(
                "SELECT TOP 1 * FROM ClientContacts WHERE client_id = :client_id AND LOWER(email) = LOWER(:email) ORDER BY id DESC",
                ['client_id' => (int)$clientId, 'email' => $email]
            );
            return $rows[0] ?? false;
        } catch (Exception $e) {
            error_log('ClientContact getByClientAndEmail error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Find a contact by email (case-insensitive)
     */
    public function getByEmail(string $email): array|false {
        try {
            $email = $this->normalizeEmail($email);
            if ($email === '') {
                return false;
            }
            $rows = $this->db->select(
                "SELECT TOP 1 * FROM ClientContacts WHERE LOWER(email) = LOWER(:email) ORDER BY id DESC",
                ['email' => $email]
            );
            return $rows[0] ?? false;
        } catch (Exception $e) {
            error_log('ClientContact getByEmail error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Return a client_id when a contact email matches
     */
    public function getClientIdByEmail(string $email): ?int {
        $row = $this->getByEmail($email);
        if (!empty($row['client_id'])) {
            return (int)$row['client_id'];
        }
        return null;
    }

    public function create(int $clientId, array $data): int|false {
        try {
            $clientId = (int)$clientId;
            if ($clientId <= 0) {
                return false;
            }

            $first = trim((string)($data['first_name'] ?? ''));
            $last = trim((string)($data['last_name'] ?? ''));
            $full = trim((string)($data['full_name'] ?? ''));
            if ($full === '') {
                $full = trim($first . ' ' . $last);
            }

            $email = $this->normalizeEmail((string)($data['email'] ?? ''));
            $phone = trim((string)($data['phone'] ?? ''));
            $mobile = trim((string)($data['mobile'] ?? ''));

            $query = "INSERT INTO ClientContacts (client_id, first_name, last_name, full_name, email, phone, mobile)
                      VALUES (:client_id, :first_name, :last_name, :full_name, :email, :phone, :mobile)";
            return $this->db->insert($query, [
                'client_id' => $clientId,
                'first_name' => $first !== '' ? $first : null,
                'last_name' => $last !== '' ? $last : null,
                'full_name' => $full !== '' ? $full : null,
                'email' => $email !== '' ? $email : null,
                'phone' => $phone !== '' ? $phone : null,
                'mobile' => $mobile !== '' ? $mobile : null
            ]);
        } catch (Exception $e) {
            error_log('ClientContact create error: ' . $e->getMessage());
            return false;
        }
    }

    public function update(int $id, int $clientId, array $data): bool {
        try {
            $id = (int)$id;
            $clientId = (int)$clientId;
            if ($id <= 0 || $clientId <= 0) {
                return false;
            }

            $first = trim((string)($data['first_name'] ?? ''));
            $last = trim((string)($data['last_name'] ?? ''));
            $full = trim((string)($data['full_name'] ?? ''));
            if ($full === '') {
                $full = trim($first . ' ' . $last);
            }

            $email = $this->normalizeEmail((string)($data['email'] ?? ''));
            $phone = trim((string)($data['phone'] ?? ''));
            $mobile = trim((string)($data['mobile'] ?? ''));

            $this->db->update(
                "UPDATE ClientContacts
                 SET first_name = :first_name,
                     last_name = :last_name,
                     full_name = :full_name,
                     email = :email,
                     phone = :phone,
                     mobile = :mobile,
                     updated_at = GETDATE()
                 WHERE id = :id AND client_id = :client_id",
                [
                    'id' => $id,
                    'client_id' => $clientId,
                    'first_name' => $first !== '' ? $first : null,
                    'last_name' => $last !== '' ? $last : null,
                    'full_name' => $full !== '' ? $full : null,
                    'email' => $email !== '' ? $email : null,
                    'phone' => $phone !== '' ? $phone : null,
                    'mobile' => $mobile !== '' ? $mobile : null,
                ]
            );
            return true;
        } catch (Exception $e) {
            error_log('ClientContact update error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id, int $clientId): bool {
        try {
            $this->db->remove(
                "DELETE FROM ClientContacts WHERE id = :id AND client_id = :client_id",
                ['id' => (int)$id, 'client_id' => (int)$clientId]
            );
            return true;
        } catch (Exception $e) {
            error_log('ClientContact delete error: ' . $e->getMessage());
            return false;
        }
    }

    private function normalizeEmail(string $email): string {
        $email = strtolower(trim($email));
        return $email;
    }
}

