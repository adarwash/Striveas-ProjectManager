<?php

class Supplier {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }
    
    /**
     * Create the Suppliers table if it doesn't exist
     * 
     * @return bool True if successful, false otherwise
     */
    public function createSuppliersTable(): bool {
        try {
            // First check if table exists
            $tableExists = false;
            
            try {
                $checkQuery = "SELECT COUNT(*) as table_count FROM INFORMATION_SCHEMA.TABLES 
                              WHERE TABLE_NAME = 'Suppliers'";
                $result = $this->db->select($checkQuery);
                $tableExists = ($result && isset($result[0]['table_count']) && $result[0]['table_count'] > 0);
                
                error_log('Suppliers table exists check: ' . ($tableExists ? 'Yes' : 'No'));
            } catch (Exception $e) {
                error_log('Error checking for Suppliers table: ' . $e->getMessage());
                // Continue with creation attempt
            }
            
            if (!$tableExists) {
                // Create table from scratch
                $sql = "
                IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[Suppliers]') AND type in (N'U'))
                BEGIN
                    CREATE TABLE [dbo].[Suppliers] (
                        [id] INT IDENTITY(1,1) PRIMARY KEY,
                        [name] NVARCHAR(200) NOT NULL,
                        [contact_name] NVARCHAR(100) NULL,
                        [email] NVARCHAR(100) NULL,
                        [phone] NVARCHAR(50) NULL,
                        [address] NVARCHAR(255) NULL,
                        [city] NVARCHAR(100) NULL,
                        [state] NVARCHAR(100) NULL,
                        [postal_code] NVARCHAR(20) NULL,
                        [country] NVARCHAR(100) NULL,
                        [website] NVARCHAR(255) NULL,
                        [notes] NVARCHAR(MAX) NULL,
                        [status] NVARCHAR(20) DEFAULT 'active',
                        [created_by] INT NOT NULL,
                        [created_at] DATETIME DEFAULT GETDATE(),
                        [updated_at] DATETIME DEFAULT GETDATE()
                    )
                END";
                
                $this->db->query($sql);
                error_log('Suppliers table created successfully');
                
                // Add foreign key constraint in a separate statement to avoid errors
                try {
                    $fkSql = "
                    IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = 'FK_Suppliers_Users')
                    BEGIN
                        ALTER TABLE [dbo].[Suppliers] ADD CONSTRAINT [FK_Suppliers_Users] 
                        FOREIGN KEY ([created_by]) REFERENCES [Users]([id]) ON DELETE NO ACTION
                    END";
                    
                    $this->db->query($fkSql);
                    error_log('Suppliers table foreign key added successfully');
                } catch (Exception $e) {
                    error_log('Error adding foreign key constraint to Suppliers table: ' . $e->getMessage());
                    // Continue anyway, table should still be usable
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Create Suppliers Table Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Create a new supplier
     *
     * @param array $data Supplier data
     * @return int|bool Supplier ID if successful, false otherwise
     */
    public function create(array $data): int|bool {
        try {
            // Ensure the Suppliers table exists
            $this->createSuppliersTable();
            
            // Add detailed logging
            error_log("Supplier::create - Preparing to insert supplier with data: " . json_encode([
                'name' => $data['name'],
                'contact_name' => $data['contact_name'] ?? null,
                'email' => $data['email'] ?? null
            ]));
            
            // Prepare the query with proper parameter handling
            $query = "INSERT INTO Suppliers (
                name, contact_name, email, phone, address, city, state, 
                postal_code, country, website, notes, status, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            // Handle the insertion with proper SQL Server NULL handling
            try {
                // Get a direct connection to the database
                $pdo = new PDO(
                    "sqlsrv:Server=" . DB1['host'] . ";Database=" . DB1['dbname'] . ";TrustServerCertificate=true", 
                    DB1['user'], 
                    DB1['pass']
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $stmt = $pdo->prepare($query);
                
                // Bind parameters individually
                $stmt->bindParam(1, $data['name'], PDO::PARAM_STR);
                
                // Handle potentially NULL values
                if (!empty($data['contact_name'])) {
                    $stmt->bindParam(2, $data['contact_name'], PDO::PARAM_STR);
                } else {
                    $stmt->bindValue(2, null, PDO::PARAM_NULL);
                }
                
                if (!empty($data['email'])) {
                    $stmt->bindParam(3, $data['email'], PDO::PARAM_STR);
                } else {
                    $stmt->bindValue(3, null, PDO::PARAM_NULL);
                }
                
                if (!empty($data['phone'])) {
                    $stmt->bindParam(4, $data['phone'], PDO::PARAM_STR);
                } else {
                    $stmt->bindValue(4, null, PDO::PARAM_NULL);
                }
                
                if (!empty($data['address'])) {
                    $stmt->bindParam(5, $data['address'], PDO::PARAM_STR);
                } else {
                    $stmt->bindValue(5, null, PDO::PARAM_NULL);
                }
                
                if (!empty($data['city'])) {
                    $stmt->bindParam(6, $data['city'], PDO::PARAM_STR);
                } else {
                    $stmt->bindValue(6, null, PDO::PARAM_NULL);
                }
                
                if (!empty($data['state'])) {
                    $stmt->bindParam(7, $data['state'], PDO::PARAM_STR);
                } else {
                    $stmt->bindValue(7, null, PDO::PARAM_NULL);
                }
                
                if (!empty($data['postal_code'])) {
                    $stmt->bindParam(8, $data['postal_code'], PDO::PARAM_STR);
                } else {
                    $stmt->bindValue(8, null, PDO::PARAM_NULL);
                }
                
                if (!empty($data['country'])) {
                    $stmt->bindParam(9, $data['country'], PDO::PARAM_STR);
                } else {
                    $stmt->bindValue(9, null, PDO::PARAM_NULL);
                }
                
                if (!empty($data['website'])) {
                    $stmt->bindParam(10, $data['website'], PDO::PARAM_STR);
                } else {
                    $stmt->bindValue(10, null, PDO::PARAM_NULL);
                }
                
                if (!empty($data['notes'])) {
                    $stmt->bindParam(11, $data['notes'], PDO::PARAM_STR);
                } else {
                    $stmt->bindValue(11, null, PDO::PARAM_NULL);
                }
                
                $status = $data['status'] ?? 'active';
                $stmt->bindParam(12, $status, PDO::PARAM_STR);
                $stmt->bindParam(13, $data['created_by'], PDO::PARAM_INT);
                
                // Execute the statement
                $result = $stmt->execute();
                
                if ($result) {
                    // Get the ID using SCOPE_IDENTITY() for SQL Server
                    $idStmt = $pdo->query("SELECT SCOPE_IDENTITY() as id");
                    $row = $idStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($row && isset($row['id']) && !empty($row['id'])) {
                        $id = (int)$row['id'];
                        error_log("Supplier::create - Successfully created supplier with ID: " . $id);
                        return $id;
                    } else {
                        // Try other methods to get the ID
                        $altIdStmt = $pdo->query("SELECT @@IDENTITY as id");
                        $altRow = $altIdStmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($altRow && isset($altRow['id']) && !empty($altRow['id'])) {
                            $id = (int)$altRow['id'];
                            error_log("Supplier::create - Successfully created supplier with alternate ID: " . $id);
                            return $id;
                        }
                        
                        // Return true if we can't get the ID but the insert was successful
                        error_log("Supplier::create - Insert successful but couldn't retrieve ID");
                        return true;
                    }
                }
                
                return false;
            } catch (PDOException $e) {
                error_log("Supplier::create - PDO Exception: " . $e->getMessage());
                return false;
            }
        } catch (Exception $e) {
            error_log('Create Supplier Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Get a supplier by ID
     *
     * @param int $id Supplier ID
     * @return array|bool Supplier data if found, false otherwise
     */
    public function getSupplierById(int $id): array|bool {
        try {
            $query = "SELECT s.*, u.username as created_by_name 
                     FROM Suppliers s
                     LEFT JOIN Users u ON s.created_by = u.id
                     WHERE s.id = ?";
            
            $result = $this->db->select($query, [$id]);
            return !empty($result) ? $result[0] : false;
        } catch (Exception $e) {
            error_log('Get Supplier Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all suppliers
     *
     * @param string|null $status Filter by status
     * @return array Array of suppliers
     */
    public function getAllSuppliers(string $status = null): array {
        try {
            if ($status) {
                $query = "SELECT s.*, u.username as created_by_name 
                         FROM Suppliers s
                         LEFT JOIN Users u ON s.created_by = u.id
                         WHERE s.status = ?
                         ORDER BY s.name ASC";
                $result = $this->db->select($query, [$status]);
            } else {
                $query = "SELECT s.*, u.username as created_by_name 
                         FROM Suppliers s
                         LEFT JOIN Users u ON s.created_by = u.id
                         ORDER BY s.name ASC";
                $result = $this->db->select($query);
            }
            
            return $result ?: [];
        } catch (Exception $e) {
            error_log('Get All Suppliers Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get suppliers by search term
     * 
     * @param string $searchTerm Search term
     * @return array Array of suppliers
     */
    public function searchSuppliers(string $searchTerm): array {
        try {
            $searchParam = '%' . $searchTerm . '%';
            $query = "SELECT s.*, u.username as created_by_name 
                     FROM Suppliers s
                     LEFT JOIN Users u ON s.created_by = u.id
                     WHERE s.name LIKE ? 
                        OR s.contact_name LIKE ? 
                        OR s.email LIKE ?
                        OR s.city LIKE ?
                        OR s.country LIKE ?
                     ORDER BY s.name ASC";
            
            $result = $this->db->select($query, [
                $searchParam, $searchParam, $searchParam, $searchParam, $searchParam
            ]);
            
            return $result ?: [];
        } catch (Exception $e) {
            error_log('Search Suppliers Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update a supplier
     *
     * @param array $data Supplier data
     * @return bool True if successful, false otherwise
     */
    public function update(array $data): bool {
        try {
            $query = "UPDATE Suppliers SET 
                     name = ?, 
                     contact_name = ?,
                     email = ?,
                     phone = ?,
                     address = ?,
                     city = ?,
                     state = ?,
                     postal_code = ?,
                     country = ?,
                     website = ?,
                     notes = ?,
                     status = ?,
                     updated_at = GETDATE()
                     WHERE id = ?";
            
            $this->db->update($query, [
                $data['name'],
                $data['contact_name'] ?? null,
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
                $data['postal_code'] ?? null,
                $data['country'] ?? null,
                $data['website'] ?? null,
                $data['notes'] ?? null,
                $data['status'] ?? 'active',
                $data['id']
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log('Update Supplier Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a supplier
     *
     * @param int $id Supplier ID
     * @return bool True if successful, false otherwise
     */
    public function delete(int $id): bool {
        try {
            // Check if the supplier has associated invoices
            $checkQuery = "SELECT COUNT(*) as invoice_count FROM Invoices WHERE supplier_id = ?";
            $checkResult = $this->db->select($checkQuery, [$id]);
            
            if ($checkResult && isset($checkResult[0]['invoice_count']) && $checkResult[0]['invoice_count'] > 0) {
                // Supplier has invoices, set status to inactive instead of deleting
                $updateQuery = "UPDATE Suppliers SET status = 'inactive', updated_at = GETDATE() WHERE id = ?";
                $this->db->update($updateQuery, [$id]);
            } else {
                // No invoices, safe to delete
                $deleteQuery = "DELETE FROM Suppliers WHERE id = ?";
                $this->db->remove($deleteQuery, [$id]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Delete Supplier Error: ' . $e->getMessage());
            return false;
        }
    }
} 