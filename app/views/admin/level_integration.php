<div class="container-fluid mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h1 class="h3 mb-1">Level.io Integration</h1>
            <p class="text-muted mb-0">
                Link Level.io device groups to your CRM clients for unified automation.
                <a href="<?= htmlspecialchars($level_settings['doc_url']) ?>" target="_blank" rel="noopener">
                    View API docs
                </a>
            </p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="<?= URLROOT ?>/admin" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Admin Home
            </a>
        </div>
    </div>

    <?php flash('settings_success'); ?>
    <?php flash('settings_error'); ?>

    <div class="row mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Integration Status</h6>
                    <p class="mb-2">
                        <span class="fw-medium">Integration Enabled:</span>
                        <span class="badge <?= !empty($level_settings['enabled']) ? 'bg-success' : 'bg-secondary' ?>">
                            <?= !empty($level_settings['enabled']) ? 'Yes' : 'No' ?>
                        </span>
                    </p>
                    <p class="mb-0">
                        <span class="fw-medium">API Key Stored:</span>
                        <span class="badge <?= !empty($level_settings['has_key']) ? 'bg-success' : 'bg-secondary' ?>">
                            <?= !empty($level_settings['has_key']) ? 'Yes' : 'No' ?>
                        </span>
                    </p>
                    <hr>
                    <p class="small text-muted mb-0">
                        Configure the API key and enable the integration from
                        <a href="<?= URLROOT ?>/admin/settings">System Settings</a>.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-semibold mb-0"><i class="bi bi-diagram-3 me-2 text-primary"></i>Available Level Groups</h6>
                        <span class="badge bg-light text-dark border">
                            <?= count($level_groups ?? []) ?> loaded
                        </span>
                    </div>
                    <?php if ($level_error): ?>
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($level_error) ?>
                        </div>
                    <?php elseif (empty($level_groups)): ?>
                        <p class="text-muted mb-0">No groups returned from Level.io. Confirm your API key and permissions.</p>
                    <?php else: ?>
                        <div class="overflow-auto" style="max-height: 230px;">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($level_groups as $group): 
                                        $groupId = $group['id'] ?? $group['group_id'] ?? ($group['uuid'] ?? '');
                                        $groupName = $group['name'] ?? $group['title'] ?? $groupId;
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($groupName) ?></td>
                                            <td class="text-muted small"><?= htmlspecialchars($groupId) ?></td>
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

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="bi bi-link-45deg me-2 text-primary"></i>Link Level Group to Client</h5>
        </div>
        <div class="card-body">
            <?php if (!$level_error && !empty($level_groups) && !empty($level_settings['enabled'])): ?>
                <form action="<?= URLROOT ?>/admin/linkLevelGroup" method="POST" class="row gy-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-medium" for="client_id">Client</label>
                        <select class="form-select" id="client_id" name="client_id" required>
                            <option value="" disabled selected>Select client</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>">
                                    <?= htmlspecialchars($client['name']) ?>
                                    <?= !empty($client['level_io_group_name']) ? ' • currently: ' . htmlspecialchars($client['level_io_group_name']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-medium" for="levelGroupSelect">Level Group</label>
                        <select class="form-select" id="levelGroupSelect" name="group_id" required>
                            <option value="" disabled selected>Select group</option>
                            <?php foreach ($level_groups as $group):
                                $groupId = $group['id'] ?? $group['group_id'] ?? ($group['uuid'] ?? '');
                                $groupName = $group['name'] ?? $group['title'] ?? $groupId;
                            ?>
                                <option value="<?= htmlspecialchars($groupId) ?>" data-group-name="<?= htmlspecialchars($groupName) ?>">
                                    <?= htmlspecialchars($groupName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="group_name" id="levelGroupName">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Link
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    Configure the Level.io integration and ensure groups load before linking.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Client Group Mappings</h5>
            <span class="badge bg-light text-dark border"><?= count($clients ?? []) ?> clients</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Client</th>
                            <th>Linked Group</th>
                            <th>Group ID</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-semibold"><?= htmlspecialchars($client['name']) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($client_groups_map[$client['id']])): ?>
                                        <?php foreach ($client_groups_map[$client['id']] as $link): ?>
                                            <div class="d-flex align-items-center justify-content-between bg-light rounded px-3 py-2 mb-2">
                                                <div>
                                                    <div class="fw-medium mb-0"><?= htmlspecialchars($link['level_group_name'] ?? $link['level_group_id']) ?></div>
                                                    <div class="text-muted small mb-0"><?= htmlspecialchars($link['level_group_id']) ?></div>
                                                </div>
                                                <form action="<?= URLROOT ?>/admin/unlinkLevelGroup" method="POST" class="ms-2">
                                                    <input type="hidden" name="client_id" value="<?= $client['id'] ?>">
                                                    <input type="hidden" name="group_id" value="<?= htmlspecialchars($link['level_group_id']) ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove link">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not linked</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const groupSelect = document.getElementById('levelGroupSelect');
    const groupNameInput = document.getElementById('levelGroupName');

    if (groupSelect && groupNameInput) {
        const syncGroupName = () => {
            const option = groupSelect.options[groupSelect.selectedIndex];
            groupNameInput.value = option ? (option.getAttribute('data-group-name') || option.text) : '';
        };
        groupSelect.addEventListener('change', syncGroupName);
        syncGroupName();
    }
});
</script>

