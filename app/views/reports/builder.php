<?php
    $datasets = is_array($datasets ?? null) ? $datasets : [];
    $roles = is_array($roles ?? null) ? $roles : [];
    $report = is_array($report ?? null) ? $report : null;
    $reportDef = is_array($report_definition ?? null) ? $report_definition : null;
    $csrf = (string)($csrf_token ?? ($_SESSION['csrf_token'] ?? ''));
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="bi bi-sliders text-primary"></i> Report Builder
            </h1>
            <p class="text-muted mb-0">Build custom reports with columns, filters, grouping, and CSV export.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="/reports/saved" class="btn btn-outline-primary">
                <i class="bi bi-folder2-open me-1"></i>Saved Reports
            </a>
            <a href="/reports" class="btn btn-outline-secondary">
                <i class="bi bi-graph-up me-1"></i>Analytics
            </a>
        </div>
    </div>

    <div class="alert d-none" id="rbAlert"></div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-wrench-adjustable-circle me-2 text-primary"></i>Builder</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Data Source</label>
                        <select class="form-select" id="rbDataset">
                            <option value="">Select dataset…</option>
                            <?php foreach ($datasets as $d): ?>
                                <?php
                                    $k = (string)($d['key'] ?? '');
                                    $lbl = (string)($d['label'] ?? $k);
                                ?>
                                <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($lbl) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text" id="rbDatasetDesc"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Columns</label>
                        <select class="form-select" id="rbColumns" multiple size="10" disabled></select>
                        <div class="form-text">Select fields to display.</div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Filters</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="rbAddFilter" disabled>
                                <i class="bi bi-plus-lg me-1"></i>Add
                            </button>
                        </div>
                        <div id="rbFilters" class="d-flex flex-column gap-2"></div>
                        <div class="form-text">Use multiple rows; all filters are ANDed together.</div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">Grouping (Group By)</label>
                        <select class="form-select" id="rbGroupBy" multiple size="6" disabled></select>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Aggregates</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="rbAddAgg" disabled>
                                <i class="bi bi-plus-lg me-1"></i>Add
                            </button>
                        </div>
                        <div id="rbAggregates" class="d-flex flex-column gap-2"></div>
                        <div class="form-text">COUNT works on any dataset. SUM/AVG/MIN/MAX require numeric fields.</div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Sorting</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="rbAddSort" disabled>
                                <i class="bi bi-plus-lg me-1"></i>Add
                            </button>
                        </div>
                        <div id="rbSort" class="d-flex flex-column gap-2"></div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Limit (TOP N)</label>
                            <input type="number" class="form-control" id="rbLimit" min="0" placeholder="0 = no limit" disabled>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Per page</label>
                            <input type="number" class="form-control" id="rbPerPage" min="1" max="200" value="25" disabled>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" id="rbPreviewBtn" disabled>
                            <i class="bi bi-play-fill me-1"></i>Preview
                        </button>
                        <button type="button" class="btn btn-outline-success" id="rbExportBtn" disabled>
                            <i class="bi bi-download me-1"></i>Export CSV
                        </button>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-save me-2 text-success"></i>Save Report</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" id="rbName" placeholder="e.g. Open tickets by priority">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="rbDescription" rows="2" placeholder="Optional"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Visibility</label>
                        <select class="form-select" id="rbVisibility">
                            <option value="admin" selected>Admin only</option>
                            <option value="roles">Allow specific roles</option>
                        </select>
                    </div>
                    <div class="mb-3" id="rbRolesWrap" style="display:none;">
                        <label class="form-label">Allowed Roles</label>
                        <select class="form-select" id="rbAllowedRoles" multiple size="6">
                            <?php foreach ($roles as $r): ?>
                                <?php
                                    $rid = (int)($r['id'] ?? 0);
                                    $rname = (string)($r['display_name'] ?? ($r['name'] ?? 'Role'));
                                ?>
                                <?php if ($rid > 0): ?>
                                    <option value="<?= $rid ?>"><?= htmlspecialchars($rname) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Selected roles can run/view this report in “Saved Reports”.</div>
                    </div>

                    <button type="button" class="btn btn-success w-100" id="rbSaveBtn">
                        <i class="bi bi-save me-1"></i>Save Report
                    </button>

                    <div class="small text-muted mt-2" id="rbSavedMeta"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-table me-2 text-info"></i>Preview</h6>
                    <div class="small text-muted" id="rbPreviewInfo"></div>
                </div>
                <div class="card-body">
                    <div id="rbPreviewEmpty" class="text-center text-muted py-5">
                        Select a dataset and click <strong>Preview</strong>.
                    </div>
                    <div id="rbPreviewLoading" class="text-center py-4 d-none">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading…</span>
                        </div>
                    </div>
                    <div class="table-responsive d-none" id="rbPreviewTableWrap">
                        <table class="table table-sm table-hover align-middle mb-0" id="rbPreviewTable"></table>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                    <div class="small text-muted" id="rbPageInfo"></div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="rbPrevPage" disabled>
                            <i class="bi bi-chevron-left"></i> Prev
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="rbNextPage" disabled>
                            Next <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const RB = {
        csrfToken: <?= json_encode($csrf) ?>,
        endpoints: {
            fields: '/reports/ajaxFields',
            preview: '/reports/ajaxPreview',
            save: '/reports/ajaxSaveReport',
            exportCsv: '/reports/exportDynamicCsv'
        },
        datasets: <?= json_encode($datasets) ?>,
        existingReport: <?= json_encode($report) ?>,
        existingDefinition: <?= json_encode($reportDef) ?>,
    };

    const els = {
        alert: document.getElementById('rbAlert'),
        dataset: document.getElementById('rbDataset'),
        datasetDesc: document.getElementById('rbDatasetDesc'),
        columns: document.getElementById('rbColumns'),
        addFilter: document.getElementById('rbAddFilter'),
        filters: document.getElementById('rbFilters'),
        groupBy: document.getElementById('rbGroupBy'),
        aggregates: document.getElementById('rbAggregates'),
        addAgg: document.getElementById('rbAddAgg'),
        sort: document.getElementById('rbSort'),
        addSort: document.getElementById('rbAddSort'),
        limit: document.getElementById('rbLimit'),
        perPage: document.getElementById('rbPerPage'),
        previewBtn: document.getElementById('rbPreviewBtn'),
        exportBtn: document.getElementById('rbExportBtn'),
        previewInfo: document.getElementById('rbPreviewInfo'),
        previewEmpty: document.getElementById('rbPreviewEmpty'),
        previewLoading: document.getElementById('rbPreviewLoading'),
        previewTableWrap: document.getElementById('rbPreviewTableWrap'),
        previewTable: document.getElementById('rbPreviewTable'),
        pageInfo: document.getElementById('rbPageInfo'),
        prevPage: document.getElementById('rbPrevPage'),
        nextPage: document.getElementById('rbNextPage'),
        name: document.getElementById('rbName'),
        description: document.getElementById('rbDescription'),
        visibility: document.getElementById('rbVisibility'),
        rolesWrap: document.getElementById('rbRolesWrap'),
        allowedRoles: document.getElementById('rbAllowedRoles'),
        saveBtn: document.getElementById('rbSaveBtn'),
        savedMeta: document.getElementById('rbSavedMeta'),
    };

    const OPS = [
        { value: '=', label: '=' },
        { value: '!=', label: '!=' },
        { value: 'contains', label: 'contains' },
        { value: 'starts_with', label: 'starts with' },
        { value: 'in', label: 'in' },
        { value: 'between', label: 'between' },
        { value: '>=', label: '>=' },
        { value: '<=', label: '<=' },
        { value: 'is_null', label: 'is null' },
        { value: 'not_null', label: 'not null' },
    ];

    const state = {
        dataset: '',
        fields: [],
        fieldsByKey: {},
        page: 1,
        lastPreviewOk: false,
    };

    function showAlert(type, msg) {
        if (!els.alert) return;
        els.alert.className = 'alert alert-' + type;
        els.alert.textContent = msg;
        els.alert.classList.remove('d-none');
        setTimeout(() => {
            if (els.alert) els.alert.classList.add('d-none');
        }, 4500);
    }

    function setLoading(on) {
        els.previewLoading.classList.toggle('d-none', !on);
        if (on) {
            els.previewEmpty.classList.add('d-none');
            els.previewTableWrap.classList.add('d-none');
        }
    }

    function setBuilderEnabled(on) {
        els.columns.disabled = !on;
        els.groupBy.disabled = !on;
        els.addFilter.disabled = !on;
        els.addAgg.disabled = !on;
        els.addSort.disabled = !on;
        els.limit.disabled = !on;
        els.perPage.disabled = !on;
        els.previewBtn.disabled = !on;
    }

    function datasetInfo(key) {
        return (RB.datasets || []).find(d => String(d.key || '') === String(key)) || null;
    }

    function populateSelect(el, options, placeholder) {
        el.innerHTML = '';
        if (placeholder) {
            const o = document.createElement('option');
            o.value = '';
            o.textContent = placeholder;
            el.appendChild(o);
        }
        options.forEach(opt => {
            const o = document.createElement('option');
            o.value = String(opt.value);
            o.textContent = String(opt.label);
            el.appendChild(o);
        });
    }

    function populateMultiSelect(el, options) {
        el.innerHTML = '';
        options.forEach(opt => {
            const o = document.createElement('option');
            o.value = String(opt.value);
            o.textContent = String(opt.label);
            el.appendChild(o);
        });
    }

    function getSelectedValues(el) {
        return Array.from(el.selectedOptions || []).map(o => o.value);
    }

    function buildFilterRow(initial) {
        const row = document.createElement('div');
        row.className = 'border rounded p-2';

        const top = document.createElement('div');
        top.className = 'd-flex gap-2 align-items-start';

        const fieldSel = document.createElement('select');
        fieldSel.className = 'form-select form-select-sm';
        fieldSel.style.maxWidth = '40%';

        const opSel = document.createElement('select');
        opSel.className = 'form-select form-select-sm';
        opSel.style.maxWidth = '28%';
        populateSelect(opSel, OPS.map(o => ({ value: o.value, label: o.label })));

        const valWrap = document.createElement('div');
        valWrap.className = 'flex-grow-1';

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-sm btn-outline-danger';
        removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';

        top.appendChild(fieldSel);
        top.appendChild(opSel);
        top.appendChild(valWrap);
        top.appendChild(removeBtn);
        row.appendChild(top);

        const updateValueInput = () => {
            const fk = fieldSel.value;
            const meta = state.fieldsByKey[fk];
            const op = opSel.value;
            valWrap.innerHTML = '';

            if (!meta) {
                return;
            }

            if (op === 'is_null' || op === 'not_null') {
                return;
            }

            const type = String(meta.type || 'string');
            const isBetween = op === 'between';
            const isIn = op === 'in';

            const mkInput = () => {
                const input = document.createElement('input');
                input.className = 'form-control form-control-sm';
                input.type = (type === 'number') ? 'number' : (type === 'date' ? 'date' : (type === 'datetime' ? 'datetime-local' : 'text'));
                if (type === 'number') input.step = 'any';
                return input;
            };

            if (isBetween) {
                const row2 = document.createElement('div');
                row2.className = 'd-flex gap-2';
                const a = mkInput();
                const b = mkInput();
                a.placeholder = 'From';
                b.placeholder = 'To';
                if (initial && initial.value_from) a.value = initial.value_from;
                if (initial && initial.value_to) b.value = initial.value_to;
                row2.appendChild(a);
                row2.appendChild(b);
                valWrap.appendChild(row2);
                return;
            }

            if (isIn && type === 'select' && Array.isArray(meta.options) && meta.options.length) {
                const sel = document.createElement('select');
                sel.className = 'form-select form-select-sm';
                sel.multiple = true;
                sel.size = Math.min(6, meta.options.length);
                populateMultiSelect(sel, meta.options.map(o => ({ value: o.value, label: o.label })));
                if (initial && Array.isArray(initial.value)) {
                    Array.from(sel.options).forEach(o => { o.selected = initial.value.includes(o.value); });
                }
                valWrap.appendChild(sel);
                return;
            }

            if (type === 'select' && Array.isArray(meta.options) && meta.options.length && op !== 'in') {
                const sel = document.createElement('select');
                sel.className = 'form-select form-select-sm';
                populateSelect(sel, meta.options.map(o => ({ value: o.value, label: o.label })), 'Select…');
                if (initial && typeof initial.value === 'string') sel.value = initial.value;
                valWrap.appendChild(sel);
                return;
            }

            const input = mkInput();
            if (isIn) input.placeholder = 'Comma-separated values';
            if (initial && typeof initial.value === 'string') input.value = initial.value;
            valWrap.appendChild(input);
        };

        removeBtn.addEventListener('click', () => {
            row.remove();
        });
        fieldSel.addEventListener('change', () => {
            updateValueInput();
        });
        opSel.addEventListener('change', () => {
            updateValueInput();
        });

        // Populate field options
        const fieldOpts = state.fields
            .filter(f => f.filterable)
            .map(f => ({ value: f.key, label: f.label }));
        populateSelect(fieldSel, fieldOpts, 'Field…');

        if (initial && initial.field) fieldSel.value = initial.field;
        if (initial && initial.op) opSel.value = initial.op;
        updateValueInput();

        return row;
    }

    function buildAggRow(initial) {
        const row = document.createElement('div');
        row.className = 'border rounded p-2';

        const wrap = document.createElement('div');
        wrap.className = 'd-flex gap-2 align-items-start';

        const fnSel = document.createElement('select');
        fnSel.className = 'form-select form-select-sm';
        fnSel.style.maxWidth = '35%';
        populateSelect(fnSel, [
            { value: 'COUNT', label: 'COUNT' },
            { value: 'SUM', label: 'SUM' },
            { value: 'AVG', label: 'AVG' },
            { value: 'MIN', label: 'MIN' },
            { value: 'MAX', label: 'MAX' },
        ]);

        const fieldSel = document.createElement('select');
        fieldSel.className = 'form-select form-select-sm';

        const labelInput = document.createElement('input');
        labelInput.className = 'form-control form-control-sm';
        labelInput.placeholder = 'Label (optional)';
        labelInput.style.maxWidth = '40%';

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-sm btn-outline-danger';
        removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';

        const refreshFields = () => {
            const fn = fnSel.value;
            let opts = [];
            if (fn === 'COUNT') {
                opts = [{ value: '', label: '*' }];
            }
            const numeric = state.fields.filter(f => f.numeric).map(f => ({ value: f.key, label: f.label }));
            populateSelect(fieldSel, opts.concat(numeric), fn === 'COUNT' ? 'Field…' : 'Numeric field…');
            if (fn !== 'COUNT' && (!fieldSel.value || fieldSel.value === '')) {
                // try keep existing
                if (initial && initial.field) fieldSel.value = initial.field;
            }
        };

        fnSel.addEventListener('change', () => {
            refreshFields();
            refreshSortOptions();
        });
        fieldSel.addEventListener('change', () => refreshSortOptions());
        labelInput.addEventListener('input', () => refreshSortOptions());

        removeBtn.addEventListener('click', () => {
            row.remove();
            refreshSortOptions();
        });

        wrap.appendChild(fnSel);
        wrap.appendChild(fieldSel);
        wrap.appendChild(labelInput);
        wrap.appendChild(removeBtn);
        row.appendChild(wrap);

        if (initial && initial.fn) fnSel.value = initial.fn;
        if (initial && initial.label) labelInput.value = initial.label;
        refreshFields();
        if (initial && initial.field !== undefined) fieldSel.value = initial.field;
        refreshSortOptions();
        return row;
    }

    function buildSortRow(initial) {
        const row = document.createElement('div');
        row.className = 'border rounded p-2';

        const wrap = document.createElement('div');
        wrap.className = 'd-flex gap-2 align-items-start';

        const fieldSel = document.createElement('select');
        fieldSel.className = 'form-select form-select-sm';

        const dirSel = document.createElement('select');
        dirSel.className = 'form-select form-select-sm';
        dirSel.style.maxWidth = '30%';
        populateSelect(dirSel, [
            { value: 'ASC', label: 'ASC' },
            { value: 'DESC', label: 'DESC' },
        ]);

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-sm btn-outline-danger';
        removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';

        removeBtn.addEventListener('click', () => row.remove());

        wrap.appendChild(fieldSel);
        wrap.appendChild(dirSel);
        wrap.appendChild(removeBtn);
        row.appendChild(wrap);

        // options are populated by refreshSortOptions()
        if (initial && initial.dir) dirSel.value = initial.dir.toUpperCase();
        row.__fieldSel = fieldSel;
        row.__dirSel = dirSel;
        return row;
    }

    function currentAggregates() {
        return Array.from(els.aggregates.querySelectorAll('.border')).map((rowEl, idx) => {
            const selects = rowEl.querySelectorAll('select');
            const inputs = rowEl.querySelectorAll('input');
            const fn = selects[0]?.value || 'COUNT';
            const field = selects[1]?.value || '';
            const label = inputs[0]?.value || '';
            return { idx, fn, field, label };
        });
    }

    function currentSortOptions() {
        const cols = getSelectedValues(els.columns);
        const group = getSelectedValues(els.groupBy);
        const aggs = currentAggregates();
        const isAgg = group.length > 0 || aggs.length > 0;

        const out = [];
        if (!isAgg) {
            cols.forEach(k => {
                const m = state.fieldsByKey[k];
                if (m) out.push({ value: k, label: m.label });
            });
            return out;
        }
        // group fields
        group.forEach(k => {
            const m = state.fieldsByKey[k];
            if (m) out.push({ value: k, label: m.label });
        });
        // aggregates (stable alias agg_{idx})
        aggs.forEach(a => {
            const label = (a.label && a.label.trim()) ? a.label.trim() : (a.fn + (a.field ? (' ' + (state.fieldsByKey[a.field]?.label || a.field)) : ''));
            out.push({ value: 'agg_' + a.idx, label });
        });
        return out;
    }

    function refreshSortOptions() {
        const opts = currentSortOptions();
        Array.from(els.sort.children).forEach(row => {
            const sel = row.__fieldSel || row.querySelector('select');
            if (!sel) return;
            const current = sel.value;
            populateSelect(sel, opts, 'Sort field…');
            if (opts.some(o => o.value === current)) {
                sel.value = current;
            }
        });
    }

    function buildDefinitionPayload() {
        const dataset = els.dataset.value;
        const columns = getSelectedValues(els.columns);
        const groupBy = getSelectedValues(els.groupBy);

        const filters = Array.from(els.filters.children).map(row => {
            const selects = row.querySelectorAll('select');
            const inputs = row.querySelectorAll('input');
            const field = selects[0]?.value || '';
            const op = selects[1]?.value || '';

            // value element is in the 3rd slot
            const valWrap = row.querySelector('.flex-grow-1');
            let value = null;
            let value_from = null;
            let value_to = null;

            if (op === 'between') {
                const ins = valWrap ? valWrap.querySelectorAll('input') : [];
                value_from = ins[0]?.value || '';
                value_to = ins[1]?.value || '';
                // normalize datetime-local
                const meta = state.fieldsByKey[field] || {};
                if (meta.type === 'datetime') {
                    value_from = value_from ? value_from.replace('T', ' ') : '';
                    value_to = value_to ? value_to.replace('T', ' ') : '';
                }
                return { field, op, value_from, value_to };
            }

            if (op === 'is_null' || op === 'not_null') {
                return { field, op };
            }

            const meta = state.fieldsByKey[field] || {};
            if (op === 'in' && meta.type === 'select') {
                const sel = valWrap ? valWrap.querySelector('select') : null;
                value = sel ? Array.from(sel.selectedOptions).map(o => o.value) : [];
                return { field, op, value };
            }

            const input = valWrap ? valWrap.querySelector('input,select') : null;
            value = input ? input.value : '';
            if (meta.type === 'datetime' && typeof value === 'string') {
                value = value.replace('T', ' ');
            }
            return { field, op, value };
        }).filter(f => f.field && f.op);

        const aggregates = currentAggregates().map(a => ({
            fn: a.fn,
            field: a.fn === 'COUNT' ? (a.field || '') : a.field,
            label: a.label || ''
        }));

        const sort = Array.from(els.sort.children).map(row => {
            const sel = row.__fieldSel || row.querySelector('select');
            const dirSel = row.__dirSel || row.querySelectorAll('select')[1];
            return { field: sel?.value || '', dir: (dirSel?.value || 'ASC') };
        }).filter(s => s.field);

        const limit = parseInt(els.limit.value || '0', 10) || 0;

        return {
            dataset,
            columns,
            filters,
            group_by: groupBy,
            aggregates,
            sort,
            limit
        };
    }

    async function fetchFields(dataset) {
        const url = RB.endpoints.fields + '?dataset=' + encodeURIComponent(dataset);
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const j = await res.json().catch(() => null);
        if (!j || !j.success) {
            throw new Error((j && j.message) ? j.message : 'Failed to load fields');
        }
        return j.fields || [];
    }

    function resetBuilderUI() {
        els.columns.innerHTML = '';
        els.groupBy.innerHTML = '';
        els.filters.innerHTML = '';
        els.aggregates.innerHTML = '';
        els.sort.innerHTML = '';
        els.limit.value = '';
        state.page = 1;
        state.lastPreviewOk = false;
        els.exportBtn.disabled = true;
        els.prevPage.disabled = true;
        els.nextPage.disabled = true;
        els.previewInfo.textContent = '';
        els.pageInfo.textContent = '';
    }

    function populateFieldsUI() {
        const all = state.fields.map(f => ({ value: f.key, label: f.label }));
        populateMultiSelect(els.columns, all);

        const groupable = state.fields.filter(f => f.groupable).map(f => ({ value: f.key, label: f.label }));
        populateMultiSelect(els.groupBy, groupable);
    }

    async function onDatasetChange() {
        const dataset = els.dataset.value;
        resetBuilderUI();
        if (!dataset) {
            setBuilderEnabled(false);
            els.datasetDesc.textContent = '';
            return;
        }
        const info = datasetInfo(dataset);
        els.datasetDesc.textContent = info ? (info.description || '') : '';

        setBuilderEnabled(false);
        try {
            const fields = await fetchFields(dataset);
            state.fields = fields;
            state.fieldsByKey = {};
            fields.forEach(f => { state.fieldsByKey[f.key] = f; });
            populateFieldsUI();
            setBuilderEnabled(true);

            // Seed one sort row by default
            if (els.sort.children.length === 0) {
                const r = buildSortRow();
                els.sort.appendChild(r);
            }
            refreshSortOptions();

            // If editing, apply existing definition
            if (RB.existingDefinition && typeof RB.existingDefinition === 'object') {
                applyExistingDefinitionOnce();
            }
        } catch (e) {
            showAlert('danger', e.message || 'Failed to load dataset fields');
            setBuilderEnabled(false);
        }
    }

    let appliedExisting = false;
    function applyExistingDefinitionOnce() {
        if (appliedExisting) return;
        appliedExisting = true;

        const def = RB.existingDefinition || {};
        // columns
        if (Array.isArray(def.columns)) {
            Array.from(els.columns.options).forEach(o => { o.selected = def.columns.includes(o.value); });
        }
        // group_by
        if (Array.isArray(def.group_by)) {
            Array.from(els.groupBy.options).forEach(o => { o.selected = def.group_by.includes(o.value); });
        }
        // filters
        if (Array.isArray(def.filters)) {
            def.filters.forEach(f => {
                els.filters.appendChild(buildFilterRow(f));
            });
        }
        // aggregates
        if (Array.isArray(def.aggregates)) {
            def.aggregates.forEach(a => {
                els.aggregates.appendChild(buildAggRow({ fn: a.fn, field: a.field, label: a.label }));
            });
        }
        // sort
        if (Array.isArray(def.sort)) {
            els.sort.innerHTML = '';
            def.sort.forEach(s => {
                const r = buildSortRow({ dir: s.dir, field: s.field });
                els.sort.appendChild(r);
            });
        }
        // limit
        if (typeof def.limit === 'number') {
            els.limit.value = String(def.limit);
        }
        refreshSortOptions();

        // Also apply report meta for edit
        if (RB.existingReport) {
            els.name.value = RB.existingReport.name || '';
            els.description.value = RB.existingReport.description || '';
            els.visibility.value = (RB.existingReport.visibility || 'admin');
            if (els.visibility.value === 'roles') {
                els.rolesWrap.style.display = '';
                const allowed = String(RB.existingReport.allowed_role_ids || '').split(',').map(s => s.trim()).filter(Boolean);
                Array.from(els.allowedRoles.options).forEach(o => { o.selected = allowed.includes(o.value); });
            }
            els.savedMeta.textContent = 'Editing saved report #' + (RB.existingReport.id || '');
        }
    }

    async function preview(page) {
        const dataset = els.dataset.value;
        if (!dataset) {
            showAlert('warning', 'Select a dataset first.');
            return;
        }
        state.page = page || 1;

        const definition = buildDefinitionPayload();
        const perPage = parseInt(els.perPage.value || '25', 10) || 25;

        setLoading(true);
        try {
            const res = await fetch(RB.endpoints.preview, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    csrf_token: RB.csrfToken,
                    definition,
                    page: state.page,
                    per_page: perPage
                })
            });
            const j = await res.json().catch(() => null);
            if (!j || !j.success) {
                throw new Error((j && j.message) ? j.message : 'Preview failed');
            }
            renderPreview(j);
            state.lastPreviewOk = true;
            els.exportBtn.disabled = false;
        } catch (e) {
            state.lastPreviewOk = false;
            els.exportBtn.disabled = true;
            showAlert('danger', e.message || 'Preview failed');
            els.previewEmpty.classList.remove('d-none');
            els.previewTableWrap.classList.add('d-none');
        } finally {
            setLoading(false);
        }
    }

    function renderPreview(j) {
        const cols = Array.isArray(j.columns) ? j.columns : [];
        const rows = Array.isArray(j.rows) ? j.rows : [];
        const total = parseInt(j.total || '0', 10) || 0;
        const page = parseInt(j.page || '1', 10) || 1;
        const perPage = parseInt(j.per_page || '25', 10) || 25;

        els.previewInfo.textContent = total ? ('Rows: ' + total) : 'No results';
        els.pageInfo.textContent = total ? ('Page ' + page + ' • ' + perPage + '/page') : '';

        const canPrev = page > 1;
        const canNext = (page * perPage) < total;
        els.prevPage.disabled = !canPrev;
        els.nextPage.disabled = !canNext;

        // build table
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
                const key = c.key;
                const v = (r && key && Object.prototype.hasOwnProperty.call(r, key)) ? r[key] : '';
                td.textContent = (v === null || v === undefined) ? '' : String(v);
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });

        els.previewTable.innerHTML = '';
        els.previewTable.appendChild(thead);
        els.previewTable.appendChild(tbody);

        els.previewEmpty.classList.add('d-none');
        els.previewTableWrap.classList.toggle('d-none', cols.length === 0);
    }

    async function exportCsv() {
        const dataset = els.dataset.value;
        if (!dataset) return;
        const definition = buildDefinitionPayload();

        try {
            els.exportBtn.disabled = true;
            const res = await fetch(RB.endpoints.exportCsv, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    csrf_token: RB.csrfToken,
                    definition
                })
            });
            if (!res.ok) {
                throw new Error('Export failed');
            }
            const blob = await res.blob();
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'dynamic_report_' + new Date().toISOString().slice(0,10) + '.csv';
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

    async function saveReport() {
        const dataset = els.dataset.value;
        if (!dataset) {
            showAlert('warning', 'Select a dataset first.');
            return;
        }
        const name = els.name.value.trim();
        if (!name) {
            showAlert('warning', 'Report name is required.');
            return;
        }

        const definition = buildDefinitionPayload();
        const visibility = els.visibility.value;
        const allowedRoleIds = getSelectedValues(els.allowedRoles);
        if (visibility === 'roles' && allowedRoleIds.length === 0) {
            showAlert('warning', 'Select at least one role for “Allow specific roles”.');
            return;
        }

        const payload = {
            csrf_token: RB.csrfToken,
            report_id: RB.existingReport && RB.existingReport.id ? RB.existingReport.id : 0,
            name,
            description: els.description.value || '',
            visibility,
            allowed_role_ids: allowedRoleIds,
            definition
        };

        els.saveBtn.disabled = true;
        try {
            const res = await fetch(RB.endpoints.save, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(payload)
            });
            const j = await res.json().catch(() => null);
            if (!j || !j.success) {
                throw new Error((j && j.message) ? j.message : 'Save failed');
            }
            showAlert('success', 'Report saved.');
            els.savedMeta.textContent = 'Saved report #' + j.report_id + ' • available under Saved Reports';
            RB.existingReport = RB.existingReport || {};
            RB.existingReport.id = j.report_id;
        } catch (e) {
            showAlert('danger', e.message || 'Save failed');
        } finally {
            els.saveBtn.disabled = false;
        }
    }

    // Events
    els.dataset.addEventListener('change', onDatasetChange);
    els.addFilter.addEventListener('click', () => {
        els.filters.appendChild(buildFilterRow());
    });
    els.addAgg.addEventListener('click', () => {
        els.aggregates.appendChild(buildAggRow());
        refreshSortOptions();
    });
    els.addSort.addEventListener('click', () => {
        const r = buildSortRow();
        els.sort.appendChild(r);
        refreshSortOptions();
    });
    els.columns.addEventListener('change', () => refreshSortOptions());
    els.groupBy.addEventListener('change', () => refreshSortOptions());

    els.previewBtn.addEventListener('click', () => preview(1));
    els.prevPage.addEventListener('click', () => preview(Math.max(1, state.page - 1)));
    els.nextPage.addEventListener('click', () => preview(state.page + 1));
    els.exportBtn.addEventListener('click', exportCsv);

    els.visibility.addEventListener('change', () => {
        els.rolesWrap.style.display = (els.visibility.value === 'roles') ? '' : 'none';
    });
    els.saveBtn.addEventListener('click', saveReport);

    // Init edit mode dataset/definition if present
    if (RB.existingDefinition && RB.existingDefinition.dataset) {
        els.dataset.value = RB.existingDefinition.dataset;
        onDatasetChange();
    }
})();
</script>

