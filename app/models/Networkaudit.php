<?php

class Networkaudit {
	private $db;

	public function __construct() {
		$this->db = new EasySQL(DB1);
		$this->ensureTable();
	}

	private function ensureTable(): void {
		try {
			$sql = "
			IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[NetworkAudits]') AND type in (N'U'))
			BEGIN
				CREATE TABLE [dbo].[NetworkAudits] (
					[id] INT IDENTITY(1,1) PRIMARY KEY,
					[client_id] INT NOT NULL,
					[site_location] NVARCHAR(255) NULL,
					[engineer_ids] NVARCHAR(MAX) NULL, -- JSON array of user IDs
					[engineer_names] NVARCHAR(MAX) NULL, -- cached names for readability
					[audit_date] DATE NOT NULL DEFAULT CAST(GETDATE() AS DATE),

					-- General Overview
					[gen_reliability_issues] BIT NOT NULL CONSTRAINT DF_NA_gen_reliability_issues DEFAULT(0),
					[gen_undocumented_systems] BIT NOT NULL CONSTRAINT DF_NA_gen_undocumented_systems DEFAULT(0),
					[gen_support_contracts] BIT NOT NULL CONSTRAINT DF_NA_gen_support_contracts DEFAULT(0),
					[gen_notes] NVARCHAR(MAX) NULL,
					[gen_additional_info] NVARCHAR(MAX) NULL,

					-- Network Topology & Connectivity
					[top_internet_provider] NVARCHAR(255) NULL,
					[top_connection_types] NVARCHAR(255) NULL, -- comma separated values
					[top_router_firewall] NVARCHAR(255) NULL,
					[top_switches] NVARCHAR(MAX) NULL,
					[top_vlans] NVARCHAR(MAX) NULL,
					[top_wifi_setup] NVARCHAR(MAX) NULL,
					[top_additional_info] NVARCHAR(MAX) NULL,

					-- Servers & Core Infrastructure
					[servers_physical] NVARCHAR(MAX) NULL, -- JSON array of rows
					[servers_virtual] NVARCHAR(MAX) NULL, -- JSON array of rows
					[servers_additional_info] NVARCHAR(MAX) NULL,

					-- Workstations & Endpoints
					[endpoints_workstations] NVARCHAR(MAX) NULL, -- JSON array of rows
					[endpoints_additional_info] NVARCHAR(MAX) NULL,

					-- Software & Licensing
					[soft_key_apps] NVARCHAR(MAX) NULL,
					[soft_licensing_type] NVARCHAR(255) NULL,
					[soft_antivirus_tools] NVARCHAR(255) NULL,
					[soft_update_mgmt] NVARCHAR(100) NULL,
					[soft_additional_info] NVARCHAR(MAX) NULL,

					-- Backup, DR & Data Protection
					[bkp_type] NVARCHAR(255) NULL,
					[bkp_frequency] NVARCHAR(255) NULL,
					[bkp_retention] NVARCHAR(255) NULL,
					[bkp_test_restores] NVARCHAR(255) NULL,
					[bkp_dr_docs] NVARCHAR(MAX) NULL,
					[bkp_additional_info] NVARCHAR(MAX) NULL,

					-- Security & Access Control
					[sec_firewall_rules] NVARCHAR(MAX) NULL,
					[sec_antivirus] NVARCHAR(255) NULL,
					[sec_mfa] BIT NOT NULL CONSTRAINT DF_NA_sec_mfa DEFAULT(0),
					[sec_password_policy] NVARCHAR(MAX) NULL,
					[sec_remote_access_tools] NVARCHAR(255) NULL,
					[sec_additional_info] NVARCHAR(MAX) NULL,

				-- Cloud Services & Integrations
				[cloud_tenant_name] NVARCHAR(255) NULL,
				[cloud_platforms] NVARCHAR(255) NULL, -- comma separated values
				[cloud_file_sharing_tools] NVARCHAR(255) NULL,
				[cloud_linked_systems] NVARCHAR(MAX) NULL,
				[cloud_additional_info] NVARCHAR(MAX) NULL,

				-- Website & Online Presence
				[web_has_website] NVARCHAR(10) NULL,
				[web_url] NVARCHAR(500) NULL,
				[web_hosting_location] NVARCHAR(50) NULL,
				[web_hosting_provider] NVARCHAR(255) NULL,
				[web_managed_by] NVARCHAR(50) NULL,
				[web_management_company] NVARCHAR(255) NULL,
				[web_cms] NVARCHAR(100) NULL,
				[web_ssl_certificate] NVARCHAR(50) NULL,
				[web_notes] NVARCHAR(MAX) NULL,
				[web_additional_info] NVARCHAR(MAX) NULL,

				-- Observations & Recommendations
				[observations] NVARCHAR(MAX) NULL,

					[created_by] INT NOT NULL,
					[created_at] DATETIME NOT NULL DEFAULT GETDATE(),
					[updated_at] DATETIME NULL
				);
			END;
			-- Add additional_info columns if they don't exist (for existing tables)
			IF COL_LENGTH('dbo.NetworkAudits', 'gen_additional_info') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [gen_additional_info] NVARCHAR(MAX) NULL;
			END;
			IF COL_LENGTH('dbo.NetworkAudits', 'top_additional_info') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [top_additional_info] NVARCHAR(MAX) NULL;
			END;
			IF COL_LENGTH('dbo.NetworkAudits', 'servers_additional_info') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [servers_additional_info] NVARCHAR(MAX) NULL;
			END;
			IF COL_LENGTH('dbo.NetworkAudits', 'endpoints_additional_info') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [endpoints_additional_info] NVARCHAR(MAX) NULL;
			END;
			IF COL_LENGTH('dbo.NetworkAudits', 'soft_additional_info') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [soft_additional_info] NVARCHAR(MAX) NULL;
			END;
			IF COL_LENGTH('dbo.NetworkAudits', 'bkp_additional_info') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [bkp_additional_info] NVARCHAR(MAX) NULL;
			END;
			IF COL_LENGTH('dbo.NetworkAudits', 'sec_additional_info') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [sec_additional_info] NVARCHAR(MAX) NULL;
			END;
			IF COL_LENGTH('dbo.NetworkAudits', 'cloud_additional_info') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [cloud_additional_info] NVARCHAR(MAX) NULL;
			END;
			-- Add website columns if they don't exist
			IF COL_LENGTH('dbo.NetworkAudits', 'web_has_website') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [web_has_website] NVARCHAR(10) NULL;
			END;
			IF COL_LENGTH('dbo.NetworkAudits', 'web_url') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [web_url] NVARCHAR(500) NULL;
			END;
			IF COL_LENGTH('dbo.NetworkAudits', 'web_hosting_location') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [web_hosting_location] NVARCHAR(50) NULL;
			END;
			IF COL_LENGTH('dbo.NetworkAudits', 'web_hosting_provider') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [web_hosting_provider] NVARCHAR(255) NULL;
			END;
			IF COL_LENGTH('dbo.NetworkAudits', 'web_managed_by') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [web_managed_by] NVARCHAR(50) NULL;
			END;
			IF COL_LENGTH('dbo.NetworkAudits', 'web_management_company') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [web_management_company] NVARCHAR(255) NULL;
			END;
			IF COL_LENGTH('dbo.NetworkAudits', 'web_cms') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [web_cms] NVARCHAR(100) NULL;
			END;
			IF COL_LENGTH('dbo.NetworkAudits', 'web_ssl_certificate') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [web_ssl_certificate] NVARCHAR(50) NULL;
			END;
			IF COL_LENGTH('dbo.NetworkAudits', 'web_notes') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [web_notes] NVARCHAR(MAX) NULL;
			END;
			IF COL_LENGTH('dbo.NetworkAudits', 'web_additional_info') IS NULL
			BEGIN
				ALTER TABLE [dbo].[NetworkAudits] ADD [web_additional_info] NVARCHAR(MAX) NULL;
			END";
			$this->db->query($sql);
		} catch (Exception $e) {
			error_log('NetworkAudits ensureTable error: ' . $e->getMessage());
		}
	}

	public function add(array $data) {
		try {
			$sql = "INSERT INTO [NetworkAudits] (
				client_id, site_location, engineer_ids, engineer_names, audit_date,
				gen_reliability_issues, gen_undocumented_systems, gen_support_contracts, gen_notes, gen_additional_info,
				top_internet_provider, top_connection_types, top_router_firewall, top_switches, top_vlans, top_wifi_setup, top_additional_info,
				servers_physical, servers_virtual, servers_additional_info,
				endpoints_workstations, endpoints_additional_info,
				soft_key_apps, soft_licensing_type, soft_antivirus_tools, soft_update_mgmt, soft_additional_info,
				bkp_type, bkp_frequency, bkp_retention, bkp_test_restores, bkp_dr_docs, bkp_additional_info,
				sec_firewall_rules, sec_antivirus, sec_mfa, sec_password_policy, sec_remote_access_tools, sec_additional_info,
				cloud_tenant_name, cloud_platforms, cloud_file_sharing_tools, cloud_linked_systems, cloud_additional_info,
				web_has_website, web_url, web_hosting_location, web_hosting_provider, web_managed_by, web_management_company, web_cms, web_ssl_certificate, web_notes, web_additional_info,
				observations, created_by
			) VALUES (
				?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
			)";

			return $this->db->insert($sql, [
				(int)$data['client_id'],
				$data['site_location'] ?? null,
				$data['engineer_ids_json'] ?? null,
				$data['engineer_names'] ?? null,
				$data['audit_date'] ?? date('Y-m-d'),
				!empty($data['gen_reliability_issues']) ? 1 : 0,
				!empty($data['gen_undocumented_systems']) ? 1 : 0,
				!empty($data['gen_support_contracts']) ? 1 : 0,
				$data['gen_notes'] ?? null,
				$data['gen_additional_info'] ?? null,
				$data['top_internet_provider'] ?? null,
				$data['top_connection_types'] ?? null,
				$data['top_router_firewall'] ?? null,
				$data['top_switches'] ?? null,
				$data['top_vlans'] ?? null,
				$data['top_wifi_setup'] ?? null,
				$data['top_additional_info'] ?? null,
				$data['servers_physical_json'] ?? null,
				$data['servers_virtual_json'] ?? null,
				$data['servers_additional_info'] ?? null,
				$data['endpoints_workstations_json'] ?? null,
				$data['endpoints_additional_info'] ?? null,
				$data['soft_key_apps'] ?? null,
				$data['soft_licensing_type'] ?? null,
				$data['soft_antivirus_tools'] ?? null,
				$data['soft_update_mgmt'] ?? null,
				$data['soft_additional_info'] ?? null,
				$data['bkp_type'] ?? null,
				$data['bkp_frequency'] ?? null,
				$data['bkp_retention'] ?? null,
				$data['bkp_test_restores'] ?? null,
				$data['bkp_dr_docs'] ?? null,
				$data['bkp_additional_info'] ?? null,
				$data['sec_firewall_rules'] ?? null,
				$data['sec_antivirus'] ?? null,
				!empty($data['sec_mfa']) ? 1 : 0,
				$data['sec_password_policy'] ?? null,
				$data['sec_remote_access_tools'] ?? null,
				$data['sec_additional_info'] ?? null,
				$data['cloud_tenant_name'] ?? null,
				$data['cloud_platforms'] ?? null,
				$data['cloud_file_sharing_tools'] ?? null,
				$data['cloud_linked_systems'] ?? null,
				$data['cloud_additional_info'] ?? null,
				$data['web_has_website'] ?? null,
				$data['web_url'] ?? null,
				$data['web_hosting_location'] ?? null,
				$data['web_hosting_provider'] ?? null,
				$data['web_managed_by'] ?? null,
				$data['web_management_company'] ?? null,
				$data['web_cms'] ?? null,
				$data['web_ssl_certificate'] ?? null,
				$data['web_notes'] ?? null,
				$data['web_additional_info'] ?? null,
				$data['observations'] ?? null,
				(int)($data['created_by'] ?? 0)
			]);
		} catch (Exception $e) {
			error_log('NetworkAudits add error: ' . $e->getMessage());
			return false;
		}
	}

	public function update(int $id, array $data): bool {
		try {
			$sql = "UPDATE [NetworkAudits] SET
				client_id = ?, site_location = ?, engineer_ids = ?, engineer_names = ?, audit_date = ?,
				gen_reliability_issues = ?, gen_undocumented_systems = ?, gen_support_contracts = ?, gen_notes = ?, gen_additional_info = ?,
				top_internet_provider = ?, top_connection_types = ?, top_router_firewall = ?, top_switches = ?, top_vlans = ?, top_wifi_setup = ?, top_additional_info = ?,
				servers_physical = ?, servers_virtual = ?, servers_additional_info = ?,
				endpoints_workstations = ?, endpoints_additional_info = ?,
				soft_key_apps = ?, soft_licensing_type = ?, soft_antivirus_tools = ?, soft_update_mgmt = ?, soft_additional_info = ?,
				bkp_type = ?, bkp_frequency = ?, bkp_retention = ?, bkp_test_restores = ?, bkp_dr_docs = ?, bkp_additional_info = ?,
				sec_firewall_rules = ?, sec_antivirus = ?, sec_mfa = ?, sec_password_policy = ?, sec_remote_access_tools = ?, sec_additional_info = ?,
				cloud_tenant_name = ?, cloud_platforms = ?, cloud_file_sharing_tools = ?, cloud_linked_systems = ?, cloud_additional_info = ?,
				web_has_website = ?, web_url = ?, web_hosting_location = ?, web_hosting_provider = ?, web_managed_by = ?, web_management_company = ?, web_cms = ?, web_ssl_certificate = ?, web_notes = ?, web_additional_info = ?,
				observations = ?, updated_at = GETDATE()
			WHERE id = ?";

			$this->db->update($sql, [
				(int)$data['client_id'],
				$data['site_location'] ?? null,
				$data['engineer_ids_json'] ?? null,
				$data['engineer_names'] ?? null,
				$data['audit_date'] ?? date('Y-m-d'),
				!empty($data['gen_reliability_issues']) ? 1 : 0,
				!empty($data['gen_undocumented_systems']) ? 1 : 0,
				!empty($data['gen_support_contracts']) ? 1 : 0,
				$data['gen_notes'] ?? null,
				$data['gen_additional_info'] ?? null,
				$data['top_internet_provider'] ?? null,
				$data['top_connection_types'] ?? null,
				$data['top_router_firewall'] ?? null,
				$data['top_switches'] ?? null,
				$data['top_vlans'] ?? null,
				$data['top_wifi_setup'] ?? null,
				$data['top_additional_info'] ?? null,
				$data['servers_physical_json'] ?? null,
				$data['servers_virtual_json'] ?? null,
				$data['servers_additional_info'] ?? null,
				$data['endpoints_workstations_json'] ?? null,
				$data['endpoints_additional_info'] ?? null,
				$data['soft_key_apps'] ?? null,
				$data['soft_licensing_type'] ?? null,
				$data['soft_antivirus_tools'] ?? null,
				$data['soft_update_mgmt'] ?? null,
				$data['soft_additional_info'] ?? null,
				$data['bkp_type'] ?? null,
				$data['bkp_frequency'] ?? null,
				$data['bkp_retention'] ?? null,
				$data['bkp_test_restores'] ?? null,
				$data['bkp_dr_docs'] ?? null,
				$data['bkp_additional_info'] ?? null,
				$data['sec_firewall_rules'] ?? null,
				$data['sec_antivirus'] ?? null,
				!empty($data['sec_mfa']) ? 1 : 0,
				$data['sec_password_policy'] ?? null,
				$data['sec_remote_access_tools'] ?? null,
				$data['sec_additional_info'] ?? null,
				$data['cloud_tenant_name'] ?? null,
				$data['cloud_platforms'] ?? null,
				$data['cloud_file_sharing_tools'] ?? null,
				$data['cloud_linked_systems'] ?? null,
				$data['cloud_additional_info'] ?? null,
				$data['web_has_website'] ?? null,
				$data['web_url'] ?? null,
				$data['web_hosting_location'] ?? null,
				$data['web_hosting_provider'] ?? null,
				$data['web_managed_by'] ?? null,
				$data['web_management_company'] ?? null,
				$data['web_cms'] ?? null,
				$data['web_ssl_certificate'] ?? null,
				$data['web_notes'] ?? null,
				$data['web_additional_info'] ?? null,
				$data['observations'] ?? null,
				$id
			]);
			return true;
		} catch (Exception $e) {
			error_log('NetworkAudits update error: ' . $e->getMessage());
			return false;
		}
	}

	public function getById(int $id): array|false {
		try {
			$rows = $this->db->select("SELECT * FROM [NetworkAudits] WHERE id = ?", [$id]);
			return $rows ? $rows[0] : false;
		} catch (Exception $e) {
			error_log('NetworkAudits getById error: ' . $e->getMessage());
			return false;
		}
	}

	public function getByClient(int $clientId): array {
		try {
			$rows = $this->db->select("SELECT * FROM [NetworkAudits] WHERE client_id = ? ORDER BY created_at DESC", [$clientId]);
			return $rows ?: [];
		} catch (Exception $e) {
			error_log('NetworkAudits getByClient error: ' . $e->getMessage());
			return [];
		}
	}
}


