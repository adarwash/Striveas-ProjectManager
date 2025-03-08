<?php require VIEWSPATH . '/partials/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= URLROOT ?>/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= URLROOT ?>/projects">Projects</a></li>
                <li class="breadcrumb-item"><a href="<?= URLROOT ?>/projects/viewProject/<?= $project->id ?>"><?= $project->title ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Manage Team</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Manage Team Members - <?= $project->title ?></h5>
                <div>
                    <a href="<?= URLROOT ?>/projects/viewProject/<?= $project->id ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Project
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php flash('project_message'); ?>
                <?php flash('project_error'); ?>

                <form action="<?= URLROOT ?>/projects/assignUsers/<?= $project->id ?>" method="POST" id="team-form">
                    <div class="mb-3">
                        <h6>Current Team Members</h6>
                        <?php if (empty($assigned_users)) : ?>
                            <p class="text-muted">No team members assigned yet.</p>
                        <?php else : ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assigned_users as $user) : ?>
                                            <tr>
                                                <td><?= $user->name ?></td>
                                                <td><?= $user->email ?></td>
                                                <td>
                                                    <span class="badge <?= $user->role == 'Manager' ? 'bg-primary' : 'bg-secondary' ?>">
                                                        <?= $user->role ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <form action="<?= URLROOT ?>/projects/removeUser/<?= $project->id ?>/<?= $user->user_id ?>" method="POST" class="d-inline">
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to remove this user from the project?')">
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
                        <h6>Assign Users</h6>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Select</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($all_users as $user) : ?>
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input user-select" type="checkbox" name="user_ids[]" value="<?= $user['id'] ?>" id="user-<?= $user['id'] ?>" 
                                                                <?= isset($assigned_user_ids[$user['id']]) ? 'checked' : '' ?>>
                                                        </div>
                                                    </td>
                                                    <td><?= $user['full_name'] ?? $user['username'] ?></td>
                                                    <td><?= $user['email'] ?? $user['username'] ?></td>
                                                    <td>
                                                        <select name="roles[]" class="form-select role-select">
                                                            <option value="Member" <?= (isset($assigned_user_ids[$user['id']]) && $assigned_user_ids[$user['id']] == 'Member') ? 'selected' : '' ?>>Member</option>
                                                            <option value="Manager" <?= (isset($assigned_user_ids[$user['id']]) && $assigned_user_ids[$user['id']] == 'Manager') ? 'selected' : '' ?>>Manager</option>
                                                            <option value="Observer" <?= (isset($assigned_user_ids[$user['id']]) && $assigned_user_ids[$user['id']] == 'Observer') ? 'selected' : '' ?>>Observer</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= URLROOT ?>/projects/viewProject/<?= $project->id ?>" class="btn btn-light me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Team Assignments</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle checkboxes and role selects
        const checkboxes = document.querySelectorAll('.user-select');
        const roleSelects = document.querySelectorAll('.role-select');
        
        // Make sure roles are only submitted for selected users
        document.getElementById('team-form').addEventListener('submit', function(e) {
            checkboxes.forEach(function(checkbox, index) {
                if (!checkbox.checked) {
                    roleSelects[index].disabled = true;
                }
            });
        });
    });
</script>

<?php require VIEWSPATH . '/partials/footer.php'; ?> 