<?php

/**
 * ReportDefinition model
 *
 * Stores saved dynamic report definitions for the report builder.
 */
class ReportDefinition {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
        $this->ensureTable();
    }

    private function ensureTable(): void {
        try {
            $sql = "
IF OBJECT_ID('dbo.ReportDefinitions', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.ReportDefinitions (
        id INT IDENTITY(1,1) PRIMARY KEY,
        name NVARCHAR(255) NOT NULL,
        description NVARCHAR(MAX) NULL,
        dataset NVARCHAR(50) NOT NULL,
        definition_json NVARCHAR(MAX) NOT NULL,
        visibility NVARCHAR(20) NOT NULL CONSTRAINT DF_ReportDefinitions_Visibility DEFAULT('admin'),
        allowed_role_ids NVARCHAR(MAX) NULL,
        created_by INT NOT NULL,
        created_at DATETIME NOT NULL CONSTRAINT DF_ReportDefinitions_CreatedAt DEFAULT(GETDATE()),
        updated_at DATETIME NULL,
        is_active BIT NOT NULL CONSTRAINT DF_ReportDefinitions_IsActive DEFAULT(1)
    );

    CREATE INDEX IX_ReportDefinitions_IsActive ON dbo.ReportDefinitions(is_active);
    CREATE INDEX IX_ReportDefinitions_Dataset ON dbo.ReportDefinitions(dataset);
    CREATE INDEX IX_ReportDefinitions_CreatedBy ON dbo.ReportDefinitions(created_by);
END
";
            $this->db->query($sql);
        } catch (Exception $e) {
            error_log('ReportDefinition ensureTable error: ' . $e->getMessage());
        }
    }

    /**
     * Normalize a list of role IDs into a comma-delimited string of ints.
     */
    private function normalizeAllowedRoleIds($value): ?string {
        if ($value === null || $value === '') {
            return null;
        }
        $ids = [];
        if (is_array($value)) {
            $ids = $value;
        } else {
            $ids = preg_split('/[\s,]+/', (string)$value) ?: [];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function($v) {
            return $v > 0;
        })));
        if (empty($ids)) {
            return null;
        }
        return implode(',', $ids);
    }

    private function parseAllowedRoleIds($value): array {
        if (empty($value)) {
            return [];
        }
        if (is_array($value)) {
            return array_values(array_unique(array_filter(array_map('intval', $value))));
        }
        $parts = explode(',', (string)$value);
        return array_values(array_unique(array_filter(array_map('intval', $parts))));
    }

    public function getById(int $id): ?array {
        try {
            $rows = $this->db->select(
                "SELECT TOP 1 * FROM dbo.ReportDefinitions WHERE id = :id",
                ['id' => $id]
            );
            return !empty($rows) ? $rows[0] : null;
        } catch (Exception $e) {
            error_log('ReportDefinition getById error: ' . $e->getMessage());
            return null;
        }
    }

    public function listAllActive(): array {
        try {
            return $this->db->select(
                "SELECT * FROM dbo.ReportDefinitions WHERE is_active = 1 ORDER BY name ASC, id DESC"
            ) ?: [];
        } catch (Exception $e) {
            error_log('ReportDefinition listAllActive error: ' . $e->getMessage());
            return [];
        }
    }

    public function listForUser(?int $roleId, bool $isAdmin = false): array {
        if ($isAdmin) {
            return $this->listAllActive();
        }

        if (empty($roleId)) {
            return [];
        }

        // Filter in SQL using a safe CHARINDEX match on comma-delimited ids.
        // Example match: ',' + allowed_role_ids + ',' contains ',3,'
        try {
            $rows = $this->db->select(
                "SELECT * FROM dbo.ReportDefinitions
                 WHERE is_active = 1
                   AND visibility = 'roles'
                   AND allowed_role_ids IS NOT NULL
                   AND CHARINDEX(',' + CAST(:rid AS NVARCHAR(20)) + ',', ',' + allowed_role_ids + ',') > 0
                 ORDER BY name ASC, id DESC",
                ['rid' => (int)$roleId]
            );
            return $rows ?: [];
        } catch (Exception $e) {
            error_log('ReportDefinition listForUser error: ' . $e->getMessage());
            return [];
        }
    }

    public function userCanView(array $report, ?int $roleId, bool $isAdmin = false): bool {
        if ($isAdmin) {
            return true;
        }
        $visibility = strtolower((string)($report['visibility'] ?? 'admin'));
        if ($visibility === 'admin') {
            return false;
        }
        if ($visibility === 'roles') {
            if (empty($roleId)) {
                return false;
            }
            $allowed = $this->parseAllowedRoleIds($report['allowed_role_ids'] ?? '');
            return in_array((int)$roleId, $allowed, true);
        }
        return false;
    }

    public function userCanEdit(array $report, int $userId, bool $isAdmin = false): bool {
        if ($isAdmin) {
            return true;
        }
        // For now: only admin can edit definitions.
        return false;
    }

    /**
     * Create a new report definition.
     *
     * Expected keys: name, description, dataset, definition_json, visibility, allowed_role_ids, created_by
     */
    public function create(array $data) {
        try {
            $name = trim((string)($data['name'] ?? ''));
            $dataset = trim((string)($data['dataset'] ?? ''));
            $definitionJson = (string)($data['definition_json'] ?? '');
            if ($name === '' || $dataset === '' || trim($definitionJson) === '') {
                return false;
            }

            $visibility = strtolower(trim((string)($data['visibility'] ?? 'admin')));
            if (!in_array($visibility, ['admin', 'roles'], true)) {
                $visibility = 'admin';
            }
            $allowedRoleIds = $visibility === 'roles'
                ? $this->normalizeAllowedRoleIds($data['allowed_role_ids'] ?? null)
                : null;

            $id = $this->db->insert(
                "INSERT INTO dbo.ReportDefinitions (name, description, dataset, definition_json, visibility, allowed_role_ids, created_by, created_at)
                 VALUES (:name, :description, :dataset, :definition_json, :visibility, :allowed_role_ids, :created_by, GETDATE())",
                [
                    'name' => $name,
                    'description' => $data['description'] ?? null,
                    'dataset' => $dataset,
                    'definition_json' => $definitionJson,
                    'visibility' => $visibility,
                    'allowed_role_ids' => $allowedRoleIds,
                    'created_by' => (int)($data['created_by'] ?? 0),
                ]
            );

            if (!empty($id)) {
                return (int)$id;
            }

            // Fallback lookup by name+creator
            $row = $this->db->select(
                "SELECT TOP 1 id FROM dbo.ReportDefinitions WHERE name = :name AND created_by = :uid ORDER BY id DESC",
                ['name' => $name, 'uid' => (int)($data['created_by'] ?? 0)]
            );
            if (!empty($row[0]['id'])) {
                return (int)$row[0]['id'];
            }
            return false;
        } catch (Exception $e) {
            error_log('ReportDefinition create error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing report definition.
     */
    public function update(int $id, array $data): bool {
        try {
            $name = trim((string)($data['name'] ?? ''));
            $dataset = trim((string)($data['dataset'] ?? ''));
            $definitionJson = (string)($data['definition_json'] ?? '');
            if ($id <= 0 || $name === '' || $dataset === '' || trim($definitionJson) === '') {
                return false;
            }

            $visibility = strtolower(trim((string)($data['visibility'] ?? 'admin')));
            if (!in_array($visibility, ['admin', 'roles'], true)) {
                $visibility = 'admin';
            }
            $allowedRoleIds = $visibility === 'roles'
                ? $this->normalizeAllowedRoleIds($data['allowed_role_ids'] ?? null)
                : null;

            $this->db->update(
                "UPDATE dbo.ReportDefinitions
                 SET name = :name,
                     description = :description,
                     dataset = :dataset,
                     definition_json = :definition_json,
                     visibility = :visibility,
                     allowed_role_ids = :allowed_role_ids,
                     updated_at = GETDATE()
                 WHERE id = :id",
                [
                    'id' => $id,
                    'name' => $name,
                    'description' => $data['description'] ?? null,
                    'dataset' => $dataset,
                    'definition_json' => $definitionJson,
                    'visibility' => $visibility,
                    'allowed_role_ids' => $allowedRoleIds,
                ]
            );

            return true;
        } catch (Exception $e) {
            error_log('ReportDefinition update error: ' . $e->getMessage());
            return false;
        }
    }
}

