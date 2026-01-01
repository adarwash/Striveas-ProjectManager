<div class="container-fluid">
    <?php
        $displayTz = $data['display_timezone'] ?? date_default_timezone_get();
        $dbTz = $data['db_timezone'] ?? $displayTz;
        $toDisplay = function($dtStr) use ($dbTz, $displayTz) {
            try {
                $dt = new DateTime($dtStr, new DateTimeZone($dbTz));
                $dt->setTimezone(new DateTimeZone($displayTz));
                return $dt;
            } catch (Exception $e) {
                return null;
            }
        };

        // Rewrite cid: inline images in stored email HTML to local attachment URLs
        $rewriteCidImages = function(string $html, ?string $messageId) use ($data) {
            $html = (string)$html;
            if ($html === '' || empty($messageId) || empty($data['attachments']) || !is_array($data['attachments'])) {
                return $html;
            }

            $cidMap = [];
            foreach ($data['attachments'] as $att) {
                if (empty($att['is_inline']) || (int)$att['is_inline'] !== 1) { continue; }
                if (empty($att['ms_message_id']) || (string)$att['ms_message_id'] !== (string)$messageId) { continue; }
                if (empty($att['content_id']) || empty($att['file_path'])) { continue; }

                $cid = strtolower(trim((string)$att['content_id'], "<> \t\r\n"));
                if ($cid === '') { continue; }

                $url = URLROOT . '/' . ltrim((string)$att['file_path'], '/');
                $cidMap[$cid] = $url;
            }
            if (empty($cidMap)) {
                return $html;
            }

            // Replace src="cid:..." / src='cid:...' / src=cid:...
            $html = preg_replace_callback(
                '/src\\s*=\\s*(?:([\"\\\'])cid:([^\"\\\']+)\\1|cid:([^\\s>]+))/i',
                function($m) use ($cidMap) {
                    $cid = $m[2] ?? ($m[3] ?? '');
                    $cid = strtolower(trim((string)$cid, "<> \t\r\n"));
                    if ($cid !== '' && isset($cidMap[$cid])) {
                        $url = htmlspecialchars($cidMap[$cid], ENT_QUOTES);
                        // Preserve quotes if present
                        if (!empty($m[1])) {
                            $q = $m[1];
                            return 'src=' . $q . $url . $q;
                        }
                        return 'src="' . $url . '"';
                    }
                    return $m[0];
                },
                $html
            );

            return $html;
        };

        // Normalize full HTML email documents into safe fragments for embedding
        $normalizeEmailHtml = function(string $html): string {
            $html = (string)$html;
            if ($html === '') return $html;

            // Drop doctype
            $html = preg_replace('/<!doctype[^>]*>/i', '', $html);
            // Remove <head> completely (contains meta/style/title)
            $html = preg_replace('#<\s*head[^>]*>.*?<\s*/\s*head\s*>#is', '', $html);
            // Remove outer html/body wrappers
            $html = preg_replace('#<\s*/\s*html\s*>#is', '', $html);
            $html = preg_replace('#<\s*html[^>]*>#is', '', $html);
            $html = preg_replace('#<\s*/\s*body\s*>#is', '', $html);
            $html = preg_replace('#<\s*body[^>]*>#is', '', $html);

            // Remove stray meta tags (sometimes outside head)
            $html = preg_replace('#<\s*meta[^>]*>#is', '', $html);

            return trim($html);
        };

        // Build conversation timeline messages (group outbound email logs with the agent comment that triggered them)
        $messagesForTimeline = [];
        $msgs = $data['messages'] ?? [];
        $total = is_array($msgs) ? count($msgs) : 0;
        for ($i = 0; $i < $total; $i++) {
            $m = $msgs[$i];
            if (($m['message_type'] ?? '') === 'email_outbound' && isset($msgs[$i + 1])) {
                $next = $msgs[$i + 1];
                $sameUser = !empty($m['user_id']) && (int)$m['user_id'] === (int)($next['user_id'] ?? 0);
                $nextIsComment = ($next['message_type'] ?? '') === 'comment';
                $t1 = strtotime((string)($m['created_at'] ?? '')) ?: 0;
                $t2 = strtotime((string)($next['created_at'] ?? '')) ?: 0;
                // Email sending can lag slightly (queue / background worker). Allow a small window for pairing.
                $closeInTime = ($t1 > 0 && $t2 > 0) ? (abs($t1 - $t2) <= 300) : false;
                if ($sameUser && $nextIsComment && $closeInTime) {
                    $next['__embeddedOutbound'] = $m;
                    $messagesForTimeline[] = $next;
                    $i++; // consume the comment
                    continue;
                }
            }
            $messagesForTimeline[] = $m;
        }
    ?>
    <!-- Modern Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fas fa-ticket-alt me-3"></i>Ticket #<?= htmlspecialchars($data['ticket']['ticket_number']) ?></h1>
            <p class="mb-0"><?= htmlspecialchars($data['ticket']['subject']) ?></p>
        </div>
        <div>
            <?php if ($data['can_edit']): ?>
            <a href="<?= URLROOT ?>/tickets/edit/<?= $data['ticket']['id'] ?>" class="btn btn-primary me-2">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <?php endif; ?>
            <a href="<?= URLROOT ?>/tickets" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Tickets
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Ticket Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h1 class="h4 mb-2"><?= htmlspecialchars($data['ticket']['subject']) ?></h1>
                            <?php if (!empty($data['ticket']['inbound_email_address'])): ?>
                            <div class="mb-2">
                                <small class="text-muted">From: </small>
                                <a href="mailto:<?= htmlspecialchars($data['ticket']['inbound_email_address']) ?>" class="text-decoration-none">
                                    <i class="bi bi-envelope me-1"></i><strong><?= htmlspecialchars($data['ticket']['inbound_email_address']) ?></strong>
                                </a>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <span class="badge fs-6" style="background-color: <?= $data['ticket']['status_color'] ?>">
                                    <?= htmlspecialchars($data['ticket']['status_display']) ?>
                                </span>
                                <span class="badge fs-6" style="background-color: <?= $data['ticket']['priority_color'] ?>">
                                    <?= htmlspecialchars($data['ticket']['priority_display']) ?>
                                </span>
                                <?php if ($data['ticket']['is_overdue']): ?>
                                    <span class="badge bg-danger fs-6">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Overdue
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($data['client'])): ?>
                                    <a href="<?= URLROOT ?>/clients/viewClient/<?= (int)$data['client']['id'] ?>" class="badge rounded-pill text-bg-primary text-decoration-none">
                                        <i class="bi bi-building me-1"></i><?= htmlspecialchars($data['client']['name']) ?>
                                    </a>
                                <?php elseif (!empty($data['ticket']['client_id'])): ?>
                                    <span class="badge rounded-pill text-bg-secondary">Client #<?= (int)$data['ticket']['client_id'] ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear me-1"></i>Actions
                            </button>
                            <ul class="dropdown-menu">
                                <?php if ($data['can_edit']): ?>
                                <li><a class="dropdown-item" href="<?= URLROOT ?>/tickets/edit/<?= $data['ticket']['id'] ?>">
                                    <i class="bi bi-pencil me-2"></i>Edit Ticket
                                </a></li>
                                <?php endif; ?>
                                <?php if ($data['can_assign']): ?>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#assignModal">
                                    <i class="bi bi-person-plus me-2"></i>Assign
                                </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="window.print()">
                                    <i class="bi bi-printer me-2"></i>Print
                                </a></li>
                                <?php if (hasPermission('tickets.delete')): ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form method="POST" action="<?= URLROOT ?>/tickets/archive/<?= $data['ticket']['id'] ?>" onsubmit="return confirm('Archive this ticket?')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? ( $_SESSION['csrf_token']=bin2hex(random_bytes(16)) )) ?>">
                                        <button type="submit" class="dropdown-item text-warning">
                                            <i class="bi bi-archive me-2"></i>Archive Ticket
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form method="POST" action="<?= URLROOT ?>/tickets/delete" onsubmit="return confirm('Delete this ticket permanently? This cannot be undone.');">
                                        <input type="hidden" name="ticket_id" value="<?= $data['ticket']['id'] ?>">
                                        <?php 
                                            if (!isset($_SESSION['csrf_token'])) { 
                                                $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); 
                                            }
                                        ?>
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-trash me-2"></i>Delete Ticket
                                        </button>
                                    </form>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Ticket #:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($data['ticket']['ticket_number']) ?></dd>
                                
                                <dt class="col-sm-4">Created by:</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($data['ticket']['created_by_name']) ?></dd>
                                
                                <?php if (!empty($data['ticket']['inbound_email_address'])): ?>
                                <dt class="col-sm-4">Requester Email:</dt>
                                <dd class="col-sm-8">
                                    <a href="mailto:<?= htmlspecialchars($data['ticket']['inbound_email_address']) ?>" class="text-decoration-none">
                                        <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($data['ticket']['inbound_email_address']) ?>
                                    </a>
                                </dd>
                                <?php endif; ?>
                                
                                <dt class="col-sm-4">Assigned to:</dt>
                                <dd class="col-sm-8">
                                    <?= $data['ticket']['assigned_to_name'] ? 
                                        htmlspecialchars($data['ticket']['assigned_to_name']) : 
                                        '<span class="text-muted">Unassigned</span>' ?>
                                </dd>
                                
                                <dt class="col-sm-4">Category:</dt>
                                <dd class="col-sm-8">
                                    <?= $data['ticket']['category_name'] ? 
                                        htmlspecialchars($data['ticket']['category_name']) : 
                                        '<span class="text-muted">None</span>' ?>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Created:</dt>
                                <dd class="col-sm-8">
                                    <?php $cdt = $toDisplay($data['ticket']['created_at']); ?>
                                    <?= $cdt ? $cdt->format('M j, Y g:i A') : date('M j, Y g:i A', strtotime($data['ticket']['created_at'])) ?>
                                    <small class="text-muted">(<?= $data['ticket']['age_hours'] ?> hours ago)</small>
                                </dd>
                                
                                <dt class="col-sm-4">Last updated:</dt>
                                <dd class="col-sm-8">
                                    <?php $udt = $toDisplay($data['ticket']['updated_at']); ?>
                                    <?= $udt ? $udt->format('M j, Y g:i A') : date('M j, Y g:i A', strtotime($data['ticket']['updated_at'])) ?>
                                </dd>
                                
                                <dt class="col-sm-4">Due date:</dt>
                                <dd class="col-sm-8">
                                    <?= $data['ticket']['due_date'] ? 
                                        date('M j, Y', strtotime($data['ticket']['due_date'])) : 
                                        '<span class="text-muted">Not set</span>' ?>
                                </dd>
                                
                                <dt class="col-sm-4">Source:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-secondary">
                                        <?= ucfirst($data['ticket']['source'] ?? 'web') ?>
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conversation Thread -->
            <div class="card border-0 shadow-sm" id="conversationCard">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-chat-dots me-2"></i>Conversation
                        <span class="badge bg-secondary ms-2"><?= count($messagesForTimeline) ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="timeline">
                        <?php if (empty($messagesForTimeline)): ?>
                            <div class="p-4 text-muted">
                                No conversation messages yet.
                            </div>
                        <?php else: ?>
                            <?php foreach ($messagesForTimeline as $index => $message): ?>
                                <?php if ($index > 0): ?>
                                    <div class="message-divider my-3"></div>
                                <?php endif; ?>
                                <div class="timeline-item p-4 overflow-auto <?= ($message['is_system_message'] ?? 0) ? 'bg-light' : '' ?>">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                    style="width: 40px; height: 40px;">
                                                <?php if (!empty($message['username'])): ?>
                                                    <?php
                                                        $fullName = trim($message['full_name'] ?? '')
                                                            ?: trim(($message['first_name'] ?? '') . ' ' . ($message['last_name'] ?? ''))
                                                            ?: ($message['username'] ?? '');
                                                        $initials = '';
                                                        if (!empty($fullName)) {
                                                            $parts = preg_split('/\s+/', trim($fullName));
                                                            $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[count($parts) - 1] ?? '', 0, 1));
                                                        }
                                                        echo htmlspecialchars($initials ?: '');
                                                    ?>
                                                <?php elseif (in_array($message['message_type'], ['email_inbound', 'email_outbound'])): ?>
                                                    <i class="bi bi-envelope"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-gear"></i>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h6 class="mb-0">
                                                        <?php if (!empty($message['username'])): ?>
                                                            <?= htmlspecialchars($fullName ?: $message['username']) ?>
                                                        <?php elseif (in_array($message['message_type'], ['email_inbound', 'email_outbound']) && !empty($message['email_from'])): ?>
                                                            <?= htmlspecialchars($message['email_from']) ?>
                                                        <?php elseif (!empty($message['full_name'])): ?>
                                                            <?= htmlspecialchars($message['full_name']) ?>
                                                        <?php else: ?>
                                                            System
                                                        <?php endif; ?>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <?php $mdt = $toDisplay($message['created_at']); ?>
                                                        <?= $mdt ? $mdt->format('M j, Y g:i A') : date('M j, Y g:i A', strtotime($message['created_at'])) ?>
                                                        
                                                        <?php if (($message['message_type'] ?? 'comment') !== 'comment'): ?>
                                                            <span class="badge bg-info ms-2">
                                                                <?= ucwords(str_replace('_', ' ', $message['message_type'])) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (empty($message['is_public'])): ?>
                                                            <span class="badge bg-warning ms-1">Internal</span>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <?php if (!empty($message['subject']) && in_array($message['message_type'], ['email_inbound', 'email_outbound'])): ?>
                                                <div class="mb-2">
                                                    <strong>Subject:</strong> <?= htmlspecialchars($message['subject']) ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="message-content">
                                                <?php if (($message['content_format'] ?? 'text') === 'html'): ?>
                                                    <div class="html-content">
                                                        <?php
                                                            // Render stored HTML for email content, but fallback if it's actually plain text
                                                            $c = (string)($message['content'] ?? '');
                                                            if (in_array($message['message_type'] ?? '', ['email_inbound', 'email_outbound'], true)) {
                                                                $c = $normalizeEmailHtml($c);
                                                                $c = $rewriteCidImages($c, $message['email_message_id'] ?? null);
                                                            }
                                                            $looksHtml = preg_match('/<\s*\w+[^>]*>/', $c) === 1;
                                                            echo $looksHtml ? $c : nl2br(htmlspecialchars($c));
                                                        ?>
                                                    </div>
                                                <?php else: ?>
                                                    <?= nl2br(htmlspecialchars($message['content'])) ?>
                                                <?php endif; ?>
                                            </div>

                                <?php
                                    // Show non-inline attachments linked to this message (by ticket_message_id or ms_message_id)
                                    $messageAttachments = array_filter($data['attachments'] ?? [], function($att) use ($message) {
                                        if (!empty($att['is_inline']) && (int)$att['is_inline'] === 1) return false;
                                        $msgId = (int)($message['id'] ?? 0);
                                        $tmMatch = isset($att['ticket_message_id']) && (int)$att['ticket_message_id'] === $msgId;
                                        $msMatch = !empty($att['ms_message_id']) && !empty($message['email_message_id']) && (string)$att['ms_message_id'] === (string)$message['email_message_id'];
                                        return $tmMatch || $msMatch;
                                    });
                                ?>
                                <?php if (!empty($messageAttachments)): ?>
                                    <div class="mt-2">
                                        <small class="text-muted d-block mb-1"><i class="bi bi-paperclip me-1"></i>Attachments</small>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php foreach ($messageAttachments as $att): ?>
                                                <?php $name = $att['original_filename'] ?: $att['filename']; ?>
                                                <?php if (!empty($att['file_path'])): ?>
                                                    <a class="badge bg-light text-dark border text-decoration-none" target="_blank"
                                                       href="<?= URLROOT . '/' . ltrim($att['file_path'], '/') ?>">
                                                        <i class="bi bi-link-45deg me-1"></i><?= htmlspecialchars($name) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($name) ?> (pending)
                                                    </span>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                            <?php
                                                $hasFull = !empty($message['content_full']) && is_string($message['content_full']) && trim($message['content_full']) !== '';
                                                $isEmailMsg = in_array($message['message_type'] ?? '', ['email_inbound', 'email_outbound'], true);
                                            ?>
                                            <?php if ($isEmailMsg && $hasFull): ?>
                                                <?php $collapseId = 'fullEmail_' . (int)$message['id']; ?>
                                                <div class="mt-2">
                                                    <button class="btn btn-sm btn-outline-secondary"
                                                            type="button"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#<?= $collapseId ?>"
                                                            aria-expanded="false"
                                                            aria-controls="<?= $collapseId ?>">
                                                        Show full email thread
                                                    </button>
                                                </div>
                                                <div class="collapse mt-2" id="<?= $collapseId ?>">
                                                    <div class="card card-body bg-light">
                                                        <?php
                                                            $full = (string)$message['content_full'];
                                                            $full = $normalizeEmailHtml($full);
                                                            $full = $rewriteCidImages($full, $message['email_message_id'] ?? null);
                                                            $looksHtmlFull = preg_match('/<\s*\w+[^>]*>/', $full) === 1;
                                                            echo $looksHtmlFull ? $full : nl2br(htmlspecialchars($full));
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($message['__embeddedOutbound']) && is_array($message['__embeddedOutbound'])): ?>
                                                <?php
                                                    $ob = $message['__embeddedOutbound'];
                                                    $outCollapseId = 'sentEmail_' . (int)($ob['id'] ?? 0) . '_' . (int)($message['id'] ?? 0);
                                                    $sentAt = $toDisplay($ob['created_at'] ?? '');
                                                ?>
                                                <div class="mt-3">
                                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                        <small class="text-muted">
                                                            <i class="bi bi-envelope-check me-1"></i>
                                                            Email sent<?= $sentAt ? (' at ' . $sentAt->format('g:i A')) : '' ?>
                                                            <?php if (!empty($ob['email_to'])): ?>
                                                                to <strong><?= htmlspecialchars($ob['email_to']) ?></strong>
                                                            <?php endif; ?>
                                                            <?php if (!empty($ob['subject'])): ?>
                                                                <span class="ms-1">| Subject: <?= htmlspecialchars($ob['subject']) ?></span>
                                                            <?php endif; ?>
                                                        </small>
                                                        <button class="btn btn-sm btn-outline-secondary"
                                                                type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#<?= $outCollapseId ?>"
                                                                aria-expanded="false"
                                                                aria-controls="<?= $outCollapseId ?>">
                                                            View sent email
                                                        </button>
                                                    </div>
                                                    <div class="collapse mt-2" id="<?= $outCollapseId ?>">
                                                        <div class="card card-body bg-light">
                                                            <?php
                                                                $oc = (string)($ob['content'] ?? '');
                                                                $oc = $normalizeEmailHtml($oc);
                                                                $oc = $rewriteCidImages($oc, $ob['email_message_id'] ?? null);
                                                                $looksHtmlO = preg_match('/<\s*\w+[^>]*>/', $oc) === 1;
                                                                echo $looksHtmlO ? $oc : nl2br(htmlspecialchars($oc));
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (in_array($message['message_type'], ['email_inbound', 'email_outbound'])): ?>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <i class="bi bi-envelope me-1"></i>
                                                        <?php if ($message['message_type'] === 'email_inbound'): ?>
                                                            From: <?= htmlspecialchars($message['email_from'] ?? '') ?>
                                                            <?php if (!empty($message['email_to'])): ?>
                                                                | To: <?= htmlspecialchars($message['email_to']) ?>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            To: <?= htmlspecialchars($message['email_to'] ?? '') ?>
                                                        <?php endif; ?>
                                                        <?php if (!empty($message['email_cc'])): ?>
                                                            | CC: <?= htmlspecialchars($message['email_cc']) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Add Reply Form -->
                <?php if (hasPermission('tickets.comment')): ?>
                <div class="card-footer bg-white">
                    <form method="POST" action="<?= URLROOT ?>/tickets/addMessage/<?= $data['ticket']['id'] ?>">
                        <div class="mb-3">
                            <label for="message" class="form-label">Add Reply</label>
                            <!-- Rich text editor (Quill) -->
                            <div id="replyEditor" style="height: 180px;"></div>
                            <textarea class="d-none" id="message" name="message"></textarea>
                            <input type="hidden" name="content_format" value="html">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="message_type" class="form-label">Message Type</label>
                                    <select class="form-select" id="message_type" name="message_type">
                                        <option value="comment">Comment</option>
                                        <option value="status_change">Status Update</option>
                                        <?php if (hasPermission('tickets.internal_notes')): ?>
                                            <option value="internal_note">Internal Note</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1" checked>
                                        <label class="form-check-label" for="is_public">
                                            Visible to customer
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Send Reply
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div id="pendingCardWrapper">
            <?php if (!empty($data['pending_attachments'])): ?>
                <div class="card border-0 shadow-sm mb-4" id="pendingAttachmentsCard"
                     data-ticket-id="<?= (int)$data['ticket']['id'] ?>"
                     data-pending-count="<?= (int)$data['pending_attachments'] ?>">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0 d-flex align-items-center justify-content-between">
                            <span>
                                <i class="bi bi-cloud-download me-2"></i>Attachments pending
                                <span class="badge bg-secondary ms-2"><?= (int)$data['pending_attachments'] ?></span>
                            </span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="text-muted small mb-2">
                            Attachments are being downloaded automatically in the background. This will refresh to show inline images as soon as they’re ready.
                        </div>
                        <div class="small" id="fetchAttachmentsStatus" aria-live="polite"></div>
                    </div>
                </div>
            <?php endif; ?>
            </div>

            <!-- Attachments (non-inline / downloadable) -->
            <?php
                $downloadableAttachments = array_filter($data['attachments'] ?? [], function($a) {
                    return empty($a['is_inline']) || (int)$a['is_inline'] !== 1;
                });
            ?>
            <div id="attachmentsCardWrapper">
            <?php if (!empty($downloadableAttachments)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-paperclip me-2"></i>Attachments
                        <span class="badge bg-secondary ms-2"><?= count($downloadableAttachments) ?></span>
                    </h6>
                </div>

                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($downloadableAttachments as $att): ?>
                            <li class="list-group-item d-flex align-items-center justify-content-between px-0">
                                <div class="me-2 d-flex align-items-center">
                                    <?php
                                        $name = $att['original_filename'] ?: $att['filename'];
                                        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                                        $icon = 'file-earmark';
                                        if (in_array($ext, ['png','jpg','jpeg','gif','bmp','webp'])) { $icon = 'file-earmark-image'; }
                                        elseif (in_array($ext, ['pdf'])) { $icon = 'file-earmark-pdf'; }
                                        elseif (in_array($ext, ['doc','docx'])) { $icon = 'file-earmark-word'; }
                                        elseif (in_array($ext, ['xls','xlsx'])) { $icon = 'file-earmark-excel'; }
                                        elseif (in_array($ext, ['ppt','pptx'])) { $icon = 'file-earmark-ppt'; }
                                    ?>
                                    <i class="bi bi-<?= $icon ?> me-2"></i>
                                    <div>
                                        <div class="fw-semibold text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($name) ?>">
                                            <?= htmlspecialchars($name) ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= !empty($att['file_size']) ? number_format(((int)$att['file_size'])/1024, 1) . ' KB' : '' ?>
                                        </small>
                                    </div>
                                </div>
                                <?php if (!empty($att['file_path'])): ?>
                                    <a class="btn btn-sm btn-outline-secondary" href="<?= URLROOT . '/' . ltrim($att['file_path'], '/') ?>" target="_blank">
                                        <i class="bi bi-download"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Unavailable</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <?php if ($data['can_edit']): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <form id="quickUpdateForm">
                        <input type="hidden" name="ticket_id" value="<?= $data['ticket']['id'] ?>">
                        
                        <div class="mb-3">
                            <label for="quick_status" class="form-label">Status</label>
                            <select class="form-select" id="quick_status" name="status_id" onchange="updateTicketStatus()">
                                <?php foreach ($data['statuses'] as $status): ?>
                                    <option value="<?= $status['id'] ?>" 
                                            <?= $data['ticket']['status_id'] == $status['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($status['display_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <?php if ($data['can_assign']): ?>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignModal">
                                    <i class="bi bi-person-plus me-2"></i>Assign Ticket
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($data['can_close'] && !$data['ticket']['is_closed']): ?>
                                <button type="button" class="btn btn-outline-success" onclick="closeTicket()">
                                    <i class="bi bi-check-circle me-2"></i>Mark as Resolved
                                </button>
                            <?php endif; ?>


                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Ticket Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>Ticket Information
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt class="small text-muted">Age</dt>
                        <dd><?= $data['ticket']['age_hours'] ?> hours</dd>
                        
                        <dt class="small text-muted">Messages</dt>
                        <dd><?= count($data['messages']) ?></dd>
                        
                        <?php if (!empty($data['ticket']['tags'])): ?>
                            <dt class="small text-muted">Tags</dt>
                            <dd>
                                <?php foreach (explode(',', $data['ticket']['tags']) as $tag): ?>
                                    <span class="badge bg-secondary me-1"><?= htmlspecialchars(trim($tag)) ?></span>
                                <?php endforeach; ?>
                            </dd>
                        <?php endif; ?>
                        
                        <?php if (!empty($data['ticket']['inbound_email_address'])): ?>
                            <dt class="small text-muted">Original Email</dt>
                            <dd>
                                <small class="text-muted">
                                    <i class="bi bi-envelope me-1"></i>
                                    <?= htmlspecialchars($data['ticket']['inbound_email_address']) ?>
                                </small>
                            </dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <!-- SLA Information -->
            <?php if (!empty($data['sla_status'])): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-clock me-2"></i>SLA Status
                        <?php if ($data['sla_status']['response_breached'] || $data['sla_status']['resolution_breached']): ?>
                            <span class="badge bg-danger ms-2">Breached</span>
                        <?php endif; ?>
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt class="small text-muted">Priority</dt>
                        <dd>
                            <span class="badge" style="background-color: <?= $data['ticket']['priority_color'] ?>">
                                <?= ucfirst($data['sla_status']['priority']) ?>
                            </span>
                        </dd>
                        
                        <dt class="small text-muted">Response Target</dt>
                        <dd>
                            <?php if (!empty($data['sla_status']['response_deadline'])): ?>
                                <?php if ($data['sla_status']['response_breached']): ?>
                                    <span class="text-danger">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Breached
                                    </span>
                                <?php elseif (!empty($data['sla_status']['first_response_at'])): ?>
                                    <span class="text-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Met
                                    </span>
                                <?php elseif (isset($data['sla_status']['response_time_remaining_hours'])): ?>
                                    <?php if ($data['sla_status']['response_time_remaining_hours'] < 0): ?>
                                        <span class="text-danger">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            Overdue
                                        </span>
                                    <?php elseif ($data['sla_status']['response_time_remaining_hours'] < 2): ?>
                                        <span class="text-warning">
                                            <i class="bi bi-clock me-1"></i>
                                            <?= round($data['sla_status']['response_time_remaining_hours'], 1) ?>h left
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            <?= round($data['sla_status']['response_time_remaining_hours'], 1) ?>h left
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Not set</span>
                            <?php endif; ?>
                        </dd>
                        
                        <dt class="small text-muted">Resolution Target</dt>
                        <dd>
                            <?php if (!empty($data['sla_status']['resolution_deadline'])): ?>
                                <?php if ($data['sla_status']['resolution_breached']): ?>
                                    <span class="text-danger">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Breached
                                    </span>
                                <?php elseif (!empty($data['sla_status']['resolved_at'])): ?>
                                    <span class="text-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Met
                                    </span>
                                <?php elseif (isset($data['sla_status']['resolution_time_remaining_hours'])): ?>
                                    <?php if ($data['sla_status']['resolution_time_remaining_hours'] < 0): ?>
                                        <span class="text-danger">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            Overdue
                                        </span>
                                    <?php elseif ($data['sla_status']['resolution_time_remaining_hours'] < 4): ?>
                                        <span class="text-warning">
                                            <i class="bi bi-clock me-1"></i>
                                            <?= round($data['sla_status']['resolution_time_remaining_hours'], 1) ?>h left
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            <?= round($data['sla_status']['resolution_time_remaining_hours'], 1) ?>h left
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Not set</span>
                            <?php endif; ?>
                        </dd>
                        
                        <?php if (!empty($data['sla_status']['response_deadline'])): ?>
                            <dt class="small text-muted">Response Deadline</dt>
                            <dd>
                                <small class="text-muted">
                                    <?= date('M j, Y g:i A', strtotime($data['sla_status']['response_deadline'])) ?>
                                </small>
                            </dd>
                        <?php endif; ?>
                        
                        <?php if (!empty($data['sla_status']['resolution_deadline'])): ?>
                            <dt class="small text-muted">Resolution Deadline</dt>
                            <dd>
                                <small class="text-muted">
                                    <?= date('M j, Y g:i A', strtotime($data['sla_status']['resolution_deadline'])) ?>
                                </small>
                            </dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
            <?php endif; ?>

            <!-- Activity Summary -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-activity me-2"></i>Activity Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Comments</span>
                        <span class="badge bg-primary">
                            <?= count(array_filter($data['messages'], function($m) { return $m['message_type'] === 'comment'; })) ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Email Messages</span>
                        <span class="badge bg-info">
                            <?= count(array_filter($data['messages'], function($m) { return in_array($m['message_type'], ['email_inbound', 'email_outbound']); })) ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">System Updates</span>
                        <span class="badge bg-secondary">
                            <?= count(array_filter($data['messages'], function($m) { return $m['is_system_message']; })) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quill (basic rich text editor) -->
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
(() => {
    const editorEl = document.getElementById('replyEditor');
    const textarea = document.getElementById('message');
    if (!editorEl || !textarea) return;

    const quill = new Quill('#replyEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                [{'header': [1, 2, 3, false]}],
                [{'list': 'ordered'}, {'list': 'bullet'}],
                ['blockquote', 'code-block'],
                ['link'],
                ['clean']
            ]
        }
    });

    const form = textarea.closest('form');
    if (!form) return;

    form.addEventListener('submit', (e) => {
        const html = quill.root.innerHTML || '';
        const text = (quill.getText() || '').trim();

        // If editor is visually empty, block submit with a clear message
        if (!text) {
            e.preventDefault();
            alert('Message content is required.');
            return;
        }

        textarea.value = html;
    });
})();
</script>

<script>
// AJAX fragment refresh + auto attachment fetch (no full page reload)
(() => {
    const pendingCard = document.getElementById('pendingAttachmentsCard');
    const status = document.getElementById('fetchAttachmentsStatus');
    const ticketId = pendingCard ? pendingCard.getAttribute('data-ticket-id') : null;

    const setStatus = (msg, cls) => {
        if (!status) return;
        status.className = 'small ' + (cls || '');
        status.textContent = msg || '';
    };

    const reinitThreadSplitter = () => {
        const emailMessages = document.querySelectorAll('.timeline-item .html-content, .timeline-item .plain-content');
        emailMessages.forEach((container, containerIdx) => {
            const html = container.innerHTML;
            const headerPattern = /<div[^>]*>\s*<b>\s*(From|Sent|Date):\s*<\/b>.*?<b>\s*(To|Date|Sent):\s*<\/b>.*?<b>\s*Subject:\s*<\/b>.*?<\/div>/i;
            const match = headerPattern.exec(html);
            if (!match) return;
            const splitIndex = match.index;
            const newContent = html.slice(0, splitIndex);
            const oldContent = html.slice(splitIndex);
            const collapseId = 'emailHistory_' + containerIdx + '_' + Math.floor(Math.random() * 10000);
            const toggleHtml = `
                <div class="email-history-toggle mt-3">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false">
                        <i class="bi bi-clock-history me-1"></i> Show previous message history
                    </button>
                </div>
                <div class="collapse mt-3" id="${collapseId}">
                    <div class="email-history-content p-3 border rounded bg-light text-muted small">
                        <div class="email-reply-divider mb-3" style="border-top: 2px solid #cbd5e1; padding-top: 0.5rem; color: #64748b;">
                            <i class="bi bi-reply me-2"></i>Previous message in thread:
                        </div>
                        ${oldContent}
                    </div>
                </div>
            `;
            container.innerHTML = newContent + toggleHtml;
        });
    };

    const refreshFragments = async () => {
        if (!ticketId) return;
        try {
            const res = await fetch(`<?= URLROOT ?>/tickets/fragment/${ticketId}`, { headers: { 'Accept': 'application/json' }});
            const data = await res.json();
            if (!data.ok) return;
            if (data.conversation) {
                const conv = document.getElementById('conversationCard');
                if (conv) {
                    const tmp = document.createElement('div');
                    tmp.innerHTML = data.conversation;
                    const newConv = tmp.firstElementChild;
                    if (newConv) conv.replaceWith(newConv);
                }
            }
            if (data.sidebar) {
                const pendingWrap = document.getElementById('pendingCardWrapper');
                const attachWrap = document.getElementById('attachmentsCardWrapper');
                const tmp = document.createElement('div');
                tmp.innerHTML = data.sidebar;
                const newPending = tmp.querySelector('#pendingCardWrapper');
                const newAttach = tmp.querySelector('#attachmentsCardWrapper');
                if (pendingWrap && newPending) pendingWrap.replaceWith(newPending);
                if (attachWrap && newAttach) attachWrap.replaceWith(newAttach);
            }
            reinitThreadSplitter();
        } catch (e) {
            console.warn('Fragment refresh failed', e);
        }
    };

    const pollPending = async (attempt = 0) => {
        if (!ticketId) return;
        try {
            const res = await fetch(`<?= URLROOT ?>/tickets/fragment/${ticketId}?mode=status`, { headers: { 'Accept': 'application/json' }});
            const data = await res.json();
            if (!data.ok) return;
            const pending = parseInt(data.pending || 0);
            if (pending <= 0) {
                await refreshFragments();
                setStatus('', '');
                return;
            }
            if (attempt < 8) { // up to ~16s
                setTimeout(() => pollPending(attempt + 1), 2000);
            } else {
                setStatus('Still downloading attachments in background…', 'text-muted');
            }
        } catch (e) {
            console.warn('Poll failed', e);
        }
    };

    const triggerDownload = async (isAuto = false) => {
        if (!ticketId) return;
        if (status) setStatus('Downloading attachments in background…', 'text-muted');
        try {
            const res = await fetch(`<?= URLROOT ?>/tickets/kickoffAttachments/${ticketId}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json().catch(() => null);
            if (!res.ok || !data || data.ok !== true) {
                setStatus('Could not start attachment download.', 'text-danger');
                return;
            }
            if (data.started) {
                pollPending(0);
            } else {
                setStatus('No pending attachments.', 'text-muted');
            }
        } catch (e) {
            setStatus('Could not start attachment download.', 'text-danger');
        }
    };

    // Auto-trigger on load if there are pending attachments
    if (pendingCard) {
        const pendingCount = parseInt(pendingCard.getAttribute('data-pending-count') || '0');
        if (pendingCount > 0) {
            setTimeout(() => triggerDownload(true), 300);
        }
    }
})();
</script>

<!-- Assignment Modal -->
<?php if ($data['can_assign']): ?>
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= URLROOT ?>/tickets/assign/<?= $data['ticket']['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="assignee_ids" class="form-label">Assign to User</label>
                        <select class="form-select" id="assignee_ids" name="assignee_ids[]" multiple>
                            <?php foreach ($data['users'] as $user): ?>
                                <?php
                                    $displayName = trim($user['full_name'] ?? '')
                                        ?: trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))
                                        ?: ($user['name'] ?? ($user['username'] ?? ('User #' . ($user['id'] ?? ''))));
                                ?>
                                <option value="<?= $user['id'] ?>" 
                                        <?= $data['ticket']['assigned_to'] == $user['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($displayName) ?>
                                    <?php if (!empty($user['email'])): ?> (<?= htmlspecialchars($user['email']) ?>)<?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Hold Ctrl to select multiple users</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function updateTicketStatus() {
    const form = document.getElementById('quickUpdateForm');
    const formData = new FormData(form);
    
    console.log('Sending request to:', '<?= URLROOT ?>/tickets/updateStatus');
    console.log('Form data:', Object.fromEntries(formData));
    
    fetch('<?= URLROOT ?>/tickets/updateStatus', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        return response.text().then(text => {
            console.log('Raw response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                throw new Error('Invalid JSON response: ' + text);
            }
        });
    })
    .then(data => {
        console.log('Parsed data:', data);
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to update status: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update status: ' + error.message);
    });
}

function closeTicket() {
    if (confirm('Are you sure you want to mark this ticket as resolved?')) {
        const statusSelect = document.getElementById('quick_status');
        // Find the 'resolved' status option
        for (let option of statusSelect.options) {
            if (option.text.toLowerCase().includes('resolved')) {
                statusSelect.value = option.value;
                updateTicketStatus();
                break;
            }
        }
    }
}

// Auto-resize textarea
document.getElementById('message').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});
</script>

<script>
// Email thread splitter: insert visual dividers at email header boundaries and collapse history
(() => {
    const emailMessages = document.querySelectorAll('.timeline-item .html-content, .timeline-item .plain-content');
    
    emailMessages.forEach((container, containerIdx) => {
        const html = container.innerHTML;
        
        // Detect Outlook/Gmail style header blocks (From:, Sent:/Date:, To:, Subject:)
        // Pattern: <b>From:</b> ... <b>Sent:</b> ... <b>To:</b> ... <b>Subject:</b>
        const headerPattern = /<div[^>]*>\s*<b>\s*(From|Sent|Date):\s*<\/b>.*?<b>\s*(To|Date|Sent):\s*<\/b>.*?<b>\s*Subject:\s*<\/b>.*?<\/div>/i;
        
        const match = headerPattern.exec(html);
        
        if (!match) return; // No thread detected
        
        // Split content at the first header block
        const splitIndex = match.index;
        const newContent = html.slice(0, splitIndex);
        const oldContent = html.slice(splitIndex);
        
        const collapseId = 'emailHistory_' + containerIdx + '_' + Math.floor(Math.random() * 10000);
        
        const toggleHtml = `
            <div class="email-history-toggle mt-3">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false">
                    <i class="bi bi-clock-history me-1"></i> Show previous message history
                </button>
            </div>
            <div class="collapse mt-3" id="${collapseId}">
                <div class="email-history-content p-3 border rounded bg-light text-muted small">
                    <div class="email-reply-divider mb-3" style="border-top: 2px solid #cbd5e1; padding-top: 0.5rem; color: #64748b;">
                        <i class="bi bi-reply me-2"></i>Previous message in thread:
                    </div>
                    ${oldContent}
                </div>
            </div>
        `;
        
        container.innerHTML = newContent + toggleHtml;
    });
})();
</script>

<!-- Styles moved to /public/css/app.css -->