# User Role Assignment Guide

## 🎯 Overview

This guide explains where and how to assign roles to users in your HiveITPortal system. There are multiple levels of role assignment depending on what type of access you want to grant.

## 📍 **System-Wide Role Assignment**

### **1. Admin User Management (Current System)**
- **URL**: `/admin/users`
- **Access**: Admin users only
- **Method**: Edit user modal → Role dropdown

**Current Basic Roles**:
- **Admin**: Full system access
- **User**: Standard user access

**Steps**:
1. Login as admin
2. Navigate to `/admin/users`
3. Click "Edit" (pencil icon) next to user
4. Select role from dropdown
5. Click "Update User"

---

### **2. Enhanced Permission System (Recommended)**
- **URL**: `/permissions` or `/enhanced_permissions`
- **Access**: Users with `admin.permissions` permission
- **Method**: Role-based permission assignment

**Enhanced System Roles**:
- **Super Administrator**: Full system access with all permissions
- **Administrator**: Administrative access to most features
- **Manager**: Project and team management capabilities
- **Employee**: Standard employee access to assigned work
- **Client**: Limited access to view assigned projects
- **Viewer**: Read-only access to assigned content

**Steps**:
1. Run the enhanced permissions SQL setup
2. Navigate to `/permissions`
3. Go to "Role Management" 
4. Select user and assign role
5. Optionally assign additional permission groups

---

## 🏢 **Project-Level Role Assignment**

### **Project Team Management**
- **URL**: `/projects/manageTeam/{project_id}`
- **Access**: Project managers and admins
- **Method**: Team assignment interface

**Project Roles**:
- **Manager**: Can manage project settings, budget, timeline
- **Member**: Can work on tasks and view project details
- **Observer**: Read-only access to project information

**Steps**:
1. Go to specific project
2. Click "Manage Team" tab
3. Select users and assign project roles
4. Save changes

---

## 🏗️ **Site-Specific Role Assignment**

### **Site Employee Assignment**
- **URL**: `/sites/assignEmployees/{site_id}`
- **Access**: Managers and admins
- **Method**: Site assignment interface

**Site Roles**:
- **Site Manager**: Full site management
- **Regular Staff**: Standard site access
- **Visiting**: Temporary site access

**Steps**:
1. Navigate to Sites section
2. Select specific site
3. Click "Assign Employees"
4. Choose employees and their site roles
5. Save assignments

---

## ⚙️ **Task-Level Assignment**

### **Task Assignment**
- **URL**: `/tasks/manageAssignments/{task_id}`
- **Access**: Project team members with assignment permissions
- **Method**: Task assignment interface

**Task Assignment**:
- Users are assigned to specific tasks
- Inherits permissions from project role
- Can be assigned/unassigned as needed

**Steps**:
1. Open specific task
2. Go to "Assignments" section
3. Select users from project team
4. Save assignments

---

## 🛡️ **Advanced Permission Management**

### **Resource-Level Permissions**
- **URL**: `/enhanced_permissions/resourcePermissions`
- **Purpose**: Grant/deny access to specific resources

**Examples**:
- Allow user to view Project #123 budget
- Grant user edit access to Task #456
- Restrict user from viewing Client #789 details

### **Field-Level Permissions**
- **URL**: `/enhanced_permissions/fieldPermissions`
- **Purpose**: Control visibility of specific form fields

**Examples**:
- Hide salary fields for non-HR users
- Restrict budget editing to managers only
- Show/hide client contact information

### **Contextual Permissions**
- **URL**: `/enhanced_permissions/contextualPermissions`
- **Purpose**: Permissions based on relationships

**Examples**:
- Task assignees can edit their own tasks
- Project members can view project details
- Department heads can manage their department

---

## 🚀 **Quick Setup for Enhanced Permissions**

### **Step 1: Database Setup**
```sql
-- Run the enhanced permissions SQL script
USE [ProjectTracker];
-- Execute: sql/enhanced_permissions_system_mssql.sql
```

### **Step 2: Create Enhanced Admin Interface**
```php
// Update your admin/users controller to use the enhanced system
public function update_user_enhanced() {
    // ... existing validation code ...
    
    $data = [
        'id' => $_POST['id'],
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'role_id' => $_POST['role_id'], // Use role_id instead of role
        'permission_groups' => $_POST['permission_groups'] ?? []
    ];
    
    // Update user role
    if ($this->userModel->updateUserRoleId($data)) {
        // Assign permission groups
        foreach ($data['permission_groups'] as $groupId) {
            $this->enhancedPermissionModel->assignPermissionGroupToUser($data['id'], $groupId);
        }
        
        flash('admin_message', 'User updated successfully', 'alert alert-success');
    } else {
        flash('admin_message', 'Error updating user', 'alert alert-danger');
    }
    
    redirect('admin/users');
}
```

### **Step 3: Permission Checking in Code**
```php
// Check if user has specific permission
if (hasEnhancedPermission($_SESSION['user_id'], 'projects.view_budget', 'project', $projectId)) {
    // Show budget information
}

// Check field-level permission
if (canViewField($_SESSION['user_id'], 'projects', 'budget')) {
    // Show budget field
}

// Check contextual permission
if (isProjectTeamMember($_SESSION['user_id'], $projectId)) {
    // Show project team features
}
```

---

## 📋 **Role Assignment Matrix**

| Role Level | Where to Assign | Best For | Permissions |
|------------|-----------------|----------|-------------|
| **System Role** | `/admin/users` or `/permissions` | Overall system access | Global permissions |
| **Project Role** | `/projects/manageTeam/` | Project-specific work | Project permissions |
| **Site Role** | `/sites/assignEmployees/` | Site-specific access | Site permissions |
| **Task Assignment** | `/tasks/manageAssignments/` | Task-specific work | Task permissions |
| **Resource Permission** | `/enhanced_permissions/` | Specific resource access | Resource-specific |
| **Field Permission** | `/enhanced_permissions/` | Field visibility control | Field-specific |

---

## 🔧 **Common Scenarios**

### **Scenario 1: New Employee**
1. **System Role**: Assign "Employee" role via `/admin/users`
2. **Projects**: Add to relevant projects via `/projects/manageTeam/`
3. **Sites**: Assign to primary work site via `/sites/assignEmployees/`
4. **Tasks**: Assign specific tasks as needed

### **Scenario 2: Promote to Manager**
1. **System Role**: Change to "Manager" role via `/admin/users`
2. **Permission Groups**: Add "team_lead" or "project_manager" groups
3. **Projects**: Update project roles to "Manager" where appropriate
4. **Resource Permissions**: Grant budget viewing/editing permissions

### **Scenario 3: Client Access**
1. **System Role**: Assign "Client" role via `/admin/users`
2. **Projects**: Add as "Observer" to client projects
3. **Field Permissions**: Hide sensitive fields (budget, internal notes)
4. **Resource Permissions**: Limit to assigned projects only

### **Scenario 4: Temporary Contractor**
1. **System Role**: Assign "Employee" role with expiration date
2. **Sites**: Assign as "Visiting" to specific sites
3. **Projects**: Add as "Member" to contracted projects
4. **Resource Permissions**: Set expiration dates on permissions

---

## 🚨 **Important Notes**

### **Security Best Practices**
- Always use the principle of least privilege
- Regularly review and audit user permissions
- Use expiration dates for temporary access
- Log all permission changes

### **Performance Considerations**
- The enhanced permission system includes optimized indexes
- Permission checks are cached for better performance
- Use views for complex permission queries

### **Migration from Basic to Enhanced**
- The enhanced system is backward compatible
- Existing `role` field is preserved
- New `role_id` field references the Roles table
- Gradually migrate users to the new system

---

## 📞 **Support**

If you need help with role assignment:
1. Check the admin interface at `/admin/users`
2. Review the permission system at `/permissions`
3. Use the enhanced permission examples provided
4. Refer to the database setup guide for advanced features

This system provides flexible, granular control over user access while maintaining ease of use for administrators. 