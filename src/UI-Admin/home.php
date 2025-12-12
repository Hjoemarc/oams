<body class="w-100">
    <section class="scroll leave-height col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="mx-2">
                <h4><i class="fa fa-tv mx-2"></i>Main Dashboard</h4>
                <small class="text-muted">Admin overview</small>
            </div>
        </div>

        <?php
        // Fetch dashboard statistics
        $stats = [];
        
        // Total Departments
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM departments");
        $stats['total_departments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Total Areas
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM accreditation_areas");
        $stats['total_areas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Total Levels
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM accreditation_levels");
        $stats['total_levels'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Total Programs
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM programs");
        $stats['total_programs'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Total Users
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Total Active Users
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE is_active = 'Active'");
        $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Total Documents
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM documents");
        $stats['total_documents'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Calculate overall progress based on compliance tracking
        $stmt = $pdo->query("
            SELECT 
                COALESCE(
                    AVG(
                        CASE 
                            WHEN ct.status = 'compliant' THEN 100
                            WHEN ct.status = 'partial' THEN 50
                            WHEN ct.status = 'non_compliant' THEN 25
                            ELSE 0
                        END
                    ), 
                    0
                ) as avg_progress
            FROM compliance_tracking ct
            WHERE ct.status != 'not_assessed'
        ");
        $overall_progress = $stmt->fetch(PDO::FETCH_ASSOC)['avg_progress'] ?? 0;
        $stats['overall_progress'] = round($overall_progress) . "%";
        
        // Fetch departments for filter
        $departmentsStmt = $pdo->query("SELECT * FROM departments ORDER BY department_name");
        $departments = $departmentsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch area progress data using compliance_tracking
        $areaProgressStmt = $pdo->query("
            SELECT 
                a.area_id,
                a.area_number,
                a.area_name,
                a.description,
                COUNT(DISTINCT ai.indicator_id) as total_indicators,
                COUNT(DISTINCT ct.indicator_id) as assessed_indicators,
                COUNT(DISTINCT p.program_id) as total_programs,
                COALESCE(
                    ROUND(
                        AVG(
                            CASE 
                                WHEN ct.status = 'compliant' THEN 100
                                WHEN ct.status = 'partial' THEN 50
                                WHEN ct.status = 'non_compliant' THEN 25
                                ELSE 0
                            END
                        )
                    , 0),
                    0
                ) as compliance_rate
            FROM accreditation_areas a
            LEFT JOIN accreditation_parameters ap ON a.area_id = ap.area_id
            LEFT JOIN accreditation_indicators ai ON ap.parameter_id = ai.parameter_id
            LEFT JOIN compliance_tracking ct ON ai.indicator_id = ct.indicator_id
            LEFT JOIN programs p ON ct.program_id = p.program_id
            GROUP BY a.area_id, a.area_number, a.area_name, a.description
            ORDER BY a.area_number
        ");
        
        // Fetch program accreditation progress
        $programProgressStmt = $pdo->query("
            SELECT 
                p.program_id,
                p.program_code,
                p.program_name,
                d.department_name,
                COUNT(DISTINCT pa.accreditation_id) as total_accreditations,
                MAX(pl.accreditation_level) as current_level,
                COUNT(DISTINCT ai.indicator_id) as total_indicators,
                COUNT(DISTINCT ct.indicator_id) as assessed_indicators,
                COALESCE(
                    ROUND(
                        AVG(
                            CASE 
                                WHEN ct.status = 'compliant' THEN 100
                                WHEN ct.status = 'partial' THEN 50
                                WHEN ct.status = 'non_compliant' THEN 25
                                ELSE 0
                            END
                        )
                    , 0),
                    0
                ) as program_progress
            FROM programs p
            LEFT JOIN departments d ON p.department_id = d.department_id
            LEFT JOIN program_accreditation pa ON p.program_id = pa.program_id
            LEFT JOIN accreditation_cycles ac ON p.program_id = ac.program_id AND ac.status = 'active'
            LEFT JOIN compliance_tracking ct ON p.program_id = ct.program_id AND ac.cycle_id = ct.cycle_id
            LEFT JOIN accreditation_indicators ai ON ct.indicator_id = ai.indicator_id
            LEFT JOIN program_levels pl ON p.program_id = pl.program_id
            WHERE p.program_id IS NOT NULL
            GROUP BY p.program_id, p.program_code, p.program_name, d.department_name
            ORDER BY program_progress DESC
            LIMIT 5
        ");
        $programProgress = $programProgressStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch level progress data using program_accreditation
        $levelProgressStmt = $pdo->query("
            SELECT 
                l.level_id,
                l.level_code,
                l.level_name,
                l.description,
                l.requirements,
                l.next_level_code,
                l.sort_order,
                COUNT(DISTINCT pa.program_id) as total_programs,
                COUNT(DISTINCT CASE WHEN pl.accreditation_level = l.level_name THEN pa.program_id END) as programs_at_level,
                COALESCE(
                    ROUND(
                        (COUNT(DISTINCT CASE WHEN pl.accreditation_level = l.level_name THEN pa.program_id END) * 100.0 / 
                        NULLIF(COUNT(DISTINCT p.program_id), 0))
                    , 0),
                    0
                ) as completion_rate,
                CASE 
                    WHEN COUNT(DISTINCT CASE WHEN pl.accreditation_level = l.level_name THEN pa.program_id END) >= 1 THEN 'completed'
                    WHEN COUNT(DISTINCT p.program_id) > 0 THEN 'current'
                    ELSE 'pending'
                END as status
            FROM accreditation_levels l
            LEFT JOIN programs p ON 1=1
            LEFT JOIN program_levels pl ON p.program_id = pl.program_id
            LEFT JOIN program_accreditation pa ON p.program_id = pa.program_id
            GROUP BY l.level_id, l.level_code, l.level_name, l.description, l.requirements, l.next_level_code, l.sort_order
            ORDER BY l.sort_order
        ");
        $levelProgress = $levelProgressStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch document status distribution
        $docStatusStmt = $pdo->query("
            SELECT 
                status,
                COUNT(*) as count
            FROM documents
            GROUP BY status
            ORDER BY FIELD(status, 'approved', 'under_review', 'submitted', 'needs_revision', 'draft')
        ");
        $docStatusData = $docStatusStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Prepare data for document status chart
        $docStatusLabels = [];
        $docStatusValues = [];
        $docStatusColors = [
            'approved' => '#1cc88a',
            'under_review' => '#f6c23e',
            'submitted' => '#4e73df',
            'needs_revision' => '#e74a3b',
            'draft' => '#858796'
        ];
        
        foreach ($docStatusData as $status) {
            $docStatusLabels[] = ucfirst(str_replace('_', ' ', $status['status']));
            $docStatusValues[] = (int)$status['count'];
        }
        
        // Fetch department progress for chart (based on compliance tracking)
        $deptProgressStmt = $pdo->query("
            SELECT 
                d.department_name,
                COALESCE(
                    ROUND(
                        AVG(
                            CASE 
                                WHEN ct.status = 'compliant' THEN 100
                                WHEN ct.status = 'partial' THEN 50
                                WHEN ct.status = 'non_compliant' THEN 25
                                ELSE 0
                            END
                        )
                    , 0),
                    0
                ) as progress
            FROM departments d
            LEFT JOIN programs p ON d.department_id = p.department_id
            LEFT JOIN compliance_tracking ct ON p.program_id = ct.program_id
            GROUP BY d.department_id, d.department_name
            ORDER BY progress DESC
            LIMIT 10
        ");
        
        $chartLabels = [];
        $chartValues = [];
        while($row = $deptProgressStmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['progress'] > 0) {
                $chartLabels[] = $row['department_name'];
                $chartValues[] = (float)$row['progress'];
            }
        }
        
        // If no data, show sample data for charts
        if (empty($chartLabels)) {
            $chartLabels = ['IT Department', 'Academic Dept', 'Administration', 'Finance'];
            $chartValues = [65, 42, 88, 30];
        }
        
        // Fetch user role distribution
        $userRoleStmt = $pdo->query("
            SELECT 
                user_role,
                COUNT(*) as count
            FROM users
            WHERE is_active = 'Active'
            GROUP BY user_role
            ORDER BY COUNT(*) DESC
        ");
        $userRoleData = $userRoleStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <!-- Dashboard Stats -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                    Total Departments</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_departments']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-building fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Programs</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_programs']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-graduation-cap fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Active Users</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['active_users']; ?> / <?php echo $stats['total_users']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                    Overall Progress</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['overall_progress']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-tasks fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accreditation Progress Charts -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-dark">Accreditation Progress by Department</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="accreditationProgressChart" height="200"></canvas>
                    </div>
                </div>
            </div>

        </div>

    </section>

    <style>
        .progress-tracker {
            position: relative;
            padding: 20px 0;
        }
        
        .progress-step {
            position: relative;
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .progress-step.completed .step-icon {
            background-color: #1cc88a;
            border-color: #1cc88a;
            color: white;
        }
        
        .progress-step.current .step-icon {
            background-color: #4e73df;
            border-color: #4e73df;
            color: white;
        }
        
        .progress-step.pending .step-icon {
            background-color: #f8f9fc;
            border-color: #dddfeb;
            color: #b7b9cc;
        }
        
        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 3px solid #dddfeb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 20px;
            z-index: 2;
            background-color: white;
        }
        
        .step-content {
            flex: 1;
        }
        
        .step-connector {
            position: absolute;
            left: 25px;
            top: 50px;
            width: 3px;
            height: 40px;
            background-color: #dddfeb;
            z-index: 1;
        }
        
        .step-connector.completed {
            background-color: #1cc88a;
        }
        
        .progress-bar-custom {
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .compliance-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge-compliant {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-partial {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-noncompliant {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>

    <script>
        // Load Chart.js from CDN
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        script.onload = initializeCharts;
        document.head.appendChild(script);

        function initializeCharts() {
            // Accreditation Progress Chart
            const progressCtx = document.getElementById('accreditationProgressChart').getContext('2d');
            new Chart(progressCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chartLabels); ?>,
                    datasets: [{
                        label: 'Completion %',
                        data: <?php echo json_encode($chartValues); ?>,
                        backgroundColor: '#4e73df',
                        borderColor: '#2e59d9',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Completion: ${context.raw}%`;
                                }
                            }
                        }
                    }
                }
            });

            // Document Status Chart
            const docStatusCtx = document.getElementById('documentStatusChart').getContext('2d');
            new Chart(docStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($docStatusLabels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($docStatusValues); ?>,
                        backgroundColor: [
                            '#1cc88a', // approved
                            '#f6c23e', // under_review
                            '#4e73df', // submitted
                            '#e74a3b', // needs_revision
                            '#858796'  // draft
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // User Role Chart
            const userRoleCtx = document.getElementById('userRoleChart').getContext('2d');
            const userRoleLabels = <?php echo json_encode(array_map(function($r) { return ucfirst($r['user_role']); }, $userRoleData)); ?>;
            const userRoleValues = <?php echo json_encode(array_column($userRoleData, 'count')); ?>;
            
            new Chart(userRoleCtx, {
                type: 'pie',
                data: {
                    labels: userRoleLabels,
                    datasets: [{
                        data: userRoleValues,
                        backgroundColor: [
                            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        }

        function filterAreaProgress(departmentId) {
            if(departmentId === 'all') {
                window.location.href = window.location.pathname;
            } else {
                window.location.href = window.location.pathname + '?department_id=' + departmentId;
            }
        }

        function viewAreaDetails(areaId) {
            window.location.href = 'area_details.php?area_id=' + areaId;
        }

        // Initialize charts when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Charts will initialize when Chart.js loads
        });
    </script>
</body>