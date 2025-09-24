<?php

class ClientDomain {
	private $db;

	public function __construct() {
		$this->db = new EasySQL(DB1);
		$this->ensureTable();
	}

	/**
	 * Ensure the ClientDomains table exists
	 */
	private function ensureTable() {
		try {
			$query = "SELECT COUNT(*) AS table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'ClientDomains'";
			$result = $this->db->select($query);
			$tableExists = ($result && isset($result[0]['table_count']) && (int)$result[0]['table_count'] > 0);

			if (!$tableExists) {
				$this->db->query("IF NOT EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'ClientDomains')
				BEGIN
					CREATE TABLE ClientDomains (
						id INT IDENTITY(1,1) PRIMARY KEY,
						client_id INT NOT NULL,
						domain NVARCHAR(255) NOT NULL,
						is_primary BIT DEFAULT 0,
						created_at DATETIME DEFAULT GETDATE(),
						updated_at DATETIME NULL,
						CONSTRAINT UQ_ClientDomains_Domain UNIQUE (domain),
						CONSTRAINT FK_ClientDomains_Clients FOREIGN KEY (client_id) REFERENCES Clients(id) ON DELETE CASCADE
					)
				END");
			}
		} catch (Exception $e) {
			error_log('ClientDomain ensureTable error: ' . $e->getMessage());
		}
	}

	/**
	 * Normalize a domain string (lowercase, trim, remove leading @)
	 */
	private function normalizeDomain($domain) {
		$normalized = strtolower(trim((string)$domain));
		$normalized = preg_replace('/^mailto:/', '', $normalized);
		$normalized = ltrim($normalized, '@');
		return $normalized;
	}

	/**
	 * Extract domain from email and return matching client_id if any
	 * @param string $email
	 * @return int|null
	 */
	public function getClientIdByEmail($email) {
		try {
			$email = strtolower(trim((string)$email));
			if (strpos($email, '@') === false) {
				return null;
			}
			$domain = substr(strrchr($email, '@'), 1);
			$domain = $this->normalizeDomain($domain);
			if ($domain === '') {
				return null;
			}
			$row = $this->db->select("SELECT TOP 1 client_id FROM ClientDomains WHERE domain = :domain", ['domain' => $domain]);
			return $row && isset($row[0]['client_id']) ? (int)$row[0]['client_id'] : null;
		} catch (Exception $e) {
			return null;
		}
	}

	/**
	 * List domains for a client
	 * @param int $clientId
	 * @return array
	 */
	public function getDomainsByClient($clientId) {
		try {
			return $this->db->select(
				"SELECT id, domain, is_primary, created_at FROM ClientDomains WHERE client_id = :client_id ORDER BY domain",
				['client_id' => $clientId]
			);
		} catch (Exception $e) {
			return [];
		}
	}

	/**
	 * Add a domain to a client (idempotent on domain)
	 * @param int $clientId
	 * @param string $domain
	 * @param bool $isPrimary
	 * @return bool
	 */
	public function addDomain($clientId, $domain, $isPrimary = false) {
		try {
			$clientId = (int)$clientId;
			$domain = $this->normalizeDomain($domain);
			if ($clientId <= 0 || $domain === '') {
				return false;
			}
			// Attempt insert; on duplicate, update client_id/is_primary
			try {
				$this->db->insert(
					"INSERT INTO ClientDomains (client_id, domain, is_primary) VALUES (:client_id, :domain, :is_primary)",
					['client_id' => $clientId, 'domain' => $domain, 'is_primary' => $isPrimary ? 1 : 0]
				);
				return true;
			} catch (Exception $e) {
				// Likely unique constraint on domain; update to new client or primary flag
				$this->db->update(
					"UPDATE ClientDomains SET client_id = :client_id, is_primary = :is_primary, updated_at = GETDATE() WHERE domain = :domain",
					['client_id' => $clientId, 'is_primary' => $isPrimary ? 1 : 0, 'domain' => $domain]
				);
				return true;
			}
		} catch (Exception $e) {
			error_log('ClientDomain addDomain error: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Remove a domain mapping by ID
	 * @param int $id
	 * @return bool
	 */
	public function removeById($id) {
		try {
			$this->db->remove("DELETE FROM ClientDomains WHERE id = :id", ['id' => (int)$id]);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
}


