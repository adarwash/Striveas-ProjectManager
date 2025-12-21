<?php require VIEWSPATH . '/customer/inc/header.php'; ?>

<style>
.ticket-header {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    position: relative;
    overflow: hidden;
}

.ticket-header::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 5px;
    height: 100%;
    background: <?= htmlspecialchars($data['ticket']['priority_color'] ?? '#6c757d') ?>;
}

.ticket-meta {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.meta-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.meta-item:last-child {
    border-bottom: none;
}

.meta-label {
    font-weight: 500;
    color: #6c757d;
    font-size: 0.9rem;
}

.meta-value {
    font-weight: 600;
    color: #495057;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.priority-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.conversation-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.conversation-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 1.5rem;
}

.message-item {
    border-bottom: 1px solid #f1f3f4;
    padding: 1.5rem;
    transition: background-color 0.3s ease;
}

.message-item:hover {
    background-color: #f8f9fa;
}

.message-item:last-child {
    border-bottom: none;
}

.message-header {
    display: flex;
    justify-content: between;
    align-items: flex-start;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.message-author {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.author-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
}

.author-info h6 {
    margin: 0;
    font-weight: 600;
    color: #495057;
}

.author-info small {
    color: #6c757d;
}

.message-date {
    color: #6c757d;
    font-size: 0.85rem;
}

.message-content {
    line-height: 1.6;
    color: #495057;
    overflow-x: auto;
}

.message-content p {
    margin-bottom: 1rem;
}

.message-content p:last-child {
    margin-bottom: 0;
}

.message-type-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.system-message {
    background: #f8f9fa;
    border-left: 4px solid #17a2b8;
    font-style: italic;
}

.email-message {
    border-left: 4px solid #28a745;
}

.internal-note {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
}

.breadcrumb-custom {
    background: none;
    padding: 0;
    margin-bottom: 1rem;
}

.breadcrumb-custom .breadcrumb-item a {
    color: #667eea;
    text-decoration: none;
}

.breadcrumb-custom .breadcrumb-item a:hover {
    color: #764ba2;
    text-decoration: underline;
}

.empty-conversation {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}
</style>

<div class="container-fluid px-4">
    <?php
        // Normalize full HTML email documents into safe fragments for embedding (customer view)
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
    ?>
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="fade-in-up">
        <ol class="breadcrumb breadcrumb-custom">
            <li class="breadcrumb-item">
                <a href="<?= URLROOT ?>/customer/dashboard">
                    <i class="bi bi-house me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= URLROOT ?>/customer/tickets">My Tickets</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= htmlspecialchars($data['ticket']['ticket_number']) ?>
            </li>
        </ol>
    </nav>

    <!-- Ticket Header -->
    <div class="ticket-header fade-in-up" style="animation-delay: 0.1s;">
        <div class="row align-items-start">
            <div class="col-lg-8">
                <div class="d-flex align-items-center mb-3">
                    <h4 class="me-3 mb-0"><?= htmlspecialchars($data['ticket']['ticket_number']) ?></h4>
                    <span class="status-badge me-2" 
                          style="background-color: <?= htmlspecialchars($data['ticket']['status_color']) ?>; color: white;">
                        <?= htmlspecialchars($data['ticket']['status_display']) ?>
                    </span>
                    <span class="priority-badge" 
                          style="background-color: <?= htmlspecialchars($data['ticket']['priority_color']) ?>20; color: <?= htmlspecialchars($data['ticket']['priority_color']) ?>;">
                        <?= htmlspecialchars($data['ticket']['priority_display']) ?> Priority
                    </span>
                </div>
                <h2 class="mb-3"><?= htmlspecialchars($data['ticket']['subject']) ?></h2>
                
                <?php if (!empty($data['ticket']['description'])): ?>
                    <div class="ticket-description">
                        <h6 class="text-muted mb-2">Description:</h6>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($data['ticket']['description'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="ticket-meta">
                    <div class="meta-item">
                        <span class="meta-label">Created</span>
                        <span class="meta-value"><?= date('M j, Y g:i A', strtotime($data['ticket']['created_at'])) ?></span>
                    </div>
                    
                    <?php if ($data['ticket']['updated_at'] !== $data['ticket']['created_at']): ?>
                        <div class="meta-item">
                            <span class="meta-label">Last Updated</span>
                            <span class="meta-value"><?= date('M j, Y g:i A', strtotime($data['ticket']['updated_at'])) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($data['ticket']['due_date']): ?>
                        <div class="meta-item">
                            <span class="meta-label">Due Date</span>
                            <span class="meta-value"><?= date('M j, Y', strtotime($data['ticket']['due_date'])) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($data['ticket']['resolved_at']): ?>
                        <div class="meta-item">
                            <span class="meta-label">Resolved</span>
                            <span class="meta-value text-success"><?= date('M j, Y g:i A', strtotime($data['ticket']['resolved_at'])) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($data['ticket']['category_display']): ?>
                        <div class="meta-item">
                            <span class="meta-label">Category</span>
                            <span class="meta-value">
                                <span class="badge bg-light text-dark">
                                    <?= htmlspecialchars($data['ticket']['category_display']) ?>
                                </span>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($data['ticket']['assigned_to_name']): ?>
                        <div class="meta-item">
                            <span class="meta-label">Assigned To</span>
                            <span class="meta-value"><?= htmlspecialchars($data['ticket']['assigned_to_name']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="d-grid">
                    <a href="<?= URLROOT ?>/customer/tickets" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Tickets
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Conversation -->
    <div class="conversation-container fade-in-up" style="animation-delay: 0.2s;">
        <div class="conversation-header">
            <h5 class="mb-0">
                <i class="bi bi-chat-dots me-2"></i>Conversation History
            </h5>
            <p class="mb-0 opacity-75">All communication related to this ticket</p>
        </div>
        
        <div class="conversation-body">
            <?php if (!empty($data['messages'])): ?>
                <?php foreach ($data['messages'] as $index => $message): ?>
                    <div class="message-item <?= $message['message_type'] === 'system' ? 'system-message' : '' ?> 
                                              <?= strpos($message['message_type'], 'email') !== false ? 'email-message' : '' ?>"
                         style="animation-delay: <?= 0.3 + ($index * 0.1) ?>s;">
                        
                        <div class="message-header">
                            <div class="message-author">
                                <div class="author-avatar">
                                    <?php if (!empty($message['user_name'])): ?>
                                        <?= strtoupper(substr($message['user_name'], 0, 1)) ?>
                                    <?php elseif ($message['email_from']): ?>
                                        <?= strtoupper(substr($message['email_from'], 0, 1)) ?>
                                    <?php else: ?>
                                        <i class="bi bi-gear"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="author-info">
                                    <h6>
                                        <?php if (!empty($message['user_name'])): ?>
                                            <?= htmlspecialchars($message['user_name']) ?>
                                        <?php elseif ($message['email_from']): ?>
                                            <?= htmlspecialchars($message['email_from']) ?>
                                        <?php else: ?>
                                            System
                                        <?php endif; ?>
                                    </h6>
                                    <?php if (!empty($message['user_email']) && $message['user_email'] !== ($message['email_from'] ?? null)): ?>
                                        <small><?= htmlspecialchars($message['user_email']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($message['message_type'] !== 'comment'): ?>
                                    <span class="message-type-badge bg-info text-white">
                                        <?= ucfirst(str_replace('_', ' ', $message['message_type'])) ?>
                                    </span>
                                <?php endif; ?>
                                <span class="message-date">
                                    <?= date('M j, Y g:i A', strtotime($message['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if ($message['subject'] && $message['subject'] !== $data['ticket']['subject']): ?>
                            <div class="message-subject mb-2">
                                <strong>Subject:</strong> <?= htmlspecialchars($message['subject']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="message-content">
                            <?php if ($message['content_format'] === 'html'): ?>
                                <?php
                                    $c = $normalizeEmailHtml((string)($message['content'] ?? ''));
                                    $looksHtml = preg_match('/<\s*\w+[^>]*>/', $c) === 1;
                                    echo $looksHtml ? $c : nl2br(htmlspecialchars($c));
                                ?>
                            <?php else: ?>
                                <?= nl2br(htmlspecialchars($message['content'])) ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($message['email_to'] || $message['email_cc']): ?>
                            <div class="email-details mt-2 pt-2 border-top">
                                <small class="text-muted">
                                    <?php if ($message['email_to']): ?>
                                        <strong>To:</strong> <?= htmlspecialchars($message['email_to']) ?><br>
                                    <?php endif; ?>
                                    <?php if ($message['email_cc']): ?>
                                        <strong>CC:</strong> <?= htmlspecialchars($message['email_cc']) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-conversation">
                    <i class="bi bi-chat-square-text display-1 mb-3"></i>
                    <h5 class="mb-2">No conversation yet</h5>
                    <p class="mb-0">This ticket doesn't have any messages or updates.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Reply form -->
        <div class="p-3 border-top bg-light">
            <form id="customer-reply-form" action="<?= URLROOT ?>/customer/reply/<?= (int)$data['ticket']['id'] ?>" method="post" class="d-flex gap-2">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf_token'] ?? '') ?>">
                <textarea name="content" class="form-control" rows="2" placeholder="Type your reply..." required></textarea>
                <button type="submit" class="btn btn-primary" id="reply-btn">
                    <i class="bi bi-send me-1"></i><span class="btn-text">Send</span>
                </button>
            </form>
        </div>
    </div>
</div>

<?php require VIEWSPATH . '/customer/inc/footer.php'; ?>
