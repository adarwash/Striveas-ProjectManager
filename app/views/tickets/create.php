<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= URLROOT ?>/tickets">Tickets</a></li>
            <li class="breadcrumb-item active">Create New Ticket</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-12 mx-auto">
            <!-- Page Header -->
            <div class="text-center mb-4">
                <h1 class="h3 mb-2">Create New Ticket</h1>
                <p class="text-muted">Submit a new support request or issue</p>
            </div>

            <!-- Create Ticket Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-ticket-perforated me-2"></i>Ticket Details
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= URLROOT ?>/tickets/create" id="createTicketForm">
                        <!-- Subject -->
                        <div class="mb-3">
                            <label for="subject" class="form-label required">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   placeholder="Brief description of the issue or request"
                                   value="<?= htmlspecialchars($data['formData']['subject'] ?? '') ?>" required>
                            <div class="form-text">Provide a clear, concise summary of your issue or request</div>
                        </div>

                        <!-- Priority and Category Row -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="priority_id" class="form-label">Priority <span class="text-danger">*</span></label>
                                <select class="form-select" id="priority_id" name="priority_id" required>
                                    <option value="">Select Priority</option>
                                    <?php foreach ($data['priorities'] as $priority): ?>
                                        <option value="<?= $priority['id'] ?>" 
                                                <?= ($data['formData']['priority_id'] ?? '') == $priority['id'] ? 'selected' : '' ?>
                                                data-color="<?= $priority['color_code'] ?>">
                                            <?= htmlspecialchars($priority['display_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    <small>
                                        <strong>Low:</strong> General inquiries, feature requests<br>
                                        <strong>Normal:</strong> Standard issues, non-urgent problems<br>
                                        <strong>High:</strong> System impacting issues<br>
                                        <strong>Critical:</strong> System down, security issues
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    <?php foreach ($data['categories'] as $category): ?>
                                        <option value="<?= $category['id'] ?>" 
                                                <?= ($data['formData']['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Choose the most appropriate category for your issue</div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label required">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="6" 
                                      placeholder="Provide detailed information about your issue or request..." required><?= htmlspecialchars($data['formData']['description'] ?? '') ?></textarea>
                            <div class="form-text">
                                Include as much detail as possible:
                                <ul class="small mb-0 mt-1">
                                    <li>What you were trying to do</li>
                                    <li>What happened instead</li>
                                    <li>Steps to reproduce the issue</li>
                                    <li>Any error messages you received</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Client, Assignment and Project Row -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="client_id" class="form-label">Client</label>
                                <select class="form-select" id="client_id" name="client_id">
                                    <option value="">No Client</option>
                                    <?php foreach ($data['clients'] as $client): ?>
                                        <option value="<?= $client['id'] ?>"
                                            <?= ($data['formData']['client_id'] ?? '') == $client['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($client['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Associate ticket with a customer (MSP)</div>
                            </div>
                            <?php if (hasPermission('tickets.assign')): ?>
                            <div class="col-md-6">
                                <label for="assigned_to" class="form-label">Assign To</label>
                                <select class="form-select" id="assigned_to" name="assigned_to">
                                    <option value="">Unassigned</option>
                                    <?php foreach ($data['users'] as $user): ?>
                                        <option value="<?= $user['id'] ?>" 
                                                <?= ($data['formData']['assigned_to'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '') ?: $user['username'] ?? $user['full_name'] ?? 'Unknown User') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Optionally assign this ticket to a specific team member</div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="col-md-6">
                                <label for="project_id" class="form-label">Related Project</label>
                                <select class="form-select" id="project_id" name="project_id">
                                    <option value="">No Project</option>
                                    <?php foreach ($data['projects'] as $project): ?>
                                        <option value="<?= $project['id'] ?>" 
                                                <?= ($data['formData']['project_id'] ?? '') == $project['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($project['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Link this ticket to a specific project if applicable</div>
                            </div>
                        </div>

                        <!-- Due Date and Tags Row -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" 
                                       value="<?= $data['formData']['due_date'] ?? '' ?>"
                                       min="<?= date('Y-m-d') ?>">
                                <div class="form-text">Set a target resolution date (optional)</div>
                            </div>
                            <div class="col-md-6">
                                <label for="tags" class="form-label">Tags</label>
                                <input type="text" class="form-control" id="tags" name="tags" 
                                       placeholder="bug, urgent, network, etc."
                                       value="<?= htmlspecialchars($data['formData']['tags'] ?? '') ?>">
                                <div class="form-text">Comma-separated tags for easier categorization</div>
                            </div>
                        </div>

                        <!-- File Attachments (Future Enhancement) -->
                        <div class="mb-4">
                            <label class="form-label">Attachments</label>
                            <div class="border rounded p-3 text-center bg-light">
                                <i class="bi bi-paperclip fs-3 text-muted mb-2"></i>
                                <p class="text-muted mb-1">File attachments will be available in a future update</p>
                                <small class="text-muted">For now, you can include file descriptions in the ticket description</small>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="<?= URLROOT ?>/tickets" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Cancel
                            </a>
                            <div>
                                <button type="button" class="btn btn-outline-primary me-2" onclick="saveAsDraft()">
                                    <i class="bi bi-save me-2"></i>Save as Draft
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Create Ticket
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Helpful Tips -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightbulb me-2"></i>Tips for Better Support
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Writing Good Tickets</h6>
                            <ul class="small">
                                <li>Use clear, descriptive subjects</li>
                                <li>Include step-by-step reproduction instructions</li>
                                <li>Mention what browser/device you're using</li>
                                <li>Include relevant screenshots or error messages</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Priority Guidelines</h6>
                            <ul class="small">
                                <li><strong>Critical:</strong> System completely unusable</li>
                                <li><strong>High:</strong> Major functionality impacted</li>
                                <li><strong>Normal:</strong> Standard issues and requests</li>
                                <li><strong>Low:</strong> Minor issues, enhancements</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-resize textarea
document.getElementById('description').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});

// Priority color preview
document.getElementById('priority_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const color = selectedOption.getAttribute('data-color');
    if (color) {
        this.style.borderLeft = `4px solid ${color}`;
    } else {
        this.style.borderLeft = '';
    }
});

// Character counter for subject
document.getElementById('subject').addEventListener('input', function() {
    const maxLength = 255;
    const currentLength = this.value.length;
    
    // Remove existing counter
    const existingCounter = this.parentNode.querySelector('.char-counter');
    if (existingCounter) {
        existingCounter.remove();
    }
    
    // Add character counter
    if (currentLength > 0) {
        const counter = document.createElement('small');
        counter.className = 'char-counter text-muted d-block mt-1';
        counter.textContent = `${currentLength}/${maxLength} characters`;
        
        if (currentLength > maxLength * 0.9) {
            counter.className = 'char-counter text-warning d-block mt-1';
        }
        if (currentLength > maxLength) {
            counter.className = 'char-counter text-danger d-block mt-1';
        }
        
        this.parentNode.appendChild(counter);
    }
});

// Form validation
document.getElementById('createTicketForm').addEventListener('submit', function(e) {
    const subject = document.getElementById('subject').value.trim();
    const description = document.getElementById('description').value.trim();
    const priority = document.getElementById('priority_id').value;
    
    if (!subject || !description || !priority) {
        e.preventDefault();
        alert('Please fill in all required fields (Subject, Description, and Priority).');
        return false;
    }
    
    if (subject.length > 255) {
        e.preventDefault();
        alert('Subject must be 255 characters or less.');
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Creating Ticket...';
    submitBtn.disabled = true;
    
    // Re-enable if there's an error (form doesn't actually submit)
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 5000);
});

// Save as draft functionality (placeholder)
function saveAsDraft() {
    alert('Draft functionality will be implemented in a future update. For now, please create the ticket normally.');
}

// Initialize priority color on page load
document.addEventListener('DOMContentLoaded', function() {
    const prioritySelect = document.getElementById('priority_id');
    if (prioritySelect.value) {
        prioritySelect.dispatchEvent(new Event('change'));
    }
});

// Auto-suggest for tags
document.getElementById('tags').addEventListener('input', function() {
    // This could be enhanced with a tag suggestion dropdown
    const value = this.value;
    const suggestions = ['bug', 'feature', 'urgent', 'network', 'hardware', 'software', 'security', 'performance'];
    
    // Simple validation - ensure tags are comma-separated
    if (value.includes(',')) {
        const tags = value.split(',').map(tag => tag.trim()).filter(tag => tag.length > 0);
        this.value = tags.join(', ');
    }
});
</script>

<style>
.required {
    position: relative;
}

.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.char-counter {
    text-align: right;
}

.card-header.bg-light {
    border-bottom: 1px solid #dee2e6;
}

#priority_id {
    transition: border-left 0.3s ease;
}
</style>