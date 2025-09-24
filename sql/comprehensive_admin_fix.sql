-- =============================================
-- Comprehensive Admin Super User Setup
-- Description: Ensures admin user has ALL permissions for complete system access
-- =============================================

USE [ProjectTracker]
GO

PRINT 'Setting up comprehensive super admin permissions...';

-- 1. Update admin user to have correct role_id
UPDATE [dbo].[Users] 
SET role_id = (SELECT id FROM [dbo].[Roles] WHERE name = 'admin'),
    role = 'admin'
WHERE username = 'admin';

-- 2. Add ALL missing comprehensive permissions
-- Dashboard and UI
IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'dashboard.access')
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('dashboard.access', 'Access Dashboard', 'Access to main dashboard', 'dashboard', 'access', 1, GETDATE(), 0, 1);

IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'calendar.access')
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('calendar.access', 'Access Calendar', 'Access to calendar view', 'calendar', 'access', 1, GETDATE(), 0, 2);

IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'gantt.access')
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('gantt.access', 'Access Gantt Chart', 'Access to Gantt chart view', 'gantt', 'access', 1, GETDATE(), 0, 3);

-- Critical Admin Permissions
IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'admin.permissions')
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('admin.permissions', 'Manage Permissions', 'Can manage system permissions and role assignments', 'admin', 'permissions', 1, GETDATE(), 0, 144);

IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'admin.access')
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('admin.access', 'Admin Access', 'Can access admin dashboard and functions', 'admin', 'access', 1, GETDATE(), 0, 140);

IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'users.manage')
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('users.manage', 'Manage Users', 'Full user management capabilities', 'users', 'manage', 1, GETDATE(), 0, 16);

-- Email Management
IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'email.inbox')
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('email.inbox', 'Email Inbox Access', 'Access to email inbox', 'email', 'inbox', 1, GETDATE(), 0, 70);

IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'email.manage')
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('email.manage', 'Manage Email', 'Can manage email system', 'email', 'manage', 1, GETDATE(), 0, 71);

IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'email.delete')
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('email.delete', 'Delete Email', 'Can delete emails', 'email', 'delete', 1, GETDATE(), 0, 72);

-- Time Management
IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'time.access')
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('time.access', 'Access Time Tracking', 'Access to time tracking system', 'time', 'access', 1, GETDATE(), 0, 61);

IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'time.admin')
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('time.admin', 'Time Tracking Admin', 'Administrative access to time tracking', 'time', 'admin', 1, GETDATE(), 0, 66);

-- Read/View variations for all modules
DECLARE @modules TABLE (module_name NVARCHAR(50), priority_start INT)
INSERT INTO @modules VALUES 
    ('projects', 20), ('tasks', 30), ('tickets', 40), ('clients', 50),
    ('notes', 75), ('suppliers', 85), ('sites', 95), ('invoices', 105),
    ('employees', 115), ('departments', 125), ('reports', 135)

DECLARE @module NVARCHAR(50), @priority INT
DECLARE module_cursor CURSOR FOR SELECT module_name, priority_start FROM @modules
OPEN module_cursor
FETCH NEXT FROM module_cursor INTO @module, @priority

WHILE @@FETCH_STATUS = 0
BEGIN
    -- Add read permission if it doesn't exist
    IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = @module + '.read')
        INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
        VALUES (@module + '.read', 'Read ' + UPPER(LEFT(@module,1)) + SUBSTRING(@module,2,LEN(@module)), 'Can read ' + @module + ' information', @module, 'read', 1, GETDATE(), 0, @priority + 1);
    
    -- Add update permission if it doesn't exist
    IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = @module + '.update')
        INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
        VALUES (@module + '.update', 'Update ' + UPPER(LEFT(@module,1)) + SUBSTRING(@module,2,LEN(@module)), 'Can update ' + @module, @module, 'update', 1, GETDATE(), 0, @priority + 4);
    
    FETCH NEXT FROM module_cursor INTO @module, @priority
END
CLOSE module_cursor
DEALLOCATE module_cursor

-- 3. Clear existing admin role permissions and reassign ALL
DELETE FROM [dbo].[RolePermissions] 
WHERE role_id = (SELECT id FROM [dbo].[Roles] WHERE name = 'admin');

-- Assign ALL permissions to admin role (true super admin)
INSERT INTO [dbo].[RolePermissions] ([role_id], [permission_id], [created_at])
SELECT r.id, p.id, GETDATE() 
FROM [dbo].[Roles] r, [dbo].[Permissions] p 
WHERE r.name = 'admin' AND p.is_active = 1;

-- 4. Verification
PRINT 'Verification:';
SELECT 'Admin User Info' as Info, id, username, role, role_id, is_active 
FROM [dbo].[Users] WHERE username = 'admin';

SELECT 'Admin Role Info' as Info, id, name, is_active 
FROM [dbo].[Roles] WHERE name = 'admin';

SELECT 'Total Permissions Assigned' as Info, COUNT(*) as total_permissions
FROM [dbo].[RolePermissions] rp
INNER JOIN [dbo].[Roles] r ON rp.role_id = r.id
WHERE r.name = 'admin';

SELECT 'Sample Permissions' as Info, p.name, p.display_name
FROM [dbo].[RolePermissions] rp
INNER JOIN [dbo].[Roles] r ON rp.role_id = r.id
INNER JOIN [dbo].[Permissions] p ON rp.permission_id = p.id
WHERE r.name = 'admin'
ORDER BY p.name;

PRINT 'Comprehensive super admin setup completed!';
PRINT '';
PRINT 'Admin user now has access to:';
PRINT '✅ All dashboard and calendar functions';
PRINT '✅ Complete user and permission management';
PRINT '✅ Full project, task, and ticket management';
PRINT '✅ All client, supplier, and site functions';
PRINT '✅ Email management and time tracking';
PRINT '✅ Employee and department management';
PRINT '✅ Invoice and supplier management';
PRINT '✅ All reporting and analytics';
PRINT '✅ Complete system administration';

GO

