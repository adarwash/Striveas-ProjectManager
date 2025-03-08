<div class="card mt-4" id="notes-section">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-journal-text me-2"></i>Notes
            <span class="badge bg-secondary ms-2"><?= count($notes ?? []) ?></span>
        </h5>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addNoteModal">
            <i class="bi bi-plus-lg"></i> Add Note
        </button>
    </div>
    <div class="card-body">
        <div id="notes-alert-container"></div>
        <div id="notesList">
            <?php if (empty($notes)): ?>
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="bi bi-journal-album text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-muted mb-0">No notes yet. Add your first note to track important information.</p>
                </div>
            <?php else: ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="input-group input-group-sm" style="max-width: 250px;">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="notes-filter" placeholder="Filter notes...">
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary active" id="sort-newest">Newest</button>
                        <button type="button" class="btn btn-outline-secondary" id="sort-oldest">Oldest</button>
                    </div>
                </div>
                <div class="notes-container">
                    <?php foreach ($notes as $index => $note): ?>
                        <div class="note-item mb-3 p-3 border rounded bg-white shadow-sm" data-note-id="<?= $note['id'] ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="mb-1 note-title"><?= htmlspecialchars($note['title']) ?></h6>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="/notes/edit/<?= $note['id'] ?>">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item note-delete-btn" data-note-id="<?= $note['id'] ?>">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="note-content mb-2 note-text" style="max-height: 100px; overflow: hidden; position: relative;">
                                <div class="note-text-inner">
                                    <?= nl2br(htmlspecialchars($note['content'])) ?>
                                </div>
                                <?php if (str_word_count($note['content']) > 30): ?>
                                    <div class="note-fade-overlay"></div>
                                    <button class="btn btn-sm btn-link note-expand-btn p-0">Show more</button>
                                <?php endif; ?>
                            </div>
                            <div class="note-meta text-muted small d-flex justify-content-between">
                                <div>
                                    <i class="bi bi-person"></i> <?= htmlspecialchars($note['created_by_name']) ?>
                                </div>
                                <div>
                                    <i class="bi bi-clock"></i> <?= date('M j, Y', strtotime($note['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <?php if ($index < count($notes) - 1): ?>
                            <hr class="my-3">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
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
            <form id="addNoteForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                        <div class="form-text">
                            <span id="content-count">0</span> characters
                        </div>
                    </div>
                    <input type="hidden" name="type" value="<?= $type ?>">
                    <input type="hidden" name="reference_id" value="<?= $reference_id ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="save-note-btn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="note-spinner"></span>
                        Save Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.note-fade-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 40px;
    background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,1));
}
.note-text.expanded {
    max-height: none !important;
}
.note-text.expanded .note-fade-overlay {
    display: none;
}
.notes-container .note-item {
    transition: transform 0.2s ease;
}
.notes-container .note-item:hover {
    transform: translateY(-2px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Setup character counter
    const contentField = document.getElementById('content');
    const contentCount = document.getElementById('content-count');
    if (contentField && contentCount) {
        contentField.addEventListener('input', function() {
            contentCount.textContent = this.value.length;
        });
    }

    // Add note form submission
    const addNoteForm = document.getElementById('addNoteForm');
    if (addNoteForm) {
        addNoteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show spinner
            const spinner = document.getElementById('note-spinner');
            const saveBtn = document.getElementById('save-note-btn');
            spinner.classList.remove('d-none');
            saveBtn.disabled = true;
            
            fetch('/notes/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(new FormData(this))
            })
            .then(response => response.json())
            .then(data => {
                // Hide spinner
                spinner.classList.add('d-none');
                saveBtn.disabled = false;
                
                if (data.success) {
                    // Refresh notes list
                    refreshNotesList();
                    
                    // Clear form and close modal
                    this.reset();
                    bootstrap.Modal.getInstance(document.getElementById('addNoteModal')).hide();
                    
                    // Show success message
                    showAlert('success', data.message || 'Note added successfully');
                } else {
                    // Show error message
                    showAlert('danger', data.message || 'Failed to add note');
                }
            })
            .catch(error => {
                // Hide spinner
                spinner.classList.add('d-none');
                saveBtn.disabled = false;
                
                console.error('Error:', error);
                showAlert('danger', 'An error occurred. Please try again.');
            });
        });
    }
    
    // Setup note expansion buttons
    document.querySelectorAll('.note-expand-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const noteText = this.closest('.note-text');
            noteText.classList.toggle('expanded');
            this.textContent = noteText.classList.contains('expanded') ? 'Show less' : 'Show more';
        });
    });
    
    // Setup note filtering
    const notesFilter = document.getElementById('notes-filter');
    if (notesFilter) {
        notesFilter.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.note-item').forEach(note => {
                const title = note.querySelector('.note-title').textContent.toLowerCase();
                const content = note.querySelector('.note-text-inner').textContent.toLowerCase();
                note.style.display = (title.includes(searchTerm) || content.includes(searchTerm)) ? '' : 'none';
            });
        });
    }
    
    // Setup note sorting
    document.getElementById('sort-newest')?.addEventListener('click', function() {
        this.classList.add('active');
        document.getElementById('sort-oldest').classList.remove('active');
        sortNotes('newest');
    });
    
    document.getElementById('sort-oldest')?.addEventListener('click', function() {
        this.classList.add('active');
        document.getElementById('sort-newest').classList.remove('active');
        sortNotes('oldest');
    });
    
    // Setup delete note buttons
    document.querySelectorAll('.note-delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const noteId = this.getAttribute('data-note-id');
            if (confirm('Are you sure you want to delete this note?')) {
                deleteNote(noteId);
            }
        });
    });
});

function sortNotes(order) {
    const notesContainer = document.querySelector('.notes-container');
    if (!notesContainer) return;
    
    const notes = Array.from(notesContainer.querySelectorAll('.note-item'));
    
    // Remove current notes
    notes.forEach(note => note.remove());
    
    // Sort notes
    notes.sort((a, b) => {
        const aDate = new Date(a.querySelector('.note-meta').textContent.split('clock')[1].trim());
        const bDate = new Date(b.querySelector('.note-meta').textContent.split('clock')[1].trim());
        
        return order === 'newest' ? bDate - aDate : aDate - bDate;
    });
    
    // Add sorted notes back
    notes.forEach((note, index) => {
        notesContainer.appendChild(note);
        
        // Add hr after each note except the last one
        if (index < notes.length - 1) {
            const hr = document.createElement('hr');
            hr.className = 'my-3';
            notesContainer.appendChild(hr);
        }
    });
}

function refreshNotesList() {
    fetch(`/notes/get/<?= $type ?>/<?= $reference_id ?>`)
        .then(response => response.json())
        .then(notes => {
            const notesList = document.getElementById('notesList');
            if (!notesList) return;
            
            if (notes.length === 0) {
                notesList.innerHTML = `
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="bi bi-journal-album text-muted" style="font-size: 3rem;"></i>
                        </div>
                        <p class="text-muted mb-0">No notes yet. Add your first note to track important information.</p>
                    </div>
                `;
            } else {
                let html = `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="input-group input-group-sm" style="max-width: 250px;">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="notes-filter" placeholder="Filter notes...">
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary active" id="sort-newest">Newest</button>
                            <button type="button" class="btn btn-outline-secondary" id="sort-oldest">Oldest</button>
                        </div>
                    </div>
                    <div class="notes-container">
                `;
                
                notes.forEach((note, index) => {
                    html += generateNoteHTML(note, index, notes.length);
                });
                
                html += '</div>';
                notesList.innerHTML = html;
                
                // Reinitialize event listeners
                document.querySelectorAll('.note-expand-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const noteText = this.closest('.note-text');
                        noteText.classList.toggle('expanded');
                        this.textContent = noteText.classList.contains('expanded') ? 'Show less' : 'Show more';
                    });
                });
                
                document.querySelectorAll('.note-delete-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const noteId = this.getAttribute('data-note-id');
                        if (confirm('Are you sure you want to delete this note?')) {
                            deleteNote(noteId);
                        }
                    });
                });
                
                document.getElementById('notes-filter')?.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    document.querySelectorAll('.note-item').forEach(note => {
                        const title = note.querySelector('.note-title').textContent.toLowerCase();
                        const content = note.querySelector('.note-text-inner').textContent.toLowerCase();
                        note.style.display = (title.includes(searchTerm) || content.includes(searchTerm)) ? '' : 'none';
                    });
                });
                
                document.getElementById('sort-newest')?.addEventListener('click', function() {
                    this.classList.add('active');
                    document.getElementById('sort-oldest').classList.remove('active');
                    sortNotes('newest');
                });
                
                document.getElementById('sort-oldest')?.addEventListener('click', function() {
                    this.classList.add('active');
                    document.getElementById('sort-newest').classList.remove('active');
                    sortNotes('oldest');
                });
            }
            
            // Update header count
            const header = document.querySelector('#notes-section .card-header h5 .badge');
            if (header) {
                header.textContent = notes.length;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Failed to refresh notes list');
        });
}

function generateNoteHTML(note, index, totalNotes) {
    const hasLongContent = (note.content && note.content.split(' ').length > 30);
    const date = new Date(note.created_at);
    const formattedDate = date.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'});
    
    let html = `
        <div class="note-item mb-3 p-3 border rounded bg-white shadow-sm" data-note-id="${note.id}">
            <div class="d-flex justify-content-between align-items-start">
                <h6 class="mb-1 note-title">${escapeHtml(note.title)}</h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="/notes/edit/${note.id}">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item note-delete-btn" data-note-id="${note.id}">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="note-content mb-2 note-text" style="max-height: 100px; overflow: hidden; position: relative;">
                <div class="note-text-inner">
                    ${escapeHtml(note.content).replace(/\n/g, '<br>')}
                </div>
                ${hasLongContent ? `
                    <div class="note-fade-overlay"></div>
                    <button class="btn btn-sm btn-link note-expand-btn p-0">Show more</button>
                ` : ''}
            </div>
            <div class="note-meta text-muted small d-flex justify-content-between">
                <div>
                    <i class="bi bi-person"></i> ${escapeHtml(note.created_by_name)}
                </div>
                <div>
                    <i class="bi bi-clock"></i> ${formattedDate}
                </div>
            </div>
        </div>
    `;
    
    if (index < totalNotes - 1) {
        html += '<hr class="my-3">';
    }
    
    return html;
}

function deleteNote(noteId) {
    fetch(`/notes/delete/${noteId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            refreshNotesList();
            showAlert('success', data.message || 'Note deleted successfully');
        } else {
            showAlert('danger', data.message || 'Failed to delete note');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while trying to delete the note');
    });
}

function showAlert(type, message) {
    const alertContainer = document.getElementById('notes-alert-container');
    if (!alertContainer) return;
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.innerHTML = '';
    alertContainer.appendChild(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    }, 5000);
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script> 