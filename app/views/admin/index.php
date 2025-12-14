<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Admin Dashboard</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-gear me-2"></i>System Administration
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-clock text-primary" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-3">SLA Settings</h5>
                                    <p class="card-text text-muted">Configure Service Level Agreement times for different ticket priorities.</p>
                                    <a href="<?= URLROOT ?>/admin/slaSettings" class="btn btn-primary">
                                        <i class="bi bi-gear me-2"></i>Manage SLA
                                    </a>
                                </div>
                            </div>
                        </div>
                        
						<div class="col-md-6 col-lg-4 mb-4">
							<div class="card border-0 shadow-sm h-100">
								<div class="card-body text-center">
									<i class="bi bi-shield-lock text-dark" style="font-size: 2rem;"></i>
									<h5 class="card-title mt-3">Login Audit</h5>
									<p class="card-text text-muted">View authentication history with timestamps, IP addresses, and status.</p>
									<a href="<?= URLROOT ?>/admin/logins" class="btn btn-dark">
										<i class="bi bi-clock-history me-2"></i>View Logins
									</a>
								</div>
							</div>
						</div>
						
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-users text-success" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-3">User Management</h5>
                                    <p class="card-text text-muted">Manage system users, roles, and permissions.</p>
                                    <a href="<?= URLROOT ?>/admin/users" class="btn btn-success">
                                        <i class="bi bi-people me-2"></i>Manage Users
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-ticket text-warning" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-3">Ticket Management</h5>
                                    <p class="card-text text-muted">View and manage all support tickets in the system.</p>
                                    <a href="<?= URLROOT ?>/tickets" class="btn btn-warning">
                                        <i class="bi bi-ticket-detailed me-2"></i>Manage Tickets
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-envelope text-info" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-3">Ticket Email Settings</h5>
                                    <p class="card-text text-muted">Configure the Microsoft 365 support mailbox and ticket email notifications.</p>
                                    <a href="<?= URLROOT ?>/admin/emailSettings" class="btn btn-info">
                                        <i class="bi bi-gear me-2"></i>Ticket Email Settings
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-graph-up text-danger" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-3">Reports</h5>
                                    <p class="card-text text-muted">View system reports and analytics.</p>
                                    <a href="<?= URLROOT ?>/reports" class="btn btn-danger">
                                        <i class="bi bi-bar-chart me-2"></i>View Reports
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-gear-wide-connected text-secondary" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-3">System Settings</h5>
                                    <p class="card-text text-muted">Configure application settings, defaults, and system behavior.</p>
                                    <a href="<?= URLROOT ?>/admin/settings" class="btn btn-secondary">
                                        <i class="bi bi-gear me-2"></i>System Settings
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center">
                                    <i class="bi bi-diagram-3 text-primary" style="font-size: 2rem;"></i>
                                    <h5 class="card-title mt-3">Level.io Integration</h5>
                                    <p class="card-text text-muted">Sync Level.io groups with CRM clients for automation.</p>
                                    <a href="<?= URLROOT ?>/admin/levelIntegration" class="btn btn-primary">
                                        <i class="bi bi-link-45deg me-2"></i>Manage Level.io
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 