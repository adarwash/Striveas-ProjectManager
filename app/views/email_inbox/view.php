<?php require VIEWSPATH . '/inc/header.php'; ?>

<div class="container-fluid">
    <!-- Navigation Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= URLROOT ?>/emailinbox" class="text-decoration-none">
                    <i class="bi bi-envelope me-1"></i>Email Inbox
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Email Details</li>
        </ol>
    </nav>

    <!-- Email Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <h1 class="h4 mb-2"><?= htmlspecialchars($data['email']['subject']) ?></h1>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-person-circle me-2 text-muted"></i>
                            <div>
                                <strong>From:</strong> <?= htmlspecialchars($data['email']['from_address']) ?>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-envelope-at me-2 text-muted"></i>
                            <div>
                                <strong>To:</strong> <?= htmlspecialchars($data['email']['to_address']) ?>
                            </div>
                        </div>
                        <?php if (!empty($data['email']['cc_address'])): ?>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-envelope-plus me-2 text-muted"></i>
                            <div>
                                <strong>CC:</strong> <?= htmlspecialchars($data['email']['cc_address']) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-calendar3 me-2 text-muted"></i>
                            <div>
                                <strong>Date:</strong> 
                                <?= date('F j, Y \a\t g:i A', strtotime($data['email']['email_date'])) ?>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-activity me-2 text-muted"></i>
                            <div>
                                <strong>Status:</strong>
                                <?php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'processed' => 'success',
                                    'error' => 'danger',
                                    'ignored' => 'secondary'
                                ];
                                $statusColor = $statusColors[$data['email']['processing_status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $statusColor ?>">
                                    <?= ucfirst($data['email']['processing_status']) ?>
                                </span>
                            </div>
                        </div>
                        <?php if ($data['ticket']): ?>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-ticket-perforated me-2 text-muted"></i>
                            <div>
                                <strong>Linked Ticket:</strong>
                                <a href="<?= URLROOT ?>/tickets/show/<?= $data['ticket']['id'] ?>" 
                                   class="text-decoration-none">
                                    <span class="badge bg-info">
                                        <?= htmlspecialchars($data['ticket']['ticket_number']) ?>
                                    </span>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="ms-3">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-gear me-1"></i>Actions
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if (!$data['ticket'] && $data['can_create_ticket']): ?>
                        <li>
                            <a class="dropdown-item" href="#" onclick="createTicketModal(<?= $data['email']['id'] ?>)">
                                <i class="bi bi-plus-circle me-2"></i>Create Ticket
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!$data['ticket'] && $data['can_link_ticket']): ?>
                        <li>
                            <a class="dropdown-item" href="#" onclick="linkTicketModal(<?= $data['email']['id'] ?>)">
                                <i class="bi bi-link me-2"></i>Link to Ticket
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('email.manage') || in_array($_SESSION['role'] ?? '', ['admin','manager','technician'])): ?>
                        <?php if (!empty($data['ticket']) || !empty($data['email']['ticket_id'])): ?>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        
                        <?php if ($data['email']['processing_status'] == 'pending'): ?>
                        <li>
                            <a class="dropdown-item" href="#" onclick="markProcessed(<?= $data['email']['id'] ?>)">
                                <i class="bi bi-check-circle me-2"></i>Mark Processed
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li>
                            <a class="dropdown-item" href="#" onclick="markIgnored(<?= $data['email']['id'] ?>)">
                                <i class="bi bi-eye-slash me-2"></i>Mark Ignored
                            </a>
                        </li>
                        
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="replyToEmail()">
                                <i class="bi bi-reply me-2"></i>Reply
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="forwardEmail()">
                                <i class="bi bi-arrow-right me-2"></i>Forward
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="redownloadAll()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Redownload All Attachments
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Attachments -->
    <?php if (!empty($data['attachments'])): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-paperclip me-2"></i>Attachments
                <span class="badge bg-secondary ms-2"><?= count($data['attachments']) ?></span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php 
                require_once APPROOT . '/app/models/EmailAttachment.php';
                foreach ($data['attachments'] as $attachment): 
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="border rounded p-3 h-100 d-flex align-items-center">
                        <div class="me-3">
                            <i class="<?= EmailAttachment::getFileIcon($attachment['mime_type']) ?> fs-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium text-truncate" title="<?= htmlspecialchars($attachment['original_filename']) ?>">
                                <?= htmlspecialchars($attachment['original_filename']) ?>
                            </div>
                            <div class="text-muted small">
                                <?= EmailAttachment::formatFileSize($attachment['file_size']) ?>
                                <?php if ($attachment['is_inline']): ?>
                                    <span class="badge bg-info ms-1">Inline</span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-2">
                                <?php if ($attachment['is_downloaded']): ?>
                                    <a href="<?= URLROOT ?>/emailinbox/downloadAttachment/<?= $attachment['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary" disabled>
                                        <i class="bi bi-clock-history me-1"></i>Pending
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Email Content -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-envelope-open me-2"></i>Email Content
            </h5>
        </div>
        <div class="card-body">
            <?php if (!empty($data['email']['body_html'])): ?>
                <!-- HTML Content (with runtime CID -> URL rewrite fallback) -->
                <div class="email-content">
                    <?php
                        $html = $data['email']['body_html'];
                        // Replace src="cid:..." with controller route to serve inline by CID
                        $html = preg_replace_callback('/src\s*=\s*(\"|\')cid:([^\"\']+)(\1)/i', function($m) use ($data) {
                            $q = $m[1];
                            $cid = trim($m[2], '<>');
                            $url = URLROOT . '/emailinbox/inline/' . $data['email']['id'] . '/' . rawurlencode($cid);
                            return 'src=' . $q . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . $q;
                        }, $html);
                        echo $html;
                    ?>
                </div>
            <?php else: ?>
                <!-- Plain Text Content -->
                <div class="email-content-text">
                    <pre class="mb-0"><?= htmlspecialchars($data['email']['body_text']) ?></pre>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Linked Ticket Details -->
    <?php if ($data['ticket']): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-ticket-perforated me-2"></i>Linked Ticket
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Ticket Number:</strong>
                        <a href="<?= URLROOT ?>/tickets/show/<?= $data['ticket']['id'] ?>" 
                           class="text-decoration-none">
                            <?= htmlspecialchars($data['ticket']['ticket_number']) ?>
                        </a>
                    </div>
                    <div class="mb-3">
                        <strong>Subject:</strong>
                        <?= htmlspecialchars($data['ticket']['subject']) ?>
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="badge bg-primary">
                            <?= htmlspecialchars($data['ticket']['status_name'] ?? 'Unknown') ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Priority:</strong>
                        <span class="badge bg-warning">
                            <?= htmlspecialchars($data['ticket']['priority_name'] ?? 'Normal') ?>
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Assigned To:</strong>
                        <?= htmlspecialchars($data['ticket']['assigned_name'] ?? 'Unassigned') ?>
                    </div>
                    <div class="mb-3">
                        <strong>Created:</strong>
                        <?= date('M j, Y g:i A', strtotime($data['ticket']['created_at'])) ?>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <a href="<?= URLROOT ?>/tickets/show/<?= $data['ticket']['id'] ?>" 
                   class="btn btn-primary">
                    <i class="bi bi-eye me-2"></i>View Full Ticket
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Actions Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-lightning me-2"></i>Quick Actions
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php if (!$data['ticket']): ?>
                <div class="col-md-4">
                    <div class="d-grid">
                        <button type="button" class="btn btn-success" onclick="createTicketModal(<?= $data['email']['id'] ?>)">
                            <i class="bi bi-plus-circle me-2"></i>Create New Ticket
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-grid">
                        <button type="button" class="btn btn-info" onclick="linkTicketModal(<?= $data['email']['id'] ?>)">
                            <i class="bi bi-link me-2"></i>Link to Existing Ticket
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="col-md-4">
                    <div class="d-grid">
                        <button type="button" class="btn btn-outline-primary" onclick="replyToEmail()">
                            <i class="bi bi-reply me-2"></i>Reply to Email
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Ticket Modal -->
<div class="modal fade" id="createTicketModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createTicketForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Create Ticket from Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_priority_id" class="form-label">Priority</label>
                        <select class="form-select" id="modal_priority_id" name="priority_id" required>
                            <option value="1">Lowest</option>
                            <option value="2">Low</option>
                            <option value="3" selected>Normal</option>
                            <option value="4">High</option>
                            <option value="5">Critical</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modal_category_id" class="form-label">Category</label>
                        <select class="form-select" id="modal_category_id" name="category_id">
                            <option value="">No Category</option>
                            <option value="1">General</option>
                            <option value="2">Technical</option>
                            <option value="3">Billing</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modal_assigned_to" class="form-label">Assign To</label>
                        <select class="form-select" id="modal_assigned_to" name="assigned_to">
                            <option value="">Unassigned</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Link Ticket Modal -->
<div class="modal fade" id="linkTicketModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="linkTicketForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Link Email to Existing Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ticket_number" class="form-label">Ticket Number</label>
                        <input type="text" class="form-control" id="ticket_number" name="ticket_number" 
                               placeholder="TKT-2024-000001" required>
                        <div class="form-text">Enter the ticket number to link this email to</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Link to Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Global variables
let currentEmailId = <?= $data['email']['id'] ?>;

// Individual actions
function markProcessed(emailId) {
    if (confirm('Mark this email as processed?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= URLROOT ?>/emailinbox/markProcessed/' + emailId;
        document.body.appendChild(form);
        form.submit();
    }
}

function markIgnored(emailId) {
    if (confirm('Mark this email as ignored?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= URLROOT ?>/emailinbox/markIgnored/' + emailId;
        document.body.appendChild(form);
        form.submit();
    }
}

// Modal functions
function createTicketModal(emailId) {
    currentEmailId = emailId;
    document.getElementById('createTicketForm').action = '<?= URLROOT ?>/emailinbox/createTicket/' + emailId;
    new bootstrap.Modal(document.getElementById('createTicketModal')).show();
}

function linkTicketModal(emailId) {
    currentEmailId = emailId;
    document.getElementById('linkTicketForm').action = '<?= URLROOT ?>/emailinbox/linkToTicket/' + emailId;
    new bootstrap.Modal(document.getElementById('linkTicketModal')).show();
}

// Email actions (placeholders)
function replyToEmail() {
    alert('Reply functionality will be implemented soon');
}

function forwardEmail() {
    alert('Forward functionality will be implemented soon');
}

function redownloadAll() {
    if (!confirm('Redownload all attachments for this email and refresh inline images?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= URLROOT ?>/emailinbox/redownloadAttachments/' + currentEmailId;
    document.body.appendChild(form);
    form.submit();
}
</script>

<style>
.email-content {
    max-width: 100%;
    overflow-x: auto;
    line-height: 1.6;
}

.email-content img {
    max-width: 100%;
    height: auto;
}

.email-content-text pre {
    white-space: pre-wrap;
    word-wrap: break-word;
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    border: 1px solid #dee2e6;
}

.card {
    transition: box-shadow 0.15s ease-in-out;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.badge {
    font-size: 0.875em;
}
</style>

<?php require VIEWSPATH . '/inc/footer.php'; ?>
