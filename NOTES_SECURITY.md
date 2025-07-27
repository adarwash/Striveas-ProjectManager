# Notes Security Documentation

## Overview
Notes in ProjectTracker are **private by default**. This means users can only see and search their own notes.

## Security Model

### Regular Users (user, employee, supervisor, team_lead)
- Can ONLY see their own notes
- Can ONLY search their own notes
- Cannot be granted 'notes.read' permission
- Notes are filtered at the database level by `created_by = user_id`

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