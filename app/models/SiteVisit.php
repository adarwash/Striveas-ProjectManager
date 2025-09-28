<?php

class SiteVisit {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
        $this->ensureTable();
    }

    /**
     * Ensure the SiteVisits table exists
     */
    private function ensureTable() {
        try {
            $sql = "IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='SiteVisits' AND xtype='U')
            BEGIN
                CREATE TABLE SiteVisits (
                    id INT IDENTITY(1,1) PRIMARY KEY,
                    site_id INT NOT NULL,
                    technician_id INT NOT NULL,
                    visit_date DATETIME NOT NULL DEFAULT GETDATE(),
                    title NVARCHAR(255) NULL,
                    summary NVARCHAR(MAX) NULL,
                    created_at DATETIME DEFAULT GETDATE(),
                    updated_at DATETIME NULL,
                    CONSTRAINT FK_SiteVisits_Sites FOREIGN KEY (site_id) REFERENCES Sites(id) ON DELETE CASCADE,
                    CONSTRAINT FK_SiteVisits_Users FOREIGN KEY (technician_id) REFERENCES Users(id) ON DELETE NO ACTION
                )
            END";
            $this->db->query($sql);

            // Ensure 'reason' column exists (NVARCHAR(255))
            $alter = "IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'SiteVisits' AND COLUMN_NAME = 'reason')
                      BEGIN
                        ALTER TABLE SiteVisits ADD reason NVARCHAR(255) NULL
                      END";
            $this->db->query($alter);

            // Ensure 'previous_visit_id' column exists
            $alterPrev = "IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'SiteVisits' AND COLUMN_NAME = 'previous_visit_id')
                          BEGIN
                            ALTER TABLE SiteVisits ADD previous_visit_id INT NULL
                          END";
            $this->db->query($alterPrev);
        } catch (Exception $e) {
            error_log('Error ensuring SiteVisits table: ' . $e->getMessage());
        }
    }

    /**
     * Add a new site visit
     */
    public function addVisit(array $data): bool {
        try {
            $query = "INSERT INTO SiteVisits (site_id, technician_id, visit_date, title, summary, reason, previous_visit_id)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $params = [
                (int)$data['site_id'],
                (int)$data['technician_id'],
                $data['visit_date'],
                $data['title'],
                $data['summary'],
                $data['reason'] ?? null,
                isset($data['previous_visit_id']) && (int)$data['previous_visit_id'] > 0 ? (int)$data['previous_visit_id'] : null
            ];
            $result = $this->db->insert($query, $params);
            return $result !== false;
        } catch (Exception $e) {
            error_log('Error adding site visit: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get visits for a site
     */
    public function getVisitsBySite(int $siteId, int $limit = 50): array {
        try {
            $query = "SELECT v.*, u.username, u.full_name
                      FROM SiteVisits v
                      LEFT JOIN Users u ON v.technician_id = u.id
                      WHERE v.site_id = ?
                      ORDER BY v.visit_date DESC, v.created_at DESC";
            if ($limit > 0) {
                // SQL Server TOP usage
                $query = str_replace('SELECT ', 'SELECT TOP ' . (int)$limit . ' ', $query);
            }
            return $this->db->select($query, [$siteId]);
        } catch (Exception $e) {
            error_log('Error fetching site visits: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single visit by ID
     */
    public function getVisitById(int $id) {
        try {
            $query = "SELECT v.*, u.username, u.full_name
                      FROM SiteVisits v
                      LEFT JOIN Users u ON v.technician_id = u.id
                      WHERE v.id = ?";
            $result = $this->db->select($query, [$id]);
            return $result ? $result[0] : false;
        } catch (Exception $e) {
            error_log('Error fetching site visit: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing visit
     */
    public function updateVisit(array $data): bool {
        try {
            $query = "UPDATE SiteVisits SET
                        visit_date = ?,
                        title = ?,
                        summary = ?,
                        reason = ?,
                        previous_visit_id = ?,
                        updated_at = GETDATE()
                      WHERE id = ?";
            $params = [
                $data['visit_date'],
                $data['title'],
                $data['summary'],
                $data['reason'] ?? null,
                isset($data['previous_visit_id']) && (int)$data['previous_visit_id'] > 0 ? (int)$data['previous_visit_id'] : null,
                (int)$data['id']
            ];
            $this->db->update($query, $params);
            return true;
        } catch (Exception $e) {
            error_log('Error updating site visit: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get distinct recent reasons to suggest
     */
    public function getRecentReasons(int $limit = 10): array {
        try {
            $top = (int)$limit > 0 ? 'TOP ' . (int)$limit . ' ' : '';
            // Use DISTINCT with alphabetical order to avoid GROUP BY aggregate issues
            $query = "SELECT " . $top . "DISTINCT reason FROM SiteVisits WHERE reason IS NOT NULL AND LTRIM(RTRIM(reason)) <> '' ORDER BY reason ASC";
            // Fallback: recent order without DISTINCT if needed
            $fallback = 'SELECT ' . $top . "reason FROM SiteVisits WHERE reason IS NOT NULL AND LTRIM(RTRIM(reason)) <> '' ORDER BY created_at DESC";
            $rows = $this->db->select($query);
            if (!$rows) {
                $rows = $this->db->select($fallback);
            }
            $reasons = [];
            foreach ($rows as $row) {
                if (!empty($row['reason'])) {
                    $reasons[] = $row['reason'];
                }
            }
            // Deduplicate while preserving order
            $reasons = array_values(array_unique($reasons));
            return $reasons;
        } catch (Exception $e) {
            error_log('Error fetching recent reasons: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get related visits by reason (same site optional)
     */
    public function getRelatedByReason(string $reason, int $siteId = null, int $limit = 10): array {
        try {
            $top = (int)$limit > 0 ? 'TOP ' . (int)$limit . ' ' : '';
            $params = [];
            $query = 'SELECT ' . $top . 'v.*, u.full_name, u.username, s.name AS site_name ' .
                     'FROM SiteVisits v ' .
                     'LEFT JOIN Users u ON u.id = v.technician_id ' .
                     'LEFT JOIN Sites s ON s.id = v.site_id ' .
                     'WHERE v.reason = ?';
            $params[] = $reason;
            if ($siteId) {
                $query .= ' AND v.site_id = ?';
                $params[] = $siteId;
            }
            $query .= ' ORDER BY v.visit_date DESC, v.created_at DESC';
            return $this->db->select($query, $params) ?: [];
        } catch (Exception $e) {
            error_log('Error fetching related visits: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Build previous chain for a visit by walking previous_visit_id up to a max depth.
     * Returns visits from earliest to the one right before the current.
     */
    public function getPreviousChain(int $visitId, int $maxDepth = 20): array {
        $chain = [];
        $seen = [];
        try {
            $currentId = $visitId;
            for ($i = 0; $i < $maxDepth; $i++) {
                $visit = $this->getVisitById($currentId);
                if (!$visit || empty($visit['previous_visit_id'])) {
                    break;
                }
                $prevId = (int)$visit['previous_visit_id'];
                if ($prevId <= 0 || isset($seen[$prevId])) {
                    break; // stop on invalid or cycle
                }
                $prev = $this->getVisitById($prevId);
                if (!$prev) {
                    break;
                }
                $chain[] = $prev;
                $seen[$prevId] = true;
                $currentId = $prevId;
            }
            // We collected from immediate previous backward; reverse to earliest -> latest
            return array_reverse($chain);
        } catch (Exception $e) {
            error_log('Error building previous chain: ' . $e->getMessage());
            return array_reverse($chain);
        }
    }

    /**
     * Get follow-up visits that directly link to the given visit (immediate nexts).
     */
    public function getNextVisits(int $visitId, int $limit = 20): array {
        try {
            $top = (int)$limit > 0 ? 'TOP ' . (int)$limit . ' ' : '';
            $query = 'SELECT ' . $top . 'v.*, u.full_name, u.username ' .
                     'FROM SiteVisits v ' .
                     'LEFT JOIN Users u ON u.id = v.technician_id ' .
                     'WHERE v.previous_visit_id = ? ' .
                     'ORDER BY v.visit_date ASC, v.created_at ASC';
            return $this->db->select($query, [$visitId]) ?: [];
        } catch (Exception $e) {
            error_log('Error fetching next visits: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get previous visits on the same site for linking (exclude a given visit ID)
     */
    public function getPreviousVisitsForSite(int $siteId, int $excludeId = null, int $limit = 50): array {
        try {
            $top = (int)$limit > 0 ? 'TOP ' . (int)$limit . ' ' : '';
            $query = "SELECT " . $top . "id, title, visit_date FROM SiteVisits WHERE site_id = ?";
            $params = [$siteId];
            if ($excludeId) {
                $query .= " AND id <> ?";
                $params[] = $excludeId;
            }
            $query .= " ORDER BY visit_date DESC, created_at DESC";
            return $this->db->select($query, $params) ?: [];
        } catch (Exception $e) {
            error_log('Error fetching previous visits for site: ' . $e->getMessage());
            return [];
        }
    }
}
?>


