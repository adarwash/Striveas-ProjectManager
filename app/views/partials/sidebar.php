<!-- Sidebar structure based on modern dashboard design -->
<div class="sidebar">
    <!-- Logo/Brand Section -->
    <div class="sidebar-header">
        <a href="/" class="sidebar-brand">
            <i class="bi bi-kanban"></i>
            <span>ProjectTracker</span>
        </a>
    </div>
    
    <!-- Navigation Menu -->
    <div class="sidebar-menu">
        <!-- Main Menu -->
        <div class="menu-category">Menu</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="/dashboard" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/dashboard' || $_SERVER['REQUEST_URI'] === '/dashboard/' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/projects" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/projects' || $_SERVER['REQUEST_URI'] === '/projects/' || strpos($_SERVER['REQUEST_URI'], '/projects/') === 0 ? 'active' : '' ?>">
                    <i class="bi bi-kanban"></i>
                    <span>Projects</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/tasks" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/tasks' || $_SERVER['REQUEST_URI'] === '/tasks/' || strpos($_SERVER['REQUEST_URI'], '/tasks/') === 0 ? 'active' : '' ?>">
                    <i class="bi bi-check2-square"></i>
                    <span>Tasks</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/notes" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/notes' || $_SERVER['REQUEST_URI'] === '/notes/' || strpos($_SERVER['REQUEST_URI'], '/notes/') === 0 ? 'active' : '' ?>">
                    <i class="bi bi-journal-text"></i>
                    <span>Notes</span>
                </a>
            </li>
            
            <!-- Time Tracking Activities -->
            <li class="nav-item">
                <a href="/activities" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/activities' || $_SERVER['REQUEST_URI'] === '/activities/' || strpos($_SERVER['REQUEST_URI'], '/activities/') === 0 ? 'active' : '' ?>">
                    <i class="bi bi-clock"></i>
                    <span>Daily Activities</span>
                </a>
            </li>
            
            <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])): ?>
            <li class="nav-item">
                <a href="/activities/manage" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/activities/manage' || $_SERVER['REQUEST_URI'] === '/activities/manage/' ? 'active' : '' ?>">
                    <i class="bi bi-clipboard-check"></i>
                    <span>Manage Activities</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/activities/report" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/activities/report' || $_SERVER['REQUEST_URI'] === '/activities/report/' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>Activity Reports</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a href="/invoices" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/invoices' || $_SERVER['REQUEST_URI'] === '/invoices/' || strpos($_SERVER['REQUEST_URI'], '/invoices/') === 0 ? 'active' : '' ?>">
                    <i class="bi bi-receipt"></i>
                    <span>Invoices</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/suppliers" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/suppliers' || $_SERVER['REQUEST_URI'] === '/suppliers/' || strpos($_SERVER['REQUEST_URI'], '/suppliers/') === 0 ? 'active' : '' ?>">
                    <i class="bi bi-shop"></i>
                    <span>Suppliers</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/departments" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/departments' || $_SERVER['REQUEST_URI'] === '/departments/' || strpos($_SERVER['REQUEST_URI'], '/departments/') === 0 ? 'active' : '' ?>">
                    <i class="bi bi-building"></i>
                    <span>Departments</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/sites" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/sites' || $_SERVER['REQUEST_URI'] === '/sites/' || strpos($_SERVER['REQUEST_URI'], '/sites/') === 0 ? 'active' : '' ?>">
                    <i class="bi bi-geo-alt"></i>
                    <span>Sites</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/dashboard/calendar" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/dashboard/calendar' || $_SERVER['REQUEST_URI'] === '/dashboard/calendar/' ? 'active' : '' ?>">
                    <i class="bi bi-calendar3"></i>
                    <span>Calendar</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/dashboard/gantt" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/dashboard/gantt' || $_SERVER['REQUEST_URI'] === '/dashboard/gantt/' ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart"></i>
                    <span>Gantt Chart</span>
                </a>
            </li>
        </ul>
        
        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])): ?>
        <!-- Admin Menu -->
        <div class="menu-category">Admin</div>
        <ul class="nav flex-column">
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <li class="nav-item">
                <a href="/admin" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/admin' || $_SERVER['REQUEST_URI'] === '/admin/' ? 'active' : '' ?>">
                    <i class="bi bi-shield-lock"></i>
                    <span>Admin Panel</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/users" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/admin/users' || $_SERVER['REQUEST_URI'] === '/admin/users/' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/settings" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/admin/settings' || $_SERVER['REQUEST_URI'] === '/admin/settings/' ? 'active' : '' ?>">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a href="/employees" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/employees' || $_SERVER['REQUEST_URI'] === '/employees/' || strpos($_SERVER['REQUEST_URI'], '/employees/') === 0 ? 'active' : '' ?>">
                    <i class="bi bi-person-badge"></i>
                    <span>Employee Management</span>
                </a>
            </li>
        </ul>
        <?php endif; ?>
    </div>
    
    <!-- User Profile Section -->
    <div class="team-section">
        <div class="menu-category">Account</div>
        <div class="team-member">
            <div class="team-member-avatar">
                <?php
                // Get profile picture or default
                $profilePic = '/uploads/profile_pictures/' . ($_SESSION['profile_picture'] ?? 'default.png');
                if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $profilePic) || empty($_SESSION['profile_picture'])) {
                    // Show initial if no profile picture
                    echo substr($_SESSION['user_name'] ?? 'U', 0, 1);
                } else {
                    echo '<img src="' . $profilePic . '" alt="Profile" class="img-fluid rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">';
                }
                ?>
            </div>
            <div class="team-member-info">
                <div class="team-member-name"><?= $_SESSION['user_name'] ?? 'User' ?></div>
                <div class="team-member-title"><?= $_SESSION['role'] ?? 'User' ?></div>
            </div>
        </div>
        
        <!-- Profile and Logout Buttons -->
        <div class="d-grid gap-2 mt-3">
            <a href="/profile" class="btn btn-light <?= $_SERVER['REQUEST_URI'] === '/profile' || $_SERVER['REQUEST_URI'] === '/profile/' || strpos($_SERVER['REQUEST_URI'], '/profile/') === 0 ? 'active' : '' ?>">
                <i class="bi bi-person-circle me-2"></i>My Profile
            </a>
            <a href="/auth/logout" class="btn btn-light">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </a>
        </div>
    </div>
</div>

<!-- Add consistent styling for the sidebar -->
<style>
/* Main content margin */
.main-content {
    margin-left: 250px !important;
    padding: 20px !important;
}

/* Consistent sidebar link styling */
.sidebar .nav-link {
    display: flex !important;
    align-items: center !important;
    padding: 0.75rem 1rem !important;
    color: var(--dark-color) !important;
    text-decoration: none !important;
    border-radius: 0.375rem !important;
    transition: all 0.2s !important;
    font-weight: 500 !important;
    gap: 0.75rem !important;
    margin: 0.25rem 0.75rem !important;
    font-size: 0.95rem !important;
}

/* Hover style */
.sidebar .nav-link:hover {
    background-color: var(--primary-light) !important;
    color: var(--primary-color) !important;
}

/* Active style */
.sidebar .nav-link.active {
    background-color: var(--primary-light) !important;
    color: var(--primary-color) !important;
    font-weight: 600 !important;
}

/* Icon styling */
.sidebar .nav-link i {
    font-size: 1.25rem !important;
    width: 1.5rem !important;
    text-align: center !important;
}

/* Menu category styling */
.sidebar .menu-category {
    text-transform: uppercase !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    color: var(--text-muted) !important;
    margin: 1rem 1.5rem 0.5rem !important;
}
</style> 