<!-- Include theme CSS -->
<link rel="stylesheet" href="<?= URLROOT ?>/views/settings/theme.css">

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h2 mb-1">Account Settings</h1>
            <p class="text-muted">Manage your account settings and preferences</p>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                    <div>
                        <?= htmlspecialchars($success_message) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                    <div>
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="list-group shadow-sm settings-nav">
                <a href="#profile" class="list-group-item list-group-item-action active d-flex align-items-center py-3" data-bs-toggle="list">
                    <i class="bi bi-person-circle me-3 fs-5"></i>
                    <div>
                        <span class="d-block">Profile</span>
                        <small class="text-muted">Personal information</small>
                    </div>
                </a>
                <a href="#password" class="list-group-item list-group-item-action d-flex align-items-center py-3" data-bs-toggle="list">
                    <i class="bi bi-shield-lock me-3 fs-5"></i>
                    <div>
                        <span class="d-block">Password</span>
                        <small class="text-muted">Update your password</small>
                    </div>
                </a>
                <a href="#notifications" class="list-group-item list-group-item-action d-flex align-items-center py-3" data-bs-toggle="list">
                    <i class="bi bi-bell me-3 fs-5"></i>
                    <div>
                        <span class="d-block">Notifications</span>
                        <small class="text-muted">Manage preferences</small>
                    </div>
                </a>
                <a href="#theme" class="list-group-item list-group-item-action d-flex align-items-center py-3" data-bs-toggle="list">
                    <i class="bi bi-palette me-3 fs-5"></i>
                    <div>
                        <span class="d-block">Theme</span>
                        <small class="text-muted">Customize appearance</small>
                    </div>
                </a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="<?= URLROOT ?>/admin" class="list-group-item list-group-item-action d-flex align-items-center py-3">
                    <i class="bi bi-gear-fill me-3 fs-5 text-primary"></i>
                    <div>
                        <span class="d-block">Admin Dashboard</span>
                        <small class="text-primary">System management</small>
                    </div>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="tab-content">
                <!-- Profile Settings -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Profile Settings</h5>
                        </div>
                        <div class="card-body">
                            <form action="/settings/profile" method="POST">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($data['user']['username'] ?? '') ?>" disabled>
                                    <div class="form-text">Your username cannot be changed</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control <?= isset($data['full_name_err']) && !empty($data['full_name_err']) ? 'is-invalid' : '' ?>" 
                                           id="full_name" name="full_name" value="<?= htmlspecialchars($data['user']['full_name'] ?? '') ?>">
                                    <div class="invalid-feedback"><?= $data['full_name_err'] ?? '' ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control <?= isset($data['email_err']) && !empty($data['email_err']) ? 'is-invalid' : '' ?>" 
                                           id="email" name="email" value="<?= htmlspecialchars($data['user']['email'] ?? '') ?>">
                                    <div class="invalid-feedback"><?= $data['email_err'] ?? '' ?></div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Password Settings -->
                <div class="tab-pane fade" id="password">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form action="/settings/password" method="POST">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control <?= isset($data['current_password_err']) && !empty($data['current_password_err']) ? 'is-invalid' : '' ?>" 
                                           id="current_password" name="current_password">
                                    <div class="invalid-feedback"><?= $data['current_password_err'] ?? '' ?></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control <?= isset($data['new_password_err']) && !empty($data['new_password_err']) ? 'is-invalid' : '' ?>" 
                                           id="new_password" name="new_password">
                                    <div class="invalid-feedback"><?= $data['new_password_err'] ?? '' ?></div>
                                    <div class="form-text">Password must be at least 6 characters long</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control <?= isset($data['confirm_password_err']) && !empty($data['confirm_password_err']) ? 'is-invalid' : '' ?>" 
                                           id="confirm_password" name="confirm_password">
                                    <div class="invalid-feedback"><?= $data['confirm_password_err'] ?? '' ?></div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Update Password</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Notification Settings -->
                <div class="tab-pane fade" id="notifications">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Notification Preferences</h5>
                        </div>
                        <div class="card-body">
                            <form action="/settings/notifications" method="POST">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" 
                                           <?= ($data['notification_settings']['email_notifications'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="email_notifications">Email Notifications</label>
                                    <div class="form-text">Receive email notifications for important updates</div>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="task_reminders" name="task_reminders" 
                                           <?= ($data['notification_settings']['task_reminders'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="task_reminders">Task Reminders</label>
                                    <div class="form-text">Receive reminders for upcoming and overdue tasks</div>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="project_updates" name="project_updates" 
                                           <?= ($data['notification_settings']['project_updates'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="project_updates">Project Updates</label>
                                    <div class="form-text">Receive notifications when projects are updated</div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save Preferences</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Theme Settings -->
                <div class="tab-pane fade" id="theme">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Theme Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Color Theme</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="colorTheme" id="lightTheme" checked>
                                        <label class="form-check-label" for="lightTheme">
                                            Light
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="colorTheme" id="darkTheme">
                                        <label class="form-check-label" for="darkTheme">
                                            Dark
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="colorTheme" id="systemTheme">
                                        <label class="form-check-label" for="systemTheme">
                                            System Default
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Font Size</label>
                                <select class="form-select">
                                    <option>Small</option>
                                    <option selected>Medium</option>
                                    <option>Large</option>
                                </select>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i> Theme settings are currently stored in your browser and will not persist across devices.
                            </div>
                            
                            <button class="btn btn-primary">Save Theme Settings</button>
                        </div>
                    </div>
                </div>
                
                <!-- Admin Settings -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <div class="tab-pane fade" id="admin">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-primary bg-opacity-10 py-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-gear-wide-connected fs-4 me-2 text-primary"></i>
                                <h5 class="mb-0">System Configuration</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                                <div>
                                    These settings affect the entire application. Use with caution.
                                </div>
                            </div>
                            
                            <form action="<?= URLROOT ?>/settings/admin" method="POST">
                                <!-- Application Status Section -->
                                <div class="admin-section mb-4">
                                    <h6 class="admin-section-title">
                                        <i class="bi bi-toggle-on me-2"></i>
                                        Application Status
                                    </h6>
                                    <div class="card border bg-light">
                                        <div class="card-body">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" role="switch" id="maintenance_mode" name="maintenance_mode">
                                                <label class="form-check-label fw-medium" for="maintenance_mode">
                                                    Maintenance Mode
                                                </label>
                                                <div class="form-text">When enabled, only administrators can access the application.</div>
                                            </div>
                                            
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" role="switch" id="enable_registration" name="enable_registration" checked>
                                                <label class="form-check-label fw-medium" for="enable_registration">
                                                    User Registration
                                                </label>
                                                <div class="form-text">Allow new users to register accounts.</div>
                                            </div>
                                            
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="enable_api" name="enable_api">
                                                <label class="form-check-label fw-medium" for="enable_api">
                                                    API Access
                                                </label>
                                                <div class="form-text">Enable API endpoints for third-party integrations.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Default Settings Section -->
                                <div class="admin-section mb-4">
                                    <h6 class="admin-section-title">
                                        <i class="bi bi-sliders me-2"></i>
                                        Default Settings
                                    </h6>
                                    <div class="card border bg-light">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="default_project_category" class="form-label fw-medium">Default Project Category</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                                        <input type="text" class="form-control" id="default_project_category" name="default_project_category" placeholder="General">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="default_project_status" class="form-label fw-medium">Default Project Status</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-flag"></i></span>
                                                        <select class="form-select" id="default_project_status" name="default_project_status">
                                                            <option value="Planning">Planning</option>
                                                            <option value="In Progress">In Progress</option>
                                                            <option value="On Hold">On Hold</option>
                                                            <option value="Completed">Completed</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label for="default_task_priority" class="form-label fw-medium">Default Task Priority</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-arrow-up-circle"></i></span>
                                                        <select class="form-select" id="default_task_priority" name="default_task_priority">
                                                            <option value="Low">Low</option>
                                                            <option value="Medium" selected>Medium</option>
                                                            <option value="High">High</option>
                                                            <option value="Critical">Critical</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="default_date_format" class="form-label fw-medium">Date Format</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                                        <select class="form-select" id="default_date_format" name="default_date_format">
                                                            <option value="Y-m-d">YYYY-MM-DD</option>
                                                            <option value="m/d/Y">MM/DD/YYYY</option>
                                                            <option value="d/m/Y">DD/MM/YYYY</option>
                                                            <option value="M j, Y">Month D, YYYY</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- System Limits Section -->
                                <div class="admin-section mb-4">
                                    <h6 class="admin-section-title">
                                        <i class="bi bi-speedometer me-2"></i>
                                        System Limits
                                    </h6>
                                    <div class="card border bg-light">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="max_upload_size" class="form-label fw-medium">Maximum Upload Size (MB)</label>
                                                    <input type="number" class="form-control" id="max_upload_size" name="max_upload_size" min="1" max="100" value="10">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="max_projects" class="form-label fw-medium">Projects Per User</label>
                                                    <input type="number" class="form-control" id="max_projects" name="max_projects" min="0" max="1000" value="0">
                                                    <div class="form-text">Enter 0 for unlimited</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>Save Changes
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary ms-2">
                                        <i class="bi bi-arrow-counterclockwise me-2"></i>Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- User Management Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-primary bg-opacity-10 py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-people fs-4 me-2 text-primary"></i>
                                    <h5 class="mb-0">User Management</h5>
                                </div>
                                <a href="<?= URLROOT ?>/admin/users" class="btn btn-sm btn-outline-primary">
                                    View All Users
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row gx-3 gy-3">
                                <div class="col-sm-6 col-md-3">
                                    <div class="card bg-light h-100">
                                        <div class="card-body text-center">
                                            <div class="display-4 mb-2">27</div>
                                            <div class="text-muted">Total Users</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="card bg-light h-100">
                                        <div class="card-body text-center">
                                            <div class="display-4 mb-2">5</div>
                                            <div class="text-muted">Admins</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="card bg-light h-100">
                                        <div class="card-body text-center">
                                            <div class="display-4 mb-2">18</div>
                                            <div class="text-muted">Active Users</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="card bg-light h-100">
                                        <div class="card-body text-center">
                                            <div class="display-4 mb-2">4</div>
                                            <div class="text-muted">Inactive</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- System Logs Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary bg-opacity-10 py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-journal-text fs-4 me-2 text-primary"></i>
                                    <h5 class="mb-0">System Logs</h5>
                                </div>
                                <a href="<?= URLROOT ?>/admin/logs" class="btn btn-sm btn-outline-primary">
                                    View All Logs
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Timestamp</th>
                                            <th>Event</th>
                                            <th>User</th>
                                            <th>IP Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-nowrap text-muted">Today, 14:45</td>
                                            <td><span class="badge bg-info">Login</span> Successful login</td>
                                            <td>admin@example.com</td>
                                            <td>192.168.1.1</td>
                                        </tr>
                                        <tr>
                                            <td class="text-nowrap text-muted">Today, 13:28</td>
                                            <td><span class="badge bg-warning">Setting</span> Changed system setting</td>
                                            <td>admin@example.com</td>
                                            <td>192.168.1.1</td>
                                        </tr>
                                        <tr>
                                            <td class="text-nowrap text-muted">Today, 12:15</td>
                                            <td><span class="badge bg-danger">Error</span> Database connection failed</td>
                                            <td>system</td>
                                            <td>--</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Additional Custom Styles */
.settings-nav .list-group-item {
    border-left: 3px solid transparent;
    transition: all 0.2s ease;
}

.settings-nav .list-group-item.active {
    border-left: 3px solid var(--bs-primary);
    background-color: rgba(13, 110, 253, 0.05);
}

.admin-section-title {
    padding-bottom: 0.75rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    font-weight: 600;
}

.form-check-input:checked {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

.display-4 {
    font-weight: 600;
    color: var(--bs-primary);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get active tab from URL hash
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector(`.list-group-item[href="${hash}"]`);
        if (tab) {
            const tabTrigger = new bootstrap.Tab(tab);
            tabTrigger.show();
        }
    }
    
    // Keep track of tab changes in URL
    const tabs = document.querySelectorAll('.list-group-item');
    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (e) {
            window.location.hash = e.target.getAttribute('href');
        });
    });
});
</script>

<!-- Include theme settings script -->
<script src="<?= URLROOT ?>/views/settings/theme.js"></script> 