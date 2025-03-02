<?php
// Get the current controller and method to highlight active nav links
$currentController = strtolower($_SESSION['current_controller'] ?? '');
$currentMethod = strtolower($_SESSION['current_method'] ?? '');

// Function to set active class for nav items
function isActive($controller, $method = '') {
    global $currentController, $currentMethod;
    
    if ($controller === $currentController) {
        if (empty($method) || $method === $currentMethod) {
            return 'active';
        }
    }
    return '';
}

// Get username from session if logged in
$username = $_SESSION['username'] ?? '';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="/">Project Tracker</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= isActive('home') ?>" href="/home">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('projects') ?>" href="/projects">Projects</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('tasks') ?>" href="/tasks">Tasks</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('notes') ?>" href="/notes"><i class="bi bi-journal-text me-1"></i>Notes</a>
                </li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= isActive('admin') ?>" href="/admin">Admin</a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in']): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($username) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="/settings"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="/auth">Login</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> 