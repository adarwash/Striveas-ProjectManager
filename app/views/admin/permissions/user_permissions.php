<?php flash('permissions_message'); ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-shield"></i> 
                    Manage Permissions for <?= htmlspecialchars($data['user']['username'] ?? $data['user']['full_name'] ?? 'User') ?>
                </h6>
                <div class="dropdown no-arrow">
                    <a class="btn btn-outline-primary btn-sm" href="/permissions/user_permissions">
                        <i class="fas fa-arrow-left"></i> Back to User List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- User Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-left-info">
                            <div class="card-body py-2">
                                <h6 class="text-info mb-1">User Details</h6>
                                <p class="mb-1"><strong>Username:</strong> <?= htmlspecialchars($data['user']['username']) ?></p>
                                <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($data['user']['email']) ?></p>
                                <p class="mb-0"><strong>Full Name:</strong> <?= htmlspecialchars($data['user']['full_name'] ?? 'Not set') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-left-warning">
                            <div class="card-body py-2">
                                <h6 class="text-warning mb-1">Role Information</h6>
                                <?php if ($data['userRole']): ?>
                                    <p class="mb-1"><strong>Role:</strong> <?= htmlspecialchars($data['userRole']['display_name']) ?></p>
                                    <p class="mb-0"><strong>Description:</strong> <?= htmlspecialchars($data['userRole']['description']) ?></p>
                                <?php else: ?>
                                    <p class="mb-0 text-muted">No role assigned</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permission Management Form -->
                <form action="/permissions/user_permissions/<?= $data['user']['id'] ?>" method="POST">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> Individual user permissions override role permissions. 
                        Permissions highlighted in <span class="badge badge-warning">yellow</span> are inherited from the user's role.
                    </div>

                    <?php if (!empty($data['allPermissions'])): ?>
                        <?php foreach ($data['allPermissions'] as $module => $permissions): ?>
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-capitalize">
                                        <i class="fas fa-cog"></i> 
                                        <?= ucfirst(str_replace('_', ' ', $module)) ?> Module
                                        <span class="badge badge-secondary ml-2"><?= count($permissions) ?> permissions</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($permissions as $permission): ?>
                                            <?php 
                                                $isDirectlyAssigned = in_array($permission['id'], $data['userPermissionIds']);
                                                $isFromRole = in_array($permission['id'], $data['rolePermissionIds']);
                                            ?>
                                            <div class="col-md-6 col-lg-4 mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" 
                                                           class="custom-control-input" 
                                                           id="permission_<?= $permission['id'] ?>" 
                                                           name="permissions[]" 
                                                           value="<?= $permission['id'] ?>"
                                                           <?= $isDirectlyAssigned ? 'checked' : '' ?>>
                                                    <label class="custom-control-label <?= $isFromRole && !$isDirectlyAssigned ? 'text-warning' : '' ?>" 
                                                           for="permission_<?= $permission['id'] ?>">
                                                        <?= htmlspecialchars($permission['display_name']) ?>
                                                        <?php if ($isFromRole && !$isDirectlyAssigned): ?>
                                                            <small class="badge badge-warning ml-1">from role</small>
                                                        <?php endif; ?>
                                                        <?php if ($isDirectlyAssigned): ?>
                                                            <small class="badge badge-success ml-1">direct</small>
                                                        <?php endif; ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            No permissions found. Please set up the permission system first.
                        </div>
                    <?php endif; ?>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update User Permissions
                        </button>
                        <a href="/permissions/user_permissions" class="btn btn-secondary ml-2">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
