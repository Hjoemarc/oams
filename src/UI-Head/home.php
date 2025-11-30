<?php
$stmt = $pdo->query("
    SELECT status, COUNT(*) AS total
    FROM employee_data
    WHERE user_role = 'EMPLOYEE' 
    GROUP BY status
");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare arrays for chart
$statuses = [];
$totals = [];

foreach ($data as $row) {
    $statuses[] = $row['status'];
    $totals[] = $row['total'];
}
// Query for leave status distribution
$stmt = $pdo->query("
    SELECT leaveStatus, COUNT(*) AS total
    FROM leaveReq
    GROUP BY leaveStatus
");
$leaveData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query for leave type distribution
$stmt2 = $pdo->query("
    SELECT leaveType, COUNT(*) AS total
    FROM leaveReq
    GROUP BY leaveType
");
$leaveTypeData = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Prepare arrays for charts
$leaveStatuses = [];
$leaveTotals = [];

$leaveTypes = [];
$leaveTypeTotals = [];

foreach ($leaveData as $row) {
    $leaveStatuses[] = $row['leaveStatus'];
    $leaveTotals[] = $row['total'];
}

foreach ($leaveTypeData as $row) {
    $leaveTypes[] = $row['leaveType'];
    $leaveTypeTotals[] = $row['total'];
}
?>
<body>
    <section class="scroll leave-height">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="mx-2">
                <h4><i class="fa fa-tv mx-2"></i>Main Dashboard</h4>
                <small class="text-muted">Employee status and leave management overview</small>
            </div>
        </div>

        

        <!-- Employee Status Chart (Existing) -->
        <div class="row mb-4">
         <!-- <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Leave Statistics</h5>
                    </div>
                    <div class="card-body m-0 p-0 mt-3">
                        <?php
                        // Additional leave statistics
                        $stmt3 = $pdo->query("
                            SELECT 
                                COUNT(*) as total_requests,
                                SUM(CASE WHEN leaveStatus = 'Approved' THEN numberOfDays ELSE 0 END) as approved_days,
                                AVG(numberOfDays) as avg_days,
                                MAX(request_date) as latest_request
                            FROM leaveReq
                        ");
                        $leaveStats = $stmt3->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="stat-item">
                                    <div class="stat-value fw-bold fs-5 text-dark"><?php echo $leaveStats['total_requests'] ?? 0; ?></div>
                                    <div class="stat-label">Total Requests</div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stat-item">
                                    <div class="stat-value fw-bold fs-5 text-success"><?php echo $leaveStats['approved_days'] ?? 0; ?></div>
                                    <div class="stat-label">Approved Days</div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stat-item">
                                    <div class="stat-value fw-bold fs-5 text-dark"><?php echo round($leaveStats['avg_days'] ?? 0, 1); ?></div>
                                    <div class="stat-label">Avg. Days/Request</div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stat-item">
                                    <div class="stat-value fw-bold fs-5 text-dark"><?php echo date('M d', strtotime($leaveStats['latest_request'] ?? 'N/A')); ?></div>
                                    <div class="stat-label">Latest Request</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            <!-- Leave Status Chart -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Leave Request Status</h5>
                    </div>
                    <div class="card-body m-0 p-0 mt-3">
                        <div class="chart-container">
                            <canvas id="leaveStatusChart" class="leave-chart"></canvas>
                            <div class="chart-center-text">
                                <div class="total-count"><?php echo array_sum($leaveTotals); ?></div>
                                <div class="chart-label">Total Requests</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
       

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Leave Type Distribution</h5>
                    </div>
                    <div class="card-body m-0 p-0 mt-3">
                        <div class="chart-container">
                            <canvas id="leaveTypeChart" class="leave-chart"></canvas>
                            <div class="chart-center-text">
                                <div class="total-count"><?php echo array_sum($leaveTypeTotals); ?></div>
                                <div class="chart-label">Total Leaves</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Employee Status Distribution</h5>
                    </div>
                    <div class="card-body m-0 p-0 mt-3">
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                            <div class="chart-center-text">
                                <div class="total-count"><?php echo array_sum($totals); ?></div>
                                <div class="chart-label">Total Employees</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
         </div>
    </section>
</body>

<script>
    // Leave Status Chart
    const leaveStatusLabels = <?php echo json_encode($leaveStatuses); ?>;
    const leaveStatusData = <?php echo json_encode($leaveTotals); ?>;
    const totalLeaveRequests = <?php echo array_sum($leaveTotals); ?>;

    const leaveStatusCtx = document.getElementById('leaveStatusChart').getContext('2d');
    const leaveStatusChart = new Chart(leaveStatusCtx, {
        type: 'doughnut',
        data: {
            labels: leaveStatusLabels,
            datasets: [{
                data: leaveStatusData,
                backgroundColor: [
                    '#10B981', // Approved - Green
                    '#EF4444', // Rejected - Red
                    '#F59E0B', // Pending - Amber
                    '#6B7280'  // Other status - Gray
                ],
                borderColor: '#ffffff',
                borderWidth: 3,
                hoverBackgroundColor: [
                    '#059669', // Darker Green
                    '#DC2626', // Darker Red
                    '#D97706', // Darker Amber
                    '#4B5563'  // Darker Gray
                ],
                hoverOffset: 15
            }]
        },
        options: {
            cutout: '60%',
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const percentage = totalLeaveRequests > 0 ? ((value / totalLeaveRequests) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });

    // Leave Type Chart
    const leaveTypeLabels = <?php echo json_encode($leaveTypes); ?>;
    const leaveTypeData = <?php echo json_encode($leaveTypeTotals); ?>;
    const totalLeaves = <?php echo array_sum($leaveTypeTotals); ?>;

    const leaveTypeCtx = document.getElementById('leaveTypeChart').getContext('2d');
    const leaveTypeChart = new Chart(leaveTypeCtx, {
        type: 'doughnut',
        data: {
            labels: leaveTypeLabels.map(label => {
                // Format leave type labels
                return label.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            }),
            datasets: [{
                data: leaveTypeData,
                backgroundColor: [
                    '#3B82F6', // Vacation Leave - Blue
                    '#8B5CF6', // Sick Leave - Purple
                    '#06B6D4', // Special Leave - Cyan
                    '#F97316', // Others - Orange
                    '#84CC16'  // Additional type - Lime
                ],
                borderColor: '#ffffff',
                borderWidth: 3,
                hoverBackgroundColor: [
                    '#2563EB', // Darker Blue
                    '#7C3AED', // Darker Purple
                    '#0891B2', // Darker Cyan
                    '#EA580C', // Darker Orange
                    '#60A30D'  // Darker Lime
                ],
                hoverOffset: 15
            }]
        },
        options: {
            cutout: '60%',
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const percentage = totalLeaves > 0 ? ((value / totalLeaves) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });

    // Original Employee Status Chart (from previous code)
    const labels = <?php echo json_encode($statuses); ?>;
    const data = <?php echo json_encode($totals); ?>;
    const totalEmployees = <?php echo array_sum($totals); ?>;

    const ctx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    '#10B981', // Active - Green
                    '#EF4444', // Inactive - Red
                    '#F59E0B'  // Pending - Yellow
                ],
                borderColor: '#ffffff',
                borderWidth: 3,
                hoverOffset: 15
            }]
        },
        options: {
            cutout: '60%',
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const percentage = ((value / totalEmployees) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
</script>