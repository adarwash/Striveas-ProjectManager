<?php

/**
 * DynamicReportService
 *
 * Builds and runs safe, validated dynamic reports against SQL Server.
 */
class DynamicReportService {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
    }

    /**
     * Dataset presets (expandable).
     *
     * @return array<string,array>
     */
    private function datasets(): array {
        return [
            'tickets' => [
                'label' => 'Tickets',
                'description' => 'Support tickets (status/priority/client/user)',
                'from' => "
                    Tickets t
                    LEFT JOIN TicketStatuses ts ON ts.id = t.status_id
                    LEFT JOIN TicketPriorities tp ON tp.id = t.priority_id
                    LEFT JOIN TicketCategories tc ON tc.id = t.category_id
                    LEFT JOIN Users ua ON ua.id = t.assigned_to
                    LEFT JOIN Users uc ON uc.id = t.created_by
                    LEFT JOIN Clients c ON c.id = t.client_id
                ",
                'access_client_id_expr' => 't.client_id',
                'fields' => [
                    'ticket_id' => ['label' => 'Ticket ID', 'type' => 'number', 'expr' => 't.id', 'groupable' => true, 'sortable' => true, 'filterable' => true, 'numeric' => true],
                    'ticket_number' => ['label' => 'Ticket #', 'type' => 'string', 'expr' => 't.ticket_number', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'subject' => ['label' => 'Subject', 'type' => 'string', 'expr' => 't.subject', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'status' => ['label' => 'Status', 'type' => 'select', 'expr' => 'ts.display_name', 'groupable' => true, 'sortable' => true, 'filterable' => true,
                        'options_sql' => "SELECT display_name AS label, display_name AS value FROM TicketStatuses WHERE is_active = 1 ORDER BY sort_order ASC"
                    ],
                    'priority' => ['label' => 'Priority', 'type' => 'select', 'expr' => 'tp.display_name', 'groupable' => true, 'sortable' => true, 'filterable' => true,
                        'options_sql' => "SELECT display_name AS label, display_name AS value FROM TicketPriorities WHERE is_active = 1 ORDER BY sort_order ASC"
                    ],
                    'category' => ['label' => 'Category', 'type' => 'select', 'expr' => 'tc.name', 'groupable' => true, 'sortable' => true, 'filterable' => true,
                        'options_sql' => "SELECT name AS label, name AS value FROM TicketCategories WHERE is_active = 1 ORDER BY name ASC"
                    ],
                    'client_name' => ['label' => 'Client', 'type' => 'string', 'expr' => 'c.name', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'assigned_to' => ['label' => 'Assigned To', 'type' => 'string', 'expr' => 'COALESCE(ua.full_name, ua.username)', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'created_by' => ['label' => 'Created By', 'type' => 'string', 'expr' => 'COALESCE(uc.full_name, uc.username)', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'created_at' => ['label' => 'Created At', 'type' => 'datetime', 'expr' => 't.created_at', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'updated_at' => ['label' => 'Updated At', 'type' => 'datetime', 'expr' => 't.updated_at', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'due_date' => ['label' => 'Due Date', 'type' => 'datetime', 'expr' => 't.due_date', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'source' => ['label' => 'Source', 'type' => 'string', 'expr' => 't.source', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                ],
            ],
            'tasks' => [
                'label' => 'Tasks',
                'description' => 'Tasks (status/priority/project/client/user)',
                'from' => "
                    tasks t
                    LEFT JOIN projects p ON p.id = t.project_id
                    LEFT JOIN Clients c ON c.id = p.client_id
                    LEFT JOIN users ua ON ua.id = t.assigned_to
                    LEFT JOIN users uc ON uc.id = t.created_by
                ",
                'access_client_id_expr' => 'p.client_id',
                'fields' => [
                    'task_id' => ['label' => 'Task ID', 'type' => 'number', 'expr' => 't.id', 'groupable' => true, 'sortable' => true, 'filterable' => true, 'numeric' => true],
                    'title' => ['label' => 'Title', 'type' => 'string', 'expr' => 't.title', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'status' => ['label' => 'Status', 'type' => 'string', 'expr' => 't.status', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'priority' => ['label' => 'Priority', 'type' => 'string', 'expr' => 't.priority', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'project_title' => ['label' => 'Project', 'type' => 'string', 'expr' => 'p.title', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'client_name' => ['label' => 'Client', 'type' => 'string', 'expr' => 'c.name', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'assigned_to' => ['label' => 'Assigned To', 'type' => 'string', 'expr' => 'COALESCE(ua.full_name, ua.username)', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'created_by' => ['label' => 'Created By', 'type' => 'string', 'expr' => 'COALESCE(uc.full_name, uc.username)', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'start_date' => ['label' => 'Start Date', 'type' => 'date', 'expr' => 't.start_date', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'due_date' => ['label' => 'Due Date', 'type' => 'date', 'expr' => 't.due_date', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'estimated_hours' => ['label' => 'Estimated Hours', 'type' => 'number', 'expr' => 't.estimated_hours', 'groupable' => true, 'sortable' => true, 'filterable' => true, 'numeric' => true],
                    'progress_percent' => ['label' => 'Progress %', 'type' => 'number', 'expr' => 't.progress_percent', 'groupable' => true, 'sortable' => true, 'filterable' => true, 'numeric' => true],
                    'created_at' => ['label' => 'Created At', 'type' => 'datetime', 'expr' => 't.created_at', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'updated_at' => ['label' => 'Updated At', 'type' => 'datetime', 'expr' => 't.updated_at', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                ],
            ],
            'projects' => [
                'label' => 'Projects',
                'description' => 'Projects (status/budget/client/department/owner)',
                'from' => "
                    projects p
                    LEFT JOIN Clients c ON c.id = p.client_id
                    LEFT JOIN departments d ON d.id = p.department_id
                    LEFT JOIN users u ON u.id = p.user_id
                ",
                'access_client_id_expr' => 'p.client_id',
                'fields' => [
                    'project_id' => ['label' => 'Project ID', 'type' => 'number', 'expr' => 'p.id', 'groupable' => true, 'sortable' => true, 'filterable' => true, 'numeric' => true],
                    'title' => ['label' => 'Title', 'type' => 'string', 'expr' => 'p.title', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'status' => ['label' => 'Status', 'type' => 'string', 'expr' => 'p.status', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'client_name' => ['label' => 'Client', 'type' => 'string', 'expr' => 'c.name', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'department' => ['label' => 'Department', 'type' => 'string', 'expr' => 'd.name', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'owner' => ['label' => 'Owner', 'type' => 'string', 'expr' => 'COALESCE(u.full_name, u.username)', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'budget' => ['label' => 'Budget', 'type' => 'number', 'expr' => 'p.budget', 'groupable' => true, 'sortable' => true, 'filterable' => true, 'numeric' => true],
                    'start_date' => ['label' => 'Start Date', 'type' => 'date', 'expr' => 'p.start_date', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'end_date' => ['label' => 'End Date', 'type' => 'date', 'expr' => 'p.end_date', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'created_at' => ['label' => 'Created At', 'type' => 'datetime', 'expr' => 'p.created_at', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'updated_at' => ['label' => 'Updated At', 'type' => 'datetime', 'expr' => 'p.updated_at', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                ],
            ],
            'time_entries' => [
                'label' => 'TimeEntries',
                'description' => 'Time entries (clock in/out, hours, user, site, client)',
                'from' => "
                    dbo.TimeEntries te
                    LEFT JOIN dbo.Users u ON u.id = te.user_id
                    LEFT JOIN Sites s ON s.id = te.site_id
                    OUTER APPLY (
                        SELECT TOP 1 c.id AS client_id, c.name AS client_name
                        FROM SiteClients sc
                        INNER JOIN Clients c ON c.id = sc.client_id
                        WHERE sc.site_id = te.site_id
                        ORDER BY c.name ASC
                    ) ca
                ",
                'access_client_id_expr' => 'ca.client_id',
                'fields' => [
                    'time_entry_id' => ['label' => 'Entry ID', 'type' => 'number', 'expr' => 'te.id', 'groupable' => true, 'sortable' => true, 'filterable' => true, 'numeric' => true],
                    'user' => ['label' => 'User', 'type' => 'string', 'expr' => 'COALESCE(u.full_name, u.username)', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'status' => ['label' => 'Status', 'type' => 'string', 'expr' => 'te.status', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'clock_in_time' => ['label' => 'Clock In', 'type' => 'datetime', 'expr' => 'te.clock_in_time', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'clock_out_time' => ['label' => 'Clock Out', 'type' => 'datetime', 'expr' => 'te.clock_out_time', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'total_hours' => ['label' => 'Total Hours', 'type' => 'number', 'expr' => 'te.total_hours', 'groupable' => true, 'sortable' => true, 'filterable' => true, 'numeric' => true],
                    'break_minutes' => ['label' => 'Break Minutes', 'type' => 'number', 'expr' => 'te.total_break_minutes', 'groupable' => true, 'sortable' => true, 'filterable' => true, 'numeric' => true],
                    'site' => ['label' => 'Site', 'type' => 'string', 'expr' => 's.name', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'client_name' => ['label' => 'Client', 'type' => 'string', 'expr' => 'ca.client_name', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'created_at' => ['label' => 'Created At', 'type' => 'datetime', 'expr' => 'te.created_at', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'updated_at' => ['label' => 'Updated At', 'type' => 'datetime', 'expr' => 'te.updated_at', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                ],
            ],
            'clients' => [
                'label' => 'Clients',
                'description' => 'Clients (status, contact info, industry)',
                'from' => "Clients c",
                'access_client_id_expr' => 'c.id',
                'fields' => [
                    'client_id' => ['label' => 'Client ID', 'type' => 'number', 'expr' => 'c.id', 'groupable' => true, 'sortable' => true, 'filterable' => true, 'numeric' => true],
                    'name' => ['label' => 'Name', 'type' => 'string', 'expr' => 'c.name', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'status' => ['label' => 'Status', 'type' => 'string', 'expr' => 'c.status', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'industry' => ['label' => 'Industry', 'type' => 'string', 'expr' => 'c.industry', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'contact_person' => ['label' => 'Contact', 'type' => 'string', 'expr' => 'c.contact_person', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'email' => ['label' => 'Email', 'type' => 'string', 'expr' => 'c.email', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'phone' => ['label' => 'Phone', 'type' => 'string', 'expr' => 'c.phone', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'created_at' => ['label' => 'Created At', 'type' => 'datetime', 'expr' => 'c.created_at', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'updated_at' => ['label' => 'Updated At', 'type' => 'datetime', 'expr' => 'c.updated_at', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                ],
            ],
            'site_visits' => [
                'label' => 'SiteVisits',
                'description' => 'Site visits (site, technician, date, client)',
                'from' => "
                    SiteVisits v
                    LEFT JOIN Sites s ON s.id = v.site_id
                    LEFT JOIN Users u ON u.id = v.technician_id
                    OUTER APPLY (
                        SELECT TOP 1 c.id AS client_id, c.name AS client_name
                        FROM SiteClients sc
                        INNER JOIN Clients c ON c.id = sc.client_id
                        WHERE sc.site_id = v.site_id
                        ORDER BY c.name ASC
                    ) ca
                ",
                'access_client_id_expr' => 'ca.client_id',
                'fields' => [
                    'visit_id' => ['label' => 'Visit ID', 'type' => 'number', 'expr' => 'v.id', 'groupable' => true, 'sortable' => true, 'filterable' => true, 'numeric' => true],
                    'visit_date' => ['label' => 'Visit Date', 'type' => 'datetime', 'expr' => 'v.visit_date', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'title' => ['label' => 'Title', 'type' => 'string', 'expr' => 'v.title', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'reason' => ['label' => 'Reason', 'type' => 'string', 'expr' => 'v.reason', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'technician' => ['label' => 'Technician', 'type' => 'string', 'expr' => 'COALESCE(u.full_name, u.username)', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'site' => ['label' => 'Site', 'type' => 'string', 'expr' => 's.name', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'client_name' => ['label' => 'Client', 'type' => 'string', 'expr' => 'ca.client_name', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'created_at' => ['label' => 'Created At', 'type' => 'datetime', 'expr' => 'v.created_at', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                    'updated_at' => ['label' => 'Updated At', 'type' => 'datetime', 'expr' => 'v.updated_at', 'groupable' => true, 'sortable' => true, 'filterable' => true],
                ],
            ],
        ];
    }

    public function getDatasetPresets(): array {
        $out = [];
        foreach ($this->datasets() as $key => $cfg) {
            $out[] = [
                'key' => $key,
                'label' => $cfg['label'] ?? $key,
                'description' => $cfg['description'] ?? '',
            ];
        }
        return $out;
    }

    /**
     * Returns field metadata for a dataset, including select options where configured.
     */
    public function getFieldsForDataset(string $datasetKey): array {
        $datasets = $this->datasets();
        if (!isset($datasets[$datasetKey])) {
            return [];
        }
        $cfg = $datasets[$datasetKey];
        $fields = $cfg['fields'] ?? [];

        $out = [];
        foreach ($fields as $key => $meta) {
            $row = [
                'key' => $key,
                'label' => $meta['label'] ?? $key,
                'type' => $meta['type'] ?? 'string',
                'groupable' => !empty($meta['groupable']),
                'sortable' => !empty($meta['sortable']),
                'filterable' => !empty($meta['filterable']),
                'numeric' => !empty($meta['numeric']),
                'options' => [],
            ];
            if (($meta['type'] ?? '') === 'select' && !empty($meta['options_sql']) && is_string($meta['options_sql'])) {
                try {
                    $opts = $this->db->select($meta['options_sql']) ?: [];
                    $row['options'] = array_values(array_map(function($o) {
                        return [
                            'label' => (string)($o['label'] ?? $o['value'] ?? ''),
                            'value' => (string)($o['value'] ?? $o['label'] ?? ''),
                        ];
                    }, $opts));
                } catch (Exception $e) {
                    $row['options'] = [];
                }
            }
            $out[] = $row;
        }
        return $out;
    }

    private function normalizeKey(string $key): string {
        // Ensure safe alias names like ticket_id, created_at
        $k = preg_replace('/[^A-Za-z0-9_]/', '_', $key);
        return $k ?: 'col';
    }

    /**
     * Build WHERE clause and parameters for the report filters.
     *
     * @return array{0:string,1:array}
     */
    private function buildWhere(array $filters, array $fieldMap): array {
        $clauses = [];
        $params = [];
        $i = 0;
        foreach ($filters as $f) {
            if (!is_array($f)) continue;
            $field = (string)($f['field'] ?? '');
            $op = strtolower(trim((string)($f['op'] ?? '')));
            if ($field === '' || !isset($fieldMap[$field])) {
                continue;
            }
            $meta = $fieldMap[$field];
            if (empty($meta['filterable'])) {
                continue;
            }
            $expr = (string)$meta['expr'];
            $pbase = 'p' . $i;

            if ($op === 'is_null') {
                $clauses[] = "{$expr} IS NULL";
                $i++;
                continue;
            }
            if ($op === 'not_null') {
                $clauses[] = "{$expr} IS NOT NULL";
                $i++;
                continue;
            }

            $val = $f['value'] ?? null;
            $type = (string)($meta['type'] ?? 'string');
            $numeric = !empty($meta['numeric']);

            $castVal = function($v) use ($numeric) {
                if ($numeric) {
                    if (is_string($v) && trim($v) === '') return null;
                    return is_numeric($v) ? (float)$v : null;
                }
                if (is_bool($v)) return $v ? 1 : 0;
                if ($v === null) return null;
                if (is_string($v) && trim($v) === '') return null;
                return (string)$v;
            };

            switch ($op) {
                case '=':
                case '!=':
                case '>=':
                case '<=':
                    $p = $pbase;
                    $pv = $castVal($val);
                    if ($pv === null) {
                        $i++;
                        continue 2;
                    }
                    $params[$p] = $pv;
                    $sqlOp = $op === '!=' ? '<>' : $op;
                    $clauses[] = "{$expr} {$sqlOp} :{$p}";
                    break;

                case 'contains':
                    $p = $pbase;
                    $sv = trim((string)$val);
                    if ($sv === '') {
                        $i++;
                        continue 2;
                    }
                    $params[$p] = '%' . $sv . '%';
                    $clauses[] = "{$expr} LIKE :{$p}";
                    break;

                case 'starts_with':
                    $p = $pbase;
                    $sv = trim((string)$val);
                    if ($sv === '') {
                        $i++;
                        continue 2;
                    }
                    $params[$p] = $sv . '%';
                    $clauses[] = "{$expr} LIKE :{$p}";
                    break;

                case 'in':
                    $values = [];
                    if (is_array($val)) {
                        $values = $val;
                    } else {
                        $values = preg_split('/\s*,\s*/', (string)$val) ?: [];
                    }
                    $values = array_values(array_filter(array_map(function($x) use ($castVal) {
                        $v = $castVal($x);
                        if ($v === null) return null;
                        if (is_string($v) && trim($v) === '') return null;
                        return $v;
                    }, $values), function($v) {
                        return $v !== null;
                    }));
                    if (empty($values)) {
                        $i++;
                        continue 2;
                    }
                    $ph = [];
                    foreach ($values as $j => $vv) {
                        $p = "{$pbase}_{$j}";
                        $ph[] = ':' . $p;
                        $params[$p] = $vv;
                    }
                    $clauses[] = "{$expr} IN (" . implode(',', $ph) . ")";
                    break;

                case 'between':
                    $a = null;
                    $b = null;
                    if (is_array($val)) {
                        $a = $val[0] ?? null;
                        $b = $val[1] ?? null;
                    } else if (is_array($f) && isset($f['value_from'], $f['value_to'])) {
                        $a = $f['value_from'];
                        $b = $f['value_to'];
                    }
                    $pa = "{$pbase}_a";
                    $pb = "{$pbase}_b";
                    $va = $castVal($a);
                    $vb = $castVal($b);
                    if ($va === null || $vb === null) {
                        $i++;
                        continue 2;
                    }
                    $params[$pa] = $va;
                    $params[$pb] = $vb;
                    $clauses[] = "{$expr} BETWEEN :{$pa} AND :{$pb}";
                    break;

                default:
                    // Unsupported operator
                    break;
            }

            $i++;
        }

        if (empty($clauses)) {
            return ['', $params];
        }
        return ['WHERE ' . implode(' AND ', $clauses), $params];
    }

    /**
     * Run a report definition.
     *
     * @param array $definition
     * @param array $ctx Access context:
     *  - role_id (int|null)
     *  - is_admin (bool)
     *  - blocked_client_ids (int[])
     * @param array $options:
     *  - page (int)
     *  - per_page (int)
     * @return array
     */
    public function run(array $definition, array $ctx = [], array $options = []): array {
        $datasets = $this->datasets();
        $datasetKey = strtolower(trim((string)($definition['dataset'] ?? '')));
        if ($datasetKey === '' || !isset($datasets[$datasetKey])) {
            return ['success' => false, 'message' => 'Invalid dataset'];
        }
        $cfg = $datasets[$datasetKey];
        $fields = $cfg['fields'] ?? [];

        $page = max(1, (int)($options['page'] ?? ($definition['page'] ?? 1)));
        $perPage = (int)($options['per_page'] ?? ($definition['per_page'] ?? 25));
        if ($perPage <= 0) $perPage = 25;
        $perPage = min(200, $perPage);

        $limit = isset($definition['limit']) ? (int)$definition['limit'] : 0;
        if ($limit < 0) $limit = 0;
        $limit = min(10000, $limit); // hard cap

        $columns = is_array($definition['columns'] ?? null) ? $definition['columns'] : [];
        $filters = is_array($definition['filters'] ?? null) ? $definition['filters'] : [];
        $groupBy = is_array($definition['group_by'] ?? null) ? $definition['group_by'] : [];
        $aggregates = is_array($definition['aggregates'] ?? null) ? $definition['aggregates'] : [];
        $sort = is_array($definition['sort'] ?? null) ? $definition['sort'] : [];

        // Build field map for quick lookup (include expr + flags)
        $fieldMap = [];
        foreach ($fields as $k => $m) {
            $fieldMap[$k] = $m;
        }

        $isAggregated = !empty($groupBy) || !empty($aggregates);

        // Validate selected columns
        $selected = [];
        foreach ($columns as $k) {
            $kk = (string)$k;
            if ($kk !== '' && isset($fieldMap[$kk])) {
                $selected[] = $kk;
            }
        }
        $selected = array_values(array_unique($selected));
        if (!$isAggregated && empty($selected)) {
            return ['success' => false, 'message' => 'Select at least one column'];
        }

        // Validate group by fields
        $groupFields = [];
        foreach ($groupBy as $k) {
            $kk = (string)$k;
            if ($kk !== '' && isset($fieldMap[$kk]) && !empty($fieldMap[$kk]['groupable'])) {
                $groupFields[] = $kk;
            }
        }
        $groupFields = array_values(array_unique($groupFields));

        // Build SELECT list
        $selectParts = [];
        $outputColumns = []; // [{key,label}]
        $aggAliases = []; // idx => alias

        if ($isAggregated) {
            foreach ($groupFields as $k) {
                $alias = $this->normalizeKey($k);
                $selectParts[] = $fieldMap[$k]['expr'] . " AS [" . $alias . "]";
                $outputColumns[] = ['key' => $alias, 'label' => $fields[$k]['label'] ?? $k];
            }
            foreach ($aggregates as $idx => $a) {
                if (!is_array($a)) continue;
                $fn = strtoupper(trim((string)($a['fn'] ?? '')));
                $field = (string)($a['field'] ?? '');
                if (!in_array($fn, ['COUNT', 'SUM', 'AVG', 'MIN', 'MAX'], true)) {
                    continue;
                }
                if ($fn !== 'COUNT') {
                    if ($field === '' || !isset($fieldMap[$field]) || empty($fieldMap[$field]['numeric'])) {
                        continue;
                    }
                }
                $alias = 'agg_' . (int)$idx;
                $aggAliases[(int)$idx] = $alias;
                if ($fn === 'COUNT') {
                    $selectParts[] = "COUNT(*) AS [" . $alias . "]";
                } else {
                    $selectParts[] = "{$fn}(" . $fieldMap[$field]['expr'] . ") AS [" . $alias . "]";
                }
                $label = trim((string)($a['label'] ?? ''));
                if ($label === '') {
                    $label = $fn . ($field !== '' ? ' ' . ($fields[$field]['label'] ?? $field) : '');
                }
                $outputColumns[] = ['key' => $alias, 'label' => $label];
            }
            if (empty($selectParts)) {
                return ['success' => false, 'message' => 'Add group by fields and/or aggregates'];
            }
        } else {
            foreach ($selected as $k) {
                $alias = $this->normalizeKey($k);
                $selectParts[] = $fieldMap[$k]['expr'] . " AS [" . $alias . "]";
                $outputColumns[] = ['key' => $alias, 'label' => $fields[$k]['label'] ?? $k];
            }
        }

        // WHERE
        [$whereClause, $params] = $this->buildWhere($filters, $fieldMap);

        // Apply blocked client restrictions when not admin
        $blocked = is_array($ctx['blocked_client_ids'] ?? null) ? $ctx['blocked_client_ids'] : [];
        $isAdmin = !empty($ctx['is_admin']);
        $clientExpr = (string)($cfg['access_client_id_expr'] ?? '');
        if (!$isAdmin && $clientExpr !== '' && !empty($blocked)) {
            $blocked = array_values(array_unique(array_filter(array_map('intval', $blocked), function($v){ return $v > 0; })));
            if (!empty($blocked)) {
                $ph = [];
                foreach ($blocked as $j => $cid) {
                    $p = 'bc_' . $j;
                    $ph[] = ':' . $p;
                    $params[$p] = (int)$cid;
                }
                $clause = "({$clientExpr} IS NULL OR {$clientExpr} NOT IN (" . implode(',', $ph) . "))";
                if ($whereClause === '') {
                    $whereClause = 'WHERE ' . $clause;
                } else {
                    $whereClause .= ' AND ' . $clause;
                }
            }
        }

        // GROUP BY
        $groupByClause = '';
        if ($isAggregated && !empty($groupFields)) {
            $groupExprs = array_map(function($k) use ($fieldMap) {
                return $fieldMap[$k]['expr'];
            }, $groupFields);
            $groupByClause = 'GROUP BY ' . implode(', ', $groupExprs);
        }

        // ORDER BY
        $orderParts = [];
        $allowedOutKeys = array_map(function($c){ return $c['key']; }, $outputColumns);
        foreach ($sort as $s) {
            if (!is_array($s)) continue;
            $fieldKey = (string)($s['field'] ?? '');
            $dir = strtoupper(trim((string)($s['dir'] ?? 'ASC')));
            if (!in_array($dir, ['ASC', 'DESC'], true)) $dir = 'ASC';

            // sort field can be output key (alias) or original field key
            $outKey = '';
            if (in_array($fieldKey, $allowedOutKeys, true)) {
                $outKey = $fieldKey;
            } else if (isset($fieldMap[$fieldKey])) {
                $outKey = $this->normalizeKey($fieldKey);
                if (!in_array($outKey, $allowedOutKeys, true)) {
                    $outKey = '';
                }
            }
            if ($outKey === '') continue;
            $orderParts[] = '[' . $outKey . '] ' . $dir;
        }
        if (empty($orderParts)) {
            // Default order: first output column
            $first = $outputColumns[0]['key'] ?? '';
            if ($first !== '') {
                $orderParts[] = '[' . $first . '] ASC';
            }
        }
        $orderByClause = 'ORDER BY ' . implode(', ', $orderParts);

        $fromClause = trim((string)$cfg['from']);

        // Total rows
        $total = 0;
        try {
            if ($isAggregated) {
                if (empty($groupFields)) {
                    // aggregate-only query always returns a single row
                    $total = 1;
                } else {
                    $groupExprs = array_map(function($k) use ($fieldMap) {
                        return $fieldMap[$k]['expr'];
                    }, $groupFields);
                    $countSql = "SELECT COUNT(*) AS total FROM (SELECT " . implode(', ', $groupExprs) . " FROM {$fromClause} {$whereClause} {$groupByClause}) g";
                    $rows = $this->db->select($countSql, $params);
                    $total = isset($rows[0]['total']) ? (int)$rows[0]['total'] : 0;
                }
            } else {
                $countSql = "SELECT COUNT(*) AS total FROM {$fromClause} {$whereClause}";
                $rows = $this->db->select($countSql, $params);
                $total = isset($rows[0]['total']) ? (int)$rows[0]['total'] : 0;
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to count results'];
        }

        if ($limit > 0) {
            $total = min($total, $limit);
        }

        $offset = ($page - 1) * $perPage;
        if ($limit > 0 && $offset >= $limit) {
            return [
                'success' => true,
                'dataset' => $datasetKey,
                'columns' => $outputColumns,
                'rows' => [],
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage
            ];
        }

        $fetch = $perPage;
        if ($limit > 0) {
            $remaining = max(0, $limit - $offset);
            $fetch = min($fetch, $remaining);
        }

        $sql = "SELECT " . implode(', ', $selectParts) . " FROM {$fromClause} {$whereClause} {$groupByClause} {$orderByClause} OFFSET " . (int)$offset . " ROWS FETCH NEXT " . (int)$fetch . " ROWS ONLY";

        try {
            $rows = $this->db->select($sql, $params) ?: [];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to run report'];
        }

        return [
            'success' => true,
            'dataset' => $datasetKey,
            'columns' => $outputColumns,
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage
        ];
    }
}

