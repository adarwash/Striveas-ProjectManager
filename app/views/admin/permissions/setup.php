<?php require_once APPROOT . '/views/partials/head.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Permission Setup</h1>
                    <p class="text-muted">Set up page access permissions and role assignments</p>
                </div>
                <div>
                    <a href="/permissions" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Permissions
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php flash('permissions_message'); ?>

    <!-- Setup Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear"></i> Permission Setup Utility
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-4">
                        This utility will add missing page access permissions to the database and assign them to appropriate roles.
                        This is a one-time setup that should be run after system installation or when new modules are added.
                    </p>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>What this will do:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Add page access permissions for all system modules (dashboard, projects, tasks, etc.)</li>
                            <li>Assign appropriate permissions to each role (super_admin, admin, manager, employee, client, viewer)</li>
                            <li>Skip any permissions that already exist</li>
                            <li>Update the navigation menu to show/hide items based on user permissions</li>
                        </ul>
                    </div>

                    <form method="POST" action="/permissions/setup">
                        <div class="d-flex gap-2">
                            <button type="submit" name="action" value="setup_permissions" class="btn btn-primary">
                                <i class="bi bi-play-circle"></i> Setup Page Permissions
                            </button>
                            <button type="submit" name="action" value="assign_roles" class="btn btn-success">
                                <i class="bi bi-person-check"></i> Assign Role Permissions
                            </button>
                            <button type="submit" name="action" value="full_setup" class="btn btn-warning">
                                <i class="bi bi-lightning"></i> Run Full Setup
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Display -->
    <?php if (isset($data['results']) && !empty($data['results'])): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-terminal"></i> Setup Results
                    </h5>
                </div>
                <div class="card-body">
                    <pre class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><?php
                        foreach ($data['results'] as $result) {
                            echo htmlspecialchars($result) . "\n";
                        }
                    ?></pre>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Current Permissions Overview -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-check"></i> Current System Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3 class="text-primary"><?= $data['stats']['total_permissions'] ?? 0 ?></h3>
                                <p class="text-muted">Total Permissions</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3 class="text-success"><?= $data['stats']['total_roles'] ?? 0 ?></h3>
                                <p class="text-muted">Total Roles</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h3 class="text-info"><?= $data['stats']['total_users'] ?? 0 ?></h3>
                                <p class="text-muted">Total Users</p>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($data['permissions_by_module'])): ?>
                    <hr>
                    <h6>Permissions by Module</h6>
                    <div class="row">
                        <?php foreach ($data['permissions_by_module'] as $module => $count): ?>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <div class="d-flex justify-content-between">
                                <span class="text-capitalize"><?= htmlspecialchars($module) ?>:</span>
                                <span class="badge bg-secondary"><?= $count ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.btn {
    border-radius: 0.375rem;
}

.alert {
    border-radius: 0.5rem;
}

pre {
    font-size: 0.875rem;
    line-height: 1.4;
}
</style>

<?php require_once APPROOT . '/views/partials/footer.php'; ?> 