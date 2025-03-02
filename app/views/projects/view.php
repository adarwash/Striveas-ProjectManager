<!-- Main Container -->
<div class="d-flex h-100">
    <!-- Project Header -->
    <div class="container-fluid px-4 py-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <h4 class="mb-0 me-3"><?= htmlspecialchars($project->title) ?></h4>
                <div class="badge bg-light text-dark"><?= round($progress) ?>% Completed</div>
            </div>
            
            <div class="d-flex align-items-center">
                <!-- Team Members -->
                <div class="d-flex align-items-center me-4">
                    <div class="avatar-group">
                        <?php foreach ($team_members as $member): ?>
                            <div class="avatar" title="<?= htmlspecialchars($member->name) ?>">
                                <?php if (!empty($member->profile_picture)): ?>
                                    <img src="<?= $member->profile_picture ?>" alt="<?= htmlspecialchars($member->name) ?>">
                                <?php else: ?>
                                    <div class="avatar-initial"><?= strtoupper(substr($member->name, 0, 1)) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="btn btn-light btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                        + Add Member
                    </button>
                </div>

                <!-- Task Actions -->
                <div class="btn-group">
                    <button class="btn btn-light" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-filter"></i> Filters
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#">All Tasks</a>
                        <a class="dropdown-item" href="#">My Tasks</a>
                        <a class="dropdown-item" href="#">Priority Tasks</a>
                    </div>

                    <button class="btn btn-light" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-sort-down"></i> Sort
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#">Due Date</a>
                        <a class="dropdown-item" href="#">Priority</a>
                        <a class="dropdown-item" href="#">Created Date</a>
                    </div>

                    <a href="/tasks/create?project_id=<?= $project->id ?>" class="btn btn-dark">
                        + Add Task
                    </a>
                </div>
            </div>
        </div>

        <!-- Task Board -->
        <div class="row g-4">
            <!-- To-do Column -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="status-indicator bg-primary me-2"></div>
                            <h6 class="mb-0">To-do</h6>
                            <span class="badge bg-light text-dark ms-2"><?= count($todo_tasks) ?></span>
                        </div>
                        <button class="btn btn-link btn-sm p-0">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <?php foreach ($todo_tasks as $task): ?>
                            <div class="task-card mb-3">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0"><?= htmlspecialchars($task->title) ?></h6>
                                            <div class="dropdown">
                                                <button class="btn btn-link btn-sm p-0" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="/tasks/edit/<?= $task->id ?>">Edit</a>
                                                    <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteTaskModal<?= $task->id ?>">Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="card-text small text-muted mb-3"><?= htmlspecialchars($task->description) ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-calendar me-1"></i>
                                                <small><?= date('M j, Y', strtotime($task->due_date)) ?></small>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-group">
                                                    <?php foreach ($task->assigned_users as $user): ?>
                                                        <div class="avatar avatar-sm" title="<?= htmlspecialchars($user->name) ?>">
                                                            <?php if (!empty($user->profile_picture)): ?>
                                                                <img src="<?= $user->profile_picture ?>" alt="<?= htmlspecialchars($user->name) ?>">
                                                            <?php else: ?>
                                                                <div class="avatar-initial"><?= strtoupper(substr($user->name, 0, 1)) ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php
                                                $priorityClass = 'bg-secondary';
                                                if ($task->priority === 'Low') $priorityClass = 'bg-success';
                                                if ($task->priority === 'Medium') $priorityClass = 'bg-warning';
                                                if ($task->priority === 'High') $priorityClass = 'bg-danger';
                                                ?>
                                                <span class="badge <?= $priorityClass ?> ms-2"><?= $task->priority ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- In Progress Column -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="status-indicator bg-warning me-2"></div>
                            <h6 class="mb-0">In Progress</h6>
                            <span class="badge bg-light text-dark ms-2"><?= count($in_progress_tasks) ?></span>
                        </div>
                        <button class="btn btn-link btn-sm p-0">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <?php foreach ($in_progress_tasks as $task): ?>
                            <!-- Similar task card structure as To-do column -->
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Completed Column -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="status-indicator bg-success me-2"></div>
                            <h6 class="mb-0">Completed</h6>
                            <span class="badge bg-light text-dark ms-2"><?= count($completed_tasks) ?></span>
                        </div>
                        <button class="btn btn-link btn-sm p-0">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <?php foreach ($completed_tasks as $task): ?>
                            <!-- Similar task card structure as To-do column -->
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add custom CSS for the new design -->
<style>
.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.avatar-group {
    display: flex;
    align-items: center;
}

.avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    overflow: hidden;
    margin-left: -8px;
    border: 2px solid #fff;
    background-color: #e9ecef;
}

.avatar:first-child {
    margin-left: 0;
}

.avatar-sm {
    width: 24px;
    height: 24px;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-initial {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
    color: #fff;
    background-color: #6c757d;
}

.task-card .card {
    transition: all 0.3s ease;
}

.task-card .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}

.dropdown-toggle::after {
    display: none;
}
</style>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Team Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="/projects/addMember/<?= $project->id ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label">Select User</label>
                        <select class="form-select" name="user_id" required>
                            <option value="">Choose a user...</option>
                            <?php foreach ($available_users as $user): ?>
                                <option value="<?= $user->id ?>"><?= htmlspecialchars($user->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="member">Team Member</option>
                            <option value="leader">Team Leader</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Member</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize drag and drop functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add drag and drop functionality here if needed
});
</script> 