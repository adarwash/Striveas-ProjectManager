<?php require VIEWSPATH . '/partials/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/projects">Projects</a></li>
                <li class="breadcrumb-item"><a href="/projects/viewProject/<?= $project->id ?>"><?= $project->title ?></a></li>
                <li class="breadcrumb-item"><a href="/tasks/show/<?= $task->id ?>"><?= $task->title ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Manage Assignments</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Manage Assignments - <?= $task->title ?></h5>
                <div>
                    <a href="/tasks/show/<?= $task->id ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Task
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php flash('task_message'); ?>
                <?php flash('task_error'); ?>

                <form action="/tasks/assignUsers/<?= $task->id ?>" method="POST" id="assignments-form">
                    <div class="mb-3">
                        <h6>Current Assignments</h6>
                        <?php if (empty($assigned_users)) : ?>
                            <p class="text-muted">No users assigned to this task yet.</p>
                        <?php else : ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assigned_users as $user) : ?>
                                            <tr>
                                                <td><?= $user->name ?></td>
                                                <td><?= $user->email ?></td>
                                                <td>
                                                    <form action="/tasks/removeUser/<?= $task->id ?>/<?= $user->user_id ?>" method="POST" class="d-inline">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to remove this user from the task?')">
                                                            <i class="bi bi-trash"></i> Remove
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h6>Assign Users from Project Team</h6>
                        <?php if (empty($project_users)) : ?>
                            <div class="alert alert-info">
                                <p class="mb-0">No users are assigned to this project. <a href="/projects/manageTeam/<?= $project->id ?>">Manage project team</a> first.</p>
                            </div>
                        <?php else : ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Select</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Project Role</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($project_users as $user) : ?>
                                                    <tr>
                                                        <td>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="user_ids[]" value="<?= $user->user_id ?>" id="user-<?= $user->user_id ?>" 
                                                                    <?= in_array($user->user_id, $assigned_user_ids) ? 'checked' : '' ?>>
                                                            </div>
                                                        </td>
                                                        <td><?= $user->name ?></td>
                                                        <td><?= $user->email ?></td>
                                                        <td><span class="badge <?= $user->role == 'Manager' ? 'bg-primary' : 'bg-secondary' ?>"><?= $user->role ?></span></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/tasks/show/<?= $task->id ?>" class="btn btn-light me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary" <?= empty($project_users) ? 'disabled' : '' ?>>Save Assignments</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require VIEWSPATH . '/partials/footer.php'; ?> 