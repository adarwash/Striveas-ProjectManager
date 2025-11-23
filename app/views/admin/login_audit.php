<?php include_once VIEWSPATH . '/inc/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center">
	<div>
		<h1 class="page-title"><i class="fas fa-user-shield me-2"></i>Login Audit</h1>
		<p class="mb-0 text-muted">Recent authentication activity across the system</p>
	</div>
	<div>
		<a href="/admin/logins" class="btn btn-outline-secondary me-2">
			<i class="fas fa-sync-alt me-1"></i>Refresh
		</a>
		<a href="/admin" class="btn btn-primary">
			<i class="fas fa-cog me-1"></i>Admin
		</a>
	</div>
</div>

<div class="card">
	<div class="card-body">
		<div class="d-flex justify-content-between align-items-center mb-3">
			<div>
				<strong><?= isset($entries) ? count($entries) : 0 ?></strong> entries
				<span class="text-muted">showing latest <?= (int)($limit ?? 200) ?></span>
			</div>
			<form class="d-flex align-items-center" method="get" action="/admin/logins">
				<label class="me-2">Limit</label>
				<input type="number" min="1" max="1000" name="limit" class="form-control form-control-sm me-2" value="<?= (int)($limit ?? 200) ?>" style="width:100px;">
				<button class="btn btn-sm btn-outline-primary" type="submit"><i class="fas fa-filter me-1"></i>Apply</button>
			</form>
		</div>
		
		<div class="table-responsive">
			<table class="table table-striped align-middle">
				<thead>
					<tr>
						<th>When</th>
						<th>User</th>
						<th>Email</th>
						<th>Username</th>
						<th>IP</th>
						<th>User Agent</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php if (empty($entries)): ?>
						<tr><td colspan="7" class="text-center text-muted py-4">No login activity recorded yet.</td></tr>
					<?php else: ?>
						<?php foreach ($entries as $e): ?>
							<tr>
								<td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($e['created_at']))) ?></td>
								<td>
									<?php if (!empty($e['full_name'])): ?>
										<?= htmlspecialchars($e['full_name']) ?>
									<?php elseif (!empty($e['db_username'])): ?>
										<?= htmlspecialchars($e['db_username']) ?>
									<?php else: ?>
										<em class="text-muted">Unknown</em>
									<?php endif; ?>
									<?php if (!empty($e['user_id'])): ?>
										<div class="small text-muted">ID: <?= (int)$e['user_id'] ?></div>
									<?php endif; ?>
								</td>
								<td><?= !empty($e['email']) ? htmlspecialchars($e['email']) : '<span class="text-muted">—</span>' ?></td>
								<td><?= !empty($e['username']) ? htmlspecialchars($e['username']) : '<span class="text-muted">—</span>' ?></td>
								<td><?= !empty($e['ip_address']) ? htmlspecialchars($e['ip_address']) : '<span class="text-muted">—</span>' ?></td>
								<td>
									<?php 
									$ua = (string)($e['user_agent'] ?? '');
									$uaShort = mb_strlen($ua) > 80 ? mb_substr($ua, 0, 80) . '…' : $ua;
									?>
									<span title="<?= htmlspecialchars($ua) ?>"><?= htmlspecialchars($uaShort) ?></span>
								</td>
								<td>
									<?php if ((int)$e['success'] === 1): ?>
										<span class="badge bg-success"><i class="fas fa-check me-1"></i>Success</span>
									<?php else: ?>
										<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Failed</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php include_once VIEWSPATH . '/inc/footer.php'; ?>


