<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/employees">Employee Management</a></li>
        <li class="breadcrumb-item"><a href="/employees/viewEmployee/<?= $employee['user_id'] ?>"><?= $employee['full_name'] ?? $employee['username'] ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Record</li>
    </ol>
</nav>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0">Edit Employee Record</h1>
        <p class="text-muted">Update performance data for <?= $employee['full_name'] ?? $employee['username'] ?></p>
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
                <form action="/employees/edit/<?= $employee['user_id'] ?>" method="post">
                    <div class="mb-4">
                        <label for="performance_rating" class="form-label">Performance Rating</label>
                        <div class="d-flex align-items-center">
                            <input type="range" class="form-range me-3" min="0" max="5" step="0.25" id="performance_rating" name="performance_rating" value="<?= $employee['performance_rating'] ?>">
                            <span id="ratingValue" class="ms-2 badge bg-primary"><?= $employee['performance_rating'] ?></span>
                        </div>
                        <div class="rating-stars mt-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= floor($employee['performance_rating'])): ?>
                                    <i class="bi bi-star-fill text-warning" data-rating="<?= $i ?>"></i>
                                <?php elseif ($i - 0.5 <= $employee['performance_rating']): ?>
                                    <i class="bi bi-star-half text-warning" data-rating="<?= $i ?>"></i>
                                <?php else: ?>
                                    <i class="bi bi-star text-warning" data-rating="<?= $i ?>"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <div class="form-text">Rate the employee's overall performance from 0 to 5</div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="tasks_completed" class="form-label">Tasks Completed</label>
                            <input type="number" class="form-control" id="tasks_completed" name="tasks_completed" value="<?= $employee['tasks_completed'] ?>" min="0">
                        </div>
                        <div class="col-md-6">
                            <label for="tasks_pending" class="form-label">Tasks Pending</label>
                            <input type="number" class="form-control" id="tasks_pending" name="tasks_pending" value="<?= $employee['tasks_pending'] ?>" min="0">
                        </div>
                        <div class="col-12 mt-2">
                            <div class="form-text">You can also <a href="/employees/updateTasks/<?= $employee['user_id'] ?>">update these automatically</a> based on actual task data.</div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="last_review_date" class="form-label">Last Review Date</label>
                            <input type="date" class="form-control" id="last_review_date" name="last_review_date" value="<?= $employee['last_review_date'] ? date('Y-m-d', strtotime($employee['last_review_date'])) : '' ?>">
                            <div class="form-text">When was the employee last reviewed</div>
                        </div>
                        <div class="col-md-6">
                            <label for="next_review_date" class="form-label">Next Review Date</label>
                            <input type="date" class="form-control" id="next_review_date" name="next_review_date" value="<?= $employee['next_review_date'] ? date('Y-m-d', strtotime($employee['next_review_date'])) : '' ?>">
                            <div class="form-text">When should the employee be reviewed next</div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="notes" class="form-label">Performance Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Enter notes about the employee's performance"><?= $employee['notes'] ?></textarea>
                        <div class="form-text">Add any additional notes about the employee's performance</div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="/employees/viewEmployee/<?= $employee['user_id'] ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Changes
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
                    <div class="avatar-circle me-3" style="width: 60px; height: 60px; font-size: 1.5rem; background-color: #6366f1; color: white;">
                        <?= substr($employee['full_name'] ?? $employee['username'], 0, 1) ?>
                    </div>
                    <div>
                        <h5 class="mb-1"><?= $employee['full_name'] ?? $employee['username'] ?></h5>
                        <p class="text-muted mb-0"><?= $employee['email'] ?></p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <span class="badge bg-<?= $employee['role'] === 'admin' ? 'danger' : ($employee['role'] === 'manager' ? 'warning' : 'info') ?>">
                        <?= ucfirst($employee['role']) ?>
                    </span>
                </div>
                
                <?php if ($employee['last_absence_start']): ?>
                <div class="alert alert-light mt-3">
                    <p class="mb-0"><strong>Last Absence:</strong> From <?= date('M d, Y', strtotime($employee['last_absence_start'])) ?> to <?= date('M d, Y', strtotime($employee['last_absence_end'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0">Rating Guide</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rating-stars me-2">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                        <strong>4.5 - 5.0</strong>
                    </div>
                    <p class="text-muted small">Exceptional performance. Consistently exceeds expectations in all areas.</p>
                </div>
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rating-stars me-2">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star text-warning"></i>
                        </div>
                        <strong>3.5 - 4.4</strong>
                    </div>
                    <p class="text-muted small">Strong performance. Exceeds expectations in most areas.</p>
                </div>
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rating-stars me-2">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star text-warning"></i>
                            <i class="bi bi-star text-warning"></i>
                        </div>
                        <strong>2.5 - 3.4</strong>
                    </div>
                    <p class="text-muted small">Meets expectations. Solid, reliable performance.</p>
                </div>
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="rating-stars me-2">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star text-warning"></i>
                            <i class="bi bi-star text-warning"></i>
                            <i class="bi bi-star text-warning"></i>
                        </div>
                        <strong>1.5 - 2.4</strong>
                    </div>
                    <p class="text-muted small">Needs improvement. Meets some but not all expectations.</p>
                </div>
                <div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="rating-stars me-2">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star text-warning"></i>
                            <i class="bi bi-star text-warning"></i>
                            <i class="bi bi-star text-warning"></i>
                            <i class="bi bi-star text-warning"></i>
                        </div>
                        <strong>0 - 1.4</strong>
                    </div>
                    <p class="text-muted small">Poor performance. Fails to meet expectations in most areas.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.rating-stars {
    font-size: 1.25rem;
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ratingSlider = document.getElementById('performance_rating');
    const ratingValue = document.getElementById('ratingValue');
    const stars = document.querySelectorAll('.rating-stars .bi');
    
    // Update rating display and stars when slider changes
    ratingSlider.addEventListener('input', function() {
        const value = parseFloat(this.value);
        ratingValue.textContent = value.toFixed(2);
        updateStars(value);
    });
    
    // Update stars based on rating value
    function updateStars(rating) {
        stars.forEach((star, index) => {
            const position = index + 1;
            
            // Full star
            if (position <= Math.floor(rating)) {
                star.classList.remove('bi-star');
                star.classList.remove('bi-star-half');
                star.classList.add('bi-star-fill');
            } 
            // Half star
            else if (position - 0.5 <= rating) {
                star.classList.remove('bi-star');
                star.classList.remove('bi-star-fill');
                star.classList.add('bi-star-half');
            } 
            // Empty star
            else {
                star.classList.remove('bi-star-fill');
                star.classList.remove('bi-star-half');
                star.classList.add('bi-star');
            }
        });
    }
    
    // Initialize stars
    updateStars(parseFloat(ratingSlider.value));
    
    // Allow clicking on stars to set rating
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseFloat(this.getAttribute('data-rating'));
            ratingSlider.value = rating;
            ratingValue.textContent = rating.toFixed(2);
            updateStars(rating);
        });
    });
});
</script> 