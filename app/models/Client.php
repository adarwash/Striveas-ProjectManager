<?php

class Client {
    private $db;

    public function __construct() {
        $this->db = new EasySQL(DB1);
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
} 