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
                    <div class="col-lg-8 mx-auto">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex align-items-center">
                                    <span class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                        <i class="bi bi-envelope fs-4 text-primary"></i>
                                    </span>
                                    <h5 class="mb-0">Email Configuration</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info d-flex align-items-center" role="alert">
                                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                                    <div>
                                        Proper email configuration is essential for system notifications, password resets, and user invitations.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="system_email" class="form-label fw-medium">System Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-at"></i></span>
                                        <input type="email" class="form-control" id="system_email" name="system_email" 
                                            placeholder="notifications@yourcompany.com"
                                            value="<?= $systemSettings['system_email'] ?? '' ?>">
                                    </div>
                                    <div class="form-text">Address used for system notifications.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="smtp_host" class="form-label fw-medium">SMTP Host</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-hdd-network"></i></span>
                                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                            placeholder="smtp.example.com"
                                            value="<?= $systemSettings['smtp_host'] ?? '' ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="smtp_port" class="form-label fw-medium">SMTP Port</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-signpost"></i></span>
                                            <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                                placeholder="587"
                                                value="<?= $systemSettings['smtp_port'] ?? '587' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
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
                                            placeholder="username@example.com"
                                            value="<?= $systemSettings['smtp_username'] ?? '' ?>">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                                    <label for="smtp_password" class="form-label fw-medium">SMTP Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                                        <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                            value="<?= $systemSettings['smtp_password'] ?? '' ?>">
                                        <button class="btn btn-outline-secondary" type="button" id="toggle_password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-primary" id="test_email">
                                        <i class="bi bi-envelope-check me-2"></i>Test Email Configuration
                                    </button>
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