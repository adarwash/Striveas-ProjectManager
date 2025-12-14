<?php

class Reminder {
	private $db;

	public function __construct() {
		$this->db = new EasySQL(DB1);
		$this->ensureTable();
	}

	private function ensureTable(): void {
		try {
			$sql = "
			IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[Reminders]') AND type in (N'U'))
			BEGIN
				CREATE TABLE [dbo].[Reminders] (
					[id] INT IDENTITY(1,1) PRIMARY KEY,
					[entity_type] NVARCHAR(50) NOT NULL,
					[entity_id] INT NOT NULL,
					[title] NVARCHAR(255) NOT NULL,
					[notes] NVARCHAR(MAX) NULL,
					[remind_at] DATETIME NOT NULL,
					[created_by] INT NOT NULL,
					[recipient_user_id] INT NULL,
					[status] NVARCHAR(20) NOT NULL DEFAULT 'Pending',
					[notify_all] BIT NOT NULL CONSTRAINT DF_Reminders_notify_all DEFAULT(0),
					[reminder_queue_id] INT NULL,
					[reminder_sent_at] DATETIME NULL,
					[completed_at] DATETIME NULL,
					[created_at] DATETIME NOT NULL DEFAULT GETDATE(),
					[updated_at] DATETIME NULL
				);
				CREATE INDEX IX_Reminders_Entity ON [dbo].[Reminders] ([entity_type], [entity_id]);
				CREATE INDEX IX_Reminders_RemindAt ON [dbo].[Reminders] ([status], [remind_at]);
			END
			;
			-- Add any missing columns safely
			IF COL_LENGTH('dbo.Reminders', 'notify_all') IS NULL
			BEGIN
				ALTER TABLE [dbo].[Reminders] ADD [notify_all] BIT NOT NULL CONSTRAINT DF_Reminders_notify_all DEFAULT(0);
			END
			;
			IF COL_LENGTH('dbo.Reminders', 'recipient_user_id') IS NULL
			BEGIN
				ALTER TABLE [dbo].[Reminders] ADD [recipient_user_id] INT NULL;
			END
			;
			-- One-time migration from legacy tables if Reminders is empty
			IF NOT EXISTS (SELECT 1 FROM [dbo].[Reminders])
			BEGIN
				IF EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[ClientCallbacks]') AND type in (N'U'))
				BEGIN
					INSERT INTO [dbo].[Reminders] (entity_type, entity_id, title, notes, remind_at, created_by, recipient_user_id, status, notify_all, reminder_queue_id, reminder_sent_at, completed_at, created_at, updated_at)
					SELECT 'client', client_id, title, notes, remind_at, created_by, NULL, status, COALESCE(notify_all, 0), reminder_queue_id, reminder_sent_at, completed_at, created_at, updated_at
					FROM [dbo].[ClientCallbacks];
				END
				IF EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[ProjectCallbacks]') AND type in (N'U'))
				BEGIN
					INSERT INTO [dbo].[Reminders] (entity_type, entity_id, title, notes, remind_at, created_by, recipient_user_id, status, notify_all, reminder_queue_id, reminder_sent_at, completed_at, created_at, updated_at)
					SELECT 'project', project_id, title, notes, remind_at, created_by, NULL, status, COALESCE(notify_all, 0), reminder_queue_id, reminder_sent_at, completed_at, created_at, updated_at
					FROM [dbo].[ProjectCallbacks];
				END
			END
			";
			$this->db->query($sql);
		} catch (Exception $e) {
			error_log('Reminder ensureTable error: ' . $e->getMessage());
		}
	}

	public function add(array $data) {
		try {
			// Detect if recipient_user_id column exists to build compatible SQL
			$hasRecipient = false;
			try {
				$col = $this->db->select("SELECT COL_LENGTH('dbo.Reminders','recipient_user_id') AS len");
				$hasRecipient = !empty($col) && isset($col[0]['len']) && $col[0]['len'] !== null;
			} catch (Exception $e) {
				$hasRecipient = false;
			}

			// Idempotency: avoid duplicate pending reminders for same entity/title/time/user
			if ($hasRecipient) {
				$existing = $this->db->select(
					"SELECT TOP 1 id FROM Reminders 
					 WHERE entity_type = ? AND entity_id = ? 
					   AND title = ? AND remind_at = ? 
					   AND created_by = ? 
					   AND ISNULL(recipient_user_id, -1) = ISNULL(?, -1)
					   AND status = 'Pending'
					 ORDER BY id DESC",
					[
						$data['entity_type'],
						(int)$data['entity_id'],
						$data['title'],
						$data['remind_at'],
						(int)$data['created_by'],
						isset($data['recipient_user_id']) ? (int)$data['recipient_user_id'] : null,
					]
				);
			} else {
				$existing = $this->db->select(
					"SELECT TOP 1 id FROM Reminders 
					 WHERE entity_type = ? AND entity_id = ? 
					   AND title = ? AND remind_at = ? 
					   AND created_by = ? 
					   AND status = 'Pending'
					 ORDER BY id DESC",
					[
						$data['entity_type'],
						(int)$data['entity_id'],
						$data['title'],
						$data['remind_at'],
						(int)$data['created_by'],
					]
				);
			}
			if (!empty($existing) && isset($existing[0]['id'])) {
				return (int)$existing[0]['id'];
			}

			if ($hasRecipient) {
				$query = "INSERT INTO Reminders (entity_type, entity_id, title, notes, remind_at, created_by, recipient_user_id, status, created_at, notify_all)
				          VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', GETDATE(), ?)";
				return $this->db->insert($query, [
					$data['entity_type'],
					(int)$data['entity_id'],
					$data['title'],
					$data['notes'] ?? null,
					$data['remind_at'],
					(int)$data['created_by'],
					isset($data['recipient_user_id']) ? (int)$data['recipient_user_id'] : null,
					!empty($data['notify_all']) ? 1 : 0
				]);
			} else {
				$query = "INSERT INTO Reminders (entity_type, entity_id, title, notes, remind_at, created_by, status, created_at, notify_all)
				          VALUES (?, ?, ?, ?, ?, ?, 'Pending', GETDATE(), ?)";
				return $this->db->insert($query, [
					$data['entity_type'],
					(int)$data['entity_id'],
					$data['title'],
					$data['notes'] ?? null,
					$data['remind_at'],
					(int)$data['created_by'],
					!empty($data['notify_all']) ? 1 : 0
				]);
			}
		} catch (Exception $e) {
			error_log('Reminder add error: ' . $e->getMessage());
			return false;
		}
	}

	public function getByEntity(string $entityType, int $entityId): array {
		try {
			$query = "SELECT * FROM Reminders WHERE entity_type = ? AND entity_id = ? ORDER BY remind_at DESC, created_at DESC";
			return $this->db->select($query, [$entityType, $entityId]) ?: [];
		} catch (Exception $e) {
			error_log('Reminder getByEntity error: ' . $e->getMessage());
			return [];
		}
	}

	/**
	 * Get the latest reminder for an entity (any status), ordered by remind_at desc
	 */
	public function getLatestReminderForEntity(string $entityType, int $entityId) {
		try {
			$query = "SELECT TOP 1 * FROM Reminders WHERE entity_type = ? AND entity_id = ? ORDER BY remind_at DESC";
			$result = $this->db->select($query, [$entityType, $entityId]);
			return $result ? $result[0] : null;
		} catch (Exception $e) {
			error_log('Reminder getLatestReminderForEntity error: ' . $e->getMessage());
			return null;
		}
	}

	public function getById(int $id) {
		try {
			$query = "SELECT * FROM Reminders WHERE id = ?";
			$result = $this->db->select($query, [$id]);
			return $result ? $result[0] : false;
		} catch (Exception $e) {
			error_log('Reminder getById error: ' . $e->getMessage());
			return false;
		}
	}

	public function getUpcomingByUser(int $userId, int $limit = 10): array {
		try {
			$top = $limit > 0 ? "TOP {$limit} " : '';
			// Detect recipient column
			$hasRecipient = false;
			try {
				$col = $this->db->select("SELECT COL_LENGTH('dbo.Reminders','recipient_user_id') AS len");
				$hasRecipient = !empty($col) && isset($col[0]['len']) && $col[0]['len'] !== null;
			} catch (Exception $e) {
				$hasRecipient = false;
			}
			if ($hasRecipient) {
				$query = "SELECT {$top} *
				          FROM Reminders
				          WHERE status = 'Pending'
				            AND remind_at >= DATEADD(day, -1, GETDATE())
				            AND (created_by = ? OR recipient_user_id = ? OR notify_all = 1)
				          ORDER BY remind_at ASC";
				return $this->db->select($query, [$userId, $userId]) ?: [];
			} else {
				$query = "SELECT {$top} *
				          FROM Reminders
				          WHERE status = 'Pending'
				            AND remind_at >= DATEADD(day, -1, GETDATE())
				            AND (created_by = ? OR notify_all = 1)
				          ORDER BY remind_at ASC";
				return $this->db->select($query, [$userId]) ?: [];
			}
		} catch (Exception $e) {
			error_log('Reminder getUpcomingByUser error: ' . $e->getMessage());
			return [];
		}
	}

	public function markCompleted(int $id): bool {
		try {
			$query = "UPDATE Reminders SET status = 'Completed', completed_at = GETDATE(), updated_at = GETDATE() WHERE id = ?";
			$this->db->update($query, [$id]);
			return true;
		} catch (Exception $e) {
			error_log('Reminder markCompleted error: ' . $e->getMessage());
			return false;
		}
	}

	public function setReminderQueueId(int $id, int $queueId): bool {
		try {
			$query = "UPDATE Reminders SET reminder_queue_id = ?, updated_at = GETDATE() WHERE id = ?";
			$this->db->update($query, [$queueId, $id]);
			return true;
		} catch (Exception $e) {
			error_log('Reminder setReminderQueueId error: ' . $e->getMessage());
			return false;
		}
	}

	public function markReminderSent(int $id): bool {
		try {
			$query = "UPDATE Reminders SET reminder_sent_at = GETDATE(), updated_at = GETDATE() WHERE id = ?";
			$this->db->update($query, [$id]);
			return true;
		} catch (Exception $e) {
			error_log('Reminder markReminderSent error: ' . $e->getMessage());
			return false;
		}
	}

	public function delete(int $id): bool {
		try {
			$this->db->remove("DELETE FROM Reminders WHERE id = ?", [$id]);
			return true;
		} catch (Exception $e) {
			error_log('Reminder delete error: ' . $e->getMessage());
			return false;
		}
	}
}


