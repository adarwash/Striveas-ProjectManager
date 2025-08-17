-- Add ticket permissions to the system
-- Run this after your main permissions system is set up

USE ProjectTracker;
SET QUOTED_IDENTIFIER ON;
SET ANSI_NULLS ON;
GO

-- Add ticket-related permissions (check if Permissions table exists)
IF OBJECT_ID('dbo.Permissions', 'U') IS NOT NULL
BEGIN
    -- Insert ticket permissions if they don't exist
    IF NOT EXISTS (SELECT 1 FROM Permissions WHERE module = 'tickets' AND action = 'create')
        INSERT INTO Permissions (name, display_name, module, action, description) VALUES ('tickets.create', 'Create Tickets', 'tickets', 'create', 'Create new tickets');
    
    IF NOT EXISTS (SELECT 1 FROM Permissions WHERE module = 'tickets' AND action = 'read')
        INSERT INTO Permissions (name, display_name, module, action, description) VALUES ('tickets.read', 'View Tickets', 'tickets', 'read', 'View tickets');
    
    IF NOT EXISTS (SELECT 1 FROM Permissions WHERE module = 'tickets' AND action = 'update')
        INSERT INTO Permissions (name, display_name, module, action, description) VALUES ('tickets.update', 'Edit Tickets', 'tickets', 'update', 'Edit tickets');
    
    IF NOT EXISTS (SELECT 1 FROM Permissions WHERE module = 'tickets' AND action = 'delete')
        INSERT INTO Permissions (name, display_name, module, action, description) VALUES ('tickets.delete', 'Delete Tickets', 'tickets', 'delete', 'Delete tickets');
    
    IF NOT EXISTS (SELECT 1 FROM Permissions WHERE module = 'tickets' AND action = 'assign')
        INSERT INTO Permissions (name, display_name, module, action, description) VALUES ('tickets.assign', 'Assign Tickets', 'tickets', 'assign', 'Assign tickets to users');
    
    IF NOT EXISTS (SELECT 1 FROM Permissions WHERE module = 'tickets' AND action = 'close')
        INSERT INTO Permissions (name, display_name, module, action, description) VALUES ('tickets.close', 'Close Tickets', 'tickets', 'close', 'Close/resolve tickets');
    
    IF NOT EXISTS (SELECT 1 FROM Permissions WHERE module = 'tickets' AND action = 'view_all')
        INSERT INTO Permissions (name, display_name, module, action, description) VALUES ('tickets.view_all', 'View All Tickets', 'tickets', 'view_all', 'View all tickets (not just assigned)');
    
    IF NOT EXISTS (SELECT 1 FROM Permissions WHERE module = 'tickets' AND action = 'comment')
        INSERT INTO Permissions (name, display_name, module, action, description) VALUES ('tickets.comment', 'Comment on Tickets', 'tickets', 'comment', 'Add comments to tickets');
    
    IF NOT EXISTS (SELECT 1 FROM Permissions WHERE module = 'tickets' AND action = 'view_private')
        INSERT INTO Permissions (name, display_name, module, action, description) VALUES ('tickets.view_private', 'View Private Messages', 'tickets', 'view_private', 'View internal messages');
    
    -- Email permissions
    IF NOT EXISTS (SELECT 1 FROM Permissions WHERE module = 'email' AND action = 'inbox')
        INSERT INTO Permissions (name, display_name, module, action, description) VALUES ('email.inbox', 'Email Inbox', 'email', 'inbox', 'Access email inbox');
    
    IF NOT EXISTS (SELECT 1 FROM Permissions WHERE module = 'email' AND action = 'manage')
        INSERT INTO Permissions (name, display_name, module, action, description) VALUES ('email.manage', 'Manage Emails', 'email', 'manage', 'Process and manage emails');
    
    IF NOT EXISTS (SELECT 1 FROM Permissions WHERE module = 'email' AND action = 'delete')
        INSERT INTO Permissions (name, display_name, module, action, description) VALUES ('email.delete', 'Delete Emails', 'email', 'delete', 'Delete emails');
    
    PRINT 'Ticket permissions added successfully.';
END
ELSE
BEGIN
    PRINT 'Permissions table not found. Please set up your permissions system first.';
END

-- Grant basic ticket permissions to admin roles (if RolePermissions table exists)
IF OBJECT_ID('dbo.RolePermissions', 'U') IS NOT NULL AND OBJECT_ID('dbo.Roles', 'U') IS NOT NULL
BEGIN
    -- Get admin role ID
    DECLARE @adminRoleId INT;
    SELECT @adminRoleId = id FROM Roles WHERE name = 'admin' OR name = 'super_admin';
    
    IF @adminRoleId IS NOT NULL
    BEGIN
        -- Grant all ticket permissions to admin
        INSERT INTO RolePermissions (role_id, permission_id)
        SELECT @adminRoleId, p.id
        FROM Permissions p
        WHERE p.module IN ('tickets', 'email')
        AND NOT EXISTS (
            SELECT 1 FROM RolePermissions rp 
            WHERE rp.role_id = @adminRoleId AND rp.permission_id = p.id
        );
        
        PRINT 'Admin permissions granted for ticketing system.';
    END
END

-- Add notification settings if Settings table exists
IF OBJECT_ID('dbo.Settings', 'U') IS NOT NULL
BEGIN
    -- Email notification settings
    IF NOT EXISTS (SELECT 1 FROM Settings WHERE setting_key = 'notification_enabled')
        INSERT INTO Settings (setting_key, setting_value) VALUES ('notification_enabled', '1');
    
    IF NOT EXISTS (SELECT 1 FROM Settings WHERE setting_key = 'notify_on_create')
        INSERT INTO Settings (setting_key, setting_value) VALUES ('notify_on_create', '1');
    
    IF NOT EXISTS (SELECT 1 FROM Settings WHERE setting_key = 'notify_on_assign')
        INSERT INTO Settings (setting_key, setting_value) VALUES ('notify_on_assign', '1');
    
    IF NOT EXISTS (SELECT 1 FROM Settings WHERE setting_key = 'notify_on_update')
        INSERT INTO Settings (setting_key, setting_value) VALUES ('notify_on_update', '1');
    
    IF NOT EXISTS (SELECT 1 FROM Settings WHERE setting_key = 'notify_on_resolve')
        INSERT INTO Settings (setting_key, setting_value) VALUES ('notify_on_resolve', '1');
    
    -- Default email configuration placeholders
    IF NOT EXISTS (SELECT 1 FROM Settings WHERE setting_key = 'smtp_host')
        INSERT INTO Settings (setting_key, setting_value) VALUES ('smtp_host', '');
    
    IF NOT EXISTS (SELECT 1 FROM Settings WHERE setting_key = 'smtp_port')
        INSERT INTO Settings (setting_key, setting_value) VALUES ('smtp_port', '587');
    
    IF NOT EXISTS (SELECT 1 FROM Settings WHERE setting_key = 'smtp_username')
        INSERT INTO Settings (setting_key, setting_value) VALUES ('smtp_username', '');
    
    IF NOT EXISTS (SELECT 1 FROM Settings WHERE setting_key = 'smtp_password')
        INSERT INTO Settings (setting_key, setting_value) VALUES ('smtp_password', '');
    
    IF NOT EXISTS (SELECT 1 FROM Settings WHERE setting_key = 'smtp_encryption')
        INSERT INTO Settings (setting_key, setting_value) VALUES ('smtp_encryption', 'tls');
    
    IF NOT EXISTS (SELECT 1 FROM Settings WHERE setting_key = 'from_email')
        INSERT INTO Settings (setting_key, setting_value) VALUES ('from_email', 'support@yourcompany.com');
    
    IF NOT EXISTS (SELECT 1 FROM Settings WHERE setting_key = 'from_name')
        INSERT INTO Settings (setting_key, setting_value) VALUES ('from_name', 'Support Team');
    
    PRINT 'Notification settings added successfully.';
END

PRINT 'Ticket system permissions and settings setup completed!';