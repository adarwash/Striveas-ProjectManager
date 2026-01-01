<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Ticket Categories</h1>
            <p class="text-muted mb-0">Manage ticket categories and optional SLA hours.</p>
        </div>
        <a href="<?= URLROOT ?>/admin/tickets/settings" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Ticket Settings
        </a>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Add Category</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= URLROOT ?>/admin/addCategory">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Hardware">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">SLA Hours (optional)</label>
                            <input type="number" name="sla_hours" class="form-control" min="0" placeholder="e.g. 24">
                            <div class="form-text">If set, this category’s SLA hours are used for response target.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Add
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Existing Categories</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th class="text-center">Active</th>
                                    <th class="text-center">SLA Hours</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data['categories'])): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No categories yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($data['categories'] as $cat): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($cat['name']) ?></td>
                                            <td class="text-center">
                                                <?php if (!empty($cat['is_active'])): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?= isset($cat['sla_hours']) ? (int)$cat['sla_hours'] : '-' ?></td>
                                            <td class="text-end">
                                                <?php if (!empty($cat['is_active'])): ?>
                                                    <a class="btn btn-sm btn-outline-secondary" href="<?= URLROOT ?>/admin/toggleCategory/<?= (int)$cat['id'] ?>?active=0">Deactivate</a>
                                                <?php else: ?>
                                                    <a class="btn btn-sm btn-outline-success" href="<?= URLROOT ?>/admin/toggleCategory/<?= (int)$cat['id'] ?>?active=1">Activate</a>
                                                <?php endif; ?>
                                                <a class="btn btn-sm btn-outline-danger" href="<?= URLROOT ?>/admin/deleteCategory/<?= (int)$cat['id'] ?>" onclick="return confirm('Delete this category?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
