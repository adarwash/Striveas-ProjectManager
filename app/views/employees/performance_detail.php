<?php require_once VIEWSPATH . '/partials/header.php'; ?>

<!-- Employee Performance Detail -->
<div class="container-fluid mt-4">
    <!-- Header with employee info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="avatar-lg me-3">
                        <span class="badge bg-primary rounded-circle p-3" style="font-size: 1.2rem;">
                            <?= strtoupper(substr($employee['full_name'], 0, 2)) ?>
                        </span>
                    </div>
                    <div>
                        <h1 class="h3 mb-0"><?= htmlspecialchars($employee['full_name']) ?></h1>
                        <p class="text-muted mb-1"><?= htmlspecialchars($employee['email']) ?> • <?= ucfirst($employee['role']) ?></p>
                        <span class="badge bg-<?= $employee['current_status']['status'] === 'clocked_in' ? 'success' : ($employee['current_status']['status'] === 'on_break' ? 'warning' : 'secondary') ?>">
                            <?= ucwords(str_replace('_', ' ', $employee['current_status']['status'])) ?>
                        </span>
                    </div>
                </div>
                <div class="btn-group">
                    <a href="/employees/performance" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <a href="/employees/viewEmployee/<?= $employee['user_id'] ?>" class="btn btn-outline-primary">
                        <i class="fas fa-user"></i> Employee Profile
                    </a>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="/employees/edit/<?= $employee['user_id'] ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Employee
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Overview Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2">
                        <?= number_format($employee['performance_rating'], 1) ?>
                    </div>
                    <h6 class="card-title">Performance Rating</h6>
                    <div class="progress">
                        <div class="progress-bar bg-primary" style="width: <?= ($employee['performance_rating'] / 5) * 100 ?>%"></div>
                    </div>
                    <small class="text-muted">Out of 5.0</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2">
                        <?= $employee['time_performance']['total_hours'] ?>h
                    </div>
                    <h6 class="card-title">Total Hours</h6>
                    <small class="text-muted">Last <?= $employee['analysis_period'] ?> days</small>
                    <div class="mt-2">
                        <span class="text-muted">Avg: <?= number_format($employee['time_performance']['avg_hours_per_day'], 1) ?>h/day</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-info mb-2">
                        <?= $employee['time_performance']['punctuality_score'] ?>%
                    </div>
                    <h6 class="card-title">Punctuality Score</h6>
                    <div class="progress">
                        <div class="progress-bar bg-info" style="width: <?= $employee['time_performance']['punctuality_score'] ?>%"></div>
                    </div>
                    <small class="text-muted">On-time arrivals</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="display-6 mb-2" style="color: 
                        <?= $employee['time_performance']['productivity_rating'] === 'Excellent' ? '#28a745' : 
                            ($employee['time_performance']['productivity_rating'] === 'Very Good' ? '#17a2b8' : 
                            ($employee['time_performance']['productivity_rating'] === 'Good' ? '#007bff' : 
                            ($employee['time_performance']['productivity_rating'] === 'Fair' ? '#ffc107' : '#dc3545'))) ?>">
                        <?= substr($employee['time_performance']['productivity_rating'], 0, 1) ?>
                    </div>
                    <h6 class="card-title">Productivity Rating</h6>
                    <span class="badge bg-<?= 
                        $employee['time_performance']['productivity_rating'] === 'Excellent' ? 'success' : 
                        ($employee['time_performance']['productivity_rating'] === 'Very Good' ? 'info' : 
                        ($employee['time_performance']['productivity_rating'] === 'Good' ? 'primary' : 
                        ($employee['time_performance']['productivity_rating'] === 'Fair' ? 'warning' : 'danger'))) ?>">
                        <?= $employee['time_performance']['productivity_rating'] ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Metrics -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Time Tracking Metrics (<?= $days ?> days)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="metric-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Attendance Rate</span>
                                    <strong><?= $employee['time_performance']['attendance_rate'] ?>%</strong>
                                </div>
                                <div class="progress mt-1">
                                    <div class="progress-bar bg-success" style="width: <?= $employee['time_performance']['attendance_rate'] ?>%"></div>
                                </div>
                            </div>
                            <div class="metric-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Consistency Score</span>
                                    <strong><?= $employee['time_performance']['consistency_score'] ?>%</strong>
                                </div>
                                <div class="progress mt-1">
                                    <div class="progress-bar bg-primary" style="width: <?= $employee['time_performance']['consistency_score'] ?>%"></div>
                                </div>
                            </div>
                            <div class="metric-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Break Efficiency</span>
                                    <strong><?= $employee['time_performance']['break_efficiency'] ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="metric-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Total Break Time</span>
                                    <strong><?= number_format($employee['time_performance']['total_break_minutes'] / 60, 1) ?>h</strong>
                                </div>
                            </div>
                            <div class="metric-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Avg Break Time/Day</span>
                                    <strong><?= number_format($employee['time_performance']['avg_break_minutes'], 0) ?> min</strong>
                                </div>
                            </div>
                            <div class="metric-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Tasks Completed</span>
                                    <strong><?= $employee['tasks_completed'] ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Current Status</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-<?= $employee['current_status']['status'] === 'clocked_in' ? 'play' : ($employee['current_status']['status'] === 'on_break' ? 'pause' : 'stop') ?>-circle fa-3x text-<?= $employee['current_status']['status'] === 'clocked_in' ? 'success' : ($employee['current_status']['status'] === 'on_break' ? 'warning' : 'muted') ?>"></i>
                        <h4 class="mt-2"><?= ucwords(str_replace('_', ' ', $employee['current_status']['status'])) ?></h4>
                        <p class="text-muted"><?= $employee['current_status']['message'] ?></p>
                    </div>
                    
                    <?php if ($employee['current_status']['status'] === 'clocked_in'): ?>
                        <div class="alert alert-info">
                            <small>
                                <strong>Started:</strong> <?= date('g:i A', strtotime($employee['current_status']['time_entry']['clock_in_time'])) ?><br>
                                <strong>Elapsed:</strong> <?= gmdate('H:i', $employee['current_status']['elapsed_work_time'] * 60) ?>
                            </small>
                        </div>
                    <?php elseif ($employee['current_status']['status'] === 'on_break'): ?>
                        <div class="alert alert-warning">
                            <small>
                                <strong>Break Started:</strong> <?= date('g:i A', strtotime($employee['current_status']['active_break']['break_start'])) ?><br>
                                <strong>Duration:</strong> <?= gmdate('H:i', $employee['current_status']['break_duration'] * 60) ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Time Tracking Trends -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daily Time Tracking History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>First Clock In</th>
                                    <th>Last Clock Out</th>
                                    <th>Total Hours</th>
                                    <th>Break Time</th>
                                    <th>Entries</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($employee['time_performance']['trends'])): ?>
                                    <?php foreach ($employee['time_performance']['trends'] as $day): ?>
                                        <tr>
                                            <td>
                                                <strong><?= date('M j, Y', strtotime($day['work_date'])) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= date('l', strtotime($day['work_date'])) ?></small>
                                            </td>
                                            <td>
                                                <?= $day['first_clock_in'] ? date('g:i A', strtotime($day['first_clock_in'])) : '-' ?>
                                                <?php if ($day['first_clock_in'] && date('H', strtotime($day['first_clock_in'])) <= 9): ?>
                                                    <span class="badge bg-success ms-1">On Time</span>
                                                <?php elseif ($day['first_clock_in']): ?>
                                                    <span class="badge bg-warning ms-1">Late</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $day['last_clock_out'] ? date('g:i A', strtotime($day['last_clock_out'])) : '-' ?></td>
                                            <td>
                                                <strong><?= number_format($day['daily_hours'], 1) ?>h</strong>
                                                <?php if ($day['daily_hours'] >= 8): ?>
                                                    <i class="fas fa-check-circle text-success ms-1"></i>
                                                <?php elseif ($day['daily_hours'] >= 6): ?>
                                                    <i class="fas fa-exclamation-triangle text-warning ms-1"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle text-danger ms-1"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= number_format($day['daily_break_minutes'] / 60, 1) ?>h</td>
                                            <td>
                                                <span class="badge bg-secondary"><?= $day['entries_count'] ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                $performance = 'Good';
                                                if ($day['daily_hours'] >= 8 && date('H', strtotime($day['first_clock_in'])) <= 9) {
                                                    $performance = 'Excellent';
                                                } elseif ($day['daily_hours'] >= 7) {
                                                    $performance = 'Good';
                                                } elseif ($day['daily_hours'] >= 5) {
                                                    $performance = 'Fair';
                                                } else {
                                                    $performance = 'Poor';
                                                }
                                                
                                                $performanceClass = match($performance) {
                                                    'Excellent' => 'bg-success',
                                                    'Good' => 'bg-primary',
                                                    'Fair' => 'bg-warning',
                                                    default => 'bg-danger'
                                                };
                                                ?>
                                                <span class="badge <?= $performanceClass ?>"><?= $performance ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-clock fa-2x mb-2"></i>
                                            <br>No time tracking data available for this period
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
.avatar-lg {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.metric-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.metric-item:last-child {
    border-bottom: none;
}

.progress {
    height: 6px;
    background-color: #e9ecef;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.display-6 {
    font-size: 2rem;
    font-weight: 600;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
}
</style>

<script>
// Add any interactive features here
document.addEventListener('DOMContentLoaded', function() {
    // You can add charts or interactive elements here
    console.log('Employee performance detail loaded');
});
</script>

<?php require_once VIEWSPATH . '/partials/footer.php'; ?> 