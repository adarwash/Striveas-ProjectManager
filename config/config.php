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
    
    // Microsoft 365 / Azure OAuth Configuration
    // To enable Microsoft 365 calendar integration, uncomment and configure these:
    // 1. Register an app in Azure Active Directory
    // 2. Add Microsoft Graph API permissions: Calendars.Read, offline_access
    // 3. Set redirect URI to: http://yourdomain.com/dashboard/microsoftCallback
    // 4. Get the Application (client) ID and create a client secret
    // 5. Uncomment and set these values:
    
    // define('MICROSOFT_CLIENT_ID', 'your-azure-app-client-id');
    // define('MICROSOFT_CLIENT_SECRET', 'your-azure-app-client-secret');
    
    // Alternative: You can also set these as environment variables:
    // - MICROSOFT_CLIENT_ID
    // - MICROSOFT_CLIENT_SECRET
?>