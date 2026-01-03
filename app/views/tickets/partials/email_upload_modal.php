<?php
// $ticketIdForUpload can be provided by parent view to append to an existing ticket
$ticketIdForUpload = $ticketIdForUpload ?? null;
?>

<div class="modal fade" id="uploadEmailModal" tabindex="-1" aria-labelledby="uploadEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadEmailModalLabel">
                    <i class="bi bi-cloud-upload me-2"></i>Upload Email (.eml/.msg)
                    <?php if (!empty($ticketIdForUpload)): ?>
                        <span class="badge bg-secondary ms-2">Append to Ticket #<?= htmlspecialchars($ticketIdForUpload) ?></span>
                    <?php endif; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= URLROOT ?>/tickets/importEmail" method="POST" enctype="multipart/form-data">
                <?php if (!empty($ticketIdForUpload)): ?>
                    <input type="hidden" name="ticket_id" value="<?= (int)$ticketIdForUpload ?>">
                <?php endif; ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="email_files" class="form-label">Select .eml or .msg files</label>
                        <input type="file" class="form-control" id="email_files" name="email_files[]" accept=".eml,.msg" multiple required>
                        <div class="form-text">We’ll parse the email, detect the client (excluding technician domains), and create or append to a ticket.</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="send_ack" name="send_ack">
                                <label class="form-check-label" for="send_ack">
                                    Send acknowledgment email
                                </label>
                                <div class="form-text">Optional. Off by default for uploaded emails.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-light border small mb-0">
                                <div class="fw-semibold mb-1">What happens:</div>
                                <ul class="mb-0 ps-3">
                                    <li>Dedupes by Message-ID</li>
                                    <li>Threads via In-Reply-To / References</li>
                                    <li>Inline images + attachments preserved</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-2"></i>Upload & Process
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
