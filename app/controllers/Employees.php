<?php

class Employees extends Controller {
    private $employeeModel;
    private $userModel;
    
    public function __construct() {
        // Check if user is logged in and has admin or manager role
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Redirect if not logged in or not an admin/manager
        if (!isset($_SESSION['is_logged_in']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
            header('Location: /home');
            exit;
        }
        
        // Load models
        $this->employeeModel = $this->model('Employee');
        $this->userModel = $this->model('User');
    }
    
    /**
     * Display list of employees with performance data
     */
    public function index() {
        // Get all employees with management data
        $employees = $this->employeeModel->getAllEmployees();
        
        // Get performance statistics
        $stats = $this->employeeModel->getPerformanceStats();
        
        $data = [
            'title' => 'Employee Management',
            'employees' => $employees,
            'stats' => $stats
        ];
        
        $this->view('employees/index', $data);
    }
    
    /**
     * View employee details
     * 
     * @param int $userId Employee/user ID
     */
    public function viewEmployee($userId = null) {
        if (!$userId) {
            flash('employee_error', 'Invalid employee ID', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Get employee data
        $employee = $this->employeeModel->getEmployeeById($userId);
        
        if (!$employee) {
            flash('employee_error', 'Employee not found', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Get absence records
        $absences = $this->employeeModel->getAbsenceRecords($userId);
        
        // Get projects the employee is working on
        $projectModel = $this->model('Project');
        $projects = $projectModel->getProjectsForEmployee($userId);
        
        // Get performance notes
        $notes = $this->employeeModel->getPerformanceNotes($userId);
        
        // Get employee documents
        $documents = $this->employeeModel->getEmployeeDocuments($userId);
        
        // Format file sizes
        if (!empty($documents)) {
            foreach ($documents as &$document) {
                $document['formatted_size'] = $this->formatFileSize($document['file_size']);
            }
        }
        
        $data = [
            'title' => 'Employee Profile',
            'employee' => $employee,
            'absences' => $absences,
            'projects' => $projects,
            'notes' => $notes,
            'documents' => $documents
        ];
        
        $this->view('employees/view', $data);
    }
    
    /**
     * Create new employee management record
     */
    public function create() {
        // Get users without management data
        $users = $this->employeeModel->getEmployeesWithoutManagementData();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form data
            $data = [
                'user_id' => filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT),
                'performance_rating' => filter_input(INPUT_POST, 'performance_rating', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'notes' => htmlspecialchars($_POST['notes'] ?? ''),
                'last_review_date' => $_POST['last_review_date'] ?? null,
                'next_review_date' => $_POST['next_review_date'] ?? null
            ];
            
            // Validate input
            if (empty($data['user_id'])) {
                flash('employee_error', 'Please select a user', 'alert alert-danger');
                redirect('/employees/create');
            }
            
            // Create record
            if ($this->employeeModel->createEmployeeRecord($data)) {
                // Update task counts
                $this->employeeModel->updateTaskCounts($data['user_id']);
                
                flash('employee_success', 'Employee record created successfully', 'alert alert-success');
                redirect('/employees');
            } else {
                flash('employee_error', 'Something went wrong', 'alert alert-danger');
                redirect('/employees/create');
            }
        } else {
            $data = [
                'title' => 'Create Employee Record',
                'users' => $users
            ];
            
            $this->view('employees/create', $data);
        }
    }
    
    /**
     * Edit employee management record
     * 
     * @param int $userId Employee/user ID
     */
    public function edit($userId = null) {
        if (!$userId) {
            flash('employee_error', 'Invalid employee ID', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Get employee data
        $employee = $this->employeeModel->getEmployeeById($userId);
        
        if (!$employee) {
            flash('employee_error', 'Employee not found', 'alert alert-danger');
            redirect('/employees');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form data
            $data = [
                'user_id' => $userId,
                'performance_rating' => filter_input(INPUT_POST, 'performance_rating', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'tasks_completed' => filter_input(INPUT_POST, 'tasks_completed', FILTER_SANITIZE_NUMBER_INT),
                'tasks_pending' => filter_input(INPUT_POST, 'tasks_pending', FILTER_SANITIZE_NUMBER_INT),
                'notes' => htmlspecialchars($_POST['notes'] ?? ''),
                'last_review_date' => $_POST['last_review_date'] ?? null,
                'next_review_date' => $_POST['next_review_date'] ?? null
            ];
            
            // Update record
            if ($this->employeeModel->updateEmployeeRecord($data, $_SESSION['user_id'] ?? null)) {
                flash('employee_success', 'Employee record updated successfully', 'alert alert-success');
                redirect('/employees/viewEmployee/' . $userId);
            } else {
                flash('employee_error', 'Something went wrong', 'alert alert-danger');
                $this->view('employees/edit', [
                    'title' => 'Edit Employee Record',
                    'employee' => $employee
                ]);
            }
        } else {
            $data = [
                'title' => 'Edit Employee Record',
                'employee' => $employee
            ];
            
            $this->view('employees/edit', $data);
        }
    }
    
    /**
     * Delete employee management record
     * 
     * @param int $userId Employee/user ID
     */
    public function delete($userId = null) {
        if (!$userId) {
            flash('employee_error', 'Invalid employee ID', 'alert alert-danger');
            redirect('/employees');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Delete record
            if ($this->employeeModel->deleteEmployeeRecord($userId)) {
                flash('employee_success', 'Employee record deleted successfully', 'alert alert-success');
            } else {
                flash('employee_error', 'Something went wrong', 'alert alert-danger');
            }
        }
        
        redirect('/employees');
    }
    
    /**
     * Add absence record
     * 
     * @param int $userId Employee/user ID
     */
    public function addAbsence($userId = null) {
        if (!$userId) {
            flash('employee_error', 'Invalid employee ID', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Get employee data
        $employee = $this->employeeModel->getEmployeeById($userId);
        
        if (!$employee) {
            flash('employee_error', 'Employee not found', 'alert alert-danger');
            redirect('/employees');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form data
            $data = [
                'user_id' => $userId,
                'start_date' => $_POST['start_date'] ?? null,
                'end_date' => $_POST['end_date'] ?? null,
                'reason' => htmlspecialchars($_POST['reason'] ?? ''),
                'approved_by' => $_SESSION['user_id'] ?? null,
                'approved_at' => date('Y-m-d H:i:s')
            ];
            
            // Validate input
            if (empty($data['start_date']) || empty($data['end_date'])) {
                flash('absence_error', 'Please provide start and end dates', 'alert alert-danger');
                redirect('/employees/viewEmployee/' . $userId);
            }
            
            // Add absence record
            if ($this->employeeModel->addAbsenceRecord($data)) {
                flash('absence_success', 'Absence record added successfully', 'alert alert-success');
            } else {
                flash('absence_error', 'Something went wrong', 'alert alert-danger');
            }
            
            redirect('/employees/viewEmployee/' . $userId);
        } else {
            $data = [
                'title' => 'Add Absence Record',
                'employee' => $employee
            ];
            
            $this->view('employees/add_absence', $data);
        }
    }
    
    /**
     * Delete absence record
     * 
     * @param int $absenceId Absence record ID
     * @param int $userId Employee/user ID
     */
    public function deleteAbsence($absenceId = null, $userId = null) {
        if (!$absenceId || !$userId) {
            flash('absence_error', 'Invalid absence record', 'alert alert-danger');
            redirect('/employees');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Delete absence record
            if ($this->employeeModel->deleteAbsenceRecord($absenceId, $userId)) {
                flash('absence_success', 'Absence record deleted successfully', 'alert alert-success');
            } else {
                flash('absence_error', 'Something went wrong', 'alert alert-danger');
            }
        }
        
        redirect('/employees/viewEmployee/' . $userId);
    }
    
    /**
     * Update task counts for an employee
     * 
     * @param int $userId Employee/user ID
     */
    public function updateTasks($userId = null) {
        if (!$userId) {
            flash('employee_error', 'Invalid employee ID', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Update task counts
        if ($this->employeeModel->updateTaskCounts($userId)) {
            flash('employee_success', 'Task counts updated successfully', 'alert alert-success');
        } else {
            flash('employee_error', 'Something went wrong', 'alert alert-danger');
        }
        
        redirect('/employees/viewEmployee/' . $userId);
    }
    
    /**
     * View rating history for an employee
     * 
     * @param int $userId Employee/user ID
     */
    public function ratingHistory($userId = null) {
        if (!$userId) {
            flash('employee_error', 'Invalid employee ID', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Get employee data
        $employee = $this->employeeModel->getEmployeeById($userId);
        
        if (!$employee) {
            flash('employee_error', 'Employee not found', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Get rating history
        $ratingHistory = $this->employeeModel->getRatingHistory($userId);
        
        $data = [
            'title' => 'Rating History',
            'employee' => $employee,
            'ratingHistory' => $ratingHistory
        ];
        
        $this->view('employees/rating_history', $data);
    }
    
    /**
     * Add performance notes for an employee
     * 
     * @param int $userId Employee/user ID
     */
    public function addNote($userId = null) {
        if (!$userId) {
            flash('employee_error', 'Invalid employee ID', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Get employee data
        $employee = $this->employeeModel->getEmployeeById($userId);
        
        if (!$employee) {
            flash('employee_error', 'Employee not found', 'alert alert-danger');
            redirect('/employees');
        }
        
        // Process form if submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Get form data
            $data = [
                'user_id' => $userId,
                'note_text' => trim($_POST['note_text']),
                'note_type' => trim($_POST['note_type']),
                'changed_by' => $_SESSION['user_id']
            ];
            
            // Validate input
            if (empty($data['note_text'])) {
                flash('employee_error', 'Please enter a note', 'alert alert-danger');
                redirect('/employees/addNote/' . $userId);
            }
            
            // Add the note
            if ($this->employeeModel->addPerformanceNote($data)) {
                flash('employee_success', 'Performance note added successfully', 'alert alert-success');
                redirect('/employees/viewEmployee/' . $userId);
            } else {
                flash('employee_error', 'Error adding performance note', 'alert alert-danger');
                redirect('/employees/addNote/' . $userId);
            }
        }
        
        // Display add note form
        $data = [
            'title' => 'Add Performance Note',
            'employee' => $employee
        ];
        
        $this->view('employees/add_note', $data);
    }
    
    public function exportProfile($userId = null) {
        // Validate input parameters
        if(!isLoggedIn()) {
            redirect('users/login');
            return;
        }

        if(!$userId) {
            redirect('employees');
            return;
        }

        $employee = $this->employeeModel->getEmployeeById($userId);
        
        if(!$employee) {
            setMessage('alert-danger', 'Employee not found');
            redirect('employees');
            return;
        }

        // Get a clean output filename without spaces or special characters
        $fullName = isset($employee['full_name']) ? $employee['full_name'] : 'Unknown_Employee';
        $safeFilename = preg_replace('/[^a-z0-9_-]/i', '_', $fullName);
        $outputFilename = 'Employee_Profile_' . $safeFilename . '_' . date('Y-m-d') . '.pdf';
        
        // Use the system's temp directory which should be writable
        $filePath = sys_get_temp_dir() . '/';
        $fullFilePath = $filePath . $outputFilename;
        
        try {
            // Get all employee-related data for the PDF
            // Use available methods in the model
            $notes = $this->employeeModel->getPerformanceNotes($userId);
            $absences = $this->employeeModel->getAbsenceRecords($userId);
            $ratingHistory = $this->employeeModel->getRatingHistory($userId);

            // FPDF is not found in the expected location. Let's use a direct approach instead.
            // We'll output structured HTML that can be printed as PDF from the browser.
            
            // Set headers to display as HTML
            header('Content-Type: text/html; charset=utf-8');
            
            // Start building the HTML output
            echo '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Employee Profile - ' . htmlspecialchars($fullName) . '</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                        line-height: 1.6;
                    }
                    h1, h2 {
                        color: #333;
                    }
                    h1 {
                        text-align: center;
                        margin-bottom: 5px;
                    }
                    h2 {
                        margin-top: 20px;
                        border-bottom: 1px solid #ddd;
                        padding-bottom: 5px;
                    }
                    .subtitle {
                        text-align: center;
                        color: #666;
                        margin-top: 0;
                        margin-bottom: 20px;
                    }
                    .info-row {
                        display: flex;
                        margin-bottom: 5px;
                    }
                    .info-label {
                        font-weight: bold;
                        width: 120px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 10px;
                    }
                    th {
                        background-color: #f2f2f2;
                        text-align: left;
                    }
                    th, td {
                        border: 1px solid #ddd;
                        padding: 8px;
                    }
                    .footer {
                        text-align: center;
                        margin-top: 30px;
                        font-size: 12px;
                        color: #666;
                    }
                    @media print {
                        body {
                            margin: 0;
                            padding: 15px;
                        }
                        button.print-button {
                            display: none;
                        }
                    }
                    .print-button {
                        display: block;
                        margin: 20px auto;
                        padding: 10px 20px;
                        background-color: #007bff;
                        color: white;
                        border: none;
                        border-radius: 4px;
                        cursor: pointer;
                    }
                </style>
            </head>
            <body>
                <button class="print-button" onclick="window.print()">Print PDF</button>
                
                <h1>EMPLOYEE PROFILE</h1>
                <p class="subtitle">' . htmlspecialchars($fullName) . '</p>
                
                <h2>Employee Information</h2>';
                
            // Employee details
            $details = [
                'Employee ID' => isset($employee['employee_id']) ? $employee['employee_id'] : 'N/A',
                'Position' => isset($employee['position']) ? $employee['position'] : 'N/A',
                'Department' => isset($employee['department_name']) ? $employee['department_name'] : 'N/A',
                'Email' => isset($employee['email']) ? $employee['email'] : 'N/A'
            ];
            
            if (isset($employee['phone'])) {
                $details['Phone'] = $employee['phone'];
            }
            
            if (isset($employee['hire_date'])) {
                $details['Hire Date'] = date('F j, Y', strtotime($employee['hire_date']));
            }
            
            foreach ($details as $label => $value) {
                echo '<div class="info-row">
                    <div class="info-label">' . $label . ':</div>
                    <div>' . htmlspecialchars($value) . '</div>
                </div>';
            }
            
            // Performance Section
            echo '<h2>Performance Information</h2>';
            
            $rating = isset($employee['performance_rating']) ? number_format($employee['performance_rating'], 1) . ' / 5.0' : 'N/A';
            echo '<div class="info-row">
                <div class="info-label">Current Rating:</div>
                <div>' . $rating . '</div>
            </div>';
            
            // Display notes if available
            if(is_array($notes) && !empty($notes)) {
                echo '<h3>Performance Notes</h3>';
                
                echo '<table>
                    <tr>
                        <th>Date</th>
                        <th>Note</th>
                    </tr>';
                
                // Limit to 5 notes to save space
                $displayedNotes = array_slice($notes, 0, 5);
                
                foreach($displayedNotes as $note) {
                    if (!isset($note['created_at']) || !isset($note['note_text'])) {
                        continue;
                    }
                    
                    echo '<tr>
                        <td>' . date('Y-m-d', strtotime($note['created_at'])) . '</td>
                        <td>' . htmlspecialchars($note['note_text']) . '</td>
                    </tr>';
                }
                
                echo '</table>';
                
                // If there are more notes than shown
                if (count($notes) > 5) {
                    echo '<p>... and ' . (count($notes) - 5) . ' more note(s)</p>';
                }
            }
            
            // Absence Records Section
            echo '<h2>Absence Records</h2>';
            
            if(is_array($absences) && !empty($absences)) {
                echo '<table>
                    <tr>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Reason</th>
                    </tr>';
                
                // Limit to 5 absences for space
                $displayedAbsences = array_slice($absences, 0, 5);
                
                foreach($displayedAbsences as $absence) {
                    if(!isset($absence['start_date'])) continue;
                    
                    // Get status text
                    $status = isset($absence['approved_by']) ? 'Approved' : 'Pending';
                    
                    echo '<tr>
                        <td>' . date('Y-m-d', strtotime($absence['start_date'])) . '</td>
                        <td>' . (isset($absence['end_date']) ? date('Y-m-d', strtotime($absence['end_date'])) : 'N/A') . '</td>
                        <td>' . $status . '</td>
                        <td>' . (isset($absence['reason']) ? htmlspecialchars($absence['reason']) : '') . '</td>
                    </tr>';
                }
                
                echo '</table>';
                
                // If there are more absences than shown
                if (count($absences) > 5) {
                    echo '<p>... and ' . (count($absences) - 5) . ' more absence record(s)</p>';
                }
            } else {
                echo '<p>No absence records</p>';
            }
            
            // Sites Section
            echo '<h2>Assigned Sites</h2>';
            
            // Check if the method exists and get sites data
            // This is a placeholder - you'll need to implement the actual method
            $sites = method_exists($this->employeeModel, 'getEmployeeSites') ? 
                $this->employeeModel->getEmployeeSites($userId) : [];
            
            if(is_array($sites) && !empty($sites)) {
                echo '<table>
                    <tr>
                        <th>Site Name</th>
                        <th>Location</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>';
                
                foreach($sites as $site) {
                    echo '<tr>
                        <td>' . htmlspecialchars($site['site_name'] ?? 'N/A') . '</td>
                        <td>' . htmlspecialchars($site['location'] ?? 'N/A') . '</td>
                        <td>' . htmlspecialchars($site['role'] ?? 'N/A') . '</td>
                        <td>' . htmlspecialchars($site['status'] ?? 'Active') . '</td>
                    </tr>';
                }
                
                echo '</table>';
            } else {
                echo '<p>No sites assigned</p>';
                
                // Checkbox for site assignment option
                echo '<div style="margin-top: 15px; padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9; border-radius: 4px;">';
                echo '<h3 style="margin-top: 0;">Site Assignment Options</h3>';
                echo '<ul style="list-style-type: none; padding-left: 0;">';
                echo '<li><input type="checkbox" disabled> Assign to Main Office</li>';
                echo '<li><input type="checkbox" disabled> Assign to Remote Location</li>';
                echo '<li><input type="checkbox" disabled> Assign to Field Operations</li>';
                echo '<li><input type="checkbox" disabled> Assign to Client Site</li>';
                echo '</ul>';
                echo '<p><i>Note: Site assignments must be made from the employee edit page.</i></p>';
                echo '</div>';
            }
            
            // Rating History Section
            if(is_array($ratingHistory) && !empty($ratingHistory)) {
                echo '<h2>Rating History</h2>';
                
                echo '<table>
                    <tr>
                        <th>Date</th>
                        <th>Old Rating</th>
                        <th>New Rating</th>
                        <th>Changed By</th>
                    </tr>';
                
                // Limit to most recent 5 ratings
                $displayedRatings = array_slice($ratingHistory, 0, 5);
                
                foreach($displayedRatings as $record) {
                    if (!isset($record['changed_at']) || !isset($record['new_rating'])) {
                        continue;
                    }
                    
                    echo '<tr>
                        <td>' . date('M d, Y', strtotime($record['changed_at'])) . '</td>
                        <td>' . (isset($record['old_rating']) ? $record['old_rating'] : 'N/A') . '</td>
                        <td>' . $record['new_rating'] . '</td>
                        <td>' . (isset($record['changed_by_name']) ? htmlspecialchars($record['changed_by_name']) : 'Unknown') . '</td>
                    </tr>';
                }
                
                echo '</table>';
                
                // Add a visual trend indicator
                echo '<h3>Rating Trend</h3>';
                
                // Show rating trend
                $trend = '';
                $prevRating = null;
                
                // Sort ratings by date to show earliest to most recent
                usort($ratingHistory, function($a, $b) {
                    return strtotime($a['changed_at']) - strtotime($b['changed_at']);
                });
                
                foreach($ratingHistory as $record) {
                    if(!isset($record['new_rating'])) continue;
                    
                    if($prevRating !== null) {
                        if($record['new_rating'] > $prevRating) {
                            $trend .= '↗ '; // Upward trend
                        } elseif($record['new_rating'] < $prevRating) {
                            $trend .= '↘ '; // Downward trend
                        } else {
                            $trend .= '→ '; // No change
                        }
                    }
                    
                    $trend .= $record['new_rating'] . ' ';
                    $prevRating = $record['new_rating'];
                }
                
                echo '<p>' . $trend . '</p>';
            }
            
            // Footer
            echo '<div class="footer">
                                        HiveITPortal | Generated on ' . date('Y-m-d H:i') . '
            </div>';
            
            echo '</body></html>';
            exit;
            
        } catch (Exception $e) {
            // If an error occurs, show error
            die('Error generating profile: ' . $e->getMessage());
        }
    }
    
    /**
     * Upload document for an employee
     * 
     * @param int $userId Employee/user ID
     */
    public function uploadDocument($userId = null) {
        if (!$userId) {
            flash('employee_error', 'Invalid employee ID', 'alert alert-danger');
            redirect('/employees');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if file was uploaded
            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                flash('employee_error', 'Error uploading file. Please try again.', 'alert alert-danger');
                redirect('/employees/viewEmployee/' . $userId);
                return;
            }

            $file = $_FILES['document'];
            
            // Validate file size (10MB max)
            if ($file['size'] > 10 * 1024 * 1024) {
                flash('employee_error', 'File size exceeds 10MB limit.', 'alert alert-danger');
                redirect('/employees/viewEmployee/' . $userId);
                return;
            }

            // Validate file type
            $allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($fileExt, $allowedTypes)) {
                flash('employee_error', 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes), 'alert alert-danger');
                redirect('/employees/viewEmployee/' . $userId);
                return;
            }

            // Create uploads directory if it doesn't exist
            $uploadDir = 'uploads/employees/' . $userId . '/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate unique filename
            $fileName = uniqid() . '_' . $file['name'];
            $filePath = $uploadDir . $fileName;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Get additional metadata
                $documentType = isset($_POST['document_type']) ? htmlspecialchars($_POST['document_type']) : null;
                $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : null;
                
                // Save to database
                $fileData = [
                    'name' => $file['name'],
                    'path' => $filePath,
                    'type' => $file['type'],
                    'size' => $file['size']
                ];

                if ($this->employeeModel->uploadDocument($userId, $fileData, $documentType, $description)) {
                    flash('employee_success', 'Document uploaded successfully.', 'alert alert-success');
                } else {
                    flash('employee_error', 'Error saving document to database.', 'alert alert-danger');
                }
            } else {
                flash('employee_error', 'Error moving uploaded file.', 'alert alert-danger');
            }
        }
        
        redirect('/employees/viewEmployee/' . $userId);
    }
    
    /**
     * Download employee document
     * 
     * @param int $documentId Document ID
     */
    public function downloadDocument($documentId = null) {
        if (!$documentId) {
            flash('employee_error', 'Invalid document ID', 'alert alert-danger');
            redirect('/employees');
            return;
        }
        
        $document = $this->employeeModel->getDocumentById($documentId);
        
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
                'png' => 'image/png',
                'txt' => 'text/plain'
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
            flash('employee_error', 'Document not found.', 'alert alert-danger');
            redirect('/employees');
        }
    }
    
    /**
     * Delete employee document
     * 
     * @param int $documentId Document ID
     * @param int $userId Employee/user ID (for redirect)
     */
    public function deleteDocument($documentId = null, $userId = null) {
        if (!$documentId) {
            flash('employee_error', 'Invalid document ID', 'alert alert-danger');
            redirect('/employees');
            return;
        }
        
        $document = $this->employeeModel->getDocumentById($documentId);
        
        if ($document) {
            // Save the user ID for redirect
            $redirectUserId = $userId ?: $document['user_id'];
            
            // Delete file from server
            if (file_exists($document['file_path'])) {
                unlink($document['file_path']);
            }
            
            // Delete from database
            if ($this->employeeModel->deleteDocument($documentId)) {
                flash('employee_success', 'Document deleted successfully.', 'alert alert-success');
            } else {
                flash('employee_error', 'Error deleting document from database.', 'alert alert-danger');
            }
            
            redirect('/employees/viewEmployee/' . $redirectUserId);
        } else {
            flash('employee_error', 'Document not found.', 'alert alert-danger');
            redirect('/employees');
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

    /**
     * Employee Performance Dashboard with Time Tracking Integration
     */
    public function performance() {
        // Check admin/manager permissions
        if (!in_array($_SESSION['role'], ['admin', 'manager'])) {
            flash('employee_error', 'You do not have permission to access performance data', 'alert alert-danger');
            redirect('/dashboard');
        }
        
        // Get parameters
        $days = $_GET['days'] ?? 30;
        $sortBy = $_GET['sort'] ?? 'productivity_rating';
        $userId = $_GET['user_id'] ?? null;
        
        if ($userId) {
            // Single employee performance view
            $employee = $this->employeeModel->getEmployeePerformanceWithTimeTracking($userId, $days);
            
            if (!$employee) {
                flash('employee_error', 'Employee not found', 'alert alert-danger');
                redirect('/employees/performance');
            }
            
            $data = [
                'title' => 'Employee Performance Analysis - ' . $employee['full_name'],
                'employee' => $employee,
                'days' => $days,
                'single_view' => true
            ];
            
            $this->view('employees/performance_detail', $data);
        } else {
            // All employees performance overview
            $employees = $this->employeeModel->getAllEmployeesWithTimeTrackingPerformance($days, $sortBy);
            $performanceSummary = $this->employeeModel->getTimeTrackingPerformanceSummary($days);
            $trackerDays = min(14, max(3, (int)$days));
            $dailyTrackerDates = [];
            for ($i = $trackerDays - 1; $i >= 0; $i--) {
                $dailyTrackerDates[] = date('Y-m-d', strtotime("-{$i} days"));
            }
            $activitySummary = [
                'project_updates' => 0,
                'task_updates' => 0,
                'task_completions' => 0,
                'ticket_replies' => 0,
                'login_count' => 0
            ];
            foreach ($employees as &$employee) {
                $stats = $employee['activity_stats'] ?? [];
                $activitySummary['project_updates'] += $stats['project_updates'] ?? 0;
                $activitySummary['task_updates'] += $stats['task_updates'] ?? 0;
                $activitySummary['task_completions'] += $stats['task_completions'] ?? 0;
                $activitySummary['ticket_replies'] += $stats['ticket_replies'] ?? 0;
                $activitySummary['login_count'] += $stats['login_count'] ?? 0;

                // Build daily tracker map
                $trendMap = [];
                if (!empty($employee['time_performance']['trends'])) {
                    foreach ($employee['time_performance']['trends'] as $trend) {
                        $trendMap[$trend['work_date']] = $trend['daily_hours'] ?? 0;
                    }
                }
                $employee['daily_tracker'] = [];
                foreach ($dailyTrackerDates as $trackerDate) {
                    $employee['daily_tracker'][$trackerDate] = isset($trendMap[$trackerDate])
                        ? round((float)$trendMap[$trackerDate], 2)
                        : 0;
                }
            }
            unset($employee);
            
            $data = [
                'title' => 'Employee Performance Dashboard',
                'employees' => $employees,
                'performance_summary' => $performanceSummary,
                'days' => $days,
                'sort_by' => $sortBy,
                'activity_summary' => $activitySummary,
                'daily_tracker_dates' => $dailyTrackerDates,
                'single_view' => false
            ];
            
            $this->view('employees/performance_dashboard', $data);
        }
    }
    
    /**
     * Get time tracking analytics for an employee (AJAX)
     */
    public function getTimeAnalytics($userId = null) {
        if (!$userId) {
            $this->jsonResponse(['error' => 'User ID required']);
            return;
        }
        
        $days = $_GET['days'] ?? 30;
        $employee = $this->employeeModel->getEmployeePerformanceWithTimeTracking($userId, $days);
        
        if (!$employee) {
            $this->jsonResponse(['error' => 'Employee not found']);
            return;
        }
        
        $this->jsonResponse([
            'success' => true,
            'data' => $employee['time_performance'],
            'trends' => $employee['time_performance']['trends']
        ]);
    }
    
    /**
     * Export employee performance report to CSV
     */
    public function exportPerformanceReport() {
        // Check admin permissions
        if ($_SESSION['role'] !== 'admin') {
            flash('employee_error', 'You do not have permission to export reports', 'alert alert-danger');
            redirect('/employees/performance');
        }
        
        $days = $_GET['days'] ?? 30;
        $employees = $this->employeeModel->getAllEmployeesWithTimeTrackingPerformance($days);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="employee_performance_report_' . date('Y-m-d') . '.csv"');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Write CSV headers
        fputcsv($output, [
            'Employee Name',
            'Email',
            'Role',
            'Performance Rating',
            'Total Hours (' . $days . ' days)',
            'Avg Hours/Day',
            'Punctuality Score (%)',
            'Attendance Rate (%)',
            'Consistency Score (%)',
            'Break Efficiency',
            'Productivity Rating',
            'Tasks Completed',
            'Tasks Pending',
            'Last Review Date'
        ]);
        
        // Write employee data
        foreach ($employees as $employee) {
            fputcsv($output, [
                $employee['full_name'],
                $employee['email'],
                $employee['role'],
                $employee['performance_rating'],
                $employee['time_performance']['total_hours'],
                $employee['time_performance']['avg_hours_per_day'],
                $employee['time_performance']['punctuality_score'],
                $employee['time_performance']['attendance_rate'],
                $employee['time_performance']['consistency_score'],
                $employee['time_performance']['break_efficiency'],
                $employee['time_performance']['productivity_rating'],
                $employee['tasks_completed'],
                $employee['tasks_pending'],
                $employee['last_review_date'] ?? 'Not reviewed'
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Update performance rating based on time tracking data
     */
    public function updatePerformanceRating($userId = null) {
        if (!$userId) {
            $this->jsonResponse(['error' => 'User ID required']);
            return;
        }
        
        // Check admin permissions
        if ($_SESSION['role'] !== 'admin') {
            $this->jsonResponse(['error' => 'Permission denied']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_id' => $userId,
                'performance_rating' => $_POST['performance_rating'] ?? 0,
                'notes' => $_POST['notes'] ?? '',
                'last_review_date' => date('Y-m-d')
            ];
            
            if ($this->employeeModel->updateEmployeeRecord($data, $_SESSION['user_id'])) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Performance rating updated successfully'
                ]);
            } else {
                $this->jsonResponse(['error' => 'Failed to update performance rating']);
            }
        }
    }

    /**
     * Helper method to send JSON responses
     */
}
?> 