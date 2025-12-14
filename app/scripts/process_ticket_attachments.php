<?php
/**
 * Download pending TicketAttachments from Microsoft Graph.
 *
 * Usage:
 *   php app/scripts/process_ticket_attachments.php [limit]
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/EasySQL.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/TicketAttachment.php';
require_once __DIR__ . '/../services/MicrosoftGraphService.php';

$limit = isset($argv[1]) ? (int)$argv[1] : 25;
if ($limit <= 0) { $limit = 25; }
if ($limit > 200) { $limit = 200; }

$settings = new Setting();
$supportEmail = $settings->get('graph_support_email') ?: $settings->get('graph_connected_email');
if (empty($supportEmail)) {
    fwrite(STDERR, "graph_support_email is not configured\n");
    exit(2);
}

$attachmentModel = new TicketAttachment();
$pending = $attachmentModel->getPending($limit);
if (empty($pending)) {
    echo "No pending attachments\n";
    exit(0);
}

$graph = new MicrosoftGraphService();
$processed = 0;

foreach ($pending as $row) {
    try {
        $recordId = (int)($row['id'] ?? 0);
        $msMessageId = (string)($row['ms_message_id'] ?? '');
        $msAttachmentId = (string)($row['ms_attachment_id'] ?? '');
        $name = (string)($row['original_filename'] ?? $row['filename'] ?? ('attachment_' . $recordId));

        if ($recordId <= 0 || $msMessageId === '' || $msAttachmentId === '') {
            continue;
        }

        // Reuse internal downloader by mimicking attachment metadata
        $ref = new ReflectionClass($graph);
        $m = $ref->getMethod('downloadAndSaveAttachment');
        $m->setAccessible(true);
        $m->invoke($graph, $supportEmail, $msMessageId, ['id' => $msAttachmentId, 'name' => $name], $recordId);
        $processed++;
    } catch (Throwable $e) {
        // Mark failed so we don't retry endlessly
        try {
            $recordId = (int)($row['id'] ?? 0);
            if ($recordId > 0) {
                $attachmentModel->markDownloadFailed($recordId, $e->getMessage());
            }
        } catch (Throwable $ignored) {}
    }
}

echo "Processed {$processed} attachment(s)\n";
exit(0);


