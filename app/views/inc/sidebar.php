<!-- Time Tracking -->
<div class="sb-sidenav-menu-heading">Time Tracking</div>
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