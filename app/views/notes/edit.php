<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Edit Note</h1>
                <a href="/notes" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Notes
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
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
</div> 