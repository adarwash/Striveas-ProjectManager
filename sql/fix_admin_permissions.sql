-- =============================================
-- Fix Admin User Permissions
-- Description: Ensures admin user has proper role_id and all permissions
-- =============================================

USE [ProjectTracker]
GO

PRINT 'Fixing admin user permissions...';

-- 1. Update admin user to have correct role_id
UPDATE [dbo].[Users] 
SET role_id = (SELECT id FROM [dbo].[Roles] WHERE name = 'admin'),
    role = 'admin'
WHERE username = 'admin';

-- 2. Add missing critical permissions if they don't exist
IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'admin.permissions')
BEGIN
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('admin.permissions', 'Manage Permissions', 'Can manage system permissions and role assignments', 'admin', 'permissions', 1, GETDATE(), 0, 73);
    PRINT 'Added admin.permissions permission';
END

IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'admin.access')
BEGIN
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('admin.access', 'Admin Access', 'Can access admin dashboard and functions', 'admin', 'access', 1, GETDATE(), 0, 74);
    PRINT 'Added admin.access permission';
END

IF NOT EXISTS (SELECT 1 FROM [dbo].[Permissions] WHERE name = 'users.manage')
BEGIN
    INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
    VALUES ('users.manage', 'Manage Users', 'Full user management capabilities', 'users', 'manage', 1, GETDATE(), 0, 6);
    PRINT 'Added users.manage permission';
END

-- 3. Ensure all permissions are assigned to admin role
-- First, clear existing admin role permissions to avoid duplicates
DELETE FROM [dbo].[RolePermissions] 
WHERE role_id = (SELECT id FROM [dbo].[Roles] WHERE name = 'admin');

-- Re-assign all permissions to admin role
INSERT INTO [dbo].[RolePermissions] ([role_id], [permission_id], [created_at])
SELECT r.id, p.id, GETDATE() 
FROM [dbo].[Roles] r, [dbo].[Permissions] p 
WHERE r.name = 'admin' AND p.is_active = 1;

-- 3. Verify the setup
PRINT 'Verification:';
SELECT 'Admin User Info' as Info, id, username, role, role_id, is_active 
FROM [dbo].[Users] WHERE username = 'admin';

SELECT 'Admin Role Info' as Info, id, name, is_active 
FROM [dbo].[Roles] WHERE name = 'admin';

SELECT 'Permission Count' as Info, COUNT(*) as total_permissions
FROM [dbo].[RolePermissions] rp
INNER JOIN [dbo].[Roles] r ON rp.role_id = r.id
WHERE r.name = 'admin';

PRINT 'Admin permissions fix completed!';

GO
