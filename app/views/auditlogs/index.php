<?php require_once VIEWSPATH . '/partials/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Audit Log</h1>
            <div class="text-muted small">System-wide activity log (filtered). For “performance”, focus on tasks/projects/notes/client/site events.</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="/auditlogs">
                <i class="bi bi-arrow-clockwise"></i> Reset
            </a>
        </div>
    </div>

    <?php if (!empty($_SESSION['flash']['audit_error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['flash']['audit_error'] ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header">
            <strong>Filters</strong>
        </div>
        <div class="card-body">
            <form class="row g-3" method="GET" action="/auditlogs">
                <div class="col-md-3">
                    <label class="form-label">User</label>
                    <select class="form-select" name="user_id">
                        <option value="">All users</option>
                        <?php foreach (($users ?? []) as $u): ?>
                            <?php
                                $uid = (int)($u['id'] ?? 0);
                                $label = $u['full_name'] ?? ($u['username'] ?? ($u['name'] ?? ('User #' . $uid)));
                                $selected = ((string)$uid === (string)($filters['user_id'] ?? '')) ? 'selected' : '';
                            ?>
                            <option value="<?= $uid ?>" <?= $selected ?>>
                                <?= htmlspecialchars($label) ?> (<?= htmlspecialchars($u['role'] ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Entity Type</label>
                    <input class="form-control" name="entity_type" value="<?= htmlspecialchars($filters['entity_type'] ?? '') ?>" placeholder="task, project, note, client...">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Entity ID</label>
                    <input class="form-control" name="entity_id" value="<?= htmlspecialchars($filters['entity_id'] ?? '') ?>" placeholder="e.g. 123">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Action</label>
                    <input class="form-control" name="action" value="<?= htmlspecialchars($filters['action'] ?? '') ?>" placeholder="created, updated...">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="<?= htmlspecialchars($filters['start_date'] ?? '') ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($filters['end_date'] ?? '') ?>">
                </div>

                <div class="col-md-1">
                    <label class="form-label">Limit</label>
                    <select class="form-select" name="limit">
                        <?php foreach ([25, 50, 100, 200] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ((string)$opt === (string)($filters['limit'] ?? '50')) ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                    <div class="text-muted small align-self-center">
                        Tip: set `AUDIT_LOG_READ_REQUESTS=1` to include page views in the audit log.
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Events</strong>
            <span class="badge bg-light text-muted"><?= count($activities ?? []) ?> shown</span>
        </div>
        <div class="card-body">
            <?php if (empty($activities)): ?>
                <p class="text-muted mb-0">No audit events found for the selected filters.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Entity</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP</th>
                                <th>Metadata</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $a): ?>
                                <?php
                                    $entityType = strtolower($a['entity_type'] ?? '');
                                    $entityId = (int)($a['entity_id'] ?? 0);
                                    $entityUrl = null;
                                    if ($entityType === 'project' && $entityId) $entityUrl = '/projects/viewProject/' . $entityId;
                                    elseif ($entityType === 'task' && $entityId) $entityUrl = '/tasks/show/' . $entityId;
                                    elseif ($entityType === 'ticket' && $entityId) $entityUrl = '/tickets/show/' . $entityId;
                                    elseif ($entityType === 'note' && $entityId) $entityUrl = '/notes/show/' . $entityId;
                                    elseif ($entityType === 'client' && $entityId) $entityUrl = '/clients/viewClient/' . $entityId;
                                    elseif ($entityType === 'site' && $entityId) $entityUrl = '/sites/viewSite/' . $entityId;

                                    $meta = [];
                                    if (!empty($a['metadata'])) {
                                        $decoded = json_decode($a['metadata'], true);
                                        if (is_array($decoded)) $meta = $decoded;
                                    }
                                ?>
                                <tr>
                                    <td class="text-nowrap"><?= !empty($a['created_at']) ? htmlspecialchars(formatDateTime($a['created_at'])) : '' ?></td>
                                    <td><?= htmlspecialchars($a['user_full_name'] ?? $a['username'] ?? ('User #' . (int)($a['user_id'] ?? 0))) ?></td>
                                    <td class="text-nowrap">
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($entityType ?: 'unknown') ?></span>
                                        <?php if ($entityId): ?>
                                            <?php if ($entityUrl): ?>
                                                <a href="<?= htmlspecialchars($entityUrl) ?>" class="ms-1 text-decoration-none">#<?= $entityId ?></a>
                                            <?php else: ?>
                                                <span class="ms-1">#<?= $entityId ?></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-nowrap"><?= htmlspecialchars($a['action'] ?? '') ?></td>
                                    <td style="min-width: 280px;"><?= htmlspecialchars($a['description'] ?? '') ?></td>
                                    <td class="text-nowrap"><?= htmlspecialchars($a['ip_address'] ?? '') ?></td>
                                    <td style="min-width: 240px;">
                                        <?php if (!empty($meta)): ?>
                                            <details>
                                                <summary class="text-muted small">View</summary>
                                                <pre class="small mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars(json_encode($meta, JSON_PRETTY_PRINT)) ?></pre>
                                            </details>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                    $base = $base_query ?? '';
                    $prevPage = max(1, (int)($page ?? 1) - 1);
                    $nextPage = (int)($page ?? 1) + 1;
                    $prevHref = '/auditlogs?' . ($base ? ($base . '&') : '') . 'page=' . $prevPage;
                    $nextHref = '/auditlogs?' . ($base ? ($base . '&') : '') . 'page=' . $nextPage;
                ?>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <a class="btn btn-outline-secondary <?= ((int)($page ?? 1) <= 1) ? 'disabled' : '' ?>" href="<?= htmlspecialchars($prevHref) ?>">Previous</a>
                    <div class="text-muted small">Page <?= (int)($page ?? 1) ?></div>
                    <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($nextHref) ?>">Next</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once VIEWSPATH . '/partials/footer.php'; ?>

