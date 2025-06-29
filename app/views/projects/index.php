<?php include_once VIEWSPATH . '/inc/header.php'; ?>

<style>
    .btn-icon {
        width: 38px;
        height: 38px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="container-fluid px-4">
    <!-- Header section with title, filters and actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h2 mb-1">Projects</h1>
            <p class="text-muted d-none d-md-block">Manage and track all your projects</p>
        </div>
        
        <div class="d-flex flex-column flex-sm-row gap-2 align-items-center">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
                    <li><a class="dropdown-item active" href="#">All Projects</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">By Status</h6></li>
                    <li><a class="dropdown-item" href="#" data-filter="Active">Active</a></li>
                    <li><a class="dropdown-item" href="#" data-filter="On Hold">On Hold</a></li>
                    <li><a class="dropdown-item" href="#" data-filter="Completed">Completed</a></li>
                    <li><a class="dropdown-item" href="#" data-filter="Cancelled">Cancelled</a></li>
                </ul>
            </div>
            
            <div class="input-group" style="height: 38px;">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="projectSearch" placeholder="Search projects..." aria-label="Search projects">
            </div>
            
            <a href="/projects/create" class="btn btn-primary btn-icon" aria-label="Create New Project" title="Create New Project">
                <i class="bi bi-plus-lg"></i>
            </a>
        </div>
    </div>
    
    <!-- Flash message container -->
    <div id="flash-message"></div>
    
    <!-- Projects display section -->
    <div class="row">
        <div class="col-12">
            <?php if (empty($projects)) : ?>
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-kanban display-1 text-muted mb-3" style="font-size: 5rem;"></i>
                        <h3 class="text-muted mb-3">No Projects Found</h3>
                        <p class="text-muted mb-4">Get started by creating your first project</p>
                        <a href="/projects/create" class="btn btn-primary px-4 py-2">
                            <i class="bi bi-plus-lg me-2"></i> Create Your First Project
                        </a>
                    </div>
                </div>
            <?php else : ?>
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">All Projects</h5>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary active view-toggle" data-view="grid">
                                    <i class="bi bi-grid-3x3-gap"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary view-toggle" data-view="list">
                                    <i class="bi bi-list-ul"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Grid View (Default) -->
                    <div class="card-body view-container" id="grid-view">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php foreach ($projects as $project) : ?>
                                <div class="col project-item" data-status="<?= $project->status ?>">
                                    <div class="card h-100 border-0 shadow-sm hover-shadow">
                                        <?php 
                                        $statusClass = 'bg-secondary';
                                        $statusColor = 'secondary';
                                        if ($project->status === 'Active') {
                                            $statusClass = 'bg-primary';
                                            $statusColor = 'primary';
                                        } elseif ($project->status === 'Completed') {
                                            $statusClass = 'bg-success';
                                            $statusColor = 'success';
                                        } elseif ($project->status === 'On Hold') {
                                            $statusClass = 'bg-warning';
                                            $statusColor = 'warning';
                                        } elseif ($project->status === 'Cancelled') {
                                            $statusClass = 'bg-danger';
                                            $statusColor = 'danger';
                                        }
                                        ?>
                                        <div class="card-header d-flex justify-content-between align-items-center py-3 border-0 <?= $statusClass ?> bg-opacity-10">
                                            <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($project->status) ?></span>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link text-dark" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="/projects/viewProject/<?= $project->id ?>">
                                                        <i class="bi bi-eye me-2 text-primary"></i> View Details
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="/projects/edit/<?= $project->id ?>">
                                                        <i class="bi bi-pencil me-2 text-secondary"></i> Edit Project
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $project->id ?>">
                                                        <i class="bi bi-trash me-2"></i> Delete Project
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a href="/projects/viewProject/<?= $project->id ?>" class="text-decoration-none text-dark stretched-link">
                                                    <?= htmlspecialchars($project->title) ?>
                                                </a>
                                            </h5>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="text-muted small">
                                                    <i class="bi bi-calendar3 me-1"></i>
                                                    <?= date('M d', strtotime($project->start_date)) ?> - 
                                                    <?= $project->end_date ? date('M d, Y', strtotime($project->end_date)) : 'Ongoing' ?>
                                                </span>
                                            </div>
                                            <?php 
                                            $progress = 0;
                                            if (!empty($project->task_count) && $project->task_count > 0) {
                                                $progress = ($project->completed_tasks / $project->task_count) * 100;
                                            }
                                            ?>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="text-<?= $statusColor ?> fw-medium small">
                                                    <?= round($progress) ?>% Complete
                                                </div>
                                                <div class="text-muted small">
                                                    <?= $project->completed_tasks ?? 0 ?>/<?= $project->task_count ?? 0 ?> tasks
                                                </div>
                                            </div>
                                            <div class="progress mt-2" style="height: 6px;">
                                                <div class="progress-bar bg-<?= $statusColor ?>" role="progressbar" 
                                                    style="width: <?= $progress ?>%;" 
                                                    aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-white border-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="bi bi-person me-1"></i> <?= htmlspecialchars($project->created_by) ?>
                                                </small>
                                                <div>
                                                    <a href="/projects/viewProject/<?= $project->id ?>" class="btn btn-sm btn-outline-primary me-1">
                                                        Details
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- List View (Alternative) -->
                    <div class="card-body view-container d-none" id="list-view">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Progress</th>
                                        <th>Dates</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projects as $project) : ?>
                                        <tr class="project-item" data-status="<?= $project->status ?>">
                                            <td>
                                                <a href="/projects/viewProject/<?= $project->id ?>" class="fw-medium text-decoration-none text-dark">
                                                    <?= htmlspecialchars($project->title) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php 
                                                $statusClass = 'bg-secondary';
                                                if ($project->status === 'Active') $statusClass = 'bg-primary';
                                                if ($project->status === 'Completed') $statusClass = 'bg-success';
                                                if ($project->status === 'On Hold') $statusClass = 'bg-warning';
                                                if ($project->status === 'Cancelled') $statusClass = 'bg-danger';
                                                ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= htmlspecialchars($project->status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                $progress = 0;
                                                if (!empty($project->task_count) && $project->task_count > 0) {
                                                    $progress = ($project->completed_tasks / $project->task_count) * 100;
                                                }
                                                ?>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1" style="height: 8px; width: 100px;">
                                                        <div class="progress-bar <?= $statusClass ?>" role="progressbar" 
                                                            style="width: <?= $progress ?>%;" 
                                                            aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <span class="text-muted small"><?= round($progress) ?>%</span>
                                                </div>
                                                <small class="text-muted">
                                                    <?= $project->completed_tasks ?? 0 ?>/<?= $project->task_count ?? 0 ?> tasks
                                                </small>
                                            </td>
                                            <td>
                                                <div class="small"><?= date('M d, Y', strtotime($project->start_date)) ?></div>
                                                <div class="small text-muted"><?= $project->end_date ? date('M d, Y', strtotime($project->end_date)) : 'Ongoing' ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($project->created_by) ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/projects/viewProject/<?= $project->id ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="/projects/edit/<?= $project->id ?>" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            data-bs-toggle="modal" data-bs-target="#deleteModal<?= $project->id ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Delete Modals -->
    <?php foreach ($projects as $project) : ?>
        <div class="modal fade" id="deleteModal<?= $project->id ?>" tabindex="-1" 
             aria-labelledby="deleteModalLabel<?= $project->id ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel<?= $project->id ?>">
                            Confirm Delete
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <p class="text-center">
                            Are you sure you want to delete the project 
                            <strong><?= htmlspecialchars($project->title) ?></strong>?
                        </p>
                        <div class="alert alert-danger mt-3 mb-0">
                            <i class="bi bi-info-circle me-2"></i> 
                            This will permanently delete the project and all associated tasks.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form action="/projects/delete/<?= $project->id ?>" method="post">
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-1"></i> Delete Project
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- JavaScript for search and filtering -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle between grid and list views
    const viewToggles = document.querySelectorAll('.view-toggle');
    const viewContainers = document.querySelectorAll('.view-container');
    
    viewToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const view = this.getAttribute('data-view');
            
            // Update active toggle button
            viewToggles.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Show the selected view
            viewContainers.forEach(container => {
                container.classList.add('d-none');
            });
            document.getElementById(view + '-view').classList.remove('d-none');
        });
    });
    
    // Project search functionality
    const searchInput = document.getElementById('projectSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const projectItems = document.querySelectorAll('.project-item');
            
            projectItems.forEach(item => {
                const projectTitle = item.querySelector('.card-title, a').textContent.toLowerCase();
                if (projectTitle.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
    
    // Filter dropdown functionality
    const filterItems = document.querySelectorAll('[data-filter]');
    filterItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const filterValue = this.getAttribute('data-filter');
            const projectItems = document.querySelectorAll('.project-item');
            
            // Update active class
            document.querySelectorAll('.dropdown-item').forEach(item => {
                item.classList.remove('active');
            });
            this.classList.add('active');
            
            // Update filter label
            document.getElementById('filterDropdown').textContent = filterValue || 'All Projects';
            
            // Apply filter
            projectItems.forEach(item => {
                if (!filterValue || item.getAttribute('data-status') === filterValue) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
    
    // "All Projects" filter
    const allProjectsFilter = document.querySelector('.dropdown-item.active');
    if (allProjectsFilter) {
        allProjectsFilter.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.project-item').forEach(item => {
                item.style.display = '';
            });
            document.getElementById('filterDropdown').textContent = 'Filter';
            
            // Update active class
            document.querySelectorAll('.dropdown-item').forEach(item => {
                item.classList.remove('active');
            });
            this.classList.add('active');
        });
    }
});
</script>

<!-- Add this stylesheet to the header -->
<style>
.hover-shadow {
    transition: all 0.3s ease;
}
.hover-shadow:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.bg-opacity-10 {
    --bs-bg-opacity: 0.1;
}
.stretched-link::after {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 1;
    content: "";
}
</style> 