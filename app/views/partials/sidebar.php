<?php
// Include PermissionHelper for menu access control
require_once __DIR__ . '/../../core/PermissionHelper.php';

// Get accessible menu items based on user permissions
$menuItems = PermissionHelper::getAccessibleMenuItems();

// Compute today's meetings count for Clients badge
$todayMeetingCount = 0;
try {
    $clientMeetingPath = __DIR__ . '/../../models/ClientMeeting.php';
    if (file_exists($clientMeetingPath)) {
        require_once $clientMeetingPath;
        if (class_exists('ClientMeeting')) {
            $cmModel = new ClientMeeting();
            if (method_exists($cmModel, 'countToday')) {
                $todayMeetingCount = (int)$cmModel->countToday();
            }
        }
    }
} catch (Exception $e) {
    error_log('Sidebar meeting count error: ' . $e->getMessage());
    $todayMeetingCount = 0;
}

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
        <ul class="nav flex-column">
            <?php foreach ($menuItems['main'] as $item): ?>
            <li class="nav-item">
                <a href="<?= $item['url'] ?>" class="nav-link <?= ($_SERVER['REQUEST_URI'] === $item['url'] || $_SERVER['REQUEST_URI'] === $item['url'] . '/' || strpos($_SERVER['REQUEST_URI'], $item['url'] . '/') === 0) ? 'active' : '' ?>">
                    <i class="<?= $item['icon'] ?>"></i>
                    <span><?= $item['title'] ?></span>
                    <?php if ($item['url'] === '/clients' && $todayMeetingCount > 0): ?>
                        <span class="badge bg-warning text-dark menu-badge"><?= $todayMeetingCount ?></span>
                    <?php endif; ?>
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
                            <circle cx="20" cy="20" r="20" fill="#6366f1"/>
                            <text x="20" y="26" font-family="Arial, sans-serif" font-size="18" font-weight="bold" fill="white" text-anchor="middle">' . $initial . '</text>
                        </svg>
                    ');
                    echo '<img src="' . $svgData . '" alt="Default Profile" class="sidebar-avatar-img">';
                } else {
                    echo '<img src="' . $profilePic . '" alt="Profile" class="sidebar-avatar-img">';
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

<!-- Sidebar styles moved to /public/css/app.css -->

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