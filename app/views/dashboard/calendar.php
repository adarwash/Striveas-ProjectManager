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
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize calendar
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
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
            ],
            eventDidMount: function(info) {
                // Add tooltips to events
                $(info.el).tooltip({
                    title: info.event.extendedProps.project + ' - ' + 
                           info.event.title + ' (' + info.event.extendedProps.status + ')',
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
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
</style> 