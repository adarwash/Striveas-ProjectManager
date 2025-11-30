<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Manage Role Permissions</h1>
            <p class="text-muted">Configure permissions for <strong><?= $role['display_name'] ?></strong></p>
        </div>
        <div>
            <a href="<?= URLROOT ?>/permissions/roles" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-2"></i>Back to Roles
            </a>
        </div>
    </div>
    
    <?php flash('permissions_message'); ?>
    
    <!-- Role Info Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary-light rounded-circle p-3 me-3">
                                    <i class="bi bi-shield-check text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1"><?= $role['display_name'] ?></h5>
                                    <p class="text-muted mb-0"><?= $role['description'] ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex justify-content-end gap-3">
                                <div class="text-center">
                                    <div class="fs-4 fw-bold text-primary"><?= count($rolePermissionIds) ?></div>
                                    <div class="small text-muted">Permissions</div>
                                </div>
                                <div class="text-center">
                                    <div class="fs-4 fw-bold text-success"><?= count($allPermissions) ?></div>
                                    <div class="small text-muted">Available</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Permissions Form -->
    <form action="<?= URLROOT ?>/permissions/role_permissions/<?= $role['id'] ?>" method="POST">
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Available Permissions</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">
                                <i class="bi bi-check-all me-1"></i>Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">
                                <i class="bi bi-x-square me-1"></i>Deselect All
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php foreach($allPermissions as $module => $permissions): ?>
                        <div class="permission-module mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <h6 class="mb-0 text-capitalize fw-bold"><?= str_replace('_', ' ', $module) ?> Permissions</h6>
                                <div class="ms-auto">
                                    <button type="button" class="btn btn-sm btn-outline-primary module-select-all" 
                                            data-module="<?= $module ?>">
                                        <i class="bi bi-check-all me-1"></i>Select All
                                    </button>
                                </div>
                            </div>
                            
                            <div class="row">
                                <?php foreach($permissions as $permission): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" 
                                               type="checkbox" 
                                               name="permissions[]" 
                                               value="<?= $permission['id'] ?>"
                                               id="permission_<?= $permission['id'] ?>"
                                               data-module="<?= $module ?>"
                                               <?= in_array($permission['id'], $rolePermissionIds) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="permission_<?= $permission['id'] ?>">
                                            <div class="fw-semibold"><?= $permission['display_name'] ?></div>
                                            <div class="small text-muted"><?= $permission['description'] ?></div>
                                            <div class="small">
                                                <span class="badge bg-light text-dark"><?= $permission['action'] ?></span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <hr>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm position-sticky" style="top: 20px;">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Save Changes</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> Changes will take effect immediately for all users assigned to this role.
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Save Permissions
                            </button>
                            <a href="<?= URLROOT ?>/permissions/roles" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg me-2"></i>Cancel
                            </a>
                        </div>
                        
                        <hr>
                        
                        <div class="small text-muted">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Selected Permissions:</span>
                                <span class="fw-bold" id="selectedCount"><?= count($rolePermissionIds) ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Total Available:</span>
                                <span class="fw-bold"><?= array_sum(array_map('count', $allPermissions)) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Permission Summary -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0">Permission Summary</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach($allPermissions as $module => $permissions): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-capitalize small"><?= str_replace('_', ' ', $module) ?></span>
                            <div>
                                <span class="badge bg-primary module-count" data-module="<?= $module ?>">
                                    <?= count(array_intersect(array_column($permissions, 'id'), $rolePermissionIds)) ?>
                                </span>
                                <span class="text-muted small">/ <?= count($permissions) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Styles moved to /public/css/app.css -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    const selectedCountEl = document.getElementById('selectedCount');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    const moduleSelectAllBtns = document.querySelectorAll('.module-select-all');
    
    // Update counters
    function updateCounters() {
        const selectedCount = document.querySelectorAll('.permission-checkbox:checked').length;
        selectedCountEl.textContent = selectedCount;
        
        // Update module counters
        document.querySelectorAll('.module-count').forEach(counter => {
            const module = counter.getAttribute('data-module');
            const moduleCheckboxes = document.querySelectorAll(`[data-module="${module}"]:checked`);
            counter.textContent = moduleCheckboxes.length;
        });
    }
    
    // Select/Deselect all permissions
    selectAllBtn.addEventListener('click', function() {
        checkboxes.forEach(checkbox => checkbox.checked = true);
        updateCounters();
    });
    
    deselectAllBtn.addEventListener('click', function() {
        checkboxes.forEach(checkbox => checkbox.checked = false);
        updateCounters();
    });
    
    // Module select all buttons
    moduleSelectAllBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const module = this.getAttribute('data-module');
            const moduleCheckboxes = document.querySelectorAll(`[data-module="${module}"]`);
            const allChecked = Array.from(moduleCheckboxes).every(cb => cb.checked);
            
            moduleCheckboxes.forEach(checkbox => {
                checkbox.checked = !allChecked;
            });
            
            this.innerHTML = allChecked ? 
                '<i class="bi bi-check-all me-1"></i>Select All' : 
                '<i class="bi bi-x-square me-1"></i>Deselect All';
            
            updateCounters();
        });
    });
    
    // Update counters when individual checkboxes change
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateCounters);
    });
    
    // Initial counter update
    updateCounters();
});
</script> 