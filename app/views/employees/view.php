<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/employees">Employee Management</a></li>
        <li class="breadcrumb-item active" aria-current="page">Employee Profile</li>
    </ol>
</nav>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0"><?= $employee['full_name'] ?? $employee['username'] ?></h1>
        <p class="text-muted">Employee Performance Profile</p>
    </div>
    <div class="d-flex">
        <a href="/employees/edit/<?= $employee['user_id'] ?>" class="btn btn-primary me-2">
            <i class="bi bi-pencil"></i> Edit Profile
        </a>
        <a href="/employees" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<!-- Flash Messages -->
<?php flash('employee_success'); ?>
<?php flash('employee_error'); ?>
<?php flash('absence_success'); ?>
<?php flash('absence_error'); ?>

<div class="row">
    <!-- Left column - Employee details -->
    <div class="col-md-4">
        <!-- Employee Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center">
                <div class="avatar-circle mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem; background-color: #6366f1; color: white;">
                    <?= substr($employee['full_name'] ?? $employee['username'], 0, 1) ?>
                </div>
                <h4 class="card-title"><?= $employee['full_name'] ?? $employee['username'] ?></h4>
                <p class="text-muted mb-2"><?= $employee['email'] ?></p>
                <span class="badge bg-<?= $employee['role'] === 'admin' ? 'danger' : ($employee['role'] === 'manager' ? 'warning' : 'info') ?> mb-3">
                    <?= ucfirst($employee['role']) ?>
                </span>
                
                <div class="d-flex justify-content-center mb-3">
                    <div class="me-3 text-center">
                        <h5 class="mb-0"><?= $employee['tasks_completed'] ?></h5>
                        <small class="text-muted">Completed</small>
                    </div>
                    <div class="me-3 text-center">
                        <h5 class="mb-0"><?= $employee['tasks_pending'] ?></h5>
                        <small class="text-muted">Pending</small>
                    </div>
                    <div class="text-center">
                        <h5 class="mb-0"><?= count($projects) ?></h5>
                        <small class="text-muted">Projects</small>
                    </div>
                </div>
                
                <hr>
                
                <h5 class="card-title mb-3">Performance Rating</h5>
                <div class="d-flex justify-content-center align-items-center mb-2">
                    <div class="rating-stars me-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= floor($employee['performance_rating'])): ?>
                                <i class="bi bi-star-fill text-warning"></i>
                            <?php elseif ($i - 0.5 <= $employee['performance_rating']): ?>
                                <i class="bi bi-star-half text-warning"></i>
                            <?php else: ?>
                                <i class="bi bi-star text-warning"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <h4 class="mb-0"><?= $employee['performance_rating'] ?>/5.0</h4>
                </div>
                
                <div class="d-flex justify-content-center mb-3">
                    <a href="/employees/ratingHistory/<?= $employee['user_id'] ?>" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-clock-history"></i> View Rating History
                    </a>
                </div>
                
                <!-- Rating Guide - Dynamic based on current rating -->
                <div class="mt-3 border-top pt-3">
                    <h6 class="text-muted mb-2 d-flex justify-content-between align-items-center">
                        Rating Guide
                        <button class="btn btn-sm btn-outline-secondary" id="toggleRatings">
                            <span class="show-all">Show All Ratings</span>
                            <span class="show-less d-none">Show Less</span>
                        </button>
                    </h6>
                    <div class="rating-guide small">
                        <?php
                        // Determine which rating range applies
                        $rating = floatval($employee['performance_rating']);
                        $rating1 = ($rating >= 4.5 && $rating <= 5.0);
                        $rating2 = ($rating >= 3.5 && $rating < 4.5);
                        $rating3 = ($rating >= 2.5 && $rating < 3.5);
                        $rating4 = ($rating >= 1.5 && $rating < 2.5);
                        $rating5 = ($rating >= 0 && $rating < 1.5);
                        ?>
                        <div class="d-flex align-items-center mb-1 fw-bold bg-light p-1 rounded border-start border-4 border-primary <?= $rating1 ? '' : 'other-rating d-none' ?>">
                            <div class="rating-label" style="width: 70px;"><strong>4.5 - 5.0:</strong></div>
                            <div>Exceptional performance</div>
                            <?php if ($rating1): ?>
                                <div class="ms-auto badge bg-primary">Current</div>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex align-items-center mb-1 fw-bold bg-light p-1 rounded border-start border-4 border-primary <?= $rating2 ? '' : 'other-rating d-none' ?>">
                            <div class="rating-label" style="width: 70px;"><strong>3.5 - 4.4:</strong></div>
                            <div>Strong performance</div>
                            <?php if ($rating2): ?>
                                <div class="ms-auto badge bg-primary">Current</div>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex align-items-center mb-1 fw-bold bg-light p-1 rounded border-start border-4 border-primary <?= $rating3 ? '' : 'other-rating d-none' ?>">
                            <div class="rating-label" style="width: 70px;"><strong>2.5 - 3.4:</strong></div>
                            <div>Meets expectations</div>
                            <?php if ($rating3): ?>
                                <div class="ms-auto badge bg-primary">Current</div>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex align-items-center mb-1 fw-bold bg-light p-1 rounded border-start border-4 border-primary <?= $rating4 ? '' : 'other-rating d-none' ?>">
                            <div class="rating-label" style="width: 70px;"><strong>1.5 - 2.4:</strong></div>
                            <div>Needs improvement</div>
                            <?php if ($rating4): ?>
                                <div class="ms-auto badge bg-primary">Current</div>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex align-items-center mb-1 fw-bold bg-light p-1 rounded border-start border-4 border-primary <?= $rating5 ? '' : 'other-rating d-none' ?>">
                            <div class="rating-label" style="width: 70px;"><strong>0 - 1.4:</strong></div>
                            <div>Poor performance</div>
                            <?php if ($rating5): ?>
                                <div class="ms-auto badge bg-primary">Current</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <a href="/employees/updateTasks/<?= $employee['user_id'] ?>" class="btn btn-sm btn-outline-primary mt-3">
                    <i class="bi bi-arrow-clockwise"></i> Update Task Stats
                </a>
            </div>
        </div>
        
        <!-- Review Information -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0">Review Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Last Review Date</label>
                    <p class="mb-0">
                        <?php if ($employee['last_review_date']): ?>
                            <?= date('F d, Y', strtotime($employee['last_review_date'])) ?>
                        <?php else: ?>
                            <span class="text-muted">Not reviewed yet</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Next Review Date</label>
                    <p class="mb-0">
                        <?php if ($employee['next_review_date']): ?>
                            <?= date('F d, Y', strtotime($employee['next_review_date'])) ?>
                        <?php else: ?>
                            <span class="text-muted">Not scheduled</span>
                        <?php endif; ?>
                    </p>
                </div>
                
                <div>
                    <label class="form-label text-muted">Performance Notes</label>
                    <div class="p-3 bg-light rounded">
                        <?php if (!empty($employee['notes'])): ?>
                            <?= nl2br($employee['notes']) ?>
                        <?php else: ?>
                            <span class="text-muted">No notes available</span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-3 text-end">
                        <a href="/employees/addNote/<?= $employee['user_id'] ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Note
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Performance Notes History -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Performance Notes</h5>
                <a href="/employees/addNote/<?= $employee['user_id'] ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Note
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($notes)): ?>
                    <div class="text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                        </div>
                        <p class="text-muted mb-0">No performance notes have been added yet.</p>
                        <a href="/employees/addNote/<?= $employee['user_id'] ?>" class="btn btn-sm btn-outline-primary mt-3">
                            <i class="bi bi-plus-circle"></i> Add First Note
                        </a>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($notes as $note): ?>
                            <div class="timeline-item mb-4">
                                <div class="d-flex">
                                    <div class="timeline-indicator">
                                        <?php 
                                        $badgeClass = 'bg-info';
                                        $icon = 'bi-journal-text';
                                        
                                        switch ($note['note_type']) {
                                            case 'achievement':
                                                $badgeClass = 'bg-success';
                                                $icon = 'bi-trophy';
                                                break;
                                            case 'improvement':
                                                $badgeClass = 'bg-warning';
                                                $icon = 'bi-arrow-up-circle';
                                                break;
                                            case 'feedback':
                                                $badgeClass = 'bg-info';
                                                $icon = 'bi-chat-dots';
                                                break;
                                            case 'training':
                                                $badgeClass = 'bg-primary';
                                                $icon = 'bi-mortarboard';
                                                break;
                                        }
                                        ?>
                                        <div class="badge rounded-circle <?= $badgeClass ?>" style="width: 36px; height: 36px;">
                                            <i class="bi <?= $icon ?>"></i>
                                        </div>
                                    </div>
                                    <div class="timeline-content ms-3">
                                        <div class="card shadow-sm border-0">
                                            <div class="card-header bg-light d-flex justify-content-between py-2">
                                                <span class="badge <?= $badgeClass ?> my-1">
                                                    <?= ucfirst($note['note_type']) ?>
                                                </span>
                                                <small class="text-muted">
                                                    <?= $note['formatted_date'] ?> by <?= $note['created_by_name'] ?>
                                                </small>
                                            </div>
                                            <div class="card-body">
                                                <p class="mb-0"><?= nl2br(htmlspecialchars($note['note_text'])) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Right column - Absence records and statistics -->
    <div class="col-md-8">
        <!-- Absence Statistics -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0">Absence Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <div class="display-5 mb-2 text-primary"><?= count($absences) ?></div>
                            <p class="mb-0 text-muted">Total Absences</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <div class="display-5 mb-2 text-danger"><?= $employee['total_absence_days'] ?? 0 ?></div>
                            <p class="mb-0 text-muted">Total Days</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <div class="display-5 mb-2 text-warning">
                                <?php 
                                    if ($employee['last_absence_end'] && $employee['last_absence_start']) {
                                        $start = new DateTime($employee['last_absence_start']);
                                        $end = new DateTime($employee['last_absence_end']);
                                        $interval = $start->diff($end);
                                        echo $interval->days + 1;
                                    } else {
                                        echo 0;
                                    }
                                ?>
                            </div>
                            <p class="mb-0 text-muted">Last Absence (Days)</p>
                        </div>
                    </div>
                </div>
                
                <?php if ($employee['last_absence_start']): ?>
                <div class="alert alert-light mt-3">
                    <p class="mb-0"><strong>Last Absence:</strong> From <?= date('M d, Y', strtotime($employee['last_absence_start'])) ?> to <?= date('M d, Y', strtotime($employee['last_absence_end'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Absence Records -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Absence Records</h5>
                <a href="/employees/addAbsence/<?= $employee['user_id'] ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Add Absence
                </a>
            </div>
            <div class="card-body p-0">
                <?php if(empty($absences)): ?>
                    <div class="p-4 text-center">
                        <p class="text-muted mb-0">No absence records found</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Period</th>
                                    <th>Duration</th>
                                    <th>Reason</th>
                                    <th>Approved By</th>
                                    <th>Approved On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($absences as $absence): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><?= date('M d, Y', strtotime($absence['start_date'])) ?></span>
                                            <small class="text-muted">to</small>
                                            <span><?= date('M d, Y', strtotime($absence['end_date'])) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                            $start = new DateTime($absence['start_date']);
                                            $end = new DateTime($absence['end_date']);
                                            $interval = $start->diff($end);
                                            echo $interval->days + 1;
                                        ?> days
                                    </td>
                                    <td>
                                        <?= !empty($absence['reason']) ? $absence['reason'] : '<span class="text-muted">Not specified</span>' ?>
                                    </td>
                                    <td>
                                        <?= !empty($absence['approved_by_name']) ? $absence['approved_by_name'] : '<span class="text-muted">Unknown</span>' ?>
                                    </td>
                                    <td>
                                        <?= date('M d, Y', strtotime($absence['approved_at'])) ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAbsenceModal<?= $absence['id'] ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Projects Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Projects</h5>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#projectsCollapse" aria-expanded="true" aria-controls="projectsCollapse">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>
            <div class="collapse show" id="projectsCollapse">
                <div class="card-body p-0">
                    <?php if (empty($projects)): ?>
                        <div class="p-4 text-center">
                            <p class="text-muted mb-0">This employee is not currently working on any projects.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Project</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Role</th>
                                        <th>Progress</th>
                                        <th>Timeline</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projects as $project): ?>
                                        <?php 
                                            // Calculate progress percentage
                                            $progress = 0;
                                            if ($project->task_count > 0) {
                                                $progress = ($project->completed_tasks / $project->task_count) * 100;
                                            }
                                            
                                            // Determine status badge class
                                            $statusClass = 'bg-secondary';
                                            if ($project->status == 'In Progress') $statusClass = 'bg-primary';
                                            if ($project->status == 'Completed') $statusClass = 'bg-success';
                                            if ($project->status == 'On Hold') $statusClass = 'bg-warning';
                                            
                                            // Determine timeline info
                                            $now = new DateTime();
                                            $start = new DateTime($project->start_date);
                                            $end = new DateTime($project->end_date);
                                            $isOverdue = $now > $end && $project->status != 'Completed';
                                        ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <a href="/projects/viewProject/<?= $project->id ?>" class="text-decoration-none fw-bold">
                                                        <?= htmlspecialchars($project->title ?? '') ?>
                                                    </a>
                                                </div>
                                                <?php 
                                                    $description = $project->description ?? '';
                                                    $truncated = strlen($description) > 60 ? substr($description, 0, 60) . '...' : $description;
                                                ?>
                                                <small class="text-muted"><?= htmlspecialchars($truncated) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($project->department_name ?? '') ?></td>
                                            <td><span class="badge <?= $statusClass ?>"><?= $project->status ?></span></td>
                                            <td><span class="badge bg-info"><?= $project->user_role ?></span></td>
                                            <td style="width: 150px;">
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar <?= $progress == 100 ? 'bg-success' : 'bg-primary' ?>" 
                                                         role="progressbar" 
                                                         style="width: <?= $progress ?>%;" 
                                                         aria-valuenow="<?= $progress ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small class="text-muted"><?= $project->completed_tasks ?>/<?= $project->task_count ?> tasks</small>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div>
                                                        <i class="bi bi-calendar-check"></i> 
                                                        <?= date('M d, Y', strtotime($project->start_date)) ?>
                                                    </div>
                                                    <div class="<?= $isOverdue ? 'text-danger' : '' ?>">
                                                        <i class="bi bi-calendar-x"></i> 
                                                        <?= date('M d, Y', strtotime($project->end_date)) ?>
                                                        <?= $isOverdue ? '<span class="badge bg-danger">Overdue</span>' : '' ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <a href="/projects/viewProject/<?= $project->id ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
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

<!-- Delete Absence Modals -->
<?php foreach ($absences as $absence): ?>
<div class="modal fade" id="deleteAbsenceModal<?= $absence['id'] ?>" tabindex="-1" aria-labelledby="deleteAbsenceModalLabel<?= $absence['id'] ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAbsenceModalLabel<?= $absence['id'] ?>">Delete Absence Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this absence record?</p>
                <p><strong>Period:</strong> <?= date('M d, Y', strtotime($absence['start_date'])) ?> to <?= date('M d, Y', strtotime($absence['end_date'])) ?></p>
                <p><strong>Reason:</strong> <?= !empty($absence['reason']) ? $absence['reason'] : 'Not specified' ?></p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="/employees/deleteAbsence/<?= $absence['id'] ?>/<?= $employee['user_id'] ?>" method="post">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<style>
.avatar-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: 600;
}

.rating-stars {
    font-size: 1.5rem;
}

/* Timeline styling */
.timeline {
    position: relative;
    padding-left: 1rem;
}

.timeline-item {
    position: relative;
}

.timeline-indicator .badge {
    display: flex;
    align-items: center;
    justify-content: center;
}

.timeline-indicator i {
    font-size: 1rem;
    color: white;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 18px;
    top: 0;
    height: 100%;
    width: 2px;
    background: #e9ecef;
}
</style>

<!-- Add JavaScript for toggle ratings functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggleRatings');
        const otherRatings = document.querySelectorAll('.other-rating');
        const showAllText = document.querySelector('.show-all');
        const showLessText = document.querySelector('.show-less');
        
        toggleBtn.addEventListener('click', function() {
            // Toggle visibility of other ratings
            otherRatings.forEach(item => {
                item.classList.toggle('d-none');
            });
            
            // Toggle button text
            showAllText.classList.toggle('d-none');
            showLessText.classList.toggle('d-none');
        });
    });
</script> 