<?php

class Auditlogs extends Controller {
    private $activityLogModel;
    private $userModel;

    public function __construct() {
        if (!isLoggedIn()) {
            redirect('auth');
        }

        // Admin/manager only by default (can be expanded with a dedicated permission later)
        if (!class_exists('PermissionHelper')) {
            require_once APPROOT . '/app/core/PermissionHelper.php';
        }
        if (!PermissionHelper::isManagerOrAdmin()) {
            flash('audit_error', 'You do not have permission to view the audit log', 'alert alert-danger');
            redirect('/dashboard');
        }

        $this->activityLogModel = $this->model('ActivityLog');
        $this->userModel = $this->model('User');
    }

    /**
     * Audit log index with filters + pagination.
     *
     * Query params:
     * - user_id, entity_type, entity_id, action, start_date, end_date
     * - page, limit
     */
    public function index() {
        $filters = [
            'user_id' => isset($_GET['user_id']) && $_GET['user_id'] !== '' ? (int)$_GET['user_id'] : null,
            'entity_type' => isset($_GET['entity_type']) ? trim((string)$_GET['entity_type']) : '',
            'entity_id' => isset($_GET['entity_id']) && $_GET['entity_id'] !== '' ? (int)$_GET['entity_id'] : null,
            'action' => isset($_GET['action']) ? trim((string)$_GET['action']) : '',
            'start_date' => isset($_GET['start_date']) ? trim((string)$_GET['start_date']) : '',
            'end_date' => isset($_GET['end_date']) ? trim((string)$_GET['end_date']) : '',
        ];

        // Normalize empties for model
        foreach ($filters as $k => $v) {
            if ($v === '' || $v === null) {
                unset($filters[$k]);
            }
        }

        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        if ($limit <= 0) $limit = 50;
        if ($limit > 200) $limit = 200;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $rows = $this->activityLogModel->getActivitiesWithFilters($filters, $limit, $offset);

        // Load user list for filter dropdown (best-effort)
        $users = [];
        try {
            if (method_exists($this->userModel, 'getAllUsersWithRoles')) {
                $users = $this->userModel->getAllUsersWithRoles();
            } else {
                $users = $this->userModel->getAllUsers();
            }
        } catch (Exception $e) {
            $users = [];
        }

        // Build pagination URLs
        $baseParams = $_GET;
        unset($baseParams['page']);

        $data = [
            'title' => 'Audit Log',
            'activities' => $rows ?: [],
            'filters' => [
                'user_id' => isset($_GET['user_id']) ? (string)$_GET['user_id'] : '',
                'entity_type' => isset($_GET['entity_type']) ? (string)$_GET['entity_type'] : '',
                'entity_id' => isset($_GET['entity_id']) ? (string)$_GET['entity_id'] : '',
                'action' => isset($_GET['action']) ? (string)$_GET['action'] : '',
                'start_date' => isset($_GET['start_date']) ? (string)$_GET['start_date'] : '',
                'end_date' => isset($_GET['end_date']) ? (string)$_GET['end_date'] : '',
                'limit' => (string)$limit,
            ],
            'users' => $users,
            'page' => $page,
            'limit' => $limit,
            'base_query' => http_build_query($baseParams),
        ];

        $this->view('auditlogs/index', $data);
    }
}

