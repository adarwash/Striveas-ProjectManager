<div class="container-fluid">
    <!-- Modern Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fas fa-ticket-alt me-3"></i>Edit Ticket</h1>
            <p class="mb-0">Update ticket details and status</p>
        </div>
        <div>
            <a href="<?= URLROOT ?>/tickets/show/<?= $data['ticket']['id'] ?>" class="btn btn-outline-secondary me-2">
                <i class="fas fa-eye me-2"></i>View Ticket
            </a>
            <a href="<?= URLROOT ?>/tickets" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Tickets
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pencil-square me-2"></i>Edit Ticket
                    </h5>
                    <span class="text-muted">#<?= htmlspecialchars($data['ticket']['ticket_number']) ?></span>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= URLROOT ?>/tickets/edit/<?= $data['ticket']['id'] ?>" id="editTicketForm">
                        <!-- Subject -->
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" value="<?= htmlspecialchars($data['ticket']['subject']) ?>" required>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="6" required><?= htmlspecialchars($data['ticket']['description'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status_id" class="form-label">Status</label>
                                <select class="form-select" id="status_id" name="status_id" required>
                                    <?php foreach ($data['statuses'] as $status): ?>
                                        <option value="<?= $status['id'] ?>" <?= $data['ticket']['status_id'] == $status['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($status['display_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="priority_id" class="form-label">Priority</label>
                                <select class="form-select" id="priority_id" name="priority_id" required>
                                    <?php foreach ($data['priorities'] as $priority): ?>
                                        <option value="<?= $priority['id'] ?>" <?= $data['ticket']['priority_id'] == $priority['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($priority['display_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">None</option>
                                    <?php foreach ($data['categories'] as $category): ?>
                                        <option value="<?= $category['id'] ?>" <?= $data['ticket']['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="assigned_to" class="form-label">Assigned To</label>
                                <select class="form-select" id="assigned_to" name="assigned_to">
                                    <option value="">Unassigned</option>
                                    <?php foreach ($data['users'] as $user): ?>
                                        <option value="<?= $user['id'] ?>" <?= (string)$data['ticket']['assigned_to'] === (string)$user['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars(
                                                trim($user['full_name'] ?? '') 
                                                ?: trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))
                                                ?: ($user['name'] ?? 'Unknown User')
                                            ) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" value="<?= $data['ticket']['due_date'] ? date('Y-m-d', strtotime($data['ticket']['due_date'])) : '' ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tags" class="form-label">Tags</label>
                                <input type="text" class="form-control" id="tags" name="tags" value="<?= htmlspecialchars($data['ticket']['tags'] ?? '') ?>" placeholder="bug, urgent, network">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= URLROOT ?>/tickets/show/<?= $data['ticket']['id'] ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
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
</script>




