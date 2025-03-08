<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/employees">Employee Management</a></li>
        <li class="breadcrumb-item"><a href="/employees/viewEmployee/<?= $employee['user_id'] ?>"><?= $employee['full_name'] ?? $employee['username'] ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Add Absence</li>
    </ol>
</nav>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0">Add Absence Record</h1>
        <p class="text-muted">Record a new absence for <?= $employee['full_name'] ?? $employee['username'] ?></p>
    </div>
    <a href="/employees/viewEmployee/<?= $employee['user_id'] ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Profile
    </a>
</div>

<!-- Flash Messages -->
<?php flash('absence_error'); ?>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="/employees/addAbsence/<?= $employee['user_id'] ?>" method="post">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                            <div class="form-text">First day of absence</div>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                            <div class="form-text">Last day of absence</div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="reason" class="form-label">Reason</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Enter reason for absence"></textarea>
                        <div class="form-text">Provide details about the absence reason</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/employees/viewEmployee/<?= $employee['user_id'] ?>" class="btn btn-outline-secondary me-md-2">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Add Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0">Employee Info</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-circle me-3" style="width: 60px; height: 60px; font-size: 1.5rem; background-color: #6366f1; color: white;">
                        <?= substr($employee['full_name'] ?? $employee['username'], 0, 1) ?>
                    </div>
                    <div>
                        <h5 class="mb-1"><?= $employee['full_name'] ?? $employee['username'] ?></h5>
                        <p class="text-muted mb-0"><?= $employee['email'] ?></p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <span class="badge bg-<?= $employee['role'] === 'admin' ? 'danger' : ($employee['role'] === 'manager' ? 'warning' : 'info') ?>">
                        <?= ucfirst($employee['role']) ?>
                    </span>
                </div>
                
                <hr>
                
                <p class="text-muted mb-2">Current Absence Statistics:</p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span>Total Records</span>
                        <span class="badge bg-primary rounded-pill"><?= $employee['absence_count'] ?? 0 ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span>Total Days</span>
                        <span class="badge bg-danger rounded-pill"><?= $employee['total_absence_days'] ?? 0 ?></span>
                    </li>
                </ul>
                
                <?php if ($employee['last_absence_start']): ?>
                <div class="alert alert-light mt-3">
                    <p class="mb-0"><strong>Last Absence:</strong> From <?= date('M d, Y', strtotime($employee['last_absence_start'])) ?> to <?= date('M d, Y', strtotime($employee['last_absence_end'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0">Absence Guidelines</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="bi bi-info-circle text-primary me-2"></i>
                        Make sure to include all days of absence, including weekends.
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-info-circle text-primary me-2"></i>
                        Provide clear reasons for accurate record-keeping.
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-info-circle text-primary me-2"></i>
                        All absences must be approved by a manager or admin.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set default values for date fields (today and tomorrow)
        const today = new Date();
        
        const startDateInput = document.getElementById('start_date');
        startDateInput.valueAsDate = today;
        
        const tomorrow = new Date();
        tomorrow.setDate(today.getDate() + 1);
        
        const endDateInput = document.getElementById('end_date');
        endDateInput.valueAsDate = tomorrow;
        
        // Validate that end date is not before start date
        startDateInput.addEventListener('change', function() {
            const startDate = new Date(this.value);
            const endDate = new Date(endDateInput.value);
            
            if (endDate < startDate) {
                endDateInput.valueAsDate = startDate;
            }
        });
        
        endDateInput.addEventListener('change', function() {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(this.value);
            
            if (endDate < startDate) {
                this.valueAsDate = startDate;
            }
        });
    });
</script> 