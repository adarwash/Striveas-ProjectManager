<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Ticket Management</h1>
            <p class="text-muted mb-0">Administrator-only settings for support tickets.</p>
        </div>
        <a href="<?= URLROOT ?>/admin" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Admin
        </a>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                            <i class="bi bi-tags text-primary fs-5"></i>
                        </span>
                        <div>
                            <h5 class="mb-1">Categories</h5>
                            <div class="text-muted small mb-2">Add, activate/deactivate, or delete ticket categories.</div>
                            <a class="btn btn-sm btn-primary" href="<?= URLROOT ?>/admin/tickets/categories">
                                Manage Categories
                            </a>
                        </div>
                    </div>
                    <?php if (!empty($data['categories'])): ?>
                        <div class="mt-3">
                            <div class="small text-muted mb-1">Active categories</div>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($data['categories'] as $cat): ?>
                                    <?php if (!empty($cat['is_active'])): ?>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($cat['name']) ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="bg-info bg-opacity-10 p-2 rounded-circle me-3">
                            <i class="bi bi-envelope-paper text-info fs-5"></i>
                        </span>
                        <div>
                            <h5 class="mb-1">Ticket Email Settings</h5>
                            <div class="text-muted small mb-2">Support mailbox, sending options, and cron status.</div>
                            <a class="btn btn-sm btn-info text-white" href="<?= URLROOT ?>/admin/emailSettings">
                                Configure Email
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="bg-secondary bg-opacity-10 p-2 rounded-circle me-3">
                            <i class="bi bi-gear text-secondary fs-5"></i>
                        </span>
                        <div>
                            <h5 class="mb-1">More coming soon</h5>
                            <div class="text-muted small">Escalation rules, SLA defaults, and notifications.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
