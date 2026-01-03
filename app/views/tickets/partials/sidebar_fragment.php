<?php
$pending = (int)($data['pending_attachments'] ?? 0);

// Downloadable (non-inline) attachments
$downloadableAttachments = array_filter($data['attachments'] ?? [], function($a) {
    return empty($a['is_inline']) || (int)$a['is_inline'] !== 1;
});
?>

<?php if ($pending > 0): ?>
    <div id="pendingCardWrapper">
        <div class="card border-0 shadow-sm mb-4" id="pendingAttachmentsCard"
             data-ticket-id="<?= (int)$data['ticket']['id'] ?>"
             data-pending-count="<?= $pending ?>">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0 d-flex align-items-center justify-content-between">
                    <span>
                        <i class="bi bi-cloud-download me-2"></i>Attachments pending
                        <span class="badge bg-secondary ms-2"><?= $pending ?></span>
                    </span>
                </h6>
            </div>
            <div class="card-body">
                <div class="text-muted small mb-2">
                    Attachments are being downloaded automatically in the background. This will refresh to show inline images as soon as they’re ready.
                </div>
                <div class="small" id="fetchAttachmentsStatus" aria-live="polite"></div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div id="pendingCardWrapper"></div>
<?php endif; ?>

<div id="attachmentsCardWrapper">
<?php if (!empty($downloadableAttachments)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h6 class="card-title mb-0">
                <i class="bi bi-paperclip me-2"></i>Attachments
                <span class="badge bg-secondary ms-2"><?= count($downloadableAttachments) ?></span>
            </h6>
        </div>

        <div class="card-body">
            <ul class="list-group list-group-flush">
                <?php foreach ($downloadableAttachments as $att): ?>
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
                                    <?= !empty($att['file_size']) ? number_format(((int)$att['file_size'])/1024, 1) . ' KB' : '' ?>
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
</div>

<?php require VIEWSPATH . '/tickets/partials/client_link_card.php'; ?>

<?php require VIEWSPATH . '/tickets/partials/outbound_email_delivery_card.php'; ?>
