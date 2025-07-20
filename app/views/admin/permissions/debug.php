<?php require VIEWSPATH . '/inc/header.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Permission Debug</h1>
            <p class="text-muted">Troubleshoot permission issues and test role assignments</p>
        </div>
        <div>
            <a href="/permissions" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-2"></i>Back to Permissions
            </a>
        </div>
    </div>
    
    <!-- Test Permission Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-search"></i> Test Permission
                    </h5>
                </div>
                <div class="card-body">
                    <form action="/permissions/debug" method="post">
                        <div class="row">
                            <div class="col-md-8">
                                <label for="test_permission" class="form-label">Permission Name</label>
                                <input type="text" class="form-control" id="test_permission" name="test_permission" 
                                       placeholder="e.g., admin.permissions, clients.read, projects.create"
                                       value="<?= htmlspecialchars($_POST['test_permission'] ?? '') ?>">
                                <div class="form-text">Enter the exact permission name you want to test</div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-play-fill"></i> Test Permission
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <?php if (!empty($test_results)): ?>
                    <div class="mt-4">
                        <h6>Test Results:</h6>
                        <div class="alert <?= isset($test_results['error']) ? 'alert-danger' : ($test_results['has_permission'] ? 'alert-success' : 'alert-warning') ?>">
                            <?php if (isset($test_results['error'])): ?>
                                <strong>Error:</strong> <?= htmlspecialchars($test_results['error']) ?>
                            <?php else: ?>
                                <strong>Permission "<?= htmlspecialchars($test_results['permission']) ?>":</strong>
                                <?= $test_results['has_permission'] ? 
                                    '<span class="text-success"><i class="bi bi-check-circle"></i> GRANTED</span>' : 
                                    '<span class="text-danger"><i class="bi bi-x-circle"></i> DENIED</span>' ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- User Debug Information -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-circle"></i> Current User Debug Information
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($debug['error'])): ?>
                        <div class="alert alert-danger">
                            <strong>Debug Error:</strong> <?= htmlspecialchars($debug['error']) ?>
                        </div>
                    <?php else: ?>
                        
                        <!-- User Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>User Information</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td><strong>User ID:</strong></td>
                                        <td><?= htmlspecialchars($debug['user']['id']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Username:</strong></td>
                                        <td><?= htmlspecialchars($debug['user']['username']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td><?= htmlspecialchars($debug['user']['email']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <?= $debug['user']['is_active'] ? 
                                                '<span class="badge bg-success">Active</span>' : 
                                                '<span class="badge bg-danger">Inactive</span>' ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Role Information</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td><strong>Role (String):</strong></td>
                                        <td><?= $debug['user']['role_field'] ? htmlspecialchars($debug['user']['role_field']) : '<em class="text-muted">None</em>' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Role ID:</strong></td>
                                        <td><?= $debug['user']['role_id_field'] ? htmlspecialchars($debug['user']['role_id_field']) : '<em class="text-muted">None</em>' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Migration Status:</strong></td>
                                        <td>
                                            <?= $debug['migration_needed'] ? 
                                                '<span class="badge bg-warning text-dark">Migration Needed</span>' : 
                                                '<span class="badge bg-success">Up to Date</span>' ?>
                                        </td>
                                    </tr>
                                    <?php if ($debug['role']): ?>
                                    <tr>
                                        <td><strong>Role Name:</strong></td>
                                        <td><?= htmlspecialchars($debug['role']['display_name']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Migration Warning -->
                        <?php if ($debug['migration_needed']): ?>
                        <div class="alert alert-warning">
                            <h6><i class="bi bi-exclamation-triangle"></i> Migration Needed</h6>
                            <p class="mb-2">This user is using the old role system and may not have access to enhanced permissions.</p>
                            <a href="/permissions/setup" class="btn btn-warning btn-sm">
                                <i class="bi bi-arrow-right-circle"></i> Run Migration
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Permissions Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Effective Permissions (<?= $debug['permissions']['count'] ?>)</h6>
                                <?php if (empty($debug['permissions']['list'])): ?>
                                    <div class="alert alert-info">No permissions found. This may indicate a permission system issue.</div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                                        <?php foreach ($debug['permissions']['list'] as $permission): ?>
                                            <div class="list-group-item list-group-item-action py-2">
                                                <small class="text-primary"><?= htmlspecialchars($permission) ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6>Role Permissions (<?= $debug['role_permissions']['count'] ?>)</h6>
                                <?php if (empty($debug['role_permissions']['list'])): ?>
                                    <div class="alert alert-warning">
                                        No role permissions found. 
                                        <?= $debug['role'] ? 'Role may not have permissions assigned.' : 'User may not have a role assigned.' ?>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                                        <?php foreach ($debug['role_permissions']['list'] as $permission): ?>
                                            <div class="list-group-item list-group-item-action py-2">
                                                <small class="text-success"><?= htmlspecialchars($permission) ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="mt-4">
                            <h6>Quick Actions</h6>
                            <div class="btn-group" role="group">
                                <?php if ($debug['migration_needed']): ?>
                                    <a href="/permissions/setup" class="btn btn-warning btn-sm">
                                        <i class="bi bi-arrow-up-circle"></i> Migrate User
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($debug['role']): ?>
                                    <a href="/permissions/role_permissions/<?= $debug['role']['id'] ?>" class="btn btn-info btn-sm">
                                        <i class="bi bi-gear"></i> Manage Role Permissions
                                    </a>
                                <?php endif; ?>
                                
                                <a href="/permissions/user_permissions/<?= $debug['user']['id'] ?>" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-person-gear"></i> Manage User Permissions
                                </a>
                            </div>
                        </div>
                        
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Common Permission Tests -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-check"></i> Common Permission Tests
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Click on any permission below to test it quickly:</p>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Admin Permissions</h6>
                            <div class="d-grid gap-1">
                                <button class="btn btn-outline-primary btn-sm test-permission-btn" data-permission="admin.permissions">admin.permissions</button>
                                <button class="btn btn-outline-primary btn-sm test-permission-btn" data-permission="users.manage">users.manage</button>
                                <button class="btn btn-outline-primary btn-sm test-permission-btn" data-permission="admin.access">admin.access</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6>Content Permissions</h6>
                            <div class="d-grid gap-1">
                                <button class="btn btn-outline-success btn-sm test-permission-btn" data-permission="projects.create">projects.create</button>
                                <button class="btn btn-outline-success btn-sm test-permission-btn" data-permission="projects.read">projects.read</button>
                                <button class="btn btn-outline-success btn-sm test-permission-btn" data-permission="tasks.create">tasks.create</button>
                                <button class="btn btn-outline-success btn-sm test-permission-btn" data-permission="clients.read">clients.read</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6>Enhanced Permissions</h6>
                            <div class="d-grid gap-1">
                                <button class="btn btn-outline-info btn-sm test-permission-btn" data-permission="projects.view_budget">projects.view_budget</button>
                                <button class="btn btn-outline-info btn-sm test-permission-btn" data-permission="reports.financial">reports.financial</button>
                                <button class="btn btn-outline-info btn-sm test-permission-btn" data-permission="users.view_salary">users.view_salary</button>
                                <button class="btn btn-outline-info btn-sm test-permission-btn" data-permission="time.admin">time.admin</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quick test permission buttons
    const testButtons = document.querySelectorAll('.test-permission-btn');
    const permissionInput = document.getElementById('test_permission');
    
    testButtons.forEach(button => {
        button.addEventListener('click', function() {
            const permission = this.getAttribute('data-permission');
            permissionInput.value = permission;
            
            // Auto-submit the form
            const form = permissionInput.closest('form');
            form.submit();
        });
    });
});
</script>

<?php require VIEWSPATH . '/inc/footer.php'; ?> 