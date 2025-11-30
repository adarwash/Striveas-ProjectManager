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

    <?php 
        $activity_summary = $activity_summary ?? [
            'project_updates' => 0,
            'task_updates' => 0,
            'task_completions' => 0,
            'ticket_replies' => 0,
            'login_count' => 0
        ];
    ?>
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Project Updates</h6>
                        <h2 class="mb-0"><?= number_format($activity_summary['project_updates']) ?></h2>
                    </div>
                    <span class="badge bg-primary rounded-circle p-3">
                        <i class="fas fa-diagram-project"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Task Updates</h6>
                        <h2 class="mb-0"><?= number_format($activity_summary['task_updates']) ?></h2>
                    </div>
                    <span class="badge bg-success rounded-circle p-3">
                        <i class="fas fa-list-check"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Tasks Closed</h6>
                        <h2 class="mb-0"><?= number_format($activity_summary['task_completions']) ?></h2>
                    </div>
                    <span class="badge bg-warning rounded-circle p-3">
                        <i class="fas fa-check-circle"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Ticket Replies</h6>
                        <h2 class="mb-0"><?= number_format($activity_summary['ticket_replies']) ?></h2>
                    </div>
                    <span class="badge bg-info rounded-circle p-3">
                        <i class="fas fa-headset"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Successful Logins</h6>
                        <h2 class="mb-0"><?= number_format($activity_summary['login_count']) ?></h2>
                    </div>
                    <span class="badge bg-secondary rounded-circle p-3">
                        <i class="fas fa-right-to-bracket"></i>
                    </span>
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

    <?php if (!empty($employees) && !empty($daily_tracker_dates)): ?>
    <!-- Daily Activity Tracker -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-0">Daily Activity Tracker</h5>
                        <small class="text-muted">Hours logged per employee across the last <?= count($daily_tracker_dates) ?> days</small>
                    </div>
                    <span class="badge bg-light text-muted">Darker cells indicate longer work days</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-muted small">Employee</th>
                                    <?php foreach ($daily_tracker_dates as $trackerDate): ?>
                                        <th class="text-center text-muted small">
                                            <?= date('M j', strtotime($trackerDate)) ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($employees as $employee): ?>
                                <tr>
                                    <td class="fw-semibold text-nowrap">
                                        <?= htmlspecialchars($employee['full_name']) ?>
                                        <div class="text-muted small"><?= htmlspecialchars($employee['role']) ?></div>
                                    </td>
                                    <?php foreach ($employee['daily_tracker'] ?? [] as $dateKey => $hours): ?>
                                        <?php
                                            $intensity = min(1, ($hours / 8));
                                            $label = $hours > 0 ? number_format($hours, 1) . ' h' : '-';
                                        ?>
                                        <td class="text-center">
                                            <div class="daily-tracker-cell" 
                                                 style="background-color: rgba(59,130,246,<?= $intensity ?>); color: <?= $hours > 0 ? '#fff' : '#6b7280' ?>;" 
                                                 title="<?= date('l, M j', strtotime($dateKey)) ?> · <?= $label ?>">
                                                <?= $hours > 0 ? number_format($hours, 1) : '&nbsp;' ?>
                                            </div>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
                                    <th>Task Updates</th>
                                    <th>Tasks Closed</th>
                                    <th>Project Touches</th>
                                    <th>Ticket Replies</th>
                                    <th>Logins</th>
                                    <th>Last Activity</th>
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
                                            <?php $activityStats = $employee['activity_stats'] ?? []; ?>
                                            <td>
                                                <span class="badge bg-light text-success border fw-semibold px-3">
                                                    <?= $activityStats['task_updates'] ?? 0 ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-warning border fw-semibold px-3">
                                                    <?= $activityStats['task_completions'] ?? 0 ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-primary border fw-semibold px-3">
                                                    <?= $activityStats['project_updates'] ?? 0 ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-info border fw-semibold px-3">
                                                    <?= $activityStats['ticket_replies'] ?? 0 ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-secondary border fw-semibold px-3">
                                                    <?= $activityStats['login_count'] ?? 0 ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $latestActivity = $activityStats['recent_activity'][0] ?? null;
                                                    echo $latestActivity ? formatDateTime($latestActivity['created_at']) : '<span class="text-muted">No recent activity</span>';
                                                ?>
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
                                        <td colspan="16" class="text-center text-muted py-4">
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

.daily-tracker-cell {
    min-width: 48px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.75rem;
    display: inline-block;
    padding: 0.35rem 0.25rem;
    line-height: 1;
    transition: transform 0.1s ease;
}

.daily-tracker-cell:hover {
    transform: scale(1.05);
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
