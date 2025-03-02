<div class="row">
    <div class="col-md-12 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Departments</h1>
            <a href="/departments/create" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> New Department
            </a>
        </div>
    </div>
</div>

<!-- Budget Summary -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-6 mb-3">
                        <p class="text-muted mb-1">Total Budget</p>
                        <div class="display-5 fw-bold text-primary">$<?= number_format($budget_stats->total_budget ?? 0, 2) ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="text-muted mb-1">Allocated Budget</p>
                        <div class="display-5 fw-bold text-success">$<?= number_format($budget_stats->total_used_budget ?? 0, 2) ?></div>
                        <?php 
                        // Calculate percentage of total budget used
                        $percentUsed = 0;
                        if (($budget_stats->total_budget ?? 0) > 0) {
                            $percentUsed = (($budget_stats->total_used_budget ?? 0) / ($budget_stats->total_budget ?? 0)) * 100;
                        }
                        ?>
                        <div class="text-muted"><?= number_format($percentUsed, 1) ?>% of total budget</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Departments List -->
<div class="row">
    <div class="col-md-12">
        <?php if (empty($departments)): ?>
            <div class="alert alert-info">
                No departments found. <a href="/departments/create">Create a department</a> to get started.
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Budget</th>
                                <th>Used</th>
                                <th>Remaining</th>
                                <th>Progress</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departments as $department): ?>
                                <?php 
                                // Calculate remaining budget and percentage
                                $usedBudget = $department->used_budget ?? 0;
                                $remainingBudget = ($department->budget ?? 0) - $usedBudget;
                                $percentUsed = 0;
                                if (($department->budget ?? 0) > 0) {
                                    $percentUsed = ($usedBudget / ($department->budget ?? 0)) * 100;
                                }
                                
                                // Determine the color based on percentage
                                $progressClass = 'bg-success';
                                if ($percentUsed > 70) {
                                    $progressClass = 'bg-warning';
                                }
                                if ($percentUsed > 90) {
                                    $progressClass = 'bg-danger';
                                }
                                ?>
                                <tr>
                                    <td>
                                        <a href="/departments/show/<?= $department->id ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($department->name) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars(substr($department->description, 0, 50)) . (strlen($department->description) > 50 ? '...' : '') ?></td>
                                    <td>$<?= number_format($department->budget, 2) ?></td>
                                    <td>$<?= number_format($usedBudget, 2) ?></td>
                                    <td>$<?= number_format($remainingBudget, 2) ?></td>
                                    <td style="width: 150px;">
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar <?= $progressClass ?>" role="progressbar" 
                                                 style="width: <?= min($percentUsed, 100) ?>%" 
                                                 aria-valuenow="<?= $percentUsed ?>" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="text-muted"><?= number_format($percentUsed, 1) ?>%</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/departments/show/<?= $department->id ?>" class="btn btn-outline-primary">View</a>
                                            <a href="/departments/edit/<?= $department->id ?>" class="btn btn-outline-secondary">Edit</a>
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