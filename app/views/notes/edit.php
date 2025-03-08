<?php
// Set title for the page
$title = 'Edit Note - ProjectTracker';
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Note</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/notes">Notes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="/notes" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Notes
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 col-md-12 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Note Details</h5>
                <span class="badge rounded-pill <?= $note['type'] === 'project' ? 'bg-primary' : 'bg-success' ?>">
                    <i class="bi <?= $note['type'] === 'project' ? 'bi-kanban' : 'bi-check2-square' ?>"></i>
                    <?= ucfirst($note['type']) ?> Note
                </span>
            </div>
            <div class="card-body">
                <?php flash('note_error'); ?>
                
                <form action="/notes/edit/<?= $note['id'] ?>" method="POST">
                    <div class="mb-4">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control form-control-lg <?= isset($title_err) && !empty($title_err) ? 'is-invalid' : '' ?>" 
                               id="title" name="title" value="<?= htmlspecialchars($note['title'] ?? '') ?>" 
                               placeholder="Enter a descriptive title" autofocus>
                        <?php if (isset($title_err) && !empty($title_err)): ?>
                            <div class="invalid-feedback"><?= $title_err ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control <?= isset($content_err) && !empty($content_err) ? 'is-invalid' : '' ?>" 
                                  id="content" name="content" rows="8" 
                                  placeholder="Enter note details here..."><?= htmlspecialchars($note['content'] ?? '') ?></textarea>
                        <?php if (isset($content_err) && !empty($content_err)): ?>
                            <div class="invalid-feedback"><?= $content_err ?></div>
                        <?php endif; ?>
                        <div class="form-text">
                            <span id="content-counter">0</span>/1000 characters
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            <i class="bi bi-link-45deg"></i> Linked to
                        </label>
                        <div class="p-3 bg-light rounded border">
                            <?php 
                            $typeIcon = $note['type'] === 'project' ? 'bi-kanban' : 'bi-check2-square';
                            $typeBadge = $note['type'] === 'project' ? 'bg-primary' : 'bg-success';
                            $typeText = ucfirst($note['type']);
                            
                            if ($note['type'] === 'project') {
                                $reference = $projects[$note['reference_id']] ?? null;
                            } else {
                                $reference = $tasks[$note['reference_id']] ?? null;
                            }
                            
                            $referenceTitle = $reference ? htmlspecialchars($reference['title']) : 'Unknown';
                            $referenceLink = '/' . $note['type'] . 's/show/' . $note['reference_id'];
                            ?>
                            
                            <div class="d-flex align-items-center">
                                <span class="badge <?= $typeBadge ?> me-2">
                                    <i class="bi <?= $typeIcon ?>"></i> <?= $typeText ?>
                                </span>
                                <a href="<?= $referenceLink ?>" class="text-decoration-none text-body">
                                    <strong><?= $referenceTitle ?></strong>
                                </a>
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="bi bi-info-circle"></i> Note association cannot be changed
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between border-top pt-4 mt-4">
                        <div class="text-muted small">
                            <i class="bi bi-clock"></i> Created: <?= date('M j, Y g:i A', strtotime($note['created_at'])) ?>
                            <?php if ($note['updated_at'] && $note['updated_at'] != $note['created_at']): ?>
                                <br><i class="bi bi-pencil"></i> Last updated: <?= date('M j, Y g:i A', strtotime($note['updated_at'])) ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <a href="/notes" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter for content
    const contentField = document.getElementById('content');
    const counterSpan = document.getElementById('content-counter');
    
    function updateCounter() {
        const count = contentField.value.length;
        counterSpan.textContent = count;
        
        if (count > 900) {
            counterSpan.classList.add('text-warning');
        } else {
            counterSpan.classList.remove('text-warning');
        }
        
        if (count > 1000) {
            counterSpan.classList.add('text-danger');
        } else {
            counterSpan.classList.remove('text-danger');
        }
    }
    
    // Update on page load
    updateCounter();
    
    // Update on input
    contentField.addEventListener('input', updateCounter);
    
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        let valid = true;
        
        // Validate title
        const title = document.getElementById('title');
        if (!title.value.trim()) {
            title.classList.add('is-invalid');
            valid = false;
        } else {
            title.classList.remove('is-invalid');
        }
        
        // Validate content
        if (!contentField.value.trim()) {
            contentField.classList.add('is-invalid');
            valid = false;
        } else {
            contentField.classList.remove('is-invalid');
        }
        
        if (!valid) {
            e.preventDefault();
        }
    });
});
</script> 