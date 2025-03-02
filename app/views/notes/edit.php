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
        <a href="/notes" class="btn btn-light">
            <i class="bi bi-arrow-left me-1"></i> Back to Notes
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 col-md-12 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Note Details</h5>
            </div>
            <div class="card-body">
                <form action="/notes/edit/<?= $note['id'] ?>" method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control <?= isset($title_err) && !empty($title_err) ? 'is-invalid' : '' ?>" 
                               id="title" name="title" value="<?= htmlspecialchars($note['title'] ?? '') ?>">
                        <?php if (isset($title_err) && !empty($title_err)): ?>
                            <div class="invalid-feedback"><?= $title_err ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control <?= isset($content_err) && !empty($content_err) ? 'is-invalid' : '' ?>" 
                                  id="content" name="content" rows="5"><?= htmlspecialchars($note['content'] ?? '') ?></textarea>
                        <?php if (isset($content_err) && !empty($content_err)): ?>
                            <div class="invalid-feedback"><?= $content_err ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Attached to</label>
                        <p class="form-control-static">
                            <?php 
                            $type = ucfirst($note['type']);
                            $reference = $note['type'] === 'project' ? $projects[$note['reference_id']] ?? null : $tasks[$note['reference_id']] ?? null;
                            echo $type . ': ' . ($reference ? htmlspecialchars($reference['title']) : 'Unknown');
                            ?>
                        </p>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/notes" class="btn btn-outline-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Note</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 