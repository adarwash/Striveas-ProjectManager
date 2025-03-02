<div class="row mb-4">
    <div class="col-md-8">
        <h1><?= htmlspecialchars($department->name) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/departments">Departments</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($department->name) ?></li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 text-md-end d-flex justify-content-md-end align-items-center">
        <a href="/departments/edit/<?= $department->id ?>" class="btn btn-outline-secondary me-2">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteDepartmentModal">
            <i class="bi bi-trash"></i> Delete
        </button>
    </div>
</div>

<!-- Department Details -->
<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Description</h5>
                <p class="card-text"><?= nl2br(htmlspecialchars($department->description)) ?></p>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5>Budget Information</h5>
                        
                        <?php 
                        // Calculate budget percentage used
                        $budgetPercentage = 0;
                        // Ensure used_budget is not null
                        $department->used_budget = $department->used_budget ?? 0;
                        
                        if ($department->budget > 0) {
                            $budgetPercentage = ($department->used_budget / $department->budget) * 100;
                        }
                        
                        // Calculate remaining budget
                        $remainingBudget = $department->budget - $department->used_budget;
                        
                        // Determine appropriate color based on percentage
                        $progressClass = 'bg-success';
                        if ($budgetPercentage > 70) {
                            $progressClass = 'bg-warning';
                        }
                        if ($budgetPercentage > 90) {
                            $progressClass = 'bg-danger';
                        }
                        ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <p class="mb-1 text-muted">Total Budget</p>
                                <h4 class="text-primary">$<?= number_format($department->budget, 2) ?></h4>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 text-muted">Allocated to Projects</p>
                                <h4 class="<?= $budgetPercentage > 100 ? 'text-danger' : 'text-success' ?>">
                                    $<?= number_format($department->used_budget, 2) ?>
                                </h4>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 text-muted">Remaining</p>
                                <h4 class="<?= $remainingBudget < 0 ? 'text-danger' : 'text-info' ?>">
                                    $<?= number_format($remainingBudget, 2) ?>
                                </h4>
                            </div>
                        </div>
                        
                        <p class="mb-1">Budget Utilization</p>
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar <?= $progressClass ?>" role="progressbar" 
                                 style="width: <?= min($budgetPercentage, 100) ?>%;" 
                                 aria-valuenow="<?= $budgetPercentage ?>" aria-valuemin="0" aria-valuemax="100">
                                <?= number_format($budgetPercentage, 1) ?>%
                            </div>
                        </div>
                        
                        <?php if ($budgetPercentage > 90): ?>
                            <div class="alert alert-warning mt-3 small">
                                <i class="bi bi-exclamation-triangle-fill"></i> 
                                Budget is nearly exhausted! Consider allocating additional funds.
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($budgetPercentage > 100): ?>
                            <div class="alert alert-danger mt-3 small">
                                <i class="bi bi-exclamation-circle-fill"></i> 
                                Budget exceeded! Projects are allocated $<?= number_format($department->used_budget - $department->budget, 2) ?> more than the available budget.
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
                <h5 class="card-title mb-0">Department Info</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <p class="mb-1 text-muted">Created</p>
                    <p><?= date('M j, Y', strtotime($department->created_at)) ?></p>
                </div>
                
                <?php if (!empty($department->updated_at)): ?>
                <div class="mb-3">
                    <p class="mb-1 text-muted">Last Updated</p>
                    <p><?= date('M j, Y', strtotime($department->updated_at)) ?></p>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <p class="mb-1 text-muted">Number of Projects</p>
                    <p><?= count($projects) ?></p>
                </div>
                
                <div class="d-grid gap-2">
                    <a href="/projects/create" class="btn btn-outline-primary">
                        <i class="bi bi-plus-lg"></i> Add New Project
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Projects List -->
<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Projects in this Department</h5>
                <a href="/projects/create" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg"></i> New Project
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($projects)): ?>
                    <div class="p-4 text-center">
                        <p class="text-muted mb-0">No projects yet. Create a project to get started.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Budget</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $project): ?>
                                    <tr>
                                        <td>
                                            <a href="/projects/show/<?= $project->id ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($project->title) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = 'bg-secondary';
                                            if ($project->status === 'Active') $statusClass = 'bg-success';
                                            if ($project->status === 'On Hold') $statusClass = 'bg-warning';
                                            if ($project->status === 'Completed') $statusClass = 'bg-info';
                                            if ($project->status === 'Cancelled') $statusClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $statusClass ?>"><?= $project->status ?></span>
                                        </td>
                                        <td>$<?= number_format($project->budget, 2) ?></td>
                                        <td><?= date('M j, Y', strtotime($project->start_date)) ?></td>
                                        <td><?= date('M j, Y', strtotime($project->end_date)) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/projects/show/<?= $project->id ?>" class="btn btn-outline-primary">View</a>
                                                <a href="/projects/edit/<?= $project->id ?>" class="btn btn-outline-secondary">Edit</a>
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
</div>

<!-- Delete Department Modal -->
<div class="modal fade" id="deleteDepartmentModal" tabindex="-1" aria-labelledby="deleteDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteDepartmentModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the department <strong><?= htmlspecialchars($department->name) ?></strong>?
                <p class="text-danger mt-2">
                    <i class="bi bi-exclamation-triangle"></i> 
                    This action cannot be undone.
                </p>
                
                <?php if (count($projects) > 0): ?>
                <div class="alert alert-warning mt-3">
                    <strong>Warning:</strong> This department has <?= count($projects) ?> associated project(s). 
                    You cannot delete a department with associated projects.
                </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="/departments/delete/<?= $department->id ?>" method="post">
                    <button type="submit" class="btn btn-danger" <?= count($projects) > 0 ? 'disabled' : '' ?>>Delete</button>
                </form>
            </div>
        </div>
    </div>
</div> 