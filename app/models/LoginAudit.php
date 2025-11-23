<?php

class LoginAudit {
	private $db;
	
	public function __construct() {
		$this->db = new EasySQL(DB1);
		$this->ensureTable();
	}
	
	private function ensureTable(): void {
		try {
			$sql = "
			IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[UserLoginAudit]') AND type in (N'U'))
			BEGIN
				CREATE TABLE [dbo].[UserLoginAudit] (
					[id] INT IDENTITY(1,1) PRIMARY KEY,
					[user_id] INT NULL,
					[username] NVARCHAR(255) NULL,
					[ip_address] NVARCHAR(45) NULL,
					[user_agent] NVARCHAR(512) NULL,
					[success] BIT NOT NULL CONSTRAINT DF_UserLoginAudit_success DEFAULT(1),
					[created_at] DATETIME NOT NULL CONSTRAINT DF_UserLoginAudit_created_at DEFAULT(GETDATE())
				);
				CREATE INDEX IX_UserLoginAudit_CreatedAt ON [dbo].[UserLoginAudit] ([created_at] DESC);
				CREATE INDEX IX_UserLoginAudit_UserId ON [dbo].[UserLoginAudit] ([user_id]);
			END
			";
			$this->db->query($sql);
		} catch (Exception $e) {
			error_log('LoginAudit ensureTable error: ' . $e->getMessage());
		}
	}
	
	public function add(array $data) {
		try {
			$query = "INSERT INTO [dbo].[UserLoginAudit] (user_id, username, ip_address, user_agent, success, created_at)
			          VALUES (?, ?, ?, ?, ?, GETDATE())";
			return $this->db->insert($query, [
				isset($data['user_id']) ? (int)$data['user_id'] : null,
				$data['username'] ?? null,
				$data['ip_address'] ?? null,
				$data['user_agent'] ?? null,
				!empty($data['success']) ? 1 : 0
			]);
		} catch (Exception $e) {
			error_log('LoginAudit add error: ' . $e->getMessage());
			return false;
		}
	}
	
	public function getRecent(int $limit = 200): array {
		try {
			$top = $limit > 0 ? "TOP {$limit} " : '';
			$sql = "
				SELECT {$top}
					a.id, a.user_id, a.username, a.ip_address, a.user_agent, a.success, a.created_at,
					u.full_name, u.email, u.username AS db_username
				FROM [dbo].[UserLoginAudit] a
				LEFT JOIN [dbo].[Users] u ON u.id = a.user_id
				ORDER BY a.created_at DESC, a.id DESC";
			return $this->db->select($sql) ?: [];
		} catch (Exception $e) {
			error_log('LoginAudit getRecent error: ' . $e->getMessage());
			return [];
		}
	}
}


