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
            
        </ul>
        
        <!-- Time Tracking Menu -->
        <div class="menu-category">Time Tracking</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="/time" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/time' || $_SERVER['REQUEST_URI'] === '/time/' || strpos($_SERVER['REQUEST_URI'], '/time/') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-clock"></i>
                    <span>Clock In/Out</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/time/history" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/time/history' || $_SERVER['REQUEST_URI'] === '/time/history/' ? 'active' : '' ?>">
                    <i class="fas fa-history"></i>
                    <span>Time History</span>
                </a>
            </li>
            
            <?php if (function_exists('hasPermission') && hasPermission('reports_read')): ?>
            <li class="nav-item">
                <a href="/time/team" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/time/team' || $_SERVER['REQUEST_URI'] === '/time/team/' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i>
                    <span>Team Time</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/time/reports" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/time/reports' || $_SERVER['REQUEST_URI'] === '/time/reports/' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Time Reports</span>
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Legacy Activities (for backward compatibility) -->
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
            <li class="nav-item">
                <a href="/permissions" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/permissions' || $_SERVER['REQUEST_URI'] === '/permissions/' || strpos($_SERVER['REQUEST_URI'], '/permissions/') === 0 ? 'active' : '' ?>">
                    <i class="bi bi-shield-check"></i>
                    <span>Permissions</span>
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
    
    <!-- Time Tracking Status Widget -->
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
            <div class="widget-card">
                <div class="widget-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-clock me-2"></i>Time Status
                        </h6>
                        <span class="widget-time" id="sidebarCurrentTime"><?php echo date('H:i'); ?></span>
                    </div>
                </div>
                <div class="widget-body">
                    <div class="status-indicator">
                        <?php if ($userStatus['status'] === 'clocked_in'): ?>
                            <div class="status-badge status-working">
                                <i class="fas fa-play"></i>
                                <span>Working</span>
                            </div>
                        <?php elseif ($userStatus['status'] === 'on_break'): ?>
                            <div class="status-badge status-break">
                                <i class="fas fa-pause"></i>
                                <span>On Break</span>
                            </div>
                        <?php else: ?>
                            <div class="status-badge status-offline">
                                <i class="fas fa-stop"></i>
                                <span>Clocked Out</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="time-summary">
                        <div class="time-item">
                            <small>Today</small>
                            <strong><?php echo $todaySummary ? number_format($todaySummary['total_hours'] ?? 0, 1) . 'h' : '0.0h'; ?></strong>
                        </div>
                        <?php if ($userStatus['status'] !== 'clocked_out'): ?>
                        <div class="time-item">
                            <small>Session</small>
                            <strong id="sidebarSessionTime">
                                <?php 
                                $minutes = $userStatus['elapsed_work_time'] ?? 0;
                                echo sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60);
                                ?>
                            </strong>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="quick-actions">
                        <?php if ($userStatus['status'] === 'clocked_out'): ?>
                            <button class="btn btn-success btn-sm w-100" onclick="sidebarClockIn()">
                                <i class="fas fa-play me-1"></i>Clock In
                            </button>
                        <?php elseif ($userStatus['status'] === 'clocked_in'): ?>
                            <button class="btn btn-warning btn-sm me-1" onclick="sidebarStartBreak()">
                                <i class="fas fa-pause me-1"></i>Break
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="sidebarClockOut()">
                                <i class="fas fa-stop me-1"></i>Out
                            </button>
                        <?php elseif ($userStatus['status'] === 'on_break'): ?>
                            <button class="btn btn-success btn-sm w-100" onclick="sidebarEndBreak()">
                                <i class="fas fa-play me-1"></i>End Break
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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
}

/* Time Tracking Widget Styles */
.time-tracking-widget {
    margin: 1rem 0.75rem;
    border-top: 1px solid rgba(0,0,0,0.1);
    padding-top: 1rem;
}

.widget-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 0.75rem;
    padding: 1rem;
    border: 1px solid rgba(0,0,0,0.05);
}

.widget-header h6 {
    color: var(--dark-color);
    font-weight: 600;
}

.widget-time {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    color: var(--primary-color);
    font-size: 0.9rem;
}

.status-indicator {
    margin: 0.75rem 0;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-radius: 2rem;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-working {
    background-color: rgba(25, 135, 84, 0.1);
    color: #198754;
    border: 1px solid rgba(25, 135, 84, 0.2);
}

.status-break {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
    border: 1px solid rgba(255, 193, 7, 0.2);
}

.status-offline {
    background-color: rgba(108, 117, 125, 0.1);
    color: #6c757d;
    border: 1px solid rgba(108, 117, 125, 0.2);
}

.time-summary {
    display: flex;
    justify-content: space-between;
    margin: 0.75rem 0;
    padding: 0.5rem;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 0.5rem;
}

.time-item {
    text-align: center;
    flex: 1;
}

.time-item small {
    display: block;
    color: var(--muted-color);
    font-size: 0.75rem;
    margin-bottom: 0.25rem;
}

.time-item strong {
    color: var(--dark-color);
    font-size: 0.9rem;
    font-family: 'Courier New', monospace;
}

.quick-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.75rem;
}

.quick-actions .btn {
    font-size: 0.8rem;
    padding: 0.375rem 0.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
}
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
                    sessionElement.textContent = String(hours).padStart(2, '0') + ':' + String(mins).padStart(2, '0');
                }
            })
            .catch(error => {
                // Silently handle errors when time tracking is not available
                console.log('Time tracking system not available:', error.message);
            });
    }
}

// Sidebar clock in function
function sidebarClockIn() {
    fetch('/time/clockIn', {
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
});

// Clean up intervals when page unloads
window.addEventListener('beforeunload', function() {
    if (sidebarUpdateInterval) {
        clearInterval(sidebarUpdateInterval);
    }
});
</script> 