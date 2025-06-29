<!-- Router Schedule Detail Styles -->
<style>
.detail-card {
    border: none;
    box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
}

.info-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.info-value {
    color: #212529;
}

.status-update-section {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-top: 1rem;
}

.router-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
}

.router-icon {
    font-size: 3rem;
    opacity: 0.8;
}
</style>

<!-- Page Header -->
<div class="bg-light rounded-3 p-4 mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="<?= URLROOT ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= URLROOT ?>/weekly_routers" class="text-decoration-none">Router Schedules</a></li>
            <li class="breadcrumb-item active" aria-current="page">Schedule Details</li>
        </ol>
    </nav>
    
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="h2 mb-1">Router Schedule Details</h1>
            <p class="text-muted mb-0">View and manage router maintenance schedule</p>
        </div>
        <div>
            <a href="<?= URLROOT ?>/weekly_routers" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Schedules
            </a>
        </div>
    </div>
</div>

<!-- Router Header Section -->
<div class="router-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center">
                <i class="bi bi-router router-icon me-3"></i>
                <div>
                    <h2 class="mb-1"><?= htmlspecialchars($router_schedule['router_name']) ?></h2>
                    <p class="mb-1"><i class="bi bi-ethernet me-2"></i><?= htmlspecialchars($router_schedule['router_ip']) ?></p>
                    <p class="mb-0"><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($router_schedule['location']) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <span class="badge <?= getStatusBadgeClass($router_schedule['status']) ?> fs-6 mb-2">
                <?= $router_schedule['status'] ?>
            </span>
            <br>
            <span class="badge <?= getPriorityBadgeClass($router_schedule['priority']) ?> fs-6">
                <?= $router_schedule['priority'] ?> Priority
            </span>
        </div>
    </div>
</div>

<div class="row">
    <!-- Schedule Details -->
    <div class="col-lg-8">
        <div class="card detail-card mb-4">
            <div class="card-header bg-white p-3 border-bottom">
                <h5 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>Schedule Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Maintenance Type</div>
                            <div class="info-value">
                                <span class="badge <?= getMaintenanceTypeBadgeClass($router_schedule['maintenance_type']) ?>">
                                    <?= ucfirst($router_schedule['maintenance_type']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Week Period</div>
                            <div class="info-value">
                                <?= date('M j, Y', strtotime($router_schedule['week_start_date'])) ?> - 
                                <?= date('M j, Y', strtotime($router_schedule['week_end_date'])) ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Estimated Hours</div>
                            <div class="info-value">
                                <?= $router_schedule['estimated_hours'] ? $router_schedule['estimated_hours'] . ' hours' : 'Not specified' ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-item">
                            <div class="info-label">Assigned Technician</div>
                            <div class="info-value">
                                <?= htmlspecialchars($router_schedule['technician_full_name'] ?: $router_schedule['technician_name'] ?: 'Unassigned') ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Created By</div>
                            <div class="info-value">
                                <?= htmlspecialchars($router_schedule['created_by_full_name'] ?: $router_schedule['created_by_name']) ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Created Date</div>
                            <div class="info-value">
                                <?= date('M j, Y g:i A', strtotime($router_schedule['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($router_schedule['description'])): ?>
                <div class="info-item">
                    <div class="info-label">Description</div>
                    <div class="info-value">
                        <?= nl2br(htmlspecialchars($router_schedule['description'])) ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($router_schedule['notes'])): ?>
                <div class="info-item">
                    <div class="info-label">Notes</div>
                    <div class="info-value">
                        <?= nl2br(htmlspecialchars($router_schedule['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($router_schedule['actual_hours'])): ?>
                <div class="info-item">
                    <div class="info-label">Actual Hours Worked</div>
                    <div class="info-value">
                        <strong><?= $router_schedule['actual_hours'] ?> hours</strong>
                        <?php if (!empty($router_schedule['estimated_hours'])): ?>
                            <?php 
                            $variance = $router_schedule['actual_hours'] - $router_schedule['estimated_hours'];
                            $varianceClass = $variance > 0 ? 'text-warning' : ($variance < 0 ? 'text-success' : 'text-muted');
                            ?>
                            <small class="<?= $varianceClass ?>">
                                (<?= $variance > 0 ? '+' : '' ?><?= $variance ?> vs estimated)
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($router_schedule['completed_at'])): ?>
                <div class="info-item">
                    <div class="info-label">Completed Date</div>
                    <div class="info-value">
                        <?= date('M j, Y g:i A', strtotime($router_schedule['completed_at'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Status Update Section -->
    <div class="col-lg-4">
        <?php 
        $userRole = $_SESSION['role'] ?? 'user';
        $userId = $_SESSION['user_id'];
        $canUpdate = (in_array($userRole, ['admin', 'manager', 'it_manager', 'IT Manager']) || 
                     $router_schedule['assigned_technician_id'] == $userId);
        ?>
        
        <?php if ($canUpdate): ?>
        <div class="card detail-card">
            <div class="card-header bg-white p-3 border-bottom">
                <h5 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i>Update Status</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= URLROOT ?>/weekly_routers/updateStatus/<?= $router_schedule['id'] ?>">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Scheduled" <?= $router_schedule['status'] === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                            <option value="In Progress" <?= $router_schedule['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="Completed" <?= $router_schedule['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="Cancelled" <?= $router_schedule['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="actual_hours" class="form-label">Actual Hours Worked</label>
                        <input type="number" class="form-control" id="actual_hours" name="actual_hours" 
                               step="0.25" min="0" max="24" 
                               value="<?= $router_schedule['actual_hours'] ?>"
                               placeholder="e.g., 2.5">
                        <div class="form-text">Enter the actual time spent on this maintenance</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" 
                                  placeholder="Add any notes about the maintenance work..."><?= htmlspecialchars($router_schedule['notes'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Quick Actions -->
        <div class="card detail-card mt-4">
            <div class="card-header bg-white p-3 border-bottom">
                <h5 class="card-title mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if (in_array($userRole, ['admin', 'manager', 'it_manager', 'IT Manager'])): ?>
                    <a href="<?= URLROOT ?>/weekly_routers/edit/<?= $router_schedule['id'] ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil me-2"></i>Edit Schedule
                    </a>
                    <?php endif; ?>
                    
                    <a href="<?= URLROOT ?>/weekly_routers" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-list me-2"></i>All Schedules
                    </a>
                    
                    <?php if ($router_schedule['assigned_technician_id'] == $userId): ?>
                    <a href="<?= URLROOT ?>/weekly_routers?technician_id=<?= $userId ?>" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-person me-2"></i>My Schedules
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Schedule Timeline -->
        <div class="card detail-card mt-4">
            <div class="card-header bg-white p-3 border-bottom">
                <h5 class="card-title mb-0"><i class="bi bi-clock-history me-2"></i>Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Schedule Created</h6>
                            <small class="text-muted">
                                <?= date('M j, Y g:i A', strtotime($router_schedule['created_at'])) ?>
                            </small>
                        </div>
                    </div>
                    
                    <?php if (!empty($router_schedule['updated_at']) && $router_schedule['updated_at'] !== $router_schedule['created_at']): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Last Updated</h6>
                            <small class="text-muted">
                                <?= date('M j, Y g:i A', strtotime($router_schedule['updated_at'])) ?>
                            </small>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($router_schedule['completed_at'])): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Completed</h6>
                            <small class="text-muted">
                                <?= date('M j, Y g:i A', strtotime($router_schedule['completed_at'])) ?>
                            </small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -1.25rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    padding-left: 1rem;
}
</style>

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