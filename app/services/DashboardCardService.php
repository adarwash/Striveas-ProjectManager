<?php

/**
 * DashboardCardService
 *
 * Central registry + data loaders for "pinnable" cards that can be rendered
 * on the Dashboard while reusing existing card UI partials.
 */
class DashboardCardService {
    /**
     * Return all supported pinnable cards.
     *
     * - id: stable identifier "module.card"
     * - widget_id: stable dashboard widget id ("card:" prefix)
     * - title/description: display metadata
     * - view: view partial (relative to views folder)
     * - default_span_lg: default Bootstrap col-lg-* span on the dashboard (3/4/6/8/12)
     * - permissions: permissions required to view this card
     */
    public function getDefinitions(): array {
        return [
            'tickets.my_open' => [
                'id' => 'tickets.my_open',
                'widget_id' => 'card:tickets.my_open',
                'module' => 'tickets',
                'title' => 'My Open Tickets',
                'description' => 'Open tickets assigned to you',
                'view' => 'tickets/partials/pinnable_my_open_tickets',
                'default_span_lg' => 6,
                'permissions' => ['tickets.read'],
            ],
            'clients.all' => [
                'id' => 'clients.all',
                'widget_id' => 'card:clients.all',
                'module' => 'clients',
                'title' => 'All Clients',
                'description' => 'Client list (quick access)',
                'view' => 'clients/partials/pinnable_all_clients',
                'default_span_lg' => 12,
                'permissions' => ['clients.read'],
            ],
            // Parameterized card: requires client_id. Instance widget ids look like:
            // card:clients.client:<client_id>
            'clients.client' => [
                'id' => 'clients.client',
                'widget_id' => 'card:clients.client', // base
                'module' => 'clients',
                'title' => 'Client',
                'description' => 'Pinned client',
                'view' => 'clients/partials/pinnable_client_summary',
                'default_span_lg' => 4,
                'permissions' => ['clients.read'],
            ],
        ];
    }

    public function isSupported(string $cardId): bool {
        $defs = $this->getDefinitions();
        return isset($defs[$cardId]);
    }

    public function getDefinition(string $cardId): ?array {
        $defs = $this->getDefinitions();
        return $defs[$cardId] ?? null;
    }

    public function getWidgetId(string $cardId): string {
        $def = $this->getDefinition($cardId);
        if (!empty($def['widget_id'])) {
            return (string)$def['widget_id'];
        }
        return 'card:' . $cardId;
    }

    /**
     * Build a widget id for a card instance.
     * For parameterized cards, include the parameter after the base id.
     */
    public function buildWidgetId(string $cardId, array $params = []): string {
        $cardId = trim($cardId);
        if ($cardId === 'clients.client') {
            $cid = isset($params['client_id']) ? (int)$params['client_id'] : 0;
            return 'card:clients.client:' . $cid;
        }
        return $this->getWidgetId($cardId);
    }

    /**
     * Parse a widget id into [card_id, params].
     * Accepts either:
     * - card:<card_id>
     * - card:<card_id>:<param> (for known parameterized cards)
     */
    public function parseWidgetId(string $widgetId): ?array {
        $widgetId = trim($widgetId);
        if ($widgetId === '' || !str_starts_with($widgetId, 'card:')) {
            return null;
        }
        $rest = substr($widgetId, 5);
        if ($rest === '') {
            return null;
        }

        // Parameterized: clients.client:<id>
        if (str_starts_with($rest, 'clients.client:')) {
            $clientIdStr = substr($rest, strlen('clients.client:'));
            $clientId = (int)$clientIdStr;
            return [
                'card_id' => 'clients.client',
                'params' => ['client_id' => $clientId],
                'widget_id' => $widgetId
            ];
        }

        // Non-parameterized
        return [
            'card_id' => $rest,
            'params' => [],
            'widget_id' => $widgetId
        ];
    }

    public function getDefaultSpanLg(string $cardId): int {
        $def = $this->getDefinition($cardId);
        $span = (int)($def['default_span_lg'] ?? 6);
        $allowed = [3, 4, 6, 8, 12];
        return in_array($span, $allowed, true) ? $span : 6;
    }

    /**
     * Fetch data needed to render the given card.
     * Returns an array that will be available as variables in the view partial.
     */
    public function fetchData(string $cardId, int $userId, array $options = []): array {
        if ($cardId === 'tickets.my_open') {
            if (!class_exists('Ticket')) {
                require_once APPROOT . '/app/models/Ticket.php';
            }
            $ticketModel = new Ticket();

            $tickets = $ticketModel->getOpenTicketsByUser($userId);
            $limit = isset($options['limit']) ? (int)$options['limit'] : 10;
            if ($limit > 0 && count($tickets) > $limit) {
                $tickets = array_slice($tickets, 0, $limit);
            }

            return [
                'tickets' => $tickets,
                'limit' => $limit,
            ];
        }

        if ($cardId === 'clients.all') {
            if (!class_exists('Client')) {
                require_once APPROOT . '/app/models/Client.php';
            }
            $clientModel = new Client();
            $clients = $clientModel->getAllClients();

            // Respect client visibility rules if role context is provided
            $roleId = isset($options['role_id']) ? (int)$options['role_id'] : null;
            $isAdmin = !empty($options['is_admin']);
            if (method_exists($clientModel, 'filterClientsForRole')) {
                $clients = $clientModel->filterClientsForRole($clients ?: [], $roleId, $isAdmin);
            }

            $limit = isset($options['limit']) ? (int)$options['limit'] : 10;
            if ($limit > 0 && count($clients) > $limit) {
                $clients = array_slice($clients, 0, $limit);
            }

            return [
                'clients' => $clients ?: [],
                'limit' => $limit
            ];
        }

        if ($cardId === 'clients.client') {
            $params = is_array($options['params'] ?? null) ? $options['params'] : [];
            $clientId = isset($params['client_id']) ? (int)$params['client_id'] : 0;
            if ($clientId <= 0) {
                return ['client' => null];
            }

            if (!class_exists('Client')) {
                require_once APPROOT . '/app/models/Client.php';
            }
            $clientModel = new Client();

            // Permission/visibility: deny if restricted and user cannot access
            $roleId = isset($options['role_id']) ? (int)$options['role_id'] : null;
            $isAdmin = !empty($options['is_admin']);
            if (method_exists($clientModel, 'canAccessClientId') && !$clientModel->canAccessClientId($clientId, $roleId, $isAdmin)) {
                return ['client' => null];
            }

            $client = $clientModel->getClientById($clientId);
            return [
                'client' => $client ?: null
            ];
        }

        return [];
    }
}


