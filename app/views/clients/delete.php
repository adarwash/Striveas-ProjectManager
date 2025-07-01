<div class="container-fluid">
    <!-- Page Header -->
    <div class="bg-light rounded-3 p-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/clients" class="text-decoration-none">Clients</a></li>
                <li class="breadcrumb-item"><a href="/clients/viewClient/<?= $client['id'] ?>" class="text-decoration-none"><?= htmlspecialchars($client['name']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Delete</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 text-danger">Delete Client</h1>
                <p class="text-muted">Permanently remove client from the system</p>
            </div>
            <div class="d-flex gap-2">
                <a href="/clients/viewClient/<?= $client['id'] ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-eye"></i> View Client
                </a>
                <a href="/clients" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Clients
                </a>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Confirm Deletion
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger" role="alert">
                        <h5 class="alert-heading">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Warning: This action cannot be undone!
                        </h5>
                        <p class="mb-0">
                            You are about to permanently delete the client <strong><?= htmlspecialchars($client['name']) ?></strong>. 
                            This will also remove all associated site assignments and relationships.
                        </p>
                    </div>
                    
                    <!-- Client Information Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-semibold">Client Details</h6>
                            <dl class="row small">
                                <dt class="col-sm-5">Name:</dt>
                                <dd class="col-sm-7"><?= htmlspecialchars($client['name']) ?></dd>
                                
                                <dt class="col-sm-5">Contact:</dt>
                                <dd class="col-sm-7">
                                    <?= !empty($client['contact_person']) ? htmlspecialchars($client['contact_person']) : '<span class="text-muted">—</span>' ?>
                                </dd>
                                
                                <dt class="col-sm-5">Email:</dt>
                                <dd class="col-sm-7">
                                    <?= !empty($client['email']) ? htmlspecialchars($client['email']) : '<span class="text-muted">—</span>' ?>
                                </dd>
                                
                                <dt class="col-sm-5">Industry:</dt>
                                <dd class="col-sm-7">
                                    <?= !empty($client['industry']) ? htmlspecialchars($client['industry']) : '<span class="text-muted">—</span>' ?>
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="fw-semibold">Impact Assessment</h6>
                            <ul class="list-unstyled small">
                                <li class="mb-2">
                                    <i class="bi bi-geo-alt text-warning me-2"></i>
                                    All site assignments will be removed
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-folder text-info me-2"></i>
                                    Client history will be permanently lost
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-link-45deg text-secondary me-2"></i>
                                    All relationships will be broken
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-database text-danger me-2"></i>
                                    Data cannot be recovered after deletion
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Confirmation Form -->
                    <form action="/clients/delete/<?= $client['id'] ?>" method="post">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                To confirm deletion, please type the client name: 
                                <span class="text-danger"><?= htmlspecialchars($client['name']) ?></span>
                            </label>
                            <input type="text" class="form-control" id="confirmation" name="confirmation" 
                                   placeholder="Type client name to confirm" required>
                            <div class="form-text">This helps prevent accidental deletions.</div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                                                         <a href="/clients/viewClient/<?= $client['id'] ?>" class="btn btn-secondary">
                                <i class="bi bi-x-lg"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-danger" id="deleteBtn" disabled>
                                <i class="bi bi-trash"></i> Delete Client Permanently
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmationInput = document.getElementById('confirmation');
    const deleteBtn = document.getElementById('deleteBtn');
    const clientName = <?= json_encode($client['name']) ?>;
    
    confirmationInput.addEventListener('input', function() {
        if (this.value === clientName) {
            deleteBtn.disabled = false;
            deleteBtn.classList.remove('btn-secondary');
            deleteBtn.classList.add('btn-danger');
        } else {
            deleteBtn.disabled = true;
            deleteBtn.classList.remove('btn-danger');
            deleteBtn.classList.add('btn-secondary');
        }
    });
    
    // Additional confirmation on form submit
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!confirm('Are you absolutely sure you want to delete this client? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
});
</script> 