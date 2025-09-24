<?php require VIEWSPATH . '/customer/inc/header.php'; ?>

<style>
.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.profile-header::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 1rem;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.info-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    margin-bottom: 1.5rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #f1f3f4;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 500;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-value {
    font-weight: 600;
    color: #495057;
}

.security-badge {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.help-section {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 1.5rem;
    border-left: 4px solid #667eea;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}
</style>

<div class="container-fluid px-4">
    <!-- Profile Header -->
    <div class="profile-header fade-in-up">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="profile-avatar">
                    <?= strtoupper(substr($data['customer_name'], 0, 1)) ?>
                </div>
                <h2 class="mb-2"><?= htmlspecialchars($data['customer_name']) ?></h2>
                <p class="mb-0 opacity-75">
                    <i class="bi bi-envelope me-2"></i><?= htmlspecialchars($data['customer_email']) ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="security-badge">
                    <i class="bi bi-shield-check"></i>
                    <span>Microsoft 365 Verified</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Account Information -->
        <div class="col-lg-8">
            <div class="info-card fade-in-up" style="animation-delay: 0.1s;">
                <h5 class="mb-4">
                    <i class="bi bi-person-circle me-2 text-primary"></i>Account Information
                </h5>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="bi bi-person"></i>
                        Full Name
                    </div>
                    <div class="info-value"><?= htmlspecialchars($data['customer_name']) ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="bi bi-envelope"></i>
                        Email Address
                    </div>
                    <div class="info-value"><?= htmlspecialchars($data['customer_email']) ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="bi bi-building"></i>
                        Domain
                    </div>
                    <div class="info-value">
                        <span class="badge bg-light text-dark">
                            <?= htmlspecialchars($data['customer_domain']) ?>
                        </span>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="bi bi-clock"></i>
                        Signed In
                    </div>
                    <div class="info-value"><?= date('M j, Y g:i A', $data['login_time']) ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <i class="bi bi-shield-lock"></i>
                        Authentication Method
                    </div>
                    <div class="info-value">
                        <span class="badge bg-primary">
                            <i class="bi bi-microsoft me-1"></i>Microsoft 365
                        </span>
                    </div>
                </div>
            </div>

            <!-- Session Information -->
            <div class="info-card fade-in-up" style="animation-delay: 0.2s;">
                <h5 class="mb-4">
                    <i class="bi bi-shield-check me-2 text-success"></i>Security & Privacy
                </h5>
                
                <div class="alert alert-success border-0">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-check-circle-fill me-3 mt-1"></i>
                        <div>
                            <h6 class="mb-1">Secure Authentication</h6>
                            <p class="mb-0 small">Your account is protected with Microsoft 365 authentication. We don't store your password - authentication is handled securely by Microsoft.</p>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info border-0">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle-fill me-3 mt-1"></i>
                        <div>
                            <h6 class="mb-1">Data Privacy</h6>
                            <p class="mb-0 small">We only access your basic profile information (name and email) to provide you with support ticket access. No other data is collected or stored.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help & Support -->
        <div class="col-lg-4">
            <div class="help-section fade-in-up" style="animation-delay: 0.3s;">
                <h6 class="mb-3">
                    <i class="bi bi-question-circle me-2"></i>Need Help?
                </h6>
                
                <div class="d-grid gap-2 mb-3">
                    <a href="<?= URLROOT ?>/customer/tickets" class="btn btn-outline-primary">
                        <i class="bi bi-ticket-perforated me-2"></i>View My Tickets
                    </a>
                    <a href="<?= URLROOT ?>/customer/dashboard" class="btn btn-outline-secondary">
                        <i class="bi bi-house me-2"></i>Dashboard
                    </a>
                </div>
                
                <div class="small text-muted">
                    <p class="mb-2">
                        <strong>Having trouble?</strong><br>
                        Contact your IT support team for assistance with account access or technical issues.
                    </p>
                    
                    <p class="mb-0">
                        <strong>Sign Out:</strong><br>
                        Always sign out when using a shared computer for security.
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="info-card fade-in-up" style="animation-delay: 0.4s;">
                <h6 class="mb-3">
                    <i class="bi bi-gear me-2"></i>Account Actions
                </h6>
                
                <div class="d-grid gap-2">
                    <a href="<?= URLROOT ?>/customer/auth/logout" class="btn btn-outline-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                    </a>
                </div>
                
                <div class="mt-3 pt-3 border-top">
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i>
                        Session expires after 8 hours of inactivity
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require VIEWSPATH . '/customer/inc/footer.php'; ?>


