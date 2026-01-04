<?php
// Reusable pinnable card: "All Clients" (list)

if (!isset($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(8));
    }
}

$cardId = isset($cardId) ? (string)$cardId : 'clients.all';
$widgetId = isset($widgetId) ? (string)$widgetId : ('card:' . $cardId);
$isPinned = !empty($isPinned);
$context = isset($context) ? (string)$context : 'module';
$cardTitle = isset($cardTitle) && $cardTitle !== '' ? (string)$cardTitle : 'All Clients';
$clients = isset($clients) && is_array($clients) ? $clients : [];
$limit = isset($limit) ? (int)$limit : (isset($clients) ? count($clients) : 0);

$idSuffix = preg_replace('/[^A-Za-z0-9_]/', '_', $widgetId);
if ($idSuffix === '') {
    $idSuffix = 'clients_all';
}
$searchId = 'clientSearch_' . $idSuffix;
$tableId = 'clientsTable_' . $idSuffix;
$titleId = 'clientCardTitle_' . $idSuffix;

$viewAllHref = '/clients';
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <div>
                <h5 class="card-title mb-0" id="<?= htmlspecialchars($titleId) ?>">
                    <i class="bi bi-building me-2"></i><?= htmlspecialchars($cardTitle) ?>
                </h5>
                <p class="text-muted mb-0 small">Quick access to your clients</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <div class="input-group input-group-sm" style="width: 220px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" placeholder="Search clients..." id="<?= htmlspecialchars($searchId) ?>">
                </div>
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
    </div>
    <div class="card-body p-0">
        <?php if (empty($clients)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-building fs-1 mb-2" style="opacity:0.5;"></i>
                <div>No clients found.</div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="<?= htmlspecialchars($tableId) ?>">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3 border-0">Client Name</th>
                            <th class="border-0">Contact Person</th>
                            <th class="border-0">Industry</th>
                            <th class="border-0">Status</th>
                            <th class="border-0">Sites</th>
                            <th class="text-end pe-3 border-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $c): ?>
                            <?php
                                $cid = (int)($c['id'] ?? 0);
                                $name = (string)($c['name'] ?? 'Client');
                                $contact = (string)($c['contact_person'] ?? '');
                                $email = (string)($c['email'] ?? '');
                                $phone = (string)($c['phone'] ?? '');
                                $industry = (string)($c['industry'] ?? '');
                                $status = (string)($c['status'] ?? '');

                                $statusClass = 'bg-secondary';
                                $statusIcon = 'bi-dash-circle';
                                if ($status === 'Active') { $statusClass = 'bg-success'; $statusIcon = 'bi-check-circle'; }
                                elseif ($status === 'Inactive') { $statusClass = 'bg-danger'; $statusIcon = 'bi-x-circle'; }
                                elseif ($status === 'Prospect') { $statusClass = 'bg-warning text-dark'; $statusIcon = 'bi-clock'; }

                                $industryClass = 'bg-light text-dark';
                                $industryIcon = 'bi-building';
                                switch (strtolower($industry)) {
                                    case 'technology':
                                        $industryClass = 'bg-primary';
                                        $industryIcon = 'bi-cpu';
                                        break;
                                    case 'manufacturing':
                                        $industryClass = 'bg-secondary';
                                        $industryIcon = 'bi-gear';
                                        break;
                                    case 'healthcare':
                                        $industryClass = 'bg-success';
                                        $industryIcon = 'bi-heart-pulse';
                                        break;
                                    case 'finance':
                                        $industryClass = 'bg-warning text-dark';
                                        $industryIcon = 'bi-bank';
                                        break;
                                    case 'retail':
                                        $industryClass = 'bg-info';
                                        $industryIcon = 'bi-shop';
                                        break;
                                    default:
                                        $industryClass = 'bg-light text-dark';
                                        $industryIcon = 'bi-building';
                                }
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <div class="me-2 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:12px;">
                                            <?= htmlspecialchars(strtoupper(substr($name, 0, 2))) ?>
                                        </div>
                                        <div>
                                            <a href="/clients/viewClient/<?= $cid ?>" class="fw-semibold text-decoration-none"><?= htmlspecialchars($name) ?></a>
                                            <?php if ($email !== ''): ?>
                                                <div class="small text-muted"><?= htmlspecialchars($email) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($contact !== ''): ?>
                                        <?= htmlspecialchars($contact) ?>
                                        <?php if ($phone !== ''): ?>
                                            <div class="small text-muted"><?= htmlspecialchars($phone) ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($industry !== ''): ?>
                                        <span class="badge <?= $industryClass ?> rounded-pill">
                                            <i class="bi <?= $industryIcon ?> me-1"></i><?= htmlspecialchars($industry) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($status !== ''): ?>
                                        <span class="badge <?= $statusClass ?> rounded-pill">
                                            <i class="bi <?= $statusIcon ?> me-1"></i><?= htmlspecialchars($status) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="text-muted">—</span></td>
                                <td class="text-end pe-3">
                                    <a href="/clients/viewClient/<?= $cid ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (function_exists('hasPermission') && hasPermission('clients.assign_sites')): ?>
                                        <a href="/clients/assignSites/<?= $cid ?>" class="btn btn-sm btn-outline-info" title="Assign Sites">
                                            <i class="bi bi-geo-alt"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (function_exists('hasPermission') && hasPermission('clients.update')): ?>
                                        <a href="/clients/edit/<?= $cid ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
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

<script>
(function(){
    const search = document.getElementById(<?= json_encode($searchId) ?>);
    const table = document.getElementById(<?= json_encode($tableId) ?>);
    const titleEl = document.getElementById(<?= json_encode($titleId) ?>);
    if (!search || !table) return;

    search.addEventListener('input', function(){
        const term = (this.value || '').toLowerCase().trim();
        const rows = table.querySelectorAll('tbody tr');
        let visible = 0;
        rows.forEach(row => {
            const text = (row.textContent || '').toLowerCase();
            const show = term === '' || text.includes(term);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (titleEl) {
            if (term) {
                titleEl.innerHTML = '<i class="bi bi-building me-2"></i>Clients (' + visible + ')';
            } else {
                titleEl.innerHTML = '<i class="bi bi-building me-2"></i>' + <?= json_encode($cardTitle) ?>;
            }
        }
    });
})();
</script>


