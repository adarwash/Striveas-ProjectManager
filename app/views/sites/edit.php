<div class="container-fluid">
    <!-- Page Header with Background -->
    <div class="bg-light rounded-3 p-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/sites" class="text-decoration-none">Sites</a></li>
                <li class="breadcrumb-item"><a href="/sites/viewSite/<?= $data['id'] ?>" class="text-decoration-none"><?= $data['name'] ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Edit Site</h1>
                <p class="text-muted">Update information for <?= $data['name'] ?></p>
            </div>
            <a href="/sites/viewSite/<?= $data['id'] ?>" class="btn btn-light">
                <i class="bi bi-arrow-left me-1"></i> Back to Site
            </a>
        </div>
    </div>
    
    <!-- Form Card -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Site Information</h5>
                </div>
                <div class="card-body">
                    <form action="/sites/edit/<?= $data['id'] ?>" method="post" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Site Name <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-building"></i>
                                    </span>
                                    <input type="text" name="name" id="name" class="form-control <?= !empty($data['name_err']) ? 'is-invalid' : '' ?>" 
                                           value="<?= $data['name'] ?>" required>
                                    <?php if (!empty($data['name_err'])): ?>
                                    <div class="invalid-feedback"><?= $data['name_err'] ?></div>
                                    <?php else: ?>
                                    <div class="invalid-feedback">Site name is required</div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-text">Official name of the site location</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="site_code" class="form-label">Site Code</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-hash"></i>
                                    </span>
                                    <input type="text" name="site_code" id="site_code" class="form-control <?= !empty($data['site_code_err']) ? 'is-invalid' : '' ?>" 
                                           value="<?= $data['site_code'] ?>">
                                    <?php if (!empty($data['site_code_err'])): ?>
                                    <div class="invalid-feedback"><?= $data['site_code_err'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-text">Unique identifier code for this site</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-geo-alt"></i>
                                    </span>
                                    <input type="text" name="location" id="location" class="form-control <?= !empty($data['location_err']) ? 'is-invalid' : '' ?>" 
                                           value="<?= $data['location'] ?>">
                                    <?php if (!empty($data['location_err'])): ?>
                                    <div class="invalid-feedback"><?= $data['location_err'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-text">City, state, or general area</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Site Type</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-building-gear"></i>
                                    </span>
                                    <select name="type" id="type" class="form-select <?= !empty($data['type_err']) ? 'is-invalid' : '' ?>">
                                        <option value="" <?= empty($data['type']) ? 'selected' : '' ?>>Select Type</option>
                                        <option value="Headquarters" <?= $data['type'] === 'Headquarters' ? 'selected' : '' ?>>Headquarters</option>
                                        <option value="Branch Office" <?= $data['type'] === 'Branch Office' ? 'selected' : '' ?>>Branch Office</option>
                                        <option value="Manufacturing" <?= $data['type'] === 'Manufacturing' ? 'selected' : '' ?>>Manufacturing</option>
                                        <option value="Distribution" <?= $data['type'] === 'Distribution' ? 'selected' : '' ?>>Distribution</option>
                                        <option value="Retail" <?= $data['type'] === 'Retail' ? 'selected' : '' ?>>Retail</option>
                                        <option value="Remote" <?= $data['type'] === 'Remote' ? 'selected' : '' ?>>Remote</option>
                                        <option value="Other" <?= $data['type'] === 'Other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                    <?php if (!empty($data['type_err'])): ?>
                                    <div class="invalid-feedback"><?= $data['type_err'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-toggle-on"></i>
                                    </span>
                                    <select name="status" id="status" class="form-select <?= !empty($data['status_err']) ? 'is-invalid' : '' ?>">
                                        <option value="Active" <?= $data['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                        <option value="Inactive" <?= $data['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                        <option value="Under Construction" <?= $data['status'] === 'Under Construction' ? 'selected' : '' ?>>Under Construction</option>
                                        <option value="Closed" <?= $data['status'] === 'Closed' ? 'selected' : '' ?>>Closed</option>
                                    </select>
                                    <?php if (!empty($data['status_err'])): ?>
                                    <div class="invalid-feedback"><?= $data['status_err'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-pin-map"></i>
                                    </span>
                                    <textarea name="address" id="address" class="form-control <?= !empty($data['address_err']) ? 'is-invalid' : '' ?>" 
                                           rows="3"><?= $data['address'] ?></textarea>
                                    <?php if (!empty($data['address_err'])): ?>
                                    <div class="invalid-feedback"><?= $data['address_err'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-text">Full street address including city, state/province, and postal code</div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div class="form-text">Fields marked with <span class="text-danger">*</span> are required</div>
                            <div class="d-flex gap-2">
                                <a href="/sites/viewSite/<?= $data['id'] ?>" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-control:focus,
.form-select:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    border-color: #86b7fe;
}
.input-group-text {
    border-color: #ced4da;
}
.input-group > :not(:first-child):not(.dropdown-menu):not(.valid-tooltip):not(.valid-feedback):not(.invalid-tooltip):not(.invalid-feedback) {
    margin-left: -1px;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}
</style>

<script>
// Form validation
(function () {
    'use strict';
    
    // Fetch all forms that need validation
    var forms = document.querySelectorAll('.needs-validation');
    
    // Loop over them and prevent submission
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
})();
</script> 