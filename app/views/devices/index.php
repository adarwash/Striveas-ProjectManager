<div class="container-fluid">
    <div class="rounded-3 p-4 mb-4 bg-light border">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Level.io Devices</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h3 mb-1 text-dark">
                    <i class="bi bi-pc-display-horizontal text-primary me-2"></i>
                    Level.io Devices
                </h1>
                <p class="text-muted mb-0">Unified view of every device available via the Level.io integration.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="/devices" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </a>
                <a href="/clients" class="btn btn-outline-secondary">
                    <i class="bi bi-people"></i> Clients
                </a>
            </div>
        </div>
    </div>

    <?php flash('devices_success'); ?>
    <?php flash('devices_error'); ?>

    <?php if (!$level_enabled): ?>
        <div class="alert alert-warning d-flex align-items-start">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Level.io integration is disabled</h5>
                <p class="mb-2">
                    Enable the integration and provide an API key under <strong>Admin → Level.io Integration</strong>
                    to view devices globally.
                </p>
                <?php if (hasPermission('admin.system_settings')): ?>
                    <a href="/admin/levelIntegration" class="btn btn-sm btn-primary">
                        <i class="bi bi-gear-wide-connected me-1"></i>Open Level.io Settings
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <strong>Unable to load devices:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php elseif (empty($devices)): ?>
            <div class="alert alert-info">
                <strong>No devices returned.</strong> Once devices are available in Level.io they will appear here automatically.
            </div>
        <?php endif; ?>

        <?php
        $totalDevices = count($devices);
        $onlineDevices = 0;
        $offlineDevices = 0;

        foreach ($devices as $device) {
            $status = $device['online'] ?? $device['is_online'] ?? $device['status'] ?? null;
            $isOnline = ($status === true || $status === 1 || strtolower((string)$status) === 'online');
            if ($isOnline) {
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

        <?php if ($hit_page_cap): ?>
            <div class="alert alert-warning mb-4">
                <i class="bi bi-info-circle-fill me-2"></i>
                Displaying the first <?= (int)$pages_fetched ?> pages (cap of <?= (int)$max_pages ?>). Add <code>?max_pages=50</code> to the URL to raise the limit if needed.
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h5 class="card-title mb-0">
                    <i class="bi bi-hdd-stack text-primary me-2"></i>All Devices
                </h5>
                <div class="d-flex gap-2 flex-wrap">
                    <input type="text" id="deviceSearch" class="form-control form-control-sm" placeholder="Search devices..." style="width: 220px;">
                    <select id="statusFilter" class="form-select form-select-sm" style="width: 140px;">
                        <option value="">All Status</option>
                        <option value="online">Online</option>
                        <option value="offline">Offline</option>
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($devices)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="devicesTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Status</th>
                                    <th>Device</th>
                                    <th>Hostname</th>
                                    <th>Group</th>
                                    <th>Role</th>
                                    <th>CPU</th>
                                    <th>Operating System</th>
                                    <th>Public IP</th>
                                    <th>Location</th>
                                    <th class="pe-4">Last Seen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($devices as $device): ?>
                                    <?php
                                    $status = $device['online'] ?? $device['is_online'] ?? $device['status'] ?? null;
                                    $isOnline = ($status === true || $status === 1 || strtolower((string)$status) === 'online');
                                    $statusClass = $isOnline ? 'success' : 'danger';
                                    $statusLabel = $isOnline ? 'Online' : 'Offline';

                                    $deviceName = $device['nickname'] ?? $device['name'] ?? $device['hostname'] ?? 'Unnamed Device';
                                    $deviceId = $device['id'] ?? $device['device_id'] ?? null;
                                    $hostname = $device['hostname'] ?? '—';
                                    $groupName = $device['group_name'] ?? '—';
                                    $role = $device['role'] ?? '—';
                                    $cpuModel = $device['cpu_model'] ?? ($device['cpu'] ?? ($device['processor'] ?? '—'));
                                    $os = $device['full_operating_system'] ?? ($device['operating_system'] ?? ($device['os'] ?? $device['platform'] ?? '—'));
                                    $publicIp = $device['public_ip_address'] ?? $device['ip_address'] ?? '—';
                                    $city = trim((string)($device['city'] ?? ''));
                                    $country = trim((string)($device['country'] ?? ''));
                                    $location = trim($city . (empty($city) || empty($country) ? '' : ', ') . $country);
                                    if ($location === '') {
                                        $location = '—';
                                    }
                                    $lastSeen = $device['last_seen'] ?? $device['last_seen_at'] ?? $device['updated_at'] ?? $device['last_reboot_time'] ?? null;
                                    $lastSeenFormatted = $lastSeen ? date('M j, Y g:i A', strtotime($lastSeen)) : '—';
                                    ?>
                                    <tr class="device-row" data-status="<?= $isOnline ? 'online' : 'offline' ?>">
                                        <td class="ps-4">
                                            <span class="badge bg-<?= $statusClass ?> rounded-pill">
                                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i><?= $statusLabel ?>
                                            </span>
                                        </td>
                                        <td class="fw-semibold">
                                            <?php if ($deviceId): ?>
                                                <a href="/devices/detail/<?= rawurlencode($deviceId) ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($deviceName) ?>
                                                    <i class="bi bi-box-arrow-up-right small ms-1"></i>
                                                </a>
                                            <?php else: ?>
                                                <?= htmlspecialchars($deviceName) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($hostname) ?></td>
                                        <td>
                                            <?php if ($groupName !== '—'): ?>
                                                <span class="badge bg-light text-dark"><?= htmlspecialchars($groupName) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($role !== '—'): ?>
                                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $role))) ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($cpuModel !== '—'): ?>
                                                <?= htmlspecialchars($cpuModel) ?>
                                                <?php if (!empty($device['cpu_cores'])): ?>
                                                    <small class="text-muted d-block"><?= (int)$device['cpu_cores'] ?> cores</small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($os !== '—'): ?>
                                                <i class="bi bi-laptop me-1"></i><?= htmlspecialchars($os) ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($publicIp !== '—'): ?>
                                                <code><?= htmlspecialchars($publicIp) ?></code>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($location !== '—'): ?>
                                                <?= htmlspecialchars($location) ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4">
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
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('deviceSearch');
    const statusFilter = document.getElementById('statusFilter');
    const rows = document.querySelectorAll('.device-row');

    function filterDevices() {
        const term = searchInput.value.toLowerCase();
        const status = statusFilter.value;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matchesSearch = text.includes(term);
            const matchesStatus = status === '' || row.dataset.status === status;
            row.style.display = matchesSearch && matchesStatus ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterDevices);
    statusFilter.addEventListener('change', filterDevices);
});
</script>


