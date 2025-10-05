<?php
// Include PermissionHelper for menu access control
require_once __DIR__ . '/../../core/PermissionHelper.php';

// Get accessible menu items based on user permissions
$menuItems = PermissionHelper::getAccessibleMenuItems();
?>

<!-- Sidebar structure based on modern dashboard design -->
<div class="sidebar">
    <!-- Logo/Brand Section -->
    <div class="sidebar-header">
        <a href="/" class="sidebar-brand">
            <i class="fas fa-layer-group"></i>
            <span><?php echo SITENAME; ?></span>
        </a>
    </div>
    
    <!-- Navigation Menu -->
    <div class="sidebar-menu">
        <!-- Main Menu -->
        <div class="menu-category">Menu</div>
        <ul class="nav flex-column">
            <?php foreach ($menuItems['main'] as $item): ?>
            <li class="nav-item">
                <a href="<?= $item['url'] ?>" class="nav-link <?= ($_SERVER['REQUEST_URI'] === $item['url'] || $_SERVER['REQUEST_URI'] === $item['url'] . '/' || strpos($_SERVER['REQUEST_URI'], $item['url'] . '/') === 0) ? 'active' : '' ?>">
                    <i class="<?= $item['icon'] ?>"></i>
                    <span><?= $item['title'] ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        
        <?php if (!empty($menuItems['admin'])): ?>
        <!-- Admin Menu -->
        <div class="menu-category">Admin</div>
        <ul class="nav flex-column">
            <?php foreach ($menuItems['admin'] as $item): ?>
            <li class="nav-item">
                <a href="<?= $item['url'] ?>" class="nav-link <?= ($_SERVER['REQUEST_URI'] === $item['url'] || $_SERVER['REQUEST_URI'] === $item['url'] . '/' || strpos($_SERVER['REQUEST_URI'], $item['url'] . '/') === 0) ? 'active' : '' ?>">
                    <i class="<?= $item['icon'] ?>"></i>
                    <span><?= $item['title'] ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
    
    <!-- Time Tracking Status Widget -->
    <?php
    // Check settings to determine if the time status widget should be shown
    $showTimeStatusWidget = true;
    try {
        require_once __DIR__ . '/../../models/Setting.php';
        if (class_exists('Setting')) {
            $settingModelForSidebar = new Setting();
            $showTimeStatusWidget = (bool)$settingModelForSidebar->get('show_sidebar_time_status', true);
        }
    } catch (Exception $e) {
        error_log('Sidebar settings check error: ' . $e->getMessage());
        $showTimeStatusWidget = true; // fail open to avoid breaking UX
    }
    ?>
    <?php if ($showTimeStatusWidget): ?>
    <div class="time-tracking-widget">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php
            try {
                // Include TimeTracking model if it exists
                $timeTrackingPath = __DIR__ . '/../../models/TimeTracking.php';
                if (file_exists($timeTrackingPath)) {
                    require_once $timeTrackingPath;
                }
                
                // Check if class exists and database is available before using it
                if (class_exists('TimeTracking') && defined('DB1')) {
                    $timeModel = new TimeTracking();
                    $userStatus = $timeModel->getUserStatus($_SESSION['user_id']);
                    $todaySummary = $timeModel->getDailySummary($_SESSION['user_id']);
                } else {
                    $userStatus = null;
                    $todaySummary = null;
                }
            } catch (Exception $e) {
                // Gracefully handle any errors
                error_log('TimeTracking Widget Error: ' . $e->getMessage());
                $userStatus = null;
                $todaySummary = null;
            }
            ?>
            
            <?php if ($userStatus): ?>
            <div class="time-status-widget">
                <div class="widget-header">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="widget-title">
                            <i class="fas fa-clock-four"></i>
                            <span>Time Status</span>
                        </div>
                        <div class="current-time" id="sidebarCurrentTime"><?php echo date('H:i'); ?></div>
                    </div>
                    
                    <!-- Status Indicator -->
                    <div class="status-display">
                        <?php if ($userStatus['status'] === 'clocked_in'): ?>
                            <div class="status-card working">
                                <div class="status-icon">
                                    <i class="fas fa-play-circle"></i>
                                </div>
                                <div class="status-info">
                                    <div class="status-label">Currently Working</div>
                                    <div class="status-time">
                                        Since <?php echo date('H:i', strtotime($userStatus['clock_in_time'] ?? 'now')); ?>
                                    </div>
                                    <?php if (!empty($userStatus['site_name'])): ?>
                                        <div class="status-site">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?= htmlspecialchars($userStatus['site_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php elseif ($userStatus['status'] === 'on_break'): ?>
                            <div class="status-card on-break">
                                <div class="status-icon">
                                    <i class="fas fa-pause-circle"></i>
                                </div>
                                <div class="status-info">
                                    <div class="status-label">On Break</div>
                                    <div class="status-time">
                                        Since <?php echo date('H:i', strtotime($userStatus['break_start_time'] ?? 'now')); ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="status-card offline">
                                <div class="status-icon">
                                    <i class="fas fa-stop-circle"></i>
                                </div>
                                <div class="status-info">
                                    <div class="status-label">Clocked Out</div>
                                    <div class="status-time">Ready to start</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Time Statistics -->
                    <div class="time-stats">
                        <div class="stat-item">
                            <div class="stat-label">Today's Hours</div>
                            <div class="stat-value"><?php echo $todaySummary ? number_format($todaySummary['total_hours'] ?? 0, 1) : '0.0'; ?>h</div>
                        </div>
                        <?php if ($userStatus['status'] !== 'clocked_out'): ?>
                        <div class="stat-item">
                            <div class="stat-label">Session Time</div>
                            <div class="stat-value" id="sidebarSessionTime">
                                <?php 
                                $minutes = $userStatus['elapsed_work_time'] ?? 0;
                                echo sprintf('%dh %02dm', floor($minutes / 60), $minutes % 60);
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="widget-actions">
                        <?php if ($userStatus['status'] === 'clocked_out'): ?>
                            <!-- Work Location Selection -->
                            <div class="location-selection mb-2">
                                <label for="sidebarClockInSite" class="form-label small">
                                    <i class="fas fa-building me-1"></i>Work Location
                                </label>
                                <select class="form-select form-select-sm" id="sidebarClockInSite">
                                    <option value="">Loading sites...</option>
                                </select>
                            </div>
                            
                            <button class="action-btn primary" onclick="sidebarClockIn()">
                                <i class="fas fa-play"></i>
                                <span>Clock In</span>
                            </button>
                        <?php elseif ($userStatus['status'] === 'clocked_in'): ?>
                            <button class="action-btn warning" onclick="sidebarStartBreak()">
                                <i class="fas fa-coffee"></i>
                                <span>Take Break</span>
                            </button>
                            <button class="action-btn danger" onclick="sidebarClockOut()">
                                <i class="fas fa-stop"></i>
                                <span>Clock Out</span>
                            </button>
                        <?php elseif ($userStatus['status'] === 'on_break'): ?>
                            <button class="action-btn success" onclick="sidebarEndBreak()">
                                <i class="fas fa-play"></i>
                                <span>End Break</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- User Profile Section -->
    <div class="team-section">
        <div class="menu-category">Account</div>
        <div class="team-member">
            <div class="team-member-avatar">
                <?php
                // Get profile picture or default
                $profilePic = '/uploads/profile_pictures/' . ($_SESSION['profile_picture'] ?? 'default.png');
                if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $profilePic) || empty($_SESSION['profile_picture'])) {
                    // Show default profile image using SVG
                    $initial = strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1));
                    $svgData = 'data:image/svg+xml;base64,' . base64_encode('
                        <svg width="40" height="40" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="20" cy="20" r="20" fill="#3498db"/>
                            <text x="20" y="28" font-family="Arial, sans-serif" font-size="16" font-weight="bold" fill="white" text-anchor="middle">' . $initial . '</text>
                        </svg>
                    ');
                    echo '<img src="' . $svgData . '" alt="Default Profile" class="img-fluid rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">';
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

/* Navigation list styling - Remove dots */
.sidebar .nav {
    list-style: none !important;
    padding-left: 0 !important;
    margin-bottom: 0 !important;
}

.sidebar .nav-item {
    list-style: none !important;
}

/* Sidebar general styling */
.sidebar {
    width: 250px;
    height: 100vh;
    background: #ffffff;
    border-right: 1px solid rgba(0,0,0,0.08);
    overflow-y: auto;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1000;
}

.sidebar-header {
    padding: 1rem;
    border-bottom: 1px solid rgba(0,0,0,0.08);
}

.sidebar-brand {
    display: flex !important;
    align-items: center !important;
    gap: 0.75rem !important;
    text-decoration: none !important;
    color: #2c3e50 !important;
    font-weight: 600 !important;
    font-size: 1.1rem !important;
}

.sidebar-brand i {
    font-size: 1.5rem !important;
    color: #3498db !important;
}

.sidebar-menu {
    padding: 1rem 0;
}

/* Consistent sidebar link styling */
.sidebar .nav-link {
    display: flex !important;
    align-items: center !important;
    padding: 0.75rem 1rem !important;
    color: #2c3e50 !important;
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
    background-color: rgba(52, 152, 219, 0.1) !important;
    color: #3498db !important;
}

/* Modern Time Status Widget Styles */
.time-tracking-widget {
    margin-bottom: -45px;
    border-top: 1px solid rgba(0,0,0,0.08);
    padding-top: 1rem;
}

.time-status-widget {
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fc 100%);
    border-radius: 12px;
    padding: 1.25rem;
    border: 1px solid rgba(0,0,0,0.06);
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.time-status-widget:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}

.widget-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.95rem;
}

.widget-title i {
    color: #3498db;
    font-size: 1.1rem;
}

.current-time {
    font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
    font-weight: 700;
    color: #3498db;
    font-size: 1rem;
    background: rgba(52, 152, 219, 0.1);
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
}

/* Status Display */
.status-display {
    margin: 0.75rem 0;
}

.status-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.status-card.working {
    background: linear-gradient(135deg, rgba(46, 204, 113, 0.1) 0%, rgba(39, 174, 96, 0.15) 100%);
    border: 1px solid rgba(46, 204, 113, 0.2);
}

.status-card.on-break {
    background: linear-gradient(135deg, rgba(241, 196, 15, 0.1) 0%, rgba(243, 156, 18, 0.15) 100%);
    border: 1px solid rgba(241, 196, 15, 0.2);
}

.status-card.offline {
    background: linear-gradient(135deg, rgba(149, 165, 166, 0.1) 0%, rgba(127, 140, 141, 0.15) 100%);
    border: 1px solid rgba(149, 165, 166, 0.2);
}

.status-icon {
    font-size: 1.5rem;
    line-height: 1;
}

.status-card.working .status-icon {
    color: #27ae60;
}

.status-card.on-break .status-icon {
    color: #f39c12;
}

.status-card.offline .status-icon {
    color: #95a5a6;
}

.status-info {
    flex: 1;
}

.status-label {
    font-weight: 600;
    font-size: 0.9rem;
    color: #2c3e50;
    margin-bottom: 0.125rem;
}

.status-time {
    font-size: 0.75rem;
    color: #7f8c8d;
    font-weight: 500;
}

.status-site {
    font-size: 0.7rem;
    color: #5a6c7d;
    font-weight: 500;
    margin-top: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.status-site i {
    font-size: 0.7rem;
}

/* Time Statistics */
.time-stats {
    display: flex;
    gap: 1rem;
    margin: 1rem 0;
    padding: 0.75rem;
    background: rgba(52, 152, 219, 0.05);
    border-radius: 8px;
    border: 1px solid rgba(52, 152, 219, 0.1);
}

.stat-item {
    flex: 1;
    text-align: center;
}

.stat-label {
    font-size: 0.7rem;
    color: #7f8c8d;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.stat-value {
    font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
    font-weight: 700;
    color: #2c3e50;
    font-size: 0.95rem;
}

/* Widget Actions */
.widget-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.action-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 0.75rem;
    border: none;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.action-btn i {
    font-size: 0.9rem;
}

.action-btn.primary {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
}

.action-btn.primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

.action-btn.success {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(46, 204, 113, 0.3);
}

.action-btn.success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(46, 204, 113, 0.4);
}

.action-btn.warning {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(243, 156, 18, 0.3);
}

.action-btn.warning:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(243, 156, 18, 0.4);
}

.action-btn.danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
}

.action-btn.danger:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
/* Active style */
.sidebar .nav-link.active {
    background-color: rgba(52, 152, 219, 0.15) !important;
    color: #3498db !important;
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
    color: #7f8c8d !important;
    margin: 1rem 1.5rem 0.5rem !important;
}

/* Time tracking widget location selection */
.time-tracking-widget .location-selection {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 12px;
}

.time-tracking-widget .location-selection .form-label {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 6px;
}

.time-tracking-widget .form-select-sm {
    background-color: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #333;
    font-size: 0.8rem;
    padding: 0.375rem 0.75rem;
}

.time-tracking-widget .form-select-sm:focus {
    background-color: #fff;
    border-color: #6a5acd;
    box-shadow: 0 0 0 0.2rem rgba(106, 90, 205, 0.25);
}
</style>

<script>
// Sidebar Time Tracking Widget JavaScript
let sidebarUpdateInterval = null;

// Update sidebar time display
function updateSidebarTime() {
    const timeElement = document.getElementById('sidebarCurrentTime');
    if (timeElement) {
        const now = new Date();
        timeElement.textContent = now.toLocaleTimeString('en-US', { 
            hour12: false, 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }
}

// Update sidebar session timer
function updateSidebarSession() {
    const sessionElement = document.getElementById('sidebarSessionTime');
    if (sessionElement) {
        fetch('/time/getStatus')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Time tracking not available');
                }
                return response.json();
            })
            .then(data => {
                if (data.status !== 'clocked_out' && data.elapsed_work_time) {
                    const minutes = data.elapsed_work_time;
                    const hours = Math.floor(minutes / 60);
                    const mins = minutes % 60;
                    sessionElement.textContent = hours + 'h ' + String(mins).padStart(2, '0') + 'm';
                }
            })
            .catch(error => {
                // Silently handle errors when time tracking is not available
                console.log('Time tracking system not available:', error.message);
            });
    }
}

// Load available sites for sidebar clock-in
function loadSidebarUserSites() {
    const siteSelect = document.getElementById('sidebarClockInSite');
    if (!siteSelect) return;
    
    fetch('/time/getUserSites')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.sites) {
                siteSelect.innerHTML = '<option value="">Select work location...</option>';
                
                data.sites.forEach(site => {
                    const option = document.createElement('option');
                    option.value = site.id;
                    option.textContent = `${site.name}${site.location ? ' - ' + site.location : ''}`;
                    siteSelect.appendChild(option);
                });
                
                // Auto-select first site if only one available
                if (data.sites.length === 1) {
                    siteSelect.value = data.sites[0].id;
                }
            } else {
                siteSelect.innerHTML = '<option value="">No sites available</option>';
            }
        })
        .catch(error => {
            console.error('Error loading sites:', error);
            siteSelect.innerHTML = '<option value="">Error loading sites</option>';
        });
}

// Sidebar clock in function
function sidebarClockIn() {
    const siteSelect = document.getElementById('sidebarClockInSite');
    const siteId = siteSelect ? siteSelect.value : '';
    
    if (!siteId) {
        showSidebarNotification('error', 'Please select a work location');
        return;
    }
    
    fetch('/time/clockIn', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `site_id=${encodeURIComponent(siteId)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Time tracking system not available');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showSidebarNotification('success', 'Clocked in successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showSidebarNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Sidebar clock in error:', error);
        if (error.message.includes('not available')) {
            showSidebarNotification('error', 'Time tracking system not available');
        } else {
            showSidebarNotification('error', 'An error occurred');
        }
    });
}

// Sidebar clock out function
function sidebarClockOut() {
    if (confirm('Are you sure you want to clock out?')) {
        fetch('/time/clockOut', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: ''
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Time tracking system not available');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showSidebarNotification('success', 'Clocked out successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showSidebarNotification('error', data.message);
            }
        })
        .catch(error => {
            console.error('Sidebar clock out error:', error);
            if (error.message.includes('not available')) {
                showSidebarNotification('error', 'Time tracking system not available');
            } else {
                showSidebarNotification('error', 'An error occurred');
            }
        });
    }
}

// Start break from sidebar
function sidebarStartBreak() {
    fetch('/time/startBreak', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'break_type=regular'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Time tracking system not available');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showSidebarNotification('success', 'Break started');
            setTimeout(() => location.reload(), 1000);
        } else {
            showSidebarNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Sidebar start break error:', error);
        if (error.message.includes('not available')) {
            showSidebarNotification('error', 'Time tracking system not available');
        } else {
            showSidebarNotification('error', 'An error occurred');
        }
    });
}

// End break from sidebar
function sidebarEndBreak() {
    fetch('/time/endBreak', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: ''
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Time tracking system not available');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showSidebarNotification('success', 'Break ended');
            setTimeout(() => location.reload(), 1000);
        } else {
            showSidebarNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Sidebar end break error:', error);
        if (error.message.includes('not available')) {
            showSidebarNotification('error', 'Time tracking system not available');
        } else {
            showSidebarNotification('error', 'An error occurred');
        }
    });
}

// Show sidebar notification
function showSidebarNotification(type, message) {
    // Create a small toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px; font-size: 0.9rem;';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 3000);
}

// Initialize sidebar time tracking
document.addEventListener('DOMContentLoaded', function() {
    // Update time every second
    updateSidebarTime();
    setInterval(updateSidebarTime, 1000);
    
    // Update session timer every minute
    updateSidebarSession();
    sidebarUpdateInterval = setInterval(updateSidebarSession, 60000);
    
    // Load available sites for clock-in
    loadSidebarUserSites();
});

// Clean up intervals when page unloads
window.addEventListener('beforeunload', function() {
    if (sidebarUpdateInterval) {
        clearInterval(sidebarUpdateInterval);
    }
});
</script> 