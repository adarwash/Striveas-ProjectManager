<?php
// Prepare theme for hero section
$currentNavBackground = $user_settings['nav_background'] ?? '';
// Default gradient if none selected (matches default header style roughly or a nice profile fallback)
$heroBackground = $currentNavBackground ?: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';

// Prepare profile picture
$profilePic = '/uploads/profile_pictures/' . ($user['profile_picture'] ?? 'default.png');
if (!file_exists('../public' . $profilePic) || empty($user['profile_picture'])) {
    $profilePic = 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name'] ?? $user['username']) . '&background=random&size=256';
} else {
    $profilePic = $profilePic . '?v=' . time();
}
?>

<div class="container-fluid px-4">
    <!-- Hero Section -->
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-header border-0 p-0" style="height: 160px; background: <?= htmlspecialchars($heroBackground) ?>;">
            <!-- Optional: Add a subtle pattern or overlay if desired -->
        </div>
        <div class="card-body position-relative pt-0 pb-4">
            <div class="row align-items-end">
                <div class="col-auto">
                    <div class="position-relative" style="margin-top: -80px;">
                        <img src="<?= $profilePic ?>" alt="Profile Picture" class="rounded-circle border border-4 border-white shadow" style="width: 150px; height: 150px; object-fit: cover; background: #fff;">
                        <button class="btn btn-light btn-sm rounded-circle position-absolute bottom-0 end-0 shadow-sm border" 
                                data-bs-toggle="modal" data-bs-target="#changeProfilePicModal" title="Change Picture">
                            <i class="bi bi-camera"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md mb-3 mb-md-0">
                    <h2 class="fw-bold mb-1"><?= htmlspecialchars($user['full_name'] ?? 'User') ?></h2>
                    <p class="text-muted mb-0">
                        <i class="bi bi-briefcase me-1"></i> <?= htmlspecialchars($user['position'] ?? 'Team Member') ?>
                        <?php if (isset($user['department'])): ?>
                            <span class="mx-2">•</span> <i class="bi bi-building me-1"></i> <?= htmlspecialchars($user['department']) ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-auto text-md-end">
                    <div class="d-flex gap-2 mt-3 mt-md-0">
                        <a href="/profile/edit" class="btn btn-outline-primary">
                            <i class="bi bi-pencil-square me-1"></i> Edit Profile
                        </a>
                        <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#customizeThemeModal">
                            <i class="bi bi-palette me-1"></i> Theme
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-gear"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal"><i class="bi bi-key me-2"></i>Change Password</a></li>
                                <li><a class="dropdown-item" href="/settings"><i class="bi bi-sliders me-2"></i>Global Settings</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Messages -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i> <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Left Column: About, Skills, Contact -->
        <div class="col-lg-4">
            <!-- About Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 card-title"><i class="bi bi-person-lines-fill me-2"></i>About</h5>
                    <a href="/profile/edit" class="btn btn-sm btn-light text-primary"><i class="bi bi-pencil"></i></a>
                </div>
                <div class="card-body">
                    <?php if (empty($user['bio'])): ?>
                        <p class="text-muted fst-italic mb-0">No bio provided. <a href="/profile/edit">Add your bio</a></p>
                    <?php else: ?>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0 card-title"><i class="bi bi-info-circle me-2"></i>Contact Info</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex align-items-center">
                            <span class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle p-2 me-3" style="width: 40px; height: 40px;">
                                <i class="bi bi-envelope text-primary"></i>
                            </span>
                            <div>
                                <small class="text-muted d-block">Email Address</small>
                                <span class="fw-medium"><?= htmlspecialchars($user['email'] ?? 'Not provided') ?></span>
                            </div>
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <span class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle p-2 me-3" style="width: 40px; height: 40px;">
                                <i class="bi bi-person-badge text-primary"></i>
                            </span>
                            <div>
                                <small class="text-muted d-block">Username</small>
                                <span class="fw-medium"><?= htmlspecialchars($user['username'] ?? 'Not provided') ?></span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Skills -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 card-title"><i class="bi bi-lightning-charge me-2"></i>Skills</h5>
                    <a href="/profile/skills" class="btn btn-sm btn-light text-primary"><i class="bi bi-pencil"></i></a>
                </div>
                <div class="card-body">
                    <?php if (empty($user_skills)): ?>
                        <p class="text-muted fst-italic mb-0">No skills added yet. <a href="/profile/skills">Add your skills</a></p>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($user_skills as $skill): ?>
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill"><?= htmlspecialchars($skill['name']) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Stats & Activity -->
        <div class="col-lg-8">
            <!-- Stats Row -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-md-3">
                    <div class="card shadow-sm h-100 border-start border-4 border-primary">
                        <div class="card-body p-3">
                            <small class="text-muted text-uppercase fw-bold">Active Projects</small>
                            <div class="d-flex align-items-center mt-2">
                                <h2 class="mb-0 me-2"><?= $projects_count['in_progress'] ?? 0 ?></h2>
                                <span class="badge bg-primary-subtle text-primary rounded-pill"><i class="bi bi-folder"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card shadow-sm h-100 border-start border-4 border-success">
                        <div class="card-body p-3">
                            <small class="text-muted text-uppercase fw-bold">Completed Projects</small>
                            <div class="d-flex align-items-center mt-2">
                                <h2 class="mb-0 me-2"><?= $projects_count['completed'] ?? 0 ?></h2>
                                <span class="badge bg-success-subtle text-success rounded-pill"><i class="bi bi-check-lg"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card shadow-sm h-100 border-start border-4 border-warning">
                        <div class="card-body p-3">
                            <small class="text-muted text-uppercase fw-bold">Active Tasks</small>
                            <div class="d-flex align-items-center mt-2">
                                <h2 class="mb-0 me-2"><?= $tasks_stats['in_progress'] ?? 0 ?></h2>
                                <span class="badge bg-warning-subtle text-warning rounded-pill"><i class="bi bi-list-task"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card shadow-sm h-100 border-start border-4 border-info">
                        <div class="card-body p-3">
                            <small class="text-muted text-uppercase fw-bold">Total Tasks</small>
                            <div class="d-flex align-items-center mt-2">
                                <h2 class="mb-0 me-2"><?= $tasks_stats['total'] ?? 0 ?></h2>
                                <span class="badge bg-info-subtle text-info rounded-pill"><i class="bi bi-list-check"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs nav-fill mb-4" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                        <i class="bi bi-bar-chart-fill me-2"></i>Performance
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab" aria-controls="activity" aria-selected="false">
                        <i class="bi bi-clock-history me-2"></i>Activity Feed
                    </button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="profileTabsContent">
                <!-- Overview Tab -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-transparent">
                                    <h6 class="mb-0 fw-bold">Projects Breakdown</h6>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $pTotal = max((int)($projects_count['total'] ?? 0), 1);
                                    $pInProgress = ((int)($projects_count['in_progress'] ?? 0) / $pTotal) * 100;
                                    $pPlanning = ((int)($projects_count['planning'] ?? 0) / $pTotal) * 100;
                                    $pCompleted = ((int)($projects_count['completed'] ?? 0) / $pTotal) * 100;
                                    $pOnHold = ((int)($projects_count['on_hold'] ?? 0) / $pTotal) * 100;
                                    ?>
                                    <div class="progress-stacked mb-4" style="height: 25px;">
                                        <div class="progress" role="progressbar" style="width: <?= $pInProgress ?>%" title="In Progress">
                                            <div class="progress-bar bg-primary"></div>
                                        </div>
                                        <div class="progress" role="progressbar" style="width: <?= $pPlanning ?>%" title="Planning">
                                            <div class="progress-bar bg-info"></div>
                                        </div>
                                        <div class="progress" role="progressbar" style="width: <?= $pCompleted ?>%" title="Completed">
                                            <div class="progress-bar bg-success"></div>
                                        </div>
                                        <div class="progress" role="progressbar" style="width: <?= $pOnHold ?>%" title="On Hold">
                                            <div class="progress-bar bg-warning"></div>
                                        </div>
                                    </div>
                                    <div class="list-group list-group-flush small">
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><i class="bi bi-circle-fill text-primary me-2"></i>In Progress</span>
                                            <span class="fw-bold"><?= $projects_count['in_progress'] ?? 0 ?></span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><i class="bi bi-circle-fill text-info me-2"></i>Planning</span>
                                            <span class="fw-bold"><?= $projects_count['planning'] ?? 0 ?></span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><i class="bi bi-circle-fill text-success me-2"></i>Completed</span>
                                            <span class="fw-bold"><?= $projects_count['completed'] ?? 0 ?></span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><i class="bi bi-circle-fill text-warning me-2"></i>On Hold</span>
                                            <span class="fw-bold"><?= $projects_count['on_hold'] ?? 0 ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-transparent">
                                    <h6 class="mb-0 fw-bold">Tasks Status</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($tasks_stats['overdue']) && $tasks_stats['overdue'] > 0): ?>
                                    <div class="alert alert-danger py-2 mb-3 small d-flex align-items-center">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <strong><?= $tasks_stats['overdue'] ?></strong>&nbsp;overdue tasks require attention
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php
                                    $tTotal = max((int)($tasks_stats['total'] ?? 0), 1);
                                    $tNotStarted = ((int)($tasks_stats['not_started'] ?? 0) / $tTotal) * 100;
                                    $tInProgress = ((int)($tasks_stats['in_progress'] ?? 0) / $tTotal) * 100;
                                    $tCompleted = ((int)($tasks_stats['completed'] ?? 0) / $tTotal) * 100;
                                    $tBlocked = ((int)($tasks_stats['blocked'] ?? 0) / $tTotal) * 100;
                                    ?>
                                    <div class="progress-stacked mb-4" style="height: 25px;">
                                        <div class="progress" role="progressbar" style="width: <?= $tNotStarted ?>%" title="Not Started">
                                            <div class="progress-bar bg-secondary"></div>
                                        </div>
                                        <div class="progress" role="progressbar" style="width: <?= $tInProgress ?>%" title="In Progress">
                                            <div class="progress-bar bg-primary"></div>
                                        </div>
                                        <div class="progress" role="progressbar" style="width: <?= $tCompleted ?>%" title="Completed">
                                            <div class="progress-bar bg-success"></div>
                                        </div>
                                        <div class="progress" role="progressbar" style="width: <?= $tBlocked ?>%" title="Blocked">
                                            <div class="progress-bar bg-danger"></div>
                                        </div>
                                    </div>
                                    <div class="list-group list-group-flush small">
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><i class="bi bi-circle-fill text-secondary me-2"></i>Not Started</span>
                                            <span class="fw-bold"><?= $tasks_stats['not_started'] ?? 0 ?></span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><i class="bi bi-circle-fill text-primary me-2"></i>In Progress</span>
                                            <span class="fw-bold"><?= $tasks_stats['in_progress'] ?? 0 ?></span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><i class="bi bi-circle-fill text-success me-2"></i>Completed</span>
                                            <span class="fw-bold"><?= $tasks_stats['completed'] ?? 0 ?></span>
                                        </div>
                                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span><i class="bi bi-circle-fill text-danger me-2"></i>Blocked</span>
                                            <span class="fw-bold"><?= $tasks_stats['blocked'] ?? 0 ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Tab -->
                <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <?php if (empty($recent_activity)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x display-4 mb-3 d-block"></i>
                                    No recent activity found.
                                </div>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($recent_activity as $index => $activity): ?>
                                        <div class="d-flex pb-4 <?= $index === count($recent_activity) - 1 ? '' : 'border-bottom mb-4' ?>">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                    <?php if ($activity['type'] === 'project'): ?>
                                                        <i class="bi bi-folder text-primary fs-5"></i>
                                                    <?php elseif ($activity['type'] === 'task'): ?>
                                                        <i class="bi bi-check2-square text-success fs-5"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-activity text-secondary fs-5"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($activity['title']) ?></h6>
                                                    <small class="text-muted"><?= date('M j, g:i A', strtotime($activity['activity_date'])) ?></small>
                                                </div>
                                                <p class="mb-0 text-muted small">
                                                    You <?= strtolower($activity['action']) ?> a <?= strtolower($activity['type']) ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Profile Picture Change Modal -->
<div class="modal fade" id="changeProfilePicModal" tabindex="-1" aria-labelledby="changeProfilePicModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeProfilePicModalLabel">Change Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="/profile/picture" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Select New Image</label>
                        <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/jpeg, image/png, image/gif" required>
                        <div class="form-text">Max file size: 2MB. Allowed formats: JPG, PNG, GIF.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Preview</label>
                        <div class="text-center">
                            <img id="image-preview" src="#" alt="Preview" class="img-fluid rounded-circle d-none" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Save New Picture</button>
                    </div>
                </form>
                
                <?php if (!empty($user['profile_picture'])): ?>
                <hr class="my-3">
                <form action="/profile/removePicture" method="POST" class="mt-3">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-trash"></i> Remove Current Picture
                        </button>
                    </div>
                    <div class="form-text text-center mt-2">This will reset your profile to the default avatar.</div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">
                    <i class="bi bi-key me-2"></i>Change Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/profile/changePassword" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password" name="new_password" minlength="6" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Minimum 6 characters</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div id="password-match-feedback" class="text-danger d-none">
                        <i class="bi bi-exclamation-circle"></i> Passwords do not match
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="changePasswordBtn">
                        <i class="bi bi-check-lg"></i> Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script for image preview -->
<script>
document.getElementById('profile_picture').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const imgPreview = document.getElementById('image-preview');
            imgPreview.src = event.target.result;
            imgPreview.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
});

// Toggle password visibility
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Password match validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    const feedback = document.getElementById('password-match-feedback');
    const submitBtn = document.getElementById('changePasswordBtn');
    
    if (confirmPassword && newPassword !== confirmPassword) {
        feedback.classList.remove('d-none');
        submitBtn.disabled = true;
    } else {
        feedback.classList.add('d-none');
        submitBtn.disabled = false;
    }
});

document.getElementById('new_password').addEventListener('input', function() {
    const confirmPassword = document.getElementById('confirm_password').value;
    if (confirmPassword) {
        document.getElementById('confirm_password').dispatchEvent(new Event('input'));
    }
});
</script>

<!-- Customize Theme Modal -->
<div class="modal fade" id="customizeThemeModal" tabindex="-1" aria-labelledby="customizeThemeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customizeThemeModalLabel">
                    <i class="bi bi-palette me-2"></i>Customize Theme
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/profile/updateTheme" method="POST">
                <input type="hidden" name="theme_project_card_headers_present" value="1">
                <div class="modal-body">
                    <p class="text-muted mb-3">Select a background style for the top search header bar.</p>
                    <?php
                        // Already extracted $currentNavBackground at top of file
                        $themeCardHeadersEnabled = !empty($user_settings['theme_card_headers']);
                        $themeProjectCardHeadersEnabled = array_key_exists('theme_project_card_headers', $user_settings)
                            ? !empty($user_settings['theme_project_card_headers'])
                            : $themeCardHeadersEnabled;
                        $savedHeaderTextColor = (string)($user_settings['theme_header_text_color'] ?? '');
                        $savedHeaderTextColor = strtoupper(trim($savedHeaderTextColor));
                        $headerTextMode = (preg_match('/^#[0-9A-F]{6}$/', $savedHeaderTextColor)) ? 'custom' : 'auto';
                        $headerTextColor = ($headerTextMode === 'custom') ? $savedHeaderTextColor : '#FFFFFF';
                        
                        $presetValues = [
                            '',
                            'linear-gradient(135deg, #0061f2 0%, rgba(105, 0, 199, 0.8) 100%)',
                            'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                            'linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%)',
                            'linear-gradient(to top, #09203f 0%, #537895 100%)',
                            'linear-gradient(to right, #654ea3, #eaafc8)',
                        ];
                        $isCustom = !in_array($currentNavBackground, $presetValues, true) && $currentNavBackground !== '';
                        
                        // Defaults match the app.css search-header gradient
                        $customStart = '#667eea';
                        $customEnd = '#764ba2';
                        $customAngle = 135;
                        
                        // If current saved value looks like a custom gradient we generated, parse it back into pickers
                        if ($isCustom && is_string($currentNavBackground)) {
                            if (preg_match('/linear-gradient\\(\\s*(\\d{1,3})deg\\s*,\\s*(#[0-9a-fA-F]{6})\\s*0%\\s*,\\s*(#[0-9a-fA-F]{6})\\s*100%\\s*\\)/', $currentNavBackground, $m)) {
                                $customAngle = max(0, min(360, (int)$m[1]));
                                $customStart = $m[2];
                                $customEnd = $m[3];
                            }
                        }
                    ?>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="theme_card_headers" name="theme_card_headers" value="1" <?= $themeCardHeadersEnabled ? 'checked' : '' ?>>
                                <label class="form-check-label" for="theme_card_headers">
                                    Apply theme to card headers
                                </label>
                            </div>
                            <div class="form-text">When enabled, all card headers will use your theme gradient. Turn it off to keep the default look.</div>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="theme_project_card_headers" name="theme_project_card_headers" value="1" <?= $themeProjectCardHeadersEnabled ? 'checked' : '' ?>>
                                <label class="form-check-label" for="theme_project_card_headers">
                                    Apply theme to project card headers
                                </label>
                            </div>
                            <div class="form-text">Controls the header bar on project cards (e.g. Projects grid view).</div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label mb-1">Header text color</label>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <div class="btn-group" role="group" aria-label="Header text color mode">
                                    <input type="radio" class="btn-check" name="header_text_mode" id="header_text_mode_auto" value="auto" <?= $headerTextMode === 'auto' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-secondary btn-sm" for="header_text_mode_auto">Auto</label>
                                    
                                    <input type="radio" class="btn-check" name="header_text_mode" id="header_text_mode_custom" value="custom" <?= $headerTextMode === 'custom' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-secondary btn-sm" for="header_text_mode_custom">Custom</label>
                                </div>
                                
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" class="form-control form-control-color" id="header_text_color" name="header_text_color" value="<?= htmlspecialchars($headerTextColor) ?>" title="Pick header text color">
                                    <span class="small text-muted" id="headerTextColorHex"><?= htmlspecialchars($headerTextColor) ?></span>
                                </div>
                            </div>
                            <div class="form-text">Used for themed card headers (and other themed header elements). Choose Auto to use the default.</div>
                        </div>

                        <!-- Default (None) -->
                        <div class="col-6">
                            <label class="theme-option-label w-100">
                                <input type="radio" name="nav_background" value="" class="d-none theme-option-input" <?= ($currentNavBackground === '') ? 'checked' : '' ?>>
                                <div class="theme-preview border bg-light"></div>
                                <div class="text-center mt-1 small">Default</div>
                            </label>
                        </div>
                        
                        <!-- Ocean Blue -->
                        <div class="col-6">
                            <label class="theme-option-label w-100">
                                <input type="radio" name="nav_background" value="linear-gradient(135deg, #0061f2 0%, rgba(105, 0, 199, 0.8) 100%)" class="d-none theme-option-input" <?= ($currentNavBackground === 'linear-gradient(135deg, #0061f2 0%, rgba(105, 0, 199, 0.8) 100%)') ? 'checked' : '' ?>>
                                <div class="theme-preview" style="background: linear-gradient(135deg, #0061f2 0%, rgba(105, 0, 199, 0.8) 100%);"></div>
                                <div class="text-center mt-1 small">Ocean Blue</div>
                            </label>
                        </div>

                        <!-- Sunset Orange -->
                        <div class="col-6">
                            <label class="theme-option-label w-100">
                                <input type="radio" name="nav_background" value="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" class="d-none theme-option-input" <?= ($currentNavBackground === 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)') ? 'checked' : '' ?>>
                                <div class="theme-preview" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                                <div class="text-center mt-1 small">Sunset Orange</div>
                            </label>
                        </div>

                        <!-- Emerald Green -->
                        <div class="col-6">
                            <label class="theme-option-label w-100">
                                <input type="radio" name="nav_background" value="linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%)" class="d-none theme-option-input" <?= ($currentNavBackground === 'linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%)') ? 'checked' : '' ?>>
                                <div class="theme-preview" style="background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);"></div>
                                <div class="text-center mt-1 small">Emerald Green</div>
                            </label>
                        </div>

                         <!-- Midnight -->
                        <div class="col-6">
                            <label class="theme-option-label w-100">
                                <input type="radio" name="nav_background" value="linear-gradient(to top, #09203f 0%, #537895 100%)" class="d-none theme-option-input" <?= ($currentNavBackground === 'linear-gradient(to top, #09203f 0%, #537895 100%)') ? 'checked' : '' ?>>
                                <div class="theme-preview" style="background: linear-gradient(to top, #09203f 0%, #537895 100%);"></div>
                                <div class="text-center mt-1 small">Midnight</div>
                            </label>
                        </div>

                         <!-- Royal Purple -->
                        <div class="col-6">
                            <label class="theme-option-label w-100">
                                <input type="radio" name="nav_background" value="linear-gradient(to right, #654ea3, #eaafc8)" class="d-none theme-option-input" <?= ($currentNavBackground === 'linear-gradient(to right, #654ea3, #eaafc8)') ? 'checked' : '' ?>>
                                <div class="theme-preview" style="background: linear-gradient(to right, #654ea3, #eaafc8);"></div>
                                <div class="text-center mt-1 small">Royal Purple</div>
                            </label>
                        </div>
                        
                        <!-- Custom (Color Picker Gradient) -->
                        <div class="col-12">
                            <div class="border rounded p-3 bg-light">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="fw-semibold"><i class="bi bi-sliders me-2"></i>Custom Gradient</div>
                                    <div class="small text-muted">Pick two colours</div>
                                </div>
                                
                                <label class="theme-option-label w-100 mt-2 mb-0">
                                    <input type="radio" name="nav_background" value="custom" id="nav_background_custom" class="d-none theme-option-input" <?= $isCustom ? 'checked' : '' ?>>
                                    <div class="theme-preview" id="customGradientPreview" style="background: <?= htmlspecialchars($isCustom ? $currentNavBackground : ('linear-gradient(' . (int)$customAngle . 'deg, ' . $customStart . ' 0%, ' . $customEnd . ' 100%)')) ?>;"></div>
                                    <div class="text-center mt-1 small">Custom</div>
                                </label>
                                
                                <div class="row g-3 mt-1">
                                    <div class="col-6 col-md-4">
                                        <label for="custom_color_start" class="form-label small mb-1">Start colour</label>
                                        <input type="color" class="form-control form-control-color w-100" id="custom_color_start" name="custom_color_start" value="<?= htmlspecialchars($customStart) ?>" title="Pick start colour">
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <label for="custom_color_end" class="form-label small mb-1">End colour</label>
                                        <input type="color" class="form-control form-control-color w-100" id="custom_color_end" name="custom_color_end" value="<?= htmlspecialchars($customEnd) ?>" title="Pick end colour">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label for="custom_angle" class="form-label small mb-1">
                                            Angle: <span id="customAngleValue"><?= (int)$customAngle ?></span>°
                                        </label>
                                        <input type="range" class="form-range" id="custom_angle" name="custom_angle" min="0" max="360" step="1" value="<?= (int)$customAngle ?>">
                                    </div>
                                </div>
                                
                                <div class="form-text mt-2">Changing colours/angle will automatically select “Custom”.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Theme</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.theme-preview {
    height: 80px;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.theme-option-input:checked + .theme-preview {
    transform: scale(0.95);
    box-shadow: 0 0 0 3px var(--bs-primary);
}
.theme-option-label:hover .theme-preview {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
</style> 

<script>
(function () {
    const customRadio = document.getElementById('nav_background_custom');
    const preview = document.getElementById('customGradientPreview');
    const start = document.getElementById('custom_color_start');
    const end = document.getElementById('custom_color_end');
    const angle = document.getElementById('custom_angle');
    const angleValue = document.getElementById('customAngleValue');
    
    const headerTextAuto = document.getElementById('header_text_mode_auto');
    const headerTextCustom = document.getElementById('header_text_mode_custom');
    const headerTextColor = document.getElementById('header_text_color');
    const headerTextHex = document.getElementById('headerTextColorHex');
    
    if (!customRadio || !preview || !start || !end || !angle) return;
    
    function safeAngle(v) {
        const n = parseInt(v || '135', 10);
        if (Number.isNaN(n)) return 135;
        return Math.max(0, Math.min(360, n));
    }
    
    function buildGradient() {
        const a = safeAngle(angle.value);
        const s = start.value || '#667eea';
        const e = end.value || '#764ba2';
        if (angleValue) angleValue.textContent = String(a);
        return `linear-gradient(${a}deg, ${s} 0%, ${e} 100%)`;
    }
    
    function updatePreview() {
        preview.style.background = buildGradient();
    }
    
    function selectCustom() {
        customRadio.checked = true;
    }
    
    start.addEventListener('input', () => { selectCustom(); updatePreview(); });
    end.addEventListener('input', () => { selectCustom(); updatePreview(); });
    angle.addEventListener('input', () => { selectCustom(); updatePreview(); });
    preview.addEventListener('click', () => { selectCustom(); });
    
    // Initialise (keeps preview in sync with picker defaults)
    updatePreview();
    
    // Header text color UI
    if (headerTextAuto && headerTextCustom && headerTextColor) {
        function syncHeaderColorUI() {
            const isCustom = headerTextCustom.checked;
            headerTextColor.disabled = !isCustom;
            headerTextColor.style.opacity = isCustom ? '1' : '0.6';
            if (headerTextHex) {
                headerTextHex.textContent = headerTextColor.value || '#FFFFFF';
            }
        }
        
        headerTextAuto.addEventListener('change', syncHeaderColorUI);
        headerTextCustom.addEventListener('change', syncHeaderColorUI);
        headerTextColor.addEventListener('input', () => {
            headerTextCustom.checked = true;
            syncHeaderColorUI();
        });
        
        syncHeaderColorUI();
    }
})();
</script>