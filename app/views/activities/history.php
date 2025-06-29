<?php require VIEWSPATH . '/partials/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Activity History</h1>
    
    <?php flash('activity_message'); ?>
    <?php flash('activity_error'); ?>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i> Filter Activities</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= URLROOT ?>/activities/history" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $data['start_date'] ?>">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $data['end_date'] ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Apply Filter
                    </button>
                    <a href="<?= URLROOT ?>/activities" class="btn btn-outline-secondary ms-2">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Summary Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i> Summary (<?= date('M d, Y', strtotime($data['start_date'])) ?> - <?= date('M d, Y', strtotime($data['end_date'])) ?>)</h5>
        </div>
        <div class="card-body">
            <?php if (empty($data['summary'])): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i> No activity data found for the selected period.
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Hours</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($data['total_hours'], 1) ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-clock-history fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Work Days</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($data['summary']) ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-calendar-check fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Avg Hours Per Day</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?= number_format($data['total_hours'] / count($data['summary']), 1) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-calculator fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Total Activities</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($data['activities']) ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-list-check fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Daily Summary Chart -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <canvas id="dailyHoursChart"></canvas>
                    </div>
                </div>
                
                <!-- Chart Script -->
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('dailyHoursChart').getContext('2d');
                        
                        // Extract data for chart
                        const dates = [];
                        const hours = [];
                        
                        <?php foreach($data['summary'] as $day): ?>
                            dates.push('<?= date('M d', strtotime($day['activity_date'])) ?>');
                            hours.push(<?= floatval($day['total_hours']) ?>);
                        <?php endforeach; ?>
                        
                        const dailyHoursChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: dates,
                                datasets: [{
                                    label: 'Daily Hours',
                                    data: hours,
                                    backgroundColor: 'rgba(78, 115, 223, 0.5)',
                                    borderColor: 'rgba(78, 115, 223, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: 'Hours'
                                        }
                                    }
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: 'Daily Hours Summary'
                                    }
                                }
                            }
                        });
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Detailed Activity List -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i> Detailed Activity Log</h5>
        </div>
        <div class="card-body">
            <?php if (empty($data['activities'])): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i> No activity data found for the selected period.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="activitiesTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Duration</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($data['activities'] as $activity): ?>
                            <tr>
                                <td><?= date('M d (D)', strtotime($activity['activity_date'])) ?></td>
                                <td><?= date('H:i:s', strtotime($activity['check_in'])) ?></td>
                                <td>
                                    <?= $activity['check_out'] ? date('H:i:s', strtotime($activity['check_out'])) : '<span class="badge bg-warning">Active</span>' ?>
                                </td>
                                <td>
                                    <?= $activity['total_hours'] ? number_format($activity['total_hours'], 1) . ' hrs' : '-' ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-link description-preview" data-bs-toggle="modal" data-bs-target="#descriptionModal" data-description="<?= htmlspecialchars($activity['description'] ?? '') ?>" data-id="<?= $activity['id'] ?>">
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
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-description" data-bs-toggle="modal" data-bs-target="#editDescriptionModal" data-id="<?= $activity['id'] ?>" data-description="<?= htmlspecialchars($activity['description'] ?? '') ?>">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
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
                <div id="descriptionContent" class="p-3 bg-light rounded">
                    <p class="text-muted">No description available.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary edit-btn" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#editDescriptionModal">Edit</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Description Modal -->
<div class="modal fade" id="editDescriptionModal" tabindex="-1" aria-labelledby="editDescriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDescriptionModalLabel">Edit Activity Description</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateDescriptionForm" action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle showing description in modal
        document.querySelectorAll('.description-preview').forEach(function(button) {
            button.addEventListener('click', function() {
                const description = this.getAttribute('data-description');
                const activityId = this.getAttribute('data-id');
                
                const descriptionContent = document.getElementById('descriptionContent');
                if (description && description.trim().length > 0) {
                    descriptionContent.innerHTML = '<p>' + description.replace(/\n/g, '<br>') + '</p>';
                } else {
                    descriptionContent.innerHTML = '<p class="text-muted">No description available.</p>';
                }
                
                // Set activity ID for edit button
                const editBtn = document.querySelector('.edit-btn');
                editBtn.setAttribute('data-id', activityId);
                editBtn.setAttribute('data-description', description);
            });
        });
        
        // Handle edit button in view modal
        document.querySelector('.edit-btn').addEventListener('click', function() {
            const activityId = this.getAttribute('data-id');
            const description = this.getAttribute('data-description');
            
            document.getElementById('edit_description').value = description;
            document.getElementById('updateDescriptionForm').action = '<?= URLROOT ?>/activities/updateDescription/' + activityId;
        });
        
        // Handle edit description buttons
        document.querySelectorAll('.edit-description').forEach(function(button) {
            button.addEventListener('click', function() {
                const activityId = this.getAttribute('data-id');
                const description = this.getAttribute('data-description');
                
                document.getElementById('edit_description').value = description;
                document.getElementById('updateDescriptionForm').action = '<?= URLROOT ?>/activities/updateDescription/' + activityId;
            });
        });
    });
</script>

<?php require VIEWSPATH . '/partials/footer.php'; ?> 