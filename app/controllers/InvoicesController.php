<?php

class InvoicesController extends Controller {
    private $invoice;
    private $supplier;
    private $user;
    
    public function __construct() {
        parent::__construct();
        $this->invoice = new Invoice();
        $this->supplier = new Supplier();
        $this->user = new User();
        
        // Check if user is logged in for all methods except for explicitly allowed ones
        $allowed = [];
        $this->user->requireLogin($allowed);
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
        
        // Load view
        $this->loadView('invoices/index', [
            'pageTitle' => $pageTitle,
            'invoices' => $invoices,
            'stats' => $stats,
            'status' => $status
        ]);
    }
    
    /**
     * Display form to add a new invoice
     */
    public function add() {
        // Get all active suppliers for dropdown
        $suppliers = $this->supplier->getAllSuppliers('active');
        
        $this->loadView('invoices/add', [
            'pageTitle' => 'Add New Invoice',
            'formAction' => '/invoices/create',
            'suppliers' => $suppliers
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
            
            // Start output buffering to catch any unexpected output
            ob_start();
            
            $data = [
                'invoice_number' => sanitize_input($_POST['invoice_number'] ?? ''),
                'supplier_id' => isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0,
                'invoice_date' => sanitize_input($_POST['invoice_date'] ?? ''),
                'due_date' => sanitize_input($_POST['due_date'] ?? ''),
                'total_amount' => isset($_POST['total_amount']) ? (float)str_replace(',', '', $_POST['total_amount']) : 0,
                'status' => sanitize_input($_POST['status'] ?? 'pending'),
                'payment_date' => !empty($_POST['payment_date']) ? sanitize_input($_POST['payment_date']) : null,
                'payment_reference' => sanitize_input($_POST['payment_reference'] ?? ''),
                'notes' => sanitize_input($_POST['notes'] ?? ''),
                'created_by' => $_SESSION['user_id']
            ];
            
            // Validation
            $errors = [];
            
            if (empty($data['invoice_number'])) {
                $errors[] = "Invoice number is required";
            }
            
            if ($data['supplier_id'] <= 0) {
                $errors[] = "Please select a valid supplier";
            }
            
            if (empty($data['invoice_date'])) {
                $errors[] = "Invoice date is required";
            } elseif (!$this->validateDate($data['invoice_date'])) {
                $errors[] = "Invalid invoice date format";
            }
            
            if (!empty($data['due_date']) && !$this->validateDate($data['due_date'])) {
                $errors[] = "Invalid due date format";
            }
            
            if ($data['total_amount'] <= 0) {
                $errors[] = "Total amount must be greater than zero";
            }
            
            // If status is paid, payment date should be provided
            if ($data['status'] === 'paid' && empty($data['payment_date'])) {
                $errors[] = "Payment date is required for paid invoices";
            }
            
            // Handle errors
            if (!empty($errors)) {
                $_SESSION['error'] = implode('<br>', $errors);
                $_SESSION['form_data'] = $data;
                
                // Clear output buffer before redirecting
                ob_end_clean();
                redirect('/invoices/add');
                return;
            }
            
            // Create invoice
            $result = $this->invoice->create($data);
            
            // Clear any buffered output
            ob_end_clean();
            
            if ($result) {
                $_SESSION['success'] = "Invoice added successfully";
                redirect('/invoices');
            } else {
                $_SESSION['error'] = "Failed to add invoice";
                $_SESSION['form_data'] = $data;
                redirect('/invoices/add');
            }
        } catch (Exception $e) {
            // Clear any buffered output
            if (ob_get_length()) ob_end_clean();
            
            error_log('Create Invoice Error: ' . $e->getMessage());
            $_SESSION['error'] = "An error occurred. Please try again.";
            redirect('/invoices/add');
        }
    }
    
    /**
     * Display form to edit an invoice
     */
    public function edit($id = null) {
        if (!$id) {
            redirect('/invoices');
        }
        
        // Get invoice data
        $invoice = $this->invoice->getInvoiceById($id);
        
        if (!$invoice) {
            $_SESSION['error'] = "Invoice not found";
            redirect('/invoices');
        }
        
        // Get all active suppliers for dropdown
        $suppliers = $this->supplier->getAllSuppliers('active');
        
        $this->loadView('invoices/edit', [
            'pageTitle' => 'Edit Invoice',
            'invoice' => $invoice,
            'suppliers' => $suppliers,
            'formAction' => '/invoices/update'
        ]);
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
            
            // Start output buffering to catch any unexpected output
            ob_start();
            
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if (!$id) {
                $_SESSION['error'] = "Invalid invoice ID";
                
                // Clear output buffer before redirecting
                ob_end_clean();
                redirect('/invoices');
                return;
            }
            
            $data = [
                'id' => $id,
                'invoice_number' => sanitize_input($_POST['invoice_number'] ?? ''),
                'supplier_id' => isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0,
                'invoice_date' => sanitize_input($_POST['invoice_date'] ?? ''),
                'due_date' => sanitize_input($_POST['due_date'] ?? ''),
                'total_amount' => isset($_POST['total_amount']) ? (float)str_replace(',', '', $_POST['total_amount']) : 0,
                'status' => sanitize_input($_POST['status'] ?? 'pending'),
                'payment_date' => !empty($_POST['payment_date']) ? sanitize_input($_POST['payment_date']) : null,
                'payment_reference' => sanitize_input($_POST['payment_reference'] ?? ''),
                'notes' => sanitize_input($_POST['notes'] ?? '')
            ];
            
            // Validation
            $errors = [];
            
            if (empty($data['invoice_number'])) {
                $errors[] = "Invoice number is required";
            }
            
            if ($data['supplier_id'] <= 0) {
                $errors[] = "Please select a valid supplier";
            }
            
            if (empty($data['invoice_date'])) {
                $errors[] = "Invoice date is required";
            } elseif (!$this->validateDate($data['invoice_date'])) {
                $errors[] = "Invalid invoice date format";
            }
            
            if (!empty($data['due_date']) && !$this->validateDate($data['due_date'])) {
                $errors[] = "Invalid due date format";
            }
            
            if ($data['total_amount'] <= 0) {
                $errors[] = "Total amount must be greater than zero";
            }
            
            // If status is paid, payment date should be provided
            if ($data['status'] === 'paid' && empty($data['payment_date'])) {
                $errors[] = "Payment date is required for paid invoices";
            }
            
            // Handle errors
            if (!empty($errors)) {
                $_SESSION['error'] = implode('<br>', $errors);
                $_SESSION['form_data'] = $data;
                
                // Clear output buffer before redirecting
                ob_end_clean();
                redirect('/invoices/edit/' . $id);
                return;
            }
            
            // Update invoice
            $result = $this->invoice->update($data);
            
            // Clear any buffered output
            ob_end_clean();
            
            if ($result) {
                $_SESSION['success'] = "Invoice updated successfully";
                redirect('/invoices');
            } else {
                $_SESSION['error'] = "Failed to update invoice";
                $_SESSION['form_data'] = $data;
                redirect('/invoices/edit/' . $id);
            }
        } catch (Exception $e) {
            // Clear any buffered output
            if (ob_get_length()) ob_end_clean();
            
            error_log('Update Invoice Error: ' . $e->getMessage());
            $_SESSION['error'] = "An error occurred. Please try again.";
            redirect('/invoices');
        }
    }
    
    /**
     * Display invoice details
     */
    public function view($id = null) {
        if (!$id) {
            redirect('/invoices');
        }
        
        // Get invoice data
        $invoice = $this->invoice->getInvoiceById($id);
        
        if (!$invoice) {
            $_SESSION['error'] = "Invoice not found";
            redirect('/invoices');
        }
        
        $this->loadView('invoices/view', [
            'pageTitle' => 'Invoice: ' . htmlspecialchars($invoice['invoice_number']),
            'invoice' => $invoice
        ]);
    }
    
    /**
     * Process request to delete an invoice
     */
    public function delete() {
        try {
            // Validate request
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                redirect('/invoices');
            }
            
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if (!$id) {
                $_SESSION['error'] = "Invalid invoice ID";
                redirect('/invoices');
            }
            
            // Delete invoice
            $result = $this->invoice->delete($id);
            
            if ($result) {
                $_SESSION['success'] = "Invoice deleted successfully";
            } else {
                $_SESSION['error'] = "Failed to delete invoice";
            }
            
            redirect('/invoices');
        } catch (Exception $e) {
            error_log('Delete Invoice Error: ' . $e->getMessage());
            $_SESSION['error'] = "An error occurred. Please try again.";
            redirect('/invoices');
        }
    }
    
    /**
     * Handle AJAX request to delete an invoice
     */
    public function deleteAjax() {
        try {
            // Start output buffering to prevent any unexpected output
            ob_start();
            
            header('Content-Type: application/json');
            
            // Validate request
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                ob_end_flush();
                return;
            }
            
            // Get and validate ID
            $id = 0;
            $requestData = json_decode(file_get_contents('php://input'), true);
            
            if (isset($requestData['id'])) {
                $id = (int)$requestData['id'];
            }
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
                ob_end_flush();
                return;
            }
            
            // Delete invoice
            $result = $this->invoice->delete($id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Invoice deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete invoice']);
            }
            
            ob_end_flush();
        } catch (Exception $e) {
            // Log the error
            error_log('Delete Invoice AJAX Error: ' . $e->getMessage());
            
            // Clear any output that might have been sent
            if (ob_get_length()) ob_clean();
            
            // Send error response
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
        }
    }
    
    /**
     * Mark an invoice as paid
     */
    public function markAsPaid() {
        try {
            // Validate request
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                redirect('/invoices');
            }
            
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if (!$id) {
                $_SESSION['error'] = "Invalid invoice ID";
                redirect('/invoices');
            }
            
            $paymentDate = !empty($_POST['payment_date']) ? sanitize_input($_POST['payment_date']) : date('Y-m-d');
            $reference = sanitize_input($_POST['payment_reference'] ?? '');
            
            // Mark as paid
            $result = $this->invoice->markAsPaid($id, $paymentDate, $reference);
            
            if ($result) {
                $_SESSION['success'] = "Invoice marked as paid successfully";
            } else {
                $_SESSION['error'] = "Failed to mark invoice as paid";
            }
            
            redirect('/invoices/view/' . $id);
        } catch (Exception $e) {
            error_log('Mark Invoice as Paid Error: ' . $e->getMessage());
            $_SESSION['error'] = "An error occurred. Please try again.";
            redirect('/invoices');
        }
    }
    
    /**
     * Handle AJAX request to mark an invoice as paid
     */
    public function markAsPaidAjax() {
        try {
            // Start output buffering to prevent any unexpected output
            ob_start();
            
            header('Content-Type: application/json');
            
            // Validate request
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                ob_end_flush();
                return;
            }
            
            // Get and validate data
            $requestData = json_decode(file_get_contents('php://input'), true);
            
            $id = isset($requestData['id']) ? (int)$requestData['id'] : 0;
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
                ob_end_flush();
                return;
            }
            
            $paymentDate = !empty($requestData['payment_date']) ? sanitize_input($requestData['payment_date']) : date('Y-m-d');
            $reference = sanitize_input($requestData['payment_reference'] ?? '');
            
            // Mark as paid
            $result = $this->invoice->markAsPaid($id, $paymentDate, $reference);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Invoice marked as paid successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to mark invoice as paid']);
            }
            
            ob_end_flush();
        } catch (Exception $e) {
            // Log the error
            error_log('Mark Invoice as Paid AJAX Error: ' . $e->getMessage());
            
            // Clear any output that might have been sent
            if (ob_get_length()) ob_clean();
            
            // Send error response
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
        }
    }
    
    /**
     * Helper method to validate date format
     * 
     * @param string $date Date string
     * @param string $format Expected format (default: Y-m-d)
     * @return bool Whether the date is valid
     */
    private function validateDate(string $date, string $format = 'Y-m-d'): bool {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
} 