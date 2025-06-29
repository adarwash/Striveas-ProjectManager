-- Create Permissions tables for granular access control
USE ProjectTracker;

-- Drop tables if they exist (in correct order to handle foreign keys)
IF OBJECT_ID('dbo.UserPermissions', 'U') IS NOT NULL
    DROP TABLE dbo.UserPermissions;
IF OBJECT_ID('dbo.RolePermissions', 'U') IS NOT NULL
    DROP TABLE dbo.RolePermissions;
IF OBJECT_ID('dbo.Permissions', 'U') IS NOT NULL
    DROP TABLE dbo.Permissions;
IF OBJECT_ID('dbo.Roles', 'U') IS NOT NULL
    DROP TABLE dbo.Roles;

-- Create Roles table
CREATE TABLE dbo.Roles (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(50) NOT NULL UNIQUE,
    display_name NVARCHAR(100) NOT NULL,
    description NVARCHAR(255) NULL,
    is_active BIT DEFAULT 1,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE()
);

-- Create Permissions table
CREATE TABLE dbo.Permissions (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(100) NOT NULL UNIQUE,
    display_name NVARCHAR(150) NOT NULL,
    description NVARCHAR(255) NULL,
    module NVARCHAR(50) NOT NULL, -- e.g., 'users', 'projects', 'tasks', 'admin'
    action NVARCHAR(50) NOT NULL, -- e.g., 'create', 'read', 'update', 'delete', 'manage'
    is_active BIT DEFAULT 1,
    created_at DATETIME DEFAULT GETDATE()
);

-- Create Role-Permission mapping table
CREATE TABLE dbo.RolePermissions (
    id INT IDENTITY(1,1) PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (role_id) REFERENCES dbo.Roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES dbo.Permissions(id) ON DELETE CASCADE,
    UNIQUE(role_id, permission_id)
);

-- Create User-Permission mapping table (for individual permissions)
CREATE TABLE dbo.UserPermissions (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    granted BIT DEFAULT 1, -- 1 for granted, 0 for explicitly denied
    created_at DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (user_id) REFERENCES dbo.Users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES dbo.Permissions(id) ON DELETE CASCADE,
    UNIQUE(user_id, permission_id)
);

-- Add role_id to Users table if it doesn't exist
IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'Users' AND COLUMN_NAME = 'role_id')
BEGIN
    ALTER TABLE dbo.Users ADD role_id INT NULL;
    ALTER TABLE dbo.Users ADD FOREIGN KEY (role_id) REFERENCES dbo.Roles(id);
END

-- Insert default roles
INSERT INTO dbo.Roles (name, display_name, description) VALUES 
    ('super_admin', 'Super Administrator', 'Full system access with all permissions'),
    ('admin', 'Administrator', 'Administrative access to most system features'),
    ('manager', 'Project Manager', 'Can manage projects, tasks, and team members'),
    ('employee', 'Employee', 'Standard employee access to assigned projects and tasks'),
    ('client', 'Client', 'Limited access to view assigned projects and reports'),
    ('viewer', 'Viewer', 'Read-only access to assigned content');

-- Insert default permissions
INSERT INTO dbo.Permissions (name, display_name, description, module, action) VALUES 
    -- User Management
    ('users.create', 'Create Users', 'Create new user accounts', 'users', 'create'),
    ('users.read', 'View Users', 'View user profiles and information', 'users', 'read'),
    ('users.update', 'Update Users', 'Edit user profiles and information', 'users', 'update'),
    ('users.delete', 'Delete Users', 'Delete user accounts', 'users', 'delete'),
    ('users.manage_roles', 'Manage User Roles', 'Assign and modify user roles', 'users', 'manage'),
    
    -- Project Management
    ('projects.create', 'Create Projects', 'Create new projects', 'projects', 'create'),
    ('projects.read', 'View Projects', 'View project details and information', 'projects', 'read'),
    ('projects.update', 'Update Projects', 'Edit project details and settings', 'projects', 'update'),
    ('projects.delete', 'Delete Projects', 'Delete projects and related data', 'projects', 'delete'),
    ('projects.manage_team', 'Manage Project Teams', 'Add/remove team members from projects', 'projects', 'manage'),
    
    -- Task Management
    ('tasks.create', 'Create Tasks', 'Create new tasks within projects', 'tasks', 'create'),
    ('tasks.read', 'View Tasks', 'View task details and assignments', 'tasks', 'read'),
    ('tasks.update', 'Update Tasks', 'Edit task details and status', 'tasks', 'update'),
    ('tasks.delete', 'Delete Tasks', 'Delete tasks', 'tasks', 'delete'),
    ('tasks.assign', 'Assign Tasks', 'Assign tasks to team members', 'tasks', 'assign'),
    
    -- Reports and Analytics
    ('reports.view', 'View Reports', 'Access reports and analytics', 'reports', 'read'),
    ('reports.export', 'Export Reports', 'Export reports and data', 'reports', 'export'),
    ('reports.advanced', 'Advanced Reports', 'Access advanced reporting features', 'reports', 'advanced'),
    
    -- Financial/Invoice Management
    ('invoices.create', 'Create Invoices', 'Create new invoices', 'invoices', 'create'),
    ('invoices.read', 'View Invoices', 'View invoice details', 'invoices', 'read'),
    ('invoices.update', 'Update Invoices', 'Edit invoice details', 'invoices', 'update'),
    ('invoices.delete', 'Delete Invoices', 'Delete invoices', 'invoices', 'delete'),
    
    -- System Administration
    ('admin.system_settings', 'System Settings', 'Access and modify system settings', 'admin', 'manage'),
    ('admin.logs', 'System Logs', 'View system logs and audit trails', 'admin', 'read'),
    ('admin.maintenance', 'System Maintenance', 'Perform system maintenance tasks', 'admin', 'manage'),
    ('admin.permissions', 'Permission Management', 'Manage roles and permissions', 'admin', 'manage'),
    
    -- Department Management
    ('departments.create', 'Create Departments', 'Create new departments', 'departments', 'create'),
    ('departments.read', 'View Departments', 'View department information', 'departments', 'read'),
    ('departments.update', 'Update Departments', 'Edit department details', 'departments', 'update'),
    ('departments.delete', 'Delete Departments', 'Delete departments', 'departments', 'delete');

-- Assign permissions to roles
-- Super Admin gets all permissions
INSERT INTO dbo.RolePermissions (role_id, permission_id)
SELECT r.id, p.id
FROM dbo.Roles r, dbo.Permissions p
WHERE r.name = 'super_admin';

-- Admin gets most permissions (excluding super admin specific ones)
INSERT INTO dbo.RolePermissions (role_id, permission_id)
SELECT r.id, p.id
FROM dbo.Roles r, dbo.Permissions p
WHERE r.name = 'admin' 
AND p.name NOT IN ('admin.maintenance', 'admin.permissions');

-- Manager gets project and team management permissions
INSERT INTO dbo.RolePermissions (role_id, permission_id)
SELECT r.id, p.id
FROM dbo.Roles r, dbo.Permissions p
WHERE r.name = 'manager' 
AND (p.module IN ('projects', 'tasks', 'reports') 
     OR p.name IN ('users.read', 'departments.read', 'invoices.read'));

-- Employee gets basic work permissions
INSERT INTO dbo.RolePermissions (role_id, permission_id)
SELECT r.id, p.id
FROM dbo.Roles r, dbo.Permissions p
WHERE r.name = 'employee' 
AND p.name IN ('projects.read', 'tasks.read', 'tasks.update', 'reports.view');

-- Client gets limited view permissions
INSERT INTO dbo.RolePermissions (role_id, permission_id)
SELECT r.id, p.id
FROM dbo.Roles r, dbo.Permissions p
WHERE r.name = 'client' 
AND p.name IN ('projects.read', 'tasks.read', 'reports.view');

-- Viewer gets read-only permissions
INSERT INTO dbo.RolePermissions (role_id, permission_id)
SELECT r.id, p.id
FROM dbo.Roles r, dbo.Permissions p
WHERE r.name = 'viewer' 
AND p.action = 'read';

-- Update existing users to have roles
UPDATE dbo.Users 
SET role_id = (SELECT id FROM dbo.Roles WHERE name = 'super_admin')
WHERE role = 'admin';

UPDATE dbo.Users 
SET role_id = (SELECT id FROM dbo.Roles WHERE name = 'employee')
WHERE role = 'user';

-- Create indexes for better performance
CREATE INDEX idx_role_permissions_role ON dbo.RolePermissions(role_id);
CREATE INDEX idx_role_permissions_permission ON dbo.RolePermissions(permission_id);
CREATE INDEX idx_user_permissions_user ON dbo.UserPermissions(user_id);
CREATE INDEX idx_user_permissions_permission ON dbo.UserPermissions(permission_id);
CREATE INDEX idx_permissions_module ON dbo.Permissions(module);

-- Display created tables
SELECT 'Roles' as TableName, COUNT(*) as RecordCount FROM dbo.Roles
UNION ALL
SELECT 'Permissions', COUNT(*) FROM dbo.Permissions
UNION ALL
SELECT 'RolePermissions', COUNT(*) FROM dbo.RolePermissions
UNION ALL
SELECT 'UserPermissions', COUNT(*) FROM dbo.UserPermissions; 