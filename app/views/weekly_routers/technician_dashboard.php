<!-- Technician Dashboard Styles -->
<style>
.router-card {
    border: none;
    box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
    transition: all 0.3s ease;
    margin-bottom: 1rem;
}

.router-card:hover {
    box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15);
    transform: translateY(-2px);
}

.priority-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.maintenance-type-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.stats-card {
    border: none;
    box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
    text-align: center;
}

.stats-number {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.current-week-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
}

.router-info {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
}

.router-info h6 {
    color: #fff;
    margin-bottom: 0.5rem;
}

.router-info p {
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}
</style>

<!-- Page Header -->
<div class="bg-light rounded-3 p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="h2 mb-1">My Router Schedules</h1>
            <p class="text-muted mb-0">View your assigned weekly router maintenance schedules</p>
        </div>
        <div>
            <span class="badge bg-primary fs-6">Technician Dashboard</span>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="stats-number text-primary"><?= $stats['total'] ?></div>
                <div class="text-muted">Total Assigned</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="stats-number text-warning"><?= $stats['scheduled'] ?></div>
                <div class="text-muted">Scheduled</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="stats-number text-info"><?= $stats['in_progress'] ?></div>
                <div class="text-muted">In Progress</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="stats-number text-success"><?= $stats['completed'] ?></div>
                <div class="text-muted">Completed</div>
            </div>
        </div>
    </div>
</div>

<!-- Current Week Section -->
<?php if (!empty($current_week_schedules)): ?>
<div class="current-week-section">
    <h3 class="mb-3"><i class="bi bi-calendar-week me-2"></i>This Week's Router Maintenance</h3>
    <div class="row">
        <?php foreach ($current_week_schedules as $schedule): ?>
        <div class="col-lg-6 col-xl-4 mb-3">
            <div class="router-info">
                <h6><i class="bi bi-router me-2"></i><?= htmlspecialchars($schedule['router_name']) ?></h6>
                <p><strong>IP:</strong> <?= htmlspecialchars($schedule['router_ip']) ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($schedule['location']) ?></p>
                <p><strong>Type:</strong> <?= ucfirst($schedule['maintenance_type']) ?></p>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="badge <?= getPriorityBadgeClass($schedule['priority']) ?> priority-badge">
                        <?= $schedule['priority'] ?> Priority
                    </span>
                    <a href="<?= URLROOT ?>/weekly_routers/view/<?= $schedule['id'] ?>" class="btn btn-light btn-sm">
                        <i class="bi bi-eye me-1"></i>View Details
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php else: ?>
<div class="current-week-section text-center">
    <i class="bi bi-calendar-x" style="font-size: 3rem; opacity: 0.7;"></i>
    <h4 class="mt-3">No Router Maintenance This Week</h4>
    <p class="mb-0">You don't have any router maintenance scheduled for this week.</p>
</div>
<?php endif; ?>

<!-- Upcoming Schedules -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white p-3 border-bottom">
                <h5 class="card-title mb-0"><i class="bi bi-calendar-plus me-2"></i>Upcoming Router Schedules</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($upcoming_schedules)): ?>
                    <div class="row">
                        <?php foreach ($upcoming_schedules as $schedule): ?>
                        <div class="col-lg-6 col-xl-4 mb-3">
                            <div class="card router-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0">
                                            <i class="bi bi-router me-2 text-primary"></i>
                                            <?= htmlspecialchars($schedule['router_name']) ?>
                                        </h6>
                                        <span class="badge <?= getStatusBadgeClass($schedule['status']) ?> status-badge">
                                            <?= $schedule['status'] ?>
                                        </span>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted d-block">
                                            <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($schedule['location']) ?>
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-ethernet me-1"></i><?= htmlspecialchars($schedule['router_ip']) ?>
                                        </small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <span class="badge <?= getMaintenanceTypeBadgeClass($schedule['maintenance_type']) ?> maintenance-type-badge me-2">
                                            <?= ucfirst($schedule['maintenance_type']) ?>
                                        </span>
                                        <span class="badge <?= getPriorityBadgeClass($schedule['priority']) ?> priority-badge">
                                            <?= $schedule['priority'] ?>
                                        </span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar-range me-1"></i>
                                            <?= date('M j', strtotime($schedule['week_start_date'])) ?> - 
                                            <?= date('M j, Y', strtotime($schedule['week_end_date'])) ?>
                                        </small>
                                        <?php if (!empty($schedule['estimated_hours'])): ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            Est. <?= $schedule['estimated_hours'] ?> hours
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($schedule['description'])): ?>
                                    <p class="card-text small text-muted mb-3">
                                        <?= htmlspecialchars(substr($schedule['description'], 0, 100)) ?>
                                        <?= strlen($schedule['description']) > 100 ? '...' : '' ?>
                                    </p>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            Assigned by: <?= htmlspecialchars($schedule['created_by_name']) ?>
                                        </small>
                                        <a href="<?= URLROOT ?>/weekly_routers/view/<?= $schedule['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i>View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-check text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-3 text-muted">No upcoming router schedules found.</p>
                        <p class="text-muted">Check back later for new assignments from your IT manager.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white p-3 border-bottom">
                <h5 class="card-title mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="d-grid">
                            <a href="<?= URLROOT ?>/weekly_routers?status=In Progress" class="btn btn-outline-primary">
                                <i class="bi bi-play-circle me-2"></i>View In Progress Tasks
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-grid">
                            <a href="<?= URLROOT ?>/weekly_routers?status=Completed" class="btn btn-outline-success">
                                <i class="bi bi-check-circle me-2"></i>View Completed Tasks
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="d-grid">
                            <a href="<?= URLROOT ?>/dashboard" class="btn btn-outline-secondary">
                                <i class="bi bi-house me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Helper functions for badge classes
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Scheduled':
            return 'bg-warning text-dark';
        case 'In Progress':
            return 'bg-info';
        case 'Completed':
            return 'bg-success';
        case 'Cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

function getPriorityBadgeClass($priority) {
    switch ($priority) {
        case 'Low':
            return 'bg-secondary';
        case 'Medium':
            return 'bg-primary';
        case 'High':
            return 'bg-warning text-dark';
        case 'Critical':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

function getMaintenanceTypeBadgeClass($type) {
    switch ($type) {
        case 'routine':
            return 'bg-info';
        case 'repair':
            return 'bg-warning text-dark';
        case 'upgrade':
            return 'bg-success';
        case 'inspection':
            return 'bg-secondary';
        default:
            return 'bg-light text-dark';
    }
}
?> 