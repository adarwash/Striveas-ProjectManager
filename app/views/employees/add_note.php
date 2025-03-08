<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/employees">Employee Management</a></li>
        <li class="breadcrumb-item"><a href="/employees/viewEmployee/<?= $employee['user_id'] ?>"><?= $employee['full_name'] ?? $employee['username'] ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Add Performance Note</li>
    </ol>
</nav>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0">Add Performance Note</h1>
        <p class="text-muted">Add a new performance note for <?= $employee['full_name'] ?? $employee['username'] ?></p>
    </div>
    <a href="/employees/viewEmployee/<?= $employee['user_id'] ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Profile
    </a>
</div>

<!-- Flash Messages -->
<?php flash('employee_error'); ?>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="/employees/addNote/<?= $employee['user_id'] ?>" method="post">
                    <div class="mb-4">
                        <label for="note_type" class="form-label">Note Type</label>
                        <select class="form-select" id="note_type" name="note_type">
                            <option value="achievement">Achievement</option>
                            <option value="improvement">Area for Improvement</option>
                            <option value="feedback">Feedback Received</option>
                            <option value="training">Training Recommendation</option>
                            <option value="general" selected>General Note</option>
                        </select>
                        <div class="form-text">Categorize this performance note for better organization</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="note_text" class="form-label">Note Content</label>
                        <textarea class="form-control" id="note_text" name="note_text" rows="6" placeholder="Enter detailed performance notes here..." required></textarea>
                        <div class="form-text">Include specific examples, metrics, or observations about the employee's performance</div>
                    </div>
                    
                    <div class="alert alert-info">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi bi-info-circle-fill fs-4"></i>
                            </div>
                            <div>
                                <p class="mb-1"><strong>Note:</strong> This performance note will be:</p>
                                <ul class="mb-0">
                                    <li>Visible to all managers and administrators</li>
                                    <li>Added to the employee's permanent record</li>
                                    <li>Timestamped and attributed to you</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="/employees/viewEmployee/<?= $employee['user_id'] ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Performance Note
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0">Employee Info</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-circle me-3" style="width: 40px; height: 40px; font-size: 1.2rem; background-color: #6366f1; color: white;">
                        <?= substr($employee['full_name'] ?? $employee['username'], 0, 1) ?>
                    </div>
                    <div>
                        <h6 class="mb-0"><?= $employee['full_name'] ?? $employee['username'] ?></h6>
                        <span class="text-muted small"><?= $employee['email'] ?></span>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <div class="text-muted small mb-1">Current Rating</div>
                    <div class="d-flex align-items-center">
                        <div class="rating-stars me-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= floor($employee['performance_rating'])): ?>
                                    <i class="bi bi-star-fill text-warning"></i>
                                <?php elseif ($i - 0.5 <= $employee['performance_rating']): ?>
                                    <i class="bi bi-star-half text-warning"></i>
                                <?php else: ?>
                                    <i class="bi bi-star text-warning"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <span class="fw-bold"><?= $employee['performance_rating'] ?>/5.0</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="text-muted small mb-1">Last Review</div>
                    <p class="mb-0">
                        <?php if ($employee['last_review_date']): ?>
                            <?= date('F d, Y', strtotime($employee['last_review_date'])) ?>
                        <?php else: ?>
                            <span class="text-muted">Not reviewed yet</span>
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="mb-0">
                    <div class="text-muted small mb-1">Role</div>
                    <p class="mb-0">
                        <span class="badge bg-<?= $employee['role'] === 'admin' ? 'danger' : ($employee['role'] === 'manager' ? 'warning' : 'info') ?>">
                            <?= ucfirst($employee['role']) ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0">Best Practices</h5>
            </div>
            <div class="card-body">
                <ul class="mb-0 ps-3">
                    <li class="mb-2">Be specific and objective in your observations</li>
                    <li class="mb-2">Include measurable achievements or areas for growth</li>
                    <li class="mb-2">Focus on behavior and results, not personality</li>
                    <li class="mb-2">Provide actionable feedback when possible</li>
                    <li class="mb-0">Reference specific projects or tasks when relevant</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: 600;
}

.rating-stars {
    font-size: 1.2rem;
}
</style> 