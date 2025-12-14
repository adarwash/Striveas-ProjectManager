<style>
    .settings-nav .list-group-item {
        transition: background-color 0.2s ease-in-out, border-left-color 0.2s ease-in-out;
        border-left: 3px solid transparent;
    }
    .settings-nav .list-group-item.active {
        border-left-color: var(--primary-color, #0d6efd);
        background-color: var(--primary-light, #eef2ff);
        color: var(--primary-color, #0d6efd);
        font-weight: 600;
    }
    .settings-nav .list-group-item:hover:not(.active) {
        background-color: #f8f9fa;
        border-left-color: #e2e8f0;
    }
    .card-header h6 {
        font-weight: 600;
    }
</style>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="bi bi-gear-fill text-primary"></i> System Settings
            </h1>
            <p class="text-muted mb-0">Configure system-wide settings, defaults, and integrations.</p>
        </div>
        <div>
            <a href="/admin" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-2"></i>Back to Admin
            </a>
            <a href="/admin/emailSettings" class="btn btn-info text-white">
                <i class="bi bi-envelope me-2"></i>Ticket Email Settings
            </a>
        </div>
    </div>
    <hr class="mt-0 mb-4">


    <!-- Flash Messages -->
    <?php flash('settings_success'); ?>
    <?php flash('settings_error'); ?>


    <!-- Settings Content -->
    <div class="row">
        <div class="col-lg-3 mb-4">
            <!-- Settings Navigation -->
            <div class="card border-0 shadow-sm settings-nav">
                <div class="card-body p-2">
                    <div class="list-group list-group-flush">
                        <a href="#application" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="bi bi-gear-wide-connected me-2"></i> Application
                        </a>
                        <a href="#defaults" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-sliders me-2"></i> Defaults
                        </a>
                        <a href="#email" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-envelope me-2"></i> Ticket Email
                        </a>
                        <a href="#currency" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-currency-exchange me-2"></i> Currency
                        </a>
                        <a href="#authentication" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-shield-lock me-2"></i> Authentication
                        </a>
                        <a href="#backup" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-database-down me-2"></i> Backup
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <!-- Tab Content -->
            <form action="<?= URLROOT ?>/admin/settings" method="POST">
            <div class="tab-content">
                <!-- Application Settings Tab -->
                <div class="tab-pane fade show active" id="application">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-toggles text-primary me-2"></i>System Controls
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="maintenance_mode" name="maintenance_mode" 
                                                <?= isset($data['systemSettings']['maintenance_mode']) && $data['systemSettings']['maintenance_mode'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="maintenance_mode">
                                                Maintenance Mode
                                            </label>
                                            <div class="form-text">When enabled, only administrators can access the application.</div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="enable_registration" name="enable_registration" 
                                                <?= !isset($data['systemSettings']['enable_registration']) || $data['systemSettings']['enable_registration'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="enable_registration">
                                                User Registration
                                            </label>
                                            <div class="form-text">Allow new users to register accounts.</div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="show_sidebar_time_status" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="show_sidebar_time_status" name="show_sidebar_time_status" value="1"
                                                <?= !isset($data['systemSettings']['show_sidebar_time_status']) || $data['systemSettings']['show_sidebar_time_status'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="show_sidebar_time_status">
                                                Sidebar Time Status
                                            </label>
                                            <div class="form-text">Show the time tracking status widget in the sidebar.</div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="enable_api" name="enable_api" 
                                                <?= isset($data['systemSettings']['enable_api']) && $data['systemSettings']['enable_api'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="enable_api">
                                                API Access
                                            </label>
                                            <div class="form-text">Enable API endpoints for third-party integrations.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-shield-lock text-primary me-2"></i>System Limits
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="max_upload_size" class="form-label fw-medium">Maximum Upload Size (MB)</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="max_upload_size" name="max_upload_size" min="1" max="100" 
                                                value="<?= $data['systemSettings']['max_upload_size'] ?? '10' ?>">
                                            <span class="input-group-text">MB</span>
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <label for="max_projects" class="form-label fw-medium">Projects Per User</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="max_projects" name="max_projects" min="0" max="1000" 
                                                value="<?= $data['systemSettings']['max_projects'] ?? '0' ?>">
                                            <span class="input-group-text">projects</span>
                                        </div>
                                        <div class="form-text">Enter 0 for unlimited</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-people text-primary me-2"></i>Prospect Follow-ups
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" role="switch" id="prospect_followup_enabled" name="prospect_followup_enabled"
                                            <?= !empty($data['systemSettings']['prospect_followup_enabled']) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-medium" for="prospect_followup_enabled">
                                            Enable automatic follow-ups for prospect clients
                                        </label>
                                        <div class="form-text">Creates reminders on a schedule until the client status changes.</div>
                                    </div>
                                    <div class="mb-0">
                                        <label for="prospect_followup_interval_days" class="form-label fw-medium">Interval</label>
                                        <div class="input-group">
                                            <input type="number" min="1" class="form-control" id="prospect_followup_interval_days" name="prospect_followup_interval_days"
                                                value="<?= htmlspecialchars($data['systemSettings']['prospect_followup_interval_days'] ?? 14) ?>">
                                            <span class="input-group-text">days</span>
                                        </div>
                                        <div class="form-text">Applies to clients with status = Prospect.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-calendar-date text-primary me-2"></i>Display Settings
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="default_date_format" class="form-label fw-medium">Date Format</label>
                                            <select class="form-select" id="default_date_format" name="default_date_format">
                                                <option value="Y-m-d" <?= (isset($data['systemSettings']['default_date_format']) && $data['systemSettings']['default_date_format'] == 'Y-m-d') ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                                <option value="m/d/Y" <?= (isset($data['systemSettings']['default_date_format']) && $data['systemSettings']['default_date_format'] == 'm/d/Y') ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                                <option value="d/m/Y" <?= (isset($data['systemSettings']['default_date_format']) && $data['systemSettings']['default_date_format'] == 'd/m/Y') ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                                <option value="M j, Y" <?= (!isset($data['systemSettings']['default_date_format']) || $data['systemSettings']['default_date_format'] == 'M j, Y') ? 'selected' : '' ?>>Month D, YYYY</option>
                                            </select>
                                            <div class="form-text mt-2">
                                                Preview: <span class="badge bg-light text-dark border" id="date_format_preview">January 1, 2024</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="display_timezone" class="form-label fw-medium">Display Timezone</label>
                                            <select class="form-select" id="display_timezone" name="display_timezone">
                                                <?php
                                                    $tzCurrent = $data['systemSettings']['display_timezone'] ?? 'America/Los_Angeles';
                                                    $tzOptions = [
                                                        'America/Los_Angeles',
                                                        'America/Toronto',
                                                        'America/New_York',
                                                        'America/Chicago',
                                                        'America/Denver',
                                                        'Europe/London',
                                                        'Europe/Paris',
                                                        'Asia/Dubai',
                                                        'Asia/Kolkata',
                                                        'Australia/Sydney',
                                                        'UTC'
                                                    ];
                                                    foreach ($tzOptions as $tzVal):
                                                ?>
                                                <option value="<?= $tzVal ?>" <?= $tzCurrent === $tzVal ? 'selected' : '' ?>><?= $tzVal ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Controls displayed times; default is America/Los_Angeles.</div>
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label for="db_timezone" class="form-label fw-medium">Database Timestamp Timezone</label>
                                            <select class="form-select" id="db_timezone" name="db_timezone">
                                                <?php
                                                    $dbTzCurrent = $data['systemSettings']['db_timezone'] ?? 'America/Toronto';
                                                    $dbTzOptions = $tzOptions;
                                                    foreach ($dbTzOptions as $tzVal):
                                                ?>
                                                <option value="<?= $tzVal ?>" <?= $dbTzCurrent === $tzVal ? 'selected' : '' ?>><?= $tzVal ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Timezone that DB timestamps are stored in (default America/Toronto).</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-1">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-plug text-primary me-2"></i>Level.io Integration
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">
                                        Manage the Level.io integration for enriched account insights and automation.
                                    </p>
                                    <div class="mb-3">
                                        <input type="hidden" name="level_io_enabled" value="0">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input level-io-toggle"
                                                   type="checkbox"
                                                   id="level_io_enabled"
                                                   name="level_io_enabled"
                                                   value="1"
                                                   <?= !empty($data['systemSettings']['level_io_enabled']) ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-medium" for="level_io_enabled">
                                                Enable Level.io Integration
                                            </label>
                                            <div class="form-text">When disabled, no requests are made to Level.io even if an API key is stored.</div>
                                        </div>
                                    </div>
                                    <label for="level_io_api_key" class="form-label fw-medium">API Key</label>
                                    <div class="input-group mb-2">
                                        <input type="password"
                                               class="form-control"
                                               id="level_io_api_key"
                                               name="level_io_api_key"
                                               value="<?= htmlspecialchars($data['systemSettings']['level_io_api_key'] ?? '') ?>"
                                               placeholder="lvl_live_XXXXXXXXXXXXXXXX"
                                               <?= empty($data['systemSettings']['level_io_enabled']) ? 'disabled' : '' ?>
                                               autocomplete="off">
                                        <button class="btn btn-outline-secondary toggle-api-visibility"
                                                type="button"
                                                data-target="#level_io_api_key"
                                                aria-label="Toggle API key visibility">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        The key is stored securely. Leave blank to remove.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Defaults Tab -->
                <div class="tab-pane fade" id="defaults">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-kanban text-primary me-2"></i>Project Defaults
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="default_project_category" class="form-label fw-medium">Default Project Category</label>
                                        <input type="text" class="form-control" id="default_project_category" name="default_project_category" 
                                            value="<?= $data['systemSettings']['default_project_category'] ?? 'General' ?>">
                                    </div>
                                    <div class="mb-0">
                                        <label for="default_project_status" class="form-label fw-medium">Default Project Status</label>
                                        <select class="form-select" id="default_project_status" name="default_project_status">
                                            <option value="Planning" <?= (isset($data['systemSettings']['default_project_status']) && $data['systemSettings']['default_project_status'] == 'Planning') ? 'selected' : '' ?>>Planning</option>
                                            <option value="In Progress" <?= (isset($data['systemSettings']['default_project_status']) && $data['systemSettings']['default_project_status'] == 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                                            <option value="On Hold" <?= (isset($data['systemSettings']['default_project_status']) && $data['systemSettings']['default_project_status'] == 'On Hold') ? 'selected' : '' ?>>On Hold</option>
                                            <option value="Completed" <?= (isset($data['systemSettings']['default_project_status']) && $data['systemSettings']['default_project_status'] == 'Completed') ? 'selected' : '' ?>>Completed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-check2-square text-primary me-2"></i>Task Defaults
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="default_task_priority" class="form-label fw-medium">Default Task Priority</label>
                                        <select class="form-select" id="default_task_priority" name="default_task_priority">
                                            <option value="Low" <?= (isset($data['systemSettings']['default_task_priority']) && $data['systemSettings']['default_task_priority'] == 'Low') ? 'selected' : '' ?>>Low</option>
                                            <option value="Medium" <?= (!isset($data['systemSettings']['default_task_priority']) || $data['systemSettings']['default_task_priority'] == 'Medium') ? 'selected' : '' ?>>Medium</option>
                                            <option value="High" <?= (isset($data['systemSettings']['default_task_priority']) && $data['systemSettings']['default_task_priority'] == 'High') ? 'selected' : '' ?>>High</option>
                                            <option value="Critical" <?= (isset($data['systemSettings']['default_task_priority']) && $data['systemSettings']['default_task_priority'] == 'Critical') ? 'selected' : '' ?>>Critical</option>
                                        </select>
                                    </div>
                                    
                                    <div class="alert alert-light border mb-0">
                                        <h6 class="alert-heading mb-2 small text-muted"><i class="bi bi-info-circle me-1"></i>Priority Guide</h6>
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-success me-2" style="width: 65px;">Low</span>
                                            <small class="text-muted">Minor tasks, flexible timing</small>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-info me-2" style="width: 65px;">Medium</span>
                                            <small class="text-muted">Standard priority tasks</small>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-warning me-2" style="width: 65px;">High</span>
                                            <small class="text-muted">Important, needs attention</small>
                                        </div>
                                        <div class="d-flex align-items-center mb-0">
                                            <span class="badge bg-danger me-2" style="width: 65px;">Critical</span>
                                            <small class="text-muted">Urgent, immediate action</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Settings Tab -->
                <div class="tab-pane fade" id="email">
                    <div class="row">
                        <div class="col-lg-10 mx-auto">
                            
                            <!-- Microsoft Graph API Settings Card -->
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-microsoft fs-4 me-3"></i>
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
                                        <div class="col-md-4 text-center">
                                            <a href="<?= URLROOT ?>/admin/emailSettings" class="btn btn-primary btn-lg">
                                                <i class="bi bi-gear-fill me-2"></i>Configure Now
                                            </a>
                                            <p class="text-muted small mt-2 mb-0">Setup takes ~5 minutes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> The SMTP/IMAP settings below are legacy options. For Microsoft 365 users, we strongly recommend using the Graph API configuration above.
                            </div>
                            
                            <!-- SMTP Configuration -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-send text-success me-2"></i>SMTP Configuration (Outbound)
                                    </h6>
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
                                                        value="<?= $data['systemSettings']['from_email'] ?? '' ?>">
                                                </div>
                                                <div class="form-text">Email address used for ticket notifications</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="from_name" class="form-label fw-medium">From Name</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                                    <input type="text" class="form-control" id="from_name" name="from_name" 
                                                        placeholder="Your Company Support"
                                                        value="<?= $data['systemSettings']['from_name'] ?? SITENAME ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="smtp_host" class="form-label fw-medium">SMTP Host</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-hdd-network"></i></span>
                                                    <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                                        placeholder="smtp.gmail.com"
                                                        value="<?= $data['systemSettings']['smtp_host'] ?? '' ?>">
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
                                                            value="<?= $data['systemSettings']['smtp_port'] ?? '587' ?>">
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <label for="smtp_encryption" class="form-label fw-medium">Encryption</label>
                                                    <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                                        <option value="tls" <?= (isset($data['systemSettings']['smtp_encryption']) && $data['systemSettings']['smtp_encryption'] == 'tls') ? 'selected' : '' ?>>TLS</option>
                                                        <option value="ssl" <?= (isset($data['systemSettings']['smtp_encryption']) && $data['systemSettings']['smtp_encryption'] == 'ssl') ? 'selected' : '' ?>>SSL</option>
                                                        <option value="none" <?= (isset($data['systemSettings']['smtp_encryption']) && $data['systemSettings']['smtp_encryption'] == 'none') ? 'selected' : '' ?>>None</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="smtp_username" class="form-label fw-medium">SMTP Username</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                                    <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                                                        placeholder="support@yourcompany.com"
                                                        value="<?= $data['systemSettings']['smtp_username'] ?? '' ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="smtp_password" class="form-label fw-medium">SMTP Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                                                    <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                                        placeholder="Use app password for Gmail/Outlook"
                                                        value="<?= $data['systemSettings']['smtp_password'] ?? '' ?>">
                                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('smtp_password')">
                                                        <i class="bi bi-eye" id="smtp_password_icon"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 d-grid">
                                        <button type="button" class="btn btn-outline-success" onclick="testSmtp()">
                                            <i class="bi bi-envelope-check me-2"></i>Test SMTP Configuration
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Ticket Processing Settings -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-gear-wide-connected text-info me-2"></i>Ticket Processing Settings
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="auto_process_emails" name="auto_process_emails" 
                                                        <?= !isset($data['systemSettings']['auto_process_emails']) || $data['systemSettings']['auto_process_emails'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="auto_process_emails">
                                                        Auto-process emails
                                                    </label>
                                                    <div class="form-text">Automatically create tickets from incoming emails</div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="auto_acknowledge_tickets" name="auto_acknowledge_tickets" 
                                                        <?= !isset($data['systemSettings']['auto_acknowledge_tickets']) || $data['systemSettings']['auto_acknowledge_tickets'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="auto_acknowledge_tickets">
                                                        Auto-acknowledge new tickets
                                                    </label>
                                                    <div class="form-text">Send automatic acknowledgment emails</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="ticket_email_pattern" class="form-label fw-medium">Ticket Pattern</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-regex"></i></span>
                                                    <input type="text" class="form-control" id="ticket_email_pattern" name="ticket_email_pattern" 
                                                        placeholder="/\[TKT-\d{4}-\d{6}\]/"
                                                        value="<?= $data['systemSettings']['ticket_email_pattern'] ?? '/\[TKT-\d{4}-\d{6}\]/' ?>">
                                                </div>
                                                <div class="form-text">Regex pattern to identify existing tickets</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>Auto-acknowledgment:</strong> When enabled, customers receive a confirmation email with their ticket number.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Currency Tab -->
                <div class="tab-pane fade" id="currency">
                    <div class="row">
                        <div class="col-lg-10 mx-auto">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-currency-exchange text-primary me-2"></i>Currency Settings
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-light border d-flex align-items-center mb-4">
                                        <i class="bi bi-cash-stack fs-1 text-success me-4"></i>
                                        <div>
                                            <h6 class="mb-1 text-muted">Current Format Preview</h6>
                                            <h4 id="currency_preview" class="mb-0 fw-bold"><?= htmlspecialchars($data['currency']['symbol'] ?? '$') ?>1,234.56</h4>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="currency_code" class="form-label fw-medium">Currency Code</label>
                                            <select class="form-select" id="currency_code" name="currency_code">
                                                <option value="USD" <?= ($data['currency']['code'] ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                                                <option value="EUR" <?= ($data['currency']['code'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                                                <option value="GBP" <?= ($data['currency']['code'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                                                <option value="CAD" <?= ($data['currency']['code'] ?? '') === 'CAD' ? 'selected' : '' ?>>CAD - Canadian Dollar</option>
                                                <option value="AUD" <?= ($data['currency']['code'] ?? '') === 'AUD' ? 'selected' : '' ?>>AUD - Australian Dollar</option>
                                                <option value="JPY" <?= ($data['currency']['code'] ?? '') === 'JPY' ? 'selected' : '' ?>>JPY - Japanese Yen</option>
                                                <option value="CNY" <?= ($data['currency']['code'] ?? '') === 'CNY' ? 'selected' : '' ?>>CNY - Chinese Yuan</option>
                                                <option value="INR" <?= ($data['currency']['code'] ?? '') === 'INR' ? 'selected' : '' ?>>INR - Indian Rupee</option>
                                                <option value="BRL" <?= ($data['currency']['code'] ?? '') === 'BRL' ? 'selected' : '' ?>>BRL - Brazilian Real</option>
                                                <option value="ZAR" <?= ($data['currency']['code'] ?? '') === 'ZAR' ? 'selected' : '' ?>>ZAR - South African Rand</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="currency_symbol" class="form-label fw-medium">Currency Symbol</label>
                                            <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" value="<?= htmlspecialchars($data['currency']['symbol'] ?? '$') ?>" maxlength="5">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Symbol Position</label>
                                            <div class="d-flex">
                                                <div class="form-check me-4">
                                                    <input class="form-check-input" type="radio" name="currency_position" id="position_before" value="before" <?= ($data['currency']['position'] ?? 'before') === 'before' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="position_before">
                                                        Before ($100)
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="currency_position" id="position_after" value="after" <?= ($data['currency']['position'] ?? '') === 'after' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="position_after">
                                                        After (100$)
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="decimals" class="form-label fw-medium">Number of Decimals</label>
                                            <select class="form-select" id="decimals" name="decimals">
                                                <option value="0" <?= ($data['currency']['decimals'] ?? 2) === 0 ? 'selected' : '' ?>>0 (e.g., 100)</option>
                                                <option value="1" <?= ($data['currency']['decimals'] ?? 2) === 1 ? 'selected' : '' ?>>1 (e.g., 100.5)</option>
                                                <option value="2" <?= ($data['currency']['decimals'] ?? 2) === 2 ? 'selected' : '' ?>>2 (e.g., 100.50)</option>
                                                <option value="3" <?= ($data['currency']['decimals'] ?? 2) === 3 ? 'selected' : '' ?>>3 (e.g., 100.500)</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="thousands_separator" class="form-label fw-medium">Thousands Separator</label>
                                            <select class="form-select" id="thousands_separator" name="thousands_separator">
                                                <option value="," <?= ($data['currency']['thousands_separator'] ?? ',') === ',' ? 'selected' : '' ?>>Comma (,) - 1,000,000</option>
                                                <option value="." <?= ($data['currency']['thousands_separator'] ?? '') === '.' ? 'selected' : '' ?>>Period (.) - 1.000.000</option>
                                                <option value=" " <?= ($data['currency']['thousands_separator'] ?? '') === ' ' ? 'selected' : '' ?>>Space ( ) - 1 000 000</option>
                                                <option value="" <?= ($data['currency']['thousands_separator'] ?? '') === '' ? 'selected' : '' ?>>None - 1000000</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="decimal_separator" class="form-label fw-medium">Decimal Separator</label>
                                            <select class="form-select" id="decimal_separator" name="decimal_separator">
                                                <option value="." <?= ($data['currency']['decimal_separator'] ?? '.') === '.' ? 'selected' : '' ?>>Period (.) - 100.50</option>
                                                <option value="," <?= ($data['currency']['decimal_separator'] ?? '') === ',' ? 'selected' : '' ?>>Comma (,) - 100,50</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Authentication Tab -->
                <div class="tab-pane fade" id="authentication">
                    <div class="row">
                        <div class="col-lg-10 mx-auto">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-shield-lock text-primary me-2"></i>Customer Portal Authentication
                                    </h6>
                                </div>
                                <div class="card-body">
                                    
                                    <div class="alert alert-info d-flex align-items-start mb-4">
                                        <i class="bi bi-microsoft fs-4 text-info me-3 mt-1"></i>
                                        <div>
                                            <h6 class="mb-1">Microsoft 365 Integration</h6>
                                            <p class="mb-0 small">Allow customers to sign in with their Microsoft 365 accounts to view their support tickets. This requires Azure AD app registration.</p>
                                        </div>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="customer_auth_enabled" name="customer_auth_enabled" 
                                                       <?= (isset($data['systemSettings']['customer_auth_enabled']) && $data['systemSettings']['customer_auth_enabled']) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="customer_auth_enabled">
                                                    Enable Customer Portal Authentication
                                                </label>
                                                <div class="form-text">When enabled, customers can sign in to view their tickets</div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="azure_tenant_id" class="form-label fw-medium">
                                                <i class="bi bi-building me-1"></i>Azure Tenant ID
                                            </label>
                                            <input type="text" class="form-control" id="azure_tenant_id" name="azure_tenant_id" 
                                                   value="<?= htmlspecialchars($data['systemSettings']['azure_tenant_id'] ?? '') ?>" 
                                                   placeholder="common (for multi-tenant) or your tenant ID">
                                            <div class="form-text">Use 'common' for multi-tenant or your specific tenant ID</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="azure_client_id" class="form-label fw-medium">
                                                <i class="bi bi-key me-1"></i>Azure Client ID (Application ID)
                                            </label>
                                            <input type="text" class="form-control" id="azure_client_id" name="azure_client_id" 
                                                   value="<?= htmlspecialchars($data['systemSettings']['azure_client_id'] ?? '') ?>" 
                                                   placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                                            <div class="form-text">From your Azure AD app registration</div>
                                        </div>

                                        <div class="col-12">
                                            <label for="azure_client_secret" class="form-label fw-medium">
                                                <i class="bi bi-shield-lock me-1"></i>Azure Client Secret
                                            </label>
                                            <input type="password" class="form-control" id="azure_client_secret" name="azure_client_secret" 
                                                   value="<?= htmlspecialchars($data['systemSettings']['azure_client_secret'] ?? '') ?>" 
                                                   placeholder="Enter your client secret">
                                            <div class="form-text">Keep this secret secure. It will be encrypted in the database.</div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-medium">
                                                <i class="bi bi-link-45deg me-1"></i>Redirect URI (Read-only)
                                            </label>
                                            <div class="input-group">
                                                <input type="text" class="form-control bg-light" readonly 
                                                       value="<?= URLROOT ?>/customer/auth/callback" id="redirect_uri">
                                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('redirect_uri')">
                                                    <i class="bi bi-clipboard"></i> Copy
                                                </button>
                                            </div>
                                            <div class="form-text">Add this URL to your Azure AD app's redirect URIs</div>
                                        </div>

                                        <div class="col-12">
                                            <div class="d-flex gap-3 align-items-center">
                                                <button type="button" class="btn btn-outline-primary" onclick="testAzureConnection()">
                                                    <i class="bi bi-plug me-2"></i>Test Azure Connection
                                                </button>
                                                <div id="azure_connection_status" class="flex-grow-1">
                                                    <?php if (isset($data['systemSettings']['azure_connection_status']) && $data['systemSettings']['azure_connection_status'] === 'connected'): ?>
                                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Connected</span>
                                                        <small class="text-muted ms-2">Last connected: <?= $data['systemSettings']['azure_connected_at'] ?? 'Unknown' ?></small>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Not Connected</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Customer Access Control -->
                                    <hr class="my-4">
                                    <h6 class="mb-3">
                                        <i class="bi bi-people me-2"></i>Customer Access Control
                                    </h6>

                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="customer_domain_restriction" class="form-label fw-medium">Domain Restriction (Optional)</label>
                                            <input type="text" class="form-control" id="customer_domain_restriction" name="customer_domain_restriction" 
                                                   value="<?= htmlspecialchars($data['systemSettings']['customer_domain_restriction'] ?? '') ?>" 
                                                   placeholder="@company.com">
                                            <div class="form-text">Only allow users from specific domains. Leave empty for all domains.</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="ticket_visibility" class="form-label fw-medium">Ticket Visibility</label>
                                            <select class="form-select" id="ticket_visibility" name="ticket_visibility">
                                                <option value="email_match" <?= ($data['systemSettings']['ticket_visibility'] ?? 'email_match') === 'email_match' ? 'selected' : '' ?>>
                                                    Own tickets only (by email)
                                                </option>
                                                <option value="domain_match" <?= ($data['systemSettings']['ticket_visibility'] ?? '') === 'domain_match' ? 'selected' : '' ?>>
                                                    All company tickets (by domain)
                                                </option>
                                            </select>
                                            <div class="form-text">Control which tickets customers can see</div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="allow_ticket_creation" name="allow_ticket_creation" 
                                                       <?= (isset($data['systemSettings']['allow_ticket_creation']) && $data['systemSettings']['allow_ticket_creation']) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="allow_ticket_creation">
                                                    Allow customers to create new tickets through the portal
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Setup Instructions -->
                                    <hr class="my-4">
                                    <div class="alert alert-light border">
                                        <h6 class="alert-heading">
                                            <i class="bi bi-info-circle me-2"></i>Azure AD Setup Instructions
                                        </h6>
                                        <ol class="mb-0 small">
                                            <li>Go to <a href="https://portal.azure.com" target="_blank">Azure Portal</a></li>
                                            <li>Navigate to Azure Active Directory → App registrations</li>
                                            <li>Create a new registration with name "ProjectTracker Customer Portal"</li>
                                            <li>Set redirect URI to: <code><?= URLROOT ?>/customer/auth/callback</code></li>
                                            <li>Add API permissions: Microsoft Graph → Delegated → User.Read, offline_access</li>
                                            <li>Create a client secret and copy the values above</li>
                                            <li>Grant admin consent for the permissions</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Backup Tab -->
                <div class="tab-pane fade" id="backup">
                    <div class="row">
                        <div class="col-lg-10 mx-auto">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0">
                                        <i class="bi bi-database-down text-primary me-2"></i>Database Backup
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-light border">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Set the directory on the SQL Server where .bak files can be written. Then generate and download a full backup.
                                        <div class="small text-muted mt-2">
                                            Requires SQL permissions for BACKUP and Ad Hoc Distributed Queries for streaming the file.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="mssql_backup_path" class="form-label fw-medium">Backup directory on SQL Server</label>
                                        <input type="text" class="form-control" id="mssql_backup_path" name="mssql_backup_path" 
                                               placeholder="/var/opt/mssql/backups or C:\MSSQL\\Backup"
                                               value="<?= htmlspecialchars($data['systemSettings']['mssql_backup_path'] ?? '/var/opt/mssql/backups') ?>">
                                        <div class="form-text">
                                            Ensure the SQL Server service account has write access to this directory.
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary"
                                                formaction="<?= URLROOT ?>/admin/backupDatabase" formmethod="post">
                                            <i class="bi bi-download me-2"></i>Create & Download Backup (.bak)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Save Button -->
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-save me-2"></i>Save All Settings
                    </button>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// Test SMTP
async function testSmtp() {
    let resultDiv = document.getElementById('smtpTestResult');
    if (!resultDiv) {
        resultDiv = document.createElement('div');
        resultDiv.id = 'smtpTestResult';
        resultDiv.className = 'mt-4';
        document.querySelector('#email .card-body').appendChild(resultDiv);
    }
    
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<div class="alert alert-info"><span class="spinner-border spinner-border-sm me-2"></span>Testing SMTP connection...</div>';
    
    try {
        const response = await fetch('/admin/testSmtp', { method: 'POST' });
        const result = await response.json();
        
        if (result.success) {
            resultDiv.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>' + result.message + '</div>';
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>' + result.message + '</div>';
        }
    } catch (error) {
        resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Test failed: ' + error.message + '</div>';
    }
}

// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Date format preview
document.addEventListener('DOMContentLoaded', function() {
    const dateFormatSelect = document.getElementById('default_date_format');
    const preview = document.getElementById('date_format_preview');
    
    if (dateFormatSelect && preview) {
        dateFormatSelect.addEventListener('change', function() {
            const formats = {
                'Y-m-d': '2024-01-01',
                'm/d/Y': '01/01/2024',
                'd/m/Y': '01/01/2024',
                'M j, Y': 'January 1, 2024'
            };
            preview.textContent = formats[this.value] || 'January 1, 2024';
        });
        // Trigger change on load to set initial preview
        dateFormatSelect.dispatchEvent(new Event('change'));
    }
    
    // Currency preview updates
    const currencyInputs = ['currency_code', 'currency_symbol', 'currency_position', 'decimals', 'thousands_separator', 'decimal_separator'];
    const currencyPreview = document.getElementById('currency_preview');
    
    if (currencyPreview) {
        currencyInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('change', updateCurrencyPreview);
            }
        });
        
        document.querySelectorAll('input[name="currency_position"]').forEach(radio => {
            radio.addEventListener('change', updateCurrencyPreview);
        });
        // Trigger change on load
        updateCurrencyPreview();
    }
    
    function updateCurrencyPreview() {
        const symbol = document.getElementById('currency_symbol')?.value || '$';
        const position = document.querySelector('input[name="currency_position"]:checked')?.value || 'before';
        const decimals = parseInt(document.getElementById('decimals')?.value || 2);
        const thousandsSep = document.getElementById('thousands_separator')?.value || ',';
        const decimalSep = document.getElementById('decimal_separator')?.value || '.';
        
        let amount = '1234';
        if (thousandsSep) {
            amount = '1' + thousandsSep + '234';
        }
        
        if (decimals > 0) {
            amount += decimalSep + '56'.substring(0, decimals);
        }
        
        if (position === 'before') {
            currencyPreview.textContent = symbol + amount;
        } else {
            currencyPreview.textContent = amount + symbol;
        }
    }
});

// Authentication Tab Functions
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    const button = element.nextElementSibling;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check"></i> Copied!';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}

function testAzureConnection() {
    const button = event.target;
    const originalText = button.innerHTML;
    const statusDiv = document.getElementById('azure_connection_status');
    
    const tenantId = document.getElementById('azure_tenant_id').value;
    const clientId = document.getElementById('azure_client_id').value;
    const clientSecret = document.getElementById('azure_client_secret').value;
    
    if (!tenantId || !clientId || !clientSecret) {
        alert('Please fill in all Azure AD configuration fields before testing.');
        return;
    }
    
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing...';
    
    const formData = new FormData();
    formData.append('action', 'test_azure_connection');
    formData.append('azure_tenant_id', tenantId);
    formData.append('azure_client_id', clientId);
    formData.append('azure_client_secret', clientSecret);
    
    fetch('<?= URLROOT ?>/admin/testAzureConnection', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusDiv.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Connection Successful</span>';
        } else {
            statusDiv.innerHTML = '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Connection Failed</span>';
        }
    })
    .catch(error => {
        statusDiv.innerHTML = '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Test Failed</span>';
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Tab handling
document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('[data-bs-toggle="list"]');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs and content
            tabLinks.forEach(l => l.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Show corresponding content
            const targetId = this.getAttribute('href').substring(1);
            const targetPane = document.getElementById(targetId);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
            }
        });
    });

    document.querySelectorAll('.toggle-api-visibility').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const targetSelector = this.getAttribute('data-target');
            const targetInput = targetSelector ? document.querySelector(targetSelector) : null;
            if (!targetInput) return;
            const icon = this.querySelector('i');
            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                if (icon) {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
            } else {
                targetInput.type = 'password';
                if (icon) {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            }
        });
    });

    const levelToggle = document.getElementById('level_io_enabled');
    const levelInput = document.getElementById('level_io_api_key');
    if (levelToggle && levelInput) {
        const updateLevelState = () => {
            levelInput.disabled = !levelToggle.checked;
        };
        levelToggle.addEventListener('change', updateLevelState);
        updateLevelState();
    }
});
</script>