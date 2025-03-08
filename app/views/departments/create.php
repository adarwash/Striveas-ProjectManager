<div class="row">
    <div class="col-md-12 mb-4">
        <h1>Create Department</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/departments">Departments</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="/departments/store" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Department Name</label>
                        <input type="text" class="form-control <?= isset($data['name_err']) && !empty($data['name_err']) ? 'is-invalid' : '' ?>" 
                               id="name" name="name" value="<?= htmlspecialchars($data['name'] ?? '') ?>" required>
                        <div class="invalid-feedback">
                            <?= $data['name_err'] ?? '' ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control <?= isset($data['description_err']) && !empty($data['description_err']) ? 'is-invalid' : '' ?>" 
                                  id="description" name="description" rows="4"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                        <div class="invalid-feedback">
                            <?= $data['description_err'] ?? '' ?>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <label for="budget" class="form-label">Department Budget</label>
                            <div class="input-group">
                                <select class="form-select" id="currency" name="currency" style="max-width: 6rem;">
                                    <?php foreach ($data['currencies'] as $code => $symbol): ?>
                                        <option value="<?= $code ?>" <?= ($data['currency'] ?? 'USD') == $code ? 'selected' : '' ?>>
                                            <?= $symbol ?> <?= $code ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" class="form-control <?= isset($data['budget_err']) && !empty($data['budget_err']) ? 'is-invalid' : '' ?>" 
                                       id="budget" name="budget" value="<?= $data['budget'] ? number_format($data['budget'], 2) : '' ?>" required>
                                <div class="invalid-feedback">
                                    <?= $data['budget_err'] ?? '' ?>
                                </div>
                            </div>
                            <small class="text-muted">Enter the total budget allocation for this department</small>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/departments" class="btn btn-light me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Department</button>
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
        const currencySelect = document.getElementById('currency');
        
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