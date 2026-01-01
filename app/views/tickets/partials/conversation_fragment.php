<?php
// Helpers (duplication from main view)
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

    $html = preg_replace_callback(
        '/src\\s*=\\s*(?:([\"\\\'])cid:([^\"\\\']+)\\1|cid:([^\\s>]+))/i',
        function($m) use ($cidMap) {
            $cid = $m[2] ?? ($m[3] ?? '');
            $cid = strtolower(trim((string)$cid, "<> \t\r\n"));
            if ($cid !== '' && isset($cidMap[$cid])) {
                $url = htmlspecialchars($cidMap[$cid], ENT_QUOTES);
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

$normalizeEmailHtml = function(string $html): string {
    $html = (string)$html;
    if ($html === '') return $html;
    $html = preg_replace('/<!doctype[^>]*>/i', '', $html);
    $html = preg_replace('#<\s*head[^>]*>.*?<\s*/\s*head\s*>#is', '', $html);
    $html = preg_replace('#<\s*/\s*html\s*>#is', '', $html);
    $html = preg_replace('#<\s*html[^>]*>#is', '', $html);
    $html = preg_replace('#<\s*/\s*body\s*>#is', '', $html);
    $html = preg_replace('#<\s*body[^>]*>#is', '', $html);
    $html = preg_replace('#<\s*meta[^>]*>#is', '', $html);
    return trim($html);
};

// Build messages timeline (same logic as main view)
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
                    `                    <i class="bi bi-gear"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-0">
                                            <?php if (!empty($message['username'])): ?>
                                                <?= htmlspecialchars($fullName ?? $message['username']) ?>
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
</div>
