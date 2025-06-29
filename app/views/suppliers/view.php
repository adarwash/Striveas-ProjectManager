<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/suppliers">Suppliers</a></li>
        <li class="breadcrumb-item active">View Supplier</li>
    </ol>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-building me-1"></i>
                        Supplier Details
                    </div>
                    <div>
                        <a href="/suppliers/edit/<?= $supplier['id'] ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                        <button type="button" class="btn btn-danger btn-sm" 
                               data-bs-toggle="modal" data-bs-target="#deleteModal" 
                               data-id="<?= $supplier['id'] ?>" 
                               data-name="<?= htmlspecialchars($supplier['name']) ?>">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="mb-0"><?= htmlspecialchars($supplier['name']) ?></h3>
                                <span class="badge <?= $supplier['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= ucfirst(htmlspecialchars($supplier['status'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">Contact Information</h5>
                            <dl class="row">
                                <?php if (!empty($supplier['contact_name'])): ?>
                                <dt class="col-sm-4">Contact Person:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($supplier['contact_name']) ?></dd>
                                <?php endif; ?>
                                
                                <?php if (!empty($supplier['email'])): ?>
                                <dt class="col-sm-4">Email:</dt>
                                <dd class="col-sm-8">
                                    <a href="mailto:<?= htmlspecialchars($supplier['email']) ?>"><?= htmlspecialchars($supplier['email']) ?></a>
                                </dd>
                                <?php endif; ?>
                                
                                <?php if (!empty($supplier['phone'])): ?>
                                <dt class="col-sm-4">Phone:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($supplier['phone']) ?></dd>
                                <?php endif; ?>
                                
                                <?php if (!empty($supplier['website'])): ?>
                                <dt class="col-sm-4">Website:</dt>
                                <dd class="col-sm-8">
                                    <a href="<?= htmlspecialchars($supplier['website']) ?>" target="_blank"><?= htmlspecialchars($supplier['website']) ?></a>
                                </dd>
                                <?php endif; ?>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">Address</h5>
                            <address>
                                <?php if (!empty($supplier['address'])): ?>
                                    <?= htmlspecialchars($supplier['address']) ?><br>
                                <?php endif; ?>
                                
                                <?php 
                                $cityStateZip = [];
                                if (!empty($supplier['city'])) $cityStateZip[] = htmlspecialchars($supplier['city']);
                                if (!empty($supplier['state'])) $cityStateZip[] = htmlspecialchars($supplier['state']);
                                if (!empty($supplier['postal_code'])) $cityStateZip[] = htmlspecialchars($supplier['postal_code']);
                                
                                if (!empty($cityStateZip)): 
                                ?>
                                    <?= implode(', ', $cityStateZip) ?><br>
                                <?php endif; ?>
                                
                                <?php if (!empty($supplier['country'])): ?>
                                    <?= htmlspecialchars($supplier['country']) ?>
                                <?php endif; ?>
                            </address>
                        </div>
                    </div>
                    
                    <?php if (!empty($supplier['notes'])): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="card-title mb-3">Notes</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <?= nl2br(htmlspecialchars($supplier['notes'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="card-title mb-3">System Information</h5>
                            <dl class="row">
                                <dt class="col-sm-3">Created by:</dt>
                                <dd class="col-sm-9"><?= htmlspecialchars($supplier['created_by_name'] ?? 'Unknown') ?></dd>
                                
                                <dt class="col-sm-3">Created on:</dt>
                                <dd class="col-sm-9"><?= date('F d, Y \a\t h:i A', strtotime($supplier['created_at'])) ?></dd>
                                
                                <dt class="col-sm-3">Last updated:</dt>
                                <dd class="col-sm-9"><?= date('F d, Y \a\t h:i A', strtotime($supplier['updated_at'])) ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-receipt me-1"></i>
                    Invoices from this Supplier
                </div>
                <div class="card-body">
                    <?php if (empty($invoices)): ?>
                        <div class="alert alert-info mb-0">
                            No invoices found for this supplier.
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($invoices as $invoice): ?>
                                <a href="/invoices/viewDetail/<?= $invoice['id'] ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($invoice['invoice_number']) ?></h6>
                                        <span class="badge <?= $invoice['status'] === 'paid' ? 'bg-success' : ($invoice['status'] === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                            <?= ucfirst(htmlspecialchars($invoice['status'])) ?>
                                        </span>
                                    </div>
                                    <div class="d-flex w-100 justify-content-between">
                                        <p class="mb-1">
                                            <?= date('M d, Y', strtotime($invoice['invoice_date'])) ?>
                                        </p>
                                        <strong>$<?= number_format($invoice['total_amount'], 2) ?></strong>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (count($invoices) > 5): ?>
                            <div class="text-center mt-3">
                                <a href="/invoices?supplier_id=<?= $supplier['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    View All Invoices
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="/invoices/add?supplier_id=<?= $supplier['id'] ?>" class="btn btn-primary">
                        <i class="bi bi-plus me-1"></i> Create New Invoice
                    </a>
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
                    <i class="bi bi-exclamation-triangle me-1"></i> 
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