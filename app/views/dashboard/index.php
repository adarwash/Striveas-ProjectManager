<?php
// Set title for the page
$title = 'Dashboard - HiveITPortal';
?>

<?php require VIEWSPATH . '/partials/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1 class="mb-3">Dashboard</h1>
        <p class="text-muted">Welcome back, <?= $_SESSION['user_name'] ?>!</p>
    </div>
</div>

<!-- Stats Overview -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Projects</h6>
                        <h3 class="mb-0"><?= count($projects) ?></h3>
                    </div>
                    <div class="bg-light rounded-circle p-3">
                        <i class="bi bi-briefcase fs-4 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Active Projects</h6>
                        <h3 class="mb-0"><?= isset($project_counts['Active']) ? $project_counts['Active'] : 0 ?></h3>
                    </div>
                    <div class="bg-light rounded-circle p-3">
                        <i class="bi bi-play-circle fs-4 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Tasks</h6>
                        <h3 class="mb-0"><?= count($tasks) ?></h3>
                    </div>
                    <div class="bg-light rounded-circle p-3">
                        <i class="bi bi-list-check fs-4 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Departments</h6>
                        <h3 class="mb-0"><?= count($departments) ?></h3>
                    </div>
                    <div class="bg-light rounded-circle p-3">
                        <i class="bi bi-building fs-4 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- My Assigned Tasks -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">My Assigned Tasks</h5>
                <a href="/tasks" class="btn btn-sm btn-outline-primary">View All Tasks</a>
            </div>
            <div class="card-body">
                <?php if (empty($assigned_tasks)) : ?>
                    <p class="text-muted">You don't have any tasks assigned to you yet.</p>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Project</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assigned_tasks as $task) : ?>
                                    <tr>
                                        <td>
                                            <a href="/tasks/show/<?= $task->id ?>"><?= htmlspecialchars($task->title) ?></a>
                                        </td>
                                        <td>
                                            <a href="/projects/viewProject/<?= $task->project_id ?>"><?= htmlspecialchars($task->project_title) ?></a>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = 'bg-secondary';
                                            if ($task->status === 'Pending') $statusClass = 'bg-secondary';
                                            if ($task->status === 'In Progress') $statusClass = 'bg-primary';
                                            if ($task->status === 'Completed') $statusClass = 'bg-success';
                                            if ($task->status === 'Cancelled') $statusClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $statusClass ?>"><?= $task->status ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $priorityClass = 'bg-secondary';
                                            if ($task->priority === 'Low') $priorityClass = 'bg-info';
                                            if ($task->priority === 'Medium') $priorityClass = 'bg-warning';
                                            if ($task->priority === 'High') $priorityClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $priorityClass ?>"><?= $task->priority ?></span>
                                        </td>
                                        <td><?= date('M j, Y', strtotime($task->due_date)) ?></td>
                                        <td>
                                            <a href="/tasks/show/<?= $task->id ?>" class="btn btn-sm btn-outline-primary">View</a>
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

<!-- Recent Projects -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Projects</h5>
                <a href="/projects" class="btn btn-sm btn-outline-primary">View All Projects</a>
            </div>
            <div class="card-body">
                <?php if (empty($projects)) : ?>
                    <p class="text-muted">No projects found.</p>
                <?php else : ?>
                    <div class="row">
                        <?php 
                        // Display only the 4 most recent projects
                        $recentProjects = array_slice($projects, 0, 4);
                        foreach ($recentProjects as $project) : 
                        ?>
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a href="/projects/viewProject/<?= $project->id ?>" class="text-decoration-none"><?= htmlspecialchars($project->title) ?></a>
                                        </h5>
                                        <div class="card-text text-muted small">
                                            <?php 
                                                $description = $project->description ?? '';
                                                echo strlen($description) > 100 ? substr(htmlspecialchars($description), 0, 100) . '...' : htmlspecialchars($description);
                                            ?>
                                        </div>
                                        <?php
                                        $statusClass = 'bg-secondary';
                                        if ($project->status === 'Active') $statusClass = 'bg-success';
                                        if ($project->status === 'On Hold') $statusClass = 'bg-warning';
                                        if ($project->status === 'Completed') $statusClass = 'bg-info';
                                        if ($project->status === 'Cancelled') $statusClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= $project->status ?></span>
                                    </div>
                                    <div class="card-footer bg-white border-0">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i> <?= date('M j, Y', strtotime($project->start_date)) ?> - <?= date('M j, Y', strtotime($project->end_date)) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require VIEWSPATH . '/partials/footer.php'; ?> 