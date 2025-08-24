<!-- Modern Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-map-marker-alt me-3"></i>Sites Management</h1>
        <p class="mb-0">Manage all company locations and facilities</p>
    </div>
    <div>
        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])): ?>
        <a href="/sites/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Site
        </a>
        <?php endif; ?>
    </div>
</div>
    
    <!-- Flash Messages -->
    <?php flash('site_success'); ?>
    <?php flash('site_error'); ?>
    
    <!-- Sites Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-map-marker-alt text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Total Sites</h6>
                        <h3 class="mt-2 mb-0"><?= count($sites) ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-check-circle text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Active Sites</h6>
                        <h3 class="mt-2 mb-0">
                            <?php 
                                $activeCount = 0;
                                foreach ($sites as $site) {
                                    if ($site['status'] == 'Active') $activeCount++;
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
                        <i class="fas fa-building text-info fs-4"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Headquarters</h6>
                        <h3 class="mt-2 mb-0">
                            <?php 
                                $hqCount = 0;
                                foreach ($sites as $site) {
                                    if ($site['type'] == 'Headquarters') $hqCount++;
                                }
                                echo $hqCount;
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
                        <i class="fas fa-users text-warning fs-4"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-0">Total Employees</h6>
                        <h3 class="mt-2 mb-0">
                            <?php
                                // This would typically come from the model
                                echo '—';
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sites List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">All Sites</h5>
                <div class="form-group has-search position-relative">
                    <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" class="form-control ps-4" placeholder="Search sites..." id="siteSearch">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($sites)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="sitesTable">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Employees</th>
                            <th>Clients</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sites as $site): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-icon rounded bg-light p-2 me-3">
                                        <i class="fas fa-building text-primary"></i>
                                    </div>
                                    <div>
                                        <a href="/sites/viewSite/<?= $site['id'] ?>" class="fw-semibold text-decoration-none">
                                            <?= $site['name'] ?>
                                        </a>
                                        <?php if(!empty($site['site_code'])): ?>
                                        <div class="small text-muted">Code: <?= $site['site_code'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?= $site['location'] ?></td>
                            <td>
                                <?php 
                                    $typeClass = '';
                                    $typeIcon = '';
                                    switch($site['type']) {
                                        case 'Headquarters':
                                            $typeClass = 'text-bg-primary';
                                            $typeIcon = 'bi-building';
                                            break;
                                        case 'Branch Office':
                                            $typeClass = 'text-bg-secondary';
                                            $typeIcon = 'bi-house-door';
                                            break;
                                        case 'Manufacturing':
                                            $typeClass = 'text-bg-info';
                                            $typeIcon = 'bi-gear';
                                            break;
                                        case 'Distribution':
                                            $typeClass = 'text-bg-warning';
                                            $typeIcon = 'bi-truck';
                                            break;
                                        case 'Retail':
                                            $typeClass = 'text-bg-success';
                                            $typeIcon = 'bi-shop';
                                            break;
                                        case 'Remote':
                                            $typeClass = 'text-bg-dark';
                                            $typeIcon = 'bi-laptop';
                                            break;
                                        default:
                                            $typeClass = 'text-bg-light text-dark';
                                            $typeIcon = 'bi-building';
                                    }
                                ?>
                                <span class="badge <?= $typeClass ?> rounded-pill">
                                    <i class="bi <?= $typeIcon ?> me-1"></i>
                                    <?= $site['type'] ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                    if ($site['status'] == 'Active') {
                                        $statusClass = 'text-bg-success';
                                        $statusIcon = 'bi-check-circle';
                                    } elseif ($site['status'] == 'Inactive') {
                                        $statusClass = 'text-bg-danger';
                                        $statusIcon = 'bi-x-circle';
                                    } elseif ($site['status'] == 'Under Construction') {
                                        $statusClass = 'text-bg-warning';
                                        $statusIcon = 'bi-exclamation-triangle';
                                    } else {
                                        $statusClass = 'text-bg-secondary';
                                        $statusIcon = 'bi-dash-circle';
                                    }
                                ?>
                                <span class="badge <?= $statusClass ?> rounded-pill">
                                    <i class="bi <?= $statusIcon ?> me-1"></i>
                                    <?= $site['status'] ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                    // Get employee count from the model
                                    $employeeCount = 0; // This would come from the model
                                ?>
                                <span class="badge rounded-pill bg-light text-dark border">
                                                                            <i class="fas fa-users me-1"></i>
                                    <?= $employeeCount ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                    // Get client count from the model
                                    $clientCount = 0; // This would come from the model
                                ?>
                                <span class="badge rounded-pill bg-light text-dark border">
                                                                            <i class="fas fa-building me-1"></i>
                                    <?= $clientCount ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/sites/viewSite/<?= $site['id'] ?>" class="btn btn-falcon-default" title="View">
                                                                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])): ?>
                                        <a href="/sites/edit/<?= $site['id'] ?>" class="btn btn-falcon-default" title="Edit">
                                                                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="/sites/assignEmployees/<?= $site['id'] ?>" class="btn btn-falcon-default" title="Assign Employees">
                                                                                            <i class="fas fa-users"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                        <button type="button" class="btn btn-falcon-default text-danger" 
                                                title="Delete" data-bs-toggle="modal" data-bs-target="#deleteSiteModal<?= $site['id'] ?>">
                                                                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Delete Confirmation Modal -->
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <div class="modal fade" id="deleteSiteModal<?= $site['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Site</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete <strong><?= $site['name'] ?></strong>?</p>
                                                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <a href="/sites/delete/<?= $site['id'] ?>" class="btn btn-danger">Delete Site</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-5 text-center">
                <div class="empty-state mb-3">
                    <i class="fas fa-building fs-1 text-muted"></i>
                </div>
                <h4>No sites found</h4>
                <p class="text-muted">There are no sites in the system yet.</p>
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])): ?>
                <a href="/sites/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Site
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Sites page uses global styles from app.css -->
<style>
/* Site-specific overrides */
.btn-falcon-default {
    border-color: #e3e6ed;
    background-color: #fff;
    color: #6c757d;
}
.btn-falcon-default:hover {
    background-color: #f9fafd;
    color: #212529;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
}
.empty-state {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background-color: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}
.has-search .form-control {
    padding-left: 2.375rem;
}
</style>

<script>
// Search functionality with more user-friendly behavior
document.getElementById('siteSearch').addEventListener('keyup', function() {
    var searchText = this.value.toLowerCase();
    var table = document.getElementById('sitesTable');
    var rows = table.getElementsByTagName('tr');
    var noResults = true;
    
    for (var i = 1; i < rows.length; i++) {
        var siteText = rows[i].textContent.toLowerCase();
        if (siteText.indexOf(searchText) > -1) {
            rows[i].style.display = "";
            noResults = false;
        } else {
            rows[i].style.display = "none";
        }
    }
    
    // Show/hide no results message
    var tbody = table.getElementsByTagName('tbody')[0];
    var existingNoResults = document.getElementById('noResultsMessage');
    
    if (noResults && searchText.length > 0) {
        if (!existingNoResults) {
            var noResultsRow = document.createElement('tr');
            noResultsRow.id = 'noResultsMessage';
            noResultsRow.innerHTML = '<td colspan="6" class="text-center py-4">' +
                                     '<i class="fas fa-search fs-4 text-muted mb-2"></i>' +
                                     '<p class="mb-0">No sites found matching <strong>"' + searchText + '"</strong></p>' +
                                     '<p class="text-muted">Try using different keywords</p>' +
                                     '</td>';
            tbody.appendChild(noResultsRow);
        }
    } else if (existingNoResults) {
        existingNoResults.remove();
    }
});

// Enable Bootstrap tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
});
</script> 