<div class="row">
    <div class="col-md-12 mb-4">
        <h1>Edit Department</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/departments">Departments</a></li>
                <li class="breadcrumb-item"><a href="/departments/show/<?= $department->id ?>">Department Details</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="/departments/update/<?= $department->id ?>" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Department Name</label>
                        <input type="text" class="form-control <?= isset($department->name_err) ? 'is-invalid' : '' ?>" 
                               id="name" name="name" value="<?= htmlspecialchars($department->name) ?>" required>
                        <div class="invalid-feedback">
                            <?= $department->name_err ?? '' ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control <?= isset($department->description_err) ? 'is-invalid' : '' ?>" 
                                  id="description" name="description" rows="4"><?= htmlspecialchars($department->description) ?></textarea>
                        <div class="invalid-feedback">
                            <?= $department->description_err ?? '' ?>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="budget" class="form-label">Department Budget</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="text" class="form-control <?= isset($department->budget_err) ? 'is-invalid' : '' ?>" 
                                   id="budget" name="budget" value="<?= number_format($department->budget, 2) ?>" required>
                            <div class="invalid-feedback">
                                <?= $department->budget_err ?? '' ?>
                            </div>
                        </div>
                        <?php if (isset($department->used_budget) && $department->used_budget > 0): ?>
                            <div class="mt-2">
                                <small class="text-muted">Current allocation: $<?= number_format($department->used_budget ?? 0, 2) ?> 
                                    (<?= number_format((($department->used_budget ?? 0) / $department->budget) * 100, 1) ?>% used)</small>
                                
                                <?php 
                                // Calculate budget percentage used
                                $budgetPercentage = (($department->used_budget ?? 0) / $department->budget) * 100;
                                
                                // Determine appropriate color based on percentage
                                $progressClass = 'bg-success';
                                if ($budgetPercentage > 70) {
                                    $progressClass = 'bg-warning';
                                }
                                if ($budgetPercentage > 90) {
                                    $progressClass = 'bg-danger';
                                }
                                ?>
                                
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar <?= $progressClass ?>" role="progressbar" 
                                         style="width: <?= min($budgetPercentage, 100) ?>%" 
                                         aria-valuenow="<?= $budgetPercentage ?>" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                
                                <?php if ($department->budget < $department->used_budget): ?>
                                    <div class="text-danger small mt-1">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Budget is less than current project allocations
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/departments/show/<?= $department->id ?>" class="btn btn-light me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Format currency input
    document.addEventListener('DOMContentLoaded', function() {
        const budgetInput = document.getElementById('budget');
        
        // Format on blur
        budgetInput.addEventListener('blur', function(e) {
            let value = e.target.value.replace(/[^\d.]/g, '');
            if (value) {
                e.target.value = parseFloat(value).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        });
        
        // Clear formatting on focus
        budgetInput.addEventListener('focus', function(e) {
            e.target.value = e.target.value.replace(/[^\d.]/g, '');
        });
    });
</script> 