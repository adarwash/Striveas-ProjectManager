<?php
    $reports = is_array($reports ?? null) ? $reports : [];
    $datasetLabels = is_array($dataset_labels ?? null) ? $dataset_labels : [];
    $isAdmin = !empty($is_admin);
    $csrf = (string)($csrf_token ?? ($_SESSION['csrf_token'] ?? ''));
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="bi bi-folder2-open text-primary"></i> Saved Reports
            </h1>
            <p class="text-muted mb-0">Run and export reports you have access to.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?php if ($isAdmin): ?>
                <a href="/reports/builder" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>New Report
                </a>
            <?php endif; ?>
            <a href="/dashboard" class="btn btn-outline-secondary">
                <i class="bi bi-house me-1"></i>Dashboard
            </a>
        </div>
    </div>

    <div class="alert d-none" id="savedReportsAlert"></div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-list-ul me-2 text-info"></i>Available Reports</h6>
            <div class="small text-muted"><?= count($reports) ?> total</div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($reports)): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                    No saved reports available.
                    <?php if ($isAdmin): ?>
                        <div class="mt-3">
                            <a href="/reports/builder" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>Create your first report
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 220px;">Name</th>
                                <th>Dataset</th>
                                <th>Visibility</th>
                                <th class="text-muted">Updated</th>
                                <th class="text-end" style="width: 260px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $r): ?>
                                <?php
                                    $rid = (int)($r['id'] ?? 0);
                                    $name = (string)($r['name'] ?? '');
                                    $dataset = (string)($r['dataset'] ?? '');
                                    $datasetLabel = $datasetLabels[$dataset] ?? $dataset;
                                    $visibility = strtolower((string)($r['visibility'] ?? 'admin'));
                                    $visLabel = $visibility === 'roles' ? 'Roles' : 'Admin only';
                                    $updatedAt = $r['updated_at'] ?? $r['created_at'] ?? null;
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($name) ?></div>
                                        <?php if (!empty($r['description'])): ?>
                                            <div class="small text-muted text-truncate" style="max-width: 420px;">
                                                <?= htmlspecialchars((string)$r['description']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars((string)$datasetLabel) ?></td>
                                    <td>
                                        <span class="badge <?= $visibility === 'roles' ? 'bg-info' : 'bg-secondary' ?>">
                                            <?= htmlspecialchars($visLabel) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        <?= !empty($updatedAt) ? htmlspecialchars(date('M j, Y H:i', strtotime((string)$updatedAt))) : '—' ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="/reports/run/<?= $rid ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-play-fill me-1"></i>Run
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-success btn-export-report" data-report-id="<?= $rid ?>">
                                                <i class="bi bi-download me-1"></i>CSV
                                            </button>
                                            <?php if ($isAdmin): ?>
                                                <a href="/reports/builder/<?= $rid ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-pencil me-1"></i>Edit
                                                </a>
                                            <?php endif; ?>
                                        </div>
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
(() => {
    const csrfToken = <?= json_encode($csrf) ?>;
    const alertEl = document.getElementById('savedReportsAlert');
    function showAlert(type, msg) {
        if (!alertEl) return;
        alertEl.className = 'alert alert-' + type;
        alertEl.textContent = msg;
        alertEl.classList.remove('d-none');
        setTimeout(() => alertEl.classList.add('d-none'), 4000);
    }

    async function exportReportCsv(reportId) {
        try {
            const res = await fetch('/reports/exportDynamicCsv', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    report_id: reportId
                })
            });
            if (!res.ok) {
                throw new Error('Export failed');
            }
            const blob = await res.blob();
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'report_' + reportId + '_' + new Date().toISOString().slice(0,10) + '.csv';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        } catch (e) {
            showAlert('danger', e.message || 'Export failed');
        }
    }

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-export-report');
        if (!btn) return;
        const rid = parseInt(btn.getAttribute('data-report-id') || '0', 10);
        if (!rid) return;
        exportReportCsv(rid);
    });
})();
</script>

