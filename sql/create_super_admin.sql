-- =============================================
-- Create Super Admin Account
-- Description: Creates the initial super admin user with all permissions
-- Run this after create_db.sql but before comprehensive_admin_fix.sql
-- =============================================

USE [ProjectTracker]
GO

PRINT 'Creating super admin account...';
PRINT '';

-- 1. First ensure the admin role exists
IF NOT EXISTS (SELECT 1 FROM [dbo].[Roles] WHERE name = 'admin')
BEGIN
    PRINT 'Creating admin role...';
    INSERT INTO [dbo].[Roles] ([name], [display_name], [description], [is_active], [created_at], [updated_at])
    VALUES ('admin', 'System Administrator', 'Full system access with all permissions', 1, GETDATE(), GETDATE());
END
ELSE
BEGIN
    PRINT 'Admin role already exists.';
END

-- 2. Create the admin user (only if it doesn't exist)
IF NOT EXISTS (SELECT 1 FROM [dbo].[Users] WHERE username = 'admin')
BEGIN
    PRINT 'Creating admin user...';
    INSERT INTO [dbo].[Users] ([username], [password], [email], [full_name], [role], [is_active], [created_at], [role_id], [position])
    SELECT 
        'admin',
        -- Default password: 'admin123' (stored as plain text for compatibility)
        -- IMPORTANT: Change this password immediately after first login!
        'admin123',
        'admin@projecttracker.local',
        'System Administrator',
        'admin',
        1,
        GETDATE(),
        r.id,
        'System Administrator'
    FROM [dbo].[Roles] r WHERE r.name = 'admin';
    
    PRINT 'Admin user created successfully!';
END
ELSE
BEGIN
    PRINT 'Admin user already exists.';
    -- Update the user to ensure they have admin role
    UPDATE [dbo].[Users] 
    SET role_id = (SELECT id FROM [dbo].[Roles] WHERE name = 'admin'),
        role = 'admin',
        is_active = 1
    WHERE username = 'admin';
    PRINT 'Admin user updated to ensure admin role.';
END

-- 3. Create essential permissions if they don't exist
PRINT '';
PRINT 'Creating essential permissions...';

-- Dashboard and basic access
IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'dashboard.access')
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('dashboard.access', 'Access Dashboard', 'Access to main dashboard', 'dashboard', 'access', 1, GETDATE(), 0, 1);

IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'admin.access')
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('admin.access', 'Admin Access', 'Can access admin dashboard and functions', 'admin', 'access', 1, GETDATE(), 0, 140);

IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'admin.permissions')
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('admin.permissions', 'Manage Permissions', 'Can manage system permissions and role assignments', 'admin', 'permissions', 1, GETDATE(), 0, 144);

IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'users.manage')
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('users.manage', 'Manage Users', 'Full user management capabilities', 'users', 'manage', 1, GETDATE(), 0, 16);

-- Create basic permissions for core modules
DECLARE @modules TABLE (module_name NVARCHAR(50))
INSERT INTO @modules VALUES 
    ('users'), ('projects'), ('tasks'), ('tickets'), ('clients'), 
    ('reports'), ('settings'), ('roles'), ('permissions')

DECLARE @module NVARCHAR(50)
DECLARE module_cursor CURSOR FOR SELECT module_name FROM @modules
OPEN module_cursor
FETCH NEXT FROM module_cursor INTO @module

WHILE @@FETCH_STATUS = 0
BEGIN
    -- View permission
    IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = @module + '.view')
        INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
        VALUES (@module + '.view', 'View ' + UPPER(LEFT(@module,1)) + SUBSTRING(@module,2,LEN(@module)), 'Can view ' + @module, @module, 'view', 1, GETDATE(), 0, 10);
    
    -- Create permission
    IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = @module + '.create')
        INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
        VALUES (@module + '.create', 'Create ' + UPPER(LEFT(@module,1)) + SUBSTRING(@module,2,LEN(@module)), 'Can create ' + @module, @module, 'create', 1, GETDATE(), 0, 20);
    
    -- Edit permission
    IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = @module + '.edit')
        INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
        VALUES (@module + '.edit', 'Edit ' + UPPER(LEFT(@module,1)) + SUBSTRING(@module,2,LEN(@module)), 'Can edit ' + @module, @module, 'edit', 1, GETDATE(), 0, 30);
    
    -- Delete permission
    IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = @module + '.delete')
        INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
        VALUES (@module + '.delete', 'Delete ' + UPPER(LEFT(@module,1)) + SUBSTRING(@module,2,LEN(@module)), 'Can delete ' + @module, @module, 'delete', 1, GETDATE(), 0, 40);
    
    FETCH NEXT FROM module_cursor INTO @module
END
CLOSE module_cursor
DEALLOCATE module_cursor

-- 4. Assign ALL permissions to admin role
PRINT '';
PRINT 'Assigning all permissions to admin role...';

-- Clear existing admin role permissions
DELETE FROM [dbo].[RolePermissions] 
WHERE role_id = (SELECT id FROM [dbo].[Roles] WHERE name = 'admin');

-- Assign all active permissions to admin role
INSERT INTO [dbo].[RolePermissions] ([role_id], [permission_id], [created_at])
SELECT r.id, p.id, GETDATE() 
FROM [dbo].[Roles] r, [dbo].[Permissions] p 
WHERE r.name = 'admin' AND p.is_active = 1;

-- 5. Verification
PRINT '';
PRINT '=== VERIFICATION ===';

-- Check admin user
DECLARE @userId INT, @roleId INT, @permCount INT

SELECT @userId = id, @roleId = role_id 
FROM [dbo].[Users] WHERE username = 'admin';

SELECT @permCount = COUNT(*)
FROM [dbo].[RolePermissions] rp
WHERE rp.role_id = @roleId;

PRINT 'Admin User ID: ' + CAST(@userId AS NVARCHAR(10));
PRINT 'Admin Role ID: ' + CAST(@roleId AS NVARCHAR(10));
PRINT 'Total Permissions Assigned: ' + CAST(@permCount AS NVARCHAR(10));

-- Show user details
SELECT 'Admin User Details' as Info, id, username, email, full_name, role, role_id, is_active 
FROM [dbo].[Users] WHERE username = 'admin';

-- Show permission count
SELECT 'Permission Summary' as Info, COUNT(*) as total_permissions
FROM [dbo].[RolePermissions] rp
INNER JOIN [dbo].[Roles] r ON rp.role_id = r.id
WHERE r.name = 'admin';

PRINT '';
PRINT '=== SUPER ADMIN ACCOUNT CREATED SUCCESSFULLY ===';
PRINT '';
PRINT 'Login Credentials:';
PRINT '  Username: admin';
PRINT '  Password: admin123';
PRINT '  Email: admin@projecttracker.local';
PRINT '';
PRINT '⚠️  IMPORTANT: Change the default password immediately after first login!';
PRINT '';
PRINT 'The admin user now has:';
PRINT '  ✅ Full system administrator role';
PRINT '  ✅ All available permissions';
PRINT '  ✅ Complete system access';
PRINT '';

GO
