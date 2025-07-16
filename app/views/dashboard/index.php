<?php
// Set title for the page
$title = 'Dashboard - HiveIT Portal';
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
        <div class="stats-card purple">
            <div class="d-flex align-items-center justify-content-between">
                    <div>
                    <div class="stats-number">1</div>
                    <div class="stats-label">Active Clients</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-building"></i>
                </div>
            </div>
        </div>
                    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card green">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number">2</div>
                    <div class="stats-label">Total Users</div>
                    </div>
                <div class="stats-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card orange">
            <div class="d-flex align-items-center justify-content-between">
                    <div>
                    <div class="stats-number">1</div>
                    <div class="stats-label">Technicians</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-tools"></i>
                </div>
            </div>
        </div>
                    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number">1</div>
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
        <div class="stats-card red">
            <div class="d-flex align-items-center justify-content-between">
                    <div>
                    <div class="stats-number">0</div>
                    <div class="stats-label">Open Tickets</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
                    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card orange">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number">0</div>
                    <div class="stats-label">Open Tasks</div>
                    </div>
                <div class="stats-icon">
                    <i class="fas fa-list-ul"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card blue">
            <div class="d-flex align-items-center justify-content-between">
                    <div>
                    <div class="stats-number">0</div>
                    <div class="stats-label">Pending Requests</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-laptop"></i>
                </div>
            </div>
        </div>
                    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card green">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="stats-number">0</div>
                    <div class="stats-label">Currently Working</div>
                    </div>
                <div class="stats-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
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
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-star me-2"></i>Top Clients</h5>
            </div>
            <div class="card-body">
                <div class="client-list">
                    <div class="client-item">
                        <div class="client-info">
                            <div class="client-name">PSandB</div>
                            <div class="client-projects">Multiple projects</div>
                                        </div>
                        <div class="client-badge">
                            <span class="badge bg-primary">0</span>
                                    </div>
                                </div>
                            </div>
                <div class="text-center mt-3">
                    <a href="/clients" class="btn btn-sm btn-outline-primary">View All Clients</a>
                    </div>
            </div>
        </div>
    </div>
</div>



<?php require VIEWSPATH . '/partials/footer.php'; ?> 