<?php

class EasySQL {
    private $connection = null;
    private $driver = null;

    // Constructor to initialize the connection
    public function __construct($config = []) {
        if (empty($config) || !isset($config['type'], $config['host'], $config['dbname'], $config['user'], $config['pass'])) {
            throw new Exception('Invalid database configuration. Please provide type, host, dbname, user, and pass.');
        }

        // Build DSN dynamically
        if ($config['type'] === 'sqlsrv') {
            // Use Encrypt=yes along with TrustServerCertificate=1 to bypass certificate validation for self-signed certificates
            $dsn = "sqlsrv:Server={$config['host']};Database={$config['dbname']};TrustServerCertificate=true";
        } else {
            $dsn = "{$config['type']}:host={$config['host']};dbname={$config['dbname']}";
            if ($config['type'] === 'mysql') {
                $dsn .= ";charset=utf8";
            }
        }

        try {
            $this->connection = new PDO($dsn, $config['user'], $config['pass']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
        } catch (PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            throw new Exception('Failed to connect to the database.');
        }
    }


    // Insert a row and return the last inserted ID
    public function insert($statement, $parameters = []) {
        try {
            $this->validateSQL($statement); // Validate the SQL statement
            
            // Handle different drivers for getting inserted ID
            if ($this->driver === 'mysql') {
                $this->executeStatement($statement, $parameters);
                return $this->connection->lastInsertId();
            } else if ($this->driver === 'sqlsrv') {
                // For SQL Server, check if table has triggers that conflict with OUTPUT clause
                $tablePattern = '/INSERT\s+INTO\s+(?:dbo\.)?(\w+)/i';
                preg_match($tablePattern, $statement, $tableMatches);
                $tableName = $tableMatches[1] ?? '';
                
                // Tables known to have triggers that conflict with OUTPUT clause
                $tablesWithTriggers = ['Tickets', 'TimeEntries'];
                
                if (in_array($tableName, $tablesWithTriggers)) {
                    // Use traditional SCOPE_IDENTITY approach for tables with triggers
                    $insertStmt = $this->executeStatement($statement, $parameters);
                    
                    if ($insertStmt) {
                        // Get the last inserted ID using a separate query
                        $scopeStmt = $this->executeStatement("SELECT SCOPE_IDENTITY() as id");
                        $scopeResult = $scopeStmt->fetch();
                        $insertedId = $scopeResult['id'] ?? null;
                        
                        // Convert to integer if it's a valid ID
                        return $insertedId ? (int)$insertedId : null;
                    }
                    return null;
                } else {
                    // For other tables, use OUTPUT clause approach
                    if (stripos($statement, 'OUTPUT') === false) {
                        $pattern = '/INSERT\s+INTO\s+\w+\s*\([^)]+\)\s*VALUES/i';
                        if (preg_match($pattern, $statement, $matches)) {
                            $statement = preg_replace(
                                '/INSERT\s+INTO\s+(\w+)\s*\(([^)]+)\)\s*VALUES/i',
                                'INSERT INTO $1 ($2) OUTPUT INSERTED.id VALUES',
                                $statement
                            );
                        }
                    }
                    
                    $stmt = $this->executeStatement($statement, $parameters);
                    $result = $stmt->fetch();
                    return $result['id'] ?? null;
                }
            }
            
            // Fallback for other drivers
            $this->executeStatement($statement, $parameters);
            return null;
        } catch (PDOException $e) {
            throw new Exception('Insert Error: ' . $e->getMessage());
        }
    }

    // Select rows and return the result
    public function select($statement, $parameters = []) {
        try {
            $this->validateSQL($statement); // Validate the SQL statement
            $stmt = $this->executeStatement($statement, $parameters);
            $results = $stmt->fetchAll();
            return $results;
        } catch (PDOException $e) {
            throw new Exception('Select Error: ' . $e->getMessage());
        }
    }

    // Update rows
    public function update($statement, $parameters = []) {
        try {
            $this->validateSQL($statement); // Validate the SQL statement
            $this->executeStatement($statement, $parameters);
        } catch (PDOException $e) {
            throw new Exception('Update Error: ' . $e->getMessage());
        }
    }

    // Remove rows
    public function remove($statement, $parameters = []) {
        try {
            $this->validateSQL($statement); // Validate the SQL statement
            $this->executeStatement($statement, $parameters);
        } catch (PDOException $e) {
            throw new Exception('Remove Error: ' . $e->getMessage());
        }
    }

    // Execute a prepared statement
    private function executeStatement($statement, $parameters = []) {
        $stmt = null;
        try {
            $stmt = $this->connection->prepare($statement);
            $stmt->execute($parameters);
            return $stmt;
        } catch (PDOException $e) {
            $errorInfo = $stmt ? $stmt->errorInfo() : null;
            $errorMessage = isset($errorInfo[2]) ? $errorInfo[2] : $e->getMessage();
            error_log("SQL Error ({$this->driver}): " . $errorMessage);
            throw new Exception('SQL Execution Failed: ' . $errorMessage);
        }
    }

    // Validate SQL to prevent risky statements
    private function validateSQL($statement) {
        $prohibitedKeywords = ['DROP', '--', '#', 'ALTER']; // Limited validation
        foreach ($prohibitedKeywords as $keyword) {
            if (stripos($statement, $keyword) !== false) {
                throw new Exception('Potentially harmful SQL detected: ' . $keyword);
            }
        }
    }

    // Execute a query and return the statement
    public function query($statement) {
        try {
            $stmt = $this->connection->query($statement);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception('Query Error: ' . $e->getMessage());
        }
    }

    // Begin a transaction
    public function beginTransaction() {
        try {
            // Check if we're already in a transaction
            if ($this->connection->inTransaction()) {
                error_log("Warning: Attempted to begin a transaction while already in one.");
                return false;
            }
            return $this->connection->beginTransaction();
        } catch (PDOException $e) {
            error_log('Begin Transaction Error: ' . $e->getMessage());
            throw new Exception('Begin Transaction Error: ' . $e->getMessage());
        }
    }
    
    // Commit a transaction
    public function commitTransaction() {
        try {
            // Check if we're in a transaction
            if (!$this->connection->inTransaction()) {
                error_log("Warning: Attempted to commit when no transaction is active.");
                return false;
            }
            return $this->connection->commit();
        } catch (PDOException $e) {
            error_log('Commit Transaction Error: ' . $e->getMessage());
            throw new Exception('Commit Transaction Error: ' . $e->getMessage());
        }
    }
    
    // Rollback a transaction
    public function rollbackTransaction() {
        try {
            // Check if we're in a transaction
            if (!$this->connection->inTransaction()) {
                error_log("Warning: Attempted to rollback when no transaction is active.");
                return false;
            }
            return $this->connection->rollBack();
        } catch (PDOException $e) {
            error_log('Rollback Transaction Error: ' . $e->getMessage());
            throw new Exception('Rollback Transaction Error: ' . $e->getMessage());
        }
    }
    
    // Check if we're in a transaction
    public function inTransaction() {
        return $this->connection->inTransaction();
    }

    // Close the connection
    public function disconnect() {
        $this->connection = null;
    }
}

?>
