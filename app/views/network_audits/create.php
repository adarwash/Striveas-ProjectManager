<?php
$title = 'Network Infrastructure Discovery Form';
?>

<div class="page-header">
	<div>
		<h1 class="page-title">Network Infrastructure Discovery Form</h1>
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= URLROOT ?>/clients">Clients</a></li>
				<?php if (!empty($client_id)): ?>
					<li class="breadcrumb-item"><a href="<?= URLROOT ?>/clients/viewClient/<?= (int)$client_id ?>">Client</a></li>
				<?php endif; ?>
				<li class="breadcrumb-item active" aria-current="page">New Audit</li>
			</ol>
		</nav>
	</div>
</div>

<form action="<?= URLROOT ?>/networkaudits/store" method="post">
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
									<option value="<?= (int)$c['id'] ?>" <?= (!empty($client_id) && (int)$client_id === (int)$c['id']) ? 'selected' : '' ?>>
										<?= htmlspecialchars($c['name']) ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-6 mb-3">
							<label for="site_location" class="form-label">Site location</label>
							<input type="text" class="form-control" id="site_location" name="site_location" placeholder="e.g., London HQ">
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label for="engineer_ids" class="form-label">Engineer(s)</label>
							<select class="form-select" id="engineer_ids" name="engineer_ids[]" multiple size="6">
								<?php foreach ($users as $u): ?>
									<option value="<?= (int)$u['id'] ?>">
										<?= htmlspecialchars($u['full_name'] ?? $u['name'] ?? $u['username'] ?? 'User') ?>
									</option>
								<?php endforeach; ?>
							</select>
							<div class="form-text">Hold Ctrl/Cmd to select multiple.</div>
						</div>
						<div class="col-md-3 mb-3">
							<label for="audit_date" class="form-label">Date</label>
							<input type="date" class="form-control" id="audit_date" name="audit_date" value="<?= htmlspecialchars($audit_date ?? date('Y-m-d')) ?>">
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
						<table class="table table-bordered align-middle" id="workstationsTable">
							<thead>
								<tr>
									<th style="min-width:160px;">User / Location</th>
									<th>Existing PC</th>
									<th>Replacement Model</th>
									<th>Software Dependencies</th>
									<th>Notes</th>
									<th style="width:60px;"></th>
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
				<h5 class="mb-0">10. Observations & Recommendations</h5>
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
document.addEventListener('DOMContentLoaded', function() {
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
			<td><input type="text" class="form-control" name="endpoints_workstations[${idx}][user_location]" value="${values.user_location || ''}"></td>
			<td><input type="text" class="form-control" name="endpoints_workstations[${idx}][existing_pc]" value="${values.existing_pc || ''}"></td>
			<td><input type="text" class="form-control" name="endpoints_workstations[${idx}][replacement_model]" value="${values.replacement_model || ''}"></td>
			<td><input type="text" class="form-control" name="endpoints_workstations[${idx}][software_deps]" value="${values.software_deps || ''}"></td>
			<td><input type="text" class="form-control" name="endpoints_workstations[${idx}][notes]" value="${values.notes || ''}"></td>
			<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>
		`;
		tbody.appendChild(tr);
	}

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

	// Start with one blank row in each table
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


