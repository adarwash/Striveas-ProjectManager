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
    <!-- My Tasks -->
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">My Tasks</h5>
                <a href="/tasks" class="btn btn-sm btn-light text-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($user_tasks)): ?>
                    <p class="text-center text-muted mb-0">You have no assigned tasks.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th class="d-none d-md-table-cell">Project</th>
                                    <th>Status</th>
                                    <th>Due</th>
                                    <th class="d-none d-md-table-cell">Priority</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($user_tasks as $task): ?>
                                    <?php 
                                        $statusClass = 'bg-secondary';
                                        if ($task->status === 'In Progress') $statusClass = 'bg-info';
                                        if ($task->status === 'Completed') $statusClass = 'bg-success';
                                        if ($task->status === 'Pending') $statusClass = 'bg-secondary';
                                        if ($task->status === 'Blocked') $statusClass = 'bg-danger';
                                        if ($task->status === 'Testing') $statusClass = 'bg-primary';
                                        
                                        $priorityClass = 'bg-light text-dark';
                                        if ($task->priority === 'High' || $task->priority === 'Critical') $priorityClass = 'bg-danger';
                                        if ($task->priority === 'Medium') $priorityClass = 'bg-warning text-dark';
                                        if ($task->priority === 'Low') $priorityClass = 'bg-success';
                                        
                                        $dueText = 'No due date';
                                        $dueClass = '';
                                        if (!empty($task->due_date)) {
                                            $dueTs = strtotime($task->due_date);
                                            if ($dueTs) {
                                                $now = time();
                                                $daysDiff = floor(($dueTs - $now) / 86400);
                                                $dueText = date('M j', $dueTs);
                                                if ($daysDiff < 0) {
                                                    $dueText = 'Overdue (' . date('M j', $dueTs) . ')';
                                                    $dueClass = 'text-danger fw-semibold';
                                                } elseif ($daysDiff <= 2) {
                                                    $dueClass = 'text-warning fw-semibold';
                                                } else {
                                                    $dueClass = 'text-muted';
                                                }
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold mb-1"><?= htmlspecialchars($task->title) ?></div>
                                            <div class="small text-muted d-md-none">
                                                <?= !empty($task->project_title) ? htmlspecialchars($task->project_title) : 'No project' ?>
                                            </div>
                                        </td>
                                        <td class="d-none d-md-table-cell text-muted">
                                            <?= !empty($task->project_title) ? htmlspecialchars($task->project_title) : 'No project' ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($task->status ?? 'Pending') ?></span>
                                        </td>
                                        <td class="<?= $dueClass ?>"><?= $dueText ?></td>
                                        <td class="d-none d-md-table-cell">
                                            <span class="badge <?= $priorityClass ?>"><?= htmlspecialchars($task->priority ?? 'Normal') ?></span>
                                        </td>
                                        <td class="text-end">
                                            <a href="/tasks/show/<?= (int)$task->id ?>" class="btn btn-sm btn-outline-primary">Open</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Budget Usage by Department -->
    <div class="col-md-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Department Budget Usage</h5>
            </div>
            <div class="card-body">
                <?php if (empty($budget_usage)): ?>
                    <p class="text-center text-muted">No budget data available.</p>
                <?php else: ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Department</span>
                                <span>Usage</span>
                            </div>
                            <?php foreach ($budget_usage as $budget): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span><?= htmlspecialchars($budget->name) ?></span>
                                        <span>
                                            $<?= number_format($budget->used_budget ?? 0, 2) ?> / 
                                            $<?= number_format($budget->total_budget ?? 0, 2) ?> 
                                            (<?= number_format(min($budget->percentage ?? 0, 100), 1) ?>%)
                                        </span>
                                    </div>
                                    <?php 
                                    // Determine appropriate color based on percentage
                                    $progressClass = 'bg-success';
                                    $percentage = $budget->percentage ?? 0;
                                    if ($percentage > 70) {
                                        $progressClass = 'bg-warning';
                                    }
                                    if ($percentage > 90) {
                                        $progressClass = 'bg-danger';
                                    }
                                    ?>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar <?= $progressClass ?>" role="progressbar" 
                                             style="width: <?= min($percentage, 100) ?>%" 
                                             aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="text-end mt-3">
                                <a href="/departments" class="btn btn-sm btn-primary">View Departments</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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