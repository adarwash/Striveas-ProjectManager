<?php

/**
 * DashboardLayoutService
 *
 * Manages per-user dashboard layout stored in UserSettings.dashboard_layout.
 * Layout shape (normalized):
 * - order: string[] (widget ids)
 * - sizes: map<string,int> (widget id -> col-lg span)
 * - hidden: string[] (widget ids)
 * - mode: string ("grid" | "masonry")
 * - heights: map<string,int> (widget id -> height in px)
 * - groups: map<string,string[]> (parent widget id -> child widget ids)
 * - titles: map<string,string> (title widget id -> title text)
 * - divider_thickness: map<string,int> (divider widget id -> 1|2|3)
 * - title_size: map<string,int> (title widget id -> 1|2|3)
 */
class DashboardLayoutService {
    private $userModel;

    public function __construct($userModel) {
        $this->userModel = $userModel;
    }

    public function getBaseWidgetIds(): array {
        return ['stats', 'plan_today', 'quick_actions', 'my_tasks', 'recent_activity', 'top_clients'];
    }

    public function normalizeLayoutRaw($raw): array {
        $baseOrder = $this->getBaseWidgetIds();
        $layout = [
            'order' => $baseOrder,
            'sizes' => [],
            'hidden' => [],
            'mode' => 'grid',
            'heights' => [],
            'groups' => [],
            'titles' => [],
            'divider_thickness' => [],
            'title_size' => [],
        ];

        if (empty($raw)) {
            return $layout;
        }

        // Old format: JSON array of ids
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $raw = $decoded;
            }
        }

        if (is_array($raw)) {
            $isList = array_keys($raw) === range(0, count($raw) - 1);
            if ($isList) {
                $layout['order'] = $raw;
                return $layout;
            }
            if (isset($raw['order']) && is_array($raw['order'])) {
                $layout['order'] = $raw['order'];
            }
            if (isset($raw['sizes']) && is_array($raw['sizes'])) {
                $layout['sizes'] = $raw['sizes'];
            }
            if (isset($raw['hidden']) && is_array($raw['hidden'])) {
                $layout['hidden'] = $raw['hidden'];
            }
            if (isset($raw['mode']) && is_string($raw['mode'])) {
                $mode = strtolower(trim($raw['mode']));
                if (in_array($mode, ['grid', 'masonry'], true)) {
                    $layout['mode'] = $mode;
                }
            }
            if (isset($raw['heights']) && is_array($raw['heights'])) {
                $layout['heights'] = $raw['heights'];
            }
            if (isset($raw['groups']) && is_array($raw['groups'])) {
                $layout['groups'] = $raw['groups'];
            }
            if (isset($raw['titles']) && is_array($raw['titles'])) {
                $layout['titles'] = $raw['titles'];
            }
            if (isset($raw['divider_thickness']) && is_array($raw['divider_thickness'])) {
                $layout['divider_thickness'] = $raw['divider_thickness'];
            }
            if (isset($raw['title_size']) && is_array($raw['title_size'])) {
                $layout['title_size'] = $raw['title_size'];
            }
        }

        return $layout;
    }

    public function getLayoutForUser(int $userId): array {
        try {
            $settings = $this->userModel->getUserSettings($userId);
            $raw = $settings['dashboard_layout'] ?? '';
            return $this->normalizeLayoutRaw($raw);
        } catch (Exception $e) {
            return $this->normalizeLayoutRaw('');
        }
    }

    public function saveLayoutForUser(int $userId, array $layout): bool {
        $payload = [
            'order' => array_values($layout['order'] ?? []),
            'sizes' => $layout['sizes'] ?? [],
            'hidden' => array_values($layout['hidden'] ?? []),
            'mode' => isset($layout['mode']) ? (string)$layout['mode'] : 'grid',
            'heights' => $layout['heights'] ?? [],
            'groups' => $layout['groups'] ?? [],
            'titles' => $layout['titles'] ?? [],
            'divider_thickness' => $layout['divider_thickness'] ?? [],
            'title_size' => $layout['title_size'] ?? [],
        ];
        $json = json_encode($payload);
        return $this->userModel->updateUserSettings($userId, ['dashboard_layout' => $json]);
    }

    public function isWidgetPinned(int $userId, string $widgetId): bool {
        $layout = $this->getLayoutForUser($userId);
        return in_array($widgetId, $layout['order'] ?? [], true);
    }

    public function pinWidget(int $userId, string $widgetId, int $defaultSpanLg = 6): bool {
        $layout = $this->getLayoutForUser($userId);
        $order = $layout['order'] ?? [];
        if (!in_array($widgetId, $order, true)) {
            $order[] = $widgetId;
        }
        $layout['order'] = $order;

        // Ensure a size exists (only if missing)
        if (!isset($layout['sizes']) || !is_array($layout['sizes'])) {
            $layout['sizes'] = [];
        }
        if (!array_key_exists($widgetId, $layout['sizes'])) {
            $allowed = [3, 4, 6, 8, 12];
            $span = in_array($defaultSpanLg, $allowed, true) ? $defaultSpanLg : 6;
            $layout['sizes'][$widgetId] = $span;
        }

        // Unhide if hidden
        if (!isset($layout['hidden']) || !is_array($layout['hidden'])) {
            $layout['hidden'] = [];
        }
        $layout['hidden'] = array_values(array_filter($layout['hidden'], function($id) use ($widgetId) {
            return $id !== $widgetId;
        }));

        return $this->saveLayoutForUser($userId, $layout);
    }

    public function unpinWidget(int $userId, string $widgetId): bool {
        $layout = $this->getLayoutForUser($userId);
        $layout['order'] = array_values(array_filter(($layout['order'] ?? []), function($id) use ($widgetId) {
            return $id !== $widgetId;
        }));
        if (isset($layout['sizes'][$widgetId])) {
            unset($layout['sizes'][$widgetId]);
        }
        if (isset($layout['heights'][$widgetId])) {
            unset($layout['heights'][$widgetId]);
        }
        if (isset($layout['groups']) && is_array($layout['groups'])) {
            // If this widget was a group parent, remove the group.
            if (isset($layout['groups'][$widgetId])) {
                unset($layout['groups'][$widgetId]);
            }
            // If this widget was grouped under another, remove it from any group lists.
            foreach ($layout['groups'] as $parentId => $childIds) {
                if (!is_array($childIds)) {
                    continue;
                }
                $filtered = array_values(array_filter($childIds, function($id) use ($widgetId) {
                    return $id !== $widgetId;
                }));
                if (empty($filtered)) {
                    unset($layout['groups'][$parentId]);
                } else {
                    $layout['groups'][$parentId] = $filtered;
                }
            }
        }
        $layout['hidden'] = array_values(array_filter(($layout['hidden'] ?? []), function($id) use ($widgetId) {
            return $id !== $widgetId;
        }));

        return $this->saveLayoutForUser($userId, $layout);
    }

    public function getPinnedCardWidgetIds(int $userId): array {
        $layout = $this->getLayoutForUser($userId);
        $order = $layout['order'] ?? [];
        $out = [];
        foreach ($order as $wid) {
            if (is_string($wid) && str_starts_with($wid, 'card:')) {
                $out[] = $wid;
            }
        }
        return $out;
    }
}


