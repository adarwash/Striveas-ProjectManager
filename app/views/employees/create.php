<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/employees">Employee Management</a></li>
        <li class="breadcrumb-item active" aria-current="page">Add Employee Record</li>
    </ol>
</nav>

<div class="mb-4">
    <h1 class="h3 mb-0">Add Employee Record</h1>
    <p class="text-muted">Create a new employee performance record</p>
</div>

<!-- Flash Messages -->
<?php flash('employee_error'); ?>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="/employees/create" method="post">
                    <div class="mb-4">
                        <label for="user_id" class="form-label">Select Employee</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">-- Select Employee --</option>
                            <?php foreach($users as $user): ?>
                                <option value="<?= $user['id'] ?>">
                                    <?= $user['full_name'] ?? $user['username'] ?> (<?= $user['email'] ?>) - <?= ucfirst($user['role']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Select the employee you want to create a performance record for</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="performance_rating" class="form-label">Performance Rating</label>
                        <div class="d-flex align-items-center">
                            <input type="range" class="form-range me-3" min="0" max="5" step="0.25" id="performance_rating" name="performance_rating" value="3.0">
                            <span id="ratingValue" class="ms-2 badge bg-primary">3.0</span>
                        </div>
                        <div class="rating-stars mt-2">
                            <i class="bi bi-star-fill text-warning" data-rating="1"></i>
                            <i class="bi bi-star-fill text-warning" data-rating="2"></i>
                            <i class="bi bi-star-fill text-warning" data-rating="3"></i>
                            <i class="bi bi-star text-warning" data-rating="4"></i>
                            <i class="bi bi-star text-warning" data-rating="5"></i>
                        </div>
                        <div class="form-text">Rate the employee's overall performance from 0 to 5</div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="last_review_date" class="form-label">Last Review Date</label>
                            <input type="date" class="form-control" id="last_review_date" name="last_review_date" value="<?= date('Y-m-d') ?>">
                            <div class="form-text">When was the employee last reviewed</div>
                        </div>
                        <div class="col-md-6">
                            <label for="next_review_date" class="form-label">Next Review Date</label>
                            <input type="date" class="form-control" id="next_review_date" name="next_review_date" value="<?= date('Y-m-d', strtotime('+6 months')) ?>">
                            <div class="form-text">When should the employee be reviewed next</div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="notes" class="form-label">Performance Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Enter notes about the employee's performance"></textarea>
                        <div class="form-text">Add any additional notes about the employee's performance</div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="/employees" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
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
        ratingValue.textContent = value.toFixed(1);
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
            ratingValue.textContent = rating.toFixed(1);
            updateStars(rating);
        });
    });
});
</script> 