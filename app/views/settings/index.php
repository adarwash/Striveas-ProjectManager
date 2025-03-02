<!-- Include theme CSS -->
<link rel="stylesheet" href="/views/settings/theme.css">

<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3">Account Settings</h1>
            <p class="text-muted">Manage your account settings and preferences</p>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                    <i class="bi bi-person me-2"></i> Profile
                </a>
                <a href="#password" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="bi bi-key me-2"></i> Password
                </a>
                <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="bi bi-bell me-2"></i> Notifications
                </a>
                <a href="#theme" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="bi bi-brush me-2"></i> Theme
                </a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="#admin" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="bi bi-shield-lock me-2"></i> Admin Settings
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
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Admin Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i> These settings affect the entire application. Use with caution.
                            </div>
                            
                            <form action="/settings/admin" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Application Mode</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode">
                                        <label class="form-check-label" for="maintenance_mode">Maintenance Mode</label>
                                        <div class="form-text">When enabled, only administrators can access the application.</div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Default Project Settings</label>
                                    <input type="text" class="form-control mb-2" name="default_project_category" placeholder="Default Project Category">
                                    <select class="form-select" name="default_project_status">
                                        <option value="">Default Project Status</option>
                                        <option value="Planning">Planning</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="On Hold">On Hold</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save Admin Settings</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

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
<script src="/views/settings/theme.js"></script> 