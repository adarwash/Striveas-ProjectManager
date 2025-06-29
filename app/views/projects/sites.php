<div class="row mb-4">
    <div class="col-md-8">
        <h1>Manage Sites for <?= htmlspecialchars($project->title) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/projects">Projects</a></li>
                <li class="breadcrumb-item"><a href="/projects/viewProject/<?= $project->id ?>"><?= htmlspecialchars($project->title) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Manage Sites</li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 text-md-end d-flex justify-content-md-end align-items-center">
        <a href="/projects/viewProject/<?= $project->id ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Project
        </a>
    </div>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert <?= $_SESSION['flash_class'] ?> alert-dismissible fade show" role="alert">
        <?= $_SESSION['flash_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_message']); ?>
    <?php unset($_SESSION['flash_class']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Link a Site</h5>
            </div>
            <div class="card-body">
                <form action="/projects/linkSite/<?= $project->id ?>" method="post">
                    <div class="mb-3">
                        <label for="site_id" class="form-label">Select Site</label>
                        <select name="site_id" id="site_id" class="form-control" required>
                            <option value="">-- Select a Site --</option>
                            <?php foreach ($all_sites as $site): ?>
                                <?php if (!in_array($site['id'], $linked_site_ids)): ?>
                                    <option value="<?= $site['id'] ?>"><?= htmlspecialchars($site['name']) ?> (<?= htmlspecialchars($site['location']) ?>)</option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add any notes about this link (e.g., why this project is linked to this site)"></textarea>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-link"></i> Link Site
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Linked Sites</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($linked_sites)): ?>
                    <div class="p-4 text-center">
                        <p class="text-muted mb-0">No sites linked to this project yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Site Name</th>
                                    <th>Location</th>
                                    <th>Date Linked</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($linked_sites as $site): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($site['name']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($site['location']) ?></td>
                                        <td><?= date('M j, Y', strtotime($site['link_date'])) ?></td>
                                        <td>
                                            <?php if (!empty($site['notes'])): ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary mb-1" 
                                                        data-bs-toggle="tooltip" data-bs-placement="top" 
                                                        title="<?= htmlspecialchars($site['notes']) ?>">
                                                    <i class="bi bi-info-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#unlinkModal<?= $site['id'] ?>">
                                                <i class="bi bi-unlink"></i>
                                            </button>
                                            
                                            <!-- Unlink Confirmation Modal -->
                                            <div class="modal fade" id="unlinkModal<?= $site['id'] ?>" tabindex="-1" aria-labelledby="unlinkModalLabel<?= $site['id'] ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="unlinkModalLabel<?= $site['id'] ?>">Confirm Unlink</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Are you sure you want to unlink <strong><?= htmlspecialchars($site['name']) ?></strong> from this project?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <form action="/projects/unlinkSite/<?= $project->id ?>/<?= $site['id'] ?>" method="post">
                                                                <button type="submit" class="btn btn-danger">Unlink</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
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
</div>

<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script> 