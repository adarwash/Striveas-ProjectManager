<?php
// Set title for the page
$title = 'Dashboard - ' . DEFAULT_TITLE;
?>

<?php require VIEWSPATH . '/partials/header.php'; ?>

<!-- Modern Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-tachometer-alt me-3"></i>Dashboard</h1>
        <p class="text-muted mb-0">Welcome back, <?= $_SESSION['user_name'] ?>!</p>
    </div>
    <div>
        <span class="badge bg-light text-dark"><?= $_SESSION['role'] ?? 'User' ?></span>
    </div>
</div>

<!-- Modern Stats Overview -->
<div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
        <div class="stats-card purple clickable-card" data-href="/clients">
            <div class="d-flex align-items-center justify-content-between">
                    <div>
                    <div class="stats-number"><?= $stats['active_clients'] ?? 0 ?></div>
                    <div class="stats-label">Active Clients</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-building"></i>
                </div>
            </div>
        </div>
                    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card green clickable-card" data-href="/users">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number"><?= $stats['total_users'] ?? 0 ?></div>
                    <div class="stats-label">Total Users</div>
                    </div>
                <div class="stats-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card orange clickable-card" data-href="/users?role=technician">
            <div class="d-flex align-items-center justify-content-between">
                    <div>
                    <div class="stats-number"><?= $stats['technicians'] ?? 0 ?></div>
                    <div class="stats-label">Technicians</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-tools"></i>
                </div>
            </div>
        </div>
                    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card blue clickable-card" data-href="/sites">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number"><?= $stats['active_sites'] ?? 0 ?></div>
                    <div class="stats-label">Active Sites</div>
                    </div>
                <div class="stats-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats Row -->
<div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
        <div class="stats-card purple clickable-card" data-href="/clients?status=Prospect">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number"><?= $stats['prospect_clients'] ?? 0 ?></div>
                    <div class="stats-label">Prospect Clients</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card red clickable-card" data-href="/tickets?status=open">
            <div class="d-flex align-items-center justify-content-between">
                    <div>
                    <div class="stats-number"><?= $stats['open_tickets'] ?? 0 ?></div>
                    <div class="stats-label">Open Tickets</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
                    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card orange clickable-card" data-href="/tasks?status=open">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number"><?= $stats['open_tasks'] ?? 0 ?></div>
                    <div class="stats-label">Open Tasks</div>
                    </div>
                <div class="stats-icon">
                    <i class="fas fa-list-ul"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="stats-card green clickable-card" data-href="/time/dashboard">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number"><?= $stats['currently_working'] ?? 0 ?></div>
                    <div class="stats-label">Currently Working</div>
                    </div>
                <div class="stats-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- My Tasks -->
<div class="card mb-5">
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

<!-- Recent Activity and Top Clients Section -->
<div class="row g-4">
    <div class="col-lg-8">
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

    <div class="col-lg-4">
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
</div>

<!-- Dashboard uses global styles from app.css -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Make cards clickable
    const clickableCards = document.querySelectorAll('.clickable-card');
    
    clickableCards.forEach(card => {
        card.addEventListener('click', function() {
            const href = this.getAttribute('data-href');
            if (href) {
                // Add a subtle animation before navigation
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    window.location.href = href;
                }, 100);
            }
        });
        
        // Add keyboard accessibility
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');
        
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
        
        // Add hover effect for keyboard focus
        card.addEventListener('focus', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.15)';
        });
        
        card.addEventListener('blur', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
});
</script>

<?php require VIEWSPATH . '/partials/footer.php'; ?> 