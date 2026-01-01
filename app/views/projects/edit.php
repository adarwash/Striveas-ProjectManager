<?php
// Set title for the page
$title = 'Edit Project - ' . DEFAULT_TITLE;
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Project</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/projects">Projects</a></li>
                <li class="breadcrumb-item"><a href="/projects/viewProject/<?= $project->id ?>">Project Details</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 col-md-12 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Project Details</h5>
            </div>
            <div class="card-body">
                <form action="/projects/update/<?= $project->id ?>" method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label">Project Title</label>
                        <input type="text" class="form-control <?= isset($project->title_err) ? 'is-invalid' : '' ?>" 
                               id="title" name="title" value="<?= htmlspecialchars($project->title) ?>" required>
                        <div class="invalid-feedback">
                            <?= $project->title_err ?? '' ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control <?= isset($project->description_err) ? 'is-invalid' : '' ?>" 
                                  id="description" name="description" rows="4"><?= htmlspecialchars($project->description) ?></textarea>
                        <div class="invalid-feedback">
                            <?= $project->description_err ?? '' ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control <?= isset($project->start_date_err) ? 'is-invalid' : '' ?>" 
                                   id="start_date" name="start_date" value="<?= htmlspecialchars($project->start_date) ?>" required>
                            <div class="invalid-feedback">
                                <?= $project->start_date_err ?? '' ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control <?= isset($project->end_date_err) ? 'is-invalid' : '' ?>" 
                                   id="end_date" name="end_date" value="<?= htmlspecialchars($project->end_date) ?>">
                            <div class="invalid-feedback">
                                <?= $project->end_date_err ?? '' ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-select <?= !empty($project->department_id_err ?? '') ? 'is-invalid' : '' ?>" 
                                    id="department_id" name="department_id">
                                <option value="" <?= empty($project->department_id ?? '') ? 'selected' : '' ?>>None</option>
                                <?php
                                // Use departments data passed from controller
                                if (isset($departments) && !empty($departments)):
                                    foreach ($departments as $department): ?>
                                        <option value="<?= $department->id ?>" <?= ($project->department_id == $department->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($department->name) ?> - Budget: <?= $currency['symbol'] ?><?= number_format($department->budget, 2) ?>
                                        </option>
                                <?php 
                                    endforeach;
                                endif; 
                                ?>
                            </select>
                            <div class="invalid-feedback">
                                <?= $project->department_id_err ?? '' ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="budget" class="form-label">Project Budget</label>
                            <div class="input-group">
                                <span class="input-group-text"><?= $currency['symbol'] ?></span>
                                <input type="text" class="form-control <?= !empty($project->budget_err ?? '') ? 'is-invalid' : '' ?>" 
                                       id="budget" name="budget" value="<?= number_format($project->budget, 2) ?>">
                                <div class="invalid-feedback">
                                    <?= $project->budget_err ?? '' ?>
                                </div>
                            </div>
                            <?php if (isset($project->department_budget) && $project->department_budget > 0): ?>
                                <small class="text-muted">Department Budget: <?= $currency['symbol'] ?><?= number_format($project->department_budget, 2) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select <?= isset($project->status_err) ? 'is-invalid' : '' ?>" 
                                id="status" name="status" required>
                            <option value="Active" <?= ($project->status === 'Active') ? 'selected' : '' ?>>Active</option>
                            <option value="On Hold" <?= ($project->status === 'On Hold') ? 'selected' : '' ?>>On Hold</option>
                            <option value="Completed" <?= ($project->status === 'Completed') ? 'selected' : '' ?>>Completed</option>
                            <option value="Cancelled" <?= ($project->status === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <div class="invalid-feedback">
                            <?= $project->status_err ?? '' ?>
                        </div>
                    </div>

                    <!-- Client Selection -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">Link to Client (Optional)</h5>
                        <div class="mb-3">
                            <label for="client_id" class="form-label">Select Client</label>
                            <select class="form-select" id="client_id" name="client_id">
                                <option value="" <?= empty($project->client_id ?? '') ? 'selected' : '' ?>>None</option>
                                <?php if (isset($clients) && !empty($clients)): ?>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client['id'] ?>" <?= (!empty($project->client_id) && (int)$project->client_id === (int)$client['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($client['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/projects/viewProject/<?= $project->id ?>" class="btn btn-light me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Project</button>
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