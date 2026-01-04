<?php require VIEWSPATH . '/partials/header.php'; ?>

<?php
// Ensure CSRF token exists for pin/unpin actions
if (!isset($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(8));
    }
}
?>

<!-- Modern Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-building me-3"></i>Client Management</h1>
        <p class="mb-0">Manage all clients and their associated sites</p>
    </div>
    <div>
        <?php if (hasPermission('clients.create')): ?>
        <a href="/clients/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Client
        </a>
        <?php endif; ?>
    </div>
</div>
    
    <!-- Flash Messages -->
    <?php flash('client_success'); ?>
    <?php flash('client_error'); ?>
    
<!-- Modern Stats Overview -->
<div class="row g-4 mb-5">
    <?php
    $totalClients = count($clients);
    $activeCount = 0;
    $inactiveCount = 0;
    foreach ($clients as $client) {
        if ($client['status'] == 'Active') $activeCount++;
        else $inactiveCount++;
    }
    $industries = array_unique(array_filter(array_column($clients, 'industry')));
    $industriesCount = count($industries);
    ?>
    
    <div class="col-lg-3 col-md-6">
        <div class="stats-card purple">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number"><?= $totalClients ?></div>
                    <div class="stats-label">Total Clients</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-building"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stats-card green">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number"><?= $activeCount ?></div>
                    <div class="stats-label">Active Clients</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stats-card blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number"><?= $industriesCount ?></div>
                    <div class="stats-label">Industries</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-industry"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stats-card orange">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number"><?= $inactiveCount ?></div>
                    <div class="stats-label">Inactive Clients</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-pause-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>
    
<!-- Modern Clients List -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-1">All Clients</h5>
                <p class="text-muted mb-0">Manage your client relationships</p>
            </div>
            <div class="d-flex gap-3 align-items-center">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" placeholder="Search clients..." id="clientSearch">
                </div>
                <button
                    type="button"
                    class="btn btn-sm <?= !empty($clients_all_pinned) ? 'btn-primary' : 'btn-outline-secondary' ?> dashboard-pin-toggle"
                    data-card-id="clients.all"
                    data-widget-id="card:clients.all"
                    data-csrf-token="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                    aria-pressed="<?= !empty($clients_all_pinned) ? 'true' : 'false' ?>"
                    title="<?= !empty($clients_all_pinned) ? 'Unpin from dashboard' : 'Pin to dashboard' ?>"
                >
                    <i class="bi <?= !empty($clients_all_pinned) ? 'bi-pin-fill' : 'bi-pin-angle' ?>"></i>
                    <span class="d-none d-md-inline ms-1"><?= !empty($clients_all_pinned) ? 'Pinned' : 'Pin' ?></span>
                </button>
                <?php if (hasPermission('clients.create')): ?>
                <a href="/clients/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Client
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($clients)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="clientsTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 border-0">Client Name</th>
                        <th class="border-0">Contact Person</th>
                        <th class="border-0">Industry</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">Sites</th>
                        <th class="text-end pe-4 border-0">Actions</th>
                    </tr>
                </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                        <?php
                            $pinnedIds = isset($pinned_client_ids) && is_array($pinned_client_ids) ? $pinned_client_ids : [];
                            $isClientPinned = in_array((int)$client['id'], $pinnedIds, true);
                            $clientWidgetId = 'card:clients.client:' . (int)$client['id'];
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                            <?= strtoupper(substr($client['name'], 0, 2)) ?>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="/clients/viewClient/<?= $client['id'] ?>" class="fw-bold text-decoration-none">
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
                                                $industryClass = 'bg-primary';
                                                $industryIcon = 'fas fa-microchip';
                                                break;
                                            case 'manufacturing':
                                                $industryClass = 'bg-secondary';
                                                $industryIcon = 'fas fa-cogs';
                                                break;
                                            case 'healthcare':
                                                $industryClass = 'bg-success';
                                                $industryIcon = 'fas fa-heartbeat';
                                                break;
                                            case 'finance':
                                                $industryClass = 'bg-warning';
                                                $industryIcon = 'fas fa-university';
                                                break;
                                            case 'retail':
                                                $industryClass = 'bg-info';
                                                $industryIcon = 'fas fa-shopping-cart';
                                                break;
                                            default:
                                                $industryClass = 'bg-light text-dark';
                                                $industryIcon = 'fas fa-building';
                                        }
                                    ?>
                                    <span class="badge <?= $industryClass ?> text-white rounded-pill">
                                        <i class="<?= $industryIcon ?> me-1"></i>
                                        <?= htmlspecialchars($client['industry']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    if ($client['status'] == 'Active') {
                                        $statusClass = 'bg-success';
                                        $statusIcon = 'fas fa-check-circle';
                                    } elseif ($client['status'] == 'Inactive') {
                                        $statusClass = 'bg-danger';
                                        $statusIcon = 'fas fa-times-circle';
                                    } else {
                                        $statusClass = 'bg-secondary';
                                        $statusIcon = 'fas fa-minus-circle';
                                    }
                                ?>
                                <span class="badge <?= $statusClass ?> text-white rounded-pill">
                                    <i class="<?= $statusIcon ?> me-1"></i>
                                    <?= $client['status'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted">—</span>
                            </td>
                            <td class="pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-sm <?= $isClientPinned ? 'btn-primary' : 'btn-outline-secondary' ?> dashboard-pin-toggle"
                                        data-card-id="clients.client"
                                        data-client-id="<?= (int)$client['id'] ?>"
                                        data-widget-id="<?= htmlspecialchars($clientWidgetId) ?>"
                                        data-csrf-token="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                        aria-pressed="<?= $isClientPinned ? 'true' : 'false' ?>"
                                        title="<?= $isClientPinned ? 'Unpin client from dashboard' : 'Pin client to dashboard' ?>"
                                    >
                                        <i class="bi <?= $isClientPinned ? 'bi-pin-fill' : 'bi-pin-angle' ?>"></i>
                                    </button>
                                    <a href="/clients/viewClient/<?= $client['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (hasPermission('clients.assign_sites')): ?>
                                    <a href="/clients/assignSites/<?= $client['id'] ?>" class="btn btn-sm btn-outline-info" title="Assign Sites">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('clients.update')): ?>
                                    <a href="/clients/edit/<?= $client['id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('clients.delete')): ?>
                                    <a href="/clients/delete/<?= $client['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
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
            <div class="mb-4">
                <i class="fas fa-building text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
            </div>
            <h5 class="text-muted mt-3">No Clients Found</h5>
            <p class="text-muted">Get started by adding your first client to manage relationships</p>
            <?php if (hasPermission('clients.create')): ?>
            <a href="/clients/create" class="btn btn-primary mt-3">
                <i class="fas fa-plus me-2"></i>Add New Client
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

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



/* Modern Search Box */
.search-box {
    position: relative;
    width: 250px;
}

.search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    z-index: 2;
}

.search-box input {
    padding-left: 40px;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    height: 40px;
}

.search-box input:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

/* Avatar Styling */
.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 14px;
    font-weight: 600;
}

/* Modern Card Styling */
.card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.card-header {
    border-radius: 12px 12px 0 0;
    border-bottom: 1px solid #e3e6f0;
}

/* Table Styling */
.table th {
    font-weight: 600;
    font-size: 0.85rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table td {
    border-color: #e3e6f0;
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: #f8f9fc;
}

/* Badge Enhancements */
.badge.text-white {
    color: white !important;
}

.badge.bg-light.text-dark {
    color: #5a5c69 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced search functionality
    const searchInput = document.getElementById('clientSearch');
    const table = document.getElementById('clientsTable');
    
    if (searchInput && table) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update visible count in header
            const cardTitle = document.querySelector('.card-title');
            if (cardTitle) {
                if (searchTerm) {
                    cardTitle.textContent = `Clients (${visibleCount} found)`;
                } else {
                    cardTitle.textContent = 'All Clients';
                }
            }
        });
        
        // Clear search when input is cleared
        searchInput.addEventListener('input', function() {
            if (this.value === '') {
                const cardTitle = document.querySelector('.card-title');
                if (cardTitle) {
                    cardTitle.textContent = 'All Clients';
                }
            }
        });
    }
});
</script>

<?php require VIEWSPATH . '/partials/footer.php'; ?> 