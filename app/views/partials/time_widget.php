<?php
// Get user's current time tracking status
if (isset($_SESSION['user_id'])) {
    try {
        $timeModel = new TimeTracking();
        $userStatus = $timeModel->getUserStatus($_SESSION['user_id']);
        $todaySummary = $timeModel->getDailySummary($_SESSION['user_id']);
    } catch (Exception $e) {
        // Time tracking not available
        $userStatus = null;
        $todaySummary = null;
    }
} else {
    $userStatus = null;
    $todaySummary = null;
}
?>

<?php if ($userStatus): ?>
<!-- Time Tracking Widget -->
<div class="card border-0 shadow-sm mb-4" id="timeWidget">
    <div class="card-header bg-gradient-primary text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="fas fa-clock me-2"></i>Time Tracking
            </h6>
            <div class="d-flex align-items-center">
                <span class="badge bg-light text-dark" id="widgetCurrentTime"><?php echo date('H:i:s'); ?></span>
                <a href="/time" class="btn btn-sm btn-outline-light ms-2">
                    <i class="fas fa-expand-alt"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="card-body p-3">
        <div class="row g-3">
            <!-- Current Status -->
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <?php if ($userStatus['status'] === 'clocked_in'): ?>
                            <div class="bg-success-light rounded-circle p-2">
                                <i class="fas fa-play text-success"></i>
                            </div>
                        <?php elseif ($userStatus['status'] === 'on_break'): ?>
                            <div class="bg-warning-light rounded-circle p-2">
                                <i class="fas fa-pause text-warning"></i>
                            </div>
                        <?php else: ?>
                            <div class="bg-secondary-light rounded-circle p-2">
                                <i class="fas fa-stop text-secondary"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <small class="text-muted d-block">Status</small>
                        <strong class="d-block"><?php echo ucfirst(str_replace('_', ' ', $userStatus['status'])); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Today's Time -->
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="bg-info-light rounded-circle p-2">
                            <i class="fas fa-stopwatch text-info"></i>
                        </div>
                    </div>
                    <div>
                        <small class="text-muted d-block">Today</small>
                        <strong class="d-block" id="widgetTodayHours">
                            <?php echo $todaySummary ? number_format($todaySummary['total_hours'] ?? 0, 2) . ' hrs' : '0.00 hrs'; ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Session Timer -->
        <?php if ($userStatus['status'] !== 'clocked_out'): ?>
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <?php if ($userStatus['status'] === 'on_break'): ?>
                            On <?php echo $userStatus['active_break']['break_type']; ?> break
                        <?php else: ?>
                            Working since <?php echo date('h:i A', strtotime($userStatus['time_entry']['clock_in_time'])); ?>
                        <?php endif; ?>
                    </small>
                    <div class="text-end">
                        <div class="fw-bold text-primary" id="widgetSessionTimer">
                            <?php 
                            if ($userStatus['status'] === 'on_break') {
                                $minutes = $userStatus['break_duration'] ?? 0;
                                echo sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60);
                            } else {
                                $minutes = $userStatus['elapsed_work_time'] ?? 0;
                                echo sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="mt-3 pt-3 border-top">
            <div class="d-flex gap-2">
                <?php if ($userStatus['status'] === 'clocked_out'): ?>
                    <button class="btn btn-success btn-sm flex-fill" onclick="widgetClockIn()">
                        <i class="fas fa-play me-1"></i>Clock In
                    </button>
                <?php elseif ($userStatus['status'] === 'clocked_in'): ?>
                    <button class="btn btn-warning btn-sm" onclick="widgetStartBreak()">
                        <i class="fas fa-pause me-1"></i>Break
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="widgetClockOut()">
                        <i class="fas fa-stop me-1"></i>Clock Out
                    </button>
                <?php elseif ($userStatus['status'] === 'on_break'): ?>
                    <button class="btn btn-success btn-sm flex-fill" onclick="widgetEndBreak()">
                        <i class="fas fa-play me-1"></i>End Break
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="widgetClockOut()">
                        <i class="fas fa-stop me-1"></i>Clock Out
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.bg-success-light { background-color: rgba(25, 135, 84, 0.1); }
.bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
.bg-secondary-light { background-color: rgba(108, 117, 125, 0.1); }
.bg-info-light { background-color: rgba(13, 202, 240, 0.1); }
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}
</style>

<script>
// Widget functionality
let widgetUpdateInterval = null;

// Update widget time
function updateWidgetTime() {
    const now = new Date();
    document.getElementById('widgetCurrentTime').textContent = now.toLocaleTimeString();
}

// Update widget session timer
function updateWidgetSession() {
    fetch('/time/getStatus')
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'clocked_out') {
                const sessionTimer = document.getElementById('widgetSessionTimer');
                if (sessionTimer) {
                    let minutes = 0;
                    if (data.status === 'on_break' && data.break_duration) {
                        minutes = data.break_duration;
                    } else if (data.elapsed_work_time) {
                        minutes = data.elapsed_work_time;
                    }
                    
                    const hours = Math.floor(minutes / 60);
                    const mins = minutes % 60;
                    sessionTimer.textContent = String(hours).padStart(2, '0') + ':' + String(mins).padStart(2, '0');
                }
            }
        })
        .catch(error => console.error('Widget update error:', error));
}

// Widget clock in
function widgetClockIn() {
    fetch('/time/clockIn', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: ''
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showWidgetAlert('success', 'Clocked in successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showWidgetAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Widget clock in error:', error);
        showWidgetAlert('danger', 'An error occurred');
    });
}

// Widget clock out
function widgetClockOut() {
    if (confirm('Are you sure you want to clock out?')) {
        fetch('/time/clockOut', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: ''
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showWidgetAlert('success', 'Clocked out successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showWidgetAlert('danger', data.message);
            }
        })
        .catch(error => {
            console.error('Widget clock out error:', error);
            showWidgetAlert('danger', 'An error occurred');
        });
    }
}

// Widget start break
function widgetStartBreak() {
    fetch('/time/startBreak', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'break_type=regular'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showWidgetAlert('success', 'Break started');
            setTimeout(() => location.reload(), 1000);
        } else {
            showWidgetAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Widget start break error:', error);
        showWidgetAlert('danger', 'An error occurred');
    });
}

// Widget end break
function widgetEndBreak() {
    fetch('/time/endBreak', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: ''
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showWidgetAlert('success', 'Break ended');
            setTimeout(() => location.reload(), 1000);
        } else {
            showWidgetAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Widget end break error:', error);
        showWidgetAlert('danger', 'An error occurred');
    });
}

// Show widget alert
function showWidgetAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}

// Initialize widget timers
document.addEventListener('DOMContentLoaded', function() {
    updateWidgetTime();
    setInterval(updateWidgetTime, 1000);
    
    updateWidgetSession();
    widgetUpdateInterval = setInterval(updateWidgetSession, 60000); // Update every minute
});
</script>
<?php endif; ?> 