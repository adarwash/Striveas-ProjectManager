<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Notes</h5>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addNoteModal">
            <i class="bi bi-plus-lg"></i> Add Note
        </button>
    </div>
    <div class="card-body">
        <div id="notesList">
            <?php if (empty($notes)): ?>
                <p class="text-muted mb-0">No notes yet.</p>
            <?php else: ?>
                <?php foreach ($notes as $note): ?>
                    <div class="note-item mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="mb-1"><?= htmlspecialchars($note['title']) ?></h6>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-dark" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="/notes/edit/<?= $note['id'] ?>">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <form action="/notes/delete/<?= $note['id'] ?>" method="POST" class="d-inline">
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this note?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="note-content mb-2">
                            <?= nl2br(htmlspecialchars($note['content'])) ?>
                        </div>
                        <div class="note-meta text-muted small">
                            <i class="bi bi-person"></i> <?= htmlspecialchars($note['created_by_name']) ?>
                            <i class="bi bi-clock ms-2"></i> <?= date('M j, Y g:i A', strtotime($note['created_at'])) ?>
                            <?php if ($note['updated_at'] !== $note['created_at']): ?>
                                <br><i class="bi bi-pencil"></i> Updated <?= date('M j, Y g:i A', strtotime($note['updated_at'])) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!$loop->last): ?>
                        <hr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/notes/add" method="POST" id="addNoteForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                    </div>
                    <input type="hidden" name="type" value="<?= $type ?>">
                    <input type="hidden" name="reference_id" value="<?= $reference_id ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('addNoteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    fetch('/notes/add', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh notes list
            fetch('/notes/get/<?= $type ?>/<?= $reference_id ?>')
                .then(response => response.json())
                .then(notes => {
                    const notesList = document.getElementById('notesList');
                    // Clear existing notes
                    notesList.innerHTML = '';
                    
                    if (notes.length === 0) {
                        notesList.innerHTML = '<p class="text-muted mb-0">No notes yet.</p>';
                    } else {
                        notes.forEach((note, index) => {
                            const noteHtml = `
                                <div class="note-item mb-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1">${escapeHtml(note.title)}</h6>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-dark" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="/notes/edit/${note.id}">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="/notes/delete/${note.id}" method="POST" class="d-inline">
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this note?')">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="note-content mb-2">
                                        ${escapeHtml(note.content).replace(/\n/g, '<br>')}
                                    </div>
                                    <div class="note-meta text-muted small">
                                        <i class="bi bi-person"></i> ${escapeHtml(note.created_by_name)}
                                        <i class="bi bi-clock ms-2"></i> ${formatDate(note.created_at)}
                                        ${note.updated_at !== note.created_at ? 
                                            `<br><i class="bi bi-pencil"></i> Updated ${formatDate(note.updated_at)}` : 
                                            ''}
                                    </div>
                                </div>
                                ${index < notes.length - 1 ? '<hr>' : ''}
                            `;
                            notesList.innerHTML += noteHtml;
                        });
                    }
                });
            
            // Clear form and close modal
            this.reset();
            bootstrap.Modal.getInstance(document.getElementById('addNoteModal')).hide();
            
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.card-body').insertBefore(alert, document.getElementById('notesList'));
        } else {
            // Show error message
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.modal-body').insertBefore(alert, document.querySelector('.modal-body .mb-3'));
        }
    });
});

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}
</script> 