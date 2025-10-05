<div class="container-fluid">
    <div class="rounded-3 p-4 mb-4 page-header-solid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/sites" class="text-decoration-none">Sites</a></li>
                <li class="breadcrumb-item"><a href="/sites/viewSite/<?= (int)$site['id'] ?>" class="text-decoration-none"><?= htmlspecialchars($site['name']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Visit</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h4 mb-1 text-dark">Edit Visit</h1>
                <div class="text-muted small">Update the logged visit</div>
            </div>
            <a href="/sitevisits/show/<?= (int)$id ?>" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Visit Details</h5>
                </div>
                <div class="card-body">
                    <form action="/sitevisits/edit/<?= (int)$id ?>" method="post" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="visit_date" class="form-label">Visit Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" id="visit_date" name="visit_date" class="form-control <?= !empty($data['visit_date_err']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($data['visit_date']) ?>" required>
                                <?php if (!empty($data['visit_date_err'])): ?>
                                <div class="invalid-feedback"><?= $data['visit_date_err'] ?></div>
                                <?php else: ?>
                                <div class="invalid-feedback">Visit date is required</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="report_title" class="form-label">Title (optional)</label>
                                <input type="text" id="report_title" name="report_title" class="form-control" maxlength="255" value="<?= htmlspecialchars($data['report_title']) ?>">
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label for="reason" class="form-label">Reason (link similar visits)</label>
                                <input list="reason_suggestions" id="reason" name="reason" class="form-control <?= !empty($data['reason_err']) ? 'is-invalid' : '' ?>" maxlength="255" value="<?= htmlspecialchars($data['reason'] ?? '') ?>" placeholder="e.g., AC maintenance, Network outage">
                                <datalist id="reason_suggestions">
                                    <?php if (!empty($recent_reasons ?? [])): foreach (($recent_reasons ?? []) as $r): ?>
                                        <option value="<?= htmlspecialchars($r) ?>"></option>
                                    <?php endforeach; endif; ?>
                                </datalist>
                            </div>
                            <div class="col-md-6">
                                <label for="previous_visit_id" class="form-label">Link to previous visit (optional)</label>
                                <select id="previous_visit_id" name="previous_visit_id" class="form-select">
                                    <option value="">None</option>
                                    <?php if (!empty($previous_visits ?? [])): foreach (($previous_visits ?? []) as $pv): ?>
                                    <option value="<?= (int)$pv['id'] ?>" <?= (!empty($data['previous_visit_id']) && (int)$data['previous_visit_id'] === (int)$pv['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(!empty($pv['title']) ? $pv['title'] : ('Visit on ' . date('M j, Y H:i', strtotime($pv['visit_date'])))) ?>
                                    </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="summary" class="form-label">What was done <span class="text-danger">*</span></label>
                            <textarea id="summary" name="summary" rows="8" class="form-control <?= !empty($data['summary_err']) ? 'is-invalid' : '' ?>" required><?= htmlspecialchars($data['summary']) ?></textarea>
                            <?php if (!empty($data['summary_err'])): ?>
                            <div class="invalid-feedback"><?= $data['summary_err'] ?></div>
                            <?php else: ?>
                            <div class="invalid-feedback">Please describe the visit</div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="/sitevisits/show/<?= (int)$id ?>" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-control:focus,
.form-select:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    border-color: #86b7fe;
}
</style>

<script>
(function () {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>


