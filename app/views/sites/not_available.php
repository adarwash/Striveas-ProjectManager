<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sites</li>
        </ol>
    </nav>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Sites Management</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h4 class="alert-heading">Coming Soon!</h4>
                        <p><?= $message ?></p>
                        <hr>
                        <p class="mb-0">The Sites Management module will allow you to:</p>
                        <ul>
                            <li>Create and manage organization locations and sites</li>
                            <li>Assign employees to specific work locations</li>
                            <li>Track employee site assignments over time</li>
                            <li>Generate reports based on site distribution</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <a href="/dashboard" class="btn btn-primary">
                            <i class="bi bi-arrow-left"></i> Return to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Preview Section -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Site Management Preview</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center">
                        <img src="/img/sites_preview.jpg" class="img-fluid rounded" alt="Site Management Preview" 
                             style="max-height: 300px; opacity: 0.7;">
                    </div>
                    <p class="text-center text-muted mt-3">Preview of the upcoming Sites Management interface</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Site Assignment Options</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <div class="list-group-item disabled">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Main Office</h6>
                                    <small>New York, NY</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">HQ</span>
                            </div>
                        </div>
                        <div class="list-group-item disabled">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">West Coast Branch</h6>
                                    <small>San Francisco, CA</small>
                                </div>
                                <span class="badge bg-secondary rounded-pill">Branch</span>
                            </div>
                        </div>
                        <div class="list-group-item disabled">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Remote Work Hub</h6>
                                    <small>Austin, TX</small>
                                </div>
                                <span class="badge bg-info rounded-pill">Remote</span>
                            </div>
                        </div>
                        <div class="list-group-item disabled">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Client Site - Acme Corp</h6>
                                    <small>Boston, MA</small>
                                </div>
                                <span class="badge bg-warning rounded-pill">Client</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 