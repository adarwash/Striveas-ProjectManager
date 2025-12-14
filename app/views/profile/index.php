<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3">My Profile</h1>
            
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
        <!-- User Profile -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <?php
                    // Get profile picture or default
                    $profilePic = '/uploads/profile_pictures/' . ($user['profile_picture'] ?? 'default.png');
                    if (!file_exists('../public' . $profilePic) || empty($user['profile_picture'])) {
                        $profilePic = 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name'] ?? $user['username']) . '&background=random&size=256';
                    } else {
                        $profilePic = $profilePic . '?v=' . time(); // Add cache buster
                    }
                    ?>
                    <img src="<?= $profilePic ?>" alt="Profile Picture" class="rounded-circle img-fluid mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    
                    <h4><?= htmlspecialchars($user['full_name'] ?? 'User') ?></h4>
                    <p class="text-muted"><?= htmlspecialchars($user['position'] ?? 'Team Member') ?></p>
                    
                    <div class="d-grid gap-2">
                        <a href="/profile/edit" class="btn btn-primary">
                            <i class="bi bi-pencil-square"></i> Edit Profile
                        </a>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#changeProfilePicModal">
                            <i class="bi bi-camera"></i> Change Picture
                        </button>
                        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="bi bi-key"></i> Change Password
                        </button>
                        <a href="/settings" class="btn btn-outline-primary">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- User Details -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Contact Information</h5>
                </div>
                <div class="card-body">
                    <p><strong><i class="bi bi-envelope"></i> Email:</strong> <?= htmlspecialchars($user['email'] ?? 'Not provided') ?></p>
                    <p><strong><i class="bi bi-person-badge"></i> Username:</strong> <?= htmlspecialchars($user['username'] ?? 'Not provided') ?></p>
                    <?php if (isset($user['department'])): ?>
                    <p><strong><i class="bi bi-building"></i> Department:</strong> <?= htmlspecialchars($user['department'] ?? 'Not assigned') ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Skills -->
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Skills</h5>
                    <a href="/profile/skills" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($user_skills)): ?>
                        <p class="text-muted">No skills added yet. <a href="/profile/skills">Add your skills</a></p>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach ($user_skills as $skill): ?>
                                <span class="badge bg-primary"><?= htmlspecialchars($skill['name']) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Stats and Activity -->
        <div class="col-md-8">
            <!-- Projects & Tasks Overview -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Projects Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6 class="fw-bold">Total Projects: <?= $projects_count['total'] ?? 0 ?></h6>
                            </div>
                            <div class="progress-stacked mb-2">
                                <?php
                                $total = max((int)($projects_count['total'] ?? 0), 1);
                                $inProgressPercent = ((int)($projects_count['in_progress'] ?? 0) / $total) * 100;
                                $planningPercent = ((int)($projects_count['planning'] ?? 0) / $total) * 100;
                                $completedPercent = ((int)($projects_count['completed'] ?? 0) / $total) * 100;
                                $onHoldPercent = ((int)($projects_count['on_hold'] ?? 0) / $total) * 100;
                                ?>
                                <div class="progress" role="progressbar" style="width: <?= $inProgressPercent ?>%">
                                    <div class="progress-bar bg-primary">In Progress</div>
                                </div>
                                <div class="progress" role="progressbar" style="width: <?= $planningPercent ?>%">
                                    <div class="progress-bar bg-info">Planning</div>
                                </div>
                                <div class="progress" role="progressbar" style="width: <?= $completedPercent ?>%">
                                    <div class="progress-bar bg-success">Completed</div>
                                </div>
                                <div class="progress" role="progressbar" style="width: <?= $onHoldPercent ?>%">
                                    <div class="progress-bar bg-warning">On Hold</div>
                                </div>
                            </div>
                            <div class="row text-center mt-3">
                                <div class="col-3">
                                    <div class="bg-primary p-2 rounded text-white"><?= $projects_count['in_progress'] ?? 0 ?></div>
                                    <small>In Progress</small>
                                </div>
                                <div class="col-3">
                                    <div class="bg-info p-2 rounded text-white"><?= $projects_count['planning'] ?? 0 ?></div>
                                    <small>Planning</small>
                                </div>
                                <div class="col-3">
                                    <div class="bg-success p-2 rounded text-white"><?= $projects_count['completed'] ?? 0 ?></div>
                                    <small>Completed</small>
                                </div>
                                <div class="col-3">
                                    <div class="bg-warning p-2 rounded text-white"><?= $projects_count['on_hold'] ?? 0 ?></div>
                                    <small>On Hold</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Tasks Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6 class="fw-bold">Total Tasks: <?= $tasks_stats['total'] ?? 0 ?></h6>
                                <?php if (isset($tasks_stats['overdue']) && $tasks_stats['overdue'] > 0): ?>
                                <div class="text-danger">
                                    <i class="bi bi-exclamation-triangle-fill"></i> <?= $tasks_stats['overdue'] ?> overdue tasks
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="progress-stacked mb-2">
                                <?php
                                $total = max((int)($tasks_stats['total'] ?? 0), 1);
                                $notStartedPercent = ((int)($tasks_stats['not_started'] ?? 0) / $total) * 100;
                                $inProgressPercent = ((int)($tasks_stats['in_progress'] ?? 0) / $total) * 100;
                                $completedPercent = ((int)($tasks_stats['completed'] ?? 0) / $total) * 100;
                                $blockedPercent = ((int)($tasks_stats['blocked'] ?? 0) / $total) * 100;
                                ?>
                                <div class="progress" role="progressbar" style="width: <?= $notStartedPercent ?>%">
                                    <div class="progress-bar bg-secondary">Not Started</div>
                                </div>
                                <div class="progress" role="progressbar" style="width: <?= $inProgressPercent ?>%">
                                    <div class="progress-bar bg-primary">In Progress</div>
                                </div>
                                <div class="progress" role="progressbar" style="width: <?= $completedPercent ?>%">
                                    <div class="progress-bar bg-success">Completed</div>
                                </div>
                                <div class="progress" role="progressbar" style="width: <?= $blockedPercent ?>%">
                                    <div class="progress-bar bg-danger">Blocked</div>
                                </div>
                            </div>
                            <div class="row text-center mt-3">
                                <div class="col-3">
                                    <div class="bg-secondary p-2 rounded text-white"><?= $tasks_stats['not_started'] ?? 0 ?></div>
                                    <small>Not Started</small>
                                </div>
                                <div class="col-3">
                                    <div class="bg-primary p-2 rounded text-white"><?= $tasks_stats['in_progress'] ?? 0 ?></div>
                                    <small>In Progress</small>
                                </div>
                                <div class="col-3">
                                    <div class="bg-success p-2 rounded text-white"><?= $tasks_stats['completed'] ?? 0 ?></div>
                                    <small>Completed</small>
                                </div>
                                <div class="col-3">
                                    <div class="bg-danger p-2 rounded text-white"><?= $tasks_stats['blocked'] ?? 0 ?></div>
                                    <small>Blocked</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- About -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">About</h5>
                    <a href="/profile/edit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($user['bio'])): ?>
                        <p class="text-muted">No bio provided. <a href="/profile/edit">Add your bio</a></p>
                    <?php else: ?>
                        <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_activity)): ?>
                        <p class="text-muted">No recent activity</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($recent_activity as $activity): ?>
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <?php if ($activity['type'] === 'project'): ?>
                                                <i class="bi bi-folder text-primary me-2"></i>
                                            <?php else: ?>
                                                <i class="bi bi-check2-square text-success me-2"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($activity['title']) ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?= date('M j, Y g:i A', strtotime($activity['activity_date'])) ?>
                                        </small>
                                    </div>
                                    <p class="mb-1">
                                        You <?= $activity['action'] ?> a <?= $activity['type'] ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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