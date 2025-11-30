<?php require VIEWSPATH . '/inc/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="rounded-3 p-4 mb-4 bg-light border">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/clients" class="text-decoration-none">Clients</a></li>
                <li class="breadcrumb-item"><a href="/clients/viewClient/<?= (int)$client['id'] ?>" class="text-decoration-none"><?= htmlspecialchars($client['name']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Level.io Devices</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 text-dark">
                    <i class="bi bi-pc-display-horizontal text-primary me-2"></i>
                    Level.io Devices
                </h1>
                <p class="text-muted mb-0">
                    Devices linked to <strong><?= htmlspecialchars($client['name']) ?></strong> via Level.io groups
                </p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
                <a href="/clients/viewClient/<?= (int)$client['id'] ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Client
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php flash('client_success'); ?>
    <?php flash('client_error'); ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-warning">
            <h6 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Some errors occurred:</h6>
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Linked Groups Info -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">
                <i class="bi bi-collection text-info me-2"></i>Linked Level.io Groups
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($linked_groups as $group): ?>
                    <span class="badge bg-info text-dark fs-6 px-3 py-2">
                        <i class="bi bi-folder me-1"></i>
                        <?= htmlspecialchars($group['level_group_name'] ?? $group['level_group_id']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Device Stats -->
    <?php
    $totalDevices = count($devices);
    $onlineDevices = 0;
    $offlineDevices = 0;
    foreach ($devices as $d) {
        $online = $d['online'] ?? $d['is_online'] ?? $d['status'] ?? null;
        if ($online === true || $online === 1 || strtolower((string)$online) === 'online') {
            $onlineDevices++;
        } else {
            $offlineDevices++;
        }
    }
    ?>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h2 class="display-5 fw-bold text-primary"><?= $totalDevices ?></h2>
                    <p class="text-muted mb-0">Total Devices</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h2 class="display-5 fw-bold text-success"><?= $onlineDevices ?></h2>
                    <p class="text-muted mb-0">Online</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h2 class="display-5 fw-bold text-danger"><?= $offlineDevices ?></h2>
                    <p class="text-muted mb-0">Offline</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Devices Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="bi bi-hdd-stack text-primary me-2"></i>All Devices
            </h5>
            <div class="d-flex gap-2">
                <input type="text" id="deviceSearch" class="form-control form-control-sm" placeholder="Search devices..." style="width: 250px;">
                <select id="statusFilter" class="form-select form-select-sm" style="width: 150px;">
                    <option value="">All Status</option>
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($devices)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <p class="text-muted mt-3">No devices found in the linked Level.io groups.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="devicesTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Status</th>
                                <th>Device Name</th>
                                <th>Hostname</th>
                                <th>IP Address</th>
                                <th>OS</th>
                                <th>Group</th>
                                <th>Last Seen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($devices as $device): ?>
                                <?php
                                $online = $device['online'] ?? $device['is_online'] ?? $device['status'] ?? null;
                                $isOnline = ($online === true || $online === 1 || strtolower((string)$online) === 'online');
                                $statusClass = $isOnline ? 'success' : 'danger';
                                $statusText = $isOnline ? 'Online' : 'Offline';
                                
                                $deviceId = $device['id'] ?? $device['device_id'] ?? null;
                                $deviceName = $device['name'] ?? $device['display_name'] ?? $device['hostname'] ?? 'Unknown';
                                $hostname = $device['hostname'] ?? $device['name'] ?? '—';
                                $ipAddress = $device['ip_address'] ?? $device['local_ip'] ?? $device['public_ip'] ?? '—';
                                $os = $device['os'] ?? $device['operating_system'] ?? $device['platform'] ?? '—';
                                $groupName = $device['_group_name'] ?? '—';
                                $lastSeen = $device['last_seen'] ?? $device['last_seen_at'] ?? $device['updated_at'] ?? null;
                                
                                if ($lastSeen) {
                                    $lastSeenFormatted = date('M j, Y g:i A', strtotime($lastSeen));
                                } else {
                                    $lastSeenFormatted = '—';
                                }
                                ?>
                                <tr class="device-row" data-status="<?= $isOnline ? 'online' : 'offline' ?>">
                                    <td class="ps-4">
                                        <span class="badge bg-<?= $statusClass ?> rounded-pill">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $detailUrl = null;
                                        if ($deviceId) {
                                            $detailUrl = '/clients/levelDevice/' . (int)$client['id'] . '/' . rawurlencode($deviceId);
                                            if (!empty($device['_group_id'])) {
                                                $detailUrl .= '?group_id=' . urlencode($device['_group_id']);
                                            }
                                        }
                                        ?>
                                        <?php if ($detailUrl): ?>
                                            <a href="<?= $detailUrl ?>" class="fw-bold text-decoration-none">
                                                <?= htmlspecialchars($deviceName) ?>
                                                <i class="bi bi-box-arrow-up-right small ms-1"></i>
                                            </a>
                                        <?php else: ?>
                                            <strong><?= htmlspecialchars($deviceName) ?></strong>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($hostname) ?></td>
                                    <td>
                                        <?php if ($ipAddress !== '—'): ?>
                                            <code><?= htmlspecialchars($ipAddress) ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($os !== '—'): ?>
                                            <?php
                                            $osIcon = 'bi-laptop';
                                            $osLower = strtolower($os);
                                            if (strpos($osLower, 'windows') !== false) {
                                                $osIcon = 'bi-windows';
                                            } elseif (strpos($osLower, 'mac') !== false || strpos($osLower, 'darwin') !== false) {
                                                $osIcon = 'bi-apple';
                                            } elseif (strpos($osLower, 'linux') !== false || strpos($osLower, 'ubuntu') !== false) {
                                                $osIcon = 'bi-ubuntu';
                                            }
                                            ?>
                                            <i class="bi <?= $osIcon ?> me-1"></i>
                                            <?= htmlspecialchars($os) ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= htmlspecialchars($groupName) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($lastSeenFormatted !== '—'): ?>
                                            <small class="text-muted"><?= $lastSeenFormatted ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('deviceSearch');
    const statusFilter = document.getElementById('statusFilter');
    const rows = document.querySelectorAll('.device-row');

    function filterDevices() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const status = row.dataset.status;

            const matchesSearch = text.includes(searchTerm);
            const matchesStatus = statusValue === '' || status === statusValue;

            row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterDevices);
    statusFilter.addEventListener('change', filterDevices);
});
</script>

<?php require VIEWSPATH . '/inc/footer.php'; ?>

