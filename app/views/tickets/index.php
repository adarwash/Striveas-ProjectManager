<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Tickets</h1>
            <p class="text-muted">Manage and track support tickets</p>
        </div>
        <div>
            <?php if (hasPermission('tickets.create')): ?>
                <a href="<?= URLROOT ?>/tickets/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>New Ticket
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="bi bi-ticket-perforated fs-1"></i>
                    </div>
                    <h4 class="mb-0"><?= $data['statistics']['total'] ?? 0 ?></h4>
                    <small class="text-muted">Total Tickets</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="bi bi-check-circle fs-1"></i>
                    </div>
                    <h4 class="mb-0"><?= $data['statistics']['open'] ?? 0 ?></h4>
                    <small class="text-muted">Open</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-secondary mb-2">
                        <i class="bi bi-archive fs-1"></i>
                    </div>
                    <h4 class="mb-0"><?= $data['statistics']['closed'] ?? 0 ?></h4>
                    <small class="text-muted">Closed</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-danger mb-2">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                    <h4 class="mb-0"><?= $data['statistics']['overdue'] ?? 0 ?></h4>
                    <small class="text-muted">Overdue</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="bi bi-star fs-1"></i>
                    </div>
                    <h4 class="mb-0"><?= $data['statistics']['high_priority'] ?? 0 ?></h4>
                    <small class="text-muted">High Priority</small>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="bi bi-person-x fs-1"></i>
                    </div>
                    <h4 class="mb-0"><?= $data['statistics']['unassigned'] ?? 0 ?></h4>
                    <small class="text-muted">Unassigned</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="bi bi-funnel me-2"></i>Filters
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= URLROOT ?>/tickets" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Ticket number or subject..." 
                           value="<?= htmlspecialchars($data['filters']['search'] ?? '') ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <?php foreach ($data['statuses'] as $status): ?>
                            <option value="<?= $status['id'] ?>" 
                                    <?= ($data['filters']['status_id'] ?? '') == $status['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status['display_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="priority" class="form-label">Priority</label>
                    <select class="form-select" id="priority" name="priority">
                        <option value="">All Priorities</option>
                        <?php foreach ($data['priorities'] as $priority): ?>
                            <option value="<?= $priority['id'] ?>" 
                                    <?= ($data['filters']['priority_id'] ?? '') == $priority['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($priority['display_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($data['categories'] as $category): ?>
                            <option value="<?= $category['id'] ?>" 
                                    <?= ($data['filters']['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php if (hasPermission('tickets.view_all')): ?>
                <div class="col-md-2">
                    <label for="assigned_to" class="form-label">Assigned To</label>
                    <select class="form-select" id="assigned_to" name="assigned_to">
                        <option value="">All Users</option>
                        <option value="unassigned" <?= (isset($data['filters']['assigned_is_null']) && $data['filters']['assigned_is_null']) ? 'selected' : '' ?>>Unassigned only</option>
                        <?php foreach ($data['users'] as $user): ?>
                            <option value="<?= $user['id'] ?>" 
                                    <?= ($data['filters']['assigned_to'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars(
                                    trim($user['full_name'] ?? '') 
                                    ?: trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))
                                    ?: ($user['name'] ?? 'Unknown User')
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="col-md-1">
                    <label for="closed" class="form-label">Status</label>
                    <select class="form-select" id="closed" name="closed">
                        <option value="">All</option>
                        <option value="0" <?= isset($data['filters']['is_closed']) && $data['filters']['is_closed'] == 0 ? 'selected' : '' ?>>Open</option>
                        <option value="1" <?= isset($data['filters']['is_closed']) && $data['filters']['is_closed'] == 1 ? 'selected' : '' ?>>Closed</option>
                    </select>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-2"></i>Filter
                    </button>
                    <a href="<?= URLROOT ?>/tickets" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="bi bi-list-ul me-2"></i>Tickets
                <span class="badge bg-secondary ms-2"><?= $data['pagination']['total'] ?></span>
            </h5>
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-sort-down me-1"></i>Sort
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="?<?= http_build_query(array_merge($_GET, ['order_by' => 'created_at DESC'])) ?>">Newest First</a></li>
                    <li><a class="dropdown-item" href="?<?= http_build_query(array_merge($_GET, ['order_by' => 'created_at ASC'])) ?>">Oldest First</a></li>
                    <li><a class="dropdown-item" href="?<?= http_build_query(array_merge($_GET, ['order_by' => 'priority_level DESC'])) ?>">Priority High to Low</a></li>
                    <li><a class="dropdown-item" href="?<?= http_build_query(array_merge($_GET, ['order_by' => 'updated_at DESC'])) ?>">Recently Updated</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($data['tickets'])): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">No tickets found</h5>
                    <p class="text-muted">Try adjusting your filters or create a new ticket.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ticket #</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Category</th>
                                <th>Assigned To</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['tickets'] as $ticket): ?>
                                <tr class="<?= $ticket['is_overdue'] ? 'table-warning' : '' ?>">
                                    <td>
                                        <a href="<?= URLROOT ?>/tickets/show/<?= $ticket['id'] ?>" 
                                           class="text-decoration-none fw-medium">
                                            <?= htmlspecialchars($ticket['ticket_number']) ?>
                                        </a>
                                        <?php if ($ticket['is_overdue']): ?>
                                            <i class="bi bi-exclamation-triangle text-danger ms-1" 
                                               title="Overdue"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= URLROOT ?>/tickets/show/<?= $ticket['id'] ?>" 
                                           class="text-decoration-none">
                                            <?= htmlspecialchars(substr($ticket['subject'], 0, 50)) ?>
                                            <?= strlen($ticket['subject']) > 50 ? '...' : '' ?>
                                        </a>
                                        <?php if ($ticket['message_count'] > 1): ?>
                                            <span class="badge bg-info ms-1"><?= $ticket['message_count'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: <?= $ticket['status_color'] ?>">
                                            <?= htmlspecialchars($ticket['status_display']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: <?= $ticket['priority_color'] ?>">
                                            <?= htmlspecialchars($ticket['priority_display']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $ticket['category_name'] ? htmlspecialchars($ticket['category_name']) : 
                                            '<span class="text-muted">-</span>' ?>
                                    </td>
                                    <td>
                                        <?= $ticket['assigned_to_name'] ? htmlspecialchars($ticket['assigned_to_name']) : 
                                            '<span class="text-muted">Unassigned</span>' ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M j, Y', strtotime($ticket['created_at'])) ?><br>
                                            <span class="text-xs"><?= date('g:i A', strtotime($ticket['created_at'])) ?></span>
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M j, Y', strtotime($ticket['updated_at'])) ?><br>
                                            <span class="text-xs"><?= date('g:i A', strtotime($ticket['updated_at'])) ?></span>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="<?= URLROOT ?>/tickets/show/<?= $ticket['id'] ?>">
                                                        <i class="bi bi-eye me-2"></i>View
                                                    </a>
                                                </li>
                                                <?php if (hasPermission('tickets.update')): ?>
                                                <li>
                                                    <a class="dropdown-item" href="<?= URLROOT ?>/tickets/edit/<?= $ticket['id'] ?>">
                                                        <i class="bi bi-pencil me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                                <?php if (hasPermission('tickets.assign')): ?>
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="assignTicket(<?= $ticket['id'] ?>)">
                                                        <i class="bi bi-person-plus me-2"></i>Assign
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
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($data['pagination']['total_pages'] > 1): ?>
            <div class="card-footer bg-white">
                <nav aria-label="Tickets pagination">
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
                        of <?= $data['pagination']['total'] ?> tickets
                    </small>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Assign Ticket Modal -->
<div class="modal fade" id="assignTicketModal" tabindex="-1" aria-labelledby="assignTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="assignTicketForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignTicketModalLabel">Assign Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="currentAssigneeInfo" class="alert alert-info d-none mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="currentAssigneeText"></span>
                    </div>
                    
                    <div class="mb-3">
                        <label for="assignee_select" class="form-label">
                            <i class="bi bi-person-check me-1"></i>Assign to User
                        </label>
                        <select class="form-select" id="assignee_select" name="assignee_ids[]" required>
                            <option value="">Select a user...</option>
                            <?php 
                            // Filter users to show only technicians, managers, and admins
                            $assignableUsers = array_filter($data['users'], function($user) {
                                $role = strtolower($user['role'] ?? '');
                                return in_array($role, ['admin', 'manager', 'technician', 'it_manager', 'it manager']);
                            });
                            
                            // Group users by role for better organization
                            $groupedUsers = [];
                            foreach ($assignableUsers as $user) {
                                $role = ucfirst(str_replace('_', ' ', $user['role'] ?? 'Other'));
                                if (!isset($groupedUsers[$role])) {
                                    $groupedUsers[$role] = [];
                                }
                                $groupedUsers[$role][] = $user;
                            }
                            
                            // Sort roles with Admin first, then Manager, then others
                            $roleOrder = ['Admin' => 1, 'Manager' => 2, 'It Manager' => 3, 'IT Manager' => 3, 'Technician' => 4];
                            uksort($groupedUsers, function($a, $b) use ($roleOrder) {
                                $orderA = $roleOrder[$a] ?? 999;
                                $orderB = $roleOrder[$b] ?? 999;
                                return $orderA - $orderB;
                            });
                            ?>
                            
                            <?php foreach ($groupedUsers as $role => $users): ?>
                                <optgroup label="<?= htmlspecialchars($role) ?>">
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id'] ?>">
                                            <?= htmlspecialchars(
                                                trim($user['full_name'] ?? '') 
                                                ?: trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))
                                                ?: ($user['name'] ?? '')
                                                ?: 'User #' . $user['id']
                                            ) ?>
                                            <?php if (!empty($user['email'])): ?>
                                                (<?= htmlspecialchars($user['email']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                            
                            <?php if (empty($assignableUsers)): ?>
                                <option disabled>No assignable users found</option>
                            <?php endif; ?>
                        </select>
                        <div class="form-text">Only Admins, Managers, and Technicians are shown</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="assign_note" class="form-label">Note (Optional)</label>
                        <textarea class="form-control" id="assign_note" name="note" rows="2" 
                                  placeholder="Add a note about this assignment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Assign Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentTicketId = null;

function assignTicket(ticketId) {
    currentTicketId = ticketId;
    // Update form action
    document.getElementById('assignTicketForm').action = '<?= URLROOT ?>/tickets/assign/' + ticketId;
    // Reset form
    document.getElementById('assignTicketForm').reset();
    
    // Find the ticket row
    const ticketRow = document.querySelector(`tr td a[href*="/tickets/show/${ticketId}"]`)?.closest('tr');
    if (ticketRow) {
        // Get ticket number
        const ticketNumber = ticketRow.querySelector('td:first-child a').textContent.trim();
        document.getElementById('assignTicketModalLabel').textContent = `Assign Ticket ${ticketNumber}`;
        
        // Get current assignee (6th column - Assigned To)
        const assigneeCell = ticketRow.querySelectorAll('td')[5];
        const currentAssignee = assigneeCell.textContent.trim();
        
        const infoDiv = document.getElementById('currentAssigneeInfo');
        const infoText = document.getElementById('currentAssigneeText');
        
        if (currentAssignee && currentAssignee !== 'Unassigned') {
            infoText.textContent = `Currently assigned to: ${currentAssignee}`;
            infoDiv.classList.remove('d-none');
        } else {
            infoText.textContent = 'This ticket is currently unassigned';
            infoDiv.classList.remove('d-none');
        }
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('assignTicketModal'));
    modal.show();
}

// Handle form submission
document.getElementById('assignTicketForm').addEventListener('submit', function(e) {
    const selectedUser = document.getElementById('assignee_select').value;
    if (!selectedUser) {
        e.preventDefault();
        alert('Please select a user to assign the ticket to.');
        return false;
    }
});
</script>