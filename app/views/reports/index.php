<?php require VIEWSPATH . '/inc/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="bi bi-graph-up text-danger"></i> System Reports & Analytics
            </h1>
            <p class="text-muted mb-0">Comprehensive system reports and performance analytics.</p>
        </div>
        <div>
            <a href="/admin" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-2"></i>Back to Admin
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-2"></i>Export Reports
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= URLROOT ?>/reports/export/users">
                        <i class="bi bi-people me-2"></i>Users Report
                    </a></li>
                    <li><a class="dropdown-item" href="<?= URLROOT ?>/reports/export/projects">
                        <i class="bi bi-kanban me-2"></i>Projects Report
                    </a></li>
                    <li><a class="dropdown-item" href="<?= URLROOT ?>/reports/export/tickets">
                        <i class="bi bi-ticket me-2"></i>Tickets Report
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
    <hr class="mt-0 mb-4">

    <!-- Overview Statistics -->
    <div class="row g-4 mb-5">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="bi bi-people fs-1 text-primary mb-2"></i>
                    <h3 class="mb-1"><?= $data['reportData']['overview']['total_users'] ?></h3>
                    <small class="text-muted">Total Users</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="bi bi-building fs-1 text-success mb-2"></i>
                    <h3 class="mb-1"><?= $data['reportData']['overview']['active_clients'] ?></h3>
                    <small class="text-muted">Active Clients</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="bi bi-kanban fs-1 text-info mb-2"></i>
                    <h3 class="mb-1"><?= $data['reportData']['overview']['total_projects'] ?></h3>
                    <small class="text-muted">Total Projects</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="bi bi-ticket fs-1 text-warning mb-2"></i>
                    <h3 class="mb-1"><?= $data['reportData']['overview']['open_tickets'] ?></h3>
                    <small class="text-muted">Open Tickets</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="bi bi-check-circle fs-1 text-success mb-2"></i>
                    <h3 class="mb-1"><?= $data['reportData']['overview']['completed_tasks'] ?></h3>
                    <small class="text-muted">Completed Tasks</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="bi bi-clock fs-1 text-danger mb-2"></i>
                    <h3 class="mb-1"><?= $data['reportData']['overview']['pending_tasks'] ?></h3>
                    <small class="text-muted">Pending Tasks</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Reports -->
    <div class="row g-4">
        <!-- User Analytics -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="bi bi-people text-primary me-2"></i>User Analytics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="small text-muted mb-2">Users by Role</h6>
                            <?php foreach ($data['reportData']['users']['by_role'] as $role => $count): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-medium"><?= ucfirst($role) ?></span>
                                <div class="d-flex align-items-center">
                                    <div class="progress me-2" style="width: 100px; height: 8px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?= ($count / max(1, $data['reportData']['overview']['total_users'])) * 100 ?>%"></div>
                                    </div>
                                    <span class="badge bg-light text-dark"><?= $count ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-success mb-1"><?= $data['reportData']['users']['recent_registrations'] ?></h5>
                                <small class="text-muted">New Users (30 days)</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-info mb-1"><?= $data['reportData']['users']['active_users'] ?></h5>
                            <small class="text-muted">Active Users (7 days)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Analytics -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="bi bi-kanban text-success me-2"></i>Project Analytics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="small text-muted mb-2">Projects by Status</h6>
                            <?php foreach ($data['reportData']['projects']['by_status'] as $status => $count): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-medium"><?= ucfirst($status) ?></span>
                                <div class="d-flex align-items-center">
                                    <div class="progress me-2" style="width: 100px; height: 8px;">
                                        <div class="progress-bar 
                                            <?= $status === 'Completed' ? 'bg-success' : 
                                                ($status === 'In Progress' ? 'bg-primary' : 
                                                 ($status === 'On Hold' ? 'bg-warning' : 'bg-secondary')) ?>" 
                                             role="progressbar" 
                                             style="width: <?= ($count / max(1, $data['reportData']['overview']['total_projects'])) * 100 ?>%"></div>
                                    </div>
                                    <span class="badge bg-light text-dark"><?= $count ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-success mb-1"><?= $data['reportData']['projects']['completed_this_month'] ?></h5>
                                <small class="text-muted">Completed (30 days)</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-danger mb-1"><?= $data['reportData']['projects']['overdue'] ?></h5>
                            <small class="text-muted">Overdue Projects</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticket Analytics -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="bi bi-ticket text-warning me-2"></i>Ticket Analytics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <h6 class="small text-muted mb-2">By Status</h6>
                            <?php foreach ($data['reportData']['tickets']['by_status'] as $status => $count): ?>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small><?= ucfirst($status) ?></small>
                                <span class="badge 
                                    <?= $status === 'Resolved' ? 'bg-success' : 
                                        ($status === 'In Progress' ? 'bg-primary' : 
                                         ($status === 'Open' ? 'bg-warning' : 'bg-secondary')) ?>"><?= $count ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="col-6">
                            <h6 class="small text-muted mb-2">By Priority</h6>
                            <?php foreach ($data['reportData']['tickets']['by_priority'] as $priority => $count): ?>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small><?= ucfirst($priority) ?></small>
                                <span class="badge 
                                    <?= $priority === 'Critical' ? 'bg-danger' : 
                                        ($priority === 'High' ? 'bg-warning' : 
                                         ($priority === 'Medium' ? 'bg-info' : 'bg-success')) ?>"><?= $count ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-success mb-1"><?= $data['reportData']['tickets']['resolved_this_week'] ?></h5>
                                <small class="text-muted">Resolved (7 days)</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-info mb-1"><?= $data['reportData']['tickets']['avg_resolution_time'] ?></h5>
                            <small class="text-muted">Avg Resolution</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Tracking Analytics -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="bi bi-clock text-info me-2"></i>Time Tracking Analytics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary mb-1"><?= number_format($data['reportData']['time_tracking']['total_hours_logged'], 1) ?></h4>
                                <small class="text-muted">Total Hours Logged</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-1"><?= number_format($data['reportData']['time_tracking']['hours_this_month'], 1) ?></h4>
                            <small class="text-muted">Hours This Month</small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <h6 class="small text-muted mb-2">Top Time Loggers (30 days)</h6>
                        <?php if (!empty($data['reportData']['time_tracking']['top_time_loggers'])): ?>
                            <?php foreach (array_slice($data['reportData']['time_tracking']['top_time_loggers'], 0, 5) as $logger): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-medium"><?= htmlspecialchars($logger['name'] ?? 'Unknown') ?></span>
                                <span class="badge bg-primary"><?= number_format($logger['hours'] ?? 0, 1) ?>h</span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-clock-history fs-2 mb-2"></i>
                                <p class="mb-0">No time tracking data available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up text-success me-2"></i>Performance Metrics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border-end">
                                <h3 class="text-success mb-2"><?= $data['reportData']['performance']['project_completion_rate'] ?></h3>
                                <h6 class="text-muted mb-0">Project Completion Rate</h6>
                                <small class="text-muted">Overall project success rate</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border-end">
                                <h3 class="text-info mb-2"><?= $data['reportData']['performance']['ticket_resolution_rate'] ?></h3>
                                <h6 class="text-muted mb-0">Ticket Resolution Rate</h6>
                                <small class="text-muted">Support ticket efficiency</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-primary mb-2">
                                <?= !empty($data['reportData']['performance']['user_productivity']) ? 
                                    count($data['reportData']['performance']['user_productivity']) : 0 ?>
                            </h3>
                            <h6 class="text-muted mb-0">Active Contributors</h6>
                            <small class="text-muted">Users with recent activity</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning text-warning me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="<?= URLROOT ?>/users" class="btn btn-outline-primary w-100">
                                <i class="bi bi-people me-2"></i>Manage Users
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= URLROOT ?>/projects" class="btn btn-outline-success w-100">
                                <i class="bi bi-kanban me-2"></i>View Projects
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= URLROOT ?>/tickets" class="btn btn-outline-warning w-100">
                                <i class="bi bi-ticket me-2"></i>Support Tickets
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= URLROOT ?>/admin/settings" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-gear me-2"></i>System Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add some custom styling for better charts -->
<style>
.progress {
    background-color: #e9ecef;
}

.stats-card {
    transition: transform 0.2s ease-in-out;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.card-header h6 {
    font-weight: 600;
}

.border-end {
    border-right: 1px solid #dee2e6 !important;
}

@media (max-width: 768px) {
    .border-end {
        border-right: none !important;
        border-bottom: 1px solid #dee2e6 !important;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }
}
</style>

<!-- JavaScript for enhanced interactivity -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to stats cards
    const statsCards = document.querySelectorAll('.card');
    statsCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.transition = 'transform 0.2s ease-in-out';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Auto-refresh data every 5 minutes
    setTimeout(function() {
        if (confirm('Would you like to refresh the report data?')) {
            window.location.reload();
        }
    }, 300000); // 5 minutes
});

// Function to refresh specific report section
function refreshReportSection(section) {
    // Could be implemented to refresh specific sections via AJAX
    console.log('Refreshing section:', section);
}
</script>

<?php require VIEWSPATH . '/inc/footer.php'; ?>
