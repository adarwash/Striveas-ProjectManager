<?php require VIEWSPATH . '/inc/header.php'; ?>

<?php flash('note_message'); ?>

<?php if (!isset($note) || !$note): ?>
    <div class="container-fluid px-4 py-4">
        <div class="alert alert-danger">
            <h4><i class="bi bi-exclamation-triangle me-2"></i>Note Not Found</h4>
            <p>The note you're looking for doesn't exist or you don't have permission to view it.</p>
            <a href="/notes" class="btn btn-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Notes
            </a>
        </div>
    </div>
    <?php require VIEWSPATH . '/inc/footer.php'; ?>
    <?php return; ?>
<?php endif; ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="note-header mb-4">
        <div class="d-flex justify-content-between align-items-start">
            <div class="note-title-section">
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="/notes" class="text-decoration-none">Notes</a></li>
                        <li class="breadcrumb-item active" aria-current="page">View Note</li>
                    </ol>
                </nav>
                <h1 class="note-title">
                    <i class="bi bi-journal-text me-3"></i>
                    <?= htmlspecialchars($note['title'] ?? 'Untitled Note') ?>
                </h1>
                <div class="note-meta">
                    <span class="badge bg-<?= $note['type'] === 'personal' ? 'primary' : ($note['type'] === 'project' ? 'success' : 'info') ?> me-2">
                        <i class="bi bi-<?= $note['type'] === 'personal' ? 'person' : ($note['type'] === 'project' ? 'folder' : 'list-task') ?> me-1"></i>
                        <?= ucfirst($note['type']) ?> Note
                    </span>
                    <span class="text-muted">
                        <i class="bi bi-calendar3 me-1"></i>
                        Created on <?= date('F j, Y \a\t g:i A', strtotime($note['created_at'])) ?>
                    </span>
                    <?php if (isset($note['updated_at']) && $note['updated_at']): ?>
                        <span class="text-muted ms-3">
                            <i class="bi bi-pencil me-1"></i>
                            Last updated <?= date('F j, Y \a\t g:i A', strtotime($note['updated_at'])) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="note-actions">
                <div class="btn-group" role="group">
                    <a href="/notes/edit/<?= $note['id'] ?>" class="btn btn-outline-primary">
                        <i class="bi bi-pencil-square me-2"></i>Edit Note
                    </a>
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/notes/add?type=<?= $note['type'] ?><?= $note['reference_id'] ? '&reference_id=' . $note['reference_id'] : '' ?>">
                            <i class="bi bi-plus-circle me-2"></i>Add Similar Note
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteNote(<?= $note['id'] ?>)">
                            <i class="bi bi-trash me-2"></i>Delete Note
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Note Content -->
        <div class="col-lg-8">
            <div class="note-content-card">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="note-content">
                            <?php if (!empty($note['content'])): ?>
                                <?= nl2br(htmlspecialchars($note['content'])) ?>
                            <?php else: ?>
                                <div class="text-muted text-center py-4">
                                    <i class="bi bi-journal-x display-4 mb-3"></i>
                                    <p>This note appears to be empty.</p>
                                    <a href="/notes/edit/<?= $note['id'] ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-pencil me-2"></i>Add Content
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Note Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>Note Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="note-details">
                        <div class="detail-item mb-3">
                            <label class="detail-label">Type:</label>
                            <span class="detail-value">
                                <span class="badge bg-<?= $note['type'] === 'personal' ? 'primary' : ($note['type'] === 'project' ? 'success' : 'info') ?>">
                                    <i class="bi bi-<?= $note['type'] === 'personal' ? 'person' : ($note['type'] === 'project' ? 'folder' : 'list-task') ?> me-1"></i>
                                    <?= ucfirst($note['type']) ?>
                                </span>
                            </span>
                        </div>
                        
                        <div class="detail-item mb-3">
                            <label class="detail-label">Created:</label>
                            <span class="detail-value"><?= date('M j, Y g:i A', strtotime($note['created_at'])) ?></span>
                        </div>
                        
                        <?php if (isset($note['updated_at']) && $note['updated_at']): ?>
                        <div class="detail-item mb-3">
                            <label class="detail-label">Last Updated:</label>
                            <span class="detail-value"><?= date('M j, Y g:i A', strtotime($note['updated_at'])) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="detail-item mb-3">
                            <label class="detail-label">Word Count:</label>
                            <span class="detail-value" id="wordCount"><?= str_word_count($note['content'] ?? '') ?> words</span>
                        </div>
                        
                        <div class="detail-item">
                            <label class="detail-label">Character Count:</label>
                            <span class="detail-value"><?= strlen($note['content'] ?? '') ?> characters</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Related Information -->
            <?php if ($related_info): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-link-45deg me-2"></i>Related <?= ucfirst($related_info['type']) ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="related-info">
                        <h6 class="related-title">
                            <i class="bi bi-<?= $related_info['type'] === 'project' ? 'folder' : 'list-task' ?> me-2"></i>
                            <?= htmlspecialchars($related_info['title'] ?? $related_info['name'] ?? 'Unknown') ?>
                        </h6>
                        
                        <?php if (isset($related_info['description']) && $related_info['description']): ?>
                        <p class="related-description text-muted">
                            <?= substr(htmlspecialchars($related_info['description']), 0, 150) ?>
                            <?= strlen($related_info['description']) > 150 ? '...' : '' ?>
                        </p>
                        <?php endif; ?>
                        
                        <div class="related-actions mt-3">
                            <a href="/<?= $related_info['type'] ?>s/<?= $related_info['type'] === 'project' ? 'viewProject' : 'show' ?>/<?= $related_info['id'] ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-arrow-right-circle me-1"></i>
                                View <?= ucfirst($related_info['type']) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/notes/add" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Create New Note
                        </a>
                        
                        <a href="/notes" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Notes
                        </a>
                        
                        <button class="btn btn-outline-info" onclick="printNote()">
                            <i class="bi bi-printer me-2"></i>Print Note
                        </button>
                        
                        <button class="btn btn-outline-success" onclick="exportNote()">
                            <i class="bi bi-download me-2"></i>Export as Text
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modern Styling -->
<style>
.note-header {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem 2rem;
    color: #333;
    margin-bottom: 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border: 1px solid rgba(0,0,0,0.05);
}

.note-header .breadcrumb {
    background: none;
    padding: 0;
    margin: 0;
}

.note-header .breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
}

.note-header .breadcrumb-item a:hover {
    color: #333;
}

.note-header .breadcrumb-item.active {
    color: #333;
}

.note-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0.5rem 0;
    color: #2c3e50;
    display: flex;
    align-items: center;
}

.note-title i {
    color: #6a5acd;
    margin-right: 0.75rem;
}

/* Ensure consistent page header styling */
.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    display: flex;
    align-items: center;
}

.page-title i {
    color: #6a5acd;
    margin-right: 0.75rem;
}

.note-meta {
    font-size: 0.95rem;
    color: #6c757d;
    margin: 0;
}

.note-meta .badge {
    background: rgba(255, 255, 255, 0.2) !important;
    color: white !important;
}

.note-actions .btn {
    border-radius: 8px;
    font-weight: 500;
}

.note-content-card {
    margin-bottom: 2rem;
}

.note-content-card .card {
    border-radius: 16px;
    overflow: hidden;
}

.note-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #2d3748;
}

.card {
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: none;
}

.card-header {
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0;
    border-radius: 12px 12px 0 0 !important;
}

.card-title {
    color: #4a5568;
    font-weight: 600;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #4a5568;
    margin: 0;
}

.detail-value {
    color: #2d3748;
    font-weight: 500;
}

.related-title {
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.related-description {
    font-size: 0.9rem;
    line-height: 1.5;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Print styles */
@media print {
    .note-actions,
    .card:not(.note-content-card .card),
    .note-header .note-actions {
        display: none !important;
    }
    
    .note-header {
        background: white !important;
        color: #333 !important;
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    
    .note-content {
        font-size: 12pt;
        line-height: 1.6;
    }
}

/* Responsive design */
@media (max-width: 768px) {
    .note-header {
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .note-header .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 1rem;
    }
    
    .note-title {
        font-size: 1.5rem;
    }
    
    .note-meta {
        font-size: 0.85rem;
    }
    
    .note-actions {
        margin-top: 1rem;
    }
    
    .btn-group {
        width: 100%;
    }
    
    .btn-group .btn {
        flex: 1;
    }
}
</style>

<!-- JavaScript for functionality -->
<script>
function deleteNote(noteId) {
    if (confirm('Are you sure you want to delete this note? This action cannot be undone.')) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/notes/delete/' + noteId;
        
        // Add CSRF token if available
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrfToken.getAttribute('content');
            form.appendChild(tokenInput);
        }
        
        document.body.appendChild(form);
        form.submit();
    }
}

function printNote() {
    window.print();
}

function exportNote() {
    const noteTitle = <?= json_encode($note['title'] ?? 'Untitled Note') ?>;
    const noteContent = <?= json_encode(($note['title'] ?? 'Untitled Note') . "\n\n" . ($note['content'] ?? '') . "\n\nCreated: " . date('F j, Y \a\t g:i A', strtotime($note['created_at'] ?? 'now'))) ?>;
    
    const blob = new Blob([noteContent], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = noteTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Initialize tooltips if Bootstrap is available
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
</script>

<?php require VIEWSPATH . '/inc/footer.php'; ?> 