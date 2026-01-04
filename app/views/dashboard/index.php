<?php
// Set title for the page
$title = 'Dashboard - ' . DEFAULT_TITLE;
?>

<?php require VIEWSPATH . '/partials/header.php'; ?>

<!-- Modern Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-tachometer-alt me-3"></i>Dashboard</h1>
        <?php 
            $hour = date('G');
            $greeting = 'Good evening';
            if ($hour < 12) {
                $greeting = 'Good morning';
            } elseif ($hour < 18) {
                $greeting = 'Good afternoon';
            }
            $firstName = !empty($data['user']['first_name']) ? $data['user']['first_name'] : ($_SESSION['user_name'] ?? 'User');
        ?>
        <p class="text-muted mb-0"><?= $greeting ?>, <?= htmlspecialchars($firstName) ?>! Here's your plan for today.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-light text-dark"><?= $_SESSION['role'] ?? 'User' ?></span>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#dashboardWidgetsModal" id="dashboardWidgetsManage">
            <i class="bi bi-layout-text-window-reverse me-1"></i>Widgets
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="dashboardCustomizeToggle">
            <i class="bi bi-arrows-move me-1"></i>Customize
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary d-none" id="dashboardAddDivider">
            <i class="bi bi-hr me-1"></i>Add Divider
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary d-none" id="dashboardAddTitle">
            <i class="bi bi-type-h1 me-1"></i>Add Title
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger d-none" id="dashboardResetLayout">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
        </button>
        <span class="small text-muted d-none" id="dashboardLayoutStatus"></span>
    </div>
</div>

<!-- Dashboard Widgets (user-customizable layout) -->
<div id="dashboardWidgets" class="row g-4 dashboard-widgets">
    <!-- Stats -->
    <div class="col-12 dashboard-widget" data-widget-id="stats">
        <div class="dashboard-widget-controls">
            <button type="button" class="dashboard-drag-handle" aria-label="Drag to move" title="Drag to move">
                <i class="bi bi-grip-vertical"></i>
            </button>
            <button type="button" class="dashboard-hide-btn" aria-label="Hide widget" title="Hide widget">
                <i class="bi bi-eye-slash"></i>
            </button>
            <button type="button" class="dashboard-resize-btn" data-resize="smaller" aria-label="Make smaller" title="Make smaller">
                <i class="bi bi-arrow-left-short"></i>
            </button>
            <button type="button" class="dashboard-resize-btn" data-resize="bigger" aria-label="Make bigger" title="Make bigger">
                <i class="bi bi-arrow-right-short"></i>
            </button>
            <button type="button" class="dashboard-height-btn" data-height="shorter" aria-label="Make shorter" title="Make shorter">
                <i class="bi bi-arrows-collapse"></i>
            </button>
            <button type="button" class="dashboard-height-btn" data-height="taller" aria-label="Make taller" title="Make taller">
                <i class="bi bi-arrows-expand"></i>
            </button>
        </div>
        <!-- Compact Stats Overview -->
        <div class="row row-cols-2 row-cols-md-4 g-3">
            <div class="col">
                <div class="compact-stat-card purple clickable-card" data-href="/clients">
                    <div class="compact-stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="compact-stat-content">
                        <div class="compact-stat-value"><?= $stats['active_clients'] ?? 0 ?></div>
                        <div class="compact-stat-label">Clients</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="compact-stat-card green clickable-card" data-href="/users">
                    <div class="compact-stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="compact-stat-content">
                        <div class="compact-stat-value"><?= $stats['total_users'] ?? 0 ?></div>
                        <div class="compact-stat-label">Users</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="compact-stat-card orange clickable-card" data-href="/users?role=technician">
                    <div class="compact-stat-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="compact-stat-content">
                        <div class="compact-stat-value"><?= $stats['technicians'] ?? 0 ?></div>
                        <div class="compact-stat-label">Techs</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="compact-stat-card blue clickable-card" data-href="/sites">
                    <div class="compact-stat-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="compact-stat-content">
                        <div class="compact-stat-value"><?= $stats['active_sites'] ?? 0 ?></div>
                        <div class="compact-stat-label">Sites</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="compact-stat-card purple clickable-card" data-href="/clients?status=Prospect">
                    <div class="compact-stat-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="compact-stat-content">
                        <div class="compact-stat-value"><?= $stats['prospect_clients'] ?? 0 ?></div>
                        <div class="compact-stat-label">Prospects</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="compact-stat-card red clickable-card" data-href="/tickets?status=open">
                    <div class="compact-stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="compact-stat-content">
                        <div class="compact-stat-value"><?= $stats['open_tickets'] ?? 0 ?></div>
                        <div class="compact-stat-label">Tickets</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="compact-stat-card orange clickable-card" data-href="/tasks?status=open">
                    <div class="compact-stat-icon">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div class="compact-stat-content">
                        <div class="compact-stat-value"><?= $stats['open_tasks'] ?? 0 ?></div>
                        <div class="compact-stat-label">Tasks</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="compact-stat-card green clickable-card" data-href="/time/dashboard">
                    <div class="compact-stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="compact-stat-content">
                        <div class="compact-stat-value"><?= $stats['currently_working'] ?? 0 ?></div>
                        <div class="compact-stat-label">Working</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Plan for Today -->
    <div class="col-12 col-lg-8 dashboard-widget" data-widget-id="plan_today">
        <div class="dashboard-widget-controls">
            <button type="button" class="dashboard-drag-handle" aria-label="Drag to move" title="Drag to move">
                <i class="bi bi-grip-vertical"></i>
            </button>
            <button type="button" class="dashboard-hide-btn" aria-label="Hide widget" title="Hide widget">
                <i class="bi bi-eye-slash"></i>
            </button>
            <button type="button" class="dashboard-resize-btn" data-resize="smaller" aria-label="Make smaller" title="Make smaller">
                <i class="bi bi-arrow-left-short"></i>
            </button>
            <button type="button" class="dashboard-resize-btn" data-resize="bigger" aria-label="Make bigger" title="Make bigger">
                <i class="bi bi-arrow-right-short"></i>
            </button>
            <button type="button" class="dashboard-height-btn" data-height="shorter" aria-label="Make shorter" title="Make shorter">
                <i class="bi bi-arrows-collapse"></i>
            </button>
            <button type="button" class="dashboard-height-btn" data-height="taller" aria-label="Make taller" title="Make taller">
                <i class="bi bi-arrows-expand"></i>
            </button>
        </div>
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-primary"><i class="bi bi-calendar-day me-2"></i>My Plan for Today</h5>
            </div>
            <div class="card-body">
                <?php
                    // Collect items for the plan
                    $planItems = [];
                    
                    // 1. Overdue/Due Today Tasks
                    if (!empty($assigned_tasks)) {
                        foreach ($assigned_tasks as $task) {
                            if (!empty($task->due_date)) {
                                $dueTs = strtotime($task->due_date);
                                $isToday = date('Y-m-d', $dueTs) === date('Y-m-d');
                                $isOverdue = $dueTs < strtotime('today');
                                
                                if ($isToday || $isOverdue) {
                                    $planItems[] = [
                                        'type' => 'task',
                                        'priority' => $isOverdue ? 1 : 2, // Overdue is highest priority
                                        'time' => $dueTs,
                                        'title' => $task->title,
                                        'status' => $task->status,
                                        'link' => '/tasks/show/' . $task->id,
                                        'meta' => $isOverdue ? 'Overdue' : 'Due Today'
                                    ];
                                }
                            }
                        }
                    }
                    
                    // 2. Open Tickets (High Priority or Assigned)
                    if (!empty($data['assigned_tickets'])) {
                        foreach ($data['assigned_tickets'] as $ticket) {
                            $isHighPri = ($ticket['priority_level'] ?? 0) >= 4; // High/Critical
                            $planItems[] = [
                                'type' => 'ticket',
                                'priority' => $isHighPri ? 1 : 3,
                                'time' => strtotime($ticket['created_at']), // Sort by creation for now
                                'title' => '#' . $ticket['ticket_number'] . ': ' . $ticket['subject'],
                                'status' => $ticket['status_name'],
                                'link' => '/tickets/show/' . $ticket['id'],
                                'meta' => $ticket['priority_display']
                            ];
                        }
                    }
                    
                    // Sort items by priority then time
                    usort($planItems, function($a, $b) {
                        if ($a['priority'] !== $b['priority']) {
                            return $a['priority'] - $b['priority'];
                        }
                        // For same priority, maybe sort tickets by oldest first (FIFO) and tasks by due date
                        return $a['time'] - $b['time'];
                    });
                ?>
                
                <?php if (empty($planItems)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle fs-1 text-success mb-2"></i>
                        <p>You have no urgent items for today. Great job!</p>
                        <a href="/tasks" class="btn btn-sm btn-outline-primary mt-2">View All Tasks</a>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($planItems as $item): ?>
                            <a href="<?= $item['link'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <?php if ($item['type'] === 'task'): ?>
                                        <span class="badge bg-primary me-2"><i class="bi bi-check2-square"></i> Task</span>
                                    <?php elseif ($item['type'] === 'ticket'): ?>
                                        <span class="badge bg-danger me-2"><i class="bi bi-ticket-perforated"></i> Ticket</span>
                                    <?php endif; ?>
                                    <span class="fw-medium"><?= htmlspecialchars($item['title']) ?></span>
                                </div>
                                <div class="text-end">
                                    <span class="badge <?= $item['meta'] === 'Overdue' || $item['meta'] === 'Critical' ? 'bg-danger' : 'bg-warning text-dark' ?>">
                                        <?= $item['meta'] ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-12 col-lg-4 dashboard-widget" data-widget-id="quick_actions">
        <div class="dashboard-widget-controls">
            <button type="button" class="dashboard-drag-handle" aria-label="Drag to move" title="Drag to move">
                <i class="bi bi-grip-vertical"></i>
            </button>
            <button type="button" class="dashboard-hide-btn" aria-label="Hide widget" title="Hide widget">
                <i class="bi bi-eye-slash"></i>
            </button>
            <button type="button" class="dashboard-resize-btn" data-resize="smaller" aria-label="Make smaller" title="Make smaller">
                <i class="bi bi-arrow-left-short"></i>
            </button>
            <button type="button" class="dashboard-resize-btn" data-resize="bigger" aria-label="Make bigger" title="Make bigger">
                <i class="bi bi-arrow-right-short"></i>
            </button>
            <button type="button" class="dashboard-height-btn" data-height="shorter" aria-label="Make shorter" title="Make shorter">
                <i class="bi bi-arrows-collapse"></i>
            </button>
            <button type="button" class="dashboard-height-btn" data-height="taller" aria-label="Make taller" title="Make taller">
                <i class="bi bi-arrows-expand"></i>
            </button>
        </div>
        <div class="card h-100 shadow-sm quick-actions-card">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="/tickets/create" class="btn btn-light text-primary fw-bold text-start">
                        <i class="bi bi-plus-circle me-2"></i> Create Ticket
                    </a>
                    <a href="/tasks/create" class="btn btn-light text-primary fw-bold text-start">
                        <i class="bi bi-list-check me-2"></i> Add Task
                    </a>
                    <a href="/time/dashboard" class="btn btn-light text-primary fw-bold text-start">
                        <i class="bi bi-stopwatch me-2"></i> Time Clock
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- My Tasks -->
    <div class="col-12 dashboard-widget" data-widget-id="my_tasks">
        <div class="dashboard-widget-controls">
            <button type="button" class="dashboard-drag-handle" aria-label="Drag to move" title="Drag to move">
                <i class="bi bi-grip-vertical"></i>
            </button>
            <button type="button" class="dashboard-hide-btn" aria-label="Hide widget" title="Hide widget">
                <i class="bi bi-eye-slash"></i>
            </button>
            <button type="button" class="dashboard-resize-btn" data-resize="smaller" aria-label="Make smaller" title="Make smaller">
                <i class="bi bi-arrow-left-short"></i>
            </button>
            <button type="button" class="dashboard-resize-btn" data-resize="bigger" aria-label="Make bigger" title="Make bigger">
                <i class="bi bi-arrow-right-short"></i>
            </button>
            <button type="button" class="dashboard-height-btn" data-height="shorter" aria-label="Make shorter" title="Make shorter">
                <i class="bi bi-arrows-collapse"></i>
            </button>
            <button type="button" class="dashboard-height-btn" data-height="taller" aria-label="Make taller" title="Make taller">
                <i class="bi bi-arrows-expand"></i>
            </button>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>My Tasks</h5>
                <a href="/tasks" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($assigned_tasks)) : ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">You have no tasks assigned.</p>
                    </div>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th class="d-none d-md-table-cell">Project</th>
                                    <th>Status</th>
                                    <th>Due</th>
                                    <th class="d-none d-md-table-cell">Priority</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assigned_tasks as $task) : ?>
                                    <?php
                                        $statusClass = 'badge bg-secondary';
                                        if ($task->status === 'In Progress') $statusClass = 'badge bg-info';
                                        if ($task->status === 'Completed') $statusClass = 'badge bg-success';
                                        if ($task->status === 'Blocked') $statusClass = 'badge bg-danger';
                                        if ($task->status === 'Testing') $statusClass = 'badge bg-primary';
                                        
                                        $priorityClass = 'badge bg-light text-dark';
                                        if ($task->priority === 'High' || $task->priority === 'Critical') $priorityClass = 'badge bg-danger';
                                        if ($task->priority === 'Medium') $priorityClass = 'badge bg-warning text-dark';
                                        if ($task->priority === 'Low') $priorityClass = 'badge bg-success';
                                        
                                        $dueText = 'No due date';
                                        $dueClass = 'text-muted';
                                        if (!empty($task->due_date)) {
                                            $dueTs = strtotime($task->due_date);
                                            if ($dueTs) {
                                                $now = time();
                                                $daysDiff = floor(($dueTs - $now) / 86400);
                                                $dueText = date('M j', $dueTs);
                                                if ($daysDiff < 0) {
                                                    $dueText = 'Overdue (' . date('M j', $dueTs) . ')';
                                                    $dueClass = 'text-danger fw-semibold';
                                                } elseif ($daysDiff <= 2) {
                                                    $dueClass = 'text-warning fw-semibold';
                                                } else {
                                                    $dueClass = 'text-muted';
                                                }
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold mb-1"><?= htmlspecialchars($task->title) ?></div>
                                            <div class="small text-muted d-md-none">
                                                <?= !empty($task->project_title) ? htmlspecialchars($task->project_title) : 'No project' ?>
                                            </div>
                                        </td>
                                        <td class="d-none d-md-table-cell text-muted">
                                            <?= !empty($task->project_title) ? htmlspecialchars($task->project_title) : 'No project' ?>
                                        </td>
                                        <td><span class="<?= $statusClass ?>"><?= htmlspecialchars($task->status ?? 'Pending') ?></span></td>
                                        <td class="<?= $dueClass ?>"><?= $dueText ?></td>
                                        <td class="d-none d-md-table-cell"><span class="<?= $priorityClass ?>"><?= htmlspecialchars($task->priority ?? 'Normal') ?></span></td>
                                        <td class="text-end">
                                            <a href="/tasks/show/<?= (int)$task->id ?>" class="btn btn-sm btn-outline-primary">Open</a>
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

    <!-- Recent Activity -->
    <div class="col-12 col-lg-8 dashboard-widget" data-widget-id="recent_activity">
        <div class="dashboard-widget-controls">
            <button type="button" class="dashboard-drag-handle" aria-label="Drag to move" title="Drag to move">
                <i class="bi bi-grip-vertical"></i>
            </button>
            <button type="button" class="dashboard-hide-btn" aria-label="Hide widget" title="Hide widget">
                <i class="bi bi-eye-slash"></i>
            </button>
            <button type="button" class="dashboard-resize-btn" data-resize="smaller" aria-label="Make smaller" title="Make smaller">
                <i class="bi bi-arrow-left-short"></i>
            </button>
            <button type="button" class="dashboard-resize-btn" data-resize="bigger" aria-label="Make bigger" title="Make bigger">
                <i class="bi bi-arrow-right-short"></i>
            </button>
            <button type="button" class="dashboard-height-btn" data-height="shorter" aria-label="Make shorter" title="Make shorter">
                <i class="bi bi-arrows-collapse"></i>
            </button>
            <button type="button" class="dashboard-height-btn" data-height="taller" aria-label="Make taller" title="Make taller">
                <i class="bi bi-arrows-expand"></i>
            </button>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                <a href="/tasks" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($assigned_tasks)) : ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No recent activity to display.</p>
                    </div>
                <?php else : ?>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon bg-success">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">login</div>
                                <div class="activity-description">User logged in by <?= $_SESSION['user_name'] ?></div>
                                <div class="activity-time"><?= date('M j, Y g:i A') ?></div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon bg-info">
                                <i class="fas fa-sign-out-alt"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">logout</div>
                                <div class="activity-description">User logged out by <?= $_SESSION['user_name'] ?></div>
                                <div class="activity-time"><?= date('M j, Y g:i A', strtotime('-1 hour')) ?></div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon bg-primary">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">login</div>
                                <div class="activity-description">User logged in by <?= $_SESSION['user_name'] ?></div>
                                <div class="activity-time"><?= date('M j, Y g:i A', strtotime('-2 hours')) ?></div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon bg-warning">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">login</div>
                                <div class="activity-description">User logged in by <?= $_SESSION['user_name'] ?></div>
                                <div class="activity-time"><?= date('M j, Y g:i A', strtotime('-3 hours')) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top Clients -->
    <div class="col-12 col-lg-4 dashboard-widget" data-widget-id="top_clients">
        <div class="dashboard-widget-controls">
            <button type="button" class="dashboard-drag-handle" aria-label="Drag to move" title="Drag to move">
                <i class="bi bi-grip-vertical"></i>
            </button>
            <button type="button" class="dashboard-hide-btn" aria-label="Hide widget" title="Hide widget">
                <i class="bi bi-eye-slash"></i>
            </button>
            <button type="button" class="dashboard-resize-btn" data-resize="smaller" aria-label="Make smaller" title="Make smaller">
                <i class="bi bi-arrow-left-short"></i>
            </button>
            <button type="button" class="dashboard-resize-btn" data-resize="bigger" aria-label="Make bigger" title="Make bigger">
                <i class="bi bi-arrow-right-short"></i>
            </button>
            <button type="button" class="dashboard-height-btn" data-height="shorter" aria-label="Make shorter" title="Make shorter">
                <i class="bi bi-arrows-collapse"></i>
            </button>
            <button type="button" class="dashboard-height-btn" data-height="taller" aria-label="Make taller" title="Make taller">
                <i class="bi bi-arrows-expand"></i>
            </button>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-star me-2"></i>Top Clients</h5>
                <form method="get" action="/dashboard" class="d-flex align-items-center" style="gap: 8px;">
                    <label for="top_client_range" class="form-label mb-0 small text-muted">Range</label>
                    <select id="top_client_range" name="top_client_range" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="today" <?= (isset($top_client_range) && $top_client_range === 'today') ? 'selected' : '' ?>>Today</option>
                        <option value="week" <?= (!isset($top_client_range) || $top_client_range === 'week') ? 'selected' : '' ?>>Week</option>
                        <option value="month" <?= (isset($top_client_range) && $top_client_range === 'month') ? 'selected' : '' ?>>Month</option>
                    </select>
                </form>
            </div>
            <div class="card-body">
                <?php if (!empty($top_clients)) : ?>
                    <div class="client-list">
                        <?php foreach ($top_clients as $client) : ?>
                            <div class="client-item">
                                <div class="client-info">
                                    <div class="client-name"><?= htmlspecialchars($client['name']) ?></div>
                                    <div class="client-projects">Tickets: <?= (int)($client['ticket_count'] ?? 0) ?></div>
                                </div>
                                <div class="client-badge">
                                    <span class="badge bg-primary"><?= (int)($client['ticket_count'] ?? 0) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="text-center py-4">
                        <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No client data yet.</p>
                    </div>
                <?php endif; ?>
                <div class="text-center mt-3">
                    <a href="/clients" class="btn btn-sm btn-outline-primary">View All Clients</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Pinned Cards (from other modules) -->
    <?php if (!empty($data['pinned_cards'])): ?>
        <?php foreach ($data['pinned_cards'] as $pc): ?>
            <?php
                $pcWidgetId = (string)($pc['widget_id'] ?? '');
                $pcCardId = (string)($pc['card_id'] ?? '');
                $pcView = (string)($pc['view'] ?? '');
                $pcTitle = (string)($pc['title'] ?? $pcCardId);
                $pcData = is_array($pc['data'] ?? null) ? $pc['data'] : [];
                $pcDefaultSpan = (int)($pc['default_span_lg'] ?? 6);
                if ($pcDefaultSpan <= 0) { $pcDefaultSpan = 6; }
            ?>
            <div class="col-12 col-lg-<?= (int)$pcDefaultSpan ?> dashboard-widget" data-widget-id="<?= htmlspecialchars($pcWidgetId) ?>">
                <div class="dashboard-widget-controls">
                    <button type="button" class="dashboard-drag-handle" aria-label="Drag to move" title="Drag to move">
                        <i class="bi bi-grip-vertical"></i>
                    </button>
                    <button type="button" class="dashboard-hide-btn" aria-label="Hide widget" title="Hide widget">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                    <button type="button" class="dashboard-resize-btn" data-resize="smaller" aria-label="Make smaller" title="Make smaller">
                        <i class="bi bi-arrow-left-short"></i>
                    </button>
                    <button type="button" class="dashboard-resize-btn" data-resize="bigger" aria-label="Make bigger" title="Make bigger">
                        <i class="bi bi-arrow-right-short"></i>
                    </button>
                    <button type="button" class="dashboard-height-btn" data-height="shorter" aria-label="Make shorter" title="Make shorter">
                        <i class="bi bi-arrows-collapse"></i>
                    </button>
                    <button type="button" class="dashboard-height-btn" data-height="taller" aria-label="Make taller" title="Make taller">
                        <i class="bi bi-arrows-expand"></i>
                    </button>
                </div>
                <?php
                    // Render using the existing module card partial
                    $cardId = $pcCardId;
                    $widgetId = $pcWidgetId;
                    $isPinned = true;
                    $context = 'dashboard';
                    $cardTitle = $pcTitle;
                    // Provide variables from card data (e.g. $tickets)
                    if (is_array($pcData)) {
                        // IMPORTANT: pinned card partials rely on their data variables (e.g. $client).
                        // Using EXTR_SKIP can leave stale variables from other dashboard sections (e.g. Top Clients).
                        extract($pcData, EXTR_OVERWRITE);
                    }
                    if (!empty($pcView) && file_exists(VIEWSPATH . '/' . $pcView . '.php')) {
                        require VIEWSPATH . '/' . $pcView . '.php';
                    } else {
                        echo '<div class="card border-0 shadow-sm"><div class="card-body text-muted">Pinned card unavailable.</div></div>';
                    }
                ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Manage Widgets Modal (show/hide) -->
<div class="modal fade" id="dashboardWidgetsModal" tabindex="-1" aria-labelledby="dashboardWidgetsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dashboardWidgetsModalLabel">
                    <i class="bi bi-layout-text-window-reverse me-2"></i>Dashboard Widgets
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Choose which widgets appear on your dashboard. You can always turn them back on later.</p>
                <div class="list-group">
                    <label class="list-group-item d-flex align-items-center gap-2">
                        <input class="form-check-input m-0 dashboard-widget-toggle" type="checkbox" data-widget-id="stats">
                        <span class="fw-semibold">Stats</span>
                        <span class="text-muted small ms-auto">Top overview cards</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center gap-2">
                        <input class="form-check-input m-0 dashboard-widget-toggle" type="checkbox" data-widget-id="plan_today">
                        <span class="fw-semibold">My Plan for Today</span>
                        <span class="text-muted small ms-auto">Urgent tasks + tickets</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center gap-2">
                        <input class="form-check-input m-0 dashboard-widget-toggle" type="checkbox" data-widget-id="quick_actions">
                        <span class="fw-semibold">Quick Actions</span>
                        <span class="text-muted small ms-auto">Create ticket/task, time clock</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center gap-2">
                        <input class="form-check-input m-0 dashboard-widget-toggle" type="checkbox" data-widget-id="my_tasks">
                        <span class="fw-semibold">My Tasks</span>
                        <span class="text-muted small ms-auto">Your assigned tasks table</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center gap-2">
                        <input class="form-check-input m-0 dashboard-widget-toggle" type="checkbox" data-widget-id="recent_activity">
                        <span class="fw-semibold">Recent Activity</span>
                        <span class="text-muted small ms-auto">Activity feed</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center gap-2">
                        <input class="form-check-input m-0 dashboard-widget-toggle" type="checkbox" data-widget-id="top_clients">
                        <span class="fw-semibold">Top Clients</span>
                        <span class="text-muted small ms-auto">Most tickets by client</span>
                    </label>

                    <?php if (!empty($data['pinned_cards'])): ?>
                        <div class="list-group-item bg-light small text-muted fw-semibold text-uppercase">
                            Pinned Cards
                        </div>
                        <?php foreach ($data['pinned_cards'] as $pc): ?>
                            <?php
                                $pcWidgetId = (string)($pc['widget_id'] ?? '');
                                $pcTitle = (string)($pc['title'] ?? ($pc['card_id'] ?? 'Pinned card'));
                                $pcDesc = (string)($pc['description'] ?? '');
                            ?>
                            <label class="list-group-item d-flex align-items-center gap-2">
                                <input class="form-check-input m-0 dashboard-widget-toggle" type="checkbox" data-widget-id="<?= htmlspecialchars($pcWidgetId) ?>">
                                <span class="fw-semibold"><?= htmlspecialchars($pcTitle) ?></span>
                                <span class="text-muted small ms-auto"><?= htmlspecialchars($pcDesc) ?></span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Title Editor Modal -->
<div class="modal fade" id="dashboardTitleModal" tabindex="-1" aria-labelledby="dashboardTitleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dashboardTitleModalLabel">Title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="dashboardTitleModalError"></div>
                <div id="dashboardTitleEditor" style="height: 160px;"></div>
                <div class="small text-muted mt-2">Formatting like bold/italic/underline and links is supported.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="dashboardTitleModalSave">Save Title</button>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard uses global styles from app.css -->

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
(function(){
    const savedLayoutRaw = <?= json_encode($data['dashboard_layout'] ?? '') ?>;
    const csrfToken = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>;
    const saveUrl = '/dashboard/saveLayout';
    let allowedWidgetIds = ['stats','plan_today','quick_actions','my_tasks','recent_activity','top_clients'];
    const allowedSpans = [3, 4, 6, 8, 12]; // Bootstrap col-lg-*
    const allowedHeights = { min: 160, max: 2000, step: 120 };
    // Grouping/stacking (no masonry resizing/positioning)

    // Helper functions for divider/title widgets (must be in outer scope for saveLayout)
    function isDividerWidget(id) {
        return typeof id === 'string' && id.startsWith('divider-');
    }

    function isTitleWidget(id) {
        return typeof id === 'string' && id.startsWith('title-');
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function parseSavedLayout(raw) {
        const out = { order: null, sizes: {}, hidden: [], heights: {}, titles: {}, divider_thickness: {}, title_size: {} };
        if (!raw) return out;

        let parsed = raw;
        if (typeof raw === 'string') {
            try {
                parsed = JSON.parse(raw);
            } catch (e) {
                parsed = null;
            }
        }

        if (Array.isArray(parsed)) {
            out.order = parsed;
            return out;
        }

        if (parsed && typeof parsed === 'object') {
            if (Array.isArray(parsed.order)) out.order = parsed.order;
            if (parsed.sizes && typeof parsed.sizes === 'object') out.sizes = parsed.sizes;
            if (Array.isArray(parsed.hidden)) out.hidden = parsed.hidden;
            if (parsed.heights && typeof parsed.heights === 'object') out.heights = parsed.heights;
            if (parsed.titles && typeof parsed.titles === 'object') out.titles = parsed.titles;
            if (parsed.divider_thickness && typeof parsed.divider_thickness === 'object') out.divider_thickness = parsed.divider_thickness;
            if (parsed.title_size && typeof parsed.title_size === 'object') out.title_size = parsed.title_size;
        }

        return out;
    }

    function setSpanVar(el, span) {
        const n = normalizeSpan(span);
        el.style.setProperty('--dashboard-span', String(n));
    }

    function applySpanVars(container) {
        container.querySelectorAll('.dashboard-widget').forEach(el => {
            setSpanVar(el, getLgSpan(el));
        });
    }

    function getWidgetHeightPx(widget) {
        const raw = widget.getAttribute('data-height-px') || '';
        const n = parseInt(raw, 10);
        return Number.isFinite(n) ? n : 0;
    }

    function setWidgetHeightPx(widget, heightPx) {
        const n = parseInt(String(heightPx), 10);
        if (!Number.isFinite(n) || n <= 0) {
            widget.removeAttribute('data-height-px');
            widget.classList.remove('dashboard-widget-fixed-height');
            widget.style.height = '';
            return;
        }
        const bounded = Math.max(allowedHeights.min, Math.min(allowedHeights.max, n));
        widget.setAttribute('data-height-px', String(bounded));
        widget.classList.add('dashboard-widget-fixed-height');
        widget.style.height = bounded + 'px';
    }


    function getCurrentHeights(container) {
        const heights = {};
        container.querySelectorAll('.dashboard-widget').forEach(el => {
            const id = el.getAttribute('data-widget-id');
            if (!id) return;
            const h = getWidgetHeightPx(el);
            if (h > 0) heights[id] = h;
        });
        return heights;
    }

    function applyHeights(container, heights) {
        if (!heights || typeof heights !== 'object') return;
        container.querySelectorAll('.dashboard-widget').forEach(el => {
            const id = el.getAttribute('data-widget-id');
            if (!id) return;
            if (!Object.prototype.hasOwnProperty.call(heights, id)) return;
            const h = parseInt(String(heights[id]), 10);
            if (!Number.isFinite(h) || h <= 0) {
                setWidgetHeightPx(el, 0);
                return;
            }
            setWidgetHeightPx(el, h);
        });
    }

    // Masonry positioning not used.

    function getCurrentOrder(container) {
        return Array.from(container.querySelectorAll('.dashboard-widget'))
            .map(el => el.getAttribute('data-widget-id'))
            .filter(Boolean);
    }

    function getCurrentTitles(container) {
        const titles = {};
        container.querySelectorAll('.dashboard-title-widget').forEach(w => {
            const id = w.getAttribute('data-widget-id');
            const textEl = w.querySelector('.dashboard-title-text');
            if (id && textEl) {
                titles[id] = (textEl.innerHTML || '').trim() || 'Section Title';
            }
        });
        return titles;
    }

    function getCurrentDividerThickness(container) {
        const out = {};
        container.querySelectorAll('.dashboard-divider-widget').forEach(w => {
            const id = w.getAttribute('data-widget-id');
            if (!id) return;
            let t = parseInt(w.getAttribute('data-divider-thickness') || '1', 10);
            if (![1, 2, 3].includes(t)) t = 1;
            out[id] = t;
        });
        return out;
    }

    function getCurrentTitleSizes(container) {
        const out = {};
        container.querySelectorAll('.dashboard-title-widget').forEach(w => {
            const id = w.getAttribute('data-widget-id');
            if (!id) return;
            let s = parseInt(w.getAttribute('data-title-size') || '2', 10);
            if (![1, 2, 3].includes(s)) s = 2;
            out[id] = s;
        });
        return out;
    }

    function getLgSpan(el) {
        const cls = Array.from(el.classList).find(c => /^col-lg-\d+$/.test(c));
        if (!cls) return 12;
        const n = parseInt(cls.replace('col-lg-', ''), 10);
        return Number.isFinite(n) ? n : 12;
    }

    function normalizeSpan(span) {
        const n = parseInt(String(span), 10);
        if (!Number.isFinite(n)) return 12;
        if (allowedSpans.includes(n)) return n;
        // Snap to nearest allowed span
        let best = allowedSpans[allowedSpans.length - 1];
        let bestDist = Infinity;
        allowedSpans.forEach(s => {
            const dist = Math.abs(s - n);
            if (dist < bestDist) {
                best = s;
                bestDist = dist;
            }
        });
        return best;
    }

    function setLgSpan(el, span) {
        const normalized = normalizeSpan(span);
        // remove any existing col-lg-* classes
        Array.from(el.classList).forEach(c => {
            if (/^col-lg-\d+$/.test(c)) el.classList.remove(c);
        });
        el.classList.add('col-lg-' + normalized);
        el.setAttribute('data-lg-span', String(normalized));
        setSpanVar(el, normalized);
    }

    function getCurrentSizes(container) {
        const sizes = {};
        container.querySelectorAll('.dashboard-widget').forEach(el => {
            const id = el.getAttribute('data-widget-id');
            if (!id) return;
            sizes[id] = normalizeSpan(getLgSpan(el));
        });
        return sizes;
    }

    function getCurrentHidden(container) {
        return Array.from(container.querySelectorAll('.dashboard-widget.dashboard-widget-hidden'))
            .map(el => el.getAttribute('data-widget-id'))
            .filter(Boolean);
    }

    function getWidgetById(container, id) {
        if (!id || !allowedWidgetIds.includes(id)) return null;
        return container.querySelector('.dashboard-widget[data-widget-id="' + id + '"]');
    }

    function setWidgetHiddenById(container, id, hide) {
        const el = getWidgetById(container, id);
        if (!el) return;
        if (hide) {
            el.classList.add('dashboard-widget-hidden');
            el.setAttribute('aria-hidden', 'true');
            el.style.display = 'none';
        } else {
            el.classList.remove('dashboard-widget-hidden');
            el.removeAttribute('aria-hidden');
            el.style.display = '';
        }
        updateResizeButtonsState(el);
    }

    function applyOrder(container, order) {
        if (!Array.isArray(order) || order.length === 0) return;
        const byId = {};
        container.querySelectorAll(':scope > .dashboard-widget').forEach(el => {
            const id = el.getAttribute('data-widget-id');
            if (id) byId[id] = el;
        });

        order.forEach(id => {
            if (byId[id]) {
                container.appendChild(byId[id]);
                delete byId[id];
            }
        });

        // Append any new widgets not in saved order
        Object.keys(byId).forEach(id => container.appendChild(byId[id]));
    }

    function applySizes(container, sizes) {
        if (!sizes || typeof sizes !== 'object') return;
        container.querySelectorAll('.dashboard-widget').forEach(el => {
            const id = el.getAttribute('data-widget-id');
            if (!id) return;
            if (!Object.prototype.hasOwnProperty.call(sizes, id)) return;
            setLgSpan(el, sizes[id]);
        });
    }

    function applyHidden(container, hiddenList) {
        const hiddenSet = new Set(
            Array.isArray(hiddenList)
                ? hiddenList.filter(id => typeof id === 'string' && allowedWidgetIds.includes(id))
                : []
        );
        container.querySelectorAll('.dashboard-widget').forEach(el => {
            const id = el.getAttribute('data-widget-id');
            if (!id) return;
            setWidgetHiddenById(container, id, hiddenSet.has(id));
        });
    }

    function syncWidgetToggles(container) {
        const hidden = new Set(getCurrentHidden(container));
        document.querySelectorAll('.dashboard-widget-toggle').forEach(cb => {
            const id = cb.getAttribute('data-widget-id');
            if (!id) return;
            if (!allowedWidgetIds.includes(id)) return;
            cb.checked = !hidden.has(id);
        });
    }

    function updateResizeButtonsState(widget) {
        const span = normalizeSpan(getLgSpan(widget));
        const idx = allowedSpans.indexOf(span);
        const smallerBtn = widget.querySelector('.dashboard-resize-btn[data-resize="smaller"]');
        const biggerBtn = widget.querySelector('.dashboard-resize-btn[data-resize="bigger"]');
        const shorterBtn = widget.querySelector('.dashboard-height-btn[data-height="shorter"]');
        const tallerBtn = widget.querySelector('.dashboard-height-btn[data-height="taller"]');
        const heightPx = getWidgetHeightPx(widget); // 0 = auto
        if (smallerBtn) {
            smallerBtn.disabled = idx <= 0;
            smallerBtn.title = 'Make smaller (currently ' + span + '/12)';
        }
        if (biggerBtn) {
            biggerBtn.disabled = idx < 0 || idx >= allowedSpans.length - 1;
            biggerBtn.title = 'Make bigger (currently ' + span + '/12)';
        }
        if (shorterBtn) {
            shorterBtn.disabled = heightPx > 0 && heightPx <= allowedHeights.min;
            shorterBtn.title = 'Make shorter (height ' + (heightPx > 0 ? (heightPx + 'px') : 'auto') + ')';
        }
        if (tallerBtn) {
            tallerBtn.disabled = heightPx > 0 && heightPx >= allowedHeights.max;
            tallerBtn.title = 'Make taller (height ' + (heightPx > 0 ? (heightPx + 'px') : 'auto') + ')';
        }
        const dragBtn = widget.querySelector('.dashboard-drag-handle');
        if (dragBtn) {
            dragBtn.title = 'Drag to move (width ' + span + '/12' + (heightPx > 0 ? (', height ' + heightPx + 'px') : '') + ')';
        }
    }

    function updateAllResizeButtonsState(container) {
        container.querySelectorAll('.dashboard-widget').forEach(el => updateResizeButtonsState(el));
    }

    function setCustomizeMode(enabled, sortable) {
        window.__dashboardCustomizeMode = !!enabled;
        document.body.classList.toggle('dashboard-customize-mode', !!enabled);
        const resetBtn = document.getElementById('dashboardResetLayout');
        if (resetBtn) resetBtn.classList.toggle('d-none', !enabled);
        if (sortable) sortable.option('disabled', !enabled);

        const toggleBtn = document.getElementById('dashboardCustomizeToggle');
        if (toggleBtn) {
            if (enabled) {
                toggleBtn.classList.remove('btn-outline-secondary');
                toggleBtn.classList.add('btn-primary');
                toggleBtn.innerHTML = '<i class="bi bi-check2 me-1"></i>Done';
            } else {
                toggleBtn.classList.add('btn-outline-secondary');
                toggleBtn.classList.remove('btn-primary');
                toggleBtn.innerHTML = '<i class="bi bi-arrows-move me-1"></i>Customize';
            }
        }
    }

    function setStatus(text) {
        const el = document.getElementById('dashboardLayoutStatus');
        if (!el) return;
        if (!text) {
            el.classList.add('d-none');
            el.textContent = '';
            return;
        }
        el.textContent = text;
        el.classList.remove('d-none');
    }

    let saveTimer = null;
    function saveLayout(order, sizes, hidden, heights, titles, dividerThickness, titleSizes) {
        // Filter + de-dupe client-side too (defense in depth)
        const seen = new Set();
        const filtered = [];
        (order || []).forEach(id => {
            if (typeof id !== 'string') return;
            if (!allowedWidgetIds.includes(id)) return;
            if (seen.has(id)) return;
            seen.add(id);
            filtered.push(id);
        });

        const sizePayload = {};
        if (sizes && typeof sizes === 'object') {
            Object.keys(sizes).forEach(id => {
                if (!allowedWidgetIds.includes(id)) return;
                sizePayload[id] = normalizeSpan(sizes[id]);
            });
        }

        const hiddenPayload = [];
        if (Array.isArray(hidden)) {
            const seenHidden = new Set();
            hidden.forEach(id => {
                if (typeof id !== 'string') return;
                if (!allowedWidgetIds.includes(id)) return;
                if (seenHidden.has(id)) return;
                seenHidden.add(id);
                hiddenPayload.push(id);
            });
        }

        const heightPayload = {};
        if (heights && typeof heights === 'object') {
            Object.keys(heights).forEach(id => {
                if (!allowedWidgetIds.includes(id)) return;
                const h = parseInt(String(heights[id]), 10);
                if (!Number.isFinite(h) || h <= 0) return;
                if (h < allowedHeights.min || h > allowedHeights.max) return;
                heightPayload[id] = h;
            });
        }

        // Title text for title widgets
        const titlesPayload = {};
        if (titles && typeof titles === 'object') {
            Object.keys(titles).forEach(id => {
                if (!allowedWidgetIds.includes(id)) return;
                if (!isTitleWidget(id)) return;
                titlesPayload[id] = String(titles[id]).substring(0, 4000); // limit length (rich HTML)
            });
        }

        // Divider thickness for divider widgets (1/2/3)
        const dividerThicknessPayload = {};
        if (dividerThickness && typeof dividerThickness === 'object') {
            Object.keys(dividerThickness).forEach(id => {
                if (!allowedWidgetIds.includes(id)) return;
                if (!isDividerWidget(id)) return;
                const t = parseInt(String(dividerThickness[id]), 10);
                if (![1, 2, 3].includes(t)) return;
                dividerThicknessPayload[id] = t;
            });
        }

        // Title sizes (1/2/3)
        const titleSizesPayload = {};
        if (titleSizes && typeof titleSizes === 'object') {
            Object.keys(titleSizes).forEach(id => {
                if (!allowedWidgetIds.includes(id)) return;
                if (!isTitleWidget(id)) return;
                const s = parseInt(String(titleSizes[id]), 10);
                if (![1, 2, 3].includes(s)) return;
                titleSizesPayload[id] = s;
            });
        }

        setStatus('Saving…');
        fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                order: filtered,
                sizes: sizePayload,
                hidden: hiddenPayload,
                heights: heightPayload,
                titles: titlesPayload,
                divider_thickness: dividerThicknessPayload,
                title_size: titleSizesPayload,
                csrf_token: csrfToken
            })
        })
        .then(r => r.json().catch(() => null))
        .then(j => {
            if (j && j.success) {
                setStatus('Saved');
                setTimeout(() => setStatus(''), 1200);
            } else {
                setStatus('Save failed');
                setTimeout(() => setStatus(''), 2500);
            }
        })
        .catch(() => {
            setStatus('Save failed');
            setTimeout(() => setStatus(''), 2500);
        });
    }

    function scheduleSave(container) {
        if (saveTimer) clearTimeout(saveTimer);
        saveTimer = setTimeout(() => {
            saveLayout(
                getCurrentOrder(container),
                getCurrentSizes(container),
                getCurrentHidden(container),
                getCurrentHeights(container),
                getCurrentTitles(container),
                getCurrentDividerThickness(container),
                getCurrentTitleSizes(container)
            );
        }, 250);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('dashboardWidgets');
        if (!container) return;

        // Allow whatever widgets are actually rendered (base widgets + pinned cards)
        allowedWidgetIds = Array.from(container.querySelectorAll('.dashboard-widget'))
            .map(el => el.getAttribute('data-widget-id'))
            .filter(id => typeof id === 'string' && id.trim() !== '');

        // Generate unique ID for dividers
        let dividerCounter = Date.now();
        function generateDividerId() {
            return 'divider-' + (dividerCounter++);
        }

        // isDividerWidget is defined in outer scope

        // Create a divider widget element
        function createDividerWidget(id) {
            id = id || generateDividerId();
            const div = document.createElement('div');
            div.className = 'col-12 dashboard-widget dashboard-divider-widget';
            div.setAttribute('data-widget-id', id);
            div.innerHTML = `
                <div class="dashboard-widget-controls">
                    <button type="button" class="dashboard-drag-handle" aria-label="Drag to move" title="Drag to move">
                        <i class="bi bi-grip-vertical"></i>
                    </button>
                    <button type="button" class="dashboard-divider-thickness-btn" data-thickness="1" aria-label="Divider thickness 1" title="Thickness 1">1</button>
                    <button type="button" class="dashboard-divider-thickness-btn" data-thickness="2" aria-label="Divider thickness 2" title="Thickness 2">2</button>
                    <button type="button" class="dashboard-divider-thickness-btn" data-thickness="3" aria-label="Divider thickness 3" title="Thickness 3">3</button>
                    <button type="button" class="dashboard-delete-divider-btn" aria-label="Delete divider" title="Delete divider">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="dashboard-divider-line">
                    <hr>
                </div>
            `;
            return div;
        }

        function setDividerThickness(widgetEl, thickness) {
            let t = parseInt(String(thickness), 10);
            if (![1, 2, 3].includes(t)) t = 1;
            widgetEl.setAttribute('data-divider-thickness', String(t));
            widgetEl.querySelectorAll('.dashboard-divider-thickness-btn').forEach(btn => {
                const bt = parseInt(btn.getAttribute('data-thickness') || '0', 10);
                btn.classList.toggle('active', bt === t);
            });
        }

        // Add a new divider at the end
        function addDivider() {
            const id = generateDividerId();
            const dividerEl = createDividerWidget(id);
            setDividerThickness(dividerEl, 1);
            container.appendChild(dividerEl);
            allowedWidgetIds.push(id);
            updateAllResizeButtonsState(container);
            scheduleSave(container);
        }

        // Delete a divider
        function deleteDivider(widgetEl) {
            const id = widgetEl.getAttribute('data-widget-id');
            if (!id || !isDividerWidget(id)) return;
            widgetEl.remove();
            // Remove from allowedWidgetIds
            const idx = allowedWidgetIds.indexOf(id);
            if (idx > -1) allowedWidgetIds.splice(idx, 1);
            scheduleSave(container);
        }

        // Restore dividers from saved layout
        function restoreDividers(order, thicknessMap) {
            if (!Array.isArray(order)) return;
            thicknessMap = thicknessMap || {};
            order.forEach(id => {
                if (isDividerWidget(id)) {
                    // Only create if not already present
                    if (!container.querySelector('[data-widget-id="' + id + '"]')) {
                        const dividerEl = createDividerWidget(id);
                        setDividerThickness(dividerEl, thicknessMap[id] || 1);
                        container.appendChild(dividerEl);
                        if (!allowedWidgetIds.includes(id)) {
                            allowedWidgetIds.push(id);
                        }
                    } else {
                        const existing = container.querySelector('[data-widget-id="' + id + '"]');
                        if (existing) {
                            setDividerThickness(existing, thicknessMap[id] || 1);
                        }
                    }
                }
            });
        }

        // ========== TITLE WIDGETS ==========
        let titleCounter = Date.now();
        function generateTitleId() {
            return 'title-' + (titleCounter++);
        }

        // isTitleWidget is defined in outer scope

        function createTitleWidget(id, text) {
            id = id || generateTitleId();
            const div = document.createElement('div');
            div.className = 'col-12 dashboard-widget dashboard-title-widget';
            div.setAttribute('data-widget-id', id);
            div.innerHTML = `
                <div class="dashboard-widget-controls">
                    <button type="button" class="dashboard-drag-handle" aria-label="Drag to move" title="Drag to move">
                        <i class="bi bi-grip-vertical"></i>
                    </button>
                    <button type="button" class="dashboard-title-size-btn" data-size="1" aria-label="Title size 1" title="Title size 1">1</button>
                    <button type="button" class="dashboard-title-size-btn" data-size="2" aria-label="Title size 2" title="Title size 2">2</button>
                    <button type="button" class="dashboard-title-size-btn" data-size="3" aria-label="Title size 3" title="Title size 3">3</button>
                    <button type="button" class="dashboard-edit-title-btn" aria-label="Edit title" title="Edit title">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="dashboard-delete-title-btn" aria-label="Delete title" title="Delete title">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="card dashboard-title-card">
                    <div class="card-body py-3">
                        <div class="dashboard-title-text"></div>
                    </div>
                </div>
            `;
            return div;
        }

        // escapeHtml is defined in outer scope

        function setTitleSize(widgetEl, size) {
            let s = parseInt(String(size), 10);
            if (![1, 2, 3].includes(s)) s = 2;
            widgetEl.setAttribute('data-title-size', String(s));
            widgetEl.querySelectorAll('.dashboard-title-size-btn').forEach(btn => {
                const bs = parseInt(btn.getAttribute('data-size') || '0', 10);
                btn.classList.toggle('active', bs === s);
            });
        }

        function setTitleHtml(widgetEl, html) {
            const el = widgetEl.querySelector('.dashboard-title-text');
            if (!el) return;
            el.innerHTML = (html || '').trim();
        }

        function insertNewTitleWidget(html) {
            const id = generateTitleId();
            const titleEl = createTitleWidget(id);
            setTitleSize(titleEl, 2);
            setTitleHtml(titleEl, html || '');
            container.appendChild(titleEl);
            allowedWidgetIds.push(id);
            updateAllResizeButtonsState(container);
            scheduleSave(container);
            return titleEl;
        }

        function deleteTitle(widgetEl) {
            const id = widgetEl.getAttribute('data-widget-id');
            if (!id || !isTitleWidget(id)) return;
            widgetEl.remove();
            const idx = allowedWidgetIds.indexOf(id);
            if (idx > -1) allowedWidgetIds.splice(idx, 1);
            scheduleSave(container);
        }

        function restoreTitles(order, titles, sizeMap) {
            if (!Array.isArray(order)) return;
            titles = titles || {};
            sizeMap = sizeMap || {};
            order.forEach(id => {
                if (isTitleWidget(id)) {
                    if (!container.querySelector('[data-widget-id="' + id + '"]')) {
                        const html = titles[id] || 'Section Title';
                        const titleEl = createTitleWidget(id);
                        setTitleSize(titleEl, sizeMap[id] || 2);
                        setTitleHtml(titleEl, html);
                        container.appendChild(titleEl);
                        if (!allowedWidgetIds.includes(id)) {
                            allowedWidgetIds.push(id);
                        }
                    } else {
                        const existing = container.querySelector('[data-widget-id="' + id + '"]');
                        if (existing) {
                            setTitleSize(existing, sizeMap[id] || 2);
                            setTitleHtml(existing, titles[id] || 'Section Title');
                        }
                    }
                }
            });
        }

        // Title rich editor modal (Quill)
        const titleModalEl = document.getElementById('dashboardTitleModal');
        const titleModalLabelEl = document.getElementById('dashboardTitleModalLabel');
        const titleEditorEl = document.getElementById('dashboardTitleEditor');
        const titleSaveBtnEl = document.getElementById('dashboardTitleModalSave');
        const titleErrorEl = document.getElementById('dashboardTitleModalError');
        let titleModal = null;
        let titleQuill = null;
        let titleEditingWidget = null;
        let titleCreateMode = false;

        function hideTitleError() {
            if (!titleErrorEl) return;
            titleErrorEl.classList.add('d-none');
            titleErrorEl.textContent = '';
        }

        function showTitleError(msg) {
            if (!titleErrorEl) return;
            titleErrorEl.textContent = String(msg || 'Please enter a title.');
            titleErrorEl.classList.remove('d-none');
        }

        function ensureTitleQuill() {
            if (!titleEditorEl || !window.Quill) return null;
            if (titleQuill) return titleQuill;
            titleQuill = new Quill(titleEditorEl, {
                theme: 'snow',
                placeholder: 'Enter title…',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        ['link'],
                        ['clean']
                    ]
                }
            });
            return titleQuill;
        }

        function openTitleModalForCreate() {
            if (!titleModalEl || !window.bootstrap) return;
            const q = ensureTitleQuill();
            if (!q) return;
            hideTitleError();
            titleCreateMode = true;
            titleEditingWidget = null;
            if (titleModalLabelEl) titleModalLabelEl.textContent = 'Create Title';
            q.setText('');
            titleModal = bootstrap.Modal.getOrCreateInstance(titleModalEl);
            titleModal.show();
            setTimeout(() => q.focus(), 150);
        }

        function openTitleModalForEdit(widgetEl) {
            if (!titleModalEl || !window.bootstrap) return;
            const q = ensureTitleQuill();
            if (!q) return;
            hideTitleError();
            titleCreateMode = false;
            titleEditingWidget = widgetEl || null;
            if (titleModalLabelEl) titleModalLabelEl.textContent = 'Edit Title';
            const existingHtml = widgetEl ? (widgetEl.querySelector('.dashboard-title-text')?.innerHTML || '') : '';
            q.setText('');
            q.clipboard.dangerouslyPasteHTML(existingHtml || '');
            titleModal = bootstrap.Modal.getOrCreateInstance(titleModalEl);
            titleModal.show();
            setTimeout(() => q.focus(), 150);
        }

        if (titleSaveBtnEl) {
            titleSaveBtnEl.addEventListener('click', function() {
                const q = ensureTitleQuill();
                if (!q) return;
                const plain = (q.getText() || '').trim();
                if (!plain) {
                    showTitleError('Title content is required.');
                    return;
                }
                hideTitleError();
                const html = q.root.innerHTML || '';
                if (titleCreateMode) {
                    insertNewTitleWidget(html);
                } else if (titleEditingWidget) {
                    setTitleHtml(titleEditingWidget, html);
                    scheduleSave(container);
                }
                if (titleModal) titleModal.hide();
            });
        }

        if (titleModalEl) {
            titleModalEl.addEventListener('hidden.bs.modal', function() {
                hideTitleError();
                titleEditingWidget = null;
                titleCreateMode = false;
            });
        }

        // Apply saved order + sizes before wiring interactions
        const savedLayout = parseSavedLayout(savedLayoutRaw);
        // Restore any dividers and titles from saved layout first
        if (savedLayout.order) {
            restoreDividers(savedLayout.order, savedLayout.divider_thickness);
            restoreTitles(savedLayout.order, savedLayout.titles, savedLayout.title_size);
            applyOrder(container, savedLayout.order);
        }
        applySizes(container, savedLayout.sizes);
        applyHeights(container, savedLayout.heights);
        applyHidden(container, savedLayout.hidden);
        updateAllResizeButtonsState(container);
        syncWidgetToggles(container);

        // Show/hide toggles (modal)
        document.querySelectorAll('.dashboard-widget-toggle').forEach(cb => {
            cb.addEventListener('change', function() {
                const id = this.getAttribute('data-widget-id');
                if (!id || !allowedWidgetIds.includes(id)) return;
                setWidgetHiddenById(container, id, !this.checked);
                updateAllResizeButtonsState(container);
                scheduleSave(container);
            });
        });

        // Make compact stat cards clickable (skip while customizing)
        const clickableCards = document.querySelectorAll('.clickable-card');
        clickableCards.forEach(card => {
            card.addEventListener('click', function() {
                if (window.__dashboardCustomizeMode) return;
                const href = this.getAttribute('data-href');
                if (href) {
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        window.location.href = href;
                    }, 100);
                }
            });

            card.setAttribute('tabindex', '0');
            card.setAttribute('role', 'button');

            card.addEventListener('keydown', function(e) {
                if (window.__dashboardCustomizeMode) return;
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });

            card.addEventListener('focus', function() {
                if (window.__dashboardCustomizeMode) return;
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.15)';
            });

            card.addEventListener('blur', function() {
                this.style.transform = '';
                this.style.boxShadow = '';
            });
        });

        // Resize/hide/delete buttons (only in customize mode)
        container.addEventListener('click', function(e) {
            // Divider thickness buttons (1/2/3)
            const thicknessBtn = e.target.closest('.dashboard-divider-thickness-btn');
            if (thicknessBtn) {
                if (!window.__dashboardCustomizeMode) return;
                e.preventDefault();
                e.stopPropagation();
                const widget = thicknessBtn.closest('.dashboard-divider-widget');
                if (!widget) return;
                const t = parseInt(thicknessBtn.getAttribute('data-thickness') || '1', 10);
                setDividerThickness(widget, t);
                scheduleSave(container);
                return;
            }

            // Title size buttons (1/2/3)
            const titleSizeBtn = e.target.closest('.dashboard-title-size-btn');
            if (titleSizeBtn) {
                if (!window.__dashboardCustomizeMode) return;
                e.preventDefault();
                e.stopPropagation();
                const widget = titleSizeBtn.closest('.dashboard-title-widget');
                if (!widget) return;
                const s = parseInt(titleSizeBtn.getAttribute('data-size') || '2', 10);
                setTitleSize(widget, s);
                scheduleSave(container);
                return;
            }

            // Delete divider button
            const deleteDividerBtn = e.target.closest('.dashboard-delete-divider-btn');
            if (deleteDividerBtn) {
                if (!window.__dashboardCustomizeMode) return;
                e.preventDefault();
                e.stopPropagation();
                const widget = deleteDividerBtn.closest('.dashboard-widget');
                if (widget) {
                    deleteDivider(widget);
                }
                return;
            }

            // Delete title button
            const deleteTitleBtn = e.target.closest('.dashboard-delete-title-btn');
            if (deleteTitleBtn) {
                if (!window.__dashboardCustomizeMode) return;
                e.preventDefault();
                e.stopPropagation();
                const widget = deleteTitleBtn.closest('.dashboard-widget');
                if (widget) {
                    deleteTitle(widget);
                }
                return;
            }

            // Edit title button
            const editTitleBtn = e.target.closest('.dashboard-edit-title-btn');
            if (editTitleBtn) {
                if (!window.__dashboardCustomizeMode) return;
                e.preventDefault();
                e.stopPropagation();
                const widget = editTitleBtn.closest('.dashboard-widget');
                if (widget) {
                    openTitleModalForEdit(widget);
                }
                return;
            }

            const hideBtn = e.target.closest('.dashboard-hide-btn');
            if (hideBtn) {
                if (!window.__dashboardCustomizeMode) return;
                e.preventDefault();
                e.stopPropagation();
                const widget = hideBtn.closest('.dashboard-widget');
                if (!widget) return;
                const id = widget.getAttribute('data-widget-id');
                if (!id || !allowedWidgetIds.includes(id)) return;
                setWidgetHiddenById(container, id, true);
                syncWidgetToggles(container);
                scheduleSave(container);
                return;
            }

            const heightBtn = e.target.closest('.dashboard-height-btn');
            if (heightBtn) {
                if (!window.__dashboardCustomizeMode) return;
                e.preventDefault();
                e.stopPropagation();

                const widget = heightBtn.closest('.dashboard-widget');
                if (!widget) return;
                const id = widget.getAttribute('data-widget-id');
                if (!id || !allowedWidgetIds.includes(id)) return;

                const dir = heightBtn.getAttribute('data-height');
                const explicit = getWidgetHeightPx(widget); // 0 = auto
                const currentAuto = widget.getBoundingClientRect().height;
                const base = explicit > 0
                    ? explicit
                    : Math.max(allowedHeights.min, Math.round(currentAuto / allowedHeights.step) * allowedHeights.step);

                let next = base;
                if (dir === 'taller') {
                    next = base + allowedHeights.step;
                    next = Math.min(allowedHeights.max, next);
                    setWidgetHeightPx(widget, next);
                } else if (dir === 'shorter') {
                    next = base - allowedHeights.step;
                    if (explicit > 0 && next < allowedHeights.min) {
                        // allow returning to auto height by shrinking below minimum
                        setWidgetHeightPx(widget, 0);
                    } else {
                        next = Math.max(allowedHeights.min, next);
                        setWidgetHeightPx(widget, next);
                    }
                }

                updateResizeButtonsState(widget);
                scheduleSave(container);
                return;
            }

            const btn = e.target.closest('.dashboard-resize-btn');
            if (!btn) return;
            if (!window.__dashboardCustomizeMode) return;
            e.preventDefault();
            e.stopPropagation();

            const widget = btn.closest('.dashboard-widget');
            if (!widget) return;

            const dir = btn.getAttribute('data-resize');
            const current = normalizeSpan(getLgSpan(widget));
            const idx = Math.max(0, allowedSpans.indexOf(current));
            let next = current;
            if (dir === 'smaller') {
                next = allowedSpans[Math.max(0, idx - 1)];
            } else if (dir === 'bigger') {
                next = allowedSpans[Math.min(allowedSpans.length - 1, idx + 1)];
            }

            setLgSpan(widget, next);
            updateResizeButtonsState(widget);
            scheduleSave(container);
        });

        // Sortable widgets (disabled by default until Customize is clicked)
        let sortable = null;
        if (typeof Sortable !== 'undefined') {
            sortable = new Sortable(container, {
                animation: 150,
                handle: '.dashboard-drag-handle',
                draggable: '.dashboard-widget',
                group: { name: 'dashboard-layout', pull: true, put: true },
                ghostClass: 'dashboard-ghost',
                chosenClass: 'dashboard-chosen',
                onEnd: function() {
                    if (!window.__dashboardCustomizeMode) return;
                    scheduleSave(container);
                }
            });
            sortable.option('disabled', true);
        }

        const toggleBtn = document.getElementById('dashboardCustomizeToggle');
        const addDividerBtn = document.getElementById('dashboardAddDivider');
        const addTitleBtn = document.getElementById('dashboardAddTitle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const enabled = !document.body.classList.contains('dashboard-customize-mode');
                setCustomizeMode(enabled, sortable);
                updateAllResizeButtonsState(container);
                // Show/hide Add Divider and Add Title buttons
                if (addDividerBtn) {
                    addDividerBtn.classList.toggle('d-none', !enabled);
                }
                if (addTitleBtn) {
                    addTitleBtn.classList.toggle('d-none', !enabled);
                }
            });
        }

        // Add Divider button
        if (addDividerBtn) {
            addDividerBtn.addEventListener('click', function() {
                if (!window.__dashboardCustomizeMode) return;
                addDivider();
            });
        }

        // Add Title button
        if (addTitleBtn) {
            addTitleBtn.addEventListener('click', function() {
                if (!window.__dashboardCustomizeMode) return;
                openTitleModalForCreate();
            });
        }

        const resetBtn = document.getElementById('dashboardResetLayout');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                if (!confirm('Reset your dashboard layout back to default?')) return;
                setStatus('Resetting…');
                fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ action: 'reset', csrf_token: csrfToken })
                })
                .then(r => r.json().catch(() => null))
                .then(j => {
                    if (j && j.success) {
                        window.location.reload();
                    } else {
                        setStatus('Reset failed');
                        setTimeout(() => setStatus(''), 2500);
                    }
                })
                .catch(() => {
                    setStatus('Reset failed');
                    setTimeout(() => setStatus(''), 2500);
                });
            });
        }

        // Ensure customize starts off
        setCustomizeMode(false, sortable);
    });
})();
</script>

<?php require VIEWSPATH . '/partials/footer.php'; ?> 
