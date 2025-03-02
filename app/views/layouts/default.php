<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'ProjectTracker' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-light: #eff6ff;
            --secondary-color: #6366f1;
            --success-color: #22c55e;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #0ea5e9;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
            --border-color: #e2e8f0;
            --text-muted: #94a3b8;
            --sidebar-width: 240px;
            --sidebar-collapsed-width: 70px;
            --header-height: 60px;
            --card-border-radius: 0.5rem;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        
        /* Layout */
        .app-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background-color: white;
            border-right: 1px solid var(--border-color);
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
        }
        
        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }
        
        .sidebar-brand {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .sidebar-brand i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
            flex-grow: 1;
            overflow-y: auto;
        }
        
        .menu-category {
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            margin: 1rem 1.5rem 0.5rem;
        }
        
        .nav-item {
            margin: 0.25rem 0.75rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--dark-color);
            text-decoration: none;
            border-radius: 0.375rem;
            transition: all 0.2s;
            font-weight: 500;
            gap: 0.75rem;
        }
        
        .nav-link:hover {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }
        
        .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .nav-link i {
            font-size: 1.25rem;
            width: 1.5rem;
            text-align: center;
        }
        
        /* Team section */
        .team-section {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        
        .team-member {
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
            gap: 0.75rem;
        }
        
        .team-member-avatar {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.75rem;
        }
        
        .team-member-info {
            overflow: hidden;
        }
        
        .team-member-name {
            font-weight: 600;
            font-size: 0.875rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .team-member-title {
            font-size: 0.75rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Main content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
            min-height: 100vh;
            background-color: #f1f5f9;
        }
        
        .content-wrapper {
            padding: 1.5rem;
        }
        
        /* Page header */
        .page-header {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        /* Cards */
        .card {
            background-color: white;
            border: none;
            border-radius: var(--card-border-radius);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            padding: 1rem 1.25rem;
            background-color: white;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
        }
        
        /* Tables */
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            font-weight: 600;
            color: var(--text-muted);
            border-bottom-width: 1px;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
        }
        
        /* Badges */
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
            border-radius: 0.25rem;
        }
        
        .badge.bg-success-light {
            background-color: rgba(34, 197, 94, 0.1);
            color: var(--success-color);
        }
        
        .badge.bg-warning-light {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }
        
        .badge.bg-danger-light {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }
        
        .badge.bg-info-light {
            background-color: rgba(14, 165, 233, 0.1);
            color: var(--info-color);
        }
        
        /* Buttons */
        .btn {
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        
        .btn-light {
            background-color: white;
            border-color: var(--border-color);
            color: var(--dark-color);
        }
        
        .btn-light:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            color: var(--dark-color);
        }
        
        /* Progress bars */
        .progress {
            height: 0.5rem;
            border-radius: 1rem;
            background-color: #e2e8f0;
        }
        
        .progress-bar {
            background-color: var(--primary-color);
        }
        
        /* Utilities */
        .text-primary { color: var(--primary-color) !important; }
        .text-success { color: var(--success-color) !important; }
        .text-warning { color: var(--warning-color) !important; }
        .text-danger { color: var(--danger-color) !important; }
        .text-muted { color: var(--text-muted) !important; }
        
        .avatar-group {
            display: flex;
            align-items: center;
        }
        
        .avatar {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            overflow: hidden;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-weight: 600;
            border: 2px solid white;
            margin-left: -0.5rem;
        }
        
        .avatar:first-child {
            margin-left: 0;
        }
        
        .avatar-count {
            background-color: #e2e8f0;
            color: var(--text-muted);
        }
        
        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: var(--sidebar-collapsed-width);
            }
            
            .main-content {
                margin-left: var(--sidebar-collapsed-width);
            }
            
            .sidebar-brand span,
            .menu-category,
            .nav-link span,
            .team-member-info {
                display: none;
            }
            
            .nav-item {
                margin: 0.25rem 0.5rem;
            }
            
            .nav-link {
                justify-content: center;
                padding: 0.75rem;
            }
            
            .nav-link i {
                margin-right: 0;
            }
            
            .team-section {
                padding: 1rem 0.5rem;
            }
            
            .team-member {
                justify-content: center;
            }
            
            .sidebar:hover {
                width: var(--sidebar-width);
            }
            
            .sidebar:hover .sidebar-brand span,
            .sidebar:hover .menu-category,
            .sidebar:hover .nav-link span,
            .sidebar:hover .team-member-info {
                display: block;
            }
            
            .sidebar:hover .nav-item {
                margin: 0.25rem 0.75rem;
            }
            
            .sidebar:hover .nav-link {
                justify-content: flex-start;
                padding: 0.75rem 1rem;
            }
            
            .sidebar:hover .team-member {
                justify-content: flex-start;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                width: var(--sidebar-width);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
        
        /* Flash messages */
        .flash-message {
            margin-bottom: 1rem;
            border-radius: var(--card-border-radius);
            border: none;
        }
        
        /* Priority indicators */
        .priority-indicator {
            display: inline-flex;
            align-items: center;
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            border-radius: 0.25rem;
        }
        
        .priority-indicator i {
            margin-right: 0.25rem;
            font-size: 0.675rem;
        }
        
        .priority-high {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }
        
        .priority-medium {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }
        
        .priority-low {
            background-color: rgba(14, 165, 233, 0.1);
            color: var(--info-color);
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <?php require_once '../app/views/partials/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="content-wrapper">
                <!-- Flash Messages -->
                <?php if (isset($_SESSION['flash_message'])) : ?>
                    <div class="alert flash-message alert-<?= $_SESSION['flash_type'] ?? 'primary' ?> alert-dismissible fade show" role="alert">
                        <?= $_SESSION['flash_message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
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
        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(message => {
            setTimeout(() => {
                const closeButton = message.querySelector('.btn-close');
                if (closeButton) {
                    closeButton.click();
                }
            }, 5000);
        });
    </script>
</body>
</html> 