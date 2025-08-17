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
            padding: 0px 20px;
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
    /* color: white; */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .team-member-title {
            font-size: 0.8rem;
            /* color: rgba(255, 255, 255, 0.7); */
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
                padding: 0px 20px;
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
        
        /* Universal Search Styles */
        .search-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: none;
            padding: 0.75rem 1rem;
            position: relative;
            z-index: 10;
            box-shadow: 0 2px 16px rgba(0,0,0,0.1);
            margin: -20px;
            margin-bottom: 10px;
        }
        
        .search-container {
            max-width: 100%;
            margin: 0;
            position: relative;
            overflow: visible;
        }
        
        .universal-search {
            position: relative;
            width: 100%;
        }
        
        .search-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            transition: all 0.2s ease;
            backdrop-filter: blur(12px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .search-input-wrapper:focus-within {
            border-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 6px 25px rgba(0,0,0,0.15);
            background: white;
            transform: translateY(-1px);
        }
        
        .search-icon {
            color: #667eea;
            font-size: 1.25rem;
            margin: 0 1rem;
            transition: all 0.2s ease;
            opacity: 0.7;
        }
        
        .search-input-wrapper:focus-within .search-icon {
            color: #6366f1;
            opacity: 1;
            transform: scale(1.05);
        }
        
        .search-input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 0.75rem 0;
            font-size: 1rem;
            color: #1e293b;
            outline: none;
            font-weight: 400;
            font-family: inherit;
        }
        
        .search-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }
        
        .search-filters {
            border-left: 1px solid rgba(102, 126, 234, 0.15);
            padding-left: 1rem;
            margin-right: 0.75rem;
        }
        
        .search-type-select {
            border: none;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            outline: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .search-type-select:hover {
            background: rgba(102, 126, 234, 0.2);
            color: #6366f1;
        }
        
        .search-loading {
            padding: 0 1rem;
            color: #667eea;
        }
        
        .search-loading-state .spinner-border {
            width: 2rem;
            height: 2rem;
            border-width: 3px;
            color: #667eea !important;
        }
        
        .search-pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .search-results {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            width: 25%;
            min-width: 350px;
            background: white;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            max-height: 450px;
            overflow-y: auto;
            z-index: 1000;
            margin-top: 0.25rem;
            backdrop-filter: blur(12px);
        }
        
        .search-results-content {
            padding: 1rem 0;
        }
        
        .search-result-item {
            display: flex;
            align-items: center;
            padding: 1.25rem 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 1px solid #f7fafc;
            position: relative;
            overflow: hidden;
        }
        
        .search-result-item:hover {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            transform: translateX(8px);
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.1);
        }
        
        .search-result-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }
        
        .search-result-item:hover::before {
            transform: scaleY(1);
        }
        
        .search-result-item:last-child {
            border-bottom: none;
        }
        
        .search-result-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }
        
        .search-result-icon.type-project {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .search-result-icon.type-task {
            background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%);
            box-shadow: 0 4px 15px rgba(78, 205, 196, 0.3);
        }
        
        .search-result-icon.type-user {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            box-shadow: 0 4px 15px rgba(252, 182, 159, 0.3);
            color: #8b4513 !important;
        }
        
        .search-result-icon.type-client {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            box-shadow: 0 4px 15px rgba(168, 237, 234, 0.3);
            color: #2d3748 !important;
        }
        
        .search-result-icon.type-note {
            background: linear-gradient(135deg, #fad0c4 0%, #ffd1ff 100%);
            box-shadow: 0 4px 15px rgba(250, 208, 196, 0.3);
            color: #553c9a !important;
        }
        
        .search-result-content {
            flex: 1;
            min-width: 0;
        }
        
        .search-result-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .search-result-description {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .search-result-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.75rem;
            color: #94a3b8;
        }
        
        .search-result-status {
            background: #e2e8f0;
            color: #64748b;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-left: auto;
            flex-shrink: 0;
        }
        
        .search-no-results {
            padding: 2rem;
            text-align: center;
            color: #94a3b8;
        }
        
        .search-no-results i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        /* Keyboard shortcuts */
        .search-shortcuts {
            padding: 0.5rem 1.25rem;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            font-size: 0.75rem;
            color: #64748b;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .search-shortcut-key {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 0.125rem 0.375rem;
            font-family: monospace;
            font-size: 0.625rem;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .search-header {
                padding: 0.5rem 0.75rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                margin: -10px;
                margin-bottom: 8px;
            }
            
            .search-container {
                max-width: none;
            }
            
            .search-input-wrapper {
                border-radius: 10px;
                margin: 0 0.25rem;
            }
            
            .search-input {
                font-size: 0.9rem;
                padding: 0.625rem 0;
            }
            
            .search-icon {
                font-size: 1.1rem;
                margin: 0 0.75rem;
            }
            
            .search-filters {
                display: none;
            }
            
            .search-results {
                left: 50%;
                transform: translateX(-50%);
                width: 90%;
                min-width: 280px;
                margin-top: 0.25rem;
                border-radius: 10px;
            }
            
            .search-result-item {
                padding: 1rem;
            }
            
            .search-result-icon {
                width: 2rem;
                height: 2rem;
                font-size: 1.25rem;
            }
        }
        
        /* Fallback for browsers that don't support gradients */
        @supports not (background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)) {
            .search-header {
                background: #667eea;
            }
            
            .search-type-select {
                background: #667eea;
            }
            
            .search-result-icon.type-project {
                background: #6366f1;
            }
            
            .search-result-icon.type-task {
                background: #10b981;
            }
            
            .search-result-icon.type-user {
                background: #f59e0b;
            }
            
            .search-result-icon.type-client {
                background: #ef4444;
            }
            
            .search-result-icon.type-note {
                background: #06b6d4;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <?php require_once '../app/views/partials/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
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
            </div>
            
            <div class="content-wrapper">
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
    </script>
</body>
</html> 