-- Add page access permissions for comprehensive page control
USE HiveITPortal;

-- Insert additional page access permissions
INSERT INTO dbo.Permissions (name, display_name, description, module, action) VALUES 
    -- Dashboard and UI
    ('dashboard.access', 'Access Dashboard', 'Access to main dashboard', 'dashboard', 'access'),
    ('calendar.access', 'Access Calendar', 'Access to calendar view', 'calendar', 'access'),
    ('gantt.access', 'Access Gantt Chart', 'Access to Gantt chart view', 'gantt', 'access'),
    
    -- Notes Module
    ('notes.create', 'Create Notes', 'Create new notes', 'notes', 'create'),
    ('notes.read', 'View Notes', 'View notes', 'notes', 'read'),
    ('notes.update', 'Update Notes', 'Edit existing notes', 'notes', 'update'),
    ('notes.delete', 'Delete Notes', 'Delete notes', 'notes', 'delete'),
    
    -- Time Tracking Enhanced
    ('time.access', 'Access Time Tracking', 'Access to time tracking system', 'time', 'access'),
    ('time.admin', 'Time Tracking Admin', 'Administrative access to time tracking', 'time', 'admin'),
    ('time.reports', 'Time Tracking Reports', 'Access to time tracking reports and analytics', 'time', 'reports'),
    
    -- Suppliers Module
    ('suppliers.create', 'Create Suppliers', 'Create new suppliers', 'suppliers', 'create'),
    ('suppliers.read', 'View Suppliers', 'View supplier information', 'suppliers', 'read'),
    ('suppliers.update', 'Update Suppliers', 'Edit supplier information', 'suppliers', 'update'),
    ('suppliers.delete', 'Delete Suppliers', 'Delete suppliers', 'suppliers', 'delete'),
    
    -- Sites Module
    ('sites.create', 'Create Sites', 'Create new sites', 'sites', 'create'),
    ('sites.read', 'View Sites', 'View site information', 'sites', 'read'),
    ('sites.update', 'Update Sites', 'Edit site information', 'sites', 'update'),
    ('sites.delete', 'Delete Sites', 'Delete sites', 'sites', 'delete'),
    
    -- Employee Management
    ('employees.create', 'Create Employees', 'Create new employee records', 'employees', 'create'),
    ('employees.read', 'View Employees', 'View employee information', 'employees', 'read'),
    ('employees.update', 'Update Employees', 'Edit employee information', 'employees', 'update'),
    ('employees.delete', 'Delete Employees', 'Delete employee records', 'employees', 'delete'),
    
    -- Enhanced Admin Permissions
    ('admin.access', 'Access Admin Panel', 'Access to administrative panel', 'admin', 'access'),
    ('users.manage', 'Manage Users', 'Full user management capabilities', 'users', 'manage'),
    
    -- Additional Task Permissions
    ('tasks.assign', 'Assign Tasks', 'Assign tasks to users', 'tasks', 'assign'),
    ('tasks.manage', 'Manage Tasks', 'Full task management capabilities', 'tasks', 'manage');

-- Update role permissions for new permissions
-- Super Admin gets all new permissions
INSERT INTO dbo.RolePermissions (role_id, permission_id)
SELECT r.id, p.id
FROM dbo.Roles r, dbo.Permissions p
WHERE r.name = 'super_admin' 
AND p.name IN (
    'dashboard.access', 'calendar.access', 'gantt.access',
    'notes.create', 'notes.read', 'notes.update', 'notes.delete',
    'time.access', 'time.admin', 'time.reports',
    'suppliers.create', 'suppliers.read', 'suppliers.update', 'suppliers.delete',
    'sites.create', 'sites.read', 'sites.update', 'sites.delete',
    'employees.create', 'employees.read', 'employees.update', 'employees.delete',
    'admin.access', 'users.manage', 'tasks.assign', 'tasks.manage'
)
AND NOT EXISTS (
    SELECT 1 FROM dbo.RolePermissions rp2 
    WHERE rp2.role_id = r.id AND rp2.permission_id = p.id
);

-- Admin gets most new permissions (excluding super admin specific ones)
INSERT INTO dbo.RolePermissions (role_id, permission_id)
SELECT r.id, p.id
FROM dbo.Roles r, dbo.Permissions p
WHERE r.name = 'admin' 
AND p.name IN (
    'dashboard.access', 'calendar.access', 'gantt.access',
    'notes.create', 'notes.read', 'notes.update', 'notes.delete',
    'time.access', 'time.admin', 'time.reports',
    'suppliers.create', 'suppliers.read', 'suppliers.update', 'suppliers.delete',
    'sites.create', 'sites.read', 'sites.update', 'sites.delete',
    'employees.create', 'employees.read', 'employees.update', 'employees.delete',
    'admin.access', 'users.manage', 'tasks.assign', 'tasks.manage'
)
AND NOT EXISTS (
    SELECT 1 FROM dbo.RolePermissions rp2 
    WHERE rp2.role_id = r.id AND rp2.permission_id = p.id
);

-- Manager gets management permissions
INSERT INTO dbo.RolePermissions (role_id, permission_id)
SELECT r.id, p.id
FROM dbo.Roles r, dbo.Permissions p
WHERE r.name = 'manager' 
AND p.name IN (
    'dashboard.access', 'calendar.access', 'gantt.access',
    'notes.create', 'notes.read', 'notes.update', 'notes.delete',
    'time.access', 'time.reports',
    'suppliers.read', 'sites.read',
    'employees.read', 'employees.update',
    'tasks.assign', 'tasks.manage'
)
AND NOT EXISTS (
    SELECT 1 FROM dbo.RolePermissions rp2 
    WHERE rp2.role_id = r.id AND rp2.permission_id = p.id
);

-- Employee gets basic access permissions
INSERT INTO dbo.RolePermissions (role_id, permission_id)
SELECT r.id, p.id
FROM dbo.Roles r, dbo.Permissions p
WHERE r.name = 'employee' 
AND p.name IN (
    'dashboard.access', 'calendar.access', 'gantt.access',
    'notes.create', 'notes.read', 'notes.update',
    'time.access',
    'suppliers.read', 'sites.read'
)
AND NOT EXISTS (
    SELECT 1 FROM dbo.RolePermissions rp2 
    WHERE rp2.role_id = r.id AND rp2.permission_id = p.id
);

-- Client gets limited permissions
INSERT INTO dbo.RolePermissions (role_id, permission_id)
SELECT r.id, p.id
FROM dbo.Roles r, dbo.Permissions p
WHERE r.name = 'client' 
AND p.name IN (
    'dashboard.access', 'calendar.access',
    'notes.read',
    'suppliers.read', 'sites.read'
)
AND NOT EXISTS (
    SELECT 1 FROM dbo.RolePermissions rp2 
    WHERE rp2.role_id = r.id AND rp2.permission_id = p.id
);

-- Viewer gets read-only permissions
INSERT INTO dbo.RolePermissions (role_id, permission_id)
SELECT r.id, p.id
FROM dbo.Roles r, dbo.Permissions p
WHERE r.name = 'viewer' 
AND p.name IN (
    'dashboard.access', 'calendar.access', 'gantt.access',
    'notes.read',
    'suppliers.read', 'sites.read'
)
AND p.action = 'read'
AND NOT EXISTS (
    SELECT 1 FROM dbo.RolePermissions rp2 
    WHERE rp2.role_id = r.id AND rp2.permission_id = p.id
);

-- Display results
SELECT 'New Permissions Added' as Status, COUNT(*) as Count
FROM dbo.Permissions 
WHERE name IN (
    'dashboard.access', 'calendar.access', 'gantt.access',
    'notes.create', 'notes.read', 'notes.update', 'notes.delete',
    'time.access', 'time.admin', 'time.reports',
    'suppliers.create', 'suppliers.read', 'suppliers.update', 'suppliers.delete',
    'sites.create', 'sites.read', 'sites.update', 'sites.delete',
    'employees.create', 'employees.read', 'employees.update', 'employees.delete',
    'admin.access', 'users.manage', 'tasks.assign', 'tasks.manage'
);

-- Show total permissions count
SELECT 'Total Permissions' as Status, COUNT(*) as Count FROM dbo.Permissions WHERE is_active = 1;

-- Show role permission assignments
SELECT r.display_name as Role, COUNT(rp.permission_id) as PermissionCount
FROM dbo.Roles r
LEFT JOIN dbo.RolePermissions rp ON r.id = rp.role_id
WHERE r.is_active = 1
GROUP BY r.id, r.display_name
ORDER BY r.display_name; 