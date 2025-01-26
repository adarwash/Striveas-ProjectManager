<?php

class EasySQL {
    private $connection = null;

    // Constructor to initialize the connection
    public function __construct($config = []) {
        if (empty($config) || !isset($config['dsn'], $config['user'], $config['pass'])) {
            throw new Exception('Invalid database configuration. Please provide dsn, user, and pass.');
        }

        try {
            $this->connection = new PDO($config['dsn'], $config['user'], $config['pass']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            throw new Exception('Failed to connect to the database.');
        }
    }

    // Insert a row and return the last inserted ID
    public function insert($statement, $parameters = []) {
        try {
            $this->validateSQL($statement); // Validate the SQL statement
            $this->executeStatement($statement, $parameters);
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception('Insert Error: ' . $e->getMessage());
        }
    }

    // Select rows and return the result
    public function select($statement, $parameters = []) {
        try {
            $this->validateSQL($statement); // Validate the SQL statement
            $stmt = $this->executeStatement($statement, $parameters);
            return $stmt->fetchAll();
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
        try {
            $stmt = $this->connection->prepare($statement);
            $stmt->execute($parameters);
            return $stmt;
        } catch (PDOException $e) {
            error_log('SQL Execution Error: ' . $e->getMessage());
            throw new Exception('SQL Execution Failed.');
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

    // Close the connection
    public function disconnect() {
        $this->connection = null;
    }
}

?>
