<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">
                    <i class="fas fa-clock text-warning"></i>
                    Time Tracking Setup Required
                </h4>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle"></i> Database Setup Required</h5>
                    <p>The time tracking system requires additional database tables to function properly.</p>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger mt-3">
                            <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card bg-light">
                    <div class="card-body">
                        <h6><i class="fas fa-cog"></i> Setup Instructions:</h6>
                        <ol>
                            <li>Run the SQL script: <code>sql/create_time_tracking_tables.sql</code></li>
                            <li>This will create the following tables:
                                <ul>
                                    <li><code>TimeEntries</code> - Main time tracking records</li>
                                    <li><code>TimeBreaks</code> - Break records</li>
                                    <li><code>BreakTypes</code> - Available break types</li>
                                </ul>
                            </li>
                            <li>Refresh this page after running the SQL script</li>
                        </ol>
                        
                        <div class="mt-3">
                            <a href="/dashboard" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                            <a href="/time" class="btn btn-primary ml-2">
                                <i class="fas fa-refresh"></i> Try Again
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h6><i class="fas fa-info-circle text-info"></i> About Time Tracking</h6>
                    <p class="text-muted">
                        Once set up, this system will allow users to:
                    </p>
                    <ul class="text-muted">
                        <li>Clock in and out of work</li>
                        <li>Track different types of breaks</li>
                        <li>View personal time history</li>
                        <li>Generate time reports (managers only)</li>
                        <li>Export time data to CSV</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div> 