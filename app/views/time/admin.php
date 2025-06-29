<?php require VIEWSPATH . '/partials/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">
        <i class="fas fa-users-cog me-2"></i>Time Tracking Admin Dashboard
    </h1>
    
    <!-- Filter Controls -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters & Controls</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $start_date ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $end_date ?>">
                </div>
                <div class="col-md-2">
                    <label for="user_id" class="form-label">User</label>
                    <select class="form-select" id="user_id" name="user_id">
                        <option value="">All Users</option>
                        <?php foreach ($all_users as $user): ?>
                            <option value="<?= $user['user_id'] ?>" <?= $selected_user_id == $user['user_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="department" class="form-label">Role</label>
                    <select class="form-select" id="department" name="department">
                        <option value="">All Roles</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= htmlspecialchars($dept) ?>" <?= $selected_department == $dept ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($dept)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i>Apply Filters
                    </button>
                    <a href="/time/admin" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                    </a>
                    <button type="button" class="btn btn-success" onclick="refreshDashboard()">
                        <i class="bi bi-arrow-repeat me-1"></i>Refresh
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Statistics Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($all_users) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Currently Active</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($currently_active) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-clock fa-2x text-gray-300"></i>
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
                                Total Hours (Period)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($time_statistics['total_hours'] ?? 0, 1) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                Avg Hours/User</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count($all_users) > 0 ? number_format(($time_statistics['total_hours'] ?? 0) / count($all_users), 1) : '0.0' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Currently Active Users -->
    <?php if (!empty($currently_active)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-user-clock me-2"></i>Currently Active Users</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($currently_active as $user): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card border-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="bg-success rounded-circle p-2 me-3">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></h6>
                                    <small class="text-muted">Since <?= date('H:i', strtotime($user['clock_in_time'])) ?></small>
                                    <div class="mt-1">
                                        <?php if ($user['status'] === 'on_break'): ?>
                                            <span class="badge bg-warning">On Break</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Working</span>
                                        <?php endif; ?>
                                        <span class="badge bg-info ms-1">
                                            <?php 
                                            $elapsed = $user['elapsed_minutes'] ?? 0;
                                            echo sprintf('%02d:%02d', floor($elapsed / 60), $elapsed % 60);
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><button class="dropdown-item" onclick="viewUserDetails(<?= $user['user_id'] ?>)">
                                            <i class="fas fa-eye me-2"></i>View Details
                                        </button></li>
                                        <li><button class="dropdown-item text-danger" onclick="adminClockOut(<?= $user['user_id'] ?>, '<?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>')">
                                            <i class="fas fa-stop me-2"></i>Force Clock Out
                                        </button></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- All Users Time Data -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-people me-2"></i>All Users Time Summary</h5>
            <div>
                <button class="btn btn-sm btn-outline-primary" onclick="exportAllData()">
                    <i class="bi bi-download me-1"></i>Export All
                </button>
                <a href="/time/analytics" class="btn btn-sm btn-outline-info ms-1">
                    <i class="fas fa-chart-pie me-1"></i>Analytics
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($all_users)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>No user data found for the selected period.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="usersTable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Status</th>
                                <th>Today's Hours</th>
                                <th>Period Total</th>
                                <th>Break Time</th>
                                <th>Efficiency</th>
                                <th>Last Activity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary rounded-circle p-2 me-3">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></strong>
                                            <br>
                                            <small class="text-muted">@<?= htmlspecialchars($user['username']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'secondary';
                                    $statusText = 'Offline';
                                    $statusIcon = 'stop';
                                    
                                    if ($user['current_status'] === 'clocked_in') {
                                        $statusClass = 'success';
                                        $statusText = 'Working';
                                        $statusIcon = 'play';
                                    } elseif ($user['current_status'] === 'on_break') {
                                        $statusClass = 'warning';
                                        $statusText = 'On Break';
                                        $statusIcon = 'pause';
                                    }
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <i class="fas fa-<?= $statusIcon ?> me-1"></i><?= $statusText ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= number_format($user['today_hours'] ?? 0, 1) ?>h</strong>
                                </td>
                                <td>
                                    <strong><?= number_format($user['period_total_hours'] ?? 0, 1) ?>h</strong>
                                </td>
                                <td>
                                    <?php 
                                    $breakMins = $user['total_break_minutes'] ?? 0;
                                    echo sprintf('%02d:%02d', floor($breakMins / 60), $breakMins % 60);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $totalHours = $user['period_total_hours'] ?? 0;
                                    $breakHours = ($user['total_break_minutes'] ?? 0) / 60;
                                    $efficiency = $totalHours > 0 ? (($totalHours - $breakHours) / $totalHours) * 100 : 0;
                                    $efficiencyClass = $efficiency >= 90 ? 'success' : ($efficiency >= 75 ? 'warning' : 'danger');
                                    ?>
                                    <span class="badge bg-<?= $efficiencyClass ?>">
                                        <?= number_format($efficiency, 0) ?>%
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['last_activity']): ?>
                                        <small><?= date('M d, H:i', strtotime($user['last_activity'])) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">No activity</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewUserDetails(<?= $user['user_id'] ?>)" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="/time/history?user_id=<?= $user['user_id'] ?>" class="btn btn-outline-info" title="View History">
                                            <i class="fas fa-history"></i>
                                        </a>
                                        <?php if ($user['current_status'] !== 'clocked_out'): ?>
                                        <button class="btn btn-outline-danger" onclick="adminClockOut(<?= $user['user_id'] ?>, '<?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>')" title="Force Clock Out">
                                            <i class="fas fa-stop"></i>
                                        </button>
                                        <?php endif; ?>
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
    
    <!-- Recent Activity -->
    <?php if (!empty($recent_activity)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activity</h5>
        </div>
        <div class="card-body">
            <div class="timeline">
                <?php foreach (array_slice($recent_activity, 0, 10) as $activity): ?>
                <div class="timeline-item">
                    <div class="timeline-marker bg-<?= $activity['action'] === 'clock_in' ? 'success' : ($activity['action'] === 'clock_out' ? 'danger' : 'warning') ?>"></div>
                    <div class="timeline-content">
                        <small class="text-muted"><?= date('M d, H:i', strtotime($activity['timestamp'])) ?></small>
                        <p class="mb-0">
                            <strong><?= htmlspecialchars($activity['user_name']) ?></strong>
                            <?php
                            switch ($activity['action']) {
                                case 'clock_in':
                                    echo 'clocked in';
                                    break;
                                case 'clock_out':
                                    echo 'clocked out';
                                    break;
                                case 'break_start':
                                    echo 'started a break';
                                    break;
                                case 'break_end':
                                    echo 'ended break';
                                    break;
                                default:
                                    echo $activity['action'];
                            }
                            ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Time Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Admin Clock Out Modal -->
<div class="modal fade" id="adminClockOutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Force Clock Out User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="adminClockOutForm">
                <div class="modal-body">
                    <p>Are you sure you want to force clock out <strong id="clockOutUserName"></strong>?</p>
                    <div class="mb-3">
                        <label for="clockOutReason" class="form-label">Reason (Optional)</label>
                        <textarea class="form-control" id="clockOutReason" name="reason" rows="3" placeholder="Enter reason for admin override..."></textarea>
                    </div>
                    <input type="hidden" id="clockOutUserId" name="user_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Force Clock Out</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Custom styles for admin dashboard */
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.text-xs {
    font-size: 0.75rem;
}

.font-weight-bold {
    font-weight: 700 !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.text-uppercase {
    text-transform: uppercase !important;
}

.no-gutters {
    margin-right: 0;
    margin-left: 0;
}

.no-gutters > .col,
.no-gutters > [class*="col-"] {
    padding-right: 0;
    padding-left: 0;
}

.py-2 {
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}

/* Timeline styles */
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline-item {
    position: relative;
    margin-bottom: 1rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
}

.timeline-content {
    background: #f8f9fa;
    padding: 0.75rem;
    border-radius: 0.375rem;
    border-left: 3px solid #dee2e6;
}

.card.shadow-sm {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}
</style>

<script>
// Refresh dashboard data
function refreshDashboard() {
    location.reload();
}

// View user details
function viewUserDetails(userId) {
    document.getElementById('userDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading user details...</p>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('userDetailsModal')).show();
    
    fetch(`/time/getMemberDetails?user_id=${userId}&date=<?= date('Y-m-d') ?>`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('userDetailsContent').innerHTML = data.html;
            } else {
                document.getElementById('userDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        Error loading user details: ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('userDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    Error loading user details. Please try again.
                </div>
            `;
        });
}

// Admin clock out user
function adminClockOut(userId, userName) {
    document.getElementById('clockOutUserId').value = userId;
    document.getElementById('clockOutUserName').textContent = userName;
    document.getElementById('clockOutReason').value = '';
    
    new bootstrap.Modal(document.getElementById('adminClockOutModal')).show();
}

// Handle admin clock out form submission
document.getElementById('adminClockOutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/time/adminClockOut', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('adminClockOutModal')).hide();
            showNotification('success', 'User clocked out successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        showNotification('error', 'An error occurred');
    });
});

// Export all data
function exportAllData() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const userId = document.getElementById('user_id').value;
    
    let url = '/time/export?';
    if (startDate) url += `start_date=${startDate}&`;
    if (endDate) url += `end_date=${endDate}&`;
    if (userId) url += `user_id=${userId}&`;
    
    window.open(url, '_blank');
}

// Show notification
function showNotification(type, message) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}

// Auto-refresh every 2 minutes
setInterval(function() {
    const activeSection = document.querySelector('.card:has(.badge.bg-success)');
    if (activeSection) {
        // Only refresh if there are active users
        refreshDashboard();
    }
}, 120000);

// Set default dates
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    startDateInput.max = today;
    endDateInput.max = today;
});
</script>

<?php require VIEWSPATH . '/partials/footer.php'; ?> 