<?php include_once VIEWSPATH . '/inc/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Header with actions -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3"><?= $data['title'] ?></h1>
                <a href="/notes/add" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Note
                </a>
            </div>
            
            <!-- Flash messages -->
            <?php flash('note_success'); ?>
            <?php flash('note_error'); ?>
            
            <!-- Search and Filter Options -->
            <?php if (!empty($data['notes'])): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" id="noteSearch" class="form-control border-start-0" placeholder="Search notes...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select id="noteTypeFilter" class="form-select">
                                    <option value="all">All Types</option>
                                    <option value="project">Projects</option>
                                    <option value="task">Tasks</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="noteSortOrder" class="form-select">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="title">Title (A-Z)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Notes Display -->
            <div id="notesContainer">
                <?php if (empty($data['notes'])): ?>
                    <div class="text-center py-5 my-5">
                        <div class="mb-4">
                            <i class="bi bi-journal-text text-muted" style="font-size: 5rem;"></i>
                        </div>
                        <h4 class="text-muted">No notes yet</h4>
                        <p class="lead text-muted">Create your first note to keep track of important information</p>
                        <a href="/notes/add" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-lg"></i> Create your first note
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php foreach ($data['notes'] as $note): ?>
                            <div class="col note-item" data-type="<?= $note['type'] ?>">
                                <div class="card h-100 note-card shadow-sm">
                                    <!-- Note Type Badge -->
                                    <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0 pb-0">
                                        <span class="badge rounded-pill <?= $note['type'] === 'project' ? 'bg-primary' : 'bg-success' ?>">
                                            <i class="bi <?= $note['type'] === 'project' ? 'bi-kanban' : 'bi-check2-square' ?>"></i>
                                            <?= ucfirst($note['type']) ?>
                                        </span>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="/notes/edit/<?= $note['id'] ?>"><i class="bi bi-pencil-square me-2"></i>Edit</a></li>
                                                <li>
                                                    <form action="/notes/delete/<?= $note['id'] ?>" method="POST" class="d-inline">
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this note?')">
                                                            <i class="bi bi-trash me-2"></i>Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        <h5 class="card-title mb-3"><?= htmlspecialchars($note['title']) ?></h5>
                                        <div class="note-content" style="max-height: 100px; overflow: hidden; position: relative;">
                                            <div class="note-text">
                                                <?= nl2br(htmlspecialchars($note['content'])) ?>
                                            </div>
                                            <?php if (strlen($note['content']) > 200): ?>
                                                <div class="content-fade"></div>
                                                <button class="btn btn-sm btn-link p-0 read-more" data-bs-toggle="modal" data-bs-target="#noteModal<?= $note['id'] ?>">Read more</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer text-muted bg-white">
                                        <div class="small d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-link-45deg"></i> 
                                                <a href="<?= $note['type'] === 'project' ? '/projects/show/' . $note['reference_id'] : '/tasks/show/' . $note['reference_id'] ?>">
                                                    <?= htmlspecialchars($note['reference_title'] ?? 'Unknown') ?>
                                                </a>
                                            </div>
                                            <div>
                                                <i class="bi bi-calendar3"></i> <?= date('M j, Y', strtotime($note['created_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Note Detail Modal -->
                            <div class="modal fade" id="noteModal<?= $note['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><?= htmlspecialchars($note['title']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <?= nl2br(htmlspecialchars($note['content'])) ?>
                                            </div>
                                            <div class="d-flex justify-content-between text-muted small">
                                                <div>
                                                    <i class="bi bi-person"></i> <?= htmlspecialchars($note['created_by_name']) ?>
                                                </div>
                                                <div>
                                                    <i class="bi bi-calendar3"></i> <?= date('M j, Y', strtotime($note['created_at'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <a href="/notes/edit/<?= $note['id'] ?>" class="btn btn-primary">Edit</a>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.content-fade {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 40px;
    background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,1));
}
.note-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 0.5rem;
}
.note-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
.note-type {
    position: absolute;
    top: 10px;
    right: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('noteSearch');
    if (searchInput) {
        searchInput.addEventListener('input', filterNotes);
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
    
    // Helper function to filter notes
    function filterNotes() {
        const searchTerm = searchInput.value.toLowerCase();
        const typeFilter = document.getElementById('noteTypeFilter').value;
        const noteItems = document.querySelectorAll('.note-item');
        
        noteItems.forEach(item => {
            const noteText = item.querySelector('.note-text').textContent.toLowerCase();
            const noteTitle = item.querySelector('.card-title').textContent.toLowerCase();
            const noteType = item.getAttribute('data-type');
            
            const matchesSearch = noteTitle.includes(searchTerm) || noteText.includes(searchTerm);
            const matchesType = typeFilter === 'all' || noteType === typeFilter;
            
            item.style.display = (matchesSearch && matchesType) ? '' : 'none';
        });
    }
    
    // Helper function to sort notes
    function sortNotes() {
        const container = document.querySelector('.row-cols-1');
        const items = Array.from(document.querySelectorAll('.note-item'));
        const sortValue = document.getElementById('noteSortOrder').value;
        
        items.sort((a, b) => {
            if (sortValue === 'title') {
                const titleA = a.querySelector('.card-title').textContent.toLowerCase();
                const titleB = b.querySelector('.card-title').textContent.toLowerCase();
                return titleA.localeCompare(titleB);
            } else if (sortValue === 'oldest') {
                const dateA = new Date(a.querySelector('.bi-calendar3').nextSibling.textContent.trim());
                const dateB = new Date(b.querySelector('.bi-calendar3').nextSibling.textContent.trim());
                return dateA - dateB;
            } else { // newest first is default
                const dateA = new Date(a.querySelector('.bi-calendar3').nextSibling.textContent.trim());
                const dateB = new Date(b.querySelector('.bi-calendar3').nextSibling.textContent.trim());
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
});
</script>

<?php include_once VIEWSPATH . '/inc/footer.php'; ?> 