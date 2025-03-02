<?php
// Set title for the page
$title = 'Dashboard - ProjectTracker';
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Project Progress Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <div class="avatar-group me-3">
            <?php for($i = 1; $i <= 4; $i++): ?>
                <div class="avatar">
                    <div><?= chr(64 + $i) ?></div>
                </div>
            <?php endfor; ?>
            <div class="avatar avatar-count">
                <div>+5</div>
            </div>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
            <i class="bi bi-plus-lg me-1"></i> Add Member
        </button>
    </div>
</div>

<!-- Progress Overview -->
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>Total Progress</div>
            <div class="text-muted">50% Completed</div>
        </div>
    </div>
    <div class="card-body">
        <div class="progress mb-3">
            <div class="progress-bar" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="p-3 bg-primary-subtle rounded text-center">
                    <h4><?= isset($stats['total_projects']) ? $stats['total_projects'] : 12 ?></h4>
                    <div class="text-muted">Total Projects</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="p-3 bg-success-subtle rounded text-center">
                    <h4><?= isset($stats['completed_tasks']) ? $stats['completed_tasks'] : 48 ?></h4>
                    <div class="text-muted">Completed Tasks</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                <div class="p-3 bg-warning-subtle rounded text-center">
                    <h4><?= isset($stats['in_progress']) ? $stats['in_progress'] : 32 ?></h4>
                    <div class="text-muted">In Progress</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="p-3 bg-danger-subtle rounded text-center">
                    <h4><?= isset($stats['overdue']) ? $stats['overdue'] : 5 ?></h4>
                    <div class="text-muted">Overdue</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Tabs -->
<div class="card mb-4">
    <div class="card-header p-2">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link active" href="#"><i class="bi bi-list me-1"></i> Table</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="bi bi-kanban me-1"></i> Board</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="bi bi-calendar2-week me-1"></i> Timeline</a>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <!-- Task Sections -->
        <div class="task-sections">
            <!-- To-do Section -->
            <div class="task-section p-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="bi bi-circle text-primary me-2"></i> 
                        To-do 
                        <span class="badge bg-light text-dark ms-2">3</span>
                    </h5>
                    <button class="btn btn-sm btn-light">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" class="form-check-input"></th>
                                <th>Task Name</th>
                                <th>Description</th>
                                <th>Deadline</th>
                                <th>People</th>
                                <th>Progress</th>
                                <th>Priority</th>
                                <th style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td>Employee Details</td>
                                <td>Create a page where there is information about employees</td>
                                <td>29 Nov, 2024</td>
                                <td>
                                    <div class="avatar-group">
                                        <div class="avatar"><div>J</div></div>
                                        <div class="avatar"><div>S</div></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 50%"></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="priority-indicator priority-medium">
                                        <i class="bi bi-flag"></i> Medium
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td>Darkmode Version</td>
                                <td>Darkmode version for all screens</td>
                                <td>29 Nov, 2024</td>
                                <td>
                                    <div class="avatar-group">
                                        <div class="avatar"><div>M</div></div>
                                        <div class="avatar"><div>K</div></div>
                                        <div class="avatar"><div>R</div></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 20%"></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="priority-indicator priority-low">
                                        <i class="bi bi-flag"></i> Low
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td>Super Admin Role</td>
                                <td>-</td>
                                <td>29 Nov, 2024</td>
                                <td>
                                    <div class="avatar-group">
                                        <div class="avatar"><div>A</div></div>
                                        <div class="avatar"><div>B</div></div>
                                        <div class="avatar"><div>C</div></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 50%"></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="priority-indicator priority-medium">
                                        <i class="bi bi-flag"></i> Medium
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- In Progress Section -->
            <div class="task-section p-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="bi bi-clock-history text-warning me-2"></i> 
                        In Progress 
                        <span class="badge bg-light text-dark ms-2">3</span>
                    </h5>
                    <button class="btn btn-sm btn-light">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" class="form-check-input"></th>
                                <th>Task Name</th>
                                <th>Description</th>
                                <th>Deadline</th>
                                <th>People</th>
                                <th>Progress</th>
                                <th>Priority</th>
                                <th style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td>Super Admin Role</td>
                                <td>-</td>
                                <td>29 Nov, 2024</td>
                                <td>
                                    <div class="avatar-group">
                                        <div class="avatar"><div>C</div></div>
                                        <div class="avatar"><div>W</div></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 95%"></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="priority-indicator priority-high">
                                        <i class="bi bi-flag"></i> High
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td>Settings Page</td>
                                <td>-</td>
                                <td>29 Nov, 2024</td>
                                <td>
                                    <div class="avatar-group">
                                        <div class="avatar"><div>J</div></div>
                                        <div class="avatar"><div>B</div></div>
                                        <div class="avatar"><div>S</div></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 50%"></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="priority-indicator priority-medium">
                                        <i class="bi bi-flag"></i> Medium
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td>Super Admin Role</td>
                                <td>Create a design that displays KPIs and employee statistics</td>
                                <td>29 Nov, 2024</td>
                                <td>
                                    <div class="avatar-group">
                                        <div class="avatar"><div>D</div></div>
                                        <div class="avatar"><div>T</div></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 20%"></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="priority-indicator priority-low">
                                        <i class="bi bi-flag"></i> Low
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Completed Section -->
            <div class="task-section p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="bi bi-check-circle text-success me-2"></i> 
                        Completed 
                        <span class="badge bg-light text-dark ms-2">1</span>
                    </h5>
                    <button class="btn btn-sm btn-light">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" class="form-check-input"></th>
                                <th>Task Name</th>
                                <th>Description</th>
                                <th>Deadline</th>
                                <th>People</th>
                                <th>Progress</th>
                                <th>Priority</th>
                                <th style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="checkbox" class="form-check-input"></td>
                                <td>Super Admin Role</td>
                                <td>-</td>
                                <td>29 Nov, 2024</td>
                                <td>
                                    <div class="avatar-group">
                                        <div class="avatar"><div>A</div></div>
                                        <div class="avatar"><div>B</div></div>
                                        <div class="avatar"><div>C</div></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="priority-indicator priority-medium">
                                        <i class="bi bi-flag"></i> Medium
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>Recent Activity</div>
        <a href="#" class="btn btn-sm btn-light">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php 
            // Sample activity data - would normally come from the database
            $activities = [
                [
                    'user' => 'John Doe',
                    'action' => 'completed task',
                    'target' => 'User Authentication',
                    'time' => '2 hours ago',
                    'icon' => 'bi-check-circle-fill',
                    'color' => 'text-success'
                ],
                [
                    'user' => 'Jane Smith',
                    'action' => 'added new task',
                    'target' => 'Payment Integration',
                    'time' => '4 hours ago',
                    'icon' => 'bi-plus-circle-fill',
                    'color' => 'text-primary'
                ],
                [
                    'user' => 'Mike Johnson',
                    'action' => 'commented on',
                    'target' => 'Dashboard Redesign',
                    'time' => '1 day ago',
                    'icon' => 'bi-chat-fill',
                    'color' => 'text-info'
                ],
                [
                    'user' => 'Sarah Wilson',
                    'action' => 'updated',
                    'target' => 'Project Timeline',
                    'time' => '2 days ago',
                    'icon' => 'bi-arrow-clockwise',
                    'color' => 'text-warning'
                ]
            ];
            
            foreach ($activities as $activity): 
            ?>
            <div class="list-group-item py-3">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi <?= $activity['icon'] ?> fs-4 <?= $activity['color'] ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div><strong><?= $activity['user'] ?></strong> <?= $activity['action'] ?> <a href="#"><?= $activity['target'] ?></a></div>
                        <div class="text-muted small"><?= $activity['time'] ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Team Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="memberName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="memberName">
                    </div>
                    <div class="mb-3">
                        <label for="memberEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="memberEmail">
                    </div>
                    <div class="mb-3">
                        <label for="memberRole" class="form-label">Role</label>
                        <select class="form-select" id="memberRole">
                            <option>Developer</option>
                            <option>Designer</option>
                            <option>Product Manager</option>
                            <option>QA Tester</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Add Member</button>
            </div>
        </div>
    </div>
</div> 