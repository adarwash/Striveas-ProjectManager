<!-- Modern Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-file-invoice me-3"></i><?= $pageTitle ?></h1>
        <p class="mb-0">Manage invoices and billing information</p>
    </div>
    <div>
        <a href="/invoices/add" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Invoice
        </a>
    </div>
</div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-4 border-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Invoices</div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="h2 mb-0 fw-bold text-gray-800"><?= $stats['total_count'] ?? 0 ?></div>
                                <div class="h5 mb-0 fw-bold text-gray-800"><?= $currency['symbol'] ?><?= number_format($stats['total_amount'] ?? 0, 2) ?></div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-4 border-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Pending Invoices</div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="h2 mb-0 fw-bold text-gray-800"><?= $stats['pending_count'] ?? 0 ?></div>
                                <div class="h5 mb-0 fw-bold text-gray-800"><?= $currency['symbol'] ?><?= number_format($stats['pending_amount'] ?? 0, 2) ?></div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <a href="/invoices?status=pending" class="card-footer bg-transparent text-decoration-none d-flex align-items-center justify-content-between">
                    <span class="small text-warning">View Pending</span>
                    <i class="fas fa-chevron-right text-warning"></i>
                </a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-4 border-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Paid Invoices</div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="h2 mb-0 fw-bold text-gray-800"><?= $stats['paid_count'] ?? 0 ?></div>
                                <div class="h5 mb-0 fw-bold text-gray-800"><?= $currency['symbol'] ?><?= number_format($stats['paid_amount'] ?? 0, 2) ?></div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <a href="/invoices?status=paid" class="card-footer bg-transparent text-decoration-none d-flex align-items-center justify-content-between">
                    <span class="small text-success">View Paid</span>
                    <i class="fas fa-chevron-right text-success"></i>
                </a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-start border-4 border-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Cancelled Invoices</div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="h2 mb-0 fw-bold text-gray-800"><?= $stats['cancelled_count'] ?? 0 ?></div>
                                <div class="h5 mb-0 fw-bold text-gray-800"><?= $currency['symbol'] ?><?= number_format($stats['cancelled_amount'] ?? 0, 2) ?></div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <a href="/invoices?status=cancelled" class="card-footer bg-transparent text-decoration-none d-flex align-items-center justify-content-between">
                    <span class="small text-danger">View Cancelled</span>
                    <i class="fas fa-chevron-right text-danger"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary d-flex align-items-center">
                <i class="bi bi-receipt me-2"></i>
                Invoices List
            </h6>
            <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                <div class="btn-group" role="group">
                    <a href="/invoices" class="btn <?= !isset($_GET['status']) ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
                    <a href="/invoices?status=pending" class="btn <?= isset($_GET['status']) && $_GET['status'] === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">Pending</a>
                    <a href="/invoices?status=paid" class="btn <?= isset($_GET['status']) && $_GET['status'] === 'paid' ? 'btn-success' : 'btn-outline-success' ?>">Paid</a>
                    <a href="/invoices?status=cancelled" class="btn <?= isset($_GET['status']) && $_GET['status'] === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' ?>">Cancelled</a>
                </div>
                <form class="d-flex" role="search" method="GET" action="/invoices">
                    <div class="input-group">
                        <input class="form-control" type="search" name="search" placeholder="Search invoices..." aria-label="Search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                <a href="/invoices/add" class="btn btn-primary d-sm-none">
                    <i class="bi bi-plus-circle"></i>
                </a>
            </div>
        </div>
        <div class="card-body">
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
            
            <?php if (empty($invoices)): ?>
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="bi bi-info-circle-fill flex-shrink-0 me-2"></i>
                    <div>
                        No invoices found. 
                        <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                            <a href="/invoices" class="alert-link">Clear search</a> to see all invoices.
                        <?php else: ?>
                            <a href="/invoices/add" class="alert-link">Add your first invoice</a>.
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="invoicesTable">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Supplier</th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr data-id="<?= $invoice['id'] ?>">
                                    <td>
                                        <a href="/invoices/viewDetail/<?= $invoice['id'] ?>" class="text-decoration-none fw-bold">
                                            <?= htmlspecialchars($invoice['invoice_number']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="/suppliers/viewDetail/<?= $invoice['supplier_id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($invoice['supplier_name']) ?>
                                        </a>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($invoice['invoice_date'])) ?></td>
                                    <td>
                                        <?php if (!empty($invoice['due_date'])): ?>
                                            <?php 
                                                $dueDate = strtotime($invoice['due_date']);
                                                $today = strtotime(date('Y-m-d'));
                                                $isPastDue = $invoice['status'] === 'pending' && $dueDate < $today;
                                            ?>
                                            <span class="<?= $isPastDue ? 'text-danger fw-bold' : '' ?>">
                                                <?= date('M d, Y', $dueDate) ?>
                                                <?= $isPastDue ? '<i class="bi bi-exclamation-circle ms-1" title="Past due"></i>' : '' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-bold"><?= $currency['symbol'] ?><?= number_format($invoice['total_amount'], 2) ?></td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/invoices/viewDetail/<?= $invoice['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/invoices/edit/<?= $invoice['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($invoice['status'] === 'pending'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success mark-paid-btn" 
                                                    title="Mark as Paid" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#markPaidModal" 
                                                    data-id="<?= $invoice['id'] ?>"
                                                    data-invoice="<?= htmlspecialchars($invoice['invoice_number']) ?>"
                                                    data-amount="<?= number_format($invoice['total_amount'], 2) ?>">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn" 
                                                title="Delete" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal" 
                                                data-id="<?= $invoice['id'] ?>" 
                                                data-invoice="<?= htmlspecialchars($invoice['invoice_number']) ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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
                <p class="text-center">Are you sure you want to delete invoice: <strong id="invoiceNumber"></strong>?</p>
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                    <div>This action cannot be undone.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="deleteInvoiceLink" class="btn btn-danger">Delete Invoice</a>
            </div>
        </div>
    </div>
</div>

<!-- Mark as Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1" aria-labelledby="markPaidModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="markPaidModalLabel">Mark Invoice as Paid</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="markPaidForm" method="POST" action="/invoices/markAsPaid">
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    </div>
                    
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 text-muted">Invoice:</div>
                                <div class="col-7 fw-bold" id="paidInvoiceNumber"></div>
                            </div>
                            <div class="row">
                                <div class="col-5 text-muted">Amount:</div>
                                <div class="col-7 fw-bold" id="paidInvoiceAmount"></div>
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="id" id="paid_invoice_id" value="">
                    
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
                    <button type="submit" class="btn btn-success" id="markPaidSubmitBtn">
                        <i class="bi bi-check-circle me-1"></i> Mark as Paid
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize DataTable - Check if DataTable is available first
    const tableElement = document.getElementById('invoicesTable');
    if (tableElement && typeof(jQuery) !== 'undefined' && typeof(jQuery.fn.DataTable) !== 'undefined') {
        jQuery('#invoicesTable').DataTable({
            responsive: true,
            order: [[2, 'desc']], // Sort by date (3rd column) by default
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search invoices...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ invoices",
                paginate: {
                    first: '<i class="bi bi-chevron-double-left"></i>',
                    previous: '<i class="bi bi-chevron-left"></i>',
                    next: '<i class="bi bi-chevron-right"></i>',
                    last: '<i class="bi bi-chevron-double-right"></i>'
                }
            }
        });
    }
    
    // Function to update statistics after deletion
    function updateStatistics() {
        // For now, we'll just reload the page after a short delay
        // In a more advanced implementation, you could use AJAX to fetch updated stats
        setTimeout(() => {
            location.reload();
        }, 1000);
    }
    
    // Handle delete button click
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const invoice = this.getAttribute('data-invoice');
            
            // Update the UI with the invoice number
            document.getElementById('invoiceNumber').textContent = invoice;
            
            // Update the delete link with the invoice ID
            const deleteLink = document.getElementById('deleteInvoiceLink');
            deleteLink.href = "/invoices/delete?id=" + id;
            
            console.log('Delete button clicked for:', invoice, 'ID:', id);
            console.log('Delete link set to:', deleteLink.href);
        });
    });
    
    // Handle mark as paid button click
    const markPaidButtons = document.querySelectorAll('.mark-paid-btn');
    markPaidButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const invoice = this.getAttribute('data-invoice');
            const amount = this.getAttribute('data-amount');
            
            document.getElementById('paid_invoice_id').value = id;
            document.getElementById('paidInvoiceNumber').textContent = invoice;
            document.getElementById('paidInvoiceAmount').textContent = '<?= $currency['symbol'] ?>' + amount;
            
            // Set the form action with ID included in both the form action and hidden field
            const form = document.getElementById('markPaidForm');
            form.action = "/invoices/markAsPaid?id=" + id;
            
            // Double-check the ID is set correctly
            console.log('Mark as paid for invoice:', invoice, 'ID:', id);
            console.log('Paid invoice ID value:', document.getElementById('paid_invoice_id').value);
            console.log('Form action:', form.action);
        });
    });
<!-- Modern Page Header Styling -->
<style>
/* Page Header */
.page-header {
    background: #ffffff;
    color: #333;
    padding: 2rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid #e9ecef;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: #333;
}

.page-title i {
    color: #7c3aed;
    margin-right: 0.75rem;
}

.page-header p {
    color: #6c757d;
    margin: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add handler for the mark as paid form submission
    const markPaidForm = document.getElementById('markPaidForm');
    if (markPaidForm) {
        markPaidForm.addEventListener('submit', function(e) {
            const id = document.getElementById('paid_invoice_id').value;
            
            if (!id || id.trim() === '') {
                e.preventDefault();
                alert('Error: Invoice ID is required for payment');
                return false;
            }
            
            // Ensure the ID is in both the URL and the form
            this.action = "/invoices/markAsPaid?id=" + id;
            console.log('Submitting mark as paid form with ID:', id);
            console.log('Form action:', this.action);
        });
    }
});
</script>