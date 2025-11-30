<?php
$hasClientContext = isset($client) && is_array($client) && !empty($client);
$backUrl = $hasClientContext
    ? '/clients/levelDevices/' . (int)$client['id']
    : ($back_url ?? '/devices');

$deviceId = $device['id'] ?? $device['device_id'] ?? 'Unknown ID';
$deviceName = $device['name'] ?? $device['display_name'] ?? $device['hostname'] ?? ('Device ' . $deviceId);
$hostname = $device['hostname'] ?? $device['name'] ?? '—';
$serial = $device['serial'] ?? $device['serial_number'] ?? '—';
$os = $device['full_operating_system'] ?? ($device['operating_system'] ?? ($device['os'] ?? $device['platform'] ?? '—'));
$publicIp = $device['public_ip'] ?? $device['public_ip_address'] ?? ($device['wan_ip'] ?? ($device['external_ip'] ?? null));
$privateIps = [];
if (!empty($device['private_ip_addresses']) && is_array($device['private_ip_addresses'])) {
    $privateIps = $device['private_ip_addresses'];
} elseif (!empty($device['local_ip_addresses']) && is_array($device['local_ip_addresses'])) {
    $privateIps = $device['local_ip_addresses'];
} elseif (!empty($device['ip_address'])) {
    $privateIps[] = $device['ip_address'];
} elseif (!empty($device['local_ip'])) {
    $privateIps[] = $device['local_ip'];
}
$localIp = $privateIps[0] ?? null;
$macAddress = $device['mac_address'] ?? $device['mac'] ?? '—';
$model = $device['model'] ?? $device['hardware_model'] ?? '—';
$manufacturer = $device['manufacturer'] ?? $device['vendor'] ?? '—';
$cpu = $device['cpu'] ?? $device['processor'] ?? '—';
$memory = $device['memory'] ?? $device['ram'] ?? null;
$storage = $device['storage'] ?? $device['disk'] ?? null;
$lastSeen = $device['last_seen'] ?? $device['last_seen_at'] ?? $device['updated_at'] ?? null;
$lastSeenFormatted = $lastSeen ? date('M j, Y g:i A', strtotime($lastSeen)) : '—';
$statusRaw = $device['online'] ?? $device['is_online'] ?? $device['status'] ?? null;
$isOnline = ($statusRaw === true || $statusRaw === 1 || strtolower((string)$statusRaw) === 'online');
$statusClass = $isOnline ? 'success' : 'danger';
$statusText = $isOnline ? 'Online' : 'Offline';
$securityScore = null;
$rawScore = $device['security_score'] ?? ($device['security']['score'] ?? null);
if ($rawScore !== null && $rawScore !== '') {
    $parsedScore = (float)$rawScore;
    if ($parsedScore > 0 && $parsedScore <= 1) {
        $parsedScore *= 100;
    }
    $securityScore = max(0, min(100, $parsedScore));
}
$securityStatus = $device['security_status'] ?? ($device['security']['status'] ?? null);
$securityColor = '#22c55e';
if ($securityScore !== null) {
    if ($securityScore < 50) {
        $securityColor = '#ef4444';
    } elseif ($securityScore < 80) {
        $securityColor = '#f97316';
    }
}
$tags = $device['tags'] ?? [];
if (is_string($tags)) {
    $decodedTags = json_decode($tags, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $tags = $decodedTags;
    } else {
        $tags = array_filter(array_map('trim', explode(',', $tags)));
    }
}
?>
<!-- Styles moved to /public/css/app.css -->

<div class="container-fluid">
    <div class="rounded-3 p-4 mb-4 bg-light border">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
                <?php if ($hasClientContext): ?>
                    <li class="breadcrumb-item"><a href="/clients" class="text-decoration-none">Clients</a></li>
                    <li class="breadcrumb-item"><a href="/clients/viewClient/<?= (int)$client['id'] ?>" class="text-decoration-none"><?= htmlspecialchars($client['name']) ?></a></li>
                    <li class="breadcrumb-item"><a href="/clients/levelDevices/<?= (int)$client['id'] ?>" class="text-decoration-none">Level.io Devices</a></li>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="/devices" class="text-decoration-none">Level.io Devices</a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($deviceName) ?></li>
            </ol>
        </nav>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="mb-3 mb-md-0">
                <h1 class="h3 mb-1">
                    <i class="bi bi-pc-display text-primary me-2"></i><?= htmlspecialchars($deviceName) ?>
                </h1>
                <p class="text-muted mb-0">
                    Device ID: <code><?= htmlspecialchars($deviceId) ?></code>
                    <?php if (!empty($group)): ?>
                        · Linked Group: <strong><?= htmlspecialchars($group['level_group_name'] ?? $group['level_group_id']) ?></strong>
                    <?php endif; ?>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Devices
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($alerts_error)): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($alerts_error) ?>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle text-primary me-2"></i>Device Summary
                    </h5>
                    <span class="badge bg-<?= $statusClass ?> px-3 py-2">
                        <i class="bi bi-circle-fill me-1" style="font-size:0.6rem"></i><?= $statusText ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted text-uppercase fw-semibold">Hostname</small>
                                <div class="fw-bold"><?= htmlspecialchars($hostname) ?></div>
                                <small class="text-muted">Last seen: <?= $lastSeenFormatted ?></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted text-uppercase fw-semibold">Operating System</small>
                                <div class="fw-bold"><?= htmlspecialchars($os) ?></div>
                                <?php if (!empty($device['os_version'])): ?>
                                    <small class="text-muted">Version: <?= htmlspecialchars($device['os_version']) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted text-uppercase fw-semibold">Network Addresses</small>
                                <div class="fw-bold">
                                    <span class="text-muted small d-block">Private</span>
                                    <?php if (!empty($privateIps)): ?>
                                        <div class="d-flex flex-column gap-1">
                                            <?php foreach ($privateIps as $ip): ?>
                                                <code><?= htmlspecialchars($ip) ?></code>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </div>
                                <div class="fw-bold mt-2">
                                    <span class="text-muted small d-block">Public</span>
                                    <?= $publicIp ? '<code>' . htmlspecialchars($publicIp) . '</code>' : '<span class="text-muted">—</span>' ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted text-uppercase fw-semibold">MAC Address</small>
                                <div class="fw-bold"><?= $macAddress !== '—' ? '<code>' . htmlspecialchars($macAddress) . '</code>' : '<span class="text-muted">—</span>' ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-cpu text-primary me-2"></i>Hardware Specs
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted text-uppercase fw-semibold">Manufacturer</small>
                                <div class="fw-bold"><?= htmlspecialchars($manufacturer) ?></div>
                                <small class="text-muted">Model: <?= htmlspecialchars($model) ?></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted text-uppercase fw-semibold">Serial Number</small>
                                <div class="fw-bold"><?= $serial !== '—' ? htmlspecialchars($serial) : '<span class="text-muted">—</span>' ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted text-uppercase fw-semibold">CPU</small>
                                <div class="fw-bold"><?= htmlspecialchars($cpu) ?></div>
                                <?php if (!empty($device['cpu_cores'])): ?>
                                    <small class="text-muted"><?= (int)$device['cpu_cores'] ?> cores</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted text-uppercase fw-semibold">Memory</small>
                                <div class="fw-bold">
                                    <?php if ($memory): ?>
                                        <?= is_numeric($memory) ? number_format((float)$memory, 2) . ' GB' : htmlspecialchars($memory) ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($storage): ?>
                                    <small class="text-muted">Storage: <?= is_numeric($storage) ? number_format((float)$storage, 2) . ' GB' : htmlspecialchars($storage) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($tags)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-tags text-primary me-2"></i>Tags
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ((array)$tags as $tag): ?>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars(is_array($tag) ? ($tag['name'] ?? json_encode($tag)) : $tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <?php if ($securityScore !== null): ?>
            <div class="card border-0 shadow-sm mb-4 security-score-card">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shield-lock text-primary me-2"></i>Security Score
                    </h5>
                </div>
                <div class="card-body text-center">
                    <?php $gaugeAngle = $securityScore * 3.6; ?>
                    <div class="security-gauge mb-3" style="--gauge-color: <?= $securityColor ?>; --gauge-angle: <?= $gaugeAngle ?>deg;">
                        <div class="security-gauge-value"><?= (int)round($securityScore) ?>%</div>
                    </div>
                    <?php if ($securityStatus): ?>
                        <p class="fw-semibold mb-1"><?= htmlspecialchars($securityStatus) ?></p>
                    <?php endif; ?>
                    <p class="text-muted small mb-0">Higher values indicate a more secure device.</p>
                </div>
            </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bell text-warning me-2"></i>Active Alerts
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($alerts)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-shield-check text-success display-5"></i>
                            <p class="text-muted mt-2 mb-0">No active alerts for this device.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($alerts as $alert): ?>
                                <?php
                                $severity = strtolower($alert['severity'] ?? $alert['priority'] ?? 'info');
                                $severityBadge = match ($severity) {
                                    'critical', 'high' => 'danger',
                                    'medium', 'warning' => 'warning',
                                    'low' => 'secondary',
                                    default => 'info'
                                };
                                $alertTitle = $alert['title'] ?? $alert['name'] ?? 'Alert';
                                $alertTime = $alert['created_at'] ?? $alert['triggered_at'] ?? $alert['updated_at'] ?? null;
                                $alertTimeFormatted = $alertTime ? date('M j, Y g:i A', strtotime($alertTime)) : null;
                                ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-1"><?= htmlspecialchars($alertTitle) ?></h6>
                                        <span class="badge bg-<?= $severityBadge ?> text-uppercase"><?= htmlspecialchars($severity) ?></span>
                                    </div>
                                    <?php if (!empty($alert['description'])): ?>
                                        <p class="small text-muted mb-1"><?= htmlspecialchars($alert['description']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($alertTimeFormatted): ?>
                                        <small class="text-muted">Updated <?= $alertTimeFormatted ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear text-secondary me-2"></i>Raw Payload
                    </h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow: auto;">
                    <pre class="bg-light border rounded p-3 mb-0"><code><?= htmlspecialchars(json_encode($device, JSON_PRETTY_PRINT)) ?></code></pre>
                </div>
            </div>
        </div>
    </div>
</div>

