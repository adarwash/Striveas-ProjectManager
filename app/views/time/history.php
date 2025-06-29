<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Time History</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="/time">Time Tracking</a></li>
                        <li class="breadcrumb-item active">History</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                                <a href="/time/history" class="btn btn-outline-secondary">
                                    <i class="fas fa-refresh me-2"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                    <h5 class="card-title">Total Days</h5>
                    <h3 class="text-primary"><?php echo count($time_entries); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-success mb-2"></i>
                    <h5 class="card-title">Total Hours</h5>
                    <h3 class="text-success"><?php echo number_format($total_hours, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                    <h5 class="card-title">Average/Day</h5>
                    <h3 class="text-info">
                        <?php echo count($time_entries) > 0 ? number_format($total_hours / count($time_entries), 2) : '0.00'; ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-coffee fa-2x text-warning mb-2"></i>
                    <h5 class="card-title">Break Time</h5>
                    <h3 class="text-warning">
                        <?php 
                        $totalBreakMinutes = array_sum(array_column($time_entries, 'total_break_minutes'));
                        echo sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);
                        ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Entries Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Time Entries</h4>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" onclick="exportData()">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($time_entries)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Clock In</th>
                                        <th>Clock Out</th>
                                        <th>Work Hours</th>
                                        <th>Break Time</th>
                                        <th>Net Hours</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($time_entries as $entry): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo date('M d, Y', strtotime($entry['clock_in_time'])); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo date('l', strtotime($entry['clock_in_time'])); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">
                                                    <?php echo date('h:i A', strtotime($entry['clock_in_time'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($entry['clock_out_time']): ?>
                                                    <span class="badge badge-danger">
                                                        <?php echo date('h:i A', strtotime($entry['clock_out_time'])); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">In Progress</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($entry['total_hours']): ?>
                                                    <strong><?php echo number_format($entry['total_hours'], 2); ?> hrs</strong>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="text-warning">
                                                    <?php 
                                                    $breakMins = $entry['total_break_minutes'] ?? 0;
                                                    echo sprintf('%02d:%02d', floor($breakMins / 60), $breakMins % 60);
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($entry['total_hours']): ?>
                                                    <?php 
                                                    $netHours = $entry['total_hours'] - (($entry['total_break_minutes'] ?? 0) / 60);
                                                    echo '<strong class="text-primary">' . number_format(max(0, $netHours), 2) . ' hrs</strong>';
                                                    ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $entry['status'] === 'completed' ? 'success' : ($entry['status'] === 'active' ? 'primary' : 'secondary'); ?>">
                                                    <?php echo ucfirst($entry['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-info" onclick="viewDetails(<?php echo $entry['id']; ?>)" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if (!empty($entry['breaks'])): ?>
                                                        <button class="btn btn-outline-warning" onclick="viewBreaks(<?php echo $entry['id']; ?>)" title="View Breaks">
                                                            <i class="fas fa-coffee"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clock fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Time Entries Found</h5>
                            <p class="text-muted">No time entries found for the selected date range.</p>
                            <a href="/time" class="btn btn-primary">
                                <i class="fas fa-clock me-2"></i>Start Tracking Time
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Time Entry Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Time Entry Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailsContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Breaks Modal -->
<div class="modal fade" id="breaksModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Break Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="breaksContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// View time entry details
function viewDetails(entryId) {
    const entry = <?php echo json_encode($time_entries); ?>.find(e => e.id == entryId);
    
    if (!entry) return;
    
    const content = `
        <div class="row">
            <div class="col-md-6">
                <h6>Date & Time</h6>
                <p><strong>Date:</strong> ${new Date(entry.clock_in_time).toLocaleDateString()}</p>
                <p><strong>Clock In:</strong> ${new Date(entry.clock_in_time).toLocaleTimeString()}</p>
                <p><strong>Clock Out:</strong> ${entry.clock_out_time ? new Date(entry.clock_out_time).toLocaleTimeString() : 'Not clocked out'}</p>
            </div>
            <div class="col-md-6">
                <h6>Time Summary</h6>
                <p><strong>Total Hours:</strong> ${entry.total_hours ? entry.total_hours + ' hrs' : 'N/A'}</p>
                <p><strong>Break Time:</strong> ${Math.floor((entry.total_break_minutes || 0) / 60)}:${String((entry.total_break_minutes || 0) % 60).padStart(2, '0')}</p>
                <p><strong>Status:</strong> <span class="badge badge-${entry.status === 'completed' ? 'success' : 'primary'}">${entry.status}</span></p>
            </div>
        </div>
        ${entry.notes ? `<div class="mt-3"><h6>Notes</h6><p class="text-muted">${entry.notes}</p></div>` : ''}
    `;
    
    document.getElementById('detailsContent').innerHTML = content;
    $('#detailsModal').modal('show');
}

// View breaks for time entry
function viewBreaks(entryId) {
    const entry = <?php echo json_encode($time_entries); ?>.find(e => e.id == entryId);
    
    if (!entry || !entry.breaks || entry.breaks.length === 0) {
        document.getElementById('breaksContent').innerHTML = '<p class="text-muted">No breaks recorded for this time entry.</p>';
        $('#breaksModal').modal('show');
        return;
    }
    
    let content = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Type</th><th>Start</th><th>End</th><th>Duration</th></tr></thead><tbody>';
    
    entry.breaks.forEach(breakItem => {
        const duration = breakItem.break_duration_minutes || 0;
        content += `
            <tr>
                <td><span class="badge badge-secondary">${breakItem.break_type}</span></td>
                <td>${new Date(breakItem.break_start).toLocaleTimeString()}</td>
                <td>${breakItem.break_end ? new Date(breakItem.break_end).toLocaleTimeString() : 'Ongoing'}</td>
                <td>${Math.floor(duration / 60)}:${String(duration % 60).padStart(2, '0')}</td>
            </tr>
        `;
    });
    
    content += '</tbody></table></div>';
    
    document.getElementById('breaksContent').innerHTML = content;
    $('#breaksModal').modal('show');
}

// Export data
function exportData() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    let url = '/time/export?';
    if (startDate) url += `start_date=${startDate}&`;
    if (endDate) url += `end_date=${endDate}&`;
    
    window.open(url, '_blank');
}

// Set default dates on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set max date to today for both date inputs
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').max = today;
    document.getElementById('end_date').max = today;
});
</script> 