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
                <a href="/dashboard" class="nav-link <?= ($_SESSION['page'] ?? '') == 'home' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/projects" class="nav-link <?= ($_SESSION['page'] ?? '') == 'projects' ? 'active' : '' ?>">
                    <i class="bi bi-kanban"></i>
                    <span>Projects</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/tasks" class="nav-link <?= ($_SESSION['page'] ?? '') == 'tasks' ? 'active' : '' ?>">
                    <i class="bi bi-check2-square"></i>
                    <span>Tasks</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/notes" class="nav-link <?= ($_SESSION['page'] ?? '') == 'notes' ? 'active' : '' ?>">
                    <i class="bi bi-journal-text"></i>
                    <span>Notes</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/departments" class="nav-link <?= ($_SESSION['page'] ?? '') == 'departments' ? 'active' : '' ?>">
                    <i class="bi bi-building"></i>
                    <span>Departments</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/dashboard/calendar" class="nav-link <?= ($_SESSION['page'] ?? '') == 'dashboard_calendar' ? 'active' : '' ?>">
                    <i class="bi bi-calendar3"></i>
                    <span>Calendar</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/dashboard/gantt" class="nav-link <?= ($_SESSION['page'] ?? '') == 'dashboard_gantt' ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart"></i>
                    <span>Gantt Chart</span>
                </a>
            </li>
        </ul>
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <!-- Admin Menu -->
        <div class="menu-category">Admin</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="/admin" class="nav-link <?= ($_SESSION['page'] ?? '') == 'admin' ? 'active' : '' ?>">
                    <i class="bi bi-shield-lock"></i>
                    <span>Admin Panel</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/users" class="nav-link <?= ($_SESSION['page'] ?? '') == 'admin_users' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/admin/settings" class="nav-link <?= ($_SESSION['page'] ?? '') == 'admin_settings' ? 'active' : '' ?>">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
        <?php endif; ?>
    </div>
    
    <!-- Team Section -->
    <div class="team-section">
        <div class="menu-category">Team</div>
        <?php 
        // This is a placeholder - in a real application, you would fetch team members from the database
        $teamMembers = [
            ['name' => 'Alex Morgan', 'title' => 'Project Manager', 'initial' => 'AM'],
            ['name' => 'Sam Wilson', 'title' => 'Developer', 'initial' => 'SW'],
            ['name' => 'Taylor Lee', 'title' => 'Designer', 'initial' => 'TL'],
        ];
        
        foreach ($teamMembers as $member): 
        ?>
        <div class="team-member">
            <div class="team-member-avatar">
                <?= $member['initial'] ?>
            </div>
            <div class="team-member-info">
                <div class="team-member-name"><?= $member['name'] ?></div>
                <div class="team-member-title"><?= $member['title'] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- User Profile Section -->
        <div class="border-top border-secondary-subtle my-3"></div>
        <div class="team-member">
            <div class="team-member-avatar">
                <?= substr($_SESSION['user_name'] ?? 'U', 0, 1) ?>
            </div>
            <div class="team-member-info">
                <div class="team-member-name"><?= $_SESSION['user_name'] ?? 'User' ?></div>
                <div class="team-member-title"><?= $_SESSION['role'] ?? 'User' ?></div>
            </div>
        </div>
        
        <!-- Logout Button -->
        <a href="/auth/logout" class="btn btn-light w-100 mt-3">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
        </a>
    </div>
</div>

<!-- Add margin to main content to accommodate sidebar -->
<style>
.main-content {
    margin-left: 250px;
    padding: 20px;
}

.sidebar .nav-link {
    padding: 0.5rem 1rem;
    font-size: 0.95rem;
    border-radius: 0.25rem;
    margin: 0.2rem 0.8rem;
}

.sidebar .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.sidebar .nav-link.active {
    font-weight: 500;
}

.sidebar-heading {
    font-size: 0.75rem;
    text-transform: uppercase;
}
</style> 