<div class="row mb-4">
    <div class="col-md-8">
        <h1>Calendar</h1>
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
                                            <?php elseif ($calendar['source'] === 'outlook'): ?>
                                                <i class="bi bi-microsoft me-2 text-info"></i> Microsoft Outlook
                                            <?php elseif ($calendar['source'] === 'ical'): ?>
                                                <i class="bi bi-calendar-event me-2 text-success"></i> iCal Feed
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="color-dot" style="background-color: <?= $calendar['color'] ?>"></span>
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

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Overlaps -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Schedule Overlaps</h5>
            </div>
            <div class="card-body">
                <div id="overlaps-container">
                    <!-- Will be populated by JavaScript -->
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
        .then(response => response.json())
        .then(data => {
            // Reset button
            button.innerHTML = originalContent;
            button.disabled = false;
            
            if (data.success) {
                showToast('Success', 'Calendar synced successfully', 'success');
                // Reload the page to reflect changes
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showToast('Error', data.message || 'Failed to sync calendar', 'danger');
            }
        })
        .catch(error => {
            // Reset button
            button.innerHTML = originalContent;
            button.disabled = false;
            showToast('Error', 'An error occurred while syncing', 'danger');
            console.error(error);
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
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
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
                showToast('Error', 'An error occurred while removing the calendar', 'danger');
                console.error(error);
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
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            events: [
                <?php if (!empty($tasks)): foreach ($tasks as $task): ?>
                {
                    id: '<?= $task->id ?>',
                    title: '<?= htmlspecialchars(addslashes($task->title)) ?>',
                    start: '<?= $task->due_date ?>',
                    url: '/tasks/show/<?= $task->id ?>',
                    <?php 
                        // Set colors based on priority and status
                        $color = '#6c757d'; // Default color (gray)
                        if ($task->priority === 'High') $color = '#ffc107';
                        if ($task->priority === 'Critical') $color = '#dc3545';
                        if ($task->status === 'Completed') $color = '#28a745';
                    ?>
                    backgroundColor: '<?= $color ?>',
                    borderColor: '<?= $color ?>',
                    <?php if ($task->status === 'Completed'): ?>
                    textColor: '#fff',
                    <?php endif; ?>
                    extendedProps: {
                        status: '<?= $task->status ?>',
                        priority: '<?= $task->priority ?>',
                        project: '<?= htmlspecialchars(addslashes($task->project_title ?? '')) ?>',
                        assignedTo: '<?= htmlspecialchars(addslashes($task->assigned_to ?? '')) ?>'
                    }
                },
                <?php endforeach; endif; ?>
                
                // Load shared calendar events if available
                <?php if (isset($shared_events) && !empty($shared_events)): foreach ($shared_events as $event): ?>
                {
                    id: 'ext-<?= $event['id'] ?>',
                    title: '<?= htmlspecialchars(addslashes($event['title'])) ?>',
                    start: '<?= $event['start_date'] ?? $event['start_time'] ?>',
                    <?php if (!empty($event['end_date']) || !empty($event['end_time'])): ?>
                    end: '<?= $event['end_date'] ?? $event['end_time'] ?>',
                    <?php endif; ?>
                    url: '<?= $event['url'] ?? 'javascript:void(0)' ?>',
                    backgroundColor: '<?= $event['color'] ?? $event['calendar_color'] ?>',
                    borderColor: '<?= $event['color'] ?? $event['calendar_color'] ?>',
                    extendedProps: {
                        type: 'external',
                        source: '<?= $event['source_name'] ?? $event['calendar_name'] ?>',
                        location: '<?= htmlspecialchars(addslashes($event['location'] ?? '')) ?>',
                        description: '<?= htmlspecialchars(addslashes($event['description'] ?? '')) ?>'
                    }
                },
                <?php endforeach; endif; ?>
            ],
            eventDidMount: function(info) {
                // Add tooltips to events
                $(info.el).tooltip({
                    title: info.event.extendedProps.project ? 
                           info.event.extendedProps.project + ' - ' + info.event.title + ' (' + info.event.extendedProps.status + ')' :
                           info.event.title + (info.event.extendedProps.source ? ' (' + info.event.extendedProps.source + ')' : ''),
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            },
            eventClick: function(info) {
                if (info.event.extendedProps.type === 'task') {
                    // For tasks, follow the URL
                    info.jsEvent.preventDefault();
                    window.location.href = info.event.url;
                } else if (info.event.extendedProps.type === 'external') {
                    // For external calendar events, show a modal with details
                    info.jsEvent.preventDefault();
                    
                    // Here you would show a modal with the event details
                    // For example:
                    alert(
                        'Event: ' + info.event.title + '\n' +
                        'Calendar: ' + info.event.extendedProps.source + '\n' +
                        (info.event.extendedProps.location ? 'Location: ' + info.event.extendedProps.location + '\n' : '') +
                        (info.event.extendedProps.description ? 'Description: ' + info.event.extendedProps.description : '')
                    );
                }
            }
        });
        
        calendar.render();
        
        // Find schedule overlaps
        findScheduleOverlaps();
        
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
                            <i class="bi bi-microsoft me-2"></i>Microsoft Outlook
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
                            <p class="text-muted">Import events from your Google Calendar into HiveITPortal</p>
                            <form action="/dashboard/connectCalendar" method="post">
                                <input type="hidden" name="calendar_type" value="google">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-google me-2"></i> Connect with Google
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Microsoft Outlook Content -->
                    <div class="tab-pane fade" id="outlook-content" role="tabpanel" aria-labelledby="outlook-tab">
                        <div class="text-center py-4">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/d/df/Microsoft_Office_Outlook_%282018%E2%80%93present%29.svg" alt="Microsoft Outlook" width="64" class="mb-3">
                            <h5>Connect with Microsoft Outlook</h5>
                            <p class="text-muted">Import events from your Outlook Calendar into HiveITPortal</p>
                            <form action="/dashboard/connectCalendar" method="post">
                                <input type="hidden" name="calendar_type" value="outlook">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-microsoft me-2"></i> Connect with Microsoft
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- iCal/URL Content -->
                    <div class="tab-pane fade" id="ical-content" role="tabpanel" aria-labelledby="ical-tab">
                        <div class="py-4">
                            <h5 class="mb-3">Connect using iCal URL</h5>
                            <p class="text-muted">Paste an iCal URL from any calendar service that supports iCal format</p>
                            <form action="/dashboard/connectCalendar" method="post">
                                <input type="hidden" name="calendar_type" value="ical">
                                
                                <div class="mb-3">
                                    <label for="calendar_name" class="form-label">Calendar Name</label>
                                    <input type="text" class="form-control" id="calendar_name" name="calendar_name" required placeholder="Work Calendar, Personal Events, etc.">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="calendar_url" class="form-label">iCal URL</label>
                                    <input type="url" class="form-control" id="calendar_url" name="calendar_url" required placeholder="https://example.com/calendar.ics">
                                    <div class="form-text">Enter the URL to your calendar's iCal feed</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="calendar_color" class="form-label">Display Color</label>
                                    <input type="color" class="form-control form-control-color" id="calendar_color" name="calendar_color" value="#039be5">
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="calendar_refresh" name="calendar_refresh" checked>
                                    <label class="form-check-label" for="calendar_refresh">Auto-refresh daily</label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-2"></i> Add Calendar
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