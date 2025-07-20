<?php include_once VIEWSPATH . '/inc/header.php'; ?>

<!-- Main Content Container -->
<div class="container-fluid px-4 py-3">
    
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="header-text">
                <h1 class="page-title">
                    <i class="fas fa-sticky-note"></i>
                    <?= $data['title'] ?>
                </h1>
                <p class="mb-0">Your collection of thoughts, ideas, and important information</p>
            </div>
            <div class="header-actions">
                <a href="/notes/add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Note
                </a>
            </div>
        </div>
    </div>
    
    <!-- Flash messages -->
    <?php flash('note_success'); ?>
    <?php flash('note_error'); ?>

    <?php if (!empty($data['notes'])): ?>
        <!-- Notes Statistics Overview -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-sticky-note"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-number"><?= count($data['notes']) ?></h3>
                        <p class="stats-label">Total Notes</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-number"><?= count(array_filter($data['notes'], fn($note) => $note['type'] === 'task')) ?></h3>
                        <p class="stats-label">Task Notes</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon bg-info">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-number"><?= count(array_filter($data['notes'], fn($note) => $note['type'] === 'project')) ?></h3>
                        <p class="stats-label">Project Notes</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon bg-warning">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-number"><?= count(array_filter($data['notes'], fn($note) => $note['type'] === 'personal')) ?></h3>
                        <p class="stats-label">Personal Notes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Search and Filter Panel -->
        <div class="filter-panel-card mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="filter-header-info">
                        <h6 class="filter-title">
                            <i class="fas fa-search me-2"></i>Search & Filter Notes
                        </h6>
                        <small class="filter-subtitle">Find and organize your notes efficiently</small>
                    </div>
                    <div class="filter-summary">
                        <span class="results-badge">
                            <i class="fas fa-chart-pie me-1"></i>
                            <?= count($data['notes']) ?> Total
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body" style="padding: 20px;">
                <div class="row align-items-center g-3">
                    <div class="col-lg-4 col-md-6">
                        <div class="search-section">
                            <label class="search-label">Search Notes</label>
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" class="form-control search-input" id="noteSearch" 
                                       placeholder="Search by title or content..." aria-label="Search notes">
                                <button class="search-clear" id="clearSearch" style="display: none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="filter-section">
                            <label class="filter-label">Filter by Type</label>
                            <select id="noteTypeFilter" class="form-select modern-select">
                                <option value="all">All Types</option>
                                <option value="project">Project Notes</option>
                                <option value="task">Task Notes</option>
                                <option value="personal">Personal Notes</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="sort-section">
                            <label class="sort-label">Sort by</label>
                            <select id="noteSortOrder" class="form-select modern-select">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="title">Title (A-Z)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <div class="view-section">
                            <label class="view-label">View</label>
                            <div class="view-toggle-group">
                                <button type="button" id="viewGrid" class="view-toggle active" title="Grid View">
                                    <i class="fas fa-th-large"></i>
                                </button>
                                <button type="button" id="viewList" class="view-toggle" title="List View">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes Grid View (Default) -->
        <div id="gridView" class="row g-4">
            <?php foreach ($data['notes'] as $note): ?>
                <div class="col-xl-4 col-lg-6 col-md-6 note-item" data-type="<?= $note['type'] ?>" data-date="<?= $note['created_at'] ?>" data-title="<?= htmlspecialchars($note['title']) ?>" data-note-id="<?= $note['id'] ?>">
                    <div class="modern-note-card">
                        <div class="note-header">
                            <div class="note-type-badge note-type-<?= $note['type'] ?>">
                                <i class="fas <?= $note['type'] === 'project' ? 'fa-project-diagram' : 
                                               ($note['type'] === 'task' ? 'fa-check-circle' : 'fa-user') ?>"></i>
                                <?= ucfirst($note['type']) ?>
                            </div>
                            <div class="note-actions">
                                <div class="dropdown">
                                    <button class="note-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="/notes/show/<?= $note['id'] ?>">
                                                <i class="fas fa-eye me-2"></i>View Note
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="/notes/edit/<?= $note['id'] ?>">
                                                <i class="fas fa-edit me-2"></i>Edit Note
                                            </a>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger" 
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal<?= $note['id'] ?>" data-note-id="<?= $note['id'] ?>">
                                                <i class="fas fa-trash me-2"></i>Delete Note
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="note-content">
                            <h5 class="note-title">
                                <a href="/notes/show/<?= $note['id'] ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($note['title']) ?>
                                </a>
                            </h5>
                            <div class="note-preview">
                                <div class="note-text">
                                    <?= nl2br(htmlspecialchars(substr($note['content'], 0, 150))) ?>
                                    <?php if (strlen($note['content']) > 150): ?>
                                        <span class="text-muted">...</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="note-footer">
                            <div class="note-date">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?= date('M j, Y', strtotime($note['created_at'])) ?>
                            </div>
                            <a href="/notes/show/<?= $note['id'] ?>" class="btn-read-more">
                                <i class="fas fa-eye me-1"></i>
                                Read More
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Delete Confirmation Modal -->
                <div class="modal fade" id="deleteModal<?= $note['id'] ?>" tabindex="-1" aria-hidden="true" data-note-id="<?= $note['id'] ?>">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content modern-modal">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                    Confirm Deletion
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete the note "<strong><?= htmlspecialchars($note['title']) ?></strong>"?</p>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Warning:</strong> This action cannot be undone. The note will be permanently deleted.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </button>
                                <form action="/notes/delete/<?= $note['id'] ?>" method="POST" class="d-inline">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash me-1"></i> Delete Note
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Note Detail Modal -->
                <div class="modal fade" id="noteModal<?= $note['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content modern-modal">
                            <div class="modal-header">
                                <h5 class="modal-title"><?= htmlspecialchars($note['title']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3 note-full-content">
                                    <?= nl2br(htmlspecialchars($note['content'])) ?>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="note-type-badge note-type-<?= $note['type'] ?>">
                                            <i class="fas <?= $note['type'] === 'project' ? 'fa-project-diagram' : 
                                                           ($note['type'] === 'task' ? 'fa-check-circle' : 'fa-user') ?>"></i>
                                            <?= ucfirst($note['type']) ?>
                                        </span>
                                        <?php if (!empty($note['reference_title'])): ?>
                                        <a href="<?= $note['type'] === 'project' ? '/projects/show/' . $note['reference_id'] : '/tasks/show/' . $note['reference_id'] ?>" class="ms-2 text-decoration-none">
                                            <i class="fas fa-link me-1"></i>
                                            <?= htmlspecialchars($note['reference_title'] ?? 'Unknown') ?>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <?= date('M j, Y', strtotime($note['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <a href="/notes/edit/<?= $note['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-edit me-1"></i> Edit Note
                                </a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Notes List View (Hidden by Default) -->
        <div id="listView" class="d-none">
            <div class="modern-table-card">
                <div class="table-responsive">
                    <table class="table table-hover modern-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Preview</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['notes'] as $note): ?>
                                <tr class="note-item" data-type="<?= $note['type'] ?>" data-date="<?= $note['created_at'] ?>" data-title="<?= htmlspecialchars($note['title']) ?>" data-note-id="<?= $note['id'] ?>">
                                    <td>
                                        <a href="#" class="note-title-link" data-bs-toggle="modal" data-bs-target="#noteModal<?= $note['id'] ?>">
                                            <?= htmlspecialchars($note['title']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="note-type-badge note-type-<?= $note['type'] ?>">
                                            <i class="fas <?= $note['type'] === 'project' ? 'fa-project-diagram' : 
                                                           ($note['type'] === 'task' ? 'fa-check-circle' : 'fa-user') ?>"></i>
                                            <?= ucfirst($note['type']) ?>
                                        </span>
                                    </td>
                                    <td class="note-preview-cell">
                                        <span class="note-preview-text">
                                            <?= htmlspecialchars(substr($note['content'], 0, 100)) ?>
                                            <?= strlen($note['content']) > 100 ? '...' : '' ?>
                                        </span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($note['created_at'])) ?></td>
                                    <td class="text-end">
                                        <div class="table-actions">
                                            <button type="button" class="table-action-btn" data-bs-toggle="modal" data-bs-target="#noteModal<?= $note['id'] ?>" title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="/notes/edit/<?= $note['id'] ?>" class="table-action-btn" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="table-action-btn text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $note['id'] ?>" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Enhanced Empty State -->
        <div class="empty-state-container">
            <div class="empty-state-content">
                <div class="empty-state-icon">
                    <i class="fas fa-sticky-note"></i>
                </div>
                <h3 class="empty-state-title">No Notes Yet</h3>
                <p class="empty-state-subtitle">
                    Create your first note to keep track of important information, ideas, and thoughts
                </p>
                <a href="/notes/add" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i>
                    Create Your First Note
                </a>
            </div>
        </div>
    <?php endif; ?>
    
</div> <!-- End Main Content Container -->

<style>
/* Stats Cards */
.stats-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    margin-right: 1rem;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.stats-label {
    color: #718096;
    font-size: 0.9rem;
    margin-bottom: 0;
}

/* Enhanced Filter Panel */
.filter-panel-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
}

.filter-panel-card .card-header {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border-bottom: 1px solid #e2e8f0;
    padding: 1.5rem;
}

.filter-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.filter-subtitle {
    color: #718096;
    font-size: 0.9rem;
}

.results-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
}

.search-label, .filter-label, .sort-label, .view-label {
    display: block;
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.search-box {
    position: relative;
}

.search-input {
    padding-left: 2.5rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.search-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #a0aec0;
    z-index: 3;
}

.search-clear {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #a0aec0;
    cursor: pointer;
    z-index: 3;
}

.modern-select {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem;
    transition: all 0.3s ease;
}

.modern-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.view-toggle-group {
    display: flex;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.view-toggle {
    background: white;
    border: 1px solid #e2e8f0;
    padding: 0.75rem 1rem;
    color: #4a5568;
    cursor: pointer;
    transition: all 0.3s ease;
    flex: 1;
}

.view-toggle:hover {
    background: #f7fafc;
}

.view-toggle.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

/* Modern Note Cards */
.modern-note-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.modern-note-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.15);
}

.note-header {
    padding: 1.5rem 1.5rem 1rem;
    display: flex;
    justify-content: between;
    align-items: flex-start;
}

.note-type-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
}

.note-type-project {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.note-type-task {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
}

.note-type-personal {
    background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
}

.note-menu-btn {
    background: none;
    border: none;
    color: #a0aec0;
    padding: 0.5rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.note-menu-btn:hover {
    background: #f7fafc;
    color: #4a5568;
}

.note-content {
    padding: 0 1.5rem;
    flex: 1;
}

.note-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 1rem;
    line-height: 1.4;
}

.note-preview {
    color: #4a5568;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.note-footer {
    padding: 1rem 1.5rem;
    background: #f7fafc;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #e2e8f0;
}

.note-date {
    color: #718096;
    font-size: 0.9rem;
}

.btn-read-more {
    background: none;
    border: none;
    color: #667eea;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-read-more:hover {
    color: #5a67d8;
}

/* Modern Table */
.modern-table-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
}

.modern-table {
    margin-bottom: 0;
}

.modern-table thead th {
    background: #f7fafc;
    border-bottom: 2px solid #e2e8f0;
    color: #4a5568;
    font-weight: 600;
    padding: 1rem;
}

.modern-table tbody tr {
    transition: all 0.3s ease;
}

.modern-table tbody tr:hover {
    background: #f7fafc;
}

.modern-table tbody td {
    padding: 1rem;
    vertical-align: middle;
}

.note-title-link {
    color: #2d3748;
    text-decoration: none;
    font-weight: 600;
}

.note-title-link:hover {
    color: #667eea;
}

.note-preview-cell {
    max-width: 300px;
}

.note-preview-text {
    color: #4a5568;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.table-actions {
    display: flex;
    gap: 0.5rem;
}

.table-action-btn {
    background: none;
    border: none;
    color: #a0aec0;
    padding: 0.5rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.table-action-btn:hover {
    background: #f7fafc;
    color: #4a5568;
}

.table-action-btn.text-danger:hover {
    background: #fed7d7;
    color: #e53e3e;
}

/* Enhanced Empty State */
.empty-state-container {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
    padding: 3rem 0;
}

.empty-state-content {
    text-align: center;
    max-width: 500px;
}

.empty-state-icon {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    font-size: 3rem;
    color: white;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.empty-state-title {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 1rem;
}

.empty-state-subtitle {
    font-size: 1.1rem;
    color: #718096;
    margin-bottom: 2rem;
    line-height: 1.6;
}

/* Modern Modals */
.modern-modal {
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    border: none;
}

.modern-modal .modal-header {
    background: #f7fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 1.5rem;
}

.modern-modal .modal-body {
    padding: 1.5rem;
}

.modern-modal .modal-footer {
    background: #f7fafc;
    border-top: 1px solid #e2e8f0;
    padding: 1rem 1.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .stats-card {
        margin-bottom: 1rem;
    }
    
    .filter-panel-card .card-body {
        padding: 1rem !important;
    }
    
    .note-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .note-type-badge {
        margin-bottom: 0.5rem;
    }
    
    .note-menu-btn {
        align-self: flex-end;
        margin-top: -2.5rem;
    }
    
    .view-toggle-group {
        width: 100%;
        margin-top: 0.5rem;
    }
}

/* Animation Effects */
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

.modern-note-card {
    animation: fadeInUp 0.5s ease-out;
}

.stats-card {
    animation: fadeInUp 0.5s ease-out;
}

.filter-panel-card {
    animation: fadeInUp 0.3s ease-out;
}

/* Enhanced Hover Effects */
.modern-note-card:hover .note-type-badge {
    transform: scale(1.05);
}

.modern-note-card:hover .note-title {
    color: #667eea;
}

.stats-card:hover .stats-icon {
    transform: scale(1.1);
}

.stats-card:hover .stats-number {
    color: #667eea;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced placeholder rotation for search
    const searchInput = document.getElementById('noteSearch');
    const placeholders = [
        'Search by title or content...',
        'Find your project notes...',
        'Search for task reminders...',
        'Look for personal notes...'
    ];
    let currentPlaceholder = 0;
    
    if (searchInput) {
        setInterval(() => {
            currentPlaceholder = (currentPlaceholder + 1) % placeholders.length;
            searchInput.placeholder = placeholders[currentPlaceholder];
        }, 3000);
    }
    
    // Search clear functionality
    const clearSearch = document.getElementById('clearSearch');
    if (searchInput && clearSearch) {
        searchInput.addEventListener('input', function() {
            clearSearch.style.display = this.value ? 'block' : 'none';
        });
        
        clearSearch.addEventListener('click', function() {
            searchInput.value = '';
            this.style.display = 'none';
            filterNotes();
        });
    }
    
    // Utility function to clean up modal artifacts
    function cleanupModal() {
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';
        document.body.style.overflow = '';
    }
    
    // Handle global escape key press to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Find any open modals
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(modalElement => {
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                    cleanupModal();
                }
            });
            
            // Also check for any lingering backdrops
            cleanupModal();
        }
    });
    
    // Add click handler for backdrop to ensure it closes properly
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop')) {
            cleanupModal();
        }
    });

    // Initialize all dropdowns for the three-dot menus
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const dropdown = new bootstrap.Dropdown(toggle);
            dropdown.toggle();
        });
    });
    
    // Ensure modal triggers work properly
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        button.addEventListener('click', function(e) {
            const target = this.getAttribute('data-bs-target');
            if (target) {
                const modalElement = document.querySelector(target);
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            }
        });
    });
    
    // Fix for empty modals - make sure everything is properly initialized
    document.querySelectorAll('.modal').forEach(function(modalElement) {
        // Initialize modal
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: true,
            keyboard: true,
            focus: true
        });
        
        // Add event listeners for showing modal
        modalElement.addEventListener('show.bs.modal', function(event) {
            // Make sure modal content is visible
            const modalDialog = this.querySelector('.modal-dialog');
            if (modalDialog) {
                modalDialog.style.display = 'block';
            }
        });
        
        // Ensure proper cleanup when modal is hidden
        modalElement.addEventListener('hidden.bs.modal', function(event) {
            cleanupModal();
        });
    });
    
    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', filterNotes);
    }
    
    // Make sure delete buttons work in both views
    const allDeleteButtons = document.querySelectorAll('.dropdown-item.text-danger, .table-action-btn.text-danger');
    allDeleteButtons.forEach(button => {
        // Make sure every delete button has the note-id attribute
        if (!button.getAttribute('data-note-id')) {
            const modalId = button.getAttribute('data-bs-target');
            const noteId = modalId ? modalId.replace('#deleteModal', '') : '';
            if (noteId) {
                button.setAttribute('data-note-id', noteId);
            }
        }
        
        // Add click event to make sure dropdown properly closes when button is clicked
        button.addEventListener('click', function(e) {
            const dropdown = this.closest('.dropdown-menu');
            if (dropdown) {
                const dropdownInstance = bootstrap.Dropdown.getInstance(dropdown.previousElementSibling);
                if (dropdownInstance) {
                    dropdownInstance.hide();
                }
            }
        });
    });
    
    // Delete form submission with feedback
    const deleteForms = document.querySelectorAll('form[action^="/notes/delete/"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent normal form submission
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const modalElement = this.closest('.modal');
            const noteId = this.action.split('/').pop(); // Get note ID from form action
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Deleting...';
            
            // Use AJAX to delete
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // Mark as AJAX request
                }
            })
            .then(response => response.json())
            .then(data => {
                // Close the modal and remove backdrop
                const modal = bootstrap.Modal.getInstance(modalElement);
                modal.hide();
                
                // Ensure backdrop is removed
                setTimeout(() => {
                    cleanupModal();
                }, 100);
                
                if (data.success) {
                    // Remove the note from the page
                    const noteElements = document.querySelectorAll(`[data-note-id="${noteId}"]`);
                    noteElements.forEach(el => el.remove());
                    
                    // Show success alert
                    showAlert('success', data.message || 'Note deleted successfully');
                    
                    // Check if we need to show the empty state
                    const notes = document.querySelectorAll('.note-item');
                    if (notes.length === 0) {
                        setTimeout(() => location.reload(), 1000); // Reload page to show empty state
                    }
                } else {
                    // Show error alert
                    showAlert('danger', data.message || 'Failed to delete note');
                }
            })
            .catch(error => {
                console.error('Error deleting note:', error);
                showAlert('danger', 'Failed to delete note. Please try again.');
                
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-trash me-1"></i> Delete Note';
                
                // Ensure backdrop is removed
                setTimeout(() => {
                    cleanupModal();
                }, 100);
            });
        });
    });
    
    // Helper function to show alerts
    function showAlert(type, message) {
        // Create alert element
        const alertEl = document.createElement('div');
        alertEl.className = `alert alert-${type} alert-dismissible fade show mb-4`;
        alertEl.role = 'alert';
        alertEl.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Find container and insert alert at the top
        const container = document.querySelector('.container-fluid.px-4');
        const firstChild = container.firstElementChild;
        container.insertBefore(alertEl, firstChild);
        
        // Auto dismiss after 4 seconds
        setTimeout(() => {
            if (alertEl.parentNode) {
                new bootstrap.Alert(alertEl).close();
            }
        }, 4000);
    }
    
    // Type filter functionality
    const typeFilter = document.getElementById('noteTypeFilter');
    if (typeFilter) {
        typeFilter.addEventListener('change', filterNotes);
    }
    
    // Sort order functionality
    const sortOrder = document.getElementById('noteSortOrder');
    if (sortOrder) {
        sortOrder.addEventListener('change', sortNotes);
    }
    
    // View toggle functionality
    const viewGrid = document.getElementById('viewGrid');
    const viewList = document.getElementById('viewList');
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    
    if (viewGrid && viewList) {
        viewGrid.addEventListener('click', function() {
            viewGrid.classList.add('active');
            viewList.classList.remove('active');
            gridView.classList.remove('d-none');
            listView.classList.add('d-none');
        });
        
        viewList.addEventListener('click', function() {
            viewList.classList.add('active');
            viewGrid.classList.remove('active');
            listView.classList.remove('d-none');
            gridView.classList.add('d-none');
            
            // Re-apply current filtering and sorting
            filterNotes();
            sortNotes();
        });
    }
    
    // Helper function to filter notes
    function filterNotes() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const typeFilter = document.getElementById('noteTypeFilter').value;
        const noteItems = document.querySelectorAll('.note-item');
        let visibleCount = 0;
        
        noteItems.forEach(item => {
            const noteText = item.querySelector('.note-text')?.textContent.toLowerCase() || 
                            item.textContent.toLowerCase();
            const noteTitle = item.getAttribute('data-title').toLowerCase();
            const noteType = item.getAttribute('data-type');
            
            const matchesSearch = !searchTerm || noteTitle.includes(searchTerm) || noteText.includes(searchTerm);
            const matchesType = typeFilter === 'all' || noteType === typeFilter;
            
            if (matchesSearch && matchesType) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Show no results message if needed
        const noResultsMsg = document.getElementById('noResultsMessage');
        
        if (visibleCount === 0 && noteItems.length > 0) {
            if (!noResultsMsg) {
                const container = document.querySelector('.container-fluid.px-4');
                const message = document.createElement('div');
                message.id = 'noResultsMessage';
                message.className = 'alert alert-info text-center mt-4';
                message.innerHTML = `
                    <i class="fas fa-search me-2"></i>
                    No notes found matching "<strong>${searchTerm}</strong>"
                    ${typeFilter !== 'all' ? ` with type "<strong>${typeFilter}</strong>"` : ''}
                `;
                container.appendChild(message);
            }
        } else if (noResultsMsg) {
            noResultsMsg.remove();
        }
    }
    
    // Helper function to sort notes
    function sortNotes() {
        const sortValue = document.getElementById('noteSortOrder').value;
        
        // Sort grid view
        const gridContainer = document.getElementById('gridView');
        const gridItems = Array.from(gridContainer.querySelectorAll('.note-item'));
        sortItems(gridItems, gridContainer, sortValue);
        
        // Sort list view
        const listContainer = document.querySelector('#listView tbody');
        if (listContainer) {
            const listItems = Array.from(listContainer.querySelectorAll('tr.note-item'));
            sortItems(listItems, listContainer, sortValue);
        }
    }
    
    function sortItems(items, container, sortValue) {
        items.sort((a, b) => {
            if (sortValue === 'title') {
                const titleA = a.getAttribute('data-title').toLowerCase();
                const titleB = b.getAttribute('data-title').toLowerCase();
                return titleA.localeCompare(titleB);
            } else if (sortValue === 'oldest') {
                const dateA = new Date(a.getAttribute('data-date'));
                const dateB = new Date(b.getAttribute('data-date'));
                return dateA - dateB;
            } else { // newest first is default
                const dateA = new Date(a.getAttribute('data-date'));
                const dateB = new Date(b.getAttribute('data-date'));
                return dateB - dateA;
            }
        });
        
        // Remove all current items
        items.forEach(item => item.remove());
        
        // Add items back in sorted order
        items.forEach(item => {
            container.appendChild(item);
        });
    }
    
    // Initialize Bootstrap tooltips
    document.querySelectorAll('[title]').forEach(function(element) {
        new bootstrap.Tooltip(element);
    });
    
    // Add smooth scrolling for better UX
    document.documentElement.style.scrollBehavior = 'smooth';
    
    // Initialize animations on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe all note cards for scroll animation
    document.querySelectorAll('.modern-note-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});
</script>

<?php include_once VIEWSPATH . '/inc/footer.php'; ?> 