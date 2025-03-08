<?php
// Include necessary files
require_once 'config/config.php';
require_once 'app/core/EasySQL.php';
require_once 'app/models/Employee.php';

// Test the getTeamMembers function
$employeeModel = new Employee();
$teamMembers = $employeeModel->getTeamMembers();

echo "Team Members from Database:\n";
echo "==========================\n";

if (empty($teamMembers)) {
    echo "No team members found. Make sure you have active users in your database.\n";
} else {
    foreach ($teamMembers as $member) {
        echo "Name: " . $member['name'] . "\n";
        echo "Title: " . $member['title'] . "\n";
        echo "Initial: " . $member['initial'] . "\n";
        echo "--------------------------\n";
    }
}

echo "\nIf you don't see team members, check if there are active users in your Users table.\n";
echo "You might need to add more users or update existing ones to be active.\n";
?> 