<?php require VIEWSPATH . '/partials/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $data['title'] ?></h1>
    
    <?php flash('activity_message'); ?>
    <?php flash('activity_error'); ?>
    
    <div class="row">
        <!-- Check In/Out Card -->
        <div class="col-xl-6 col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock me-2"></i> Time Tracking</h5>
                    <div>
                        <?php if (isset($data['active_check_in']) && $data['active_check_in']): ?>
                            <span class="badge bg-success me-2">Checked In</span>
                        <?php else: ?>
                            <span class="badge bg-secondary me-2">Not Checked In</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($data['active_check_in']) && $data['active_check_in']): ?>
                        <!-- Currently Checked In -->
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i> You are currently checked in
                            <div class="mt-2">
                                <strong>Check-in time:</strong> <?= date('H:i:s', strtotime($data['active_check_in']['check_in'])) ?>
                                <br>
                                <strong>Duration:</strong> <span id="duration"></span>
                            </div>
                        </div>
                        
                        <!-- Check Out Form -->
                        <form action="<?= URLROOT ?>/activities/checkOut" method="POST">
                            <div class="mb-3">
                                <label for="description" class="form-label">What did you work on today?</label>
                                <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe your activities..."><?= htmlspecialchars($data['active_check_in']['description']) ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-box-arrow-left me-1"></i> Check Out
                            </button>
                        </form>
                        
                        <!-- Timer Script -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const checkInTime = new Date('<?= $data['active_check_in']['check_in'] ?>');
                                
                                function updateDuration() {
                                    const now = new Date();
                                    const diffMs = now - checkInTime;
                                    
                                    // Calculate hours, minutes, seconds
                                    const hours = Math.floor(diffMs / (1000 * 60 * 60));
                                    const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                                    const seconds = Math.floor((diffMs % (1000 * 60)) / 1000);
                                    
                                    // Format as HH:MM:SS
                                    const formattedHours = String(hours).padStart(2, '0');
                                    const formattedMinutes = String(minutes).padStart(2, '0');
                                    const formattedSeconds = String(seconds).padStart(2, '0');
                                    
                                    document.getElementById('duration').textContent = 
                                        `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
                                }
                                
                                // Update every second
                                updateDuration();
                                setInterval(updateDuration, 1000);
                            });
                        </script>
                    <?php else: ?>
                        <!-- Check In Form -->
                        <form action="<?= URLROOT ?>/activities/checkIn" method="POST">
                            <div class="mb-3">
                                <label for="description" class="form-label">What will you be working on today?</label>
                                <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe your planned activities..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Check In
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Monthly Summary Card -->
        <div class="col-xl-6 col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i> Month Summary (<?= date('F Y') ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($data['monthly_summary'])): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> No activity recorded this month yet.
                        </div>
                    <?php else: ?>
                        <div class="d-flex justify-content-between mb-4">
                            <div class="text-center">
                                <h3 class="mb-0"><?= number_format($data['total_monthly_hours'], 1) ?></h3>
                                <small class="text-muted">Total Hours</small>
                            </div>
                            <div class="text-center">
                                <h3 class="mb-0"><?= count($data['monthly_summary']) ?></h3>
                                <small class="text-muted">Work Days</small>
                            </div>
                            <div class="text-center">
                                <h3 class="mb-0"><?= number_format($data['total_monthly_hours'] / count($data['monthly_summary']), 1) ?></h3>
                                <small class="text-muted">Avg Hours/Day</small>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Hours</th>
                                        <th>First In</th>
                                        <th>Last Out</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($data['monthly_summary'] as $day): ?>
                                    <tr>
                                        <td><?= date('M d (D)', strtotime($day['activity_date'])) ?></td>
                                        <td><?= number_format($day['total_hours'], 1) ?></td>
                                        <td><?= date('H:i', strtotime($day['first_check_in'])) ?></td>
                                        <td><?= date('H:i', strtotime($day['last_check_out'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-center mt-3">
                        <a href="<?= URLROOT ?>/activities/history" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-calendar-week me-1"></i> View Full History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activities -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i> Recent Activities</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($data['activities'])): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> No recent activities found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Hours</th>
                                        <th>Description</th>
                                        <th>Status</th>
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
                                            <?= $activity['total_hours'] ? number_format($activity['total_hours'], 1) : '-' ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $desc = htmlspecialchars($activity['description'] ?? '');
                                            echo !empty($desc) ? (strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc) : '<span class="text-muted">No description</span>';
                                            ?>
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
</div>

<?php require VIEWSPATH . '/partials/footer.php'; ?> 