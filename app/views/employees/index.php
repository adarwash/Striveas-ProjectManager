<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Employee Management</li>
    </ol>
</nav>

<div class="mb-4">
    <h1 class="h3 mb-0">Employee Management</h1>
    <p class="text-muted">Manage employee performance, tasks, and absence records</p>
</div>

<!-- Flash Messages -->
<?php flash('employee_success'); ?>
<?php flash('employee_error'); ?>

<!-- Performance Overview Dashboard -->
<div class="row mb-4">
    <!-- Average Performance Rating -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="card-title mb-0">Average Rating</h6>
                    <div class="card-icon bg-primary-light text-primary">
                        <i class="bi bi-bar-chart"></i>
                    </div>
                </div>
                <h2 class="mb-0"><?= number_format($stats['avg_rating'], 2) ?>/5.00</h2>
                <div class="progress mt-2" style="height: 5px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= ($stats['avg_rating']/5)*100 ?>%" aria-valuenow="<?= ($stats['avg_rating']/5)*100 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Employees -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="card-title mb-0">Total Employees</h6>
                    <div class="card-icon bg-success-light text-success">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <h2 class="mb-0"><?= count($employees) ?></h2>
                <p class="text-muted mb-0 small">Being Tracked</p>
            </div>
        </div>
    </div>
    
    <!-- Total Tasks Completed -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="card-title mb-0">Tasks Completed</h6>
                    <div class="card-icon bg-info-light text-info">
                        <i class="bi bi-check2-all"></i>
                    </div>
                </div>
                <?php
                $totalCompleted = 0;
                foreach ($employees as $employee) {
                    $totalCompleted += $employee['tasks_completed'];
                }
                ?>
                <h2 class="mb-0"><?= $totalCompleted ?></h2>
                <p class="text-muted mb-0 small">Across All Employees</p>
            </div>
        </div>
    </div>
    
    <!-- Total Absences -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="card-title mb-0">Absences</h6>
                    <div class="card-icon bg-warning-light text-warning">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                </div>
                <h2 class="mb-0"><?= $stats['total_absences'] ?></h2>
                <p class="text-muted mb-0 small">Total Records</p>
            </div>
        </div>
    </div>
</div>

<!-- Top Performers and Recent Reviews -->
<div class="row mb-4">
    <!-- Top Performers -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">Top Performers</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($stats['top_performers'] as $performer): ?>
                    <div class="list-group-item border-0 px-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-primary-light text-primary me-3">
                                <?= substr($performer['full_name'] ?? $performer['username'], 0, 1) ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= $performer['full_name'] ?? $performer['username'] ?></h6>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <span class="text-warning">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= floor($performer['performance_rating'])): ?>
                                                    <i class="bi bi-star-fill"></i>
                                                <?php elseif ($i - 0.5 <= $performer['performance_rating']): ?>
                                                    <i class="bi bi-star-half"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </span>
                                        <span class="ms-1 text-muted"><?= $performer['performance_rating'] ?></span>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-check2-all me-1"></i>
                                        <?= $performer['tasks_completed'] ?> tasks completed
                                    </div>
                                </div>
                            </div>
                            <a href="/employees/viewEmployee/<?= $performer['user_id'] ?>" class="btn btn-sm btn-outline-primary">
                                View
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($stats['top_performers'])): ?>
                    <div class="list-group-item border-0 px-3">
                        <p class="text-muted text-center my-3">No performance data available</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recently Reviewed -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">Recently Reviewed</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($stats['recently_reviewed'] as $reviewed): ?>
                    <div class="list-group-item border-0 px-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-info-light text-info me-3">
                                <?= substr($reviewed['full_name'] ?? $reviewed['username'], 0, 1) ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= $reviewed['full_name'] ?? $reviewed['username'] ?></h6>
                                <div class="d-flex align-items-center small text-muted">
                                    <div class="me-3">
                                        <i class="bi bi-calendar-check me-1"></i>
                                        <?= date('M d, Y', strtotime($reviewed['last_review_date'])) ?>
                                    </div>
                                    <div>
                                        <i class="bi bi-star-fill me-1 text-warning"></i>
                                        <?= $reviewed['performance_rating'] ?>
                                    </div>
                                </div>
                            </div>
                            <a href="/employees/viewEmployee/<?= $reviewed['user_id'] ?>" class="btn btn-sm btn-outline-primary">
                                View
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($stats['recently_reviewed'])): ?>
                    <div class="list-group-item border-0 px-3">
                        <p class="text-muted text-center my-3">No review data available</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Employee List Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Employee List</h5>
        <div class="btn-group">
            <a href="/employees/performance" class="btn btn-sm btn-info">
                <i class="bi bi-graph-up-arrow"></i> Performance Dashboard
            </a>
        <a href="/employees/create" class="btn btn-sm btn-primary">
            <i class="bi bi-plus"></i> Add New
        </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Role</th>
                        <th>Performance</th>
                        <th>Tasks</th>
                        <th>Absences</th>
                        <th>Last Review</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $employee): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-light text-dark me-2">
                                    <?= substr($employee['full_name'] ?? $employee['username'], 0, 1) ?>
                                </div>
                                <div>
                                    <h6 class="mb-0"><?= $employee['full_name'] ?? $employee['username'] ?></h6>
                                    <span class="small text-muted"><?= $employee['email'] ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-<?= $employee['role'] === 'admin' ? 'danger' : ($employee['role'] === 'manager' ? 'warning' : 'info') ?>">
                                <?= ucfirst($employee['role']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rating-stars me-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= floor($employee['performance_rating'])): ?>
                                            <i class="bi bi-star-fill text-warning"></i>
                                        <?php elseif ($i - 0.5 <= $employee['performance_rating']): ?>
                                            <i class="bi bi-star-half text-warning"></i>
                                        <?php else: ?>
                                            <i class="bi bi-star text-warning"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <span><?= $employee['performance_rating'] ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="badge bg-success mb-1">Completed: <?= $employee['tasks_completed'] ?></span>
                                <span class="badge bg-info">Pending: <?= $employee['tasks_pending'] ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= $employee['absence_count'] ?> Records</span>
                        </td>
                        <td>
                            <?php if ($employee['last_review_date']): ?>
                                <?= date('M d, Y', strtotime($employee['last_review_date'])) ?>
                            <?php else: ?>
                                <span class="text-muted">Not reviewed</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/employees/viewEmployee/<?= $employee['user_id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="/employees/edit/<?= $employee['user_id'] ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $employee['user_id'] ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($employees)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <p class="text-muted mb-0">No employee records found</p>
                            <a href="/employees/create" class="btn btn-sm btn-primary mt-2">
                                <i class="bi bi-plus"></i> Add Employee Record
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Modals -->
<?php foreach ($employees as $employee): ?>
<div class="modal fade" id="deleteModal<?= $employee['user_id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $employee['user_id'] ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel<?= $employee['user_id'] ?>">Delete Employee Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the performance record for <strong><?= $employee['full_name'] ?? $employee['username'] ?></strong>?</p>
                <p class="text-danger"><strong>Note:</strong> This will also delete all absence records. This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="/employees/delete/<?= $employee['user_id'] ?>" method="post">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Styles moved to /public/css/app.css -->

<script>
    // Initialize any interactive elements here
    document.addEventListener('DOMContentLoaded', function() {
        // Add any JavaScript functionality needed
    });
</script> 