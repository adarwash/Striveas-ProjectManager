<?php
// Reusable pinnable card: "My Open Tickets"

if (!isset($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(8));
    }
}

$cardId = isset($cardId) ? (string)$cardId : 'tickets.my_open';
$widgetId = isset($widgetId) ? (string)$widgetId : ('card:' . $cardId);
$isPinned = !empty($isPinned);
$context = isset($context) ? (string)$context : 'module';
$cardTitle = isset($cardTitle) && $cardTitle !== '' ? (string)$cardTitle : 'My Open Tickets';
$tickets = isset($tickets) && is_array($tickets) ? $tickets : [];

// Safe link to full ticket list (open + assigned)
$assignedTo = (int)($_SESSION['user_id'] ?? 0);
$viewAllHref = '/tickets?assigned_to=' . $assignedTo . '&closed=0';
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="bi bi-ticket-perforated me-2"></i><?= htmlspecialchars($cardTitle) ?>
        </h5>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= htmlspecialchars($viewAllHref) ?>" class="btn btn-sm btn-outline-primary">
                View All
            </a>
            <button
                type="button"
                class="btn btn-sm <?= $isPinned ? 'btn-primary' : 'btn-outline-secondary' ?> dashboard-pin-toggle"
                data-card-id="<?= htmlspecialchars($cardId) ?>"
                data-widget-id="<?= htmlspecialchars($widgetId) ?>"
                data-csrf-token="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                aria-pressed="<?= $isPinned ? 'true' : 'false' ?>"
                title="<?= $isPinned ? 'Unpin from dashboard' : 'Pin to dashboard' ?>"
            >
                <i class="bi <?= $isPinned ? 'bi-pin-fill' : 'bi-pin-angle' ?>"></i>
                <span class="d-none d-md-inline ms-1"><?= $isPinned ? 'Pinned' : 'Pin' ?></span>
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($tickets)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-check-circle fs-2 text-success mb-2"></i>
                <div>No open tickets assigned to you.</div>
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($tickets as $t): ?>
                    <?php
                        $ticketId = (int)($t['id'] ?? 0);
                        $ticketNumber = (string)($t['ticket_number'] ?? '');
                        $subject = (string)($t['subject'] ?? 'Ticket');
                        $priority = (string)($t['priority_display'] ?? ($t['priority_name'] ?? ''));
                        $priorityLevel = (int)($t['priority_level'] ?? 0);
                        $statusName = (string)($t['status_name'] ?? '');

                        $priBadge = 'bg-secondary';
                        if ($priorityLevel >= 4) $priBadge = 'bg-danger';
                        elseif ($priorityLevel === 3) $priBadge = 'bg-warning text-dark';
                        elseif ($priorityLevel === 2) $priBadge = 'bg-info';
                        elseif ($priorityLevel === 1) $priBadge = 'bg-success';

                        $href = $ticketId ? ('/tickets/show/' . $ticketId) : '#';
                    ?>
                    <a href="<?= htmlspecialchars($href) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                        <div class="me-3">
                            <div class="fw-semibold">
                                <?= $ticketNumber !== '' ? htmlspecialchars($ticketNumber) . ' · ' : '' ?>
                                <?= htmlspecialchars($subject) ?>
                            </div>
                            <?php if ($statusName !== ''): ?>
                                <div class="small text-muted"><?= htmlspecialchars($statusName) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="text-end">
                            <?php if ($priority !== ''): ?>
                                <span class="badge <?= $priBadge ?>"><?= htmlspecialchars($priority) ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>


