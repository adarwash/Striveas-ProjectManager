<?php

class Site {
    private $db;
    
    /**
     * Constructor - initializes the database connection
     */
    public function __construct() {
        $this->db = new EasySQL(DB1);
        
        // Verify that required tables exist
        $this->verifyClientTables();
    }
    
    /**
     * Verify client-related tables exist
     * If not, create them
     */
    private function verifyClientTables() {
        try {
            // Check if Clients table exists - SQL Server compatible syntax
            $query = "SELECT COUNT(*) AS table_count FROM INFORMATION_SCHEMA.TABLES 
                     WHERE TABLE_NAME = 'Clients'";
            $result = $this->db->select($query);
            $tableExists = ($result && isset($result[0]['table_count']) && $result[0]['table_count'] > 0);
            
            if (!$tableExists) {
                error_log('Clients table does not exist. Attempting to create it...');
                
                // Create Clients table - SQL Server syntax
                $this->db->query("IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'Clients')
                BEGIN
                    CREATE TABLE Clients (
                        id INT IDENTITY(1,1) PRIMARY KEY,
                        name NVARCHAR(255) NOT NULL,
                        contact_person NVARCHAR(255) NULL,
                        email NVARCHAR(255) NULL,
                        phone NVARCHAR(50) NULL,
                        address NVARCHAR(MAX) NULL,
                        industry NVARCHAR(100) NULL,
                        status NVARCHAR(20) DEFAULT 'Active' NULL,
                        notes NVARCHAR(MAX) NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME NULL
                    )
                END");
                
                error_log('Clients table created successfully');
                
                // Add sample client - SQL Server syntax
                $this->db->query("INSERT INTO Clients (name, contact_person, email, industry, status) 
                                 VALUES ('Sample Client', 'John Doe', 'john@example.com', 'Technology', 'Active')");
            }
            
            // Check if SiteClients table exists - SQL Server compatible syntax
            $query = "SELECT COUNT(*) AS table_count FROM INFORMATION_SCHEMA.TABLES 
                     WHERE TABLE_NAME = 'SiteClients'";
            $result = $this->db->select($query);
            $tableExists = ($result && isset($result[0]['table_count']) && $result[0]['table_count'] > 0);
            
            if (!$tableExists) {
                error_log('SiteClients table does not exist. Attempting to create it...');
                
                // Create SiteClients junction table - SQL Server syntax
                $this->db->query("IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'SiteClients')
                BEGIN
                    CREATE TABLE SiteClients (
                        id INT IDENTITY(1,1) PRIMARY KEY,
                        site_id INT NOT NULL,
                        client_id INT NOT NULL,
                        relationship_type NVARCHAR(20) DEFAULT 'Standard',
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME NULL,
                        CONSTRAINT FK_SiteClients_Sites FOREIGN KEY (site_id) REFERENCES Sites(id) ON DELETE CASCADE,
                        CONSTRAINT FK_SiteClients_Clients FOREIGN KEY (client_id) REFERENCES Clients(id) ON DELETE CASCADE,
                        CONSTRAINT UQ_SiteClient UNIQUE (site_id, client_id)
                    )
                END");
                
                error_log('SiteClients table created successfully');
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Error verifying client tables: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all sites
     * 
     * @return array List of sites
     */
    public function getAllSites() {
        try {
            $query = "SELECT * FROM Sites ORDER BY name ASC";
            return $this->db->select($query);
        } catch (Exception $e) {
            error_log('Error fetching sites: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get site by ID
     * 
     * @param int $id Site ID
     * @return array|bool Site data or false if not found
     */
    public function getSiteById($id) {
        try {
            $query = "SELECT * FROM Sites WHERE id = ?";
            $result = $this->db->select($query, [$id]);
            
            if (!empty($result)) {
                return $result[0];
            }
            
            return false;
        } catch (Exception $e) {
            error_log('Error fetching site: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add a new site
     * 
     * @param array $data Site data
     * @return bool Success status
     */
    public function addSite($data) {
        try {
            $query = "INSERT INTO Sites (
                        name, location, address, site_code, type, status
                    ) VALUES (?, ?, ?, ?, ?, ?)";
                    
            $params = [
                $data['name'],
                $data['location'],
                $data['address'],
                $data['site_code'],
                $data['type'],
                $data['status']
            ];
            
            // The insert method returns the last inserted ID
            $result = $this->db->insert($query, $params);
            
            return $result !== false;
        } catch (Exception $e) {
            error_log('Error adding site: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update existing site
     * 
     * @param array $data Site data
     * @return bool Success status
     */
    public function updateSite($data) {
        try {
            $query = "UPDATE Sites SET
                        name = ?,
                        location = ?,
                        address = ?,
                        site_code = ?,
                        type = ?,
                        status = ?,
                        updated_at = GETDATE()
                    WHERE id = ?";
                    
            $params = [
                $data['name'],
                $data['location'],
                $data['address'],
                $data['site_code'],
                $data['type'],
                $data['status'],
                $data['id']
            ];
            
            // The update method doesn't return anything
            $this->db->update($query, $params);
            
            // If we get here, the update was successful
            return true;
        } catch (Exception $e) {
            error_log('Error updating site: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete site
     * 
     * @param int $id Site ID
     * @return bool Success status
     */
    public function deleteSite($id) {
        try {
            // First remove all employee assignments
            $this->removeAllEmployeesFromSite($id);
            
            // Then delete the site
            $query = "DELETE FROM Sites WHERE id = ?";
            $this->db->remove($query, [$id]);
            
            return true;
        } catch (Exception $e) {
            error_log('Error deleting site: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get employees assigned to a site
     * 
     * @param int $siteId Site ID
     * @return array List of employees
     */
    public function getSiteEmployees($siteId) {
        try {
            $query = "SELECT es.*, u.username, u.full_name, u.email, u.role
                     FROM EmployeeSites es
                     JOIN Users u ON es.user_id = u.id
                     WHERE es.site_id = ?
                     ORDER BY u.full_name ASC";
                     
            return $this->db->select($query, [$siteId]);
        } catch (Exception $e) {
            error_log('Error fetching site employees: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update site employee assignments
     * 
     * @param int $siteId Site ID
     * @param array $employeeIds Array of employee IDs to assign
     * @return bool Success status
     */
    public function updateSiteAssignments($siteId, $employeeIds) {
        try {
            // Start a transaction
            $this->db->beginTransaction();
            
            // Remove all current assignments
            $this->removeAllEmployeesFromSite($siteId);
            
            // Add new assignments
            if (!empty($employeeIds)) {
                $query = "INSERT INTO EmployeeSites (user_id, site_id, role, is_primary)
                         VALUES (?, ?, ?, ?)";
                         
                foreach ($employeeIds as $employeeId) {
                    // Check if this is the employee's primary site
                    $isPrimary = $this->isEmployeePrimarySite($employeeId, $siteId);
                    
                    $this->db->insert($query, [
                        $employeeId,
                        $siteId,
                        'Regular Staff', // Default role
                        $isPrimary ? 1 : 0
                    ]);
                }
            }
            
            // Commit the transaction
            $this->db->commitTransaction();
            
            return true;
        } catch (Exception $e) {
            // Roll back the transaction on error
            $this->db->rollbackTransaction();
            error_log('Error updating site assignments: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove all employees from a site
     * 
     * @param int $siteId Site ID
     * @return bool Success status
     */
    private function removeAllEmployeesFromSite($siteId) {
        try {
            $query = "DELETE FROM EmployeeSites WHERE site_id = ?";
            $this->db->remove($query, [$siteId]);
            
            return true;
        } catch (Exception $e) {
            error_log('Error removing employees from site: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a site is an employee's primary site
     * 
     * @param int $employeeId Employee ID
     * @param int $siteId Site ID
     * @return bool True if primary site
     */
    private function isEmployeePrimarySite($employeeId, $siteId) {
        try {
            // If this is the employee's only site, it's primary
            $query = "SELECT COUNT(*) as count FROM EmployeeSites WHERE user_id = ?";
            $result = $this->db->select($query, [$employeeId]);
            
            if (empty($result) || $result[0]['count'] == 0) {
                return true;
            }
            
            // If the employee is already assigned to this site and it's primary, keep it primary
            $query = "SELECT is_primary FROM EmployeeSites WHERE user_id = ? AND site_id = ?";
            $result = $this->db->select($query, [$employeeId, $siteId]);
            
            if (!empty($result) && $result[0]['is_primary'] == 1) {
                return true;
            }
            
            // Otherwise, it's not primary
            return false;
        } catch (Exception $e) {
            error_log('Error checking primary site: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get site count (for dashboard/stats)
     * 
     * @return int Number of sites
     */
    public function getSiteCount() {
        try {
            $query = "SELECT COUNT(*) as count FROM Sites";
            $result = $this->db->select($query);
            
            return $result[0]['count'] ?? 0;
        } catch (Exception $e) {
            error_log('Error counting sites: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get projects linked to a site
     * 
     * @param int $siteId Site ID
     * @return array List of linked projects
     */
    public function getLinkedProjects($siteId) {
        try {
            // Check if the project_sites table exists
            $query = "SELECT COUNT(*) AS table_count FROM INFORMATION_SCHEMA.TABLES 
                     WHERE TABLE_NAME = 'project_sites'";
            $result = $this->db->select($query);
            $tableExists = ($result && isset($result[0]['table_count']) && $result[0]['table_count'] > 0);
            
            if (!$tableExists) {
                error_log('The project_sites table does not exist. Creating it now...');
                // Try to create the table
                $this->createProjectSitesTable();
                return [];
            }
            
            $query = "SELECT p.*, u.username as created_by, ps.notes, ps.link_date
                     FROM projects p
                     JOIN project_sites ps ON p.id = ps.project_id
                     LEFT JOIN users u ON p.user_id = u.id
                     WHERE ps.site_id = ?
                     ORDER BY p.title ASC";
            
            error_log("Executing query for site ID $siteId: " . $query);
            $results = $this->db->select($query, [$siteId]);
            error_log("Query returned " . count($results) . " results");
            
            return $results;
        } catch (Exception $e) {
            error_log('Error getting linked projects: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }
    
    /**
     * Create the project_sites table
     * 
     * @return boolean Success status
     */
    public function createProjectSitesTable() {
        try {
            $sql = "
            IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='project_sites' AND xtype='U')
            BEGIN
                CREATE TABLE project_sites (
                    id INT IDENTITY(1,1) PRIMARY KEY,
                    project_id INT NOT NULL,
                    site_id INT NOT NULL,
                    link_date DATETIME DEFAULT GETDATE(),
                    notes NVARCHAR(500) NULL,
                    created_at DATETIME DEFAULT GETDATE(),
                    updated_at DATETIME NULL,
                    CONSTRAINT fk_project_sites_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
                    CONSTRAINT fk_project_sites_site FOREIGN KEY (site_id) REFERENCES Sites(id) ON DELETE CASCADE,
                    CONSTRAINT uk_project_site UNIQUE (project_id, site_id)
                )
            END";
            
            $this->db->query($sql);
            error_log('Successfully created project_sites table');
            return true;
        } catch (Exception $e) {
            error_log('Error creating project_sites table: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get clients associated with a site
     * 
     * @param int $siteId Site ID
     * @return array List of clients
     */
    public function getSiteClients($siteId) {
        try {
            // Check if the SiteClients table exists first
            $query = "SELECT COUNT(*) AS table_count FROM INFORMATION_SCHEMA.TABLES 
                     WHERE TABLE_NAME = 'SiteClients'";
            $result = $this->db->select($query);
            $tableExists = ($result && isset($result[0]['table_count']) && $result[0]['table_count'] > 0);
            
            if (!$tableExists) {
                error_log("SiteClients table doesn't exist yet. Creating tables...");
                $this->verifyClientTables();
                return [];
            }
            
            // SQL Server friendly join query
            $query = "SELECT c.*, sc.relationship_type 
                     FROM Clients c
                     INNER JOIN SiteClients sc ON c.id = sc.client_id
                     WHERE sc.site_id = ?
                     ORDER BY c.name";
            
            return $this->db->select($query, [$siteId]);
        } catch (Exception $e) {
            error_log('Error getting site clients: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update site client assignments
     * 
     * @param int $siteId Site ID
     * @param array $clientIds Client IDs to assign
     * @param array $relationshipTypes Relationship types for each client
     * @return boolean Success status
     */
    public function updateSiteClientAssignments($siteId, $clientIds, $relationshipTypes = []) {
        $siteId = intval($siteId);
        
        error_log("Starting client assignment update for site ID: $siteId");
        error_log("Client IDs to assign: " . json_encode($clientIds));
        error_log("Relationship types: " . json_encode($relationshipTypes));
        
        try {
            // 1. Delete all existing assignments for this site
            $query = "DELETE FROM SiteClients WHERE site_id = ?";
            $this->db->remove($query, [$siteId]);
            error_log("Deleted existing client assignments for site $siteId");
            
            // 2. If there are no clients selected, we're done (all removed)
            if (empty($clientIds)) {
                error_log("No clients selected, all assignments removed.");
                return true;
            }
            
            // 3. Insert new assignments one by one (SQL Server friendly approach)
            $successCount = 0;
            foreach ($clientIds as $clientId) {
                // Default relationship type is Standard
                $relationshipType = 'Standard';
                
                // If a specific relationship type is provided, use it
                if (isset($relationshipTypes[$clientId])) {
                    $relationshipType = $relationshipTypes[$clientId];
                }
                
                // SQL Server friendly insert
                $query = "INSERT INTO SiteClients (client_id, site_id, relationship_type) 
                         VALUES (?, ?, ?)";
                         
                try {
                    $this->db->insert($query, [
                        $clientId,
                        $siteId,
                        $relationshipType
                    ]);
                    $successCount++;
                    error_log("Added client $clientId to site $siteId with relationship: $relationshipType");
                } catch (Exception $ex) {
                    // Log but continue with other inserts
                    error_log("Failed to add client $clientId: " . $ex->getMessage());
                }
            }
            
            error_log("Successfully added $successCount clients to site $siteId");
            return true;
        } catch (Exception $e) {
            error_log("Error updating client assignments: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get client count for a site
     * 
     * @param int $siteId Site ID
     * @return int Number of clients
     */
    public function getSiteClientCount($siteId) {
        try {
            $query = "SELECT COUNT(*) as count FROM SiteClients WHERE site_id = ?";
            $result = $this->db->select($query, [$siteId]);
            return $result[0]['count'] ?? 0;
        } catch (Exception $e) {
            error_log('Error getting site client count: ' . $e->getMessage());
            return 0;
        }
    }
}
?> 