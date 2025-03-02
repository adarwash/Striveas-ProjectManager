<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Edit Profile</h1>
                <a href="/profile" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Profile
                </a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <form action="/profile/edit" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" disabled>
                            <div class="form-text">Your username cannot be changed</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control <?= isset($full_name_err) && !empty($full_name_err) ? 'is-invalid' : '' ?>" 
                                   id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                            <?php if (isset($full_name_err) && !empty($full_name_err)): ?>
                                <div class="invalid-feedback"><?= $full_name_err ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control <?= isset($email_err) && !empty($email_err) ? 'is-invalid' : '' ?>" 
                                   id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                            <?php if (isset($email_err) && !empty($email_err)): ?>
                                <div class="invalid-feedback"><?= $email_err ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="position" class="form-label">Position/Job Title</label>
                            <input type="text" class="form-control <?= isset($position_err) && !empty($position_err) ? 'is-invalid' : '' ?>" 
                                   id="position" name="position" value="<?= htmlspecialchars($user['position'] ?? '') ?>">
                            <?php if (isset($position_err) && !empty($position_err)): ?>
                                <div class="invalid-feedback"><?= $position_err ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="5"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                            <div class="form-text">Tell us about yourself and your professional background</div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/profile" class="btn btn-outline-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 