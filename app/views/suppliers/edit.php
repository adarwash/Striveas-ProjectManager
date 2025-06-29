<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/suppliers">Suppliers</a></li>
        <li class="breadcrumb-item active">Edit Supplier</li>
    </ol>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-edit me-1"></i>
                    Edit Supplier Information
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <form action="<?= $formAction ?>" method="POST">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($supplier['id']) ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?= isset($_SESSION['form_data']['name']) ? htmlspecialchars($_SESSION['form_data']['name']) : htmlspecialchars($supplier['name']) ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="contact_name" class="form-label">Contact Person</label>
                                <input type="text" class="form-control" id="contact_name" name="contact_name"
                                       value="<?= isset($_SESSION['form_data']['contact_name']) ? htmlspecialchars($_SESSION['form_data']['contact_name']) : htmlspecialchars($supplier['contact_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : htmlspecialchars($supplier['email'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                       value="<?= isset($_SESSION['form_data']['phone']) ? htmlspecialchars($_SESSION['form_data']['phone']) : htmlspecialchars($supplier['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="website" class="form-label">Website</label>
                                <input type="url" class="form-control" id="website" name="website" 
                                       placeholder="https://example.com"
                                       value="<?= isset($_SESSION['form_data']['website']) ? htmlspecialchars($_SESSION['form_data']['website']) : htmlspecialchars($supplier['website'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address"
                                       value="<?= isset($_SESSION['form_data']['address']) ? htmlspecialchars($_SESSION['form_data']['address']) : htmlspecialchars($supplier['address'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city"
                                       value="<?= isset($_SESSION['form_data']['city']) ? htmlspecialchars($_SESSION['form_data']['city']) : htmlspecialchars($supplier['city'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state" name="state"
                                       value="<?= isset($_SESSION['form_data']['state']) ? htmlspecialchars($_SESSION['form_data']['state']) : htmlspecialchars($supplier['state'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code"
                                       value="<?= isset($_SESSION['form_data']['postal_code']) ? htmlspecialchars($_SESSION['form_data']['postal_code']) : htmlspecialchars($supplier['postal_code'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country"
                                       value="<?= isset($_SESSION['form_data']['country']) ? htmlspecialchars($_SESSION['form_data']['country']) : htmlspecialchars($supplier['country'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <?php 
                                    $currentStatus = isset($_SESSION['form_data']['status']) ? $_SESSION['form_data']['status'] : $supplier['status'];
                                    ?>
                                    <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"><?= isset($_SESSION['form_data']['notes']) ? htmlspecialchars($_SESSION['form_data']['notes']) : htmlspecialchars($supplier['notes'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="/suppliers" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Supplier</button>
                        </div>
                    </form>
                    
                    <?php 
                    // Clear form data after displaying
                    if (isset($_SESSION['form_data'])) {
                        unset($_SESSION['form_data']);
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Supplier Details
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Supplier Information</h5>
                        <span class="badge <?= $supplier['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>">
                            <?= ucfirst(htmlspecialchars($supplier['status'])) ?>
                        </span>
                    </div>
                    
                    <dl class="row mb-3">
                        <dt class="col-sm-5">Created by:</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars($supplier['created_by_name'] ?? 'Unknown') ?></dd>
                        
                        <dt class="col-sm-5">Created on:</dt>
                        <dd class="col-sm-7"><?= date('M d, Y', strtotime($supplier['created_at'])) ?></dd>
                    </dl>
                    
                    <div class="d-grid mt-3">
                        <a href="/suppliers/viewDetail/<?= $supplier['id'] ?>" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-1"></i> View Supplier Details
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Danger Zone
                </div>
                <div class="card-body">
                    <p>Be careful with these actions:</p>
                    
                    <button type="button" class="btn btn-outline-danger w-100" 
                           data-bs-toggle="modal" data-bs-target="#deleteModal" 
                           data-id="<?= $supplier['id'] ?>" 
                           data-name="<?= htmlspecialchars($supplier['name']) ?>">
                        <i class="fas fa-trash me-1"></i> Delete Supplier
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this supplier? This action cannot be undone.
                
                <p class="text-danger mt-2">
                    <i class="fas fa-exclamation-triangle me-1"></i> 
                    If this supplier has associated invoices, it will be marked as inactive instead of being deleted.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="/suppliers/delete">
                    <input type="hidden" name="id" value="<?= $supplier['id'] ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div> 