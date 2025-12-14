<div class="container-fluid">
    <!-- Page Header -->
    <div class="bg-light rounded-3 p-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/clients" class="text-decoration-none">Clients</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create Client</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Create New Client</h1>
                <p class="text-muted">Add a new client to the system</p>
            </div>
            <a href="/clients" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Clients
            </a>
        </div>
    </div>
    
    <!-- Create Client Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Client Information</h5>
                </div>
                <div class="card-body">
                    <form action="/clients/create" method="post">
                        <div class="row">
                            <!-- Client Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Client Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= !empty($name_err) ? 'is-invalid' : '' ?>" 
                                       id="name" name="name" value="<?= $name ?>" required>
                                <?php if (!empty($name_err)): ?>
                                <div class="invalid-feedback"><?= $name_err ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Contact Person -->
                            <div class="col-md-6 mb-3">
                                <label for="contact_person" class="form-label">Contact Person</label>
                                <input type="text" class="form-control" id="contact_person" 
                                       name="contact_person" value="<?= $contact_person ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control <?= !empty($email_err) ? 'is-invalid' : '' ?>" 
                                       id="email" name="email" value="<?= $email ?>">
                                <?php if (!empty($email_err)): ?>
                                <div class="invalid-feedback"><?= $email_err ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?= $phone ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Industry -->
                            <div class="col-md-6 mb-3">
                                <label for="industry" class="form-label">Industry</label>
                                <select class="form-select" id="industry" name="industry">
                                    <option value="">Select Industry</option>
                                    <option value="Technology" <?= $industry == 'Technology' ? 'selected' : '' ?>>Technology</option>
                                    <option value="Manufacturing" <?= $industry == 'Manufacturing' ? 'selected' : '' ?>>Manufacturing</option>
                                    <option value="Healthcare" <?= $industry == 'Healthcare' ? 'selected' : '' ?>>Healthcare</option>
                                    <option value="Finance" <?= $industry == 'Finance' ? 'selected' : '' ?>>Finance</option>
                                    <option value="Retail" <?= $industry == 'Retail' ? 'selected' : '' ?>>Retail</option>
                                    <option value="Education" <?= $industry == 'Education' ? 'selected' : '' ?>>Education</option>
                                    <option value="Construction" <?= $industry == 'Construction' ? 'selected' : '' ?>>Construction</option>
                                    <option value="Real Estate" <?= $industry == 'Real Estate' ? 'selected' : '' ?>>Real Estate</option>
                                    <option value="Transportation" <?= $industry == 'Transportation' ? 'selected' : '' ?>>Transportation</option>
                                    <option value="Other" <?= $industry == 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                            
                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="Active" <?= $status == 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Inactive" <?= $status == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                    <option value="Prospect" <?= $status == 'Prospect' ? 'selected' : '' ?>>Prospect</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Address -->
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?= $address ?></textarea>
                        </div>
                        
                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4" 
                                      placeholder="Any additional notes about this client..."><?= $notes ?></textarea>
                        </div>

                        <!-- Visibility Controls -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_restricted" name="is_restricted" value="1" <?= !empty($is_restricted) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_restricted">Restrict visibility to specific roles</label>
                            </div>
                            <small class="text-muted d-block mb-2">When restricted, only users in the selected roles will see this client and its related projects/tasks.</small>
                            <label for="allowed_roles" class="form-label">Allowed Roles</label>
                            <select id="allowed_roles" name="allowed_roles[]" class="form-select" multiple size="6">
                                <?php if (!empty($roles)): ?>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= (int)$role['id'] ?>" <?= in_array((int)$role['id'], $allowed_roles_selected ?? []) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($role['display_name'] ?? $role['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No roles available</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/clients" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Create Client
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Help Panel -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-info-circle text-primary me-2"></i>
                        Client Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-semibold">Required Fields</h6>
                        <ul class="small text-muted mb-0">
                            <li>Client Name is required</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="fw-semibold">Best Practices</h6>
                        <ul class="small text-muted mb-0">
                            <li>Use the official company name</li>
                            <li>Include a primary contact person</li>
                            <li>Provide accurate contact information</li>
                            <li>Select the appropriate industry</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h6 class="fw-semibold">Next Steps</h6>
                        <p class="small text-muted mb-2">After creating the client, you can:</p>
                        <ul class="small text-muted mb-0">
                            <li>Assign sites to the client</li>
                            <li>View client details and history</li>
                            <li>Update client information</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 