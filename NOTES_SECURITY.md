# Notes Security Documentation

## Overview
Notes in ProjectTracker have a flexible visibility system:
- **Personal notes** are private by default
- **Project/Task notes** are automatically visible to team members
- Notes can be **explicitly shared** with other users

## Security Model

### 1. Personal Notes
- Private by default - only visible to the creator
- Can be shared with specific users (view or edit permissions)
- Owners can manage sharing at any time

### 2. Project Notes
- **Automatically visible** to all project team members
- Any user who is a member of the project can view these notes
- Helps team collaboration on project-related information

### 3. Task Notes
- **Automatically visible** to:
  - The task creator
  - The task assignee
- Ensures relevant parties have access to task-related information

### Regular Users (user, employee, supervisor, team_lead)
- Can see their own notes
- Can see notes explicitly shared with them
- Can see project notes (if they're project members)
- Can see task notes (if they're the creator or assignee)
- Cannot be granted global 'notes.read' permission

### Admin/Manager Roles
- Can see ALL notes from all users
- Can search ALL notes
- This is for oversight and management purposes

## Implementation Details

### Search Controller (`app/controllers/Search.php`)
```php
// Only admins and managers can see all notes
$hasFullNotesAccess = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager']);
```

### Permission Helper (`app/helpers/search_permissions.php`)
- The `notes.read` permission is NOT granted to any regular role
- Only admin/manager roles have implicit access to all notes

### Database Filtering (`app/models/Note.php`)
The `searchNotesSecure()` method filters notes:
- If `$hasFullAccess` is false, adds `AND n.created_by = ?` to the query
- This ensures database-level filtering for security

## Important Notes

1. **DO NOT** grant `notes.read` permission to regular users
2. **DO NOT** modify the search logic to be more permissive
3. Notes contain potentially sensitive information and must remain private

## Testing
To verify notes security:
1. Log in as a regular user
2. Create some notes
3. Log in as a different regular user
4. Search for the first user's notes - they should NOT appear
5. Log in as admin/manager - all notes should be visible

Last Updated: 2024-01-09 