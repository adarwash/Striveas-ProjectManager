<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">User Management</h1>
            <p class="text-muted">Manage system users and access</p>
        </div>
        <div>
            <a href="<?= URLROOT ?>/admin" class="btn btn-outline-secondary me-2">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </div>
    </div>
    
    <?php flash('admin_message'); ?>
    
    <!-- Enhanced Permissions Notice -->
    <?php if (count($available_roles) > 2): ?>
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle-fill me-3 text-info" style="font-size: 1.5rem;"></i>
            <div>
                <h6 class="alert-heading mb-1">Enhanced Permission System Active</h6>
                <p class="mb-2">You're using the enhanced permission system with granular roles and permissions.</p>
                <div class="small">
                    <a href="/permissions" class="alert-link">Manage Permissions</a> • 
                    <a href="/enhanced_permissions" class="alert-link">Advanced Settings</a> • 
                    <a href="/permissions/setup" class="alert-link">System Setup</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- User Management Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Users</h5>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-plus-lg me-1"></i> Add User
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
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
                                    </div>
                                </div>
                            </td>
                            <td><?= $user['email'] ?></td>
                            <td>
                                <?php 
                                // Find the role display name from available roles
                                $roleDisplayName = $user['role'];
                                $roleClass = 'bg-secondary';
                                
                                foreach($available_roles as $role) {
                                    if ($role['name'] === $user['role']) {
                                        $roleDisplayName = $role['display_name'];
                                        break;
                                    }
                                }
                                
                                // Set badge color based on role
                                switch(strtolower($user['role'])) {
                                    case 'super_admin': $roleClass = 'bg-danger'; break;
                                    case 'admin': $roleClass = 'bg-primary'; break;
                                    case 'manager': $roleClass = 'bg-success'; break;
                                    case 'employee': $roleClass = 'bg-info'; break;
                                    case 'client': $roleClass = 'bg-warning text-dark'; break;
                                    case 'viewer': $roleClass = 'bg-light text-dark'; break;
                                    default: $roleClass = 'bg-secondary'; break;
                                }
                                ?>
                                <span class="badge <?= $roleClass ?>"><?= htmlspecialchars($roleDisplayName) ?></span>
                            </td>
                            <td><?= formatDate($user['created_at']) ?></td>
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
                                        title="Edit User">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="<?= URLROOT ?>/admin/impersonate/<?= $user['id'] ?>" 
                                       class="btn btn-sm btn-outline-dark" 
                                       onclick="return confirm('Are you sure you want to login as <?= htmlspecialchars($user['name']) ?>?');"
                                       title="Login as User">
                                        <i class="bi bi-person-bounding-box"></i>
                                    </a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-user-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteUserModal"
                                        data-user-id="<?= $user['id'] ?>"
                                        data-user-name="<?= $user['name'] ?>">
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= URLROOT ?>/admin/add_user" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <?php foreach($available_roles as $role): ?>
                                <option value="<?= htmlspecialchars($role['name']) ?>" 
                                        title="<?= htmlspecialchars($role['description']) ?>">
                                    <?= htmlspecialchars($role['display_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= URLROOT ?>/admin/update_user" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_user_id" name="id">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <?php foreach($available_roles as $role): ?>
                                <option value="<?= htmlspecialchars($role['name']) ?>" 
                                        title="<?= htmlspecialchars($role['description']) ?>">
                                    <?= htmlspecialchars($role['display_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= URLROOT ?>/admin/delete_user" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="delete_user_id" name="id">
                    <p>Are you sure you want to delete <strong id="delete_user_name"></strong>?</p>
                    <p class="text-danger mb-0"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
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
            const userRole = this.getAttribute('data-user-role');
            
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_name').value = userName;
            document.getElementById('edit_email').value = userEmail;
            document.getElementById('edit_role').value = userRole;
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