<!-- Time Tracking -->
<div class="sb-sidenav-menu-heading">Time Tracking</div>
<a class="nav-link <?= urlIs('/time') ? 'active' : '' ?>" href="<?= URLROOT ?>/time">
    <div class="sb-nav-link-icon"><i class="fas fa-clock"></i></div>
    Clock In/Out
</a>
<a class="nav-link <?= urlIs('/time/history') ? 'active' : '' ?>" href="<?= URLROOT ?>/time/history">
    <div class="sb-nav-link-icon"><i class="fas fa-history"></i></div>
    Time History
</a>
<?php if (hasPermission('reports_read')): ?>
<a class="nav-link <?= urlIs('/time/team') ? 'active' : '' ?>" href="<?= URLROOT ?>/time/team">
    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
    Team Time
</a>
<a class="nav-link <?= urlIs('/time/reports') ? 'active' : '' ?>" href="<?= URLROOT ?>/time/reports">
    <div class="sb-nav-link-icon"><i class="fas fa-chart-bar"></i></div>
    Time Reports
</a>
<?php endif; ?>

<!-- Legacy Activities (keeping for backward compatibility) -->
<a class="nav-link <?= urlIs('/activities') ? 'active' : '' ?>" href="<?= URLROOT ?>/activities">
    <div class="sb-nav-link-icon"><i class="bi bi-clock"></i></div>
    Daily Activities
</a>
<?php if (isAdmin() || isManager()): ?>
<a class="nav-link <?= urlIs('/activities/manage') ? 'active' : '' ?>" href="<?= URLROOT ?>/activities/manage">
    <div class="sb-nav-link-icon"><i class="bi bi-clipboard-check"></i></div>
    Manage Activities
</a>
<a class="nav-link <?= urlIs('/activities/report') ? 'active' : '' ?>" href="<?= URLROOT ?>/activities/report">
    <div class="sb-nav-link-icon"><i class="bi bi-file-earmark-bar-graph"></i></div>
    Activity Reports
</a>
<?php endif; ?> 