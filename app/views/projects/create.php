<?php
// Set title for the page
$title = 'Create Project - HiveITPortal';
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
                                        <option value="<?= $department->id ?>" data-budget="<?= $department->budget ?>">
                                            <?= htmlspecialchars($department->name) ?> - Budget: <?= $currency['symbol'] ?><?= number_format($department->budget, 2) ?>
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
                                <span class="input-group-text"><?= $currency['symbol'] ?></span>
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
                    
                    <!-- Site Selection -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">Link to Site (Optional)</h5>
                        <div class="mb-3">
                            <label for="site_id" class="form-label">Select Site</label>
                            <select class="form-select" id="site_id" name="site_id">
                                <option value="">None - Link site later</option>
                                <?php
                                // Use sites data passed from controller
                                if (isset($sites) && !empty($sites)):
                                    foreach ($sites as $site): ?>
                                        <option value="<?= $site['id'] ?>" <?= (isset($selected_site_id) && $selected_site_id == $site['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($site['name']) ?> (<?= htmlspecialchars($site['location']) ?>)
                                        </option>
                                <?php 
                                    endforeach;
                                endif; 
                                ?>
                            </select>
                            <small class="text-muted">You can link more sites after creating the project</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="site_notes" class="form-label">Site Link Notes (Optional)</label>
                            <textarea class="form-control" id="site_notes" name="site_notes" rows="2" 
                                      placeholder="Add notes about why this project is linked to this site"></textarea>
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