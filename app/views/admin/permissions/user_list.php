<?php require VIEWSPATH . '/inc/header.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">User Permission Management</h1>
            <p class="text-muted">Select a user to manage their permissions</p>
        </div>
        <div>
            <a href="<?= URLROOT ?>/permissions" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-2"></i>Back to Permissions
            </a>
        </div>
    </div>
    
    <?php flash('permissions_message'); ?>
    
    <!-- Users List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">System Users</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary-light rounded-circle p-2 me-3">
                                            <i class="bi bi-person text-primary"></i>
                                        </div>
                                        <div>
                                            <p class="fw-bold mb-0"><?= $user['name'] ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td><?= $user['email'] ?></td>
                                <td>
                                    <?php if($user['role'] === 'admin'): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary text-capitalize"><?= $user['role'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= isset($user['created_at']) ? date('M j, Y', strtotime($user['created_at'])) : 'N/A' ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="<?= URLROOT ?>/permissions/user_permissions/<?= $user['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-key me-1"></i>Manage Permissions
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                                        No users found
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.bg-primary-light { background-color: rgba(13, 110, 253, 0.1); }
</style>

<?php require VIEWSPATH . '/inc/footer.php'; ?> 