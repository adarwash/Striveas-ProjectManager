<!-- Modern Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-plus me-3"></i><?= $pageTitle ?></h1>
        <p class="mb-0">Create a new invoice for your supplier</p>
    </div>
    <div>
        <a href="/invoices" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left me-2"></i>Back to Invoices
        </a>
        <button type="submit" form="invoice-form" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>Save Invoice
        </button>
    </div>
</div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-plus me-1"></i>
                    Invoice Information
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <form id="invoice-form" action="<?= $formAction ?>" method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="invoice_number" class="form-label">Invoice Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="invoice_number" name="invoice_number" required
                                       value="<?= isset($_SESSION['form_data']['invoice_number']) ? htmlspecialchars($_SESSION['form_data']['invoice_number']) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                                <select class="form-select" id="supplier_id" name="supplier_id" required>
                                    <option value="">-- Select Supplier --</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?= $supplier['id'] ?>" 
                                            <?= (isset($_SESSION['form_data']['supplier_id']) && $_SESSION['form_data']['supplier_id'] == $supplier['id']) 
                                                || (isset($_GET['supplier_id']) && $_GET['supplier_id'] == $supplier['id']) 
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
                                       value="<?= isset($_SESSION['form_data']['invoice_date']) ? htmlspecialchars($_SESSION['form_data']['invoice_date']) : date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date"
                                       value="<?= isset($_SESSION['form_data']['due_date']) ? htmlspecialchars($_SESSION['form_data']['due_date']) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="total_amount" class="form-label">Total Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><?= $currency['symbol'] ?></span>
                                    <input type="text" class="form-control" id="total_amount" name="total_amount" required
                                           placeholder="0.00" inputmode="decimal" pattern="[0-9]*[.,]?[0-9]+"
                                           value="<?= isset($_SESSION['form_data']['total_amount']) ? htmlspecialchars($_SESSION['form_data']['total_amount']) : '' ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="pending" <?= (isset($_SESSION['form_data']['status']) && $_SESSION['form_data']['status'] === 'pending') || !isset($_SESSION['form_data']['status']) ? 'selected' : '' ?>>Pending</option>
                                    <option value="paid" <?= isset($_SESSION['form_data']['status']) && $_SESSION['form_data']['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                                    <option value="cancelled" <?= isset($_SESSION['form_data']['status']) && $_SESSION['form_data']['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
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
                                                   value="<?= isset($_SESSION['form_data']['payment_date']) ? htmlspecialchars($_SESSION['form_data']['payment_date']) : date('Y-m-d') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="payment_reference" class="form-label">Payment Reference</label>
                                            <input type="text" class="form-control" id="payment_reference" name="payment_reference"
                                                   placeholder="Check #, Transaction ID, etc."
                                                   value="<?= isset($_SESSION['form_data']['payment_reference']) ? htmlspecialchars($_SESSION['form_data']['payment_reference']) : '' ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"><?= isset($_SESSION['form_data']['notes']) ? htmlspecialchars($_SESSION['form_data']['notes']) : '' ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="/invoices" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Invoice</button>
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
                    <h5>Adding a New Invoice</h5>
                    <p>Use this form to add a new invoice to your system. Fields marked with <span class="text-danger">*</span> are required.</p>
                    
                    <h6>Quick Tips:</h6>
                    <ul>
                        <li>Enter a unique invoice number for easy reference.</li>
                        <li>If the supplier isn't in the list, <a href="/suppliers/add" target="_blank">add them first</a>.</li>
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
                
                return;
            }
        }
        
        // No supplier selected or not found
        noSupplierSelected.style.display = 'block';
        supplierDetails.style.display = 'none';
    }
    
    // Set initial state
    updateSupplierDetails();
    
    // Add event listener
    supplierSelect.addEventListener('change', updateSupplierDetails);
});
</script> 