<?php
// Reusable pinnable card: single client summary

if (!isset($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(8));
    }
}

$cardId = isset($cardId) ? (string)$cardId : 'clients.client';
$widgetId = isset($widgetId) ? (string)$widgetId : ('card:' . $cardId);
$isPinned = !empty($isPinned);
$context = isset($context) ? (string)$context : 'dashboard';
$client = isset($client) && is_array($client) ? $client : null;

if (empty($client)) {
    ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-muted">
            <i class="bi bi-exclamation-triangle me-2"></i>Client not available (may be restricted or deleted).
        </div>
    </div>
    <?php
    return;
}

$clientId = (int)($client['id'] ?? 0);
$name = (string)($client['name'] ?? ('Client #' . $clientId));
$status = (string)($client['status'] ?? '');
$contactPerson = (string)($client['contact_person'] ?? '');
$email = (string)($client['email'] ?? '');
$phone = (string)($client['phone'] ?? '');

$statusClass = 'text-bg-secondary';
$statusIcon = 'bi-dash-circle';
if ($status === 'Active') { $statusClass = 'text-bg-success'; $statusIcon = 'bi-check-circle'; }
elseif ($status === 'Inactive') { $statusClass = 'text-bg-danger'; $statusIcon = 'bi-x-circle'; }
elseif ($status === 'Prospect') { $statusClass = 'text-bg-warning'; $statusIcon = 'bi-clock'; }

$viewHref = '/clients/viewClient/' . $clientId;
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-building text-primary"></i>
            <div class="fw-semibold">
                <a href="<?= htmlspecialchars($viewHref) ?>" class="text-decoration-none"><?= htmlspecialchars($name) ?></a>
            </div>
            <?php if ($status !== ''): ?>
                <span class="badge <?= $statusClass ?> rounded-pill ms-1">
                    <i class="bi <?= $statusIcon ?> me-1"></i><?= htmlspecialchars($status) ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= htmlspecialchars($viewHref) ?>" class="btn btn-sm btn-outline-primary" title="Open client">
                <i class="bi bi-box-arrow-up-right"></i>
            </a>
            <button
                type="button"
                class="btn btn-sm <?= $isPinned ? 'btn-primary' : 'btn-outline-secondary' ?> dashboard-pin-toggle"
                data-card-id="clients.client"
                data-client-id="<?= (int)$clientId ?>"
                data-widget-id="<?= htmlspecialchars($widgetId) ?>"
                data-csrf-token="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                aria-pressed="<?= $isPinned ? 'true' : 'false' ?>"
                title="<?= $isPinned ? 'Unpin from dashboard' : 'Pin to dashboard' ?>"
            >
                <i class="bi <?= $isPinned ? 'bi-pin-fill' : 'bi-pin-angle' ?>"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-12">
                <div class="small text-muted">Contact</div>
                <div><?= $contactPerson !== '' ? htmlspecialchars($contactPerson) : '<span class="text-muted">—</span>' ?></div>
            </div>
            <div class="col-12 col-md-6">
                <div class="small text-muted">Email</div>
                <div>
                    <?php if ($email !== ''): ?>
                        <a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="small text-muted">Phone</div>
                <div>
                    <?php if ($phone !== ''): ?>
                        <a href="tel:<?= htmlspecialchars($phone) ?>"><?= htmlspecialchars($phone) ?></a>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


