<?php include_once VIEWSPATH . '/inc/header.php'; ?>

<!-- Modern Project Dashboard -->
<div class="container-fluid px-4 py-3">
    
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="header-text">
                <h1 class="page-title">
                    <i class="fas fa-briefcase"></i>
                    Project Dashboard
                </h1>
                <p class="mb-0">Manage and track all your projects in one place</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline-primary" onclick="refreshProjects()">
                    <i class="fas fa-sync-alt me-2"></i>Refresh
                </button>
                <a href="/projects/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>New Project
                </a>
            </div>
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
                                <label class="search-label">Search Projects</label>
                                <div class="search-box">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" class="form-control search-input" id="projectSearch" 
                                           placeholder="Search by project name..." aria-label="Search projects">
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

<!-- Modern Styling -->
<style>
/* Page Header */
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    position: relative;
}

.page-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.1);
    border-radius: 1rem;
    pointer-events: none;
}

.page-header > * {
    position: relative;
    z-index: 1;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: #ffffff;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.page-title i {
    color: #ffffff;
    text-shadow: 0 1px 3px rgba(0,0,0,0.3);
    margin-right: 0.75rem;
}

.page-header p {
    color: #ffffff;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.header-text {
    flex: 1;
    margin-right: 2rem;
}

.header-actions {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.page-header .btn-outline-primary {
    background: rgba(255,255,255,0.15);
    border-color: rgba(255,255,255,0.3);
    color: #ffffff;
    backdrop-filter: blur(10px);
    text-shadow: none;
    font-weight: 600;
}

.page-header .btn-outline-primary:hover {
    background: rgba(255,255,255,0.25);
    border-color: rgba(255,255,255,0.5);
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.page-header .btn-primary {
    background: rgba(255,255,255,0.2);
    border-color: rgba(255,255,255,0.4);
    color: #ffffff;
    backdrop-filter: blur(10px);
    font-weight: 600;
    text-shadow: none;
}

.page-header .btn-primary:hover {
    background: rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.6);
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

/* Modern Stats Cards */
.modern-stats-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: none;
    height: 100%;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.modern-stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--card-color), var(--card-color-light));
}

.modern-stats-card.card-primary {
    --card-color: #3b82f6;
    --card-color-light: #60a5fa;
}

.modern-stats-card.card-success {
    --card-color: #10b981;
    --card-color-light: #34d399;
}

.modern-stats-card.card-info {
    --card-color: #06b6d4;
    --card-color-light: #22d3ee;
}

.modern-stats-card.card-warning {
    --card-color: #f59e0b;
    --card-color-light: #fbbf24;
}

.modern-stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    background: linear-gradient(135deg, var(--card-color), var(--card-color-light));
    margin-bottom: 1rem;
}

.stats-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    font-family: 'SF Mono', 'Monaco', monospace;
    margin-bottom: 0.5rem;
}

.stats-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 1rem;
}

.stats-progress {
    height: 4px;
    background: #e5e7eb;
    border-radius: 2px;
    overflow: hidden;
}

.stats-progress .progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--card-color), var(--card-color-light));
    transition: width 0.3s ease;
}

/* Enhanced Filter Panel */
.filter-panel-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fc 100%);
    border-radius: 1rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    border: 1px solid rgba(59, 130, 246, 0.1);
    overflow: hidden;
}

.filter-panel-card .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 2px solid rgba(59, 130, 246, 0.1);
    padding: 1.25rem 1.5rem;
}

.filter-title {
    color: #1f2937;
    font-weight: 700;
    margin: 0;
    font-size: 1.1rem;
}

.filter-title i {
    color: #3b82f6;
}

.filter-subtitle {
    color: #6b7280;
    font-weight: 500;
}

.results-badge {
    background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.search-section,
.filter-section {
    margin-bottom: 0;
}

.search-label,
.filter-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.search-box {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
    z-index: 10;
    font-size: 1rem;
}

.search-input {
    padding: 0.75rem 1rem 0.75rem 2.75rem;
    border-radius: 0.75rem;
    border: 2px solid #e5e7eb;
    transition: all 0.3s ease;
    background: #ffffff;
    font-size: 0.95rem;
    width: 100%;
}

.search-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    background: #ffffff;
}

.search-clear {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 0.25rem;
    transition: all 0.3s ease;
}

.search-clear:hover {
    color: #6b7280;
    background: rgba(0,0,0,0.05);
}

.filter-buttons {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.filter-btn {
    background: #ffffff;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    color: #6b7280;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 500;
    cursor: pointer;
    padding: 0;
    overflow: hidden;
    position: relative;
}

.filter-btn-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
}

.filter-btn-content i {
    font-size: 1rem;
    width: 20px;
    text-align: center;
}

.filter-text {
    font-weight: 600;
    font-size: 0.875rem;
}

.filter-badge {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-weight: 600;
    min-width: 24px;
    text-align: center;
}

.filter-btn:hover {
    border-color: #d1d5db;
    background: #f9fafb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.filter-btn.active {
    background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
    border-color: #3b82f6;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
}

.filter-btn.active .filter-badge {
    background: rgba(255,255,255,0.2);
    color: white;
}

.filter-btn.active .filter-btn-content i {
    color: white;
}

/* Status-specific styling for inactive buttons */
.filter-btn-active:not(.active):hover {
    border-color: #10b981;
    background: rgba(16, 185, 129, 0.05);
}

.filter-btn-completed:not(.active):hover {
    border-color: #06b6d4;
    background: rgba(6, 182, 212, 0.05);
}

.filter-btn-hold:not(.active):hover {
    border-color: #f59e0b;
    background: rgba(245, 158, 11, 0.05);
}

.filter-btn-cancelled:not(.active):hover {
    border-color: #ef4444;
    background: rgba(239, 68, 68, 0.05);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .filter-panel-card .card-header {
        padding: 1rem;
    }
    
    .filter-buttons {
        flex-direction: column;
    }
    
    .filter-btn {
        width: 100%;
    }
    
    .filter-btn-content {
        justify-content: space-between;
    }
}

/* View Controls */
.view-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.results-info {
    color: #6b7280;
    font-size: 0.875rem;
}

.results-count {
    font-weight: 600;
    color: #1f2937;
}

.view-toggle-group {
    display: flex;
    gap: 0.25rem;
    background: #f8f9fa;
    padding: 0.25rem;
    border-radius: 0.5rem;
    border: 1px solid #e9ecef;
}

.view-toggle {
    padding: 0.5rem 0.75rem;
    border: none;
    background: transparent;
    color: #6b7280;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.view-toggle:hover {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.view-toggle.active {
    background: #3b82f6;
    color: white;
}

/* Project Grid */
.projects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.project-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: none;
    transition: all 0.3s ease;
    overflow: hidden;
}

.project-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.project-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.status-badge {
    display: flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-badge.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.status-completed {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge.status-on-hold {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.action-btn {
    background: none;
    border: none;
    color: #6b7280;
    padding: 0.5rem;
    border-radius: 0.25rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.action-btn:hover {
    background: rgba(0,0,0,0.1);
    color: #374151;
}

.project-card-body {
    padding: 1.5rem;
}

.project-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #1f2937;
}

.project-link {
    color: #1f2937;
    text-decoration: none;
    transition: color 0.3s ease;
}

.project-link:hover {
    color: #3b82f6;
}

.project-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
    font-size: 0.875rem;
}

.meta-item i {
    color: #9ca3af;
}

.project-progress {
    margin-top: 1rem;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.progress-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

.progress-percentage {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
}

.progress-bar-container {
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #60a5fa);
    transition: width 0.3s ease;
}

.progress-footer {
    display: flex;
    justify-content: center;
}

.task-count {
    font-size: 0.75rem;
    color: #6b7280;
}

.project-card-footer {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

/* List View */
.projects-list-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: none;
    overflow: hidden;
}

.modern-table {
    margin-bottom: 0;
}

.modern-table th {
    background: #f8f9fa;
    border-top: none;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #6b7280;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 1rem;
}

.modern-table td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f4;
}

.project-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.project-name {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
}

.project-id {
    font-size: 0.75rem;
    color: #6b7280;
}

.progress-display {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
}

.progress-bar-small {
    flex: 1;
    height: 6px;
    background: #e5e7eb;
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #60a5fa);
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1f2937;
    min-width: 40px;
}

.task-summary {
    font-size: 0.75rem;
    color: #6b7280;
}

.timeline-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.start-date {
    font-weight: 600;
    color: #1f2937;
}

.end-date {
    font-size: 0.875rem;
    color: #6b7280;
}

.user-info {
    display: flex;
    align-items: center;
    color: #6b7280;
    font-size: 0.875rem;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

/* Empty State */
.empty-state-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: none;
    padding: 4rem 2rem;
    text-align: center;
}

.empty-state-content {
    max-width: 400px;
    margin: 0 auto;
}

.empty-icon {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #9ca3af;
    margin: 0 auto 2rem;
}

.empty-state-card h3 {
    color: #1f2937;
    font-weight: 700;
    margin-bottom: 1rem;
}

.empty-state-card p {
    color: #6b7280;
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header {
        padding: 1.5rem;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .projects-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-buttons {
        flex-direction: column;
        align-items: stretch;
    }
    
    .view-controls {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .project-card-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
}

/* Animation for filtering */
.project-card,
.project-row {
    transition: all 0.3s ease;
}

.project-card.hidden,
.project-row.hidden {
    opacity: 0;
    transform: scale(0.9);
    display: none;
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