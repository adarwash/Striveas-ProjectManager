<?php
$a = $audit;
$c = $client;
?>
<div class="container-fluid">
	<!-- Page Header -->
	<div class="rounded-3 p-4 mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb mb-2">
				<li class="breadcrumb-item"><a href="/dashboard" class="text-white text-decoration-none">Dashboard</a></li>
				<li class="breadcrumb-item"><a href="/clients" class="text-white text-decoration-none">Clients</a></li>
				<li class="breadcrumb-item"><a href="/clients/viewClient/<?= (int)$c['id'] ?>" class="text-white text-decoration-none"><?= htmlspecialchars($c['name']) ?></a></li>
				<li class="breadcrumb-item active text-white" aria-current="page">Discovery Form</li>
			</ol>
		</nav>
		
		<div class="d-flex justify-content-between align-items-center">
			<div class="text-white">
				<h1 class="h3 mb-1">Network Infrastructure Discovery</h1>
				<p class="mb-0 opacity-75">
					<?= htmlspecialchars($c['name']) ?> • <?= !empty($a['audit_date']) ? date('M j, Y', strtotime($a['audit_date'])) : 'Date Not Set' ?>
				</p>
			</div>
		<div class="d-flex gap-2">
			<a href="/clients/viewClient/<?= (int)$c['id'] ?>" class="btn btn-light">
				<i class="bi bi-arrow-left"></i> Back to Client
			</a>
			<?php if (hasPermission('clients.update')): ?>
			<a href="/networkaudits/edit/<?= (int)$a['id'] ?>" class="btn btn-warning">
				<i class="bi bi-pencil"></i> Edit
			</a>
			<?php endif; ?>
			<button onclick="window.print()" class="btn btn-outline-light">
				<i class="bi bi-printer"></i> Print
			</button>
		</div>
		</div>
	</div>

	<?php flash('client_success'); ?>
	<?php flash('client_error'); ?>

	<!-- 1. Client & Site Details -->
	<div class="card border-0 shadow-sm mb-4">
		<div class="card-header bg-white py-3">
			<h5 class="card-title mb-0"><i class="bi bi-building me-2 text-primary"></i>Client & Site Details</h5>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-md-6">
					<dl class="row">
						<dt class="col-sm-4">Client:</dt>
						<dd class="col-sm-8"><?= htmlspecialchars($c['name']) ?></dd>
						
						<dt class="col-sm-4">Site Location:</dt>
						<dd class="col-sm-8"><?= !empty($a['site_location']) ? htmlspecialchars($a['site_location']) : '<span class="text-muted">—</span>' ?></dd>
					</dl>
				</div>
				<div class="col-md-6">
					<dl class="row">
						<dt class="col-sm-4">Engineer(s):</dt>
						<dd class="col-sm-8"><?= !empty($a['engineer_names']) ? htmlspecialchars($a['engineer_names']) : '<span class="text-muted">—</span>' ?></dd>
						
						<dt class="col-sm-4">Date:</dt>
						<dd class="col-sm-8"><?= !empty($a['audit_date']) ? date('M j, Y', strtotime($a['audit_date'])) : '<span class="text-muted">—</span>' ?></dd>
					</dl>
				</div>
			</div>
		</div>
	</div>

	<!-- 2. General Overview -->
	<div class="card border-0 shadow-sm mb-4">
		<div class="card-header bg-white py-3">
			<h5 class="card-title mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>General Overview</h5>
		</div>
		<div class="card-body">
			<div class="row mb-3">
				<div class="col-md-4">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" disabled <?= !empty($a['gen_reliability_issues']) ? 'checked' : '' ?>>
						<label class="form-check-label">Reliability Issues Identified</label>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" disabled <?= !empty($a['gen_undocumented_systems']) ? 'checked' : '' ?>>
						<label class="form-check-label">Undocumented Systems</label>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-check">
						<input class="form-check-input" type="checkbox" disabled <?= !empty($a['gen_support_contracts']) ? 'checked' : '' ?>>
						<label class="form-check-label">Active Support Contracts</label>
					</div>
				</div>
			</div>
		<?php if (!empty($a['gen_notes'])): ?>
		<div class="mt-3">
			<strong>Notes:</strong>
			<p class="mb-0 mt-2"><?= nl2br(htmlspecialchars($a['gen_notes'])) ?></p>
		</div>
		<?php endif; ?>
		<?php if (!empty($a['gen_additional_info'])): ?>
		<div class="mt-3">
			<strong>Additional Information:</strong>
			<div class="alert alert-info mt-2 mb-0"><?= nl2br(htmlspecialchars($a['gen_additional_info'])) ?></div>
		</div>
		<?php endif; ?>
	</div>
</div>

	<!-- 3. Network Topology & Connectivity -->
	<div class="card border-0 shadow-sm mb-4">
		<div class="card-header bg-white py-3">
			<h5 class="card-title mb-0"><i class="bi bi-diagram-3 me-2 text-primary"></i>Network Topology & Connectivity</h5>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-md-6 mb-3">
					<strong>Internet Provider:</strong>
					<p class="mb-0"><?= !empty($a['top_internet_provider']) ? htmlspecialchars($a['top_internet_provider']) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
				<div class="col-md-6 mb-3">
					<strong>Connection Type(s):</strong>
					<p class="mb-0">
						<?php if (!empty($a['top_connection_types_array'])): ?>
							<?php foreach ($a['top_connection_types_array'] as $ct): ?>
								<span class="badge bg-secondary me-1"><?= htmlspecialchars($ct) ?></span>
							<?php endforeach; ?>
						<?php else: ?>
							<span class="text-muted">Not specified</span>
						<?php endif; ?>
					</p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6 mb-3">
					<strong>Router/Firewall:</strong>
					<p class="mb-0"><?= !empty($a['top_router_firewall']) ? htmlspecialchars($a['top_router_firewall']) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
				<div class="col-md-6 mb-3">
					<strong>Switches:</strong>
					<p class="mb-0"><?= !empty($a['top_switches']) ? nl2br(htmlspecialchars($a['top_switches'])) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6 mb-3">
					<strong>VLANs:</strong>
					<p class="mb-0"><?= !empty($a['top_vlans']) ? nl2br(htmlspecialchars($a['top_vlans'])) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
				<div class="col-md-6 mb-3">
					<strong>Wi-Fi Setup:</strong>
					<p class="mb-0"><?= !empty($a['top_wifi_setup']) ? nl2br(htmlspecialchars($a['top_wifi_setup'])) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
			</div>
			<?php if (!empty($a['top_additional_info'])): ?>
			<div class="mt-3">
				<strong>Additional Information:</strong>
				<div class="alert alert-info mt-2 mb-0"><?= nl2br(htmlspecialchars($a['top_additional_info'])) ?></div>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- 4. Servers & Core Infrastructure -->
	<div class="card border-0 shadow-sm mb-4">
		<div class="card-header bg-white py-3">
			<h5 class="card-title mb-0"><i class="bi bi-server me-2 text-primary"></i>Servers & Core Infrastructure</h5>
		</div>
		<div class="card-body">
			<h6 class="mb-3">Physical Servers</h6>
			<?php if (!empty($a['servers_physical_decoded'])): ?>
			<div class="table-responsive mb-4">
				<table class="table table-sm table-bordered">
					<thead class="table-light">
						<tr>
							<th>Name</th>
							<th>Role</th>
							<th>Location</th>
							<th>Quantity</th>
							<th>Notes</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($a['servers_physical_decoded'] as $srv): ?>
						<tr>
							<td><?= htmlspecialchars($srv['name'] ?? '') ?></td>
							<td><?= htmlspecialchars($srv['role'] ?? '') ?></td>
							<td><?= htmlspecialchars($srv['location'] ?? '') ?></td>
							<td><?= htmlspecialchars($srv['quantity'] ?? '') ?></td>
							<td><?= htmlspecialchars($srv['notes'] ?? '') ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php else: ?>
			<p class="text-muted mb-4">No physical servers recorded.</p>
			<?php endif; ?>

			<h6 class="mb-3">Virtual Servers</h6>
			<?php if (!empty($a['servers_virtual_decoded'])): ?>
			<div class="table-responsive">
				<table class="table table-sm table-bordered">
					<thead class="table-light">
						<tr>
							<th>VM Name</th>
							<th>Role</th>
							<th>OS</th>
							<th>Host/Platform</th>
							<th>IP Address</th>
							<th>Dependencies</th>
							<th>Notes</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($a['servers_virtual_decoded'] as $vm): ?>
						<tr>
							<td><?= htmlspecialchars($vm['vm_name'] ?? '') ?></td>
							<td><?= htmlspecialchars($vm['role'] ?? '') ?></td>
							<td><?= htmlspecialchars($vm['os'] ?? '') ?></td>
							<td><?= htmlspecialchars($vm['host'] ?? '') ?></td>
							<td><?= htmlspecialchars($vm['ip'] ?? '') ?></td>
							<td><?= htmlspecialchars($vm['dependencies'] ?? '') ?></td>
							<td><?= htmlspecialchars($vm['notes'] ?? '') ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
		</div>
		<?php else: ?>
		<p class="text-muted">No virtual servers recorded.</p>
		<?php endif; ?>
		<?php if (!empty($a['servers_additional_info'])): ?>
		<div class="mt-3">
			<strong>Additional Information:</strong>
			<div class="alert alert-info mt-2 mb-0"><?= nl2br(htmlspecialchars($a['servers_additional_info'])) ?></div>
		</div>
		<?php endif; ?>
	</div>
</div>

<!-- 5. Workstations & Endpoints -->
	<div class="card border-0 shadow-sm mb-4">
		<div class="card-header bg-white py-3">
			<h5 class="card-title mb-0"><i class="bi bi-pc-display me-2 text-primary"></i>Workstations & Endpoints</h5>
		</div>
		<div class="card-body">
		<?php if (!empty($a['endpoints_workstations_decoded'])): ?>
		<div class="table-responsive">
			<table class="table table-sm table-bordered" style="font-size: 0.85rem;">
				<thead class="table-light">
					<tr>
						<th>Machine Name</th>
						<th>Manufacturer</th>
						<th>Model Number</th>
						<th>CPU</th>
						<th>RAM</th>
						<th>Graphics Card</th>
						<th>Location</th>
						<th>Current OS</th>
						<th>Needs Replacing?</th>
						<th>OS Status</th>
						<th>Notes</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($a['endpoints_workstations_decoded'] as $ep): ?>
					<tr>
						<td><?= htmlspecialchars($ep['machine_name'] ?? '') ?></td>
						<td><?= htmlspecialchars($ep['manufacturer'] ?? '') ?></td>
						<td><?= htmlspecialchars($ep['model_number'] ?? '') ?></td>
						<td><?= htmlspecialchars($ep['cpu'] ?? '') ?></td>
						<td><?= htmlspecialchars($ep['ram'] ?? '') ?></td>
						<td><?= htmlspecialchars($ep['graphics_card'] ?? '') ?></td>
						<td><?= htmlspecialchars($ep['location'] ?? '') ?></td>
						<td><?= htmlspecialchars($ep['current_os'] ?? '') ?></td>
						<td>
							<?php 
							$needs = $ep['needs_replacing'] ?? '';
							if ($needs === 'Yes') {
								echo '<span class="badge bg-danger">Yes</span>';
							} elseif ($needs === 'Soon') {
								echo '<span class="badge bg-warning">Soon</span>';
							} elseif ($needs === 'No') {
								echo '<span class="badge bg-success">No</span>';
							} else {
								echo '<span class="text-muted">—</span>';
							}
							?>
						</td>
						<td>
							<?php 
							$os_status = $ep['os_status'] ?? '';
							if ($os_status === 'Current') {
								echo '<span class="badge bg-success">Current</span>';
							} elseif ($os_status === 'Needs Upgrade') {
								echo '<span class="badge bg-warning">Needs Upgrade</span>';
							} elseif ($os_status === 'End of Life') {
								echo '<span class="badge bg-danger">End of Life</span>';
							} else {
								echo '<span class="text-muted">—</span>';
							}
							?>
						</td>
						<td><?= htmlspecialchars($ep['notes'] ?? '') ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
	</div>
	<?php else: ?>
	<p class="text-muted">No workstation data recorded.</p>
	<?php endif; ?>
		<?php if (!empty($a['endpoints_additional_info'])): ?>
		<div class="mt-3">
			<strong>Additional Information:</strong>
			<div class="alert alert-info mt-2 mb-0"><?= nl2br(htmlspecialchars($a['endpoints_additional_info'])) ?></div>
		</div>
		<?php endif; ?>
	</div>
</div>

<!-- 6. Software & Licensing -->
	<div class="card border-0 shadow-sm mb-4">
		<div class="card-header bg-white py-3">
			<h5 class="card-title mb-0"><i class="bi bi-box-seam me-2 text-primary"></i>Software & Licensing</h5>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-md-6 mb-3">
					<strong>Key Applications:</strong>
					<p class="mb-0"><?= !empty($a['soft_key_apps']) ? nl2br(htmlspecialchars($a['soft_key_apps'])) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
				<div class="col-md-6 mb-3">
					<strong>Licensing Type:</strong>
					<p class="mb-0"><?= !empty($a['soft_licensing_type']) ? htmlspecialchars($a['soft_licensing_type']) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6 mb-3">
					<strong>Antivirus Tools:</strong>
					<p class="mb-0"><?= !empty($a['soft_antivirus_tools']) ? htmlspecialchars($a['soft_antivirus_tools']) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
				<div class="col-md-6 mb-3">
				<strong>Update Management:</strong>
				<p class="mb-0"><?= !empty($a['soft_update_mgmt']) ? htmlspecialchars($a['soft_update_mgmt']) : '<span class="text-muted">Not specified</span>' ?></p>
			</div>
		</div>
		<?php if (!empty($a['soft_additional_info'])): ?>
		<div class="mt-3">
			<strong>Additional Information:</strong>
			<div class="alert alert-info mt-2 mb-0"><?= nl2br(htmlspecialchars($a['soft_additional_info'])) ?></div>
		</div>
		<?php endif; ?>
	</div>
</div>

<!-- 7. Backup, DR & Data Protection -->
	<div class="card border-0 shadow-sm mb-4">
		<div class="card-header bg-white py-3">
			<h5 class="card-title mb-0"><i class="bi bi-shield-check me-2 text-primary"></i>Backup, DR & Data Protection</h5>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-md-4 mb-3">
					<strong>Backup Type:</strong>
					<p class="mb-0"><?= !empty($a['bkp_type']) ? htmlspecialchars($a['bkp_type']) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
				<div class="col-md-4 mb-3">
					<strong>Frequency:</strong>
					<p class="mb-0"><?= !empty($a['bkp_frequency']) ? htmlspecialchars($a['bkp_frequency']) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
				<div class="col-md-4 mb-3">
					<strong>Retention:</strong>
					<p class="mb-0"><?= !empty($a['bkp_retention']) ? htmlspecialchars($a['bkp_retention']) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6 mb-3">
					<strong>Test Restores:</strong>
					<p class="mb-0"><?= !empty($a['bkp_test_restores']) ? htmlspecialchars($a['bkp_test_restores']) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
				<div class="col-md-6 mb-3">
				<strong>DR Documentation:</strong>
				<p class="mb-0"><?= !empty($a['bkp_dr_docs']) ? nl2br(htmlspecialchars($a['bkp_dr_docs'])) : '<span class="text-muted">Not specified</span>' ?></p>
			</div>
		</div>
		<?php if (!empty($a['bkp_additional_info'])): ?>
		<div class="mt-3">
			<strong>Additional Information:</strong>
			<div class="alert alert-info mt-2 mb-0"><?= nl2br(htmlspecialchars($a['bkp_additional_info'])) ?></div>
		</div>
		<?php endif; ?>
	</div>
</div>

<!-- 8. Security & Access Control -->
	<div class="card border-0 shadow-sm mb-4">
		<div class="card-header bg-white py-3">
			<h5 class="card-title mb-0"><i class="bi bi-lock me-2 text-primary"></i>Security & Access Control</h5>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-md-6 mb-3">
					<strong>Firewall Rules:</strong>
					<p class="mb-0"><?= !empty($a['sec_firewall_rules']) ? nl2br(htmlspecialchars($a['sec_firewall_rules'])) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
				<div class="col-md-6 mb-3">
					<strong>Antivirus:</strong>
					<p class="mb-0"><?= !empty($a['sec_antivirus']) ? htmlspecialchars($a['sec_antivirus']) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4 mb-3">
					<strong>MFA Enabled:</strong>
					<p class="mb-0">
						<?php if (!empty($a['sec_mfa'])): ?>
							<span class="badge bg-success">Yes</span>
						<?php else: ?>
							<span class="badge bg-secondary">No</span>
						<?php endif; ?>
					</p>
				</div>
				<div class="col-md-4 mb-3">
					<strong>Password Policy:</strong>
					<p class="mb-0"><?= !empty($a['sec_password_policy']) ? htmlspecialchars($a['sec_password_policy']) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
				<div class="col-md-4 mb-3">
					<strong>Remote Access Tools:</strong>
					<p class="mb-0"><?= !empty($a['sec_remote_access_tools']) ? htmlspecialchars($a['sec_remote_access_tools']) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
			</div>
			<?php if (!empty($a['sec_additional_info'])): ?>
			<div class="mt-3">
				<strong>Additional Information:</strong>
				<div class="alert alert-info mt-2 mb-0"><?= nl2br(htmlspecialchars($a['sec_additional_info'])) ?></div>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- 9. Cloud Services & Integrations -->
	<div class="card border-0 shadow-sm mb-4">
		<div class="card-header bg-white py-3">
			<h5 class="card-title mb-0"><i class="bi bi-cloud me-2 text-primary"></i>Cloud Services & Integrations</h5>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="col-md-6 mb-3">
					<strong>Tenant Name:</strong>
					<p class="mb-0"><?= !empty($a['cloud_tenant_name']) ? htmlspecialchars($a['cloud_tenant_name']) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
				<div class="col-md-6 mb-3">
					<strong>Cloud Platform(s):</strong>
					<p class="mb-0">
						<?php if (!empty($a['cloud_platforms_array'])): ?>
							<?php foreach ($a['cloud_platforms_array'] as $plat): ?>
								<span class="badge bg-info me-1"><?= htmlspecialchars($plat) ?></span>
							<?php endforeach; ?>
						<?php else: ?>
							<span class="text-muted">Not specified</span>
						<?php endif; ?>
					</p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6 mb-3">
					<strong>File Sharing Tools:</strong>
					<p class="mb-0"><?= !empty($a['cloud_file_sharing_tools']) ? htmlspecialchars($a['cloud_file_sharing_tools']) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
				<div class="col-md-6 mb-3">
					<strong>Linked Systems:</strong>
					<p class="mb-0"><?= !empty($a['cloud_linked_systems']) ? nl2br(htmlspecialchars($a['cloud_linked_systems'])) : '<span class="text-muted">Not specified</span>' ?></p>
				</div>
			</div>
		<?php if (!empty($a['cloud_additional_info'])): ?>
		<div class="mt-3">
			<strong>Additional Information:</strong>
			<div class="alert alert-info mt-2 mb-0"><?= nl2br(htmlspecialchars($a['cloud_additional_info'])) ?></div>
		</div>
		<?php endif; ?>
	</div>
</div>

<!-- 10. Website & Online Presence -->
<div class="card border-0 shadow-sm mb-4">
	<div class="card-header bg-white py-3">
		<h5 class="card-title mb-0"><i class="bi bi-globe me-2 text-primary"></i>Website & Online Presence</h5>
	</div>
	<div class="card-body">
		<div class="row">
			<div class="col-md-6 mb-3">
				<strong>Has Website:</strong>
				<p class="mb-0">
					<?php 
					$hasWeb = $a['web_has_website'] ?? '';
					if ($hasWeb === 'Yes') {
						echo '<span class="badge bg-success">Yes</span>';
					} elseif ($hasWeb === 'No') {
						echo '<span class="badge bg-secondary">No</span>';
					} else {
						echo '<span class="text-muted">Not specified</span>';
					}
					?>
				</p>
			</div>
			<div class="col-md-6 mb-3">
				<strong>Website URL:</strong>
				<p class="mb-0">
					<?php if (!empty($a['web_url'])): ?>
						<a href="<?= htmlspecialchars($a['web_url']) ?>" target="_blank" class="text-decoration-none">
							<?= htmlspecialchars($a['web_url']) ?> <i class="bi bi-box-arrow-up-right small"></i>
						</a>
					<?php else: ?>
						<span class="text-muted">Not specified</span>
					<?php endif; ?>
				</p>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6 mb-3">
				<strong>Hosting Location:</strong>
				<p class="mb-0">
					<?php 
					$hosting = $a['web_hosting_location'] ?? '';
					if ($hosting === 'Locally Hosted') {
						echo '<span class="badge bg-info">Locally Hosted</span>';
					} elseif ($hosting === 'Cloud Hosted') {
						echo '<span class="badge bg-primary">Cloud Hosted</span>';
					} else {
						echo '<span class="text-muted">Not specified</span>';
					}
					?>
				</p>
			</div>
			<div class="col-md-6 mb-3">
				<strong>Hosting Provider:</strong>
				<p class="mb-0"><?= !empty($a['web_hosting_provider']) ? htmlspecialchars($a['web_hosting_provider']) : '<span class="text-muted">Not specified</span>' ?></p>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6 mb-3">
				<strong>Managed By:</strong>
				<p class="mb-0">
					<?php 
					$managed = $a['web_managed_by'] ?? '';
					if ($managed === 'In-House') {
						echo '<span class="badge bg-success">In-House</span>';
					} elseif ($managed === 'External Company') {
						echo '<span class="badge bg-warning">External Company</span>';
					} elseif ($managed === 'Both') {
						echo '<span class="badge bg-info">Both (Shared)</span>';
					} else {
						echo '<span class="text-muted">Not specified</span>';
					}
					?>
				</p>
			</div>
			<div class="col-md-6 mb-3">
				<strong>Management Company:</strong>
				<p class="mb-0"><?= !empty($a['web_management_company']) ? htmlspecialchars($a['web_management_company']) : '<span class="text-muted">Not specified</span>' ?></p>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6 mb-3">
				<strong>CMS / Platform:</strong>
				<p class="mb-0"><?= !empty($a['web_cms']) ? htmlspecialchars($a['web_cms']) : '<span class="text-muted">Not specified</span>' ?></p>
			</div>
			<div class="col-md-6 mb-3">
				<strong>SSL Certificate:</strong>
				<p class="mb-0">
					<?php 
					$ssl = $a['web_ssl_certificate'] ?? '';
					if ($ssl === 'Valid') {
						echo '<span class="badge bg-success">Valid</span>';
					} elseif ($ssl === 'Expired') {
						echo '<span class="badge bg-danger">Expired</span>';
					} elseif ($ssl === 'None') {
						echo '<span class="badge bg-warning">None</span>';
					} else {
						echo '<span class="text-muted">Not specified</span>';
					}
					?>
				</p>
			</div>
		</div>
		<?php if (!empty($a['web_notes'])): ?>
		<div class="mt-3">
			<strong>Website Notes:</strong>
			<p class="mb-0 mt-2"><?= nl2br(htmlspecialchars($a['web_notes'])) ?></p>
		</div>
		<?php endif; ?>
		<?php if (!empty($a['web_additional_info'])): ?>
		<div class="mt-3">
			<strong>Additional Information:</strong>
			<div class="alert alert-info mt-2 mb-0"><?= nl2br(htmlspecialchars($a['web_additional_info'])) ?></div>
		</div>
		<?php endif; ?>
	</div>
</div>

<!-- 11. Observations & Recommendations -->
	<div class="card border-0 shadow-sm mb-4">
		<div class="card-header bg-white py-3">
			<h5 class="card-title mb-0"><i class="bi bi-lightbulb me-2 text-primary"></i>Observations & Recommendations</h5>
		</div>
		<div class="card-body">
			<?php if (!empty($a['observations'])): ?>
				<p class="mb-0"><?= nl2br(htmlspecialchars($a['observations'])) ?></p>
			<?php else: ?>
				<p class="text-muted mb-0">No observations recorded.</p>
			<?php endif; ?>
		</div>
	</div>

	<!-- Metadata -->
	<div class="card border-0 shadow-sm mb-4">
		<div class="card-body">
			<div class="row text-muted small">
				<div class="col-md-6">
					<strong>Created:</strong> <?= !empty($a['created_at']) ? date('M j, Y g:i A', strtotime($a['created_at'])) : 'Unknown' ?>
				</div>
				<div class="col-md-6 text-end">
					<strong>Audit ID:</strong> #<?= (int)$a['id'] ?>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
@media print {
	.btn, nav, .breadcrumb { display: none !important; }
	.card { page-break-inside: avoid; border: 1px solid #ddd !important; }
}
</style>

