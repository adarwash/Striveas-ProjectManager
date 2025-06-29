<?php
/**
 * Database Class
 * 
 * Singleton pattern for database connections
 */
class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        $this->connection = new EasySQL(DB1);
    }
    
    /**
     * Get singleton instance
     * 
     * @return EasySQL Database connection
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }
    
    /**
     * Clone is not allowed
     */
    private function __clone() {}
    
    /**
     * Wakeup is not allowed
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
} 