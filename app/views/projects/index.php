<?php include_once VIEWSPATH . '/inc/header.php'; ?>

<!-- Modern Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-briefcase me-3"></i>Project Dashboard</h1>
        <p class="mb-0">Manage and track all your projects in one place</p>
    </div>
    <div>
        <button class="btn btn-primary me-2" onclick="refreshProjects()">
            <i class="fas fa-sync-alt me-2"></i>Refresh
        </button>
        <a href="/projects/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>New Project
        </a>
    </div>
</div>

    <!-- Project Statistics Overview -->
    <div class="row mb-4">
        <?php
        $totalProjects = count($projects);
        $activeProjects = count(array_filter($projects, function($p) { return $p->status === 'Active'; }));
        $completedProjects = count(array_filter($projects, function($p) { return $p->status === 'Completed'; }));
        $onHoldProjects = count(array_filter($projects, function($p) { return $p->status === 'On Hold'; }));
        $cancelledProjects = count(array_filter($projects, function($p) { return $p->status === 'Cancelled'; }));
        
        $totalTasks = array_sum(array_column($projects, 'task_count'));
        $completedTasks = array_sum(array_column($projects, 'completed_tasks'));
        $overallProgress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
        ?>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="modern-stats-card card-primary">
                <div class="stats-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value"><?= $totalProjects ?></div>
                    <div class="stats-label">Total Projects</div>
                    <div class="stats-progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="modern-stats-card card-success">
                <div class="stats-icon">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value"><?= $activeProjects ?></div>
                    <div class="stats-label">Active Projects</div>
                    <div class="stats-progress">
                        <div class="progress-bar" style="width: <?= $totalProjects > 0 ? ($activeProjects / $totalProjects) * 100 : 0 ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="modern-stats-card card-info">
                <div class="stats-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value"><?= $completedProjects ?></div>
                    <div class="stats-label">Completed Projects</div>
                    <div class="stats-progress">
                        <div class="progress-bar" style="width: <?= $totalProjects > 0 ? ($completedProjects / $totalProjects) * 100 : 0 ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="modern-stats-card card-warning">
                <div class="stats-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value"><?= number_format($overallProgress, 1) ?>%</div>
                    <div class="stats-label">Overall Progress</div>
                    <div class="stats-progress">
                        <div class="progress-bar" style="width: <?= $overallProgress ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Filters and Search -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-panel-card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="filter-header-info">
                            <h6 class="filter-title">
                                <i class="fas fa-filter me-2"></i>Filter & Search Projects
                            </h6>
                            <small class="filter-subtitle">Find and organize your projects efficiently</small>
                        </div>
                        <div class="filter-summary">
                            <span class="results-badge">
                                <i class="fas fa-chart-pie me-1"></i>
                                <?= $totalProjects ?> Total
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <div class="row align-items-center">
                        <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                            <div class="search-section">
                                <div class="search-box" style="position: relative;">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" class="form-control search-input" id="projectSearch" 
                                           placeholder="Search projects..." aria-label="Search projects">
                                    <button class="search-clear" id="clearSearch" style="display: none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8 col-md-6">
                            <div class="filter-section">
                                <label class="filter-label">Filter by Status</label>
                                <div class="filter-buttons">
                                    <button class="filter-btn active" data-filter="all">
                                        <div class="filter-btn-content">
                                            <i class="fas fa-th-large"></i>
                                            <span class="filter-text">All Projects</span>
                                            <span class="filter-badge"><?= $totalProjects ?></span>
                                        </div>
                                    </button>
                                    <button class="filter-btn filter-btn-active" data-filter="Active">
                                        <div class="filter-btn-content">
                                            <i class="fas fa-play-circle"></i>
                                            <span class="filter-text">Active</span>
                                            <span class="filter-badge"><?= $activeProjects ?></span>
                                        </div>
                                    </button>
                                    <button class="filter-btn filter-btn-completed" data-filter="Completed">
                                        <div class="filter-btn-content">
                                            <i class="fas fa-check-circle"></i>
                                            <span class="filter-text">Completed</span>
                                            <span class="filter-badge"><?= $completedProjects ?></span>
                                        </div>
                                    </button>
                                    <button class="filter-btn filter-btn-hold" data-filter="On Hold">
                                        <div class="filter-btn-content">
                                            <i class="fas fa-pause-circle"></i>
                                            <span class="filter-text">On Hold</span>
                                            <span class="filter-badge"><?= $onHoldProjects ?></span>
                                        </div>
                                    </button>
                                    <button class="filter-btn filter-btn-cancelled" data-filter="Cancelled">
                                        <div class="filter-btn-content">
                                            <i class="fas fa-times-circle"></i>
                                            <span class="filter-text">Cancelled</span>
                                            <span class="filter-badge"><?= $cancelledProjects ?></span>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Content -->
    <div class="row">
        <div class="col-12">
            <?php if (empty($projects)) : ?>
                <div class="empty-state-card">
                    <div class="empty-state-content">
                        <div class="empty-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h3>No Projects Yet</h3>
                        <p>Get started by creating your first project and begin managing your work efficiently.</p>
                        <a href="/projects/create" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Create Your First Project
                        </a>
                    </div>
                </div>
            <?php else : ?>
                <div class="projects-container">
                    <!-- View Toggle -->
                    <div class="view-controls mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="results-info">
                                <span class="results-count"><?= count($projects) ?></span> projects found
                            </div>
                            <div class="view-toggle-group">
                                <button class="view-toggle active" data-view="grid">
                                    <i class="fas fa-th-large"></i>
                                </button>
                                <button class="view-toggle" data-view="list">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Grid View -->
                    <div class="view-container" id="grid-view">
                        <div class="projects-grid">
                            <?php foreach ($projects as $project) : ?>
                                <div class="project-card" data-status="<?= $project->status ?>">
                                    <div class="project-card-header">
                                        <div class="project-status">
                                            <?php 
                                            $statusConfig = [
                                                'Active' => ['class' => 'status-active', 'icon' => 'fas fa-play-circle'],
                                                'Completed' => ['class' => 'status-completed', 'icon' => 'fas fa-check-circle'],
                                                'On Hold' => ['class' => 'status-on-hold', 'icon' => 'fas fa-pause-circle'],
                                                'Cancelled' => ['class' => 'status-cancelled', 'icon' => 'fas fa-times-circle']
                                            ];
                                            $config = $statusConfig[$project->status] ?? ['class' => 'status-default', 'icon' => 'fas fa-circle'];
                                            ?>
                                            <span class="status-badge <?= $config['class'] ?>">
                                                <i class="<?= $config['icon'] ?> me-1"></i>
                                                <?= htmlspecialchars($project->status) ?>
                                            </span>
                                        </div>
                                        <div class="project-actions">
                                            <div class="dropdown">
                                                <button class="action-btn" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="/projects/viewProject/<?= $project->id ?>">
                                                            <i class="fas fa-eye me-2"></i>View Details
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="/projects/edit/<?= $project->id ?>">
                                                            <i class="fas fa-edit me-2"></i>Edit Project
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $project->id ?>">
                                                            <i class="fas fa-trash me-2"></i>Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="project-card-body">
                                        <h5 class="project-title">
                                            <a href="/projects/viewProject/<?= $project->id ?>" class="project-link">
                                                <?= htmlspecialchars($project->title) ?>
                                            </a>
                                        </h5>
                                        
                                        <div class="project-meta">
                                            <div class="meta-item">
                                                <i class="fas fa-calendar-alt"></i>
                                                <span><?= date('M d', strtotime($project->start_date)) ?> - 
                                                      <?= $project->end_date ? date('M d, Y', strtotime($project->end_date)) : 'Ongoing' ?></span>
                                            </div>
                                            <div class="meta-item">
                                                <i class="fas fa-user"></i>
                                                <span><?= htmlspecialchars($project->created_by) ?></span>
                                            </div>
                                        </div>
                                        
                                        <?php 
                                        $progress = 0;
                                        if (!empty($project->task_count) && $project->task_count > 0) {
                                            $progress = ($project->completed_tasks / $project->task_count) * 100;
                                        }
                                        ?>
                                        
                                        <div class="project-progress">
                                            <div class="progress-header">
                                                <span class="progress-label">Progress</span>
                                                <span class="progress-percentage"><?= round($progress) ?>%</span>
                                            </div>
                                            <div class="progress-bar-container">
                                                <div class="progress-bar-fill" style="width: <?= $progress ?>%"></div>
                                            </div>
                                            <div class="progress-footer">
                                                <span class="task-count"><?= $project->completed_tasks ?? 0 ?>/<?= $project->task_count ?? 0 ?> tasks completed</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="project-card-footer">
                                        <a href="/projects/viewProject/<?= $project->id ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-arrow-right me-1"></i>View Details
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- List View -->
                    <div class="view-container d-none" id="list-view">
                        <div class="projects-list-card">
                            <div class="table-responsive">
                                <table class="table modern-table">
                                    <thead>
                                        <tr>
                                            <th>Project</th>
                                            <th>Status</th>
                                            <th>Progress</th>
                                            <th>Timeline</th>
                                            <th>Created By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($projects as $project) : ?>
                                            <tr class="project-row" data-status="<?= $project->status ?>">
                                                <td>
                                                    <div class="project-info">
                                                        <h6 class="project-name">
                                                            <a href="/projects/viewProject/<?= $project->id ?>" class="project-link">
                                                                <?= htmlspecialchars($project->title) ?>
                                                            </a>
                                                        </h6>
                                                        <div class="project-id">ID: <?= $project->id ?></div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $statusConfig = [
                                                        'Active' => ['class' => 'status-active', 'icon' => 'fas fa-play-circle'],
                                                        'Completed' => ['class' => 'status-completed', 'icon' => 'fas fa-check-circle'],
                                                        'On Hold' => ['class' => 'status-on-hold', 'icon' => 'fas fa-pause-circle'],
                                                        'Cancelled' => ['class' => 'status-cancelled', 'icon' => 'fas fa-times-circle']
                                                    ];
                                                    $config = $statusConfig[$project->status] ?? ['class' => 'status-default', 'icon' => 'fas fa-circle'];
                                                    ?>
                                                    <span class="status-badge <?= $config['class'] ?>">
                                                        <i class="<?= $config['icon'] ?> me-1"></i>
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
                                                    <div class="progress-display">
                                                        <div class="progress-bar-small">
                                                            <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                                                        </div>
                                                        <span class="progress-text"><?= round($progress) ?>%</span>
                                                    </div>
                                                    <div class="task-summary">
                                                        <?= $project->completed_tasks ?? 0 ?>/<?= $project->task_count ?? 0 ?> tasks
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="timeline-info">
                                                        <div class="start-date"><?= date('M d, Y', strtotime($project->start_date)) ?></div>
                                                        <div class="end-date"><?= $project->end_date ? date('M d, Y', strtotime($project->end_date)) : 'Ongoing' ?></div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="user-info">
                                                        <i class="fas fa-user-circle me-1"></i>
                                                        <?= htmlspecialchars($project->created_by) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="/projects/viewProject/<?= $project->id ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="/projects/edit/<?= $project->id ?>" class="btn btn-sm btn-outline-secondary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $project->id ?>">
                                                            <i class="fas fa-trash"></i>
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
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Enhanced Delete Modals -->
<?php foreach ($projects as $project) : ?>
    <div class="modal fade" id="deleteModal<?= $project->id ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-center">
                        Are you sure you want to delete the project 
                        <strong><?= htmlspecialchars($project->title) ?></strong>?
                    </p>
                    <div class="alert alert-danger">
                        <i class="fas fa-info-circle me-2"></i> 
                        This action cannot be undone. This will permanently delete the project and all associated tasks.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <form action="/projects/delete/<?= $project->id ?>" method="post" style="display: inline;">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete Project
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Projects specific styles -->
<style>
/* Project-specific overrides and custom styles */
.search-section .form-control {
    border: 2px solid #e2e8f0 !important;
    border-radius: 8px !important;
}

.search-section .form-control:focus {
    border-color: #667eea !important;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View toggle functionality
    const viewToggles = document.querySelectorAll('.view-toggle');
    const viewContainers = document.querySelectorAll('.view-container');
    
    viewToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const view = this.getAttribute('data-view');
            
            // Update active toggle
            viewToggles.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Show selected view
            viewContainers.forEach(container => {
                container.classList.add('d-none');
            });
            document.getElementById(view + '-view').classList.remove('d-none');
        });
    });
    
    // Enhanced search functionality
    const searchInput = document.getElementById('projectSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const projectItems = document.querySelectorAll('.project-card, .project-row');
            let visibleCount = 0;
            
            // Show/hide clear button
            if (searchTerm.length > 0) {
                clearSearchBtn.style.display = 'block';
            } else {
                clearSearchBtn.style.display = 'none';
            }
            
            projectItems.forEach(item => {
                const titleElement = item.querySelector('.project-title a, .project-name a');
                const projectTitle = titleElement ? titleElement.textContent.toLowerCase() : '';
                
                if (projectTitle.includes(searchTerm)) {
                    item.classList.remove('hidden');
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                    item.style.display = 'none';
                }
            });
            
            // Update results count
            updateResultsCount(visibleCount);
        });
        
        // Add placeholder animation
        let placeholderIndex = 0;
        const placeholders = [
            'Search by project name...',
            'Try "Website", "App", "Portal"...',
            'Find your project quickly...',
            'Search across all projects...'
        ];
        
        setInterval(() => {
            if (searchInput.value === '') {
                searchInput.placeholder = placeholders[placeholderIndex];
                placeholderIndex = (placeholderIndex + 1) % placeholders.length;
            }
        }, 3000);
    }
    
    // Clear search functionality
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearSearchBtn.style.display = 'none';
            
            // Reset all items
            const projectItems = document.querySelectorAll('.project-card, .project-row');
            projectItems.forEach(item => {
                item.classList.remove('hidden');
                item.style.display = '';
            });
            
            // Update results count
            updateResultsCount(projectItems.length);
            
            // Focus back on search input
            searchInput.focus();
        });
    }
    
    // Enhanced filter functionality
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filterValue = this.getAttribute('data-filter');
            const projectItems = document.querySelectorAll('.project-card, .project-row');
            let visibleCount = 0;
            
            // Update active filter
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Apply filter
            projectItems.forEach(item => {
                const itemStatus = item.getAttribute('data-status');
                
                if (filterValue === 'all' || itemStatus === filterValue) {
                    item.classList.remove('hidden');
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                    item.style.display = 'none';
                }
            });
            
            // Update results count
            updateResultsCount(visibleCount);
        });
    });
    
    // Update results count
    function updateResultsCount(count) {
        const resultsCount = document.querySelector('.results-count');
        if (resultsCount) {
            resultsCount.textContent = count;
        }
    }
    
    // Refresh projects functionality
    window.refreshProjects = function() {
        location.reload();
    };
    
    // Initialize tooltips if needed
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script> 