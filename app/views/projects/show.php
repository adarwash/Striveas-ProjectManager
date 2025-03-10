<div class="row mb-4">
    <div class="col-md-8">
        <h1><?= htmlspecialchars($project->title) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/projects">Projects</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($project->title) ?></li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 text-md-end d-flex justify-content-md-end align-items-center">
        <a href="/projects/edit/<?= $project->id ?>" class="btn btn-outline-secondary me-2">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteProjectModal">
            <i class="bi bi-trash"></i> Delete
        </button>
    </div>
</div>

<!-- Project Details -->
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
                        
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1 text-muted">Department</p>
                                <p class="mb-3">
                                    <?= isset($project->department_name) ? htmlspecialchars($project->department_name) : 'Not Assigned' ?>
                                </p>
                            </div>
                            <div class="col-md-8">
                                <p class="mb-1 text-muted">Budget</p>
                                <p class="mb-1">
                                    <strong>$<?= number_format($project->budget ?? 0, 2) ?></strong> 
                                    of $<?= number_format($project->department_budget ?? 0, 2) ?>
                                </p>
                                
                                <?php 
                                // Calculate budget percentage used
                                $budgetPercentage = 0;
                                if (($project->department_budget ?? 0) > 0) {
                                    $budgetPercentage = (($project->budget ?? 0) / ($project->department_budget ?? 0)) * 100;
                                }
                                
                                // Determine appropriate color based on percentage
                                $progressClass = 'bg-success';
                                if ($budgetPercentage > 70) {
                                    $progressClass = 'bg-warning';
                                }
                                if ($budgetPercentage > 90) {
                                    $progressClass = 'bg-danger';
                                }
                                ?>
                                
                                <div class="progress mb-3" style="height: 10px;">
                                    <div class="progress-bar <?= $progressClass ?>" role="progressbar" 
                                         style="width: <?= min($budgetPercentage, 100) ?>%" 
                                         aria-valuenow="<?= $budgetPercentage ?>" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="text-muted"><?= number_format($budgetPercentage, 1) ?>% of department budget</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1 text-muted">Created By</p>
                                <p class="mb-3"><?= $project->created_by ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Progress</h5>
                        <?php 
                        $completed = 0;
                        $total = count($tasks) > 0 ? count($tasks) : 1;
                        foreach ($tasks as $task) {
                            if ($task->status === 'Completed') {
                                $completed++;
                            }
                        }
                        $progress = ($completed / $total) * 100;
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
                        
                        <?php if ($project->status !== 'Completed' && $progress === 100): ?>
                            <div class="alert alert-success mt-3 small">
                                <i class="bi bi-check-circle-fill"></i> 
                                All tasks are completed! You can now mark this project as completed.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Tasks</h5>
                <a href="/tasks/create/<?= $project->id ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg"></i> New Task
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($tasks)): ?>
                    <div class="p-4 text-center">
                        <p class="text-muted mb-0">No tasks yet. Add a task to get started.</p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($tasks as $task): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <?php 
                                    $taskStatusClass = 'bg-secondary';
                                    if ($task->status === 'Pending') $taskStatusClass = 'bg-secondary';
                                    if ($task->status === 'In Progress') $taskStatusClass = 'bg-primary';
                                    if ($task->status === 'Completed') $taskStatusClass = 'bg-success';
                                    if ($task->status === 'Testing') $taskStatusClass = 'bg-info';
                                    if ($task->status === 'Blocked') $taskStatusClass = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $taskStatusClass ?> me-2"><?= $task->status ?></span>
                                    <a href="/tasks/show/<?= $task->id ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($task->title) ?>
                                    </a>
                                    <?php if ($task->priority === 'High' || $task->priority === 'Critical'): ?>
                                        <span class="badge bg-danger ms-1">
                                            <?= $task->priority === 'Critical' ? '!' : '' ?>
                                            <?= $task->priority ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <a href="/tasks/edit/<?= $task->id ?>" class="btn btn-sm btn-outline-secondary me-1">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (count($tasks) > 5): ?>
                        <div class="card-footer text-center">
                            <a href="/tasks?project_id=<?= $project->id ?>" class="text-decoration-none">
                                View all tasks
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
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