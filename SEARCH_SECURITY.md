# Enhanced Search Security

## Overview

The universal search functionality has been enhanced with comprehensive permission-based security to ensure users only see results they have permission to access.

## Security Features

### 1. **Permission-Based Entity Access**
- Users can only search entity types they have permissions for
- Each entity type (projects, tasks, users, clients, notes) has specific permission requirements
- Fallback role-based permissions for systems without full permission tables

### 2. **Item-Level Security**
- Individual items are filtered based on ownership and permissions
- Users can only see items they created, are assigned to, or have specific read permissions for
- Admins and managers have full access to all items

### 3. **Permission Requirements**

| Entity Type | Required Permissions | Fallback Access |
|-------------|---------------------|-----------------|
| Projects    | `projects.read`     | Owner/Creator   |
| Tasks       | `tasks.read`        | Assignee/Creator |
| Users       | `users.read` OR `reports_read` | Permission required |
| Clients     | `clients.read`      | Permission required |
| Notes       | `notes.read`        | Owner/Creator   |

### 4. **Role-Based Fallbacks**

When the full permission system is not available, the search uses role-based access:

- **Admin/Manager**: Full access to all content
- **Supervisor**: Access to projects, tasks, users, clients, notes, reports
- **Employee**: Access to projects, tasks, users, notes
- **Team Lead**: Access to projects, tasks, users, notes
- **User**: Access to tasks, notes only

## Implementation Details

### Permission Checking Flow

1. **Entity-Level Check**: Verify user can search the entity type
2. **Search Execution**: Perform database search for allowed entities
3. **Item-Level Filtering**: Filter results based on ownership/permissions
4. **Response**: Return only authorized results

### Security Logging

- All search queries are logged with user ID for security auditing
- Permission denied attempts are logged separately
- Error handling prevents information leakage

### API Endpoints

#### `/search` - Main Search
- Supports `q` (query), `type` (entity type), `limit` parameters
- Returns filtered results based on user permissions
- Includes permission information in response

#### `/search/permissions` - Debug Permissions
- Shows user's search permissions for each entity type
- Useful for troubleshooting access issues
- Admin/development use only

### Frontend Integration

- Enhanced error handling for permission-denied scenarios
- User-friendly messages for access restrictions
- Automatic filtering of search type options based on permissions

## Configuration

### Required Permissions

Ensure these permissions exist in your system:

```sql
INSERT INTO Permissions (name, description) VALUES
('projects.read', 'View projects'),
('tasks.read', 'View tasks'),
('users.read', 'View users'),
('clients.read', 'View clients'),
('notes.read', 'View notes'),
('reports_read', 'View reports and analytics');
```

### Role Assignment

Assign appropriate permissions to roles based on your organizational structure.

## Testing

### Test User Permissions
```
GET /search/permissions
```

### Test Search with Different Users
```
GET /search?q=test&type=all
```

## Security Considerations

1. **Information Leakage**: Search results don't reveal existence of unauthorized content
2. **Performance**: Permission checks are optimized to avoid N+1 queries
3. **Fallback Security**: Multiple layers ensure access control even if one system fails
4. **Audit Trail**: All search activity is logged for security review

## Troubleshooting

### No Search Results
1. Check user permissions with `/search/permissions`
2. Verify role assignments
3. Check if permission tables exist and are populated
4. Review error logs for permission failures

### Performance Issues
1. Ensure database indexes on permission-related columns
2. Monitor permission check query performance
3. Consider caching for frequently accessed permissions

## Future Enhancements

- Field-level permissions for sensitive data
- Search result ranking based on relevance and permissions
- Advanced audit logging with search analytics
- Integration with external authorization systems 