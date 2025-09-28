<div class="container-fluid">
    <div class="bg-light rounded-3 p-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/sites" class="text-decoration-none">Sites</a></li>
                <li class="breadcrumb-item"><a href="/sites/viewSite/<?= (int)$site['id'] ?>" class="text-decoration-none"><?= htmlspecialchars($site['name']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Visit Preview</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Visit on <?= date('M j, Y H:i', strtotime($visit['visit_date'])) ?></h1>
                <p class="text-muted mb-0">Technician: <?= htmlspecialchars($visit['full_name'] ?? $visit['username'] ?? 'Technician') ?></p>
            </div>
            <div class="d-flex gap-2">
                <?php $canEdit = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') || ((int)($_SESSION['user_id'] ?? 0) === (int)$visit['technician_id']); ?>
                <?php if ($canEdit): ?>
                <a href="/sitevisits/edit/<?= (int)$visit['id'] ?>" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
                <?php endif; ?>
                <a href="/sites/viewSite/<?= (int)$site['id'] ?>" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Back</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-9">
            <?php if (!empty($previous_chain ?? []) || !empty($next_visits ?? [])): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Visit Link Chain</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <?php foreach (($previous_chain ?? []) as $idx => $pv): ?>
                            <a href="/sitevisits/show/<?= (int)$pv['id'] ?>" class="badge bg-light text-dark border text-decoration-none">
                                <?= htmlspecialchars($pv['title'] ?: ('Visit ' . date('M j, Y', strtotime($pv['visit_date'])))) ?>
                            </a>
                            <span class="text-muted">→</span>
                        <?php endforeach; ?>
                        <span class="badge bg-primary">Current</span>
                        <?php foreach (($next_visits ?? []) as $nv): ?>
                            <span class="text-muted">→</span>
                            <a href="/sitevisits/show/<?= (int)$nv['id'] ?>" class="badge bg-light text-dark border text-decoration-none">
                                <?= htmlspecialchars($nv['title'] ?: ('Visit ' . date('M j, Y', strtotime($nv['visit_date'])))) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Summary</h5>
                        <?php if (!empty($visit['reason'])): ?>
                        <span class="badge bg-light text-dark border">
                            Reason: <?= htmlspecialchars($visit['reason']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="mb-3"><?= htmlspecialchars($visit['title'] ?? '') ?></h5>
                    <div class="prose">
                        <pre class="mb-0" style="white-space: pre-wrap; word-wrap: break-word; background: #f8f9fa; border: 1px solid #eee; padding: 1rem; border-radius: .5rem;">
<?= htmlspecialchars($visit['summary'] ?? '') ?>
                        </pre>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Details</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2"><span class="text-muted">Date:</span> <?= date('M j, Y H:i', strtotime($visit['visit_date'])) ?></div>
                    <div class="mb-2"><span class="text-muted">Technician:</span> <?= htmlspecialchars($visit['full_name'] ?? $visit['username'] ?? 'Technician') ?></div>
                    <div class="mb-0"><span class="text-muted">Site:</span> <?= htmlspecialchars($site['name']) ?></div>
                </div>
            </div>

            <?php if (!empty($related_visits ?? [])): ?>
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Related Visits</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($related_visits as $rv): if ((int)$rv['id'] === (int)$visit['id']) continue; ?>
                        <a href="/sitevisits/show/<?= (int)$rv['id'] ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <span class="text-truncate" style="max-width: 160px;"><?= htmlspecialchars($rv['title'] ?: ($rv['site_name'] ?? 'Visit')) ?></span>
                                <small class="text-muted"><?= date('M j, Y H:i', strtotime($rv['visit_date'])) ?></small>
                            </div>
                            <div class="small text-muted">By <?= htmlspecialchars($rv['full_name'] ?? $rv['username'] ?? 'Technician') ?></div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>


