<?php
$title = 'Edit Network Infrastructure Discovery Form';
?>

<div class="page-header">
	<div>
		<h1 class="page-title">Edit Network Infrastructure Discovery Form</h1>
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= URLROOT ?>/clients">Clients</a></li>
				<li class="breadcrumb-item"><a href="<?= URLROOT ?>/clients/viewClient/<?= (int)$audit['client_id'] ?>">Client</a></li>
				<li class="breadcrumb-item"><a href="<?= URLROOT ?>/networkaudits/show/<?= (int)$audit['id'] ?>">Audit</a></li>
				<li class="breadcrumb-item active" aria-current="page">Edit</li>
			</ol>
		</nav>
	</div>
</div>

<form action="<?= URLROOT ?>/networkaudits/update/<?= (int)$audit['id'] ?>" method="post">
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card mb-4">
				<div class="card-header">
					<h5 class="mb-0">1. Client & Site Details</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6 mb-3">
							<label for="client_id" class="form-label">Client name</label>
							<select class="form-select" id="client_id" name="client_id" required>
								<option value="">Select client</option>
								<?php foreach ($clients as $c): ?>
									<option value="<?= (int)$c['id'] ?>" <?= (!empty($audit['client_id']) && (int)$audit['client_id'] === (int)$c['id']) ? 'selected' : '' ?>>
										<?= htmlspecialchars($c['name']) ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-6 mb-3">
							<label for="site_location" class="form-label">Site location</label>
							<input type="text" class="form-control" id="site_location" name="site_location" placeholder="e.g., London HQ" value="<?= htmlspecialchars($audit['site_location'] ?? '') ?>">
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label for="engineer_ids" class="form-label">Engineer(s)</label>
							<select class="form-select" id="engineer_ids" name="engineer_ids[]" multiple size="6">
								<?php foreach ($users as $u): ?>
									<option value="<?= (int)$u['id'] ?>" <?= (!empty($audit['engineer_ids_array']) && in_array((int)$u['id'], $audit['engineer_ids_array'])) ? 'selected' : '' ?>>
										<?= htmlspecialchars($u['full_name'] ?? $u['name'] ?? $u['username'] ?? 'User') ?>
									</option>
								<?php endforeach; ?>
							</select>
							<div class="form-text">Hold Ctrl/Cmd to select multiple.</div>
						</div>
						<div class="col-md-3 mb-3">
							<label for="audit_date" class="form-label">Date</label>
							<input type="date" class="form-control" id="audit_date" name="audit_date" value="<?= htmlspecialchars($audit['audit_date'] ?? date('Y-m-d')) ?>">
						</div>
					</div>
				</div>
			</div>

			<div class="card mb-4">
				<div class="card-header">
					<h5 class="mb-0">2. General Overview</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-4 mb-3">
							<div class="form-check">
								<input class="form-check-input" type="checkbox" id="gen_reliability_issues" name="gen_reliability_issues" value="1">
								<label class="form-check-label" for="gen_reliability_issues">Reliability issues</label>
							</div>
						</div>
						<div class="col-md-4 mb-3">
							<div class="form-check">
								<input class="form-check-input" type="checkbox" id="gen_undocumented_systems" name="gen_undocumented_systems" value="1">
								<label class="form-check-label" for="gen_undocumented_systems">Undocumented systems</label>
							</div>
						</div>
						<div class="col-md-4 mb-3">
							<div class="form-check">
								<input class="form-check-input" type="checkbox" id="gen_support_contracts" name="gen_support_contracts" value="1">
								<label class="form-check-label" for="gen_support_contracts">Support contracts in place</label>
							</div>
						</div>
					</div>
				<div class="mb-3">
					<label for="gen_notes" class="form-label">Notes</label>
					<textarea class="form-control" id="gen_notes" name="gen_notes" rows="3" placeholder="General observations, problem areas, context..."></textarea>
				</div>
				<div class="mb-3">
					<button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAdditionalInfo('gen')">
						<i class="bi bi-plus-circle"></i> Additional Information
					</button>
					<textarea class="form-control mt-2 additional-info-field" id="gen_additional_info" name="gen_additional_info" rows="4" placeholder="Additional context, recommendations, or detailed notes..." style="display:none;"></textarea>
				</div>
			</div>
		</div>

			<div class="card mb-4">
				<div class="card-header">
					<h5 class="mb-0">3. Network Topology & Connectivity</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6 mb-3">
							<label for="top_internet_provider" class="form-label">Internet provider</label>
							<input type="text" class="form-control" id="top_internet_provider" name="top_internet_provider">
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label d-block">Connection type</label>
							<div class="d-flex flex-wrap gap-3">
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="ct_fibre" name="top_connection_types[]" value="Fibre">
									<label class="form-check-label" for="ct_fibre">Fibre</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="ct_fttc" name="top_connection_types[]" value="FTTC">
									<label class="form-check-label" for="ct_fttc">FTTC</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="ct_fttp" name="top_connection_types[]" value="FTTP">
									<label class="form-check-label" for="ct_fttp">FTTP</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="ct_leased" name="top_connection_types[]" value="Leased Line">
									<label class="form-check-label" for="ct_leased">Leased Line</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="ct_4g5g" name="top_connection_types[]" value="4G/5G">
									<label class="form-check-label" for="ct_4g5g">4G/5G</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="ct_other" name="top_connection_types[]" value="Other">
									<label class="form-check-label" for="ct_other">Other</label>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label for="top_router_firewall" class="form-label">Router / Firewall</label>
							<input type="text" class="form-control" id="top_router_firewall" name="top_router_firewall" placeholder="e.g., FortiGate 60F">
						</div>
						<div class="col-md-6 mb-3">
							<label for="top_switches" class="form-label">Switches</label>
							<input type="text" class="form-control" id="top_switches" name="top_switches" placeholder="e.g., Aruba 2930, Cisco SG350">
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label for="top_vlans" class="form-label">VLANs</label>
							<textarea class="form-control" id="top_vlans" name="top_vlans" rows="3" placeholder="List key VLANs and purposes"></textarea>
						</div>
					<div class="col-md-6 mb-3">
						<label for="top_wifi_setup" class="form-label">Wi‑Fi setup</label>
						<textarea class="form-control" id="top_wifi_setup" name="top_wifi_setup" rows="3" placeholder="Controllers, SSIDs, authentication, guest, coverage, roaming"></textarea>
					</div>
				</div>
				<div class="mb-3">
					<button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAdditionalInfo('top')">
						<i class="bi bi-plus-circle"></i> Additional Information
					</button>
					<textarea class="form-control mt-2 additional-info-field" id="top_additional_info" name="top_additional_info" rows="4" placeholder="Additional network topology details..." style="display:none;"></textarea>
				</div>
			</div>
		</div>

		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">4. Servers & Core Infrastructure</h5>
				</div>
				<div class="card-body">
					<h6 class="mb-3">Physical Servers</h6>
					<div class="table-responsive mb-3">
						<table class="table table-bordered align-middle" id="physicalServersTable">
							<thead>
								<tr>
									<th style="min-width:140px;">Name</th>
									<th>Role</th>
									<th>Location</th>
									<th style="width:110px;">Quantity</th>
									<th>Notes</th>
									<th style="width:60px;"></th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
					<button type="button" class="btn btn-sm btn-outline-primary" id="addPhysicalServer">Add row</button>
					<hr class="my-4">
					<h6 class="mb-3">Virtual Servers</h6>
					<div class="table-responsive mb-3">
						<table class="table table-bordered align-middle" id="virtualServersTable">
							<thead>
								<tr>
									<th style="min-width:140px;">VM Name</th>
									<th>Role</th>
									<th>OS</th>
									<th>Host/Platform</th>
									<th>IP Address</th>
									<th>Dependencies</th>
									<th>Notes</th>
									<th style="width:60px;"></th>
								</tr>
							</thead>
							<tbody></tbody>
				</table>
			</div>
			<button type="button" class="btn btn-sm btn-outline-primary" id="addVirtualServer">Add row</button>
			<div class="mt-3">
				<button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAdditionalInfo('servers')">
					<i class="bi bi-plus-circle"></i> Additional Information
				</button>
				<textarea class="form-control mt-2 additional-info-field" id="servers_additional_info" name="servers_additional_info" rows="4" placeholder="Additional server infrastructure details..." style="display:none;"></textarea>
			</div>
		</div>
	</div>

	<div class="card mb-4">
		<div class="card-header">
			<h5 class="mb-0">5. Workstations & Endpoints</h5>
				</div>
				<div class="card-body">
					<div class="table-responsive mb-3">
					<table class="table table-bordered align-middle" id="workstationsTable" style="font-size: 0.9rem;">
						<thead>
							<tr>
								<th style="min-width:140px;">Machine Name</th>
								<th style="min-width:120px;">Manufacturer</th>
								<th style="min-width:120px;">Model Number</th>
								<th style="min-width:100px;">CPU</th>
								<th style="min-width:80px;">RAM</th>
								<th style="min-width:100px;">Graphics Card</th>
								<th style="min-width:120px;">Location</th>
								<th style="min-width:120px;">Current OS</th>
								<th style="min-width:110px;">Needs Replacing?</th>
								<th style="min-width:110px;">OS Status</th>
								<th style="min-width:150px;">Notes</th>
								<th style="width:50px;"></th>
							</tr>
						</thead>
						<tbody></tbody>
				</table>
				</div>
				<button type="button" class="btn btn-sm btn-outline-primary" id="addWorkstation">Add row</button>
				<div class="mt-3">
					<button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAdditionalInfo('endpoints')">
						<i class="bi bi-plus-circle"></i> Additional Information
					</button>
					<textarea class="form-control mt-2 additional-info-field" id="endpoints_additional_info" name="endpoints_additional_info" rows="4" placeholder="Additional workstation/endpoint details..." style="display:none;"></textarea>
				</div>
			</div>
		</div>

		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">6. Software & Licensing</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6 mb-3">
							<label for="soft_key_apps" class="form-label">Key applications</label>
							<textarea class="form-control" id="soft_key_apps" name="soft_key_apps" rows="3" placeholder="Line-of-business apps, accounting, CRM, bespoke..."></textarea>
						</div>
						<div class="col-md-3 mb-3">
							<label for="soft_licensing_type" class="form-label">Licensing type</label>
							<input type="text" class="form-control" id="soft_licensing_type" name="soft_licensing_type" placeholder="Per device, per user, subscription...">
						</div>
						<div class="col-md-3 mb-3">
							<label for="soft_antivirus_tools" class="form-label">Antivirus/EDR</label>
							<input type="text" class="form-control" id="soft_antivirus_tools" name="soft_antivirus_tools" placeholder="e.g., Defender for Business">
						</div>
					</div>
					<div class="row">
						<div class="col-md-4 mb-3">
							<label for="soft_update_mgmt" class="form-label">Update management</label>
							<select class="form-select" id="soft_update_mgmt" name="soft_update_mgmt">
								<option value="">Select</option>
								<option value="Intune">Intune</option>
								<option value="WSUS">WSUS</option>
								<option value="SCCM">SCCM/ConfigMgr</option>
								<option value="RMM">RMM</option>
								<option value="Manual">Manual</option>
								<option value="Other">Other</option>
						</select>
					</div>
				</div>
				<div class="mb-3">
					<button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAdditionalInfo('soft')">
						<i class="bi bi-plus-circle"></i> Additional Information
					</button>
					<textarea class="form-control mt-2 additional-info-field" id="soft_additional_info" name="soft_additional_info" rows="4" placeholder="Additional software/licensing details..." style="display:none;"></textarea>
				</div>
			</div>
		</div>

		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">7. Backup, DR & Data Protection</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-3 mb-3">
							<label for="bkp_type" class="form-label">Backup type</label>
							<input type="text" class="form-control" id="bkp_type" name="bkp_type" placeholder="Image, file-level, 365, on-prem, cloud...">
						</div>
						<div class="col-md-3 mb-3">
							<label for="bkp_frequency" class="form-label">Frequency</label>
							<input type="text" class="form-control" id="bkp_frequency" name="bkp_frequency" placeholder="Hourly, daily, weekly">
						</div>
						<div class="col-md-3 mb-3">
							<label for="bkp_retention" class="form-label">Retention</label>
							<input type="text" class="form-control" id="bkp_retention" name="bkp_retention" placeholder="e.g., 30 days, 1 year">
						</div>
						<div class="col-md-3 mb-3">
							<label for="bkp_test_restores" class="form-label">Test restores</label>
							<input type="text" class="form-control" id="bkp_test_restores" name="bkp_test_restores" placeholder="e.g., last tested date or frequency">
						</div>
					</div>
				<div class="mb-3">
					<label for="bkp_dr_docs" class="form-label">DR documentation</label>
					<textarea class="form-control" id="bkp_dr_docs" name="bkp_dr_docs" rows="3" placeholder="Where is DR plan, procedures, contacts stored?"></textarea>
				</div>
				<div class="mb-3">
					<button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAdditionalInfo('bkp')">
						<i class="bi bi-plus-circle"></i> Additional Information
					</button>
					<textarea class="form-control mt-2 additional-info-field" id="bkp_additional_info" name="bkp_additional_info" rows="4" placeholder="Additional backup/DR details..." style="display:none;"></textarea>
				</div>
			</div>
		</div>

		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">8. Security & Access Control</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6 mb-3">
							<label for="sec_firewall_rules" class="form-label">Firewall rules / notes</label>
							<textarea class="form-control" id="sec_firewall_rules" name="sec_firewall_rules" rows="3"></textarea>
						</div>
						<div class="col-md-6 mb-3">
							<label for="sec_password_policy" class="form-label">Password policy</label>
							<textarea class="form-control" id="sec_password_policy" name="sec_password_policy" rows="3"></textarea>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4 mb-3">
							<label for="sec_antivirus" class="form-label">Antivirus</label>
							<input type="text" class="form-control" id="sec_antivirus" name="sec_antivirus">
						</div>
						<div class="col-md-4 mb-3">
							<div class="form-check mt-4">
								<input class="form-check-input" type="checkbox" id="sec_mfa" name="sec_mfa" value="1">
								<label class="form-check-label" for="sec_mfa">MFA enabled</label>
							</div>
						</div>
					<div class="col-md-4 mb-3">
						<label for="sec_remote_access_tools" class="form-label">Remote access tools</label>
						<input type="text" class="form-control" id="sec_remote_access_tools" name="sec_remote_access_tools" placeholder="e.g., AnyDesk, RDP Gateway">
					</div>
				</div>
				<div class="mb-3">
					<button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAdditionalInfo('sec')">
						<i class="bi bi-plus-circle"></i> Additional Information
					</button>
					<textarea class="form-control mt-2 additional-info-field" id="sec_additional_info" name="sec_additional_info" rows="4" placeholder="Additional security details..." style="display:none;"></textarea>
				</div>
			</div>
		</div>

		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">9. Cloud Services & Integrations</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-4 mb-3">
							<label for="cloud_tenant_name" class="form-label">Tenant name</label>
							<input type="text" class="form-control" id="cloud_tenant_name" name="cloud_tenant_name" placeholder="e.g., contoso.onmicrosoft.com">
						</div>
						<div class="col-md-8 mb-3">
							<label class="form-label d-block">Cloud platforms</label>
							<div class="d-flex flex-wrap gap-3">
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="cp_m365" name="cloud_platforms[]" value="Microsoft 365">
									<label class="form-check-label" for="cp_m365">Microsoft 365</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="cp_gws" name="cloud_platforms[]" value="Google Workspace">
									<label class="form-check-label" for="cp_gws">Google Workspace</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="cp_aws" name="cloud_platforms[]" value="AWS">
									<label class="form-check-label" for="cp_aws">AWS</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="cp_azure" name="cloud_platforms[]" value="Azure">
									<label class="form-check-label" for="cp_azure">Azure</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="cp_other" name="cloud_platforms[]" value="Other">
									<label class="form-check-label" for="cp_other">Other</label>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label for="cloud_file_sharing_tools" class="form-label">File sharing tools</label>
							<input type="text" class="form-control" id="cloud_file_sharing_tools" name="cloud_file_sharing_tools" placeholder="SharePoint, OneDrive, Dropbox, etc.">
						</div>
						<div class="col-md-6 mb-3">
						<label for="cloud_linked_systems" class="form-label">Linked systems / integrations</label>
						<textarea class="form-control" id="cloud_linked_systems" name="cloud_linked_systems" rows="3"></textarea>
					</div>
				</div>
				<div class="mb-3">
					<button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAdditionalInfo('cloud')">
						<i class="bi bi-plus-circle"></i> Additional Information
					</button>
					<textarea class="form-control mt-2 additional-info-field" id="cloud_additional_info" name="cloud_additional_info" rows="4" placeholder="Additional cloud services details..." style="display:none;"></textarea>
				</div>
			</div>
		</div>

		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">10. Website & Online Presence</h5>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-6 mb-3">
						<label class="form-label">Does the client have a website?</label>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="web_has_website" id="web_has_website_yes" value="Yes">
							<label class="form-check-label" for="web_has_website_yes">Yes</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="web_has_website" id="web_has_website_no" value="No">
							<label class="form-check-label" for="web_has_website_no">No</label>
						</div>
					</div>
					<div class="col-md-6 mb-3">
						<label for="web_url" class="form-label">Website URL</label>
						<input type="text" class="form-control" id="web_url" name="web_url" placeholder="https://example.com">
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 mb-3">
						<label class="form-label">Hosting Location</label>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="web_hosting_location" id="web_hosting_local" value="Locally Hosted">
							<label class="form-check-label" for="web_hosting_local">Locally Hosted (On-Premise)</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="web_hosting_location" id="web_hosting_cloud" value="Cloud Hosted">
							<label class="form-check-label" for="web_hosting_cloud">Cloud Hosted</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="web_hosting_location" id="web_hosting_unknown" value="Unknown">
							<label class="form-check-label" for="web_hosting_unknown">Unknown</label>
						</div>
					</div>
					<div class="col-md-6 mb-3">
						<label for="web_hosting_provider" class="form-label">Hosting Provider</label>
						<input type="text" class="form-control" id="web_hosting_provider" name="web_hosting_provider" placeholder="e.g., AWS, Azure, GoDaddy, Self-hosted">
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 mb-3">
						<label class="form-label">Website Managed By</label>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="web_managed_by" id="web_managed_inhouse" value="In-House">
							<label class="form-check-label" for="web_managed_inhouse">In-House</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="web_managed_by" id="web_managed_external" value="External Company">
							<label class="form-check-label" for="web_managed_external">External Company</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="web_managed_by" id="web_managed_both" value="Both">
							<label class="form-check-label" for="web_managed_both">Both (Shared)</label>
						</div>
					</div>
					<div class="col-md-6 mb-3">
						<label for="web_management_company" class="form-label">Management Company Name</label>
						<input type="text" class="form-control" id="web_management_company" name="web_management_company" placeholder="External company name (if applicable)">
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 mb-3">
						<label for="web_cms" class="form-label">CMS / Platform</label>
						<input type="text" class="form-control" id="web_cms" name="web_cms" placeholder="e.g., WordPress, Drupal, Custom, Wix">
					</div>
					<div class="col-md-6 mb-3">
						<label for="web_ssl_certificate" class="form-label">SSL Certificate Status</label>
						<select class="form-select" id="web_ssl_certificate" name="web_ssl_certificate">
							<option value="">-</option>
							<option value="Valid">Valid</option>
							<option value="Expired">Expired</option>
							<option value="None">None</option>
							<option value="Unknown">Unknown</option>
						</select>
					</div>
				</div>
				<div class="mb-3">
					<label for="web_notes" class="form-label">Additional Website Notes</label>
					<textarea class="form-control" id="web_notes" name="web_notes" rows="3" placeholder="Domain registrar, renewal dates, access credentials location, etc."></textarea>
				</div>
				<div class="mb-3">
					<button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAdditionalInfo('web')">
						<i class="bi bi-plus-circle"></i> Additional Information
					</button>
					<textarea class="form-control mt-2 additional-info-field" id="web_additional_info" name="web_additional_info" rows="4" placeholder="Additional website/online presence details..." style="display:none;"></textarea>
				</div>
			</div>
		</div>

		<div class="card mb-4">
			<div class="card-header">
				<h5 class="mb-0">11. Observations & Recommendations</h5>
				</div>
				<div class="card-body">
					<div class="mb-3">
						<textarea class="form-control" id="observations" name="observations" rows="5" placeholder="Key risks, improvements, recommended actions..."></textarea>
					</div>
				</div>
			</div>

			<div class="card mb-4">
				<div class="card-body d-flex justify-content-between">
					<div>
						<button type="submit" class="btn btn-primary">Save Audit</button>
						<?php if (!empty($client_id)): ?>
							<a href="<?= URLROOT ?>/clients/viewClient/<?= (int)$client_id ?>" class="btn btn-light">Cancel</a>
						<?php else: ?>
							<a href="<?= URLROOT ?>/clients" class="btn btn-light">Cancel</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>

<script>
// Define table row functions globally so they can be used in multiple scripts
function addPhysicalRow(values = {}) {
		const tbody = document.querySelector('#physicalServersTable tbody');
		const idx = tbody.children.length;
		const tr = document.createElement('tr');
		tr.innerHTML = `
			<td><input type="text" class="form-control" name="servers_physical[${idx}][name]" value="${values.name || ''}"></td>
			<td><input type="text" class="form-control" name="servers_physical[${idx}][role]" value="${values.role || ''}"></td>
			<td><input type="text" class="form-control" name="servers_physical[${idx}][location]" value="${values.location || ''}"></td>
			<td><input type="number" min="1" class="form-control" name="servers_physical[${idx}][quantity]" value="${values.quantity || ''}"></td>
			<td><input type="text" class="form-control" name="servers_physical[${idx}][notes]" value="${values.notes || ''}"></td>
			<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>
		`;
		tbody.appendChild(tr);
	}
function addVirtualRow(values = {}) {
		const tbody = document.querySelector('#virtualServersTable tbody');
		const idx = tbody.children.length;
		const tr = document.createElement('tr');
		tr.innerHTML = `
			<td><input type="text" class="form-control" name="servers_virtual[${idx}][vm_name]" value="${values.vm_name || ''}"></td>
			<td><input type="text" class="form-control" name="servers_virtual[${idx}][role]" value="${values.role || ''}"></td>
			<td><input type="text" class="form-control" name="servers_virtual[${idx}][os]" value="${values.os || ''}"></td>
			<td><input type="text" class="form-control" name="servers_virtual[${idx}][host]" value="${values.host || ''}"></td>
			<td><input type="text" class="form-control" name="servers_virtual[${idx}][ip]" value="${values.ip || ''}"></td>
			<td><input type="text" class="form-control" name="servers_virtual[${idx}][dependencies]" value="${values.dependencies || ''}"></td>
			<td><input type="text" class="form-control" name="servers_virtual[${idx}][notes]" value="${values.notes || ''}"></td>
			<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>
		`;
		tbody.appendChild(tr);
	}
function addWorkstationRow(values = {}) {
		const tbody = document.querySelector('#workstationsTable tbody');
		const idx = tbody.children.length;
		const tr = document.createElement('tr');
		tr.innerHTML = `
			<td><input type="text" class="form-control form-control-sm" name="endpoints_workstations[${idx}][machine_name]" value="${values.machine_name || ''}" placeholder="e.g., WS001"></td>
			<td><input type="text" class="form-control form-control-sm" name="endpoints_workstations[${idx}][manufacturer]" value="${values.manufacturer || ''}" placeholder="e.g., Dell, HP"></td>
			<td><input type="text" class="form-control form-control-sm" name="endpoints_workstations[${idx}][model_number]" value="${values.model_number || ''}" placeholder="e.g., OptiPlex 7090"></td>
			<td><input type="text" class="form-control form-control-sm" name="endpoints_workstations[${idx}][cpu]" value="${values.cpu || ''}" placeholder="e.g., i5-11500"></td>
			<td><input type="text" class="form-control form-control-sm" name="endpoints_workstations[${idx}][ram]" value="${values.ram || ''}" placeholder="e.g., 16GB"></td>
			<td><input type="text" class="form-control form-control-sm" name="endpoints_workstations[${idx}][graphics_card]" value="${values.graphics_card || ''}" placeholder="e.g., Intel UHD"></td>
			<td><input type="text" class="form-control form-control-sm" name="endpoints_workstations[${idx}][location]" value="${values.location || ''}" placeholder="e.g., Office 201"></td>
			<td><input type="text" class="form-control form-control-sm" name="endpoints_workstations[${idx}][current_os]" value="${values.current_os || ''}" placeholder="e.g., Windows 11"></td>
			<td>
				<select class="form-select form-select-sm" name="endpoints_workstations[${idx}][needs_replacing]">
					<option value="">-</option>
					<option value="Yes" ${values.needs_replacing === 'Yes' ? 'selected' : ''}>Yes</option>
					<option value="No" ${values.needs_replacing === 'No' ? 'selected' : ''}>No</option>
					<option value="Soon" ${values.needs_replacing === 'Soon' ? 'selected' : ''}>Soon</option>
				</select>
			</td>
			<td>
				<select class="form-select form-select-sm" name="endpoints_workstations[${idx}][os_status]">
					<option value="">-</option>
					<option value="Current" ${values.os_status === 'Current' ? 'selected' : ''}>Current</option>
					<option value="Needs Upgrade" ${values.os_status === 'Needs Upgrade' ? 'selected' : ''}>Needs Upgrade</option>
					<option value="End of Life" ${values.os_status === 'End of Life' ? 'selected' : ''}>End of Life</option>
				</select>
			</td>
			<td><input type="text" class="form-control form-control-sm" name="endpoints_workstations[${idx}][notes]" value="${values.notes || ''}" placeholder="Additional notes"></td>
			<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>
		`;
		tbody.appendChild(tr);
	}


// Initialize tables and event listeners
document.addEventListener('DOMContentLoaded', function() {
	document.getElementById('addPhysicalServer').addEventListener('click', function() {
		addPhysicalRow();
	});
	document.getElementById('addVirtualServer').addEventListener('click', function() {
		addVirtualRow();
	});
	document.getElementById('addWorkstation').addEventListener('click', function() {
		addWorkstationRow();
	});
	document.body.addEventListener('click', function(e) {
		if (e.target && e.target.classList.contains('remove-row')) {
			const tr = e.target.closest('tr');
			if (tr) tr.remove();
		}
	});

	// Start with one blank row in each table (will be replaced by prefill if editing)
	addPhysicalRow();
	addVirtualRow();
	addWorkstationRow();
});

// Toggle additional information fields
function toggleAdditionalInfo(section) {
	const field = document.getElementById(section + '_additional_info');
	const button = event.target.closest('button');
	const icon = button.querySelector('i');
	
	if (field.style.display === 'none') {
		field.style.display = 'block';
		icon.classList.remove('bi-plus-circle');
		icon.classList.add('bi-dash-circle');
		button.querySelector('.bi').nextSibling.textContent = ' Hide Additional Information';
	} else {
		field.style.display = 'none';
		icon.classList.remove('bi-dash-circle');
		icon.classList.add('bi-plus-circle');
		button.querySelector('.bi').nextSibling.textContent = ' Additional Information';
	}
}
</script>



<script>
// Prefill all form fields from audit data
document.addEventListener('DOMContentLoaded', function() {
	const audit = <?= json_encode($audit) ?>;
	
	// Prefill text inputs and textareas
	const textFields = {
		'gen_notes': audit.gen_notes,
		'gen_additional_info': audit.gen_additional_info,
		'top_internet_provider': audit.top_internet_provider,
		'top_router_firewall': audit.top_router_firewall,
		'top_switches': audit.top_switches,
		'top_vlans': audit.top_vlans,
		'top_wifi_setup': audit.top_wifi_setup,
		'top_additional_info': audit.top_additional_info,
		'servers_additional_info': audit.servers_additional_info,
		'endpoints_additional_info': audit.endpoints_additional_info,
		'soft_key_apps': audit.soft_key_apps,
		'soft_licensing_type': audit.soft_licensing_type,
		'soft_antivirus_tools': audit.soft_antivirus_tools,
		'soft_update_mgmt': audit.soft_update_mgmt,
		'soft_additional_info': audit.soft_additional_info,
		'bkp_type': audit.bkp_type,
		'bkp_frequency': audit.bkp_frequency,
		'bkp_retention': audit.bkp_retention,
		'bkp_test_restores': audit.bkp_test_restores,
		'bkp_dr_docs': audit.bkp_dr_docs,
		'bkp_additional_info': audit.bkp_additional_info,
		'sec_firewall_rules': audit.sec_firewall_rules,
		'sec_antivirus': audit.sec_antivirus,
		'sec_password_policy': audit.sec_password_policy,
		'sec_remote_access_tools': audit.sec_remote_access_tools,
		'sec_additional_info': audit.sec_additional_info,
		'cloud_tenant_name': audit.cloud_tenant_name,
		'cloud_file_sharing_tools': audit.cloud_file_sharing_tools,
		'cloud_linked_systems': audit.cloud_linked_systems,
		'cloud_additional_info': audit.cloud_additional_info,
		'web_url': audit.web_url,
		'web_hosting_provider': audit.web_hosting_provider,
		'web_management_company': audit.web_management_company,
		'web_cms': audit.web_cms,
		'web_ssl_certificate': audit.web_ssl_certificate,
		'web_notes': audit.web_notes,
		'web_additional_info': audit.web_additional_info,
		'observations': audit.observations
	};
	
	for (const [field, value] of Object.entries(textFields)) {
		const el = document.getElementById(field);
		if (el && value) {
			el.value = value;
			// Show additional info fields if they have content
			if (field.includes('additional_info') && value) {
				el.style.display = 'block';
				const btn = el.previousElementSibling;
				if (btn && btn.tagName === 'BUTTON') {
					const icon = btn.querySelector('i');
					if (icon) {
						icon.classList.remove('bi-plus-circle');
						icon.classList.add('bi-dash-circle');
					}
				}
			}
		}
	}
	
	// Prefill checkboxes (ensure '0' string is not treated as true)
	const toBool = (v) => v === 1 || v === '1' || v === true;
	const relCb = document.getElementById('gen_reliability_issues');
	if (relCb) relCb.checked = toBool(audit.gen_reliability_issues);
	const undocCb = document.getElementById('gen_undocumented_systems');
	if (undocCb) undocCb.checked = toBool(audit.gen_undocumented_systems);
	const contractsCb = document.getElementById('gen_support_contracts');
	if (contractsCb) contractsCb.checked = toBool(audit.gen_support_contracts);
	const mfaCb = document.getElementById('sec_mfa');
	if (mfaCb) mfaCb.checked = toBool(audit.sec_mfa);
	
	// Prefill radio buttons
	if (audit.web_has_website) {
		const radio = document.querySelector(`input[name="web_has_website"][value="${audit.web_has_website}"]`);
		if (radio) radio.checked = true;
	}
	if (audit.web_hosting_location) {
		const radio = document.querySelector(`input[name="web_hosting_location"][value="${audit.web_hosting_location}"]`);
		if (radio) radio.checked = true;
	}
	if (audit.web_managed_by) {
		const radio = document.querySelector(`input[name="web_managed_by"][value="${audit.web_managed_by}"]`);
		if (radio) radio.checked = true;
	}
	
	// Prefill connection types checkboxes
	if (audit.top_connection_types_array) {
		audit.top_connection_types_array.forEach(type => {
			const cb = document.querySelector(`input[name="top_connection_types[]"][value="${type}"]`);
			if (cb) cb.checked = true;
		});
	}
	
	// Prefill cloud platforms checkboxes
	if (audit.cloud_platforms_array) {
		audit.cloud_platforms_array.forEach(plat => {
			const cb = document.querySelector(`input[name="cloud_platforms[]"][value="${plat}"]`);
			if (cb) cb.checked = true;
		});
	}
	
	// Prefill dynamic tables
	if (audit.servers_physical_decoded && audit.servers_physical_decoded.length > 0) {
		// Clear default row
		document.querySelector('#physicalServersTable tbody').innerHTML = '';
		audit.servers_physical_decoded.forEach(row => addPhysicalRow(row));
	}
	if (audit.servers_virtual_decoded && audit.servers_virtual_decoded.length > 0) {
		document.querySelector('#virtualServersTable tbody').innerHTML = '';
		audit.servers_virtual_decoded.forEach(row => addVirtualRow(row));
	}
	if (audit.endpoints_workstations_decoded && audit.endpoints_workstations_decoded.length > 0) {
		document.querySelector('#workstationsTable tbody').innerHTML = '';
		audit.endpoints_workstations_decoded.forEach(row => addWorkstationRow(row));
	}
});
</script>

<script>
// Autocomplete for workstation fields based on previously entered values
function setupWorkstationAutocomplete() {
	const table = document.getElementById('workstationsTable');
	if (!table) return;
	
	function getUniqueValues(fieldName) {
		const values = new Set();
		const inputs = table.querySelectorAll(`input[name^="endpoints_workstations"][name$="[${fieldName}]"]`);
		inputs.forEach(input => {
			if (input.value && input.value.trim()) {
				values.add(input.value.trim());
			}
		});
		return Array.from(values);
	}
	
	function setupAutocomplete(input, fieldName) {
		let datalistId = 'datalist_' + fieldName;
		let datalist = document.getElementById(datalistId);
		
		if (!datalist) {
			datalist = document.createElement('datalist');
			datalist.id = datalistId;
			document.body.appendChild(datalist);
		}
		
		input.setAttribute('list', datalistId);
		
		input.addEventListener('focus', function() {
			const values = getUniqueValues(fieldName);
			datalist.innerHTML = '';
			values.forEach(value => {
				const option = document.createElement('option');
				option.value = value;
				datalist.appendChild(option);
			});
		});
	}
	
	const observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			mutation.addedNodes.forEach(function(node) {
				if (node.nodeType === 1 && node.tagName === 'TR') {
					const autocompleteFields = ['manufacturer', 'model_number', 'cpu', 'ram', 'graphics_card', 'current_os'];
					autocompleteFields.forEach(field => {
						const input = node.querySelector(`input[name$="[${field}]"]`);
						if (input) {
							setupAutocomplete(input, field);
						}
					});
				}
			});
		});
	});
	
	observer.observe(table.querySelector('tbody'), { childList: true });
	
	const autocompleteFields = ['manufacturer', 'model_number', 'cpu', 'ram', 'graphics_card', 'current_os'];
	autocompleteFields.forEach(field => {
		const inputs = table.querySelectorAll(`input[name$="[${field}]"]`);
		inputs.forEach(input => setupAutocomplete(input, field));
	});
}

setTimeout(setupWorkstationAutocomplete, 600);
</script>
