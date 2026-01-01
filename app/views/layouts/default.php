<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? DEFAULT_TITLE ?></title>
    
    <!-- Dark Mode Detection (must run before any CSS) -->
    <script>
        // Detect and apply theme IMMEDIATELY before CSS loads
        (function() {
            // Check for saved preference first, then system preference
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Application CSS -->
    <link href="/css/app.css?v=<?= time() ?>" rel="stylesheet">
    
    <!-- Listen for system theme changes (only if user hasn't set preference) -->
    <script>
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                // Only update if user hasn't manually set a preference
                if (!localStorage.getItem('theme')) {
                    document.documentElement.setAttribute('data-bs-theme', e.matches ? 'dark' : 'light');
                }
            });
        }
    </script>
    
    <!-- Custom CSS for specific overrides -->
    
</head>
<body>
    <div class="app-container">

        
        <!-- Main Content -->
        <div class="main-content">
        <!-- Sidebar -->
        <?php require_once '../app/views/partials/sidebar.php'; ?>
            <!-- Universal Search Bar -->
            <div class="search-header">
                <div class="search-container">
                    <div class="universal-search">
                        <div class="search-input-wrapper">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" id="universalSearch" class="search-input" placeholder="Search projects, tasks, users, clients... (Ctrl+K)" autocomplete="off">
                            <div class="search-filters">
                                <select id="searchType" class="search-type-select">
                                    <option value="all">All</option>
                                    <option value="projects">Projects</option>
                                    <option value="tasks">Tasks</option>
                                    <option value="users">Users</option>
                                    <option value="clients">Clients</option>
                                    <option value="notes">Notes</option>
                                </select>
                            </div>
                            <div class="search-loading" id="searchLoading" style="display: none;">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Searching...</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Search Results Dropdown -->
                        <div class="search-results" id="searchResults" style="display: none;">
                            <div class="search-results-content">
                                <div class="search-placeholder">
                                    <div class="search-no-results">
                                        <i class="bi bi-search"></i>
                                        <div>Start typing to search...</div>
                                        <div style="font-size: 0.75rem; margin-top: 0.5rem;">Search across projects, tasks, users, clients, and notes</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                // Build notifications for logged-in user (upcoming callbacks)
                $notifCount = 0;
                $notifItems = [];
                if (function_exists('isLoggedIn') && isLoggedIn()) {
                    try {
                        if (!class_exists('Reminder')) {
                            require_once APPROOT . '/app/models/Reminder.php';
                        }
                        $remModel = new Reminder();
                        $notifItems = $remModel->getUpcomingByUser((int)($_SESSION['user_id'] ?? 0), 5);
                        if (!is_array($notifItems)) { $notifItems = []; }
                        $notifCount = count($notifItems);
                    } catch (Exception $e) {
                        $notifItems = [];
                        $notifCount = 0;
                    }
                }
                ?>
                <div class="header-actions">
                    <!-- Theme Toggle -->
                    <button class="btn btn-light me-2" type="button" id="themeToggle" title="Toggle Dark/Light Mode">
                        <i class="bi bi-sun-fill" id="themeIcon"></i>
                    </button>
                    
                    <div class="dropdown">
                        <button class="btn btn-light position-relative" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                            <i class="bi bi-bell"></i>
                            <?php if (!empty($notifCount)): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= (int)$notifCount ?>
                                <span class="visually-hidden">unread notifications</span>
                            </span>
                            <?php endif; ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-notifications shadow" aria-labelledby="notificationsDropdown">
                            <div class="px-3 py-2 border-bottom bg-light">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fw-semibold">Notifications</span>
                                    <span class="badge bg-light text-dark border"><?= (int)$notifCount ?></span>
                                </div>
                            </div>
                            <?php if (!empty($notifItems)): ?>
                                <?php
                                // Attempt to load client names and project titles
                                $clientNameCache = [];
                                $projectTitleCache = [];
                                if (!class_exists('Client')) {
                                    require_once APPROOT . '/app/models/Client.php';
                                }
                                $clientModelTmp = new Client();
                                if (!class_exists('Project')) {
                                    require_once APPROOT . '/app/models/Project.php';
                                }
                                $projectModelTmp = new Project();
                                if (!class_exists('Task')) {
                                    require_once APPROOT . '/app/models/Task.php';
                                }
                                $taskModelTmp = new Task();
                                ?>
                                <?php foreach ($notifItems as $ni): ?>
                                    <?php
                                    $type = strtolower($ni['entity_type'] ?? '');
                                    $clientId = $type === 'client' ? (int)($ni['entity_id'] ?? 0) : 0;
                                    $projectId = $type === 'project' ? (int)($ni['entity_id'] ?? 0) : 0;
                                    $taskId = $type === 'task' ? (int)($ni['entity_id'] ?? 0) : 0;
                                    $entityLabel = '';
                                    $openHref = '';
                                    
                                    if ($type === 'client' && $clientId) {
                                        $entityLabel = 'Client #' . $clientId;
                                        if (!isset($clientNameCache[$clientId])) {
                                            try {
                                                $c = $clientModelTmp->getClientById($clientId);
                                                $clientNameCache[$clientId] = $c ? ($c['name'] ?? $entityLabel) : $entityLabel;
                                            } catch (Exception $e) {
                                                $clientNameCache[$clientId] = $entityLabel;
                                            }
                                        }
                                        $entityLabel = $clientNameCache[$clientId];
                                        $openHref = '/clients/viewClient/' . (int)$clientId;
                                    } elseif ($type === 'project' && $projectId) {
                                        $entityLabel = 'Project #' . $projectId;
                                        if (!isset($projectTitleCache[$projectId])) {
                                            try {
                                                $p = $projectModelTmp->getProjectById($projectId);
                                                $projectTitleCache[$projectId] = $p ? ($p->title ?? $entityLabel) : $entityLabel;
                                            } catch (Exception $e) {
                                                $projectTitleCache[$projectId] = $entityLabel;
                                            }
                                        }
                                        $entityLabel = $projectTitleCache[$projectId];
                                        $openHref = '/projects/viewProject/' . (int)$projectId;
                                    } elseif ($type === 'task' && $taskId) {
                                        $entityLabel = 'Task #' . $taskId;
                                        try {
                                            $t = $taskModelTmp->getTaskById($taskId);
                                            if ($t && !empty($t->title)) {
                                                $entityLabel = $t->title;
                                            }
                                        } catch (Exception $e) { /* ignore */ }
                                        $openHref = '/tasks/show/' . (int)$taskId;
                                    }
                                    $remindAt = !empty($ni['remind_at']) ? date('M j, Y g:i A', strtotime($ni['remind_at'])) : '';
                                    $title = htmlspecialchars($ni['title'] ?? 'Callback');
                                    ?>
                                    <div class="notification-item">
                                        <div class="d-flex">
                                            <div class="me-2">
                                                <span class="badge rounded-circle bg-primary" style="width:10px;height:10px;">&nbsp;</span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="notification-title text-truncate" title="<?= $title ?>"><?= $title ?></div>
                                                <div class="notification-meta">
                                                    <i class="bi <?= $type === 'project' ? 'bi-kanban' : ($type === 'task' ? 'bi-list-task' : 'bi-person-badge') ?> me-1"></i><?= htmlspecialchars($entityLabel) ?>
                                                    <?php if ($remindAt): ?> • <i class="bi bi-clock ms-1 me-1"></i><?= $remindAt ?><?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="ms-2 d-flex align-items-center">
                                                <a class="btn btn-sm btn-outline-success" href="<?=
                                                    $type === 'project' ? '/projects/completeCallback/' . (int)$ni['id'] :
                                                    ($type === 'task' ? '/tasks/completeCallback/' . (int)$ni['id'] :
                                                    '/clients/completeCallback/' . (int)$ni['id']) ?>" title="Mark Completed">
                                                    <i class="bi bi-check2-circle"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="mt-2 d-flex gap-2">
                                            <?php if (!empty($openHref)): ?>
                                            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($openHref) ?>"><?= $type === 'project' ? 'Open Project' : ($type === 'task' ? 'Open Task' : 'Open Client') ?></a>
                                            <?php endif; ?>
                                            <?php if (!empty($ni['notes'])): ?>
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#cb-notes-<?= (int)$ni['id'] ?>" aria-expanded="false" aria-controls="cb-notes-<?= (int)$ni['id'] ?>">
                                                Notes
                                            </button>
                                            <div class="collapse w-100" id="cb-notes-<?= (int)$ni['id'] ?>">
                                                <div class="card card-body border-0 p-2 mt-1" style="background:#f8fafc;">
                                                    <div class="small text-muted"><?= nl2br(htmlspecialchars($ni['notes'])) ?></div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="p-3 text-center text-muted">
                                    <i class="bi bi-bell-slash" style="font-size:1.25rem;"></i>
                                    <div class="small mt-1">No upcoming follow-ups</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="content-wrapper">
                <?php if (isset($_SESSION['original_user'])): ?>
                    <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                You are currently impersonating <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>.
                            </div>
                            <a href="<?= URLROOT ?>/admin/stopImpersonating" class="btn btn-sm btn-warning fw-bold">
                                <i class="bi bi-arrow-return-left me-1"></i> Return to Admin
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                <!-- Flash Messages (supports both legacy and new helper) -->
                <?php if (isset($_SESSION['flash_message'])) : ?>
                    <div class="alert flash-message alert-<?= $_SESSION['flash_type'] ?? 'primary' ?> alert-dismissible fade show" role="alert">
                        <?= $_SESSION['flash_message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                <?php endif; ?>
                <?php if (!empty($_SESSION['flash_messages']) && is_array($_SESSION['flash_messages'])): ?>
                    <?php foreach ($_SESSION['flash_messages'] as $name => $flash): ?>
                        <div class="alert flash-message <?= htmlspecialchars($flash['class'] ?? 'alert-primary') ?> alert-dismissible fade show" role="alert">
                            <?= $flash['message'] ?? '' ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['flash_messages']); ?>
                <?php endif; ?>
                
                <!-- Main Content -->
                <?= $content ?>
            </div>
        </div>
    </div>
    
    <!-- Mobile Toggle Button -->
    <button class="btn btn-primary d-md-none position-fixed" id="sidebarToggle" 
            style="bottom: 20px; right: 20px; z-index: 1050; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
        <i class="bi bi-list"></i>
    </button>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Mobile sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            if (sidebar.classList.contains('show') && 
                !sidebar.contains(event.target) && 
                event.target !== sidebarToggle) {
                sidebar.classList.remove('show');
            }
        });
        
        // Close alerts after 5 seconds
        if (typeof flashMessagesInitialized === 'undefined') {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(message => {
                setTimeout(() => {
                    const closeButton = message.querySelector('.btn-close');
                    if (closeButton) {
                        closeButton.click();
                    }
                }, 5000);
            });
            
            // Mark as initialized to prevent duplicate execution
            var flashMessagesInitialized = true;
        }
        
        // Universal Search Functionality
        let searchTimeout;
        let currentSearchQuery = '';
        
        const searchInput = document.getElementById('universalSearch');
        const searchType = document.getElementById('searchType');
        const searchResults = document.getElementById('searchResults');
        const searchLoading = document.getElementById('searchLoading');
        
        if (searchInput) {
            // Handle search input
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                currentSearchQuery = query;
                
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    hideSearchResults();
                    return;
                }
                
                // Show loading state
                showSearchLoading();
                
                // Debounce search
                searchTimeout = setTimeout(() => {
                    performSearch(query, searchType.value);
                }, 300);
            });
            
            // Handle search type change
            searchType.addEventListener('change', function() {
                if (currentSearchQuery.length >= 2) {
                    showSearchLoading();
                    performSearch(currentSearchQuery, this.value);
                }
            });
            
            // Handle keyboard navigation
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    hideSearchResults();
                    this.blur();
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    focusNextResult();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    focusPrevResult();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    clickFocusedResult();
                }
            });
            
            // Handle focus events
            searchInput.addEventListener('focus', function() {
                if (currentSearchQuery.length >= 2) {
                    showSearchResults();
                } else {
                    showSearchPlaceholder();
                }
            });
        }
        
        // Close search results when clicking outside
        document.addEventListener('click', function(event) {
            const searchContainer = document.querySelector('.search-container');
            if (searchContainer && !searchContainer.contains(event.target)) {
                hideSearchResults();
            }
        });
        
        // Search functionality
        function performSearch(query, type) {
            const searchUrl = `/search?q=${encodeURIComponent(query)}&type=${encodeURIComponent(type)}&limit=10`;
            console.log('Performing search:', { query, type, url: searchUrl });
            
            fetch(searchUrl)
                .then(response => {
                    console.log('Search response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Search response data:', data);
                    hideSearchLoading();
                    if (data.success) {
                        displaySearchResults(data.results, data.permissions);
                    } else {
                        displaySearchError(data.message || 'Search failed', data.error);
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                    hideSearchLoading();
                    displaySearchError(`Network error: ${error.message}`);
                });
        }
        
        function displaySearchResults(results, permissions) {
            const resultsContent = searchResults.querySelector('.search-results-content');
            
            if (results.length === 0) {
                let noResultsMessage = 'Try different keywords or filters';
                
                // Check if no results due to permissions
                if (permissions) {
                    const hasAnyPermission = Object.values(permissions).some(p => p === true);
                    if (!hasAnyPermission) {
                        noResultsMessage = 'You may not have permission to search the available content types';
                    }
                }
                
                resultsContent.innerHTML = `
                    <div class="search-no-results">
                        <i class="bi bi-search"></i>
                        <div>No results found</div>
                        <div style="font-size: 0.75rem; margin-top: 0.5rem;">${noResultsMessage}</div>
                    </div>
                `;
            } else {
                let html = '';
                results.forEach(result => {
                    const metaItems = Object.entries(result.meta || {})
                        .map(([key, value]) => `<span>${key}: ${value}</span>`)
                        .join('');
                    
                    html += `
                        <div class="search-result-item" onclick="navigateToResult('${result.url}')">
                            <div class="search-result-icon type-${result.type}">
                                <i class="${result.icon}"></i>
                            </div>
                            <div class="search-result-content">
                                <div class="search-result-title">${escapeHtml(result.title)}</div>
                                ${result.description ? `<div class="search-result-description">${escapeHtml(result.description)}</div>` : ''}
                                ${metaItems ? `<div class="search-result-meta">${metaItems}</div>` : ''}
                            </div>
                            <div class="search-result-status">${result.status}</div>
                        </div>
                    `;
                });
                
                // Add keyboard shortcuts hint
                html += `
                    <div class="search-shortcuts">
                        <span>Use <span class="search-shortcut-key">↑</span> <span class="search-shortcut-key">↓</span> to navigate, <span class="search-shortcut-key">Enter</span> to select</span>
                        <span><span class="search-shortcut-key">Esc</span> to close</span>
                    </div>
                `;
                
                resultsContent.innerHTML = html;
            }
            
            showSearchResults();
        }
        
        function displaySearchError(message, errorType) {
            const resultsContent = searchResults.querySelector('.search-results-content');
            let icon = 'bi bi-exclamation-triangle';
            let title = 'Search Error';
            
            // Handle specific error types
            if (errorType === 'insufficient_permissions') {
                icon = 'bi bi-shield-exclamation';
                title = 'Access Restricted';
                message = 'You do not have permission to search this content. Contact your administrator if you need access.';
            }
            
            resultsContent.innerHTML = `
                <div class="search-no-results">
                    <i class="${icon}"></i>
                    <div>${title}</div>
                    <div style="font-size: 0.75rem; margin-top: 0.5rem;">${escapeHtml(message)}</div>
                </div>
            `;
            showSearchResults();
        }
        
        function showSearchResults() {
            searchResults.style.display = 'block';
        }
        
        function hideSearchResults() {
            searchResults.style.display = 'none';
        }
        
        function showSearchPlaceholder() {
            const resultsContent = searchResults.querySelector('.search-results-content');
            resultsContent.innerHTML = `
                <div class="search-placeholder">
                    <div class="search-no-results">
                        <i class="bi bi-search"></i>
                        <div>Start typing to search...</div>
                        <div style="font-size: 0.75rem; margin-top: 0.5rem;">Search across projects, tasks, users, clients, and notes</div>
                    </div>
                </div>
            `;
            showSearchResults();
        }
        
        function showSearchLoading() {
            searchLoading.style.display = 'block';
            const resultsContent = searchResults.querySelector('.search-results-content');
            resultsContent.innerHTML = `
                <div class="search-loading-state" style="padding: 2rem; text-align: center;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Searching...</span>
                    </div>
                    <div style="margin-top: 1rem; color: #64748b;">Searching...</div>
                </div>
            `;
            showSearchResults();
        }
        
        function hideSearchLoading() {
            searchLoading.style.display = 'none';
        }
        
        function navigateToResult(url) {
            window.location.href = url;
        }
        
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        
        // Keyboard navigation for search results
        let focusedResultIndex = -1;
        
        function focusNextResult() {
            const items = searchResults.querySelectorAll('.search-result-item');
            if (items.length === 0) return;
            
            focusedResultIndex = Math.min(focusedResultIndex + 1, items.length - 1);
            updateFocusedResult(items);
        }
        
        function focusPrevResult() {
            const items = searchResults.querySelectorAll('.search-result-item');
            if (items.length === 0) return;
            
            focusedResultIndex = Math.max(focusedResultIndex - 1, 0);
            updateFocusedResult(items);
        }
        
        function updateFocusedResult(items) {
            items.forEach((item, index) => {
                if (index === focusedResultIndex) {
                    item.style.background = '#f8fafc';
                    item.style.transform = 'translateX(4px)';
                } else {
                    item.style.background = '';
                    item.style.transform = '';
                }
            });
        }
        
        function clickFocusedResult() {
            const items = searchResults.querySelectorAll('.search-result-item');
            if (items[focusedResultIndex]) {
                items[focusedResultIndex].click();
            }
        }
        
        // Global keyboard shortcut (Ctrl+K or Cmd+K)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
        });
        
        // Theme Toggle Functionality
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        
        if (themeToggle && themeIcon) {
            // Set initial icon based on current theme
            function updateThemeIcon() {
                const currentTheme = document.documentElement.getAttribute('data-bs-theme');
                if (currentTheme === 'dark') {
                    themeIcon.classList.remove('bi-sun-fill');
                    themeIcon.classList.add('bi-moon-fill');
                } else {
                    themeIcon.classList.remove('bi-moon-fill');
                    themeIcon.classList.add('bi-sun-fill');
                }
            }
            
            updateThemeIcon();
            
            // Toggle theme on button click
            themeToggle.addEventListener('click', function() {
                const currentTheme = document.documentElement.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                // Update theme
                document.documentElement.setAttribute('data-bs-theme', newTheme);
                
                // Save preference
                localStorage.setItem('theme', newTheme);
                
                // Update icon
                updateThemeIcon();
            });
        }
    </script>
</body>
</html> 