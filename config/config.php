<?php
    define('DEFAULT_TITLE', 'ProjectTracker');
    
    // SQL Server connection
    define('DB1', [
        'type'   => 'sqlsrv',              // Database type: mysql, sqlsrv, pgsql, etc.
        'host'   => '192.168.2.13',        // Hostname or IP address of the database server
        'dbname' => 'ProjectTracker',      // Name of the database
        'user'   => 'ProjectTracker',                  // Username for the database
        'pass'   => 'Password'             // Password for the database
    ]);
?>