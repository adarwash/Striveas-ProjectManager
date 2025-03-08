<?php
// Set title for the page
$title = 'Edit Task - ProjectTracker';
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Task</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/tasks">Tasks</a></li>
                <li class="breadcrumb-item"><a href="/projects/viewProject/<?= $task->project_id ?>">Project</a></li>
                <li class="breadcrumb-item"><a href="/tasks/show/<?= $task->id ?>"><?= htmlspecialchars($task->title) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 col-md-12 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Task Details</h5>
            </div>
            <div class="card-body">
                <form action="/tasks/update/<?= $task->id ?>" method="post">
                    <div class="mb-3">
                        <label for="project_id" class="form-label">Project</label>
                        <select class="form-select <?= isset($task->project_id_err) ? 'is-invalid' : '' ?>" 
                                id="project_id" name="project_id" required>
                            <option value="">Select Project</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project->id ?>" <?= ($task->project_id == $project->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($project->title) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            <?= $task->project_id_err ?? '' ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Task Title</label>
                        <input type="text" class="form-control <?= isset($task->title_err) ? 'is-invalid' : '' ?>" 
                               id="title" name="title" value="<?= htmlspecialchars($task->title) ?>" required>
                        <div class="invalid-feedback">
                            <?= $task->title_err ?? '' ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control <?= isset($task->description_err) ? 'is-invalid' : '' ?>" 
                                  id="description" name="description" rows="4"><?= htmlspecialchars($task->description) ?></textarea>
                        <div class="invalid-feedback">
                            <?= $task->description_err ?? '' ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control <?= isset($task->start_date_err) ? 'is-invalid' : '' ?>" 
                                   id="start_date" name="start_date" value="<?= htmlspecialchars($task->start_date) ?>" required>
                            <div class="invalid-feedback">
                                <?= $task->start_date_err ?? '' ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" class="form-control <?= isset($task->due_date_err) ? 'is-invalid' : '' ?>" 
                                   id="due_date" name="due_date" value="<?= htmlspecialchars($task->due_date) ?>">
                            <div class="invalid-feedback">
                                <?= $task->due_date_err ?? '' ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select <?= isset($task->status_err) ? 'is-invalid' : '' ?>" 
                                    id="status" name="status" required>
                                <option value="Pending" <?= ($task->status == 'Pending') ? 'selected' : '' ?>>Pending</option>
                                <option value="In Progress" <?= ($task->status == 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                                <option value="Completed" <?= ($task->status == 'Completed') ? 'selected' : '' ?>>Completed</option>
                                <option value="Testing" <?= ($task->status == 'Testing') ? 'selected' : '' ?>>Testing</option>
                                <option value="Blocked" <?= ($task->status == 'Blocked') ? 'selected' : '' ?>>Blocked</option>
                            </select>
                            <div class="invalid-feedback">
                                <?= $task->status_err ?? '' ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select <?= isset($task->priority_err) ? 'is-invalid' : '' ?>" 
                                    id="priority" name="priority" required>
                                <option value="Low" <?= ($task->priority == 'Low') ? 'selected' : '' ?>>Low</option>
                                <option value="Medium" <?= ($task->priority == 'Medium') ? 'selected' : '' ?>>Medium</option>
                                <option value="High" <?= ($task->priority == 'High') ? 'selected' : '' ?>>High</option>
                                <option value="Critical" <?= ($task->priority == 'Critical') ? 'selected' : '' ?>>Critical</option>
                            </select>
                            <div class="invalid-feedback">
                                <?= $task->priority_err ?? '' ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="estimated_hours" class="form-label">Estimated Hours</label>
                        <input type="number" class="form-control <?= isset($task->estimated_hours_err) ? 'is-invalid' : '' ?>" 
                               id="estimated_hours" name="estimated_hours" value="<?= htmlspecialchars($task->estimated_hours) ?>" 
                               step="0.5" min="0">
                        <div class="invalid-feedback">
                            <?= $task->estimated_hours_err ?? '' ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags (comma separated)</label>
                        <input type="text" class="form-control <?= isset($task->tags_err) ? 'is-invalid' : '' ?>" 
                               id="tags" name="tags" value="<?= htmlspecialchars($task->tags ?? '') ?>">
                        <div class="invalid-feedback">
                            <?= $task->tags_err ?? '' ?>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/tasks/show/<?= $task->id ?>" class="btn btn-outline-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(event) {
        let valid = true;
        
        // Validate title
        const title = document.getElementById('title');
        if (!title.value.trim()) {
            title.classList.add('is-invalid');
            title.nextElementSibling.textContent = 'Title is required';
            valid = false;
        } else {
            title.classList.remove('is-invalid');
        }
        
        // Validate dates
        const startDate = document.getElementById('start_date');
        const dueDate = document.getElementById('due_date');
        
        if (startDate.value && dueDate.value) {
            if (new Date(startDate.value) > new Date(dueDate.value)) {
                dueDate.classList.add('is-invalid');
                dueDate.nextElementSibling.textContent = 'Due date must be after start date';
                valid = false;
            } else {
                dueDate.classList.remove('is-invalid');
            }
        }
        
        if (!valid) {
            event.preventDefault();
        }
    });
});
</script> 