<?php require VIEWSPATH . '/inc/header.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">System Settings</h1>
            <p class="text-muted">Configure application-wide settings</p>
        </div>
        <div>
            <a href="<?= URLROOT ?>/admin" class="btn btn-outline-primary">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </div>
    </div>
    
    <?php flash('settings_success'); ?>
    <?php flash('settings_error'); ?>
    
    <!-- Settings Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="application-tab" data-bs-toggle="tab" data-bs-target="#application" 
                type="button" role="tab" aria-controls="application" aria-selected="true">
                <i class="bi bi-gear-wide-connected me-2"></i>Application
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="defaults-tab" data-bs-toggle="tab" data-bs-target="#defaults" 
                type="button" role="tab" aria-controls="defaults" aria-selected="false">
                <i class="bi bi-sliders me-2"></i>Defaults
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" 
                type="button" role="tab" aria-controls="email" aria-selected="false">
                <i class="bi bi-envelope me-2"></i>Email
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="currency-tab" data-bs-toggle="tab" data-bs-target="#currency" 
                type="button" role="tab" aria-controls="currency" aria-selected="false">
                <i class="bi bi-currency-exchange me-2"></i>Currency
            </button>
        </li>
    </ul>
    
    <form action="<?= URLROOT ?>/admin/settings" method="POST">
        <div class="tab-content" id="settingsTabContent">
            <!-- Application Settings Tab -->
            <div class="tab-pane fade show active" id="application" role="tabpanel" aria-labelledby="application-tab">
        <div class="row g-4">
                    <div class="col-md-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex align-items-center">
                                    <span class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                        <i class="bi bi-toggles fs-4 text-primary"></i>
                                    </span>
                                    <h5 class="mb-0">System Status</h5>
                        </div>
                    </div>
                    <div class="card-body">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="maintenance_mode" name="maintenance_mode" 
                                    <?= isset($systemSettings['maintenance_mode']) && $systemSettings['maintenance_mode'] ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-medium" for="maintenance_mode">
                                    Maintenance Mode
                                </label>
                                <div class="form-text">When enabled, only administrators can access the application.</div>
                                    </div>
                            </div>
                            
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="enable_registration" name="enable_registration" 
                                    <?= !isset($systemSettings['enable_registration']) || $systemSettings['enable_registration'] ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-medium" for="enable_registration">
                                    User Registration
                                </label>
                                <div class="form-text">Allow new users to register accounts.</div>
                                    </div>
                            </div>
                            
                                <div class="mb-0">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="enable_api" name="enable_api" 
                                    <?= isset($systemSettings['enable_api']) && $systemSettings['enable_api'] ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-medium" for="enable_api">
                                    API Access
                                </label>
                                <div class="form-text">Enable API endpoints for third-party integrations.</div>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                        
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex align-items-center">
                                    <span class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                        <i class="bi bi-shield-lock fs-4 text-primary"></i>
                                    </span>
                                    <h5 class="mb-0">System Limits</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="max_upload_size" class="form-label fw-medium">Maximum Upload Size (MB)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="max_upload_size" name="max_upload_size" min="1" max="100" 
                                            value="<?= $systemSettings['max_upload_size'] ?? '10' ?>">
                                        <span class="input-group-text">MB</span>
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label for="max_projects" class="form-label fw-medium">Projects Per User</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="max_projects" name="max_projects" min="0" max="1000" 
                                            value="<?= $systemSettings['max_projects'] ?? '0' ?>">
                                        <span class="input-group-text">projects</span>
                                    </div>
                                    <div class="form-text">Enter 0 for unlimited</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex align-items-center">
                                    <span class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                        <i class="bi bi-calendar-date fs-4 text-primary"></i>
                                    </span>
                                    <h5 class="mb-0">Display Settings</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-0">
                                    <label for="default_date_format" class="form-label fw-medium">Date Format</label>
                                    <select class="form-select" id="default_date_format" name="default_date_format">
                                        <option value="Y-m-d" <?= (isset($systemSettings['default_date_format']) && $systemSettings['default_date_format'] == 'Y-m-d') ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                        <option value="m/d/Y" <?= (isset($systemSettings['default_date_format']) && $systemSettings['default_date_format'] == 'm/d/Y') ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                        <option value="d/m/Y" <?= (isset($systemSettings['default_date_format']) && $systemSettings['default_date_format'] == 'd/m/Y') ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                        <option value="M j, Y" <?= (!isset($systemSettings['default_date_format']) || $systemSettings['default_date_format'] == 'M j, Y') ? 'selected' : '' ?>>Month D, YYYY</option>
                                    </select>
                                    <div class="form-text mt-2">
                                        Preview: <span class="badge bg-light text-dark border" id="date_format_preview">January 1, 2023</span>
                                    </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Defaults Tab -->
            <div class="tab-pane fade" id="defaults" role="tabpanel" aria-labelledby="defaults-tab">
                <div class="row g-4">
                    <div class="col-md-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex align-items-center">
                                    <span class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                        <i class="bi bi-kanban fs-4 text-primary"></i>
                                    </span>
                                    <h5 class="mb-0">Project Defaults</h5>
                        </div>
                    </div>
                    <div class="card-body">
                            <div class="mb-3">
                                    <label for="default_project_category" class="form-label fw-medium">Default Project Category</label>
                                <input type="text" class="form-control" id="default_project_category" name="default_project_category" 
                                    value="<?= $systemSettings['default_project_category'] ?? 'General' ?>">
                            </div>
                                <div class="mb-0">
                                    <label for="default_project_status" class="form-label fw-medium">Default Project Status</label>
                                <select class="form-select" id="default_project_status" name="default_project_status">
                                    <option value="Planning" <?= (isset($systemSettings['default_project_status']) && $systemSettings['default_project_status'] == 'Planning') ? 'selected' : '' ?>>Planning</option>
                                    <option value="In Progress" <?= (isset($systemSettings['default_project_status']) && $systemSettings['default_project_status'] == 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                                    <option value="On Hold" <?= (isset($systemSettings['default_project_status']) && $systemSettings['default_project_status'] == 'On Hold') ? 'selected' : '' ?>>On Hold</option>
                                    <option value="Completed" <?= (isset($systemSettings['default_project_status']) && $systemSettings['default_project_status'] == 'Completed') ? 'selected' : '' ?>>Completed</option>
                                </select>
                                </div>
                            </div>
                            </div>
                        </div>
                        
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex align-items-center">
                                    <span class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                        <i class="bi bi-check2-square fs-4 text-primary"></i>
                                    </span>
                                    <h5 class="mb-0">Task Defaults</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-0">
                                    <label for="default_task_priority" class="form-label fw-medium">Default Task Priority</label>
                                <select class="form-select" id="default_task_priority" name="default_task_priority">
                                    <option value="Low" <?= (isset($systemSettings['default_task_priority']) && $systemSettings['default_task_priority'] == 'Low') ? 'selected' : '' ?>>Low</option>
                                    <option value="Medium" <?= (!isset($systemSettings['default_task_priority']) || $systemSettings['default_task_priority'] == 'Medium') ? 'selected' : '' ?>>Medium</option>
                                    <option value="High" <?= (isset($systemSettings['default_task_priority']) && $systemSettings['default_task_priority'] == 'High') ? 'selected' : '' ?>>High</option>
                                    <option value="Critical" <?= (isset($systemSettings['default_task_priority']) && $systemSettings['default_task_priority'] == 'Critical') ? 'selected' : '' ?>>Critical</option>
                                </select>
                                    <div class="mt-3">
                                        <div class="d-flex align-items-center mt-2">
                                            <span class="badge bg-success me-2" style="width: 60px;">Low</span>
                                            <span class="text-muted small">Minor tasks, can be completed when convenient</span>
                                        </div>
                                        <div class="d-flex align-items-center mt-2">
                                            <span class="badge bg-info me-2" style="width: 60px;">Medium</span>
                                            <span class="text-muted small">Standard tasks requiring attention this sprint</span>
                                        </div>
                                        <div class="d-flex align-items-center mt-2">
                                            <span class="badge bg-warning me-2" style="width: 60px;">High</span>
                                            <span class="text-muted small">Important tasks needing prompt attention</span>
                                        </div>
                                        <div class="d-flex align-items-center mt-2">
                                            <span class="badge bg-danger me-2" style="width: 60px;">Critical</span>
                                            <span class="text-muted small">Urgent tasks requiring immediate attention</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Email Tab -->
            <div class="tab-pane fade" id="email" role="tabpanel" aria-labelledby="email-tab">
                <div class="row">
                    <div class="col-lg-10 mx-auto">
                        
                        <!-- Microsoft Graph API Settings Card -->
                        <div class="card border-0 shadow-sm mb-4 border-primary">
                            <div class="card-header bg-primary text-white">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <span class="bg-white bg-opacity-25 p-2 rounded-circle me-3">
                                            <i class="bi bi-microsoft fs-4"></i>
                                        </span>
                                        <div>
                                            <h5 class="mb-0">Microsoft 365 Email Integration</h5>
                                            <small>Modern, secure email integration using Microsoft Graph API</small>
                                        </div>
                                    </div>
                                    <span class="badge bg-success">Recommended</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-2">Why use Microsoft Graph API?</h6>
                                        <ul class="mb-0">
                                            <li>Works with Security Defaults enabled</li>
                                            <li>No app passwords required</li>
                                            <li>OAuth2 authentication</li>
                                            <li>Better security and reliability</li>
                                            <li>Full support for reading and sending emails</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <a href="<?= URLROOT ?>/admin/emailSettings" class="btn btn-primary btn-lg">
                                            <i class="bi bi-gear-fill me-2"></i>Configure Now
                                        </a>
                                        <p class="text-muted small mt-2 mb-0">Setup takes ~5 minutes</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> The SMTP/IMAP settings below are legacy options. For Microsoft 365 users, we strongly recommend using the Graph API configuration above.
                        </div>
                        
                        <!-- SMTP Configuration (Outbound) -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex align-items-center">
                                    <span class="bg-success bg-opacity-10 p-2 rounded-circle me-3">
                                        <i class="bi bi-send fs-4 text-success"></i>
                                    </span>
                                    <div>
                                        <h5 class="mb-0">SMTP Configuration (Outbound)</h5>
                                        <small class="text-muted">Settings for sending emails and ticket notifications</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="from_email" class="form-label fw-medium">From Email</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-at"></i></span>
                                                <input type="email" class="form-control" id="from_email" name="from_email" 
                                                    placeholder="support@yourcompany.com"
                                                    value="<?= $systemSettings['from_email'] ?? '' ?>">
                                            </div>
                                            <div class="form-text">Email address used for ticket notifications</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="from_name" class="form-label fw-medium">From Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                                <input type="text" class="form-control" id="from_name" name="from_name" 
                                                    placeholder="Your Company Support"
                                                    value="<?= $systemSettings['from_name'] ?? 'Hive IT Portal' ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="smtp_host" class="form-label fw-medium">SMTP Host</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-hdd-network"></i></span>
                                                <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                                    placeholder="smtp.gmail.com"
                                                    value="<?= $systemSettings['smtp_host'] ?? '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <label for="smtp_port" class="form-label fw-medium">SMTP Port</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-signpost"></i></span>
                                                    <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                                        placeholder="587"
                                                        value="<?= $systemSettings['smtp_port'] ?? '587' ?>">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <label for="smtp_encryption" class="form-label fw-medium">Encryption</label>
                                                <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                                    <option value="tls" <?= (isset($systemSettings['smtp_encryption']) && $systemSettings['smtp_encryption'] == 'tls') ? 'selected' : '' ?>>TLS</option>
                                                    <option value="ssl" <?= (isset($systemSettings['smtp_encryption']) && $systemSettings['smtp_encryption'] == 'ssl') ? 'selected' : '' ?>>SSL</option>
                                                    <option value="none" <?= (isset($systemSettings['smtp_encryption']) && $systemSettings['smtp_encryption'] == 'none') ? 'selected' : '' ?>>None</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="smtp_username" class="form-label fw-medium">SMTP Username</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                                <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                                                    placeholder="support@yourcompany.com"
                                                    value="<?= $systemSettings['smtp_username'] ?? '' ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="smtp_password" class="form-label fw-medium">SMTP Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                                <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                                    placeholder="Use app password for Gmail/Outlook"
                                                    value="<?= $systemSettings['smtp_password'] ?? '' ?>">
                                                <button class="btn btn-outline-secondary" type="button" id="toggle_smtp_password">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-success" id="test_smtp">
                                        <i class="bi bi-envelope-check me-2"></i>Test SMTP Configuration
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Inbound Email Configuration (IMAP/POP3) -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex align-items-center">
                                    <span class="bg-info bg-opacity-10 p-2 rounded-circle me-3">
                                        <i class="bi bi-inbox fs-4 text-info"></i>
                                    </span>
                                    <div>
                                        <h5 class="mb-0">Inbound Email Configuration</h5>
                                        <small class="text-muted">Settings for receiving emails and creating tickets (supports IMAP and POP3)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                                    <div>
                                        <strong>Required for ticket system:</strong> Configure inbound email to automatically create tickets from incoming emails.
                                    </div>
                                </div>
                                
                                <!-- Protocol Selection -->
                                <div class="mb-4">
                                    <label class="form-label fw-medium">Email Protocol</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="inbound_protocol" id="protocol_imap" value="imap" 
                                                    <?= (!isset($systemSettings['inbound_protocol']) || $systemSettings['inbound_protocol'] == 'imap') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="protocol_imap">
                                                    <strong>IMAP</strong> - Keeps emails on server, supports folders
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="inbound_protocol" id="protocol_pop3" value="pop3"
                                                    <?= (isset($systemSettings['inbound_protocol']) && $systemSettings['inbound_protocol'] == 'pop3') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="protocol_pop3">
                                                    <strong>POP3</strong> - Downloads emails to server, simpler setup
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Authentication Type Selection -->
                                <div class="mb-4">
                                    <label class="form-label fw-medium">Authentication Method</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="inbound_auth_type" id="auth_password" value="password" 
                                                    <?= (!isset($systemSettings['inbound_auth_type']) || $systemSettings['inbound_auth_type'] == 'password') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="auth_password">
                                                    <strong>Password</strong> - Traditional username/password or App Password
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="inbound_auth_type" id="auth_oauth2" value="oauth2"
                                                    <?= (isset($systemSettings['inbound_auth_type']) && $systemSettings['inbound_auth_type'] == 'oauth2') ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="auth_oauth2">
                                                    <strong>OAuth2</strong> - Modern secure authentication (recommended)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="inbound_host" class="form-label fw-medium">Email Host</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-hdd-network"></i></span>
                                                <input type="text" class="form-control" id="inbound_host" name="inbound_host" 
                                                    placeholder="imap.gmail.com or pop.gmail.com"
                                                    value="<?= $systemSettings['inbound_host'] ?? '' ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="inbound_username" class="form-label fw-medium">Username</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                                <input type="text" class="form-control" id="inbound_username" name="inbound_username" 
                                                    placeholder="support@yourcompany.com"
                                                    value="<?= $systemSettings['inbound_username'] ?? '' ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="inbound_password" class="form-label fw-medium">Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                                <input type="password" class="form-control" id="inbound_password" name="inbound_password" 
                                                    placeholder="Use app password for Gmail/Outlook"
                                                    value="<?= $systemSettings['inbound_password'] ?? '' ?>">
                                                <button class="btn btn-outline-secondary" type="button" id="toggle_inbound_password">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <label for="inbound_port" class="form-label fw-medium">Port</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-signpost"></i></span>
                                                    <input type="number" class="form-control" id="inbound_port" name="inbound_port" 
                                                        placeholder="993 (IMAP) / 995 (POP3)"
                                                        value="<?= $systemSettings['inbound_port'] ?? '993' ?>">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <label for="inbound_encryption" class="form-label fw-medium">Encryption</label>
                                                <select class="form-select" id="inbound_encryption" name="inbound_encryption">
                                                    <option value="ssl" <?= (isset($systemSettings['inbound_encryption']) && $systemSettings['inbound_encryption'] == 'ssl') ? 'selected' : '' ?>>SSL</option>
                                                    <option value="tls" <?= (isset($systemSettings['inbound_encryption']) && $systemSettings['inbound_encryption'] == 'tls') ? 'selected' : '' ?>>TLS</option>
                                                    <option value="none" <?= (isset($systemSettings['inbound_encryption']) && $systemSettings['inbound_encryption'] == 'none') ? 'selected' : '' ?>>None</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <!-- IMAP Folder (only shown for IMAP) -->
                                        <div class="mb-3" id="imap_folder_group">
                                            <label for="imap_folder" class="form-label fw-medium">IMAP Folder</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-folder"></i></span>
                                                <input type="text" class="form-control" id="imap_folder" name="imap_folder" 
                                                    placeholder="INBOX"
                                                    value="<?= $systemSettings['imap_folder'] ?? 'INBOX' ?>">
                                            </div>
                                            <div class="form-text">Folder to monitor for new emails (IMAP only)</div>
                                        </div>
                                        
                                        <!-- OAuth2 Configuration (only shown for OAuth2) -->
                                        <div id="oauth2_config_group" style="display: none;">
                                            <div class="mb-3">
                                                <label for="oauth2_provider" class="form-label fw-medium">OAuth2 Provider</label>
                                                <select class="form-select" id="oauth2_provider" name="oauth2_provider">
                                                    <option value="microsoft" <?= (isset($systemSettings['oauth2_provider']) && $systemSettings['oauth2_provider'] == 'microsoft') ? 'selected' : '' ?>>Microsoft 365</option>
                                                    <option value="google" <?= (isset($systemSettings['oauth2_provider']) && $systemSettings['oauth2_provider'] == 'google') ? 'selected' : '' ?>>Google Workspace</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="oauth2_client_id" class="form-label fw-medium">Client ID</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                                                    <input type="text" class="form-control" id="oauth2_client_id" name="oauth2_client_id" 
                                                        placeholder="Your OAuth2 Client ID"
                                                        value="<?= $systemSettings['oauth2_client_id'] ?? '' ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="oauth2_client_secret" class="form-label fw-medium">Client Secret</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                                    <input type="password" class="form-control" id="oauth2_client_secret" name="oauth2_client_secret" 
                                                        placeholder="Your OAuth2 Client Secret"
                                                        value="<?= $systemSettings['oauth2_client_secret'] ?? '' ?>">
                                                    <button class="btn btn-outline-secondary" type="button" id="toggle_oauth2_secret">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="oauth2_redirect_uri" class="form-label fw-medium">Redirect URI</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-arrow-return-left"></i></span>
                                                    <input type="url" class="form-control" id="oauth2_redirect_uri" name="oauth2_redirect_uri" 
                                                        placeholder="<?= URLROOT ?>/oauth2/callback"
                                                        value="<?= $systemSettings['oauth2_redirect_uri'] ?? URLROOT . '/oauth2/callback' ?>">
                                                </div>
                                                <div class="form-text">Use this exact URL in your OAuth2 app configuration</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="d-grid gap-2">
                                                    <button type="button" class="btn btn-success" id="oauth2_authorize">
                                                        <i class="bi bi-shield-check me-2"></i>Authorize with OAuth2
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info" id="oauth2_test">
                                                        <i class="bi bi-check-circle me-2"></i>Test OAuth2 Connection
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" id="oauth2_revoke">
                                                        <i class="bi bi-x-circle me-2"></i>Revoke Authorization
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="alert alert-info" role="alert">
                                                <i class="bi bi-info-circle me-2"></i>
                                                <strong>OAuth2 Setup:</strong> Create an OAuth2 app in your provider's console, then authorize access using the button above.
                                            </div>
                                        </div>
                                        
                                        <!-- Protocol Information -->
                                        <div class="mb-3">
                                            <div class="alert alert-info d-flex align-items-center" role="alert" id="protocol_info">
                                                <i class="bi bi-info-circle-fill me-2"></i>
                                                <div id="protocol_info_text">
                                                    <strong>IMAP:</strong> Keeps emails on server, supports folders, allows multiple clients to access the same mailbox.
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="delete_processed_emails" name="delete_processed_emails" 
                                                    <?= isset($systemSettings['delete_processed_emails']) && $systemSettings['delete_processed_emails'] ? 'checked' : '' ?>>
                                                <label class="form-check-label fw-medium" for="delete_processed_emails">
                                                    Delete processed emails
                                                </label>
                                                <div class="form-text" id="delete_emails_help">Remove emails from server after processing</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-info" id="test_inbound">
                                        <i class="bi bi-inbox-check me-2"></i>Test Connection
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ticket Processing Settings -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex align-items-center">
                                    <span class="bg-warning bg-opacity-10 p-2 rounded-circle me-3">
                                        <i class="bi bi-ticket-perforated fs-4 text-warning"></i>
                                    </span>
                                    <div>
                                        <h5 class="mb-0">Ticket Processing Settings</h5>
                                        <small class="text-muted">How emails are converted to tickets</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="auto_process_emails" name="auto_process_emails" 
                                                    <?= !isset($systemSettings['auto_process_emails']) || $systemSettings['auto_process_emails'] ? 'checked' : '' ?>>
                                                <label class="form-check-label fw-medium" for="auto_process_emails">
                                                    Auto-process emails
                                                </label>
                                                <div class="form-text">Automatically create tickets from incoming emails</div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="ticket_email_pattern" class="form-label fw-medium">Ticket Pattern</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-regex"></i></span>
                                                <input type="text" class="form-control" id="ticket_email_pattern" name="ticket_email_pattern" 
                                                    placeholder="/\[TKT-\d{4}-\d{6}\]/"
                                                    value="<?= $systemSettings['ticket_email_pattern'] ?? '/\[TKT-\d{4}-\d{6}\]/' ?>">
                                            </div>
                                            <div class="form-text">Regex pattern to identify existing tickets in email subjects</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="max_attachment_size" class="form-label fw-medium">Max Attachment Size (MB)</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-file-earmark"></i></span>
                                                <input type="number" class="form-control" id="max_attachment_size" name="max_attachment_size" 
                                                    placeholder="10"
                                                    value="<?= isset($systemSettings['max_attachment_size']) ? ($systemSettings['max_attachment_size'] / 1048576) : '10' ?>">
                                                <span class="input-group-text">MB</span>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="allowed_file_types" class="form-label fw-medium">Allowed File Types</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-filetype-txt"></i></span>
                                                <input type="text" class="form-control" id="allowed_file_types" name="allowed_file_types" 
                                                    placeholder="pdf,doc,docx,txt,png,jpg,jpeg,gif"
                                                    value="<?= isset($systemSettings['allowed_file_types']) ? (is_array($systemSettings['allowed_file_types']) ? implode(',', $systemSettings['allowed_file_types']) : $systemSettings['allowed_file_types']) : 'pdf,doc,docx,txt,png,jpg,jpeg,gif' ?>">
                                            </div>
                                            <div class="form-text">Comma-separated list of allowed file extensions</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Email Provider Quick Setup -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex align-items-center">
                                    <span class="bg-secondary bg-opacity-10 p-2 rounded-circle me-3">
                                        <i class="bi bi-lightning fs-4 text-secondary"></i>
                                    </span>
                                    <div>
                                        <h5 class="mb-0">Quick Setup</h5>
                                        <small class="text-muted">Pre-configured settings for common email providers</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-outline-primary w-100" onclick="setupGmail()">
                                            <i class="bi bi-google me-2"></i>Gmail Setup
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-outline-primary w-100" onclick="setupOutlook()">
                                            <i class="bi bi-microsoft me-2"></i>Outlook Setup
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-outline-secondary w-100" onclick="clearSettings()">
                                            <i class="bi bi-arrow-clockwise me-2"></i>Reset Settings
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            
            <!-- Currency Tab -->
            <div class="tab-pane fade" id="currency" role="tabpanel" aria-labelledby="currency-tab">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex align-items-center">
                                    <span class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                        <i class="bi bi-currency-exchange fs-4 text-primary"></i>
                                    </span>
                                    <h5 class="mb-0">Currency Settings</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-success mb-4 d-flex align-items-center">
                                    <div class="d-flex justify-content-center align-items-center me-3">
                                        <div style="width: 45px; height: 45px;" class="bg-success bg-opacity-25 rounded-circle d-flex justify-content-center align-items-center">
                                            <i class="bi bi-cash-stack text-success fs-4"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">Current Format:</h5>
                                        <h3 id="currency_preview" class="mb-0"><?= htmlspecialchars($currency['symbol']) ?>1,234.56</h3>
                                    </div>
                                </div>
                                
                            <div class="row mb-3">
                                <div class="col-md-6">
                                        <label for="currency_code" class="form-label fw-medium">Currency Code</label>
                                    <select class="form-select" id="currency_code" name="currency_code">
                                        <option value="USD" <?= $currency['code'] === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                                        <option value="EUR" <?= $currency['code'] === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                                        <option value="GBP" <?= $currency['code'] === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                                        <option value="CAD" <?= $currency['code'] === 'CAD' ? 'selected' : '' ?>>CAD - Canadian Dollar</option>
                                        <option value="AUD" <?= $currency['code'] === 'AUD' ? 'selected' : '' ?>>AUD - Australian Dollar</option>
                                        <option value="JPY" <?= $currency['code'] === 'JPY' ? 'selected' : '' ?>>JPY - Japanese Yen</option>
                                        <option value="CNY" <?= $currency['code'] === 'CNY' ? 'selected' : '' ?>>CNY - Chinese Yuan</option>
                                        <option value="INR" <?= $currency['code'] === 'INR' ? 'selected' : '' ?>>INR - Indian Rupee</option>
                                        <option value="BRL" <?= $currency['code'] === 'BRL' ? 'selected' : '' ?>>BRL - Brazilian Real</option>
                                        <option value="ZAR" <?= $currency['code'] === 'ZAR' ? 'selected' : '' ?>>ZAR - South African Rand</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                        <label for="currency_symbol" class="form-label fw-medium">Currency Symbol</label>
                                    <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" value="<?= htmlspecialchars($currency['symbol']) ?>" maxlength="5">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                        <label for="currency_position" class="form-label fw-medium">Symbol Position</label>
                                        <div class="d-flex">
                                            <div class="form-check me-4">
                                                <input class="form-check-input" type="radio" name="currency_position" id="position_before" value="before" <?= $currency['position'] === 'before' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="position_before">
                                                    Before ($100)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="currency_position" id="position_after" value="after" <?= $currency['position'] === 'after' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="position_after">
                                                    After (100$)
                                                </label>
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-6">
                                        <label for="decimals" class="form-label fw-medium">Number of Decimals</label>
                                    <select class="form-select" id="decimals" name="decimals">
                                        <option value="0" <?= $currency['decimals'] === 0 ? 'selected' : '' ?>>0 (e.g., 100)</option>
                                        <option value="1" <?= $currency['decimals'] === 1 ? 'selected' : '' ?>>1 (e.g., 100.5)</option>
                                        <option value="2" <?= $currency['decimals'] === 2 ? 'selected' : '' ?>>2 (e.g., 100.50)</option>
                                        <option value="3" <?= $currency['decimals'] === 3 ? 'selected' : '' ?>>3 (e.g., 100.500)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                        <label for="thousands_separator" class="form-label fw-medium">Thousands Separator</label>
                                    <select class="form-select" id="thousands_separator" name="thousands_separator">
                                        <option value="," <?= $currency['thousands_separator'] === ',' ? 'selected' : '' ?>>Comma (,) - 1,000,000</option>
                                        <option value="." <?= $currency['thousands_separator'] === '.' ? 'selected' : '' ?>>Period (.) - 1.000.000</option>
                                        <option value=" " <?= $currency['thousands_separator'] === ' ' ? 'selected' : '' ?>>Space ( ) - 1 000 000</option>
                                        <option value="" <?= $currency['thousands_separator'] === '' ? 'selected' : '' ?>>None - 1000000</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                        <label for="decimal_separator" class="form-label fw-medium">Decimal Separator</label>
                                    <select class="form-select" id="decimal_separator" name="decimal_separator">
                                        <option value="." <?= $currency['decimal_separator'] === '.' ? 'selected' : '' ?>>Period (.) - 100.50</option>
                                        <option value="," <?= $currency['decimal_separator'] === ',' ? 'selected' : '' ?>>Comma (,) - 100,50</option>
                                    </select>
                                </div>
                            </div>
                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <button type="reset" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Changes
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save me-2"></i>Save Settings
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Email Configuration Quick Setup Functions
function setupGmail() {
    document.getElementById('smtp_host').value = 'smtp.gmail.com';
    document.getElementById('smtp_port').value = '587';
    document.getElementById('smtp_encryption').value = 'tls';
    document.getElementById('imap_host').value = 'imap.gmail.com';
    document.getElementById('imap_port').value = '993';
    document.getElementById('imap_encryption').value = 'ssl';
    document.getElementById('imap_folder').value = 'INBOX';
    
    alert('✅ Gmail settings configured! Please enter your email and app password.');
}

function setupOutlook() {
    document.getElementById('smtp_host').value = 'smtp-mail.outlook.com';
    document.getElementById('smtp_port').value = '587';
    document.getElementById('smtp_encryption').value = 'tls';
    document.getElementById('imap_host').value = 'outlook.office365.com';
    document.getElementById('imap_port').value = '993';
    document.getElementById('imap_encryption').value = 'ssl';
    document.getElementById('imap_folder').value = 'INBOX';
    
    alert('✅ Outlook/Microsoft 365 settings configured! Please enter your email and app password.');
}

function clearSettings() {
    if (confirm('Are you sure you want to clear all email settings?')) {
        // Clear SMTP settings
        document.getElementById('from_email').value = '';
        document.getElementById('from_name').value = 'Hive IT Portal';
        document.getElementById('smtp_host').value = '';
        document.getElementById('smtp_port').value = '587';
        document.getElementById('smtp_username').value = '';
        document.getElementById('smtp_password').value = '';
        document.getElementById('smtp_encryption').value = 'tls';
        
        // Clear IMAP settings
        document.getElementById('imap_host').value = '';
        document.getElementById('imap_port').value = '993';
        document.getElementById('imap_username').value = '';
        document.getElementById('imap_password').value = '';
        document.getElementById('imap_encryption').value = 'ssl';
        document.getElementById('imap_folder').value = 'INBOX';
        
        // Reset ticket processing settings
        document.getElementById('auto_process_emails').checked = true;
        document.getElementById('delete_processed_emails').checked = false;
        document.getElementById('ticket_email_pattern').value = '/\\[TKT-\\d{4}-\\d{6}\\]/';
        document.getElementById('max_attachment_size').value = '10';
        document.getElementById('allowed_file_types').value = 'pdf,doc,docx,txt,png,jpg,jpeg,gif';
        
        alert('✅ Email settings cleared!');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Toggle for maintenance mode label
    const maintenanceToggle = document.getElementById('maintenance_mode');
    if (maintenanceToggle) {
        maintenanceToggle.addEventListener('change', function() {
            const label = this.nextElementSibling;
            if (this.checked) {
                label.textContent = 'Maintenance Mode Active';
                label.classList.add('text-danger', 'fw-bold');
            } else {
                label.textContent = 'Maintenance Mode';
                label.classList.remove('text-danger', 'fw-bold');
            }
        });
        // Trigger once on load
        maintenanceToggle.dispatchEvent(new Event('change'));
    }
    
    // Show/hide password toggle
    const togglePassword = document.getElementById('toggle_password');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const passwordInput = document.getElementById('smtp_password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    }
    
    // Date format preview
    const dateFormatSelect = document.getElementById('default_date_format');
    if (dateFormatSelect) {
        const formatPreview = document.getElementById('date_format_preview');
        dateFormatSelect.addEventListener('change', function() {
            // This is a simple preview, in a real app you'd use a proper date formatter
            const format = this.value;
            let preview = 'January 1, 2023';
            
            switch(format) {
                case 'Y-m-d':
                    preview = '2023-01-01';
                    break;
                case 'm/d/Y':
                    preview = '01/01/2023';
                    break;
                case 'd/m/Y':
                    preview = '01/01/2023';
                    break;
                case 'M j, Y':
                    preview = 'Jan 1, 2023';
                    break;
            }
            
            formatPreview.textContent = preview;
        });
        // Trigger on page load
        dateFormatSelect.dispatchEvent(new Event('change'));
    }
    
    // Test email button
    const testEmailBtn = document.getElementById('test_email');
    if (testEmailBtn) {
        testEmailBtn.addEventListener('click', function() {
            // In a real app, this would make an AJAX request to test the email configuration
            this.disabled = true;
            this.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Sending Test Email...';
            
            setTimeout(() => {
                this.innerHTML = '<i class="bi bi-check-circle me-2"></i>Test Email Sent!';
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-success');
                
                setTimeout(() => {
                    this.innerHTML = '<i class="bi bi-envelope-check me-2"></i>Test Email Configuration';
                    this.classList.remove('btn-success');
                    this.classList.add('btn-outline-primary');
                    this.disabled = false;
                }, 3000);
            }, 2000);
        });
    }
    
    // Currency preview functionality
    const updateCurrencyPreview = function() {
        const code = document.getElementById('currency_code').value;
        const symbol = document.getElementById('currency_symbol').value;
        const position = document.querySelector('input[name="currency_position"]:checked').value;
        const decimals = parseInt(document.getElementById('decimals').value);
        const thousandsSep = document.getElementById('thousands_separator').value;
        const decimalSep = document.getElementById('decimal_separator').value;
        
        // Example amount
        let amount = 1234.56;
        
        // Format with specified decimals
        let formattedAmount = amount.toFixed(decimals);
        
        // Split into parts before and after decimal
        let parts = formattedAmount.split('.');
        
        // Format thousands
        if (thousandsSep) {
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);
        }
        
        // Join with decimal separator
        let result = parts.length > 1 ? parts[0] + decimalSep + parts[1] : parts[0];
        
        // Add currency symbol based on position
        if (position === 'before') {
            result = symbol + result;
        } else {
            result = result + symbol;
        }
        
        // Update preview
        document.getElementById('currency_preview').textContent = result;
    };
    
    // Email Configuration JavaScript
    
    // Password toggle functionality
    document.getElementById('toggle_smtp_password')?.addEventListener('click', function() {
        const passwordField = document.getElementById('smtp_password');
        const icon = this.querySelector('i');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            passwordField.type = 'password';
            icon.className = 'bi bi-eye';
        }
    });
    
    document.getElementById('toggle_inbound_password')?.addEventListener('click', function() {
        const passwordField = document.getElementById('inbound_password');
        const icon = this.querySelector('i');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            passwordField.type = 'password';
            icon.className = 'bi bi-eye';
        }
    });
    
    // Test SMTP Configuration
    document.getElementById('test_smtp')?.addEventListener('click', function() {
        const button = this;
        const originalText = button.innerHTML;
        
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing...';
        
        const formData = new FormData();
        formData.append('action', 'test_smtp');
        formData.append('smtp_host', document.getElementById('smtp_host').value);
        formData.append('smtp_port', document.getElementById('smtp_port').value);
        formData.append('smtp_username', document.getElementById('smtp_username').value);
        formData.append('smtp_password', document.getElementById('smtp_password').value);
        formData.append('smtp_encryption', document.getElementById('smtp_encryption').value);
        formData.append('from_email', document.getElementById('from_email').value);
        formData.append('from_name', document.getElementById('from_name').value);
        
        fetch('<?= URLROOT ?>/admin/testEmail', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned non-JSON response. Check server logs for errors.');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('✅ SMTP configuration test successful! Check your email.');
            } else {
                alert('❌ SMTP test failed: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('SMTP Test Error:', error);
            alert('❌ Test failed: ' + error.message);
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
    });
    
    // Handle protocol switching
    document.querySelectorAll('input[name="inbound_protocol"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            updateProtocolSettings();
        });
    });
    
    // Handle authentication type switching
    document.querySelectorAll('input[name="inbound_auth_type"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            updateAuthSettings();
        });
    });
    
    function updateProtocolSettings() {
        const protocol = document.querySelector('input[name="inbound_protocol"]:checked').value;
        const imapFolderGroup = document.getElementById('imap_folder_group');
        const protocolInfoText = document.getElementById('protocol_info_text');
        const deleteEmailsHelp = document.getElementById('delete_emails_help');
        const portField = document.getElementById('inbound_port');
        const hostField = document.getElementById('inbound_host');
        
        if (protocol === 'imap') {
            // Show IMAP-specific fields
            imapFolderGroup.style.display = 'block';
            
            // Update default port
            if (portField.value === '995' || portField.value === '') {
                portField.value = '993';
            }
            
            // Update placeholder for host
            hostField.placeholder = 'imap.gmail.com or outlook.office365.com';
            
            // Update info text
            protocolInfoText.innerHTML = '<strong>IMAP:</strong> Keeps emails on server, supports folders, allows multiple clients to access the same mailbox.';
            deleteEmailsHelp.textContent = 'Remove emails from server after processing (optional for IMAP)';
        } else {
            // Hide IMAP-specific fields
            imapFolderGroup.style.display = 'none';
            
            // Update default port
            if (portField.value === '993' || portField.value === '') {
                portField.value = '995';
            }
            
            // Update placeholder for host
            hostField.placeholder = 'pop.gmail.com or outlook.office365.com';
            
            // Update info text
            protocolInfoText.innerHTML = '<strong>POP3:</strong> Downloads emails to server, simpler setup, but doesn\'t support folders.';
            deleteEmailsHelp.textContent = 'Remove emails from server after processing (recommended for POP3)';
        }
    }
    
    function updateAuthSettings() {
        const authType = document.querySelector('input[name="inbound_auth_type"]:checked').value;
        const oauth2ConfigGroup = document.getElementById('oauth2_config_group');
        const passwordFields = document.querySelectorAll('#inbound_password, #toggle_inbound_password');
        
        if (authType === 'oauth2') {
            // Show OAuth2 configuration
            oauth2ConfigGroup.style.display = 'block';
            
            // Hide password field
            passwordFields.forEach(field => {
                field.closest('.mb-3').style.display = 'none';
            });
            
            // Update OAuth2 status
            updateOAuth2Status();
        } else {
            // Hide OAuth2 configuration
            oauth2ConfigGroup.style.display = 'none';
            
            // Show password field
            passwordFields.forEach(field => {
                field.closest('.mb-3').style.display = 'block';
            });
        }
    }
    
    // OAuth2 Functions
    function updateOAuth2Status() {
        const provider = document.getElementById('oauth2_provider').value;
        
        fetch('<?= URLROOT ?>/oauth2/status?provider=' + provider)
        .then(response => response.json())
        .then(data => {
            const authorizeBtn = document.getElementById('oauth2_authorize');
            const testBtn = document.getElementById('oauth2_test');
            const revokeBtn = document.getElementById('oauth2_revoke');
            
            if (data.authenticated) {
                authorizeBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Re-authorize OAuth2';
                authorizeBtn.className = 'btn btn-outline-success';
                testBtn.disabled = false;
                revokeBtn.disabled = false;
                
                if (data.expired) {
                    authorizeBtn.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Token Expired - Re-authorize';
                    authorizeBtn.className = 'btn btn-warning';
                }
            } else {
                authorizeBtn.innerHTML = '<i class="bi bi-shield-check me-2"></i>Authorize with OAuth2';
                authorizeBtn.className = 'btn btn-success';
                testBtn.disabled = true;
                revokeBtn.disabled = true;
            }
        })
        .catch(error => {
            console.error('OAuth2 Status Error:', error);
        });
    }
    
    // OAuth2 Authorization
    document.getElementById('oauth2_authorize')?.addEventListener('click', function() {
        const provider = document.getElementById('oauth2_provider').value;
        const clientId = document.getElementById('oauth2_client_id').value;
        const redirectUri = document.getElementById('oauth2_redirect_uri').value;
        
        if (!clientId || !redirectUri) {
            alert('Please configure Client ID and Redirect URI first, then save settings.');
            return;
        }
        
        // Open OAuth2 authorization in new window
        const authUrl = '<?= URLROOT ?>/oauth2/authorize?provider=' + provider;
        window.open(authUrl, 'oauth2_auth', 'width=600,height=700,scrollbars=yes,resizable=yes');
    });
    
    // OAuth2 Test
    document.getElementById('oauth2_test')?.addEventListener('click', function() {
        const button = this;
        const originalText = button.innerHTML;
        
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing...';
        
        fetch('<?= URLROOT ?>/oauth2/test')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ OAuth2 connection successful! Provider: ' + data.provider);
            } else {
                alert('❌ OAuth2 test failed: ' + data.error);
            }
        })
        .catch(error => {
            alert('❌ Test failed: ' + error.message);
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
    });
    
    // OAuth2 Revoke
    document.getElementById('oauth2_revoke')?.addEventListener('click', function() {
        if (!confirm('Are you sure you want to revoke OAuth2 authorization? You will need to re-authorize to use OAuth2 authentication.')) {
            return;
        }
        
        const provider = document.getElementById('oauth2_provider').value;
        const formData = new FormData();
        formData.append('provider', provider);
        
        fetch('<?= URLROOT ?>/oauth2/revoke', {
            method: 'POST',
            body: formData
        })
        .then(() => {
            updateOAuth2Status();
            alert('OAuth2 authorization revoked successfully');
        })
        .catch(error => {
            alert('Failed to revoke authorization: ' + error.message);
        });
    });
    
    // OAuth2 Client Secret Toggle
    document.getElementById('toggle_oauth2_secret')?.addEventListener('click', function() {
        const passwordField = document.getElementById('oauth2_client_secret');
        const icon = this.querySelector('i');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            passwordField.type = 'password';
            icon.className = 'bi bi-eye';
        }
    });
    
    // Initialize display on page load
    document.addEventListener('DOMContentLoaded', function() {
        const selectedProtocol = document.querySelector('input[name="inbound_protocol"]:checked');
        if (selectedProtocol) {
            updateProtocolSettings();
        }
        
        const selectedAuth = document.querySelector('input[name="inbound_auth_type"]:checked');
        if (selectedAuth) {
            updateAuthSettings();
        }
    });
    
    // Test Inbound Email Configuration (supports both IMAP and POP3)
    document.getElementById('test_inbound')?.addEventListener('click', function() {
        const button = this;
        const originalText = button.innerHTML;
        const protocol = document.querySelector('input[name="inbound_protocol"]:checked').value;
        
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing...';
        
        const formData = new FormData();
        formData.append('action', 'test_inbound');
        formData.append('inbound_protocol', protocol);
        formData.append('inbound_host', document.getElementById('inbound_host').value);
        formData.append('inbound_port', document.getElementById('inbound_port').value);
        formData.append('inbound_username', document.getElementById('inbound_username').value);
        formData.append('inbound_password', document.getElementById('inbound_password').value);
        formData.append('inbound_encryption', document.getElementById('inbound_encryption').value);
        if (protocol === 'imap') {
            formData.append('imap_folder', document.getElementById('imap_folder').value);
        }
        
        fetch('<?= URLROOT ?>/admin/testEmail', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned non-JSON response. Check server logs for errors.');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.protocol + ' connection test successful! Found ' + (data.message_count || 0) + ' messages.');
            } else {
                alert('❌ ' + protocol.toUpperCase() + ' test failed: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Inbound Email Test Error:', error);
            alert('❌ Test failed: ' + error.message);
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
    });

    // Track changes to currency-related inputs
    document.getElementById('currency_code')?.addEventListener('change', function() {
        // Update currency symbol based on code
        const currencySymbols = {
            'USD': '$', 'EUR': '€', 'GBP': '£', 'CAD': 'C$',
            'AUD': 'A$', 'JPY': '¥', 'CNY': '¥', 'INR': '₹',
            'BRL': 'R$', 'ZAR': 'R'
        };
        
        const code = this.value;
        if (currencySymbols[code]) {
            document.getElementById('currency_symbol').value = currencySymbols[code];
        }
        updateCurrencyPreview();
    });
    
    // Add event listeners to all currency settings fields
    ['currency_symbol', 'decimals', 'thousands_separator', 'decimal_separator'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', updateCurrencyPreview);
    });
    
    // Radio button listeners
    document.querySelectorAll('input[name="currency_position"]').forEach(radio => {
        radio.addEventListener('change', updateCurrencyPreview);
    });
    
    // Initial preview update
    if (document.getElementById('currency_preview')) {
        updateCurrencyPreview();
    }
});
</script>

<?php require VIEWSPATH . '/inc/footer.php'; ?> 