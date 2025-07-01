<div class="container-fluid">
    <!-- Page Header with Background -->
    <div class="bg-light rounded-3 p-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Clients</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Client Management</h1>
                <p class="text-muted">Manage all clients and their associated sites</p>
            </div>
            <?php if (hasPermission('clients.create')): ?>
            <a href="/clients/create" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add New Client
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Flash Messages -->
    <?php flash('client_success'); ?>
    <?php flash('client_error'); ?>
    
    <!-- Clients Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-people text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Total Clients</h6>
                        <h3 class="mt-2 mb-0"><?= count($clients) ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Active Clients</h6>
                        <h3 class="mt-2 mb-0">
                            <?php 
                                $activeCount = 0;
                                foreach ($clients as $client) {
                                    if ($client['status'] == 'Active') $activeCount++;
                                }
                                echo $activeCount;
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="bi bi-building text-info fs-4"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Industries</h6>
                        <h3 class="mt-2 mb-0">
                            <?php 
                                $industries = array_unique(array_column($clients, 'industry'));
                                echo count(array_filter($industries));
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="bi bi-geo-alt text-warning fs-4"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">With Sites</h6>
                        <h3 class="mt-2 mb-0">—</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Clients List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">All Clients</h5>
                <div class="form-group has-search position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" class="form-control ps-4" placeholder="Search clients..." id="clientSearch">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($clients)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="clientsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Client Name</th>
                            <th>Contact Person</th>
                            <th>Industry</th>
                            <th>Status</th>
                            <th>Sites</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-icon rounded bg-light p-2 me-3">
                                        <i class="bi bi-person-circle text-primary"></i>
                                    </div>
                                    <div>
                                        <a href="/clients/viewClient/<?= $client['id'] ?>" class="fw-semibold text-decoration-none">
                                            <?= htmlspecialchars($client['name']) ?>
                                        </a>
                                        <?php if(!empty($client['email'])): ?>
                                        <div class="small text-muted"><?= htmlspecialchars($client['email']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if(!empty($client['contact_person'])): ?>
                                    <?= htmlspecialchars($client['contact_person']) ?>
                                    <?php if(!empty($client['phone'])): ?>
                                    <div class="small text-muted"><?= htmlspecialchars($client['phone']) ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!empty($client['industry'])): ?>
                                    <?php 
                                        $industryClass = '';
                                        $industryIcon = '';
                                        switch(strtolower($client['industry'])) {
                                            case 'technology':
                                                $industryClass = 'text-bg-primary';
                                                $industryIcon = 'bi-cpu';
                                                break;
                                            case 'manufacturing':
                                                $industryClass = 'text-bg-secondary';
                                                $industryIcon = 'bi-gear';
                                                break;
                                            case 'healthcare':
                                                $industryClass = 'text-bg-success';
                                                $industryIcon = 'bi-heart-pulse';
                                                break;
                                            case 'finance':
                                                $industryClass = 'text-bg-warning';
                                                $industryIcon = 'bi-bank';
                                                break;
                                            case 'retail':
                                                $industryClass = 'text-bg-info';
                                                $industryIcon = 'bi-shop';
                                                break;
                                            default:
                                                $industryClass = 'text-bg-light text-dark';
                                                $industryIcon = 'bi-building';
                                        }
                                    ?>
                                    <span class="badge <?= $industryClass ?> rounded-pill">
                                        <i class="bi <?= $industryIcon ?> me-1"></i>
                                        <?= htmlspecialchars($client['industry']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    if ($client['status'] == 'Active') {
                                        $statusClass = 'text-bg-success';
                                        $statusIcon = 'bi-check-circle';
                                    } elseif ($client['status'] == 'Inactive') {
                                        $statusClass = 'text-bg-danger';
                                        $statusIcon = 'bi-x-circle';
                                    } else {
                                        $statusClass = 'text-bg-secondary';
                                        $statusIcon = 'bi-dash-circle';
                                    }
                                ?>
                                <span class="badge <?= $statusClass ?> rounded-pill">
                                    <i class="bi <?= $statusIcon ?> me-1"></i>
                                    <?= $client['status'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted">—</span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="/clients/viewClient/<?= $client['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (hasPermission('clients.assign_sites')): ?>
                                    <a href="/clients/assignSites/<?= $client['id'] ?>" class="btn btn-sm btn-outline-info" title="Assign Sites">
                                        <i class="bi bi-geo-alt"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('clients.update')): ?>
                                    <a href="/clients/edit/<?= $client['id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('clients.delete')): ?>
                                    <a href="/clients/delete/<?= $client['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                </div>
                <h5 class="text-muted">No Clients Found</h5>
                <p class="text-muted">Get started by adding your first client</p>
                <?php if (hasPermission('clients.create')): ?>
                <a href="/clients/create" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add New Client
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('clientSearch');
    const table = document.getElementById('clientsTable');
    
    if (searchInput && table) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script> 