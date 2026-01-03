<?php
$ticket = $data['ticket'] ?? [];
$canEdit = !empty($data['can_edit']);
$hasClient = !empty($ticket['client_id']);
$requesterEmail = (string)($ticket['inbound_email_address'] ?? '');
$suggested = $data['suggested_client'] ?? null;

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

if (!$canEdit || $hasClient || $requesterEmail === '') {
    return;
}
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="card-title mb-0">
            <i class="bi bi-building-exclamation me-2"></i>Client not linked
        </h6>
    </div>
    <div class="card-body">
        <div class="small text-muted mb-2">This ticket came from:</div>
        <div class="fw-semibold mb-3">
            <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($requesterEmail) ?>
        </div>
        <?php if (!empty($suggested) && !empty($suggested['id'])): ?>
            <div class="alert alert-light border small mb-3">
                <div class="fw-semibold mb-1">Suggested match</div>
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="text-truncate">
                        <i class="bi bi-building me-1"></i>
                        <?= htmlspecialchars($suggested['name'] ?? ('Client #' . (int)$suggested['id'])) ?>
                        <?php if (!empty($suggested['email'])): ?>
                            <span class="text-muted">— <?= htmlspecialchars($suggested['email']) ?></span>
                        <?php endif; ?>
                    </div>
                    <form method="POST" action="<?= URLROOT ?>/tickets/linkClient/<?= (int)$ticket['id'] ?>" class="m-0">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="client_action" value="link">
                        <input type="hidden" name="existing_client_id" value="<?= (int)$suggested['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-link-45deg me-1"></i>Link
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
        <button type="button" class="btn btn-sm btn-primary w-100" data-bs-toggle="modal" data-bs-target="#linkClientModal">
            <i class="bi bi-link-45deg me-2"></i>Link / Create Client
        </button>
        <div class="form-text mt-2">We do not auto-create clients from email anymore.</div>
    </div>
</div>

