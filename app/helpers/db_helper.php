<?php
/**
 * Database helper functions
 */

/**
 * Check if a column exists in a table
 * 
 * @param EasySQL $db Database connection
 * @param string $table Table name
 * @param string $column Column name
 * @return bool True if column exists, false otherwise
 */
function column_exists($db, $table, $column) {
    try {
        $query = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_NAME = ? AND COLUMN_NAME = ?";
        $result = $db->select($query, [$table, $column]);
        return !empty($result) && $result[0]['count'] > 0;
    } catch (Exception $e) {
        error_log('Error checking if column exists: ' . $e->getMessage());
        return false;
    }
}

/**
 * Add currency column to departments table
 * 
 * @return bool True if successful, false otherwise
 */
function add_currency_column() {
    try {
        $db = new EasySQL(DB1);
        
        // Check if column exists
        if (!column_exists($db, 'departments', 'currency')) {
            error_log('Currency column does not exist. Adding it to departments table.');
            
            try {
                // Add currency column with default value
                $query = "ALTER TABLE departments ADD currency NVARCHAR(3) NOT NULL DEFAULT 'USD'";
                $db->query($query);
                error_log('Currency column added successfully.');
                
                // Update existing records
                $query = "UPDATE departments SET currency = 'USD' WHERE currency IS NULL";
                $db->query($query);
                error_log('Existing departments updated with default currency USD.');
                
                return true;
            } catch (Exception $e) {
                error_log('Error adding currency column: ' . $e->getMessage());
                // If the error is about a constraint or the column already exists, 
                // we can continue with our app
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    error_log('Currency column already exists despite check. Continuing.');
                    return true;
                }
                return false;
            }
        } else {
            error_log('Currency column already exists in departments table.');
            return true; // Column already exists
        }
    } catch (Exception $e) {
        error_log('Error in add_currency_column function: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get SQL Server error information
 * 
 * @param Exception $e The exception caught
 * @return string Formatted error message
 */
function get_sql_error_info($e) {
    $error = $e->getMessage();
    $errorInfo = '';
    
    // Extract error code and message for SQL Server errors
    if (preg_match('/SQLSTATE\[(\w+)\].*?: (\d+) (.*)/', $error, $matches)) {
        $sqlstate = $matches[1];
        $errorCode = $matches[2];
        $errorMessage = $matches[3];
        
        $errorInfo = "SQL Error: SQLSTATE[$sqlstate], Code: $errorCode, Message: $errorMessage";
    } else {
        $errorInfo = "Error: $error";
    }
    
    return $errorInfo;
}
?> 