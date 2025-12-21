<?php require_once VIEWSPATH . '/partials/header.php'; ?>

<!-- Employee Performance Detail -->
<div class="container-fluid mt-4">
    <?php 
        $activityStats = $employee['activity_stats'] ?? [
            'project_updates' => 0,
            'ticket_replies' => 0,
            'login_count' => 0,
            'recent_activity' => []
        ];
    ?>
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

    <!-- Activity Contribution Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2">
                        <?= number_format($activityStats['project_updates'] ?? 0) ?>
                    </div>
                    <h6 class="card-title mb-0">Project Updates</h6>
                    <small class="text-muted">Interactions with projects</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2">
                        <?= number_format($activityStats['task_updates'] ?? 0) ?>
                    </div>
                    <h6 class="card-title mb-0">Task Updates</h6>
                    <small class="text-muted">Edits & progress changes</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2">
                        <?= number_format($activityStats['task_completions'] ?? 0) ?>
                    </div>
                    <h6 class="card-title mb-0">Tasks Closed</h6>
                    <small class="text-muted">Marked as complete</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 text-info mb-2">
                        <?= number_format($activityStats['ticket_replies'] ?? 0) ?>
                    </div>
                    <h6 class="card-title mb-0">Ticket Replies</h6>
                    <small class="text-muted">Support responses</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 text-secondary mb-2">
                        <?= number_format($activityStats['login_count'] ?? 0) ?>
                    </div>
                    <h6 class="card-title mb-0">Successful Logins</h6>
                    <small class="text-muted"><?= $employee['analysis_period'] ?> day window</small>
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

    <?php 
        $dailyTrends = $employee['time_performance']['trends'] ?? [];
        $dailyTrackerLabels = [];
        $dailyTrackerHours = [];
        $dailyTrackerEntries = [];
        foreach (array_reverse($dailyTrends) as $trend) { // chronological
            $dailyTrackerLabels[] = date('M j', strtotime($trend['work_date']));
            $dailyTrackerHours[] = round($trend['daily_hours'] ?? 0, 2);
            $dailyTrackerEntries[] = (int)($trend['entries_count'] ?? 0);
        }
    ?>

    <!-- Daily Tracker Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Daily Activity Tracker</h5>
                        <small class="text-muted">Hours logged and time entries over the last <?= $employee['analysis_period'] ?> days</small>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-center">
                            <strong class="d-block fs-5"><?= number_format(array_sum($dailyTrackerHours), 1) ?>h</strong>
                            <span class="text-muted small">Total hours</span>
                        </div>
                        <div class="text-center">
                            <strong class="d-block fs-5"><?= array_sum($dailyTrackerEntries) ?></strong>
                            <span class="text-muted small">Total entries</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($dailyTrackerLabels)): ?>
                        <canvas id="dailyTrackerChart" height="120"></canvas>
                    <?php else: ?>
                        <p class="text-muted mb-0">Not enough time tracking data to visualize.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Timeline -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Activity</h5>
                    <span class="badge bg-light text-muted"><?= count($activityStats['recent_activity'] ?? []) ?> events</span>
                </div>
                <div class="card-body">
                    <?php if (!empty($activityStats['recent_activity'])): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($activityStats['recent_activity'] as $activity): ?>
                                <?php
                                    $icon = 'bi bi-circle';
                                    $label = ucfirst($activity['action']);
                                    $link = null;
                                    if ($activity['entity_type'] === 'project' && !empty($activity['entity_id'])) {
                                        $icon = 'bi bi-kanban';
                                        $label .= ' project';
                                        $link = URLROOT . '/projects/viewProject/' . (int)$activity['entity_id'];
                                    } elseif ($activity['entity_type'] === 'task' && !empty($activity['entity_id'])) {
                                        $icon = 'bi bi-check2-square';
                                        $taskTitle = $activity['metadata']['task_title'] ?? null;
                                        $label = ucfirst(str_replace('_', ' ', $activity['action'])) . ' task';
                                        if ($taskTitle) {
                                            $label .= ' "' . $taskTitle . '"';
                                        }
                                        $link = URLROOT . '/tasks/show/' . (int)$activity['entity_id'];
                                    } elseif ($activity['entity_type'] === 'ticket' && !empty($activity['entity_id'])) {
                                        $icon = 'bi bi-ticket-detailed';
                                        $label .= ' ticket';
                                        $link = URLROOT . '/tickets/show/' . (int)$activity['entity_id'];
                                    } elseif ($activity['entity_type'] === 'note' && !empty($activity['entity_id'])) {
                                        $icon = 'bi bi-journal-text';
                                        $noteTitle = $activity['metadata']['note_title'] ?? null;
                                        $label = ucfirst(str_replace('_', ' ', $activity['action'])) . ' note';
                                        if ($noteTitle) {
                                            $label .= ' "' . $noteTitle . '"';
                                        }
                                        $link = URLROOT . '/notes/show/' . (int)$activity['entity_id'];
                                    } elseif ($activity['entity_type'] === 'client' && !empty($activity['entity_id'])) {
                                        $icon = 'bi bi-people';
                                        $clientName = $activity['metadata']['client_name'] ?? null;
                                        $label = ucfirst(str_replace('_', ' ', $activity['action'])) . ' client';
                                        if ($clientName) {
                                            $label .= ' "' . $clientName . '"';
                                        }
                                        $link = URLROOT . '/clients/viewClient/' . (int)$activity['entity_id'];
                                    } elseif ($activity['entity_type'] === 'site' && !empty($activity['entity_id'])) {
                                        $icon = 'bi bi-geo-alt';
                                        $siteName = $activity['metadata']['site_name'] ?? null;
                                        $label = ucfirst(str_replace('_', ' ', $activity['action'])) . ' site';
                                        if ($siteName) {
                                            $label .= ' "' . $siteName . '"';
                                        }
                                        $link = URLROOT . '/sites/viewSite/' . (int)$activity['entity_id'];
                                    } elseif ($activity['entity_type'] === 'request') {
                                        $icon = 'bi bi-activity';
                                        $label = ($activity['action'] === 'viewed') ? 'Viewed page' : 'Request';
                                    } elseif ($activity['entity_type'] === 'login') {
                                        $icon = 'bi bi-box-arrow-in-right';
                                        $label = 'Logged in';
                                    }
                                ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="<?= $icon ?> text-primary me-2"></i>
                                            <?php if ($link): ?>
                                                <a href="<?= $link ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($label) ?>
                                                </a>
                                            <?php else: ?>
                                                <span><?= htmlspecialchars($label) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($activity['description'])): ?>
                                                <div class="text-muted small"><?= htmlspecialchars($activity['description']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted"><?= formatDateTime($activity['created_at']) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No tracked activity during this period.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Engagement Summary</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span><i class="bi bi-kanban me-2 text-primary"></i>Project updates</span>
                            <strong><?= number_format($activityStats['project_updates'] ?? 0) ?></strong>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span><i class="bi bi-list-check me-2 text-success"></i>Task updates</span>
                            <strong><?= number_format($activityStats['task_updates'] ?? 0) ?></strong>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span><i class="bi bi-check-circle me-2 text-warning"></i>Tasks closed</span>
                            <strong><?= number_format($activityStats['task_completions'] ?? 0) ?></strong>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span><i class="bi bi-chat-left-text me-2 text-info"></i>Ticket replies</span>
                            <strong><?= number_format($activityStats['ticket_replies'] ?? 0) ?></strong>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span><i class="bi bi-box-arrow-in-right me-2 text-secondary"></i>Successful logins</span>
                            <strong><?= number_format($activityStats['login_count'] ?? 0) ?></strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php $taskActivityFeed = $activityStats['task_activity'] ?? []; ?>
    <?php if (!empty($taskActivityFeed)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Task Updates &amp; Closures</h5>
                    <span class="badge bg-light text-muted"><?= count($taskActivityFeed) ?> entries</span>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="text-muted small">
                            <tr>
                                <th>Task</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>When</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($taskActivityFeed as $entry): ?>
                                <?php 
                                    $taskTitle = $entry['metadata']['task_title'] ?? ('Task #' . $entry['entity_id']);
                                    $actionLabel = ucwords(str_replace('_', ' ', $entry['action']));
                                    $statusAfter = $entry['metadata']['new_status'] ?? null;
                                    $statusBefore = $entry['metadata']['old_status'] ?? null;
                                    $changes = $entry['metadata']['changes'] ?? [];
                                ?>
                                <tr>
                                    <td>
                                        <a href="<?= URLROOT ?>/tasks/show/<?= (int)$entry['entity_id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($taskTitle) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-semibold px-3">
                                            <?= htmlspecialchars($actionLabel) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($statusAfter): ?>
                                            <small class="text-muted">
                                                Status: <?= htmlspecialchars($statusBefore ?? 'N/A') ?> → <?= htmlspecialchars($statusAfter) ?>
                                            </small>
                                        <?php elseif (!empty($changes)): ?>
                                            <small class="text-muted">
                                                <?= count($changes) ?> field<?= count($changes) === 1 ? '' : 's' ?> updated
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($entry['description'] ?? 'Updated task') ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= formatDateTime($entry['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const dailyTrackerData = {
    labels: <?= json_encode($dailyTrackerLabels) ?>,
    hours: <?= json_encode($dailyTrackerHours) ?>,
    entries: <?= json_encode($dailyTrackerEntries) ?>
};

document.addEventListener('DOMContentLoaded', function() {
    const trackerCtx = document.getElementById('dailyTrackerChart');
    if (trackerCtx && dailyTrackerData.labels.length) {
        new Chart(trackerCtx, {
            data: {
                labels: dailyTrackerData.labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Hours Worked',
                        data: dailyTrackerData.hours,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.15)',
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'y'
                    },
                    {
                        type: 'bar',
                        label: 'Time Entries',
                        data: dailyTrackerData.entries,
                        backgroundColor: 'rgba(16, 185, 129, 0.6)',
                        borderRadius: 4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Hours' }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Entries' }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });
    }
});
</script>

<?php require_once VIEWSPATH . '/partials/footer.php'; ?> 
