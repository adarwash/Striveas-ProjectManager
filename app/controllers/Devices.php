<?php

class Devices extends Controller {
    /**
     * @var LevelApiService
     */
    private $levelApi;

    public function __construct() {
        if (!isLoggedIn()) {
            redirect('/auth/login');
            return;
        }

        if (!class_exists('LevelApiService')) {
            require_once APPROOT . '/app/services/LevelApiService.php';
        }

        $this->levelApi = new LevelApiService();
    }

    /**
     * Show global Level.io device inventory
     */
    public function index() {
        if (!hasPermission('clients.read')) {
            flash('devices_error', 'You do not have permission to view Level.io devices.', 'alert-danger');
            redirect('dashboard');
            return;
        }

        $devices = [];
        $meta = null;
        $error = null;
        $levelEnabled = $this->levelApi->isEnabled();
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 200;
        $perPage = max(10, min($perPage, 200));
        $maxPages = isset($_GET['max_pages']) ? (int)$_GET['max_pages'] : 25;
        $maxPages = max(1, min($maxPages, 50));
        $pagesFetched = 0;
        $hitPageCap = false;

        if ($levelEnabled) {
            try {
                $currentPage = 1;
                do {
                    $response = $this->levelApi->listDevices($currentPage, $perPage);
                    $payload = $response['data'] ?? ($response['devices'] ?? $response);
                    if (is_array($payload)) {
                        $devices = array_merge($devices, $payload);
                    }

                    $meta = $response['meta'] ?? ($response['pagination'] ?? null);
                    $pagesFetched++;
                    $currentPage++;

                    $hasNext = false;
                    if (is_array($meta)) {
                        if (!empty($meta['next_page'])) {
                            $hasNext = true;
                        } elseif (isset($meta['total_pages']) && $pagesFetched < (int)$meta['total_pages']) {
                            $hasNext = true;
                        } elseif (isset($meta['page']) && isset($meta['per_page']) && isset($meta['total'])) {
                            $totalPages = (int)ceil($meta['total'] / max(1, (int)$meta['per_page']));
                            if ($pagesFetched < $totalPages) {
                                $hasNext = true;
                            }
                        }
                    }

                    if (!$hasNext && is_array($payload) && count($payload) === $perPage && !is_array($meta)) {
                        $hasNext = true;
                    }

                    if ($hasNext && $pagesFetched >= $maxPages) {
                        $hitPageCap = true;
                        break;
                    }
                } while ($hasNext);
            } catch (Exception $e) {
                $error = $e->getMessage();
                error_log('Level.io devices listing error: ' . $e->getMessage());
            }
        }

        $this->view('devices/index', [
            'title' => 'Level.io Devices',
            'devices' => $devices,
            'meta' => $meta,
            'per_page' => $perPage,
            'level_enabled' => $levelEnabled,
            'error' => $error,
            'pages_fetched' => $pagesFetched,
            'hit_page_cap' => $hitPageCap,
            'max_pages' => $maxPages
        ]);
    }

    /**
     * Display detailed information for a single device
     */
    public function detail($deviceId = null) {
        if (!hasPermission('clients.read')) {
            flash('devices_error', 'You do not have permission to view Level.io devices.', 'alert-danger');
            redirect('dashboard');
            return;
        }

        if (!$deviceId) {
            redirect('devices');
            return;
        }

        if (!$this->levelApi->isEnabled()) {
            flash('devices_error', 'Level.io integration is not enabled.', 'alert-warning');
            redirect('devices');
            return;
        }

        try {
            $device = $this->levelApi->getDevice($deviceId);
        } catch (Exception $e) {
            flash('devices_error', 'Unable to load device details: ' . $e->getMessage(), 'alert-danger');
            redirect('devices');
            return;
        }

        $group = null;
        if (!empty($device['group_id']) || !empty($device['group_name'])) {
            $group = [
                'level_group_id' => $device['group_id'] ?? ($device['group']['id'] ?? null),
                'level_group_name' => $device['group_name'] ?? ($device['group']['name'] ?? null)
            ];
        }

        $alerts = [];
        $alertsError = null;
        try {
            $alertResp = $this->levelApi->listDeviceAlerts($deviceId, 1, 100);
            $alerts = $alertResp['data'] ?? $alertResp ?? [];
        } catch (Exception $e) {
            $alertsError = $e->getMessage();
            error_log('Level.io device alerts error (global): ' . $e->getMessage());
        }

        $this->view('clients/level_device_detail', [
            'title' => 'Device Details - ' . ($device['name'] ?? $device['hostname'] ?? 'Device'),
            'device' => $device,
            'alerts' => $alerts,
            'alerts_error' => $alertsError,
            'group' => $group,
            'client' => null,
            'back_url' => '/devices'
        ]);
    }
}


