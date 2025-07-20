# Enhanced Granular Permission System - Setup Guide

## 🎯 Overview

This enhanced permission system extends your existing role-based permissions with:
- **Resource-level permissions** (project/task/user specific)
- **Field-level permissions** (control access to individual form fields)
- **Contextual permissions** (automatic permissions based on relationships)
- **Time-based permissions** (permissions that expire)
- **Permission groups** (predefined permission sets)

## 📋 Setup Steps

### 1. Database Setup

Run the enhanced permissions SQL script:

```sql
-- Execute the enhanced permissions script
USE ProjectTracker;
-- Run the content from sql/enhanced_permissions_system.sql
```

This creates these new tables:
- `ResourcePermissions` - Project/task/user specific permissions
- `FieldPermissions` - Field-level access control
- `ContextualPermissions` - Relationship-based permissions
- `PermissionGroups` - Predefined permission sets
- `PermissionGroupMembers` - Group membership

### 2. Include the Enhanced Permission Model

Add the enhanced permission model to your autoloader or include it in controllers:

```php
require_once 'app/models/EnhancedPermission.php';
require_once 'app/helpers/enhanced_permissions_helper.php';
```

### 3. Update Your Controllers

Replace basic permission checks with enhanced permission checking:

```php
// Before: Basic permission check
if (!hasPermission('projects.read')) {
    redirect('dashboard');
}

// After: Enhanced permission check with context
if (!canAccessProject($projectId, 'read')) {
    redirect('dashboard');
}
```

### 4. Update Your Views

Use the new permission helper functions in your views:

```php
<!-- Budget section - only show if user has permission -->
<?php if (canViewBudget($project->id)): ?>
    <div class="budget-section">
        <h5>Budget: $<?= number_format($project->budget, 2) ?></h5>
        
        <!-- Edit button - only if user can edit budget -->
        <?php if (canEditBudget($project->id)): ?>
            <a href="/projects/editBudget/<?= $project->id ?>" class="btn btn-primary">Edit Budget</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Field-level permissions -->
<?php $fieldAccess = getFieldAccessLevel('projects', 'description', $project->id); ?>
<?php if ($fieldAccess !== 'hidden'): ?>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description" 
                  <?= $fieldAccess === 'read' ? 'readonly' : '' ?>
                  class="form-control"><?= $project->description ?></textarea>
    </div>
<?php endif; ?>
```

## 🔧 Configuration Examples

### 1. Set Up Resource-Level Permissions

Grant specific permissions for a project:

```php
$enhancedPermission = new EnhancedPermission();

// Grant user permission to view budget for specific project
$enhancedPermission->grantResourcePermission(
    $userId, 
    'projects.view_budget', 
    'project', 
    $projectId,
    $_SESSION['user_id'], // granted by
    '2024-12-31 23:59:59' // expires at (optional)
);

// Grant user permission to edit specific task
$enhancedPermission->grantResourcePermission(
    $userId, 
    'tasks.update', 
    'task', 
    $taskId
);
```

### 2. Set Up Field-Level Permissions

Control access to specific fields:

```php
// Hide salary field for this user
$enhancedPermission->grantFieldPermission($userId, 'users', 'salary', 'hidden');

// Make budget field read-only for this user
$enhancedPermission->grantFieldPermission($userId, 'projects', 'budget', 'read');

// Give full write access to description field
$enhancedPermission->grantFieldPermission($userId, 'projects', 'description', 'write');
```

### 3. Set Up Contextual Permissions

Grant permissions based on relationships:

```php
// When a user joins a project team, they automatically get certain permissions
// This is handled automatically by the system, but you can also grant explicit ones:

$enhancedPermission->grantContextualPermission(
    $userId, 
    'project_member', 
    $projectId, 
    'projects.read'
);

$enhancedPermission->grantContextualPermission(
    $userId, 
    'task_assignee', 
    $taskId, 
    'tasks.update'
);
```

### 4. Use Permission Groups

Create and assign permission groups:

```php
// Assign a permission group to a user
$enhancedPermission->assignPermissionGroupToUser($userId, $groupId);

// This automatically grants all permissions in that group
```

## 🎨 Permission Helper Functions

### Basic Permission Checking
```php
// Check if user can access specific project
if (canAccessProject($projectId, 'read')) {
    // Show project details
}

// Check if user can access specific task
if (canAccessTask($taskId, 'update')) {
    // Show edit button
}

// Check field access level
$accessLevel = getFieldAccessLevel('projects', 'budget', $projectId);
// Returns: 'read', 'write', or 'hidden'
```

### Context-Aware Permissions
```php
// Check if user is project team member
if (isProjectTeamMember($projectId)) {
    // Show team-specific features
}

// Check if user is task assignee
if (isTaskAssignee($taskId)) {
    // Show assignee-specific options
}
```

### Financial Permissions
```php
// Check budget access
if (canViewBudget($projectId)) {
    // Show budget information
}

if (canEditBudget($projectId)) {
    // Show budget edit controls
}

// Check general financial access
if (canAccessFinancials()) {
    // Show financial reports
}
```

### Advanced Permission Checking
```php
// Check multiple permissions
if (hasAnyEnhancedPermission(['projects.read', 'projects.update'], $context)) {
    // User has at least one permission
}

if (hasAllEnhancedPermissions(['projects.read', 'projects.update'], $context)) {
    // User has all permissions
}
```

## 🔄 Migration from Basic to Enhanced Permissions

### Step 1: Update Controllers Gradually

Start with one controller at a time:

```php
// OLD: Basic permission check
class Projects extends Controller {
    public function view($id) {
        if (!hasPermission('projects.read')) {
            redirect('dashboard');
        }
        // ... rest of method
    }
}

// NEW: Enhanced permission check
class Projects extends Controller {
    public function view($id) {
        if (!canAccessProject($id, 'read')) {
            redirect('dashboard');
        }
        // ... rest of method
    }
}
```

### Step 2: Update Views Gradually

Replace permission checks in views:

```php
<!-- OLD: Basic permission check -->
<?php if (hasPermission('projects.update')): ?>
    <a href="/projects/edit/<?= $project->id ?>" class="btn btn-primary">Edit</a>
<?php endif; ?>

<!-- NEW: Enhanced permission check -->
<?php if (canAccessProject($project->id, 'update')): ?>
    <a href="/projects/edit/<?= $project->id ?>" class="btn btn-primary">Edit</a>
<?php endif; ?>
```

### Step 3: Add Field-Level Controls

Enhance forms with field-level permissions:

```php
<!-- Check each field's access level -->
<?php $fields = ['title', 'description', 'budget', 'start_date', 'end_date']; ?>
<?php foreach ($fields as $field): ?>
    <?php $accessLevel = getFieldAccessLevel('projects', $field, $project->id); ?>
    <?php if ($accessLevel !== 'hidden'): ?>
        <div class="form-group">
            <label><?= ucfirst(str_replace('_', ' ', $field)) ?></label>
            <input type="text" 
                   name="<?= $field ?>" 
                   value="<?= $project->$field ?>"
                   <?= $accessLevel === 'read' ? 'readonly' : '' ?>
                   class="form-control">
        </div>
    <?php endif; ?>
<?php endforeach; ?>
```

## 🎯 Best Practices

### 1. Use Contextual Permissions for Team Relationships
- Automatically grant permissions when users join project teams
- Use `isProjectTeamMember()` and `isTaskAssignee()` for UI decisions

### 2. Use Field-Level Permissions for Sensitive Data
- Hide salary information from non-HR users
- Make budget fields read-only for team members
- Hide internal notes from clients

### 3. Use Resource-Level Permissions for Specific Access
- Grant project-specific budget access to project managers
- Allow task-specific editing for assignees
- Give time-limited access for contractors

### 4. Use Permission Groups for Common Patterns
- Create "Project Manager" group with standard PM permissions
- Create "Financial Viewer" group for budget access
- Create "Team Lead" group for team management

### 5. Regular Cleanup
Set up a cron job to clean expired permissions:

```php
// Clean up expired permissions daily
$enhancedPermission = new EnhancedPermission();
$enhancedPermission->cleanupExpiredPermissions();
```

## 🔍 Debugging Permissions

Add permission debugging for administrators:

```php
<?php if (hasPermission('admin.permissions')): ?>
    <div class="alert alert-info">
        <h6>Permission Debug (Admin Only)</h6>
        <small>
            Your permissions for this resource:<br>
            • Can View: <?= canAccessProject($project->id, 'read') ? '✅' : '❌' ?><br>
            • Can Edit: <?= canAccessProject($project->id, 'update') ? '✅' : '❌' ?><br>
            • Can View Budget: <?= canViewBudget($project->id) ? '✅' : '❌' ?><br>
            • Is Team Member: <?= isProjectTeamMember($project->id) ? '✅' : '❌' ?><br>
        </small>
    </div>
<?php endif; ?>
```

## 📊 Permission Management UI

Access the enhanced permission management interface at:
- `/enhanced_permissions` - Main dashboard
- `/enhanced_permissions/resourcePermissions/project/123` - Project-specific permissions
- `/enhanced_permissions/fieldPermissions/projects` - Field-level permissions
- `/enhanced_permissions/userSummary/456` - User permission summary

## 🚀 Ready to Go!

Your enhanced permission system is now ready! This provides:

✅ **Project-specific access control**  
✅ **Field-level data protection**  
✅ **Automatic team-based permissions**  
✅ **Time-based access management**  
✅ **Easy permission group management**  
✅ **Backward compatibility with existing system**

The system gracefully falls back to your existing permissions when enhanced permissions aren't defined, so you can migrate gradually without breaking existing functionality. 