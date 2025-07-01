<div class="container-fluid">
    <!-- Page Header -->
    <div class="bg-light rounded-3 p-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/clients" class="text-decoration-none">Clients</a></li>
                <li class="breadcrumb-item"><a href="/clients/viewClient/<?= $id ?>" class="text-decoration-none"><?= htmlspecialchars($name) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Edit Client</h1>
                <p class="text-muted">Update client information</p>
            </div>
            <div class="d-flex gap-2">
                <a href="/clients/viewClient/<?= $id ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-eye"></i> View Client
                </a>
                <a href="/clients" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Clients
                </a>
            </div>
        </div>
    </div>
    
    <!-- Edit Client Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Client Information</h5>
                </div>
                <div class="card-body">
                    <form action="/clients/edit/<?= $id ?>" method="post">
                        <div class="row">
                            <!-- Client Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Client Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= !empty($name_err) ? 'is-invalid' : '' ?>" 
                                       id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
                                <?php if (!empty($name_err)): ?>
                                <div class="invalid-feedback"><?= $name_err ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Contact Person -->
                            <div class="col-md-6 mb-3">
                                <label for="contact_person" class="form-label">Contact Person</label>
                                <input type="text" class="form-control" id="contact_person" 
                                       name="contact_person" value="<?= htmlspecialchars($contact_person) ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control <?= !empty($email_err) ? 'is-invalid' : '' ?>" 
                                       id="email" name="email" value="<?= htmlspecialchars($email) ?>">
                                <?php if (!empty($email_err)): ?>
                                <div class="invalid-feedback"><?= $email_err ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>">
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
                            <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($address) ?></textarea>
                        </div>
                        
                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4" 
                                      placeholder="Any additional notes about this client..."><?= htmlspecialchars($notes) ?></textarea>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/clients/viewClient/<?= $id ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Update Client
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
                        Update Information
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
                            <li>Keep contact information up to date</li>
                            <li>Update status when client relationship changes</li>
                            <li>Add notes for important updates</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h6 class="fw-semibold">Related Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="/clients/assignSites/<?= $id ?>" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-geo-alt me-1"></i> Manage Sites
                            </a>
                            <?php if (hasPermission('clients.delete')): ?>
                            <a href="/clients/delete/<?= $id ?>" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash me-1"></i> Delete Client
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 