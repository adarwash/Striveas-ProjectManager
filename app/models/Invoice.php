<?php

class Invoice {
    private $db;
    
    public function __construct() {
        $this->db = new EasySQL(DB1);
        $this->ensureDocumentsTableExists();
    }
    
    private function ensureDocumentsTableExists() {
        try {
            // First check if the table exists
            $checkTable = "SELECT CASE WHEN EXISTS (
                            SELECT * FROM INFORMATION_SCHEMA.TABLES 
                            WHERE TABLE_NAME = 'invoice_documents'
                           ) THEN 1 ELSE 0 END AS table_exists";
            
            $stmt = $this->db->query($checkTable);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // If table doesn't exist, create it
            if (!$row || !$row['table_exists']) {
                $createTable = "CREATE TABLE invoice_documents (
                    id INT IDENTITY(1,1) PRIMARY KEY,
                    invoice_id INT NOT NULL,
                    file_name NVARCHAR(255) NOT NULL,
                    file_path NVARCHAR(1000) NOT NULL,
                    file_type NVARCHAR(50) NOT NULL,
                    file_size INT NOT NULL,
                    uploaded_by INT NOT NULL,
                    uploaded_at DATETIME NOT NULL DEFAULT GETDATE(),
                    CONSTRAINT FK_invoice_docs_invoice FOREIGN KEY (invoice_id) 
                        REFERENCES invoices(id) ON DELETE CASCADE,
                    CONSTRAINT FK_invoice_docs_user FOREIGN KEY (uploaded_by) 
                        REFERENCES users(id)
                )";
                
                $this->db->query($createTable);
                
                // Create indexes for better performance
                $this->db->query("CREATE INDEX IX_invoice_docs_invoice_id ON invoice_documents(invoice_id)");
                $this->db->query("CREATE INDEX IX_invoice_docs_uploaded_by ON invoice_documents(uploaded_by)");
                
                error_log('Created invoice_documents table successfully');
            }
        } catch (Exception $e) {
            error_log('Error creating invoice_documents table: ' . $e->getMessage());
        }
    }
    
    /**
     * Create the Invoices table if it doesn't exist
     * 
     * @return bool True if successful, false otherwise
     */
    public function createInvoicesTable(): bool {
        try {
            // First check if table exists
            $tableExists = false;
            
            try {
                $checkQuery = "SELECT COUNT(*) as table_count FROM INFORMATION_SCHEMA.TABLES 
                              WHERE TABLE_NAME = 'Invoices'";
                $result = $this->db->select($checkQuery);
                $tableExists = ($result && isset($result[0]['table_count']) && $result[0]['table_count'] > 0);
                
                error_log('Invoices table exists check: ' . ($tableExists ? 'Yes' : 'No'));
            } catch (Exception $e) {
                error_log('Error checking for Invoices table: ' . $e->getMessage());
                // Continue with creation attempt
            }
            
            if (!$tableExists) {
                // Create table from scratch
                $sql = "
                IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[Invoices]') AND type in (N'U'))
                BEGIN
                    CREATE TABLE [dbo].[Invoices] (
                        [id] INT IDENTITY(1,1) PRIMARY KEY,
                        [invoice_number] NVARCHAR(50) NOT NULL,
                        [supplier_id] INT NOT NULL,
                        [invoice_date] DATE NOT NULL,
                        [due_date] DATE NULL,
                        [total_amount] DECIMAL(18, 2) NOT NULL,
                        [status] NVARCHAR(20) DEFAULT 'pending', -- pending, paid, cancelled
                        [payment_date] DATE NULL,
                        [payment_reference] NVARCHAR(100) NULL,
                        [notes] NVARCHAR(MAX) NULL,
                        [created_by] INT NOT NULL,
                        [created_at] DATETIME DEFAULT GETDATE(),
                        [updated_at] DATETIME DEFAULT GETDATE()
                    )
                END";
                
                $this->db->query($sql);
                error_log('Invoices table created successfully');
                
                // Add foreign key constraints in separate statements to avoid errors
                try {
                    $fkUserSql = "
                    IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = 'FK_Invoices_Users')
                    BEGIN
                        ALTER TABLE [dbo].[Invoices] ADD CONSTRAINT [FK_Invoices_Users] 
                        FOREIGN KEY ([created_by]) REFERENCES [Users]([id]) ON DELETE NO ACTION
                    END";
                    
                    $this->db->query($fkUserSql);
                    error_log('Invoices table user foreign key added successfully');
                } catch (Exception $e) {
                    error_log('Error adding user foreign key constraint to Invoices table: ' . $e->getMessage());
                    // Continue anyway, table should still be usable
                }
                
                try {
                    $fkSupplierSql = "
                    IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = 'FK_Invoices_Suppliers')
                    BEGIN
                        ALTER TABLE [dbo].[Invoices] ADD CONSTRAINT [FK_Invoices_Suppliers] 
                        FOREIGN KEY ([supplier_id]) REFERENCES [Suppliers]([id]) ON DELETE NO ACTION
                    END";
                    
                    $this->db->query($fkSupplierSql);
                    error_log('Invoices table supplier foreign key added successfully');
                } catch (Exception $e) {
                    error_log('Error adding supplier foreign key constraint to Invoices table: ' . $e->getMessage());
                    // Continue anyway, table should still be usable
                }
                
                try {
                    // Add index for performance
                    $indexSql = "
                    IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IDX_Invoices_SupplierID' AND object_id = OBJECT_ID('Invoices'))
                    BEGIN
                        CREATE INDEX [IDX_Invoices_SupplierID] ON [dbo].[Invoices]([supplier_id])
                    END";
                    
                    $this->db->query($indexSql);
                    error_log('Invoices table index added successfully');
                } catch (Exception $e) {
                    error_log('Error adding index to Invoices table: ' . $e->getMessage());
                    // Continue anyway, table should still be usable
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Create Invoices Table Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Create a new invoice
     *
     * @param array $data Invoice data
     * @return int|bool Invoice ID if successful, false otherwise
     */
    public function create(array $data): int|bool {
        try {
            // Ensure the Invoices table exists
            $this->createInvoicesTable();
            
            // First, confirm the supplier exists
            $supplierCheck = "SELECT COUNT(*) as supplier_count FROM Suppliers WHERE id = ?";
            $supplierResult = $this->db->select($supplierCheck, [$data['supplier_id']]);
            
            if (!$supplierResult || !isset($supplierResult[0]['supplier_count']) || $supplierResult[0]['supplier_count'] == 0) {
                error_log("Invoice::create - Supplier ID {$data['supplier_id']} does not exist");
                return false;
            }
            
            // Add detailed logging
            error_log("Invoice::create - Preparing to insert invoice with data: " . json_encode([
                'invoice_number' => $data['invoice_number'],
                'supplier_id' => $data['supplier_id'],
                'invoice_date' => $data['invoice_date'],
                'total_amount' => $data['total_amount']
            ]));
            
            // Prepare the query with proper parameter handling
            $query = "INSERT INTO Invoices (
                invoice_number, supplier_id, invoice_date, due_date, total_amount,
                status, payment_date, payment_reference, notes, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
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
                $stmt->bindParam(1, $data['invoice_number'], PDO::PARAM_STR);
                $stmt->bindParam(2, $data['supplier_id'], PDO::PARAM_INT);
                $stmt->bindParam(3, $data['invoice_date'], PDO::PARAM_STR);
                
                // Handle potentially NULL values
                if (!empty($data['due_date'])) {
                    $stmt->bindParam(4, $data['due_date'], PDO::PARAM_STR);
                } else {
                    $stmt->bindValue(4, null, PDO::PARAM_NULL);
                }
                
                $stmt->bindParam(5, $data['total_amount'], PDO::PARAM_STR);
                
                $status = $data['status'] ?? 'pending';
                $stmt->bindParam(6, $status, PDO::PARAM_STR);
                
                if (!empty($data['payment_date'])) {
                    $stmt->bindParam(7, $data['payment_date'], PDO::PARAM_STR);
                } else {
                    $stmt->bindValue(7, null, PDO::PARAM_NULL);
                }
                
                if (!empty($data['payment_reference'])) {
                    $stmt->bindParam(8, $data['payment_reference'], PDO::PARAM_STR);
                } else {
                    $stmt->bindValue(8, null, PDO::PARAM_NULL);
                }
                
                if (!empty($data['notes'])) {
                    $stmt->bindParam(9, $data['notes'], PDO::PARAM_STR);
                } else {
                    $stmt->bindValue(9, null, PDO::PARAM_NULL);
                }
                
                $stmt->bindParam(10, $data['created_by'], PDO::PARAM_INT);
                
                // Execute the statement
                $result = $stmt->execute();
                
                if ($result) {
                    // Get the ID using SCOPE_IDENTITY() for SQL Server
                    $idStmt = $pdo->query("SELECT SCOPE_IDENTITY() as id");
                    $row = $idStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($row && isset($row['id']) && !empty($row['id'])) {
                        $id = (int)$row['id'];
                        error_log("Invoice::create - Successfully created invoice with ID: " . $id);
                        return $id;
                    } else {
                        // Try other methods to get the ID
                        $altIdStmt = $pdo->query("SELECT @@IDENTITY as id");
                        $altRow = $altIdStmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($altRow && isset($altRow['id']) && !empty($altRow['id'])) {
                            $id = (int)$altRow['id'];
                            error_log("Invoice::create - Successfully created invoice with alternate ID: " . $id);
                            return $id;
                        }
                        
                        // Return true if we can't get the ID but the insert was successful
                        error_log("Invoice::create - Insert successful but couldn't retrieve ID");
                        return true;
                    }
                }
                
                return false;
            } catch (PDOException $e) {
                error_log("Invoice::create - PDO Exception: " . $e->getMessage());
                return false;
            }
        } catch (Exception $e) {
            error_log('Create Invoice Error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Get an invoice by ID
     *
     * @param int $id Invoice ID
     * @return array|bool Invoice data if found, false otherwise
     */
    public function getInvoiceById(int $id): array|bool {
        try {
            $query = "SELECT i.*, s.name as supplier_name, u.username as created_by_name 
                     FROM Invoices i
                     LEFT JOIN Suppliers s ON i.supplier_id = s.id
                     LEFT JOIN Users u ON i.created_by = u.id
                     WHERE i.id = ?";
            
            $result = $this->db->select($query, [$id]);
            return !empty($result) ? $result[0] : false;
        } catch (Exception $e) {
            error_log('Get Invoice Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all invoices
     *
     * @param string|null $status Filter by status
     * @return array Array of invoices
     */
    public function getAllInvoices(string $status = null): array {
        try {
            if ($status) {
                $query = "SELECT i.*, s.name as supplier_name, u.username as created_by_name 
                         FROM Invoices i
                         LEFT JOIN Suppliers s ON i.supplier_id = s.id
                         LEFT JOIN Users u ON i.created_by = u.id
                         WHERE i.status = ?
                         ORDER BY i.invoice_date DESC";
                $result = $this->db->select($query, [$status]);
            } else {
                $query = "SELECT i.*, s.name as supplier_name, u.username as created_by_name 
                         FROM Invoices i
                         LEFT JOIN Suppliers s ON i.supplier_id = s.id
                         LEFT JOIN Users u ON i.created_by = u.id
                         ORDER BY i.invoice_date DESC";
                $result = $this->db->select($query);
            }
            
            return $result ?: [];
        } catch (Exception $e) {
            error_log('Get All Invoices Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get invoices by supplier ID
     *
     * @param int $supplierId Supplier ID
     * @return array Array of invoices
     */
    public function getInvoicesBySupplier(int $supplierId): array {
        try {
            $query = "SELECT i.*, s.name as supplier_name, u.username as created_by_name 
                     FROM Invoices i
                     LEFT JOIN Suppliers s ON i.supplier_id = s.id
                     LEFT JOIN Users u ON i.created_by = u.id
                     WHERE i.supplier_id = ?
                     ORDER BY i.invoice_date DESC";
            
            $result = $this->db->select($query, [$supplierId]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log('Get Supplier Invoices Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search invoices by term
     *
     * @param string $searchTerm Search term
     * @return array Array of invoices
     */
    public function searchInvoices(string $searchTerm): array {
        try {
            $searchParam = '%' . $searchTerm . '%';
            $query = "SELECT i.*, s.name as supplier_name, u.username as created_by_name 
                     FROM Invoices i
                     LEFT JOIN Suppliers s ON i.supplier_id = s.id
                     LEFT JOIN Users u ON i.created_by = u.id
                     WHERE i.invoice_number LIKE ? 
                        OR s.name LIKE ? 
                        OR i.payment_reference LIKE ?
                        OR CAST(i.total_amount AS NVARCHAR) LIKE ?
                     ORDER BY i.invoice_date DESC";
            
            $result = $this->db->select($query, [
                $searchParam, $searchParam, $searchParam, $searchParam
            ]);
            
            return $result ?: [];
        } catch (Exception $e) {
            error_log('Search Invoices Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update an invoice
     *
     * @param array $data Invoice data
     * @return bool True if successful, false otherwise
     */
    public function update(array $data): bool {
        try {
            $query = "UPDATE Invoices SET 
                     invoice_number = ?,
                     supplier_id = ?,
                     invoice_date = ?,
                     due_date = ?,
                     total_amount = ?,
                     status = ?,
                     payment_date = ?,
                     payment_reference = ?,
                     notes = ?,
                     updated_at = GETDATE()
                     WHERE id = ?";
            
            $this->db->update($query, [
                $data['invoice_number'],
                $data['supplier_id'],
                $data['invoice_date'],
                $data['due_date'] ?? null,
                $data['total_amount'],
                $data['status'] ?? 'pending',
                $data['payment_date'] ?? null,
                $data['payment_reference'] ?? null,
                $data['notes'] ?? null,
                $data['id']
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log('Update Invoice Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete an invoice
     *
     * @param int $id Invoice ID
     * @return bool True if successful, false otherwise
     */
    public function delete(int $id): bool {
        try {
            // First check if invoice exists
            $invoice = $this->getInvoiceById($id);
            if (!$invoice) {
                return false;
            }

            // Delete the invoice
            $deleteQuery = "DELETE FROM Invoices WHERE id = ?";
            $this->db->remove($deleteQuery, [$id]);
            return true;
        } catch (Exception $e) {
            error_log('Delete Invoice Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark an invoice as paid
     *
     * @param array $data Invoice data including id, payment_date, and payment_reference
     * @return bool True if successful, false otherwise
     */
    public function markAsPaid(array $data): bool {
        try {
            // Extract the data from the array
            $id = $data['id'];
            $paymentDate = $data['payment_date'] ?? date('Y-m-d');
            $reference = $data['payment_reference'] ?? null;
            
            // First check if invoice exists and is in pending status
            $invoice = $this->getInvoiceById($id);
            if (!$invoice || $invoice['status'] !== 'pending') {
                return false;
            }

            // Update the invoice status
            $query = "UPDATE Invoices 
                     SET status = 'paid', 
                         payment_date = ?, 
                         payment_reference = ?,
                         updated_at = GETDATE()
                     WHERE id = ?";

            $this->db->update($query, [
                $paymentDate,
                $reference,
                $id
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log('Mark Invoice As Paid Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get invoice statistics
     *
     * @return array Statistics about invoices
     */
    public function getStatistics(): array {
        try {
            // Total invoices by status
            $statusQuery = "SELECT status, COUNT(*) as count, SUM(total_amount) as total
                           FROM Invoices
                           GROUP BY status";
            $statusStats = $this->db->select($statusQuery) ?: [];
            
            // Format stats in a more usable way
            $stats = [
                'total_count' => 0,
                'total_amount' => 0,
                'pending_count' => 0,
                'pending_amount' => 0,
                'paid_count' => 0,
                'paid_amount' => 0,
                'cancelled_count' => 0,
                'cancelled_amount' => 0
            ];
            
            foreach ($statusStats as $stat) {
                $status = strtolower($stat['status']);
                $stats[$status . '_count'] = (int)$stat['count'];
                $stats[$status . '_amount'] = (float)$stat['total'];
                
                $stats['total_count'] += (int)$stat['count'];
                $stats['total_amount'] += (float)$stat['total'];
            }
            
            // Add recent invoices
            $recentQuery = "SELECT TOP 5 i.*, s.name as supplier_name
                           FROM Invoices i
                           LEFT JOIN Suppliers s ON i.supplier_id = s.id
                           ORDER BY i.created_at DESC";
            $stats['recent_invoices'] = $this->db->select($recentQuery) ?: [];
            
            return $stats;
        } catch (Exception $e) {
            error_log('Get Invoice Statistics Error: ' . $e->getMessage());
            return [
                'total_count' => 0,
                'total_amount' => 0,
                'pending_count' => 0,
                'pending_amount' => 0,
                'paid_count' => 0,
                'paid_amount' => 0,
                'cancelled_count' => 0,
                'cancelled_amount' => 0,
                'recent_invoices' => []
            ];
        }
    }

    public function uploadDocument($invoiceId, $fileData) {
        try {
            // Check if the invoice exists first
            $invoice = $this->getInvoiceById($invoiceId);
            if (!$invoice) {
                error_log('Invoice not found: ' . $invoiceId);
                return false;
            }
            
            // Use direct PDO connection to ensure compatibility
            $pdo = new PDO(
                "sqlsrv:Server=" . DB1['host'] . ";Database=" . DB1['dbname'] . ";TrustServerCertificate=true", 
                DB1['user'], 
                DB1['pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "INSERT INTO invoice_documents 
                    (invoice_id, file_name, file_path, file_type, file_size, uploaded_by, uploaded_at) 
                    VALUES (?, ?, ?, ?, ?, ?, GETDATE())";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $invoiceId,
                $fileData['name'],
                $fileData['path'],
                $fileData['type'],
                $fileData['size'],
                $_SESSION['user_id']
            ]);
            
            return $result;
        } catch (Exception $e) {
            error_log('Upload Document Error: ' . $e->getMessage());
            return false;
        }
    }

    public function getInvoiceDocuments($invoiceId) {
        try {
            // Use direct PDO connection to ensure compatibility
            $pdo = new PDO(
                "sqlsrv:Server=" . DB1['host'] . ";Database=" . DB1['dbname'] . ";TrustServerCertificate=true", 
                DB1['user'], 
                DB1['pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check if the table exists
            $checkTable = "SELECT CASE WHEN EXISTS (
                            SELECT * FROM INFORMATION_SCHEMA.TABLES 
                            WHERE TABLE_NAME = 'invoice_documents'
                          ) THEN 1 ELSE 0 END AS table_exists";
            
            $checkStmt = $pdo->query($checkTable);
            $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row || $row['table_exists'] != 1) {
                // Table doesn't exist, return empty array
                return [];
            }
            
            // If table exists, get documents
            $sql = "SELECT d.*, u.username as uploaded_by_name 
                    FROM invoice_documents d
                    LEFT JOIN users u ON d.uploaded_by = u.id
                    WHERE d.invoice_id = ? 
                    ORDER BY d.uploaded_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$invoiceId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Get Invoice Documents Error: ' . $e->getMessage());
            return [];
        }
    }

    public function getDocumentById($documentId) {
        try {
            // Use direct PDO connection to ensure compatibility
            $pdo = new PDO(
                "sqlsrv:Server=" . DB1['host'] . ";Database=" . DB1['dbname'] . ";TrustServerCertificate=true", 
                DB1['user'], 
                DB1['pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if the table exists
            $checkTable = "SELECT CASE WHEN EXISTS (
                            SELECT * FROM INFORMATION_SCHEMA.TABLES 
                            WHERE TABLE_NAME = 'invoice_documents'
                          ) THEN 1 ELSE 0 END AS table_exists";
            
            $checkStmt = $pdo->query($checkTable);
            $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row || $row['table_exists'] != 1) {
                // Table doesn't exist, return null
                return null;
            }
            
            // If table exists, get document
            $sql = "SELECT d.*, u.username as uploaded_by_name 
                    FROM invoice_documents d
                    LEFT JOIN users u ON d.uploaded_by = u.id
                    WHERE d.id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$documentId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log('Get Document By ID Error: ' . $e->getMessage());
            return null;
        }
    }

    public function deleteDocument($documentId) {
        try {
            // Use direct PDO connection to ensure compatibility
            $pdo = new PDO(
                "sqlsrv:Server=" . DB1['host'] . ";Database=" . DB1['dbname'] . ";TrustServerCertificate=true", 
                DB1['user'], 
                DB1['pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "DELETE FROM invoice_documents WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$documentId]);
        } catch (Exception $e) {
            error_log('Delete Document Error: ' . $e->getMessage());
            return false;
        }
    }
} 