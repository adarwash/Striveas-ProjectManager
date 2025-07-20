-- Enhanced Granular Permission System
-- This extends the existing permission system with more granular controls

USE ProjectTracker;

-- Add new columns to Permissions table for enhanced features
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'Permissions' AND COLUMN_NAME = 'resource_type')
BEGIN
    ALTER TABLE dbo.Permissions ADD resource_type NVARCHAR(50) NULL; -- e.g., 'global', 'project', 'task', 'user'
    ALTER TABLE dbo.Permissions ADD is_conditional BIT DEFAULT 0; -- Whether permission has conditions
    ALTER TABLE dbo.Permissions ADD conditions NVARCHAR(MAX) NULL; -- JSON conditions for dynamic permissions
    ALTER TABLE dbo.Permissions ADD priority INT DEFAULT 0; -- Priority for conflicting permissions
END

-- Create Resource-Level Permissions table
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'ResourcePermissions')
BEGIN
    CREATE TABLE dbo.ResourcePermissions (
        id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        permission_name NVARCHAR(100) NOT NULL,
        resource_type NVARCHAR(50) NOT NULL, -- 'project', 'task', 'client', 'site'
        resource_id INT NOT NULL,
        granted BIT DEFAULT 1,
        expires_at DATETIME NULL, -- Time-based permissions
        conditions NVARCHAR(MAX) NULL, -- JSON conditions
        granted_by INT NULL, -- User who granted this permission
        created_at DATETIME DEFAULT GETDATE(),
        updated_at DATETIME DEFAULT GETDATE(),
        FOREIGN KEY (user_id) REFERENCES dbo.Users(id) ON DELETE CASCADE,
        FOREIGN KEY (granted_by) REFERENCES dbo.Users(id),
        INDEX idx_resource_permissions_user (user_id),
        INDEX idx_resource_permissions_resource (resource_type, resource_id),
        INDEX idx_resource_permissions_permission (permission_name)
    );
END

-- Create Field-Level Permissions table
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'FieldPermissions')
BEGIN
    CREATE TABLE dbo.FieldPermissions (
        id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        module NVARCHAR(50) NOT NULL, -- 'projects', 'tasks', 'users'
        field_name NVARCHAR(100) NOT NULL, -- 'budget', 'salary', 'notes'
        access_level NVARCHAR(20) NOT NULL, -- 'read', 'write', 'hidden'
        resource_id INT NULL, -- Specific resource ID (optional)
        created_at DATETIME DEFAULT GETDATE(),
        FOREIGN KEY (user_id) REFERENCES dbo.Users(id) ON DELETE CASCADE,
        INDEX idx_field_permissions_user (user_id),
        INDEX idx_field_permissions_module (module, field_name),
        UNIQUE(user_id, module, field_name, resource_id)
    );
END

-- Create Permission Groups table (for easier permission management)
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'PermissionGroups')
BEGIN
    CREATE TABLE dbo.PermissionGroups (
        id INT IDENTITY(1,1) PRIMARY KEY,
        name NVARCHAR(100) NOT NULL UNIQUE,
        display_name NVARCHAR(150) NOT NULL,
        description NVARCHAR(255) NULL,
        module NVARCHAR(50) NOT NULL,
        is_active BIT DEFAULT 1,
        created_at DATETIME DEFAULT GETDATE()
    );
END

-- Create Permission Group Members table
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'PermissionGroupMembers')
BEGIN
    CREATE TABLE dbo.PermissionGroupMembers (
        id INT IDENTITY(1,1) PRIMARY KEY,
        group_id INT NOT NULL,
        permission_id INT NOT NULL,
        created_at DATETIME DEFAULT GETDATE(),
        FOREIGN KEY (group_id) REFERENCES dbo.PermissionGroups(id) ON DELETE CASCADE,
        FOREIGN KEY (permission_id) REFERENCES dbo.Permissions(id) ON DELETE CASCADE,
        UNIQUE(group_id, permission_id)
    );
END

-- Create Contextual Permissions table (relationship-based permissions)
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'ContextualPermissions')
BEGIN
    CREATE TABLE dbo.ContextualPermissions (
        id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        context_type NVARCHAR(50) NOT NULL, -- 'project_member', 'task_assignee', 'department_head'
        context_id INT NOT NULL, -- ID of the context (project_id, task_id, department_id)
        permission_name NVARCHAR(100) NOT NULL,
        granted BIT DEFAULT 1,
        created_at DATETIME DEFAULT GETDATE(),
        FOREIGN KEY (user_id) REFERENCES dbo.Users(id) ON DELETE CASCADE,
        INDEX idx_contextual_permissions_user (user_id),
        INDEX idx_contextual_permissions_context (context_type, context_id),
        UNIQUE(user_id, context_type, context_id, permission_name)
    );
END

-- Insert enhanced granular permissions
INSERT INTO dbo.Permissions (name, display_name, description, module, action, resource_type, is_conditional) VALUES 
    -- Project-level granular permissions
    ('projects.view_budget', 'View Project Budget', 'View budget information for projects', 'projects', 'read', 'project', 0),
    ('projects.edit_budget', 'Edit Project Budget', 'Modify project budget information', 'projects', 'update', 'project', 0),
    ('projects.view_financials', 'View Project Financials', 'Access financial reports and data', 'projects', 'read', 'project', 0),
    ('projects.manage_timeline', 'Manage Project Timeline', 'Modify project start and end dates', 'projects', 'update', 'project', 0),
    ('projects.archive', 'Archive Projects', 'Archive completed or cancelled projects', 'projects', 'update', 'project', 0),
    ('projects.export_data', 'Export Project Data', 'Export project information and reports', 'projects', 'export', 'project', 0),
    
    -- Task-level granular permissions
    ('tasks.view_time_tracking', 'View Task Time Tracking', 'View time tracking data for tasks', 'tasks', 'read', 'task', 0),
    ('tasks.edit_estimates', 'Edit Task Estimates', 'Modify task time and cost estimates', 'tasks', 'update', 'task', 0),
    ('tasks.change_priority', 'Change Task Priority', 'Modify task priority levels', 'tasks', 'update', 'task', 0),
    ('tasks.view_all_comments', 'View All Task Comments', 'View all comments on tasks', 'tasks', 'read', 'task', 0),
    ('tasks.moderate_comments', 'Moderate Task Comments', 'Edit or delete task comments', 'tasks', 'manage', 'task', 0),
    ('tasks.bulk_operations', 'Bulk Task Operations', 'Perform bulk operations on multiple tasks', 'tasks', 'manage', 'global', 0),
    
    -- User management granular permissions
    ('users.view_salary', 'View User Salary', 'View salary and compensation information', 'users', 'read', 'user', 0),
    ('users.edit_permissions', 'Edit User Permissions', 'Modify individual user permissions', 'users', 'manage', 'user', 0),
    ('users.view_activity_logs', 'View User Activity', 'View user activity and audit logs', 'users', 'read', 'user', 0),
    ('users.reset_passwords', 'Reset User Passwords', 'Reset passwords for other users', 'users', 'manage', 'user', 0),
    ('users.impersonate', 'Impersonate Users', 'Login as another user (admin only)', 'users', 'manage', 'user', 0),
    
    -- Report permissions with different levels
    ('reports.basic', 'Basic Reports', 'Access basic reporting features', 'reports', 'read', 'global', 0),
    ('reports.advanced', 'Advanced Reports', 'Access advanced analytics and reports', 'reports', 'read', 'global', 0),
    ('reports.custom', 'Custom Reports', 'Create and modify custom reports', 'reports', 'create', 'global', 0),
    ('reports.financial', 'Financial Reports', 'Access financial and budget reports', 'reports', 'read', 'global', 0),
    ('reports.export_sensitive', 'Export Sensitive Data', 'Export reports containing sensitive information', 'reports', 'export', 'global', 0),
    
    -- Department-specific permissions
    ('departments.view_budget', 'View Department Budget', 'View department budget information', 'departments', 'read', 'department', 0),
    ('departments.manage_budget', 'Manage Department Budget', 'Modify department budget allocations', 'departments', 'update', 'department', 0),
    ('departments.view_performance', 'View Department Performance', 'Access department performance metrics', 'departments', 'read', 'department', 0),
    
    -- Client interaction permissions
    ('clients.view_internal_notes', 'View Internal Client Notes', 'View internal notes about clients', 'clients', 'read', 'client', 0),
    ('clients.manage_contracts', 'Manage Client Contracts', 'Create and modify client contracts', 'clients', 'manage', 'client', 0),
    ('clients.view_payment_history', 'View Client Payment History', 'Access client payment and billing history', 'clients', 'read', 'client', 0),
    
    -- Site-specific permissions
    ('sites.view_sensitive_info', 'View Sensitive Site Info', 'Access sensitive site information', 'sites', 'read', 'site', 0),
    ('sites.manage_access_codes', 'Manage Site Access Codes', 'Modify site access codes and security', 'sites', 'manage', 'site', 0),
    ('sites.schedule_visits', 'Schedule Site Visits', 'Schedule and manage site visits', 'sites', 'create', 'site', 0),
    
    -- Time tracking granular permissions
    ('time.view_all_entries', 'View All Time Entries', 'View time entries for all users', 'time', 'read', 'global', 0),
    ('time.edit_others_time', 'Edit Others Time Entries', 'Modify time entries for other users', 'time', 'update', 'global', 0),
    ('time.approve_timesheets', 'Approve Timesheets', 'Approve or reject timesheet submissions', 'time', 'approve', 'global', 0),
    ('time.generate_payroll', 'Generate Payroll Data', 'Generate data for payroll processing', 'time', 'export', 'global', 0),
    
    -- Invoice granular permissions
    ('invoices.view_all', 'View All Invoices', 'View invoices from all departments/projects', 'invoices', 'read', 'global', 0),
    ('invoices.approve', 'Approve Invoices', 'Approve invoices for payment', 'invoices', 'approve', 'invoice', 0),
    ('invoices.process_payments', 'Process Invoice Payments', 'Mark invoices as paid and process payments', 'invoices', 'manage', 'invoice', 0),
    
    -- System administration granular permissions
    ('admin.view_system_logs', 'View System Logs', 'Access detailed system and audit logs', 'admin', 'read', 'global', 0),
    ('admin.manage_backups', 'Manage System Backups', 'Create and restore system backups', 'admin', 'manage', 'global', 0),
    ('admin.system_health', 'View System Health', 'Monitor system performance and health', 'admin', 'read', 'global', 0),
    ('admin.manage_integrations', 'Manage Integrations', 'Configure external system integrations', 'admin', 'manage', 'global', 0);

-- Insert permission groups for easier management
INSERT INTO dbo.PermissionGroups (name, display_name, description, module) VALUES 
    ('project_viewer', 'Project Viewer', 'Basic project viewing permissions', 'projects'),
    ('project_manager', 'Project Manager', 'Full project management permissions', 'projects'),
    ('task_worker', 'Task Worker', 'Basic task management for assigned tasks', 'tasks'),
    ('team_lead', 'Team Lead', 'Team leadership and task oversight', 'tasks'),
    ('financial_viewer', 'Financial Viewer', 'View financial information', 'reports'),
    ('financial_manager', 'Financial Manager', 'Manage financial data and budgets', 'admin'),
    ('hr_basic', 'HR Basic', 'Basic HR functions', 'users'),
    ('hr_admin', 'HR Administrator', 'Full HR administrative functions', 'users'),
    ('client_manager', 'Client Manager', 'Client relationship management', 'clients'),
    ('site_coordinator', 'Site Coordinator', 'Site management and coordination', 'sites');

-- Create a function to check contextual permissions
-- This would be used by the application to check if a user has permission based on their relationship to a resource

-- Sample: Assign permission groups to common permission combinations
-- Project Viewer Group
INSERT INTO dbo.PermissionGroupMembers (group_id, permission_id)
SELECT pg.id, p.id
FROM dbo.PermissionGroups pg, dbo.Permissions p
WHERE pg.name = 'project_viewer' 
AND p.name IN ('projects.read', 'projects.view_budget', 'tasks.read', 'reports.basic');

-- Project Manager Group
INSERT INTO dbo.PermissionGroupMembers (group_id, permission_id)
SELECT pg.id, p.id
FROM dbo.PermissionGroups pg, dbo.Permissions p
WHERE pg.name = 'project_manager' 
AND p.name IN ('projects.create', 'projects.read', 'projects.update', 'projects.delete', 'projects.view_budget', 'projects.edit_budget', 'projects.manage_timeline', 'tasks.create', 'tasks.read', 'tasks.update', 'tasks.assign', 'reports.advanced');

-- Financial Manager Group
INSERT INTO dbo.PermissionGroupMembers (group_id, permission_id)
SELECT pg.id, p.id
FROM dbo.PermissionGroups pg, dbo.Permissions p
WHERE pg.name = 'financial_manager' 
AND p.name IN ('projects.view_financials', 'projects.edit_budget', 'departments.view_budget', 'departments.manage_budget', 'reports.financial', 'invoices.read', 'invoices.approve');

-- Add indexes for better performance
CREATE INDEX idx_resource_permissions_expires ON dbo.ResourcePermissions(expires_at) WHERE expires_at IS NOT NULL;
CREATE INDEX idx_permissions_resource_type ON dbo.Permissions(resource_type, module);
CREATE INDEX idx_permissions_conditional ON dbo.Permissions(is_conditional) WHERE is_conditional = 1;

-- Create views for easier permission checking
CREATE VIEW dbo.vw_UserEffectivePermissions AS
SELECT DISTINCT 
    u.id as user_id,
    u.username,
    p.name as permission_name,
    p.display_name,
    p.module,
    p.action,
    p.resource_type,
    'role' as permission_source,
    NULL as resource_id,
    NULL as expires_at
FROM dbo.Users u
INNER JOIN dbo.Roles r ON u.role_id = r.id
INNER JOIN dbo.RolePermissions rp ON r.id = rp.role_id
INNER JOIN dbo.Permissions p ON rp.permission_id = p.id
WHERE r.is_active = 1 AND p.is_active = 1

UNION ALL

SELECT DISTINCT 
    u.id as user_id,
    u.username,
    p.name as permission_name,
    p.display_name,
    p.module,
    p.action,
    p.resource_type,
    'direct' as permission_source,
    NULL as resource_id,
    NULL as expires_at
FROM dbo.Users u
INNER JOIN dbo.UserPermissions up ON u.id = up.user_id
INNER JOIN dbo.Permissions p ON up.permission_id = p.id
WHERE up.granted = 1 AND p.is_active = 1

UNION ALL

SELECT DISTINCT 
    u.id as user_id,
    u.username,
    rp.permission_name,
    p.display_name,
    p.module,
    p.action,
    p.resource_type,
    'resource' as permission_source,
    rp.resource_id,
    rp.expires_at
FROM dbo.Users u
INNER JOIN dbo.ResourcePermissions rp ON u.id = rp.user_id
LEFT JOIN dbo.Permissions p ON rp.permission_name = p.name
WHERE rp.granted = 1 AND (rp.expires_at IS NULL OR rp.expires_at > GETDATE());

PRINT 'Enhanced Permission System Created Successfully';
PRINT 'New Features Added:';
PRINT '- Resource-level permissions for project/task specific access';
PRINT '- Field-level permissions for granular data access control';
PRINT '- Permission groups for easier bulk permission management';
PRINT '- Contextual permissions based on user relationships';
PRINT '- Time-based permissions with expiration dates';
PRINT '- Enhanced permission conditions and priorities'; 