<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'HiveITPortal' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-light: #e0e7ff;
            --secondary-color: #8b5cf6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
            --border-color: #e2e8f0;
            --text-muted: #94a3b8;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 70px;
            --header-height: 60px;
            --card-border-radius: 1rem;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f3f4;
            color: #1e293b;
            min-height: 100vh;
        }
        
        /* Layout */
        .app-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Modern Gradient Sidebar - Exact match to image */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #6a5acd 0%, #4b0082 50%, #800080 100%);
            border: none;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
        }
        
        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .sidebar-brand i {
            color: #fbbf24;
            font-size: 1.75rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .sidebar-menu {
            padding: 1.5rem 0;
            flex-grow: 1;
            overflow-y: auto;
        }
        
        .menu-category {
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            margin: 0.5rem 1.5rem 0.75rem;
            letter-spacing: 0.5px;
        }
        
        .menu-category:first-child {
            margin-top: 0;
        }
        
        .nav-item {
            margin: 0.125rem 1rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            font-weight: 500;
            gap: 0.875rem;
            font-size: 0.925rem;
        }
        
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .nav-link i {
            font-size: 1.25rem;
            width: 1.5rem;
            text-align: center;
            opacity: 0.9;
        }
        
        /* Team section */
        .team-section {
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
        }
        
        .team-member {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            gap: 1rem;
        }
        
        .team-member-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.875rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .team-member-info {
            overflow: hidden;
        }
        
        .team-member-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: white;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .team-member-title {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.7);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-transform: capitalize;
        }
        
        /* Main content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
            min-height: 100vh;
            background: #f1f3f4;
        }
        
        .content-wrapper {
            padding: 2rem;
        }
        
        /* Page header */
        .page-header {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 1.5rem 2rem;
            border-radius: var(--card-border-radius);
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
        }
        
        .page-title i {
            color: #6a5acd;
            margin-right: 0.75rem;
        }
        
        /* Modern Dashboard Cards */
        .stats-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border: none;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
            height: 140px;
        }
        
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }
        
        .stats-card.purple {
            background: linear-gradient(135deg, #6a5acd 0%, #9370db 100%);
            color: white;
        }
        
        .stats-card.green {
            background: linear-gradient(135deg, #00c9a7 0%, #00b894 100%);
            color: white;
        }
        
        .stats-card.orange {
            background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
            color: white;
        }
        
        .stats-card.blue {
            background: linear-gradient(135deg, #00cec9 0%, #0984e3 100%);
            color: white;
        }
        
        .stats-card.red {
            background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
            color: white;
        }
        
        .stats-card .stats-icon {
            font-size: 2.5rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }
        
        .stats-card .stats-number {
            font-size: 2.75rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
            line-height: 1;
        }
        
        .stats-card .stats-label {
            font-size: 0.9rem;
            opacity: 0.95;
            font-weight: 500;
            line-height: 1.2;
        }
        
        /* Card improvements */
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            background: white;
        }
        
        .card:hover {
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        
        .card-header {
            background: white !important;
            border-bottom: 1px solid var(--border-color);
            border-radius: 1rem 1rem 0 0 !important;
            padding: 1.5rem 2rem;
        }
        
        .card-body {
            padding: 1.5rem 2rem;
        }
        
        /* Button improvements */
        .btn {
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            padding: 0.5rem 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6a5acd 0%, #9370db 100%);
            box-shadow: 0 4px 15px rgba(106, 90, 205, 0.3);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(106, 90, 205, 0.4);
            background: linear-gradient(135deg, #5a4fcf 0%, #8a5cf6 100%);
        }
        
        .btn-outline-primary {
            border: 1px solid #6a5acd;
            color: #6a5acd;
            background: transparent;
        }
        
        .btn-outline-primary:hover {
            background: #6a5acd;
            color: white;
            transform: translateY(-1px);
        }
        
        /* Notification improvements */
        .alert {
            border: none;
            border-radius: var(--card-border-radius);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        /* Badge improvements */
        .badge {
            font-weight: 500;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
        }
        
        /* Activity List Styling */
        .activity-list {
            max-height: 450px;
            overflow-y: auto;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 1.25rem 0;
            border-bottom: 1px solid var(--border-color);
            gap: 1rem;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
            text-transform: capitalize;
        }
        
        .activity-description {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        
        .activity-time {
            color: var(--text-muted);
            font-size: 0.8rem;
        }
        
        /* Client List Styling */
        .client-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .client-item:last-child {
            border-bottom: none;
        }
        
        .client-info {
            flex: 1;
        }
        
        .client-name {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
        }
        
        .client-projects {
            color: var(--text-muted);
            font-size: 0.875rem;
        }
        
        .client-badge .badge {
            font-size: 0.875rem;
        }
        
        /* Time Tracking Widget Styling */
        .time-tracking-widget {
            padding: 1rem 0.3rem;
            margin-top: auto;
        }
        
        .time-status-widget {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .widget-title {
            color: white;
            font-size: 0.875rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .current-time {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-card {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .status-card.working {
            background: rgba(34, 197, 94, 0.2);
        }
        
        .status-card.on-break {
            background: rgba(245, 158, 11, 0.2);
        }
        
        .status-card.offline {
            background: rgba(107, 114, 128, 0.2);
        }
        
        .status-display {
            margin-bottom: 1rem;
        }
        
        .status-icon {
            margin-right: 0.5rem;
        }
        
        .status-info {
            flex: 1;
        }
        
        .status-label {
            font-weight: 600;
            color: white;
            font-size: 0.875rem;
        }
        
        .status-time {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.75rem;
        }
        
        .time-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.75rem;
            margin-bottom: 0.25rem;
        }
        
        .stat-value {
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .widget-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .action-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .action-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }
        
        .action-btn.primary {
            background: rgba(34, 197, 94, 0.3);
            border-color: rgba(34, 197, 94, 0.5);
        }
        
        .action-btn.warning {
            background: rgba(245, 158, 11, 0.3);
            border-color: rgba(245, 158, 11, 0.5);
        }
        
        .action-btn.danger {
            background: rgba(239, 68, 68, 0.3);
            border-color: rgba(239, 68, 68, 0.5);
        }
        
        .action-btn.success {
            background: rgba(34, 197, 94, 0.3);
            border-color: rgba(34, 197, 94, 0.5);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .content-wrapper {
                padding: 1rem;
            }
            
            .stats-card .stats-number {
                font-size: 2rem;
            }
            
            .stats-card .stats-icon {
                font-size: 2rem;
            }
        }
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
    </script>
</body>
</html> 