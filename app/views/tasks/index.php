<!-- Modern Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-tasks me-3"></i>Tasks Management</h1>
        <p class="mb-0">Manage and track tasks across all projects</p>
    </div>
    <div>
        <a href="<?= URLROOT ?>/tasks/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>New Task
        </a>
    </div>
</div>

<!-- Task Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm h-100 bg-primary bg-opacity-10">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Total Tasks</h5>
                    <i class="fas fa-tasks fs-4 text-primary"></i>
                </div>
                <h2 class="mt-3 mb-2"><?= count($tasks) ?></h2>
                <p class="card-text text-muted mt-auto small">All tasks across projects</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm h-100 bg-success bg-opacity-10">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Completed</h5>
                    <i class="fas fa-check-circle fs-4 text-success"></i>
                </div>
                <h2 class="mt-3 mb-2"><?= count(array_filter($tasks, function($task) { return $task->status === 'Completed'; })) ?></h2>
                <p class="card-text text-muted mt-auto small">Tasks with completed status</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm h-100 bg-warning bg-opacity-10">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">In Progress</h5>
                    <i class="fas fa-clock fs-4 text-warning"></i>
                </div>
                <h2 class="mt-3 mb-2"><?= count(array_filter($tasks, function($task) { return $task->status === 'In Progress'; })) ?></h2>
                <p class="card-text text-muted mt-auto small">Tasks currently being worked on</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm h-100 bg-danger bg-opacity-10">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">High Priority</h5>
                    <i class="fas fa-exclamation-triangle fs-4 text-danger"></i>
                </div>
                <h2 class="mt-3 mb-2"><?= count(array_filter($tasks, function($task) { return $task->priority === 'High' || $task->priority === 'Critical'; })) ?></h2>
                <p class="card-text text-muted mt-auto small">Tasks needing immediate attention</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters with improved styling -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">
                    <i class="bi bi-funnel me-2"></i> Filter Tasks
                </h5>
                <button class="btn btn-sm btn-link p-0 text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>
            <div class="collapse show" id="filterCollapse">
            <div class="card-body">
                    <form action="<?= URLROOT ?>/tasks" method="get" class="row g-3" id="task-filter-form">
                    <div class="col-md-3">
                        <label for="project_id" class="form-label">Project</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-briefcase"></i></span>
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
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-check2-circle"></i></span>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="Pending" <?= isset($_GET['status']) && $_GET['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="In Progress" <?= isset($_GET['status']) && $_GET['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="Testing" <?= isset($_GET['status']) && $_GET['status'] == 'Testing' ? 'selected' : '' ?>>Testing</option>
                            <option value="Completed" <?= isset($_GET['status']) && $_GET['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="Blocked" <?= isset($_GET['status']) && $_GET['status'] == 'Blocked' ? 'selected' : '' ?>>Blocked</option>
                        </select>
                            </div>
                    </div>
                    <div class="col-md-3">
                        <label for="priority" class="form-label">Priority</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-flag"></i></span>
                        <select class="form-select" id="priority" name="priority">
                            <option value="">All Priorities</option>
                            <option value="Low" <?= isset($_GET['priority']) && $_GET['priority'] == 'Low' ? 'selected' : '' ?>>Low</option>
                            <option value="Medium" <?= isset($_GET['priority']) && $_GET['priority'] == 'Medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="High" <?= isset($_GET['priority']) && $_GET['priority'] == 'High' ? 'selected' : '' ?>>High</option>
                            <option value="Critical" <?= isset($_GET['priority']) && $_GET['priority'] == 'Critical' ? 'selected' : '' ?>>Critical</option>
                        </select>
                            </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search me-1"></i> Apply
                            </button>
                            <a href="<?= URLROOT ?>/tasks" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </a>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tasks List -->
<div class="row">
    <div class="col-md-12">
        <?php if (empty($tasks)): ?>
            <div class="alert alert-info d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                <div>
                <?php if (!empty($_GET['project_id']) || !empty($_GET['status']) || !empty($_GET['priority'])): ?>
                        No tasks match your filter criteria. <a href="<?= URLROOT ?>/tasks" class="alert-link">View all tasks</a>.
                <?php else: ?>
                        No tasks found. <a href="<?= URLROOT ?>/tasks/create" class="alert-link">Create a task</a> to get started.
                <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">Task List</h5>
                        </div>
                        <div class="col-auto">
                            <div class="input-group">
                                <input type="text" id="task-search" class="form-control form-control-sm" placeholder="Search tasks...">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tasks-table">
                        <thead class="table-light">
                            <tr>
                                <th class="sortable" data-sort="title">Title <i class="bi bi-arrow-down-up text-muted ms-1"></i></th>
                                <th class="sortable" data-sort="project">Project <i class="bi bi-arrow-down-up text-muted ms-1"></i></th>
                                <th class="sortable" data-sort="status">Status <i class="bi bi-arrow-down-up text-muted ms-1"></i></th>
                                <th class="sortable" data-sort="priority">Priority <i class="bi bi-arrow-down-up text-muted ms-1"></i></th>
                                <th class="sortable" data-sort="due-date">Due Date <i class="bi bi-arrow-down-up text-muted ms-1"></i></th>
                                <th>Assigned To</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td>
                                        <a href="<?= URLROOT ?>/tasks/show/<?= $task->id ?>" class="fw-medium text-decoration-none">
                                            <?= htmlspecialchars($task->title) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if (isset($task->project_id) && isset($task->project_title)): ?>
                                            <a href="<?= URLROOT ?>/projects/viewProject/<?= $task->project_id ?>" class="text-decoration-none">
                                                <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars($task->project_title) ?>
                                                </span>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = 'bg-secondary';
                                        $statusIcon = 'circle';
                                        
                                        if ($task->status === 'Pending') {
                                            $statusClass = 'bg-secondary';
                                            $statusIcon = 'hourglass-split';
                                        }
                                        if ($task->status === 'In Progress') {
                                            $statusClass = 'bg-primary';
                                            $statusIcon = 'arrow-repeat';
                                        }
                                        if ($task->status === 'Completed') {
                                            $statusClass = 'bg-success';
                                            $statusIcon = 'check-circle';
                                        }
                                        if ($task->status === 'Testing') {
                                            $statusClass = 'bg-info';
                                            $statusIcon = 'bug';
                                        }
                                        if ($task->status === 'Blocked') {
                                            $statusClass = 'bg-danger';
                                            $statusIcon = 'x-circle';
                                        }
                                        ?>
                                        <span class="badge <?= $statusClass ?>">
                                            <i class="bi bi-<?= $statusIcon ?> me-1"></i>
                                            <?= $task->status ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $priorityClass = 'bg-secondary';
                                        $priorityIcon = 'flag';
                                        
                                        if ($task->priority === 'Low') {
                                            $priorityClass = 'bg-success';
                                            $priorityIcon = 'arrow-down-circle';
                                        }
                                        if ($task->priority === 'Medium') {
                                            $priorityClass = 'bg-info';
                                            $priorityIcon = 'dash-circle';
                                        }
                                        if ($task->priority === 'High') {
                                            $priorityClass = 'bg-warning';
                                            $priorityIcon = 'arrow-up-circle';
                                        }
                                        if ($task->priority === 'Critical') {
                                            $priorityClass = 'bg-danger';
                                            $priorityIcon = 'exclamation-triangle';
                                        }
                                        ?>
                                        <span class="badge <?= $priorityClass ?>">
                                            <i class="bi bi-<?= $priorityIcon ?> me-1"></i>
                                            <?= $task->priority ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($task->due_date)): ?>
                                            <?php 
                                                $dueDate = strtotime($task->due_date);
                                                $today = strtotime('today');
                                                $tomorrow = strtotime('tomorrow');
                                                $yesterday = strtotime('yesterday');
                                                $dueDateClass = '';
                                                
                                                if ($dueDate < $today && $task->status !== 'Completed') {
                                                    $dueDateClass = 'text-danger fw-bold';
                                                } elseif ($dueDate == $today) {
                                                    $dueDateClass = 'text-warning fw-bold';
                                                }
                                            ?>
                                            <span class="<?= $dueDateClass ?>">
                                                <?php if ($dueDate == $today): ?>
                                                    Today
                                                <?php elseif ($dueDate == $tomorrow): ?>
                                                    Tomorrow
                                                <?php elseif ($dueDate == $yesterday): ?>
                                                    Yesterday
                                                <?php else: ?>
                                                    <?= date('M j, Y', $dueDate) ?>
                                                <?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($task->assigned_to)): ?>
                                            <div class="d-flex align-items-center">
                                                <?php
                                                // Get user data
                                                $userId = (int)($task->assigned_to ?? 0);
                                                $displayNameRaw = $task->assigned_to_name ?? '';
                                                $userName = htmlspecialchars($displayNameRaw !== '' ? $displayNameRaw : (string)$userId);
                                                
                                                // Extract initials properly
                                                if (strpos($userName, ' ') !== false) {
                                                    // If there's a space, get first letter of first and last name
                                                    $nameParts = explode(' ', $userName);
                                                    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
                                                } else {
                                                    // If no space, get first 1-2 letters of name
                                                    $initials = strtoupper(substr($userName, 0, min(2, strlen($userName))));
                                                }
                                                
                                                // Check for profile image - try multiple options
                                                $hasCustomAvatar = false;
                                                $avatarUrl = '';
                                                
                                                // Option 1: Check for user-specific profile picture
                                                $profilePic = '/uploads/profile_pictures/user_' . $userId . '_profile.jpg';
                                                if (file_exists('../public' . $profilePic)) {
                                                    $hasCustomAvatar = true;
                                                    $avatarUrl = URLROOT . $profilePic;
                                                }
                                                
                                                // Option 2: Use the getAvatarUrl helper if available
                                                if (!$hasCustomAvatar && function_exists('getAvatarUrl')) {
                                                    $avatarUrl = getAvatarUrl($userName);
                                                    $hasCustomAvatar = !strpos($avatarUrl, 'ui-avatars.com'); // Check if it's not the default UI avatars service
                                                }
                                                
                                                if ($hasCustomAvatar):
                                                ?>
                                                    <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars($userName) ?>" 
                                                         class="rounded-circle me-2" width="32" height="32">
                                                <?php else: ?>
                                                    <div class="avatar-circle me-2" style="width: 32px; height: 32px; background-color: <?= generateColorFromString($userName) ?>; display: flex; align-items: center; justify-content: center;">
                                                        <?= $initials ?>
                                                    </div>
                                                <?php endif; ?>
                                                <span class="text-truncate" style="max-width: 120px;"><?= $userName ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="<?= URLROOT ?>/tasks/show/<?= $task->id ?>">
                                                        <i class="bi bi-eye me-2"></i> View Details
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="<?= URLROOT ?>/tasks/edit/<?= $task->id ?>">
                                                        <i class="bi bi-pencil me-2"></i> Edit Task
                                                    </a>
                                                </li>
                                                <?php if ($task->status !== 'Completed'): ?>
                                                <li>
                                                    <a class="dropdown-item text-success" href="<?= URLROOT ?>/tasks/markComplete/<?= $task->id ?>">
                                                        <i class="bi bi-check-circle me-2"></i> Mark Complete
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" onclick="confirmDelete(<?= $task->id ?>)">
                                                        <i class="bi bi-trash me-2"></i> Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (count($tasks) > 10): ?>
                <div class="card-footer">
                    <nav>
                        <ul class="pagination pagination-sm justify-content-end mb-0">
                            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">Next</a></li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tasks page uses global styles from app.css -->

<!-- Enhanced JavaScript for form handling and additional features -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when selecting a filter option
    document.querySelectorAll('#task-filter-form select').forEach(function(select) {
        select.addEventListener('change', function() {
            document.getElementById('task-filter-form').submit();
        });
    });
    
    // Task search functionality
    const searchInput = document.getElementById('task-search');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const table = document.getElementById('tasks-table');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(function(row) {
                const title = row.querySelector('td:first-child').textContent.toLowerCase();
                const project = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                
                if (title.includes(searchText) || project.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Add sorting functionality
    document.querySelectorAll('.sortable').forEach(function(header) {
        header.addEventListener('click', function() {
            const sortKey = this.dataset.sort;
            const table = document.getElementById('tasks-table');
            const rows = Array.from(table.querySelectorAll('tbody tr'));
            const direction = this.classList.contains('sort-asc') ? 'desc' : 'asc';
            
            // Reset all headers
            document.querySelectorAll('.sortable').forEach(function(h) {
                h.classList.remove('sort-asc', 'sort-desc');
                h.querySelector('i').className = 'bi bi-arrow-down-up text-muted ms-1';
            });
            
            // Set this header's sort direction
            this.classList.add('sort-' + direction);
            this.querySelector('i').className = direction === 'asc' ? 
                'bi bi-arrow-up ms-1 text-primary' : 
                'bi bi-arrow-down ms-1 text-primary';
            
            // Sort the rows
            rows.sort(function(a, b) {
                let aValue, bValue;
                
                switch(sortKey) {
                    case 'title':
                        aValue = a.querySelector('td:nth-child(1)').textContent;
                        bValue = b.querySelector('td:nth-child(1)').textContent;
                        break;
                    case 'project':
                        aValue = a.querySelector('td:nth-child(2)').textContent;
                        bValue = b.querySelector('td:nth-child(2)').textContent;
                        break;
                    case 'status':
                        aValue = a.querySelector('td:nth-child(3)').textContent;
                        bValue = b.querySelector('td:nth-child(3)').textContent;
                        break;
                    case 'priority':
                        aValue = a.querySelector('td:nth-child(4)').textContent;
                        bValue = b.querySelector('td:nth-child(4)').textContent;
                        // Custom priority sort order
                        const priorityOrder = {'Critical': 4, 'High': 3, 'Medium': 2, 'Low': 1};
                        return direction === 'asc' ? 
                            priorityOrder[aValue.trim()] - priorityOrder[bValue.trim()] : 
                            priorityOrder[bValue.trim()] - priorityOrder[aValue.trim()];
                    case 'due-date':
                        const dateA = a.querySelector('td:nth-child(5)').textContent.trim();
                        const dateB = b.querySelector('td:nth-child(5)').textContent.trim();
                        
                        // Handle special date strings
                        if (dateA === 'Today') aValue = new Date();
                        else if (dateA === 'Tomorrow') {
                            const tomorrow = new Date();
                            tomorrow.setDate(tomorrow.getDate() + 1);
                            aValue = tomorrow;
                        }
                        else if (dateA === 'Yesterday') {
                            const yesterday = new Date();
                            yesterday.setDate(yesterday.getDate() - 1);
                            aValue = yesterday;
                        }
                        else if (dateA === '—') aValue = new Date('9999-12-31');
                        else aValue = new Date(dateA);
                        
                        if (dateB === 'Today') bValue = new Date();
                        else if (dateB === 'Tomorrow') {
                            const tomorrow = new Date();
                            tomorrow.setDate(tomorrow.getDate() + 1);
                            bValue = tomorrow;
                        }
                        else if (dateB === 'Yesterday') {
                            const yesterday = new Date();
                            yesterday.setDate(yesterday.getDate() - 1);
                            bValue = yesterday;
                        }
                        else if (dateB === '—') bValue = new Date('9999-12-31');
                        else bValue = new Date(dateB);
                        
                        return direction === 'asc' ? aValue - bValue : bValue - aValue;
                }
                
                // Default string comparison
                if (direction === 'asc') {
                    return aValue.localeCompare(bValue);
                } else {
                    return bValue.localeCompare(aValue);
                }
            });
            
            // Reorder the rows
            const tbody = table.querySelector('tbody');
            rows.forEach(function(row) {
                tbody.appendChild(row);
            });
        });
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Alt+N for new task
        if (e.altKey && e.key === 'n') {
            e.preventDefault();
            window.location.href = '<?= URLROOT ?>/tasks/create';
        }
        
        // Alt+F to focus search
        if (e.altKey && e.key === 'f') {
            e.preventDefault();
            document.getElementById('task-search')?.focus();
        }
        
        // / to focus search (when not in an input)
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault();
            document.getElementById('task-search')?.focus();
        }
    });
});

// Add a hidden form for task deletion
document.write(`
    <form id="deleteTaskForm" action="" method="POST" style="display: none;">
    </form>
`);

// Confirm delete function
function confirmDelete(taskId) {
    if (confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
        // Set the form action and submit it as POST
        const form = document.getElementById('deleteTaskForm');
        form.action = '<?= URLROOT ?>/tasks/delete/' + taskId;
        form.submit();
    }
}
</script> 