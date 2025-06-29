<!-- Create Router Schedule Styles -->
<style>
.form-card {
    border: none;
    box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
}

.form-section {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-section h6 {
    color: #495057;
    margin-bottom: 1rem;
    font-weight: 600;
}

.required-field::after {
    content: " *";
    color: #dc3545;
}

.create-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
}

.preview-card {
    background: #e3f2fd;
    border: 1px solid #bbdefb;
    border-radius: 0.5rem;
    padding: 1rem;
}
</style>

<!-- Page Header -->
<div class="create-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="h2 mb-1"><i class="bi bi-plus-circle me-3"></i>Create Router Schedule</h1>
            <p class="mb-0">Schedule weekly router maintenance for technicians</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="<?= URLROOT ?>/weekly_routers" class="btn btn-light">
                <i class="bi bi-arrow-left me-2"></i>Back to Schedules
            </a>
        </div>
    </div>
</div>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= URLROOT ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= URLROOT ?>/weekly_routers" class="text-decoration-none">Router Schedules</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create Schedule</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-8">
        <!-- Main Form -->
        <div class="card form-card">
            <div class="card-header bg-white p-3 border-bottom">
                <h5 class="card-title mb-0"><i class="bi bi-form me-2"></i>Router Schedule Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= URLROOT ?>/weekly_routers/store" id="createScheduleForm">
                    
                    <!-- Router Information Section -->
                    <div class="form-section">
                        <h6><i class="bi bi-router me-2"></i>Router Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="router_name" class="form-label required-field">Router Name</label>
                                    <input type="text" class="form-control <?= isset($errors['router_name']) ? 'is-invalid' : '' ?>" 
                                           id="router_name" name="router_name" 
                                           value="<?= $data['router_name'] ?? '' ?>"
                                           placeholder="e.g., Main Office Router" required>
                                    <?php if (isset($errors['router_name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['router_name'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="router_ip" class="form-label required-field">Router IP Address</label>
                                    <input type="text" class="form-control <?= isset($errors['router_ip']) ? 'is-invalid' : '' ?>" 
                                           id="router_ip" name="router_ip" 
                                           value="<?= $data['router_ip'] ?? '' ?>"
                                           placeholder="e.g., 192.168.1.1" required>
                                    <?php if (isset($errors['router_ip'])): ?>
                                        <div class="invalid-feedback"><?= $errors['router_ip'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label required-field">Location</label>
                            <input type="text" class="form-control <?= isset($errors['location']) ? 'is-invalid' : '' ?>" 
                                   id="location" name="location" 
                                   value="<?= $data['location'] ?? '' ?>"
                                   placeholder="e.g., Building A, Floor 2, Server Room" required>
                            <?php if (isset($errors['location'])): ?>
                                <div class="invalid-feedback"><?= $errors['location'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Schedule Information Section -->
                    <div class="form-section">
                        <h6><i class="bi bi-calendar-week me-2"></i>Schedule Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="week_start_date" class="form-label required-field">Week Start Date</label>
                                    <input type="date" class="form-control <?= isset($errors['week_start_date']) ? 'is-invalid' : '' ?>" 
                                           id="week_start_date" name="week_start_date" 
                                           value="<?= $data['week_start_date'] ?? '' ?>" required>
                                    <?php if (isset($errors['week_start_date'])): ?>
                                        <div class="invalid-feedback"><?= $errors['week_start_date'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="week_end_date" class="form-label required-field">Week End Date</label>
                                    <input type="date" class="form-control <?= isset($errors['week_end_date']) ? 'is-invalid' : '' ?>" 
                                           id="week_end_date" name="week_end_date" 
                                           value="<?= $data['week_end_date'] ?? '' ?>" required>
                                    <?php if (isset($errors['week_end_date'])): ?>
                                        <div class="invalid-feedback"><?= $errors['week_end_date'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="maintenance_type" class="form-label required-field">Maintenance Type</label>
                                    <select class="form-select <?= isset($errors['maintenance_type']) ? 'is-invalid' : '' ?>" 
                                            id="maintenance_type" name="maintenance_type" required>
                                        <option value="">Select Type</option>
                                        <option value="routine" <?= isset($data['maintenance_type']) && $data['maintenance_type'] === 'routine' ? 'selected' : '' ?>>Routine Maintenance</option>
                                        <option value="repair" <?= isset($data['maintenance_type']) && $data['maintenance_type'] === 'repair' ? 'selected' : '' ?>>Repair</option>
                                        <option value="upgrade" <?= isset($data['maintenance_type']) && $data['maintenance_type'] === 'upgrade' ? 'selected' : '' ?>>Upgrade</option>
                                        <option value="inspection" <?= isset($data['maintenance_type']) && $data['maintenance_type'] === 'inspection' ? 'selected' : '' ?>>Inspection</option>
                                    </select>
                                    <?php if (isset($errors['maintenance_type'])): ?>
                                        <div class="invalid-feedback"><?= $errors['maintenance_type'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="priority" class="form-label required-field">Priority</label>
                                    <select class="form-select <?= isset($errors['priority']) ? 'is-invalid' : '' ?>" 
                                            id="priority" name="priority" required>
                                        <option value="">Select Priority</option>
                                        <option value="Low" <?= isset($data['priority']) && $data['priority'] === 'Low' ? 'selected' : '' ?>>Low</option>
                                        <option value="Medium" <?= isset($data['priority']) && $data['priority'] === 'Medium' ? 'selected' : '' ?>>Medium</option>
                                        <option value="High" <?= isset($data['priority']) && $data['priority'] === 'High' ? 'selected' : '' ?>>High</option>
                                        <option value="Critical" <?= isset($data['priority']) && $data['priority'] === 'Critical' ? 'selected' : '' ?>>Critical</option>
                                    </select>
                                    <?php if (isset($errors['priority'])): ?>
                                        <div class="invalid-feedback"><?= $errors['priority'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="estimated_hours" class="form-label">Estimated Hours</label>
                                    <input type="number" class="form-control" 
                                           id="estimated_hours" name="estimated_hours" 
                                           value="<?= $data['estimated_hours'] ?? '' ?>"
                                           step="0.25" min="0" max="24" 
                                           placeholder="e.g., 2.5">
                                    <div class="form-text">Optional: Estimated time for completion</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Section -->
                    <div class="form-section">
                        <h6><i class="bi bi-person-gear me-2"></i>Technician Assignment</h6>
                        <div class="mb-3">
                            <label for="assigned_technician_id" class="form-label">Assigned Technician</label>
                            <select class="form-select" id="assigned_technician_id" name="assigned_technician_id">
                                <option value="">Select Technician (Optional)</option>
                                <?php foreach ($technicians as $technician): ?>
                                <option value="<?= $technician['id'] ?>" 
                                        <?= isset($data['assigned_technician_id']) && $data['assigned_technician_id'] == $technician['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($technician['full_name'] ?: $technician['username']) ?>
                                    <?php if (!empty($technician['email'])): ?>
                                        (<?= htmlspecialchars($technician['email']) ?>)
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">You can assign a technician now or leave it unassigned for later</div>
                        </div>
                    </div>

                    <!-- Description Section -->
                    <div class="form-section">
                        <h6><i class="bi bi-file-text me-2"></i>Additional Information</h6>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                      placeholder="Describe the maintenance work to be performed, any special requirements, or notes for the technician..."><?= $data['description'] ?? '' ?></textarea>
                            <div class="form-text">Provide detailed instructions or notes for the technician</div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="<?= URLROOT ?>/weekly_routers" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Create Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Preview Card -->
        <div class="card form-card mb-4">
            <div class="card-header bg-white p-3 border-bottom">
                <h5 class="card-title mb-0"><i class="bi bi-eye me-2"></i>Schedule Preview</h5>
            </div>
            <div class="card-body">
                <div class="preview-card">
                    <h6 class="mb-3">Schedule Summary</h6>
                    <div id="preview-content">
                        <p class="text-muted">Fill out the form to see a preview of your router schedule.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Help Card -->
        <div class="card form-card">
            <div class="card-header bg-white p-3 border-bottom">
                <h5 class="card-title mb-0"><i class="bi bi-question-circle me-2"></i>Help & Tips</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Router Information</h6>
                    <ul class="small text-muted">
                        <li>Use descriptive names for easy identification</li>
                        <li>Ensure IP addresses are accurate</li>
                        <li>Include specific location details</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <h6>Maintenance Types</h6>
                    <ul class="small text-muted">
                        <li><strong>Routine:</strong> Regular maintenance checks</li>
                        <li><strong>Repair:</strong> Fix specific issues</li>
                        <li><strong>Upgrade:</strong> Hardware/software updates</li>
                        <li><strong>Inspection:</strong> Detailed examination</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <h6>Priority Levels</h6>
                    <ul class="small text-muted">
                        <li><strong>Critical:</strong> Immediate attention required</li>
                        <li><strong>High:</strong> Important, schedule soon</li>
                        <li><strong>Medium:</strong> Normal priority</li>
                        <li><strong>Low:</strong> Can be scheduled flexibly</li>
                    </ul>
                </div>
                
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-2"></i>
                    Technicians will receive notifications about their assigned schedules and can update the status as they work.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill week end date when start date is selected
    const weekStartInput = document.getElementById('week_start_date');
    const weekEndInput = document.getElementById('week_end_date');
    
    weekStartInput.addEventListener('change', function() {
        if (this.value && !weekEndInput.value) {
            const startDate = new Date(this.value);
            const endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + 6); // Add 6 days for a week
            
            weekEndInput.value = endDate.toISOString().split('T')[0];
        }
        updatePreview();
    });
    
    // Update preview when form fields change
    const formInputs = document.querySelectorAll('#createScheduleForm input, #createScheduleForm select, #createScheduleForm textarea');
    formInputs.forEach(input => {
        input.addEventListener('input', updatePreview);
        input.addEventListener('change', updatePreview);
    });
    
    function updatePreview() {
        const routerName = document.getElementById('router_name').value;
        const routerIp = document.getElementById('router_ip').value;
        const location = document.getElementById('location').value;
        const weekStart = document.getElementById('week_start_date').value;
        const weekEnd = document.getElementById('week_end_date').value;
        const maintenanceType = document.getElementById('maintenance_type').value;
        const priority = document.getElementById('priority').value;
        const estimatedHours = document.getElementById('estimated_hours').value;
        const technicianSelect = document.getElementById('assigned_technician_id');
        const technicianName = technicianSelect.options[technicianSelect.selectedIndex].text;
        
        let previewHtml = '';
        
        if (routerName || routerIp || location) {
            previewHtml += '<div class="mb-2">';
            if (routerName) previewHtml += `<strong>${routerName}</strong><br>`;
            if (routerIp) previewHtml += `<small class="text-muted">IP: ${routerIp}</small><br>`;
            if (location) previewHtml += `<small class="text-muted">Location: ${location}</small>`;
            previewHtml += '</div>';
        }
        
        if (weekStart && weekEnd) {
            const startDate = new Date(weekStart);
            const endDate = new Date(weekEnd);
            previewHtml += `<div class="mb-2"><strong>Week:</strong> ${startDate.toLocaleDateString()} - ${endDate.toLocaleDateString()}</div>`;
        }
        
        if (maintenanceType) {
            const typeColors = {
                'routine': 'info',
                'repair': 'warning',
                'upgrade': 'success',
                'inspection': 'secondary'
            };
            previewHtml += `<div class="mb-2"><span class="badge bg-${typeColors[maintenanceType] || 'secondary'}">${maintenanceType.charAt(0).toUpperCase() + maintenanceType.slice(1)}</span></div>`;
        }
        
        if (priority) {
            const priorityColors = {
                'Low': 'secondary',
                'Medium': 'primary',
                'High': 'warning',
                'Critical': 'danger'
            };
            previewHtml += `<div class="mb-2"><span class="badge bg-${priorityColors[priority] || 'secondary'}">${priority} Priority</span></div>`;
        }
        
        if (estimatedHours) {
            previewHtml += `<div class="mb-2"><strong>Estimated:</strong> ${estimatedHours} hours</div>`;
        }
        
        if (technicianSelect.value) {
            previewHtml += `<div class="mb-2"><strong>Assigned to:</strong> ${technicianName}</div>`;
        }
        
        if (!previewHtml) {
            previewHtml = '<p class="text-muted">Fill out the form to see a preview of your router schedule.</p>';
        }
        
        document.getElementById('preview-content').innerHTML = previewHtml;
    }
    
    // Form validation
    document.getElementById('createScheduleForm').addEventListener('submit', function(e) {
        const weekStart = document.getElementById('week_start_date').value;
        const weekEnd = document.getElementById('week_end_date').value;
        
        if (weekStart && weekEnd && new Date(weekEnd) < new Date(weekStart)) {
            e.preventDefault();
            alert('Week end date cannot be before the start date.');
            return false;
        }
    });
});
</script> 