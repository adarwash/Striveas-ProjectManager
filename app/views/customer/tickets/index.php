<?php require VIEWSPATH . '/customer/inc/header.php'; ?>

<style>
.tickets-header {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.filter-tabs {
    border: none;
    background: none;
}

.filter-tabs .nav-link {
    border: none;
    border-radius: 25px;
    padding: 0.7rem 1.5rem;
    color: #6c757d;
    font-weight: 500;
    transition: all 0.3s ease;
    margin-right: 0.5rem;
}

.filter-tabs .nav-link:hover {
    background: #f8f9fa;
    color: #495057;
}

.filter-tabs .nav-link.active {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
}

.ticket-item {
    background: white;
    border-radius: 12px;
    border: none;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    margin-bottom: 1rem;
    position: relative;
    overflow: hidden;
}

.ticket-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.ticket-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 4px;
    height: 100%;
    background: var(--priority-color, #6c757d);
}

.ticket-number {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #495057;
    font-size: 0.9rem;
}

.ticket-subject {
    color: #212529;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.ticket-subject:hover {
    color: #667eea;
}

.status-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.priority-badge {
    padding: 0.3rem 0.6rem;
    border-radius: 15px;
    font-size: 0.7rem;
    font-weight: 500;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}

.empty-state i {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 1rem;
}

.stats-mini {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.stat-mini {
    text-align: center;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    min-width: 80px;
}

.stat-mini-number {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 0.2rem;
}

.stat-mini-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.5s ease-out;
}
</style>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="tickets-header fade-in-up">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h3 class="mb-2">
                    <i class="bi bi-ticket-perforated me-2 text-primary"></i>My Support Tickets
                </h3>
                <p class="text-muted mb-0">View and track your support requests</p>
            </div>
            <div class="stats-mini">
                <div class="stat-mini">
                    <div class="stat-mini-number text-primary"><?= $data['stats']['total'] ?></div>
                    <div class="stat-mini-label">Total</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-mini-number text-warning"><?= $data['stats']['open'] ?></div>
                    <div class="stat-mini-label">Open</div>
                </div>
                <div class="stat-mini">
                    <div class="stat-mini-number text-success"><?= $data['stats']['closed'] ?></div>
                    <div class="stat-mini-label">Resolved</div>
                </div>
            </div>
        </div>
        
        <!-- Filter Tabs -->
        <ul class="nav filter-tabs mt-3" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= $data['current_status'] === 'all' ? 'active' : '' ?>" 
                   href="<?= URLROOT ?>/customer/tickets/all">
                    All Tickets
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= $data['current_status'] === 'open' ? 'active' : '' ?>" 
                   href="<?= URLROOT ?>/customer/tickets/open">
                    Open
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= $data['current_status'] === 'closed' ? 'active' : '' ?>" 
                   href="<?= URLROOT ?>/customer/tickets/closed">
                    Resolved
                </a>
            </li>
        </ul>
    </div>

    <!-- Tickets List -->
    <div class="row">
        <div class="col-12">
            <?php if (!empty($data['tickets'])): ?>
                <?php foreach ($data['tickets'] as $index => $ticket): ?>
                    <div class="ticket-item fade-in-up" 
                         style="--priority-color: <?= htmlspecialchars($ticket['priority_color'] ?? '#6c757d') ?>; animation-delay: <?= $index * 0.1 ?>s;">
                        <div class="card-body p-4 ps-5">
                            <div class="row align-items-center">
                                <!-- Ticket Info -->
                                <div class="col-lg-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="ticket-number me-3"><?= htmlspecialchars($ticket['ticket_number']) ?></span>
                                        <span class="status-badge me-2" 
                                              style="background-color: <?= htmlspecialchars($ticket['status_color']) ?>20; color: <?= htmlspecialchars($ticket['status_color']) ?>;">
                                            <?= htmlspecialchars($ticket['status_display']) ?>
                                        </span>
                                        <span class="priority-badge" 
                                              style="background-color: <?= htmlspecialchars($ticket['priority_color']) ?>20; color: <?= htmlspecialchars($ticket['priority_color']) ?>;">
                                            <?= htmlspecialchars($ticket['priority_display']) ?>
                                        </span>
                                    </div>
                                    <h6 class="mb-2">
                                        <a href="<?= URLROOT ?>/tickets/show/<?= $ticket['id'] ?>" class="ticket-subject">
                                            <?= htmlspecialchars($ticket['subject']) ?>
                                        </a>
                                    </h6>
                                    <div class="text-muted small">
                                        <i class="bi bi-calendar me-1"></i>
                                        Created: <?= date('M j, Y g:i A', strtotime($ticket['created_at'])) ?>
                                        <?php if ($ticket['last_activity']): ?>
                                            <span class="ms-3">
                                                <i class="bi bi-clock me-1"></i>
                                                Last activity: <?= date('M j, Y g:i A', strtotime($ticket['last_activity'])) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Additional Info -->
                                <div class="col-lg-3 text-lg-center">
                                    <?php if ($ticket['category']): ?>
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Category</small>
                                            <span class="badge bg-light text-dark">
                                                <?= htmlspecialchars($ticket['category_display'] ?? $ticket['category']) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($ticket['assigned_to_name']): ?>
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Assigned to</small>
                                            <span class="text-dark"><?= htmlspecialchars($ticket['assigned_to_name']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Actions -->
                                <div class="col-lg-3 text-lg-end">
                                    <div class="mb-2">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-chat-dots me-1"></i>
                                            <?= $ticket['message_count'] ?> message<?= $ticket['message_count'] != 1 ? 's' : '' ?>
                                        </span>
                                    </div>
                                    <a href="<?= URLROOT ?>/tickets/show/<?= $ticket['id'] ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state fade-in-up">
                    <?php if ($data['current_status'] === 'open'): ?>
                        <i class="bi bi-check-circle"></i>
                        <h4 class="text-muted mb-3">No Open Tickets</h4>
                        <p class="text-muted">Great news! You don't have any open support tickets at the moment.</p>
                    <?php elseif ($data['current_status'] === 'closed'): ?>
                        <i class="bi bi-archive"></i>
                        <h4 class="text-muted mb-3">No Resolved Tickets</h4>
                        <p class="text-muted">You don't have any resolved tickets to display.</p>
                    <?php else: ?>
                        <i class="bi bi-inbox"></i>
                        <h4 class="text-muted mb-3">No Tickets Found</h4>
                        <p class="text-muted">You don't have any support tickets yet.</p>
                    <?php endif; ?>
                    
                    <a href="<?= URLROOT ?>/customer/dashboard" class="btn btn-primary mt-3">
                        <i class="bi bi-house me-2"></i>Back to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require VIEWSPATH . '/customer/inc/footer.php'; ?>
