<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Team Time Tracking</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="/time">Time Tracking</a></li>
                        <li class="breadcrumb-item active">Team View</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Selection -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="<?php echo $selected_date; ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>View Date
                            </button>
                            <a href="/time/team" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-calendar-day me-2"></i>Today
                            </a>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-outline-info" onclick="refreshTeamData()">
                                <i class="fas fa-sync-alt me-2"></i>Refresh
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Summary Cards -->
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-2">Total Members</h4>
                            <p class="card-text mb-0">
                                <span class="fs-4 fw-bold text-primary"><?php echo count($team_summary); ?></span>
                            </p>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-users fa-2x text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-2">Currently Working</h4>
                            <p class="card-text mb-0">
                                <span class="fs-4 fw-bold text-success">
                                    <?php 
                                    $workingCount = 0;
                                    foreach ($team_summary as $member) {
                                        if ($member['status'] === 'active') {
                                            $workingCount++;
                                        }
                                    }
                                    echo $workingCount;
                                    ?>
                                </span>
                            </p>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-play fa-2x text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-2">Total Hours</h4>
                            <p class="card-text mb-0">
                                <span class="fs-4 fw-bold text-info">
                                    <?php 
                                    $totalHours = 0;
                                    foreach ($team_summary as $member) {
                                        $totalHours += $member['total_hours'] ?? 0;
                                    }
                                    echo number_format($totalHours, 1);
                                    ?>
                                </span>
                            </p>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-clock fa-2x text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-2">Average Hours</h4>
                            <p class="card-text mb-0">
                                <span class="fs-4 fw-bold text-warning">
                                    <?php 
                                    $completedEntries = array_filter($team_summary, function($member) {
                                        return ($member['total_hours'] ?? 0) > 0;
                                    });
                                    $avgHours = count($completedEntries) > 0 ? $totalHours / count($completedEntries) : 0;
                                    echo number_format($avgHours, 1);
                                    ?>
                                </span>
                            </p>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-chart-line fa-2x text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Members Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Team Time Summary - <?php echo date('M d, Y', strtotime($selected_date)); ?></h4>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" onclick="exportTeamData()">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($team_summary)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Status</th>
                                        <th>Clock In</th>
                                        <th>Clock Out</th>
                                        <th>Work Hours</th>
                                        <th>Break Time</th>
                                        <th>Net Hours</th>
                                        <th>Progress</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($team_summary as $member): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary-light rounded-circle p-2 me-3">
                                                        <i class="fas fa-user text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo $member['full_name']; ?></strong>
                                                        <br>
                                                        <small class="text-muted">@<?php echo $member['username']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                $statusClass = '';
                                                $statusText = '';
                                                switch ($member['status']) {
                                                    case 'active':
                                                        $statusClass = 'success';
                                                        $statusText = 'Working';
                                                        break;
                                                    case 'completed':
                                                        $statusClass = 'info';
                                                        $statusText = 'Completed';
                                                        break;
                                                    default:
                                                        $statusClass = 'secondary';
                                                        $statusText = 'Not Started';
                                                }
                                                ?>
                                                <span class="badge badge-<?php echo $statusClass; ?>">
                                                    <?php echo $statusText; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($member['clock_in_time']): ?>
                                                    <span class="badge badge-success">
                                                        <?php echo date('h:i A', strtotime($member['clock_in_time'])); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">Not clocked in</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($member['clock_out_time']): ?>
                                                    <span class="badge badge-danger">
                                                        <?php echo date('h:i A', strtotime($member['clock_out_time'])); ?>
                                                    </span>
                                                <?php elseif ($member['status'] === 'active'): ?>
                                                    <span class="badge badge-warning">In Progress</span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($member['total_hours']): ?>
                                                    <strong><?php echo number_format($member['total_hours'], 2); ?> hrs</strong>
                                                <?php elseif ($member['status'] === 'active'): ?>
                                                    <span class="text-primary">
                                                        <?php 
                                                        $elapsed = $member['net_work_minutes'] ?? 0;
                                                        echo sprintf('%02d:%02d', floor($elapsed / 60), $elapsed % 60);
                                                        ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">0.00 hrs</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="text-warning">
                                                    <?php 
                                                    $breakMins = $member['total_break_minutes'] ?? 0;
                                                    echo sprintf('%02d:%02d', floor($breakMins / 60), $breakMins % 60);
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($member['total_hours']): ?>
                                                    <?php 
                                                    $netHours = $member['total_hours'] - (($member['total_break_minutes'] ?? 0) / 60);
                                                    echo '<strong class="text-primary">' . number_format(max(0, $netHours), 2) . ' hrs</strong>';
                                                    ?>
                                                <?php elseif ($member['status'] === 'active'): ?>
                                                    <span class="text-primary">
                                                        <?php 
                                                        $netMins = max(0, ($member['net_work_minutes'] ?? 0));
                                                        echo number_format($netMins / 60, 2) . ' hrs';
                                                        ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">0.00 hrs</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $targetHours = 8; // 8-hour workday
                                                $currentHours = $member['total_hours'] ?? 0;
                                                if ($member['status'] === 'active' && !$currentHours) {
                                                    $currentHours = max(0, ($member['net_work_minutes'] ?? 0)) / 60;
                                                }
                                                $progressPercent = min(100, ($currentHours / $targetHours) * 100);
                                                $progressClass = $progressPercent >= 100 ? 'success' : ($progressPercent >= 75 ? 'info' : ($progressPercent >= 50 ? 'warning' : 'danger'));
                                                ?>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-<?php echo $progressClass; ?>" 
                                                         role="progressbar" 
                                                         style="width: <?php echo $progressPercent; ?>%"
                                                         title="<?php echo number_format($progressPercent, 1); ?>% of 8 hours">
                                                        <?php echo number_format($progressPercent, 0); ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-info" onclick="viewMemberDetails(<?php echo $member['user_id']; ?>)" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="/time/reports?user_id=<?php echo $member['user_id']; ?>&start_date=<?php echo $selected_date; ?>&end_date=<?php echo $selected_date; ?>" 
                                                       class="btn btn-outline-primary" title="View Reports">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Team Data Found</h5>
                            <p class="text-muted">No time tracking data found for the selected date.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Member Details Modal -->
<div class="modal fade" id="memberDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Member Time Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="memberDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.bg-primary-light { 
    background-color: rgba(13, 110, 253, 0.1); 
}
.progress {
    background-color: #f8f9fa;
}
.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}
</style>

<script>
// Refresh team data
function refreshTeamData() {
    location.reload();
}

// Export team data
function exportTeamData() {
    const date = document.getElementById('date').value;
    window.open(`/time/export?start_date=${date}&end_date=${date}`, '_blank');
}

// View member details
function viewMemberDetails(userId) {
    const selectedDate = '<?php echo $selected_date; ?>';
    
    // Show loading state
    document.getElementById('memberDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading member details...</p>
        </div>
    `;
    
    $('#memberDetailsModal').modal('show');
    
    // Fetch member details (you'd implement this endpoint)
    fetch(`/time/getMemberDetails?user_id=${userId}&date=${selectedDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('memberDetailsContent').innerHTML = data.html;
            } else {
                document.getElementById('memberDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        Error loading member details: ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('memberDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    Error loading member details. Please try again.
                </div>
            `;
        });
}

// Auto-refresh every 5 minutes for live updates
setInterval(function() {
    const currentDate = document.getElementById('date').value;
    const today = new Date().toISOString().split('T')[0];
    
    // Only auto-refresh if viewing today's data
    if (currentDate === today) {
        refreshTeamData();
    }
}, 300000); // 5 minutes

// Set default date to today
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    const dateInput = document.getElementById('date');
    
    // Set max date to today
    dateInput.max = today;
});
</script> 