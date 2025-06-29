<?php
    // App Root
    define('APPROOT', dirname(dirname(__FILE__)));
    
    // Views Path
    define('VIEWSPATH', APPROOT . '/app/views');
    
    // URL Root (for links)
    define('URLROOT', 'http://' . $_SERVER['HTTP_HOST']);
    
    // Site Name
    define('SITENAME', 'Hive IT Portal');
    
    define('DEFAULT_TITLE', 'Hive IT Portal');
    
    // SQL Server connection
    define('DB1', [
        'type'   => 'sqlsrv',              // Database type: mysql, sqlsrv, pgsql, etc.
        'host'   => '192.168.2.13',        // Hostname or IP address of the database server
        'dbname' => 'ProjectTracker',      // Name of the database
        'user'   => 'ProjectTracker',      // Username for the database
        'pass'   => 'Password'             // Password for the database
    ]);
?>