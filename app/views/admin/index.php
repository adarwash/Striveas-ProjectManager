<?php require VIEWSPATH . '/inc/header.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Admin Dashboard</h1>
            <p class="text-muted">System overview and management</p>
        </div>
        <div>
            <a href="<?= URLROOT ?>/settings" class="btn btn-outline-secondary">
                <i class="bi bi-person-gear me-2"></i>Back to Settings
            </a>
        </div>
    </div>
    
    <?php flash('admin_message'); ?>
    
    <!-- Stats Cards Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">Total Users</h6>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-2">
                            <i class="bi bi-people fs-4 text-primary"></i>
                        </div>
                    </div>
                    <h2 class="display-5 fw-bold mb-1"><?= $totalUsers ?></h2>
                    <p class="card-text text-muted">Registered users</p>
                    <a href="<?= URLROOT ?>/admin/users" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">Total Projects</h6>
                        <div class="rounded-circle bg-success bg-opacity-10 p-2">
                            <i class="bi bi-folder fs-4 text-success"></i>
                        </div>
                    </div>
                    <h2 class="display-5 fw-bold mb-1"><?= $totalProjects ?></h2>
                    <p class="card-text text-muted">Active projects</p>
                    <a href="<?= URLROOT ?>/projects" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">Total Tasks</h6>
                        <div class="rounded-circle bg-info bg-opacity-10 p-2">
                            <i class="bi bi-check2-square fs-4 text-info"></i>
                        </div>
                    </div>
                    <h2 class="display-5 fw-bold mb-1"><?= $totalTasks ?></h2>
                    <p class="card-text text-muted">Created tasks</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="card-title mb-0">System Status</h6>
                        <div class="rounded-circle bg-warning bg-opacity-10 p-2">
                            <i class="bi bi-gear fs-4 text-warning"></i>
                        </div>
                    </div>
                    <?php $maintenanceMode = isset($systemSettings['maintenance_mode']) && $systemSettings['maintenance_mode'] ? true : false; ?>
                    <?php if ($maintenanceMode): ?>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-warning me-2">Maintenance</span>
                            <span>Mode Active</span>
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success me-2">Online</span>
                            <span>Normal Operations</span>
                        </div>
                    <?php endif; ?>
                    <a href="<?= URLROOT ?>/admin/settings" class="btn btn-sm btn-outline-secondary mt-3">Manage Settings</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- Recent Users Card -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Recent Users</h5>
                        <a href="<?= URLROOT ?>/admin/users" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">User</th>
                                    <th class="border-0">Role</th>
                                    <th class="border-0">Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recentUsers as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?= getAvatarUrl($user->name) ?>" class="rounded-circle" width="40" height="40">
                                            <div class="ms-3">
                                                <p class="fw-bold mb-0"><?= $user->name ?></p>
                                                <p class="text-muted mb-0"><?= $user->email ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($user->role === 'admin'): ?>
                                            <span class="badge bg-primary">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= formatDate($user->created_at) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- System Information Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">System Information</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-medium">PHP Version</span>
                            </div>
                            <span><?= phpversion() ?></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-medium">Server Software</span>
                            </div>
                            <span><?= $_SERVER['SERVER_SOFTWARE'] ?></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-medium">Database Type</span>
                            </div>
                            <span>MySQL</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-medium">Application Version</span>
                            </div>
                            <span>1.0.0</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions Card -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="<?= URLROOT ?>/admin/users" class="card h-100 border-0 shadow-sm bg-light">
                                <div class="card-body p-4 text-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                                        <i class="bi bi-people-fill fs-1 text-primary"></i>
                                    </div>
                                    <h5>Manage Users</h5>
                                    <p class="text-muted mb-0">Add, edit, and remove users</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= URLROOT ?>/admin/settings" class="card h-100 border-0 shadow-sm bg-light">
                                <div class="card-body p-4 text-center">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                                        <i class="bi bi-sliders fs-1 text-success"></i>
                                    </div>
                                    <h5>System Settings</h5>
                                    <p class="text-muted mb-0">Configure application options</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= URLROOT ?>/admin/emailSettings" class="card h-100 border-0 shadow-sm bg-light">
                                <div class="card-body p-4 text-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                                        <i class="bi bi-envelope-at fs-1 text-primary"></i>
                                    </div>
                                    <h5>Email Settings</h5>
                                    <p class="text-muted mb-0">Configure email integration</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= URLROOT ?>/admin/logs" class="card h-100 border-0 shadow-sm bg-light">
                                <div class="card-body p-4 text-center">
                                    <div class="rounded-circle bg-info bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                                        <i class="bi bi-journal-text fs-1 text-info"></i>
                                    </div>
                                    <h5>View Logs</h5>
                                    <p class="text-muted mb-0">Review system activity</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= URLROOT ?>/admin/backup" class="card h-100 border-0 shadow-sm bg-light">
                                <div class="card-body p-4 text-center">
                                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 mx-auto mb-3" style="width: fit-content;">
                                        <i class="bi bi-database-fill fs-1 text-warning"></i>
                                    </div>
                                    <h5>Backup Data</h5>
                                    <p class="text-muted mb-0">Export and save data</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Maintenance Mode Panel -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Maintenance Control</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="rounded-circle p-3 bg-warning bg-opacity-10 me-3">
                            <i class="bi bi-tools fs-2 text-warning"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Maintenance Mode</h5>
                            <p class="text-muted mb-0">Limit access to administrators only</p>
                        </div>
                    </div>
                    
                    <form action="<?= URLROOT ?>/admin/toggle_maintenance" method="POST">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="maintenanceToggle" name="maintenance_mode" 
                                <?= $maintenanceMode ? 'checked' : '' ?>>
                            <label class="form-check-label" for="maintenanceToggle">
                                <?= $maintenanceMode ? 'Maintenance Mode Active' : 'Maintenance Mode Inactive' ?>
                            </label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle for maintenance mode label
    const maintenanceToggle = document.getElementById('maintenanceToggle');
    if (maintenanceToggle) {
        maintenanceToggle.addEventListener('change', function() {
            const label = this.nextElementSibling;
            if (this.checked) {
                label.textContent = 'Maintenance Mode Active';
            } else {
                label.textContent = 'Maintenance Mode Inactive';
            }
        });
    }
});
</script>

<?php require VIEWSPATH . '/inc/footer.php'; ?> 