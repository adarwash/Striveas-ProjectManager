<?php
// Set title for the page
$title = htmlspecialchars($task->title) . ' - ' . DEFAULT_TITLE;
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
                <i class="bi bi-pencil"></i> Edit Task
            </a>
            <a href="/tasks/manageAssignments/<?= $task->id ?>" class="btn btn-light">
                <i class="bi bi-people"></i> Manage Assignments
            </a>
            <form action="/tasks/addQuickCallback/<?= (int)$task->id ?>" method="post">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-bell-fill"></i> Quick Follow-up
                </button>
            </form>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteTaskModal">
                <i class="bi bi-trash"></i> Delete
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
                <p class="card-text"><?= nl2br(htmlspecialchars($task->description)) ?></p>
                
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
                            <?php 
                                $assignee = $task->assigned_to_name ?? '';
                                echo $assignee !== '' 
                                    ? htmlspecialchars($assignee) 
                                    : '<span class="text-muted">Unassigned</span>';
                            ?>
                        </p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <h6>Created By</h6>
                        <p class="mb-0">
                            <?php 
                                $creator = $task->created_by_name ?? '';
                                echo $creator !== '' 
                                    ? htmlspecialchars($creator) 
                                    : '<span class="text-muted">Unknown</span>';
                            ?>
                        </p>
                    </div>
                </div>
                
                <!-- Assigned Users Section -->
                <div class="col-12 mt-4">
                    <h6>Assigned Users</h6>
                    
                    <?php if (empty($assigned_users)) : ?>
                        <p class="text-muted">No users assigned to this task. <a href="/tasks/manageAssignments/<?= $task->id ?>">Assign users</a></p>
                    <?php else : ?>
                        <div class="row">
                            <?php foreach ($assigned_users as $user) : ?>
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center">
										<?php 
											$dispName = $user->name ?? ($user->username ?? 'User');
											$initial = strtoupper(substr((string)$dispName, 0, 1));
										?>
										<div class="user-avatar me-2 bg-<?= strtolower($initial) ?>">
											<?= $initial ?>
                                        </div>
                                        <div>
											<div class="fs-6"><?= htmlspecialchars($dispName) ?></div>
											<small class="text-muted"><?= htmlspecialchars($user->email ?? ($user->username ?? '')) ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-2">
                            <a href="/tasks/manageAssignments/<?= $task->id ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-people"></i> Manage Assignments
                            </a>
                        </div>
                    <?php endif; ?>
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
        
        <!-- Subtasks -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Subtasks</h5>
                <span class="badge bg-light text-dark border"><?= isset($subtasks) ? count($subtasks) : 0 ?></span>
            </div>
            <div class="card-body">
                <?php if (!empty($subtasks)): ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Assignee</th>
                                <th>Priority</th>
                                <th>Due</th>
                                <th>Progress</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subtasks as $st): ?>
                            <?php
                                $stStatusClass = 'bg-secondary';
                                if (($st->status ?? '') === 'Pending') $stStatusClass = 'bg-secondary';
                                if (($st->status ?? '') === 'In Progress') $stStatusClass = 'bg-primary';
                                if (($st->status ?? '') === 'Completed') $stStatusClass = 'bg-success';
                                if (($st->status ?? '') === 'Testing') $stStatusClass = 'bg-info';
                                if (($st->status ?? '') === 'Blocked') $stStatusClass = 'bg-danger';
                                
                                $stPriorityClass = 'bg-secondary';
                                if (($st->priority ?? '') === 'Low') $stPriorityClass = 'bg-success';
                                if (($st->priority ?? '') === 'Medium') $stPriorityClass = 'bg-info';
                                if (($st->priority ?? '') === 'High') $stPriorityClass = 'bg-warning';
                                if (($st->priority ?? '') === 'Critical') $stPriorityClass = 'bg-danger';
                            ?>
                            <tr>
                                <td>
                                    <a href="/tasks/show/<?= (int)$st->id ?>" class="text-decoration-none fw-semibold">
                                        <?= htmlspecialchars($st->title ?? '') ?>
                                    </a>
                                    <?php if (!empty($st->references_text)): ?>
                                    <div class="small text-muted mt-1">
                                        Ref: <?= htmlspecialchars($st->references_text) ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= $stStatusClass ?>"><?= htmlspecialchars($st->status ?? 'Pending') ?></span></td>
                                <td><?= htmlspecialchars($st->assigned_to_name ?? '—') ?></td>
                                <td><span class="badge <?= $stPriorityClass ?>"><?= htmlspecialchars($st->priority ?? 'Medium') ?></span></td>
                                <td><?= !empty($st->due_date) ? date('M j, Y', strtotime($st->due_date)) : '—' ?></td>
                                <td style="min-width:160px;">
                                    <form action="/tasks/updateProgress/<?= (int)$st->id ?>" method="post" class="d-flex align-items-center gap-2">
                                        <input type="range" name="progress_percent" min="0" max="100" value="<?= (int)($st->progress_percent ?? 0) ?>" class="form-range" style="width:100px;">
                                        <span class="small text-muted"><?= (int)($st->progress_percent ?? 0) ?>%</span>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Save</button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <?php if (($st->status ?? '') !== 'Completed'): ?>
                                    <form action="/tasks/updateStatus/<?= (int)$st->id ?>" method="post" class="d-inline">
                                        <input type="hidden" name="status" value="Completed">
                                        <button class="btn btn-sm btn-outline-success"><i class="bi bi-check2-circle"></i></button>
                                    </form>
                                    <?php endif; ?>
                                    <a href="/tasks/edit/<?= (int)$st->id ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-muted">No subtasks yet.</div>
                <?php endif; ?>
                <hr>
                <h6 class="mb-3">Add Subtask</h6>
                <form action="/tasks/addSubtask/<?= (int)$task->id ?>" method="post">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <input type="text" name="title" class="form-control" placeholder="Subtask title" required>
                        </div>
                        <div class="col-md-3">
                            <select name="assigned_to" class="form-select">
                                <option value="">Unassigned</option>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $u): 
                                        $displayName = $u['full_name'] ?? ($u['name'] ?? ($u['username'] ?? 'User'));
                                    ?>
                                        <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($displayName) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="due_date" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <select name="priority" class="form-select">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-12">
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional details"></textarea>
                        </div>
                    </div>
                <div class="row g-2 mt-2">
                    <div class="col-12">
                        <input type="text" name="references_text" class="form-control" placeholder="References (links, ticket numbers, etc.)">
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-12">
                        <label class="form-label mb-1">Progress</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="range" name="progress_percent" min="0" max="100" value="0" class="form-range" style="width:200px;">
                            <span class="text-muted small" id="newSubtaskProgressValue">0%</span>
                        </div>
                    </div>
                </div>
                    <div class="mt-2 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Add Subtask
                        </button>
                    </div>
                </form>
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
                            <?php 
                                $creator = $task->created_by_name ?? '';
                                echo ($creator !== '' ? htmlspecialchars($creator) : 'Unknown user') . ' created this task.';
                            ?>
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
        
        <!-- Follow-ups / Reminders -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0"><i class="bi bi-bell text-primary me-2"></i>Follow-ups & Reminders</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 text-muted">Add Follow-up</h6>
                    <a href="/tasks/callbacksHistory/<?= (int)$task->id ?>" class="small text-decoration-none">
                        <i class="bi bi-clock-history me-1"></i>View History
                    </a>
                </div>
                <form action="/tasks/addCallback/<?= (int)$task->id ?>" method="post" class="mb-3">
                    <div class="mb-2">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g., Check progress with assignee" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Remind At</label>
                        <input type="datetime-local" name="remind_at" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Add context or next steps"></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="notify_all_task" name="notify_all">
                        <label class="form-check-label" for="notify_all_task">
                            Show in notifications for all users
                        </label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>Add Follow-up
                        </button>
                    </div>
                </form>

                <h6 class="text-muted mb-2">Upcoming</h6>
                <?php
                $pending = array_values(array_filter(($callbacks ?? []), function($c) { return ($c['status'] ?? '') === 'Pending'; }));
                usort($pending, function($a, $b) {
                    return strtotime($a['remind_at']) <=> strtotime($b['remind_at']);
                });
                ?>
                <?php if (!empty($pending)): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($pending as $cb): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="me-2">
                            <div class="fw-semibold text-truncate" style="max-width:220px;">
                                <?= htmlspecialchars($cb['title']) ?>
                            </div>
                            <div class="small text-muted">
                                <?= date('M j, Y g:i A', strtotime($cb['remind_at'])) ?>
                            </div>
                            <?php if (!empty($cb['notes'])): ?>
                            <div class="small text-muted text-truncate" style="max-width:260px;">
                                <?= htmlspecialchars($cb['notes']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <a href="/tasks/completeCallback/<?= (int)$cb['id'] ?>" class="btn btn-sm btn-outline-success" title="Mark Completed">
                                <i class="bi bi-check2-circle"></i>
                            </a>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="text-center py-2 text-muted small">No upcoming follow-ups.</div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    var newSubtaskSlider = document.querySelector('form[action^="/tasks/addSubtask"] input[name="progress_percent"]');
    var newSubtaskValue = document.getElementById('newSubtaskProgressValue');
    if (newSubtaskSlider && newSubtaskValue) {
        newSubtaskSlider.addEventListener('input', function() {
            newSubtaskValue.textContent = this.value + '%';
        });
    }
});
</script>

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