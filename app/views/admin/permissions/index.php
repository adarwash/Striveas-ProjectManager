<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Permission Management</h1>
            <p class="text-muted">Manage roles, permissions, and user access control</p>
        </div>
        <div>
            <a href="<?= URLROOT ?>/admin" class="btn btn-outline-secondary me-2">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </div>
    </div>
    
    <?php flash('permissions_message'); ?>
    
    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary-light rounded-3 p-3">
                                <i class="bi bi-shield-check text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Total Roles</div>
                            <div class="fs-4 fw-bold"><?= count($roles) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success-light rounded-3 p-3">
                                <i class="bi bi-key text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Total Permissions</div>
                            <div class="fs-4 fw-bold"><?= $totalPermissions ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info-light rounded-3 p-3">
                                <i class="bi bi-diagram-3 text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Permission Modules</div>
                            <div class="fs-4 fw-bold"><?= count($permissions) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning-light rounded-3 p-3">
                                <i class="bi bi-people text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Active Users</div>
                            <div class="fs-4 fw-bold"><?= array_sum(array_column($roles, 'user_count')) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="<?= URLROOT ?>/permissions/roles" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-shield-check d-block fs-3 mb-2"></i>
                                Manage Roles
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= URLROOT ?>/permissions/user_permissions" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-person-gear d-block fs-3 mb-2"></i>
                                User Permissions
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= URLROOT ?>/admin/users" class="btn btn-outline-info w-100 py-3">
                                <i class="bi bi-people d-block fs-3 mb-2"></i>
                                Manage Users
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= URLROOT ?>/admin/logs" class="btn btn-outline-warning w-100 py-3">
                                <i class="bi bi-file-earmark-text d-block fs-3 mb-2"></i>
                                System Logs
                            </a>
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <a href="<?= URLROOT ?>/permissions/setup" class="btn btn-outline-danger w-100 py-3">
                                <i class="bi bi-gear d-block fs-3 mb-2"></i>
                                Permission Setup
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?= URLROOT ?>/permissions/debug" class="btn btn-outline-warning w-100 py-3">
                                <i class="bi bi-bug d-block fs-3 mb-2"></i>
                                Debug Permissions
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="<?= URLROOT ?>/admin/users" class="btn btn-outline-info w-100 py-3">
                                <i class="bi bi-people d-block fs-3 mb-2"></i>
                                User Management
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Roles Overview -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">System Roles</h5>
                    <a href="<?= URLROOT ?>/permissions/roles" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Manage Roles
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Role</th>
                                    <th>Users</th>
                                    <th>Permissions</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($roles as $role): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div>
                                            <p class="fw-bold mb-1"><?= $role['display_name'] ?></p>
                                            <p class="text-muted small mb-0"><?= $role['description'] ?></p>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= $role['user_count'] ?> users</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= $role['permission_count'] ?> permissions</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="<?= URLROOT ?>/permissions/role_permissions/<?= $role['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Manage Permissions">
                                                <i class="bi bi-key"></i>
                                            </a>
                                            <a href="<?= URLROOT ?>/permissions/edit_role/<?= $role['id'] ?>" 
                                               class="btn btn-sm btn-outline-secondary" title="Edit Role">
                                                <i class="bi bi-pencil"></i>
                                            </a>
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
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Permission Modules</h5>
                </div>
                <div class="card-body">
                    <?php foreach($permissions as $module => $modulePermissions): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-bold text-capitalize"><?= str_replace('_', ' ', $module) ?></div>
                            <div class="text-muted small"><?= count($modulePermissions) ?> permissions</div>
                        </div>
                        <div class="progress" style="width: 100px; height: 8px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: <?= min(100, (count($modulePermissions) / $totalPermissions) * 100 * 5) ?>%">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Security Notice</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Changes to roles and permissions affect user access immediately. 
                        Always test permission changes in a safe environment before applying to production users.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles moved to /public/css/app.css --> 