<?php
/**
 * Process Microsoft 365 support mailbox into tickets.
 *
 * Usage:
 *   php app/scripts/process_graph_tickets.php [limit]
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/EasySQL.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../services/MicrosoftGraphService.php';

$limit = isset($argv[1]) ? (int)$argv[1] : 25;
if ($limit <= 0) { $limit = 25; }

$settings = new Setting();
$settings->set('graph_last_cron_started_at', gmdate('c'));
$settings->set('graph_last_cron_status', 'running');

$supportEmail = $settings->get('graph_support_email') ?: $settings->get('graph_connected_email');

if (empty($supportEmail)) {
    $settings->set('graph_last_cron_finished_at', gmdate('c'));
    $settings->set('graph_last_cron_status', 'error');
    $settings->set('graph_last_cron_error', 'graph_support_email is not configured');
    fwrite(STDERR, "graph_support_email is not configured\n");
    exit(2);
}

$graph = new MicrosoftGraphService();

try {
    $processed = $graph->processEmailsToTickets($supportEmail, true, $limit);
    if ($processed === false) {
        $settings->set('graph_last_cron_finished_at', gmdate('c'));
        $settings->set('graph_last_cron_status', 'error');
        $settings->set('graph_last_cron_error', 'Failed to process emails (see logs)');
        fwrite(STDERR, "Failed to process emails (see logs)\n");
        exit(1);
    }

    $settings->set('graph_last_cron_finished_at', gmdate('c'));
    $settings->set('graph_last_cron_status', 'ok');
    $settings->set('graph_last_cron_mailbox', $supportEmail);
    $settings->set('graph_last_cron_processed', (string)$processed);
    $settings->set('graph_last_cron_error', '');
} catch (Throwable $e) {
    $msg = $e->getMessage();
    if (is_string($msg) && strlen($msg) > 500) {
        $msg = substr($msg, 0, 500) . '...';
    }
    $settings->set('graph_last_cron_finished_at', gmdate('c'));
    $settings->set('graph_last_cron_status', 'error');
    $settings->set('graph_last_cron_error', $msg);
    fwrite(STDERR, "Cron run error: {$e->getMessage()}\n");
    exit(1);
}

echo "Processed {$processed} email(s) from {$supportEmail}\n";
exit(0);
