<?php

class Client {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
    }

    /**
     * Ensure optional Level.io columns exist on Clients table
     */
    public function ensureLevelIntegrationColumns(): void {
        try {
            $columns = [
                'level_io_group_id' => 'NVARCHAR(255) NULL',
                'level_io_group_name' => 'NVARCHAR(255) NULL'
            ];
            foreach ($columns as $column => $definition) {
                $exists = $this->db->select(
                    "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'Clients' AND COLUMN_NAME = ?",
                    [$column]
                );
                if (!$exists) {
                    $this->db->query("ALTER TABLE Clients ADD {$column} {$definition}");
                }
            }
        } catch (Exception $e) {
            error_log('ensureLevelIntegrationColumns error: ' . $e->getMessage());
        }
    }

    /**
     * Get all clients
     *
     * @return array Array of clients
     */
    public function getAllClients() {
        $query = "SELECT * FROM Clients ORDER BY name ASC";
        return $this->db->select($query);
    }

    /**
     * Get clients by status
     */
    public function getClientsByStatus(string $status) {
        try {
            $query = "SELECT * FROM Clients WHERE status = ? ORDER BY name ASC";
            return $this->db->select($query, [$status]);
        } catch (Exception $e) {
            error_log('getClientsByStatus error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get client by ID
     *
     * @param int $id Client ID
     * @return array|false Client data or false if not found
     */
    public function getClientById($id) {
        $query = "SELECT * FROM Clients WHERE id = ?";
        $result = $this->db->select($query, [$id]);
        
        // Return the first row or false if no rows
        return $result ? $result[0] : false;
    }
    
    /**
     * Get sites assigned to a client
     *
     * @param int $clientId Client ID
     * @return array Array of sites assigned to the client
     */
    public function getSiteClients($clientId) {
        $query = "SELECT s.*, sc.relationship_type 
                 FROM Sites s
                 JOIN SiteClients sc ON s.id = sc.site_id
                 WHERE sc.client_id = ?
                 ORDER BY s.name ASC";
        
        return $this->db->select($query, [$clientId]);
    }

    /**
     * Get top-N recent site visits for a client across all linked sites
     */
    public function getRecentSiteVisits(int $clientId, int $limit = 10): array {
        try {
            // Ensure SiteVisits and SiteClients exist
            $check1 = $this->db->select("SELECT COUNT(*) AS table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'SiteVisits'");
            $check2 = $this->db->select("SELECT COUNT(*) AS table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'SiteClients'");
            if (!$check1 || (int)($check1[0]['table_count'] ?? 0) === 0 || !$check2 || (int)($check2[0]['table_count'] ?? 0) === 0) {
                return [];
            }

            // SQL Server TOP usage for limit
            $top = (int)$limit > 0 ? 'TOP ' . (int)$limit . ' ' : '';
            $query = "SELECT {$top}v.id, v.site_id, v.technician_id, v.visit_date, v.title, v.summary,
                             s.name AS site_name, s.location AS site_location,
                             u.full_name, u.username
                      FROM SiteVisits v
                      INNER JOIN SiteClients sc ON sc.site_id = v.site_id AND sc.client_id = ?
                      LEFT JOIN Sites s ON s.id = v.site_id
                      LEFT JOIN Users u ON u.id = v.technician_id
                      ORDER BY v.visit_date DESC, v.created_at DESC";
            return $this->db->select($query, [$clientId]) ?: [];
        } catch (Exception $e) {
            error_log('Error fetching recent site visits for client: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Add a new client
     *
     * @param array $data Client data
     * @return int|boolean The inserted ID or false on failure
     */
    public function addClient($data) {
        try {
            $query = "INSERT INTO Clients (name, contact_person, email, phone, address, industry, status, notes, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, GETDATE())";
            
            return $this->db->insert($query, [
                $data['name'],
                $data['contact_person'] ?? null,
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['industry'] ?? null,
                $data['status'] ?? 'Active',
                $data['notes'] ?? null
            ]);
        } catch (Exception $e) {
            error_log('Error adding client: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a client
     *
     * @param array $data Client data
     * @return boolean Success status
     */
    public function updateClient($data) {
        try {
            $query = "UPDATE Clients 
                     SET name = ?, 
                         contact_person = ?, 
                         email = ?, 
                         phone = ?, 
                         address = ?, 
                         industry = ?, 
                         status = ?, 
                         notes = ?, 
                         updated_at = GETDATE() 
                     WHERE id = ?";
            
            $this->db->update($query, [
                $data['name'],
                $data['contact_person'] ?? null,
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['industry'] ?? null,
                $data['status'] ?? 'Active',
                $data['notes'] ?? null,
                $data['id']
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log('Error updating client: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ensure link table for multiple Level.io groups per client
     */
    public function ensureLevelGroupLinkTable(): void {
        try {
            $exists = $this->db->select("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'ClientLevelGroups'");
            if (!$exists) {
            $this->db->query("
                IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[ClientLevelGroups]') AND type in (N'U'))
                BEGIN
                    CREATE TABLE ClientLevelGroups (
                        id INT IDENTITY(1,1) PRIMARY KEY,
                        client_id INT NOT NULL,
                        level_group_id NVARCHAR(255) NOT NULL,
                        level_group_name NVARCHAR(255) NULL,
                        created_at DATETIME DEFAULT GETDATE(),
                        CONSTRAINT UQ_ClientLevelGroups UNIQUE (client_id, level_group_id),
                        CONSTRAINT FK_ClientLevelGroups_Client FOREIGN KEY (client_id) REFERENCES Clients(id) ON DELETE CASCADE
                    );
                END
            ");
            }
        } catch (Exception $e) {
            error_log('ensureLevelGroupLinkTable error: ' . $e->getMessage());
        }
    }

    public function getLevelGroups(int $clientId): array {
        try {
            $this->ensureLevelGroupLinkTable();
            $query = "SELECT * FROM ClientLevelGroups WHERE client_id = ? ORDER BY level_group_name";
            return $this->db->select($query, [$clientId]) ?: [];
        } catch (Exception $e) {
            error_log('getLevelGroups error: ' . $e->getMessage());
            return [];
        }
    }

    public function addLevelGroup(int $clientId, string $groupId, ?string $groupName = null): bool {
        try {
            $this->ensureLevelGroupLinkTable();
            // Check if already exists
            $check = $this->db->select(
                "SELECT id FROM ClientLevelGroups WHERE client_id = ? AND level_group_id = ?",
                [$clientId, $groupId]
            );
            if ($check && count($check) > 0) {
                // Already linked, update name if provided
                if ($groupName !== null) {
                    $this->db->update(
                        "UPDATE ClientLevelGroups SET level_group_name = ? WHERE client_id = ? AND level_group_id = ?",
                        [$groupName, $clientId, $groupId]
                    );
                }
                return true;
            }
            // Insert new link
            $this->db->insert(
                "INSERT INTO ClientLevelGroups (client_id, level_group_id, level_group_name, created_at) VALUES (?, ?, ?, GETDATE())",
                [$clientId, $groupId, $groupName]
            );
            return true;
        } catch (Exception $e) {
            error_log('addLevelGroup error: ' . $e->getMessage());
            return false;
        }
    }

    public function unlinkLevelGroup(int $clientId, string $groupId): bool {
        try {
            $this->ensureLevelGroupLinkTable();
            $query = "DELETE FROM ClientLevelGroups WHERE client_id = ? AND level_group_id = ?";
            $this->db->remove($query, [$clientId, $groupId]);
            return true;
        } catch (Exception $e) {
            error_log('unlinkLevelGroup error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a client
     *
     * @param int $id Client ID
     * @return boolean Success status
     */
    public function deleteClient($id) {
        try {
            // First remove any site associations
            $queryAssoc = "DELETE FROM SiteClients WHERE client_id = ?";
            $this->db->remove($queryAssoc, [$id]);
            
            // Then delete the client
            $query = "DELETE FROM Clients WHERE id = ?";
            $this->db->remove($query, [$id]);
            
            return true;
        } catch (Exception $e) {
            error_log('Error deleting client: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Assign a client to a site
     * 
     * @param int $clientId Client ID
     * @param int $siteId Site ID
     * @param string $relationshipType Type of relationship
     * @return boolean Success status
     */
    public function assignClientToSite($clientId, $siteId, $relationshipType = 'Standard') {
        try {
            // Check if relationship already exists
            $checkQuery = "SELECT * FROM SiteClients WHERE client_id = ? AND site_id = ?";
            $exists = $this->db->select($checkQuery, [$clientId, $siteId]);
            
            if ($exists) {
                // Update existing relationship
                $query = "UPDATE SiteClients SET relationship_type = ? WHERE client_id = ? AND site_id = ?";
                $this->db->update($query, [$relationshipType, $clientId, $siteId]);
            } else {
                // Create new relationship
                $query = "INSERT INTO SiteClients (client_id, site_id, relationship_type, created_at) 
                         VALUES (?, ?, ?, GETDATE())";
                $this->db->insert($query, [$clientId, $siteId, $relationshipType]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Error assigning client to site: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove a client from a site
     * 
     * @param int $clientId Client ID
     * @param int $siteId Site ID
     * @return boolean Success status
     */
    public function removeClientFromSite($clientId, $siteId) {
        try {
            $query = "DELETE FROM SiteClients WHERE client_id = ? AND site_id = ?";
            $this->db->remove($query, [$clientId, $siteId]);
            
            return true;
        } catch (Exception $e) {
            error_log('Error removing client from site: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update site client assignments for a specific client
     * 
     * @param int $clientId Client ID
     * @param array $siteIds Site IDs to assign
     * @param array $relationshipTypes Relationship types for each site
     * @return boolean Success status
     */
    public function updateSiteClientAssignments($clientId, $siteIds, $relationshipTypes) {
        try {
            // Remove all current site assignments for this client
            $removeQuery = "DELETE FROM SiteClients WHERE client_id = ?";
            $this->db->remove($removeQuery, [$clientId]);
            
            // Add new assignments
            if (!empty($siteIds)) {
                foreach ($siteIds as $index => $siteId) {
                    $relationshipType = isset($relationshipTypes[$index]) 
                                      ? $relationshipTypes[$index] 
                                      : 'Standard';
                    
                    $query = "INSERT INTO SiteClients (client_id, site_id, relationship_type, created_at) 
                             VALUES (?, ?, ?, GETDATE())";
                    $this->db->insert($query, [
                        $clientId,
                        $siteId,
                        $relationshipType
                    ]);
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Error updating client site assignments: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get client count
     * 
     * @return int Number of clients
     */
    public function getClientCount() {
        $query = "SELECT COUNT(*) as count FROM Clients";
        $result = $this->db->select($query);
        return $result[0]['count'] ?? 0;
    }
    
    /**
     * Get active clients count
     * 
     * @return int Number of active clients
     */
    public function getActiveClientsCount() {
        try {
            $query = "SELECT COUNT(*) as count FROM Clients WHERE status = 'Active'";
            $result = $this->db->select($query);
            return $result[0]['count'] ?? 0;
        } catch (Exception $e) {
            error_log('GetActiveClientsCount Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get clients count by status (e.g., 'Prospect')
     */
    public function getClientsCountByStatus(string $status) {
        try {
            $query = "SELECT COUNT(*) as count FROM Clients WHERE status = ?";
            $result = $this->db->select($query, [$status]);
            return $result[0]['count'] ?? 0;
        } catch (Exception $e) {
            error_log('GetClientsCountByStatus Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Search clients by name, contact person, email, or industry
     */
    public function searchClients($searchQuery, $limit = 10) {
        try {
            $query = "SELECT * FROM [Clients]
                     WHERE (name LIKE ? OR contact_person LIKE ? OR email LIKE ? OR industry LIKE ?)
                     AND status = 'Active'
                     ORDER BY 
                         CASE 
                             WHEN name LIKE ? THEN 1
                             WHEN contact_person LIKE ? THEN 2
                             WHEN email LIKE ? THEN 3
                             WHEN industry LIKE ? THEN 4
                             ELSE 5
                         END,
                         name ASC";
            
            $params = [
                $searchQuery, $searchQuery, $searchQuery, $searchQuery,
                $searchQuery, $searchQuery, $searchQuery, $searchQuery
            ];
            
            // SQL Server uses TOP instead of LIMIT
            if ($limit > 0) {
                $query = str_replace("SELECT *", "SELECT TOP $limit *", $query);
            }
            
            $results = $this->db->select($query, $params);
            return $results ?: [];
        } catch (Exception $e) {
            error_log('Client search error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top clients by number of associated projects (fallback: sites)
     *
     * Attempts to compute top clients by counting distinct projects linked to
     * the client's sites via project_sites. If project_sites doesn't exist,
     * falls back to counting distinct sites from SiteClients.
     *
     * @param int $limit Maximum number of clients to return
     * @return array List of top clients with counts
     */
    public function getTopClients($limit = 5) {
        try {
            // Ensure SiteClients table exists
            $tableCheck = "SELECT COUNT(*) AS table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'SiteClients'";
            $result = $this->db->select($tableCheck);
            $siteClientsExists = ($result && isset($result[0]['table_count']) && (int)$result[0]['table_count'] > 0);

            if (!$siteClientsExists) {
                // No linkage table; return active clients with zero counts as a safe fallback
                $query = "SELECT id, name, CAST(0 AS INT) AS sites_count, CAST(0 AS INT) AS projects_count FROM Clients WHERE status = 'Active' ORDER BY name ASC";
                if ($limit > 0) {
                    $query = str_replace("SELECT id, name", "SELECT TOP $limit id, name", $query);
                }
                return $this->db->select($query) ?: [];
            }

            // Check if project_sites table exists
            $psCheck = "SELECT COUNT(*) AS table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'project_sites'";
            $psResult = $this->db->select($psCheck);
            $projectSitesExists = ($psResult && isset($psResult[0]['table_count']) && (int)$psResult[0]['table_count'] > 0);

            if ($projectSitesExists) {
                $query = "SELECT c.id, c.name,
                                COUNT(DISTINCT sc.site_id) AS sites_count,
                                COUNT(DISTINCT ps.project_id) AS projects_count
                          FROM Clients c
                          LEFT JOIN SiteClients sc ON sc.client_id = c.id
                          LEFT JOIN project_sites ps ON ps.site_id = sc.site_id
                          WHERE c.status = 'Active'
                          GROUP BY c.id, c.name
                          ORDER BY COUNT(DISTINCT ps.project_id) DESC, COUNT(DISTINCT sc.site_id) DESC, c.name ASC";
                if ($limit > 0) {
                    $query = str_replace("SELECT c.id, c.name", "SELECT TOP $limit c.id, c.name", $query);
                }
                return $this->db->select($query) ?: [];
            } else {
                $query = "SELECT c.id, c.name,
                                 COUNT(DISTINCT sc.site_id) AS sites_count
                          FROM Clients c
                          LEFT JOIN SiteClients sc ON sc.client_id = c.id
                          WHERE c.status = 'Active'
                          GROUP BY c.id, c.name
                          ORDER BY COUNT(DISTINCT sc.site_id) DESC, c.name ASC";
                if ($limit > 0) {
                    $query = str_replace("SELECT c.id, c.name", "SELECT TOP $limit c.id, c.name", $query);
                }
                $rows = $this->db->select($query) ?: [];
                // Normalize shape by adding projects_count = 0
                foreach ($rows as &$row) {
                    if (!isset($row['projects_count'])) {
                        $row['projects_count'] = 0;
                    }
                }
                return $rows;
            }
        } catch (Exception $e) {
            error_log('Error fetching top clients: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top clients by ticket volume within a date range (capped to 90 days)
     *
     * @param string $range One of: 'today', 'week', 'month'
     * @param int $limit Max number of clients to return
     * @param int $maxDaysCap Absolute maximum days to include (default 90)
     * @return array
     */
    public function getTopClientsByTickets($range = 'month', $limit = 5, $maxDaysCap = 90) {
        try {
            // Verify Tickets table exists
            $checkTickets = "SELECT COUNT(*) AS table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'Tickets'";
            $exists = $this->db->select($checkTickets);
            if (!$exists || (int)($exists[0]['table_count'] ?? 0) === 0) {
                return [];
            }

            // Determine window in days
            $range = strtolower((string)$range);
            switch ($range) {
                case 'today':
                    $days = 0;
                    break;
                case 'week':
                    $days = 7;
                    break;
                case 'month':
                default:
                    $days = 30;
                    break;
            }
            // Cap to max days
            $days = min((int)$days, (int)$maxDaysCap);

            // Compute from-date
            if ($days === 0) {
                // Today starting at midnight
                $fromDate = date('Y-m-d 00:00:00');
            } else {
                $fromDate = date('Y-m-d H:i:s', strtotime("-$days days"));
            }

            // Build query (SQL Server TOP needs literal integer)
            $top = (int)$limit > 0 ? "TOP $limit " : '';
            $query = "SELECT {$top}c.id, c.name, COUNT(*) AS ticket_count
                      FROM Tickets t
                      INNER JOIN Clients c ON c.id = t.client_id
                      WHERE t.client_id IS NOT NULL
                        AND t.created_at >= :from_date
                        AND t.created_at <= GETDATE()
                      GROUP BY c.id, c.name
                      ORDER BY COUNT(*) DESC, c.name ASC";

            $rows = $this->db->select($query, ['from_date' => $fromDate]);
            return $rows ?: [];
        } catch (Exception $e) {
            error_log('Error fetching top clients by tickets: ' . $e->getMessage());
            return [];
        }
    }
} 