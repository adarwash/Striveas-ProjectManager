<?php

class Clients extends Controller {
    private $reminderModel;
    private $clientModel;
    private $siteModel;
    private $contractModel;
    private $projectModel;
    private $settingModel;
	private $callbackModel;
	private $auditModel;
	private $clientMeetingModel;
	private $clientDocumentModel;
    
    /**
     * Initialize controller and load models
     */
    public function __construct() {
        // Make sure user is logged in
        if(!isLoggedIn()) {
            redirect('users/login');
        }
        
        // Load models
        $this->clientModel = $this->model('Client');
        $this->siteModel = $this->model('Site');
        $this->contractModel = $this->model('ClientContract');
        $this->projectModel = $this->model('Project');
		$this->settingModel = $this->model('Setting');
		$this->callbackModel = $this->model('ClientCallback');
		$this->reminderModel = $this->model('Reminder');
		$this->auditModel = $this->model('Networkaudit');
		$this->clientMeetingModel = $this->model('ClientMeeting');
		$this->clientDocumentModel = $this->model('ClientDocument');
        
        // Set the page for sidebar highlighting
        $_SESSION['page'] = 'clients';
    }
    
    /**
     * Clients index/list page
     */
    public function index() {
        // Check permission
        if (!hasPermission('clients.read')) {
            flash('client_error', 'You do not have permission to view clients', 'alert-danger');
            redirect('dashboard');
            return;
        }
        
        // Get all clients
        $clients = $this->clientModel->getAllClients();
        
        $data = [
            'title' => 'Clients',
            'clients' => $clients
        ];
        
        $this->view('clients/index', $data);
    }
    
    /**
     * View a specific client
     * 
     * @param int $id Client ID
     */
    public function viewClient($id = null) {
        // Check permission
        if (!hasPermission('clients.read')) {
            flash('client_error', 'You do not have permission to view clients', 'alert-danger');
            redirect('dashboard');
            return;
        }
        
        if (!$id) {
            redirect('clients');
            return;
        }
        
        // Get client details
        $client = $this->clientModel->getClientById($id);
        
        if (!$client) {
            flash('client_error', 'Client not found', 'alert-danger');
            redirect('clients');
            return;
        }
        
        // Get sites assigned to this client
        $sites = $this->clientModel->getSiteClients($id);

        // Ensure projects table has client_id column, then get projects for this client
        try {
            if (method_exists($this->projectModel, 'ensureClientColumn')) {
                $this->projectModel->ensureClientColumn();
            }
        } catch (Exception $e) { /* ignore */ }
        $projects = [];
        try {
            $projects = $this->projectModel->getProjectsByClient($id);
        } catch (Exception $e) { $projects = []; }

		// Get follow-ups/reminders (from universal Reminders)
		$callbacks = [];
		try {
			$callbacks = $this->reminderModel->getByEntity('client', (int)$id);
		} catch (Exception $e) {
			$callbacks = [];
		}

		// Get network audits for this client
		$audits = [];
		try {
			$audits = $this->auditModel->getByClient((int)$id);
		} catch (Exception $e) {
			$audits = [];
		}

        // Load services per site for quick overview
        $sitesWithServices = $sites;
        try {
            $siteServiceModel = $this->model('SiteService');
            foreach ($sitesWithServices as &$s) {
                $s['services'] = $siteServiceModel->listBySite((int)$s['id']);
            }
        } catch (Exception $e) {
            // Fallback without services
        }

        // Get recent site visits for this client
        try {
            $recentVisits = $this->clientModel->getRecentSiteVisits($id, 10);
        } catch (Exception $e) {
            $recentVisits = [];
        }

		// Client documents
		$documents = [];
		try {
			$documents = $this->clientDocumentModel->listByClient((int)$id);
			if (!empty($documents)) {
				foreach ($documents as &$doc) {
					$doc['formatted_size'] = $this->formatFileSize((int)($doc['file_size'] ?? 0));
				}
			}
		} catch (Exception $e) {
			$documents = [];
		}

        // Get client domains
        try {
            if (!class_exists('ClientDomain')) {
                require_once APPROOT . '/app/models/ClientDomain.php';
            }
            $clientDomainModel = new ClientDomain();
            $domains = $clientDomainModel->getDomainsByClient($id);
        } catch (Exception $e) {
            $domains = [];
        }

        // Client notes (top 10 recent)
        $clientNotes = [];
        try {
            $noteModel = $this->model('Note');
            $clientNotes = $noteModel->getRecentByReference('client', (int)$id, 10);
        } catch (Exception $e) {
            $clientNotes = [];
        }

        // Get contracts
        try {
            $contracts = $this->contractModel->getContractsByClient($id);
        } catch (Exception $e) {
            $contracts = [];
        }

        // Level.io integration info
        $levelIoEnabled = false;
        $levelIoGroups = [];
        try {
            $systemSettings = $this->settingModel->getSystemSettings();
            $levelIoEnabled = !empty($systemSettings['level_io_enabled']) && !empty($systemSettings['level_io_api_key']);
            if ($levelIoEnabled && method_exists($this->clientModel, 'getLevelGroups')) {
                $levelIoGroups = $this->clientModel->getLevelGroups((int)$id);
            }
        } catch (Exception $e) {
            $levelIoEnabled = false;
            $levelIoGroups = [];
        }
        
        $data = [
            'title' => $client['name'],
            'client' => $client,
            'sites' => $sitesWithServices,
            'all_sites' => $this->siteModel->getAllSites(),
            'domains' => $domains,
            'recent_visits' => $recentVisits,
            'contracts' => $contracts,
			'projects' => $projects,
			'callbacks' => $callbacks,
			'audits' => $audits,
            'client_notes' => $clientNotes,
			'meetings' => $this->clientMeetingModel->listByClient((int)$id),
            'currency' => $this->settingModel->getCurrency(),
            'documents' => $documents,
            'level_io_enabled' => $levelIoEnabled,
            'level_io_groups' => $levelIoGroups
        ];
        
        $this->view('clients/view', $data);
    }

	/**
	 * Add a meeting for a client
	 */
	public function addMeeting($clientId) {
		if (!hasPermission('clients.update')) {
			flash('client_error', 'You do not have permission to create meetings.', 'alert-danger');
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			redirect('clients/viewClient/' . (int)$clientId);
		}
		$title = trim((string)($_POST['title'] ?? ''));
		$personGoing = trim((string)($_POST['person_going'] ?? ''));
		$personVisiting = trim((string)($_POST['person_visiting'] ?? ''));
		$additionalGoing = trim((string)($_POST['additional_going'] ?? ''));
		$additionalMeeting = trim((string)($_POST['additional_meeting'] ?? ''));
		$siteId = (string)($_POST['site_id'] ?? '');
		$meetingAt = trim((string)($_POST['meeting_at'] ?? ''));
		$info = trim((string)($_POST['info'] ?? ''));
		
		if ($title === '' || $meetingAt === '') {
			flash('client_error', 'Please provide a title and date/time for the meeting.', 'alert-danger');
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}
		// Normalize datetime-local input to SQL format
		$meetingTs = strtotime($meetingAt);
		if ($meetingTs === false) {
			flash('client_error', 'Invalid meeting date/time.', 'alert-danger');
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}
		$meetingAtSql = date('Y-m-d H:i:s', $meetingTs);
		$payload = [
			'client_id' => (int)$clientId,
			'site_id' => $siteId,
			'title' => $title,
			'person_going' => $personGoing,
			'person_visiting' => $personVisiting,
			'additional_going' => $additionalGoing,
			'additional_meeting' => $additionalMeeting,
			'info' => $info,
			'meeting_at' => $meetingAtSql,
			'created_by' => (int)($_SESSION['user_id'] ?? 0)
		];
		$ok = $this->clientMeetingModel->add($payload);
		if ($ok) {
			flash('client_success', 'Meeting created.');
		} else {
			flash('client_error', 'Failed to create meeting.', 'alert-danger');
		}
		redirect('clients/viewClient/' . (int)$clientId);
	}

	/**
	 * Upload a general client document (with tags)
	 */
	public function uploadDocument($clientId = null) {
		if (!$clientId) {
			redirect('clients');
			return;
		}
		if (!hasPermission('clients.update')) {
			flash('client_error', 'You do not have permission to upload documents', 'alert-danger');
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			redirect('clients/viewClient/' . (int)$clientId);
		}

		if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
			flash('client_error', 'Error uploading file. Please try again.', 'alert-danger');
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}

		$file = $_FILES['document'];

		// Validate size (10MB)
		if ((int)$file['size'] > 10 * 1024 * 1024) {
			flash('client_error', 'File size exceeds 10MB limit.', 'alert-danger');
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}

		// Validate type
		$allowed = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png','txt'];
		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		if (!in_array($ext, $allowed, true)) {
			flash('client_error', 'Invalid file type. Allowed: ' . implode(', ', $allowed), 'alert-danger');
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}

		// Resolve document root and upload dir
		$docRoot = (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'])
			? rtrim($_SERVER['DOCUMENT_ROOT'], '/')
			: (APPROOT . '/public');
		$uploadDir = $docRoot . '/uploads/clients/' . (int)$clientId . '/documents/';
		if (!file_exists($uploadDir)) {
			if (!@mkdir($uploadDir, 0775, true)) {
				flash('client_error', 'Server cannot create upload directory.', 'alert-danger');
				redirect('clients/viewClient/' . (int)$clientId);
				return;
			}
		}
		if (!is_writable($uploadDir)) {
			flash('client_error', 'Upload directory is not writable. Please contact admin.', 'alert-danger');
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}

		// Safe file name
		$safeBase = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file['name']);
		$newName = uniqid('doc_') . '_' . $safeBase;
		$absPath = $uploadDir . $newName;

		if (!move_uploaded_file($file['tmp_name'], $absPath)) {
			flash('client_error', 'Error saving uploaded file.', 'alert-danger');
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}

		$publicPath = '/uploads/clients/' . (int)$clientId . '/documents/' . $newName;
		$tags = trim((string)($_POST['tags'] ?? ''));
		$description = trim((string)($_POST['description'] ?? ''));

		$fileData = [
			'file_name' => $file['name'],
			'file_path' => $publicPath,
			'file_type' => $file['type'] ?? null,
			'file_size' => (int)$file['size'],
			'uploaded_by' => (int)($_SESSION['user_id'] ?? 0)
		];

		$insertId = $this->clientDocumentModel->uploadDocument((int)$clientId, $fileData, $tags !== '' ? $tags : null, $description !== '' ? $description : null);
		if ($insertId) {
			flash('client_success', 'Document uploaded successfully.');
		} else {
			// rollback file if DB failed
			if (file_exists($absPath)) {
				@unlink($absPath);
			}
			flash('client_error', 'Error saving document metadata.', 'alert-danger');
		}

		redirect('clients/viewClient/' . (int)$clientId);
	}

	/**
	 * Download a client document
	 */
	public function downloadDocument($documentId = null) {
		if (!$documentId) {
			redirect('clients');
			return;
		}
		$document = $this->clientDocumentModel->getById((int)$documentId);
		if (!$document) {
			flash('client_error', 'Document not found.', 'alert-danger');
			redirect('clients');
			return;
		}
		$absPath = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/') . $document['file_path'];
		if (!file_exists($absPath)) {
			flash('client_error', 'File not found on server.', 'alert-danger');
			redirect('clients/viewClient/' . (int)$document['client_id']);
			return;
		}

		while (ob_get_level()) { ob_end_clean(); }

		$mime = 'application/octet-stream';
		if (!empty($document['file_type'])) {
			$mime = $document['file_type'];
		} else {
			$ext = strtolower(pathinfo($document['file_name'], PATHINFO_EXTENSION));
			$map = [
				'pdf' => 'application/pdf',
				'doc' => 'application/msword',
				'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'xls' => 'application/vnd.ms-excel',
				'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'jpg' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'png' => 'image/png',
				'txt' => 'text/plain'
			];
			if (isset($map[$ext])) { $mime = $map[$ext]; }
		}

		header('Content-Type: ' . $mime);
		header('Content-Disposition: attachment; filename="' . $document['file_name'] . '"');
		header('Content-Length: ' . filesize($absPath));
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		header('Expires: 0');
		readfile($absPath);
		exit;
	}

	/**
	 * Rename a client document (update display name only)
	 */
	public function renameDocument($documentId = null) {
		if (!$documentId) {
			redirect('clients');
			return;
		}
		if (!hasPermission('clients.update')) {
			flash('client_error', 'You do not have permission to rename documents', 'alert-danger');
			redirect('clients');
			return;
		}
		$document = $this->clientDocumentModel->getById((int)$documentId);
		if (!$document) {
			flash('client_error', 'Document not found.', 'alert-danger');
			redirect('clients');
			return;
		}
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			redirect('clients/viewClient/' . (int)$document['client_id']);
		}
		$newName = trim((string)($_POST['file_name'] ?? ''));
		if ($newName === '') {
			flash('client_error', 'Please provide a valid name.', 'alert-danger');
			redirect('clients/viewClient/' . (int)$document['client_id']);
			return;
		}
		// Sanitize unsafe characters in display name
		$newName = preg_replace('/[\\r\\n\\t\\x00\\x0B]/', ' ', $newName);
		if ($this->clientDocumentModel->rename((int)$documentId, $newName)) {
			flash('client_success', 'Document name updated.');
		} else {
			flash('client_error', 'Failed to update document name.', 'alert-danger');
		}
		redirect('clients/viewClient/' . (int)$document['client_id']);
	}

	/**
	 * Delete a client document
	 */
	public function deleteDocument($documentId = null) {
		if (!$documentId) {
			redirect('clients');
			return;
		}
		if (!hasPermission('clients.update')) {
			flash('client_error', 'You do not have permission to delete documents', 'alert-danger');
			redirect('clients');
			return;
		}
		$document = $this->clientDocumentModel->getById((int)$documentId);
		if (!$document) {
			flash('client_error', 'Document not found.', 'alert-danger');
			redirect('clients');
			return;
		}
		$absPath = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/') . $document['file_path'];
		if (file_exists($absPath)) {
			@unlink($absPath);
		}
		if ($this->clientDocumentModel->delete((int)$documentId)) {
			flash('client_success', 'Document deleted successfully.');
		} else {
			flash('client_error', 'Error deleting document.', 'alert-danger');
		}
		redirect('clients/viewClient/' . (int)$document['client_id']);
	}

	private function formatFileSize($bytes) {
		if (!is_numeric($bytes) || $bytes < 0) {
			return '0 B';
		}
		$units = ['B','KB','MB','GB','TB'];
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);
		return round($bytes, 2) . ' ' . $units[$pow];
	}

    /**
     * Upload client contract
     */
    public function uploadContract($clientId) {
        if (!hasPermission('clients.update')) {
            flash('client_error', 'You do not have permission to upload contracts', 'alert-danger');
            redirect('clients/viewClient/' . (int)$clientId);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('clients/viewClient/' . (int)$clientId);
        }

        if (!isset($_FILES['contract']) || $_FILES['contract']['error'] !== UPLOAD_ERR_OK) {
            $err = $_FILES['contract']['error'] ?? 'no_file';
            error_log('Contract upload: initial upload error. client_id=' . (int)$clientId . ' err=' . print_r($err, true));
            flash('client_error', 'Error uploading contract. Please try again.', 'alert-danger');
            redirect('clients/viewClient/' . (int)$clientId);
            return;
        }

        $file = $_FILES['contract'];

        // Validate file size (10MB max)
        if ($file['size'] > 10 * 1024 * 1024) {
            error_log('Contract upload: file too large (' . (int)$file['size'] . ' bytes) client_id=' . (int)$clientId);
            flash('client_error', 'File size exceeds 10MB limit.', 'alert-danger');
            redirect('clients/viewClient/' . (int)$clientId);
            return;
        }

        // Validate file type
        $allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $allowedTypes, true)) {
            error_log('Contract upload: invalid extension .' . $fileExt . ' client_id=' . (int)$clientId . ' name=' . ($file['name'] ?? ''));
            flash('client_error', 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes), 'alert-danger');
            redirect('clients/viewClient/' . (int)$clientId);
            return;
        }

        // Resolve document root and ensure upload directory
        $docRoot = (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'])
            ? rtrim($_SERVER['DOCUMENT_ROOT'], '/')
            : (APPROOT . '/public');
        $uploadDir = $docRoot . '/uploads/clients/' . (int)$clientId . '/contracts/';
        if (!file_exists($uploadDir)) {
            $mkOk = @mkdir($uploadDir, 0775, true);
            if (!$mkOk) {
                $lastErr = error_get_last();
                error_log('Contract upload: mkdir failed at ' . $uploadDir . ' err=' . ($lastErr['message'] ?? 'unknown'));
                flash('client_error', 'Server cannot create upload directory. Please contact admin.', 'alert-danger');
                redirect('clients/viewClient/' . (int)$clientId);
                return;
            }
        }
        if (!is_writable($uploadDir)) {
            error_log('Contract upload: directory not writable ' . $uploadDir . ' perms=' . substr(sprintf('%o', fileperms($uploadDir)), -4));
            flash('client_error', 'Upload directory is not writable. Please contact admin.', 'alert-danger');
            redirect('clients/viewClient/' . (int)$clientId);
            return;
        }

        // Unique filename
        $safeBaseName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file['name']);
        $fileName = uniqid('contract_') . '_' . $safeBaseName;
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $lastErr = error_get_last();
            error_log('Contract upload: move_uploaded_file failed. tmp=' . ($file['tmp_name'] ?? '') . ' dest=' . $filePath . ' dir_writable=' . (int)is_writable($uploadDir) . ' err=' . ($lastErr['message'] ?? 'none'));
            flash('client_error', 'Error saving uploaded file.', 'alert-danger');
            redirect('clients/viewClient/' . (int)$clientId);
            return;
        }

        // Public path to serve the file
        $publicPath = '/uploads/clients/' . (int)$clientId . '/contracts/' . $fileName;

        $fileData = [
            'name' => $file['name'],
            'path' => $publicPath,
            'type' => $file['type'] ?? null,
            'size' => (int)$file['size']
        ];

        if ($this->contractModel->addContract((int)$clientId, $fileData, (int)($_SESSION['user_id'] ?? 0))) {
            flash('client_success', 'Contract uploaded successfully.');
        } else {
            // Rollback file if DB failed
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            error_log('Contract upload: DB insert failed for client_id=' . (int)$clientId . ' file=' . $publicPath);
            flash('client_error', 'Error saving contract metadata.', 'alert-danger');
        }

        redirect('clients/viewClient/' . (int)$clientId);
    }

    /**
     * Download a specific contract
     */
    public function downloadContract($contractId) {
        $contract = $this->contractModel->getContractById((int)$contractId);
        if (!$contract) {
            flash('client_error', 'Contract not found.', 'alert-danger');
            redirect('clients');
            return;
        }

        $absPath = $_SERVER['DOCUMENT_ROOT'] . $contract['file_path'];
        if (!file_exists($absPath)) {
            flash('client_error', 'File not found on server.', 'alert-danger');
            redirect('clients/viewClient/' . (int)$contract['client_id']);
            return;
        }

        // Clear output buffers
        while (ob_get_level()) { ob_end_clean(); }

        $ext = strtolower(pathinfo($contract['file_name'], PATHINFO_EXTENSION));
        $mime = 'application/octet-stream';
        $map = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png'
        ];
        if (isset($map[$ext])) { $mime = $map[$ext]; }

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $contract['file_name'] . '"');
        header('Content-Length: ' . filesize($absPath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($absPath);
        exit;
    }

    /**
     * Delete a contract
     */
    public function deleteContract($contractId) {
        if (!hasPermission('clients.update')) {
            flash('client_error', 'You do not have permission to delete contracts', 'alert-danger');
            redirect('clients');
            return;
        }

        $contract = $this->contractModel->getContractById((int)$contractId);
        if (!$contract) {
            flash('client_error', 'Contract not found.', 'alert-danger');
            redirect('clients');
            return;
        }

        $absPath = $_SERVER['DOCUMENT_ROOT'] . $contract['file_path'];
        if (file_exists($absPath)) {
            @unlink($absPath);
        }

        if ($this->contractModel->deleteContract((int)$contractId)) {
            flash('client_success', 'Contract deleted successfully.');
        } else {
            flash('client_error', 'Error deleting contract.', 'alert-danger');
        }

        redirect('clients/viewClient/' . (int)$contract['client_id']);
    }

    /**
     * Manage client domains (add/remove)
     */
    public function manageDomains($id = null) {
        // Permissions: update clients
        if (!hasPermission('clients.update')) {
            flash('client_error', 'You do not have permission to manage client domains', 'alert-danger');
            redirect('clients');
            return;
        }
        if (!$id) {
            redirect('clients');
            return;
        }
        // Load client
        $client = $this->clientModel->getClientById($id);
        if (!$client) {
            flash('client_error', 'Client not found', 'alert-danger');
            redirect('clients');
            return;
        }
        if (!class_exists('ClientDomain')) {
            require_once APPROOT . '/app/models/ClientDomain.php';
        }
        $clientDomainModel = new ClientDomain();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'add') {
                $domain = trim($_POST['domain'] ?? '');
                $isPrimary = !empty($_POST['is_primary']);
                if ($domain && $clientDomainModel->addDomain($id, $domain, $isPrimary)) {
                    flash('client_success', 'Domain added successfully');
                } else {
                    flash('client_error', 'Failed to add domain', 'alert-danger');
                }
            } elseif ($action === 'remove') {
                $domainId = (int)($_POST['domain_id'] ?? 0);
                if ($domainId && $clientDomainModel->removeById($domainId)) {
                    flash('client_success', 'Domain removed successfully');
                } else {
                    flash('client_error', 'Failed to remove domain', 'alert-danger');
                }
            }
            redirect('clients/viewClient/' . $id);
            return;
        }

        // GET fallback shows the view page
        redirect('clients/viewClient/' . $id);
    }
    
    /**
     * Create a new client
     */
    public function create() {
        // Check permission
        if (!hasPermission('clients.create')) {
            flash('client_error', 'You do not have permission to create clients', 'alert-danger');
            redirect('clients');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form
            
            // Sanitize POST data using modern approach
            $sanitizedPost = [];
            foreach ($_POST as $key => $value) {
                $sanitizedPost[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
            
            // Init data
            $data = [
                'title' => 'Create Client',
                'name' => $sanitizedPost['name'] ?? '',
                'contact_person' => $sanitizedPost['contact_person'] ?? '',
                'email' => $sanitizedPost['email'] ?? '',
                'phone' => $sanitizedPost['phone'] ?? '',
                'address' => $sanitizedPost['address'] ?? '',
                'industry' => $sanitizedPost['industry'] ?? '',
                'status' => $sanitizedPost['status'] ?? '',
                'notes' => $sanitizedPost['notes'] ?? '',
                'name_err' => '',
                'email_err' => ''
            ];
            
            // Validate name
            if (empty($data['name'])) {
                $data['name_err'] = 'Please enter client name';
            }
            
            // Validate email if provided
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['email_err'] = 'Please enter a valid email address';
            }
            
            // Make sure no errors
            if (empty($data['name_err']) && empty($data['email_err'])) {
                
                // Validated
                if ($this->clientModel->addClient($data)) {
                    flash('client_success', 'Client added successfully');
                    redirect('clients');
                } else {
                    flash('client_error', 'Failed to add client. Please check the form data and try again.', 'alert-danger');
                    $this->view('clients/create', $data);
                }
            } else {
                // Load view with errors
                $this->view('clients/create', $data);
            }
        } else {
            // Init data
            $data = [
                'title' => 'Create Client',
                'name' => '',
                'contact_person' => '',
                'email' => '',
                'phone' => '',
                'address' => '',
                'industry' => '',
                'status' => 'Active',
                'notes' => '',
                'name_err' => '',
                'email_err' => ''
            ];
            
            // Load view
            $this->view('clients/create', $data);
        }
    }
    
    /**
     * Edit existing client
     * 
     * @param int $id Client ID
     */
    public function edit($id = null) {
        // Check permission
        if (!hasPermission('clients.update')) {
            flash('client_error', 'You do not have permission to edit clients', 'alert-danger');
            redirect('clients');
            return;
        }
        
        if (!$id) {
            redirect('clients');
            return;
        }
        
        // Get client details
        $client = $this->clientModel->getClientById($id);
        
        if (!$client) {
            flash('client_error', 'Client not found', 'alert-danger');
            redirect('clients');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form
            
            // Sanitize POST data using modern approach
            $sanitizedPost = [];
            foreach ($_POST as $key => $value) {
                $sanitizedPost[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
            
            // Init data
            $data = [
                'id' => $id,
                'title' => 'Edit Client',
                'name' => $sanitizedPost['name'] ?? '',
                'contact_person' => $sanitizedPost['contact_person'] ?? '',
                'email' => $sanitizedPost['email'] ?? '',
                'phone' => $sanitizedPost['phone'] ?? '',
                'address' => $sanitizedPost['address'] ?? '',
                'industry' => $sanitizedPost['industry'] ?? '',
                'status' => $sanitizedPost['status'] ?? '',
                'notes' => $sanitizedPost['notes'] ?? '',
                'name_err' => '',
                'email_err' => ''
            ];
            
            // Validate name
            if (empty($data['name'])) {
                $data['name_err'] = 'Please enter client name';
            }
            
            // Validate email if provided
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['email_err'] = 'Please enter a valid email address';
            }
            
            // Make sure no errors
            if (empty($data['name_err']) && empty($data['email_err'])) {
                
                // Validated
                if ($this->clientModel->updateClient($data)) {
                    flash('client_success', 'Client updated successfully');
                    redirect('clients');
                } else {
                    flash('client_error', 'Failed to update client. Please check the form data and try again.', 'alert-danger');
                    $this->view('clients/edit', $data);
                }
            } else {
                // Load view with errors
                $this->view('clients/edit', $data);
            }
        } else {
            // Init data with client details
            $data = [
                'id' => $client['id'],
                'title' => 'Edit Client',
                'name' => $client['name'],
                'contact_person' => $client['contact_person'],
                'email' => $client['email'],
                'phone' => $client['phone'],
                'address' => $client['address'],
                'industry' => $client['industry'],
                'status' => $client['status'],
                'notes' => $client['notes'],
                'name_err' => '',
                'email_err' => ''
            ];
            
            // Load view
            $this->view('clients/edit', $data);
        }
    }
    
    /**
     * Delete client
     * 
     * @param int $id Client ID
     */
    public function delete($id = null) {
        // Check permission
        if (!hasPermission('clients.delete')) {
            flash('client_error', 'You do not have permission to delete clients', 'alert-danger');
            redirect('clients');
            return;
        }
        
        if (!$id) {
            redirect('clients');
            return;
        }
        
        // Get client details
        $client = $this->clientModel->getClientById($id);
        
        if (!$client) {
            flash('client_error', 'Client not found', 'alert-danger');
            redirect('clients');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Delete the client
            if ($this->clientModel->deleteClient($id)) {
                flash('client_success', 'Client deleted successfully');
                redirect('clients');
            } else {
                flash('client_error', 'Failed to delete client. The client may have associated data that prevents deletion.', 'alert-danger');
                redirect('clients/viewClient/' . $id);
            }
        } else {
            $data = [
                'title' => 'Delete Client',
                'client' => $client
            ];
            
            $this->view('clients/delete', $data);
        }
    }
    
    /**
     * Assign sites to client
     * 
     * @param int $id Client ID
     */
    public function assignSites($id = null) {
        // Check permission
        if (!hasPermission('clients.assign_sites')) {
            flash('client_error', 'You do not have permission to assign sites to clients', 'alert-danger');
            redirect('clients');
            return;
        }
        
        if (!$id) {
            redirect('clients');
            return;
        }
        
        // Get client details
        $client = $this->clientModel->getClientById($id);
        
        if (!$client) {
            flash('client_error', 'Client not found', 'alert-danger');
            redirect('clients');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process site assignments
            $siteIds = isset($_POST['site_ids']) ? $_POST['site_ids'] : [];
            $relationshipTypes = isset($_POST['relationship_types']) ? $_POST['relationship_types'] : [];
            
            if ($this->clientModel->updateSiteClientAssignments($id, $siteIds, $relationshipTypes)) {
                flash('client_success', 'Site assignments updated successfully');
                redirect('clients/viewClient/' . $id);
            } else {
                flash('client_error', 'Failed to update site assignments', 'alert-danger');
                redirect('clients/viewClient/' . $id);
            }
        } else {
            // Get all sites and current assignments
            $allSites = $this->siteModel->getAllSites();
            $assignedSites = $this->clientModel->getSiteClients($id);
            
            // Create array of assigned site IDs for easy checking
            $assignedSiteIds = [];
            foreach ($assignedSites as $site) {
                $assignedSiteIds[$site['id']] = $site['relationship_type'];
            }
            
            $data = [
                'title' => 'Assign Sites - ' . $client['name'],
                'client' => $client,
                'all_sites' => $allSites,
                'assigned_sites' => $assignedSiteIds
            ];
            
            $this->view('clients/assign_sites', $data);
        }
    }

	/**
	 * Add a client callback/reminder
	 */
	public function addCallback($clientId = null) {
		if (!isLoggedIn()) {
			redirect('users/login');
			return;
		}
		if (!hasPermission('clients.update')) {
			flash('client_error', 'You do not have permission to add callbacks', 'alert-danger');
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}
		if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$clientId) {
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}

		$title = trim($_POST['title'] ?? '');
		$notes = trim($_POST['notes'] ?? '');
		$remindAtRaw = trim($_POST['remind_at'] ?? '');

		if ($title === '' || $remindAtRaw === '') {
			flash('client_error', 'Title and reminder date/time are required.', 'alert-danger');
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}

		// Parse datetime-local input
		$remindTs = strtotime($remindAtRaw);
		if ($remindTs === false) {
			flash('client_error', 'Invalid reminder date/time.', 'alert-danger');
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}
		$remindAt = date('Y-m-d H:i:s', $remindTs);

		$insertId = $this->reminderModel->add([
			'entity_type' => 'client',
			'entity_id' => (int)$clientId,
			'title' => $title,
			'notes' => $notes,
			'remind_at' => $remindAt,
			'created_by' => (int)($_SESSION['user_id'] ?? 0),
			'notify_all' => !empty($_POST['notify_all']) ? 1 : 0,
		]);

		if (!$insertId) {
			flash('client_error', 'Failed to create callback.', 'alert-danger');
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}

        // Queue reminder email to creator
		try {
			$userModel = $this->model('User');
			$user = $userModel->getUserById((int)($_SESSION['user_id'] ?? 0));
			$client = $this->clientModel->getClientById((int)$clientId);
			$toEmail = $user && !empty($user['email']) ? $user['email'] : null;

			if ($toEmail) {
				require_once APPROOT . '/app/services/EmailService.php';
				$emailService = new EmailService();

                $subject = '[Reminder] Client follow-up: ' . ($client['name'] ?? 'Client') . ' - ' . $title;
				$link = URLROOT . '/clients/viewClient/' . (int)$clientId;
				$html = "
                <h2>Follow-up Reminder</h2>
				<p><strong>Client:</strong> " . htmlspecialchars($client['name'] ?? 'Client') . "</p>
				<p><strong>When:</strong> " . date('M j, Y g:i A', $remindTs) . "</p>
				<p><strong>Title:</strong> " . htmlspecialchars($title) . "</p>
				" . (!empty($notes) ? "<p><strong>Notes:</strong><br>" . nl2br(htmlspecialchars($notes)) . "</p>" : "") . "
				<p><a href=\"" . $link . "\">Open client</a></p>
				";
				$emailData = [
					'to' => $toEmail,
					'subject' => $subject,
					'html_body' => $html,
					'body' => strip_tags($html),
				];
				$queueId = $emailService->queueEmail($emailData, 3, new DateTime($remindAt));
				if ($queueId) {
					$this->reminderModel->setReminderQueueId((int)$insertId, (int)$queueId);
				}
			}
		} catch (Exception $e) {
			error_log('Queue callback reminder email failed: ' . $e->getMessage());
		}

		flash('client_success', 'Callback created. Reminder scheduled for ' . date('M j, Y g:i A', $remindTs) . '.');
		redirect('clients/viewClient/' . (int)$clientId);
	}

	/**
	 * Mark a callback as completed
	 */
	public function completeCallback($callbackId = null) {
		if (!isLoggedIn()) {
			redirect('users/login');
			return;
		}
		if (!hasPermission('clients.update')) {
			flash('client_error', 'You do not have permission to update callbacks', 'alert-danger');
			redirect('clients');
			return;
		}
		if (!$callbackId) {
			redirect('clients');
			return;
		}
		$cb = $this->reminderModel->getById((int)$callbackId);
		if (!$cb) {
			flash('client_error', 'Callback not found', 'alert-danger');
			redirect('clients');
			return;
		}
		$this->reminderModel->markCompleted((int)$callbackId);
		flash('client_success', 'Callback marked as completed.');
		$redirectClient = (int)($cb['entity_id'] ?? 0);
		redirect('clients/viewClient/' . $redirectClient);
	}

	/**
	 * View callbacks history for a client (completed and missed)
	 */
	public function callbacksHistory($clientId = null) {
		// Auth
		if (!isLoggedIn()) {
			redirect('users/login');
			return;
		}
		if (!hasPermission('clients.read')) {
			flash('client_error', 'You do not have permission to view callbacks', 'alert-danger');
			redirect('clients');
			return;
		}
		if (!$clientId) {
			redirect('clients');
			return;
		}

		// Client
		$client = $this->clientModel->getClientById((int)$clientId);
		if (!$client) {
			flash('client_error', 'Client not found', 'alert-danger');
			redirect('clients');
			return;
		}

		// Fetch follow-ups
		$all = [];
		try {
			$all = $this->reminderModel->getByEntity('client', (int)$clientId);
		} catch (Exception $e) {
			$all = [];
		}

		// Categorize
		$now = time();
		$completed = [];
		$missed = [];
		$pending = [];
		foreach ($all as $cb) {
			$status = $cb['status'] ?? 'Pending';
			$rt = isset($cb['remind_at']) ? strtotime($cb['remind_at']) : null;
			if ($status === 'Completed') {
				$completed[] = $cb;
			} elseif ($status === 'Pending' && $rt !== false && $rt !== null && $rt < $now) {
				$missed[] = $cb; // reminder in the past but not completed
			} else {
				$pending[] = $cb;
			}
		}

		// Filter param (?status=completed|missed|pending|all). Default: history (completed+missed)
		$filter = strtolower(trim($_GET['status'] ?? 'history'));
		switch ($filter) {
			case 'completed':
				$list = $completed;
				break;
			case 'missed':
				$list = $missed;
				break;
			case 'pending':
				$list = $pending;
				break;
			case 'all':
				$list = $all;
				break;
			case 'history':
			default:
				$list = array_merge($completed, $missed);
				break;
		}

		// Sort by remind_at desc for history by default
		usort($list, function($a, $b) {
			return strtotime($b['remind_at'] ?? '') <=> strtotime($a['remind_at'] ?? '');
		});

		$data = [
			'title' => 'Callback History - ' . ($client['name'] ?? 'Client'),
			'client' => $client,
			'callbacks' => $list,
			'counts' => [
				'all' => count($all),
				'completed' => count($completed),
				'missed' => count($missed),
				'pending' => count($pending)
			],
			'active_filter' => $filter
		];

		$this->view('clients/callbacks_history', $data);
	}

	/**
	 * Quick follow-up (client): creates a follow-up for the current user +24h
	 */
	public function addQuickCallback($clientId = null) {
		if (!isLoggedIn()) {
			redirect('users/login');
			return;
		}
		if (!hasPermission('clients.update')) {
			flash('client_error', 'You do not have permission to add follow-ups', 'alert-danger');
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}
		if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$clientId) {
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}

		$title = 'Follow-up';
		$notes = '';
		$remindTs = time() + 24 * 3600; // +24 hours
		$remindAt = date('Y-m-d H:i:s', $remindTs);

		$insertId = $this->reminderModel->add([
			'entity_type' => 'client',
			'entity_id' => (int)$clientId,
			'title' => $title,
			'notes' => $notes,
			'remind_at' => $remindAt,
			'created_by' => (int)($_SESSION['user_id'] ?? 0),
			'notify_all' => 0,
		]);

		if (!$insertId) {
			flash('client_error', 'Failed to create follow-up.', 'alert-danger');
			redirect('clients/viewClient/' . (int)$clientId);
			return;
		}

		// Queue reminder email to creator
		try {
			$userModel = $this->model('User');
			$user = $userModel->getUserById((int)($_SESSION['user_id'] ?? 0));
			$client = $this->clientModel->getClientById((int)$clientId);
			$toEmail = $user && !empty($user['email']) ? $user['email'] : null;

			if ($toEmail) {
				require_once APPROOT . '/app/services/EmailService.php';
				$emailService = new EmailService();

				$subject = '[Reminder] Client follow-up: ' . ($client['name'] ?? 'Client') . ' - ' . $title;
				$link = URLROOT . '/clients/viewClient/' . (int)$clientId;
				$html = "
				<h2>Follow-up Reminder</h2>
				<p><strong>Client:</strong> " . htmlspecialchars($client['name'] ?? 'Client') . "</p>
				<p><strong>When:</strong> " . date('M j, Y g:i A', $remindTs) . "</p>
				<p><strong>Title:</strong> " . htmlspecialchars($title) . "</p>
				<p><a href=\"" . $link . "\">Open client</a></p>
				";
				$emailData = [
					'to' => $toEmail,
					'subject' => $subject,
					'html_body' => $html,
					'body' => strip_tags($html),
				];
				$queueId = $emailService->queueEmail($emailData, 3, new DateTime($remindAt));
				if ($queueId) {
					$this->reminderModel->setReminderQueueId((int)$insertId, (int)$queueId);
				}
			}
		} catch (Exception $e) {
			error_log('Queue client quick follow-up email failed: ' . $e->getMessage());
		}

		flash('client_success', 'Quick follow-up created for ' . date('M j, Y g:i A', $remindTs) . '.');
		redirect('clients/viewClient/' . (int)$clientId);
	}

	/**
	 * Delete a client follow-up (admin/authorized users)
	 */
	public function deleteCallback($callbackId = null) {
		if (!isLoggedIn()) {
			redirect('users/login');
			return;
		}
		if (!hasPermission('clients.update')) {
			flash('client_error', 'You do not have permission to delete follow-ups', 'alert-danger');
			redirect('clients');
			return;
		}
		if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$callbackId) {
			redirect('clients');
			return;
		}
		$cb = $this->reminderModel->getById((int)$callbackId);
		if (!$cb || (($cb['entity_type'] ?? '') !== 'client')) {
			flash('client_error', 'Follow-up not found', 'alert-danger');
			redirect('clients');
			return;
		}
		$clientId = (int)($cb['entity_id'] ?? 0);
		$ok = $this->reminderModel->delete((int)$callbackId);
		if ($ok) {
			flash('client_success', 'Follow-up deleted.');
		} else {
			flash('client_error', 'Failed to delete follow-up.', 'alert-danger');
		}
		redirect('clients/callbacksHistory/' . $clientId);
	}

	/**
	 * Level.io Devices page for a client
	 * Shows all devices from linked Level.io groups
	 */
	public function levelDevices($clientId = null) {
		if (!hasPermission('clients.read')) {
			flash('client_error', 'You do not have permission to view clients', 'alert-danger');
			redirect('dashboard');
			return;
		}

		if (!$clientId) {
			redirect('clients');
			return;
		}

		$client = $this->clientModel->getClientById($clientId);
		if (!$client) {
			flash('client_error', 'Client not found', 'alert-danger');
			redirect('clients');
			return;
		}

		// Check if Level.io integration is enabled
		require_once APPROOT . '/app/services/LevelApiService.php';
		$levelApi = new LevelApiService();

		if (!$levelApi->isEnabled()) {
			flash('client_error', 'Level.io integration is not enabled. Please configure it in Admin Settings.', 'alert-warning');
			redirect('clients/viewClient/' . $clientId);
			return;
		}

		// Get linked Level.io groups for this client
		$linkedGroups = $this->clientModel->getLevelGroups((int)$clientId);

		if (empty($linkedGroups)) {
			flash('client_error', 'This client has no Level.io groups linked. Link groups in Admin → Level.io Integration.', 'alert-warning');
			redirect('clients/viewClient/' . $clientId);
			return;
		}

		// Fetch devices from all linked groups
		$allDevices = [];
		$errors = [];

		foreach ($linkedGroups as $group) {
			$groupId = $group['level_group_id'] ?? '';
			$groupName = $group['level_group_name'] ?? $groupId;

			if (empty($groupId)) continue;

			try {
				$response = $levelApi->listDevices(1, 200, $groupId);
				$devices = $response['data'] ?? $response ?? [];

				foreach ($devices as $device) {
					$device['_group_name'] = $groupName;
					$device['_group_id'] = $groupId;
					$allDevices[] = $device;
				}
			} catch (Exception $e) {
				$errors[] = "Failed to fetch devices for group '{$groupName}': " . $e->getMessage();
				error_log("Level.io device fetch error for group {$groupId}: " . $e->getMessage());
			}
		}

		$data = [
			'title' => 'Level.io Devices - ' . $client['name'],
			'client' => $client,
			'devices' => $allDevices,
			'linked_groups' => $linkedGroups,
			'errors' => $errors
		];

		$this->view('clients/level_devices', $data);
	}

	/**
	 * Detailed view for a specific Level.io device
	 */
	public function levelDevice($clientId = null, $deviceId = null) {
		if (!hasPermission('clients.read')) {
			flash('client_error', 'You do not have permission to view clients', 'alert-danger');
			redirect('dashboard');
			return;
		}

		if (!$clientId || !$deviceId) {
			redirect('clients');
			return;
		}

		$client = $this->clientModel->getClientById($clientId);
		if (!$client) {
			flash('client_error', 'Client not found', 'alert-danger');
			redirect('clients');
			return;
		}

		require_once APPROOT . '/app/services/LevelApiService.php';
		$levelApi = new LevelApiService();

		if (!$levelApi->isEnabled()) {
			flash('client_error', 'Level.io integration is not enabled.', 'alert-warning');
			redirect('clients/viewClient/' . $clientId);
			return;
		}

		$linkedGroups = $this->clientModel->getLevelGroups((int)$clientId);
		if (empty($linkedGroups)) {
			flash('client_error', 'This client has no Level.io groups linked.', 'alert-warning');
			redirect('clients/viewClient/' . $clientId);
			return;
		}

		$requestedGroupId = trim((string)($_GET['group_id'] ?? ''));
		$selectedGroup = null;
		if ($requestedGroupId !== '') {
			foreach ($linkedGroups as $group) {
				if (($group['level_group_id'] ?? '') === $requestedGroupId) {
					$selectedGroup = $group;
					break;
				}
			}
			if ($selectedGroup === null) {
				flash('client_error', 'Selected group is not linked to this client.', 'alert-danger');
				redirect('clients/levelDevices/' . $clientId);
				return;
			}
		}

		try {
			$device = $levelApi->getDevice($deviceId);
		} catch (Exception $e) {
			flash('client_error', 'Unable to load device details: ' . $e->getMessage(), 'alert-danger');
			redirect('clients/levelDevices/' . $clientId);
			return;
		}

		$alerts = [];
		$alertsError = null;
		try {
			$alertResp = $levelApi->listDeviceAlerts($deviceId, 1, 100);
			$alerts = $alertResp['data'] ?? $alertResp ?? [];
		} catch (Exception $e) {
			$alertsError = $e->getMessage();
			error_log('Level.io device alerts error: ' . $e->getMessage());
		}

		$data = [
			'title' => 'Device Details - ' . ($device['name'] ?? $device['hostname'] ?? 'Device'),
			'client' => $client,
			'device' => $device,
			'alerts' => $alerts,
			'alerts_error' => $alertsError,
			'group' => $selectedGroup,
			'linked_groups' => $linkedGroups
		];

		$this->view('clients/level_device_detail', $data);
	}
}
?> 