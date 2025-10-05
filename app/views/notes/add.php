<div class="container">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title">
                <i class="fas fa-plus me-3"></i>Add Note
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/notes">Notes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add New</li>
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
        <div class="col-lg-12 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Create New Note</h5>
                </div>
                <div class="card-body">
                    <form action="/notes/add" method="POST">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control form-control-lg <?= isset($title_err) && !empty($title_err) ? 'is-invalid' : '' ?>" 
                                   id="title" name="title" value="<?= htmlspecialchars($title ?? '') ?>" 
                                   placeholder="Enter a descriptive title">
                            <?php if (isset($title_err) && !empty($title_err)): ?>
                                <div class="invalid-feedback"><?= $title_err ?></div>
                            <?php endif; ?>
                            <div class="form-text">A clear, descriptive title helps you find this note later</div>
                        </div>

                        <div class="mb-4">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control <?= isset($content_err) && !empty($content_err) ? 'is-invalid' : '' ?>" 
                                      id="content" name="content" rows="8" placeholder="Write your note details here..."><?= htmlspecialchars($content ?? '') ?></textarea>
                            <?php if (isset($content_err) && !empty($content_err)): ?>
                                <div class="invalid-feedback"><?= $content_err ?></div>
                            <?php endif; ?>
                            <div class="form-text">You can use plain text - line breaks will be preserved</div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Link your note</h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="type" class="form-label">Link Type</label>
                                <select class="form-select <?= isset($type_err) && !empty($type_err) ? 'is-invalid' : '' ?>" 
                                        id="type" name="type" onchange="updateReferenceOptions()">
                                    <option value="">Select type...</option>
                                    <option value="project" <?= (isset($type) && $type === 'project') ? 'selected' : '' ?>>Project</option>
                                    <option value="task" <?= (isset($type) && $type === 'task') ? 'selected' : '' ?>>Task</option>
                                    <option value="personal" <?= (isset($type) && $type === 'personal') ? 'selected' : '' ?>>Personal</option>
                                </select>
                                <?php if (isset($type_err) && !empty($type_err)): ?>
                                    <div class="invalid-feedback"><?= $type_err ?></div>
                                <?php endif; ?>
                                <div class="form-text">Personal notes aren't linked to any project or task</div>
                            </div>

                            <div class="col-md-6" id="reference-container">
                                <label for="reference_id" class="form-label">Link To</label>
                                <select class="form-select <?= isset($reference_id_err) && !empty($reference_id_err) ? 'is-invalid' : '' ?>" 
                                        id="reference_id" name="reference_id">
                                    <option value="">Select item...</option>
                                </select>
                                <?php if (isset($reference_id_err) && !empty($reference_id_err)): ?>
                                    <div class="invalid-feedback"><?= $reference_id_err ?></div>
                                <?php endif; ?>
                                <div id="reference-none" class="form-text d-none">
                                    <i class="bi bi-info-circle"></i> Select a type first
                                </div>
                                <div id="reference-loading" class="form-text d-none">
                                    <i class="bi bi-hourglass"></i> Loading items...
                                </div>
                                <div id="reference-empty" class="form-text d-none">
                                    <i class="bi bi-exclamation-circle"></i> No items available
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="/notes" class="btn btn-outline-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Note
                            </button>
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
    const referenceContainer = document.getElementById('reference-container');
    const selectedType = typeSelect.value;
    
    // Reference helper messages
    const noneMessage = document.getElementById('reference-none');
    const loadingMessage = document.getElementById('reference-loading');
    const emptyMessage = document.getElementById('reference-empty');
    
    // Clear current options
    referenceSelect.innerHTML = '<option value="">Select item...</option>';
    
    // Hide all messages initially
    noneMessage.classList.add('d-none');
    loadingMessage.classList.add('d-none');
    emptyMessage.classList.add('d-none');
    
    // Hide/show reference container based on type
    if (selectedType === 'personal') {
        referenceContainer.classList.add('d-none');
        return;
    } else {
        referenceContainer.classList.remove('d-none');
    }
    
    if (!selectedType) {
        noneMessage.classList.remove('d-none');
        return;
    }
    
    // Show loading message briefly
    loadingMessage.classList.remove('d-none');
    
    setTimeout(() => {
        loadingMessage.classList.add('d-none');
        
        if (selectedType === 'project') {
            if (projects.length === 0) {
                emptyMessage.classList.remove('d-none');
            } else {
                projects.forEach(project => {
                    const option = document.createElement('option');
                    option.value = project.id;
                    option.textContent = project.title;
                    referenceSelect.appendChild(option);
                });
            }
        } else if (selectedType === 'task') {
            if (tasks.length === 0) {
                emptyMessage.classList.remove('d-none');
            } else {
                tasks.forEach(task => {
                    const option = document.createElement('option');
                    option.value = task.id;
                    option.textContent = task.title;
                    referenceSelect.appendChild(option);
                });
            }
        }
        
        // Pre-select reference_id if it exists
        const preSelectedId = '<?= $reference_id ?? '' ?>';
        if (preSelectedId) {
            referenceSelect.value = preSelectedId;
        }
    }, 300); // Short delay for the loading indicator
}

// Initialize reference options if type is pre-selected
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('type').value) {
        updateReferenceOptions();
    } else {
        document.getElementById('reference-none').classList.remove('d-none');
    }
    
    // Add character counter for note content
    const contentField = document.getElementById('content');
    contentField.addEventListener('input', function() {
        const length = this.value.length;
        // You can add a character counter here if needed
    });
});
</script> 