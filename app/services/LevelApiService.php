<?php

if (!class_exists('Setting')) {
    require_once APPROOT . '/app/models/Setting.php';
}

class LevelApiService {
    private $apiKey;
    private $enabled;
    private $baseUrl;

    public function __construct() {
        $settingModel = new Setting();
        $systemSettings = $settingModel->getSystemSettings();
        $this->apiKey = $systemSettings['level_io_api_key'] ?? '';
        $this->enabled = !empty($systemSettings['level_io_enabled']) && !empty($this->apiKey);
        $this->baseUrl = rtrim('https://api.level.io/v2', '/');
    }

    /**
     * Fetch paginated list of Level.io groups
     */
    public function listGroups(int $page = 1, int $perPage = 100): array {
        $query = [
            'page' => max(1, $page),
            'per_page' => max(1, min($perPage, 200))
        ];
        return $this->request('GET', '/groups', $query);
    }

    /**
     * Fetch a single Level.io group by ID
     */
    public function getGroup(string $groupId): array {
        $groupId = trim($groupId);
        if ($groupId === '') {
            throw new InvalidArgumentException('Group ID is required');
        }
        return $this->request('GET', '/groups/' . urlencode($groupId));
    }

    /**
     * Fetch paginated list of Level.io devices
     * @param int $page Page number
     * @param int $perPage Items per page (max 200)
     * @param string|null $groupId Optional group ID to filter by
     * @return array
     */
    public function listDevices(int $page = 1, int $perPage = 100, ?string $groupId = null): array {
        $query = [
            'page' => max(1, $page),
            'per_page' => max(1, min($perPage, 200))
        ];
        if ($groupId !== null && $groupId !== '') {
            $query['group_id'] = $groupId;
        }
        return $this->request('GET', '/devices', $query);
    }

    /**
     * Fetch a single Level.io device by ID
     */
    public function getDevice(string $deviceId): array {
        $deviceId = trim($deviceId);
        if ($deviceId === '') {
            throw new InvalidArgumentException('Device ID is required');
        }
        return $this->request('GET', '/devices/' . urlencode($deviceId));
    }

    /**
     * Fetch alerts for a specific device
     */
    public function listDeviceAlerts(string $deviceId, int $page = 1, int $perPage = 100): array {
        $deviceId = trim($deviceId);
        if ($deviceId === '') {
            throw new InvalidArgumentException('Device ID is required to fetch alerts');
        }
        $query = [
            'device_id' => $deviceId,
            'page' => max(1, $page),
            'per_page' => max(1, min($perPage, 200))
        ];
        return $this->request('GET', '/alerts', $query);
    }

    /**
     * Check if Level.io integration is enabled
     */
    public function isEnabled(): bool {
        return $this->enabled;
    }

    private function ensureEnabled(): void {
        if (!$this->enabled) {
            throw new Exception('Level.io integration is disabled or missing an API key.');
        }
    }

    private function request(string $method, string $path, array $query = [], $body = null): array {
        $this->ensureEnabled();

        $url = $this->baseUrl . $path;
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        $ch = curl_init($url);
        $headers = [
            'Accept: application/json',
            'Authorization: ' . $this->apiKey
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($body !== null) {
            $payload = is_string($body) ? $body : json_encode($body);
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Level.io request failed: ' . $error);
        }

        curl_close($ch);

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Unable to parse Level.io response (HTTP ' . $httpCode . ').');
        }

        if ($httpCode >= 400) {
            $message = $decoded['message'] ?? $decoded['error'] ?? 'Unknown error';
            throw new Exception('Level.io API error: ' . $message);
        }

        return $decoded;
    }
}

