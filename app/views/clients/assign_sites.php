<div class="container-fluid">
    <!-- Page Header -->
    <div class="bg-light rounded-3 p-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/clients" class="text-decoration-none">Clients</a></li>
                <li class="breadcrumb-item"><a href="/clients/viewClient/<?= $client['id'] ?>" class="text-decoration-none"><?= htmlspecialchars($client['name']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Assign Sites</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Assign Sites</h1>
                <p class="text-muted">Manage site assignments for <?= htmlspecialchars($client['name']) ?></p>
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
    
    <!-- Flash Messages -->
    <?php flash('client_success'); ?>
    <?php flash('client_error'); ?>
    
    <div class="row">
        <!-- Site Assignment Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-geo-alt text-primary me-2"></i>
                        Available Sites
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($all_sites)): ?>
                    <form action="/clients/assignSites/<?= $client['id'] ?>" method="post">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                        </th>
                                        <th>Site Name</th>
                                        <th>Location</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Relationship</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_sites as $site): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input site-checkbox" 
                                                   name="site_ids[]" value="<?= $site['id'] ?>"
                                                   <?= isset($assigned_sites[$site['id']]) ? 'checked' : '' ?>>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-icon rounded bg-light p-2 me-3">
                                                    <i class="bi bi-building text-primary"></i>
                                                </div>
                                                <div>
                                                    <a href="/sites/viewSite/<?= $site['id'] ?>" class="fw-semibold text-decoration-none">
                                                        <?= htmlspecialchars($site['name']) ?>
                                                    </a>
                                                    <?php if (!empty($site['site_code'])): ?>
                                                    <div class="small text-muted">Code: <?= htmlspecialchars($site['site_code']) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($site['location'] ?? '—') ?></td>
                                        <td>
                                            <span class="badge text-bg-secondary rounded-pill">
                                                <?= htmlspecialchars($site['type'] ?? 'Unknown') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                                $status = $site['status'] ?? 'Unknown';
                                                if ($status == 'Active') {
                                                    $statusClass = 'text-bg-success';
                                                    $statusIcon = 'bi-check-circle';
                                                } elseif ($status == 'Inactive') {
                                                    $statusClass = 'text-bg-danger';
                                                    $statusIcon = 'bi-x-circle';
                                                } else {
                                                    $statusClass = 'text-bg-secondary';
                                                    $statusIcon = 'bi-dash-circle';
                                                }
                                            ?>
                                            <span class="badge <?= $statusClass ?> rounded-pill">
                                                <i class="bi <?= $statusIcon ?> me-1"></i>
                                                <?= $status ?>
                                            </span>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm" 
                                                    name="relationship_types[<?= $site['id'] ?>]">
                                                <option value="Standard" <?= (isset($assigned_sites[$site['id']]) && $assigned_sites[$site['id']] == 'Standard') ? 'selected' : '' ?>>Standard</option>
                                                <option value="Primary" <?= (isset($assigned_sites[$site['id']]) && $assigned_sites[$site['id']] == 'Primary') ? 'selected' : '' ?>>Primary</option>
                                                <option value="Secondary" <?= (isset($assigned_sites[$site['id']]) && $assigned_sites[$site['id']] == 'Secondary') ? 'selected' : '' ?>>Secondary</option>
                                                <option value="Temporary" <?= (isset($assigned_sites[$site['id']]) && $assigned_sites[$site['id']] == 'Temporary') ? 'selected' : '' ?>>Temporary</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="small text-muted">
                                <span id="selectedCount">0</span> of <?= count($all_sites) ?> sites selected
                            </div>
                            <div class="d-flex gap-2">
                                <a href="/clients/viewClient/<?= $client['id'] ?>" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg"></i> Update Site Assignments
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-muted">No Sites Available</h5>
                        <p class="text-muted">There are no sites in the system to assign to this client.</p>
                        <a href="/sites/create" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Create First Site
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Client Info & Help -->
        <div class="col-lg-4">
            <!-- Client Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-person-circle text-primary me-2"></i>
                        Client Information
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="row small mb-0">
                        <dt class="col-5">Name:</dt>
                        <dd class="col-7"><?= htmlspecialchars($client['name']) ?></dd>
                        
                        <dt class="col-5">Contact:</dt>
                        <dd class="col-7">
                            <?= !empty($client['contact_person']) ? htmlspecialchars($client['contact_person']) : '<span class="text-muted">—</span>' ?>
                        </dd>
                        
                        <dt class="col-5">Industry:</dt>
                        <dd class="col-7">
                            <?= !empty($client['industry']) ? htmlspecialchars($client['industry']) : '<span class="text-muted">—</span>' ?>
                        </dd>
                        
                        <dt class="col-5">Status:</dt>
                        <dd class="col-7">
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
                        </dd>
                    </dl>
                </div>
            </div>
            
            <!-- Help Information -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-info-circle text-primary me-2"></i>
                        Site Assignment Guide
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-semibold">Relationship Types</h6>
                        <ul class="small text-muted mb-0">
                            <li><strong>Primary:</strong> Main site for this client</li>
                            <li><strong>Secondary:</strong> Additional important site</li>
                            <li><strong>Standard:</strong> Regular site assignment</li>
                            <li><strong>Temporary:</strong> Short-term assignment</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="fw-semibold">Quick Actions</h6>
                        <ul class="small text-muted mb-0">
                            <li>Use "Select All" to quickly assign all sites</li>
                            <li>Choose appropriate relationship types</li>
                            <li>Uncheck sites to remove assignments</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h6 class="fw-semibold">Best Practices</h6>
                        <ul class="small text-muted mb-0">
                            <li>Assign only relevant sites to clients</li>
                            <li>Set clear relationship types</li>
                            <li>Review assignments regularly</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const siteCheckboxes = document.querySelectorAll('.site-checkbox');
    const selectedCountSpan = document.getElementById('selectedCount');
    
    // Update selected count
    function updateSelectedCount() {
        const checkedCount = document.querySelectorAll('.site-checkbox:checked').length;
        selectedCountSpan.textContent = checkedCount;
    }
    
    // Select/Deselect all functionality
    selectAllCheckbox.addEventListener('change', function() {
        siteCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedCount();
    });
    
    // Update select all checkbox state when individual checkboxes change
    siteCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const totalCheckboxes = siteCheckboxes.length;
            const checkedCheckboxes = document.querySelectorAll('.site-checkbox:checked').length;
            
            if (checkedCheckboxes === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (checkedCheckboxes === totalCheckboxes) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
            }
            
            updateSelectedCount();
        });
    });
    
    // Initial count update
    updateSelectedCount();
    
    // Update select all checkbox initial state
    const totalCheckboxes = siteCheckboxes.length;
    const checkedCheckboxes = document.querySelectorAll('.site-checkbox:checked').length;
    
    if (checkedCheckboxes === totalCheckboxes && totalCheckboxes > 0) {
        selectAllCheckbox.checked = true;
    } else if (checkedCheckboxes > 0) {
        selectAllCheckbox.indeterminate = true;
    }
});
</script> 