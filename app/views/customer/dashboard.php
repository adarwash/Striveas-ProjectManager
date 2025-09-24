<?php require VIEWSPATH . '/customer/inc/header.php'; ?>

<style>
.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.dashboard-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    transform: translate(50px, -50px);
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    border: none;
    transition: all 0.3s ease;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.ticket-card {
    background: white;
    border-radius: 12px;
    border: none;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    margin-bottom: 1rem;
}

.ticket-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
}

.ticket-number {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #495057;
}

.status-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.priority-indicator {
    width: 4px;
    height: 100%;
    position: absolute;
    left: 0;
    top: 0;
    border-radius: 12px 0 0 12px;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}
</style>

<div class="container-fluid px-4">
    <!-- Dashboard Header -->
    <div class="dashboard-header fade-in-up">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2">Welcome back, <?= htmlspecialchars($data['customer_name']) ?>!</h2>
                <p class="mb-0 opacity-75">
                    <i class="bi bi-envelope me-2"></i><?= htmlspecialchars($data['customer_email']) ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-flex gap-2 justify-content-md-end">
                    <a href="<?= URLROOT ?>/customer/tickets" class="btn btn-light">
                        <i class="bi bi-ticket-perforated me-2"></i>View All Tickets
                    </a>
                    <a href="<?= URLROOT ?>/customer/profile" class="btn btn-outline-light">
                        <i class="bi bi-person me-2"></i>Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card fade-in-up" style="animation-delay: 0.1s;">
                <div class="stat-icon bg-primary bg-opacity-10">
                    <i class="bi bi-ticket-perforated text-primary"></i>
                </div>
                <div class="stat-number text-primary"><?= $data['stats']['total'] ?></div>
                <div class="text-muted">Total Tickets</div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="stat-card fade-in-up" style="animation-delay: 0.2s;">
                <div class="stat-icon bg-warning bg-opacity-10">
                    <i class="bi bi-clock text-warning"></i>
                </div>
                <div class="stat-number text-warning"><?= $data['stats']['open'] ?></div>
                <div class="text-muted">Open Tickets</div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="stat-card fade-in-up" style="animation-delay: 0.3s;">
                <div class="stat-icon bg-success bg-opacity-10">
                    <i class="bi bi-check-circle text-success"></i>
                </div>
                <div class="stat-number text-success"><?= $data['stats']['closed'] ?></div>
                <div class="text-muted">Resolved</div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <div class="stat-card fade-in-up" style="animation-delay: 0.4s;">
                <div class="stat-icon bg-danger bg-opacity-10">
                    <i class="bi bi-exclamation-triangle text-danger"></i>
                </div>
                <div class="stat-number text-danger"><?= $data['stats']['high_priority'] ?></div>
                <div class="text-muted">High Priority</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Tickets -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm fade-in-up" style="animation-delay: 0.5s;">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history me-2 text-primary"></i>Recent Tickets
                        </h5>
                        <a href="<?= URLROOT ?>/customer/tickets" class="btn btn-sm btn-outline-primary">
                            View All <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($data['recent_tickets'])): ?>
                        <?php foreach ($data['recent_tickets'] as $index => $ticket): ?>
                            <div class="ticket-card mx-3 my-3" style="animation-delay: <?= 0.6 + ($index * 0.1) ?>s;">
                                <div class="priority-indicator" style="background-color: <?= htmlspecialchars($ticket['priority_color'] ?? '#6c757d') ?>;"></div>
                                <div class="card-body py-3 ps-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="ticket-number me-3"><?= htmlspecialchars($ticket['ticket_number']) ?></span>
                                                <span class="status-badge" style="background-color: <?= htmlspecialchars($ticket['status_color']) ?>20; color: <?= htmlspecialchars($ticket['status_color']) ?>;">
                                                    <?= htmlspecialchars($ticket['status_display']) ?>
                                                </span>
                                            </div>
                                            <h6 class="mb-1">
                                                <a href="<?= URLROOT ?>/tickets/show/<?= $ticket['id'] ?>" class="text-decoration-none text-dark">
                                                    <?= htmlspecialchars($ticket['subject']) ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>
                                                <?= date('M j, Y', strtotime($ticket['created_at'])) ?>
                                            </small>
                                        </div>
                                        <div class="col-md-3 text-md-center">
                                            <small class="text-muted d-block">Priority</small>
                                            <span class="badge" style="background-color: <?= htmlspecialchars($ticket['priority_color']) ?>;">
                                                <?= htmlspecialchars($ticket['priority_display']) ?>
                                            </span>
                                        </div>
                                        <div class="col-md-3 text-md-end">
                                            <small class="text-muted d-block">Messages</small>
                                            <span class="badge bg-light text-dark">
                                                <i class="bi bi-chat-dots me-1"></i><?= $ticket['message_count'] ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No tickets found</h5>
                            <p class="text-muted">You don't have any support tickets yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4 fade-in-up" style="animation-delay: 0.7s;">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning me-2 text-warning"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= URLROOT ?>/customer/tickets/open" class="btn btn-outline-primary">
                            <i class="bi bi-clock me-2"></i>View Open Tickets
                        </a>
                        <a href="<?= URLROOT ?>/customer/tickets/closed" class="btn btn-outline-success">
                            <i class="bi bi-check-circle me-2"></i>View Resolved Tickets
                        </a>
                        <a href="<?= URLROOT ?>/customer/profile" class="btn btn-outline-secondary">
                            <i class="bi bi-person me-2"></i>My Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Account Info -->
            <div class="card border-0 shadow-sm fade-in-up" style="animation-delay: 0.8s;">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2 text-info"></i>Account Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Access Level</small>
                        <span class="badge bg-info">
                            <?= $data['ticket_visibility'] === 'domain_match' ? 'Company Tickets' : 'Personal Tickets' ?>
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Average Resolution Time</small>
                        <strong><?= $data['stats']['avg_resolution_days'] ?> days</strong>
                    </div>
                    
                    <div class="alert alert-light border-0 mb-0">
                        <small class="text-muted">
                            <i class="bi bi-shield-check me-1"></i>
                            Your data is secure and protected with Microsoft 365 authentication.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require VIEWSPATH . '/customer/inc/footer.php'; ?>
