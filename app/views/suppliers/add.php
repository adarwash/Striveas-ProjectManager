<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/suppliers">Suppliers</a></li>
        <li class="breadcrumb-item active">Add New Supplier</li>
    </ol>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-plus-circle me-1"></i>
                    Supplier Information
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
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?= isset($_SESSION['form_data']['name']) ? htmlspecialchars($_SESSION['form_data']['name']) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="contact_name" class="form-label">Contact Person</label>
                                <input type="text" class="form-control" id="contact_name" name="contact_name"
                                       value="<?= isset($_SESSION['form_data']['contact_name']) ? htmlspecialchars($_SESSION['form_data']['contact_name']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                       value="<?= isset($_SESSION['form_data']['phone']) ? htmlspecialchars($_SESSION['form_data']['phone']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="website" class="form-label">Website</label>
                                <input type="url" class="form-control" id="website" name="website" 
                                       placeholder="https://example.com"
                                       value="<?= isset($_SESSION['form_data']['website']) ? htmlspecialchars($_SESSION['form_data']['website']) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address"
                                       value="<?= isset($_SESSION['form_data']['address']) ? htmlspecialchars($_SESSION['form_data']['address']) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city"
                                       value="<?= isset($_SESSION['form_data']['city']) ? htmlspecialchars($_SESSION['form_data']['city']) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state" name="state"
                                       value="<?= isset($_SESSION['form_data']['state']) ? htmlspecialchars($_SESSION['form_data']['state']) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code"
                                       value="<?= isset($_SESSION['form_data']['postal_code']) ? htmlspecialchars($_SESSION['form_data']['postal_code']) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country"
                                       value="<?= isset($_SESSION['form_data']['country']) ? htmlspecialchars($_SESSION['form_data']['country']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?= (isset($_SESSION['form_data']['status']) && $_SESSION['form_data']['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= (isset($_SESSION['form_data']['status']) && $_SESSION['form_data']['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"><?= isset($_SESSION['form_data']['notes']) ? htmlspecialchars($_SESSION['form_data']['notes']) : '' ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="/suppliers" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Supplier</button>
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
                    Help Information
                </div>
                <div class="card-body">
                    <h5>Adding a New Supplier</h5>
                    <p>Use this form to add a new supplier to your system. Fields marked with <span class="text-danger">*</span> are required.</p>
                    
                    <h6>Quick Tips:</h6>
                    <ul>
                        <li>The supplier name is mandatory.</li>
                        <li>Adding contact information will make it easier to get in touch with your supplier.</li>
                        <li>Inactive suppliers won't appear in dropdown lists for new invoices.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div> 