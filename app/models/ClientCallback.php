<?php

class ClientCallback {
	private $db;

	public function __construct() {
		$this->db = new EasySQL(DB1);
		$this->ensureTable();
	}

	private function ensureTable(): void {
		try {
			$sql = "
			IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[ClientCallbacks]') AND type in (N'U'))
			BEGIN
				CREATE TABLE [dbo].[ClientCallbacks] (
					[id] INT IDENTITY(1,1) PRIMARY KEY,
					[client_id] INT NOT NULL,
					[title] NVARCHAR(255) NOT NULL,
					[notes] NVARCHAR(MAX) NULL,
					[remind_at] DATETIME NOT NULL,
					[created_by] INT NOT NULL,
					[status] NVARCHAR(20) NOT NULL DEFAULT 'Pending',
					[reminder_queue_id] INT NULL,
					[reminder_sent_at] DATETIME NULL,
					[completed_at] DATETIME NULL,
					[created_at] DATETIME NOT NULL DEFAULT GETDATE(),
					[updated_at] DATETIME NULL
				);
			END
			;
			IF COL_LENGTH('dbo.ClientCallbacks', 'notify_all') IS NULL
			BEGIN
				ALTER TABLE [dbo].[ClientCallbacks] ADD [notify_all] BIT NOT NULL CONSTRAINT DF_ClientCallbacks_notify_all DEFAULT(0);
			END
			";
			$this->db->query($sql);
		} catch (Exception $e) {
			error_log('ClientCallback ensureTable error: ' . $e->getMessage());
		}
	}

	public function add(array $data) {
		try {
			$query = "INSERT INTO ClientCallbacks (client_id, title, notes, remind_at, created_by, status, created_at, notify_all)
			          VALUES (?, ?, ?, ?, ?, 'Pending', GETDATE(), ?)";
			return $this->db->insert($query, [
				(int)$data['client_id'],
				$data['title'],
				$data['notes'] ?? null,
				$data['remind_at'],
				(int)$data['created_by'],
				!empty($data['notify_all']) ? 1 : 0
			]);
		} catch (Exception $e) {
			error_log('ClientCallback add error: ' . $e->getMessage());
			return false;
		}
	}

	public function getByClientId(int $clientId): array {
		try {
			$query = "SELECT * FROM ClientCallbacks WHERE client_id = ? ORDER BY remind_at DESC, created_at DESC";
			return $this->db->select($query, [$clientId]) ?: [];
		} catch (Exception $e) {
			error_log('ClientCallback getByClientId error: ' . $e->getMessage());
			return [];
		}
	}

	public function getById(int $id) {
		try {
			$query = "SELECT * FROM ClientCallbacks WHERE id = ?";
			$result = $this->db->select($query, [$id]);
			return $result ? $result[0] : false;
		} catch (Exception $e) {
			error_log('ClientCallback getById error: ' . $e->getMessage());
			return false;
		}
	}

	public function getUpcomingByUser(int $userId, int $limit = 10): array {
		try {
			$top = $limit > 0 ? "TOP {$limit} " : '';
			$query = "SELECT {$top}*
			          FROM ClientCallbacks
			          WHERE status = 'Pending' 
			            AND remind_at >= DATEADD(day, -1, GETDATE())
			            AND (created_by = ? OR notify_all = 1)
			          ORDER BY remind_at ASC";
			return $this->db->select($query, [$userId]) ?: [];
		} catch (Exception $e) {
			error_log('ClientCallback getUpcomingByUser error: ' . $e->getMessage());
			return [];
		}
	}

	public function markCompleted(int $id): bool {
		try {
			$query = "UPDATE ClientCallbacks SET status = 'Completed', completed_at = GETDATE(), updated_at = GETDATE() WHERE id = ?";
			$this->db->update($query, [$id]);
			return true;
		} catch (Exception $e) {
			error_log('ClientCallback markCompleted error: ' . $e->getMessage());
			return false;
		}
	}

	public function setReminderQueueId(int $id, int $queueId): bool {
		try {
			$query = "UPDATE ClientCallbacks SET reminder_queue_id = ?, updated_at = GETDATE() WHERE id = ?";
			$this->db->update($query, [$queueId, $id]);
			return true;
		} catch (Exception $e) {
			error_log('ClientCallback setReminderQueueId error: ' . $e->getMessage());
			return false;
		}
	}

	public function markReminderSent(int $id): bool {
		try {
			$query = "UPDATE ClientCallbacks SET reminder_sent_at = GETDATE(), updated_at = GETDATE() WHERE id = ?";
			$this->db->update($query, [$id]);
			return true;
		} catch (Exception $e) {
			error_log('ClientCallback markReminderSent error: ' . $e->getMessage());
			return false;
		}
	}
}


