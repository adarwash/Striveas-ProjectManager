<?php
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;
    private $stmt;
    private $error;

    public function __construct() {
        // Set DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        );

        // Create PDO instance
        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database Connection Error: " . $this->error);
            throw new Exception("Database connection failed");
        }
    }

    // Prepare statement with query
    public function query($sql) {
        $this->stmt = $this->conn->prepare($sql);
    }

    // Bind values
    public function bind($param, $value, $type = null) {
        if(is_null($type)) {
            switch(true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        $this->stmt->bindValue($param, $value, $type);
    }

    // Execute the prepared statement
    public function execute() {
        return $this->stmt->execute();
    }

    // Get result set as array of objects
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    // Get single record as object
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    // Get row count
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    // Get last inserted ID
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }

    // Begin Transaction
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    // End Transaction
    public function endTransaction() {
        return $this->conn->commit();
    }

    // Cancel Transaction
    public function cancelTransaction() {
        return $this->conn->rollBack();
    }

    // Select records
    public function select($query, $params = []) {
        $this->query($query);
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $this->bind($key + 1, $value);
            }
        }
        return $this->resultSet();
    }

    // Insert record
    public function insert($query, $params = []) {
        try {
            $this->query($query);
            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $this->bind($key + 1, $value);
                }
            }
            $this->execute();
            return $this->lastInsertId();
        } catch (PDOException $e) {
            error_log("Insert Error: " . $e->getMessage());
            return false;
        }
    }

    // Update record
    public function update($query, $params = []) {
        try {
            $this->query($query);
            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $this->bind($key + 1, $value);
                }
            }
            return $this->execute();
        } catch (PDOException $e) {
            error_log("Update Error: " . $e->getMessage());
            return false;
        }
    }

    // Delete record
    public function delete($query, $params = []) {
        try {
            $this->query($query);
            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $this->bind($key + 1, $value);
                }
            }
            return $this->execute();
        } catch (PDOException $e) {
            error_log("Delete Error: " . $e->getMessage());
            return false;
        }
    }
} 