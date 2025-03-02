<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Add Note</h1>
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
                    <form action="/notes/add" method="POST">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control <?= isset($title_err) && !empty($title_err) ? 'is-invalid' : '' ?>" 
                                   id="title" name="title" value="<?= htmlspecialchars($title ?? '') ?>">
                            <?php if (isset($title_err) && !empty($title_err)): ?>
                                <div class="invalid-feedback"><?= $title_err ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control <?= isset($content_err) && !empty($content_err) ? 'is-invalid' : '' ?>" 
                                      id="content" name="content" rows="5"><?= htmlspecialchars($content ?? '') ?></textarea>
                            <?php if (isset($content_err) && !empty($content_err)): ?>
                                <div class="invalid-feedback"><?= $content_err ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Attach to</label>
                            <select class="form-select <?= isset($type_err) && !empty($type_err) ? 'is-invalid' : '' ?>" 
                                    id="type" name="type" onchange="updateReferenceOptions()">
                                <option value="">Select type...</option>
                                <option value="project" <?= (isset($type) && $type === 'project') ? 'selected' : '' ?>>Project</option>
                                <option value="task" <?= (isset($type) && $type === 'task') ? 'selected' : '' ?>>Task</option>
                            </select>
                            <?php if (isset($type_err) && !empty($type_err)): ?>
                                <div class="invalid-feedback"><?= $type_err ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="reference_id" class="form-label">Select Item</label>
                            <select class="form-select <?= isset($reference_id_err) && !empty($reference_id_err) ? 'is-invalid' : '' ?>" 
                                    id="reference_id" name="reference_id">
                                <option value="">Select item...</option>
                            </select>
                            <?php if (isset($reference_id_err) && !empty($reference_id_err)): ?>
                                <div class="invalid-feedback"><?= $reference_id_err ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/notes" class="btn btn-outline-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Note</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Store projects and tasks data
const projects = <?= json_encode($projects ?? []) ?>;
const tasks = <?= json_encode($tasks ?? []) ?>;

function updateReferenceOptions() {
    const typeSelect = document.getElementById('type');
    const referenceSelect = document.getElementById('reference_id');
    const selectedType = typeSelect.value;
    
    // Clear current options
    referenceSelect.innerHTML = '<option value="">Select item...</option>';
    
    if (selectedType === 'project') {
        projects.forEach(project => {
            const option = document.createElement('option');
            option.value = project.id;
            option.textContent = project.title;
            referenceSelect.appendChild(option);
        });
    } else if (selectedType === 'task') {
        tasks.forEach(task => {
            const option = document.createElement('option');
            option.value = task.id;
            option.textContent = task.title;
            referenceSelect.appendChild(option);
        });
    }
}

// Initialize reference options if type is pre-selected
if (document.getElementById('type').value) {
    updateReferenceOptions();
    
    // Pre-select reference_id if it exists
    const preSelectedId = '<?= $reference_id ?? '' ?>';
    if (preSelectedId) {
        document.getElementById('reference_id').value = preSelectedId;
    }
}
</script> 