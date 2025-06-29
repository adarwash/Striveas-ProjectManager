<?php require VIEWSPATH . '/partials/header.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Time History</h1>
    
    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i> Filter Time Entries</h5>
        </div>
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
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Apply Filter
                    </button>
                    <a href="/time/history" class="btn btn-outline-secondary ms-2">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset
                    </a>
                    <a href="/time" class="btn btn-outline-info ms-2">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i> Summary (<?= date('M d, Y', strtotime($start_date)) ?> - <?= date('M d, Y', strtotime($end_date)) ?>)</h5>
        </div>
        <div class="card-body">
            <?php if (empty($time_entries)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i> No time tracking data found for the selected period.
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
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($total_hours, 1) ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($time_entries) ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
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
                                            <?= count($time_entries) > 0 ? number_format($total_hours / count($time_entries), 1) : '0.0' ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                            Total Break Time</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            $totalBreakMinutes = array_sum(array_column($time_entries, 'total_break_minutes'));
                                            echo sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-coffee fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Daily Hours Chart -->
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
                        const breakMinutes = [];
                        
                        <?php foreach($time_entries as $entry): ?>
                            dates.push('<?= date('M d', strtotime($entry['clock_in_time'])) ?>');
                            hours.push(<?= floatval($entry['total_hours'] ?? 0) ?>);
                            breakMinutes.push(<?= floatval(($entry['total_break_minutes'] ?? 0) / 60) ?>);
                        <?php endforeach; ?>
                        
                        const dailyHoursChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: dates,
                                datasets: [{
                                    label: 'Work Hours',
                                    data: hours,
                                    backgroundColor: 'rgba(78, 115, 223, 0.6)',
                                    borderColor: 'rgba(78, 115, 223, 1)',
                                    borderWidth: 1
                                }, {
                                    label: 'Break Hours',
                                    data: breakMinutes,
                                    backgroundColor: 'rgba(255, 193, 7, 0.6)',
                                    borderColor: 'rgba(255, 193, 7, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                scales: {
                                    x: {
                                        stacked: false,
                                    },
                                    y: {
                                        stacked: false,
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
                                        text: 'Daily Time Tracking Summary'
                                    },
                                    legend: {
                                        display: true,
                                        position: 'top'
                                    }
                                }
                            }
                        });
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>

    <!-- Detailed Time Entries -->
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i> Detailed Time Log</h5>
            <div>
                <button class="btn btn-sm btn-outline-primary" onclick="exportData()">
                    <i class="bi bi-download me-1"></i>Export CSV
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($time_entries)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i> No time entries found for the selected period.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="timeEntriesTable">
                        <thead>
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
                                <td><?= date('M d (D)', strtotime($entry['clock_in_time'])) ?></td>
                                <td>
                                    <span class="badge bg-success">
                                        <?= date('H:i', strtotime($entry['clock_in_time'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($entry['clock_out_time']): ?>
                                        <span class="badge bg-danger">
                                            <?= date('H:i', strtotime($entry['clock_out_time'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $entry['total_hours'] ? number_format($entry['total_hours'], 1) . ' hrs' : '-' ?>
                                </td>
                                <td>
                                    <?php 
                                    $breakMins = $entry['total_break_minutes'] ?? 0;
                                    echo sprintf('%02d:%02d', floor($breakMins / 60), $breakMins % 60);
                                    ?>
                                </td>
                                <td>
                                    <?php if ($entry['total_hours']): ?>
                                        <?php 
                                        $netHours = $entry['total_hours'] - (($entry['total_break_minutes'] ?? 0) / 60);
                                        echo '<strong>' . number_format(max(0, $netHours), 1) . ' hrs</strong>';
                                        ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($entry['status'] === 'completed'): ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php elseif($entry['status'] === 'active'): ?>
                                        <span class="badge bg-primary">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Incomplete</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewTimeDetails(<?= $entry['id'] ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <?php if (!empty($entry['breaks'])): ?>
                                        <button type="button" class="btn btn-sm btn-outline-warning ms-1" onclick="viewBreaks(<?= $entry['id'] ?>)">
                                            <i class="bi bi-cup"></i>
                                        </button>
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
function viewTimeDetails(entryId) {
    const entry = <?php echo json_encode($time_entries); ?>.find(e => e.id == entryId);
    
    if (!entry) return;
    
    const content = `
        <div class="row">
            <div class="col-md-6">
                <h6><i class="bi bi-calendar3 me-2"></i>Date & Time</h6>
                <div class="mb-3">
                    <small class="text-muted">Date</small>
                    <div><strong>${new Date(entry.clock_in_time).toLocaleDateString()}</strong></div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Clock In</small>
                    <div><span class="badge bg-success">${new Date(entry.clock_in_time).toLocaleTimeString()}</span></div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Clock Out</small>
                    <div>${entry.clock_out_time ? '<span class="badge bg-danger">' + new Date(entry.clock_out_time).toLocaleTimeString() + '</span>' : '<span class="badge bg-warning">Not clocked out</span>'}</div>
                </div>
            </div>
            <div class="col-md-6">
                <h6><i class="bi bi-clock me-2"></i>Time Summary</h6>
                <div class="mb-3">
                    <small class="text-muted">Total Hours</small>
                    <div><strong>${entry.total_hours ? entry.total_hours + ' hrs' : 'N/A'}</strong></div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Break Time</small>
                    <div><strong>${Math.floor((entry.total_break_minutes || 0) / 60)}:${String((entry.total_break_minutes || 0) % 60).padStart(2, '0')}</strong></div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Status</small>
                    <div><span class="badge bg-${entry.status === 'completed' ? 'success' : (entry.status === 'active' ? 'primary' : 'secondary')}">${entry.status.charAt(0).toUpperCase() + entry.status.slice(1)}</span></div>
                </div>
            </div>
        </div>
        ${entry.notes ? `<div class="mt-4"><h6><i class="bi bi-sticky me-2"></i>Notes</h6><div class="p-3 bg-light rounded"><p class="mb-0">${entry.notes}</p></div></div>` : ''}
    `;
    
    document.getElementById('detailsContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}

// View breaks for time entry
function viewBreaks(entryId) {
    const entry = <?php echo json_encode($time_entries); ?>.find(e => e.id == entryId);
    
    if (!entry || !entry.breaks || entry.breaks.length === 0) {
        document.getElementById('breaksContent').innerHTML = '<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>No breaks recorded for this time entry.</div>';
        new bootstrap.Modal(document.getElementById('breaksModal')).show();
        return;
    }
    
    let content = '<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Type</th><th>Start Time</th><th>End Time</th><th>Duration</th><th>Notes</th></tr></thead><tbody>';
    
    entry.breaks.forEach(breakItem => {
        const duration = breakItem.break_duration_minutes || 0;
        const breakTypeColor = breakItem.break_type === 'lunch' ? 'warning' : 
                              breakItem.break_type === 'meeting' ? 'info' : 
                              breakItem.break_type === 'personal' ? 'danger' : 'secondary';
        content += `
            <tr>
                <td><span class="badge bg-${breakTypeColor}">${breakItem.break_type}</span></td>
                <td>${new Date(breakItem.break_start).toLocaleTimeString()}</td>
                <td>${breakItem.break_end ? new Date(breakItem.break_end).toLocaleTimeString() : '<span class="badge bg-warning">Ongoing</span>'}</td>
                <td><strong>${Math.floor(duration / 60)}:${String(duration % 60).padStart(2, '0')}</strong></td>
                <td><small class="text-muted">${breakItem.notes || '-'}</small></td>
            </tr>
        `;
    });
    
    content += '</tbody></table></div>';
    
    document.getElementById('breaksContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('breaksModal')).show();
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

<style>
/* Custom styling for summary cards */
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

/* Chart container styling */
#dailyHoursChart {
    max-height: 400px;
}

/* Table styling improvements */
.table th {
    border-top: none;
    font-weight: 600;
    color: #6c757d;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.card.shadow-sm {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.card.shadow {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}
</style>

<?php require VIEWSPATH . '/partials/footer.php'; ?> 