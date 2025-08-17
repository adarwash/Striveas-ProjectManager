<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= URLROOT ?>/tickets">Tickets</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($data['ticket']['ticket_number']) ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Ticket Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h1 class="h4 mb-2"><?= htmlspecialchars($data['ticket']['subject']) ?></h1>
                            <?php if (!empty($data['ticket']['inbound_email_address'])): ?>
                            <div class="mb-2">
                                <small class="text-muted">From: </small>
                                <a href="mailto:<?= htmlspecialchars($data['ticket']['inbound_email_address']) ?>" class="text-decoration-none">
                                    <i class="bi bi-envelope me-1"></i><strong><?= htmlspecialchars($data['ticket']['inbound_email_address']) ?></strong>
                                </a>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge fs-6" style="background-color: <?= $data['ticket']['status_color'] ?>">
                                    <?= htmlspecialchars($data['ticket']['status_display']) ?>
                                </span>
                                <span class="badge fs-6" style="background-color: <?= $data['ticket']['priority_color'] ?>">
                                    <?= htmlspecialchars($data['ticket']['priority_display']) ?>
                                </span>
                                <?php if ($data['ticket']['is_overdue']): ?>
                                    <span class="badge bg-danger fs-6">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Overdue
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear me-1"></i>Actions
                            </button>
                            <ul class="dropdown-menu">
                                <?php if ($data['can_edit']): ?>
                                <li><a class="dropdown-item" href="<?= URLROOT ?>/tickets/edit/<?= $data['ticket']['id'] ?>">
                                    <i class="bi bi-pencil me-2"></i>Edit Ticket
                                </a></li>
                                <?php endif; ?>
                                <?php if ($data['can_assign']): ?>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#assignModal">
                                    <i class="bi bi-person-plus me-2"></i>Assign
                                </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= URLROOT ?>/tickets/refresh/<?= $data['ticket']['id'] ?>">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Reload from System
                                </a></li>
                                <li><a class="dropdown-item" href="<?= URLROOT ?>/tickets/refresh/<?= $data['ticket']['id'] ?>?force=1">
                                    <i class="bi bi-arrow-repeat me-2"></i>Force Reload (Fix Images)
                                </a></li>
                                <li><a class="dropdown-item" href="<?= URLROOT ?>/tickets/refresh/<?= $data['ticket']['id'] ?>?download=1" 
                                       onclick="return confirm('Download all pending attachments for this ticket?')">
                                    <i class="bi bi-download me-2"></i>Download All Attachments
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="window.print()">
                                    <i class="bi bi-printer me-2"></i>Print
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Ticket #:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($data['ticket']['ticket_number']) ?></dd>
                                
                                <dt class="col-sm-4">Created by:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($data['ticket']['created_by_name']) ?></dd>
                                
                                <?php if (!empty($data['ticket']['inbound_email_address'])): ?>
                                <dt class="col-sm-4">Requester Email:</dt>
                                <dd class="col-sm-8">
                                    <a href="mailto:<?= htmlspecialchars($data['ticket']['inbound_email_address']) ?>" class="text-decoration-none">
                                        <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($data['ticket']['inbound_email_address']) ?>
                                    </a>
                                </dd>
                                <?php endif; ?>
                                
                                <dt class="col-sm-4">Assigned to:</dt>
                                <dd class="col-sm-8">
                                    <?= $data['ticket']['assigned_to_name'] ? 
                                        htmlspecialchars($data['ticket']['assigned_to_name']) : 
                                        '<span class="text-muted">Unassigned</span>' ?>
                                </dd>
                                
                                <dt class="col-sm-4">Category:</dt>
                                <dd class="col-sm-8">
                                    <?= $data['ticket']['category_name'] ? 
                                        htmlspecialchars($data['ticket']['category_name']) : 
                                        '<span class="text-muted">None</span>' ?>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Created:</dt>
                                <dd class="col-sm-8">
                                    <?= date('M j, Y g:i A', strtotime($data['ticket']['created_at'])) ?>
                                    <small class="text-muted">(<?= $data['ticket']['age_hours'] ?> hours ago)</small>
                                </dd>
                                
                                <dt class="col-sm-4">Last updated:</dt>
                                <dd class="col-sm-8"><?= date('M j, Y g:i A', strtotime($data['ticket']['updated_at'])) ?></dd>
                                
                                <dt class="col-sm-4">Due date:</dt>
                                <dd class="col-sm-8">
                                    <?= $data['ticket']['due_date'] ? 
                                        date('M j, Y', strtotime($data['ticket']['due_date'])) : 
                                        '<span class="text-muted">Not set</span>' ?>
                                </dd>
                                
                                <dt class="col-sm-4">Source:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-secondary">
                                        <?= ucfirst($data['ticket']['source'] ?? 'web') ?>
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conversation Thread -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-chat-dots me-2"></i>Conversation
                        <span class="badge bg-secondary ms-2"><?= count($data['messages']) ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="timeline">
                        <?php if (empty($data['messages'])): ?>
                            <div class="p-4 text-muted">
                                No conversation messages yet.
                            </div>
                        <?php else: ?>
                            <?php foreach ($data['messages'] as $index => $message): ?>
                                <div class="timeline-item border-bottom p-4 <?= ($message['is_system_message'] ?? 0) ? 'bg-light' : '' ?>">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                    style="width: 40px; height: 40px;">
                                                <?php if (!empty($message['username'])): ?>
                                                    <?php
                                                        $fullName = trim($message['full_name'] ?? '')
                                                            ?: trim(($message['first_name'] ?? '') . ' ' . ($message['last_name'] ?? ''))
                                                            ?: ($message['username'] ?? '');
                                                        $initials = '';
                                                        if (!empty($fullName)) {
                                                            $parts = preg_split('/\s+/', trim($fullName));
                                                            $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[count($parts) - 1] ?? '', 0, 1));
                                                        }
                                                        echo htmlspecialchars($initials ?: '');
                                                    ?>
                                                <?php elseif (in_array($message['message_type'], ['email_inbound', 'email_outbound'])): ?>
                                                    <i class="bi bi-envelope"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-gear"></i>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h6 class="mb-0">
                                                        <?php if (!empty($message['username'])): ?>
                                                            <?= htmlspecialchars($fullName ?: $message['username']) ?>
                                                        <?php elseif (in_array($message['message_type'], ['email_inbound', 'email_outbound']) && !empty($message['email_from'])): ?>
                                                            <?= htmlspecialchars($message['email_from']) ?>
                                                        <?php elseif (!empty($message['full_name'])): ?>
                                                            <?= htmlspecialchars($message['full_name']) ?>
                                                        <?php else: ?>
                                                            System
                                                        <?php endif; ?>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <?= date('M j, Y g:i A', strtotime($message['created_at'])) ?>
                                                        
                                                        <?php if (($message['message_type'] ?? 'comment') !== 'comment'): ?>
                                                            <span class="badge bg-info ms-2">
                                                                <?= ucwords(str_replace('_', ' ', $message['message_type'])) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (empty($message['is_public'])): ?>
                                                            <span class="badge bg-warning ms-1">Internal</span>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <?php if (!empty($message['subject']) && in_array($message['message_type'], ['email_inbound', 'email_outbound'])): ?>
                                                <div class="mb-2">
                                                    <strong>Subject:</strong> <?= htmlspecialchars($message['subject']) ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="message-content">
                                                <?php if (($message['content_format'] ?? 'text') === 'html'): ?>
                                                    <div class="html-content">
                                                        <?php
                                                            // Replace cid inline images with a route that serves them by CID via EmailInbox controller
                                                            $contentHtml = $message['content'];
                                                            $contentHtml = preg_replace_callback('/src\s*=\s*(\"|\')cid:([^\"\']+)(\1)/i', function($m) use ($data, $message) {
                                                                $q = $m[1];
                                                                $cid = trim($m[2], '<>');
                                                                $emailId = $message['email_inbox_id'] ?? ($data['ticket']['email_inbox_id'] ?? null);
                                                                // If not directly linked, fall back to generic route without email id (will 1x1 pixel)
                                                                if ($emailId) {
                                                                    $url = URLROOT . '/emailinbox/inline/' . $emailId . '/' . rawurlencode($cid);
                                                                    return 'src=' . $q . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . $q;
                                                                }
                                                                return $m[0];
                                                            }, $contentHtml);
                                                            echo $contentHtml;
                                                        ?>
                                                    </div>
                                                <?php else: ?>
                                                    <?= nl2br(htmlspecialchars($message['content'])) ?>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if (in_array($message['message_type'], ['email_inbound', 'email_outbound'])): ?>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="bi bi-envelope me-1"></i>
                                                        <?php if ($message['message_type'] === 'email_inbound'): ?>
                                                            From: <?= htmlspecialchars($message['email_from'] ?? '') ?>
                                                            <?php if (!empty($message['email_to'])): ?>
                                                                | To: <?= htmlspecialchars($message['email_to']) ?>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            To: <?= htmlspecialchars($message['email_to'] ?? '') ?>
                                                        <?php endif; ?>
                                                        <?php if (!empty($message['email_cc'])): ?>
                                                            | CC: <?= htmlspecialchars($message['email_cc']) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Add Reply Form -->
                <?php if (hasPermission('tickets.comment')): ?>
                <div class="card-footer bg-white">
                    <form method="POST" action="<?= URLROOT ?>/tickets/addMessage/<?= $data['ticket']['id'] ?>">
                        <div class="mb-3">
                            <label for="message" class="form-label">Add Reply</label>
                            <textarea class="form-control" id="message" name="message" rows="4" 
                                      placeholder="Type your message here..." required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="message_type" class="form-label">Message Type</label>
                                    <select class="form-select" id="message_type" name="message_type">
                                        <option value="comment">Comment</option>
                                        <option value="status_change">Status Update</option>
                                        <?php if (hasPermission('tickets.internal_notes')): ?>
                                            <option value="internal_note">Internal Note</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1" checked>
                                        <label class="form-check-label" for="is_public">
                                            Visible to customer
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Send Reply
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Attachments -->
            <?php if (!empty($data['attachments'])): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-paperclip me-2"></i>Attachments
                        <span class="badge bg-secondary ms-2"><?= count($data['attachments']) ?></span>
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($data['attachments'] as $att): ?>
                            <li class="list-group-item d-flex align-items-center justify-content-between px-0">
                                <div class="me-2 d-flex align-items-center">
                                    <?php
                                        $name = $att['original_filename'] ?: $att['filename'];
                                        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                                        $icon = 'file-earmark';
                                        if (in_array($ext, ['png','jpg','jpeg','gif','bmp','webp'])) { $icon = 'file-earmark-image'; }
                                        elseif (in_array($ext, ['pdf'])) { $icon = 'file-earmark-pdf'; }
                                        elseif (in_array($ext, ['doc','docx'])) { $icon = 'file-earmark-word'; }
                                        elseif (in_array($ext, ['xls','xlsx'])) { $icon = 'file-earmark-excel'; }
                                        elseif (in_array($ext, ['ppt','pptx'])) { $icon = 'file-earmark-ppt'; }
                                    ?>
                                    <i class="bi bi-<?= $icon ?> me-2"></i>
                                    <div>
                                        <div class="fw-semibold text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($name) ?>">
                                            <?= htmlspecialchars($name) ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= !empty($att['file_size']) ? number_format($att['file_size']/1024, 1) . ' KB' : '' ?>
                                        </small>
                                    </div>
                                </div>
                                <?php if (!empty($att['file_path'])): ?>
                                    <a class="btn btn-sm btn-outline-secondary" href="<?= URLROOT . '/' . ltrim($att['file_path'], '/') ?>" target="_blank">
                                        <i class="bi bi-download"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Unavailable</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            <!-- Quick Actions -->
            <?php if ($data['can_edit']): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <form id="quickUpdateForm">
                        <input type="hidden" name="ticket_id" value="<?= $data['ticket']['id'] ?>">
                        
                        <div class="mb-3">
                            <label for="quick_status" class="form-label">Status</label>
                            <select class="form-select" id="quick_status" name="status_id" onchange="updateTicketStatus()">
                                <?php foreach ($data['statuses'] as $status): ?>
                                    <option value="<?= $status['id'] ?>" 
                                            <?= $data['ticket']['status_id'] == $status['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($status['display_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <?php if ($data['can_assign']): ?>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignModal">
                                    <i class="bi bi-person-plus me-2"></i>Assign Ticket
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($data['can_close'] && !$data['ticket']['is_closed']): ?>
                                <button type="button" class="btn btn-outline-success" onclick="closeTicket()">
                                    <i class="bi bi-check-circle me-2"></i>Mark as Resolved
                                </button>
                            <?php endif; ?>


                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Ticket Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>Ticket Information
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt class="small text-muted">Age</dt>
                        <dd><?= $data['ticket']['age_hours'] ?> hours</dd>
                        
                        <dt class="small text-muted">Messages</dt>
                        <dd><?= count($data['messages']) ?></dd>
                        
                        <?php if (!empty($data['ticket']['tags'])): ?>
                            <dt class="small text-muted">Tags</dt>
                            <dd>
                                <?php foreach (explode(',', $data['ticket']['tags']) as $tag): ?>
                                    <span class="badge bg-secondary me-1"><?= htmlspecialchars(trim($tag)) ?></span>
                                <?php endforeach; ?>
                            </dd>
                        <?php endif; ?>
                        
                        <?php if (!empty($data['ticket']['inbound_email_address'])): ?>
                            <dt class="small text-muted">Original Email</dt>
                            <dd>
                                <small class="text-muted">
                                    <i class="bi bi-envelope me-1"></i>
                                    <?= htmlspecialchars($data['ticket']['inbound_email_address']) ?>
                                </small>
                            </dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <!-- Activity Summary -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-activity me-2"></i>Activity Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Comments</span>
                        <span class="badge bg-primary">
                            <?= count(array_filter($data['messages'], function($m) { return $m['message_type'] === 'comment'; })) ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Email Messages</span>
                        <span class="badge bg-info">
                            <?= count(array_filter($data['messages'], function($m) { return in_array($m['message_type'], ['email_inbound', 'email_outbound']); })) ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">System Updates</span>
                        <span class="badge bg-secondary">
                            <?= count(array_filter($data['messages'], function($m) { return $m['is_system_message']; })) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assignment Modal -->
<?php if ($data['can_assign']): ?>
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= URLROOT ?>/tickets/assign/<?= $data['ticket']['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="assignee_ids" class="form-label">Assign to User</label>
                        <select class="form-select" id="assignee_ids" name="assignee_ids[]" multiple>
                            <?php foreach ($data['users'] as $user): ?>
                                <?php
                                    $displayName = trim($user['full_name'] ?? '')
                                        ?: trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))
                                        ?: ($user['name'] ?? ($user['username'] ?? ('User #' . ($user['id'] ?? ''))));
                                ?>
                                <option value="<?= $user['id'] ?>" 
                                        <?= $data['ticket']['assigned_to'] == $user['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($displayName) ?>
                                    <?php if (!empty($user['email'])): ?> (<?= htmlspecialchars($user['email']) ?>)<?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Hold Ctrl to select multiple users</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function updateTicketStatus() {
    const form = document.getElementById('quickUpdateForm');
    const formData = new FormData(form);
    
    console.log('Sending request to:', '<?= URLROOT ?>/tickets/updateStatus');
    console.log('Form data:', Object.fromEntries(formData));
    
    fetch('<?= URLROOT ?>/tickets/updateStatus', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        return response.text().then(text => {
            console.log('Raw response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                throw new Error('Invalid JSON response: ' + text);
            }
        });
    })
    .then(data => {
        console.log('Parsed data:', data);
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to update status: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update status: ' + error.message);
    });
}

function closeTicket() {
    if (confirm('Are you sure you want to mark this ticket as resolved?')) {
        const statusSelect = document.getElementById('quick_status');
        // Find the 'resolved' status option
        for (let option of statusSelect.options) {
            if (option.text.toLowerCase().includes('resolved')) {
                statusSelect.value = option.value;
                updateTicketStatus();
                break;
            }
        }
    }
}

// Auto-resize textarea
document.getElementById('message').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});
</script>

<style>
.timeline-item:last-child {
    border-bottom: none !important;
}

.avatar {
    font-size: 14px;
    font-weight: 600;
}

.html-content {
    /* Show full email content without clipping */
    max-height: none;
    overflow: visible;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 1rem;
    background-color: #f8f9fa;
}

.html-content img {
    max-width: 100%;
    height: auto;
}
</style>