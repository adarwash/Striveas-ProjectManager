<div class="container-fluid">
    <!-- Page Header -->
    <div class="bg-light rounded-3 p-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/clients" class="text-decoration-none">Clients</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($client['name']) ?></li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1"><?= htmlspecialchars($client['name']) ?></h1>
                <p class="text-muted">Client details and site assignments</p>
            </div>
            <div class="d-flex gap-2">
                <?php if (hasPermission('clients.assign_sites')): ?>
                <a href="/clients/assignSites/<?= $client['id'] ?>" class="btn btn-outline-info">
                    <i class="bi bi-geo-alt"></i> Manage Sites
                </a>
                <?php endif; ?>
                <?php if (hasPermission('clients.update')): ?>
                <a href="/clients/edit/<?= $client['id'] ?>" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit Client
                </a>
                <?php endif; ?>
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
        <!-- Client Information -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-circle text-primary me-2"></i>
                        Client Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Client Name:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($client['name']) ?></dd>
                                
                                <dt class="col-sm-4">Contact Person:</dt>
                                <dd class="col-sm-8">
                                    <?= !empty($client['contact_person']) ? htmlspecialchars($client['contact_person']) : '<span class="text-muted">—</span>' ?>
                                </dd>
                                
                                <dt class="col-sm-4">Email:</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($client['email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($client['email']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($client['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </dd>
                                
                                <dt class="col-sm-4">Phone:</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($client['phone'])): ?>
                                        <a href="tel:<?= htmlspecialchars($client['phone']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($client['phone']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Industry:</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($client['industry'])): ?>
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
                                </dd>
                                
                                <dt class="col-sm-4">Status:</dt>
                                <dd class="col-sm-8">
                                    <?php 
                                        if ($client['status'] == 'Active') {
                                            $statusClass = 'text-bg-success';
                                            $statusIcon = 'bi-check-circle';
                                        } elseif ($client['status'] == 'Inactive') {
                                            $statusClass = 'text-bg-danger';
                                            $statusIcon = 'bi-x-circle';
                                        } elseif ($client['status'] == 'Prospect') {
                                            $statusClass = 'text-bg-warning';
                                            $statusIcon = 'bi-clock';
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
                                
                                <dt class="col-sm-4">Created:</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($client['created_at'])): ?>
                                        <?= date('M j, Y', strtotime($client['created_at'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </dd>
                                
                                <dt class="col-sm-4">Last Updated:</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($client['updated_at'])): ?>
                                        <?= date('M j, Y', strtotime($client['updated_at'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    <?php if (!empty($client['address'])): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <dt>Address:</dt>
                            <dd class="mt-1"><?= nl2br(htmlspecialchars($client['address'])) ?></dd>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($client['notes'])): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <dt>Notes:</dt>
                            <dd class="mt-1"><?= nl2br(htmlspecialchars($client['notes'])) ?></dd>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Assigned Sites -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-geo-alt text-primary me-2"></i>
                            Assigned Sites
                        </h5>
                        <?php if (hasPermission('clients.assign_sites')): ?>
                        <a href="/clients/assignSites/<?= $client['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-lg"></i> Manage Sites
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($sites)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Site Name</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Relationship</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sites as $site): ?>
                                <tr>
                                    <td>
                                        <a href="/sites/viewSite/<?= $site['id'] ?>" class="fw-semibold text-decoration-none">
                                            <?= htmlspecialchars($site['name']) ?>
                                        </a>
                                        <?php if (!empty($site['site_code'])): ?>
                                        <div class="small text-muted">Code: <?= htmlspecialchars($site['site_code']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($site['location'] ?? '—') ?></td>
                                    <td>
                                        <span class="badge text-bg-secondary rounded-pill">
                                            <?= htmlspecialchars($site['type'] ?? 'Unknown') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-info rounded-pill">
                                            <?= htmlspecialchars($site['relationship_type'] ?? 'Standard') ?>
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
                                        <div class="d-flex justify-content-end">
                                            <a href="/sites/viewSite/<?= $site['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Site">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="bi bi-geo-alt text-muted" style="font-size: 2rem;"></i>
                        </div>
                        <h6 class="text-muted">No Sites Assigned</h6>
                        <p class="text-muted mb-3">This client doesn't have any sites assigned yet.</p>
                        <?php if (hasPermission('clients.assign_sites')): ?>
                        <a href="/clients/assignSites/<?= $client['id'] ?>" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Assign Sites
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightning text-primary me-2"></i>
                        Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if (hasPermission('clients.assign_sites')): ?>
                        <a href="/clients/assignSites/<?= $client['id'] ?>" class="btn btn-outline-info">
                            <i class="bi bi-geo-alt me-2"></i>Manage Sites
                        </a>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('clients.update')): ?>
                        <a href="/clients/edit/<?= $client['id'] ?>" class="btn btn-outline-warning">
                            <i class="bi bi-pencil me-2"></i>Edit Client
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($client['email'])): ?>
                        <a href="mailto:<?= htmlspecialchars($client['email']) ?>" class="btn btn-outline-primary">
                            <i class="bi bi-envelope me-2"></i>Send Email
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($client['phone'])): ?>
                        <a href="tel:<?= htmlspecialchars($client['phone']) ?>" class="btn btn-outline-success">
                            <i class="bi bi-telephone me-2"></i>Call Client
                        </a>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('clients.delete')): ?>
                        <hr>
                        <a href="/clients/delete/<?= $client['id'] ?>" class="btn btn-outline-danger">
                            <i class="bi bi-trash me-2"></i>Delete Client
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Client Stats -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-bar-chart text-primary me-2"></i>
                        Client Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="mb-0 text-primary"><?= count($sites) ?></h4>
                                <small class="text-muted">Sites</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="mb-0 text-info">—</h4>
                            <small class="text-muted">Projects</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 