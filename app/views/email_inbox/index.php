<?php require VIEWSPATH . '/inc/header.php'; ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="bi bi-envelope me-2"></i>Email Inbox
            </h1>
            <p class="text-muted">Manage email communications and ticket integration</p>
        </div>
        <div>
            <?php if (hasPermission('email.manage') || in_array($_SESSION['role'] ?? '', ['admin','manager','technician'])): ?>
                <form method="POST" action="<?= URLROOT ?>/emailinbox/syncFromGraph" class="d-inline me-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-download me-2"></i>Sync from Microsoft 365
                    </button>
                </form>
                <form method="POST" action="<?= URLROOT ?>/emailinbox/processPending" class="d-inline">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-clockwise me-2"></i>Process Pending
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="bi bi-envelope fs-1"></i>
                    </div>
                    <h4 class="mb-0"><?= $data['statistics']['total'] ?? 0 ?></h4>
                    <small class="text-muted">Total Emails</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="bi bi-clock fs-1"></i>
                    </div>
                    <h4 class="mb-0"><?= $data['statistics']['pending'] ?? 0 ?></h4>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="bi bi-check-circle fs-1"></i>
                    </div>
                    <h4 class="mb-0"><?= $data['statistics']['processed'] ?? 0 ?></h4>
                    <small class="text-muted">Processed</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-danger mb-2">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                    <h4 class="mb-0"><?= $data['statistics']['error'] ?? 0 ?></h4>
                    <small class="text-muted">Errors</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-secondary mb-2">
                        <i class="bi bi-eye-slash fs-1"></i>
                    </div>
                    <h4 class="mb-0"><?= $data['statistics']['ignored'] ?? 0 ?></h4>
                    <small class="text-muted">Ignored</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="bi bi-ticket-perforated fs-1"></i>
                    </div>
                    <h4 class="mb-0"><?= $data['statistics']['with_tickets'] ?? 0 ?></h4>
                    <small class="text-muted">With Tickets</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-funnel me-2"></i>Filters
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= URLROOT ?>/emailinbox" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Subject, sender, or recipient..."
                           value="<?= htmlspecialchars($data['filters']['search'] ?? '') ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= ($data['filters']['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="processed" <?= ($data['filters']['status'] ?? '') == 'processed' ? 'selected' : '' ?>>Processed</option>
                        <option value="error" <?= ($data['filters']['status'] ?? '') == 'error' ? 'selected' : '' ?>>Error</option>
                        <option value="ignored" <?= ($data['filters']['status'] ?? '') == 'ignored' ? 'selected' : '' ?>>Ignored</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="has_ticket" class="form-label">Ticket Status</label>
                    <select class="form-select" id="has_ticket" name="has_ticket">
                        <option value="">All Emails</option>
                        <option value="1" <?= ($data['filters']['has_ticket'] ?? '') == '1' ? 'selected' : '' ?>>With Tickets</option>
                        <option value="0" <?= ($data['filters']['has_ticket'] ?? '') == '0' ? 'selected' : '' ?>>Without Tickets</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from"
                           value="<?= $data['filters']['date_from'] ?? '' ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to"
                           value="<?= $data['filters']['date_to'] ?? '' ?>">
                </div>
                
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                
                <div class="col-12">
                    <a href="<?= URLROOT ?>/emailinbox" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Email List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="bi bi-list-ul me-2"></i>Emails
                <span class="badge bg-secondary ms-2"><?= $data['pagination']['total'] ?></span>
            </h5>
            <?php if (hasPermission('email.manage')): ?>
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-gear me-1"></i>Bulk Actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="bulkAction('mark_processed')">Mark as Processed</a></li>
                    <li><a class="dropdown-item" href="#" onclick="bulkAction('mark_ignored')">Mark as Ignored</a></li>
                    <?php if (hasPermission('email.delete')): ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="bulkAction('delete')">Delete</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($data['emails'])): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">No emails found</h5>
                    <p class="text-muted">Try adjusting your filters or check back later.</p>
                </div>
            <?php else: ?>
                <form id="bulkActionForm" method="POST" action="<?= URLROOT ?>/emailinbox/bulkAction">
                    <input type="hidden" name="bulk_action" id="bulkActionInput">
                    
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <?php if (hasPermission('email.manage')): ?>
                                    <th width="40">
                                        <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
                                    </th>
                                    <?php endif; ?>
                                    <th>Status</th>
                                    <th>From</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Ticket</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['emails'] as $email): ?>
                                    <tr class="<?= $email['processing_status'] == 'error' ? 'table-danger' : ($email['processing_status'] == 'pending' ? 'table-warning' : '') ?>">
                                        <?php if (hasPermission('email.manage')): ?>
                                        <td>
                                            <input type="checkbox" class="form-check-input email-checkbox" 
                                                   name="email_ids[]" value="<?= $email['id'] ?>">
                                        </td>
                                        <?php endif; ?>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'processed' => 'success',
                                                'error' => 'danger',
                                                'ignored' => 'secondary'
                                            ];
                                            $statusColor = $statusColors[$email['processing_status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?= $statusColor ?>">
                                                <?= ucfirst($email['processing_status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-medium"><?= htmlspecialchars($email['from_address']) ?></div>
                                            <?php if ($email['reply_to'] && $email['reply_to'] != $email['from_address']): ?>
                                                <small class="text-muted">Reply-To: <?= htmlspecialchars($email['reply_to']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= URLROOT ?>/emailinbox/show/<?= $email['id'] ?>" 
                                               class="text-decoration-none">
                                                <?php if (!empty($email['has_attachments'])): ?>
                                                    <i class="bi bi-paperclip text-muted me-1"></i>
                                                <?php endif; ?>
                                                <?= htmlspecialchars(substr($email['subject'], 0, 60)) ?>
                                                <?= strlen($email['subject']) > 60 ? '...' : '' ?>
                                            </a>
                                            <br>
                                            <small class="text-muted">
                                                To: <?= htmlspecialchars(substr($email['to_address'], 0, 40)) ?>
                                                <?= strlen($email['to_address']) > 40 ? '...' : '' ?>
                                                <?php if (!empty($email['attachment_count'])): ?>
                                                    <span class="badge bg-light text-dark ms-1">
                                                        <i class="bi bi-paperclip"></i> <?= $email['attachment_count'] ?>
                                                    </span>
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-medium">
                                                <?= date('M j, Y', strtotime($email['email_date'])) ?>
                                            </div>
                                            <small class="text-muted">
                                                <?= date('g:i A', strtotime($email['email_date'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($email['ticket_id']): ?>
                                                <a href="<?= URLROOT ?>/tickets/show/<?= $email['ticket_id'] ?>" 
                                                   class="text-decoration-none">
                                                    <span class="badge bg-info">
                                                        <?= htmlspecialchars($email['ticket_number']) ?>
                                                    </span>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                        type="button" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="<?= URLROOT ?>/emailinbox/show/<?= $email['id'] ?>">
                                                            <i class="bi bi-eye me-2"></i>View
                                                        </a>
                                                    </li>
                                                    
                                                    <?php if (!$email['ticket_id'] && hasPermission('tickets.create')): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="createTicketModal(<?= $email['id'] ?>)">
                                                            <i class="bi bi-plus-circle me-2"></i>Create Ticket
                                                        </a>
                                                    </li>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!$email['ticket_id'] && hasPermission('tickets.update')): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="linkTicketModal(<?= $email['id'] ?>)">
                                                            <i class="bi bi-link me-2"></i>Link to Ticket
                                                        </a>
                                                    </li>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (hasPermission('email.manage')): ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <?php if ($email['processing_status'] == 'pending'): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="markProcessed(<?= $email['id'] ?>)">
                                                            <i class="bi bi-check-circle me-2"></i>Mark Processed
                                                        </a>
                                                    </li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="markIgnored(<?= $email['id'] ?>)">
                                                            <i class="bi bi-eye-slash me-2"></i>Mark Ignored
                                                        </a>
                                                    </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($data['pagination']['total_pages'] > 1): ?>
            <div class="card-footer bg-white">
                <nav aria-label="Email pagination">
                    <ul class="pagination justify-content-center mb-0">
                        <?php
                        $currentPage = $data['pagination']['current_page'];
                        $totalPages = $data['pagination']['total_pages'];
                        $queryParams = $_GET;
                        ?>
                        
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <?php $queryParams['page'] = $currentPage - 1; ?>
                                <a class="page-link" href="?<?= http_build_query($queryParams) ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <?php $queryParams['page'] = $i; ?>
                                <a class="page-link" href="?<?= http_build_query($queryParams) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <?php $queryParams['page'] = $currentPage + 1; ?>
                                <a class="page-link" href="?<?= http_build_query($queryParams) ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <div class="text-center mt-2">
                    <small class="text-muted">
                        Showing <?= ($currentPage - 1) * $data['pagination']['per_page'] + 1 ?> to 
                        <?= min($currentPage * $data['pagination']['per_page'], $data['pagination']['total']) ?> 
                        of <?= $data['pagination']['total'] ?> emails
                    </small>
                </div>
            </div>
        <?php endif; ?>
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
let currentEmailId = null;

// Select all functionality
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.email-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

// Bulk actions
function bulkAction(action) {
    const checkedBoxes = document.querySelectorAll('.email-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
        alert('Please select at least one email.');
        return;
    }
    
    let confirmMessage = '';
    switch (action) {
        case 'mark_processed':
            confirmMessage = `Mark ${checkedBoxes.length} emails as processed?`;
            break;
        case 'mark_ignored':
            confirmMessage = `Mark ${checkedBoxes.length} emails as ignored?`;
            break;
        case 'delete':
            confirmMessage = `Delete ${checkedBoxes.length} emails? This action cannot be undone.`;
            break;
    }
    
    if (confirm(confirmMessage)) {
        document.getElementById('bulkActionInput').value = action;
        document.getElementById('bulkActionForm').submit();
    }
}

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

// Auto-refresh pending emails
setInterval(function() {
    const currentStatus = new URLSearchParams(window.location.search).get('status');
    if (currentStatus === 'pending' || !currentStatus) {
        // Only refresh if on pending view and no user interaction in last 30 seconds
        if (Date.now() - lastUserInteraction > 30000) {
            location.reload();
        }
    }
}, 60000); // Check every minute

let lastUserInteraction = Date.now();
document.addEventListener('click', () => lastUserInteraction = Date.now());
document.addEventListener('keypress', () => lastUserInteraction = Date.now());
</script>

<style>
.table tbody tr.table-warning {
    --bs-table-accent-bg: rgba(255, 193, 7, 0.1);
}

.table tbody tr.table-danger {
    --bs-table-accent-bg: rgba(220, 53, 69, 0.1);
}

.dropdown-menu {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.badge {
    font-size: 0.75em;
}
</style>

<?php require VIEWSPATH . '/inc/footer.php'; ?>