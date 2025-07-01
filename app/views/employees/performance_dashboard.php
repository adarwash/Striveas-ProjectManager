<?php require_once VIEWSPATH . '/partials/header.php'; ?>

<!-- Performance Dashboard -->
<div class="container-fluid mt-4">
    <!-- Dashboard Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Employee Performance Dashboard</h1>
                    <p class="text-muted">Comprehensive performance analysis with time tracking integration</p>
                </div>
                <div class="btn-group">
                    <a href="/employees/exportPerformanceReport?days=<?= $days ?>" class="btn btn-success">
                        <i class="fas fa-download"></i> Export Report
                    </a>
                    <a href="/employees" class="btn btn-outline-secondary">
                        <i class="fas fa-users"></i> Employee Management
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Active Employees</h5>
                            <h2 class="mb-0"><?= $performance_summary['active_employees'] ?? 0 ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Hours</h5>
                            <h2 class="mb-0"><?= number_format($performance_summary['total_company_hours'] ?? 0, 1) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Punctuality Rate</h5>
                            <h2 class="mb-0"><?= $performance_summary['punctuality_rate'] ?? 0 ?>%</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Avg Hours/Entry</h5>
                            <h2 class="mb-0"><?= number_format($performance_summary['avg_hours_per_entry'] ?? 0, 1) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter and Sort Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="/employees/performance" class="row g-3">
                        <div class="col-md-3">
                            <label for="days" class="form-label">Analysis Period</label>
                            <select name="days" id="days" class="form-select">
                                <option value="7" <?= $days == 7 ? 'selected' : '' ?>>Last 7 days</option>
                                <option value="30" <?= $days == 30 ? 'selected' : '' ?>>Last 30 days</option>
                                <option value="60" <?= $days == 60 ? 'selected' : '' ?>>Last 60 days</option>
                                <option value="90" <?= $days == 90 ? 'selected' : '' ?>>Last 90 days</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sort" class="form-label">Sort By</label>
                            <select name="sort" id="sort" class="form-select">
                                <option value="productivity_rating" <?= $sort_by == 'productivity_rating' ? 'selected' : '' ?>>Productivity Rating</option>
                                <option value="performance_rating" <?= $sort_by == 'performance_rating' ? 'selected' : '' ?>>Performance Rating</option>
                                <option value="total_hours" <?= $sort_by == 'total_hours' ? 'selected' : '' ?>>Total Hours</option>
                                <option value="punctuality_score" <?= $sort_by == 'punctuality_score' ? 'selected' : '' ?>>Punctuality</option>
                                <option value="attendance_rate" <?= $sort_by == 'attendance_rate' ? 'selected' : '' ?>>Attendance Rate</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Performance Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Employee Performance Analysis (<?= $days ?> days)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Employee</th>
                                    <th>Role</th>
                                    <th>Performance Rating</th>
                                    <th>Productivity Rating</th>
                                    <th>Total Hours</th>
                                    <th>Avg Hours/Day</th>
                                    <th>Punctuality</th>
                                    <th>Attendance</th>
                                    <th>Current Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($employees)): ?>
                                    <?php foreach ($employees as $employee): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-2">
                                                        <span class="badge bg-secondary rounded-circle p-2">
                                                            <?= strtoupper(substr($employee['full_name'], 0, 2)) ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($employee['full_name']) ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?= htmlspecialchars($employee['email']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= ucfirst($employee['role']) ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 60px; height: 8px;">
                                                        <div class="progress-bar bg-primary" 
                                                             style="width: <?= ($employee['performance_rating'] / 5) * 100 ?>%"></div>
                                                    </div>
                                                    <span><?= number_format($employee['performance_rating'], 1) ?>/5</span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                $productivity = $employee['time_performance']['productivity_rating'];
                                                if ($productivity === 'Excellent') {
                                                    $badgeClass = 'bg-success';
                                                } elseif ($productivity === 'Very Good') {
                                                    $badgeClass = 'bg-info';
                                                } elseif ($productivity === 'Good') {
                                                    $badgeClass = 'bg-primary';
                                                } elseif ($productivity === 'Fair') {
                                                    $badgeClass = 'bg-warning';
                                                } else {
                                                    $badgeClass = 'bg-danger';
                                                }
                                                ?>
                                                <span class="badge <?= $badgeClass ?>"><?= $productivity ?></span>
                                            </td>
                                            <td>
                                                <strong><?= number_format($employee['time_performance']['total_hours'], 1) ?>h</strong>
                                            </td>
                                            <td><?= number_format($employee['time_performance']['avg_hours_per_day'], 1) ?>h</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 50px; height: 8px;">
                                                        <div class="progress-bar bg-success" 
                                                             style="width: <?= $employee['time_performance']['punctuality_score'] ?>%"></div>
                                                    </div>
                                                    <span><?= $employee['time_performance']['punctuality_score'] ?>%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 50px; height: 8px;">
                                                        <div class="progress-bar bg-info" 
                                                             style="width: <?= $employee['time_performance']['attendance_rate'] ?>%"></div>
                                                    </div>
                                                    <span><?= $employee['time_performance']['attendance_rate'] ?>%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                $status = $employee['current_status']['status'];
                                                if ($status === 'clocked_in') {
                                                    $statusClass = 'text-success';
                                                    $statusIcon = 'fas fa-play-circle';
                                                } elseif ($status === 'on_break') {
                                                    $statusClass = 'text-warning';
                                                    $statusIcon = 'fas fa-pause-circle';
                                                } else {
                                                    $statusClass = 'text-muted';
                                                    $statusIcon = 'fas fa-stop-circle';
                                                }
                                                ?>
                                                <span class="<?= $statusClass ?>">
                                                    <i class="<?= $statusIcon ?>"></i> 
                                                    <?= ucwords(str_replace('_', ' ', $status)) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="/employees/performance?user_id=<?= $employee['user_id'] ?>&days=<?= $days ?>" 
                                                       class="btn btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="/employees/viewEmployee/<?= $employee['user_id'] ?>" 
                                                       class="btn btn-outline-secondary" title="Employee Profile">
                                                        <i class="fas fa-user"></i>
                                                    </a>
                                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                                        <a href="/employees/edit/<?= $employee['user_id'] ?>" 
                                                           class="btn btn-outline-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="fas fa-users fa-3x mb-3"></i>
                                            <br>No employee performance data available
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.progress {
    background-color: #e9ecef;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    letter-spacing: 0.5px;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}
</style>

<script>
// Auto-submit form when filters change
document.getElementById('days').addEventListener('change', function() {
    this.form.submit();
});

document.getElementById('sort').addEventListener('change', function() {
    this.form.submit();
});
</script>

<?php require_once VIEWSPATH . '/partials/footer.php'; ?>
