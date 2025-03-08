<?php

/**
 * Helper function to get team members for the sidebar
 * 
 * @param int $limit Maximum number of members to display
 * @return array Team members for display
 */
function get_sidebar_team_members($limit = 5) {
    return get_sidebar_team_members_direct($limit);
}

/**
 * Get user initials from a name
 * 
 * @param string $name Full name
 * @return string Initials
 */
function get_user_initials($name) {
    $initials = '';
    $nameParts = explode(' ', $name);
    
    if (count($nameParts) > 0) {
        $initials .= substr($nameParts[0], 0, 1);
        
        if (count($nameParts) > 1) {
            $initials .= substr(end($nameParts), 0, 1);
        }
    }
    
    return strtoupper($initials);
}

/**
 * Get team members directly from the database
 * This doesn't rely on the Employee model
 * 
 * @param int $limit Maximum number of members to display
 * @return array Team members for display
 */
function get_sidebar_team_members_direct($limit = 5) {
    static $teamMembers = null;
    
    // Cache the results to avoid multiple database calls
    if ($teamMembers === null) {
        // Set default as empty array in case of errors
        $teamMembers = [];
        
        try {
            // Make sure the DB1 constant is defined
            if (!defined('DB1')) {
                // If DB1 isn't defined, fall back to hardcoded team members for display purposes
                return get_fallback_team_members();
            }
            
            // Check if EasySQL class exists
            if (!class_exists('EasySQL')) {
                // If not, try to load it
                $sqlPath = dirname(dirname(__FILE__)) . '/core/EasySQL.php';
                if (file_exists($sqlPath)) {
                    require_once $sqlPath;
                } else {
                    // Can't load EasySQL, use fallback
                    return get_fallback_team_members();
                }
            }
            
            // Create database connection
            $db = new EasySQL(DB1);
            
            // Query to get team members
            $query = "SELECT TOP $limit id, username, full_name, email, role
                     FROM Users
                     WHERE is_active = 1
                     ORDER BY full_name ASC";
            
            $users = $db->select($query);
            
            // Process query results
            if (!empty($users)) {
                foreach ($users as $user) {
                    // Create initials
                    $name = $user['full_name'] ?? $user['username'];
                    
                    // Map role to title
                    $title = 'Team Member';
                    switch($user['role']) {
                        case 'admin':
                            $title = 'Administrator';
                            break;
                        case 'manager':
                            $title = 'Project Manager';
                            break;
                        case 'developer':
                            $title = 'Developer';
                            break;
                        case 'designer':
                            $title = 'Designer';
                            break;
                    }
                    
                    $teamMembers[] = [
                        'id' => $user['id'],
                        'name' => $name,
                        'title' => $title,
                        'initial' => get_user_initials($name),
                        'email' => $user['email']
                    ];
                }
            }
        } catch (Exception $e) {
            // Log error but don't expose to user
            if (function_exists('error_log')) {
                error_log('Error getting team members: ' . $e->getMessage());
            }
            
            // Fall back to sample team members
            $teamMembers = get_fallback_team_members();
        }
    }
    
    return $teamMembers;
}

/**
 * Get fallback/sample team members when database access fails
 * 
 * @return array Team members
 */
function get_fallback_team_members() {
    return [
        [
            'id' => 1,
            'name' => 'Alex Morgan',
            'title' => 'Project Manager',
            'initial' => 'AM',
            'email' => 'alex@example.com'
        ],
        [
            'id' => 2,
            'name' => 'Sam Wilson',
            'title' => 'Developer',
            'initial' => 'SW',
            'email' => 'sam@example.com'
        ],
        [
            'id' => 3,
            'name' => 'Taylor Lee',
            'title' => 'Designer',
            'initial' => 'TL',
            'email' => 'taylor@example.com'
        ]
    ];
}
?> 