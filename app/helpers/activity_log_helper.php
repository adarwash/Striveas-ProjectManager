<?php
/**
 * Activity/Audit logging helpers.
 *
 * Uses the existing activity_logs table as the system audit log source of truth.
 * Performance dashboards can then filter/aggregate the same events.
 */
function activity_logger_instance(): ?ActivityLog {
    static $logger = null;
    if ($logger !== null) {
        return $logger;
    }
    try {
        if (!class_exists('ActivityLog')) {
            require_once APPROOT . '/app/models/ActivityLog.php';
        }
        $logger = new ActivityLog();
        return $logger;
    } catch (Exception $e) {
        error_log('activity_logger_instance error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Log an activity event if a user is logged in.
 *
 * @param string $entityType e.g. project, task, client, site, note, request
 * @param int $entityId entity id (use 0 when not applicable)
 * @param string $action e.g. created, updated, deleted, viewed, status_changed
 * @param string|null $description free-text
 * @param array|null $metadata JSON-serializable metadata
 * @param int|null $userId override actor id (defaults to session user_id)
 * @return int|bool inserted id or false
 */
function log_activity(string $entityType, int $entityId, string $action, ?string $description = null, ?array $metadata = null, ?int $userId = null) {
    try {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $actorId = $userId ?? (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null);
        if (!$actorId) {
            return false;
        }
        $logger = activity_logger_instance();
        if (!$logger) {
            return false;
        }
        return $logger->log($actorId, $entityType, $entityId, $action, $description, $metadata);
    } catch (Exception $e) {
        error_log('log_activity error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Request-level audit logging (optional).
 *
 * Defaults:
 * - Logs all non-GET requests for logged-in users.
 * - Logs GET requests only when AUDIT_LOG_READ_REQUESTS=1.
 *
 * This is intentionally low-detail (keys only) to avoid storing secrets.
 */
function log_request_activity(string $controller, string $method, array $params = []): void {
    try {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            return;
        }

        $reqMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $logReads = getenv('AUDIT_LOG_READ_REQUESTS') === '1';
        $logWrites = getenv('AUDIT_LOG_WRITE_REQUESTS');
        if ($logWrites === false) {
            $logWrites = '1';
        }
        $logWrites = $logWrites === '1';

        if ($reqMethod === 'GET' && !$logReads) {
            return;
        }
        if ($reqMethod !== 'GET' && !$logWrites) {
            return;
        }

        $path = $_SERVER['REQUEST_URI'] ?? '';
        $queryKeys = array_keys($_GET ?? []);
        $postKeys = $reqMethod !== 'GET' ? array_keys($_POST ?? []) : [];

        $metadata = [
            'controller' => $controller,
            'method' => $method,
            'params' => array_values($params),
            'request_method' => $reqMethod,
            'path' => $path,
            'query_keys' => $queryKeys,
            'post_keys' => $postKeys,
            'is_ajax' => !empty($_SERVER['HTTP_X_REQUESTED_WITH']),
        ];

        $action = ($reqMethod === 'GET') ? 'viewed' : 'called';
        $description = $reqMethod . ' ' . $path;
        log_activity('request', 0, $action, $description, $metadata);
    } catch (Exception $e) {
        error_log('log_request_activity error: ' . $e->getMessage());
    }
}

