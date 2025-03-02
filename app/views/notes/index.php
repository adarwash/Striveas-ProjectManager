<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0">My Notes</h1>
                <a href="/notes/add" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Note
                </a>
            </div>
        </div>
    </div>

    <?php flash('note_success'); ?>
    <?php flash('note_error'); ?>

    <?php if (empty($notes)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> You haven't created any notes yet.
            <a href="/notes/add">Create your first note</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($notes as $note): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?= htmlspecialchars($note['title']) ?></h5>
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
                        <div class="card-body">
                            <div class="mb-3">
                                <?= nl2br(htmlspecialchars($note['content'])) ?>
                            </div>
                            <div class="text-muted small">
                                <p class="mb-1">
                                    <i class="bi bi-link-45deg"></i> 
                                    <?= ucfirst($note['type']) ?>: 
                                    <a href="/<?= $note['type'] ?>s/view/<?= $note['reference_id'] ?>">
                                        <?= htmlspecialchars($note['reference_title']) ?>
                                    </a>
                                </p>
                                <p class="mb-0">
                                    <i class="bi bi-person"></i> Created by <?= htmlspecialchars($note['created_by_name']) ?>
                                    <br>
                                    <i class="bi bi-clock"></i> <?= date('M j, Y g:i A', strtotime($note['created_at'])) ?>
                                    <?php if ($note['updated_at'] !== $note['created_at']): ?>
                                        <br>
                                        <i class="bi bi-pencil"></i> Updated <?= date('M j, Y g:i A', strtotime($note['updated_at'])) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div> 