<div class="container-fluid">
	<!-- Page Header -->
	<div class="rounded-3 p-4 mb-4 client-header-solid">
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb mb-2">
				<li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
				<li class="breadcrumb-item"><a href="/tasks" class="text-decoration-none">Tasks</a></li>
				<li class="breadcrumb-item"><a href="/tasks/show/<?= (int)$task->id ?>" class="text-decoration-none"><?= htmlspecialchars($task->title) ?></a></li>
				<li class="breadcrumb-item active" aria-current="page">Follow-ups History</li>
			</ol>
		</nav>
		<div class="d-flex justify-content-between align-items-center">
			<div>
				<h1 class="h3 mb-1 text-dark">Follow-ups History</h1>
				<p class="text-muted mb-0">Completed and missed reminders for this task</p>
			</div>
			<div class="d-flex gap-2">
				<a href="/tasks/show/<?= (int)$task->id ?>" class="btn btn-outline-secondary">
					<i class="bi bi-arrow-left"></i> Back to Task
				</a>
			</div>
		</div>
	</div>

	<?php flash('task_success'); ?>
	<?php flash('task_error'); ?>

	<div class="card border-0 shadow-sm">
		<div class="card-header bg-white py-3">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
				<h5 class="card-title mb-0">
					<i class="bi bi-bell text-primary me-2"></i>
					Follow-ups
				</h5>
				<ul class="nav nav-pills">
					<li class="nav-item">
						<a class="nav-link <?= ($active_filter === 'history' ? 'active' : 'bg-light border text-dark') ?>" href="?status=history">
							History <span class="badge bg-light text-dark border ms-1"><?= (int)(($counts['completed'] ?? 0) + ($counts['missed'] ?? 0)) ?></span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link <?= ($active_filter === 'completed' ? 'active' : 'bg-light border text-dark') ?>" href="?status=completed">
							Completed <span class="badge bg-light text-dark border ms-1"><?= (int)($counts['completed'] ?? 0) ?></span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link <?= ($active_filter === 'missed' ? 'active' : 'bg-light border text-dark') ?>" href="?status=missed">
							Missed <span class="badge bg-light text-dark border ms-1"><?= (int)($counts['missed'] ?? 0) ?></span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link <?= ($active_filter === 'pending' ? 'active' : 'bg-light border text-dark') ?>" href="?status=pending">
							Pending <span class="badge bg-light text-dark border ms-1"><?= (int)($counts['pending'] ?? 0) ?></span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link <?= ($active_filter === 'all' ? 'active' : 'bg-light border text-dark') ?>" href="?status=all">
							All <span class="badge bg-light text-dark border ms-1"><?= (int)($counts['all'] ?? 0) ?></span>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<div class="card-body">
			<?php if (!empty($callbacks)): ?>
			<div class="table-responsive">
				<table class="table table-hover align-middle">
					<thead class="table-light">
						<tr>
							<th>Title</th>
							<th>Reminder</th>
							<th>Status</th>
							<th>Completed</th>
							<th>Notes</th>
							<th class="text-end">Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($callbacks as $cb): ?>
						<?php
							$status = $cb['status'] ?? 'Pending';
							$isMissed = ($status === 'Pending' && strtotime($cb['remind_at'] ?? '') < time());
							if ($status === 'Completed') {
								$badge = 'success';
								$icon = 'bi-check2-circle';
							} elseif ($isMissed) {
								$badge = 'warning';
								$icon = 'bi-exclamation-circle';
							} else {
								$badge = 'secondary';
								$icon = 'bi-clock';
							}
						?>
						<tr>
							<td class="fw-semibold"><?= htmlspecialchars($cb['title']) ?></td>
							<td>
								<div class="small text-muted"><?= date('M j, Y g:i A', strtotime($cb['remind_at'])) ?></div>
							</td>
							<td>
								<span class="badge text-bg-<?= $badge ?>">
									<i class="bi <?= $icon ?> me-1"></i>
									<?= $isMissed ? 'Missed' : htmlspecialchars($status) ?>
								</span>
							</td>
							<td>
								<?php if (!empty($cb['completed_at'])): ?>
									<div class="small text-muted"><?= date('M j, Y g:i A', strtotime($cb['completed_at'])) ?></div>
								<?php else: ?>
									<span class="text-muted">—</span>
								<?php endif; ?>
							</td>
							<td>
								<?php if (!empty($cb['notes'])): ?>
									<div class="small text-truncate" style="max-width: 320px;" title="<?= htmlspecialchars($cb['notes']) ?>">
										<?= htmlspecialchars($cb['notes']) ?>
									</div>
								<?php else: ?>
									<span class="text-muted">—</span>
								<?php endif; ?>
							</td>
							<td class="text-end">
								<?php if (hasPermission('tasks.update')): ?>
								<?php if ($status !== 'Completed'): ?>
								<a href="/tasks/completeCallback/<?= (int)$cb['id'] ?>" class="btn btn-sm btn-outline-success" title="Mark Completed">
									<i class="bi bi-check2-circle"></i>
								</a>
								<?php endif; ?>
								<form action="/tasks/deleteCallback/<?= (int)$cb['id'] ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this follow-up?');">
									<button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
										<i class="bi bi-trash"></i>
									</button>
								</form>
								<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php else: ?>
			<div class="text-center py-4">
				<i class="bi bi-bell-slash text-muted" style="font-size: 2rem;"></i>
				<h6 class="text-muted mt-2 mb-0">No follow-ups found</h6>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>


