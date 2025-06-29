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
     * Get clients assigned to a site
     *
     * @param int $siteId Site ID
     * @return array Array of clients
     */
    public function getSiteClients($siteId) {
        $query = "SELECT c.*, sc.relationship_type 
                 FROM Clients c
                 JOIN SiteClients sc ON c.id = sc.client_id
                 WHERE sc.site_id = ?
                 ORDER BY c.name ASC";
        
        return $this->db->select($query, [$siteId]);
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
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
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
                         updated_at = NOW() 
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
                         VALUES (?, ?, ?, NOW())";
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
     * Update site client assignments
     * 
     * @param int $siteId Site ID
     * @param array $clientIds Client IDs to assign
     * @param array $relationshipTypes Relationship types for each client
     * @return boolean Success status
     */
    public function updateSiteClientAssignments($siteId, $clientIds, $relationshipTypes) {
        try {
            $this->db->beginTransaction();
            
            // Remove all current client assignments
            $removeQuery = "DELETE FROM SiteClients WHERE site_id = ?";
            $this->db->remove($removeQuery, [$siteId]);
            
            // Add new assignments
            if (!empty($clientIds)) {
                $query = "INSERT INTO SiteClients (client_id, site_id, relationship_type, created_at) 
                         VALUES (?, ?, ?, NOW())";
                
                foreach ($clientIds as $index => $clientId) {
                    $relationshipType = isset($relationshipTypes[$index]) 
                                      ? $relationshipTypes[$index] 
                                      : 'Standard';
                    
                    $this->db->insert($query, [
                        $clientId,
                        $siteId,
                        $relationshipType
                    ]);
                }
            }
            
            $this->db->commitTransaction();
            
            return true;
        } catch (Exception $e) {
            $this->db->rollbackTransaction();
            error_log('Error updating site client assignments: ' . $e->getMessage());
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
} 