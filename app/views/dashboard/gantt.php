<div class="row mb-4">
    <div class="col-md-8">
        <h1>Project Timeline</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Gantt Chart</li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 text-md-end">
        <div class="btn-group">
            <a href="/projects/create" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> New Project
            </a>
            <a href="/dashboard/calendar" class="btn btn-primary">
                <i class="bi bi-calendar3"></i> Calendar View
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">Project Timeline</h5>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="btn-group btn-group-sm">
                            <button id="zoom-in" class="btn btn-outline-secondary">
                                <i class="bi bi-zoom-in"></i>
                            </button>
                            <button id="zoom-out" class="btn btn-outline-secondary">
                                <i class="bi bi-zoom-out"></i>
                            </button>
                            <button id="today" class="btn btn-outline-primary">Today</button>
                        </div>
                        <div class="btn-group btn-group-sm ms-2">
                            <button id="critical-path" class="btn btn-outline-danger">Show Critical Path</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="gantt_here" style="width:100%; height:600px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Resource Allocation -->
<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Resource Allocation</h5>
            </div>
            <div class="card-body">
                <div id="resource-chart" style="width:100%; height:300px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Include dhtmlxGantt -->
<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

<!-- Include Chart.js for resource allocation -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configure Gantt chart
        gantt.config.date_format = "%Y-%m-%d";
        gantt.config.start_date = new Date();
        gantt.config.end_date = new Date();
        
        // Set work time
        gantt.config.work_time = true;
        gantt.config.skip_off_time = true;
        
        // Configure columns
        gantt.config.columns = [
            {name: "text", label: "Task/Project", tree: true, width: 250},
            {name: "start_date", label: "Start Date", align: "center", width: 120},
            {name: "duration", label: "Duration", align: "center", width: 70},
            {name: "status", label: "Status", align: "center", width: 100, template: function(obj) {
                if (obj.status) {
                    return obj.status;
                }
                return "";
            }}
        ];
        
        // Add scales
        gantt.config.scales = [
            {unit: "month", step: 1, format: "%F, %Y"},
            {unit: "week", step: 1, format: "Week %W"},
            {unit: "day", step: 1, format: "%d %M"}
        ];
        
        // Enable tooltips
        gantt.templates.tooltip_text = function(start, end, task) {
            return "<b>" + task.text + "</b><br/>" +
                   "Start: " + gantt.templates.tooltip_date_format(start) + "<br/>" +
                   "End: " + gantt.templates.tooltip_date_format(end) + "<br/>" +
                   (task.status ? "Status: " + task.status + "<br/>" : "") +
                   (task.assigned_to ? "Assigned to: " + task.assigned_to : "");
        };
        
        // Color tasks based on status
        gantt.templates.task_class = function(start, end, task) {
            if (task.status === "Completed") return "completed-task";
            if (task.status === "On Hold" || task.status === "Blocked") return "hold-task";
            if (task.status === "In Progress") return "progress-task";
            return "";
        };
        
        // Add links between tasks (dependencies)
        gantt.config.links = {
            "finish_to_start": "0",
            "start_to_start": "1",
            "finish_to_finish": "2",
            "start_to_finish": "3"
        };
        
        // Initialize Gantt
        gantt.init("gantt_here");
        
        // Load data
        gantt.parse({
            data: [
                <?php if (!empty($projects)): foreach ($projects as $index => $project): ?>
                {
                    id: "p<?= $project->id ?>",
                    text: "<?= htmlspecialchars(addslashes($project->title)) ?>",
                    start_date: "<?= $project->start_date ?>",
                    end_date: "<?= $project->end_date ?>",
                    status: "<?= $project->status ?>",
                    type: "project",
                    open: true
                },
                <?php if (!empty($project->tasks)): foreach ($project->tasks as $task): ?>
                {
                    id: "t<?= $task->id ?>",
                    text: "<?= htmlspecialchars(addslashes($task->title)) ?>",
                    start_date: "<?= !empty($task->start_date) ? $task->start_date : $project->start_date ?>",
                    end_date: "<?= !empty($task->due_date) ? $task->due_date : $project->end_date ?>",
                    status: "<?= $task->status ?>",
                    parent: "p<?= $project->id ?>",
                    assigned_to: "<?= htmlspecialchars(addslashes($task->assigned_to ?? '')) ?>",
                    priority: "<?= $task->priority ?>",
                    progress: <?= $task->status === 'Completed' ? '1' : ($task->status === 'In Progress' ? '0.5' : '0') ?>
                },
                <?php endforeach; endif; ?>
                <?php endforeach; endif; ?>
            ],
            links: [
                // Here you would add dependencies between tasks
                // Example: {id: 1, source: "t1", target: "t2", type: "0"}
            ]
        });
        
        // Add event for task click
        gantt.attachEvent("onTaskClick", function(id){
            const task = gantt.getTask(id);
            if (id.toString().startsWith("p")) {
                window.location.href = "/projects/show/" + id.substring(1);
            } else if (id.toString().startsWith("t")) {
                window.location.href = "/tasks/show/" + id.substring(1);
            }
            return true;
        });
        
        // Zoom controls
        var zoomConfig = {
            levels: [
                {
                    name: "day",
                    scale_height: 60,
                    min_column_width: 30,
                    scales: [
                        {unit: "month", step: 1, format: "%F, %Y"},
                        {unit: "day", step: 1, format: "%j %D"}
                    ]
                },
                {
                    name: "week",
                    scale_height: 60,
                    min_column_width: 50,
                    scales: [
                        {unit: "month", step: 1, format: "%F, %Y"},
                        {unit: "week", step: 1, format: "Week %W"}
                    ]
                },
                {
                    name: "month",
                    scale_height: 60,
                    min_column_width: 120,
                    scales: [
                        {unit: "month", step: 1, format: "%F, %Y"}
                    ]
                }
            ],
            currentLevel: 1
        };
        
        function setZoom(level){
            gantt.config.scales = zoomConfig.levels[level].scales;
            gantt.config.scale_height = zoomConfig.levels[level].scale_height;
            gantt.config.min_column_width = zoomConfig.levels[level].min_column_width;
            gantt.render();
        }
        
        // Initial zoom level
        setZoom(zoomConfig.currentLevel);
        
        // Zoom in button
        document.getElementById("zoom-in").addEventListener("click", function(){
            zoomConfig.currentLevel = Math.min(zoomConfig.currentLevel + 1, zoomConfig.levels.length - 1);
            setZoom(zoomConfig.currentLevel);
        });
        
        // Zoom out button
        document.getElementById("zoom-out").addEventListener("click", function(){
            zoomConfig.currentLevel = Math.max(zoomConfig.currentLevel - 1, 0);
            setZoom(zoomConfig.currentLevel);
        });
        
        // Today button
        document.getElementById("today").addEventListener("click", function(){
            gantt.showDate(new Date());
        });
        
        // Critical path button
        document.getElementById("critical-path").addEventListener("click", function(){
            if (gantt.config.highlight_critical_path) {
                gantt.config.highlight_critical_path = false;
                this.classList.remove("active");
                this.textContent = "Show Critical Path";
            } else {
                gantt.config.highlight_critical_path = true;
                this.classList.add("active");
                this.textContent = "Hide Critical Path";
            }
            gantt.render();
        });
        
        // Resource allocation chart
        function createResourceChart() {
            // Gather data by assigned person
            const resources = {};
            const allTasks = gantt.getTaskByTime();
            
            allTasks.forEach(task => {
                if (task.assigned_to && !task.type) { // Skip projects, only include tasks
                    if (!resources[task.assigned_to]) {
                        resources[task.assigned_to] = {
                            pending: 0,
                            inProgress: 0,
                            completed: 0,
                            total: 0
                        };
                    }
                    
                    resources[task.assigned_to].total++;
                    
                    if (task.status === 'Completed') {
                        resources[task.assigned_to].completed++;
                    } else if (task.status === 'In Progress') {
                        resources[task.assigned_to].inProgress++;
                    } else {
                        resources[task.assigned_to].pending++;
                    }
                }
            });
            
            // Prepare data for Chart.js
            const labels = Object.keys(resources);
            const pendingData = labels.map(label => resources[label].pending);
            const inProgressData = labels.map(label => resources[label].inProgress);
            const completedData = labels.map(label => resources[label].completed);
            
            // Create chart
            const ctx = document.getElementById('resource-chart').getContext('2d');
            const resourceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Pending',
                            data: pendingData,
                            backgroundColor: '#6c757d',
                            borderColor: '#5a6268',
                            borderWidth: 1
                        },
                        {
                            label: 'In Progress',
                            data: inProgressData,
                            backgroundColor: '#007bff',
                            borderColor: '#0069d9',
                            borderWidth: 1
                        },
                        {
                            label: 'Completed',
                            data: completedData,
                            backgroundColor: '#28a745',
                            borderColor: '#218838',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Task Allocation by Team Member'
                        },
                    },
                    scales: {
                        x: {
                            stacked: true,
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
        
        // Create resource chart after Gantt is loaded
        setTimeout(createResourceChart, 500);
    });
</script>

<style>
    /* Gantt Chart Styles */
    .gantt_task_line.completed-task {
        background-color: #28a745;
    }
    .gantt_task_line.hold-task {
        background-color: #ffc107;
    }
    .gantt_task_line.progress-task {
        background-color: #007bff;
    }
    .gantt_grid_head_cell, .gantt_grid_data_cell {
        font-size: 14px;
    }
</style> 