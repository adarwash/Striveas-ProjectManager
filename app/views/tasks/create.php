<?php
// Set title for the page
$title = 'Create Task - ProjectTracker';
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Create New Task</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= URLROOT ?>/projects">Projects</a></li>
                <?php if (isset($project_id)): ?>
                <li class="breadcrumb-item"><a href="<?= URLROOT ?>/projects/viewProject/<?= $project_id ?>">Project Details</a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page">Create Task</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 col-md-12 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Task Details</h5>
            </div>
            <div class="card-body">
                <form action="<?= URLROOT ?>/tasks/store" method="post">
                    <div class="mb-3">
                        <label for="project_id" class="form-label">Project</label>
                        <select class="form-select <?= isset($data['project_id_err']) ? 'is-invalid' : '' ?>" 
                                id="project_id" name="project_id" required>
                            <option value="">Select Project</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project->id ?>" <?= (
                                    (isset($data['project_id']) && $data['project_id'] == $project->id) || 
                                    (isset($project_id) && $project_id == $project->id)
                                ) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($project->title) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            <?= $data['project_id_err'] ?? '' ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Task Title</label>
                        <input type="text" class="form-control <?= isset($data['title_err']) ? 'is-invalid' : '' ?>" 
                               id="title" name="title" value="<?= $data['title'] ?? '' ?>" required>
                        <div class="invalid-feedback">
                            <?= $data['title_err'] ?? '' ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= $data['description'] ?? '' ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select <?= isset($data['status_err']) ? 'is-invalid' : '' ?>" 
                                    id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="Pending" <?= (isset($data['status']) && $data['status'] === 'Pending') ? 'selected' : '' ?>>Pending</option>
                                <option value="In Progress" <?= (isset($data['status']) && $data['status'] === 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                                <option value="Completed" <?= (isset($data['status']) && $data['status'] === 'Completed') ? 'selected' : '' ?>>Completed</option>
                                <option value="Testing" <?= (isset($data['status']) && $data['status'] === 'Testing') ? 'selected' : '' ?>>Testing</option>
                                <option value="Blocked" <?= (isset($data['status']) && $data['status'] === 'Blocked') ? 'selected' : '' ?>>Blocked</option>
                            </select>
                            <div class="invalid-feedback">
                                <?= $data['status_err'] ?? '' ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select <?= isset($data['priority_err']) ? 'is-invalid' : '' ?>" 
                                    id="priority" name="priority" required>
                                <option value="">Select Priority</option>
                                <option value="Low" <?= (isset($data['priority']) && $data['priority'] === 'Low') ? 'selected' : '' ?>>Low</option>
                                <option value="Medium" <?= (isset($data['priority']) && $data['priority'] === 'Medium') ? 'selected' : '' ?>>Medium</option>
                                <option value="High" <?= (isset($data['priority']) && $data['priority'] === 'High') ? 'selected' : '' ?>>High</option>
                                <option value="Critical" <?= (isset($data['priority']) && $data['priority'] === 'Critical') ? 'selected' : '' ?>>Critical</option>
                            </select>
                            <div class="invalid-feedback">
                                <?= $data['priority_err'] ?? '' ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="due_date" 
                                   name="due_date" value="<?= $data['due_date'] ?? date('Y-m-d', strtotime('+7 days')) ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="assigned_to" class="form-label">Assign To</label>
                            <select class="form-select" id="assigned_to" name="assigned_to">
                                <option value="">Unassigned</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= (isset($data['assigned_to']) && $data['assigned_to'] == $user['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-buttons mt-4">
                                <button type="submit" class="btn btn-primary">Create Task</button>
                                <?php if (isset($project_id)): ?>
                                    <a href="<?= URLROOT ?>/projects/viewProject/<?= $project_id ?>" class="btn btn-light">Cancel</a>
                                <?php else: ?>
                                    <a href="<?= URLROOT ?>/tasks" class="btn btn-light">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Additional client-side validation can be added here
    document.addEventListener('DOMContentLoaded', function() {
        // Get form elements
        const form = document.querySelector('form');
        const statusSelect = document.getElementById('status');
        const prioritySelect = document.getElementById('priority');
        
        // Set default values if not already set
        if (!statusSelect.value) {
            statusSelect.value = 'Pending';
        }
        
        if (!prioritySelect.value) {
            prioritySelect.value = 'Medium';
        }
    });
</script> 