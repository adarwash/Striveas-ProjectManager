<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Projects</h1>
    <a href="/projects/create" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> New Project
    </a>
</div>

<!-- Flash message (placeholder for when flash message functionality is added) -->
<div id="flash-message"></div>

<div class="row">
    <div class="col-md-12">
        <?php if (empty($projects)) : ?>
            <div class="alert alert-info">
                <p>No projects found. Click the "New Project" button to create your first project.</p>
            </div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project) : ?>
                            <tr>
                                <td>
                                    <a href="/projects/show/<?= $project->id ?>" class="fw-bold text-decoration-none">
                                        <?= htmlspecialchars($project->title) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php 
                                    $statusClass = 'bg-secondary';
                                    if ($project->status === 'Active') $statusClass = 'bg-primary';
                                    if ($project->status === 'Completed') $statusClass = 'bg-success';
                                    if ($project->status === 'On Hold') $statusClass = 'bg-warning';
                                    if ($project->status === 'Cancelled') $statusClass = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= htmlspecialchars($project->status) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $progress = 0;
                                    if (!empty($project->task_count) && $project->task_count > 0) {
                                        $progress = ($project->completed_tasks / $project->task_count) * 100;
                                    }
                                    ?>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: <?= $progress ?>%;" 
                                             aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?= $project->completed_tasks ?? 0 ?>/<?= $project->task_count ?? 0 ?> tasks
                                    </small>
                                </td>
                                <td><?= date('M d, Y', strtotime($project->start_date)) ?></td>
                                <td>
                                    <?= $project->end_date ? date('M d, Y', strtotime($project->end_date)) : 'N/A' ?>
                                </td>
                                <td><?= htmlspecialchars($project->created_by) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/projects/show/<?= $project->id ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/projects/edit/<?= $project->id ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                data-bs-toggle="modal" data-bs-target="#deleteModal<?= $project->id ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Delete Confirmation Modal -->
                                    <div class="modal fade" id="deleteModal<?= $project->id ?>" tabindex="-1" 
                                         aria-labelledby="deleteModalLabel<?= $project->id ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel<?= $project->id ?>">
                                                        Confirm Delete
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete the project 
                                                    <strong><?= htmlspecialchars($project->title) ?></strong>?
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
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div> 