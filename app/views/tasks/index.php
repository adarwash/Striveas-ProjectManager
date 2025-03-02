<div class="row mb-4">
    <div class="col-md-8">
        <h1>Tasks</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tasks</li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="/tasks/create" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> New Task
        </a>
    </div>
</div>

<!-- Filters -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="/tasks" method="get" class="row g-3">
                    <div class="col-md-3">
                        <label for="project_id" class="form-label">Project</label>
                        <select class="form-select" id="project_id" name="project_id">
                            <option value="">All Projects</option>
                            <?php if (!empty($projects)): ?>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?= $project->id ?>" <?= isset($_GET['project_id']) && $_GET['project_id'] == $project->id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($project->title) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="Pending" <?= isset($_GET['status']) && $_GET['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="In Progress" <?= isset($_GET['status']) && $_GET['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="Testing" <?= isset($_GET['status']) && $_GET['status'] == 'Testing' ? 'selected' : '' ?>>Testing</option>
                            <option value="Completed" <?= isset($_GET['status']) && $_GET['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="Blocked" <?= isset($_GET['status']) && $_GET['status'] == 'Blocked' ? 'selected' : '' ?>>Blocked</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="">All Priorities</option>
                            <option value="Low" <?= isset($_GET['priority']) && $_GET['priority'] == 'Low' ? 'selected' : '' ?>>Low</option>
                            <option value="Medium" <?= isset($_GET['priority']) && $_GET['priority'] == 'Medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="High" <?= isset($_GET['priority']) && $_GET['priority'] == 'High' ? 'selected' : '' ?>>High</option>
                            <option value="Critical" <?= isset($_GET['priority']) && $_GET['priority'] == 'Critical' ? 'selected' : '' ?>>Critical</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                        <a href="/tasks" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tasks List -->
<div class="row">
    <div class="col-md-12">
        <?php if (empty($tasks)): ?>
            <div class="alert alert-info">
                <?php if (!empty($_GET['project_id']) || !empty($_GET['status']) || !empty($_GET['priority'])): ?>
                    No tasks match your filter criteria. <a href="/tasks">View all tasks</a>.
                <?php else: ?>
                    No tasks found. <a href="/tasks/create">Create a task</a> to get started.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Due Date</th>
                                <th>Assigned To</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td>
                                        <a href="/tasks/show/<?= $task->id ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($task->title) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if (isset($task->project_id) && isset($task->project_title)): ?>
                                            <a href="/projects/show/<?= $task->project_id ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($task->project_title) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No project</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = 'bg-secondary';
                                        if ($task->status === 'Pending') $statusClass = 'bg-secondary';
                                        if ($task->status === 'In Progress') $statusClass = 'bg-primary';
                                        if ($task->status === 'Completed') $statusClass = 'bg-success';
                                        if ($task->status === 'Testing') $statusClass = 'bg-info';
                                        if ($task->status === 'Blocked') $statusClass = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= $task->status ?></span>
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
                                    <td><?= !empty($task->due_date) ? date('M j, Y', strtotime($task->due_date)) : '—' ?></td>
                                    <td><?= !empty($task->assigned_to) ? htmlspecialchars($task->assigned_to) : '—' ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/tasks/show/<?= $task->id ?>" class="btn btn-outline-primary">View</a>
                                            <a href="/tasks/edit/<?= $task->id ?>" class="btn btn-outline-secondary">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div> 