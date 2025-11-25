<?php

class ClientDocument {
	private $db;

	public function __construct() {
		$this->db = new EasySQL(DB1);
		$this->ensureTableExists();
	}

	private function ensureTableExists(): void {
		try {
			$exists = $this->db->select("SELECT COUNT(*) AS table_count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'client_documents'");
			if ($exists && (int)($exists[0]['table_count'] ?? 0) > 0) {
				return;
			}

			$create = "
				CREATE TABLE client_documents (
					id INT IDENTITY(1,1) PRIMARY KEY,
					client_id INT NOT NULL,
					file_name NVARCHAR(255) NOT NULL,
					file_path NVARCHAR(1024) NOT NULL,
					file_type NVARCHAR(255) NULL,
					file_size BIGINT NULL,
					tags NVARCHAR(255) NULL,
					description NVARCHAR(MAX) NULL,
					uploaded_by INT NOT NULL,
					uploaded_at DATETIME NOT NULL DEFAULT GETDATE(),
					CONSTRAINT fk_client_documents_client FOREIGN KEY (client_id) REFERENCES Clients(id) ON DELETE CASCADE,
					CONSTRAINT fk_client_documents_user FOREIGN KEY (uploaded_by) REFERENCES Users(id)
				)
			";
			$this->db->query($create);
			$this->db->query("CREATE INDEX IX_client_docs_client_id ON client_documents(client_id)");
			$this->db->query("CREATE INDEX IX_client_docs_uploaded_at ON client_documents(uploaded_at)");
		} catch (Exception $e) {
			error_log('ClientDocument ensureTableExists error: ' . $e->getMessage());
		}
	}

	public function listByClient(int $clientId): array {
		try {
			$query = "
				SELECT d.*, u.username AS uploaded_by_name
				FROM client_documents d
				LEFT JOIN Users u ON u.id = d.uploaded_by
				WHERE d.client_id = ?
				ORDER BY d.uploaded_at DESC
			";
			return $this->db->select($query, [$clientId]) ?: [];
		} catch (Exception $e) {
			error_log('ClientDocument listByClient error: ' . $e->getMessage());
			return [];
		}
	}

	/**
	 * @param array $fileData Keys: file_name, file_path, file_type, file_size, uploaded_by
	 */
	public function uploadDocument(int $clientId, array $fileData, ?string $tags = null, ?string $description = null) {
		try {
			$query = "
				INSERT INTO client_documents (client_id, file_name, file_path, file_type, file_size, tags, description, uploaded_by, uploaded_at)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, GETDATE())
			";
			return $this->db->insert($query, [
				$clientId,
				$fileData['file_name'],
				$fileData['file_path'],
				$fileData['file_type'] ?? null,
				(int)($fileData['file_size'] ?? 0),
				$tags,
				$description,
				(int)($fileData['uploaded_by'] ?? 0)
			]);
		} catch (Exception $e) {
			error_log('ClientDocument uploadDocument error: ' . $e->getMessage());
			return false;
		}
	}

	public function getById(int $id) {
		try {
			$rows = $this->db->select("
				SELECT d.*, u.username AS uploaded_by_name
				FROM client_documents d
				LEFT JOIN Users u ON u.id = d.uploaded_by
				WHERE d.id = ?
			", [$id]);
			return $rows ? $rows[0] : false;
		} catch (Exception $e) {
			error_log('ClientDocument getById error: ' . $e->getMessage());
			return false;
		}
	}

	public function delete(int $id): bool {
		try {
			$this->db->remove("DELETE FROM client_documents WHERE id = ?", [$id]);
			return true;
		} catch (Exception $e) {
			error_log('ClientDocument delete error: ' . $e->getMessage());
			return false;
		}
	}

	public function rename(int $id, string $newName): bool {
		try {
			$newName = trim($newName);
			if ($newName === '') {
				return false;
			}
			$this->db->update("UPDATE client_documents SET file_name = ? WHERE id = ?", [$newName, $id]);
			return true;
		} catch (Exception $e) {
			error_log('ClientDocument rename error: ' . $e->getMessage());
			return false;
		}
	}
}


