<?php

class ClientMeeting {
	private $db;
	
	public function __construct() {
		$this->db = new EasySQL(DB1);
		$this->ensureTable();
	}
	
	private function ensureTable(): void {
		try {
			$sql = "
			IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[ClientMeetings]') AND type in (N'U'))
			BEGIN
				CREATE TABLE [dbo].[ClientMeetings] (
					[id] INT IDENTITY(1,1) PRIMARY KEY,
					[client_id] INT NOT NULL,
					[site_id] INT NULL,
					[title] NVARCHAR(255) NOT NULL,
					[person_name] NVARCHAR(255) NULL,
					[person_going] NVARCHAR(255) NULL,
					[person_visiting] NVARCHAR(255) NULL,
					[additional_going] NVARCHAR(MAX) NULL,
					[additional_meeting] NVARCHAR(MAX) NULL,
					[info] NVARCHAR(MAX) NULL,
					[meeting_at] DATETIME NOT NULL,
					[created_by] INT NOT NULL,
					[created_at] DATETIME NOT NULL CONSTRAINT DF_ClientMeetings_created_at DEFAULT(GETDATE()),
					[updated_at] DATETIME NULL
				);
				CREATE INDEX IX_ClientMeetings_Client ON [dbo].[ClientMeetings] ([client_id], [meeting_at]);
			END
			";
			$this->db->query($sql);
			
			// Safe schema evolution: add new columns if missing
			$this->db->query("
				IF COL_LENGTH('dbo.ClientMeetings', 'person_going') IS NULL
					ALTER TABLE [dbo].[ClientMeetings] ADD [person_going] NVARCHAR(255) NULL;
				IF COL_LENGTH('dbo.ClientMeetings', 'person_visiting') IS NULL
					ALTER TABLE [dbo].[ClientMeetings] ADD [person_visiting] NVARCHAR(255) NULL;
				IF COL_LENGTH('dbo.ClientMeetings', 'additional_going') IS NULL
					ALTER TABLE [dbo].[ClientMeetings] ADD [additional_going] NVARCHAR(MAX) NULL;
				IF COL_LENGTH('dbo.ClientMeetings', 'additional_meeting') IS NULL
					ALTER TABLE [dbo].[ClientMeetings] ADD [additional_meeting] NVARCHAR(MAX) NULL;
			");
		} catch (Exception $e) {
			error_log('ClientMeeting ensureTable error: ' . $e->getMessage());
		}
	}
	
	public function add(array $data) {
		try {
			$query = "INSERT INTO ClientMeetings (client_id, site_id, title, person_going, person_visiting, additional_going, additional_meeting, info, meeting_at, created_by, created_at)
			          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE())";
			return $this->db->insert($query, [
				(int)$data['client_id'],
				isset($data['site_id']) && $data['site_id'] !== '' ? (int)$data['site_id'] : null,
				$data['title'],
				$data['person_going'] ?? null,
				$data['person_visiting'] ?? null,
				$data['additional_going'] ?? null,
				$data['additional_meeting'] ?? null,
				$data['info'] ?? null,
				$data['meeting_at'],
				(int)$data['created_by']
			]);
		} catch (Exception $e) {
			error_log('ClientMeeting add error: ' . $e->getMessage());
			return false;
		}
	}
	
	public function listByClient(int $clientId): array {
		try {
			$sql = "SELECT m.*, s.name AS site_name, s.location AS site_location, u.full_name AS creator_name, u.username AS creator_username
			        FROM ClientMeetings m
			        LEFT JOIN Sites s ON s.id = m.site_id
			        LEFT JOIN Users u ON u.id = m.created_by
			        WHERE m.client_id = ?
			        ORDER BY m.meeting_at DESC, m.id DESC";
			return $this->db->select($sql, [$clientId]) ?: [];
		} catch (Exception $e) {
			error_log('ClientMeeting listByClient error: ' . $e->getMessage());
			return [];
		}
	}
	
	public function countToday(): int {
		try {
			$sql = "SELECT COUNT(*) AS cnt FROM ClientMeetings WHERE CAST(meeting_at AS DATE) = CAST(GETDATE() AS DATE)";
			$result = $this->db->select($sql) ?: [];
			if (!empty($result) && isset($result[0]['cnt'])) {
				return (int)$result[0]['cnt'];
			}
			return 0;
		} catch (Exception $e) {
			error_log('ClientMeeting countToday error: ' . $e->getMessage());
			return 0;
		}
	}
	
	public function delete(int $id): bool {
		try {
			$this->db->remove("DELETE FROM ClientMeetings WHERE id = ?", [$id]);
			return true;
		} catch (Exception $e) {
			error_log('ClientMeeting delete error: ' . $e->getMessage());
			return false;
		}
	}
}


