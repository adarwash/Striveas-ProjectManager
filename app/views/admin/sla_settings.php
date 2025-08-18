<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= URLROOT ?>/admin">Admin</a></li>
            <li class="breadcrumb-item active">SLA Settings</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-clock me-2"></i>SLA Settings
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Priority</th>
                                        <th>Response Time (Hours)</th>
                                        <th>Resolution Time (Hours)</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['priorities'] as $priority): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($priority['display_name']) ?></strong>
                                            <input type="hidden" name="priorities[<?= $priority['id'] ?>][id]" value="<?= $priority['id'] ?>">
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="priorities[<?= $priority['id'] ?>][response_hours]" 
                                                   value="<?= $priority['response_time_hours'] ?>" 
                                                   min="1" 
                                                   max="168"
                                                   required>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="priorities[<?= $priority['id'] ?>][resolution_hours]" 
                                                   value="<?= $priority['resolution_time_hours'] ?>" 
                                                   min="1" 
                                                   max="720"
                                                   required>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php
                                                $responseTime = $priority['response_time_hours'];
                                                $resolutionTime = $priority['resolution_time_hours'];
                                                
                                                if ($responseTime < 24) {
                                                    echo $responseTime . 'h response, ' . $resolutionTime . 'h resolution';
                                                } else {
                                                    $responseDays = floor($responseTime / 24);
                                                    $resolutionDays = floor($resolutionTime / 24);
                                                    echo $responseDays . 'd response, ' . $resolutionDays . 'd resolution';
                                                }
                                                ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save SLA Settings
                            </button>
                            <a href="<?= URLROOT ?>/admin" class="btn btn-outline-secondary ms-2">
                                <i class="bi bi-arrow-left me-2"></i>Back to Admin
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- SLA Information -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>SLA Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Response Time</h6>
                            <p class="text-muted">The time within which the first response must be provided to the customer.</p>
                            <ul class="small text-muted">
                                <li>Critical: Immediate response (1-2 hours)</li>
                                <li>High: Quick response (2-4 hours)</li>
                                <li>Normal: Standard response (4-8 hours)</li>
                                <li>Low: Extended response (8-24 hours)</li>
                                <li>Lowest: Maximum response time (24+ hours)</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Resolution Time</h6>
                            <p class="text-muted">The time within which the ticket must be fully resolved.</p>
                            <ul class="small text-muted">
                                <li>Critical: Same day resolution (4-6 hours)</li>
                                <li>High: Quick resolution (8-12 hours)</li>
                                <li>Normal: Standard resolution (16-24 hours)</li>
                                <li>Low: Extended resolution (24-48 hours)</li>
                                <li>Lowest: Maximum resolution time (72+ hours)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
