<?php
class Dashboard extends Controller {
    private $userModel;
    private $projectModel;
    private $taskModel;
    private $departmentModel;
    private $clientModel;
    private $settingModel;
    
    public function __construct() {
        // Load models
        $this->userModel = $this->model('User');
        $this->projectModel = $this->model('Project');
        $this->taskModel = $this->model('Task');
        $this->departmentModel = $this->model('Department');
        $this->clientModel = $this->model('Client');
        $this->settingModel = $this->model('Setting');
    }

    /**
     * Automatically create follow-ups for prospect clients based on admin-configured interval.
     * Runs at most once every 12 hours.
     */
    private function ensureProspectFollowups(): void {
        try {
            $settings = $this->settingModel->getSystemSettings();
            $enabled = !empty($settings['prospect_followup_enabled']);
            $intervalDays = max(1, (int)($settings['prospect_followup_interval_days'] ?? 14));
            if (!$enabled) {
                return;
            }

            $lastRunRaw = $settings['prospect_followup_last_run'] ?? null;
            $now = time();
            if (!empty($lastRunRaw)) {
                $lastRunTs = strtotime($lastRunRaw);
                // Avoid running more than twice a day
                if ($lastRunTs !== false && ($now - $lastRunTs) < 43200) {
                    return;
                }
            }

            $prospects = $this->clientModel->getClientsByStatus('Prospect');
            if (empty($prospects)) {
                $this->settingModel->set('prospect_followup_last_run', date('Y-m-d H:i:s', $now));
                return;
            }

            $reminderModel = $this->model('Reminder');
            $creatorId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;
            $intervalSeconds = $intervalDays * 86400;

            foreach ($prospects as $p) {
                $clientId = (int)$p['id'];
                $latest = $reminderModel->getLatestReminderForEntity('client', $clientId);

                $needNew = false;
                if (empty($latest)) {
                    $needNew = true;
                } else {
                    $lastRemindTs = strtotime($latest['remind_at'] ?? '');
                    if ($lastRemindTs === false || ($now - $lastRemindTs) >= $intervalSeconds) {
                        $needNew = true;
                    }
                }

                if ($needNew) {
                    $remindAt = date('Y-m-d H:i:s', $now + $intervalSeconds);
                    $title = 'Prospect follow-up';
                    $notes = 'Automated follow-up for prospect client ' . ($p['name'] ?? ('#' . $clientId));
                    $reminderModel->add([
                        'entity_type' => 'client',
                        'entity_id' => $clientId,
                        'title' => $title,
                        'notes' => $notes,
                        'remind_at' => $remindAt,
                        'created_by' => $creatorId,
                        'notify_all' => 1,
                    ]);
                }
            }

            $this->settingModel->set('prospect_followup_last_run', date('Y-m-d H:i:s', $now));
        } catch (Exception $e) {
            error_log('Prospect follow-up scheduler error: ' . $e->getMessage());
        }
    }

    private function currentRoleId(): ?int {
        return isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : null;
    }

    private function isAdminRole(): bool {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    private function blockedClientIds(): array {
        return $this->clientModel->getBlockedClientIdsForRole($this->currentRoleId(), $this->isAdminRole());
    }
    
    public function index() {
        // Require login to view dashboard
        if (!isLoggedIn()) {
            redirect('users/login');
            return;
        }
        // Get user ID from session
        $userId = $_SESSION['user_id'];

        // CSRF token for dashboard layout save (AJAX)
        if (!isset($_SESSION['csrf_token'])) {
            try {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            } catch (Exception $e) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(8));
            }
        }

        // Auto follow-ups for prospect clients (admin-configurable)
        $this->ensureProspectFollowups();
        
        $blockedClientIds = $this->blockedClientIds();

        // Get projects (filtered)
        $projects = $this->projectModel->getAllProjects();
        if (!empty($blockedClientIds)) {
            $projects = array_values(array_filter($projects, function($project) use ($blockedClientIds) {
                $clientId = isset($project->client_id) ? (int)$project->client_id : null;
                return empty($clientId) || !in_array($clientId, $blockedClientIds, true);
            }));
        }
        
        // Get tasks
        $tasks = $this->taskModel->getAllTasks($blockedClientIds);
        
        // Get tasks assigned to the current user (only open tasks)
        $assignedTasks = $this->taskModel->getOpenTasksByUser($userId, $blockedClientIds);
        
        // Get tickets assigned to the current user (only open tickets)
        $ticketModel = $this->model('Ticket');
        $assignedTickets = method_exists($ticketModel, 'getOpenTicketsByUser') 
            ? $ticketModel->getOpenTicketsByUser($userId) 
            : [];
        
        // Get departments
        $departments = $this->departmentModel->getAllDepartments();
        
        // Get project counts by status (filtered)
        $projectCounts = [
            'Planning' => 0,
            'In Progress' => 0,
            'Active' => 0,
            'Completed' => 0,
            'On Hold' => 0
        ];
        foreach ($projects as $p) {
            $status = $p->status ?? 'Active';
            if (isset($projectCounts[$status])) {
                $projectCounts[$status]++;
            }
        }
        
        // Get task counts by status
        $taskCounts = $this->taskModel->getTaskCountsByStatus($blockedClientIds);
        
        // Get projects assigned to the current user
        $userProjects = $this->projectModel->getProjectsCountByUser($userId);
        
        // Get dashboard statistics
        $dashboardStats = $this->getDashboardStats($blockedClientIds);
        
        // Determine range for top clients by tickets (today|week|month), capped at 90 days
        $range = isset($_GET['top_client_range']) ? strtolower(trim($_GET['top_client_range'])) : 'month';
        if (!in_array($range, ['today', 'week', 'month'])) {
            $range = 'month';
        }
        // Get top clients by ticket volume within range
        $topClients = $this->clientModel->getTopClientsByTickets($range, 5, 90);
        
        // Get active user data for personalized greeting
        $user = $this->userModel->getUserById($userId);

        // Per-user dashboard layout (widget order)
        $dashboardLayout = '';
        try {
            $userSettings = $this->userModel->getUserSettings((int)$userId);
            $dashboardLayout = $userSettings['dashboard_layout'] ?? '';
        } catch (Exception $e) {
            $dashboardLayout = '';
        }

        // Pinned cards (cross-module dashboard cards)
        $pinnedCards = [];
        $seenPinned = [];
        try {
            require_once APPROOT . '/app/services/DashboardCardService.php';
            require_once APPROOT . '/app/services/DashboardLayoutService.php';
            $cardService = new DashboardCardService();
            $layoutService = new DashboardLayoutService($this->userModel);
            $layout = $layoutService->getLayoutForUser((int)$userId);
            $order = $layout['order'] ?? [];
            if (is_array($order)) {
                $roleIdCtx = isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : null;
                $isAdminCtx = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
                foreach ($order as $wid) {
                    if (!is_string($wid) || !str_starts_with($wid, 'card:')) {
                        continue;
                    }
                    $parsed = $cardService->parseWidgetId($wid);
                    if (empty($parsed) || empty($parsed['card_id'])) {
                        continue;
                    }
                    $cardId = (string)$parsed['card_id'];
                    $params = is_array($parsed['params'] ?? null) ? $parsed['params'] : [];
                    if ($cardId === '' || !$cardService->isSupported($cardId)) {
                        continue;
                    }
                    if (isset($seenPinned[$wid])) {
                        continue;
                    }
                    $seenPinned[$wid] = true;
                    $def = $cardService->getDefinition($cardId);
                    if (empty($def)) {
                        continue;
                    }
                    // Permission check (best-effort)
                    $perms = $def['permissions'] ?? [];
                    if (is_array($perms) && !empty($perms)) {
                        foreach ($perms as $p) {
                            if (!hasPermission((string)$p)) {
                                continue 2;
                            }
                        }
                    }

                    $cardLimit = 10;
                    if ($cardId === 'clients.all') {
                        $cardLimit = 50;
                    }

                    $cardData = $cardService->fetchData($cardId, (int)$userId, [
                        'limit' => $cardLimit,
                        'role_id' => $roleIdCtx,
                        'is_admin' => $isAdminCtx,
                        'params' => $params
                    ]);
                    // Dynamic title for parameterized cards
                    $title = $def['title'] ?? $cardId;
                    if ($cardId === 'clients.client' && !empty($cardData['client']['name'])) {
                        $title = (string)$cardData['client']['name'];
                    }
                    $pinnedCards[] = [
                        'card_id' => $cardId,
                        'widget_id' => $wid,
                        'title' => $title,
                        'description' => $def['description'] ?? '',
                        'view' => $def['view'] ?? '',
                        'default_span_lg' => (int)($def['default_span_lg'] ?? 6),
                        'data' => $cardData,
                    ];
                }
            }
        } catch (Exception $e) {
            $pinnedCards = [];
        }
        
        $data = [
            'title' => 'Dashboard',
            'user' => $user,
            'projects' => $projects,
            'tasks' => $tasks,
            'assigned_tasks' => $assignedTasks,
            'assigned_tickets' => $assignedTickets,
            'departments' => $departments,
            'project_counts' => $projectCounts,
            'task_counts' => $taskCounts,
            'user_projects' => $userProjects,
            'stats' => $dashboardStats,
            'top_clients' => $topClients,
            'top_client_range' => $range,
            'dashboard_layout' => $dashboardLayout,
            'pinned_cards' => $pinnedCards
        ];
        
        $this->view('dashboard/index', $data);
    }

    /**
     * Toggle pin/unpin for a supported dashboard card (AJAX or form POST).
     * POST JSON: { card_id: "tickets.my_open", csrf_token: "..." }
     */
    public function togglePinCard() {
        $isAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');
        if ($isAjax) {
            header('Content-Type: application/json');
        }

        if (!isLoggedIn()) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Not authenticated']);
                exit;
            }
            redirect('users/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                exit;
            }
            redirect('dashboard');
            return;
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            $payload = $_POST ?? [];
        }

        // Ensure CSRF token exists
        if (!isset($_SESSION['csrf_token'])) {
            try {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            } catch (Exception $e) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(8));
            }
        }

        $posted = (string)($payload['csrf_token'] ?? '');
        if ($posted === '' || !hash_equals((string)$_SESSION['csrf_token'], $posted)) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
                exit;
            }
            flash('error', 'Security check failed');
            redirect('dashboard');
            return;
        }

        $cardId = trim((string)($payload['card_id'] ?? ''));
        if ($cardId === '' || strlen($cardId) > 120) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Invalid card']);
                exit;
            }
            redirect('dashboard');
            return;
        }

        require_once APPROOT . '/app/services/DashboardCardService.php';
        require_once APPROOT . '/app/services/DashboardLayoutService.php';
        $cardService = new DashboardCardService();
        if (!$cardService->isSupported($cardId)) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Card not supported']);
                exit;
            }
            redirect('dashboard');
            return;
        }

        $def = $cardService->getDefinition($cardId);
        $perms = $def['permissions'] ?? [];
        if (is_array($perms) && !empty($perms)) {
            foreach ($perms as $p) {
                if (!hasPermission((string)$p)) {
                    if ($isAjax) {
                        echo json_encode(['success' => false, 'message' => 'Permission denied']);
                        exit;
                    }
                    flash('error', 'Permission denied');
                    redirect('dashboard');
                    return;
                }
            }
        }

        // Parameters for cards that need them (e.g. specific client)
        $params = [];
        if ($cardId === 'clients.client') {
            $params['client_id'] = isset($payload['client_id']) ? (int)$payload['client_id'] : 0;
            if ((int)$params['client_id'] <= 0) {
                if ($isAjax) {
                    echo json_encode(['success' => false, 'message' => 'Client is required']);
                    exit;
                }
                flash('error', 'Client is required');
                redirect('clients');
                return;
            }

            // Ensure the user can access this client
            try {
                $clientModel = $this->model('Client');
                $roleId = isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : null;
                $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
                if (method_exists($clientModel, 'canAccessClientId') && !$clientModel->canAccessClientId((int)$params['client_id'], $roleId, $isAdmin)) {
                    if ($isAjax) {
                        echo json_encode(['success' => false, 'message' => 'Permission denied']);
                        exit;
                    }
                    flash('error', 'Permission denied');
                    redirect('clients');
                    return;
                }
            } catch (Exception $e) {
                // ignore; fall through
            }
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $layoutService = new DashboardLayoutService($this->userModel);
        $widgetId = $cardService->buildWidgetId($cardId, $params);

        $currentlyPinned = $layoutService->isWidgetPinned($userId, $widgetId);
        $ok = false;
        $pinned = $currentlyPinned;
        if ($currentlyPinned) {
            $ok = $layoutService->unpinWidget($userId, $widgetId);
            $pinned = false;
        } else {
            $ok = $layoutService->pinWidget($userId, $widgetId, $cardService->getDefaultSpanLg($cardId));
            $pinned = true;
        }

        if ($isAjax) {
            echo json_encode([
                'success' => (bool)$ok,
                'pinned' => (bool)$pinned,
                'card_id' => $cardId,
                'widget_id' => $widgetId
            ]);
            exit;
        }

        if ($ok) {
            flash('success', $pinned ? 'Pinned to dashboard.' : 'Unpinned from dashboard.');
        } else {
            flash('error', 'Failed to update pin.');
        }

        // Redirect back if possible
        $back = $_SERVER['HTTP_REFERER'] ?? '';
        if (!empty($back)) {
            header('Location: ' . $back);
            exit;
        }
        redirect('dashboard');
    }

    /**
     * Save per-user dashboard widget order/layout (AJAX)
     */
    public function saveLayout() {
        // AJAX JSON response
        $isAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');
        if ($isAjax) {
            header('Content-Type: application/json');
        }

        if (!isLoggedIn()) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Not authenticated']);
                exit;
            }
            redirect('users/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                exit;
            }
            redirect('dashboard');
            return;
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            $payload = $_POST ?? [];
        }

        $csrf = (string)($payload['csrf_token'] ?? '');
        $sessionToken = (string)($_SESSION['csrf_token'] ?? '');
        if ($sessionToken !== '' && $csrf !== $sessionToken) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
                exit;
            }
            flash('client_error', 'Security check failed', 'alert-danger');
            redirect('dashboard');
            return;
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $action = strtolower(trim((string)($payload['action'] ?? 'save')));

        if ($action === 'reset') {
            $ok = $this->userModel->updateUserSettings($userId, ['dashboard_layout' => null]);
            if ($isAjax) {
                echo json_encode(['success' => (bool)$ok]);
                exit;
            }
            redirect('dashboard');
            return;
        }

        // Read existing layout so we can merge (order + sizes)
        $existingOrder = [];
        $existingSizes = [];
        $existingHidden = [];
        $existingMode = 'grid';
        $existingHeights = [];
        $existingGroups = [];
        $existingTitles = [];
        $existingDividerThickness = [];
        $existingTitleSizes = [];
        try {
            $userSettings = $this->userModel->getUserSettings($userId);
            $existingRaw = $userSettings['dashboard_layout'] ?? '';
            if (is_string($existingRaw) && trim($existingRaw) !== '') {
                $decoded = json_decode($existingRaw, true);
                if (is_array($decoded)) {
                    // Old format: JSON array of ids
                    $isList = array_keys($decoded) === range(0, count($decoded) - 1);
                    if ($isList) {
                        $existingOrder = $decoded;
                    } else {
                        $existingOrder = is_array($decoded['order'] ?? null) ? $decoded['order'] : [];
                        $existingSizes = is_array($decoded['sizes'] ?? null) ? $decoded['sizes'] : [];
                        $existingHidden = is_array($decoded['hidden'] ?? null) ? $decoded['hidden'] : [];
                        $existingMode = is_string($decoded['mode'] ?? null) ? (string)$decoded['mode'] : 'grid';
                        $existingHeights = is_array($decoded['heights'] ?? null) ? $decoded['heights'] : [];
                        $existingGroups = is_array($decoded['groups'] ?? null) ? $decoded['groups'] : [];
                        $existingTitles = is_array($decoded['titles'] ?? null) ? $decoded['titles'] : [];
                        $existingDividerThickness = is_array($decoded['divider_thickness'] ?? null) ? $decoded['divider_thickness'] : [];
                        $existingTitleSizes = is_array($decoded['title_size'] ?? null) ? $decoded['title_size'] : [];
                    }
                }
            }
        } catch (Exception $e) {
            $existingOrder = [];
            $existingSizes = [];
            $existingHidden = [];
            $existingMode = 'grid';
            $existingHeights = [];
            $existingGroups = [];
            $existingTitles = [];
            $existingDividerThickness = [];
            $existingTitleSizes = [];
        }

        $baseAllowed = [
            'stats',
            'plan_today',
            'quick_actions',
            'my_tasks',
            'recent_activity',
            'top_clients'
        ];

        // Allow pinned card widgets already present in the saved layout (validated against registry)
        $allowedWidgets = $baseAllowed;
        try {
            require_once APPROOT . '/app/services/DashboardCardService.php';
            $cardSvc = new DashboardCardService();
            foreach ($existingOrder as $wid) {
                if (!is_string($wid)) {
                    continue;
                }
                // Allow pinned cards
                if (str_starts_with($wid, 'card:')) {
                    $parsed = $cardSvc->parseWidgetId($wid);
                    $cid = (string)($parsed['card_id'] ?? '');
                    if ($cid !== '' && $cardSvc->isSupported($cid)) {
                        $allowedWidgets[] = $wid;
                    }
                }
                // Allow dividers and titles from existing order
                if (str_starts_with($wid, 'divider-') || str_starts_with($wid, 'title-')) {
                    $allowedWidgets[] = $wid;
                }
            }
        } catch (Exception $e) {
            // ignore
        }
        
        // Also allow any new dividers/titles from the incoming payload
        $incomingOrder = $payload['order'] ?? ($payload['layout']['order'] ?? null);
        if (is_array($incomingOrder)) {
            foreach ($incomingOrder as $wid) {
                if (!is_string($wid)) {
                    continue;
                }
                if (str_starts_with($wid, 'divider-') || str_starts_with($wid, 'title-')) {
                    $allowedWidgets[] = $wid;
                }
            }
        }
        
        $allowedWidgets = array_values(array_unique($allowedWidgets));

        // Accept either {order, sizes} or {layout:{order,sizes}}
        $order = $payload['order'] ?? null;
        $sizes = $payload['sizes'] ?? null;
        $hidden = $payload['hidden'] ?? null;
        $mode = $payload['mode'] ?? null;
        $heights = $payload['heights'] ?? null;
        $groups = $payload['groups'] ?? null;
        $titles = $payload['titles'] ?? null;
        $dividerThickness = $payload['divider_thickness'] ?? null;
        $titleSizes = $payload['title_size'] ?? null;
        if (isset($payload['layout']) && is_array($payload['layout'])) {
            $order = $payload['layout']['order'] ?? $order;
            $sizes = $payload['layout']['sizes'] ?? $sizes;
            $hidden = $payload['layout']['hidden'] ?? $hidden;
            $mode = $payload['layout']['mode'] ?? $mode;
            $heights = $payload['layout']['heights'] ?? $heights;
            $groups = $payload['layout']['groups'] ?? $groups;
            $titles = $payload['layout']['titles'] ?? $titles;
            $dividerThickness = $payload['layout']['divider_thickness'] ?? $dividerThickness;
            $titleSizes = $payload['layout']['title_size'] ?? $titleSizes;
        }

        if (!is_array($order)) {
            $order = null; // means "keep existing"
        }
        if (!is_array($sizes)) {
            $sizes = null; // means "keep existing"
        }
        if (!is_array($hidden)) {
            $hidden = null; // means "keep existing"
        }

        // Merge/validate groups (parent widget id -> list of child widget ids)
        $mergedGroups = is_array($existingGroups ?? null) ? $existingGroups : [];
        if (is_array($groups)) {
            $mergedGroups = [];
            $usedChildren = [];
            foreach ($groups as $parentId => $childList) {
                if (!is_string($parentId)) {
                    continue;
                }
                $parentId = trim($parentId);
                if ($parentId === '' || !in_array($parentId, $allowedWidgets, true)) {
                    continue;
                }
                if (!is_array($childList)) {
                    continue;
                }
                $clean = [];
                foreach ($childList as $childId) {
                    if (!is_string($childId)) {
                        continue;
                    }
                    $childId = trim($childId);
                    if ($childId === '' || $childId === $parentId) {
                        continue;
                    }
                    if (!in_array($childId, $allowedWidgets, true)) {
                        continue;
                    }
                    if (isset($usedChildren[$childId])) {
                        continue;
                    }
                    $usedChildren[$childId] = true;
                    $clean[] = $childId;
                }
                if (!empty($clean)) {
                    $mergedGroups[$parentId] = $clean;
                }
            }
        }

        // Validate mode ("grid" or "masonry")
        $modeToStore = $existingMode ?? 'grid';
        if (is_string($mode)) {
            $m = strtolower(trim($mode));
            if (in_array($m, ['grid', 'masonry'], true)) {
                $modeToStore = $m;
            }
        }

        // Merge/validate heights (px)
        $mergedHeights = is_array($existingHeights ?? null) ? $existingHeights : [];
        if (is_array($heights)) {
            foreach ($heights as $wid => $h) {
                if (!is_string($wid)) {
                    continue;
                }
                $wid = trim($wid);
                if ($wid === '' || !in_array($wid, $allowedWidgets, true)) {
                    continue;
                }

                // allow 0/null to clear
                if ($h === null || $h === '' || (is_string($h) && trim($h) === '')) {
                    unset($mergedHeights[$wid]);
                    continue;
                }
                $hInt = (int)$h;
                if ($hInt <= 0) {
                    unset($mergedHeights[$wid]);
                    continue;
                }
                // bounds
                if ($hInt < 160 || $hInt > 2000) {
                    continue;
                }
                $mergedHeights[$wid] = $hInt;
            }
        }

        // Filter & de-dupe order
        $filteredOrder = $existingOrder;
        if (is_array($order)) {
            $seen = [];
            $filteredOrder = [];
            foreach ($order as $id) {
                if (!is_string($id)) {
                    continue;
                }
                $id = trim($id);
                if ($id === '' || isset($seen[$id])) {
                    continue;
                }
                if (!in_array($id, $allowedWidgets, true)) {
                    continue;
                }
                $seen[$id] = true;
                $filteredOrder[] = $id;
            }
        }

        // Merge/validate sizes (Bootstrap 12-col on lg breakpoint)
        $allowedSpans = [3, 4, 6, 8, 12];
        $mergedSizes = is_array($existingSizes) ? $existingSizes : [];
        if (is_array($sizes)) {
            foreach ($sizes as $wid => $span) {
                if (!is_string($wid)) {
                    continue;
                }
                $wid = trim($wid);
                if ($wid === '' || !in_array($wid, $allowedWidgets, true)) {
                    continue;
                }
                $spanInt = (int)$span;
                if (!in_array($spanInt, $allowedSpans, true)) {
                    continue;
                }
                $mergedSizes[$wid] = $spanInt;
            }
        }

        // Merge/validate hidden widgets
        $mergedHidden = [];
        $sourceHidden = $existingHidden;
        if (is_array($hidden)) {
            $sourceHidden = $hidden;
        }
        if (is_array($sourceHidden)) {
            $seenHidden = [];
            foreach ($sourceHidden as $wid) {
                if (!is_string($wid)) {
                    continue;
                }
                $wid = trim($wid);
                if ($wid === '' || isset($seenHidden[$wid])) {
                    continue;
                }
                if (!in_array($wid, $allowedWidgets, true)) {
                    continue;
                }
                $seenHidden[$wid] = true;
                $mergedHidden[] = $wid;
            }
        }

        // Merge/validate title text
        $mergedTitles = is_array($existingTitles ?? null) ? $existingTitles : [];
        if (is_array($titles)) {
            foreach ($titles as $wid => $text) {
                if (!is_string($wid)) {
                    continue;
                }
                $wid = trim($wid);
                // Only allow title widgets
                if (!str_starts_with($wid, 'title-')) {
                    continue;
                }
                if (!in_array($wid, $allowedWidgets, true)) {
                    continue;
                }
                $raw = is_string($text) ? (string)$text : '';
                $raw = trim($raw);

                // Sanitize rich HTML (Quill output) for safe display.
                if ($raw !== '' && function_exists('sanitize_rich_text_html')) {
                    $cleanHtml = sanitize_rich_text_html($raw);
                } else {
                    // Fallback: strip to a safe subset (no attributes)
                    $allowed = '<p><br><div><span><strong><b><em><i><u><s><ul><ol><li><blockquote><pre><code><h1><h2><h3><h4><h5><h6><hr><a>';
                    $cleanHtml = strip_tags($raw, $allowed);
                }

                $cleanHtml = trim((string)$cleanHtml);
                $plain = trim(html_entity_decode(strip_tags($cleanHtml), ENT_QUOTES, 'UTF-8'));
                if ($plain === '') {
                    $cleanHtml = 'Section Title';
                }

                // Cap to prevent bloating dashboard_layout
                if (mb_strlen($cleanHtml) > 4000) {
                    $cleanHtml = mb_substr($cleanHtml, 0, 4000);
                }

                $mergedTitles[$wid] = $cleanHtml;
            }
        }
        // Clean up titles for removed title widgets
        $mergedTitles = array_filter($mergedTitles, function($k) use ($filteredOrder) {
            return in_array($k, $filteredOrder, true);
        }, ARRAY_FILTER_USE_KEY);

        // Merge/validate title size (1/2/3)
        $mergedTitleSizes = is_array($existingTitleSizes ?? null) ? $existingTitleSizes : [];
        if (is_array($titleSizes)) {
            foreach ($titleSizes as $wid => $s) {
                if (!is_string($wid)) {
                    continue;
                }
                $wid = trim($wid);
                // Only allow title widgets
                if (!str_starts_with($wid, 'title-')) {
                    continue;
                }
                if (!in_array($wid, $allowedWidgets, true)) {
                    continue;
                }
                $sInt = (int)$s;
                if (!in_array($sInt, [1, 2, 3], true)) {
                    continue;
                }
                $mergedTitleSizes[$wid] = $sInt;
            }
        }
        // Clean up title sizes for removed title widgets
        $mergedTitleSizes = array_filter($mergedTitleSizes, function($k) use ($filteredOrder) {
            return in_array($k, $filteredOrder, true);
        }, ARRAY_FILTER_USE_KEY);

        // Merge/validate divider thickness (1/2/3)
        $mergedDividerThickness = is_array($existingDividerThickness ?? null) ? $existingDividerThickness : [];
        if (is_array($dividerThickness)) {
            foreach ($dividerThickness as $wid => $t) {
                if (!is_string($wid)) {
                    continue;
                }
                $wid = trim($wid);
                // Only allow divider widgets
                if (!str_starts_with($wid, 'divider-')) {
                    continue;
                }
                if (!in_array($wid, $allowedWidgets, true)) {
                    continue;
                }
                $tInt = (int)$t;
                if (!in_array($tInt, [1, 2, 3], true)) {
                    continue;
                }
                $mergedDividerThickness[$wid] = $tInt;
            }
        }
        // Clean up divider thickness for removed divider widgets
        $mergedDividerThickness = array_filter($mergedDividerThickness, function($k) use ($filteredOrder) {
            return in_array($k, $filteredOrder, true);
        }, ARRAY_FILTER_USE_KEY);

        // Store a consistent object format going forward
        $layoutToStore = [
            'order' => $filteredOrder,
            'sizes' => $mergedSizes,
            'hidden' => $mergedHidden,
            'mode' => $modeToStore,
            'heights' => $mergedHeights,
            'groups' => $mergedGroups,
            'titles' => $mergedTitles,
            'divider_thickness' => $mergedDividerThickness,
            'title_size' => $mergedTitleSizes
        ];
        $layoutJson = json_encode($layoutToStore);
        $ok = $this->userModel->updateUserSettings($userId, ['dashboard_layout' => $layoutJson]);

        if ($isAjax) {
            echo json_encode([
                'success' => (bool)$ok,
                'layout' => $layoutToStore
            ]);
            exit;
        }

        redirect('dashboard');
    }
    
    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(array $blockedClientIds = []) {
        $stats = [];
        
        try {
            // Get total users
            $stats['total_users'] = $this->userModel->getTotalUsers() ?? 0;
            
            // Get technicians count
            $stats['technicians'] = $this->userModel->getUsersByRole('technician') ?? 0;
            
            // Get active clients count
            $clientModel = $this->model('Client');
            $stats['active_clients'] = $clientModel->getActiveClientsCount() ?? 0;
            // Get prospect clients count
            $stats['prospect_clients'] = $clientModel->getClientsCountByStatus('Prospect') ?? 0;
        } catch (Exception $e) {
            // If clients table doesn't exist, default to 0
            $stats['active_clients'] = 0;
        }
        
        try {
            // Get active sites count
            $siteModel = $this->model('Site');
            $stats['active_sites'] = $siteModel->getActiveSitesCount() ?? 0;
        } catch (Exception $e) {
            $stats['active_sites'] = 0;
        }
        
        try {
            // Get open tickets count
            $ticketModel = $this->model('Ticket');
            $stats['open_tickets'] = $ticketModel->getOpenTicketsCount() ?? 0;
        } catch (Exception $e) {
            $stats['open_tickets'] = 0;
        }
        
            // Get open tasks count from existing task counts
            $stats['open_tasks'] = $this->taskModel->getOpenTasksCount($blockedClientIds) ?? 0;
        
        // Removed pending requests from dashboard
        
        try {
            // Get currently working count (from time tracking)
            $timeModel = $this->model('TimeTracking');
            $stats['currently_working'] = $timeModel->getCurrentlyWorkingCount() ?? 0;
        } catch (Exception $e) {
            $stats['currently_working'] = 0;
        }
        
        return $stats;
    }
    
    public function calendar() {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect('/users/login');
        }
        
        // Get user info
        $user_id = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($user_id);
        
        // Get all tasks with project details
        $tasks = $this->taskModel->getAllTasksWithProjects($user_id);
        
        // Get connected calendars
        $calendarModel = $this->model('Calendar');
        $connected_calendars = $calendarModel->getCalendarsByUser($user_id);
        
        // Get shared calendar events
        $shared_events = [];
        if (!empty($connected_calendars)) {
            $shared_events = $calendarModel->getCalendarEvents($user_id);
        }
        
        // Check if Microsoft 365 OAuth is configured
        $clientId = $_ENV['MICROSOFT_CLIENT_ID'] ?? (defined('MICROSOFT_CLIENT_ID') ? MICROSOFT_CLIENT_ID : '');
        $clientSecret = $_ENV['MICROSOFT_CLIENT_SECRET'] ?? (defined('MICROSOFT_CLIENT_SECRET') ? MICROSOFT_CLIENT_SECRET : '');
        $microsoft365_configured = !empty($clientId) && !empty($clientSecret);
        
        $data = [
            'title' => 'Calendar View',
            'user' => $user,
            'tasks' => $tasks,
            'connected_calendars' => $connected_calendars,
            'shared_events' => $shared_events,
            'microsoft365_configured' => $microsoft365_configured
        ];
        
        $this->view('dashboard/calendar', $data);
    }
    
    /**
     * Connect a new external calendar or test iCal URL
     */
    public function connectCalendar() {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect('/users/login');
        }
        
        // Handle iCal URL testing (AJAX request)
        if ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' === 'XMLHttpRequest') {
            return $this->handleIcalUrlTest();
        }
        
        // Verify it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Get user info
        $user_id = $_SESSION['user_id'];
        
        // Get the calendar type
        $calendar_type = $_POST['calendar_type'] ?? '';
        
        // Load Calendar model
        $calendarModel = $this->model('Calendar');
        
        // Handle different calendar types
        switch ($calendar_type) {
            case 'google':
                // Typically, you would redirect to Google OAuth flow
                // For now, we'll simulate this with a success message
                $_SESSION['success'] = 'Google Calendar connection initiated. OAuth flow would start here.';
                redirect('/dashboard/calendar');
                break;
                
            case 'outlook':
                // Legacy Outlook handling - redirect to microsoft365
                $_SESSION['info'] = 'Redirecting to Microsoft 365 integration...';
                redirect('/dashboard/calendar');
                break;
                
            case 'microsoft365':
                // Check if Microsoft OAuth is configured before proceeding
                $clientId = $_ENV['MICROSOFT_CLIENT_ID'] ?? (defined('MICROSOFT_CLIENT_ID') ? MICROSOFT_CLIENT_ID : '');
                $clientSecret = $_ENV['MICROSOFT_CLIENT_SECRET'] ?? (defined('MICROSOFT_CLIENT_SECRET') ? MICROSOFT_CLIENT_SECRET : '');
                
                if (empty($clientId) || empty($clientSecret)) {
                    $_SESSION['error'] = 'Microsoft 365 integration is not configured. Please contact your administrator to set up Azure app credentials.';
                    redirect('/dashboard/calendar');
                    return;
                }
                
                // Store form data in session for after OAuth
                $_SESSION['microsoft365_form_data'] = [
                    'calendar_name' => filter_input(INPUT_POST, 'calendar_name', FILTER_SANITIZE_STRING) ?? 'Microsoft 365 Calendar',
                    'calendar_color' => filter_input(INPUT_POST, 'calendar_color', FILTER_SANITIZE_STRING) ?? '#0078d4',
                    'auto_refresh' => isset($_POST['auto_refresh']) ? 1 : 0,
                    'calendar_scope' => $_POST['calendar_scope'] ?? 'primary'
                ];
                
                // Redirect to Microsoft OAuth
                $this->initiateMicrosoftOAuth();
                break;
                
            case 'ical':
                // For iCal URLs, we can directly process the form
                $calendar_name = filter_input(INPUT_POST, 'calendar_name', FILTER_SANITIZE_STRING);
                $calendar_url = filter_input(INPUT_POST, 'calendar_url', FILTER_SANITIZE_URL);
                $calendar_color = filter_input(INPUT_POST, 'calendar_color', FILTER_SANITIZE_STRING) ?? '#039be5';
                $auto_refresh = isset($_POST['calendar_refresh']) ? 1 : 0;
                
                // Validate inputs
                if (empty($calendar_name) || empty($calendar_url)) {
                    $_SESSION['error'] = 'Please provide both calendar name and URL';
                    redirect('/dashboard/calendar');
                    return;
                }
                
                // Basic URL validation
                if (!filter_var($calendar_url, FILTER_VALIDATE_URL)) {
                    $_SESSION['error'] = 'Please provide a valid URL';
                    redirect('/dashboard/calendar');
                    return;
                }
                
                // Additional iCal URL validation
                if (!preg_match('/\.(ics|ical)(\?.*)?$/i', $calendar_url) && 
                    !strpos($calendar_url, 'calendar') && 
                    !strpos($calendar_url, 'ical')) {
                    $_SESSION['warning'] = 'The URL doesn\'t appear to be an iCal feed. Make sure it ends with .ics or contains calendar/ical in the path.';
                }
                
                // Prepare calendar data
                $calendarData = [
                    'user_id' => $user_id,
                    'name' => $calendar_name,
                    'source' => 'ical',
                    'source_id' => $calendar_url,
                    'color' => $calendar_color,
                    'auto_refresh' => $auto_refresh,
                    'access_token' => null,
                    'refresh_token' => null,
                    'active' => 1
                ];
                
                // Add the calendar
                $result = $calendarModel->addCalendar($calendarData);
                
                if ($result) {
                    // Sync the calendar immediately
                    $calendarModel->syncCalendar($result);
                    $_SESSION['success'] = 'Calendar added and synced successfully';
                } else {
                    $_SESSION['error'] = 'Failed to add calendar';
                }
                
                redirect('/dashboard/calendar');
                break;
                
            default:
                $_SESSION['error'] = 'Unknown calendar type';
                redirect('/dashboard/calendar');
                break;
        }
    }
    
    /**
     * Sync a connected calendar
     * 
     * @param int $id Calendar ID
     * @return void
     */
    public function syncCalendar($id = null) {
        // For AJAX requests, handle everything in try-catch to ensure clean JSON responses
        if ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' === 'XMLHttpRequest') {
            try {
                // Clean any previous output
                if (ob_get_level()) {
                    ob_clean();
                }
                
                // Set JSON header immediately
                header('Content-Type: application/json');
                
                // Check if user is logged in
                if(!isLoggedIn()) {
                    error_log("Sync: User not logged in");
                    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
                    exit;
                }
                
                // Ensure we have an ID
                if (!$id) {
                    error_log("Sync: No calendar ID provided");
                    echo json_encode(['success' => false, 'message' => 'Calendar ID is required']);
                    exit;
                }
                
                // Get user info
                $user_id = $_SESSION['user_id'];
                
                // Load Calendar model
                $calendarModel = $this->model('Calendar');
                
                // Verify the calendar belongs to the current user
                $calendar = $calendarModel->getCalendarById($id);
                
                if (!$calendar || $calendar['user_id'] != $user_id) {
                    error_log("Sync: Calendar not found or access denied for ID: {$id}");
                    echo json_encode(['success' => false, 'message' => 'Calendar not found or access denied']);
                    exit;
                }
                
                // Perform the sync
                error_log("Sync: Attempting to sync calendar ID: {$id}");
                $result = $calendarModel->syncCalendar($id);
                error_log("Sync: Calendar sync result: " . ($result ? 'success' : 'failed'));
                
                if ($result) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Calendar synced successfully',
                        'last_synced' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    // Provide more specific error messages
                    $calendar = $calendarModel->getCalendarById($id);
                    $errorMessage = 'Failed to sync calendar';
                    
                    if ($calendar && $calendar['source'] === 'ical') {
                        $errorMessage = 'Failed to sync iCal calendar. Please check: 1) The URL is accessible, 2) It returns iCal content (not HTML), 3) The calendar is publicly shared';
                    }
                    
                    echo json_encode(['success' => false, 'message' => $errorMessage]);
                }
                
                exit;
                
            } catch (Exception $e) {
                error_log('Exception in syncCalendar AJAX: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
                exit;
            }
        }
        
        // Handle non-AJAX requests (fallback)
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect('/users/login');
        }
        
        // Ensure we have an ID
        if (!$id) {
            $_SESSION['error'] = 'Calendar ID is required';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Get user info
        $user_id = $_SESSION['user_id'];
        
        // Load Calendar model
        $calendarModel = $this->model('Calendar');
        
        // Verify the calendar belongs to the current user
        $calendar = $calendarModel->getCalendarById($id);
        
        if (!$calendar || $calendar['user_id'] != $user_id) {
            $_SESSION['error'] = 'Calendar not found or access denied';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Perform the sync (non-AJAX)
        try {
            $result = $calendarModel->syncCalendar($id);
            
            if ($result) {
                $_SESSION['success'] = 'Calendar synced successfully';
            } else {
                $_SESSION['error'] = 'Failed to sync calendar';
            }
        } catch (Exception $e) {
            error_log('Error syncing calendar: ' . $e->getMessage());
            $_SESSION['error'] = 'Sync error occurred';
        }
        
        redirect('/dashboard/calendar');
    }
    
    /**
     * Handle iCal URL testing (called from connectCalendar for AJAX requests)
     * 
     * @return void
     */
    private function handleIcalUrlTest() {
        try {
            // Clean any previous output
            if (ob_get_level()) {
                ob_clean();
            }
            
            // Set JSON header
            header('Content-Type: application/json');
            
            // Get the JSON input
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data || !isset($data['url'])) {
                echo json_encode(['success' => false, 'message' => 'URL is required']);
                exit;
            }
            
            $url = trim($data['url']);
            
            // Validate URL format
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                echo json_encode(['success' => false, 'message' => 'Invalid URL format']);
                exit;
            }
            
            // Load Calendar model and test the URL
            $calendarModel = $this->model('Calendar');
            
            // Use reflection to access the private method for testing
            $reflection = new ReflectionClass($calendarModel);
            $fetchMethod = $reflection->getMethod('fetchIcalContent');
            $fetchMethod->setAccessible(true);
            $validateMethod = $reflection->getMethod('isValidIcalContent');
            $validateMethod->setAccessible(true);
            
            // Test fetching the content
            $content = $fetchMethod->invoke($calendarModel, $url);
            
            if ($content === false) {
                echo json_encode(['success' => false, 'message' => 'Cannot access the URL. It may be down, require authentication, or force downloads.']);
                exit;
            }
            
            // Validate the content
            if (!$validateMethod->invoke($calendarModel, $content)) {
                echo json_encode(['success' => false, 'message' => 'URL does not return valid iCal content. It may return HTML or require different access.']);
                exit;
            }
            
            // Count events to give feedback
            $eventCount = substr_count($content, 'BEGIN:VEVENT');
            
            echo json_encode([
                'success' => true, 
                'message' => "✅ Valid iCal URL! Found {$eventCount} event(s). This URL should work for calendar sync."
            ]);
            exit;
            
        } catch (Exception $e) {
            error_log('handleIcalUrlTest error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error testing URL: ' . $e->getMessage()]);
            exit;
        }
    }
    
    /**
     * Display form to edit a connected calendar
     * 
     * @param int $id Calendar ID
     * @return void
     */
    public function editCalendar($id = null) {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect('/users/login');
        }
        
        // Ensure we have an ID
        if (!$id) {
            $_SESSION['error'] = 'Calendar ID is required';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Get user info
        $user_id = $_SESSION['user_id'];
        
        // Load Calendar model
        $calendarModel = $this->model('Calendar');
        
        // Verify the calendar belongs to the current user
        $calendar = $calendarModel->getCalendarById($id);
        
        if (!$calendar || $calendar['user_id'] != $user_id) {
            $_SESSION['error'] = 'Calendar not found or access denied';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $calendar_name = filter_input(INPUT_POST, 'calendar_name', FILTER_SANITIZE_STRING);
            $calendar_color = filter_input(INPUT_POST, 'calendar_color', FILTER_SANITIZE_STRING) ?? '#039be5';
            $auto_refresh = isset($_POST['calendar_refresh']) ? 1 : 0;
            $active = isset($_POST['calendar_active']) ? 1 : 0;
            
            // For iCal URLs, also update the URL
            $calendar_url = '';
            if ($calendar['source'] === 'ical') {
                $calendar_url = filter_input(INPUT_POST, 'calendar_url', FILTER_SANITIZE_URL);
                
                // Basic URL validation
                if (!filter_var($calendar_url, FILTER_VALIDATE_URL)) {
                    $_SESSION['error'] = 'Please provide a valid URL';
                    redirect('/dashboard/editCalendar/' . $id);
                    return;
                }
            }
            
            // Prepare calendar data
            $calendarData = [
                'id' => $id,
                'name' => $calendar_name,
                'color' => $calendar_color,
                'auto_refresh' => $auto_refresh,
                'active' => $active
            ];
            
            // Add source_id for iCal
            if ($calendar['source'] === 'ical' && !empty($calendar_url)) {
                $calendarData['source_id'] = $calendar_url;
            }
            
            // Update the calendar
            $result = $calendarModel->updateCalendar($calendarData);
            
            if ($result) {
                $_SESSION['success'] = 'Calendar updated successfully';
                redirect('/dashboard/calendar');
            } else {
                $_SESSION['error'] = 'Failed to update calendar';
                redirect('/dashboard/editCalendar/' . $id);
            }
            
            return;
        }
        
        // Prepare view data
        $data = [
            'title' => 'Edit Calendar',
            'calendar' => $calendar
        ];
        
        // Display edit form
        $this->view('dashboard/edit_calendar', $data);
    }
    
    /**
     * Remove a connected calendar
     * 
     * @param int $id Calendar ID
     * @return void
     */
    public function removeCalendar($id = null) {
        // For AJAX requests, handle everything in try-catch to ensure clean JSON responses
        if ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' === 'XMLHttpRequest') {
            try {
                // Clean any previous output
                if (ob_get_level()) {
                    ob_clean();
                }
                
                // Set JSON header immediately
                header('Content-Type: application/json');
                
                // Debug log
                error_log("removeCalendar AJAX called with ID: " . ($id ?? 'null'));
                
                // Check if user is logged in
                if(!isLoggedIn()) {
                    error_log("User not logged in");
                    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
                    exit;
                }
                
                // Ensure we have an ID
                if (!$id) {
                    error_log("No calendar ID provided");
                    echo json_encode(['success' => false, 'message' => 'Calendar ID is required']);
                    exit;
                }
                
                // Get user info
                $user_id = $_SESSION['user_id'];
                
                // Load Calendar model
                $calendarModel = $this->model('Calendar');
                
                // Verify the calendar belongs to the current user
                $calendar = $calendarModel->getCalendarById($id);
                error_log("Calendar found: " . ($calendar ? 'yes' : 'no'));
                
                if (!$calendar || $calendar['user_id'] != $user_id) {
                    error_log("Calendar not found or access denied");
                    echo json_encode(['success' => false, 'message' => 'Calendar not found or access denied']);
                    exit;
                }
                
                // Remove the calendar
                error_log("Attempting to remove calendar ID: " . $id);
                $result = $calendarModel->removeCalendar($id);
                error_log("Remove calendar result: " . ($result ? 'success' : 'failed'));
                
                if ($result) {
                    error_log("Sending success response");
                    echo json_encode(['success' => true, 'message' => 'Calendar removed successfully']);
                } else {
                    error_log("Sending failure response");
                    echo json_encode(['success' => false, 'message' => 'Failed to remove calendar from database']);
                }
                
                exit;
                
            } catch (Exception $e) {
                error_log('Exception in removeCalendar AJAX: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
                exit;
            }
        }
        
        // Handle non-AJAX requests (fallback)
        error_log("removeCalendar non-AJAX called with ID: " . ($id ?? 'null'));
        
        // Check if user is logged in
        if(!isLoggedIn()) {
            error_log("User not logged in, redirecting");
            redirect('/users/login');
        }
        
        // Ensure we have an ID
        if (!$id) {
            error_log("No calendar ID provided");
            $_SESSION['error'] = 'Calendar ID is required';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Get user info
        $user_id = $_SESSION['user_id'];
        
        // Load Calendar model
        $calendarModel = $this->model('Calendar');
        
        // Verify the calendar belongs to the current user
        $calendar = $calendarModel->getCalendarById($id);
        
        if (!$calendar || $calendar['user_id'] != $user_id) {
            $_SESSION['error'] = 'Calendar not found or access denied';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Remove the calendar (non-AJAX)
        try {
            $result = $calendarModel->removeCalendar($id);
            
            if ($result) {
                $_SESSION['success'] = 'Calendar removed successfully';
            } else {
                $_SESSION['error'] = 'Failed to remove calendar';
            }
        } catch (Exception $e) {
            error_log('Error removing calendar: ' . $e->getMessage());
            $_SESSION['error'] = 'Database error occurred while removing calendar';
        }
        
        redirect('/dashboard/calendar');
    }
    
    /**
     * Initiate Microsoft OAuth flow
     */
    private function initiateMicrosoftOAuth() {
        // Microsoft Graph OAuth configuration
        // Try $_ENV first, then fall back to defined constants or config
        $clientId = $_ENV['MICROSOFT_CLIENT_ID'] ?? (defined('MICROSOFT_CLIENT_ID') ? MICROSOFT_CLIENT_ID : '');
        $clientSecret = $_ENV['MICROSOFT_CLIENT_SECRET'] ?? (defined('MICROSOFT_CLIENT_SECRET') ? MICROSOFT_CLIENT_SECRET : '');
        $appUrl = $_ENV['APP_URL'] ?? (defined('URLROOT') ? URLROOT : 'http://192.168.2.12');
        $redirectUri = $appUrl . '/dashboard/microsoftCallback';
        $scope = 'https://graph.microsoft.com/calendars.read offline_access';
        
        // Check if all required config is present
        if (empty($clientId) || empty($clientSecret)) {
            $_SESSION['error'] = 'Microsoft 365 integration is not configured yet. Please add your Azure app credentials to the configuration.';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Generate state parameter for security
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;
        
        // Build OAuth URL
        $params = http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'state' => $state,
            'response_mode' => 'query'
        ]);
        
        $authUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?' . $params;
        
        // Redirect to Microsoft OAuth
        header('Location: ' . $authUrl);
        exit;
    }
    
    /**
     * Handle Microsoft OAuth callback
     */
    public function microsoftCallback() {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect('/users/login');
        }
        
        // Verify state parameter
        $state = $_GET['state'] ?? '';
        $sessionState = $_SESSION['oauth_state'] ?? '';
        
        if (empty($state) || $state !== $sessionState) {
            $_SESSION['error'] = 'Invalid OAuth state. Please try again.';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Clear state
        unset($_SESSION['oauth_state']);
        
        // Check for error
        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            $errorDescription = $_GET['error_description'] ?? 'Unknown error';
            $_SESSION['error'] = 'Microsoft 365 authorization failed: ' . $errorDescription;
            redirect('/dashboard/calendar');
            return;
        }
        
        // Get authorization code
        $code = $_GET['code'] ?? '';
        if (empty($code)) {
            $_SESSION['error'] = 'No authorization code received from Microsoft.';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Exchange code for tokens
        $tokenData = $this->exchangeCodeForTokens($code);
        
        if (!$tokenData) {
            $_SESSION['error'] = 'Failed to obtain access token from Microsoft.';
            redirect('/dashboard/calendar');
            return;
        }
        
        // Get form data from session
        $formData = $_SESSION['microsoft365_form_data'] ?? [];
        unset($_SESSION['microsoft365_form_data']);
        
        // Create calendar connection
        $this->createMicrosoftCalendarConnection($tokenData, $formData);
    }
    
    /**
     * Exchange authorization code for access tokens
     */
    private function exchangeCodeForTokens($code) {
        $clientId = $_ENV['MICROSOFT_CLIENT_ID'] ?? (defined('MICROSOFT_CLIENT_ID') ? MICROSOFT_CLIENT_ID : '');
        $clientSecret = $_ENV['MICROSOFT_CLIENT_SECRET'] ?? (defined('MICROSOFT_CLIENT_SECRET') ? MICROSOFT_CLIENT_SECRET : '');
        $appUrl = $_ENV['APP_URL'] ?? (defined('URLROOT') ? URLROOT : 'http://192.168.2.12');
        $redirectUri = $appUrl . '/dashboard/microsoftCallback';
        
        if (empty($clientId) || empty($clientSecret)) {
            return false;
        }
        
        $tokenUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
        
        $postData = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
            'scope' => 'https://graph.microsoft.com/calendars.read offline_access'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return false;
    }
    
    /**
     * Create Microsoft calendar connection
     */
    private function createMicrosoftCalendarConnection($tokenData, $formData) {
        $user_id = $_SESSION['user_id'];
        $calendarModel = $this->model('Calendar');
        
        // Calculate token expiration
        $expiresIn = $tokenData['expires_in'] ?? 3600;
        $tokenExpires = date('Y-m-d H:i:s', time() + $expiresIn);
        
        // Prepare calendar data
        $calendarData = [
            'user_id' => $user_id,
            'name' => $formData['calendar_name'] ?? 'Microsoft 365 Calendar',
            'source' => 'microsoft365',
            'source_id' => 'primary', // Will be updated after getting calendar details
            'color' => $formData['calendar_color'] ?? '#0078d4',
            'auto_refresh' => $formData['auto_refresh'] ?? 1,
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'active' => 1
        ];
        
        // Add the calendar
        $calendarId = $calendarModel->addCalendar($calendarData);
        
        if ($calendarId) {
            // Store token expiration separately if needed
            $this->storeMicrosoftTokenExpiration($calendarId, $tokenExpires);
            
            // Try to sync immediately to get calendar details
            $syncResult = $calendarModel->syncCalendar($calendarId);
            
            if ($syncResult) {
                $_SESSION['success'] = 'Microsoft 365 calendar connected and synced successfully!';
            } else {
                $_SESSION['success'] = 'Microsoft 365 calendar connected! Sync will happen automatically.';
            }
        } else {
            $_SESSION['error'] = 'Failed to save Microsoft 365 calendar connection.';
        }
        
        redirect('/dashboard/calendar');
    }
    
    /**
     * Store Microsoft token expiration
     */
    private function storeMicrosoftTokenExpiration($calendarId, $tokenExpires) {
        // Update the calendar with token expiration
        $calendarModel = $this->model('Calendar');
        $calendarModel->updateCalendar([
            'id' => $calendarId,
            'token_expires' => $tokenExpires
        ]);
    }
    
    public function gantt() {
        // Check if user is logged in
        if(!isLoggedIn()) {
            redirect('/users/login');
        }
        
        // Get user info
        $user_id = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($user_id);
        
        // Get all projects with tasks
        $projects = $this->projectModel->getProjectsWithTasks($user_id);
        
        $data = [
            'title' => 'Gantt Chart',
            'user' => $user,
            'projects' => $projects
        ];
        
        $this->view('dashboard/gantt', $data);
    }
}
?> 