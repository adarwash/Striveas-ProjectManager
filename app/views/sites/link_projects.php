<div class="container-fluid">
    <!-- Page Header -->
    <div class="rounded-3 p-4 mb-4 page-header-solid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/sites" class="text-decoration-none">Sites</a></li>
                <li class="breadcrumb-item"><a href="/sites/viewSite/<?= $site['id'] ?>" class="text-decoration-none"><?= $site['name'] ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Link Projects</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1 class="h4 mb-1 text-dark">Link Projects to <?= $site['name'] ?></h1>
                <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i> <?= $site['location'] ?></div>
            </div>
            <div class="d-flex flex-wrap mt-2 mt-md-0 gap-2">
                <a href="/sites/viewSite/<?= $site['id'] ?>" class="btn btn-light">
                    <i class="bi bi-arrow-left me-1"></i> Back to Site
                </a>
                <a href="/projects/create?site_id=<?= $site['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create New Project
                </a>
            </div>
        </div>
    </div>
    
    <!-- Flash Messages -->
    <?php flash('site_success'); ?>
    <?php flash('site_error'); ?>
    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Select Projects to Link</h5>
            <div class="form-group has-search position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" class="form-control ps-4" placeholder="Search projects..." id="projectSearch">
            </div>
        </div>
        <div class="card-body">
            <form action="/sites/linkProjects/<?= $site['id'] ?>" method="post">
                <?php if (empty($projects)): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-briefcase display-4 text-muted"></i>
                    </div>
                    <h5>No Projects Available</h5>
                    <p class="text-muted">There are no projects to link to this site.</p>
                    <a href="/projects/create?site_id=<?= $site['id'] ?>" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Create New Project
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="projectsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                    </div>
                                </th>
                                <th>Project Name</th>
                                <th>Status</th>
                                <th>Department</th>
                                <th>Timeline</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                            <?php 
                                // Skip if this project is already linked
                                $isLinked = in_array($project->id, $linkedIds);
                                $rowClass = $isLinked ? 'table-secondary' : '';
                            ?>
                            <tr class="<?= $rowClass ?>">
                                <td class="text-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="projects[]" value="<?= $project->id ?>" <?= $isLinked ? 'disabled checked' : '' ?>>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($isLinked): ?>
                                        <span class="badge bg-success me-2">Already Linked</span>
                                        <?php endif; ?>
                                        <a href="/projects/viewProject/<?= $project->id ?>" class="text-decoration-none" target="_blank">
                                            <?= htmlspecialchars($project->title) ?>
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'bg-secondary';
                                    if ($project->status === 'Active') $statusClass = 'bg-success';
                                    if ($project->status === 'On Hold') $statusClass = 'bg-warning';
                                    if ($project->status === 'Completed') $statusClass = 'bg-info';
                                    if ($project->status === 'Cancelled') $statusClass = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $statusClass ?>"><?= $project->status ?></span>
                                </td>
                                <td><?= $project->department_name ?? 'N/A' ?></td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">Start: <?= date('M j, Y', strtotime($project->start_date)) ?></small>
                                        <small class="text-muted">End: <?= date('M j, Y', strtotime($project->end_date)) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!$isLinked): ?>
                                    <input type="text" class="form-control form-control-sm" name="notes[<?= $project->id ?>]" placeholder="Add link note (optional)">
                                    <?php else: ?>
                                    <span class="text-muted">Already linked</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <a href="/sites/viewSite/<?= $site['id'] ?>" class="btn btn-light me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-link me-1"></i> Link Selected Projects
                    </button>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle select all checkbox
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('input[name="projects[]"]:not(:disabled)');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    // Update "select all" if any checkbox changes
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            let allChecked = true;
            checkboxes.forEach(cb => {
                if (!cb.checked) allChecked = false;
            });
            if (selectAll) selectAll.checked = allChecked;
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('projectSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('#projectsTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script> 