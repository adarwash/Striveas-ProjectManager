<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Email Settings</h1>
            <p class="text-muted">Configure email integration for ticket system</p>
        </div>
        <div>
            <a href="<?= URLROOT ?>/admin" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Admin
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <!-- Email Configuration Tabs -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#graph-api">
                        <i class="bi bi-microsoft me-2"></i>Microsoft Graph API
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#smtp-settings">
                        <i class="bi bi-envelope-arrow-up me-2"></i>SMTP (Legacy)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#test-connection">
                        <i class="bi bi-check2-circle me-2"></i>Test Connection
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="card-body">
            <div class="tab-content">
                <!-- Microsoft Graph API Tab -->
                <div class="tab-pane fade show active" id="graph-api">
                    <form method="POST" action="<?= URLROOT ?>/admin/saveEmailSettings" id="graphSettingsForm">
                        <input type="hidden" name="settings_type" value="graph_api">
                        
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Recommended:</strong> Microsoft Graph API is the modern, secure way to integrate with Microsoft 365 email.
                            No IMAP configuration needed, works with Security Defaults enabled.
                        </div>

                        <h5 class="mb-3">Azure AD App Configuration</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="graph_tenant_id" class="form-label">
                                    <i class="bi bi-building me-1"></i>Tenant ID
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="graph_tenant_id" name="graph_tenant_id" 
                                       value="<?= htmlspecialchars($data['settings']['graph_tenant_id'] ?? '') ?>"
                                       placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" required>
                                <div class="form-text">Found in Azure AD → Overview → Tenant ID</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="graph_client_id" class="form-label">
                                    <i class="bi bi-app me-1"></i>Application (Client) ID
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="graph_client_id" name="graph_client_id"
                                       value="<?= htmlspecialchars($data['settings']['graph_client_id'] ?? '') ?>"
                                       placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" required>
                                <div class="form-text">Found in App Registration → Overview</div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="graph_client_secret" class="form-label">
                                    <i class="bi bi-key me-1"></i>Client Secret
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="graph_client_secret" name="graph_client_secret"
                                           value="<?= htmlspecialchars($data['settings']['graph_client_secret'] ?? '') ?>"
                                           placeholder="Enter client secret value" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('graph_client_secret')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">From Certificates & secrets (copy immediately after creation)</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="graph_support_email" class="form-label">
                                    <i class="bi bi-envelope me-1"></i>Support Email Address
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control" id="graph_support_email" name="graph_support_email"
                                       value="<?= htmlspecialchars($data['settings']['graph_support_email'] ?? '') ?>"
                                       placeholder="support@yourdomain.com" required>
                                <div class="form-text">The mailbox to monitor for incoming tickets</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="graph_enabled" name="graph_enabled"
                                           <?= ($data['settings']['graph_enabled'] ?? false) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="graph_enabled">
                                        Enable Graph API Integration
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="graph_auto_process" name="graph_auto_process"
                                           <?= ($data['settings']['graph_auto_process'] ?? true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="graph_auto_process">
                                        Automatically process emails to tickets
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Setup Instructions -->
                        <div class="accordion mb-4" id="setupAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#setupInstructions">
                                        <i class="bi bi-question-circle me-2"></i>How to get these values?
                                    </button>
                                </h2>
                                <div id="setupInstructions" class="accordion-collapse collapse" data-bs-parent="#setupAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li class="mb-2">
                                                <strong>Register App in Azure:</strong>
                                                <a href="https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps/ApplicationsListBlade" target="_blank" class="text-decoration-none">
                                                    Go to Azure Portal <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                                → App registrations → New registration
                                            </li>
                                            <li class="mb-2">
                                                <strong>Create Client Secret:</strong>
                                                In your app → Certificates & secrets → New client secret
                                            </li>
                                            <li class="mb-2">
                                                <strong>Grant Permissions:</strong>
                                                API permissions → Add permission → Microsoft Graph → Application permissions:
                                                <ul class="mt-1">
                                                    <li><code>Mail.Read</code></li>
                                                    <li><code>Mail.Send</code></li>
                                                    <li><code>Mail.ReadWrite</code></li>
                                                </ul>
                                            </li>
                                            <li>
                                                <strong>Grant Admin Consent:</strong>
                                                Click "Grant admin consent" button after adding permissions
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Graph API Settings
                            </button>
                            <button type="button" class="btn btn-outline-success" onclick="testGraphConnection()">
                                <i class="bi bi-check2-circle me-2"></i>Test Connection
                            </button>
                        </div>
                    </form>
                </div>

                <!-- SMTP Settings Tab (Legacy) -->
                <div class="tab-pane fade" id="smtp-settings">
                    <form method="POST" action="<?= URLROOT ?>/admin/saveEmailSettings">
                        <input type="hidden" name="settings_type" value="smtp">
                        
                        <div class="alert alert-warning mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> SMTP/IMAP requires app passwords and may not work with Security Defaults enabled.
                            Consider using Graph API instead.
                        </div>

                        <h5 class="mb-3">SMTP Configuration (Outgoing Mail)</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="smtp_host" class="form-label">SMTP Host</label>
                                <input type="text" class="form-control" id="smtp_host" name="smtp_host"
                                       value="<?= htmlspecialchars($data['settings']['smtp_host'] ?? 'smtp.office365.com') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="smtp_port" class="form-label">SMTP Port</label>
                                <input type="number" class="form-control" id="smtp_port" name="smtp_port"
                                       value="<?= htmlspecialchars($data['settings']['smtp_port'] ?? '587') ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="smtp_username" class="form-label">SMTP Username</label>
                                <input type="email" class="form-control" id="smtp_username" name="smtp_username"
                                       value="<?= htmlspecialchars($data['settings']['smtp_username'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="smtp_password" class="form-label">SMTP Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="smtp_password" name="smtp_password"
                                           value="<?= htmlspecialchars($data['settings']['smtp_password'] ?? '') ?>">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('smtp_password')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save SMTP Settings
                        </button>
                    </form>
                </div>

                <!-- Test Connection Tab -->
                <div class="tab-pane fade" id="test-connection">
                    <h5 class="mb-3">Test Email Connection</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-microsoft me-2"></i>Test Graph API
                                    </h6>
                                    <p class="card-text">Test Microsoft Graph API connection and permissions</p>
                                    <button class="btn btn-primary" onclick="testGraphConnection()">
                                        <i class="bi bi-play-circle me-2"></i>Run Test
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-envelope me-2"></i>Send Test Email
                                    </h6>
                                    <p class="card-text">Send a test email to verify configuration</p>
                                    <button class="btn btn-primary" onclick="sendTestEmail()">
                                        <i class="bi bi-send me-2"></i>Send Test
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="testResults" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        button.classList.remove('bi-eye');
        button.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        button.classList.remove('bi-eye-slash');
        button.classList.add('bi-eye');
    }
}

function testGraphConnection() {
    const resultsDiv = document.getElementById('testResults') || document.querySelector('#test-connection #testResults');
    
    // Get form values
    const tenantId = document.getElementById('graph_tenant_id').value;
    const clientId = document.getElementById('graph_client_id').value;
    const clientSecret = document.getElementById('graph_client_secret').value;
    const supportEmail = document.getElementById('graph_support_email').value;
    
    if (!tenantId || !clientId || !clientSecret || !supportEmail) {
        alert('Please fill in all Graph API settings first');
        return;
    }
    
    resultsDiv.innerHTML = '<div class="alert alert-info"><i class="spinner-border spinner-border-sm me-2"></i>Testing connection...</div>';
    
    fetch('<?= URLROOT ?>/admin/testGraphConnection', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            tenant_id: tenantId,
            client_id: clientId,
            client_secret: clientSecret,
            support_email: supportEmail
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultsDiv.innerHTML = `
                <div class="alert alert-success">
                    <h6><i class="bi bi-check-circle me-2"></i>Connection Successful!</h6>
                    <ul class="mb-0">
                        <li>✅ Authentication successful</li>
                        <li>✅ Can read emails (${data.email_count} emails found)</li>
                        <li>✅ Can send emails</li>
                    </ul>
                </div>
            `;
        } else {
            resultsDiv.innerHTML = `
                <div class="alert alert-danger">
                    <h6><i class="bi bi-x-circle me-2"></i>Connection Failed</h6>
                    <p>${data.error}</p>
                    ${data.details ? '<pre class="mb-0">' + data.details + '</pre>' : ''}
                </div>
            `;
        }
    })
    .catch(error => {
        resultsDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-x-circle me-2"></i>Error: ${error.message}
            </div>
        `;
    });
}

function sendTestEmail() {
    const email = prompt('Enter email address to send test to:');
    if (!email) return;
    
    const resultsDiv = document.getElementById('testResults');
    resultsDiv.innerHTML = '<div class="alert alert-info"><i class="spinner-border spinner-border-sm me-2"></i>Sending test email...</div>';
    
    fetch('<?= URLROOT ?>/admin/sendTestEmail', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ to: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultsDiv.innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>Test email sent successfully to ${email}
                </div>
            `;
        } else {
            resultsDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>Failed to send: ${data.error}
                </div>
            `;
        }
    });
}
</script>
