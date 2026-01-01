<!-- Clean Modern Calendar Styling -->
<style>
/* Modern Minimal Calendar Design */
.calendar-page {
    padding: 2rem 0;
}

.calendar-header {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
}

.calendar-title {
    color: #1a202c;
    font-weight: 600;
    font-size: 2.25rem;
    margin: 0;
    letter-spacing: -0.025em;
}

.breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0.75rem 0 0 0;
}

.breadcrumb-item a {
    color: #64748b;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.breadcrumb-item a:hover {
    color: #3b82f6;
}

.breadcrumb-item.active {
    color: #475569;
    font-weight: 500;
}

/* Clean Button Styling */
.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    border: none;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-outline-secondary {
    border: 1px solid #d1d5db;
    color: #374151;
    background: white;
}

.btn-outline-secondary:hover {
    background: #f9fafb;
    border-color: #9ca3af;
    color: #111827;
}

/* Clean Card Styling */
.card {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    transition: box-shadow 0.2s ease;
    background: white;
}

.card:hover {
    box-shadow: 0 4px 25px rgba(0,0,0,0.12);
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    background: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0;
    padding: 1.5rem;
}

/* Modern Compact Calendar Styling */
#calendar {
    background: white;
    border-radius: 8px;
    padding: 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.fc-header-toolbar {
    margin-bottom: 0 !important;
    padding: 1rem 1.5rem !important;
    border-bottom: 1px solid #f3f4f6;
    background: #fafafa;
    border-radius: 8px 8px 0 0;
}

.fc-button-primary {
    background: white !important;
    border: 1px solid #d1d5db !important;
    border-radius: 4px !important;
    font-weight: 400 !important;
    padding: 0.375rem 0.75rem !important;
    font-size: 0.875rem !important;
    color: #374151 !important;
    transition: all 0.15s ease !important;
}

.fc-button-primary:hover {
    background: #f9fafb !important;
    border-color: #9ca3af !important;
    transform: none !important;
    box-shadow: none !important;
}

.fc-button-primary:not(:disabled):active,
.fc-button-primary:not(:disabled).fc-button-active {
    background: #f3f4f6 !important;
    border-color: #6b7280 !important;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.1) !important;
    transform: none !important;
}

.fc-toolbar-title {
    font-weight: 600 !important;
    font-size: 1.125rem !important;
    color: #111827 !important;
    letter-spacing: 0 !important;
}

/* Compact Day Grid */
.fc-daygrid {
    border: none !important;
}

.fc-daygrid-day {
    border: 1px solid #f3f4f6 !important;
    min-height: 90px !important;
    background: white;
}

.fc-daygrid-day:hover {
    background-color: #fafafa !important;
}

.fc-day-today {
    background: white !important;
    border-color: #f3f4f6 !important;
}

.fc-day-today .fc-daygrid-day-number {
    background: #3b82f6 !important;
    color: white !important;
    border-radius: 50% !important;
    width: 24px !important;
    height: 24px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin: 4px !important;
    font-weight: 600 !important;
}

.fc-daygrid-day-number {
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    color: #374151 !important;
    padding: 4px 6px !important;
    text-align: left !important;
}

.fc-col-header-cell {
    background: #fafafa !important;
    font-weight: 500 !important;
    color: #6b7280 !important;
    border: 1px solid #f3f4f6 !important;
    padding: 0.5rem !important;
    font-size: 0.75rem !important;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    text-align: center;
}

.fc-scrollgrid {
    border: none !important;
}

.fc-scrollgrid-section > * {
    border: none !important;
}

/* Compact Event Styling */
.fc-event {
    border-radius: 3px !important;
    border: none !important;
    font-weight: 400 !important;
    transition: none !important;
    margin: 1px 2px !important;
    padding: 1px 4px !important;
    min-height: 16px !important;
    font-size: 11px !important;
    line-height: 1.2 !important;
}

.fc-event:hover {
    transform: none !important;
    box-shadow: none !important;
    opacity: 0.8 !important;
}

.fc-event-main-frame {
    border-radius: 3px;
    overflow: hidden;
    padding: 0 !important;
}

.fc-event-time {
    font-weight: 600 !important;
    font-size: 10px !important;
    color: white !important;
    display: inline !important;
    margin-right: 2px !important;
}

.fc-event-title {
    font-weight: 400 !important;
    font-size: 10px !important;
    color: white !important;
    display: inline !important;
}

.fc-daygrid-event {
    margin: 1px 2px !important;
    padding: 2px 4px !important;
    font-size: 10px !important;
    border-radius: 3px !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.fc-daygrid-event-harness {
    margin: 1px 0 !important;
}

.fc-daygrid-day-events {
    margin: 2px !important;
}

.fc-more-link {
    font-size: 10px !important;
    color: #6b7280 !important;
    margin: 1px 2px !important;
    padding: 1px 4px !important;
    background: #f3f4f6 !important;
    border-radius: 2px !important;
    border: none !important;
}

/* Week and Day View Fixes */
.fc-timegrid-event {
    border-radius: 3px !important;
    border: none !important;
    font-size: 11px !important;
    padding: 2px 4px !important;
}

.fc-timegrid-event .fc-event-main {
    /* Keep main styling simple */
}

.fc-timegrid-event .fc-event-title {
    font-size: 11px !important;
    font-weight: 400 !important;
}

.fc-timegrid-event .fc-event-time {
    font-size: 10px !important;
    font-weight: 600 !important;
    display: block !important;
    margin-bottom: 1px !important;
}

.fc-timegrid-slot {
    height: 2em !important;
}

.fc-timegrid-axis {
    font-size: 11px !important;
    color: #6b7280 !important;
}

/* Specific overflow fix for timegrid events */
.fc-timegrid-event-harness {
    max-width: 95% !important;
    right: 2px !important;
}

.fc-timegrid-event-harness-inset {
    right: 2px !important;
    max-width: 95% !important;
}

/* Default timegrid event styling */
.fc-timegrid-event .fc-event-main {
    overflow: visible !important;
    white-space: normal !important;
    color: white !important;
    padding: 2px 4px !important;
}

.fc-timegrid-event .fc-event-title {
    color: white !important;
    font-size: 11px !important;
    font-weight: 400 !important;
    line-height: 1.2 !important;
}

.fc-timegrid-event .fc-event-time {
    color: white !important;
    font-size: 10px !important;
    font-weight: 600 !important;
    line-height: 1.2 !important;
}

/* Fix for FullCalendar overflow issue */
.fc-view-harness {
    overflow: scroll !important;
}

/* Ensure events don't overflow their containers */
.fc-event-main-frame {
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    max-width: 100% !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

/* Specific event type styling */
.calendar-event-normal {
    background-color: #6b7280 !important;
}

.calendar-event-high {
    background-color: #f59e0b !important;
}

.calendar-event-critical {
    background-color: #ef4444 !important;
}

.calendar-event-completed {
    background-color: #10b981 !important;
}

.calendar-event-external {
    /* External calendar colors will be set dynamically */
}

/* Ensure colors override any theme conflicts */
.fc-daygrid-event {
    border: 2px solid !important;
    border-radius: 6px !important;
}

.fc-event .fc-event-main {
    color: white !important;
}

.fc-event .fc-event-title,
.fc-event .fc-event-time {
    color: white !important;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3) !important;
}

/* Override FullCalendar's default border styles */
.fc-h-event {
    border: none !important;
}

.fc-v-event {
    border: none !important;
}

/* Clean Table Styling */
.table {
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    background: white;
}

.table thead th {
    background: #f8fafc;
    color: #374151;
    font-weight: 600;
    border: none;
    padding: 1rem;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.table tbody tr {
    transition: background-color 0.2s ease;
    border-top: 1px solid #f1f5f9;
}

.table tbody tr:hover {
    background-color: #f8fafc;
}

.table tbody td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
}

/* Color Indicators */
.color-dot {
    display: inline-block;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
    margin-right: 0.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .calendar-page {
        padding: 1rem 0;
    }
    
    .calendar-header {
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .calendar-title {
        font-size: 1.875rem;
    }
    
    .btn-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        width: 100%;
    }
    
    .btn-group .btn {
        width: 100%;
    }
    
    .fc-header-toolbar {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .fc-toolbar-title {
        font-size: 1.25rem !important;
    }
    
    #calendar {
        padding: 1rem;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .calendar-header, .card, #calendar {
        background: #1e293b;
        border-color: #334155;
    }
    
    .calendar-title {
        color: #f1f5f9;
    }
    
    .fc-col-header-cell {
        background: #334155 !important;
        color: #cbd5e1 !important;
    }
    
    .fc-day-today {
        background: #1e40af !important;
        border-color: #3b82f6 !important;
    }
    
    .table thead th {
        background: #334155;
        color: #cbd5e1;
    }
}
</style>

<div class="calendar-page">
    <div class="container-fluid">
        <div class="calendar-header">
        <div class="row mb-0">
            <div class="col-md-8">
                <h1 class="calendar-title">Calendar</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Calendar</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="btn-group">
                    <a href="/tasks/create" class="btn btn-success">
                        <i class="bi bi-plus-lg"></i> New Task
                    </a>
                    <a href="/dashboard/gantt" class="btn btn-primary">
                        <i class="bi bi-bar-chart"></i> Gantt View
                    </a>
                    <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#sharedCalendarModal">
                        <i class="bi bi-calendar-plus"></i> Link Calendar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Shared Calendars Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar2-week me-2"></i>Linked Calendars
                    </h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sharedCalendarModal">
                        <i class="bi bi-plus-lg"></i> Link New Calendar
                    </button>
                </div>
                <div class="card-body">
                    <?php if (isset($connected_calendars) && !empty($connected_calendars)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Calendar Name</th>
                                        <th>Source</th>
                                        <th>Color</th>
                                        <th>Last Synced</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($connected_calendars as $calendar): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($calendar['name']) ?></td>
                                        <td>
                                            <span class="d-flex align-items-center">
                                                <?php if ($calendar['source'] === 'google'): ?>
                                                    <i class="bi bi-google me-2 text-primary"></i> Google Calendar
                                                <?php elseif ($calendar['source'] === 'outlook' || $calendar['source'] === 'microsoft365'): ?>
                                                    <i class="bi bi-microsoft me-2 text-info"></i> Microsoft 365
                                                <?php elseif ($calendar['source'] === 'ical'): ?>
                                                    <i class="bi bi-calendar-event me-2 text-success"></i> iCal Feed
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="color-dot" style="background-color: <?= $calendar['color'] ?? '#3b82f6' ?>"></span>
                                            <span class="text-muted small"><?= $calendar['color'] ?? '#3b82f6' ?></span>
                                        </td>
                                        <td><?= isset($calendar['last_synced']) && $calendar['last_synced'] ? date('M j, Y g:i A', strtotime($calendar['last_synced'])) : 'Never' ?></td>
                                        <td>
                                            <?php if (isset($calendar['active']) && $calendar['active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Disconnected</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick="syncCalendar(<?= $calendar['id'] ?>)">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                                <button class="btn btn-outline-secondary" onclick="editCalendar(<?= $calendar['id'] ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" onclick="removeCalendar(<?= $calendar['id'] ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="bi bi-calendar2-plus" style="font-size: 3rem; color: #ccc;"></i>
                            </div>
                            <h6 class="text-muted">No Calendars Connected</h6>
                            <p class="text-muted">Link external calendars to view all your events in one place.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sharedCalendarModal">
                                <i class="bi bi-plus-lg me-1"></i> Link External Calendar
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>



    <!-- Calendar Section -->
    <div class="row">
        <div class="col-md-9">
            <div id="calendar"></div>
        </div>
        <div class="col-md-3">
            <!-- Schedule Overlaps -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>Schedule Overlaps
                    </h6>
                </div>
                <div class="card-body">
                    <div id="overlaps-container">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Add FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css" rel="stylesheet">

<!-- Add FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>

<script>
    // Function to sync a calendar
    function syncCalendar(calendarId) {
        // Add spinner to the button
        const button = document.querySelector(`.btn-group button[onclick="syncCalendar(${calendarId})"]`);
        const originalContent = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
        button.disabled = true;
        
        // Make the fetch request
        fetch(`/dashboard/syncCalendar/${calendarId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Reset button
            button.innerHTML = originalContent;
            button.disabled = false;
            
            if (data.success) {
                showToast('Success', 'Calendar synced successfully - Refreshing calendar...', 'success');
                // Reload the page to reflect changes
                setTimeout(() => window.location.reload(), 2000);
            } else {
                console.error('Sync failed:', data.message);
                showToast('Error', data.message || 'Failed to sync calendar', 'danger');
            }
        })
        .catch(error => {
            // Reset button
            button.innerHTML = originalContent;
            button.disabled = false;
            console.error('Sync error:', error);
            showToast('Error', `Sync failed: ${error.message}`, 'danger');
        });
    }
    
    // Function to navigate to edit calendar page
    function editCalendar(calendarId) {
        window.location.href = `/dashboard/editCalendar/${calendarId}`;
    }
    
    // Function to remove a calendar
    function removeCalendar(calendarId) {
        if (confirm('Are you sure you want to remove this calendar? All associated events will be deleted.')) {
            // Make the fetch request
            fetch(`/dashboard/removeCalendar/${calendarId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('Success', 'Calendar removed successfully', 'success');
                    // Reload the page to reflect changes
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast('Error', data.message || 'Failed to remove calendar', 'danger');
                }
            })
            .catch(error => {
                console.error('Calendar removal error:', error);
                showToast('Error', `An error occurred while removing the calendar: ${error.message}`, 'danger');
            });
        }
    }
    
    // Helper function to show toast notifications
    function showToast(title, message, type = 'info') {
        const toastContainer = document.getElementById('toast-container') || createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}</strong>: ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, { autohide: true, delay: 3000 });
        bsToast.show();
        
        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
    
    // Helper function to create a toast container if one doesn't exist
    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '1050';
        document.body.appendChild(container);
        return container;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize calendar
        var calendarEl = document.getElementById('calendar');
        
        // Check if calendar element exists
        
        // Build events array safely
        var calendarEvents = [];
        
        // Add task events
        <?php if (!empty($tasks)): ?>
        <?php foreach ($tasks as $task): ?>
        <?php if (!empty($task->title) && !empty($task->due_date)): ?>
        <?php 
            // Keep original title for proper FullCalendar rendering
            $taskTitle = $task->title;
            $taskStart = $task->due_date;
            $taskAllDay = false;
            
            // Check if the due_date has time info (not just a date)
            if (strlen($taskStart) <= 10 || strpos($taskStart, '00:00:00') !== false) {
                // All day or date only
                $taskAllDay = true;
            }
        ?>
        calendarEvents.push({
            id: '<?= $task->id ?>',
            title: '<?= htmlspecialchars(addslashes($taskTitle)) ?>',
            start: '<?= $taskStart ?>',
            <?php if ($taskAllDay): ?>
            allDay: true,
            <?php endif; ?>
            url: '/tasks/show/<?= $task->id ?>',
            <?php 
                // Set colors based on priority and status
                $color = '#6b7280'; // Default color (gray)
                if (isset($task->priority) && $task->priority === 'High') {
                    $color = '#f59e0b';
                }
                if (isset($task->priority) && $task->priority === 'Critical') {
                    $color = '#ef4444';
                }
                if (isset($task->status) && $task->status === 'Completed') {
                    $color = '#10b981';
                }
            ?>
            backgroundColor: '<?= $color ?>',
            borderColor: '<?= $color ?>',
            textColor: '#ffffff',
            className: 'calendar-event-<?= $task->status === 'Completed' ? 'completed' : (isset($task->priority) ? strtolower($task->priority) : 'normal') ?>',
            extendedProps: {
                type: 'task',
                status: '<?= $task->status ?? '' ?>',
                priority: '<?= $task->priority ?? '' ?>',
                project: '<?= htmlspecialchars(addslashes($task->project_title ?? '')) ?>',
                assignedTo: '<?= htmlspecialchars(addslashes($task->assigned_to ?? '')) ?>'
            }
        });
        <?php endif; ?>
        <?php endforeach; ?>
        <?php endif; ?>
        
        // Add external calendar events
        <?php if (isset($shared_events) && !empty($shared_events)): ?>
        <?php foreach ($shared_events as $event): ?>
        <?php if (!empty($event['title']) && !empty($event['start_time'])): ?>
        <?php 
            // Keep original title for proper FullCalendar rendering
            $eventTitle = $event['title'];
            $isAllDay = !empty($event['all_day']) && $event['all_day'];
        ?>
        calendarEvents.push({
            id: 'ext-<?= $event['id'] ?>',
            title: '<?= htmlspecialchars(addslashes($eventTitle)) ?>',
            start: '<?= $event['start_time'] ?>',
            <?php if (!empty($event['end_time'])): ?>
            end: '<?= $event['end_time'] ?>',
            <?php endif; ?>
            <?php if (!empty($event['all_day']) && $event['all_day']): ?>
            allDay: true,
            <?php endif; ?>
            <?php 
                $calendarColor = $event['calendar_color'] ?? '#3b82f6';
            ?>
            backgroundColor: '<?= $calendarColor ?>',
            borderColor: '<?= $calendarColor ?>',
            textColor: '#ffffff',
            className: 'calendar-event-external',
            extendedProps: {
                type: 'external',
                source: '<?= htmlspecialchars(addslashes($event['calendar_name'] ?? '')) ?>',
                location: '<?= htmlspecialchars(addslashes($event['location'] ?? '')) ?>',
                description: '<?= htmlspecialchars(addslashes($event['description'] ?? '')) ?>'
            }
        });
        <?php endif; ?>
        <?php endforeach; ?>
        <?php endif; ?>
        
        // Ensure we have a valid events array
        if (!Array.isArray(calendarEvents)) {
            console.error('Calendar events is not an array:', calendarEvents);
            calendarEvents = [];
        }
        
        // Filter out any invalid events
        calendarEvents = calendarEvents.filter(function(event) {
            return event && typeof event === 'object' && event.title && event.start;
        });
        
        // Create FullCalendar instance with error handling
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            events: calendarEvents,
            dayMaxEvents: 4,
            moreLinkClick: 'popover',
            height: 'auto',
            aspectRatio: 1.35,
            eventDataTransform: function(eventData) {
                // Ensure all required properties exist
                if (!eventData.title) eventData.title = 'Untitled Event';
                if (!eventData.start) eventData.start = new Date().toISOString();
                return eventData;
            },
            eventContent: function(arg) {
                // Different content for different views
                var event = arg.event;
                var view = arg.view;
                
                // For timegrid views (week/day), create simple text content
                if (view.type === 'timeGridWeek' || view.type === 'timeGridDay') {
                    var container = document.createElement('div');
                    container.className = 'fc-event-main';
                    container.style.padding = '2px 4px';
                    container.style.color = 'white';
                    container.style.fontSize = '11px';
                    container.style.lineHeight = '1.2';
                    
                    var timeEl = document.createElement('div');
                    timeEl.className = 'fc-event-time';
                    timeEl.style.fontSize = '10px';
                    timeEl.style.fontWeight = '600';
                    timeEl.style.color = 'white';
                    
                    var titleEl = document.createElement('div');
                    titleEl.className = 'fc-event-title';
                    titleEl.style.fontSize = '11px';
                    titleEl.style.fontWeight = '400';
                    titleEl.style.color = 'white';
                    titleEl.textContent = event.title;
                    
                    // Add time if not all day
                    if (!event.allDay && event.start) {
                        var timeText = new Date(event.start).toLocaleTimeString([], {
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                        });
                        timeEl.textContent = timeText;
                        container.appendChild(timeEl);
                    }
                    
                    container.appendChild(titleEl);
                    
                    return { domNodes: [container] };
                }
                
                // For month view, use compact design
                var container = document.createElement('div');
                container.className = 'fc-event-main-frame';
                container.style.whiteSpace = 'nowrap';
                container.style.overflow = 'hidden';
                container.style.textOverflow = 'ellipsis';
                container.style.padding = '0';
                container.style.fontSize = '10px';
                container.style.lineHeight = '12px';
                
                var content = '';
                var originalTitle = event.title;
                
                // Format time and title on same line for month view
                if (event.allDay) {
                    content = originalTitle;
                } else if (event.start) {
                    var startTime = new Date(event.start).toLocaleTimeString([], {
                        hour: 'numeric', 
                        minute: '2-digit',
                        hour12: false
                    });
                    content = startTime + ' ' + originalTitle;
                }
                
                container.textContent = content;
                
                return { domNodes: [container] };
            },
            eventDidMount: function(info) {
                // Ensure colors are applied at DOM level
                try {
                    // Force apply background colors
                    if (info.event.backgroundColor) {
                        info.el.style.setProperty('background-color', info.event.backgroundColor, 'important');
                        info.el.style.setProperty('border', 'none', 'important');
                    }
                    
                    // Also apply to any nested elements that might override
                    var mainEl = info.el.querySelector('.fc-event-main');
                    if (mainEl) {
                        mainEl.style.setProperty('background-color', info.event.backgroundColor, 'important');
                        mainEl.style.setProperty('border', 'none', 'important');
                    }
                    
                    // Ensure text color is white
                    var titleEl = info.el.querySelector('.fc-event-title');
                    var timeEl = info.el.querySelector('.fc-event-time');
                    if (titleEl) titleEl.style.setProperty('color', 'white', 'important');
                    if (timeEl) timeEl.style.setProperty('color', 'white', 'important');
                    
                    // Add tooltips to events using Bootstrap 5
                    var tooltipText = '';
                    
                    // Build comprehensive tooltip with time information
                    if (info.event.extendedProps.project) {
                        // Task event
                        tooltipText = info.event.title + '\n';
                        tooltipText += 'Project: ' + info.event.extendedProps.project + '\n';
                        tooltipText += 'Status: ' + info.event.extendedProps.status;
                        if (info.event.extendedProps.priority) {
                            tooltipText += '\nPriority: ' + info.event.extendedProps.priority;
                        }
                        if (info.event.extendedProps.assignedTo) {
                            tooltipText += '\nAssigned to: ' + info.event.extendedProps.assignedTo;
                        }
                    } else if (info.event.extendedProps.source) {
                        // External calendar event
                        tooltipText = info.event.title + '\n';
                        tooltipText += 'Calendar: ' + info.event.extendedProps.source;
                        
                        // Add time details
                        if (info.event.allDay) {
                            tooltipText += '\nTime: All Day';
                        } else if (info.event.start) {
                            var startTime = new Date(info.event.start).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            if (info.event.end) {
                                var endTime = new Date(info.event.end).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                                tooltipText += '\nTime: ' + startTime + ' - ' + endTime;
                            } else {
                                tooltipText += '\nTime: ' + startTime;
                            }
                        }
                        
                        if (info.event.extendedProps.location) {
                            tooltipText += '\nLocation: ' + info.event.extendedProps.location;
                        }
                        if (info.event.extendedProps.description) {
                            tooltipText += '\nDescription: ' + info.event.extendedProps.description;
                        }
                    } else {
                        tooltipText = info.event.title;
                    }
                    
                    // Set Bootstrap tooltip attributes
                    info.el.setAttribute('data-bs-toggle', 'tooltip');
                    info.el.setAttribute('data-bs-placement', 'top');
                    info.el.setAttribute('data-bs-title', tooltipText);
                    
                    // Initialize Bootstrap tooltip if available
                    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                        new bootstrap.Tooltip(info.el);
                    }
                } catch (error) {
                    console.warn('Error setting up event styling or tooltip:', error);
                }
            },
            eventClick: function(info) {
                if (info.event.extendedProps.type === 'task') {
                    // For tasks, follow the URL
                    info.jsEvent.preventDefault();
                    window.location.href = info.event.url;
                } else if (info.event.extendedProps.type === 'external') {
                    // For external calendar events, show a modal with details
                    info.jsEvent.preventDefault();
                    
                    // Build detailed event information
                    var eventDetails = 'Event: ' + info.event.title + '\n';
                    eventDetails += 'Calendar: ' + info.event.extendedProps.source + '\n';
                    
                    // Add time information
                    if (info.event.allDay) {
                        eventDetails += 'Time: All Day Event\n';
                    } else {
                        var startDate = new Date(info.event.start);
                        var dateStr = startDate.toLocaleDateString();
                        var timeStr = startDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        
                        if (info.event.end) {
                            var endDate = new Date(info.event.end);
                            var endTimeStr = endDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            eventDetails += 'Date: ' + dateStr + '\n';
                            eventDetails += 'Time: ' + timeStr + ' - ' + endTimeStr + '\n';
                        } else {
                            eventDetails += 'Date: ' + dateStr + '\n';
                            eventDetails += 'Time: ' + timeStr + '\n';
                        }
                    }
                    
                    if (info.event.extendedProps.location) {
                        eventDetails += 'Location: ' + info.event.extendedProps.location + '\n';
                    }
                    if (info.event.extendedProps.description) {
                        eventDetails += 'Description: ' + info.event.extendedProps.description;
                    }
                    
                    alert(eventDetails);
                }
            }
        });
        
        try {
            calendar.render();
            
            // Initialize any existing tooltips after calendar renders
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        } catch (error) {
            console.error('Error rendering calendar:', error);
            
            // Show user-friendly error message
            var errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger mt-3';
            errorDiv.innerHTML = '<h6>Calendar Error</h6><p>There was an issue loading the calendar. Please refresh the page or contact support if the problem persists.</p>';
            document.getElementById('calendar').appendChild(errorDiv);
        }
        

        
        // Test tab functionality
        const outlookTab = document.getElementById('outlook-tab');
        if (outlookTab) {
            outlookTab.addEventListener('click', function() {
                // Tab clicked
            });
        }
        
        // Find schedule overlaps
        findScheduleOverlaps();
        
        // Handle Microsoft 365 form submission (only if configured)
        const microsoft365Form = document.getElementById('microsoft365Form');
        if (microsoft365Form) {
            microsoft365Form.addEventListener('submit', function(e) {
                // Show loading state
                const btn = document.getElementById('microsoftConnectBtn');
                const buttonText = btn.querySelector('.button-text');
                const buttonLoading = btn.querySelector('.button-loading');
                const infoAlert = document.getElementById('microsoft365Info');
                
                // Ensure all elements exist before manipulating them
                if (btn && buttonText && buttonLoading && infoAlert) {
                    // Update button state
                    buttonText.classList.add('d-none');
                    buttonLoading.classList.remove('d-none');
                    btn.disabled = true;
                    
                    // Show info message
                    infoAlert.classList.remove('d-none');
                }
                
                // Allow form to submit normally (which will redirect to Microsoft OAuth)
                // No preventDefault() here because we want the OAuth redirect to happen
            });
        }
        
        // Handle Google form submission
        const googleForm = document.getElementById('googleForm');
        if (googleForm) {
            googleForm.addEventListener('submit', function(e) {
                // Show loading state
                const btn = document.getElementById('googleConnectBtn');
                const buttonText = btn.querySelector('.button-text');
                const buttonLoading = btn.querySelector('.button-loading');
                const infoAlert = document.getElementById('googleInfo');
                
                // Update button state
                buttonText.classList.add('d-none');
                buttonLoading.classList.remove('d-none');
                btn.disabled = true;
                
                // Show info message
                infoAlert.classList.remove('d-none');
                
                // Allow form to submit normally (which will redirect to Google OAuth)
                // No preventDefault() here because we want the OAuth redirect to happen
            });
        }
        
        // Handle iCal form submission
        const icalForm = document.getElementById('icalForm');
        if (icalForm) {
            icalForm.addEventListener('submit', function(e) {
                // Show loading state
                const btn = document.getElementById('icalConnectBtn');
                const buttonText = btn.querySelector('.button-text');
                const buttonLoading = btn.querySelector('.button-loading');
                
                // Update button state
                buttonText.classList.add('d-none');
                buttonLoading.classList.remove('d-none');
                btn.disabled = true;
                
                // Allow form to submit normally
                // No preventDefault() here because we want the form submission to happen
            });
        }
        
        function findScheduleOverlaps() {
            const tasks = [
                <?php if (!empty($tasks)): foreach ($tasks as $task): ?>
                <?php if (!empty($task->assigned_to) && !empty($task->due_date) && $task->status !== 'Completed'): ?>
                {
                    id: <?= $task->id ?>,
                    title: "<?= htmlspecialchars(addslashes($task->title)) ?>",
                    dueDate: "<?= $task->due_date ?>",
                    assignedTo: "<?= htmlspecialchars(addslashes($task->assigned_to)) ?>",
                    projectId: <?= $task->project_id ?? 'null' ?>,
                    projectTitle: "<?= htmlspecialchars(addslashes($task->project_title ?? '')) ?>",
                    url: "/tasks/show/<?= $task->id ?>"
                },
                <?php endif; ?>
                <?php endforeach; endif; ?>
            ];
            
            const overlaps = {};
            
            // Group tasks by assigned user
            tasks.forEach(task => {
                if (!overlaps[task.assignedTo]) {
                    overlaps[task.assignedTo] = [];
                }
                overlaps[task.assignedTo].push(task);
            });
            
            // Find overlaps (tasks due on the same day)
            const overlapResults = [];
            
            Object.keys(overlaps).forEach(user => {
                const userTasks = overlaps[user];
                if (userTasks.length > 1) {
                    // Group by due date
                    const tasksByDate = {};
                    userTasks.forEach(task => {
                        if (!tasksByDate[task.dueDate]) {
                            tasksByDate[task.dueDate] = [];
                        }
                        tasksByDate[task.dueDate].push(task);
                    });
                    
                    // Find dates with multiple tasks
                    Object.keys(tasksByDate).forEach(date => {
                        if (tasksByDate[date].length > 1) {
                            overlapResults.push({
                                user: user,
                                date: date,
                                tasks: tasksByDate[date]
                            });
                        }
                    });
                }
            });
            
            // Display overlaps
            const overlapsContainer = document.getElementById('overlaps-container');
            
            if (overlapResults.length === 0) {
                overlapsContainer.innerHTML = '<p class="text-center text-muted mb-0">No schedule overlaps detected.</p>';
                return;
            }
            
            let html = '';
            
            overlapResults.forEach(overlap => {
                const formattedDate = new Date(overlap.date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
                
                html += `
                    <div class="alert alert-warning mb-3">
                        <h6><i class="bi bi-exclamation-triangle-fill me-2"></i> Schedule Overlap for ${overlap.user}</h6>
                        <p class="mb-2">${overlap.tasks.length} tasks due on ${formattedDate}:</p>
                        <ul class="mb-0">
                `;
                
                overlap.tasks.forEach(task => {
                    html += `
                        <li>
                            <a href="${task.url}" class="alert-link">${task.title}</a>
                            ${task.projectTitle ? `(Project: ${task.projectTitle})` : ''}
                        </li>
                    `;
                });
                
                html += `
                        </ul>
                    </div>
                `;
            });
            
            overlapsContainer.innerHTML = html;
        }
    });
    
    // Function to switch to iCal tab from Microsoft 365 tab
    function switchToIcalTab() {
        // Activate the iCal tab
        const icalTab = document.getElementById('ical-tab');
        const icalTabPane = document.getElementById('ical-content');
        const microsoftTab = document.getElementById('outlook-tab');
        const microsoftTabPane = document.getElementById('outlook-content');
        
        if (icalTab && icalTabPane && microsoftTab && microsoftTabPane) {
            // Remove active class from Microsoft tab
            microsoftTab.classList.remove('active');
            microsoftTab.setAttribute('aria-selected', 'false');
            microsoftTabPane.classList.remove('show', 'active');
            
            // Add active class to iCal tab
            icalTab.classList.add('active');
            icalTab.setAttribute('aria-selected', 'true');
            icalTabPane.classList.add('show', 'active');
            
            // Focus on the calendar URL input field
            const urlInput = document.getElementById('calendar_url');
            if (urlInput) {
                setTimeout(() => {
                    urlInput.focus();
                    urlInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 150);
            }
        }
    }
    
    // Function to test iCal URL
    function testIcalUrl() {
        const urlInput = document.getElementById('calendar_url');
        const testBtn = document.getElementById('testUrlBtn');
        const resultDiv = document.getElementById('urlTestResult');
        
        if (!urlInput || !urlInput.value.trim()) {
            resultDiv.innerHTML = '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> Please enter a URL first</div>';
            return;
        }
        
        const url = urlInput.value.trim();
        
        // Basic URL validation
        try {
            new URL(url);
        } catch (e) {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle"></i> Invalid URL format</div>';
            return;
        }
        
        // Update button state
        const originalContent = testBtn.innerHTML;
        testBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Testing...';
        testBtn.disabled = true;
        
        // Test the URL by trying to fetch it
        fetch('/dashboard/connectCalendar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ url: url })
        })
        .then(response => response.json())
        .then(data => {
            testBtn.innerHTML = originalContent;
            testBtn.disabled = false;
            
            if (data.success) {
                resultDiv.innerHTML = `<div class="alert alert-success"><i class="bi bi-check-circle"></i> ${data.message}</div>`;
            } else {
                resultDiv.innerHTML = `<div class="alert alert-danger"><i class="bi bi-x-circle"></i> ${data.message}</div>`;
            }
        })
        .catch(error => {
            testBtn.innerHTML = originalContent;
            testBtn.disabled = false;
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle"></i> Error testing URL. Please try again.</div>';
            console.error('URL test error:', error);
        });
    }
</script>

<style>
    /* Calendar Styles */
    #calendar {
        height: 650px;
    }
    .fc-event {
        cursor: pointer;
    }
    .fc-event-time {
        display: none; /* Hide event time since we're just showing the day */
    }
    
    /* Color dot for calendar sources */
    .color-dot {
        display: inline-block;
        width: 16px;
        height: 16px;
        border-radius: 50%;
    }
    
    /* Modal styles */
    .tab-content {
        min-height: 300px;
    }
    
    /* Fix tab visibility in modal */
    .modal .nav-tabs .nav-link {
        color: #495057;
        border: 1px solid #dee2e6;
        background-color: #f8f9fa;
        margin-right: 2px;
    }
    
    .modal .nav-tabs .nav-link:hover {
        color: #0056b3;
        border-color: #b4d7ff #b4d7ff #dee2e6;
        background-color: #e9ecef;
    }
    
    .modal .nav-tabs .nav-link.active {
        color: #495057;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
        border-bottom: 1px solid #fff;
    }
</style>

<!-- Add Modal for Linking Shared Calendars -->
<div class="modal fade" id="sharedCalendarModal" tabindex="-1" aria-labelledby="sharedCalendarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sharedCalendarModalLabel">Link External Calendar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-4" id="calendarSourceTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="google-tab" data-bs-toggle="tab" data-bs-target="#google-content" type="button" role="tab" aria-controls="google-content" aria-selected="true">
                            <i class="bi bi-google me-2"></i>Google Calendar
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="outlook-tab" data-bs-toggle="tab" data-bs-target="#outlook-content" type="button" role="tab" aria-controls="outlook-content" aria-selected="false">
                            <i class="bi bi-microsoft me-2"></i>Microsoft 365
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ical-tab" data-bs-toggle="tab" data-bs-target="#ical-content" type="button" role="tab" aria-controls="ical-content" aria-selected="false">
                            <i class="bi bi-calendar-event me-2"></i>iCal/URL
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="calendarSourceTabContent">
                    <!-- Google Calendar Content -->
                    <div class="tab-pane fade show active" id="google-content" role="tabpanel" aria-labelledby="google-tab">
                        <div class="text-center py-4">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Google_Calendar_icon_%282020%29.svg" alt="Google Calendar" width="64" class="mb-3">
                            <h5>Connect with Google Calendar</h5>
                            <p class="text-muted">Import events from your Google Calendar into <?= DEFAULT_TITLE ?></p>
                            <form action="/dashboard/connectCalendar" method="post" id="googleForm">
                                <input type="hidden" name="calendar_type" value="google">
                                <button type="submit" class="btn btn-primary" id="googleConnectBtn">
                                    <span class="button-text">
                                        <i class="bi bi-google me-2"></i> Connect with Google
                                    </span>
                                    <span class="button-loading d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Redirecting to Google...
                                    </span>
                                </button>
                                
                                <div class="mt-3 d-none" id="googleInfo">
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>Redirecting to Google...</strong><br>
                                        You'll be taken to Google's sign-in page to authorize calendar access. After authorization, you'll be redirected back here.
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Microsoft 365 Content -->
                    <div class="tab-pane fade" id="outlook-content" role="tabpanel" aria-labelledby="outlook-tab">
                        <div class="text-center py-4">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/d/df/Microsoft_Office_Outlook_%282018%E2%80%93present%29.svg" alt="Microsoft 365" width="64" class="mb-3">
                            <h5>Connect with Microsoft 365</h5>
                            <p class="text-muted">Import events from your Microsoft 365 Calendar into your dashboard</p>
                            
                            <?php if ($microsoft365_configured): ?>
                                <form action="/dashboard/connectCalendar" method="post" id="microsoft365Form">
                                    <input type="hidden" name="calendar_type" value="microsoft365">
                                    
                                    <div class="mb-3">
                                        <label for="microsoft_calendar_name" class="form-label">Calendar Name</label>
                                        <input type="text" class="form-control" id="microsoft_calendar_name" name="calendar_name" 
                                               value="Microsoft 365 Calendar" required placeholder="My Work Calendar">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="microsoft_calendar_color" class="form-label">Display Color</label>
                                        <input type="color" class="form-control form-control-color" id="microsoft_calendar_color" 
                                               name="calendar_color" value="#0078d4">
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="microsoft_auto_refresh" name="auto_refresh" checked>
                                        <label class="form-check-label" for="microsoft_auto_refresh">Auto-refresh daily</label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary" id="microsoftConnectBtn">
                                        <span class="button-text">
                                            <i class="bi bi-microsoft me-2"></i> Connect with Microsoft 365
                                        </span>
                                        <span class="button-loading d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Redirecting to Microsoft...
                                        </span>
                                    </button>
                                    
                                    <div class="mt-3 d-none" id="microsoft365Info">
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <strong>Redirecting to Microsoft...</strong><br>
                                            You'll be taken to Microsoft's sign-in page to authorize calendar access. After authorization, you'll be redirected back here.
                                        </div>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Alternative: Use iCal URL Method</strong><br>
                                    You can still connect your Microsoft calendar using the iCal/URL method below!
                                </div>
                                
                                <div class="mt-3">
                                    <h6 class="text-primary">How to get your Microsoft calendar iCal URL:</h6>
                                    <ol class="small text-start">
                                        <li>Go to <a href="https://outlook.live.com/calendar" target="_blank" class="text-decoration-none">Outlook.com</a> or <a href="https://outlook.office.com/calendar" target="_blank" class="text-decoration-none">Outlook 365</a></li>
                                        <li>Click the Settings gear icon → <strong>View all Outlook settings</strong></li>
                                        <li>Go to <strong>Calendar</strong> → <strong>Shared calendars</strong></li>
                                        <li>Under "Publish a calendar", select your calendar</li>
                                        <li>Choose <strong>"Can view all details"</strong> permission level</li>
                                        <li>Click <strong>"Publish"</strong> and copy the <strong>ICS link</strong></li>
                                        <li>Use this link in the <strong>"iCal/URL"</strong> tab below</li>
                                    </ol>
                                    
                                    <div class="alert alert-light mt-2">
                                        <small class="text-muted">
                                            <i class="bi bi-lightbulb me-1"></i>
                                            <strong>Tip:</strong> Once you have the iCal URL, 
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" onclick="switchToIcalTab()">
                                                switch to the "iCal/URL" tab
                                            </button> 
                                            and paste it there. This works for both Outlook.com and Microsoft 365 calendars!
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="mt-3 pt-3 border-top">
                                    <details class="small">
                                        <summary class="text-muted" style="cursor: pointer;">
                                            <i class="bi bi-gear me-1"></i> Advanced: OAuth Setup for Administrators
                                        </summary>
                                        <div class="mt-2 text-muted">
                                            <p><strong>For full OAuth integration, administrators need to:</strong></p>
                                            <ul>
                                                <li>Register an app in Azure Active Directory</li>
                                                <li>Add Microsoft Graph calendar permissions</li>
                                                <li>Configure MICROSOFT_CLIENT_ID and MICROSOFT_CLIENT_SECRET</li>
                                            </ul>
                                        </div>
                                    </details>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- iCal/URL Content -->
                    <div class="tab-pane fade" id="ical-content" role="tabpanel" aria-labelledby="ical-tab">
                        <div class="py-4">
                            <h5 class="mb-3">Connect using iCal URL</h5>
                            <p class="text-muted">Connect calendars from Microsoft 365, Outlook.com, Google Calendar, Apple iCloud, or any service that provides iCal URLs</p>
                            
                            <div class="alert alert-light mb-3">
                                <div class="row text-start">
                                    <div class="col-md-6">
                                        <strong class="text-primary">📅 Microsoft/Outlook:</strong>
                                        <small class="d-block">Outlook.com → Settings → Calendar → Shared calendars → Publish → Copy <strong>ICS link</strong> (not download link)</small>
                                    </div>
                                    <div class="col-md-6">
                                        <strong class="text-success">📱 Google Calendar:</strong>
                                        <small class="d-block">Calendar Settings → Export → Calendar → Copy Secret Address</small>
                                    </div>
                                </div>
                                <div class="row text-start mt-2">
                                    <div class="col-md-6">
                                        <strong class="text-info">🍎 Apple iCloud:</strong>
                                        <small class="d-block">iCloud.com → Calendar → Share Calendar → Public Calendar</small>
                                    </div>
                                    <div class="col-md-6">
                                        <strong class="text-secondary">🔗 Other Services:</strong>
                                        <small class="d-block">Look for "Export", "Share", or "iCal" options</small>
                                    </div>
                                </div>
                            </div>
                            <form action="/dashboard/connectCalendar" method="post" id="icalForm">
                                <input type="hidden" name="calendar_type" value="ical">
                                
                                <div class="mb-3">
                                    <label for="calendar_name" class="form-label">Calendar Name</label>
                                    <input type="text" class="form-control" id="calendar_name" name="calendar_name" required placeholder="Work Calendar, Personal Events, etc.">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="calendar_url" class="form-label">iCal URL</label>
                                    <div class="input-group">
                                        <input type="url" class="form-control" id="calendar_url" name="calendar_url" required placeholder="https://example.com/calendar.ics">
                                        <button type="button" class="btn btn-outline-info" onclick="testIcalUrl()" id="testUrlBtn">
                                            <i class="bi bi-check-circle"></i> Test URL
                                        </button>
                                    </div>
                                    <div class="form-text">Enter the URL to your calendar's iCal feed</div>
                                    <div id="urlTestResult" class="mt-2"></div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="calendar_color" class="form-label">Display Color</label>
                                    <input type="color" class="form-control form-control-color" id="calendar_color" name="calendar_color" value="#039be5">
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="calendar_refresh" name="calendar_refresh" checked>
                                    <label class="form-check-label" for="calendar_refresh">Auto-refresh daily</label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary" id="icalConnectBtn">
                                    <span class="button-text">
                                        <i class="bi bi-plus-lg me-2"></i> Add Calendar
                                    </span>
                                    <span class="button-loading d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Adding Calendar...
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    </div>
</div> 