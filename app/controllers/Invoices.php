<?php

class Invoices extends Controller {
    private $invoice;
    private $supplier;
    private $user;
    private $setting;
    
    public function __construct() {
        // Use the model() method to load model classes properly
        $this->invoice = $this->model('Invoice');
        $this->supplier = $this->model('Supplier');
        $this->user = $this->model('User');
        $this->setting = $this->model('Setting');
        
        // Check if user is logged in
        if (!isLoggedIn()) {
            redirect('/auth/login');
        }
    }
    
    /**
     * Display invoices list
     */
    public function index() {
        // Get invoices from the model
        $status = isset($_GET['status']) ? sanitize_input($_GET['status']) : null;
        
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $searchTerm = sanitize_input($_GET['search']);
            $invoices = $this->invoice->searchInvoices($searchTerm);
            $pageTitle = "Search Results for: " . htmlspecialchars($searchTerm);
        } else {
            $invoices = $this->invoice->getAllInvoices($status);
            $pageTitle = "Invoices";
            
            if ($status) {
                $pageTitle .= " - " . ucfirst($status);
            }
        }
        
        // Get invoice statistics
        $stats = $this->invoice->getStatistics();
        
        // Get currency settings
        $currency = $this->setting->getCurrency();
        
        // Load view
        $this->view('invoices/index', [
            'pageTitle' => $pageTitle,
            'invoices' => $invoices,
            'stats' => $stats,
            'status' => $status,
            'currency' => $currency
        ]);
    }
    
    /**
     * Display form to add a new invoice
     */
    public function add() {
        // Get all suppliers for the dropdown
        $suppliers = $this->supplier->getAllSuppliers('active');
        
        // Get currency settings
        $currency = $this->setting->getCurrency();
        
        $this->view('invoices/add', [
            'pageTitle' => 'Add New Invoice',
            'formAction' => '/invoices/create',
            'suppliers' => $suppliers,
            'currency' => $currency
        ]);
    }
    
    /**
     * Process form submission to create a new invoice
     */
    public function create() {
        try {
            // Validate form data
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                redirect('/invoices');
            }
            
            $data = [
                'invoice_number' => sanitize_input($_POST['invoice_number'] ?? ''),
                'supplier_id' => isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0,
                'invoice_date' => sanitize_input($_POST['invoice_date'] ?? ''),
                'due_date' => sanitize_input($_POST['due_date'] ?? null),
                'total_amount' => isset($_POST['total_amount']) ? (float)str_replace(',', '', $_POST['total_amount']) : 0,
                'status' => sanitize_input($_POST['status'] ?? 'pending'),
                'notes' => sanitize_input($_POST['notes'] ?? ''),
                'created_by' => $_SESSION['user_id']
            ];
            
            // If status is paid, add payment details
            if ($data['status'] === 'paid') {
                $data['payment_date'] = sanitize_input($_POST['payment_date'] ?? '');
                $data['payment_reference'] = sanitize_input($_POST['payment_reference'] ?? '');
            }
            
            // Validation
            if (empty($data['invoice_number'])) {
                $_SESSION['error'] = "Invoice number is required";
                $_SESSION['form_data'] = $data;
                redirect('/invoices/add');
            }
            
            if ($data['supplier_id'] <= 0) {
                $_SESSION['error'] = "Please select a supplier";
                $_SESSION['form_data'] = $data;
                redirect('/invoices/add');
            }
            
            if (empty($data['invoice_date'])) {
                $_SESSION['error'] = "Invoice date is required";
                $_SESSION['form_data'] = $data;
                redirect('/invoices/add');
            }
            
            if ($data['total_amount'] <= 0) {
                $_SESSION['error'] = "Total amount must be greater than zero";
                $_SESSION['form_data'] = $data;
                redirect('/invoices/add');
            }
            
            // Date validation
            if (!$this->validateDate($data['invoice_date'])) {
                $_SESSION['error'] = "Invalid invoice date format";
                $_SESSION['form_data'] = $data;
                redirect('/invoices/add');
            }
            
            if (!empty($data['due_date']) && !$this->validateDate($data['due_date'])) {
                $_SESSION['error'] = "Invalid due date format";
                $_SESSION['form_data'] = $data;
                redirect('/invoices/add');
            }
            
            // Validate payment date if status is paid
            if ($data['status'] === 'paid') {
                if (empty($data['payment_date']) || !$this->validateDate($data['payment_date'])) {
                    $_SESSION['error'] = "Valid payment date is required for paid invoices";
                    $_SESSION['form_data'] = $data;
                    redirect('/invoices/add');
                }
            }
            
            // Create invoice
            $result = $this->invoice->create($data);
            
            if ($result) {
                $_SESSION['success'] = "Invoice added successfully";
                redirect('/invoices');
            } else {
                $_SESSION['error'] = "Failed to add invoice";
                $_SESSION['form_data'] = $data;
                redirect('/invoices/add');
            }
        } catch (Exception $e) {
            error_log('Create Invoice Error: ' . $e->getMessage());
            $_SESSION['error'] = "An error occurred. Please try again.";
            redirect('/invoices/add');
        }
    }
    
    /**
     * Display form to edit an invoice
     */
    public function edit($id = null) {
        // Debug information
        error_log("\n\n===== EDIT INVOICE START =====");
        error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Request URI: " . $_SERVER['REQUEST_URI']);
        error_log("Requested ID: " . ($id ?? 'null'));
        
        // Validate ID parameter
        if (!$id || !is_numeric($id)) {
            error_log("Invalid invoice ID: " . ($id ?? 'null'));
            $_SESSION['error'] = "Invalid invoice ID provided. Please select a valid invoice to edit.";
            redirect('/invoices');
        }
        
        // Get invoice data
        error_log("Fetching invoice with ID: " . $id);
        $invoice = $this->invoice->getInvoiceById($id);
        
        if (!$invoice) {
            error_log("Invoice not found with ID: " . $id);
            $_SESSION['error'] = "Invoice not found. It may have been deleted or doesn't exist.";
            redirect('/invoices');
        }
        
        error_log("Invoice found, proceeding to load edit form");
        
        // Get suppliers for dropdown
        $suppliers = $this->supplier->getAllSuppliers();
        
        // Get currency settings
        $currency = $this->setting->getCurrency();
        
        // Load view
        $this->view('invoices/edit', [
            'pageTitle' => 'Edit Invoice: ' . htmlspecialchars($invoice['invoice_number']),
            'invoice' => $invoice,
            'suppliers' => $suppliers,
            'formAction' => '/invoices/update',
            'currency' => $currency
        ]);
        
        error_log("===== EDIT INVOICE END =====\n\n");
    }
    
    /**
     * Process form submission to update an invoice
     */
    public function update() {
        try {
            // Validate form data
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                redirect('/invoices');
            }
            
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if (!$id) {
                $_SESSION['error'] = "Invalid invoice ID";
                redirect('/invoices');
            }
            
            $data = [
                'id' => $id,
                'invoice_number' => sanitize_input($_POST['invoice_number'] ?? ''),
                'supplier_id' => isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0,
                'invoice_date' => sanitize_input($_POST['invoice_date'] ?? ''),
                'due_date' => sanitize_input($_POST['due_date'] ?? null),
                'total_amount' => isset($_POST['total_amount']) ? (float)str_replace(',', '', $_POST['total_amount']) : 0,
                'status' => sanitize_input($_POST['status'] ?? 'pending'),
                'notes' => sanitize_input($_POST['notes'] ?? '')
            ];
            
            // If status is paid, add payment details
            if ($data['status'] === 'paid') {
                $data['payment_date'] = sanitize_input($_POST['payment_date'] ?? '');
                $data['payment_reference'] = sanitize_input($_POST['payment_reference'] ?? '');
            } else {
                $data['payment_date'] = null;
                $data['payment_reference'] = null;
            }
            
            // Validation
            if (empty($data['invoice_number'])) {
                $_SESSION['error'] = "Invoice number is required";
                $_SESSION['form_data'] = $data;
                redirect('/invoices/edit/' . $id);
            }
            
            if ($data['supplier_id'] <= 0) {
                $_SESSION['error'] = "Please select a supplier";
                $_SESSION['form_data'] = $data;
                redirect('/invoices/edit/' . $id);
            }
            
            if (empty($data['invoice_date'])) {
                $_SESSION['error'] = "Invoice date is required";
                $_SESSION['form_data'] = $data;
                redirect('/invoices/edit/' . $id);
            }
            
            if ($data['total_amount'] <= 0) {
                $_SESSION['error'] = "Total amount must be greater than zero";
                $_SESSION['form_data'] = $data;
                redirect('/invoices/edit/' . $id);
            }
            
            // Date validation
            if (!$this->validateDate($data['invoice_date'])) {
                $_SESSION['error'] = "Invalid invoice date format";
                $_SESSION['form_data'] = $data;
                redirect('/invoices/edit/' . $id);
            }
            
            if (!empty($data['due_date']) && !$this->validateDate($data['due_date'])) {
                $_SESSION['error'] = "Invalid due date format";
                $_SESSION['form_data'] = $data;
                redirect('/invoices/edit/' . $id);
            }
            
            // Validate payment date if status is paid
            if ($data['status'] === 'paid') {
                if (empty($data['payment_date']) || !$this->validateDate($data['payment_date'])) {
                    $_SESSION['error'] = "Valid payment date is required for paid invoices";
                    $_SESSION['form_data'] = $data;
                    redirect('/invoices/edit/' . $id);
                }
            }
            
            // Update invoice
            $result = $this->invoice->update($data);
            
            if ($result) {
                $_SESSION['success'] = "Invoice updated successfully";
                redirect('/invoices');
            } else {
                $_SESSION['error'] = "Failed to update invoice";
                $_SESSION['form_data'] = $data;
                redirect('/invoices/edit/' . $id);
            }
        } catch (Exception $e) {
            error_log('Update Invoice Error: ' . $e->getMessage());
            $_SESSION['error'] = "An error occurred. Please try again.";
            redirect('/invoices');
        }
    }
    
    /**
     * Display invoice details
     */
    public function viewDetail($id = null) {
        // Validate ID parameter
        if (!$id || !is_numeric($id)) {
            $_SESSION['error'] = "Invalid invoice ID provided. Please select a valid invoice.";
            redirect('/invoices');
        }
        
        // Get invoice data
        $invoice = $this->invoice->getInvoiceById($id);
        
        if (!$invoice) {
            $_SESSION['error'] = "Invoice not found. It may have been deleted or doesn't exist.";
            redirect('/invoices');
        }
        
        // Get currency settings
        $currency = $this->setting->getCurrency();
        
        // Get invoice documents
        $documents = $this->invoice->getInvoiceDocuments($id);
        
        // Format file sizes
        if (!empty($documents)) {
            foreach ($documents as &$document) {
                $document['formatted_size'] = $this->formatFileSize($document['file_size']);
            }
        }
        
        $this->view('invoices/view', [
            'pageTitle' => 'Invoice: ' . htmlspecialchars($invoice['invoice_number']),
            'invoice' => $invoice,
            'documents' => $documents,
            'currency' => $currency
        ]);
    }
    
    /**
     * Process AJAX request to delete an invoice
     */
    public function deleteAjax() {
        try {
            // Check if it's an AJAX request
            if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                return;
            }
            
            // Get the raw input and decode JSON
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            // Validate invoice ID
            if (!isset($data['id']) || empty($data['id'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invoice ID is required']);
                return;
            }
            
            // Convert ID to integer and validate it's positive
            $id = filter_var($data['id'], FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid invoice ID format']);
                return;
            }
            
            // Delete the invoice
            $result = $this->invoice->delete($id);
            
            // Return response
            header('Content-Type: application/json');
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Invoice deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete invoice']);
            }
        } catch (Exception $e) {
            error_log('Delete Invoice AJAX Error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
        }
    }
    
    /**
     * Process request to delete an invoice
     */
    public function delete() {
        try {
            error_log("\n\n===== DELETE INVOICE REQUEST START =====");
            error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
            error_log("Request URI: " . $_SERVER['REQUEST_URI']);
            error_log("POST data: " . print_r($_POST, true));
            error_log("GET data: " . print_r($_GET, true));
            
            // Get the ID from GET parameters
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            
            if (!$id) {
                error_log("No valid ID found in request");
                $_SESSION['error'] = "Invoice ID is required";
                redirect('/invoices');
            }
            
            error_log("Processing delete for invoice ID: " . $id);
            
            // Check if invoice exists before attempting to delete
            $invoice = $this->invoice->getInvoiceById($id);
            if (!$invoice) {
                error_log("Invoice not found with ID: " . $id);
                $_SESSION['error'] = "Invoice not found";
                redirect('/invoices');
            }
            
            error_log("Invoice found, proceeding with deletion");
            
            // Delete the invoice
            $result = $this->invoice->delete($id);
            
            if ($result) {
                error_log("Invoice deleted successfully");
                $_SESSION['success'] = "Invoice deleted successfully";
            } else {
                error_log("Failed to delete invoice");
                $_SESSION['error'] = "Failed to delete invoice";
            }
            
            error_log("===== DELETE INVOICE REQUEST END =====\n\n");
            redirect('/invoices');
            
        } catch (Exception $e) {
            error_log("Delete Invoice Error: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred. Please try again.";
            redirect('/invoices');
        }
    }
    
    /**
     * Process request to mark an invoice as paid
     */
    public function markAsPaid() {
        try {
            error_log("\n\n===== MARK AS PAID REQUEST START =====");
            error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
            error_log("Request URI: " . $_SERVER['REQUEST_URI']);
            error_log("POST data: " . print_r($_POST, true));
            error_log("GET data: " . print_r($_GET, true));
            
            // Validate request method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                error_log("Invalid request method");
                redirect('/invoices');
            }
            
            // Get the ID from GET parameters first, then POST
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id && isset($_POST['id']) && !empty($_POST['id'])) {
                $id = (int)$_POST['id'];
            }
            
            if (!$id) {
                error_log("No valid ID found in request");
                $_SESSION['error'] = "Invoice ID is required";
                redirect('/invoices');
            }
            
            error_log("Processing mark as paid for invoice ID: " . $id);
            
            // Get the payment details
            $payment_date = isset($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d');
            $payment_reference = isset($_POST['payment_reference']) ? $_POST['payment_reference'] : null;
            
            // Check if invoice exists and is in pending status
            $invoice = $this->invoice->getInvoiceById($id);
            if (!$invoice) {
                error_log("Invoice not found with ID: " . $id);
                $_SESSION['error'] = "Invoice not found";
                redirect('/invoices');
            }
            
            if ($invoice['status'] !== 'pending') {
                error_log("Cannot mark as paid - invoice is not in pending status");
                $_SESSION['error'] = "Only pending invoices can be marked as paid";
                redirect('/invoices');
            }
            
            error_log("Invoice found and is pending, proceeding with payment");
            
            // Update invoice status to paid
            $data = [
                'id' => $id,
                'status' => 'paid',
                'payment_date' => $payment_date,
                'payment_reference' => $payment_reference
            ];
            
            $result = $this->invoice->markAsPaid($data);
            
            if ($result) {
                error_log("Invoice marked as paid successfully");
                $_SESSION['success'] = "Invoice marked as paid successfully";
            } else {
                error_log("Failed to mark invoice as paid");
                $_SESSION['error'] = "Failed to mark invoice as paid";
            }
            
            error_log("===== MARK AS PAID REQUEST END =====\n\n");
            redirect('/invoices');
            
        } catch (Exception $e) {
            error_log("Mark as Paid Error: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred. Please try again.";
            redirect('/invoices');
        }
    }
    
    /**
     * Validate a date string
     * 
     * @param string $date Date string to validate
     * @param string $format Date format to check against
     * @return bool True if date is valid, false otherwise
     */
    private function validateDate(string $date, string $format = 'Y-m-d'): bool {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public function uploadDocument($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Check if file was uploaded
            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = 'Error uploading file. Please try again.';
                redirect('/invoices/viewDetail/' . $id);
                return;
            }

            $file = $_FILES['document'];
            
            // Validate file size (10MB max)
            if ($file['size'] > 10 * 1024 * 1024) {
                $_SESSION['error'] = 'File size exceeds 10MB limit.';
                redirect('/invoices/viewDetail/' . $id);
                return;
            }

            // Validate file type
            $allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($fileExt, $allowedTypes)) {
                $_SESSION['error'] = 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes);
                redirect('/invoices/viewDetail/' . $id);
                return;
            }

            // Create uploads directory if it doesn't exist
            $uploadDir = 'uploads/invoices/' . $id . '/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate unique filename
            $fileName = uniqid() . '_' . $file['name'];
            $filePath = $uploadDir . $fileName;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Save to database
                $fileData = [
                    'name' => $file['name'],
                    'path' => $filePath,
                    'type' => $file['type'],
                    'size' => $file['size']
                ];

                if ($this->invoice->uploadDocument($id, $fileData)) {
                    $_SESSION['success'] = 'Document uploaded successfully.';
                } else {
                    $_SESSION['error'] = 'Error saving document to database.';
                }
            } else {
                $_SESSION['error'] = 'Error moving uploaded file.';
            }
        }
        
        redirect('/invoices/viewDetail/' . $id);
    }

    public function downloadDocument($documentId) {
        $document = $this->invoice->getDocumentById($documentId);
        
        if ($document && file_exists($document['file_path'])) {
            // Clear any output buffers that might corrupt the file
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Get MIME type based on file extension
            $ext = strtolower(pathinfo($document['file_name'], PATHINFO_EXTENSION));
            $contentType = 'application/octet-stream'; // Default
            
            // Set proper MIME type based on file extension
            $mimeTypes = [
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png'
            ];
            
            if (array_key_exists($ext, $mimeTypes)) {
                $contentType = $mimeTypes[$ext];
            }
            
            // Set the headers
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . $document['file_name'] . '"');
            header('Content-Length: ' . filesize($document['file_path']));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Output the file in binary mode
            readfile($document['file_path']);
            exit;
        } else {
            $_SESSION['error'] = 'Document not found.';
            redirect('/invoices');
        }
    }

    public function deleteDocument($documentId) {
        $document = $this->invoice->getDocumentById($documentId);
        
        if ($document) {
            // Delete file from server
            if (file_exists($document['file_path'])) {
                unlink($document['file_path']);
            }
            
            // Delete from database
            if ($this->invoice->deleteDocument($documentId)) {
                $_SESSION['success'] = 'Document deleted successfully.';
            } else {
                $_SESSION['error'] = 'Error deleting document from database.';
            }
            
            redirect('/invoices/viewDetail/' . $document['invoice_id']);
        } else {
            $_SESSION['error'] = 'Document not found.';
            redirect('/invoices');
        }
    }

    /**
     * Format file size in human-readable format
     * 
     * @param int $bytes Raw file size in bytes
     * @return string Formatted file size (e.g., "2.5 MB")
     */
    private function formatFileSize($bytes) {
        if (!is_numeric($bytes) || $bytes < 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
} 