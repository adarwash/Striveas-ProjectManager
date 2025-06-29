# Permission Management System

## Overview

I've built a comprehensive role-based permission management system for your ProjectTracker application. This system allows you to:

- Create and manage roles with different access levels
- Assign granular permissions to roles
- Manage individual user permissions
- Control access to different modules and actions

## Database Structure

The permission system adds the following tables:

### Roles Table
- Stores role definitions (Super Admin, Admin, Manager, Employee, Client, Viewer)
- Each role has a name, display name, and description

### Permissions Table
- Stores individual permissions organized by module and action
- Examples: `users.create`, `projects.read`, `tasks.update`, `admin.permissions`

### RolePermissions Table
- Maps roles to their assigned permissions
- Allows bulk permission assignment by role

### UserPermissions Table
- Allows individual permission overrides for specific users
- Can grant or deny permissions on a per-user basis

## Setup Instructions

1. **Run the SQL Script**:
   ```sql
   -- Execute the permissions table creation script
   USE ProjectTracker;
   -- Run the content from sql/create_permissions_tables.sql
   ```

2. **Access the Permission Management**:
   - Navigate to `/permissions` in your application
   - Only users with `admin.permissions` permission can access this area

## Features

### 1. Permission Dashboard
- Overview of all roles and permissions
- Quick statistics and navigation
- Accessible at `/permissions`

### 2. Role Management (`/permissions/roles`)
- Create new roles
- Edit existing roles
- Delete unused roles (with safety checks)
- View role statistics (user count, permission count)

### 3. Role Permission Assignment (`/permissions/role_permissions/{role_id}`)
- Assign permissions to roles by module
- Interactive checkboxes for easy management
- Real-time counter updates
- Module-level select all/deselect all

### 4. User Permission Management (`/permissions/user_permissions`)
- View all users and their current roles
- Assign individual permissions to users
- Override role-based permissions when needed

## Permission Structure

### Modules
- **users**: User management operations
- **projects**: Project-related operations
- **tasks**: Task management
- **reports**: Reporting and analytics
- **invoices**: Financial operations
- **admin**: System administration
- **departments**: Department management

### Actions
- **create**: Create new records
- **read**: View existing records
- **update**: Modify existing records
- **delete**: Remove records
- **manage**: Advanced management operations

### Default Roles and Permissions

1. **Super Administrator**
   - All permissions across all modules

2. **Administrator**
   - Most permissions except system maintenance and permission management

3. **Manager**
   - Project and task management
   - User viewing
   - Basic reporting

4. **Employee**
   - Basic project and task operations
   - View-only reports

5. **Client**
   - Limited project viewing
   - Basic reports

6. **Viewer**
   - Read-only access to assigned content

## Usage Examples

### Checking Permissions in Controllers
```php
// Check if user has specific permission
if (!$this->permissionModel->userHasPermission($_SESSION['user_id'], 'users.create')) {
    flash('access_denied', 'You do not have permission to create users', 'alert alert-danger');
    redirect('dashboard');
}
```

### Using in Views
```php
<?php if (isset($_SESSION['user_id']) && $userModel->hasPermission($_SESSION['user_id'], 'admin.settings')): ?>
    <a href="/admin/settings" class="btn btn-primary">System Settings</a>
<?php endif; ?>
```

## Security Features

- **Immediate Effect**: Permission changes take effect immediately
- **Cascading Permissions**: Role-based permissions with individual overrides
- **Safety Checks**: Prevents deletion of roles with assigned users
- **Audit Trail**: All permission changes are logged
- **Access Control**: Only authorized users can manage permissions

## Navigation

The permission management is integrated into the admin sidebar:
- Navigate to **Admin** → **Permissions**
- Available only to users with admin role and `admin.permissions` permission

## File Structure

```
app/
├── controllers/
│   └── Permissions.php          # Main permissions controller
├── models/
│   ├── Permission.php           # Permission model
│   ├── Role.php                 # Role model
│   └── User.php                 # Updated with permission methods
└── views/
    └── admin/
        └── permissions/
            ├── index.php        # Main dashboard
            ├── roles.php        # Role management
            └── role_permissions.php  # Role permission assignment

sql/
└── create_permissions_tables.sql   # Database schema
```

## API Methods

### Permission Model
- `getAllPermissions()` - Get all available permissions
- `userHasPermission($userId, $permissionName)` - Check user permission
- `getUserPermissions($userId)` - Get user's permission list
- `syncUserPermissions($userId, $permissionIds)` - Update user permissions

### Role Model
- `getAllRoles()` - Get all roles
- `createRole($data)` - Create new role
- `syncRolePermissions($roleId, $permissionIds)` - Update role permissions
- `getRolePermissions($roleId)` - Get role's permissions

### User Model
- `hasPermission($userId, $permissionName)` - Check if user has permission
- `getUserPermissions($userId)` - Get user's permission list
- `updateUserRoleId($data)` - Update user's role

## Best Practices

1. **Principle of Least Privilege**: Grant only necessary permissions
2. **Role-Based Assignment**: Use roles for common permission sets
3. **Individual Overrides**: Use user permissions sparingly for exceptions
4. **Regular Audits**: Review permissions periodically
5. **Test Changes**: Verify permission changes in development first

## Troubleshooting

### Common Issues
1. **Access Denied**: Ensure user has proper permissions assigned
2. **Database Errors**: Verify all tables are created correctly
3. **Role Assignment**: Check if user has a role assigned

### Debugging
- Check the system logs for permission-related errors
- Verify database table structure matches the schema
- Ensure proper foreign key relationships

This permission system provides enterprise-level access control while maintaining ease of use and flexibility for your ProjectTracker application. 