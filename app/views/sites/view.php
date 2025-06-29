<div class="container-fluid">
    <!-- Page Header with Background -->
    <div class="bg-light rounded-3 p-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/sites" class="text-decoration-none">Sites</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $site['name'] ?></li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1 class="h3 mb-1"><?= $site['name'] ?></h1>
                <p class="text-muted">
                    <i class="bi bi-geo-alt me-1"></i> <?= $site['location'] ?>
                    <?php 
                        $statusIcon = 'bi-circle-fill';
                        $statusClass = 'text-success';
                        if ($site['status'] !== 'Active') {
                            $statusClass = 'text-danger';
                        }
                    ?>
                    <span class="ms-3">
                        <i class="bi <?= $statusIcon ?> <?= $statusClass ?>" style="font-size: 10px;"></i>
                        <span class="ms-1"><?= $site['status'] ?></span>
                    </span>
                </p>
            </div>
            <div class="d-flex flex-wrap mt-2 mt-md-0 gap-2">
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])): ?>
                <a href="/sites/assignEmployees/<?= $site['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-people me-1"></i> Assign Employees
                </a>
                <a href="/sites/edit/<?= $site['id'] ?>" class="btn btn-light btn-icon">
                    <i class="bi bi-pencil"></i>
                </a>
                <?php endif; ?>
                <a href="/sites" class="btn btn-light btn-icon">
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Flash Messages -->
    <?php flash('site_success'); ?>
    <?php flash('site_error'); ?>
    
    <div class="row">
        <!-- Site Details -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0">Site Details</h5>
                    <?php if (!empty($site['site_code'])): ?>
                    <span class="badge bg-light text-dark border"><?= $site['site_code'] ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="d-flex mb-4">
                        <div class="avatar-lg rounded bg-light p-3 me-3 d-flex align-items-center justify-content-center">
                            <?php
                                $icon = 'bi-building';
                                if ($site['type'] === 'Headquarters') {
                                    $icon = 'bi-bank';
                                } elseif ($site['type'] === 'Branch Office') {
                                    $icon = 'bi-house-door';
                                } elseif ($site['type'] === 'Manufacturing') {
                                    $icon = 'bi-gear';
                                } elseif ($site['type'] === 'Distribution') {
                                    $icon = 'bi-truck';
                                } elseif ($site['type'] === 'Retail') {
                                    $icon = 'bi-shop';
                                }
                            ?>
                            <i class="bi <?= $icon ?> fs-1 text-primary"></i>
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                            <h5 class="mb-1"><?= $site['type'] ?></h5>
                            <span class="badge <?= ($site['status'] === 'Active') ? 'text-bg-success' : 'text-bg-danger' ?> rounded-pill">
                                <?= $site['status'] ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small">FULL ADDRESS</label>
                        <p class="mb-0"><?= $site['address'] ?? 'No address specified' ?></p>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Created</span>
                        <span><?= date('M j, Y', strtotime($site['created_at'])) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Last Updated</span>
                        <span><?= date('M j, Y', strtotime($site['updated_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Key Metrics -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Key Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                        <i class="bi bi-people text-primary"></i>
                                    </div>
                                    <h6 class="mb-0">Employees</h6>
                                </div>
                                <h3 class="mb-0"><?= count($employees) ?></h3>
                            </div>
                        </div>
                        
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-2 me-2">
                                        <i class="bi bi-briefcase text-success"></i>
                                    </div>
                                    <h6 class="mb-0">Projects</h6>
                                </div>
                                <h3 class="mb-0"><?= count($linked_projects) ?></h3>
                            </div>
                        </div>
                        
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle bg-info bg-opacity-10 p-2 me-2">
                                        <i class="bi bi-check2-square text-info"></i>
                                    </div>
                                    <h6 class="mb-0">Tasks</h6>
                                </div>
                                <h3 class="mb-0">—</h3>
                            </div>
                        </div>
                        
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-2">
                                        <i class="bi bi-lightning-charge text-warning"></i>
                                    </div>
                                    <h6 class="mb-0">Activity</h6>
                                </div>
                                <h3 class="mb-0">—</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Map Section -->
        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Location Map</h5>
                </div>
                <div class="card-body">
                    <div class="position-relative" style="height: 250px;">
                        <div class="bg-light rounded text-center p-4 h-100 d-flex flex-column justify-content-center align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 mb-3">
                                <i class="bi bi-geo-alt text-primary fs-3"></i>
                            </div>
                            <h6><?= $site['name'] ?></h6>
                            <p class="text-muted mb-0"><?= $site['address'] ?? 'No address specified' ?></p>
                            <?php if (!empty($site['address'])): ?>
                            <a href="https://maps.google.com/?q=<?= urlencode($site['address']) ?>" target="_blank" class="btn btn-sm btn-primary mt-3">
                                <i class="bi bi-map"></i> View on Google Maps
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Assigned Employees -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Employees</h5>
                <p class="text-muted small mb-0">People assigned to this site</p>
            </div>
            <div class="d-flex align-items-center">
                <div class="form-group has-search position-relative me-3">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" class="form-control form-control-sm ps-4" placeholder="Search employees..." id="employeeSearch">
                </div>
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])): ?>
                <a href="/sites/assignEmployees/<?= $site['id'] ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-people"></i> Manage Assignments
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($employees)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="employeesTable">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Contact</th>
                            <th>Assignment</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-circle me-3">
                                        <?php if (isset($employee['profile_picture']) && !empty($employee['profile_picture'])): ?>
                                            <img src="/uploads/profile_pictures/<?= $employee['profile_picture'] ?>" alt="Profile" class="avatar-img">
                                        <?php else: ?>
                                            <div class="avatar-initial"><?= substr($employee['full_name'], 0, 1) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <a href="/employees/viewEmployee/<?= $employee['user_id'] ?>" class="fw-semibold text-decoration-none">
                                            <?= $employee['full_name'] ?>
                                        </a>
                                        <div class="small text-muted">ID: <?= $employee['user_id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?= isset($employee['department']) ? $employee['department'] : '—' ?>
                            </td>
                            <td><?= ucfirst($employee['role']) ?></td>
                            <td>
                                <div class="d-flex flex-column">
                                    <div><i class="bi bi-envelope-fill text-muted me-1 small"></i> <?= $employee['email'] ?></div>
                                    <?php if (isset($employee['phone']) && !empty($employee['phone'])): ?>
                                    <div><i class="bi bi-telephone-fill text-muted me-1 small"></i> <?= $employee['phone'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if($employee['is_primary']): ?>
                                    <span class="badge text-bg-success rounded-pill">Primary Site</span>
                                    <?php else: ?>
                                    <span class="badge text-bg-secondary rounded-pill">Secondary Site</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="/employees/viewEmployee/<?= $employee['user_id'] ?>" class="btn btn-falcon-default" title="View Profile">
                                        <i class="bi bi-person"></i>
                                    </a>
                                    <a href="mailto:<?= $employee['email'] ?>" class="btn btn-falcon-default" title="Send Email">
                                        <i class="bi bi-envelope"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-5 text-center">
                <div class="empty-state mb-3">
                    <i class="bi bi-people fs-1 text-muted"></i>
                </div>
                <h4>No employees assigned</h4>
                <p class="text-muted">There are no employees assigned to this site yet.</p>
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])): ?>
                <a href="/sites/assignEmployees/<?= $site['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-people"></i> Assign Employees
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Linked Projects -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Linked Projects</h5>
                <p class="text-muted small mb-0">Projects associated with this site</p>
            </div>
            <div class="d-flex align-items-center">
                <div class="form-group has-search position-relative me-3">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" class="form-control form-control-sm ps-4" placeholder="Search projects..." id="projectSearch">
                </div>
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])): ?>
                <div class="btn-group">
                    <a href="/projects/create?site_id=<?= $site['id'] ?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg"></i> Create Project
                    </a>
                    <a href="/sites/linkProjects/<?= $site['id'] ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-link"></i> Link Existing
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($linked_projects)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="projectsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Project Name</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Timeline</th>
                            <th>Notes</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($linked_projects as $project): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-circle me-3 bg-primary bg-opacity-10">
                                        <div class="avatar-initial text-primary">
                                            <i class="bi bi-briefcase"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="/projects/viewProject/<?= $project['id'] ?>" class="fw-semibold text-decoration-none">
                                            <?= htmlspecialchars($project['title']) ?>
                                        </a>
                                        <?php if (!empty($project['department_id'])): ?>
                                        <div class="small text-muted">Dept ID: <?= $project['department_id'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php
                                $statusClass = 'bg-secondary';
                                if ($project['status'] === 'Active') $statusClass = 'bg-success';
                                if ($project['status'] === 'On Hold') $statusClass = 'bg-warning';
                                if ($project['status'] === 'Completed') $statusClass = 'bg-info';
                                if ($project['status'] === 'Cancelled') $statusClass = 'bg-danger';
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= $project['status'] ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars($project['created_by'] ?? 'Unknown') ?>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <small class="text-muted">Start: <?= date('M j, Y', strtotime($project['start_date'])) ?></small>
                                    <small class="text-muted">End: <?= date('M j, Y', strtotime($project['end_date'])) ?></small>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($project['notes'])): ?>
                                <span class="d-inline-block text-truncate" style="max-width: 150px;" data-bs-toggle="tooltip" title="<?= htmlspecialchars($project['notes']) ?>">
                                    <?= htmlspecialchars($project['notes']) ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="/projects/viewProject/<?= $project['id'] ?>" class="btn btn-sm btn-light" title="View Project">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-4 text-center">
                <div class="py-5">
                    <div class="mb-3">
                        <i class="bi bi-briefcase display-4 text-muted"></i>
                    </div>
                    <h5>No Projects Linked</h5>
                    <p class="text-muted">There are no projects linked to this site yet.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Assigned Clients -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Clients</h5>
                <p class="text-muted small mb-0">Organizations associated with this site</p>
            </div>
            <div class="d-flex align-items-center">
                <div class="form-group has-search position-relative me-3">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" class="form-control form-control-sm ps-4" placeholder="Search clients..." id="clientSearch">
                </div>
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])): ?>
                <a href="/sites/assignClients/<?= $site['id'] ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-building-add"></i> Manage Clients
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($clients)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="clientsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Client</th>
                            <th>Industry</th>
                            <th>Contact</th>
                            <th>Relationship</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-icon rounded bg-light p-2 me-3">
                                        <i class="bi bi-building text-primary"></i>
                                    </div>
                                    <div>
                                        <span class="fw-semibold"><?= $client['name'] ?></span>
                                        <?php if(!empty($client['notes'])): ?>
                                        <div class="small text-muted text-truncate" style="max-width: 200px;"><?= $client['notes'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?= $client['industry'] ?? 'N/A' ?></td>
                            <td>
                                <?php if(!empty($client['contact_person']) || !empty($client['email'])): ?>
                                <div class="d-flex flex-column">
                                    <?php if(!empty($client['contact_person'])): ?>
                                    <span><?= $client['contact_person'] ?></span>
                                    <?php endif; ?>
                                    <?php if(!empty($client['email'])): ?>
                                    <span class="small text-muted"><?= $client['email'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <span class="text-muted">No contact info</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $relationshipClass = 'bg-secondary';
                                    $relationshipIcon = 'bi-building';
                                    
                                    switch($client['relationship_type']) {
                                        case 'Primary':
                                            $relationshipClass = 'bg-primary';
                                            $relationshipIcon = 'bi-star-fill';
                                            break;
                                        case 'Secondary':
                                            $relationshipClass = 'bg-info';
                                            $relationshipIcon = 'bi-star';
                                            break;
                                        case 'Partner':
                                            $relationshipClass = 'bg-success';
                                            $relationshipIcon = 'bi-people';
                                            break;
                                        case 'Vendor':
                                            $relationshipClass = 'bg-warning';
                                            $relationshipIcon = 'bi-shop';
                                            break;
                                        case 'Prospect':
                                            $relationshipClass = 'bg-info';
                                            $relationshipIcon = 'bi-lightbulb';
                                            break;
                                        case 'Former':
                                            $relationshipClass = 'bg-danger';
                                            $relationshipIcon = 'bi-x-circle';
                                            break;
                                    }
                                ?>
                                <span class="badge <?= $relationshipClass ?> rounded-pill">
                                    <i class="bi <?= $relationshipIcon ?> me-1"></i>
                                    <?= $client['relationship_type'] ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                    $statusClass = 'bg-secondary';
                                    if ($client['status'] === 'Active') {
                                        $statusClass = 'bg-success';
                                    } elseif ($client['status'] === 'Inactive') {
                                        $statusClass = 'bg-danger';
                                    } elseif ($client['status'] === 'Prospect') {
                                        $statusClass = 'bg-warning';
                                    }
                                ?>
                                <span class="badge <?= $statusClass ?> rounded-pill"><?= $client['status'] ?></span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <?php if (!empty($client['email'])): ?>
                                    <a href="mailto:<?= $client['email'] ?>" class="btn btn-falcon-default" title="Send Email">
                                        <i class="bi bi-envelope"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (!empty($client['phone'])): ?>
                                    <a href="tel:<?= $client['phone'] ?>" class="btn btn-falcon-default" title="Call">
                                        <i class="bi bi-telephone"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])): ?>
                                    <button type="button" class="btn btn-falcon-default" 
                                            data-bs-toggle="modal" data-bs-target="#clientModal<?= $client['id'] ?>" title="View Details">
                                        <i class="bi bi-info-circle"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Client Details Modal -->
                                <div class="modal fade" id="clientModal<?= $client['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><?= $client['name'] ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6 class="fw-bold">Contact Information</h6>
                                                        <div class="mb-3">
                                                            <label class="text-muted small">CONTACT PERSON</label>
                                                            <div><?= $client['contact_person'] ?? 'Not specified' ?></div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="text-muted small">EMAIL</label>
                                                            <div><?= $client['email'] ?? 'Not specified' ?></div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="text-muted small">PHONE</label>
                                                            <div><?= $client['phone'] ?? 'Not specified' ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="fw-bold">Company Information</h6>
                                                        <div class="mb-3">
                                                            <label class="text-muted small">INDUSTRY</label>
                                                            <div><?= $client['industry'] ?? 'Not specified' ?></div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="text-muted small">ADDRESS</label>
                                                            <div><?= $client['address'] ?? 'Not specified' ?></div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="text-muted small">RELATIONSHIP WITH SITE</label>
                                                            <div>
                                                                <span class="badge <?= $relationshipClass ?>"><?= $client['relationship_type'] ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <?php if (!empty($client['notes'])): ?>
                                                <hr>
                                                <h6 class="fw-bold">Notes</h6>
                                                <p class="mb-0"><?= nl2br($client['notes']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <?php if (!empty($client['email'])): ?>
                                                <a href="mailto:<?= $client['email'] ?>" class="btn btn-primary">
                                                    <i class="bi bi-envelope me-1"></i> Contact
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-5 text-center">
                <div class="empty-state mb-3">
                    <i class="bi bi-building fs-1 text-muted"></i>
                </div>
                <h4>No clients assigned</h4>
                <p class="text-muted">There are no clients assigned to this site yet.</p>
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])): ?>
                <a href="/sites/assignClients/<?= $site['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-building-add"></i> Assign Clients
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.avatar-lg {
    width: 70px;
    height: 70px;
}
.avatar {
    position: relative;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    background-color: #e9ecef;
}
.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    background-color: #6c757d;
    color: #fff;
    font-weight: 600;
}
.avatar-circle {
    border-radius: 50%;
}
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
.btn-icon {
    width: 38px;
    height: 38px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
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
.form-control:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}
</style>

<script>
// Search functionality for employees
document.getElementById('employeeSearch')?.addEventListener('keyup', function() {
    var searchText = this.value.toLowerCase();
    var table = document.getElementById('employeesTable');
    var rows = table.getElementsByTagName('tr');
    var noResults = true;
    
    for (var i = 1; i < rows.length; i++) {
        var employeeText = rows[i].textContent.toLowerCase();
        if (employeeText.indexOf(searchText) > -1) {
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
                                    '<i class="bi bi-search fs-4 text-muted mb-2"></i>' +
                                    '<p class="mb-0">No employees found matching <strong>"' + searchText + '"</strong></p>' +
                                    '<p class="text-muted">Try using different keywords</p>' +
                                    '</td>';
            tbody.appendChild(noResultsRow);
        }
    } else if (existingNoResults) {
        existingNoResults.remove();
    }
});

// Search functionality for clients
document.getElementById('clientSearch')?.addEventListener('keyup', function() {
    var searchText = this.value.toLowerCase();
    var table = document.getElementById('clientsTable');
    var rows = table.getElementsByTagName('tr');
    var noResults = true;
    
    for (var i = 1; i < rows.length; i++) {
        var clientText = rows[i].textContent.toLowerCase();
        if (clientText.indexOf(searchText) > -1) {
            rows[i].style.display = "";
            noResults = false;
        } else {
            rows[i].style.display = "none";
        }
    }
    
    // Show/hide no results message
    var tbody = table.getElementsByTagName('tbody')[0];
    var existingNoResults = document.getElementById('clientNoResultsMessage');
    
    if (noResults && searchText.length > 0) {
        if (!existingNoResults) {
            var noResultsRow = document.createElement('tr');
            noResultsRow.id = 'clientNoResultsMessage';
            noResultsRow.innerHTML = '<td colspan="6" class="text-center py-4">' +
                                    '<i class="bi bi-search fs-4 text-muted mb-2"></i>' +
                                    '<p class="mb-0">No clients found matching <strong>"' + searchText + '"</strong></p>' +
                                    '<p class="text-muted">Try using different keywords</p>' +
                                    '</td>';
            tbody.appendChild(noResultsRow);
        }
    } else if (existingNoResults) {
        existingNoResults.remove();
    }
});

// Search functionality for projects
document.getElementById('projectSearch')?.addEventListener('keyup', function() {
    var searchText = this.value.toLowerCase();
    var table = document.getElementById('projectsTable');
    var rows = table.getElementsByTagName('tr');
    var noResults = true;
    
    for (var i = 1; i < rows.length; i++) {
        var projectText = rows[i].textContent.toLowerCase();
        if (projectText.indexOf(searchText) > -1) {
            rows[i].style.display = "";
            noResults = false;
        } else {
            rows[i].style.display = "none";
        }
    }
    
    // Show/hide no results message
    var tbody = table.getElementsByTagName('tbody')[0];
    var existingNoResults = document.getElementById('projectNoResultsMessage');
    
    if (noResults && searchText.length > 0) {
        if (!existingNoResults) {
            var noResultsRow = document.createElement('tr');
            noResultsRow.id = 'projectNoResultsMessage';
            noResultsRow.innerHTML = '<td colspan="6" class="text-center py-4">' +
                                    '<i class="bi bi-search fs-4 text-muted mb-2"></i>' +
                                    '<p class="mb-0">No projects found matching <strong>"' + searchText + '"</strong></p>' +
                                    '<p class="text-muted">Try using different keywords</p>' +
                                    '</td>';
            tbody.appendChild(noResultsRow);
        }
    } else if (existingNoResults) {
        existingNoResults.remove();
    }
});

// Enable Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script> 