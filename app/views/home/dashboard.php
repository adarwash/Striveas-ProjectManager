<?php
// Dashboard view for logged-in users
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Welcome, <?= htmlspecialchars($username) ?>!</h2>
                <p class="card-text">This is your project tracking dashboard. Here you can see your active projects and tasks.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Project Summary -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Project Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <div class="display-4 fw-bold text-primary"><?= $project_stats->active_count ?></div>
                        <div class="text-muted">Active</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="display-4 fw-bold text-success"><?= $project_stats->completed_count ?></div>
                        <div class="text-muted">Completed</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="display-4 fw-bold text-warning"><?= $project_stats->on_hold_count ?></div>
                        <div class="text-muted">On Hold</div>
                    </div>
                </div>
                <div class="text-end">
                    <a href="/projects" class="btn btn-sm btn-primary">View All Projects</a>
                    <a href="/projects/create" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-lg"></i> New Project
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Task Summary -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Task Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <div class="display-4 fw-bold text-primary"><?= $task_stats->pending_count ?></div>
                        <div class="text-muted">Pending</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="display-4 fw-bold text-info"><?= $task_stats->in_progress_count ?></div>
                        <div class="text-muted">In Progress</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="display-4 fw-bold text-success"><?= $task_stats->completed_count ?></div>
                        <div class="text-muted">Completed</div>
                    </div>
                </div>
                <div class="text-end">
                    <a href="/tasks" class="btn btn-sm btn-primary">View All Tasks</a>
                    <a href="/tasks/create" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-lg"></i> New Task
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Activity -->
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Recent Activity</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_activity)): ?>
                    <p class="text-center text-muted">No recent activity found.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recent_activity as $activity): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <?php 
                                    $badgeClass = 'bg-secondary';
                                    if ($activity->status === 'Pending') $badgeClass = 'bg-secondary';
                                    if ($activity->status === 'In Progress') $badgeClass = 'bg-info';
                                    if ($activity->status === 'Completed') $badgeClass = 'bg-success';
                                    if ($activity->status === 'Testing') $badgeClass = 'bg-primary';
                                    if ($activity->status === 'Blocked') $badgeClass = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $badgeClass ?> me-2">Task</span>
                                    <?php 
                                    $activity_text = '';
                                    if ($activity->status === 'In Progress') {
                                        $activity_text = 'Updated task "' . htmlspecialchars($activity->title) . '" status to In Progress';
                                    } elseif ($activity->status === 'Completed') {
                                        $activity_text = 'Completed project "' . htmlspecialchars($activity->project_title) . '"';
                                    } else {
                                        $activity_text = 'Created task "' . htmlspecialchars($activity->title) . '"';
                                    }
                                    ?>
                                    <span><?= $activity_text ?></span>
                                </div>
                                <small class="text-muted">
                                    <?php 
                                    $activity_time = !empty($activity->updated_at) ? strtotime($activity->updated_at) : time();
                                    $now = time();
                                    $diff = $now - $activity_time;
                                    
                                    if ($diff < 60) {
                                        echo 'Just now';
                                    } elseif ($diff < 3600) {
                                        echo floor($diff / 60) . ' minute' . (floor($diff / 60) > 1 ? 's' : '') . ' ago';
                                    } elseif ($diff < 86400) {
                                        echo 'Today, ' . date('g:i A', $activity_time);
                                    } elseif ($diff < 172800) {
                                        echo 'Yesterday, ' . date('g:i A', $activity_time);
                                    } else {
                                        echo date('M j, Y', $activity_time);
                                    }
                                    ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div> 