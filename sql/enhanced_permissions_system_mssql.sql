-- Enhanced Granular Permission System for MS SQL Server
-- This extends the existing permission system with more granular controls
-- Compatible with Microsoft SQL Server 2016+

USE [ProjectTracker];
GO

-- Enable QUOTED_IDENTIFIER for better SQL Server compatibility
SET QUOTED_IDENTIFIER ON;
GO

PRINT 'Starting Enhanced Permission System Setup for MS SQL Server...';

-- Add new columns to Permissions table for enhanced features
IF NOT EXISTS (
    SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'dbo' 
    AND TABLE_NAME = 'Permissions' 
    AND COLUMN_NAME = 'resource_type'
)
BEGIN
    PRINT 'Adding enhanced columns to Permissions table...';
    
    ALTER TABLE [dbo].[Permissions] 
    ADD [resource_type] NVARCHAR(50) NULL, -- e.g., 'global', 'project', 'task', 'user'
        [is_conditional] BIT NOT NULL DEFAULT 0, -- Whether permission has conditions
        [conditions] NVARCHAR(MAX) NULL, -- JSON conditions for dynamic permissions
        [priority] INT NOT NULL DEFAULT 0; -- Priority for conflicting permissions
        
    PRINT 'Enhanced columns added to Permissions table.';
END
ELSE
BEGIN
    PRINT 'Enhanced columns already exist in Permissions table.';
END
GO

-- Create Resource-Level Permissions table
IF NOT EXISTS (
    SELECT * FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = 'dbo' 
    AND TABLE_NAME = 'ResourcePermissions'
)
BEGIN
    PRINT 'Creating ResourcePermissions table...';
    
    CREATE TABLE [dbo].[ResourcePermissions] (
        [id] INT IDENTITY(1,1) NOT NULL,
        [user_id] INT NOT NULL,
        [permission_name] NVARCHAR(100) NOT NULL,
        [resource_type] NVARCHAR(50) NOT NULL, -- 'project', 'task', 'client', 'site'
        [resource_id] INT NOT NULL,
        [granted] BIT NOT NULL DEFAULT 1,
        [expires_at] DATETIME2(7) NULL, -- Time-based permissions
        [conditions] NVARCHAR(MAX) NULL, -- JSON conditions
        [granted_by] INT NULL, -- User who granted this permission
        [created_at] DATETIME2(7) NOT NULL DEFAULT GETDATE(),
        [updated_at] DATETIME2(7) NOT NULL DEFAULT GETDATE(),
        
        CONSTRAINT [PK_ResourcePermissions] PRIMARY KEY CLUSTERED ([id] ASC),
        CONSTRAINT [FK_ResourcePermissions_Users] FOREIGN KEY ([user_id]) 
            REFERENCES [dbo].[Users]([id]) ON DELETE CASCADE,
        CONSTRAINT [FK_ResourcePermissions_GrantedBy] FOREIGN KEY ([granted_by]) 
            REFERENCES [dbo].[Users]([id]) ON DELETE NO ACTION,
        CONSTRAINT [CK_ResourcePermissions_ResourceType] CHECK ([resource_type] IN ('project', 'task', 'client', 'site', 'user', 'department', 'invoice'))
    );
    
    -- Create indexes for better performance
    CREATE NONCLUSTERED INDEX [IX_ResourcePermissions_User] 
        ON [dbo].[ResourcePermissions] ([user_id]);
    
    CREATE NONCLUSTERED INDEX [IX_ResourcePermissions_Resource] 
        ON [dbo].[ResourcePermissions] ([resource_type], [resource_id]);
    
    CREATE NONCLUSTERED INDEX [IX_ResourcePermissions_Permission] 
        ON [dbo].[ResourcePermissions] ([permission_name]);
    
    CREATE NONCLUSTERED INDEX [IX_ResourcePermissions_Expires] 
        ON [dbo].[ResourcePermissions] ([expires_at]) 
        WHERE [expires_at] IS NOT NULL;
    
    PRINT 'ResourcePermissions table created successfully.';
END
ELSE
BEGIN
    PRINT 'ResourcePermissions table already exists.';
END
GO

-- Create Field-Level Permissions table
IF NOT EXISTS (
    SELECT * FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = 'dbo' 
    AND TABLE_NAME = 'FieldPermissions'
)
BEGIN
    PRINT 'Creating FieldPermissions table...';
    
    CREATE TABLE [dbo].[FieldPermissions] (
        [id] INT IDENTITY(1,1) NOT NULL,
        [user_id] INT NOT NULL,
        [module] NVARCHAR(50) NOT NULL, -- 'projects', 'tasks', 'users'
        [field_name] NVARCHAR(100) NOT NULL, -- 'budget', 'salary', 'notes'
        [access_level] NVARCHAR(20) NOT NULL, -- 'read', 'write', 'hidden'
        [resource_id] INT NULL, -- Specific resource ID (optional)
        [created_at] DATETIME2(7) NOT NULL DEFAULT GETDATE(),
        
        CONSTRAINT [PK_FieldPermissions] PRIMARY KEY CLUSTERED ([id] ASC),
        CONSTRAINT [FK_FieldPermissions_Users] FOREIGN KEY ([user_id]) 
            REFERENCES [dbo].[Users]([id]) ON DELETE CASCADE,
        CONSTRAINT [CK_FieldPermissions_AccessLevel] CHECK ([access_level] IN ('read', 'write', 'hidden')),
        CONSTRAINT [CK_FieldPermissions_Module] CHECK ([module] IN ('projects', 'tasks', 'users', 'clients', 'sites', 'invoices', 'reports', 'departments'))
    );
    
    -- Create indexes
    CREATE NONCLUSTERED INDEX [IX_FieldPermissions_User] 
        ON [dbo].[FieldPermissions] ([user_id]);
    
    CREATE NONCLUSTERED INDEX [IX_FieldPermissions_Module] 
        ON [dbo].[FieldPermissions] ([module], [field_name]);
    
    -- Create unique constraint to prevent duplicate permissions
    CREATE UNIQUE NONCLUSTERED INDEX [IX_FieldPermissions_Unique] 
        ON [dbo].[FieldPermissions] ([user_id], [module], [field_name], [resource_id]);
    
    PRINT 'FieldPermissions table created successfully.';
END
ELSE
BEGIN
    PRINT 'FieldPermissions table already exists.';
END
GO

-- Create Permission Groups table (for easier permission management)
IF NOT EXISTS (
    SELECT * FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = 'dbo' 
    AND TABLE_NAME = 'PermissionGroups'
)
BEGIN
    PRINT 'Creating PermissionGroups table...';
    
    CREATE TABLE [dbo].[PermissionGroups] (
        [id] INT IDENTITY(1,1) NOT NULL,
        [name] NVARCHAR(100) NOT NULL,
        [display_name] NVARCHAR(150) NOT NULL,
        [description] NVARCHAR(255) NULL,
        [module] NVARCHAR(50) NOT NULL,
        [is_active] BIT NOT NULL DEFAULT 1,
        [created_at] DATETIME2(7) NOT NULL DEFAULT GETDATE(),
        
        CONSTRAINT [PK_PermissionGroups] PRIMARY KEY CLUSTERED ([id] ASC),
        CONSTRAINT [UQ_PermissionGroups_Name] UNIQUE ([name])
    );
    
    PRINT 'PermissionGroups table created successfully.';
END
ELSE
BEGIN
    PRINT 'PermissionGroups table already exists.';
END
GO

-- Create Permission Group Members table
IF NOT EXISTS (
    SELECT * FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = 'dbo' 
    AND TABLE_NAME = 'PermissionGroupMembers'
)
BEGIN
    PRINT 'Creating PermissionGroupMembers table...';
    
    CREATE TABLE [dbo].[PermissionGroupMembers] (
        [id] INT IDENTITY(1,1) NOT NULL,
        [group_id] INT NOT NULL,
        [permission_id] INT NOT NULL,
        [created_at] DATETIME2(7) NOT NULL DEFAULT GETDATE(),
        
        CONSTRAINT [PK_PermissionGroupMembers] PRIMARY KEY CLUSTERED ([id] ASC),
        CONSTRAINT [FK_PermissionGroupMembers_Groups] FOREIGN KEY ([group_id]) 
            REFERENCES [dbo].[PermissionGroups]([id]) ON DELETE CASCADE,
        CONSTRAINT [FK_PermissionGroupMembers_Permissions] FOREIGN KEY ([permission_id]) 
            REFERENCES [dbo].[Permissions]([id]) ON DELETE CASCADE,
        CONSTRAINT [UQ_PermissionGroupMembers] UNIQUE ([group_id], [permission_id])
    );
    
    PRINT 'PermissionGroupMembers table created successfully.';
END
ELSE
BEGIN
    PRINT 'PermissionGroupMembers table already exists.';
END
GO

-- Create Contextual Permissions table (relationship-based permissions)
IF NOT EXISTS (
    SELECT * FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = 'dbo' 
    AND TABLE_NAME = 'ContextualPermissions'
)
BEGIN
    PRINT 'Creating ContextualPermissions table...';
    
    CREATE TABLE [dbo].[ContextualPermissions] (
        [id] INT IDENTITY(1,1) NOT NULL,
        [user_id] INT NOT NULL,
        [context_type] NVARCHAR(50) NOT NULL, -- 'project_member', 'task_assignee', 'department_head'
        [context_id] INT NOT NULL, -- ID of the context (project_id, task_id, department_id)
        [permission_name] NVARCHAR(100) NOT NULL,
        [granted] BIT NOT NULL DEFAULT 1,
        [created_at] DATETIME2(7) NOT NULL DEFAULT GETDATE(),
        
        CONSTRAINT [PK_ContextualPermissions] PRIMARY KEY CLUSTERED ([id] ASC),
        CONSTRAINT [FK_ContextualPermissions_Users] FOREIGN KEY ([user_id]) 
            REFERENCES [dbo].[Users]([id]) ON DELETE CASCADE,
        CONSTRAINT [CK_ContextualPermissions_ContextType] CHECK ([context_type] IN ('project_member', 'task_assignee', 'department_head', 'department_member', 'site_manager'))
    );
    
    -- Create indexes
    CREATE NONCLUSTERED INDEX [IX_ContextualPermissions_User] 
        ON [dbo].[ContextualPermissions] ([user_id]);
    
    CREATE NONCLUSTERED INDEX [IX_ContextualPermissions_Context] 
        ON [dbo].[ContextualPermissions] ([context_type], [context_id]);
    
    -- Create unique constraint
    CREATE UNIQUE NONCLUSTERED INDEX [IX_ContextualPermissions_Unique] 
        ON [dbo].[ContextualPermissions] ([user_id], [context_type], [context_id], [permission_name]);
    
    PRINT 'ContextualPermissions table created successfully.';
END
ELSE
BEGIN
    PRINT 'ContextualPermissions table already exists.';
END
GO

-- Insert enhanced granular permissions
PRINT 'Inserting enhanced granular permissions...';

-- Use MERGE to avoid duplicate insertions
WITH NewPermissions AS (
    SELECT * FROM (VALUES
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
        ('admin.manage_integrations', 'Manage Integrations', 'Configure external system integrations', 'admin', 'manage', 'global', 0)
    ) AS p([name], [display_name], [description], [module], [action], [resource_type], [is_conditional])
)
MERGE [dbo].[Permissions] AS target
USING NewPermissions AS source
ON (target.[name] = source.[name])
WHEN NOT MATCHED THEN
    INSERT ([name], [display_name], [description], [module], [action], [resource_type], [is_conditional])
    VALUES (source.[name], source.[display_name], source.[description], source.[module], source.[action], source.[resource_type], source.[is_conditional]);

PRINT 'Enhanced permissions inserted successfully.';
GO

-- Insert permission groups for easier management
PRINT 'Inserting permission groups...';

MERGE [dbo].[PermissionGroups] AS target
USING (
    VALUES 
        ('project_viewer', 'Project Viewer', 'Basic project viewing permissions', 'projects'),
        ('project_manager', 'Project Manager', 'Full project management permissions', 'projects'),
        ('task_worker', 'Task Worker', 'Basic task management for assigned tasks', 'tasks'),
        ('team_lead', 'Team Lead', 'Team leadership and task oversight', 'tasks'),
        ('financial_viewer', 'Financial Viewer', 'View financial information', 'reports'),
        ('financial_manager', 'Financial Manager', 'Manage financial data and budgets', 'admin'),
        ('hr_basic', 'HR Basic', 'Basic HR functions', 'users'),
        ('hr_admin', 'HR Administrator', 'Full HR administrative functions', 'users'),
        ('client_manager', 'Client Manager', 'Client relationship management', 'clients'),
        ('site_coordinator', 'Site Coordinator', 'Site management and coordination', 'sites')
) AS source ([name], [display_name], [description], [module])
ON (target.[name] = source.[name])
WHEN NOT MATCHED THEN
    INSERT ([name], [display_name], [description], [module])
    VALUES (source.[name], source.[display_name], source.[description], source.[module]);

PRINT 'Permission groups inserted successfully.';
GO

-- Assign permission groups to common permission combinations
PRINT 'Assigning permissions to groups...';

-- Project Viewer Group
IF NOT EXISTS (
    SELECT 1 FROM [dbo].[PermissionGroupMembers] pgm
    INNER JOIN [dbo].[PermissionGroups] pg ON pgm.[group_id] = pg.[id]
    WHERE pg.[name] = 'project_viewer'
)
BEGIN
    INSERT INTO [dbo].[PermissionGroupMembers] ([group_id], [permission_id])
    SELECT pg.[id], p.[id]
    FROM [dbo].[PermissionGroups] pg
    CROSS JOIN [dbo].[Permissions] p
    WHERE pg.[name] = 'project_viewer' 
    AND p.[name] IN ('projects.read', 'projects.view_budget', 'tasks.read', 'reports.basic');
END

-- Project Manager Group
IF NOT EXISTS (
    SELECT 1 FROM [dbo].[PermissionGroupMembers] pgm
    INNER JOIN [dbo].[PermissionGroups] pg ON pgm.[group_id] = pg.[id]
    WHERE pg.[name] = 'project_manager'
)
BEGIN
    INSERT INTO [dbo].[PermissionGroupMembers] ([group_id], [permission_id])
    SELECT pg.[id], p.[id]
    FROM [dbo].[PermissionGroups] pg
    CROSS JOIN [dbo].[Permissions] p
    WHERE pg.[name] = 'project_manager' 
    AND p.[name] IN (
        'projects.create', 'projects.read', 'projects.update', 'projects.delete', 
        'projects.view_budget', 'projects.edit_budget', 'projects.manage_timeline', 
        'tasks.create', 'tasks.read', 'tasks.update', 'tasks.assign', 'reports.advanced'
    );
END

-- Financial Manager Group
IF NOT EXISTS (
    SELECT 1 FROM [dbo].[PermissionGroupMembers] pgm
    INNER JOIN [dbo].[PermissionGroups] pg ON pgm.[group_id] = pg.[id]
    WHERE pg.[name] = 'financial_manager'
)
BEGIN
    INSERT INTO [dbo].[PermissionGroupMembers] ([group_id], [permission_id])
    SELECT pg.[id], p.[id]
    FROM [dbo].[PermissionGroups] pg
    CROSS JOIN [dbo].[Permissions] p
    WHERE pg.[name] = 'financial_manager' 
    AND p.[name] IN (
        'projects.view_financials', 'projects.edit_budget', 'departments.view_budget', 
        'departments.manage_budget', 'reports.financial', 'invoices.read', 'invoices.approve'
    );
END

PRINT 'Permission group assignments completed.';
GO

-- Create additional indexes for better performance
PRINT 'Creating additional performance indexes...';

-- Index for permissions by resource type and module
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_Permissions_ResourceType_Module')
BEGIN
    CREATE NONCLUSTERED INDEX [IX_Permissions_ResourceType_Module] 
        ON [dbo].[Permissions] ([resource_type], [module]) 
        INCLUDE ([name], [is_active]);
END

-- Index for conditional permissions
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_Permissions_Conditional')
BEGIN
    CREATE NONCLUSTERED INDEX [IX_Permissions_Conditional] 
        ON [dbo].[Permissions] ([is_conditional]) 
        WHERE [is_conditional] = 1;
END

PRINT 'Performance indexes created successfully.';
GO

-- Create views for easier permission checking
PRINT 'Creating permission views...';

-- Drop view if it exists
IF EXISTS (SELECT 1 FROM sys.views WHERE name = 'vw_UserEffectivePermissions')
BEGIN
    DROP VIEW [dbo].[vw_UserEffectivePermissions];
END

-- Create effective permissions view
EXEC sp_executesql N'
CREATE VIEW [dbo].[vw_UserEffectivePermissions] AS
SELECT DISTINCT 
    u.[id] as [user_id],
    u.[username],
    p.[name] as [permission_name],
    p.[display_name],
    p.[module],
    p.[action],
    p.[resource_type],
    ''role'' as [permission_source],
    NULL as [resource_id],
    NULL as [expires_at]
FROM [dbo].[Users] u
INNER JOIN [dbo].[Roles] r ON u.[role_id] = r.[id]
INNER JOIN [dbo].[RolePermissions] rp ON r.[id] = rp.[role_id]
INNER JOIN [dbo].[Permissions] p ON rp.[permission_id] = p.[id]
WHERE r.[is_active] = 1 AND p.[is_active] = 1

UNION ALL

SELECT DISTINCT 
    u.[id] as [user_id],
    u.[username],
    p.[name] as [permission_name],
    p.[display_name],
    p.[module],
    p.[action],
    p.[resource_type],
    ''direct'' as [permission_source],
    NULL as [resource_id],
    NULL as [expires_at]
FROM [dbo].[Users] u
INNER JOIN [dbo].[UserPermissions] up ON u.[id] = up.[user_id]
INNER JOIN [dbo].[Permissions] p ON up.[permission_id] = p.[id]
WHERE up.[granted] = 1 AND p.[is_active] = 1

UNION ALL

SELECT DISTINCT 
    u.[id] as [user_id],
    u.[username],
    rp.[permission_name],
    p.[display_name],
    p.[module],
    p.[action],
    p.[resource_type],
    ''resource'' as [permission_source],
    rp.[resource_id],
    rp.[expires_at]
FROM [dbo].[Users] u
INNER JOIN [dbo].[ResourcePermissions] rp ON u.[id] = rp.[user_id]
LEFT JOIN [dbo].[Permissions] p ON rp.[permission_name] = p.[name]
WHERE rp.[granted] = 1 AND (rp.[expires_at] IS NULL OR rp.[expires_at] > GETDATE());
';

PRINT 'Permission views created successfully.';
GO

-- Create stored procedures for common operations
PRINT 'Creating stored procedures...';

-- Procedure to cleanup expired permissions
IF EXISTS (SELECT 1 FROM sys.procedures WHERE name = 'sp_CleanupExpiredPermissions')
BEGIN
    DROP PROCEDURE [dbo].[sp_CleanupExpiredPermissions];
END

EXEC sp_executesql N'
CREATE PROCEDURE [dbo].[sp_CleanupExpiredPermissions]
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @DeletedCount INT = 0;
    
    BEGIN TRY
        BEGIN TRANSACTION;
        
        DELETE FROM [dbo].[ResourcePermissions] 
        WHERE [expires_at] IS NOT NULL AND [expires_at] <= GETDATE();
        
        SET @DeletedCount = @@ROWCOUNT;
        
        COMMIT TRANSACTION;
        
        PRINT CAST(@DeletedCount AS NVARCHAR(10)) + '' expired permissions cleaned up.'';
        
        RETURN @DeletedCount;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;
            
        DECLARE @ErrorMessage NVARCHAR(4000) = ERROR_MESSAGE();
        DECLARE @ErrorSeverity INT = ERROR_SEVERITY();
        DECLARE @ErrorState INT = ERROR_STATE();
        
        RAISERROR(@ErrorMessage, @ErrorSeverity, @ErrorState);
        
        RETURN -1;
    END CATCH
END
';

PRINT 'Stored procedures created successfully.';
GO

-- Create a function to check if user has enhanced permission
IF EXISTS (SELECT 1 FROM sys.objects WHERE name = 'fn_UserHasEnhancedPermission' AND type = 'FN')
BEGIN
    DROP FUNCTION [dbo].[fn_UserHasEnhancedPermission];
END

EXEC sp_executesql N'
CREATE FUNCTION [dbo].[fn_UserHasEnhancedPermission]
(
    @UserId INT,
    @PermissionName NVARCHAR(100),
    @ResourceType NVARCHAR(50) = NULL,
    @ResourceId INT = NULL
)
RETURNS BIT
AS
BEGIN
    DECLARE @HasPermission BIT = 0;
    
    -- Check resource-specific permissions first
    IF @ResourceType IS NOT NULL AND @ResourceId IS NOT NULL
    BEGIN
        SELECT @HasPermission = 1
        FROM [dbo].[ResourcePermissions]
        WHERE [user_id] = @UserId 
        AND [permission_name] = @PermissionName 
        AND [resource_type] = @ResourceType 
        AND [resource_id] = @ResourceId
        AND [granted] = 1
        AND ([expires_at] IS NULL OR [expires_at] > GETDATE());
        
        IF @HasPermission = 1
            RETURN @HasPermission;
    END
    
    -- Check standard permissions
    SELECT @HasPermission = 1
    FROM [dbo].[vw_UserEffectivePermissions]
    WHERE [user_id] = @UserId 
    AND [permission_name] = @PermissionName;
    
    RETURN ISNULL(@HasPermission, 0);
END
';

PRINT 'User-defined functions created successfully.';
GO

-- Final summary
PRINT '';
PRINT '=======================================================';
PRINT 'Enhanced Permission System Setup Complete!';
PRINT '=======================================================';
PRINT '';
PRINT 'New Tables Created:';
PRINT '- ResourcePermissions: Project/task/user specific permissions';
PRINT '- FieldPermissions: Field-level access control';
PRINT '- ContextualPermissions: Relationship-based permissions';
PRINT '- PermissionGroups: Predefined permission sets';
PRINT '- PermissionGroupMembers: Group membership';
PRINT '';
PRINT 'New Features Added:';
PRINT '- Resource-level permissions for project/task specific access';
PRINT '- Field-level permissions for granular data access control';
PRINT '- Permission groups for easier bulk permission management';
PRINT '- Contextual permissions based on user relationships';
PRINT '- Time-based permissions with expiration dates';
PRINT '- Enhanced permission conditions and priorities';
PRINT '';
PRINT 'Performance Features:';
PRINT '- Optimized indexes for fast permission checking';
PRINT '- Views for simplified permission queries';
PRINT '- Stored procedures for maintenance operations';
PRINT '- User-defined functions for permission validation';
PRINT '';
PRINT 'MS SQL Server Optimizations:';
PRINT '- DATETIME2(7) for better datetime precision';
PRINT '- Proper NVARCHAR sizing for Unicode support';
PRINT '- Clustered and non-clustered indexes';
PRINT '- Check constraints for data validation';
PRINT '- Foreign key constraints with cascade options';
PRINT '';

-- Display statistics
SELECT 
    'Enhanced Permissions' as [Component],
    COUNT(*) as [Record_Count]
FROM [dbo].[Permissions] 
WHERE [resource_type] IS NOT NULL

UNION ALL

SELECT 'Permission Groups', COUNT(*) 
FROM [dbo].[PermissionGroups]

UNION ALL

SELECT 'Group Assignments', COUNT(*) 
FROM [dbo].[PermissionGroupMembers];

PRINT 'Setup completed successfully!';
GO 