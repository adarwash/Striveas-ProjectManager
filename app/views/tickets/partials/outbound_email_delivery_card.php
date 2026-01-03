<?php
// Outbound Email Delivery (EmailQueue troubleshooting)
$rows = $data['email_queue'] ?? [];
$last = !empty($rows) ? $rows[0] : null;

$status = $last['status'] ?? '';
$statusLabel = $status ? ucfirst($status) : 'N/A';
$statusBadge = 'bg-secondary';
if ($status === 'sent') { $statusBadge = 'bg-success'; }
elseif ($status === 'pending' || $status === 'sending') { $statusBadge = 'bg-warning text-dark'; }
elseif ($status === 'failed') { $statusBadge = 'bg-danger'; }

$ticketId = (int)($data['ticket']['id'] ?? 0);

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="card-title mb-0 d-flex align-items-center justify-content-between">
            <span>
                <i class="bi bi-envelope-check me-2"></i>Outbound Email Delivery
            </span>
            <?php if (!empty($last)): ?>
                <span class="badge <?= $statusBadge ?>"><?= htmlspecialchars($statusLabel) ?></span>
            <?php endif; ?>
        </h6>
    </div>
    <div class="card-body">
        <?php if (empty($last)): ?>
            <div class="text-muted small">
                No outbound emails have been sent/queued for this ticket yet.
            </div>
        <?php else: ?>
            <div class="small">
                <div class="mb-2">
                    <span class="text-muted">To:</span>
                    <strong><?= htmlspecialchars($last['to_address'] ?? 'Unknown') ?></strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted">Subject:</span>
                    <span class="text-truncate d-inline-block" style="max-width: 260px;" title="<?= htmlspecialchars($last['subject'] ?? '') ?>">
                        <?= htmlspecialchars($last['subject'] ?? '') ?>
                    </span>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <div class="text-muted">Queued</div>
                        <div class="fw-semibold"><?= htmlspecialchars((string)($last['created_at'] ?? '')) ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">Last attempt</div>
                        <div class="fw-semibold"><?= htmlspecialchars((string)($last['last_attempt_at'] ?? '')) ?></div>
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <div class="text-muted">Sent at</div>
                        <div class="fw-semibold"><?= htmlspecialchars((string)($last['sent_at'] ?? '')) ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">Attempts</div>
                        <div class="fw-semibold">
                            <?= (int)($last['attempts'] ?? 0) ?>/<?= (int)($last['max_attempts'] ?? 0) ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($last['error_message'])): ?>
                    <div class="alert alert-danger py-2 small mb-2">
                        <strong>Error:</strong> <?= htmlspecialchars((string)$last['error_message']) ?>
                    </div>
                <?php endif; ?>

                <div class="alert alert-light border py-2 small mb-3">
                    <div class="fw-semibold mb-1">Troubleshooting tip</div>
                    If this shows <strong>Sent</strong> but the client didn’t receive it, check their Spam/Junk/Quarantine and run a Microsoft 365 Message Trace.
                </div>

                <?php if (hasPermission('tickets.comment') && $ticketId > 0): ?>
                    <form method="POST" action="<?= URLROOT ?>/tickets/resendLastUpdate/<?= $ticketId ?>" class="d-grid">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <button type="submit" class="btn btn-outline-primary btn-sm" onclick="return confirm('Resend the last ticket update email to the customer?');">
                            <i class="bi bi-arrow-repeat me-2"></i>Resend last update
                        </button>
                    </form>
                <?php endif; ?>

                <?php if (!empty($rows) && count($rows) > 1): ?>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#emailQueueHistory<?= $ticketId ?>" aria-expanded="false">
                            <i class="bi bi-list-ul me-2"></i>Show send history
                        </button>
                        <div class="collapse mt-2" id="emailQueueHistory<?= $ticketId ?>">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>To</th>
                                            <th>Queued</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $r): ?>
                                            <tr>
                                                <td><?= htmlspecialchars((string)($r['status'] ?? '')) ?></td>
                                                <td class="text-truncate" style="max-width: 160px;" title="<?= htmlspecialchars((string)($r['to_address'] ?? '')) ?>">
                                                    <?= htmlspecialchars((string)($r['to_address'] ?? '')) ?>
                                                </td>
                                                <td><?= htmlspecialchars((string)($r['created_at'] ?? '')) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

