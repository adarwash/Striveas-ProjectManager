<?php require VIEWSPATH . '/inc/header.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Email Integration Settings</h1>
            <p class="text-muted">Configure Microsoft 365 email integration for automatic ticket creation</p>
        </div>
        <div>
            <a href="<?= URLROOT ?>/admin/settings" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Settings
            </a>
        </div>
    </div>

    <?php flash('settings_success'); ?>
    <?php flash('settings_error'); ?>

    <!-- Microsoft 365 Connection Status -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center">
                <span class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                    <i class="bi bi-microsoft fs-4 text-primary"></i>
                </span>
                <div>
                    <h5 class="mb-0">Microsoft 365 Connection</h5>
                    <small class="text-muted">Connect your Microsoft 365 account for email integration</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php 
            $isConnected = isset($data['settings']['graph_connection_status']) && 
                          $data['settings']['graph_connection_status'] === 'connected';
            $connectedEmail = isset($data['settings']['graph_connected_email']) ? $data['settings']['graph_connected_email'] : '';
            $connectedName = isset($data['settings']['graph_connected_name']) ? $data['settings']['graph_connected_name'] : '';
            $connectedAt = isset($data['settings']['graph_connected_at']) ? $data['settings']['graph_connected_at'] : '';
            ?>
            
            <?php if ($isConnected): ?>
                <!-- Connected State -->
                <div class="alert alert-success d-flex align-items-center mb-4">
                    <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Connected to Microsoft 365</h6>
                        <p class="mb-0">
                            <strong><?= htmlspecialchars($connectedName) ?></strong><br>
                            <?= htmlspecialchars($connectedEmail) ?><br>
                            <small class="text-muted">Connected on <?= date('M d, Y \a\t g:i A', strtotime($connectedAt)) ?></small>
                        </p>
                    </div>
                    <form action="<?= URLROOT ?>/microsoftAuth/disconnect" method="POST" class="ms-3">
                        <button type="submit" class="btn btn-outline-danger" 
                                onclick="return confirm('Are you sure you want to disconnect from Microsoft 365?')">
                            <i class="bi bi-x-circle me-2"></i>Disconnect
                        </button>
                    </form>
                </div>
                
                <!-- Connection Details -->
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Permissions Granted:</h6>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check text-success me-2"></i>Read emails from inbox</li>
                            <li><i class="bi bi-check text-success me-2"></i>Send email replies</li>
                            <li><i class="bi bi-check text-success me-2"></i>Manage email folders</li>
                            <li><i class="bi bi-check text-success me-2"></i>Access user profile</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Integration Features:</h6>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check text-success me-2"></i>Automatic ticket creation from emails</li>
                            <li><i class="bi bi-check text-success me-2"></i>Email reply tracking</li>
                            <li><i class="bi bi-check text-success me-2"></i>Attachment support</li>
                            <li><i class="bi bi-check text-success me-2"></i>Secure OAuth2 authentication</li>
                        </ul>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Disconnected State -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-envelope-plus display-1 text-muted"></i>
                    </div>
                    <h5 class="mb-3">Connect Your Microsoft 365 Account</h5>
                    <p class="text-muted mb-4">
                        Click the button below to securely connect your Microsoft 365 account.<br>
                        You'll be redirected to Microsoft to grant permissions.
                    </p>
                    
                    <!-- App Registration Check -->
                    <?php if (empty($data['settings']['graph_client_id']) || empty($data['settings']['graph_client_secret'])): ?>
                        <div class="alert alert-warning mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Setup Required:</strong> Please configure your Microsoft App registration first.
                        </div>
                    <?php endif; ?>
                    
                    <a href="<?= URLROOT ?>/microsoftAuth/connect" 
                       class="btn btn-primary btn-lg <?= empty($data['settings']['graph_client_id']) || empty($data['settings']['graph_client_secret']) ? 'disabled' : '' ?>">
                        <i class="bi bi-microsoft me-2"></i>Connect with Microsoft
                    </a>
                    
                    <div class="mt-4">
                        <small class="text-muted">
                            <i class="bi bi-shield-check me-1"></i>
                            Secure OAuth2 authentication - we never store your password
                        </small>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Microsoft App Registration Settings -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <span class="bg-info bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="bi bi-gear fs-4 text-info"></i>
                    </span>
                    <div>
                        <h5 class="mb-0">Microsoft App Registration</h5>
                        <small class="text-muted">Configure your Azure AD application settings</small>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" 
                        data-bs-target="#setupInstructions" aria-expanded="false">
                    <i class="bi bi-question-circle me-1"></i>Setup Guide
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Setup Instructions (Collapsible) -->
            <div class="collapse mb-4" id="setupInstructions">
                <div class="alert alert-info">
                    <h6 class="alert-heading">Quick Setup Guide:</h6>
                    <ol class="mb-0">
                        <li>Go to <a href="https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade" target="_blank">Azure AD App Registrations</a></li>
                        <li>Click "New registration"</li>
                        <li>Name: "ProjectTracker Email Integration"</li>
                        <li>Supported account types: "Single tenant" or "Multitenant"</li>
                        <li>Redirect URI: <code><?= URLROOT ?>/microsoftAuth/callback</code></li>
                        <li>After creation, copy the Application (client) ID</li>
                        <li>Go to "Certificates & secrets" → "New client secret"</li>
                        <li>Copy the secret value immediately (it won't be shown again)</li>
                        <li>Go to "API permissions" → Add permissions for:
                            <ul>
                                <li>Mail.Read, Mail.Send, Mail.ReadWrite</li>
                                <li>User.Read</li>
                                <li>offline_access</li>
                            </ul>
                        </li>
                        <li>Click "Grant admin consent" if you're an admin</li>
                    </ol>
                </div>
            </div>

            <form action="<?= URLROOT ?>/admin/saveEmailSettings" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="graph_client_id" class="form-label">
                                Application (Client) ID <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="graph_client_id" name="graph_client_id" 
                                   value="<?= htmlspecialchars(isset($data['settings']['graph_client_id']) ? $data['settings']['graph_client_id'] : '') ?>"
                                   placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" required>
                            <div class="form-text">Found in Azure AD → App registrations → Your app</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="graph_tenant_id" class="form-label">
                                Directory (Tenant) ID
                            </label>
                            <input type="text" class="form-control" id="graph_tenant_id" name="graph_tenant_id" 
                                   value="<?= htmlspecialchars(isset($data['settings']['graph_tenant_id']) ? $data['settings']['graph_tenant_id'] : '') ?>"
                                   placeholder="common (for multi-tenant) or your tenant ID">
                            <div class="form-text">Leave as "common" for multi-tenant, or enter your tenant ID</div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="graph_client_secret" class="form-label">
                        Client Secret <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="graph_client_secret" name="graph_client_secret" 
                               value="<?= htmlspecialchars(isset($data['settings']['graph_client_secret']) ? $data['settings']['graph_client_secret'] : '') ?>"
                               placeholder="Enter your client secret" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('graph_client_secret')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="form-text">Found in Azure AD → Your app → Certificates & secrets</div>
                </div>

                <div class="mb-3">
                    <label for="graph_redirect_uri" class="form-label">Redirect URI</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="graph_redirect_uri" 
                               value="<?= URLROOT ?>/microsoftAuth/callback" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('graph_redirect_uri')">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                    <div class="form-text">Add this exact URI to your app's authentication settings in Azure</div>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Save Configuration
                    </button>
                    <?php if (!empty($data['settings']['graph_client_id']) && !empty($data['settings']['graph_client_secret'])): ?>
                        <button type="button" class="btn btn-outline-success" onclick="testConnection()">
                            <i class="bi bi-wifi me-2"></i>Test Connection
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Email Processing Settings -->
    <?php if ($isConnected): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center">
                <span class="bg-success bg-opacity-10 p-2 rounded-circle me-3">
                    <i class="bi bi-inbox fs-4 text-success"></i>
                </span>
                <div>
                    <h5 class="mb-0">Email Processing Settings</h5>
                    <small class="text-muted">Configure how emails are converted to tickets</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="<?= URLROOT ?>/admin/saveEmailProcessingSettings" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email_folder" class="form-label">Email Folder to Monitor</label>
                            <select class="form-select" id="email_folder" name="email_folder">
                                <option value="Inbox" <?= (isset($data['settings']['email_folder']) ? $data['settings']['email_folder'] : 'Inbox') === 'Inbox' ? 'selected' : '' ?>>Inbox</option>
                                <option value="Support" <?= (isset($data['settings']['email_folder']) ? $data['settings']['email_folder'] : '') === 'Support' ? 'selected' : '' ?>>Support Folder</option>
                                <option value="Custom" <?= (isset($data['settings']['email_folder']) ? $data['settings']['email_folder'] : '') === 'Custom' ? 'selected' : '' ?>>Custom Folder</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="check_interval" class="form-label">Check Interval (minutes)</label>
                            <input type="number" class="form-control" id="check_interval" name="check_interval" 
                                   value="<?= isset($data['settings']['check_interval']) ? $data['settings']['check_interval'] : '5' ?>" min="1" max="60">
                            <div class="form-text">How often to check for new emails</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="auto_reply" class="form-label">Auto-Reply to New Tickets</label>
                            <select class="form-select" id="auto_reply" name="auto_reply">
                                <option value="1" <?= (isset($data['settings']['auto_reply']) ? $data['settings']['auto_reply'] : '1') === '1' ? 'selected' : '' ?>>Yes - Send confirmation</option>
                                <option value="0" <?= (isset($data['settings']['auto_reply']) ? $data['settings']['auto_reply'] : '') === '0' ? 'selected' : '' ?>>No - Don't send</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="default_priority" class="form-label">Default Ticket Priority</label>
                            <select class="form-select" id="default_priority" name="default_priority">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="allowed_domains" class="form-label">Allowed Email Domains</label>
                    <textarea class="form-control" id="allowed_domains" name="allowed_domains" rows="3" 
                              placeholder="Leave empty to accept all domains, or enter one domain per line"><?= htmlspecialchars(isset($data['settings']['allowed_domains']) ? $data['settings']['allowed_domains'] : '') ?></textarea>
                    <div class="form-text">Only create tickets from these domains (one per line)</div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Save Processing Settings
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = event.currentTarget;
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

function copyToClipboard(fieldId) {
    const field = document.getElementById(fieldId);
    field.select();
    document.execCommand('copy');
    
    // Show feedback
    const button = event.currentTarget;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check"></i> Copied!';
    button.classList.remove('btn-outline-secondary');
    button.classList.add('btn-success');
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}

function testConnection() {
    const button = event.currentTarget;
    const originalHTML = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing...';
    
    fetch('<?= URLROOT ?>/admin/testGraphConnection', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        button.disabled = false;
        if (data.success) {
            button.innerHTML = '<i class="bi bi-check-circle me-2"></i>Connected!';
            button.classList.remove('btn-outline-success');
            button.classList.add('btn-success');
            
            // Show success message
            showAlert('success', 'Connection successful! Ready to connect with Microsoft.');
        } else {
            button.innerHTML = '<i class="bi bi-x-circle me-2"></i>Failed';
            button.classList.remove('btn-outline-success');
            button.classList.add('btn-danger');
            
            // Show error message
            showAlert('danger', 'Connection failed: ' + (data.message || 'Unknown error'));
        }
        
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('btn-success', 'btn-danger');
            button.classList.add('btn-outline-success');
        }, 3000);
    })
    .catch(error => {
        button.disabled = false;
        button.innerHTML = originalHTML;
        showAlert('danger', 'Test failed: ' + error.message);
    });
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.children[1]);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>

<?php require VIEWSPATH . '/inc/footer.php'; ?>
