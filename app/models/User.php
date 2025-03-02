<?php

class User {
    private $db;
    
    /**
     * Constructor - initializes the database connection
     */
    public function __construct() {
        $this->db = new EasySQL(DB1);
    }
    
    /**
     * Authenticate a user by username and password
     *
     * @param string $username
     * @param string $password
     * @return array|bool User data if authenticated, false otherwise
     */
    public function authenticate(string $username, string $password): array|bool {
        try {
            // Query to find the user by username
            // Adjust this query based on your actual database schema
            $query = "SELECT * FROM Users WHERE username = ?";
            $result = $this->db->select($query, [$username]);
            
            if (empty($result)) {
                return false; // User not found
            }
            
            $user = $result[0];
            
            // Verify password - this assumes passwords are stored as hashes
            // In a real application, you should use password_hash() and password_verify()
            // For demo purposes, we're doing a simple comparison
            // IMPORTANT: In production, always use secure password hashing
            if ($user['password'] === $password) {
                // Remove password from the user data before returning
                unset($user['password']);
                return $user;
            }
            
            return false; // Invalid password
            
        } catch (Exception $e) {
            // Log the error
            error_log('Authentication Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get a user by their ID
     *
     * @param int $userId
     * @return array|bool User data if found, false otherwise
     */
    public function getUserById(int $userId): array|bool {
        try {
            $query = "SELECT * FROM Users WHERE id = ?";
            $result = $this->db->select($query, [$userId]);
            
            if (empty($result)) {
                return false;
            }
            
            $user = $result[0];
            unset($user['password']); // Don't return the password
            
            return $user;
        } catch (Exception $e) {
            error_log('GetUserById Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all users for assignments
     *
     * @return array Array of users with id, username, and full_name
     */
    public function getAllUsers(): array {
        try {
            $query = "SELECT id, username, full_name FROM Users WHERE is_active = 1 ORDER BY username";
            $result = $this->db->select($query);
            
            return $result ?: [];
        } catch (Exception $e) {
            error_log('GetAllUsers Error: ' . $e->getMessage());
            return [];
        }
    }
}

?> 