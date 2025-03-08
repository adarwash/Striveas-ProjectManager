<!-- Project Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <h1><?= htmlspecialchars($project->title) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= URLROOT ?>/projects">Projects</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($project->title) ?></li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 text-md-end">
        <div class="btn-group">
            <a href="<?= URLROOT ?>/tasks/create?project_id=<?= $project->id ?>" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> New Task
            </a>
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="<?= URLROOT ?>/projects/edit/<?= $project->id ?>">
                        <i class="bi bi-pencil"></i> Edit Project
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= URLROOT ?>/projects/manageTeam/<?= $project->id ?>">
                        <i class="bi bi-people"></i> Manage Team
                    </a>
                </li>
                <li>
                    <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteProjectModal">
                        <i class="bi bi-trash"></i> Delete Project
                    </button>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- View Options -->
<div class="row mb-4">
    <div class="col-md-12">
        <ul class="nav nav-tabs" id="projectViewTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="details-tab" data-bs-toggle="tab" href="#details" role="tab">
                    <i class="bi bi-info-circle"></i> Details
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tasks-tab" data-bs-toggle="tab" href="#tasks" role="tab">
                    <i class="bi bi-list-task"></i> Tasks
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="gantt-tab" data-bs-toggle="tab" href="#gantt" role="tab">
                    <i class="bi bi-bar-chart"></i> Gantt Chart
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="calendar-tab" data-bs-toggle="tab" href="#calendar" role="tab">
                    <i class="bi bi-calendar3"></i> Calendar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="notes-tab" data-bs-toggle="tab" href="#notes" role="tab">
                    <i class="bi bi-journal-text"></i> Notes
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Tab Content -->
<div class="tab-content" id="projectViewTabContent">
    <!-- Details Tab -->
    <div class="tab-pane fade show active" id="details" role="tabpanel">
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Description</h5>
                        <p class="card-text"><?= nl2br(htmlspecialchars($project->description)) ?></p>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5>Details</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="mb-1 text-muted">Status</p>
                                        <div class="mb-3">
                                            <?php
                                            $statusClass = 'bg-secondary';
                                            if ($project->status === 'Active') $statusClass = 'bg-success';
                                            if ($project->status === 'On Hold') $statusClass = 'bg-warning';
                                            if ($project->status === 'Completed') $statusClass = 'bg-info';
                                            if ($project->status === 'Cancelled') $statusClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $statusClass ?>"><?= $project->status ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1 text-muted">Start Date</p>
                                        <p class="mb-3"><?= date('M j, Y', strtotime($project->start_date)) ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1 text-muted">End Date</p>
                                        <p class="mb-3"><?= date('M j, Y', strtotime($project->end_date)) ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>Progress</h5>
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
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?= $progress ?>%;" 
                                         aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?= round($progress) ?>%
                                    </div>
                                </div>
                                <div class="text-muted small">
                                    <?= $completed ?> of <?= $total ?> tasks completed
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Team Members Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Team Members</h5>
                            <a href="<?= URLROOT ?>/projects/manageTeam/<?= $project->id ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-people"></i> Manage Team
                            </a>
                        </div>
                        
                        <?php if (empty($assigned_users)) : ?>
                            <p class="text-muted">No team members assigned to this project.</p>
                        <?php else : ?>
                            <div class="row">
                                <?php foreach ($assigned_users as $user) : ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-3 bg-<?= strtolower(substr($user->name, 0, 1)) ?>">
                                                <?= strtoupper(substr($user->name, 0, 1)) ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($user->name) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($user->email) ?></small>
                                                <div>
                                                    <span class="badge <?= $user->role == 'Manager' ? 'bg-primary' : 'bg-secondary' ?>">
                                                        <?= htmlspecialchars($user->role) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Project Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <p class="mb-1 text-muted">Department</p>
                            <p><?= isset($project->department_name) ? htmlspecialchars($project->department_name) : 'Not Assigned' ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <p class="mb-1 text-muted">Budget</p>
                            <h4 class="text-primary">$<?= number_format($project->budget, 2) ?></h4>
                            <div class="progress mb-2" style="height: 5px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 45%"></div>
                            </div>
                            <small class="text-muted">45% of budget used</small>
                        </div>
                        
                        <div class="mb-3">
                            <p class="mb-1 text-muted">Created By</p>
                            <p><?= htmlspecialchars($project->created_by) ?></p>
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
        <?php require_once '../app/views/partials/notes_section.php'; ?>
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