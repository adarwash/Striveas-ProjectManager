SELECT 'users', id, username, role, role_id FROM Users WHERE username = 'admin'
SELECT 'roles', id, name FROM Roles WHERE name = 'admin'
SELECT 'permissions', id, name FROM Permissions WHERE name = 'admin.system_settings'
SELECT 'role_permissions', role_id, permission_id FROM RolePermissions WHERE role_id = (SELECT id FROM Roles WHERE name = 'admin') AND permission_id = (SELECT id FROM Permissions WHERE name = 'admin.system_settings')
