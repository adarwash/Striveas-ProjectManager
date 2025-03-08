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
        
        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])): ?>
        <!-- Admin Menu -->
        <div class="menu-category">Admin</div>
        <ul class="nav flex-column">
            <?php if ($_SESSION['role'] === 'admin'): ?>
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
            <?php endif; ?>
            <li class="nav-item">
                <a href="/employees" class="nav-link <?= ($_SESSION['page'] ?? '') == 'employees' ? 'active' : '' ?>">
                    <i class="bi bi-person-badge"></i>
                    <span>Employee Management</span>
                </a>
            </li>
        </ul>
        <?php endif; ?>
    </div>
    
    <!-- Team Section -->
    <div class="team-section">
        <div class="menu-category">Team</div>
        <?php 
        // Get team members from the database
        $teamMembers = get_sidebar_team_members_direct(3); // Get up to 3 team members
        
        if (!empty($teamMembers)):
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
        <?php 
            endforeach; 
        else:
        ?>
        <div class="p-2 text-muted small">No team members found</div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div class="text-center mt-2 mb-3">
            <a href="/employees" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-people"></i> Manage Team
            </a>
        </div>
        <?php endif; ?>
        
        <!-- User Profile Section -->
        <div class="border-top border-secondary-subtle my-3"></div>
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
            <a href="/profile" class="btn btn-light">
                <i class="bi bi-person-circle me-2"></i>My Profile
            </a>
            <a href="/auth/logout" class="btn btn-light">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </a>
        </div>
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