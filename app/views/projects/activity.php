<!-- Activity Page Styles -->
<style>
.activity-timeline {
    position: relative;
    padding-left: 20px;
}

.activity-timeline::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 19px;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-dot {
    position: absolute;
    left: -20px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: #fff;
    border: 2px solid #0d6efd;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
}

.timeline-dot.task {
    border-color: #198754;
}

.timeline-dot.note {
    border-color: #0d6efd;
}

.timeline-dot.created {
    border-color: #198754;
}

.timeline-dot.updated {
    border-color: #0d6efd;
}

.timeline-dot.deleted {
    border-color: #dc3545;
}

.timeline-dot.uploaded {
    border-color: #6610f2;
}

.timeline-dot.downloaded {
    border-color: #fd7e14;
}

.timeline-dot.assigned {
    border-color: #0dcaf0;
}

.timeline-content {
    padding: 1rem;
    background-color: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
    margin-bottom: 0.5rem;
}

.activity-date {
    position: relative;
    margin: 2rem 0 1rem;
    text-align: left;
    z-index: 2;
}

.activity-date::before {
    content: '';
    position: absolute;
    top: 50%;
    left: -20px;
    right: 0;
    height: 2px;
    background-color: #e9ecef;
    z-index: -1;
}

.activity-date span {
    background-color: #f8f9fa;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #6c757d;
    position: relative;
    display: inline-block;
}

.filter-card {
    border: none;
    box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
    margin-bottom: 1.5rem;
}

.metadata-list {
    margin: 0;
    padding: 0;
    list-style: none;
    font-size: 0.875rem;
}

.metadata-list li {
    display: flex;
    margin-bottom: 0.25rem;
    color: #6c757d;
}

.metadata-list .metadata-label {
    width: 100px;
    font-weight: 500;
}
</style>

<!-- Project Header -->
<div class="bg-light rounded-3 p-4 mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= URLROOT ?>/projects" class="text-decoration-none">Projects</a></li>
            <li class="breadcrumb-item"><a href="<?= URLROOT ?>/projects/viewProject/<?= $project->id ?>" class="text-decoration-none"><?= htmlspecialchars($project->title) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Activity</li>
        </ol>
    </nav>
    
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="h2 mb-1"><?= htmlspecialchars($project->title) ?> - Activity</h1>
            <p class="text-muted mb-0">Complete activity history for this project</p>
        </div>
        <div>
            <a href="<?= URLROOT ?>/projects/viewProject/<?= $project->id ?>" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Project
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-9">
        <!-- Activity Filters -->
        <div class="card filter-card mb-4">
            <div class="card-header bg-white p-3 border-bottom">
                <h5 class="card-title mb-0"><i class="bi bi-funnel me-2"></i>Activity Filters</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= URLROOT ?>/projects/activity/<?= $project->id ?>" class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?= $filters['start_date'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="<?= $filters['end_date'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="action_type" class="form-label">Action Type</label>
                        <select class="form-select" id="action_type" name="action_type">
                            <option value="all">All Actions</option>
                            <?php foreach ($action_types as $value => $label): ?>
                                <option value="<?= $value ?>" <?= (isset($filters['action']) && $filters['action'] === $value) ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="user_id" class="form-label">User</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="all">All Users</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user->id ?>" <?= (isset($filters['user_id']) && $filters['user_id'] == $user->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user->username) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-filter"></i> Apply Filters
                        </button>
                        <a href="<?= URLROOT ?>/projects/activity/<?= $project->id ?>" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="bi bi-clock-history me-2"></i>Activity Timeline</h5>
            </div>
            <div class="card-body">
                <?php if (empty($activities) && empty($legacy_activities)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-clock-history text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-3 text-muted">No activity recorded for this project yet.</p>
                    </div>
                <?php else: ?>
                    <div class="activity-timeline">
                        <?php 
                        if (!empty($activities)):
                            $currentDate = null;
                            foreach ($activities as $index => $activity): 
                                $activityDate = date('Y-m-d', strtotime($activity['created_at']));
                                
                                // Show date header if it's a new date
                                if ($currentDate !== $activityDate):
                                    $currentDate = $activityDate;
                                    $displayDate = date('F j, Y', strtotime($activity['created_at']));
                        ?>
                            <div class="activity-date">
                                <span><?= $displayDate ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="timeline-item">
                            <div class="timeline-dot <?= $activity['action'] ?>">
                                <i class="bi <?= $this->getActionIcon($activity['action']) ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">
                                        <?= htmlspecialchars($activity['action_text']) ?>
                                    </h6>
                                    <span class="badge bg-<?= $this->getActionBadgeClass($activity['action']) ?> rounded-pill">
                                        <?= ucfirst($activity['action']) ?>
                                    </span>
                                </div>
                                
                                <?php if (!empty($activity['metadata_obj'])): ?>
                                <div class="mb-2 small">
                                    <ul class="metadata-list">
                                        <?php foreach ($this->getRelevantMetadata($activity['action'], $activity['metadata_obj']) as $label => $value): ?>
                                            <li>
                                                <span class="metadata-label"><?= $label ?>:</span>
                                                <span><?= htmlspecialchars($value) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                                
                                <div class="d-flex align-items-center text-muted small">
                                    <i class="bi bi-person me-1"></i>
                                    <span><?= htmlspecialchars($activity['user_display_name']) ?></span>
                                    <span class="mx-2">•</span>
                                    <i class="bi bi-clock me-1"></i>
                                    <span><?= date('g:i a', strtotime($activity['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                            
                        <?php 
                        // Show legacy activities if needed
                        elseif (!empty($legacy_activities)):
                            $currentDate = null;
                            foreach ($legacy_activities as $index => $activity): 
                                $activityDate = date('Y-m-d', strtotime($activity['date']));
                                
                                // Show date header if it's a new date
                                if ($currentDate !== $activityDate):
                                    $currentDate = $activityDate;
                                    $displayDate = date('F j, Y', strtotime($activity['date']));
                        ?>
                            <div class="activity-date">
                                <span><?= $displayDate ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="timeline-item activity-item" data-type="<?= $activity['type'] ?>">
                            <div class="timeline-dot <?= $activity['type'] ?>">
                                <i class="bi <?= $activity['type'] === 'note' ? 'bi-chat-left-text' : 'bi-clipboard-check' ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <?php if ($activity['type'] === 'note'): ?>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0">
                                            <?= htmlspecialchars($activity['data']['title'] ?? 'Note') ?>
                                        </h6>
                                        <span class="badge bg-primary rounded-pill">Note</span>
                                    </div>
                                    <p class="mb-2"><?= nl2br(htmlspecialchars($activity['data']['content'] ?? '')) ?></p>
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="bi bi-person me-1"></i>
                                        <span><?= htmlspecialchars($activity['data']['created_by_name'] ?? $activity['data']['created_by'] ?? 'Unknown') ?></span>
                                        <span class="mx-2">•</span>
                                        <i class="bi bi-clock me-1"></i>
                                        <span><?= date('g:i a', strtotime($activity['date'])) ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0">
                                            <a href="<?= URLROOT ?>/tasks/show/<?= $activity['data']->id ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($activity['data']->title) ?>
                                            </a>
                                        </h6>
                                        <span class="badge <?= $activity['data']->activity_type === 'updated' ? 'bg-warning' : 'bg-success' ?> rounded-pill">
                                            <?= ucfirst($activity['data']->activity_type) ?> Task
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <?php if ($activity['data']->activity_type === 'updated'): ?>
                                            <div class="d-flex gap-2 mb-2">
                                                <span class="badge <?= getTaskStatusClass($activity['data']->status) ?>"><?= $activity['data']->status ?></span>
                                                <?php if (!empty($activity['data']->assigned_to_name)): ?>
                                                    <span class="badge bg-secondary">Assigned to: <?= htmlspecialchars($activity['data']->assigned_to_name) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex align-items-center text-muted small">
                                        <i class="bi bi-person me-1"></i>
                                        <span><?= htmlspecialchars($activity['data']->created_by_name ?? 'Unknown') ?></span>
                                        <span class="mx-2">•</span>
                                        <i class="bi bi-clock me-1"></i>
                                        <span><?= date('g:i a', strtotime($activity['date'])) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3">
        <!-- Activity Summary -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white p-3 border-bottom">
                <h5 class="card-title mb-0"><i class="bi bi-graph-up me-2"></i>Activity Summary</h5>
            </div>
            <div class="card-body">
                <?php
                // Calculate activity counts from new activity logs
                $activityCounts = [
                    'created' => 0,
                    'updated' => 0,
                    'uploaded' => 0,
                    'downloaded' => 0,
                    'deleted' => 0,
                    'assigned' => 0,
                    'total' => count($activities)
                ];
                
                foreach ($activities as $activity) {
                    $action = $activity['action'];
                    if (isset($activityCounts[$action])) {
                        $activityCounts[$action]++;
                    }
                }
                
                // If using legacy activities, calculate those counts instead
                if (empty($activities) && !empty($legacy_activities)) {
                    $noteCount = 0;
                    $taskCreatedCount = 0;
                    $taskUpdatedCount = 0;
                    
                    foreach ($legacy_activities as $activity) {
                        if ($activity['type'] === 'note') {
                            $noteCount++;
                        } else {
                            if ($activity['data']->activity_type === 'created') {
                                $taskCreatedCount++;
                            } else {
                                $taskUpdatedCount++;
                            }
                        }
                    }
                    
                    $totalCount = count($legacy_activities);
                    
                    // Display legacy activity counts
                ?>
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Total Activities</h6>
                        <span class="badge bg-primary rounded-pill"><?= $totalCount ?></span>
                    </div>
                    <div class="progress mb-1" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Notes</h6>
                        <span class="badge bg-info rounded-pill"><?= $noteCount ?></span>
                    </div>
                    <div class="progress mb-1" style="height: 8px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= ($totalCount > 0) ? ($noteCount / $totalCount * 100) : 0 ?>%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Tasks Created</h6>
                        <span class="badge bg-success rounded-pill"><?= $taskCreatedCount ?></span>
                    </div>
                    <div class="progress mb-1" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= ($totalCount > 0) ? ($taskCreatedCount / $totalCount * 100) : 0 ?>%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Tasks Updated</h6>
                        <span class="badge bg-warning rounded-pill"><?= $taskUpdatedCount ?></span>
                    </div>
                    <div class="progress mb-1" style="height: 8px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= ($totalCount > 0) ? ($taskUpdatedCount / $totalCount * 100) : 0 ?>%"></div>
                    </div>
                </div>
                <?php } else { 
                    // Display new activity counts
                ?>
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Total Activities</h6>
                        <span class="badge bg-primary rounded-pill"><?= $activityCounts['total'] ?></span>
                    </div>
                    <div class="progress mb-1" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
                
                <?php if ($activityCounts['created'] > 0): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Created</h6>
                        <span class="badge bg-success rounded-pill"><?= $activityCounts['created'] ?></span>
                    </div>
                    <div class="progress mb-1" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= ($activityCounts['total'] > 0) ? ($activityCounts['created'] / $activityCounts['total'] * 100) : 0 ?>%"></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($activityCounts['updated'] > 0): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Updated</h6>
                        <span class="badge bg-info rounded-pill"><?= $activityCounts['updated'] ?></span>
                    </div>
                    <div class="progress mb-1" style="height: 8px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= ($activityCounts['total'] > 0) ? ($activityCounts['updated'] / $activityCounts['total'] * 100) : 0 ?>%"></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($activityCounts['uploaded'] > 0): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Uploaded</h6>
                        <span class="badge bg-purple rounded-pill"><?= $activityCounts['uploaded'] ?></span>
                    </div>
                    <div class="progress mb-1" style="height: 8px;">
                        <div class="progress-bar bg-purple" role="progressbar" style="width: <?= ($activityCounts['total'] > 0) ? ($activityCounts['uploaded'] / $activityCounts['total'] * 100) : 0 ?>%"></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($activityCounts['downloaded'] > 0): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Downloaded</h6>
                        <span class="badge bg-warning rounded-pill"><?= $activityCounts['downloaded'] ?></span>
                    </div>
                    <div class="progress mb-1" style="height: 8px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= ($activityCounts['total'] > 0) ? ($activityCounts['downloaded'] / $activityCounts['total'] * 100) : 0 ?>%"></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($activityCounts['deleted'] > 0): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Deleted</h6>
                        <span class="badge bg-danger rounded-pill"><?= $activityCounts['deleted'] ?></span>
                    </div>
                    <div class="progress mb-1" style="height: 8px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?= ($activityCounts['total'] > 0) ? ($activityCounts['deleted'] / $activityCounts['total'] * 100) : 0 ?>%"></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($activityCounts['assigned'] > 0): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Assigned</h6>
                        <span class="badge bg-secondary rounded-pill"><?= $activityCounts['assigned'] ?></span>
                    </div>
                    <div class="progress mb-1" style="height: 8px;">
                        <div class="progress-bar bg-secondary" role="progressbar" style="width: <?= ($activityCounts['total'] > 0) ? ($activityCounts['assigned'] / $activityCounts['total'] * 100) : 0 ?>%"></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php
// Helper functions for the view
function getTaskStatusClass($status) {
    switch ($status) {
        case 'Completed':
            return 'bg-success';
        case 'In Progress':
            return 'bg-primary';
        case 'Pending':
            return 'bg-warning';
        case 'Cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

// Add these helper methods to the Controller class
function getActionIcon($action) {
    switch ($action) {
        case 'created':
            return 'bi-plus-circle';
        case 'updated':
            return 'bi-pencil';
        case 'deleted':
            return 'bi-trash';
        case 'completed':
            return 'bi-check-circle';
        case 'assigned':
            return 'bi-person-plus';
        case 'unassigned':
            return 'bi-person-dash';
        case 'commented':
            return 'bi-chat-text';
        case 'uploaded':
            return 'bi-upload';
        case 'downloaded':
            return 'bi-download';
        case 'linked':
            return 'bi-link';
        case 'unlinked':
            return 'bi-link-45deg';
        default:
            return 'bi-activity';
    }
}

function getActionBadgeClass($action) {
    switch ($action) {
        case 'created':
            return 'success';
        case 'updated':
            return 'info';
        case 'deleted':
            return 'danger';
        case 'completed':
            return 'success';
        case 'assigned':
            return 'primary';
        case 'unassigned':
            return 'warning';
        case 'commented':
            return 'secondary';
        case 'uploaded':
            return 'purple';
        case 'downloaded':
            return 'orange';
        case 'linked':
            return 'teal';
        case 'unlinked':
            return 'pink';
        default:
            return 'secondary';
    }
}

function getRelevantMetadata($action, $metadata) {
    $result = [];
    
    switch ($action) {
        case 'created':
            if (isset($metadata['start_date'])) $result['Start Date'] = date('M j, Y', strtotime($metadata['start_date']));
            if (isset($metadata['end_date'])) $result['End Date'] = date('M j, Y', strtotime($metadata['end_date']));
            if (isset($metadata['budget'])) $result['Budget'] = '$' . number_format($metadata['budget'], 2);
            if (isset($metadata['status'])) $result['Status'] = $metadata['status'];
            if (isset($metadata['department_name'])) $result['Department'] = $metadata['department_name'];
            break;
            
        case 'updated':
            // Show what was updated
            if (isset($metadata['changes'])) {
                foreach ($metadata['changes'] as $field => $change) {
                    $result[$field] = $change['from'] . ' → ' . $change['to'];
                }
            }
            break;
            
        case 'uploaded':
            if (isset($metadata['file_name'])) $result['File'] = $metadata['file_name'];
            if (isset($metadata['document_type'])) $result['Type'] = $metadata['document_type'];
            if (isset($metadata['file_size'])) $result['Size'] = formatFileSizeForDisplay($metadata['file_size']);
            break;
            
        case 'assigned':
            if (isset($metadata['user_name'])) $result['Assigned To'] = $metadata['user_name'];
            if (isset($metadata['role'])) $result['Role'] = $metadata['role'];
            break;
    }
    
    return $result;
}

function formatFileSizeForDisplay($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}
?> 