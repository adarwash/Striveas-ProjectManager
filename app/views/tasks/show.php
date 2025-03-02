<?php
// Set title for the page
$title = htmlspecialchars($task->title) . ' - ProjectTracker';
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><?= htmlspecialchars($task->title) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tasks">Tasks</a></li>
                <li class="breadcrumb-item"><a href="/projects/viewProject/<?= $task->project_id ?>">Project</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($task->title) ?></li>
            </ol>
        </nav>
    </div>
    <div>
        <div class="btn-group">
            <a href="/tasks/edit/<?= $task->id ?>" class="btn btn-light">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteTaskModal">
                <i class="bi bi-trash me-1"></i> Delete
            </button>
        </div>
    </div>
</div>

<!-- Task Details -->
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="card-title">Task Details</h5>
                    <?php
                    $statusClass = 'bg-secondary';
                    if ($task->status === 'Pending') $statusClass = 'bg-secondary';
                    if ($task->status === 'In Progress') $statusClass = 'bg-primary';
                    if ($task->status === 'Completed') $statusClass = 'bg-success';
                    if ($task->status === 'Testing') $statusClass = 'bg-info';
                    if ($task->status === 'Blocked') $statusClass = 'bg-danger';
                    
                    $priorityClass = 'bg-secondary';
                    if ($task->priority === 'Low') $priorityClass = 'bg-success';
                    if ($task->priority === 'Medium') $priorityClass = 'bg-info';
                    if ($task->priority === 'High') $priorityClass = 'bg-warning';
                    if ($task->priority === 'Critical') $priorityClass = 'bg-danger';
                    ?>
                    <div>
                        <span class="badge <?= $statusClass ?> me-2"><?= $task->status ?></span>
                        <span class="badge <?= $priorityClass ?>"><?= $task->priority ?></span>
                    </div>
                </div>
                
                <h6>Description</h6>
                <p class="card-text mb-4"><?= nl2br(htmlspecialchars($task->description)) ?></p>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6>Project</h6>
                        <?php if (isset($task->project_id) && isset($task->project_title)): ?>
                            <p class="mb-0">
                                <a href="/projects/viewProject/<?= $task->project_id ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($task->project_title) ?>
                                </a>
                            </p>
                        <?php else: ?>
                            <p class="text-muted mb-0">No project assigned</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <h6>Due Date</h6>
                        <p class="mb-0">
                            <?php if (!empty($task->due_date)): ?>
                                <?= date('M j, Y', strtotime($task->due_date)) ?>
                                <?php 
                                // Check if task is overdue
                                if ($task->status !== 'Completed' && strtotime($task->due_date) < time()): 
                                ?>
                                    <span class="badge bg-danger ms-2">Overdue</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">No due date</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6>Assigned To</h6>
                        <p class="mb-0">
                            <?= !empty($task->assigned_to) ? htmlspecialchars($task->assigned_to) : '<span class="text-muted">Unassigned</span>' ?>
                        </p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <h6>Created By</h6>
                        <p class="mb-0">
                            <?= !empty($task->created_by) ? htmlspecialchars($task->created_by) : '<span class="text-muted">Unknown</span>' ?>
                        </p>
                    </div>
                </div>
                
                <?php if ($task->status !== 'Completed'): ?>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                    <form action="/tasks/updateStatus/<?= $task->id ?>" method="post" class="me-md-2">
                        <input type="hidden" name="status" value="Completed">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Mark as Completed
                        </button>
                    </form>
                    
                    <?php if ($task->status === 'Pending'): ?>
                    <form action="/tasks/updateStatus/<?= $task->id ?>" method="post">
                        <input type="hidden" name="status" value="In Progress">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-play-fill"></i> Start Working
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Task Comments/Activity -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Activity</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($task->created_at)): ?>
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                        <div class="bg-light rounded-circle p-2">
                            <i class="bi bi-plus-circle text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-0">Task Created</h6>
                            <small class="text-muted"><?= date('M j, Y g:i A', strtotime($task->created_at)) ?></small>
                        </div>
                        <p class="mb-0 text-muted small">
                            <?= !empty($task->created_by) ? htmlspecialchars($task->created_by) : 'Unknown user' ?> created this task.
                        </p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($task->updated_at) && $task->updated_at !== $task->created_at): ?>
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                        <div class="bg-light rounded-circle p-2">
                            <i class="bi bi-pencil text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-0">Task Updated</h6>
                            <small class="text-muted"><?= date('M j, Y g:i A', strtotime($task->updated_at)) ?></small>
                        </div>
                        <p class="mb-0 text-muted small">
                            Task was updated.
                        </p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Additional activity could be listed here -->
                
                <!-- Comments section could be added here if needed -->
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Task Info</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <p class="mb-1 text-muted">Created</p>
                    <p class="mb-0"><?= date('M j, Y', strtotime($task->created_at)) ?></p>
                </div>
                
                <?php if (!empty($task->updated_at) && $task->updated_at !== $task->created_at): ?>
                <div class="mb-3">
                    <p class="mb-1 text-muted">Last Updated</p>
                    <p class="mb-0"><?= date('M j, Y', strtotime($task->updated_at)) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($task->status === 'Completed' && !empty($task->completed_at)): ?>
                <div class="mb-3">
                    <p class="mb-1 text-muted">Completed</p>
                    <p class="mb-0"><?= date('M j, Y', strtotime($task->completed_at)) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (isset($task->project_id) && isset($task->project_title)): ?>
                <div class="d-grid mt-4">
                    <a href="/projects/viewProject/<?= $task->project_id ?>" class="btn btn-outline-primary">
                        <i class="bi bi-kanban"></i> View Project
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (isset($related_tasks) && !empty($related_tasks)): ?>
        <!-- Related Tasks from same project -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Related Tasks</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($related_tasks as $related): ?>
                        <?php if ($related->id !== $task->id): ?>
                        <li class="list-group-item">
                            <div class="d-flex align-items-center">
                                <?php
                                $relatedStatusClass = 'bg-secondary';
                                if ($related->status === 'Pending') $relatedStatusClass = 'bg-secondary';
                                if ($related->status === 'In Progress') $relatedStatusClass = 'bg-primary';
                                if ($related->status === 'Completed') $relatedStatusClass = 'bg-success';
                                if ($related->status === 'Testing') $relatedStatusClass = 'bg-info';
                                if ($related->status === 'Blocked') $relatedStatusClass = 'bg-danger';
                                ?>
                                <span class="badge <?= $relatedStatusClass ?> me-2"><?= $related->status ?></span>
                                <a href="/tasks/show/<?= $related->id ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($related->title) ?>
                                </a>
                            </div>
                        </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Task Modal -->
<div class="modal fade" id="deleteTaskModal" tabindex="-1" aria-labelledby="deleteTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTaskModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the task <strong><?= htmlspecialchars($task->title) ?></strong>?
                <p class="text-danger mt-2">
                    <i class="bi bi-exclamation-triangle"></i> 
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="/tasks/delete/<?= $task->id ?>" method="post">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div> 