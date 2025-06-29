<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $pageTitle ?></h1>
    
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/invoices">Invoices</a></li>
        <li class="breadcrumb-item active">Edit Invoice</li>
    </ol>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-edit me-1"></i>
                    Edit Invoice Information
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
                        <input type="hidden" name="id" value="<?= $invoice['id'] ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="invoice_number" class="form-label">Invoice Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="invoice_number" name="invoice_number" required
                                       value="<?= isset($_SESSION['form_data']['invoice_number']) ? htmlspecialchars($_SESSION['form_data']['invoice_number']) : htmlspecialchars($invoice['invoice_number']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                                <select class="form-select" id="supplier_id" name="supplier_id" required>
                                    <option value="">-- Select Supplier --</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?= $supplier['id'] ?>" 
                                            <?= (isset($_SESSION['form_data']['supplier_id']) && $_SESSION['form_data']['supplier_id'] == $supplier['id']) 
                                                || (!isset($_SESSION['form_data']['supplier_id']) && $invoice['supplier_id'] == $supplier['id'])
                                                    ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($supplier['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="invoice_date" class="form-label">Invoice Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="invoice_date" name="invoice_date" required
                                       value="<?= isset($_SESSION['form_data']['invoice_date']) ? htmlspecialchars($_SESSION['form_data']['invoice_date']) : htmlspecialchars($invoice['invoice_date']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date"
                                       value="<?= isset($_SESSION['form_data']['due_date']) ? htmlspecialchars($_SESSION['form_data']['due_date']) : htmlspecialchars($invoice['due_date'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="total_amount" class="form-label">Total Amount <span class="text-danger">*</span></label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><?= $currency['symbol'] ?></span>
                                    <input type="number" step="0.01" min="0.01" class="form-control" id="total_amount" name="total_amount" 
                                        placeholder="Enter amount" required
                                        value="<?= isset($_SESSION['form_data']['total_amount']) ? htmlspecialchars($_SESSION['form_data']['total_amount']) : htmlspecialchars($invoice['total_amount']) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="pending" <?= (isset($_SESSION['form_data']['status']) && $_SESSION['form_data']['status'] === 'pending') || (!isset($_SESSION['form_data']['status']) && $invoice['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                                    <option value="paid" <?= (isset($_SESSION['form_data']['status']) && $_SESSION['form_data']['status'] === 'paid') || (!isset($_SESSION['form_data']['status']) && $invoice['status'] === 'paid') ? 'selected' : '' ?>>Paid</option>
                                    <option value="cancelled" <?= (isset($_SESSION['form_data']['status']) && $_SESSION['form_data']['status'] === 'cancelled') || (!isset($_SESSION['form_data']['status']) && $invoice['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="payment-details" id="paymentDetailsSection" style="display: none;">
                            <div class="card mb-3">
                                <div class="card-header bg-success text-white">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    Payment Details
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="payment_date" class="form-label">Payment Date</label>
                                            <input type="date" class="form-control" id="payment_date" name="payment_date"
                                                   value="<?= isset($_SESSION['form_data']['payment_date']) ? htmlspecialchars($_SESSION['form_data']['payment_date']) : htmlspecialchars($invoice['payment_date'] ?? date('Y-m-d')) ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="payment_reference" class="form-label">Payment Reference</label>
                                            <input type="text" class="form-control" id="payment_reference" name="payment_reference"
                                                   placeholder="Check #, Transaction ID, etc."
                                                   value="<?= isset($_SESSION['form_data']['payment_reference']) ? htmlspecialchars($_SESSION['form_data']['payment_reference']) : htmlspecialchars($invoice['payment_reference'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"><?= isset($_SESSION['form_data']['notes']) ? htmlspecialchars($_SESSION['form_data']['notes']) : htmlspecialchars($invoice['notes'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="/invoices" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Invoice</button>
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
                    <h5>Editing an Invoice</h5>
                    <p>Use this form to update the existing invoice. Fields marked with <span class="text-danger">*</span> are required.</p>
                    
                    <h6>Quick Tips:</h6>
                    <ul>
                        <li>Edit the invoice details as needed.</li>
                        <li>You can change the status to track paid or cancelled invoices.</li>
                        <li>For paid invoices, make sure to include the payment date.</li>
                    </ul>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-link me-1"></i>
                    Supplier Information
                </div>
                <div class="card-body">
                    <div id="noSupplierSelected" class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i> Select a supplier to see their details.
                    </div>
                    
                    <div id="supplierDetails" style="display: none;">
                        <h5 id="supplierName"></h5>
                        <div id="supplierContact" class="small mb-3"></div>
                        
                        <a href="#" id="viewSupplierLink" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i> View Supplier
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize status change handling
    const statusSelect = document.getElementById('status');
    const paymentDetailsSection = document.getElementById('paymentDetailsSection');
    
    function togglePaymentDetails() {
        if (statusSelect.value === 'paid') {
            paymentDetailsSection.style.display = 'block';
            document.getElementById('payment_date').setAttribute('required', 'required');
        } else {
            paymentDetailsSection.style.display = 'none';
            document.getElementById('payment_date').removeAttribute('required');
        }
    }
    
    // Set initial state
    togglePaymentDetails();
    
    // Add event listener
    statusSelect.addEventListener('change', togglePaymentDetails);
    
    // Format amount input
    const amountInput = document.getElementById('total_amount');
    
    amountInput.addEventListener('input', function(e) {
        // Remove non-numeric characters except for decimal point
        let value = this.value.replace(/[^\d.]/g, '');
        
        // Ensure only one decimal point
        const decimalPoints = value.match(/\./g);
        if (decimalPoints && decimalPoints.length > 1) {
            value = value.slice(0, value.lastIndexOf('.'));
        }
        
        // Update the input value
        this.value = value;
    });
    
    // Handle supplier selection
    const supplierSelect = document.getElementById('supplier_id');
    const noSupplierSelected = document.getElementById('noSupplierSelected');
    const supplierDetails = document.getElementById('supplierDetails');
    const supplierName = document.getElementById('supplierName');
    const supplierContact = document.getElementById('supplierContact');
    const viewSupplierLink = document.getElementById('viewSupplierLink');
    
    // Supplier data
    const suppliers = <?= json_encode($suppliers) ?>;
    
    // Function to update supplier details
    function updateSupplierDetails() {
        const selectedSupplierId = supplierSelect.value;
        
        if (selectedSupplierId) {
            const selectedSupplier = suppliers.find(s => s.id == selectedSupplierId);
            
            if (selectedSupplier) {
                // Show supplier details
                noSupplierSelected.style.display = 'none';
                supplierDetails.style.display = 'block';
                
                // Update content
                supplierName.textContent = selectedSupplier.name;
                
                let contactInfo = '';
                if (selectedSupplier.contact_name) {
                    contactInfo += `<i class="fas fa-user me-1"></i> ${selectedSupplier.contact_name}<br>`;
                }
                if (selectedSupplier.email) {
                    contactInfo += `<i class="fas fa-envelope me-1"></i> ${selectedSupplier.email}<br>`;
                }
                if (selectedSupplier.phone) {
                    contactInfo += `<i class="fas fa-phone me-1"></i> ${selectedSupplier.phone}<br>`;
                }
                
                supplierContact.innerHTML = contactInfo || 'No contact information available';
                
                // Update link
                viewSupplierLink.href = `/suppliers/view/${selectedSupplier.id}`;
            } else {
                // Hide supplier details if supplier not found
                noSupplierSelected.style.display = 'block';
                supplierDetails.style.display = 'none';
            }
        } else {
            // Hide supplier details if no supplier selected
            noSupplierSelected.style.display = 'block';
            supplierDetails.style.display = 'none';
        }
    }
    
    // Initial update
    updateSupplierDetails();
    
    // Add event listener
    supplierSelect.addEventListener('change', updateSupplierDetails);
});
</script> 