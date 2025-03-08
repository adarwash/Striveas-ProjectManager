<?php require VIEWSPATH . '/inc/header.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">System Logs</h1>
            <p class="text-muted">Monitor system activity and events</p>
        </div>
        <div>
            <a href="<?= URLROOT ?>/admin" class="btn btn-outline-secondary me-2">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </div>
    </div>
    
    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Filters</h5>
        </div>
        <div class="card-body">
            <form action="<?= URLROOT ?>/admin/logs" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="log_type" class="form-label">Event Type</label>
                    <select class="form-select" id="log_type" name="type">
                        <option value="">All Events</option>
                        <option value="login">Login Events</option>
                        <option value="error">Error Events</option>
                        <option value="change">System Changes</option>
                        <option value="security">Security Events</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="log_user" class="form-label">User</label>
                    <input type="text" class="form-control" id="log_user" name="user" placeholder="Username or email">
                </div>
                <div class="col-md-2">
                    <label for="log_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="log_from" name="from_date">
                </div>
                <div class="col-md-2">
                    <label for="log_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="log_to" name="to_date">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-2"></i>Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Logs Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Activity Logs</h5>
            <div>
                <button type="button" class="btn btn-outline-secondary btn-sm me-2" id="refreshLogs">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" id="exportLogs">
                    <i class="bi bi-download me-1"></i>Export
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 200px;">Timestamp</th>
                            <th style="width: 140px;">Type</th>
                            <th>Event</th>
                            <th>User</th>
                            <th>IP Address</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach($logs as $log): ?>
                            <tr>
                                <td class="ps-4 text-nowrap"><?= formatDateTime($log->timestamp) ?></td>
                                <td>
                                    <?php if($log->type === 'login'): ?>
                                        <span class="badge bg-info text-nowrap">Login</span>
                                    <?php elseif($log->type === 'error'): ?>
                                        <span class="badge bg-danger text-nowrap">Error</span>
                                    <?php elseif($log->type === 'change'): ?>
                                        <span class="badge bg-warning text-nowrap">Change</span>
                                    <?php elseif($log->type === 'security'): ?>
                                        <span class="badge bg-danger text-nowrap">Security</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary text-nowrap"><?= ucfirst($log->type) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($log->message) ?></td>
                                <td><?= $log->user ?? 'System' ?></td>
                                <td><?= $log->ip_address ?? '--' ?></td>
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-sm btn-outline-secondary view-details-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#logDetailsModal"
                                        data-log-id="<?= $log->id ?>">
                                        <i class="bi bi-info-circle"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No log entries found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (isset($pagination) && $pagination->totalPages > 1): ?>
        <div class="card-footer bg-white">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <?php if ($pagination->currentPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= URLROOT ?>/admin/logs?page=<?= $pagination->currentPage - 1 ?><?= isset($_GET['type']) ? '&type=' . $_GET['type'] : '' ?><?= isset($_GET['user']) ? '&user=' . $_GET['user'] : '' ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $pagination->currentPage - 2); $i <= min($pagination->totalPages, $pagination->currentPage + 2); $i++): ?>
                    <li class="page-item <?= $i == $pagination->currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="<?= URLROOT ?>/admin/logs?page=<?= $i ?><?= isset($_GET['type']) ? '&type=' . $_GET['type'] : '' ?><?= isset($_GET['user']) ? '&user=' . $_GET['user'] : '' ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($pagination->currentPage < $pagination->totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= URLROOT ?>/admin/logs?page=<?= $pagination->currentPage + 1 ?><?= isset($_GET['type']) ? '&type=' . $_GET['type'] : '' ?><?= isset($_GET['user']) ? '&user=' . $_GET['user'] : '' ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logDetailsModalLabel">Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6 class="fw-bold">Timestamp</h6>
                    <p id="log-detail-timestamp">2023-07-15 14:30:22</p>
                </div>
                <div class="mb-3">
                    <h6 class="fw-bold">Event Type</h6>
                    <p id="log-detail-type"><span class="badge bg-info">Login</span></p>
                </div>
                <div class="mb-3">
                    <h6 class="fw-bold">User</h6>
                    <p id="log-detail-user">admin@example.com</p>
                </div>
                <div class="mb-3">
                    <h6 class="fw-bold">IP Address</h6>
                    <p id="log-detail-ip">192.168.1.1</p>
                </div>
                <div class="mb-3">
                    <h6 class="fw-bold">User Agent</h6>
                    <p id="log-detail-ua" class="text-break">Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36</p>
                </div>
                <div class="mb-0">
                    <h6 class="fw-bold">Message</h6>
                    <p id="log-detail-message">User successfully logged in</p>
                </div>
                <div id="log-detail-additional" class="d-none">
                    <hr>
                    <div class="mb-0">
                        <h6 class="fw-bold">Additional Data</h6>
                        <pre id="log-detail-data" class="bg-light p-3 rounded"><code>{ "method": "POST", "route": "/users/login" }</code></pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Refresh logs button
    const refreshBtn = document.getElementById('refreshLogs');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            window.location.reload();
        });
    }
    
    // Export logs button
    const exportBtn = document.getElementById('exportLogs');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            window.location.href = '<?= URLROOT ?>/admin/logs_export<?= isset($_GET['type']) ? '?type=' . $_GET['type'] : '' ?><?= isset($_GET['user']) ? (isset($_GET['type']) ? '&' : '?') . 'user=' . $_GET['user'] : '' ?><?= isset($_GET['from_date']) ? (isset($_GET['type']) || isset($_GET['user']) ? '&' : '?') . 'from_date=' . $_GET['from_date'] : '' ?><?= isset($_GET['to_date']) ? (isset($_GET['type']) || isset($_GET['user']) || isset($_GET['from_date']) ? '&' : '?') . 'to_date=' . $_GET['to_date'] : '' ?>';
        });
    }
    
    // Log details modal
    const viewDetailsBtns = document.querySelectorAll('.view-details-btn');
    viewDetailsBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const logId = this.getAttribute('data-log-id');
            
            // Show loading state
            document.getElementById('log-detail-timestamp').textContent = 'Loading...';
            document.getElementById('log-detail-type').innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
            document.getElementById('log-detail-user').textContent = 'Loading...';
            document.getElementById('log-detail-ip').textContent = 'Loading...';
            document.getElementById('log-detail-ua').textContent = 'Loading...';
            document.getElementById('log-detail-message').textContent = 'Loading...';
            
            // Hide additional data section by default
            const additionalData = document.getElementById('log-detail-additional');
            additionalData.classList.add('d-none');
            
            // Fetch log details via AJAX
            fetch('<?= URLROOT ?>/admin/log_details/' + logId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Update modal with log details
                document.getElementById('log-detail-timestamp').textContent = data.timestamp;
                
                let typeClass = 'bg-secondary';
                if (data.type === 'login') typeClass = 'bg-info';
                else if (data.type === 'error') typeClass = 'bg-danger';
                else if (data.type === 'change') typeClass = 'bg-warning';
                else if (data.type === 'security') typeClass = 'bg-danger';
                
                document.getElementById('log-detail-type').innerHTML = `<span class="badge ${typeClass}">${data.type}</span>`;
                document.getElementById('log-detail-user').textContent = data.user || 'System';
                document.getElementById('log-detail-ip').textContent = data.ip_address || '--';
                document.getElementById('log-detail-ua').textContent = data.user_agent || '--';
                document.getElementById('log-detail-message').textContent = data.message;
                
                // Show additional data if available
                if (data.additional_data) {
                    additionalData.classList.remove('d-none');
                    try {
                        const jsonData = JSON.parse(data.additional_data);
                        document.getElementById('log-detail-data').innerHTML = '<code>' + JSON.stringify(jsonData, null, 2) + '</code>';
                    } catch (e) {
                        document.getElementById('log-detail-data').innerHTML = '<code>' + data.additional_data + '</code>';
                    }
                } else {
                    additionalData.classList.add('d-none');
                }
            })
            .catch(error => {
                console.error('Error fetching log details:', error);
                document.getElementById('log-detail-timestamp').textContent = 'Error loading details';
                document.getElementById('log-detail-type').innerHTML = '<span class="badge bg-danger">Error</span>';
                document.getElementById('log-detail-message').textContent = 'Could not load log details. Please try again.';
                document.getElementById('log-detail-user').textContent = '--';
                document.getElementById('log-detail-ip').textContent = '--';
                document.getElementById('log-detail-ua').textContent = '--';
                additionalData.classList.add('d-none');
            });
        });
    });
});
</script>

<?php require VIEWSPATH . '/inc/footer.php'; ?> 