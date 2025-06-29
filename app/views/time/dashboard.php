<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Time Tracking</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Time Tracking</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Status Cards -->
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-2">Current Status</h4>
                            <p class="card-text mb-0">
                                <span class="badge badge-<?php echo $user_status['status'] === 'clocked_in' ? 'success' : ($user_status['status'] === 'on_break' ? 'warning' : 'secondary'); ?> fs-6">
                                    <?php echo ucfirst(str_replace('_', ' ', $user_status['status'])); ?>
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
                            <h4 class="card-title mb-2">Today's Hours</h4>
                            <p class="card-text mb-0">
                                <span class="fs-4 fw-bold text-primary" id="todayHours">
                                    <?php echo $today_summary ? number_format($today_summary['total_hours'] ?? 0, 2) : '0.00'; ?>
                                </span>
                            </p>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-stopwatch fa-2x text-muted"></i>
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
                            <h4 class="card-title mb-2">Current Session</h4>
                            <p class="card-text mb-0">
                                <span class="fs-5 fw-bold text-info" id="currentSession">
                                    <?php 
                                    if ($user_status['status'] !== 'clocked_out') {
                                        $minutes = $user_status['elapsed_work_time'] ?? 0;
                                        echo sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60);
                                    } else {
                                        echo '00:00';
                                    }
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
                            <h4 class="card-title mb-2">Break Time</h4>
                            <p class="card-text mb-0">
                                <span class="fs-5 fw-bold text-warning" id="breakTime">
                                    <?php 
                                    $breakMinutes = 0;
                                    if ($user_status['status'] === 'on_break' && isset($user_status['break_duration'])) {
                                        $breakMinutes = $user_status['break_duration'];
                                    } elseif ($today_summary && $today_summary['total_break_minutes']) {
                                        $breakMinutes = $today_summary['total_break_minutes'];
                                    }
                                    echo sprintf('%02d:%02d', floor($breakMinutes / 60), $breakMinutes % 60);
                                    ?>
                                </span>
                            </p>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-pause fa-2x text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Control Panel -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Time Control</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Clock In/Out Section -->
                        <div class="col-md-6">
                            <div class="text-center p-3 border rounded">
                                <h5 class="mb-3">Clock In/Out</h5>
                                <div class="mb-3">
                                    <i class="fas fa-clock fa-3x text-primary"></i>
                                </div>
                                <div class="mb-3">
                                    <span class="fs-4 fw-bold" id="currentTime"><?php echo date('H:i:s'); ?></span>
                                </div>
                                
                                <?php if ($user_status['status'] === 'clocked_out'): ?>
                                    <button class="btn btn-success btn-lg" onclick="clockIn()">
                                        <i class="fas fa-play me-2"></i>Clock In
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-danger btn-lg" onclick="clockOut()">
                                        <i class="fas fa-stop me-2"></i>Clock Out
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Break Control Section -->
                        <div class="col-md-6">
                            <div class="text-center p-3 border rounded">
                                <h5 class="mb-3">Break Control</h5>
                                <div class="mb-3">
                                    <i class="fas fa-pause fa-3x text-warning"></i>
                                </div>
                                
                                <?php if ($user_status['status'] === 'clocked_in'): ?>
                                    <div class="mb-3">
                                        <select class="form-select" id="breakType">
                                            <?php foreach ($break_types as $type): ?>
                                                <option value="<?php echo $type['name']; ?>"><?php echo $type['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button class="btn btn-warning btn-lg" onclick="startBreak()">
                                        <i class="fas fa-pause me-2"></i>Start Break
                                    </button>
                                <?php elseif ($user_status['status'] === 'on_break'): ?>
                                    <div class="mb-3">
                                        <span class="badge badge-warning fs-6">On <?php echo $user_status['active_break']['break_type']; ?> Break</span>
                                    </div>
                                    <button class="btn btn-success btn-lg" onclick="endBreak()">
                                        <i class="fas fa-play me-2"></i>End Break
                                    </button>
                                <?php else: ?>
                                    <div class="mb-3">
                                        <span class="text-muted">Clock in to take breaks</span>
                                    </div>
                                    <button class="btn btn-warning btn-lg" disabled>
                                        <i class="fas fa-pause me-2"></i>Start Break
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Quick Actions</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/time/history" class="btn btn-outline-primary">
                            <i class="fas fa-history me-2"></i>View History
                        </a>
                        <a href="/time/team" class="btn btn-outline-info">
                            <i class="fas fa-users me-2"></i>Team View
                        </a>
                        <a href="/time/reports" class="btn btn-outline-success">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Time Entries -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Recent Time Entries</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_entries)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Clock In</th>
                                        <th>Clock Out</th>
                                        <th>Total Hours</th>
                                        <th>Break Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_entries as $entry): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($entry['clock_in_time'])); ?></td>
                                            <td><?php echo date('h:i A', strtotime($entry['clock_in_time'])); ?></td>
                                            <td><?php echo $entry['clock_out_time'] ? date('h:i A', strtotime($entry['clock_out_time'])) : 'Not clocked out'; ?></td>
                                            <td><?php echo $entry['total_hours'] ? number_format($entry['total_hours'], 2) . ' hrs' : 'N/A'; ?></td>
                                            <td><?php echo $entry['total_break_minutes'] ? sprintf('%02d:%02d', floor($entry['total_break_minutes'] / 60), $entry['total_break_minutes'] % 60) : '00:00'; ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $entry['status'] === 'completed' ? 'success' : ($entry['status'] === 'active' ? 'primary' : 'secondary'); ?>">
                                                    <?php echo ucfirst($entry['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No time entries found. Click "Clock In" to start tracking your time.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="notesForm">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (optional)</label>
                        <textarea class="form-control" id="notes" rows="3" placeholder="Enter any notes about your work session..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitWithNotes()">Continue</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentAction = null;
let updateInterval = null;

// Update current time every second
function updateCurrentTime() {
    const now = new Date();
    document.getElementById('currentTime').textContent = now.toLocaleTimeString();
}

// Update session time every minute
function updateSessionTime() {
    fetch('/time/getStatus')
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'clocked_out' && data.elapsed_work_time) {
                const minutes = data.elapsed_work_time;
                const hours = Math.floor(minutes / 60);
                const mins = minutes % 60;
                document.getElementById('currentSession').textContent = 
                    String(hours).padStart(2, '0') + ':' + String(mins).padStart(2, '0');
            }
        })
        .catch(error => console.error('Error updating session time:', error));
}

// Start timers
function startTimers() {
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);
    
    updateSessionTime();
    updateInterval = setInterval(updateSessionTime, 60000); // Update every minute
}

// Clock In
function clockIn() {
    currentAction = 'clockIn';
    $('#notesModal').modal('show');
}

// Clock Out
function clockOut() {
    currentAction = 'clockOut';
    $('#notesModal').modal('show');
}

// Start Break
function startBreak() {
    const breakType = document.getElementById('breakType').value;
    
    fetch('/time/startBreak', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `break_type=${encodeURIComponent(breakType)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
    });
}

// End Break
function endBreak() {
    fetch('/time/endBreak', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: ''
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
    });
}

// Submit with notes
function submitWithNotes() {
    const notes = document.getElementById('notes').value;
    const endpoint = currentAction === 'clockIn' ? '/time/clockIn' : '/time/clockOut';
    
    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `notes=${encodeURIComponent(notes)}`
    })
    .then(response => response.json())
    .then(data => {
        $('#notesModal').modal('hide');
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
    });
}

// Show alert
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.container-fluid').prepend(alertDiv);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    startTimers();
    
    // Clear notes when modal is hidden
    $('#notesModal').on('hidden.bs.modal', function () {
        document.getElementById('notes').value = '';
        currentAction = null;
    });
});
</script> 