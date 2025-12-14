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
                        <span class="input-group-text border-end-0">
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
                        <?php 
                            $rawTags = $note['tags'] ?? '';
                            $noteTagList = array_values(array_filter(array_map('trim', explode(',', $rawTags))));
                            $noteTagsAttr = strtolower(implode(' ', $noteTagList));
                        ?>
                        <div class="mb-3 p-3 border rounded shadow-sm" data-note-id="<?= $note['id'] ?>" data-tags="<?= htmlspecialchars($noteTagsAttr) ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="mb-1 note-title"><?= htmlspecialchars($note['title']) ?></h6>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary rounded-circle" type="button" data-bs-toggle="dropdown">
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
                            <?php if (!empty($noteTagList)): ?>
                            <div class="mb-2 note-tag-list">
                                <?php foreach ($noteTagList as $tag): ?>
                                    <span class="badge note-tag-badge"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
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
                    <div class="mb-3">
                        <label for="modal-tags" class="form-label">Tags <span class="text-muted">(optional)</span></label>
                        <input type="text" class="form-control" id="modal-tags" name="tags" placeholder="e.g., kickoff, finance, follow-up">
                        <div class="form-text">Separate tags with commas</div>
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
            
            // Get form data manually to ensure all fields are included
            const formData = new FormData(this);
            console.log('Form data being submitted:', {
                title: formData.get('title'),
                content: formData.get('content'),
                type: formData.get('type'),
                reference_id: formData.get('reference_id'),
                tags: formData.get('tags')
            });
            
            const params = new URLSearchParams(formData);
            
            fetch('/notes/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: params
            })
            .then(response => {
                console.log(`Response received: Status ${response.status}, Content-Type: ${response.headers.get('content-type')}`);
                
                // Always get the text first so we can log it for debugging
                return response.text().then(text => {
                    // Try to parse as JSON if we can
                    let data;
                    try {
                        if (text && text.trim()) {
                            data = JSON.parse(text);
                            console.log('Parsed JSON response:', data);
                            return { ok: response.ok, json: data, text };
                        }
                    } catch (e) {
                        console.error('Error parsing JSON response:', e);
                    }
                    
                    return { ok: response.ok, json: null, text };
                });
            })
            .then(({ ok, json, text }) => {
                if (!ok) {
                    console.error('Server error response:', text);
                    throw new Error(`Server responded with status: ${text}`);
                }
                
                if (!json) {
                    console.error('Non-JSON response or empty response:', text);
                    throw new Error(`Expected JSON response but got: ${text.substring(0, 100)}`);
                }
                
                // Hide spinner
                spinner.classList.add('d-none');
                saveBtn.disabled = false;
                
                if (json.success) {
                    // Refresh notes list
                    refreshNotesList();
                    
                    // Clear form and close modal
                    this.reset();
                    bootstrap.Modal.getInstance(document.getElementById('addNoteModal')).hide();
                    
                    // Show success message
                    showAlert('success', json.message || 'Note added successfully');
                    
                    // Log whether we got full note details or just confirmation
                    if (json.note) {
                        console.log('Note created with full details:', json.note);
                    } else if (json.note_created) {
                        console.log('Note created successfully, without full details');
                    }
                } else {
                    // Show error message
                    showAlert('danger', json.message || 'Failed to add note');
                    
                    // If there are specific validation errors, show them
                    if (json.errors) {
                        let errorMsg = '<ul class="mb-0">';
                        for (const [field, error] of Object.entries(json.errors)) {
                            errorMsg += `<li>${error}</li>`;
                        }
                        errorMsg += '</ul>';
                        showAlert('danger', errorMsg);
                    }
                }
            })
            .catch(error => {
                // Hide spinner
                spinner.classList.add('d-none');
                saveBtn.disabled = false;
                
                // Add detailed debugging info
                try {
                    console.group('Debug - Note Submission Error');
                console.error('Error:', error);
                    console.log('Error message:', error.message);
                    
                    // Log form values
                    const formData = {
                        title: document.getElementById('title')?.value || 'Not found',
                        content: document.getElementById('content')?.value || 'Not found',
                        type: document.querySelector('input[name="type"]')?.value || 'Not found',
                        reference_id: document.querySelector('input[name="reference_id"]')?.value || 'Not found',
                        tags: document.getElementById('modal-tags')?.value || 'Not found'
                    };
                    
                    console.log('Form data:', formData);
                    console.log('Form data serialized:', new URLSearchParams(new FormData(addNoteForm)).toString());
                    console.groupEnd();
                } catch (debugError) {
                    console.error('Error during debugging:', debugError);
                }
                
                // Create a more detailed error message
                const errorMessage = document.createElement('div');
                errorMessage.innerHTML = `
                    <p class="mb-2">An error occurred while saving the note. Please try again.</p>
                    <div class="alert alert-danger">
                        <strong>Error:</strong> ${escapeHtml(error.toString())}
                    </div>
                    <details>
                        <summary>Technical Details (for support)</summary>
                        <div class="text-start">
                            <strong>Form data:</strong><br>
                            - Title: ${escapeHtml(document.getElementById('title')?.value || 'Not found')}<br>
                            - Type: ${escapeHtml(document.querySelector('input[name="type"]')?.value || 'Not found')}<br>
                            - Reference ID: ${escapeHtml(document.querySelector('input[name="reference_id"]')?.value || 'Not found')}
                        </div>
                    </details>
                `;
                
                showAlert('danger', errorMessage.innerHTML);
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
            document.querySelectorAll('[data-note-id]').forEach(note => {
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
    
    // Function to show alert messages
    function showAlert(type, message) {
        const alertContainer = document.getElementById('notes-alert-container');
        if (!alertContainer) return;
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        alertContainer.innerHTML = '';
        alertContainer.appendChild(alertDiv);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                bootstrap.Alert.getOrCreateInstance(alertDiv).close();
            }
        }, 5000);
    }
    
    // Function to refresh notes list
function refreshNotesList() {
        const notesListContainer = document.getElementById('notesList');
        if (!notesListContainer) return;
        
        const type = document.querySelector('input[name="type"]').value;
        const referenceId = document.querySelector('input[name="reference_id"]').value;
        
        fetch(`/notes/get/${type}/${referenceId}`)
        .then(response => response.json())
        .then(notes => {
            if (notes.length === 0) {
                    notesListContainer.innerHTML = `
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="bi bi-journal-album text-muted" style="font-size: 3rem;"></i>
                        </div>
                        <p class="text-muted mb-0">No notes yet. Add your first note to track important information.</p>
                    </div>
                `;
                    return;
                }
                
                // Update the notes list with the new data
                let notesHtml = `
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
                <div class="notes-container">`;
                
                notes.forEach((note, index) => {
                    const isLongContent = (note.content && note.content.split(' ').length > 30);
                    const tagList = note.tags ? note.tags.split(',').map(tag => tag.trim()).filter(Boolean) : [];
                    const tagsAttr = tagList.map(tag => tag.toLowerCase()).join(' ');
                    
                    notesHtml += `
                    <div class="mb-3 p-3 border rounded bg-white shadow-sm" data-note-id="${note.id}" data-tags="${tagsAttr}">
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
                            ${tagList.length ? `<div class="note-tag-list mb-2">${tagList.map(tag => `<span class="badge note-tag-badge">${escapeHtml(tag)}</span>`).join(' ')}</div>` : ''}
                            <div class="note-text-inner">
                                ${escapeHtml(note.content).replace(/\n/g, '<br>')}
                            </div>
                            ${isLongContent ? 
                                `<div class="note-fade-overlay"></div>
                                <button class="btn btn-sm btn-link note-expand-btn p-0">Show more</button>` 
                                : ''}
                        </div>
                        <div class="note-meta text-muted small d-flex justify-content-between">
                            <div>
                                <i class="bi bi-person"></i> ${escapeHtml(note.created_by_name)}
                            </div>
                            <div>
                                <i class="bi bi-clock"></i> ${formatDate(note.created_at)}
                            </div>
                        </div>
                    </div>
                    ${index < notes.length - 1 ? '<hr class="my-3">' : ''}
                    `;
                });
                
                notesHtml += '</div>';
                
                notesListContainer.innerHTML = notesHtml;
                
                // Reattach event listeners
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
                
                // Setup note filtering for new elements
                const notesFilter = document.getElementById('notes-filter');
                if (notesFilter) {
                    notesFilter.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        document.querySelectorAll('[data-note-id]').forEach(note => {
                            const title = note.querySelector('.note-title').textContent.toLowerCase();
                            const content = note.querySelector('.note-text-inner').textContent.toLowerCase();
                            const tags = (note.getAttribute('data-tags') || '');
                            note.style.display = (title.includes(searchTerm) || content.includes(searchTerm) || tags.includes(searchTerm)) ? '' : 'none';
                        });
                    });
                }
                
                // Reattach sorting functionality
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
        })
        .catch(error => {
                console.error('Error refreshing notes:', error);
                showAlert('danger', 'Error loading notes. Please refresh the page.');
            });
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Helper function to format dates
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
    
    // Function to sort notes
    function sortNotes(order) {
        const notesContainer = document.querySelector('.notes-container');
        if (!notesContainer) return;
        
        const notes = Array.from(notesContainer.querySelectorAll('[data-note-id]'));
        const separators = Array.from(notesContainer.querySelectorAll('hr'));
        
        // Remove all notes and separators from container
        notes.forEach(note => note.remove());
        separators.forEach(sep => sep.remove());
        
        // Sort notes by date
        notes.sort((a, b) => {
            const dateA = new Date(a.querySelector('.note-meta').lastElementChild.textContent);
            const dateB = new Date(b.querySelector('.note-meta').lastElementChild.textContent);
            return order === 'newest' ? dateB - dateA : dateA - dateB;
        });
        
        // Add sorted notes back with separators
        notes.forEach((note, index) => {
            notesContainer.appendChild(note);
            if (index < notes.length - 1) {
                const hr = document.createElement('hr');
                hr.className = 'my-3';
                notesContainer.appendChild(hr);
            }
        });
    }
    
    // Function to delete a note
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
                // Remove note from DOM
                const noteElement = document.querySelector(`[data-note-id="${noteId}"]`);
                const nextSeparator = noteElement.nextElementSibling;
                
                if (noteElement) {
                    noteElement.remove();
                    if (nextSeparator && nextSeparator.tagName === 'HR') {
                        nextSeparator.remove();
                    }
                }
                
                // Show success message
            showAlert('success', data.message || 'Note deleted successfully');
                
                // If no notes left, show empty state
                const remainingNotes = document.querySelectorAll('[data-note-id]');
                if (remainingNotes.length === 0) {
                    document.getElementById('notesList').innerHTML = `
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="bi bi-journal-album text-muted" style="font-size: 3rem;"></i>
                            </div>
                            <p class="text-muted mb-0">No notes yet. Add your first note to track important information.</p>
                        </div>
                    `;
                }
        } else {
                // Show error message
            showAlert('danger', data.message || 'Failed to delete note');
        }
    })
    .catch(error => {
        console.error('Error:', error);
            showAlert('danger', 'An error occurred. Please try again.');
        });
    }
});
</script> 
