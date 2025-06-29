<?php require VIEWSPATH . '/inc/header.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Role Management</h1>
            <p class="text-muted">Create and manage system roles</p>
        </div>
        <div>
            <a href="<?= URLROOT ?>/permissions" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-2"></i>Back to Permissions
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                <i class="bi bi-plus-lg me-2"></i>Add Role
            </button>
        </div>
    </div>
    
    <?php flash('permissions_message'); ?>
    
    <!-- Roles List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">System Roles</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Role</th>
                            <th>Users Assigned</th>
                            <th>Permissions</th>
                            <th>Created</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($roles as $role): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-light rounded-circle p-2 me-3">
                                        <i class="bi bi-shield-check text-primary"></i>
                                    </div>
                                    <div>
                                        <p class="fw-bold mb-1"><?= $role['display_name'] ?></p>
                                        <p class="text-muted small mb-0"><?= $role['description'] ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if($role['user_count'] > 0): ?>
                                    <span class="badge bg-secondary"><?= $role['user_count'] ?> users</span>
                                <?php else: ?>
                                    <span class="text-muted">No users</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?= $role['permission_count'] ?> permissions</span>
                            </td>
                            <td>
                                <span class="text-muted small"><?= formatDate($role['created_at']) ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="<?= URLROOT ?>/permissions/role_permissions/<?= $role['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Manage Permissions">
                                        <i class="bi bi-key"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-role-btn"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editRoleModal"
                                            data-role-id="<?= $role['id'] ?>"
                                            data-role-name="<?= $role['display_name'] ?>"
                                            data-role-description="<?= $role['description'] ?>"
                                            title="Edit Role">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php if($role['user_count'] == 0 && !in_array($role['name'], ['super_admin', 'admin'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-role-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteRoleModal"
                                            data-role-id="<?= $role['id'] ?>"
                                            data-role-name="<?= $role['display_name'] ?>"
                                            title="Delete Role">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
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

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRoleModalLabel">Add New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= URLROOT ?>/permissions/create_role" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="display_name" class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="display_name" name="display_name" required>
                        <div class="form-text">A descriptive name for the role (e.g., "Project Manager")</div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        <div class="form-text">Brief description of what this role can do</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRoleModalLabel">Edit Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= URLROOT ?>/permissions/edit_role" method="POST" id="editRoleForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_role_id" name="id">
                    <div class="mb-3">
                        <label for="edit_display_name" class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="edit_display_name" name="display_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" checked>
                            <label class="form-check-label" for="edit_is_active">
                                Active (users can be assigned to this role)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Role Modal -->
<div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-labelledby="deleteRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteRoleModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= URLROOT ?>/permissions/delete_role" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="delete_role_id" name="id">
                    <p>Are you sure you want to delete the role <strong id="delete_role_name"></strong>?</p>
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

<style>
.bg-primary-light { background-color: rgba(13, 110, 253, 0.1); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle Edit Role Modal
    const editRoleBtns = document.querySelectorAll('.edit-role-btn');
    editRoleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const roleId = this.getAttribute('data-role-id');
            const roleName = this.getAttribute('data-role-name');
            const roleDescription = this.getAttribute('data-role-description');
            
            document.getElementById('edit_role_id').value = roleId;
            document.getElementById('edit_display_name').value = roleName;
            document.getElementById('edit_description').value = roleDescription;
            
            // Update form action
            document.getElementById('editRoleForm').action = '<?= URLROOT ?>/permissions/edit_role/' + roleId;
        });
    });
    
    // Handle Delete Role Modal
    const deleteRoleBtns = document.querySelectorAll('.delete-role-btn');
    deleteRoleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const roleId = this.getAttribute('data-role-id');
            const roleName = this.getAttribute('data-role-name');
            
            document.getElementById('delete_role_id').value = roleId;
            document.getElementById('delete_role_name').textContent = roleName;
        });
    });
});
</script>

<?php require VIEWSPATH . '/inc/footer.php'; ?> 