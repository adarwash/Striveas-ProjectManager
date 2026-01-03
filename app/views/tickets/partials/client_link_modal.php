<?php
$ticket = $data['ticket'] ?? [];
$ticketId = (int)($ticket['id'] ?? 0);
$requesterEmail = (string)($ticket['inbound_email_address'] ?? '');
$allClients = $data['all_clients'] ?? [];
$suggestedId = (int)(($data['suggested_client']['id'] ?? 0));
$canCreateClient = hasPermission('clients.create');

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

$suggestedName = '';
if ($requesterEmail && strpos($requesterEmail, '@') !== false) {
    $local = substr($requesterEmail, 0, strpos($requesterEmail, '@'));
    $local = str_replace(['.', '_', '-'], ' ', $local);
    $suggestedName = ucwords(trim($local));
}
if ($suggestedName === '') {
    $suggestedName = 'New Client';
}
?>

<div class="modal fade" id="linkClientModal" tabindex="-1" aria-labelledby="linkClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="linkClientModalLabel">
                    <i class="bi bi-building-add me-2"></i>Link or Create Client
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= URLROOT ?>/tickets/linkClient/<?= $ticketId ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="modal-body">
                    <div class="alert alert-light border small">
                        <div class="fw-semibold mb-1">Requester email</div>
                        <div><i class="bi bi-envelope me-1"></i><strong><?= htmlspecialchars($requesterEmail ?: 'Unknown') ?></strong></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <div class="btn-group w-100" role="group" aria-label="Client action">
                            <input type="radio" class="btn-check" name="client_action" id="client_action_link" value="link" checked>
                            <label class="btn btn-outline-primary" for="client_action_link">
                                <i class="bi bi-link-45deg me-1"></i>Link existing client
                            </label>

                            <input type="radio" class="btn-check" name="client_action" id="client_action_create" value="create" <?= $canCreateClient ? '' : 'disabled' ?>>
                            <label class="btn btn-outline-success" for="client_action_create">
                                <i class="bi bi-plus-circle me-1"></i>Create new client
                            </label>
                        </div>
                        <?php if (!$canCreateClient): ?>
                            <div class="form-text text-muted">You don’t have permission to create clients.</div>
                        <?php endif; ?>
                    </div>

                    <!-- Link existing -->
                    <div id="linkExistingClientFields" class="border rounded p-3 mb-3">
                        <div class="fw-semibold mb-2">Select an existing client</div>
                        <select class="form-select" name="existing_client_id">
                            <option value="">-- Select client --</option>
                            <?php foreach ($allClients as $c): ?>
                                <?php
                                    $cid = (int)($c['id'] ?? 0);
                                    $cname = (string)($c['name'] ?? ('Client #' . $cid));
                                    $cemail = (string)($c['email'] ?? '');
                                ?>
                                <option value="<?= $cid ?>" <?= ($suggestedId > 0 && $cid === $suggestedId) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cname) ?><?= $cemail ? (' — ' . htmlspecialchars($cemail)) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">This will link the ticket to the selected client.</div>
                    </div>

                    <!-- Create new -->
                    <div id="createNewClientFields" class="border rounded p-3 mb-3 d-none">
                        <div class="fw-semibold mb-2">Create a new client</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Client Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="new_client_name" value="<?= htmlspecialchars($suggestedName) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="new_client_status">
                                    <option value="Active">Active</option>
                                    <option value="Prospect" selected>Prospect</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Person</label>
                                <input type="text" class="form-control" name="new_client_contact_person" value="<?= htmlspecialchars($suggestedName) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="new_client_email" value="<?= htmlspecialchars($requesterEmail) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="new_client_phone" value="">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Industry</label>
                                <input type="text" class="form-control" name="new_client_industry" value="">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="new_client_address" value="">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="new_client_notes" rows="2">Created from ticket <?= htmlspecialchars((string)($ticket['ticket_number'] ?? '')) ?> (email import)</textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="redirect_to_client" id="redirect_to_client" value="1" checked>
                                    <label class="form-check-label" for="redirect_to_client">
                                        Open client after saving (recommended)
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-text mt-2">You can fill out the full client profile after creation.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const linkRadio = document.getElementById('client_action_link');
    const createRadio = document.getElementById('client_action_create');
    const linkBox = document.getElementById('linkExistingClientFields');
    const createBox = document.getElementById('createNewClientFields');
    if (!linkRadio || !createRadio || !linkBox || !createBox) return;

    function sync() {
        const isCreate = createRadio.checked;
        linkBox.classList.toggle('d-none', isCreate);
        createBox.classList.toggle('d-none', !isCreate);
    }
    linkRadio.addEventListener('change', sync);
    createRadio.addEventListener('change', sync);
    sync();
})();
</script>

