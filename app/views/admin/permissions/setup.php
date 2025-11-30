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

    <!-- Role Migration Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-people-fill"></i> Role Migration
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        <strong>Migrate users from old role system to enhanced permission system.</strong><br>
                        This will update users who are using string-based roles (admin, user) to use the new ID-based role system.
                    </p>
                    
                    <div id="migration-status" class="mb-3">
                        <!-- Migration status will be loaded here -->
                    </div>
                    
                    <form action="<?= URLROOT ?>/permissions/setup" method="post" class="mb-3">
                        <input type="hidden" name="action" value="migrate_users">
                        <div class="d-flex gap-2">
                            <button type="button" id="check-migration-btn" class="btn btn-info">
                                <i class="bi bi-search"></i> Check Users Needing Migration
                            </button>
                            <button type="submit" id="migrate-users-btn" class="btn btn-warning" disabled>
                                <i class="bi bi-arrow-right-circle"></i> Migrate Users to New System
                            </button>
                        </div>
                    </form>
                    
                    <div class="alert alert-info mb-0">
                        <small>
                            <strong>Note:</strong> This migration is safe and can be run multiple times. 
                            Users will keep their current permissions but will be able to use enhanced features.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkMigrationBtn = document.getElementById('check-migration-btn');
    const migrateUsersBtn = document.getElementById('migrate-users-btn');
    const migrationStatus = document.getElementById('migration-status');
    
    if (checkMigrationBtn) {
        checkMigrationBtn.addEventListener('click', function() {
            checkUsersNeedingMigration();
        });
    }
    
    function checkUsersNeedingMigration() {
        checkMigrationBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Checking...';
        checkMigrationBtn.disabled = true;
        
        fetch('<?= URLROOT ?>/permissions/checkMigration', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMigrationStatus(data.users);
            } else {
                migrationStatus.innerHTML = '<div class="alert alert-danger">Error checking migration status: ' + (data.message || 'Unknown error') + '</div>';
            }
        })
        .catch(error => {
            console.error('Migration check error:', error);
            migrationStatus.innerHTML = '<div class="alert alert-danger">Error checking migration status.</div>';
        })
        .finally(() => {
            checkMigrationBtn.innerHTML = '<i class="bi bi-search"></i> Check Users Needing Migration';
            checkMigrationBtn.disabled = false;
        });
    }
    
    function displayMigrationStatus(users) {
        if (users.length === 0) {
            migrationStatus.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle"></i> All users are already using the enhanced permission system!</div>';
            migrateUsersBtn.disabled = true;
        } else {
            let html = '<div class="alert alert-warning">';
            html += '<h6><i class="bi bi-exclamation-triangle"></i> Users needing migration (' + users.length + '):</h6>';
            html += '<div class="row">';
            
            users.forEach((user, index) => {
                if (index > 0 && index % 3 === 0) html += '</div><div class="row">';
                html += '<div class="col-md-4"><small><strong>' + user.username + '</strong> (' + user.role + ')</small></div>';
            });
            
            html += '</div></div>';
            migrationStatus.innerHTML = html;
            migrateUsersBtn.disabled = false;
        }
    }
    
    // Auto-check migration status on page load
    checkUsersNeedingMigration();
});
</script> 