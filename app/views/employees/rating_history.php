<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/employees">Employee Management</a></li>
        <li class="breadcrumb-item"><a href="/employees/viewEmployee/<?= $employee['user_id'] ?>"><?= $employee['full_name'] ?? $employee['username'] ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Rating History</li>
    </ol>
</nav>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0"><?= $employee['full_name'] ?? $employee['username'] ?> - Rating History</h1>
        <p class="text-muted">Performance rating change history</p>
    </div>
    <a href="/employees/viewEmployee/<?= $employee['user_id'] ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Profile
    </a>
</div>

<!-- Current Rating -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent">
        <h5 class="card-title mb-0">Current Rating</h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="rating-stars me-3">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <?php if ($i <= floor($employee['performance_rating'])): ?>
                        <i class="bi bi-star-fill text-warning"></i>
                    <?php elseif ($i - 0.5 <= $employee['performance_rating']): ?>
                        <i class="bi bi-star-half text-warning"></i>
                    <?php else: ?>
                        <i class="bi bi-star text-warning"></i>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <h3 class="mb-0"><?= $employee['performance_rating'] ?>/5.0</h3>
        </div>
    </div>
</div>

<!-- Rating History -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent">
        <h5 class="card-title mb-0">Rating History</h5>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($ratingHistory)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Previous Rating</th>
                            <th>New Rating</th>
                            <th>Change</th>
                            <th>Notes</th>
                            <th>Changed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ratingHistory as $history): ?>
                            <tr>
                                <td><?= $history['formatted_date'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rating-stars me-2" style="font-size: 0.8rem;">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= floor($history['old_rating'])): ?>
                                                    <i class="bi bi-star-fill text-warning"></i>
                                                <?php elseif ($i - 0.5 <= $history['old_rating']): ?>
                                                    <i class="bi bi-star-half text-warning"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-star text-warning"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <span><?= $history['old_rating'] ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rating-stars me-2" style="font-size: 0.8rem;">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= floor($history['new_rating'])): ?>
                                                    <i class="bi bi-star-fill text-warning"></i>
                                                <?php elseif ($i - 0.5 <= $history['new_rating']): ?>
                                                    <i class="bi bi-star-half text-warning"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-star text-warning"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <span><?= $history['new_rating'] ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $change = $history['new_rating'] - $history['old_rating'];
                                    $colorClass = $change > 0 ? 'text-success' : ($change < 0 ? 'text-danger' : 'text-muted');
                                    $icon = $change > 0 ? 'bi-arrow-up' : ($change < 0 ? 'bi-arrow-down' : 'bi-dash');
                                    ?>
                                    <span class="<?= $colorClass ?>">
                                        <i class="bi <?= $icon ?>"></i>
                                        <?= abs($change) ?>
                                    </span>
                                </td>
                                <td><?= !empty($history['notes']) ? htmlspecialchars($history['notes']) : '<span class="text-muted">No notes</span>' ?></td>
                                <td><?= $history['changed_by_name'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-4 text-center">
                <p class="text-muted mb-0">No rating history available</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Rating Statistics -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent">
        <h5 class="card-title mb-0">Rating Statistics</h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <?php
            // Calculate statistics
            $ratings = array_column($ratingHistory, 'new_rating');
            $changes = [];
            
            for ($i = 1; $i < count($ratingHistory); $i++) {
                $changes[] = $ratingHistory[$i]['new_rating'] - $ratingHistory[$i]['old_rating'];
            }
            
            $avgChange = !empty($changes) ? array_sum($changes) / count($changes) : 0;
            $totalImprovement = !empty($ratings) && count($ratings) > 1 ? end($ratings) - $ratings[0] : 0;
            $maxRating = !empty($ratings) ? max($ratings) : 0;
            $avgRating = !empty($ratings) ? array_sum($ratings) / count($ratings) : 0;
            
            // Determine trend
            $trend = 'neutral';
            if (count($ratings) >= 3) {
                $recentChanges = array_slice($changes, -2);
                if ($recentChanges[0] > 0 && $recentChanges[1] > 0) {
                    $trend = 'increasing';
                } elseif ($recentChanges[0] < 0 && $recentChanges[1] < 0) {
                    $trend = 'decreasing';
                }
            }
            
            // Icons and colors for trend
            $trendIcon = 'bi-dash';
            $trendColor = 'text-muted';
            if ($trend === 'increasing') {
                $trendIcon = 'bi-graph-up-arrow';
                $trendColor = 'text-success';
            } elseif ($trend === 'decreasing') {
                $trendIcon = 'bi-graph-down-arrow';
                $trendColor = 'text-danger';
            }
            ?>
            
            <!-- Average Rating -->
            <div class="col-md-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary-light text-primary me-3">
                        <i class="bi bi-star-half"></i>
                    </div>
                    <div>
                        <div class="stat-label text-muted">Average Rating</div>
                        <div class="stat-value h4 mb-0"><?= number_format($avgRating, 2) ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Highest Rating -->
            <div class="col-md-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success-light text-success me-3">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <div>
                        <div class="stat-label text-muted">Highest Rating</div>
                        <div class="stat-value h4 mb-0"><?= number_format($maxRating, 2) ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Average Change -->
            <div class="col-md-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-info-light text-info me-3">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div>
                        <div class="stat-label text-muted">Avg. Change</div>
                        <div class="stat-value h4 mb-0 <?= $avgChange > 0 ? 'text-success' : ($avgChange < 0 ? 'text-danger' : '') ?>">
                            <?= ($avgChange > 0 ? '+' : '') . number_format($avgChange, 2) ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Overall Trend -->
            <div class="col-md-3">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning-light text-warning me-3">
                        <i class="bi <?= $trendIcon ?>"></i>
                    </div>
                    <div>
                        <div class="stat-label text-muted">Recent Trend</div>
                        <div class="stat-value h4 mb-0 <?= $trendColor ?>">
                            <?= ucfirst($trend) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rating Trend Graph -->
<?php if (!empty($ratingHistory) && count($ratingHistory) > 1): ?>
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Rating Trend Over Time</h5>
        <div class="btn-group btn-group-sm chart-type-selector">
            <button type="button" class="btn btn-outline-primary active" id="lineChartBtn">
                <i class="bi bi-graph-up"></i> Line
            </button>
            <button type="button" class="btn btn-outline-primary" id="barChartBtn">
                <i class="bi bi-bar-chart"></i> Bar
            </button>
            <button type="button" class="btn btn-outline-primary" id="polarChartBtn">
                <i class="bi bi-pie-chart"></i> Polar
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="chart-container">
            <canvas id="ratingChart"></canvas>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get data in chronological order (oldest first)
    const historyData = <?= json_encode(array_reverse($ratingHistory)) ?>;
    
    // Prepare data
    const labels = historyData.map(item => item.formatted_date);
    const ratings = historyData.map(item => item.new_rating);
    
    // Initialize chart
    const ctx = document.getElementById('ratingChart');
    if (!ctx) return;
    
    // Create line/bar chart configuration
    const createLineBarConfig = (type) => {
        return {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    label: 'Performance Rating',
                    data: ratings,
                    borderColor: '#3b82f6',
                    backgroundColor: type === 'line' ? 'rgba(59, 130, 246, 0.1)' : 
                        ratings.map((rating, index) => {
                            if (index === 0) return '#64748b';
                            const prevRating = ratings[index-1];
                            if (rating > prevRating) return 'rgba(16, 185, 129, 0.6)'; // Green - improved
                            if (rating < prevRating) return 'rgba(239, 68, 68, 0.6)';  // Red - declined
                            return 'rgba(59, 130, 246, 0.6)'; // Blue - unchanged
                        }),
                    borderWidth: 2,
                    tension: 0.2,
                    fill: type === 'line'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const rating = context.raw;
                                const dataIndex = context.dataIndex;
                                let label = `Rating: ${rating}/5.0`;
                                
                                // Add change information for points after the first one
                                if (dataIndex > 0) {
                                    const prevRating = ratings[dataIndex-1];
                                    const change = rating - prevRating;
                                    const changeText = change > 0 ? `+${change.toFixed(2)}` : change.toFixed(2);
                                    label += ` (${changeText})`;
                                }
                                
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 5,
                        ticks: {
                            stepSize: 0.5
                        },
                        title: {
                            display: true,
                            text: 'Rating',
                            color: '#666'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date',
                            color: '#666'
                        }
                    }
                }
            }
        };
    };
    
    // Create polar chart configuration
    const createPolarConfig = () => {
        return {
            type: 'polarArea',
            data: {
                labels: labels,
                datasets: [{
                    data: ratings,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                        'rgba(255, 159, 64, 0.5)',
                        'rgba(199, 199, 199, 0.5)',
                        'rgba(83, 102, 255, 0.5)',
                        'rgba(40, 159, 64, 0.5)',
                        'rgba(210, 199, 199, 0.5)'
                    ],
                    borderColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 206, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                        'rgb(255, 159, 64)',
                        'rgb(159, 159, 159)',
                        'rgb(83, 102, 255)',
                        'rgb(40, 159, 64)',
                        'rgb(210, 159, 159)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map(function(label, i) {
                                        const value = data.datasets[0].data[i];
                                        return {
                                            text: `${label}: ${value}/5.0`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: data.datasets[0].borderColor[i],
                                            lineWidth: 1,
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Rating: ${context.raw}/5.0`;
                            }
                        }
                    }
                },
                scales: {
                    r: {
                        min: 0,
                        max: 5,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        };
    };
    
    // Create the chart
    let myChart = new Chart(ctx, createLineBarConfig('line'));
    
    // Handle chart type toggles
    document.getElementById('lineChartBtn').addEventListener('click', function() {
        if (myChart) {
            myChart.destroy();
        }
        myChart = new Chart(ctx, createLineBarConfig('line'));
        
        // Update active class
        document.getElementById('lineChartBtn').classList.add('active');
        document.getElementById('barChartBtn').classList.remove('active');
        document.getElementById('polarChartBtn').classList.remove('active');
    });
    
    document.getElementById('barChartBtn').addEventListener('click', function() {
        if (myChart) {
            myChart.destroy();
        }
        myChart = new Chart(ctx, createLineBarConfig('bar'));
        
        // Update active class
        document.getElementById('lineChartBtn').classList.remove('active');
        document.getElementById('barChartBtn').classList.add('active');
        document.getElementById('polarChartBtn').classList.remove('active');
    });
    
    document.getElementById('polarChartBtn').addEventListener('click', function() {
        if (myChart) {
            myChart.destroy();
        }
        myChart = new Chart(ctx, createPolarConfig());
        
        // Update active class
        document.getElementById('lineChartBtn').classList.remove('active');
        document.getElementById('barChartBtn').classList.remove('active');
        document.getElementById('polarChartBtn').classList.add('active');
    });

    // Add proper resize listener for better chart responsiveness
    window.addEventListener('resize', function() {
        if (myChart) {
            // Destroy and rebuild chart on window resize for better responsiveness
            const activeChartType = document.querySelector('.chart-type-selector .active').id;
            const currentConfig = myChart.config;
            myChart.destroy();
            
            if (activeChartType === 'lineChartBtn') {
                myChart = new Chart(ctx, createLineBarConfig('line'));
            } else if (activeChartType === 'barChartBtn') {
                myChart = new Chart(ctx, createLineBarConfig('bar'));
            } else if (activeChartType === 'polarChartBtn') {
                myChart = new Chart(ctx, createPolarConfig());
            }
        }
    });
});
</script>
<?php endif; ?>

<style>
.rating-stars {
    font-size: 1.5rem;
}

.stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.bg-primary-light {
    background-color: rgba(59, 130, 246, 0.15);
}

.bg-success-light {
    background-color: rgba(16, 185, 129, 0.15);
}

.bg-info-light {
    background-color: rgba(14, 165, 233, 0.15);
}

.bg-warning-light {
    background-color: rgba(245, 158, 11, 0.15);
}

/* Chart container */
.chart-container {
    position: relative;
    height: 300px;
    max-height: 300px;
    width: 100%;
}

/* Make sure canvas is responsive within its container */
.chart-container canvas {
    max-width: 100% !important;
    max-height: 100% !important;
}

.chart-type-selector .btn {
    display: flex;
    align-items: center;
    gap: 5px;
}

.chart-type-selector .btn i {
    font-size: 0.9rem;
}
</style> 