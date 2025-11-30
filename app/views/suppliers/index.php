<div class="container-fluid px-4">
    <!-- Modern Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fas fa-truck me-3"></i>Suppliers</h1>
            <p class="mb-0">Manage your vendors and purchasing partners</p>
        </div>
        <div>
            <a href="/suppliers/add" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>New Supplier
            </a>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-table me-1"></i>
                Suppliers List
            </div>
            <div class="d-flex gap-2">
                <form class="d-flex" role="search" method="GET" action="/suppliers">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search suppliers..." aria-label="Search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                </form>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Filter
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                        <li><a class="dropdown-item <?= !isset($_GET['status']) ? 'active' : '' ?>" href="/suppliers">All Suppliers</a></li>
                        <li><a class="dropdown-item <?= isset($_GET['status']) && $_GET['status'] === 'active' ? 'active' : '' ?>" href="/suppliers?status=active">Active</a></li>
                        <li><a class="dropdown-item <?= isset($_GET['status']) && $_GET['status'] === 'inactive' ? 'active' : '' ?>" href="/suppliers?status=inactive">Inactive</a></li>
                    </ul>
                </div>
                <a href="/suppliers/add" class="btn btn-primary">Add New Supplier</a>
            </div>
        </div>
        <div class="card-body">
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
            
            <?php if (empty($suppliers)): ?>
                <div class="alert alert-info">
                    No suppliers found. 
                    <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                        <a href="/suppliers" class="alert-link">Clear search</a> to see all suppliers.
                    <?php else: ?>
                        <a href="/suppliers/add" class="alert-link">Add your first supplier</a>.
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="suppliersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Contact Person</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>City</th>
                                <th>Country</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suppliers as $supplier): ?>
                                <tr>
                                    <td><?= htmlspecialchars($supplier['id']) ?></td>
                                    <td>
                                        <a href="/suppliers/viewDetail/<?= $supplier['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($supplier['name']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($supplier['contact_name'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($supplier['email'])): ?>
                                            <a href="mailto:<?= htmlspecialchars($supplier['email']) ?>"><?= htmlspecialchars($supplier['email']) ?></a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($supplier['phone'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($supplier['city'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($supplier['country'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge <?= $supplier['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>">
                                            <?= ucfirst(htmlspecialchars($supplier['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/suppliers/viewDetail/<?= $supplier['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="/suppliers/edit/<?= $supplier['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn" title="Delete" 
                                                data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                                data-id="<?= $supplier['id'] ?>" 
                                                data-name="<?= htmlspecialchars($supplier['name']) ?>">
                                                <i class="bi bi-trash"></i>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the supplier: <strong id="supplierName"></strong>?
                <p class="text-danger mt-2">
                    <i class="bi bi-exclamation-triangle"></i> This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="/suppliers/delete">
                    <input type="hidden" name="id" id="supplier_id" value="">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize DataTable
    if (document.getElementById('suppliersTable')) {
        new DataTable('#suppliersTable', {
            responsive: true
        });
    }
    
    // Handle delete button click
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            document.getElementById('supplier_id').value = id;
            document.getElementById('supplierName').textContent = name;
        });
    });
    
    // Handle AJAX delete form submission
    document.getElementById('deleteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('supplier_id').value;
        const modalElement = document.getElementById('deleteModal');
        const modal = bootstrap.Modal.getInstance(modalElement);
        
        // Show loading state
        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
        
        fetch('/suppliers/deleteAjax', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            // Hide modal
            modal.hide();
            
            // Clean up modal backdrop
            setTimeout(() => {
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                document.body.classList.remove('modal-open');
                document.body.style.paddingRight = '';
                document.body.style.overflow = '';
            }, 200);
            
            if (data.success) {
                // Show success message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show';
                alertDiv.setAttribute('role', 'alert');
                alertDiv.innerHTML = `
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                document.querySelector('.card-body').prepend(alertDiv);
                
                // Remove the row from the table
                const row = document.querySelector(`button[data-id="${id}"]`).closest('tr');
                row.remove();
                
                // If no more rows, show empty message
                const tableRows = document.querySelectorAll('#suppliersTable tbody tr');
                if (tableRows.length === 0) {
                    location.reload(); // Refresh to show empty state
                }
            } else {
                // Show error message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.setAttribute('role', 'alert');
                alertDiv.innerHTML = `
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                document.querySelector('.card-body').prepend(alertDiv);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Hide modal
            modal.hide();
            
            // Clean up modal backdrop
            setTimeout(() => {
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                document.body.classList.remove('modal-open');
                document.body.style.paddingRight = '';
                document.body.style.overflow = '';
            }, 200);
            
            // Show error message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.setAttribute('role', 'alert');
            alertDiv.innerHTML = `
                An error occurred. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            document.querySelector('.card-body').prepend(alertDiv);
        })
        .finally(() => {
            // Reset button
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
        });
    });
});
</script> 