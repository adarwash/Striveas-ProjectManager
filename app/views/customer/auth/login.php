<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['title'] ?? 'Customer Portal Login' ?> - <?= SITENAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            flex-direction: column;
            gap: 24px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin: 8px 0;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 70px rgba(0, 0, 0, 0.2);
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }
        
        .login-body {
            padding: 2.5rem;
        }
        
        .microsoft-btn {
            background: linear-gradient(135deg, #0078d4, #106ebe);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .microsoft-btn:hover {
            background: linear-gradient(135deg, #106ebe, #005a9e);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 120, 212, 0.3);
        }
        
        .microsoft-btn:active {
            transform: translateY(0);
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            padding: 8px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .feature-list i {
            color: #28a745;
            font-size: 1.1rem;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 1.5rem;
        }
        
        .login-footer {
            text-align: center;
            color: #6c757d;
            margin-top: 0;
            font-size: 0.9rem;
            width: 100%;
            max-width: 500px;
        }
        
        .login-footer a {
            color: #0d6efd;
            text-decoration: none;
        }
        
        .login-footer a:hover {
            color: #0a58ca;
            text-decoration: underline;
        }
        
        .not-configured {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px dashed #adb5bd;
            border-radius: 15px;
            padding: 3rem 2rem;
            text-align: center;
            color: #495057;
            position: relative;
            overflow: hidden;
        }
        
        .not-configured::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(108, 117, 125, 0.05) 10px,
                rgba(108, 117, 125, 0.05) 20px
            );
            animation: slide 20s linear infinite;
        }
        
        @keyframes slide {
            0% {
                transform: translateX(-50%) translateY(-50%) rotate(0deg);
            }
            100% {
                transform: translateX(-50%) translateY(-50%) rotate(360deg);
            }
        }
        
        .not-configured-content {
            position: relative;
            z-index: 2;
        }
        
        .not-configured .gear-icon {
            background: linear-gradient(135deg, #6c757d, #495057);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: rotate 3s linear infinite;
        }
        
        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        .not-configured h5 {
            color: #343a40;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .not-configured p {
            color: #6c757d;
            line-height: 1.6;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-card {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body>
    <div class="d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 20px;">
        <div class="login-card">
            <div class="login-header">
                <i class="bi bi-shield-lock"></i>
                <h3 class="mb-0">Customer Portal</h3>
                <p class="mb-0 opacity-75">Access Your Support Tickets</p>
            </div>
            
            <div class="login-body">
                <!-- Flash Messages -->
                <?php if (isset($_SESSION['flash_auth_error'])): ?>
                    <div class="alert alert-danger d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?= $_SESSION['flash_auth_error'] ?></div>
                    </div>
                    <?php unset($_SESSION['flash_auth_error']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['flash_auth_success'])): ?>
                    <div class="alert alert-success d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <div><?= $_SESSION['flash_auth_success'] ?></div>
                    </div>
                    <?php unset($_SESSION['flash_auth_success']); ?>
                <?php endif; ?>
                
                <?php if ($data['azure_configured']): ?>
                    <!-- Login Options -->
                    <div class="text-center mb-4">
                        <h5 class="mb-3">Sign in with your Microsoft account</h5>
                        <p class="text-muted small">Use your work or school account to access your support tickets</p>
                    </div>
                    
                    <a href="<?= URLROOT ?>/customer/auth/login" class="microsoft-btn">
                        <i class="bi bi-microsoft" style="font-size: 1.2rem;"></i>
                        <span>Sign in with Microsoft 365</span>
                    </a>
                    
                    <hr class="my-4">
                    
                    <!-- Features -->
                    <div class="mb-4">
                        <h6 class="mb-3">What you can do:</h6>
                        <ul class="feature-list">
                            <li>
                                <i class="bi bi-ticket-perforated"></i>
                                <span>View your open and closed support tickets</span>
                            </li>
                            <li>
                                <i class="bi bi-chat-dots"></i>
                                <span>See all conversation history and updates</span>
                            </li>
                            <li>
                                <i class="bi bi-bell"></i>
                                <span>Get notified about ticket status changes</span>
                            </li>
                            <li>
                                <i class="bi bi-shield-check"></i>
                                <span>Secure access with Microsoft 365 authentication</span>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Help Text -->
                    <div class="alert alert-light">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Need help?</strong> Contact your IT support team if you're having trouble accessing your account.
                        </small>
                    </div>
                    
                <?php else: ?>
                    <!-- Not Configured -->
                    <div class="not-configured">
                        <div class="not-configured-content">
                            <i class="bi bi-gear gear-icon" style="font-size: 4rem; margin-bottom: 1.5rem; display: block;"></i>
                            <h5>Authentication Not Configured</h5>
                            <p class="mb-3">The customer portal authentication is not yet configured. Please contact your system administrator to set up Microsoft 365 integration.</p>
                            <div class="mt-4">
                                <small class="text-muted d-block mb-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Administrators can configure this in Admin → Settings → Authentication
                                </small>
                                <?php if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] && isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <a href="<?= URLROOT ?>/admin/settings" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-gear me-1"></i>Configure Authentication
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="border-top px-4 py-3" style="background:#fff;">
                <div class="login-footer mx-auto">
                    <p class="mb-1">&copy; <?= date('Y') ?> <?= SITENAME ?>. All rights reserved.</p>
                    <p class="mb-0">
                        <a href="<?= URLROOT ?>">Back to Main Site</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
