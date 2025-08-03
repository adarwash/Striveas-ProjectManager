<!-- Modern Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-clock me-3"></i>Time Tracking Dashboard</h1>
        <p class="mb-0">Manage your work time and track productivity</p>
    </div>
    <div>
        <button class="btn btn-secondary me-2" onclick="refreshData()">
            <i class="fas fa-sync-alt me-2"></i>Refresh
        </button>
        <a href="/time/history" class="btn btn-primary">
            <i class="fas fa-history me-2"></i>View History
        </a>
    </div>
</div>

    <!-- Current Status Hero Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="status-hero-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="status-hero-content">
                            <div class="current-status">
                                <div class="status-indicator status-<?php echo $user_status['status']; ?>">
                                    <?php if ($user_status['status'] === 'clocked_in'): ?>
                                        <i class="fas fa-play-circle"></i>
                                    <?php elseif ($user_status['status'] === 'on_break'): ?>
                                        <i class="fas fa-pause-circle"></i>
                                    <?php else: ?>
                                        <i class="fas fa-stop-circle"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="status-text">
                                    <h3 class="status-title">
                                        <?php echo ucfirst(str_replace('_', ' ', $user_status['status'])); ?>
                                    </h3>
                                    <p class="status-subtitle">
                                        <?php if ($user_status['status'] === 'clocked_in'): ?>
                                            Working since <?php echo date('h:i A', strtotime($user_status['clock_in_time'] ?? 'now')); ?>
                                        <?php elseif ($user_status['status'] === 'on_break'): ?>
                                            On break since <?php echo date('h:i A', strtotime($user_status['break_start_time'] ?? 'now')); ?>
                                        <?php else: ?>
                                            Ready to start your work day
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="current-time-display">
                            <div class="time-label">Current Time</div>
                            <div class="time-value" id="heroCurrentTime"><?php echo date('h:i A'); ?></div>
                            <div class="date-value"><?php echo date('M d, Y'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="modern-stats-card card-primary">
                <div class="stats-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value" id="todayHours">
                        <?php echo $today_summary ? number_format($today_summary['total_hours'] ?? 0, 1) : '0.0'; ?>h
                    </div>
                    <div class="stats-label">Today's Hours</div>
                    <div class="stats-progress">
                        <div class="progress-bar" style="width: <?php echo min(($today_summary['total_hours'] ?? 0) / 8 * 100, 100); ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="modern-stats-card card-success">
                <div class="stats-icon">
                    <i class="fas fa-play"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value" id="currentSession">
                        <?php 
                        if ($user_status['status'] !== 'clocked_out') {
                            $minutes = $user_status['elapsed_work_time'] ?? 0;
                            $hours = floor($minutes / 60);
                            $mins = $minutes % 60;
                            echo sprintf('%dh %02dm', $hours, $mins);
                        } else {
                            echo '0h 00m';
                        }
                        ?>
                    </div>
                    <div class="stats-label">Current Session</div>
                    <div class="stats-progress">
                        <div class="progress-bar" style="width: <?php echo $user_status['status'] !== 'clocked_out' ? min(($user_status['elapsed_work_time'] ?? 0) / 480 * 100, 100) : 0; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="modern-stats-card card-warning">
                <div class="stats-icon">
                    <i class="fas fa-coffee"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value" id="breakTime">
                        <?php 
                        $breakMinutes = 0;
                        if ($user_status['status'] === 'on_break' && isset($user_status['break_duration'])) {
                            $breakMinutes = $user_status['break_duration'];
                        } elseif ($today_summary && $today_summary['total_break_minutes']) {
                            $breakMinutes = $today_summary['total_break_minutes'];
                        }
                        $breakHours = floor($breakMinutes / 60);
                        $breakMins = $breakMinutes % 60;
                        echo sprintf('%dh %02dm', $breakHours, $breakMins);
                        ?>
                    </div>
                    <div class="stats-label">Break Time</div>
                    <div class="stats-progress">
                        <div class="progress-bar" style="width: <?php echo min($breakMinutes / 60 * 100, 100); ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="modern-stats-card card-info">
                <div class="stats-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value">
                        <?php echo $today_summary ? number_format(max(0, ($today_summary['total_hours'] ?? 0) - ($today_summary['total_break_minutes'] ?? 0) / 60), 1) : '0.0'; ?>h
                    </div>
                    <div class="stats-label">Net Work Time</div>
                    <div class="stats-progress">
                        <div class="progress-bar" style="width: <?php echo min((($today_summary['total_hours'] ?? 0) - ($today_summary['total_break_minutes'] ?? 0) / 60) / 8 * 100, 100); ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Control Panel -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="control-panel-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-cog me-2"></i>Time Control Center
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Clock In/Out Control -->
                        <div class="col-md-6">
                            <div class="control-section">
                                <div class="control-header">
                                    <h6>Clock In/Out</h6>
                                    <span class="control-time" id="controlCurrentTime"><?php echo date('h:i:s A'); ?></span>
                                </div>
                                
                                <!-- Current Site Display -->
                                <?php if ($user_status['status'] === 'clocked_in' && !empty($user_status['site_name'])): ?>
                                    <div class="current-site-info mb-3">
                                        <div class="site-badge">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            <strong><?= htmlspecialchars($user_status['site_name']) ?></strong>
                                            <?php if (!empty($user_status['site_location'])): ?>
                                                <small class="text-muted d-block"><?= htmlspecialchars($user_status['site_location']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Site Selection (for Clock In) -->
                                <?php if ($user_status['status'] === 'clocked_out'): ?>
                                    <div class="site-selection mb-3">
                                        <label for="clockInSite" class="form-label">
                                            <i class="fas fa-building me-2"></i>Work Location
                                        </label>
                                        <select class="form-select" id="clockInSite">
                                            <option value="">Loading sites...</option>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="control-actions">
                                    <?php if ($user_status['status'] === 'clocked_out'): ?>
                                        <button class="btn btn-success btn-lg control-btn" onclick="clockIn()">
                                            <i class="fas fa-play me-2"></i>
                                            <span>Clock In</span>
                                        </button>
                                        <p class="control-hint">Start your work session</p>
                                    <?php else: ?>
                                        <button class="btn btn-danger btn-lg control-btn" onclick="clockOut()">
                                            <i class="fas fa-stop me-2"></i>
                                            <span>Clock Out</span>
                                        </button>
                                        <p class="control-hint">End your work session</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Break Control -->
                        <div class="col-md-6">
                            <div class="control-section">
                                <div class="control-header">
                                    <h6>Break Management</h6>
                                    <span class="control-status">
                                        <?php if ($user_status['status'] === 'on_break'): ?>
                                            <span class="badge bg-warning">On Break</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark">Available</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                
                                <div class="control-actions">
                                    <?php if ($user_status['status'] === 'clocked_in'): ?>
                                        <div class="mb-3">
                                            <select class="form-select" id="breakType">
                                                <?php foreach ($break_types as $type): ?>
                                                    <option value="<?php echo $type['name']; ?>"><?php echo ucfirst($type['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <button class="btn btn-warning btn-lg control-btn" onclick="startBreak()">
                                            <i class="fas fa-pause me-2"></i>
                                            <span>Start Break</span>
                                        </button>
                                    <?php elseif ($user_status['status'] === 'on_break'): ?>
                                        <div class="mb-3">
                                            <span class="break-type-display"><?php echo ucfirst($user_status['active_break']['break_type'] ?? 'break'); ?></span>
                                        </div>
                                        <button class="btn btn-success btn-lg control-btn" onclick="endBreak()">
                                            <i class="fas fa-play me-2"></i>
                                            <span>End Break</span>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-warning btn-lg control-btn" disabled>
                                            <i class="fas fa-pause me-2"></i>
                                            <span>Start Break</span>
                                        </button>
                                        <p class="control-hint">Clock in to take breaks</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Sidebar -->
        <div class="col-lg-4 mb-4">
            <div class="quick-actions-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="quick-actions-grid">
                        <a href="/time/history" class="quick-action-item">
                            <div class="action-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <div class="action-content">
                                <h6>Time History</h6>
                                <p>View past time entries</p>
                            </div>
                        </a>
                        
                        <a href="/time/team" class="quick-action-item">
                            <div class="action-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="action-content">
                                <h6>Team View</h6>
                                <p>Monitor team activity</p>
                            </div>
                        </a>
                        
                        <a href="/time/reports" class="quick-action-item">
                            <div class="action-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="action-content">
                                <h6>Reports</h6>
                                <p>Generate time reports</p>
                            </div>
                        </a>
                        
                        <button class="quick-action-item" onclick="exportData()">
                            <div class="action-icon">
                                <i class="fas fa-download"></i>
                            </div>
                            <div class="action-content">
                                <h6>Export Data</h6>
                                <p>Download time data</p>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Time Entries -->
    <div class="row">
        <div class="col-12">
            <div class="recent-entries-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            <i class="fas fa-clock me-2"></i>Recent Time Entries
                        </h5>
                        <a href="/time/history" class="btn btn-sm btn-outline-primary">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_entries)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover modern-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Site</th>
                                        <th>Clock In</th>
                                        <th>Clock Out</th>
                                        <th>Work Hours</th>
                                        <th>Break Time</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_entries as $entry): ?>
                                        <tr>
                                            <td>
                                                <div class="date-display">
                                                    <div class="date-main"><?php echo date('M d', strtotime($entry['clock_in_time'])); ?></div>
                                                    <div class="date-year"><?php echo date('Y', strtotime($entry['clock_in_time'])); ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($entry['site_name'])): ?>
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info" style="font-size: 0.7rem;" title="<?= htmlspecialchars($entry['site_location'] ?? '') ?>">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        <?= htmlspecialchars($entry['site_name']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted" style="font-size: 0.8rem;">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="time-display"><?php echo date('h:i A', strtotime($entry['clock_in_time'])); ?></span>
                                            </td>
                                            <td>
                                                <?php if ($entry['clock_out_time']): ?>
                                                    <span class="time-display"><?php echo date('h:i A', strtotime($entry['clock_out_time'])); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary">Active</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="hours-display">
                                                    <?php echo $entry['total_hours'] ? number_format($entry['total_hours'], 1) . 'h' : '0.0h'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="break-time-display">
                                                    <?php echo $entry['total_break_minutes'] ? sprintf('%02d:%02d', floor($entry['total_break_minutes'] / 60), $entry['total_break_minutes'] % 60) : '00:00'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge status-badge status-<?php echo $entry['status']; ?>">
                                                    <?php echo ucfirst($entry['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewDetails('<?php echo $entry['id']; ?>')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h6>No Time Entries Yet</h6>
                            <p>Start tracking your time by clicking the "Clock In" button above.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-sticky-note me-2"></i>Add Notes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="notesForm">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Work Notes (Optional)</label>
                        <textarea class="form-control" id="notes" rows="4" placeholder="Describe what you worked on, accomplishments, or any relevant information..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="submitWithNotes()">
                    <i class="fas fa-check me-2"></i>Continue
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modern Styling -->
<style>
/* Page Header */
.page-header {
    background: #ffffff;
    color: #333;
    padding: 2rem;
    border-radius: 1rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid #e9ecef;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: #333;
}

.page-title i {
    color: #7c3aed;
    margin-right: 0.75rem;
}

.page-header p {
    color: #6c757d;
    margin: 0;
}

.header-text {
    flex: 1;
    margin-right: 2rem;
}

.header-actions {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}



/* Status Hero Card */
.status-hero-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.current-status {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.status-indicator {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.status-indicator.status-clocked_in {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.status-indicator.status-on_break {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.status-indicator.status-clocked_out {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    color: white;
}

.status-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: #1f2937;
}

.status-subtitle {
    color: #6b7280;
    font-size: 1.1rem;
    margin: 0;
}

.current-time-display {
    text-align: center;
    padding: 1.5rem;
    background: rgba(255,255,255,0.7);
    border-radius: 0.75rem;
    border: 1px solid rgba(0,0,0,0.1);
}

.time-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.time-value {
    font-size: 2.25rem;
    font-weight: 700;
    color: #1f2937;
    font-family: 'SF Mono', 'Monaco', monospace;
}

.date-value {
    font-size: 1rem;
    color: #6b7280;
    font-weight: 500;
}

/* Modern Stats Cards */
.modern-stats-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: none;
    height: 100%;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.modern-stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--card-color), var(--card-color-light));
}

.modern-stats-card.card-primary {
    --card-color: #3b82f6;
    --card-color-light: #60a5fa;
}

.modern-stats-card.card-success {
    --card-color: #10b981;
    --card-color-light: #34d399;
}

.modern-stats-card.card-warning {
    --card-color: #f59e0b;
    --card-color-light: #fbbf24;
}

.modern-stats-card.card-info {
    --card-color: #06b6d4;
    --card-color-light: #22d3ee;
}

.modern-stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    background: linear-gradient(135deg, var(--card-color), var(--card-color-light));
    margin-bottom: 1rem;
}

.stats-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    font-family: 'SF Mono', 'Monaco', monospace;
    margin-bottom: 0.5rem;
}

.stats-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 1rem;
}

.stats-progress {
    height: 4px;
    background: #e5e7eb;
    border-radius: 2px;
    overflow: hidden;
}

.stats-progress .progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--card-color), var(--card-color-light));
    transition: width 0.3s ease;
}

/* Control Panel */
.control-panel-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: none;
    height: 100%;
}

.control-panel-card .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
    border-radius: 1rem 1rem 0 0;
    padding: 1.5rem;
}

.control-section {
    background: #f8f9fa;
    border-radius: 0.75rem;
    padding: 1.5rem;
    height: 100%;
    border: 1px solid #e9ecef;
}

.control-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.control-header h6 {
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.control-time {
    font-family: 'SF Mono', 'Monaco', monospace;
    font-weight: 600;
    color: #6b7280;
    font-size: 0.875rem;
}

.control-btn {
    width: 100%;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
    margin-bottom: 1rem;
}

.control-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.control-hint {
    font-size: 0.875rem;
    color: #6b7280;
    text-align: center;
    margin: 0;
}

/* Site Information Styles */
.current-site-info {
    background: #e7f3ff;
    border: 1px solid #b3d9ff;
    border-radius: 0.5rem;
    padding: 1rem;
}

.site-badge {
    color: #0066cc;
    font-size: 0.875rem;
}

.site-badge i {
    color: #0052a3;
}

.site-selection label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.site-selection .form-select {
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 0.625rem 1rem;
    font-size: 0.875rem;
}

.site-selection .form-select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
}

.break-type-display {
    display: inline-block;
    background: #fff3cd;
    color: #856404;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

/* Quick Actions */
.quick-actions-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: none;
    height: 100%;
}

.quick-actions-card .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
    border-radius: 1rem 1rem 0 0;
    padding: 1.5rem;
}

.quick-actions-grid {
    display: grid;
    gap: 1rem;
}

.quick-action-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
}

.quick-action-item:hover {
    background: #e9ecef;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    color: inherit;
}

.action-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
    color: white;
    font-size: 1.1rem;
}

.action-content h6 {
    margin: 0 0 0.25rem 0;
    font-weight: 600;
    color: #1f2937;
}

.action-content p {
    margin: 0;
    font-size: 0.875rem;
    color: #6b7280;
}

/* Recent Entries */
.recent-entries-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: none;
}

.recent-entries-card .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
    border-radius: 1rem 1rem 0 0;
    padding: 1.5rem;
}

.modern-table {
    margin-bottom: 0;
}

.modern-table th {
    background: #f8f9fa;
    border-top: none;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #6b7280;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 1rem;
}

.modern-table td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f4;
}

.date-display {
    text-align: center;
}

.date-main {
    font-weight: 600;
    color: #1f2937;
}

.date-year {
    font-size: 0.75rem;
    color: #6b7280;
}

.time-display {
    font-family: 'SF Mono', 'Monaco', monospace;
    font-weight: 600;
    color: #374151;
}

.hours-display {
    font-family: 'SF Mono', 'Monaco', monospace;
    font-weight: 600;
    color: #059669;
}

.break-time-display {
    font-family: 'SF Mono', 'Monaco', monospace;
    font-weight: 600;
    color: #d97706;
}

.status-badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
}

.status-badge.status-completed {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.status-active {
    background: #dbeafe;
    color: #1e40af;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;
}

.empty-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto 1rem;
}

.empty-state h6 {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header {
        padding: 1.5rem;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .status-hero-card {
        padding: 1.5rem;
    }
    
    .current-status {
        flex-direction: column;
        text-align: center;
    }
    
    .current-time-display {
        margin-top: 1rem;
    }
    
    .control-section {
        margin-bottom: 1rem;
    }
}
</style>

<script>
let currentAction = null;
let updateInterval = null;

// Update current time displays
function updateTimeDisplays() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit'
    });
    const timeString12 = now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit'
    });
    
    // Update all time displays
    const heroTime = document.getElementById('heroCurrentTime');
    const controlTime = document.getElementById('controlCurrentTime');
    
    if (heroTime) heroTime.textContent = timeString12;
    if (controlTime) controlTime.textContent = timeString;
}

// Update session time
function updateSessionTime() {
    fetch('/time/getStatus')
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'clocked_out' && data.elapsed_work_time) {
                const minutes = data.elapsed_work_time;
                const hours = Math.floor(minutes / 60);
                const mins = minutes % 60;
                const sessionElement = document.getElementById('currentSession');
                if (sessionElement) {
                    sessionElement.textContent = `${hours}h ${String(mins).padStart(2, '0')}m`;
                }
            }
        })
        .catch(error => console.error('Error updating session time:', error));
}

// Start all timers
function startTimers() {
    updateTimeDisplays();
    setInterval(updateTimeDisplays, 1000);
    
    updateSessionTime();
    updateInterval = setInterval(updateSessionTime, 60000);
}

// Clock In
function clockIn() {
    currentAction = 'clockIn';
    const modal = new bootstrap.Modal(document.getElementById('notesModal'));
    modal.show();
}

// Clock Out
function clockOut() {
    currentAction = 'clockOut';
    const modal = new bootstrap.Modal(document.getElementById('notesModal'));
    modal.show();
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
            showModernAlert('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showModernAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showModernAlert('error', 'An error occurred. Please try again.');
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
            showModernAlert('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showModernAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showModernAlert('error', 'An error occurred. Please try again.');
    });
}

// Submit with notes
function submitWithNotes() {
    const notes = document.getElementById('notes').value;
    const endpoint = currentAction === 'clockIn' ? '/time/clockIn' : '/time/clockOut';
    
    // Prepare form data
    let formData = `notes=${encodeURIComponent(notes)}`;
    
    // Add site_id for clock in
    if (currentAction === 'clockIn') {
        const siteSelect = document.getElementById('clockInSite');
        if (siteSelect && siteSelect.value) {
            formData += `&site_id=${encodeURIComponent(siteSelect.value)}`;
        }
    }
    
    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('notesModal'));
        modal.hide();
        
        if (data.success) {
            showModernAlert('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showModernAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showModernAlert('error', 'An error occurred. Please try again.');
    });
}

// Modern alert system
function showModernAlert(type, message) {
    // Remove any existing alerts
    const existingAlert = document.querySelector('.modern-alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `modern-alert alert-${type}`;
    alertDiv.innerHTML = `
        <div class="alert-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            <span>${message}</span>
        </div>
        <button class="alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add styles
    alertDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1055;
        background: ${type === 'success' ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        max-width: 400px;
        display: flex;
        align-items: center;
        gap: 1rem;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => alertDiv.remove(), 300);
        }
    }, 5000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .modern-alert .alert-content {
        flex: 1;
        display: flex;
        align-items: center;
    }
    
    .modern-alert .alert-close {
        background: none;
        border: none;
        color: white;
        font-size: 1rem;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 0.25rem;
        transition: background-color 0.2s;
    }
    
    .modern-alert .alert-close:hover {
        background: rgba(255,255,255,0.2);
    }
`;
document.head.appendChild(style);

// Refresh data
function refreshData() {
    location.reload();
}

// Export data
function exportData() {
    window.open('/time/export', '_blank');
}

// View entry details
function viewDetails(entryId) {
    // This would open a modal with entry details
    console.log('View details for entry:', entryId);
}

// Load available sites for clock-in
function loadUserSites() {
    const siteSelect = document.getElementById('clockInSite');
    if (!siteSelect) return;
    
    fetch('/time/getUserSites')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.sites) {
                siteSelect.innerHTML = '<option value="">Select work location...</option>';
                
                data.sites.forEach(site => {
                    const option = document.createElement('option');
                    option.value = site.id;
                    option.textContent = `${site.name}${site.location ? ' - ' + site.location : ''}`;
                    siteSelect.appendChild(option);
                });
                
                // Auto-select first site if only one available
                if (data.sites.length === 1) {
                    siteSelect.value = data.sites[0].id;
                }
            } else {
                siteSelect.innerHTML = '<option value="">No sites available</option>';
            }
        })
        .catch(error => {
            console.error('Error loading sites:', error);
            siteSelect.innerHTML = '<option value="">Error loading sites</option>';
        });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    startTimers();
    loadUserSites();
    
    // Clear notes when modal is hidden
    const notesModal = document.getElementById('notesModal');
    if (notesModal) {
        notesModal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('notes').value = '';
            currentAction = null;
        });
    }
});
</script> 