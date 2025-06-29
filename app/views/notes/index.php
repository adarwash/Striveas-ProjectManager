<?php include_once VIEWSPATH . '/inc/header.php'; ?>

<div class="container-fluid py-4">
    <!-- Page Header with Background -->
    <div class="bg-light rounded-3 p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1"><?= $data['title'] ?></h1>
                <p class="text-muted">Your collection of thoughts, ideas, and important information</p>
            </div>
            <a href="/notes/add" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add Note
            </a>
        </div>
    </div>
    
    <!-- Flash messages -->
    <?php flash('note_success'); ?>
    <?php flash('note_error'); ?>
    
    <?php if (!empty($data['notes'])): ?>
        <!-- Search and Filter Options -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-5 col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="noteSearch" class="form-control border-start-0" placeholder="Search notes...">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <select id="noteTypeFilter" class="form-select">
                            <option value="all">All Types</option>
                            <option value="project">Projects</option>
                            <option value="task">Tasks</option>
                            <option value="personal">Personal</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-3">
                        <select id="noteSortOrder" class="form-select">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="title">Title (A-Z)</option>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-12 text-end">
                        <div class="btn-group" role="group">
                            <button type="button" id="viewGrid" class="btn btn-outline-secondary active" title="Grid View">
                                <i class="bi bi-grid-3x3-gap-fill"></i>
                            </button>
                            <button type="button" id="viewList" class="btn btn-outline-secondary" title="List View">
                                <i class="bi bi-list-ul"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes Grid View (Default) -->
        <div id="gridView" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
            <?php foreach ($data['notes'] as $note): ?>
                <div class="col note-item" data-type="<?= $note['type'] ?>" data-date="<?= $note['created_at'] ?>" data-title="<?= htmlspecialchars($note['title']) ?>" data-note-id="<?= $note['id'] ?>">
                    <div class="card h-100 note-card border-0 shadow-sm">
                        <!-- Color Bar by Type -->
                        <div class="card-color-bar 
                            <?= $note['type'] === 'project' ? 'bg-primary' : 
                               ($note['type'] === 'task' ? 'bg-success' : 'bg-info') ?>">
                        </div>
                        
                        <!-- Note Type Badge -->
                        <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0 pb-1 pt-3">
                            <span class="badge rounded-pill 
                                <?= $note['type'] === 'project' ? 'text-bg-primary' : 
                                   ($note['type'] === 'task' ? 'text-bg-success' : 'text-bg-info') ?>">
                                <i class="bi <?= $note['type'] === 'project' ? 'bi-kanban' : 
                                             ($note['type'] === 'task' ? 'bi-check2-square' : 'bi-journal-text') ?>"></i>
                                <?= ucfirst($note['type']) ?>
                            </span>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-icon dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="/notes/edit/<?= $note['id'] ?>">
                                            <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Note
                                        </a>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item text-danger" 
                                                data-bs-toggle="modal" data-bs-target="#deleteModal<?= $note['id'] ?>" data-note-id="<?= $note['id'] ?>">
                                            <i class="bi bi-trash me-2"></i>Delete Note
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="card-body pt-2">
                            <h5 class="card-title"><?= htmlspecialchars($note['title']) ?></h5>
                            <div class="note-content note-preview" data-id="<?= $note['id'] ?>">
                                <div class="note-text">
                                    <?= nl2br(htmlspecialchars(substr($note['content'], 0, 150))) ?>
                                    <?php if (strlen($note['content']) > 150): ?>
                                        <span class="text-muted">...</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-white border-top-0">
                            <div class="d-flex justify-content-between align-items-center small text-muted">
                                <div>
                                    <i class="bi bi-calendar3"></i> <?= date('M j, Y', strtotime($note['created_at'])) ?>
                                </div>
                                <button class="btn btn-sm btn-link p-0" data-bs-toggle="modal" data-bs-target="#noteModal<?= $note['id'] ?>">
                                    Read More
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Delete Confirmation Modal -->
                <div class="modal fade" id="deleteModal<?= $note['id'] ?>" tabindex="-1" aria-hidden="true" data-note-id="<?= $note['id'] ?>">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Delete Note
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete the note "<strong><?= htmlspecialchars($note['title']) ?></strong>"?</p>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Warning:</strong> This action cannot be undone. The note will be permanently deleted.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-lg me-1"></i> Cancel
                                </button>
                                <form action="/notes/delete/<?= $note['id'] ?>" method="POST" class="d-inline">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-trash me-1"></i> Delete Note
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Note Detail Modal -->
                <div class="modal fade" id="noteModal<?= $note['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><?= htmlspecialchars($note['title']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3 note-full-content">
                                    <?= nl2br(htmlspecialchars($note['content'])) ?>
                                </div>
                                <div class="d-flex justify-content-between text-muted small">
                                    <div>
                                        <span class="badge rounded-pill 
                                            <?= $note['type'] === 'project' ? 'text-bg-primary' : 
                                                ($note['type'] === 'task' ? 'text-bg-success' : 'text-bg-info') ?>">
                                            <?= ucfirst($note['type']) ?>
                                        </span>
                                        <?php if (!empty($note['reference_title'])): ?>
                                        <a href="<?= $note['type'] === 'project' ? '/projects/show/' . $note['reference_id'] : '/tasks/show/' . $note['reference_id'] ?>" class="ms-2">
                                            <?= htmlspecialchars($note['reference_title'] ?? 'Unknown') ?>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <i class="bi bi-calendar3"></i> <?= date('M j, Y', strtotime($note['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <a href="/notes/edit/<?= $note['id'] ?>" class="btn btn-primary">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Notes List View (Hidden by Default) -->
        <div id="listView" class="d-none">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
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
                                        <a href="#" class="fw-semibold text-decoration-none" data-bs-toggle="modal" data-bs-target="#noteModal<?= $note['id'] ?>">
                                            <?= htmlspecialchars($note['title']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill 
                                            <?= $note['type'] === 'project' ? 'text-bg-primary' : 
                                               ($note['type'] === 'task' ? 'text-bg-success' : 'text-bg-info') ?>">
                                            <?= ucfirst($note['type']) ?>
                                        </span>
                                    </td>
                                    <td class="text-truncate" style="max-width: 300px;"><?= htmlspecialchars(substr($note['content'], 0, 100)) ?><?= strlen($note['content']) > 100 ? '...' : '' ?></td>
                                    <td><?= date('M j, Y', strtotime($note['created_at'])) ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-falcon-default" data-bs-toggle="modal" data-bs-target="#noteModal<?= $note['id'] ?>" title="View">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="/notes/edit/<?= $note['id'] ?>" class="btn btn-falcon-default" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-falcon-default text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $note['id'] ?>" title="Delete">
                                                <i class="bi bi-trash"></i>
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
        <!-- Empty State -->
        <div class="text-center py-5 my-5">
            <div class="empty-state mb-4">
                <i class="bi bi-journal-text text-muted"></i>
            </div>
            <h3>No notes yet</h3>
            <p class="text-muted mb-4 lead">Create your first note to keep track of important information</p>
            <a href="/notes/add" class="btn btn-primary btn-lg">
                <i class="bi bi-plus-lg me-1"></i> Create your first note
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
.card-color-bar {
    height: 5px;
    width: 100%;
    border-top-left-radius: 0.375rem;
    border-top-right-radius: 0.375rem;
}
.note-card {
    border-radius: 0.5rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    overflow: hidden;
}
.note-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
.note-preview {
    max-height: 100px;
    overflow: hidden;
    position: relative;
}
.empty-state {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background-color: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}
.empty-state i {
    font-size: 3rem;
}
.btn-icon {
    background: transparent;
    border: none;
    color: #6c757d;
    padding: 0.25rem 0.5rem;
}
.btn-icon:hover {
    color: #343a40;
    background-color: rgba(0,0,0,0.05);
    border-radius: 4px;
}
.note-full-content {
    white-space: pre-line;
}
.btn-falcon-default {
    background-color: #fff;
    border-color: #e3e6ed;
}
.btn-falcon-default:hover {
    background-color: #f9fafd;
}


</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
    const searchInput = document.getElementById('noteSearch');
    if (searchInput) {
        searchInput.addEventListener('input', filterNotes);
    }
    
    // Make sure delete buttons work in both views
    const allDeleteButtons = document.querySelectorAll('.dropdown-item.text-danger, .btn-falcon-default.text-danger');
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
                submitBtn.innerHTML = '<i class="bi bi-trash me-1"></i> Delete Note';
                
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
        const container = document.querySelector('.container-fluid');
        container.insertBefore(alertEl, container.querySelector('.bg-light.rounded-3').nextSibling);
        
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
        const searchTerm = searchInput.value.toLowerCase();
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
                const container = document.getElementById('gridView').parentNode;
                const message = document.createElement('div');
                message.id = 'noResultsMessage';
                message.className = 'alert alert-info text-center mt-4';
                message.innerHTML = `
                    <i class="bi bi-search me-2"></i>
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
});
</script>

<?php include_once VIEWSPATH . '/inc/footer.php'; ?> 