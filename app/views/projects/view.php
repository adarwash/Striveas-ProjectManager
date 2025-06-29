<style>
/* Custom styling for the project view page */
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.timeline-simple .timeline-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: #f0f5ff;
    color: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.description-content {
    min-height: 50px;
    line-height: 1.6;
}

/* Make the tabs more stylish */
.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
}

.nav-tabs .nav-link:hover {
    color: #0d6efd;
    border-bottom-color: #0d6efd50;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom-color: #0d6efd;
    background-color: transparent;
    font-weight: 500;
}

/* Better hover effects for cards */
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}

/* Style for the list group items */
.list-group-item-action:hover {
    background-color: #f8f9fa;
}

/* Badge styling */
.badge.rounded-pill {
    font-weight: 500;
}
</style>

<!-- Project Header -->
<div class="bg-light rounded-3 p-4 mb-4">
        <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= URLROOT ?>/projects" class="text-decoration-none">Projects</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($project->title) ?></li>
            </ol>
        </nav>
    
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="h2 mb-1"><?= htmlspecialchars($project->title) ?></h1>
            <div class="d-flex align-items-center gap-3">
                <?php
                $statusClass = 'bg-secondary';
                if ($project->status === 'Active') $statusClass = 'bg-success';
                if ($project->status === 'On Hold') $statusClass = 'bg-warning';
                if ($project->status === 'Completed') $statusClass = 'bg-info';
                if ($project->status === 'Cancelled') $statusClass = 'bg-danger';
                ?>
                <span class="badge <?= $statusClass ?> rounded-pill px-3 py-2"><?= $project->status ?></span>
                <span class="text-muted"><i class="bi bi-calendar3 me-1"></i> <?= date('M j, Y', strtotime($project->start_date)) ?> - <?= date('M j, Y', strtotime($project->end_date)) ?></span>
            </div>
    </div>
        <div class="d-flex flex-wrap mt-2 mt-md-0 gap-2">
            <a href="<?= URLROOT ?>/tasks/create?project_id=<?= $project->id ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> New Task
            </a>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots"></i> Actions
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="<?= URLROOT ?>/projects/edit/<?= $project->id ?>">
                            <i class="bi bi-pencil me-2"></i> Edit Project
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= URLROOT ?>/projects/manageTeam/<?= $project->id ?>">
                            <i class="bi bi-people me-2"></i> Manage Team
                    </a>
                </li>
                    <li><hr class="dropdown-divider"></li>
                <li>
                    <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteProjectModal">
                            <i class="bi bi-trash me-2"></i> Delete Project
                    </button>
                </li>
            </ul>
            </div>
        </div>
    </div>
</div>

<!-- View Options -->
<ul class="nav nav-tabs nav-fill mb-4" id="projectViewTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="details-tab" data-bs-toggle="tab" href="#details" role="tab">
            <i class="bi bi-info-circle me-1"></i> Overview
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tasks-tab" data-bs-toggle="tab" href="#tasks" role="tab">
            <i class="bi bi-list-task me-1"></i> Tasks
            <span class="badge rounded-pill bg-secondary ms-1"><?= count($tasks) ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="gantt-tab" data-bs-toggle="tab" href="#gantt" role="tab">
            <i class="bi bi-bar-chart me-1"></i> Gantt Chart
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="calendar-tab" data-bs-toggle="tab" href="#calendar" role="tab">
            <i class="bi bi-calendar3 me-1"></i> Calendar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="notes-tab" data-bs-toggle="tab" href="#notes" role="tab">
            <i class="bi bi-journal-text me-1"></i> Notes
            <span class="badge rounded-pill bg-secondary ms-1"><?= count($notes) ?></span>
                </a>
            </li>
        </ul>

<!-- Tab Content -->
<div class="tab-content" id="projectViewTabContent">
    <!-- Details Tab -->
    <div class="tab-pane fade show active" id="details" role="tabpanel">
        <div class="row">
            <div class="col-lg-8">
                <!-- Description Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white p-3 border-bottom">
                        <h5 class="card-title mb-0"><i class="bi bi-file-text me-2 text-primary"></i>Description</h5>
                                        </div>
                    <div class="card-body p-4">
                        <div class="description-content">
                            <?php if (!empty(trim($project->description))): ?>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($project->description)) ?></p>
                            <?php else: ?>
                                <p class="text-muted fst-italic mb-0">No description provided.</p>
                            <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                <!-- Progress Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="bi bi-graph-up me-2 text-success"></i>Progress</h5>
                        <a href="#tasks" class="link-primary text-decoration-none small" data-bs-toggle="tab">View all tasks</a>
                    </div>
                    <div class="card-body p-4">
                                <?php 
                                $completed = 0;
                                $total = count($tasks);
                                foreach ($tasks as $task) {
                                    if ($task->status === 'Completed') {
                                        $completed++;
                                    }
                                }
                                $progress = $total > 0 ? ($completed / $total) * 100 : 0;
                                ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Tasks Completion</h6>
                            <h6 class="mb-0 fw-bold"><?= round($progress) ?>%</h6>
                        </div>
                        <div class="progress mb-3" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?= $progress ?>%;" 
                                         aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="text-muted small d-flex justify-content-between">
                            <span><?= $completed ?> of <?= $total ?> tasks completed</span>
                            <span><?= $total - $completed ?> remaining</span>
                        </div>
                    </div>
                </div>
                
                <!-- Team Members Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="bi bi-people me-2 text-info"></i>Team Members</h5>
                            <a href="<?= URLROOT ?>/projects/manageTeam/<?= $project->id ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-people"></i> Manage Team
                            </a>
                        </div>
                    <div class="card-body p-0">
                        <?php if (empty($assigned_users)) : ?>
                            <div class="p-4 text-center">
                                <div class="mb-3">
                                    <i class="bi bi-people display-4 text-muted"></i>
                                </div>
                                <h6>No team members assigned</h6>
                                <p class="text-muted small mb-4">Assign team members to collaborate on this project</p>
                                <a href="<?= URLROOT ?>/projects/manageTeam/<?= $project->id ?>" class="btn btn-primary">
                                    <i class="bi bi-person-plus"></i> Assign Members
                                </a>
                            </div>
                        <?php else : ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($assigned_users as $user) : ?>
                                    <div class="list-group-item border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-3" style="background-color: #<?= substr(md5($user->name), 0, 6) ?>">
                                                <?= strtoupper(substr($user->name, 0, 1)) ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0"><?= htmlspecialchars($user->name) ?></h6>
                                                <div class="d-flex align-items-center">
                                                    <small class="text-muted me-2"><?= htmlspecialchars($user->email) ?></small>
                                                    <span class="badge <?= $user->role == 'Manager' ? 'bg-primary' : 'bg-secondary' ?> rounded-pill">
                                                        <?= htmlspecialchars($user->role) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="mailto:<?= $user->email ?>"><i class="bi bi-envelope me-2"></i> Email</a></li>
                                                        <li><a class="dropdown-item" href="#"><i class="bi bi-chat me-2"></i> Message</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Linked Sites -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="bi bi-geo-alt me-2 text-warning"></i>Linked Sites</h5>
                        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])): ?>
                        <a href="<?= URLROOT ?>/projects/sites/<?= $project->id ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-link"></i> Manage Sites
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($linked_sites)) : ?>
                            <div class="p-4 text-center">
                                <div class="mb-3">
                                    <i class="bi bi-geo-alt display-4 text-muted"></i>
                                </div>
                                <h6>No sites linked to this project</h6>
                                <p class="text-muted small mb-4">Link sites to associate this project with physical locations</p>
                                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])): ?>
                                <a href="<?= URLROOT ?>/projects/sites/<?= $project->id ?>" class="btn btn-primary">
                                    <i class="bi bi-link"></i> Link Sites
                                </a>
                                <?php endif; ?>
                            </div>
                        <?php else : ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($linked_sites as $site) : ?>
                                    <div class="list-group-item border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <i class="bi bi-building fs-3 text-muted"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0"><?= htmlspecialchars($site['name']) ?></h6>
                                                    <a href="<?= URLROOT ?>/sites/viewSite/<?= $site['id'] ?>" class="btn btn-sm btn-light">
                                                        <i class="bi bi-arrow-right"></i> View
                                                    </a>
                                                </div>
                                                <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($site['location']) ?></small>
                                                <?php if (!empty($site['notes'])): ?>
                                                <div class="mt-2">
                                                    <small class="text-muted fst-italic"><?= htmlspecialchars($site['notes']) ?></small>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Project Info Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white p-3 border-bottom">
                        <h5 class="card-title mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Project Info</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                                <div>
                                    <span class="text-muted d-block small">Department</span>
                                    <strong><?= isset($project->department_name) ? htmlspecialchars($project->department_name) : 'Not Assigned' ?></strong>
                                </div>
                                <i class="bi bi-building text-muted"></i>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                                <div>
                                    <span class="text-muted d-block small">Created By</span>
                                    <strong><?= htmlspecialchars($project->created_by) ?></strong>
                                </div>
                                <i class="bi bi-person text-muted"></i>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                                <div>
                                    <span class="text-muted d-block small">Created On</span>
                                    <strong><?= date('M j, Y', strtotime($project->created_at)) ?></strong>
                                </div>
                                <i class="bi bi-calendar-date text-muted"></i>
                            </li>
                            <li class="list-group-item py-3 px-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">Budget</span>
                                    <h5 class="card-title text-primary mb-0"><?= $currency['symbol'] ?><?= number_format($project->budget, 2) ?></h5>
                        </div>
                                <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 45%"></div>
                            </div>
                                <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">45% of budget used</small>
                                    <strong><?= $currency['symbol'] ?><?= number_format($project->budget * 0.45, 2) ?></strong>
                                </div>
                            </li>
                            <li class="list-group-item py-3 px-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">Timeline</span>
                                    <span class="badge bg-info">
                                        <?php
                                        $start = new DateTime($project->start_date);
                                        $end = new DateTime($project->end_date);
                                        $now = new DateTime();
                                        $totalDays = $start->diff($end)->days;
                                        $daysElapsed = $start->diff($now)->days;
                                        
                                        if ($now < $start) {
                                            echo 'Not Started';
                                        } elseif ($now > $end) {
                                            echo 'Completed';
                                        } else {
                                            $percent = min(100, max(0, ($daysElapsed / $totalDays) * 100));
                                            echo round($percent) . '% Elapsed';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="progress mb-2" style="height: 8px;">
                                    <?php
                                    if ($now < $start) {
                                        $percent = 0;
                                    } elseif ($now > $end) {
                                        $percent = 100;
                                    } else {
                                        $percent = min(100, max(0, ($daysElapsed / $totalDays) * 100));
                                    }
                                    ?>
                                    <div class="progress-bar bg-info" role="progressbar" style="width: <?= $percent ?>%"></div>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span><?= date('M j, Y', strtotime($project->start_date)) ?></span>
                                    <span><?= date('M j, Y', strtotime($project->end_date)) ?></span>
                                </div>
                            </li>
                        </ul>
                    </div>
                        </div>
                        
                <!-- Recent Activity -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white p-3 border-bottom">
                        <h5 class="card-title mb-0"><i class="bi bi-clock-history me-2 text-danger"></i>Recent Activity</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="timeline-simple">
                            <?php 
                            // Combine notes and task activities
                            $activities = [];
                            
                            // Add notes to activities
                            foreach ($notes as $note) {
                                $activities[] = [
                                    'type' => 'note',
                                    'data' => $note,
                                    'date' => $note['created_at']
                                ];
                            }
                            
                            // Add task activities to activities
                            foreach ($task_activities as $task) {
                                $activities[] = [
                                    'type' => 'task',
                                    'data' => $task,
                                    'date' => ($task->activity_type === 'updated' && !empty($task->updated_at)) ? $task->updated_at : $task->created_at
                                ];
                            }
                            
                            // Sort activities by date (newest first)
                            usort($activities, function($a, $b) {
                                return strtotime($b['date']) - strtotime($a['date']);
                            });
                            
                            // Get the 5 most recent activities
                            $recentActivities = array_slice($activities, 0, 5);
                            
                            if (empty($recentActivities)): 
                            ?>
                                <div class="p-4 text-center">
                                    <p class="text-muted mb-0">No recent activity to display</p>
                                </div>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                <?php foreach ($recentActivities as $activity): ?>
                                    <li class="list-group-item py-3 px-4">
                                        <div class="d-flex">
                                            <div class="timeline-icon me-3">
                                                <?php if ($activity['type'] === 'note'): ?>
                                                    <i class="bi bi-chat-left-text"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-clipboard-check"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <?php if ($activity['type'] === 'note'): ?>
                                                    <!-- Note content -->
                                                    <p class="mb-1"><?= htmlspecialchars(substr($activity['data']['content'] ?? '', 0, 100)) ?><?= strlen($activity['data']['content'] ?? '') > 100 ? '...' : '' ?></p>
                                                    <div class="d-flex align-items-center text-muted small">
                                                        <i class="bi bi-person me-1"></i>
                                                        <span><?= htmlspecialchars($activity['data']['created_by_name'] ?? $activity['data']['created_by'] ?? 'Unknown') ?></span>
                                                        <span class="mx-2">•</span>
                                                        <i class="bi bi-clock me-1"></i>
                                                        <span><?= $activity['data']['created_at'] ? date('M j, g:i a', strtotime($activity['data']['created_at'])) : 'Unknown date' ?></span>
                                                    </div>
                                                <?php else: ?>
                                                    <!-- Task activity -->
                                                    <p class="mb-1">
                                                        <span class="fw-medium"><?= $activity['data']->activity_type === 'updated' ? 'Updated' : 'Created' ?> task:</span>
                                                        <a href="<?= URLROOT ?>/tasks/show/<?= $activity['data']->id ?>" class="text-decoration-none">
                                                            <?= htmlspecialchars($activity['data']->title) ?>
                                                        </a>
                                                        <?php if ($activity['data']->activity_type === 'updated'): ?>
                                                            <span class="badge <?= getTaskStatusClass($activity['data']->status) ?> ms-2"><?= $activity['data']->status ?></span>
                                                        <?php endif; ?>
                                                    </p>
                                                    <div class="d-flex align-items-center text-muted small">
                                                        <i class="bi bi-person me-1"></i>
                                                        <span><?= htmlspecialchars($activity['data']->created_by_name ?? 'Unknown') ?></span>
                                                        <span class="mx-2">•</span>
                                                        <i class="bi bi-clock me-1"></i>
                                                        <span>
                                                            <?php 
                                                            $taskDate = $activity['data']->activity_type === 'updated' ? $activity['data']->updated_at : $activity['data']->created_at;
                                                            echo date('M j, g:i a', strtotime($taskDate));
                                                            ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                                </ul>
                                <div class="p-3 text-center border-top">
                                    <a href="<?= URLROOT ?>/projects/activity/<?= $project->id ?>" class="text-decoration-none">View all activity</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks Tab -->
    <div class="tab-pane fade" id="tasks" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Tasks</h5>
                <a href="<?= URLROOT ?>/tasks/create?project_id=<?= $project->id ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-plus-lg"></i> New Task
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($tasks)): ?>
                    <div class="p-4 text-center">
                        <p class="text-muted mb-0">No tasks yet. Add a task to get started.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Assigned To</th>
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks as $task): ?>
                                    <tr>
                                        <td>
                                            <div class="task-title">
                                                <a href="<?= URLROOT ?>/tasks/show/<?= $task->id ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($task->title) ?>
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $taskStatusClass = 'bg-secondary';
                                            if ($task->status === 'Pending') $taskStatusClass = 'bg-secondary';
                                            if ($task->status === 'In Progress') $taskStatusClass = 'bg-primary';
                                            if ($task->status === 'Completed') $taskStatusClass = 'bg-success';
                                            if ($task->status === 'Testing') $taskStatusClass = 'bg-info';
                                            if ($task->status === 'Blocked') $taskStatusClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $taskStatusClass ?>"><?= $task->status ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $priorityClass = 'bg-secondary';
                                            if ($task->priority === 'Low') $priorityClass = 'bg-success';
                                            if ($task->priority === 'Medium') $priorityClass = 'bg-info';
                                            if ($task->priority === 'High') $priorityClass = 'bg-warning';
                                            if ($task->priority === 'Critical') $priorityClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $priorityClass ?>"><?= $task->priority ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($task->assigned_to ?? 'Unassigned') ?></td>
                                        <td><?= !empty($task->due_date) ? date('M j, Y', strtotime($task->due_date)) : '—' ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?= URLROOT ?>/tasks/show/<?= $task->id ?>" class="btn btn-outline-primary">View</a>
                                                <a href="<?= URLROOT ?>/tasks/edit/<?= $task->id ?>" class="btn btn-outline-secondary">Edit</a>
                                            </div>
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

    <!-- Gantt Chart Tab -->
    <div class="tab-pane fade" id="gantt" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="card-title mb-0">Project Timeline</h5>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="btn-group btn-group-sm">
                            <button id="zoom-in" class="btn btn-outline-secondary">
                                <i class="bi bi-zoom-in"></i>
                            </button>
                            <button id="zoom-out" class="btn btn-outline-secondary">
                                <i class="bi bi-zoom-out"></i>
                            </button>
                            <button id="today" class="btn btn-outline-primary">Today</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="gantt_here" style="width:100%; height:500px;"></div>
            </div>
        </div>
    </div>

    <!-- Calendar Tab -->
    <div class="tab-pane fade" id="calendar" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Project Calendar</h5>
            </div>
            <div class="card-body">
                <div id="calendar_here"></div>
            </div>
        </div>
    </div>

    <!-- Notes Tab -->
    <div class="tab-pane fade" id="notes" role="tabpanel">
        <?php 
        // Set the type and reference_id for the notes section
        $type = 'project';
        $reference_id = $project->id; 
        require_once '../app/views/partials/notes_section.php'; 
        ?>
    </div>
</div>

<!-- Delete Project Modal -->
<div class="modal fade" id="deleteProjectModal" tabindex="-1" aria-labelledby="deleteProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteProjectModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the project <strong><?= htmlspecialchars($project->title) ?></strong>?
                <p class="text-danger mt-2">
                    <i class="bi bi-exclamation-triangle"></i> 
                    This will permanently delete the project and all associated tasks.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="/projects/delete/<?= $project->id ?>" method="post">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Include dhtmlxGantt -->
<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

<!-- Include FullCalendar -->
<link href='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.css' rel='stylesheet' />
<link href='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Gantt Chart
    gantt.config.date_format = "%Y-%m-%d";
    gantt.init("gantt_here");
    
    // Load project and tasks data into Gantt
    gantt.parse({
        data: [
            {
                id: "p<?= $project->id ?>",
                text: "<?= htmlspecialchars(addslashes($project->title)) ?>",
                start_date: "<?= $project->start_date ?>",
                end_date: "<?= $project->end_date ?>",
                type: "project",
                open: true
            },
            <?php foreach ($tasks as $task): ?>
            {
                id: "t<?= $task->id ?>",
                text: "<?= htmlspecialchars(addslashes($task->title)) ?>",
                start_date: "<?= !empty($task->start_date) ? $task->start_date : $project->start_date ?>",
                end_date: "<?= !empty($task->due_date) ? $task->due_date : $project->end_date ?>",
                parent: "p<?= $project->id ?>",
                progress: <?= $task->status === 'Completed' ? '1' : ($task->status === 'In Progress' ? '0.5' : '0') ?>
            },
            <?php endforeach; ?>
        ]
    });

    // Initialize Calendar
    var calendarEl = document.getElementById('calendar_here');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: ['dayGrid'],
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek'
        },
        events: [
            {
                title: '<?= htmlspecialchars(addslashes($project->title)) ?>',
                start: '<?= $project->start_date ?>',
                end: '<?= $project->end_date ?>',
                color: '#007bff'
            },
            <?php foreach ($tasks as $task): ?>
            {
                title: '<?= htmlspecialchars(addslashes($task->title)) ?>',
                start: '<?= !empty($task->start_date) ? $task->start_date : $project->start_date ?>',
                end: '<?= !empty($task->due_date) ? $task->due_date : $project->end_date ?>',
                color: '<?= $task->status === 'Completed' ? '#28a745' : ($task->status === 'In Progress' ? '#007bff' : '#6c757d') ?>'
            },
            <?php endforeach; ?>
        ]
    });
    calendar.render();

    // Handle tab changes
    document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (e) {
            if (e.target.id === 'gantt-tab') {
                gantt.render(); // Refresh Gantt chart when tab is shown
            } else if (e.target.id === 'calendar-tab') {
                calendar.render(); // Refresh calendar when tab is shown
            }
        });
    });

    // Gantt zoom controls
    document.getElementById('zoom-in').addEventListener('click', function() {
        gantt.ext.zoom.zoomIn();
    });
    document.getElementById('zoom-out').addEventListener('click', function() {
        gantt.ext.zoom.zoomOut();
    });
    document.getElementById('today').addEventListener('click', function() {
        gantt.showDate(new Date());
    });
});
</script> 

<?php
// Helper function to get badge class for task status
function getTaskStatusClass($status) {
    switch ($status) {
        case 'Pending':
            return 'bg-secondary';
        case 'In Progress':
            return 'bg-primary';
        case 'Completed':
            return 'bg-success';
        case 'Testing':
            return 'bg-info';
        case 'Blocked':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}
?> 