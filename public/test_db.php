<?php
// /var/www/ProjectTracker/public/test_db.php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define constants if not already defined
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'sa');
if (!defined('DB_PASS')) define('DB_PASS', 'Hive2024#');
if (!defined('DB_NAME')) define('DB_NAME', 'ProjectTracker');

echo "<h1>Database Connection Test</h1>";
echo "<p>Attempting to connect to database '<strong>" . DB_NAME . "</strong>' on host '<strong>" . DB_HOST . "</strong>' with user '<strong>" . DB_USER . "</strong>'.</p>";

try {
    // 1. Test PDO Connection
    echo "<h2>1. Testing PDO Connection...</h2>";
    $pdo = new PDO("sqlsrv:server=" . DB_HOST . ";Database=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green;'>PDO connection successful!</p>";

    // 2. Fetch Ticket Details
    echo "<h2>2. Fetching Ticket #176 Details...</h2>";
    $ticketId = 176;
    $stmt = $pdo->prepare("SELECT * FROM TicketDashboard WHERE id = :id");
    $stmt->execute(['id' => $ticketId]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ticket) {
        echo "<p style='color:green;'>Successfully fetched ticket #176.</p>";
        echo "<pre>" . print_r($ticket, true) . "</pre>";
    } else {
        echo "<p style='color:red;'>Failed to fetch ticket #176. Ticket not found.</p>";
    }

    // 3. Fetch Ticket Messages
    echo "<h2>3. Fetching Messages for Ticket #176...</h2>";
    $stmt = $pdo->prepare("SELECT * FROM TicketMessages WHERE ticket_id = :ticket_id ORDER BY created_at ASC");
    $stmt->execute(['ticket_id' => $ticketId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($messages) {
        echo "<p style='color:green;'>Successfully fetched " . count($messages) . " messages.</p>";
        echo "<pre>" . print_r($messages, true) . "</pre>";
    } else {
        echo "<p style='color:orange;'>No messages found in TicketMessages for ticket #176.</p>";
    }

    // 4. Fetch Original Email from EmailInbox
    if ($ticket && $ticket['source'] === 'email') {
        echo "<h2>4. Fetching Original Email from EmailInbox...</h2>";
        $stmt = $pdo->prepare("SELECT TOP 1 * FROM EmailInbox WHERE ticket_id = :ticket_id ORDER BY id ASC");
        $stmt->execute(['ticket_id' => $ticketId]);
        $originalEmail = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($originalEmail) {
            echo "<p style='color:green;'>Successfully fetched original email.</p>";
            echo "<pre>" . print_r($originalEmail, true) . "</pre>";
        } else {
            echo "<p style='color:orange;'>No original email found in EmailInbox for ticket #176.</p>";
        }
    } else {
        echo "<h2>4. Original Email Fetch Skipped</h2>";
        echo "<p>Ticket source is not 'email'.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red;'><strong>Connection failed:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Troubleshooting Tips:</strong></p>";
    echo "<ul>";
    echo "<li>Verify the database server is running and accessible.</li>";
    echo "<li>Check that the database name, username, and password are correct in your configuration.</li>";
    echo "<li>Ensure the SQL Server user 'sa' has permission to connect from this web server.</li>";
    echo "<li>Check the SQL Server error logs for more details.</li>";
    echo "</ul>";
}




