<?php
// Example: Enhanced Project View with Granular Permissions
// This demonstrates how the new permission system would be integrated

require_once '../app/helpers/enhanced_permissions_helper.php';

// Check if user can access this specific project
if (!canAccessProject($project->id, 'read')) {
    flash('access_denied', 'You do not have permission to view this project', 'alert alert-danger');
    redirect('projects');
}

// Get user's field permissions for this project
$fieldPermissions = getUserFieldPermissions('projects', $project->id);

// Check specific permissions for this project
$canViewBudget = canViewBudget($project->id);
$canEditBudget = canEditBudget($project->id);
$canManageTeam = canManageTeam($project->id);
$canExportData = hasEnhancedPermission('projects.export_data', [
    'resource_type' => 'project',
    'resource_id' => $project->id
]);

// Check if user is a project team member for contextual permissions
$isTeamMember = isProjectTeamMember($project->id);
?>

<div class="project-page">
    <div class="container-fluid">
        <!-- Project Header with Enhanced Permissions -->
        <div class="project-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/projects">Projects</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($project->title) ?></li>
                </ol>
            </nav>
            
            <div class="row mb-0">
                <div class="col-md-8">
                    <h1 class="project-title"><?= htmlspecialchars($project->title) ?></h1>
                    <div class="d-flex align-items-center gap-3 mt-3">
                        <span class="badge bg-success rounded-pill"><?= $project->status ?></span>
                        <span class="text-muted d-flex align-items-center">
                            <i class="bi bi-calendar3 me-2"></i> 
                            <?= date('M j, Y', strtotime($project->start_date)) ?> - <?= date('M j, Y', strtotime($project->end_date)) ?>
                        </span>
                        
                        <!-- Team Member Indicator -->
                        <?php if ($isTeamMember): ?>
                            <span class="badge bg-info rounded-pill">
                                <i class="bi bi-people me-1"></i> Team Member
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="btn-group">
                        <!-- Only show if user can create tasks for this project -->
                        <?php if (canAccessTask(null, 'create') && $isTeamMember): ?>
                            <a href="/tasks/create?project_id=<?= $project->id ?>" class="btn btn-success">
                                <i class="bi bi-plus-lg"></i> New Task
                            </a>
                        <?php endif; ?>
                        
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i> Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <!-- Only show if user can edit this specific project -->
                                <?php if (canAccessProject($project->id, 'update')): ?>
                                    <li>
                                        <a class="dropdown-item" href="/projects/edit/<?= $project->id ?>">
                                            <i class="bi bi-pencil me-2"></i> Edit Project
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <!-- Only show if user can manage team for this project -->
                                <?php if ($canManageTeam): ?>
                                    <li>
                                        <a class="dropdown-item" href="/projects/manageTeam/<?= $project->id ?>">
                                            <i class="bi bi-people me-2"></i> Manage Team
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <!-- Only show if user can export data -->
                                <?php if ($canExportData): ?>
                                    <li>
                                        <a class="dropdown-item" href="/projects/export/<?= $project->id ?>">
                                            <i class="bi bi-download me-2"></i> Export Data
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if (canAccessProject($project->id, 'update') || $canManageTeam || $canExportData): ?>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                
                                <!-- Only show if user can delete this specific project -->
                                <?php if (canAccessProject($project->id, 'delete')): ?>
                                    <li>
                                        <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteProjectModal">
                                            <i class="bi bi-trash me-2"></i> Delete Project
                                        </button>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Info Card with Field-Level Permissions -->
        <div class="row">
            <div class="col-lg-8">
                <!-- Description Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="bi bi-file-text me-2 text-primary"></i>Description</h5>
                    </div>
                    <div class="card-body">
                        <div class="description-content">
                            <?php if (!empty(trim($project->description))): ?>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($project->description)) ?></p>
                            <?php else: ?>
                                <p class="text-muted fst-italic mb-0">No description provided.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Budget Information - Only show if user has permission -->
                <?php if ($canViewBudget): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="bi bi-currency-dollar me-2 text-success"></i>Budget Information</h5>
                            <?php if ($canEditBudget): ?>
                                <a href="/projects/editBudget/<?= $project->id ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit Budget
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Total Budget</h6>
                                    <h4 class="text-success"><?= $currency['symbol'] ?><?= number_format($project->budget, 2) ?></h4>
                                </div>
                                <div class="col-md-6">
                                    <h6>Spent</h6>
                                    <h4 class="text-warning"><?= $currency['symbol'] ?><?= number_format($project->budget * 0.45, 2) ?></h4>
                                    <small class="text-muted">45% of budget used</small>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 8px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 45%"></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <!-- Project Info Card with Field-Level Permissions -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Project Info</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <!-- Always visible fields -->
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted d-block small">Status</span>
                                    <strong><?= htmlspecialchars($project->status) ?></strong>
                                </div>
                                <i class="bi bi-activity text-muted"></i>
                            </li>
                            
                            <!-- Department - check field permission -->
                            <?php if (getFieldAccessLevel('projects', 'department', $project->id) !== 'hidden'): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted d-block small">Department</span>
                                        <strong><?= isset($project->department_name) ? htmlspecialchars($project->department_name) : 'Not Assigned' ?></strong>
                                    </div>
                                    <i class="bi bi-building text-muted"></i>
                                </li>
                            <?php endif; ?>
                            
                            <!-- Created By -->
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted d-block small">Created By</span>
                                    <strong><?= htmlspecialchars($project->created_by) ?></strong>
                                </div>
                                <i class="bi bi-person text-muted"></i>
                            </li>
                            
                            <!-- Start Date - check field permission -->
                            <?php if (getFieldAccessLevel('projects', 'start_date', $project->id) !== 'hidden'): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted d-block small">Start Date</span>
                                        <strong><?= date('M j, Y', strtotime($project->start_date)) ?></strong>
                                    </div>
                                    <i class="bi bi-calendar-date text-muted"></i>
                                </li>
                            <?php endif; ?>
                            
                            <!-- End Date - check field permission -->
                            <?php if (getFieldAccessLevel('projects', 'end_date', $project->id) !== 'hidden'): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted d-block small">Due Date</span>
                                        <strong><?= date('M j, Y', strtotime($project->end_date)) ?></strong>
                                    </div>
                                    <i class="bi bi-calendar-check text-muted"></i>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Permission Summary for Debugging (only for admins) -->
                <?php if (hasPermission('admin.permissions')): ?>
                    <div class="card border-0 shadow-sm mb-4 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="card-title mb-0"><i class="bi bi-shield-check me-2"></i>Permission Debug</h6>
                        </div>
                        <div class="card-body">
                            <small>
                                <strong>Your permissions for this project:</strong><br>
                                • View Budget: <?= $canViewBudget ? '✅' : '❌' ?><br>
                                • Edit Budget: <?= $canEditBudget ? '✅' : '❌' ?><br>
                                • Manage Team: <?= $canManageTeam ? '✅' : '❌' ?><br>
                                • Export Data: <?= $canExportData ? '✅' : '❌' ?><br>
                                • Team Member: <?= $isTeamMember ? '✅' : '❌' ?><br>
                                
                                <hr class="my-2">
                                <strong>Field Access:</strong><br>
                                <?php
                                $fields = ['department', 'start_date', 'end_date', 'budget'];
                                foreach ($fields as $field) {
                                    $access = getFieldAccessLevel('projects', $field, $project->id);
                                    echo "• $field: $access<br>";
                                }
                                ?>
                            </small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Tasks Section with Enhanced Permissions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Tasks</h5>
                        <?php if (canAccessTask(null, 'create') && $isTeamMember): ?>
                            <a href="/tasks/create?project_id=<?= $project->id ?>" class="btn btn-sm btn-success">
                                <i class="bi bi-plus-lg"></i> New Task
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tasks)): ?>
                            <div class="text-center py-4">
                                <p class="text-muted mb-0">No tasks yet.</p>
                                <?php if (canAccessTask(null, 'create') && $isTeamMember): ?>
                                    <a href="/tasks/create?project_id=<?= $project->id ?>" class="btn btn-primary mt-2">
                                        Create First Task
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Status</th>
                                            <th>Priority</th>
                                            <th>Assigned To</th>
                                            <th>Due Date</th>
                                            <?php if (hasEnhancedPermission('tasks.view_time_tracking')): ?>
                                                <th>Time Spent</th>
                                            <?php endif; ?>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tasks as $task): ?>
                                            <tr>
                                                <td>
                                                    <?php if (canAccessTask($task->id, 'read')): ?>
                                                        <a href="/tasks/show/<?= $task->id ?>" class="text-decoration-none">
                                                            <?= htmlspecialchars($task->title) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted"><?= htmlspecialchars($task->title) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?= $task->status ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning"><?= $task->priority ?></span>
                                                </td>
                                                <td><?= htmlspecialchars($task->assigned_to ?? 'Unassigned') ?></td>
                                                <td><?= !empty($task->due_date) ? date('M j, Y', strtotime($task->due_date)) : '—' ?></td>
                                                
                                                <!-- Time tracking column - only if user has permission -->
                                                <?php if (hasEnhancedPermission('tasks.view_time_tracking')): ?>
                                                    <td>
                                                        <span class="text-muted">
                                                            <?= $task->actual_hours ?? '0' ?>h / <?= $task->estimated_hours ?? '0' ?>h
                                                        </span>
                                                    </td>
                                                <?php endif; ?>
                                                
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if (canAccessTask($task->id, 'read')): ?>
                                                            <a href="/tasks/show/<?= $task->id ?>" class="btn btn-outline-primary">View</a>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (canAccessTask($task->id, 'update')): ?>
                                                            <a href="/tasks/edit/<?= $task->id ?>" class="btn btn-outline-secondary">Edit</a>
                                                        <?php endif; ?>
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
    </div>
</div>

<style>
.permission-info {
    font-size: 0.75rem;
    color: #6c757d;
    border-left: 3px solid #007bff;
    padding-left: 8px;
    margin-top: 8px;
}

.permission-denied {
    background-color: #f8f9fa;
    border: 1px dashed #dee2e6;
    padding: 1rem;
    text-align: center;
    color: #6c757d;
    border-radius: 8px;
}

.field-hidden {
    display: none;
}

.field-readonly {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    color: #6c757d;
}
</style>

<?php
/*
This example demonstrates several key features of the enhanced permission system:

1. **Resource-Level Permissions**: Checking permissions specific to this project
   - canAccessProject($project->id, 'read/update/delete')
   - canViewBudget($project->id) 
   - canEditBudget($project->id)

2. **Contextual Permissions**: Automatic permissions based on relationships
   - isProjectTeamMember($project->id) - automatically grants certain permissions
   - Task creation only available to team members

3. **Field-Level Permissions**: Controlling access to specific fields
   - getFieldAccessLevel('projects', 'budget', $project->id)
   - Hiding/showing fields based on permission level

4. **Enhanced Permission Checking**: Using context for more intelligent decisions
   - hasEnhancedPermission() with context arrays
   - Different permissions for different actions

5. **Permission Debugging**: For administrators to see effective permissions

This approach provides:
- Fine-grained control over what users can see and do
- Automatic permission inheritance based on relationships
- Easy debugging and administration
- Backward compatibility with existing permission system
*/
?> 