<?php
// Example: Enhanced User Role Assignment Interface
// This shows how to integrate the enhanced permission system with the existing admin interface

require_once '../app/models/Role.php';
require_once '../app/models/EnhancedPermission.php';

$roleModel = new Role();
$enhancedPermission = new EnhancedPermission();

// Get all available roles
$systemRoles = $roleModel->getAllRoles();
$permissionGroups = $enhancedPermission->getPermissionGroups();
?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Enhanced User Management</h1>
            <p class="text-muted">Manage system users, roles, and permissions</p>
        </div>
        <div>
            <a href="/admin" class="btn btn-outline-secondary me-2">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </div>
    </div>
    
    <!-- Enhanced User Management Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Users</h5>
            <div>
                <button type="button" class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-lg me-1"></i> Add User
                </button>
                <a href="/permissions" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-shield-check me-1"></i> Manage Permissions
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Email</th>
                            <th>System Role</th>
                            <th>Permission Groups</th>
                            <th>Projects</th>
                            <th>Last Login</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <img src="<?= getAvatarUrl($user['name']) ?>" class="rounded-circle" width="40" height="40">
                                    <div class="ms-3">
                                        <p class="fw-bold mb-0"><?= $user['name'] ?></p>
                                        <small class="text-muted"><?= $user['email'] ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= $user['email'] ?></td>
                            <td>
                                <!-- Enhanced Role Display -->
                                <?php 
                                $roleClass = 'bg-secondary';
                                $roleName = $user['role_display_name'] ?? $user['role'] ?? 'No Role';
                                
                                switch(strtolower($user['role'] ?? '')) {
                                    case 'super_admin': $roleClass = 'bg-danger'; break;
                                    case 'admin': $roleClass = 'bg-primary'; break;
                                    case 'manager': $roleClass = 'bg-success'; break;
                                    case 'employee': $roleClass = 'bg-info'; break;
                                    case 'client': $roleClass = 'bg-warning text-dark'; break;
                                    case 'viewer': $roleClass = 'bg-light text-dark'; break;
                                }
                                ?>
                                <span class="badge <?= $roleClass ?>"><?= htmlspecialchars($roleName) ?></span>
                            </td>
                            <td>
                                <!-- Permission Groups -->
                                <div class="d-flex flex-wrap gap-1">
                                    <?php 
                                    // Get user's permission groups (example)
                                    $userGroups = ['project_viewer', 'task_worker']; // This would come from database
                                    foreach($userGroups as $groupName): 
                                    ?>
                                        <span class="badge bg-light text-dark border small"><?= htmlspecialchars($groupName) ?></span>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($userGroups)): ?>
                                        <span class="text-muted small">None assigned</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <!-- Project Count -->
                                <?php $projectCount = 3; // This would come from database ?>
                                <span class="text-muted small"><?= $projectCount ?> projects</span>
                            </td>
                            <td>
                                <?= $user['last_login'] ? formatDate($user['last_login']) : 'Never' ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-user-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editUserModal" 
                                        data-user-id="<?= $user['id'] ?>"
                                        data-user-name="<?= $user['name'] ?>"
                                        data-user-email="<?= $user['email'] ?>"
                                        data-user-role="<?= $user['role'] ?>"
                                        data-user-role-id="<?= $user['role_id'] ?? '' ?>"
                                        title="Edit User">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="/enhanced_permissions/userSummary/<?= $user['id'] ?>" 
                                       class="btn btn-sm btn-outline-info" title="View Permissions">
                                        <i class="bi bi-shield-check"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-user-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteUserModal"
                                        data-user-id="<?= $user['id'] ?>"
                                        data-user-name="<?= $user['name'] ?>"
                                        title="Delete User">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/admin/add_user_enhanced" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department_id" class="form-label">Department</label>
                                <select class="form-select" id="department_id" name="department_id">
                                    <option value="">Select Department</option>
                                    <!-- Departments would be loaded here -->
                                    <option value="1">IT</option>
                                    <option value="2">Marketing</option>
                                    <option value="3">Sales</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- System Role Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">System Role</label>
                        <div class="row">
                            <?php foreach($systemRoles as $role): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="role_id" 
                                               id="role_<?= $role['id'] ?>" value="<?= $role['id'] ?>"
                                               <?= $role['name'] === 'employee' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="role_<?= $role['id'] ?>">
                                            <strong><?= htmlspecialchars($role['display_name']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($role['description']) ?></small>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Permission Groups Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Permission Groups (Optional)</label>
                        <small class="text-muted d-block mb-2">Select additional permission groups to assign to this user</small>
                        <div class="row">
                            <?php foreach($permissionGroups as $group): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permission_groups[]" 
                                               id="group_<?= $group['id'] ?>" value="<?= $group['id'] ?>">
                                        <label class="form-check-label" for="group_<?= $group['id'] ?>">
                                            <strong><?= htmlspecialchars($group['display_name']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($group['description']) ?></small>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Send Welcome Email -->
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="send_welcome" id="send_welcome" checked>
                        <label class="form-check-label" for="send_welcome">
                            Send welcome email with login credentials
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Enhanced Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/admin/update_user_enhanced" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_user_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="edit_password" name="password" 
                               placeholder="Leave blank to keep current password">
                        <small class="text-muted">Only enter a password if you want to change it</small>
                    </div>
                    
                    <!-- Enhanced Role Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">System Role</label>
                        <div class="row" id="edit_roles_container">
                            <?php foreach($systemRoles as $role): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="role_id" 
                                               id="edit_role_<?= $role['id'] ?>" value="<?= $role['id'] ?>">
                                        <label class="form-check-label" for="edit_role_<?= $role['id'] ?>">
                                            <strong><?= htmlspecialchars($role['display_name']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($role['description']) ?></small>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Permission Management Links -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Advanced Permission Management</h6>
                        <p class="mb-2">For detailed permission management, use these tools:</p>
                        <div class="btn-group btn-group-sm">
                            <a href="#" class="btn btn-outline-info" id="view_user_permissions">
                                <i class="bi bi-shield-check me-1"></i> View Permissions
                            </a>
                            <a href="#" class="btn btn-outline-primary" id="manage_resource_permissions">
                                <i class="bi bi-gear me-1"></i> Resource Permissions
                            </a>
                            <a href="#" class="btn btn-outline-success" id="manage_field_permissions">
                                <i class="bi bi-eye me-1"></i> Field Permissions
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle Edit User Modal
    const editUserBtns = document.querySelectorAll('.edit-user-btn');
    editUserBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            const userEmail = this.getAttribute('data-user-email');
            const userRoleId = this.getAttribute('data-user-role-id');
            
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_name').value = userName;
            document.getElementById('edit_email').value = userEmail;
            
            // Set the role radio button
            if (userRoleId) {
                const roleRadio = document.getElementById('edit_role_' + userRoleId);
                if (roleRadio) {
                    roleRadio.checked = true;
                }
            }
            
            // Update permission management links
            document.getElementById('view_user_permissions').href = '/enhanced_permissions/userSummary/' + userId;
            document.getElementById('manage_resource_permissions').href = '/enhanced_permissions/resourcePermissions/user/' + userId;
            document.getElementById('manage_field_permissions').href = '/enhanced_permissions/fieldPermissions/users?user_id=' + userId;
        });
    });
    
    // Handle Delete User Modal
    const deleteUserBtns = document.querySelectorAll('.delete-user-btn');
    deleteUserBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            
            document.getElementById('delete_user_id').value = userId;
            document.getElementById('delete_user_name').textContent = userName;
        });
    });
});
</script>

<style>
.form-check-label strong {
    color: #495057;
}

.form-check-input:checked + .form-check-label strong {
    color: #0d6efd;
}

.alert-info {
    border-left: 4px solid #0dcaf0;
}

.btn-group-sm .btn {
    font-size: 0.775rem;
    padding: 0.25rem 0.5rem;
}
</style>

<?php
/*
This enhanced interface provides:

1. **System Role Assignment**: Choose from predefined roles like Super Admin, Admin, Manager, Employee, etc.

2. **Permission Groups**: Assign additional permission groups for specific functions

3. **Advanced Permission Management**: Links to detailed permission management for:
   - Resource-specific permissions (project/task specific)
   - Field-level permissions (hide/show specific form fields)
   - Contextual permissions (based on relationships)

4. **User Permission Summary**: View all effective permissions for a user

5. **Department Assignment**: Link users to departments for automatic permissions

Usage:
- Admins can assign system-wide roles that define base permissions
- Additional permission groups can be assigned for specific functions
- Individual permissions can be fine-tuned using the enhanced permission system
- All changes take effect immediately
*/
?> 