<?php require VIEWSPATH . '/partials/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $data['title'] ?></h1>
    
    <?php flash('activity_message'); ?>
    <?php flash('activity_error'); ?>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i> Filter Activities</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= URLROOT ?>/activities/manage" class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $data['start_date'] ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $data['end_date'] ?>">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="" <?= !$data['status'] ? 'selected' : '' ?>>All Statuses</option>
                        <option value="Pending" <?= $data['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Approved" <?= $data['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="Rejected" <?= $data['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Apply Filter
                    </button>
                    <button type="reset" class="btn btn-outline-secondary ms-2">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Activity Management Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list-check me-2"></i> Employee Activities</h5>
        </div>
        <div class="card-body">
            <?php if (empty($data['activities'])): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i> No activity data found for the selected period or status.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="activitiesTable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Hours</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($data['activities'] as $activity): ?>
                            <tr>
                                <td><?= htmlspecialchars($activity['full_name'] ?? $activity['user_name']) ?></td>
                                <td><?= date('M d (D)', strtotime($activity['activity_date'])) ?></td>
                                <td><?= date('H:i:s', strtotime($activity['check_in'])) ?></td>
                                <td>
                                    <?= $activity['check_out'] ? date('H:i:s', strtotime($activity['check_out'])) : '<span class="badge bg-warning">Active</span>' ?>
                                </td>
                                <td>
                                    <?= $activity['total_hours'] ? number_format($activity['total_hours'], 1) . ' hrs' : '-' ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-link description-preview" data-bs-toggle="modal" data-bs-target="#descriptionModal" data-description="<?= htmlspecialchars($activity['description'] ?? '') ?>" data-id="<?= $activity['id'] ?>" data-user="<?= htmlspecialchars($activity['full_name'] ?? $activity['user_name']) ?>">
                                        <?php 
                                            $desc = $activity['description'] ?? '';
                                            echo !empty($desc) ? (strlen($desc) > 30 ? substr($desc, 0, 30) . '...' : $desc) : '<span class="text-muted">No description</span>';
                                        ?>
                                    </button>
                                </td>
                                <td>
                                    <?php if($activity['status'] === 'Approved'): ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php elseif($activity['status'] === 'Rejected'): ?>
                                        <span class="badge bg-danger">Rejected</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="actionDropdown<?= $activity['id'] ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="actionDropdown<?= $activity['id'] ?>">
                                            <?php if($activity['status'] !== 'Approved'): ?>
                                            <li>
                                                <form action="<?= URLROOT ?>/activities/updateStatus/<?= $activity['id'] ?>/Approved" method="POST">
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="bi bi-check-circle me-1"></i> Approve
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                            
                                            <?php if($activity['status'] !== 'Rejected'): ?>
                                            <li>
                                                <form action="<?= URLROOT ?>/activities/updateStatus/<?= $activity['id'] ?>/Rejected" method="POST">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-x-circle me-1"></i> Reject
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                            
                                            <?php if($activity['status'] !== 'Pending'): ?>
                                            <li>
                                                <form action="<?= URLROOT ?>/activities/updateStatus/<?= $activity['id'] ?>/Pending" method="POST">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Pending
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
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

<!-- View Description Modal -->
<div class="modal fade" id="descriptionModal" tabindex="-1" aria-labelledby="descriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="descriptionModalLabel">Activity Description</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Employee:</strong> <span id="modalUserName"></span>
                </div>
                <div id="descriptionContent" class="p-3 bg-light rounded">
                    <p class="text-muted">No description available.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <div class="btn-group" id="actionButtons">
                    <button type="button" class="btn btn-success" id="approveBtn"><i class="bi bi-check-circle me-1"></i> Approve</button>
                    <button type="button" class="btn btn-danger" id="rejectBtn"><i class="bi bi-x-circle me-1"></i> Reject</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Initialize DataTable for easier management -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTable
        const table = new DataTable('#activitiesTable', {
            order: [[1, 'desc'], [2, 'desc']], // Sort by date (desc) and then check-in time (desc)
            pageLength: 25,
            language: {
                search: "Search employees/activities:"
            }
        });
        
        // Handle showing description in modal
        document.querySelectorAll('.description-preview').forEach(function(button) {
            button.addEventListener('click', function() {
                const description = this.getAttribute('data-description');
                const activityId = this.getAttribute('data-id');
                const userName = this.getAttribute('data-user');
                
                document.getElementById('modalUserName').textContent = userName;
                
                const descriptionContent = document.getElementById('descriptionContent');
                if (description && description.trim().length > 0) {
                    descriptionContent.innerHTML = '<p>' + description.replace(/\n/g, '<br>') + '</p>';
                } else {
                    descriptionContent.innerHTML = '<p class="text-muted">No description available.</p>';
                }
                
                // Set up action buttons
                const approveBtn = document.getElementById('approveBtn');
                const rejectBtn = document.getElementById('rejectBtn');
                
                approveBtn.onclick = function() {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?= URLROOT ?>/activities/updateStatus/' + activityId + '/Approved';
                    document.body.appendChild(form);
                    form.submit();
                };
                
                rejectBtn.onclick = function() {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?= URLROOT ?>/activities/updateStatus/' + activityId + '/Rejected';
                    document.body.appendChild(form);
                    form.submit();
                };
            });
        });
    });
</script>

<?php require VIEWSPATH . '/partials/footer.php'; ?> 