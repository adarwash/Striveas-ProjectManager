<!-- Manager Dashboard Styles -->
<style>
.filter-card {
    border: none;
    box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
    margin-bottom: 1.5rem;
}

.stats-card {
    border: none;
    box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
    text-align: center;
    transition: transform 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.stats-number {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.router-table {
    border: none;
    box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
}

.table-responsive {
    border-radius: 0.5rem;
    overflow: hidden;
}

.priority-badge, .status-badge, .maintenance-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.action-buttons .btn {
    margin-right: 0.25rem;
    margin-bottom: 0.25rem;
}

.manager-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
}
</style>

<!-- Page Header -->
<div class="manager-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="h2 mb-1"><i class="bi bi-gear-wide-connected me-3"></i>Router Schedule Management</h1>
            <p class="mb-0">Manage weekly router maintenance schedules for technicians</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="<?= URLROOT ?>/weekly_routers/create" class="btn btn-light btn-lg">
                <i class="bi bi-plus-circle me-2"></i>Create New Schedule
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="stats-number text-primary"><?= $stats['total'] ?></div>
                <div class="text-muted">Total Schedules</div>
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

<!-- Filters -->
<div class="card filter-card">
    <div class="card-header bg-white p-3 border-bottom">
        <h5 class="card-title mb-0"><i class="bi bi-funnel me-2"></i>Filter Schedules</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= URLROOT ?>/weekly_routers" class="row g-3">
            <div class="col-md-3">
                <label for="technician_id" class="form-label">Technician</label>
                <select class="form-select" id="technician_id" name="technician_id">
                    <option value="">All Technicians</option>
                    <?php foreach ($technicians as $technician): ?>
                    <option value="<?= $technician['id'] ?>" <?= isset($filters['technician_id']) && $filters['technician_id'] == $technician['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($technician['full_name'] ?: $technician['username']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="Scheduled" <?= isset($filters['status']) && $filters['status'] === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                    <option value="In Progress" <?= isset($filters['status']) && $filters['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="Completed" <?= isset($filters['status']) && $filters['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="Cancelled" <?= isset($filters['status']) && $filters['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="week_start" class="form-label">Week Start</label>
                <input type="date" class="form-control" id="week_start" name="week_start" 
                       value="<?= $filters['week_start'] ?? '' ?>">
            </div>
            
            <div class="col-md-2">
                <label for="week_end" class="form-label">Week End</label>
                <input type="date" class="form-control" id="week_end" name="week_end" 
                       value="<?= $filters['week_end'] ?? '' ?>">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    <a href="<?= URLROOT ?>/weekly_routers" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Router Schedules Table -->
<div class="card router-table">
    <div class="card-header bg-white p-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="bi bi-table me-2"></i>Router Schedules</h5>
            <div>
                <span class="text-muted">Total: <?= count($router_schedules) ?> schedules</span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($router_schedules)): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Router</th>
                        <th>Location</th>
                        <th>Technician</th>
                        <th>Week Period</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Hours</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($router_schedules as $schedule): ?>
                    <tr>
                        <td>
                            <div>
                                <strong><?= htmlspecialchars($schedule['router_name']) ?></strong>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($schedule['router_ip']) ?></small>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted"><?= htmlspecialchars($schedule['location']) ?></span>
                        </td>
                        <td>
                            <?php if (!empty($schedule['technician_full_name']) || !empty($schedule['technician_name'])): ?>
                                <span><?= htmlspecialchars($schedule['technician_full_name'] ?: $schedule['technician_name']) ?></span>
                            <?php else: ?>
                                <span class="text-muted fst-italic">Unassigned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small>
                                <?= date('M j', strtotime($schedule['week_start_date'])) ?> - 
                                <?= date('M j, Y', strtotime($schedule['week_end_date'])) ?>
                            </small>
                        </td>
                        <td>
                            <span class="badge <?= getMaintenanceTypeBadgeClass($schedule['maintenance_type']) ?> maintenance-badge">
                                <?= ucfirst($schedule['maintenance_type']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= getPriorityBadgeClass($schedule['priority']) ?> priority-badge">
                                <?= $schedule['priority'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= getStatusBadgeClass($schedule['status']) ?> status-badge">
                                <?= $schedule['status'] ?>
                            </span>
                        </td>
                        <td>
                            <small>
                                <?php if (!empty($schedule['actual_hours'])): ?>
                                    <strong><?= $schedule['actual_hours'] ?>h</strong>
                                    <?php if (!empty($schedule['estimated_hours'])): ?>
                                        <br><span class="text-muted">Est: <?= $schedule['estimated_hours'] ?>h</span>
                                    <?php endif; ?>
                                <?php elseif (!empty($schedule['estimated_hours'])): ?>
                                    <span class="text-muted">Est: <?= $schedule['estimated_hours'] ?>h</span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </small>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?= URLROOT ?>/weekly_routers/view/<?= $schedule['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= URLROOT ?>/weekly_routers/edit/<?= $schedule['id'] ?>" 
                                   class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="confirmDelete(<?= $schedule['id'] ?>, '<?= htmlspecialchars($schedule['router_name']) ?>')"
                                        title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
            <p class="mt-3 text-muted">No router schedules found.</p>
            <?php if (!empty($filters)): ?>
                <p class="text-muted">Try adjusting your filters or <a href="<?= URLROOT ?>/weekly_routers">clear all filters</a>.</p>
            <?php else: ?>
                <a href="<?= URLROOT ?>/weekly_routers/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Create First Schedule
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
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
                    <div class="col-md-3 mb-3">
                        <div class="d-grid">
                            <a href="<?= URLROOT ?>/weekly_routers/create" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Create New Schedule
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="d-grid">
                            <a href="<?= URLROOT ?>/weekly_routers?status=In Progress" class="btn btn-outline-info">
                                <i class="bi bi-play-circle me-2"></i>View In Progress
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="d-grid">
                            <a href="<?= URLROOT ?>/weekly_routers?status=Scheduled" class="btn btn-outline-warning">
                                <i class="bi bi-calendar-event me-2"></i>View Scheduled
                            </a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the router schedule for <strong id="routerName"></strong>?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Delete Schedule</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(scheduleId, routerName) {
    document.getElementById('routerName').textContent = routerName;
    document.getElementById('deleteForm').action = '<?= URLROOT ?>/weekly_routers/delete/' + scheduleId;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

// Auto-submit form when filters change (optional)
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.querySelector('form[method="GET"]');
    const selects = filterForm.querySelectorAll('select');
    
    selects.forEach(select => {
        select.addEventListener('change', function() {
            // Optional: Auto-submit on filter change
            // filterForm.submit();
        });
    });
});
</script>

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