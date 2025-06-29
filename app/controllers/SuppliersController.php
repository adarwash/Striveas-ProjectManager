<?php

class SuppliersController extends Controller {
    private $supplier;
    private $user;
    
    public function __construct() {
        parent::__construct();
        $this->supplier = new Supplier();
        $this->user = new User();
        
        // Check if user is logged in for all methods except for explicitly allowed ones
        $allowed = [];
        $this->user->requireLogin($allowed);
    }
    
    /**
     * Display suppliers list
     */
    public function index() {
        // Get suppliers from the model
        $status = isset($_GET['status']) ? sanitize_input($_GET['status']) : null;
        
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $searchTerm = sanitize_input($_GET['search']);
            $suppliers = $this->supplier->searchSuppliers($searchTerm);
            $pageTitle = "Search Results for: " . htmlspecialchars($searchTerm);
        } else {
            $suppliers = $this->supplier->getAllSuppliers($status);
            $pageTitle = "Suppliers";
            
            if ($status) {
                $pageTitle .= " - " . ucfirst($status);
            }
        }
        
        // Load view
        $this->loadView('suppliers/index', [
            'pageTitle' => $pageTitle,
            'suppliers' => $suppliers,
            'status' => $status
        ]);
    }
    
    /**
     * Display form to add a new supplier
     */
    public function add() {
        $this->loadView('suppliers/add', [
            'pageTitle' => 'Add New Supplier',
            'formAction' => '/suppliers/create'
        ]);
    }
    
    /**
     * Process form submission to create a new supplier
     */
    public function create() {
        try {
            // Validate form data
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                redirect('/suppliers');
            }
            
            $data = [
                'name' => sanitize_input($_POST['name'] ?? ''),
                'contact_name' => sanitize_input($_POST['contact_name'] ?? ''),
                'email' => sanitize_input($_POST['email'] ?? ''),
                'phone' => sanitize_input($_POST['phone'] ?? ''),
                'address' => sanitize_input($_POST['address'] ?? ''),
                'city' => sanitize_input($_POST['city'] ?? ''),
                'state' => sanitize_input($_POST['state'] ?? ''),
                'postal_code' => sanitize_input($_POST['postal_code'] ?? ''),
                'country' => sanitize_input($_POST['country'] ?? ''),
                'website' => sanitize_input($_POST['website'] ?? ''),
                'notes' => sanitize_input($_POST['notes'] ?? ''),
                'status' => sanitize_input($_POST['status'] ?? 'active'),
                'created_by' => $_SESSION['user_id']
            ];
            
            // Validation
            if (empty($data['name'])) {
                $_SESSION['error'] = "Supplier name is required";
                $_SESSION['form_data'] = $data;
                redirect('/suppliers/add');
            }
            
            // Basic email validation if provided
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Invalid email format";
                $_SESSION['form_data'] = $data;
                redirect('/suppliers/add');
            }
            
            // Create supplier
            $result = $this->supplier->create($data);
            
            if ($result) {
                $_SESSION['success'] = "Supplier added successfully";
                redirect('/suppliers');
            } else {
                $_SESSION['error'] = "Failed to add supplier";
                $_SESSION['form_data'] = $data;
                redirect('/suppliers/add');
            }
        } catch (Exception $e) {
            error_log('Create Supplier Error: ' . $e->getMessage());
            $_SESSION['error'] = "An error occurred. Please try again.";
            redirect('/suppliers/add');
        }
    }
    
    /**
     * Display form to edit a supplier
     */
    public function edit($id = null) {
        if (!$id) {
            redirect('/suppliers');
        }
        
        // Get supplier data
        $supplier = $this->supplier->getSupplierById($id);
        
        if (!$supplier) {
            $_SESSION['error'] = "Supplier not found";
            redirect('/suppliers');
        }
        
        $this->loadView('suppliers/edit', [
            'pageTitle' => 'Edit Supplier',
            'supplier' => $supplier,
            'formAction' => '/suppliers/update'
        ]);
    }
    
    /**
     * Process form submission to update a supplier
     */
    public function update() {
        try {
            // Validate form data
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                redirect('/suppliers');
            }
            
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if (!$id) {
                $_SESSION['error'] = "Invalid supplier ID";
                redirect('/suppliers');
            }
            
            $data = [
                'id' => $id,
                'name' => sanitize_input($_POST['name'] ?? ''),
                'contact_name' => sanitize_input($_POST['contact_name'] ?? ''),
                'email' => sanitize_input($_POST['email'] ?? ''),
                'phone' => sanitize_input($_POST['phone'] ?? ''),
                'address' => sanitize_input($_POST['address'] ?? ''),
                'city' => sanitize_input($_POST['city'] ?? ''),
                'state' => sanitize_input($_POST['state'] ?? ''),
                'postal_code' => sanitize_input($_POST['postal_code'] ?? ''),
                'country' => sanitize_input($_POST['country'] ?? ''),
                'website' => sanitize_input($_POST['website'] ?? ''),
                'notes' => sanitize_input($_POST['notes'] ?? ''),
                'status' => sanitize_input($_POST['status'] ?? 'active')
            ];
            
            // Validation
            if (empty($data['name'])) {
                $_SESSION['error'] = "Supplier name is required";
                $_SESSION['form_data'] = $data;
                redirect('/suppliers/edit/' . $id);
            }
            
            // Basic email validation if provided
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Invalid email format";
                $_SESSION['form_data'] = $data;
                redirect('/suppliers/edit/' . $id);
            }
            
            // Update supplier
            $result = $this->supplier->update($data);
            
            if ($result) {
                $_SESSION['success'] = "Supplier updated successfully";
                redirect('/suppliers');
            } else {
                $_SESSION['error'] = "Failed to update supplier";
                $_SESSION['form_data'] = $data;
                redirect('/suppliers/edit/' . $id);
            }
        } catch (Exception $e) {
            error_log('Update Supplier Error: ' . $e->getMessage());
            $_SESSION['error'] = "An error occurred. Please try again.";
            redirect('/suppliers');
        }
    }
    
    /**
     * Display supplier details
     */
    public function view($id = null) {
        if (!$id) {
            redirect('/suppliers');
        }
        
        // Get supplier data
        $supplier = $this->supplier->getSupplierById($id);
        
        if (!$supplier) {
            $_SESSION['error'] = "Supplier not found";
            redirect('/suppliers');
        }
        
        // Get supplier's invoices
        $invoice = new Invoice();
        $invoices = $invoice->getInvoicesBySupplier($id);
        
        $this->loadView('suppliers/view', [
            'pageTitle' => 'Supplier: ' . htmlspecialchars($supplier['name']),
            'supplier' => $supplier,
            'invoices' => $invoices
        ]);
    }
    
    /**
     * Process request to delete a supplier
     */
    public function delete() {
        try {
            // Validate request
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                redirect('/suppliers');
            }
            
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if (!$id) {
                $_SESSION['error'] = "Invalid supplier ID";
                redirect('/suppliers');
            }
            
            // Delete supplier
            $result = $this->supplier->delete($id);
            
            if ($result) {
                $_SESSION['success'] = "Supplier deleted successfully";
            } else {
                $_SESSION['error'] = "Failed to delete supplier";
            }
            
            redirect('/suppliers');
        } catch (Exception $e) {
            error_log('Delete Supplier Error: ' . $e->getMessage());
            $_SESSION['error'] = "An error occurred. Please try again.";
            redirect('/suppliers');
        }
    }
    
    /**
     * Handle AJAX request to delete a supplier
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
                echo json_encode(['success' => false, 'message' => 'Invalid supplier ID']);
                ob_end_flush();
                return;
            }
            
            // Delete supplier
            $result = $this->supplier->delete($id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Supplier deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete supplier']);
            }
            
            ob_end_flush();
        } catch (Exception $e) {
            // Log the error
            error_log('Delete Supplier AJAX Error: ' . $e->getMessage());
            
            // Clear any output that might have been sent
            if (ob_get_length()) ob_clean();
            
            // Send error response
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
        }
    }
} 