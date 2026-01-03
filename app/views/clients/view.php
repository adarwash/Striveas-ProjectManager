<div class="container-fluid">
    <!-- Page Header -->
    <div class="rounded-3 p-4 mb-4 client-header-solid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/clients" class="text-decoration-none">Clients</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($client['name']) ?></li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
				<h1 class="h3 mb-1 text-dark">
					<?= htmlspecialchars($client['name']) ?>
					<?php
					$hasMeetingToday = false;
					if (!empty($meetings)) {
						$todayStart = strtotime(date('Y-m-d 00:00:00'));
						$todayEnd = strtotime(date('Y-m-d 23:59:59'));
						foreach ($meetings as $m) {
							$mt = isset($m['meeting_at']) ? strtotime($m['meeting_at']) : null;
							if ($mt && $mt >= $todayStart && $mt <= $todayEnd) { $hasMeetingToday = true; break; }
						}
					}
					?>
					<?php if ($hasMeetingToday): ?>
						<span class="badge bg-warning text-dark ms-2">
							<i class="bi bi-calendar-event me-1"></i>Meeting Today
						</span>
					<?php endif; ?>
				</h1>
                <p class="text-muted mb-0">Client details and site assignments</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <?php
                $levelEnabled = !empty($level_io_enabled);
                $hasLevelGroups = !empty($level_io_groups);
                ?>
                <?php if ($levelEnabled && $hasLevelGroups): ?>
                <a href="/clients/levelDevices/<?= (int)$client['id'] ?>" class="btn btn-info text-white d-inline-flex align-items-center gap-2">
                    <i class="bi bi-pc-display-horizontal"></i>
                    <span>Level.io Devices</span>
                </a>
                <?php endif; ?>
                <?php if (hasPermission('clients.update')): ?>
                <form action="/clients/addQuickCallback/<?= (int)$client['id'] ?>" method="post" class="d-inline m-0">
                    <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2">
                        <i class="bi bi-bell-fill"></i>
                        <span>Quick Follow-up</span>
                    </button>
                </form>
                <a href="/networkaudits/create?client_id=<?= (int)$client['id'] ?>" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-diagram-3"></i>
                    <span>New Discovery Form</span>
                </a>
                <?php endif; ?>
                <?php if (hasPermission('clients.assign_sites')): ?>
                <a href="/clients/assignSites/<?= $client['id'] ?>" class="btn btn-info text-white d-inline-flex align-items-center gap-2">
                    <i class="bi bi-geo-alt"></i>
                    <span>Manage Sites</span>
                </a>
                <?php endif; ?>
                <?php if (hasPermission('clients.update')): ?>
                <a href="/clients/edit/<?= $client['id'] ?>" class="btn btn-warning d-inline-flex align-items-center gap-2">
                    <i class="bi bi-pencil"></i>
                    <span>Edit Client</span>
                </a>
                <?php endif; ?>
                <a href="/clients" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Clients</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Flash Messages -->
    <?php flash('client_success'); ?>
    <?php flash('client_error'); ?>
    
    <div class="row">
        <!-- Client Information -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-circle text-primary me-2"></i>
                        Client Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Client Name:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($client['name']) ?></dd>
                                
                                <dt class="col-sm-4">Contact Person:</dt>
                                <dd class="col-sm-8">
                                    <?= !empty($client['contact_person']) ? htmlspecialchars($client['contact_person']) : '<span class="text-muted">—</span>' ?>
                                </dd>
                                
                                <dt class="col-sm-4">Email:</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($client['email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($client['email']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($client['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </dd>
                                
                                <dt class="col-sm-4">Phone:</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($client['phone'])): ?>
                                        <a href="tel:<?= htmlspecialchars($client['phone']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($client['phone']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Industry:</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($client['industry'])): ?>
                                        <?php 
                                            $industryClass = '';
                                            $industryIcon = '';
                                            switch(strtolower($client['industry'])) {
                                                case 'technology':
                                                    $industryClass = 'text-bg-primary';
                                                    $industryIcon = 'bi-cpu';
                                                    break;
                                                case 'manufacturing':
                                                    $industryClass = 'text-bg-secondary';
                                                    $industryIcon = 'bi-gear';
                                                    break;
                                                case 'healthcare':
                                                    $industryClass = 'text-bg-success';
                                                    $industryIcon = 'bi-heart-pulse';
                                                    break;
                                                case 'finance':
                                                    $industryClass = 'text-bg-warning';
                                                    $industryIcon = 'bi-bank';
                                                    break;
                                                case 'retail':
                                                    $industryClass = 'text-bg-info';
                                                    $industryIcon = 'bi-shop';
                                                    break;
                                                default:
                                                    $industryClass = 'text-bg-light text-dark';
                                                    $industryIcon = 'bi-building';
                                            }
                                        ?>
                                        <span class="badge <?= $industryClass ?> rounded-pill">
                                            <i class="bi <?= $industryIcon ?> me-1"></i>
                                            <?= htmlspecialchars($client['industry']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </dd>
                                
                                <dt class="col-sm-4">Status:</dt>
                                <dd class="col-sm-8">
                                    <?php 
                                        if ($client['status'] == 'Active') {
                                            $statusClass = 'text-bg-success';
                                            $statusIcon = 'bi-check-circle';
                                        } elseif ($client['status'] == 'Inactive') {
                                            $statusClass = 'text-bg-danger';
                                            $statusIcon = 'bi-x-circle';
                                        } elseif ($client['status'] == 'Prospect') {
                                            $statusClass = 'text-bg-warning';
                                            $statusIcon = 'bi-clock';
                                        } else {
                                            $statusClass = 'text-bg-secondary';
                                            $statusIcon = 'bi-dash-circle';
                                        }
                                    ?>
                                    <span class="badge <?= $statusClass ?> rounded-pill">
                                        <i class="bi <?= $statusIcon ?> me-1"></i>
                                        <?= $client['status'] ?>
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-4">Created:</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($client['created_at'])): ?>
                                        <?= date('M j, Y', strtotime($client['created_at'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </dd>
                                
                                <dt class="col-sm-4">Profile Age:</dt>
                                <dd class="col-sm-8">
                                    <?= htmlspecialchars($client_age ?? '—') ?>
                                </dd>
                                
                                <?php if (!empty($client['converted_to_active_date'])): ?>
                                <dt class="col-sm-4">Converted to Active:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge text-bg-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        <?= date('M j, Y', strtotime($client['converted_to_active_date'])) ?>
                                    </span>
                                </dd>
                                <?php endif; ?>
                                
                                <dt class="col-sm-4">Last Updated:</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($client['updated_at'])): ?>
                                        <?= date('M j, Y', strtotime($client['updated_at'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    <?php if (!empty($client['address'])): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <dt>Address:</dt>
                            <dd class="mt-1"><?= nl2br(htmlspecialchars($client['address'])) ?></dd>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($client['notes'])): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <dt>Notes:</dt>
                            <dd class="mt-1"><?= nl2br(htmlspecialchars($client['notes'])) ?></dd>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Client Notes -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-journal-text text-primary me-2"></i>
                            Notes
                        </h5>
                        <div class="d-flex gap-2">
                            <a href="/notes?type=client&reference_id=<?= (int)$client['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-collection"></i> View All Notes
                            </a>
                            <a href="/notes/add?type=client&reference_id=<?= (int)$client['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-plus-lg"></i> Add Note
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($client_notes)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Created By</th>
                                    <th>Created</th>
                                    <th>Updated</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($client_notes as $n): ?>
                                <tr>
                                    <td>
                                        <a href="/notes/show/<?= (int)$n['id'] ?>" class="fw-semibold text-decoration-none">
                                            <?= htmlspecialchars($n['title']) ?>
                                        </a>
                                        <?php if (!empty($n['content'])): ?>
                                        <div class="small text-muted text-truncate" style="max-width: 520px;">
                                            <?= htmlspecialchars(mb_strimwidth($n['content'], 0, 120, '…')) ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($n['created_by_name'] ?? '—') ?></td>
                                    <td><?= !empty($n['created_at']) ? date('M j, Y H:i', strtotime($n['created_at'])) : '—' ?></td>
                                    <td><?= !empty($n['updated_at']) ? date('M j, Y H:i', strtotime($n['updated_at'])) : '—' ?></td>
                                    <td class="text-end">
                                        <a href="/notes/show/<?= (int)$n['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (!empty($n['created_by']) && isset($_SESSION['user_id']) && (int)$n['created_by'] === (int)$_SESSION['user_id']): ?>
                                        <a href="/notes/edit/<?= (int)$n['id'] ?>" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-journal-text text-muted" style="font-size: 2rem;"></i>
                        <h6 class="text-muted mt-2">No Notes</h6>
                        <p class="text-muted mb-3">Create client-specific notes to keep context and history together.</p>
                        <a href="/notes/add?type=client&reference_id=<?= (int)$client['id'] ?>" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Add Note
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Meetings -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-people text-primary me-2"></i>
                            Meetings
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    $nowTs = time();
                    $todayStart = strtotime(date('Y-m-d 00:00:00'));
                    $todayEnd = strtotime(date('Y-m-d 23:59:59'));
                    $past = [];
                    $today = [];
                    $upcoming = [];
                    foreach (($meetings ?? []) as $m) {
                        $mt = isset($m['meeting_at']) ? strtotime($m['meeting_at']) : null;
                        if (!$mt) { continue; }
                        if ($mt < $todayStart) {
                            $past[] = $m;
                        } elseif ($mt >= $todayStart && $mt <= $todayEnd) {
                            $today[] = $m;
                        } else {
                            $upcoming[] = $m;
                        }
                    }
                    $fmt = function($m) {
                        $when = !empty($m['meeting_at']) ? date('M j, Y g:i A', strtotime($m['meeting_at'])) : '—';
                        $site = !empty($m['site_name']) ? $m['site_name'] . (!empty($m['site_location']) ? ' - ' . $m['site_location'] : '') : '—';
                        $parts = [];
                        if (!empty($m['person_going'])) { $parts[] = 'Going: ' . $m['person_going']; }
                        if (!empty($m['person_visiting'])) { $parts[] = 'Visiting: ' . $m['person_visiting']; }
                        if (!empty($m['additional_going'])) { $parts[] = 'Also going: ' . $m['additional_going']; }
                        if (!empty($m['additional_meeting'])) { $parts[] = 'Also meeting: ' . $m['additional_meeting']; }
                        $who = !empty($parts) ? implode(' • ', $parts) : '—';
                        return [$when, $site, $who];
                    };
                    ?>
                    <div class="row">
                        <div class="col-12 mb-4">
                            <h6 class="text-muted mb-2"><i class="bi bi-calendar-check me-1"></i>Today</h6>
                            <?php if (!empty($today)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($today as $m): [$when,$site,$who] = $fmt($m); ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($m['title'] ?? '') ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($when) ?> • <?= htmlspecialchars($site) ?></div>
                                        </div>
                                        <div class="small"><?= htmlspecialchars($who) ?></div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php else: ?>
                            <div class="text-muted small">No meetings today.</div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12">
                            <h6 class="text-muted mb-2"><i class="bi bi-calendar-event me-1"></i>Upcoming</h6>
                            <?php if (!empty($upcoming)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>When</th>
                                            <th>Title</th>
                                            <th>People</th>
                                            <th>Site</th>
                                            <th>Info</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($upcoming as $m): [$when,$site,$who] = $fmt($m); ?>
                                        <tr>
                                            <td><?= htmlspecialchars($when) ?></td>
                                            <td><?= htmlspecialchars($m['title'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($who) ?></td>
                                            <td><?= htmlspecialchars($site) ?></td>
                                            <td class="text-truncate" style="max-width:300px;">
                                                <?= htmlspecialchars(mb_strimwidth($m['info'] ?? '', 0, 140, '…')) ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-muted small">No upcoming meetings scheduled.</div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 mt-4">
                            <h6 class="text-muted mb-2"><i class="bi bi-clock-history me-1"></i>Past</h6>
                            <?php if (!empty($past)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>When</th>
                                            <th>Title</th>
                                            <th>People</th>
                                            <th>Site</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($past as $m): [$when,$site,$who] = $fmt($m); ?>
                                        <tr>
                                            <td><?= htmlspecialchars($when) ?></td>
                                            <td><?= htmlspecialchars($m['title'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($who) ?></td>
                                            <td><?= htmlspecialchars($site) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="text-muted small">No past meetings.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (hasPermission('clients.update')): ?>
                    <hr class="my-4">
                    <h6 class="mb-3">Create Meeting</h6>
                    <form action="/clients/addMeeting/<?= (int)$client['id'] ?>" method="post">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" placeholder="e.g., Quarterly Review" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Person Going</label>
                                <input type="text" name="person_going" class="form-control" placeholder="Internal attendee">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Person Visiting</label>
                                <input type="text" name="person_visiting" class="form-control" placeholder="Client attendee">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Site</label>
                                <select name="site_id" class="form-select">
                                    <option value="">—</option>
									<?php $siteOptions = !empty($sites) ? $sites : ($all_sites ?? []); ?>
									<?php foreach ($siteOptions as $s): ?>
                                        <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name']) ?><?= !empty($s['location']) ? ' - ' . htmlspecialchars($s['location']) : '' ?></option>
                                    <?php endforeach; ?>
									<?php if (empty($siteOptions)): ?>
										<option value="" disabled>No sites available</option>
									<?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">When</label>
                                <input type="datetime-local" name="meeting_at" class="form-control" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Info</label>
                                <textarea name="info" class="form-control" rows="2" placeholder="Agenda, notes, or details"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Additional People Going</label>
                                <textarea name="additional_going" class="form-control" rows="2" placeholder="Comma-separated names of internal attendees"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Additional People Meeting</label>
                                <textarea name="additional_meeting" class="form-control" rows="2" placeholder="Comma-separated names of client attendees"></textarea>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>Create Meeting
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Assigned Sites -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-geo-alt text-primary me-2"></i>
                            Assigned Sites
                        </h5>
                        <?php if (hasPermission('clients.assign_sites')): ?>
                        <a href="/clients/assignSites/<?= $client['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-lg"></i> Manage Sites
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($sites)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Site Name</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Relationship</th>
                                    <th>Services</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sites as $site): ?>
                                <tr>
                                    <td>
                                        <a href="/sites/viewSite/<?= $site['id'] ?>" class="fw-semibold text-decoration-none">
                                            <?= htmlspecialchars($site['name']) ?>
                                        </a>
                                        <?php if (!empty($site['site_code'])): ?>
                                        <div class="small text-muted">Code: <?= htmlspecialchars($site['site_code']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($site['location'] ?? '—') ?></td>
                                    <td>
                                        <span class="badge text-bg-secondary rounded-pill">
                                            <?= htmlspecialchars($site['type'] ?? 'Unknown') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-info rounded-pill">
                                            <?= htmlspecialchars($site['relationship_type'] ?? 'Standard') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($site['services'])): ?>
                                            <div class="d-flex flex-column small" style="max-width: 220px;">
                                                <?php 
                                                $maxShow = 3;
                                                $count = 0;
                                                foreach ($site['services'] as $svc):
                                                    if ($count++ >= $maxShow) break;
                                                ?>
                                                    <div class="text-truncate" title="<?= htmlspecialchars($svc['service_name']) ?>">
                                                        <i class="bi bi-tools text-muted me-1"></i><?= htmlspecialchars($svc['service_name']) ?>
                                                        <?php if (!empty($svc['service_type'])): ?>
                                                            <span class="text-muted">(<?= htmlspecialchars($svc['service_type']) ?>)</span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php if (count($site['services']) > $maxShow): ?>
                                                    <div class="text-muted">+ <?= count($site['services']) - $maxShow ?> more</div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $status = $site['status'] ?? 'Unknown';
                                            if ($status == 'Active') {
                                                $statusClass = 'text-bg-success';
                                                $statusIcon = 'bi-check-circle';
                                            } elseif ($status == 'Inactive') {
                                                $statusClass = 'text-bg-danger';
                                                $statusIcon = 'bi-x-circle';
                                            } else {
                                                $statusClass = 'text-bg-secondary';
                                                $statusIcon = 'bi-dash-circle';
                                            }
                                        ?>
                                        <span class="badge <?= $statusClass ?> rounded-pill">
                                            <i class="bi <?= $statusIcon ?> me-1"></i>
                                            <?= $status ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="/sitevisits/create/<?= $site['id'] ?>" class="btn btn-sm btn-primary" title="Log Visit">
                                                <i class="bi bi-journal-plus"></i>
                                            </a>
                                            <a href="/sites/viewSite/<?= $site['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Site">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="bi bi-geo-alt text-muted" style="font-size: 2rem;"></i>
                        </div>
                        <h6 class="text-muted">No Sites Assigned</h6>
                        <p class="text-muted mb-3">This client doesn't have any sites assigned yet.</p>
                        <?php if (hasPermission('clients.assign_sites')): ?>
                        <a href="/clients/assignSites/<?= $client['id'] ?>" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Assign Sites
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Projects -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-kanban text-primary me-2"></i>
                            Projects
                        </h5>
                        <a href="/projects/create" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-lg"></i> New Project
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($projects)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Department</th>
                                    <th class="text-end">Budget</th>
                                    <th>Dates</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $p): ?>
                                <tr>
                                    <td>
                                        <a href="/projects/viewProject/<?= (int)$p->id ?>" class="fw-semibold text-decoration-none">
                                            <?= htmlspecialchars($p->title) ?>
                                        </a>
                                        <?php if (!empty($p->task_count)): ?>
                                        <div class="small text-muted">Tasks: <?= (int)$p->task_count ?><?= isset($p->completed_tasks) ? ' • Completed: ' . (int)$p->completed_tasks : '' ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-secondary rounded-pill">
                                            <?= htmlspecialchars($p->status ?? 'Unknown') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($p->department_name ?? '—') ?></td>
                                    <td class="text-end">
                                        <?php $sym = $currency['symbol'] ?? ''; ?>
                                        <?= $sym ?><?= number_format((float)($p->budget ?? 0), 2) ?>
                                    </td>
                                    <td>
                                        <div class="small text-muted">
                                            <?= !empty($p->start_date) ? date('M j, Y', strtotime($p->start_date)) : '—' ?>
                                            <?php if (!empty($p->end_date)): ?>
                                                – <?= date('M j, Y', strtotime($p->end_date)) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <a href="/projects/viewProject/<?= (int)$p->id ?>" class="btn btn-sm btn-outline-primary" title="View Project">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/projects/edit/<?= (int)$p->id ?>" class="btn btn-sm btn-primary" title="Edit Project">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-kanban text-muted" style="font-size: 2rem;"></i>
                        <h6 class="text-muted mt-2">No Projects</h6>
                        <p class="text-muted mb-3">This client doesn't have any projects yet.</p>
                        <a href="/projects/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create Project</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Document Library -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-folder2-open text-primary me-2"></i>
                            Documents
                        </h5>
                        <?php if (hasPermission('clients.update')): ?>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadClientDocModal">
                            <i class="bi bi-upload"></i> Upload Document
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($documents)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>File Name</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Tags</th>
                                    <th>Uploaded By</th>
                                    <th>Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documents as $document): ?>
                                <?php 
                                    $fileExt = strtolower(pathinfo($document['file_name'], PATHINFO_EXTENSION));
                                    $iconClass = 'bi-file-earmark';
                                    switch ($fileExt) {
                                        case 'pdf': $iconClass = 'bi-file-earmark-pdf'; break;
                                        case 'doc': case 'docx': $iconClass = 'bi-file-earmark-word'; break;
                                        case 'xls': case 'xlsx': $iconClass = 'bi-file-earmark-excel'; break;
                                        case 'jpg': case 'jpeg': case 'png': $iconClass = 'bi-file-earmark-image'; break;
                                        case 'txt': $iconClass = 'bi-file-earmark-text'; break;
                                    }
                                    $tags = array_filter(array_map('trim', explode(',', (string)($document['tags'] ?? ''))));
                                ?>
                                <tr>
                                    <td>
                                        <i class="bi <?= $iconClass ?> me-1"></i>
                                        <?= htmlspecialchars($document['file_name']) ?>
                                    </td>
                                    <td><?= strtoupper($fileExt) ?></td>
                                    <td><?= $document['formatted_size'] ?></td>
                                    <td>
                                        <?php if (!empty($tags)): ?>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php foreach ($tags as $t): ?>
                                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($t) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($document['uploaded_by_name'] ?? '—') ?></td>
                                    <td><?= !empty($document['uploaded_at']) ? date('Y-m-d H:i', strtotime($document['uploaded_at'])) : '—' ?></td>
                                    <td class="text-end">
										<?php if (hasPermission('clients.update')): ?>
										<button class="btn btn-sm btn-outline-secondary" 
												data-bs-toggle="modal" 
												data-bs-target="#renameClientDocModal"
												data-doc-id="<?= (int)$document['id'] ?>"
												data-doc-name="<?= htmlspecialchars($document['file_name']) ?>">
											<i class="bi bi-pencil"></i>
										</button>
										<?php endif; ?>
                                        <a href="/clients/downloadDocument/<?= (int)$document['id'] ?>" class="btn btn-sm btn-outline-primary" title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <?php if (hasPermission('clients.update')): ?>
                                        <a href="/clients/deleteDocument/<?= (int)$document['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this document?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-folder2-open text-muted" style="font-size: 2rem;"></i>
                        <h6 class="text-muted mt-2">No Documents</h6>
                        <p class="text-muted mb-0">Upload client documents and tag them for quick retrieval.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

			<!-- Rename Document Modal -->
			<div class="modal fade" id="renameClientDocModal" tabindex="-1" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Rename Document</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<form id="renameClientDocForm" action="#" method="post">
							<div class="modal-body">
								<div class="mb-3">
									<label class="form-label">New Name</label>
									<input type="text" class="form-control" id="renameDocNameInput" name="file_name" required>
									<div class="form-text">Changing the name does not change the underlying file.</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
								<button type="submit" class="btn btn-primary">
									<i class="bi bi-check2-circle me-1"></i>Save
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>

            <!-- Upload Document Modal -->
            <div class="modal fade" id="uploadClientDocModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Upload Document</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="/clients/uploadDocument/<?= (int)$client['id'] ?>" method="post" enctype="multipart/form-data">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">File</label>
                                    <input type="file" class="form-control" name="document" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt" required>
                                    <div class="form-text">Max 10MB. Allowed: PDF, DOC(X), XLS(X), JPG/PNG, TXT</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tags</label>
                                    <input type="text" class="form-control" name="tags" placeholder="e.g., contract, legal">
                                    <div class="form-text">Comma-separated. Example: proposal, signed</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description (optional)</label>
                                    <textarea class="form-control" name="description" rows="2" placeholder="Short description"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Upload</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Network Infrastructure Discovery Forms -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-hdd-network text-primary me-2"></i>
                            Network Infrastructure Audits
                        </h5>
                        <a href="/networkaudits/create?client_id=<?= $client['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-lg"></i> New Discovery Form
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($audits)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Audit Date</th>
                                    <th>Site Location</th>
                                    <th>Engineer(s)</th>
                                    <th>Created</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($audits as $audit): ?>
                                <tr>
                                    <td>
                                        <?= !empty($audit['audit_date']) ? date('M j, Y', strtotime($audit['audit_date'])) : '—' ?>
                                    </td>
                                    <td><?= !empty($audit['site_location']) ? htmlspecialchars($audit['site_location']) : '<span class="text-muted">Not specified</span>' ?></td>
                                    <td><?= !empty($audit['engineer_names']) ? htmlspecialchars($audit['engineer_names']) : '<span class="text-muted">—</span>' ?></td>
                                    <td>
                                        <div class="small text-muted">
                                            <?= !empty($audit['created_at']) ? date('M j, Y', strtotime($audit['created_at'])) : '—' ?>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <a href="/networkaudits/show/<?= (int)$audit['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Audit">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-hdd-network text-muted" style="font-size: 2rem;"></i>
                        <h6 class="text-muted mt-2">No Network Audits</h6>
                        <p class="text-muted mb-3">No infrastructure discovery forms have been completed yet.</p>
                        <a href="/networkaudits/create?client_id=<?= $client['id'] ?>" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Create Discovery Form
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Client Domains -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-globe2 text-primary me-2"></i>
                            Email Domains
                        </h5>
                        <?php if (hasPermission('clients.update')): ?>
                        <form action="/clients/manageDomains/<?= $client['id'] ?>" method="post" class="d-flex gap-2">
                            <input type="hidden" name="action" value="add">
                            <input type="text" name="domain" class="form-control form-control-sm" placeholder="Add domain (example.com)" required>
                            <div class="form-check form-check-inline align-self-center">
                                <input class="form-check-input" type="checkbox" id="is_primary" name="is_primary">
                                <label class="form-check-label" for="is_primary">Primary</label>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-plus"></i> Add
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($domains)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Domain</th>
                                    <th>Primary</th>
                                    <th>Added</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($domains as $d): ?>
                                <tr>
                                    <td><?= htmlspecialchars($d['domain']) ?></td>
                                    <td>
                                        <?php if (!empty($d['is_primary'])): ?>
                                        <span class="badge text-bg-success">Primary</span>
                                        <?php else: ?>
                                        <span class="badge text-bg-secondary">Secondary</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= !empty($d['created_at']) ? date('M j, Y', strtotime($d['created_at'])) : '—' ?></td>
                                    <td class="text-end">
                                        <?php if (hasPermission('clients.update')): ?>
                                        <form action="/clients/manageDomains/<?= $client['id'] ?>" method="post" class="d-inline">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="domain_id" value="<?= $d['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
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
                        <div class="mb-3">
                            <i class="bi bi-globe2 text-muted" style="font-size: 2rem;"></i>
                        </div>
                        <h6 class="text-muted">No Domains Added</h6>
                        <p class="text-muted mb-0">Add domains to auto-link tickets by sender email domain.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-lg-4">
            <!-- Contacts -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-person-vcard text-primary me-2"></i>
                        Contacts
                    </h6>
                    <?php if (hasPermission('clients.update')): ?>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addClientContactCollapse" aria-expanded="false" aria-controls="addClientContactCollapse">
                            <i class="bi bi-plus-lg"></i> Add
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($client['contact_person']) || !empty($client['email']) || !empty($client['phone'])): ?>
                        <div class="alert alert-light border small mb-3">
                            <div class="fw-semibold mb-1">Primary (Client profile)</div>
                            <div class="text-muted">
                                <?= !empty($client['contact_person']) ? htmlspecialchars($client['contact_person']) : '—' ?>
                                <?php if (!empty($client['email'])): ?>
                                    · <a href="mailto:<?= htmlspecialchars($client['email']) ?>"><?= htmlspecialchars($client['email']) ?></a>
                                <?php endif; ?>
                                <?php if (!empty($client['phone'])): ?>
                                    · <a href="tel:<?= htmlspecialchars($client['phone']) ?>"><?= htmlspecialchars($client['phone']) ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($contacts)): ?>
                        <ul class="list-group list-group-flush mb-3">
                            <?php foreach ($contacts as $ct): ?>
                                <?php
                                    $ctName = trim((string)($ct['full_name'] ?? ''));
                                    if ($ctName === '') {
                                        $ctName = trim(((string)($ct['first_name'] ?? '')) . ' ' . ((string)($ct['last_name'] ?? '')));
                                    }
                                    if ($ctName === '') {
                                        $ctName = (string)($ct['email'] ?? 'Contact');
                                    }
                                ?>
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="me-2">
                                            <div class="fw-semibold"><?= htmlspecialchars($ctName) ?></div>
                                            <div class="small text-muted">
                                                <?php if (!empty($ct['email'])): ?>
                                                    <i class="bi bi-envelope me-1"></i><a href="mailto:<?= htmlspecialchars($ct['email']) ?>"><?= htmlspecialchars($ct['email']) ?></a>
                                                <?php endif; ?>
                                                <?php if (!empty($ct['phone'])): ?>
                                                    <?= !empty($ct['email']) ? ' · ' : '' ?>
                                                    <i class="bi bi-telephone me-1"></i><a href="tel:<?= htmlspecialchars($ct['phone']) ?>"><?= htmlspecialchars($ct['phone']) ?></a>
                                                <?php endif; ?>
                                                <?php if (!empty($ct['mobile'])): ?>
                                                    <?= (!empty($ct['email']) || !empty($ct['phone'])) ? ' · ' : '' ?>
                                                    <i class="bi bi-phone me-1"></i><a href="tel:<?= htmlspecialchars($ct['mobile']) ?>"><?= htmlspecialchars($ct['mobile']) ?></a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if (hasPermission('clients.update')): ?>
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#editContactModal<?= (int)$ct['id'] ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="/clients/deleteContact/<?= (int)$ct['id'] ?>/<?= (int)$client['id'] ?>" method="post" onsubmit="return confirm('Remove this contact?');">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </li>

                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mb-3">No additional contacts added yet.</p>
                    <?php endif; ?>

                    <?php if (hasPermission('clients.update')): ?>
                        <div class="collapse" id="addClientContactCollapse">
                            <form action="/clients/addContact/<?= (int)$client['id'] ?>" method="post">
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name" class="form-control">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name" class="form-control">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="full_name" class="form-control" placeholder="Optional">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control">
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label">Main number</label>
                                        <input type="text" name="phone" class="form-control">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Mobile number</label>
                                        <input type="text" name="mobile" class="form-control">
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg"></i> Save Contact
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Client Services -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-tools text-primary me-2"></i>
                        Client Services
                    </h6>
                    <?php if (hasPermission('clients.update')): ?>
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addClientServiceCollapse" aria-expanded="false" aria-controls="addClientServiceCollapse">
                        <i class="bi bi-plus-lg"></i> Add
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($client_services)): ?>
                        <ul class="list-group list-group-flush mb-3">
                            <?php foreach ($client_services as $svc): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="me-2">
                                        <div class="fw-semibold"><?= htmlspecialchars($svc['service_name']) ?></div>
                                        <?php if (!empty($svc['service_type'])): ?>
                                            <div class="small text-muted"><?= htmlspecialchars($svc['service_type']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($svc['quantity'])): ?>
                                            <div><span class="badge text-bg-secondary">Qty: <?= (int)$svc['quantity'] ?></span></div>
                                        <?php endif; ?>
                                        <?php if (!empty($svc['start_date']) || !empty($svc['end_date'])): ?>
                                            <div class="small text-muted">
                                                <?php if (!empty($svc['start_date'])): ?>
                                                    Start: <?= date('Y-m-d', strtotime($svc['start_date'])) ?>
                                                <?php endif; ?>
                                                <?php if (!empty($svc['end_date'])): ?>
                                                    · End: <?= date('Y-m-d', strtotime($svc['end_date'])) ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($svc['notes'])): ?>
                                            <div class="small text-muted"><?= nl2br(htmlspecialchars($svc['notes'])) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (hasPermission('clients.update')): ?>
                                    <form action="/clients/deleteService/<?= (int)$svc['id'] ?>/<?= (int)$client['id'] ?>" method="post" onsubmit="return confirm('Remove this service?');">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted mb-3">No services recorded for this client.</p>
                    <?php endif; ?>

                    <?php if (hasPermission('clients.update')): ?>
                    <div class="collapse" id="addClientServiceCollapse">
                        <form action="/clients/addService/<?= (int)$client['id'] ?>" method="post">
                            <div class="mb-3">
                                <label class="form-label">Service Name <span class="text-danger">*</span></label>
                                <input type="text" name="service_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Service Type</label>
                                <input type="text" name="service_type" class="form-control" placeholder="e.g., Managed IT, Security, Backup">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" class="form-control" min="1" value="1">
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date" class="form-control">
                                </div>
                                <div class="col">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" class="form-control">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Details, scope, SLAs, etc."></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg"></i> Save Service
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Visibility -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-eye text-primary me-2"></i>
                        Visibility
                    </h6>
                    <?php if (hasPermission('clients.update')): ?>
                    <a href="/clients/edit/<?= (int)$client['id'] ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($is_restricted)): ?>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-danger me-2">Restricted</span>
                            <small class="text-muted">Only allowed roles can view.</small>
                        </div>
                        <div class="mb-2">
                            <div class="fw-semibold small text-uppercase text-muted">Allowed Roles</div>
                            <?php if (!empty($allowed_role_names)): ?>
                                <ul class="list-unstyled mb-0 small">
                                    <?php foreach ($allowed_role_names as $roleName): ?>
                                    <li class="d-flex align-items-center">
                                        <i class="bi bi-shield-check text-success me-2"></i>
                                        <?= htmlspecialchars($roleName) ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="text-muted small">No roles selected.</div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success me-2">Public</span>
                            <small class="text-muted">All users with client access can view.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Status History -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-clock-history text-primary me-2"></i>
                        Status History
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($status_history)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($status_history as $hist): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($hist['old_status'] ?? 'Unknown') ?>
                                        <i class="bi bi-arrow-right-short mx-1"></i>
                                        <?= htmlspecialchars($hist['new_status'] ?? 'Unknown') ?>
                                    </div>
                                    <div class="small text-muted">
                                        <?= !empty($hist['username']) ? htmlspecialchars($hist['username']) : 'System' ?>
                                    </div>
                                </div>
                                <div class="text-muted small">
                                    <?= !empty($hist['changed_at']) ? date('M j, Y g:i A', strtotime($hist['changed_at'])) : '' ?>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <p class="text-muted mb-0">No status changes recorded.</p>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Contracts -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i>
                        Contracts
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <?php if (hasPermission('clients.update')): ?>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addContractCollapse" aria-expanded="false" aria-controls="addContractCollapse">
                            <i class="bi bi-upload"></i> Upload
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (hasPermission('clients.update')): ?>
                    <div class="collapse mb-3" id="addContractCollapse">
                        <form action="/clients/uploadContract/<?= (int)$client['id'] ?>" method="post" enctype="multipart/form-data">
                            <div class="input-group">
                                <input type="file" name="contract" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save</button>
                            </div>
                            <div class="form-text">Max 10MB. Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG</div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($contracts)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($contracts as $contract): ?>
                        <li class="list-group-item d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-text text-secondary"></i>
                                <div>
                                    <div class="fw-semibold text-truncate" style="max-width:200px;" title="<?= htmlspecialchars($contract['file_name']) ?>">
                                        <?= htmlspecialchars($contract['file_name']) ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= !empty($contract['uploaded_at']) ? date('M j, Y', strtotime($contract['uploaded_at'])) : '' ?>
                                        <?php if (!empty($contract['file_size'])): ?>
                                            • <?= number_format((int)$contract['file_size']/1024, 1) ?> KB
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="/clients/downloadContract/<?= (int)$contract['id'] ?>" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                                <?php if (hasPermission('clients.update')): ?>
                                <a class="btn btn-sm btn-outline-danger" href="/clients/deleteContract/<?= (int)$contract['id'] ?>" title="Delete" onclick="return confirm('Delete this contract?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <div class="text-center py-2 text-muted small">No contracts uploaded.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Follow-ups / Reminders -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-bell text-primary me-2"></i>
                        Follow-ups & Reminders
                    </h6>
                    <?php if (hasPermission('clients.update')): ?>
                    <div class="d-flex align-items-center gap-2">
                        <a href="/clients/callbacksHistory/<?= (int)$client['id'] ?>" class="btn btn-sm btn-link text-decoration-none">
                            <i class="bi bi-clock-history me-1"></i> History
                        </a>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addFollowupCollapse" aria-expanded="false" aria-controls="addFollowupCollapse">
                            <i class="bi bi-plus-lg"></i> Add
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (hasPermission('clients.update')): ?>
                    <div class="collapse mb-3" id="addFollowupCollapse">
                        <form action="/clients/addCallback/<?= (int)$client['id'] ?>" method="post">
                            <div class="mb-2">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" placeholder="e.g., Call back regarding proposal" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Remind At</label>
                                <input type="datetime-local" name="remind_at" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes (optional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Add context or talking points"></textarea>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="1" id="notify_all" name="notify_all">
                                <label class="form-check-label" for="notify_all">
                                    Show in notifications for all users
                                </label>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>Save Follow-up
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <h6 class="text-muted mb-2">Upcoming</h6>
                    <?php
                    $pending = array_values(array_filter(($callbacks ?? []), function($c) { return ($c['status'] ?? '') === 'Pending'; }));
                    usort($pending, function($a, $b) {
                        return strtotime($a['remind_at']) <=> strtotime($b['remind_at']);
                    });
                    ?>
                    <?php if (!empty($pending)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($pending as $cb): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="me-2">
                                <div class="fw-semibold text-truncate" style="max-width:220px;">
                                    <?= htmlspecialchars($cb['title']) ?>
                                </div>
                                <div class="small text-muted">
                                    <?= date('M j, Y g:i A', strtotime($cb['remind_at'])) ?>
                                </div>
                                <?php if (!empty($cb['notes'])): ?>
                                <div class="small text-muted text-truncate" style="max-width:260px;">
                                    <?= htmlspecialchars($cb['notes']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php if (hasPermission('clients.update')): ?>
                            <div>
                                <a href="/clients/completeCallback/<?= (int)$cb['id'] ?>" class="btn btn-sm btn-outline-success" title="Mark Completed">
                                    <i class="bi bi-check2-circle"></i>
                                </a>
                            </div>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <div class="text-center py-2 text-muted small">No upcoming follow-ups.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightning text-primary me-2"></i>
                        Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if (hasPermission('clients.assign_sites')): ?>
                        <a href="/clients/assignSites/<?= $client['id'] ?>" class="btn btn-outline-info">
                            <i class="bi bi-geo-alt me-2"></i>Manage Sites
                        </a>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('clients.update')): ?>
                        <a href="/clients/edit/<?= $client['id'] ?>" class="btn btn-outline-warning">
                            <i class="bi bi-pencil me-2"></i>Edit Client
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($client['email'])): ?>
                        <a href="mailto:<?= htmlspecialchars($client['email']) ?>" class="btn btn-outline-primary">
                            <i class="bi bi-envelope me-2"></i>Send Email
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($client['phone'])): ?>
                        <a href="tel:<?= htmlspecialchars($client['phone']) ?>" class="btn btn-outline-success">
                            <i class="bi bi-telephone me-2"></i>Call Client
                        </a>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('clients.delete')): ?>
                        <hr>
                        <a href="/clients/delete/<?= $client['id'] ?>" class="btn btn-outline-danger">
                            <i class="bi bi-trash me-2"></i>Delete Client
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Site Visits -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-journal-text text-primary me-2"></i>
                        Recent Site Visits
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border">Top 10</span>
                        <?php if (!empty($sites)): ?>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#logVisitModal">
                            <i class="bi bi-journal-plus"></i> Log Visit
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_visits)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_visits as $rv): ?>
                        <a href="/sitevisits/show/<?= (int)$rv['id'] ?>" class="list-group-item list-group-item-action d-flex align-items-start gap-3 w-100">
                            <div class="rounded bg-primary bg-opacity-10 p-2">
                                <i class="bi bi-clipboard-check text-primary"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="d-flex justify-content-between">
                                    <strong class="text-truncate text-break" style="max-width: 240px;">
                                        <?= htmlspecialchars($rv['title'] ?: ($rv['site_name'] ?? 'Visit')) ?>
                                    </strong>
                                    <small class="text-muted"><?= date('M j, Y H:i', strtotime($rv['visit_date'])) ?></small>
                                </div>
                                <div class="small text-muted text-break">
                                    <?= htmlspecialchars($rv['site_name'] ?? 'Unknown Site') ?>
                                    <?php if (!empty($rv['site_location'])): ?>
                                        • <?= htmlspecialchars($rv['site_location']) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted mt-1">By <?= htmlspecialchars($rv['full_name'] ?? $rv['username'] ?? 'Technician') ?></div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-3">
                        <i class="bi bi-journal-x text-muted" style="font-size: 1.5rem;"></i>
                        <div class="mt-2 small text-muted">No recent visits</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Log Visit Modal -->
            <div class="modal fade" id="logVisitModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Log Site Visit</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <?php if (!empty($sites)): ?>
                            <div class="mb-3">
                                <label for="logVisitSiteSelect" class="form-label">Select Site</label>
                                <select id="logVisitSiteSelect" class="form-select">
                                    <?php foreach ($sites as $s): ?>
                                    <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name']) ?><?= !empty($s['location']) ? ' - ' . htmlspecialchars($s['location']) : '' ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info mb-0">No sites assigned to this client.</div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="goLogVisitFromModal()">Continue</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client Stats -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-bar-chart text-primary me-2"></i>
                        Client Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <div class="border-end">
                                <h4 class="mb-0 text-primary"><?= count($sites) ?></h4>
                                <small class="text-muted">Sites</small>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <div class="border-end">
                                <h4 class="mb-0 text-info"><?= isset($projects) ? count($projects) : 0 ?></h4>
                                <small class="text-muted">Projects</small>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <?php $totalEngineerVisits = (int)($site_visit_stats['total_visits'] ?? 0); ?>
                            <h4 class="mb-0 text-success"><?= $totalEngineerVisits ?></h4>
                            <small class="text-muted">Engineer Site Visits</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (hasPermission('clients.update') && !empty($contacts)): ?>
    <?php foreach ($contacts as $ct): ?>
        <!-- Edit Contact Modal (kept outside sidebar/cards to avoid CSS transform positioning issues) -->
        <div class="modal fade" id="editContactModal<?= (int)$ct['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-person-vcard me-2"></i>Edit Contact</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="/clients/updateContact/<?= (int)$ct['id'] ?>/<?= (int)$client['id'] ?>" method="post">
                        <div class="modal-body">
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars((string)($ct['first_name'] ?? '')) ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars((string)($ct['last_name'] ?? '')) ?>">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars((string)($ct['full_name'] ?? '')) ?>">
                                <div class="form-text">If empty, we’ll use First + Last.</div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars((string)($ct['email'] ?? '')) ?>">
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label">Main number</label>
                                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars((string)($ct['phone'] ?? '')) ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Mobile number</label>
                                    <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars((string)($ct['mobile'] ?? '')) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
(function(){
    window.goLogVisitFromModal = function() {
        var sel = document.getElementById('logVisitSiteSelect');
        if (!sel || !sel.value) return;
        window.location.href = '/sitevisits/create/' + sel.value;
    }

	// Hook up Rename Document modal
	var renameModal = document.getElementById('renameClientDocModal');
	if (renameModal) {
		renameModal.addEventListener('show.bs.modal', function (event) {
			var button = event.relatedTarget;
			if (!button) return;
			var docId = button.getAttribute('data-doc-id');
			var docName = button.getAttribute('data-doc-name');
			var form = document.getElementById('renameClientDocForm');
			var input = document.getElementById('renameDocNameInput');
			if (form && docId) {
				form.action = '/clients/renameDocument/' + docId;
			}
			if (input) {
				input.value = docName || '';
				setTimeout(function(){ input.focus(); input.select(); }, 150);
			}
		});
	}
})();
</script> 
