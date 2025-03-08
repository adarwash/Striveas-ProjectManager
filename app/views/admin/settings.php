<?php require VIEWSPATH . '/inc/header.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">System Settings</h1>
            <p class="text-muted">Configure application-wide settings</p>
        </div>
        <div>
            <a href="<?= URLROOT ?>/admin" class="btn btn-outline-secondary">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </div>
    </div>
    
    <?php flash('settings_success'); ?>
    <?php flash('settings_error'); ?>
    
    <form action="<?= URLROOT ?>/admin/settings" method="POST">
        <div class="row g-4">
            <!-- Application Settings Card -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-gear-wide-connected fs-4 me-2 text-primary"></i>
                            <h5 class="mb-0">Application Settings</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">System Status</h6>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="maintenance_mode" name="maintenance_mode" 
                                    <?= isset($systemSettings['maintenance_mode']) && $systemSettings['maintenance_mode'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="maintenance_mode">
                                    Maintenance Mode
                                </label>
                                <div class="form-text">When enabled, only administrators can access the application.</div>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="enable_registration" name="enable_registration" 
                                    <?= !isset($systemSettings['enable_registration']) || $systemSettings['enable_registration'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="enable_registration">
                                    User Registration
                                </label>
                                <div class="form-text">Allow new users to register accounts.</div>
                            </div>
                            
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="enable_api" name="enable_api" 
                                    <?= isset($systemSettings['enable_api']) && $systemSettings['enable_api'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="enable_api">
                                    API Access
                                </label>
                                <div class="form-text">Enable API endpoints for third-party integrations.</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Email Configuration</h6>
                            <div class="mb-3">
                                <label for="smtp_host" class="form-label">SMTP Host</label>
                                <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                    value="<?= $systemSettings['smtp_host'] ?? '' ?>">
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="smtp_port" class="form-label">SMTP Port</label>
                                    <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                        value="<?= $systemSettings['smtp_port'] ?? '587' ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="smtp_encryption" class="form-label">Encryption</label>
                                    <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                        <option value="tls" <?= (isset($systemSettings['smtp_encryption']) && $systemSettings['smtp_encryption'] == 'tls') ? 'selected' : '' ?>>TLS</option>
                                        <option value="ssl" <?= (isset($systemSettings['smtp_encryption']) && $systemSettings['smtp_encryption'] == 'ssl') ? 'selected' : '' ?>>SSL</option>
                                        <option value="none" <?= (isset($systemSettings['smtp_encryption']) && $systemSettings['smtp_encryption'] == 'none') ? 'selected' : '' ?>>None</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="smtp_username" class="form-label">SMTP Username</label>
                                <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                                    value="<?= $systemSettings['smtp_username'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label for="smtp_password" class="form-label">SMTP Password</label>
                                <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                    value="<?= $systemSettings['smtp_password'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label for="system_email" class="form-label">System Email</label>
                                <input type="email" class="form-control" id="system_email" name="system_email" 
                                    value="<?= $systemSettings['system_email'] ?? '' ?>">
                                <div class="form-text">Address used for system notifications.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Default Values Card -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-sliders fs-4 me-2 text-primary"></i>
                            <h5 class="mb-0">Default Values</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Project Defaults</h6>
                            <div class="mb-3">
                                <label for="default_project_category" class="form-label">Default Project Category</label>
                                <input type="text" class="form-control" id="default_project_category" name="default_project_category" 
                                    value="<?= $systemSettings['default_project_category'] ?? 'General' ?>">
                            </div>
                            <div class="mb-3">
                                <label for="default_project_status" class="form-label">Default Project Status</label>
                                <select class="form-select" id="default_project_status" name="default_project_status">
                                    <option value="Planning" <?= (isset($systemSettings['default_project_status']) && $systemSettings['default_project_status'] == 'Planning') ? 'selected' : '' ?>>Planning</option>
                                    <option value="In Progress" <?= (isset($systemSettings['default_project_status']) && $systemSettings['default_project_status'] == 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                                    <option value="On Hold" <?= (isset($systemSettings['default_project_status']) && $systemSettings['default_project_status'] == 'On Hold') ? 'selected' : '' ?>>On Hold</option>
                                    <option value="Completed" <?= (isset($systemSettings['default_project_status']) && $systemSettings['default_project_status'] == 'Completed') ? 'selected' : '' ?>>Completed</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Task Defaults</h6>
                            <div class="mb-3">
                                <label for="default_task_priority" class="form-label">Default Task Priority</label>
                                <select class="form-select" id="default_task_priority" name="default_task_priority">
                                    <option value="Low" <?= (isset($systemSettings['default_task_priority']) && $systemSettings['default_task_priority'] == 'Low') ? 'selected' : '' ?>>Low</option>
                                    <option value="Medium" <?= (!isset($systemSettings['default_task_priority']) || $systemSettings['default_task_priority'] == 'Medium') ? 'selected' : '' ?>>Medium</option>
                                    <option value="High" <?= (isset($systemSettings['default_task_priority']) && $systemSettings['default_task_priority'] == 'High') ? 'selected' : '' ?>>High</option>
                                    <option value="Critical" <?= (isset($systemSettings['default_task_priority']) && $systemSettings['default_task_priority'] == 'Critical') ? 'selected' : '' ?>>Critical</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">System Limits</h6>
                            <div class="mb-3">
                                <label for="max_upload_size" class="form-label">Maximum Upload Size (MB)</label>
                                <input type="number" class="form-control" id="max_upload_size" name="max_upload_size" min="1" max="100" 
                                    value="<?= $systemSettings['max_upload_size'] ?? '10' ?>">
                            </div>
                            <div class="mb-3">
                                <label for="max_projects" class="form-label">Projects Per User</label>
                                <input type="number" class="form-control" id="max_projects" name="max_projects" min="0" max="1000" 
                                    value="<?= $systemSettings['max_projects'] ?? '0' ?>">
                                <div class="form-text">Enter 0 for unlimited</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="fw-bold mb-3">Display Settings</h6>
                            <label for="default_date_format" class="form-label">Date Format</label>
                            <select class="form-select" id="default_date_format" name="default_date_format">
                                <option value="Y-m-d" <?= (isset($systemSettings['default_date_format']) && $systemSettings['default_date_format'] == 'Y-m-d') ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                <option value="m/d/Y" <?= (isset($systemSettings['default_date_format']) && $systemSettings['default_date_format'] == 'm/d/Y') ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                <option value="d/m/Y" <?= (isset($systemSettings['default_date_format']) && $systemSettings['default_date_format'] == 'd/m/Y') ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                <option value="M j, Y" <?= (!isset($systemSettings['default_date_format']) || $systemSettings['default_date_format'] == 'M j, Y') ? 'selected' : '' ?>>Month D, YYYY</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save me-2"></i>Save Settings
                    </button>
                </div>
            </div>
        </div>
    </form>
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