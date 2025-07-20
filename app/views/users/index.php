<?php require VIEWSPATH . '/partials/header.php'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-users me-3"></i>
            <?= isset($filtered_role) ? ucfirst($filtered_role) . ' Directory' : 'User Directory' ?>
        </h1>
        <p class="text-muted mb-0">
            <?= isset($filtered_role) ? 'Showing all ' . $filtered_role . 's' : 'Directory of all system users' ?>
        </p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <?= isset($filtered_role) ? ucfirst($filtered_role) . 's' : 'Users' ?>
                        <span class="badge bg-primary ms-2"><?= count($users) ?></span>
                    </h5>
                    <?php if (isset($filtered_role)): ?>
                        <a href="/users" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-1"></i>Clear Filter
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card-body p-0">
                <?php if (empty($users)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                        <h5 class="text-muted mt-3">
                            <?= isset($filtered_role) ? 'No ' . $filtered_role . 's found' : 'No users found' ?>
                        </h5>
                        <p class="text-muted">
                            <?= isset($filtered_role) ? 'There are currently no users with the role of ' . $filtered_role : 'There are currently no users in the system' ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">User</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Last Login</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                                    <?= strtoupper(substr($user['name'], 0, 2)) ?>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="fw-bold mb-0"><?= htmlspecialchars($user['name']) ?></p>
                                                <?php if (!empty($user['full_name']) && $user['full_name'] !== $user['name']): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($user['full_name']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($user['email']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= getUserRoleColor($user['role']) ?>">
                                            <?= ucfirst(htmlspecialchars($user['role'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= $user['last_login'] ? date('M j, Y', strtotime($user['last_login'])) : 'Never' ?>
                                        </small>
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
</div>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 14px;
    font-weight: 600;
}

.card {
    border-radius: 12px;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.85rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table td {
    border-color: #f8f9fa;
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}
</style>

<?php
// Helper function for role colors
function getUserRoleColor($role) {
    switch (strtolower($role)) {
        case 'admin':
            return 'danger';
        case 'manager':
            return 'warning';
        case 'technician':
            return 'info';
        default:
            return 'secondary';
    }
}
?>

<?php require VIEWSPATH . '/partials/footer.php'; ?> 