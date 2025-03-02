<?php
// Set title for the page
$title = 'Create Project - ProjectTracker';
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Create New Project</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/projects">Projects</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 col-md-12 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Project Details</h5>
            </div>
            <div class="card-body">
                <form action="/projects/store" method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label">Project Title</label>
                        <input type="text" class="form-control <?= isset($data['title_err']) ? 'is-invalid' : '' ?>" 
                               id="title" name="title" value="<?= $data['title'] ?? '' ?>" required>
                        <div class="invalid-feedback">
                            <?= $data['title_err'] ?? '' ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control <?= isset($data['description_err']) ? 'is-invalid' : '' ?>" 
                                  id="description" name="description" rows="4"><?= $data['description'] ?? '' ?></textarea>
                        <div class="invalid-feedback">
                            <?= $data['description_err'] ?? '' ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control <?= isset($data['start_date_err']) ? 'is-invalid' : '' ?>" 
                                   id="start_date" name="start_date" value="<?= $data['start_date'] ?? date('Y-m-d') ?>" required>
                            <div class="invalid-feedback">
                                <?= $data['start_date_err'] ?? '' ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control <?= isset($data['end_date_err']) ? 'is-invalid' : '' ?>" 
                                   id="end_date" name="end_date" value="<?= $data['end_date'] ?? date('Y-m-d', strtotime('+30 days')) ?>">
                            <div class="invalid-feedback">
                                <?= $data['end_date_err'] ?? '' ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-select <?= isset($data['department_id_err']) ? 'is-invalid' : '' ?>" 
                                    id="department_id" name="department_id" required>
                                <option value="">Select Department</option>
                                <?php
                                // Use departments data passed from controller
                                if (isset($departments) && !empty($departments)):
                                    foreach ($departments as $department): ?>
                                        <option value="<?= $department->id ?>" <?= (isset($data['department_id']) && $data['department_id'] == $department->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($department->name) ?> - Budget: $<?= number_format($department->budget, 2) ?>
                                        </option>
                                <?php 
                                    endforeach;
                                endif; 
                                ?>
                            </select>
                            <div class="invalid-feedback">
                                <?= $data['department_id_err'] ?? '' ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="budget" class="form-label">Project Budget</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control <?= isset($data['budget_err']) ? 'is-invalid' : '' ?>" 
                                       id="budget" name="budget" value="<?= $data['budget'] ?? '0.00' ?>" required>
                                <div class="invalid-feedback">
                                    <?= $data['budget_err'] ?? '' ?>
                                </div>
                            </div>
                            <small class="text-muted">Enter the estimated budget for this project</small>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select <?= isset($data['status_err']) ? 'is-invalid' : '' ?>" 
                                id="status" name="status" required>
                            <option value="Active" <?= (isset($data['status']) && $data['status'] === 'Active') ? 'selected' : '' ?>>Active</option>
                            <option value="On Hold" <?= (isset($data['status']) && $data['status'] === 'On Hold') ? 'selected' : '' ?>>On Hold</option>
                            <option value="Completed" <?= (isset($data['status']) && $data['status'] === 'Completed') ? 'selected' : '' ?>>Completed</option>
                            <option value="Cancelled" <?= (isset($data['status']) && $data['status'] === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <div class="invalid-feedback">
                            <?= $data['status_err'] ?? '' ?>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="/projects" class="btn btn-light me-md-2">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Create Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Add client-side validation
    document.addEventListener('DOMContentLoaded', function() {
        // Get form elements
        const form = document.querySelector('form');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        
        // Validate end date is after start date
        form.addEventListener('submit', function(event) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            
            if (endDate < startDate) {
                event.preventDefault();
                endDateInput.classList.add('is-invalid');
                endDateInput.nextElementSibling.textContent = 'End date cannot be before start date';
            }
        });
        
        // Clear validation on input change
        endDateInput.addEventListener('change', function() {
            endDateInput.classList.remove('is-invalid');
        });
    });
</script> 