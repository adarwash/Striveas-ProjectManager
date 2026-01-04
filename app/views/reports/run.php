<?php
    $report = is_array($report ?? null) ? $report : [];
    $rid = (int)($report['id'] ?? 0);
    $name = (string)($report['name'] ?? 'Report');
    $desc = (string)($report['description'] ?? '');
    $csrf = (string)($csrf_token ?? ($_SESSION['csrf_token'] ?? ''));
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="bi bi-play-circle text-primary"></i> <?= htmlspecialchars($name) ?>
            </h1>
            <?php if ($desc !== ''): ?>
                <p class="text-muted mb-0"><?= htmlspecialchars($desc) ?></p>
            <?php else: ?>
                <p class="text-muted mb-0">Run and export this report.</p>
            <?php endif; ?>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="/reports/saved" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <button type="button" class="btn btn-outline-success" id="runExportBtn">
                <i class="bi bi-download me-1"></i>Export CSV
            </button>
        </div>
    </div>

    <div class="alert d-none" id="runAlert"></div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-table me-2 text-info"></i>Results</h6>
            <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Per page</label>
                <input type="number" class="form-control form-control-sm" id="runPerPage" value="25" min="1" max="200" style="width:90px;">
                <button type="button" class="btn btn-sm btn-primary" id="runRefreshBtn">
                    <i class="bi bi-arrow-repeat me-1"></i>Run
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="runLoading" class="text-center py-4 d-none">
                <div class="spinner-border" role="status"><span class="visually-hidden">Loading…</span></div>
            </div>
            <div id="runEmpty" class="text-center text-muted py-5">
                Click <strong>Run</strong> to load results.
            </div>
            <div class="table-responsive d-none" id="runTableWrap">
                <table class="table table-sm table-hover align-middle mb-0" id="runTable"></table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <div class="small text-muted" id="runPageInfo"></div>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="runPrevBtn" disabled>
                    <i class="bi bi-chevron-left"></i> Prev
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="runNextBtn" disabled>
                    Next <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const reportId = <?= json_encode($rid) ?>;
    const csrfToken = <?= json_encode($csrf) ?>;

    const els = {
        alert: document.getElementById('runAlert'),
        perPage: document.getElementById('runPerPage'),
        refresh: document.getElementById('runRefreshBtn'),
        exportBtn: document.getElementById('runExportBtn'),
        loading: document.getElementById('runLoading'),
        empty: document.getElementById('runEmpty'),
        tableWrap: document.getElementById('runTableWrap'),
        table: document.getElementById('runTable'),
        pageInfo: document.getElementById('runPageInfo'),
        prev: document.getElementById('runPrevBtn'),
        next: document.getElementById('runNextBtn'),
    };

    const state = { page: 1, total: 0, perPage: 25 };

    function showAlert(type, msg) {
        if (!els.alert) return;
        els.alert.className = 'alert alert-' + type;
        els.alert.textContent = msg;
        els.alert.classList.remove('d-none');
        setTimeout(() => els.alert.classList.add('d-none'), 4500);
    }

    function setLoading(on) {
        els.loading.classList.toggle('d-none', !on);
        if (on) {
            els.empty.classList.add('d-none');
            els.tableWrap.classList.add('d-none');
        }
    }

    async function run(page) {
        state.page = page || 1;
        state.perPage = Math.min(200, Math.max(1, parseInt(els.perPage.value || '25', 10) || 25));
        els.perPage.value = String(state.perPage);

        setLoading(true);
        try {
            const res = await fetch('/reports/ajaxPreview', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    report_id: reportId,
                    page: state.page,
                    per_page: state.perPage
                })
            });
            const j = await res.json().catch(() => null);
            if (!j || !j.success) {
                throw new Error((j && j.message) ? j.message : 'Run failed');
            }
            render(j);
        } catch (e) {
            showAlert('danger', e.message || 'Run failed');
            els.empty.classList.remove('d-none');
        } finally {
            setLoading(false);
        }
    }

    function render(j) {
        const cols = Array.isArray(j.columns) ? j.columns : [];
        const rows = Array.isArray(j.rows) ? j.rows : [];
        const total = parseInt(j.total || '0', 10) || 0;
        const page = parseInt(j.page || '1', 10) || 1;
        const perPage = parseInt(j.per_page || '25', 10) || 25;

        state.total = total;
        state.page = page;
        state.perPage = perPage;

        els.pageInfo.textContent = total ? ('Rows: ' + total + ' • Page ' + page) : 'No results';
        els.prev.disabled = page <= 1;
        els.next.disabled = (page * perPage) >= total;

        if (!cols.length) {
            els.empty.classList.remove('d-none');
            els.tableWrap.classList.add('d-none');
            return;
        }

        const thead = document.createElement('thead');
        const trh = document.createElement('tr');
        cols.forEach(c => {
            const th = document.createElement('th');
            th.textContent = c.label || c.key || '';
            trh.appendChild(th);
        });
        thead.appendChild(trh);

        const tbody = document.createElement('tbody');
        rows.forEach(r => {
            const tr = document.createElement('tr');
            cols.forEach(c => {
                const td = document.createElement('td');
                const k = c.key;
                const v = (r && k && Object.prototype.hasOwnProperty.call(r, k)) ? r[k] : '';
                td.textContent = (v === null || v === undefined) ? '' : String(v);
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });

        els.table.innerHTML = '';
        els.table.appendChild(thead);
        els.table.appendChild(tbody);
        els.empty.classList.add('d-none');
        els.tableWrap.classList.remove('d-none');
    }

    async function exportCsv() {
        try {
            els.exportBtn.disabled = true;
            const res = await fetch('/reports/exportDynamicCsv', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ csrf_token: csrfToken, report_id: reportId })
            });
            if (!res.ok) throw new Error('Export failed');
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
        } finally {
            els.exportBtn.disabled = false;
        }
    }

    els.refresh.addEventListener('click', () => run(1));
    els.prev.addEventListener('click', () => run(Math.max(1, state.page - 1)));
    els.next.addEventListener('click', () => run(state.page + 1));
    els.exportBtn.addEventListener('click', exportCsv);
})();
</script>

