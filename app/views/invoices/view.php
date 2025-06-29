<?php
// Invoice details view
// Clear any output buffers that might contain unwanted text
ob_clean();
?>
<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= $pageTitle ?></h1>
        <div>
            <a href="/invoices/edit/<?= $invoice['id'] ?>" class="btn btn-primary shadow-sm">
                <i class="bi bi-pencil me-1"></i> Edit Invoice
            </a>
            <a href="/invoices" class="btn btn-outline-secondary shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Invoices
            </a>
        </div>
    </div>
    
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/invoices" class="text-decoration-none">Invoices</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($invoice['invoice_number']) ?></li>
        </ol>
    </nav>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Invoice Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-receipt me-1"></i> Invoice Details
                    </h6>
                    <?php
                        $statusClass = '';
                        $statusIcon = '';
                        switch($invoice['status']) {
                            case 'paid':
                                $statusClass = 'bg-success';
                                $statusIcon = 'bi-check-circle-fill';
                                break;
                            case 'cancelled':
                                $statusClass = 'bg-danger';
                                $statusIcon = 'bi-x-circle-fill';
                                break;
                            default: // pending
                                $statusClass = 'bg-warning text-dark';
                                $statusIcon = 'bi-clock-fill';
                        }
                    ?>
                    <span class="badge <?= $statusClass ?> rounded-pill">
                        <i class="bi <?= $statusIcon ?> me-1"></i>
                        <?= ucfirst(htmlspecialchars($invoice['status'])) ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th width="30%" class="bg-light">Invoice Number</th>
                                    <td><?= htmlspecialchars($invoice['invoice_number']) ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Supplier</th>
                                    <td>
                                        <a href="/suppliers/viewDetail/<?= $invoice['supplier_id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($invoice['supplier_name']) ?>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Invoice Date</th>
                                    <td><?= date('F d, Y', strtotime($invoice['invoice_date'])) ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Due Date</th>
                                    <td>
                                        <?php if (!empty($invoice['due_date'])): ?>
                                            <?php 
                                                $dueDate = strtotime($invoice['due_date']);
                                                $today = strtotime(date('Y-m-d'));
                                                $isPastDue = $invoice['status'] === 'pending' && $dueDate < $today;
                                            ?>
                                            <span class="<?= $isPastDue ? 'text-danger fw-bold' : '' ?>">
                                                <?= date('F d, Y', $dueDate) ?>
                                                <?= $isPastDue ? '<i class="bi bi-exclamation-circle ms-1" title="Past due"></i>' : '' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Amount</th>
                                    <td class="fw-bold"><?= $currency['symbol'] ?><?= number_format($invoice['total_amount'], 2) ?></td>
                                </tr>
                                <?php if ($invoice['status'] === 'paid'): ?>
                                <tr>
                                    <th class="bg-light">Payment Date</th>
                                    <td><?= date('F d, Y', strtotime($invoice['payment_date'])) ?></td>
                                </tr>
                                <?php if (!empty($invoice['payment_reference'])): ?>
                                <tr>
                                    <th class="bg-light">Payment Reference</th>
                                    <td><?= htmlspecialchars($invoice['payment_reference']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php endif; ?>
                                <tr>
                                    <th class="bg-light">Created By</th>
                                    <td><?= htmlspecialchars($invoice['created_by_name'] ?? 'Unknown') ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Created Date</th>
                                    <td><?= date('F d, Y g:i A', strtotime($invoice['created_at'])) ?></td>
                                </tr>
                                <?php if (!empty($invoice['updated_at'])): ?>
                                <tr>
                                    <th class="bg-light">Last Updated</th>
                                    <td><?= date('F d, Y g:i A', strtotime($invoice['updated_at'])) ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (!empty($invoice['notes'])): ?>
                    <div class="mt-4">
                        <h6 class="fw-bold"><i class="bi bi-sticky me-1"></i> Notes</h6>
                        <div class="p-3 bg-light rounded">
                            <?= nl2br(htmlspecialchars($invoice['notes'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Action Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-gear me-1"></i> Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/invoices/edit/<?= $invoice['id'] ?>" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i> Edit Invoice
                        </a>
                        
                        <?php if ($invoice['status'] === 'pending'): ?>
                            <button type="button" class="btn btn-success" 
                                data-bs-toggle="modal" 
                                data-bs-target="#markPaidModal">
                                <i class="bi bi-check-circle me-1"></i> Mark as Paid
                            </button>
                        <?php endif; ?>
                        
                        <button type="button" class="btn btn-danger" 
                            data-bs-toggle="modal" 
                            data-bs-target="#deleteModal">
                            <i class="bi bi-trash me-1"></i> Delete Invoice
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Supplier Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-building me-1"></i> Supplier Details
                    </h6>
                </div>
                <div class="card-body">
                    <h5><?= htmlspecialchars($invoice['supplier_name']) ?></h5>
                    <?php if (!empty($invoice['supplier_contact'])): ?>
                        <p class="mb-1"><i class="bi bi-person me-2"></i><?= htmlspecialchars($invoice['supplier_contact']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($invoice['supplier_email'])): ?>
                        <p class="mb-1"><i class="bi bi-envelope me-2"></i><a href="mailto:<?= htmlspecialchars($invoice['supplier_email']) ?>"><?= htmlspecialchars($invoice['supplier_email']) ?></a></p>
                    <?php endif; ?>
                    <?php if (!empty($invoice['supplier_phone'])): ?>
                        <p class="mb-1"><i class="bi bi-telephone me-2"></i><a href="tel:<?= htmlspecialchars($invoice['supplier_phone']) ?>"><?= htmlspecialchars($invoice['supplier_phone']) ?></a></p>
                    <?php endif; ?>
                    
                    <div class="mt-3">
                        <a href="/suppliers/viewDetail/<?= $invoice['supplier_id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-arrow-right me-1"></i> View Supplier
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Invoice Documents</h5>
                </div>
                <div class="card-body">
                    <!-- Document Upload Form -->
                    <form action="/invoices/uploadDocument/<?= $invoice['id'] ?>" method="POST" enctype="multipart/form-data" class="mb-4">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="file" class="form-control" id="document" name="document" required>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-upload me-1"></i> Upload Document
                                    </button>
                                </div>
                                <div class="form-text">Supported formats: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max size: 10MB)</div>
                            </div>
                        </div>
                    </form>

                    <!-- Documents List -->
                    <?php if (!empty($documents)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Uploaded By</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documents as $document): ?>
                                <tr>
                                    <td>
                                        <i class="bi bi-file-earmark me-1"></i>
                                        <?= htmlspecialchars($document['file_name']) ?>
                                    </td>
                                    <td><?= strtoupper(pathinfo($document['file_name'], PATHINFO_EXTENSION)) ?></td>
                                    <td><?= $document['formatted_size'] ?></td>
                                    <td><?= htmlspecialchars($document['uploaded_by_name']) ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($document['uploaded_at'])) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/invoices/downloadDocument/<?= $document['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <a href="/invoices/deleteDocument/<?= $document['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this document?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i> No documents uploaded yet.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-trash-fill text-danger" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center">Are you sure you want to delete invoice: <strong><?= htmlspecialchars($invoice['invoice_number']) ?></strong>?</p>
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                    <div>This action cannot be undone.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="/invoices/delete">
                    <input type="hidden" name="id" value="<?= $invoice['id'] ?>">
                    <button type="submit" class="btn btn-danger">Delete Invoice</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Mark as Paid Modal -->
<?php if ($invoice['status'] === 'pending'): ?>
<div class="modal fade" id="markPaidModal" tabindex="-1" aria-labelledby="markPaidModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="markPaidModalLabel">Mark Invoice as Paid</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="/invoices/markAsPaid">
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    </div>
                    
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 text-muted">Invoice:</div>
                                <div class="col-7 fw-bold"><?= htmlspecialchars($invoice['invoice_number']) ?></div>
                            </div>
                            <div class="row">
                                <div class="col-5 text-muted">Amount:</div>
                                <div class="col-7 fw-bold"><?= $currency['symbol'] ?><?= number_format($invoice['total_amount'], 2) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="id" value="<?= $invoice['id'] ?>">
                    
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Payment Date</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_reference" class="form-label">Payment Reference</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-hash"></i></span>
                            <input type="text" class="form-control" id="payment_reference" name="payment_reference" placeholder="Optional reference number">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> Mark as Paid
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?> 