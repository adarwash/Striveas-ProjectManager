<?php

class Networkaudits extends Controller {
	private $clientModel;
	private $userModel;
	private $auditModel;

	public function __construct() {
		if (!isLoggedIn()) {
			redirect('users/login');
			return;
		}
		$this->clientModel = $this->model('Client');
		$this->userModel = $this->model('User');
		$this->auditModel = $this->model('Networkaudit');
		$_SESSION['page'] = 'clients';
	}

	public function create() {
		if (!hasPermission('clients.update')) {
			flash('client_error', 'You do not have permission to create audits.', 'alert-danger');
			redirect('clients');
			return;
		}

		$clients = $this->clientModel->getAllClients();
		$users = $this->userModel->getAllUsers();

		$prefillClientId = isset($_GET['client_id']) ? (int)$_GET['client_id'] : null;

		$data = [
			'title' => 'Network Infrastructure Discovery Form',
			'clients' => $clients,
			'users' => $users,
			'client_id' => $prefillClientId,
			'audit_date' => date('Y-m-d'),
		];

		$this->view('network_audits/create', $data);
	}

	public function show($id = null) {
		if (!$id) {
			flash('client_error', 'Invalid audit ID.', 'alert-danger');
			redirect('clients');
			return;
		}

		$audit = $this->auditModel->getById((int)$id);
		if (!$audit) {
			flash('client_error', 'Audit not found.', 'alert-danger');
			redirect('clients');
			return;
		}

		$client = $this->clientModel->getClientById((int)$audit['client_id']);
		if (!$client) {
			flash('client_error', 'Client not found.', 'alert-danger');
			redirect('clients');
			return;
		}

		// Parse JSON fields
		$audit['servers_physical_decoded'] = !empty($audit['servers_physical']) ? json_decode($audit['servers_physical'], true) : [];
		$audit['servers_virtual_decoded'] = !empty($audit['servers_virtual']) ? json_decode($audit['servers_virtual'], true) : [];
		$audit['endpoints_workstations_decoded'] = !empty($audit['endpoints_workstations']) ? json_decode($audit['endpoints_workstations'], true) : [];
		$audit['top_connection_types_array'] = !empty($audit['top_connection_types']) ? explode(',', $audit['top_connection_types']) : [];
		$audit['cloud_platforms_array'] = !empty($audit['cloud_platforms']) ? explode(',', $audit['cloud_platforms']) : [];

		$data = [
			'title' => 'Network Infrastructure Discovery - ' . $client['name'],
			'audit' => $audit,
			'client' => $client,
		];

		$this->view('network_audits/view', $data);
	}

	public function store() {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			redirect('networkaudits/create');
			return;
		}
		if (!hasPermission('clients.update')) {
			flash('client_error', 'You do not have permission to create audits.', 'alert-danger');
			redirect('clients');
			return;
		}

		$clientId = (int)($_POST['client_id'] ?? 0);
		if ($clientId <= 0) {
			flash('client_error', 'Client is required.', 'alert-danger');
			redirect('networkaudits/create');
			return;
		}

		// Collect engineers (multi-select of user IDs)
		$engineerIds = isset($_POST['engineer_ids']) && is_array($_POST['engineer_ids']) ? array_map('intval', $_POST['engineer_ids']) : [];
		$engineerIdsJson = $engineerIds ? json_encode($engineerIds) : null;

		// Resolve engineer names for readability
		$engineerNames = null;
		if (!empty($engineerIds)) {
			$allUsers = $this->userModel->getAllUsers();
			$idToName = [];
			foreach ($allUsers as $u) {
				$label = $u['full_name'] ?? $u['name'] ?? $u['username'] ?? 'User';
				$idToName[(int)$u['id']] = $label;
			}
			$names = [];
			foreach ($engineerIds as $eid) {
				if (isset($idToName[$eid])) {
					$names[] = $idToName[$eid];
				}
			}
			$engineerNames = $names ? implode(', ', $names) : null;
		}

		// Connection types and cloud platforms from checkbox arrays
		$topConnectionTypes = isset($_POST['top_connection_types']) && is_array($_POST['top_connection_types'])
			? implode(',', $_POST['top_connection_types']) : null;
		$cloudPlatforms = isset($_POST['cloud_platforms']) && is_array($_POST['cloud_platforms'])
			? implode(',', $_POST['cloud_platforms']) : null;

		// Dynamic tables: physical servers, virtual servers, workstations
		$serversPhysicalJson = null;
		if (!empty($_POST['servers_physical']) && is_array($_POST['servers_physical'])) {
			$rows = array_values(array_filter($_POST['servers_physical'], function($r) {
				return !empty($r['name']) || !empty($r['role']) || !empty($r['location']) || !empty($r['quantity']) || !empty($r['notes']);
			}));
			if ($rows) { $serversPhysicalJson = json_encode($rows); }
		}
		$serversVirtualJson = null;
		if (!empty($_POST['servers_virtual']) && is_array($_POST['servers_virtual'])) {
			$rows = array_values(array_filter($_POST['servers_virtual'], function($r) {
				return !empty($r['vm_name']) || !empty($r['role']) || !empty($r['os']) || !empty($r['host']) || !empty($r['ip']) || !empty($r['dependencies']) || !empty($r['notes']);
			}));
			if ($rows) { $serversVirtualJson = json_encode($rows); }
		}
		$endpointsWorkstationsJson = null;
		if (!empty($_POST['endpoints_workstations']) && is_array($_POST['endpoints_workstations'])) {
			$rows = array_values(array_filter($_POST['endpoints_workstations'], function($r) {
				return !empty($r['machine_name']) || !empty($r['manufacturer']) || !empty($r['model_number']) || 
				       !empty($r['cpu']) || !empty($r['ram']) || !empty($r['graphics_card']) || 
				       !empty($r['location']) || !empty($r['current_os']) || !empty($r['needs_replacing']) || 
				       !empty($r['os_status']) || !empty($r['notes']);
			}));
			if ($rows) { $endpointsWorkstationsJson = json_encode($rows); }
		}

		$insertId = $this->auditModel->add([
			'client_id' => $clientId,
			'site_location' => trim($_POST['site_location'] ?? ''),
			'engineer_ids_json' => $engineerIdsJson,
			'engineer_names' => $engineerNames,
			'audit_date' => $_POST['audit_date'] ?? date('Y-m-d'),

			'gen_reliability_issues' => !empty($_POST['gen_reliability_issues']),
			'gen_undocumented_systems' => !empty($_POST['gen_undocumented_systems']),
			'gen_support_contracts' => !empty($_POST['gen_support_contracts']),
			'gen_notes' => trim($_POST['gen_notes'] ?? ''),
			'gen_additional_info' => trim($_POST['gen_additional_info'] ?? ''),

			'top_internet_provider' => trim($_POST['top_internet_provider'] ?? ''),
			'top_connection_types' => $topConnectionTypes,
			'top_router_firewall' => trim($_POST['top_router_firewall'] ?? ''),
			'top_switches' => trim($_POST['top_switches'] ?? ''),
			'top_vlans' => trim($_POST['top_vlans'] ?? ''),
			'top_wifi_setup' => trim($_POST['top_wifi_setup'] ?? ''),
			'top_additional_info' => trim($_POST['top_additional_info'] ?? ''),

			'servers_physical_json' => $serversPhysicalJson,
			'servers_virtual_json' => $serversVirtualJson,
			'servers_additional_info' => trim($_POST['servers_additional_info'] ?? ''),

			'endpoints_workstations_json' => $endpointsWorkstationsJson,
			'endpoints_additional_info' => trim($_POST['endpoints_additional_info'] ?? ''),

			'soft_key_apps' => trim($_POST['soft_key_apps'] ?? ''),
			'soft_licensing_type' => trim($_POST['soft_licensing_type'] ?? ''),
			'soft_antivirus_tools' => trim($_POST['soft_antivirus_tools'] ?? ''),
			'soft_update_mgmt' => trim($_POST['soft_update_mgmt'] ?? ''),
			'soft_additional_info' => trim($_POST['soft_additional_info'] ?? ''),

			'bkp_type' => trim($_POST['bkp_type'] ?? ''),
			'bkp_frequency' => trim($_POST['bkp_frequency'] ?? ''),
			'bkp_retention' => trim($_POST['bkp_retention'] ?? ''),
			'bkp_test_restores' => trim($_POST['bkp_test_restores'] ?? ''),
			'bkp_dr_docs' => trim($_POST['bkp_dr_docs'] ?? ''),
			'bkp_additional_info' => trim($_POST['bkp_additional_info'] ?? ''),

			'sec_firewall_rules' => trim($_POST['sec_firewall_rules'] ?? ''),
			'sec_antivirus' => trim($_POST['sec_antivirus'] ?? ''),
			'sec_mfa' => !empty($_POST['sec_mfa']),
			'sec_password_policy' => trim($_POST['sec_password_policy'] ?? ''),
			'sec_remote_access_tools' => trim($_POST['sec_remote_access_tools'] ?? ''),
			'sec_additional_info' => trim($_POST['sec_additional_info'] ?? ''),

			'cloud_tenant_name' => trim($_POST['cloud_tenant_name'] ?? ''),
			'cloud_platforms' => $cloudPlatforms,
			'cloud_file_sharing_tools' => trim($_POST['cloud_file_sharing_tools'] ?? ''),
			'cloud_linked_systems' => trim($_POST['cloud_linked_systems'] ?? ''),
			'cloud_additional_info' => trim($_POST['cloud_additional_info'] ?? ''),

			'web_has_website' => trim($_POST['web_has_website'] ?? ''),
			'web_url' => trim($_POST['web_url'] ?? ''),
			'web_hosting_location' => trim($_POST['web_hosting_location'] ?? ''),
			'web_hosting_provider' => trim($_POST['web_hosting_provider'] ?? ''),
			'web_managed_by' => trim($_POST['web_managed_by'] ?? ''),
			'web_management_company' => trim($_POST['web_management_company'] ?? ''),
			'web_cms' => trim($_POST['web_cms'] ?? ''),
			'web_ssl_certificate' => trim($_POST['web_ssl_certificate'] ?? ''),
			'web_notes' => trim($_POST['web_notes'] ?? ''),
			'web_additional_info' => trim($_POST['web_additional_info'] ?? ''),

			'observations' => trim($_POST['observations'] ?? ''),
			'created_by' => (int)($_SESSION['user_id'] ?? 0),
		]);

		if (!$insertId) {
			flash('client_error', 'Failed to save discovery form.', 'alert-danger');
			redirect('networkaudits/create?client_id=' . $clientId);
			return;
		}

		flash('client_success', 'Network Infrastructure Discovery Form saved.', 'alert-success');
		redirect('networkaudits/show/' . $insertId);
	}
}


