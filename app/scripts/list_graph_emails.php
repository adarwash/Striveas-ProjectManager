<?php
/**
 * Debug helper: list recent Microsoft Graph emails for the support mailbox.
 *
 * Usage:
 *   php app/scripts/list_graph_emails.php [limit]
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/EasySQL.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../services/MicrosoftGraphService.php';

$limit = $argv[1] ?? 10;
$limit = (int)$limit;
if ($limit < 1) {
    $limit = 10;
} elseif ($limit > 50) {
    $limit = 50;
}

$settingModel = new Setting();
$supportEmail = $settingModel->get('graph_support_email') ?: $settingModel->get('graph_connected_email');
if (empty($supportEmail)) {
    fwrite(STDERR, "graph_support_email is not configured.\n");
    exit(1);
}

$graph = new MicrosoftGraphService();
$emails = $graph->getEmails($supportEmail, $limit, false);

echo "Graph returned " . count($emails) . " messages for {$supportEmail} (limit={$limit}).\n";
foreach ($emails as $index => $email) {
    printf(
        "%2d) %s | %s | %s | hasAttachments=%s | isRead=%s\n",
        $index + 1,
        $email['receivedDateTime'] ?? '—',
        substr($email['subject'] ?? '(no subject)', 0, 60),
        $email['id'] ?? '(no id)',
        !empty($email['hasAttachments']) ? 'Y' : 'N',
        (!empty($email['isRead']) ? 'Y' : 'N')
    );
}
