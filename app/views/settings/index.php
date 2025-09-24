<?php require VIEWSPATH . '/inc/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="bi bi-gear-fill text-primary"></i> Personal Settings
                    </h1>
                    <nav aria-label="breadcrumb" class="mt-2">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                            <li class="breadcrumb-item active">Settings</li>
                        </ol>
                    </nav>
                </div>
                <?php if ($data['isAdmin']): ?>
                <a href="/admin/settings" class="btn btn-outline-primary">
                    <i class="bi bi-shield-lock me-2"></i>Admin Settings
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['settings_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= $_SESSION['settings_success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['settings_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['settings_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-circle me-2"></i><?= $_SESSION['settings_error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['settings_error']); ?>
    <?php endif; ?>

    <!-- Settings Content -->
    <div class="row">
        <div class="col-lg-3 mb-4">
            <!-- Settings Navigation -->
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="bi bi-person me-2"></i> Profile
                        </a>
                        <a href="#preferences" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-sliders me-2"></i> Preferences
                        </a>
                        <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-bell me-2"></i> Notifications
                        </a>
                        <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-shield-lock me-2"></i> Security
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Profile Tab -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <form action="<?= URLROOT ?>/settings/updateProfile" method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="full_name" class="form-label fw-medium">Full Name</label>
                                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                                value="<?= htmlspecialchars($data['user']['full_name'] ?? '') ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label fw-medium">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                value="<?= htmlspecialchars($data['user']['email'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="username" class="form-label fw-medium">Username</label>
                                            <input type="text" class="form-control bg-light" id="username" name="username" 
                                                value="<?= htmlspecialchars($data['user']['username'] ?? '') ?>" readonly>
                                            <div class="form-text">Username cannot be changed</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="department" class="form-label fw-medium">Department</label>
                                            <input type="text" class="form-control" id="department" name="department" 
                                                value="<?= htmlspecialchars($data['user']['department'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Preferences Tab -->
                <div class="tab-pane fade" id="preferences">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">Display Preferences</h6>
                                </div>
                                <div class="card-body">
                                    <form action="<?= URLROOT ?>/settings/updatePreferences" method="POST">
                                        <div class="mb-3">
                                            <label for="theme" class="form-label fw-medium">Theme</label>
                                            <select class="form-select" id="theme" name="theme">
                                                <option value="light" <?= ($data['userSettings']['theme'] ?? 'light') === 'light' ? 'selected' : '' ?>>Light</option>
                                                <option value="dark" <?= ($data['userSettings']['theme'] ?? '') === 'dark' ? 'selected' : '' ?>>Dark</option>
                                                <option value="auto" <?= ($data['userSettings']['theme'] ?? '') === 'auto' ? 'selected' : '' ?>>Auto (System)</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="items_per_page" class="form-label fw-medium">Items per Page</label>
                                            <select class="form-select" id="items_per_page" name="items_per_page">
                                                <option value="10" <?= ($data['userSettings']['items_per_page'] ?? '25') === '10' ? 'selected' : '' ?>>10</option>
                                                <option value="25" <?= ($data['userSettings']['items_per_page'] ?? '25') === '25' ? 'selected' : '' ?>>25</option>
                                                <option value="50" <?= ($data['userSettings']['items_per_page'] ?? '') === '50' ? 'selected' : '' ?>>50</option>
                                                <option value="100" <?= ($data['userSettings']['items_per_page'] ?? '') === '100' ? 'selected' : '' ?>>100</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="date_format" class="form-label fw-medium">Date Format</label>
                                            <select class="form-select" id="date_format" name="date_format">
                                                <option value="Y-m-d" <?= ($data['userSettings']['date_format'] ?? 'M j, Y') === 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                                <option value="m/d/Y" <?= ($data['userSettings']['date_format'] ?? '') === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                                <option value="d/m/Y" <?= ($data['userSettings']['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                                <option value="M j, Y" <?= ($data['userSettings']['date_format'] ?? 'M j, Y') === 'M j, Y' ? 'selected' : '' ?>>Month D, YYYY</option>
                                            </select>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-save me-2"></i>Save Preferences
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">Time & Language</h6>
                                </div>
                                <div class="card-body">
                                    <form action="<?= URLROOT ?>/settings/updateTimeSettings" method="POST">
                                        <div class="mb-3">
                                            <label for="timezone" class="form-label fw-medium">Timezone</label>
                                            <select class="form-select" id="timezone" name="timezone">
                                                <?php 
                                                $userTimezone = $data['userSettings']['timezone'] ?? 'UTC';
                                                $timezones = [
                                                    'UTC' => 'UTC',
                                                    'America/New_York' => 'Eastern Time (US)',
                                                    'America/Chicago' => 'Central Time (US)',
                                                    'America/Denver' => 'Mountain Time (US)',
                                                    'America/Los_Angeles' => 'Pacific Time (US)',
                                                    'Europe/London' => 'London',
                                                    'Europe/Paris' => 'Paris',
                                                    'Europe/Berlin' => 'Berlin',
                                                    'Asia/Tokyo' => 'Tokyo',
                                                    'Asia/Shanghai' => 'Shanghai',
                                                    'Australia/Sydney' => 'Sydney'
                                                ];
                                                foreach ($timezones as $tz => $label): ?>
                                                    <option value="<?= $tz ?>" <?= $userTimezone === $tz ? 'selected' : '' ?>><?= $label ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="time_format" class="form-label fw-medium">Time Format</label>
                                            <select class="form-select" id="time_format" name="time_format">
                                                <option value="12" <?= ($data['userSettings']['time_format'] ?? '12') === '12' ? 'selected' : '' ?>>12-hour (3:30 PM)</option>
                                                <option value="24" <?= ($data['userSettings']['time_format'] ?? '') === '24' ? 'selected' : '' ?>>24-hour (15:30)</option>
                                            </select>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-save me-2"></i>Save Settings
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Tab -->
                <div class="tab-pane fade" id="notifications">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Notification Preferences</h5>
                        </div>
                        <div class="card-body">
                            <form action="<?= URLROOT ?>/settings/updateNotifications" method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold mb-3">Email Notifications</h6>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="email_new_tickets" name="email_new_tickets" 
                                                <?= ($data['userSettings']['email_new_tickets'] ?? true) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="email_new_tickets">
                                                New tickets assigned to me
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="email_ticket_updates" name="email_ticket_updates" 
                                                <?= ($data['userSettings']['email_ticket_updates'] ?? true) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="email_ticket_updates">
                                                Ticket status updates
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="email_comments" name="email_comments" 
                                                <?= ($data['userSettings']['email_comments'] ?? true) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="email_comments">
                                                New comments on my tickets
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold mb-3">System Notifications</h6>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="browser_notifications" name="browser_notifications" 
                                                <?= ($data['userSettings']['browser_notifications'] ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="browser_notifications">
                                                Browser notifications
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="daily_digest" name="daily_digest" 
                                                <?= ($data['userSettings']['daily_digest'] ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="daily_digest">
                                                Daily activity digest
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="weekly_summary" name="weekly_summary" 
                                                <?= ($data['userSettings']['weekly_summary'] ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="weekly_summary">
                                                Weekly summary report
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>Save Notification Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div class="tab-pane fade" id="security">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">Change Password</h6>
                                </div>
                                <div class="card-body">
                                    <form action="<?= URLROOT ?>/settings/updatePassword" method="POST">
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label fw-medium">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label fw-medium">New Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label fw-medium">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-shield-lock me-2"></i>Update Password
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">Session Management</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Current Session:</strong>
                                        <div class="text-muted small">
                                            Last login: <?= date('M j, Y g:i A', strtotime($data['user']['last_login'] ?? 'now')) ?>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <a href="<?= URLROOT ?>/auth/logout" class="btn btn-outline-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                                        </a>
                                    </div>
                                </div>
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
    // Handle tab switching
    const tabLinks = document.querySelectorAll('[data-bs-toggle="list"]');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs and content
            tabLinks.forEach(l => l.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Show corresponding content
            const targetId = this.getAttribute('href').substring(1);
            const targetPane = document.getElementById(targetId);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
            }
        });
    });
});
</script>

<?php require VIEWSPATH . '/inc/footer.php'; ?>
