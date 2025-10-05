<?php

class Sitevisits extends Controller {
    private $siteVisitModel;
    private $siteModel;
    private $userModel;
    private $attachmentModel;

    public function __construct() {
        if (!isLoggedIn()) {
            redirect('users/login');
        }
        $this->siteVisitModel = $this->model('SiteVisit');
        $this->siteModel = $this->model('Site');
        $this->userModel = $this->model('User');
        $this->attachmentModel = $this->model('SiteVisitAttachment');
    }

    /**
     * Show create form
     */
    public function create($siteId = null) {
        if (!$siteId) {
            $siteId = isset($_GET['site_id']) ? intval($_GET['site_id']) : null;
        }

        if (!$siteId) {
            flash('site_error', 'Missing site ID', 'alert-danger');
            redirect('sites');
            return;
        }

        $site = $this->siteModel->getSiteById($siteId);
        if (!$site) {
            flash('site_error', 'Site not found', 'alert-danger');
            redirect('sites');
            return;
        }

        // Build technician list (prefer role technician)
        $users = $this->userModel->getAllUsers();
        $technicians = [];
        foreach ($users as $u) {
            $role = strtolower($u['role'] ?? '');
            if ($role === 'technician') {
                $technicians[] = $u;
            }
        }
        if (empty($technicians)) {
            $technicians = $users;
        }

        // Suggest recent reasons
        $recentReasons = $this->siteVisitModel->getRecentReasons(10);
        // Load previous visits for linking
        $previousVisits = $this->siteVisitModel->getPreviousVisitsForSite($siteId, null, 50);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $data = [
                'title' => 'Log Site Visit',
                'site' => $site,
                'site_id' => $siteId,
                'visit_date' => trim($_POST['visit_date'] ?? date('Y-m-d\TH:i')),
                'report_title' => trim($_POST['report_title'] ?? ''),
                'summary' => trim($_POST['summary'] ?? ''),
                'reason' => trim($_POST['reason'] ?? ''),
                'recent_reasons' => $recentReasons,
                'previous_visits' => $previousVisits,
                'technician_id' => (int)($_POST['technician_id'] ?? ($_SESSION['user_id'] ?? 0)),
                'technicians' => $technicians,
                'visit_date_err' => '',
                'summary_err' => '',
                'reason_err' => '',
                'technician_err' => ''
            ];

            if (empty($data['visit_date'])) {
                $data['visit_date_err'] = 'Visit date is required';
            }
            if (empty($data['summary'])) {
                $data['summary_err'] = 'Please enter what was done on site';
            }
            if (empty($data['technician_id'])) {
                $data['technician_err'] = 'Please select a technician';
            }

            if (empty($data['visit_date_err']) && empty($data['summary_err']) && empty($data['technician_err'])) {
                $payload = [
                    'site_id' => $siteId,
                    'technician_id' => (int)$data['technician_id'],
                    'visit_date' => date('Y-m-d H:i:s', strtotime($data['visit_date'])),
                    'title' => $data['report_title'],
                    'summary' => $data['summary'],
                    'reason' => $data['reason'] ?: null,
                    'previous_visit_id' => isset($_POST['previous_visit_id']) && (int)$_POST['previous_visit_id'] > 0 ? (int)$_POST['previous_visit_id'] : null
                ];

                if ($this->siteVisitModel->addVisit($payload)) {
                    flash('site_success', 'Visit report saved');
                    redirect('/sites/viewSite/' . $siteId);
                    return;
                } else {
                    flash('site_error', 'Failed to save visit report', 'alert-danger');
                }
            }

            $this->view('sitevisits/create', $data);
        } else {
            $defaultTechId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
            $data = [
                'title' => 'Log Site Visit',
                'site' => $site,
                'site_id' => $siteId,
                'visit_date' => date('Y-m-d\TH:i'),
                'report_title' => '',
                'summary' => '',
                'reason' => '',
                'recent_reasons' => $recentReasons,
                'previous_visits' => $previousVisits,
                'technician_id' => $defaultTechId,
                'technicians' => $technicians,
                'visit_date_err' => '',
                'summary_err' => '',
                'reason_err' => '',
                'technician_err' => ''
            ];
            $this->view('sitevisits/create', $data);
        }
    }

    /**
     * View a visit (preview)
     */
    public function show($id = null) {
        if (!$id) {
            redirect('/sites');
            return;
        }

        $visit = $this->siteVisitModel->getVisitById((int)$id);
        if (!$visit) {
            flash('site_error', 'Visit not found', 'alert-danger');
            redirect('/sites');
            return;
        }

        $site = $this->siteModel->getSiteById((int)$visit['site_id']);
        // Load related visits if a reason exists
        $relatedVisits = [];
        if (!empty($visit['reason'])) {
            $relatedVisits = $this->siteVisitModel->getRelatedByReason($visit['reason'], (int)$visit['site_id'], 10);
        }

        // Build previous chain and immediate nexts for a daisy chain view
        $previousChain = $this->siteVisitModel->getPreviousChain((int)$id, 20);
        $nextVisits = $this->siteVisitModel->getNextVisits((int)$id, 10);

        // Load attachments
        $attachments = $this->attachmentModel->getByVisit((int)$id);

        $data = [
            'title' => 'Visit Preview',
            'visit' => $visit,
            'site' => $site,
            'related_visits' => $relatedVisits,
            'previous_chain' => $previousChain,
            'next_visits' => $nextVisits,
            'attachments' => $attachments
        ];
        $this->view('sitevisits/view', $data);
    }

    /**
     * Upload attachment for a site visit
     */
    public function uploadAttachment($visitId) {
        if (!isLoggedIn()) {
            redirect('users/login');
        }
        $visitId = (int)$visitId;
        $visit = $this->siteVisitModel->getVisitById($visitId);
        if (!$visit) {
            flash('site_error', 'Visit not found', 'alert-danger');
            redirect('/sites');
            return;
        }
        // Permission: technician who created it or admin
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        if (!$isAdmin && $currentUserId !== (int)$visit['technician_id']) {
            flash('site_error', 'You do not have permission to add attachments for this visit', 'alert-danger');
            redirect('/sitevisits/show/' . $visitId);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/sitevisits/show/' . $visitId);
        }

        if (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
            flash('site_error', 'Error uploading file. Please try again.', 'alert-danger');
            redirect('/sitevisits/show/' . $visitId);
            return;
        }

        $file = $_FILES['attachment'];
        if ($file['size'] > 10 * 1024 * 1024) {
            flash('site_error', 'File size exceeds 10MB limit.', 'alert-danger');
            redirect('/sitevisits/show/' . $visitId);
            return;
        }
        $allowed = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            flash('site_error', 'Invalid file type. Allowed: ' . implode(', ', $allowed), 'alert-danger');
            redirect('/sitevisits/show/' . $visitId);
            return;
        }

        $docRoot = (isset($_SERVER['DOCUMENT_ROOT']) && $_SERVER['DOCUMENT_ROOT'])
            ? rtrim($_SERVER['DOCUMENT_ROOT'], '/')
            : (APPROOT . '/public');
        $uploadDir = $docRoot . '/uploads/sitevisits/' . $visitId . '/';
        if (!file_exists($uploadDir)) {
            if (!@mkdir($uploadDir, 0775, true)) {
                flash('site_error', 'Server cannot create upload directory.', 'alert-danger');
                redirect('/sitevisits/show/' . $visitId);
                return;
            }
        }
        if (!is_writable($uploadDir)) {
            flash('site_error', 'Upload directory is not writable.', 'alert-danger');
            redirect('/sitevisits/show/' . $visitId);
            return;
        }

        $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $file['name']);
        $fileName = uniqid('visit_') . '_' . $safeName;
        $dest = $uploadDir . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            flash('site_error', 'Error saving uploaded file.', 'alert-danger');
            redirect('/sitevisits/show/' . $visitId);
            return;
        }

        $publicPath = '/uploads/sitevisits/' . $visitId . '/' . $fileName;
        $fileData = [
            'name' => $file['name'],
            'path' => $publicPath,
            'type' => $file['type'] ?? null,
            'size' => (int)$file['size']
        ];
        if ($this->attachmentModel->add($visitId, $fileData, $currentUserId)) {
            flash('site_success', 'Attachment uploaded successfully.');
        } else {
            @unlink($dest);
            flash('site_error', 'Error saving attachment metadata.', 'alert-danger');
        }
        redirect('/sitevisits/show/' . $visitId);
    }

    public function downloadAttachment($attachmentId) {
        $attachment = $this->attachmentModel->getById((int)$attachmentId);
        if (!$attachment) {
            flash('site_error', 'Attachment not found', 'alert-danger');
            redirect('/sites');
            return;
        }
        $absPath = $_SERVER['DOCUMENT_ROOT'] . $attachment['file_path'];
        if (!file_exists($absPath)) {
            flash('site_error', 'File not found on server.', 'alert-danger');
            redirect('/sitevisits/show/' . (int)$attachment['visit_id']);
            return;
        }
        while (ob_get_level()) { ob_end_clean(); }
        $ext = strtolower(pathinfo($attachment['file_name'], PATHINFO_EXTENSION));
        $mime = 'application/octet-stream';
        $map = [
            'pdf'=>'application/pdf','doc'=>'application/msword','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'=>'application/vnd.ms-excel','xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif'
        ];
        if (isset($map[$ext])) { $mime = $map[$ext]; }
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $attachment['file_name'] . '"');
        header('Content-Length: ' . filesize($absPath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($absPath);
        exit;
    }

    public function deleteAttachment($attachmentId) {
        if (!isLoggedIn()) { redirect('users/login'); }
        $attachment = $this->attachmentModel->getById((int)$attachmentId);
        if (!$attachment) {
            flash('site_error', 'Attachment not found', 'alert-danger');
            redirect('/sites');
            return;
        }
        // Permission: creator or admin
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        if (!$isAdmin && $currentUserId !== (int)($attachment['uploaded_by'] ?? 0)) {
            flash('site_error', 'You do not have permission to delete this file', 'alert-danger');
            redirect('/sitevisits/show/' . (int)$attachment['visit_id']);
            return;
        }
        $absPath = $_SERVER['DOCUMENT_ROOT'] . $attachment['file_path'];
        if (file_exists($absPath)) { @unlink($absPath); }
        if ($this->attachmentModel->delete((int)$attachmentId)) {
            flash('site_success', 'Attachment deleted');
        } else {
            flash('site_error', 'Error deleting attachment', 'alert-danger');
        }
        redirect('/sitevisits/show/' . (int)$attachment['visit_id']);
    }

    /**
     * Edit a visit (only the creator/technician can edit)
     */
    public function edit($id = null) {
        if (!$id) {
            redirect('/sites');
            return;
        }

        $visit = $this->siteVisitModel->getVisitById((int)$id);
        if (!$visit) {
            flash('site_error', 'Visit not found', 'alert-danger');
            redirect('/sites');
            return;
        }

        // Permission check: only the technician who logged it (creator) can edit; admins override
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        if (!$isAdmin && $currentUserId !== (int)$visit['technician_id']) {
            flash('site_error', 'You do not have permission to edit this visit', 'alert-danger');
            redirect('/sitevisits/show/' . (int)$id);
            return;
        }

        $site = $this->siteModel->getSiteById((int)$visit['site_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $data = [
                'id' => (int)$id,
                'site' => $site,
                'visit' => $visit,
                'visit_date' => trim($_POST['visit_date'] ?? date('Y-m-d\TH:i')),
                'report_title' => trim($_POST['report_title'] ?? ''),
                'summary' => trim($_POST['summary'] ?? ''),
                'reason' => trim($_POST['reason'] ?? ($visit['reason'] ?? '')),
                'previous_visit_id' => isset($_POST['previous_visit_id']) ? (int)$_POST['previous_visit_id'] : 0,
                'visit_date_err' => '',
                'summary_err' => '',
                'reason_err' => ''
            ];

            if (empty($data['visit_date'])) {
                $data['visit_date_err'] = 'Visit date is required';
            }
            if (empty($data['summary'])) {
                $data['summary_err'] = 'Please enter what was done on site';
            }

            if (empty($data['visit_date_err']) && empty($data['summary_err'])) {
                $payload = [
                    'id' => (int)$id,
                    'visit_date' => date('Y-m-d H:i:s', strtotime($data['visit_date'])),
                    'title' => $data['report_title'],
                    'summary' => $data['summary'],
                    'reason' => $data['reason'] ?: null,
                    'previous_visit_id' => $data['previous_visit_id'] > 0 ? $data['previous_visit_id'] : null
                ];

                if ($this->siteVisitModel->updateVisit($payload)) {
                    flash('site_success', 'Visit updated successfully');
                    redirect('/sitevisits/show/' . (int)$id);
                    return;
                } else {
                    flash('site_error', 'Failed to update visit', 'alert-danger');
                }
            }

            $this->view('sitevisits/edit', $data);
        } else {
            // Fetch recent reasons for suggestions
            $recentReasons = $this->siteVisitModel->getRecentReasons(10);
            $data = [
                'id' => (int)$id,
                'site' => $site,
                'visit' => $visit,
                'visit_date' => date('Y-m-d\TH:i', strtotime($visit['visit_date'])),
                'report_title' => $visit['title'] ?? '',
                'summary' => $visit['summary'] ?? '',
                'reason' => $visit['reason'] ?? '',
                'recent_reasons' => $recentReasons,
                'previous_visits' => $this->siteVisitModel->getPreviousVisitsForSite((int)$visit['site_id'], (int)$id, 50),
                'visit_date_err' => '',
                'summary_err' => '',
                'reason_err' => ''
            ];
            $this->view('sitevisits/edit', $data);
        }
    }
}

?>


