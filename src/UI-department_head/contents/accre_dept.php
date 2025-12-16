<?php
// Get the program_id for the current user (program head)
$departmentSTMT = $pdo->prepare("SELECT programs.program_id, d.department_id, programs.program_name, programs.program_code FROM programs 
INNER JOIN users ON programs.program_id = users.program_id
INNER JOIN departments d ON programs.department_id = d.department_id
WHERE users.user_id = :user_id");
$departmentSTMT->execute([
    'user_id' => $user_id
]);
$departmentData = $departmentSTMT->fetch(PDO::FETCH_ASSOC);

if ($departmentData) {
    $program_id = $departmentData["program_id"];
    $department_id = $departmentData["department_id"];
    $program_name = $departmentData["program_name"];
    $program_code = $departmentData["program_code"];
    
    try {
        // Get the department for this program
        $collegeStmt = $pdo->prepare("
            SELECT d.*, p.program_id, p.program_name, p.program_code
            FROM departments d
            INNER JOIN programs p ON d.department_id = p.department_id
            WHERE p.program_id = :program_id
        ");
        
        $collegeStmt->execute(['program_id' => $program_id]);
        $colleges = $collegeStmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        $colleges = [];
    }
} else {
    echo "<div class='alert alert-warning'>No program assigned to your account. Please contact administrator.</div>";
    $colleges = [];
    $program_id = null;
}
?>

<section>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="mx-2">
            <h4><span><i class="fa-solid fa-clipboard-check"></i></span> Program Accreditation Status</h4>
            <small class="text-muted">Track your program's accreditation status and survey dates</small>
        </div>
        <?php if ($departmentData): ?>
        <div class="alert mx-2">
            <i class="fas fa-user-tie me-2"></i>
            <strong>Program Head:</strong> <?= htmlspecialchars($program_name) ?> (<?= htmlspecialchars($program_code) ?>)
        </div>
        <?php endif; ?>
    </div>

    <!-- Summary Cards (Only show for this program) -->
    <div class="row mb-4">
        <?php if ($program_id): ?>
        <div class="col-md-3 mb-3">
            <div class="card border-success border-start border-4">
                <div class="card-body">
                    <h6 class="card-title text-success"><i class="fas fa-check-circle"></i> Level IV</h6>
                    <h3 id="level4Count" class="card-text">0</h3>
                    <p class="card-text text-muted small">Highest Accreditation</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-primary border-start border-4">
                <div class="card-body">
                    <h6 class="card-title text-primary"><i class="fas fa-chart-line"></i> Level III</h6>
                    <h3 id="level3Count" class="card-text">0</h3>
                    <p class="card-text text-muted small">Re-accredited</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-warning border-start border-4">
                <div class="card-body">
                    <h6 class="card-title text-warning"><i class="fas fa-flag"></i> Level II</h6>
                    <h3 id="level2Count" class="card-text">0</h3>
                    <p class="card-text text-muted small">Accredited</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-secondary border-start border-4">
                <div class="card-body">
                    <h6 class="card-title text-secondary"><i class="fas fa-rocket"></i> Level I</h6>
                    <h3 id="level1Count" class="card-text">0</h3>
                    <p class="card-text text-muted small">Candidate Status</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Main Accreditation Display -->
    <div id="accreditationDisplay">
        <?php if(is_array($colleges) && count($colleges) > 0): ?>
        <?php foreach($colleges as $college): ?>
        <?php
            try {
                // Get specific program details with accreditation status
                $programsStmt = $pdo->prepare("
                    SELECT p.*,
                        COALESCE(
                            (SELECT pl.accreditation_level 
                            FROM program_accreditation pa 
                            LEFT JOIN program_levels pl ON pa.program_id = pl.program_id
                            WHERE pa.program_id = p.program_id 
                            ORDER BY pa.survey_date DESC 
                            LIMIT 1),
                            'not_accredited'
                        ) as current_level,
                        (SELECT pa.survey_date 
                        FROM program_accreditation pa 
                        WHERE pa.program_id = p.program_id 
                        ORDER BY pa.survey_date DESC 
                        LIMIT 1) as survey_date,
                        (SELECT pa.remarks 
                        FROM program_accreditation pa 
                        WHERE pa.program_id = p.program_id 
                        ORDER BY pa.survey_date DESC 
                        LIMIT 1) as remarks
                    FROM programs p
                    INNER JOIN departments d ON p.department_id = d.department_id 
                    WHERE d.department_id = :department_id
                ");
                $programsStmt->execute(['department_id' => $department_id]);
                $programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Also get latest accreditation details for display
                $accreditationStmt = $pdo->prepare("
                    SELECT * FROM program_accreditation 
                    WHERE program_id = :program_id 
                    ORDER BY survey_date DESC 
                    LIMIT 1
                ");
                $accreditationStmt->execute(['program_id' => $program_id]);
                $latestAccreditation = $accreditationStmt->fetch(PDO::FETCH_ASSOC);
                
            } catch (PDOException $e) {
                $programs = [];
                echo "<div class='alert alert-warning mb-2'>Error loading program: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
            
            if (count($programs) > 0):
        ?>
        <div class="card mb-4 college-card">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-building me-2"></i>
                    <?= htmlspecialchars($college['department_name']) ?>
                    <span class="badge bg-secondary float-end">Your Programs</span>
                </h5>
            </div>
            <div class="card-body">
                <!-- Program Header with Current Status -->
                <!-- <div class="row mb-4">
                    <div class="col-md-8">
                        <h5><i class="fas fa-graduation-cap me-2"></i><?= htmlspecialchars($college['program_name']) ?></h5>
                        <p class="text-muted">
                            <i class="fas fa-hashtag me-1"></i>Code: <?= htmlspecialchars($college['program_code']) ?>
                            <?php if($latestAccreditation): ?>
                            <br><i class="fas fa-calendar-alt me-1"></i>Last Survey: <?= date('F j, Y', strtotime($latestAccreditation['survey_date'])) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php 
                        $program = $programs[0];
                        $levelClass = getLevelBadgeClass($program['current_level']);
                        ?>
                        <div class="d-inline-block text-center p-3 border rounded">
                            <h6 class="text-muted mb-1">Current Status</h6>
                            <?php if($program['current_level'] && $program['current_level'] != 'not_accredited'): ?>
                            <span class="badge <?= $levelClass ?> fs-6 p-2">
                                <?= formatLevelText($program['current_level']) ?>
                            </span>
                            <?php else: ?>
                            <span class="badge bg-light text-dark fs-6 p-2">Not Accredited</span>
                            <?php endif; ?>
                            <?php if($program['remarks']): ?>
                            <p class="mt-2 small text-muted mb-0"><?= htmlspecialchars($program['remarks']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div> -->
                
                <!-- Detailed Program Information -->
                <div class="table-responsive">
                    <table class="table table-sm table-hover text-center" style="font-size: 0.875rem;">
                        <thead class="table-light">
                            <tr>
                                <th width="40%">Program Information</th>
                                <th width="20%">Accreditation Level</th>
                                <th width="25%">Survey Visit Date</th>
                                <th width="15%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($programs as $program): 
                                $levelClass = getLevelBadgeClass($program['current_level']);
                                $surveyDate = $program['survey_date'] ? date('F j, Y', strtotime($program['survey_date'])) : 'Not scheduled';
                            ?>
                            <tr>
                                <td class="text-start">
                                    <strong><?= htmlspecialchars($program['program_name']) ?></strong><br>
                                    <small class="text-muted">
                                        <i class="fas fa-hashtag"></i> <?= htmlspecialchars($program['program_code']) ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if($program['current_level'] && $program['current_level'] != 'not_accredited'): ?>
                                    <span class="badge <?= $levelClass ?>">
                                        <?= formatLevelText($program['current_level']) ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-light text-dark">Not Accredited</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($program['survey_date']): ?>
                                    <span class="text-dark"><?= $surveyDate ?></span>
                                    <?php if(isUpcomingSurvey($program['survey_date'])): ?>
                                    <br><small class="text-danger"><i class="fas fa-exclamation-circle"></i> Upcoming</small>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <span class="text-muted">No date set</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="index.php?page=contents/program_history&program_id=<?= $program['program_id'] ?>" 
                                           class="btn btn-info btn-sm m-0" title="View History">
                                            <i class="fas fa-history"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Accreditation Timeline Summary -->
                <?php
                try {
                    $historyStmt = $pdo->prepare("
                        SELECT accreditation_level, survey_date, accrediting_body, remarks
                        FROM program_accreditation 
                        WHERE program_id = :program_id 
                        ORDER BY survey_date DESC 
                        LIMIT 5
                    ");
                    $historyStmt->execute(['program_id' => $program_id]);
                    $recentHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if(count($recentHistory) > 0):
                ?>
                <div class="mt-4">
                    <h6><i class="fas fa-timeline me-2"></i>Recent Accreditation History</h6>
                    <div class="timeline-summary">
                        <?php foreach($recentHistory as $record): ?>
                        <div class="d-flex align-items-center mb-2">
                            <div class="me-3">
                                <span class="badge bg-primary">
                                    <?= formatLevelText($record['accreditation_level']) ?>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-dark"><?= date('F j, Y', strtotime($record['survey_date'])) ?></small>
                                <?php if($record['remarks']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($record['remarks']) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php } catch (PDOException $e) { /* Silently ignore if no history */ } ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No program assigned to your account. Please contact administrator.
        </div>
        <?php endif; ?>
    </div>


    <!-- Rest of the modals remain the same... -->
    <!-- View History Modal -->
    <div class="modal fade" id="viewHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title text-white" id="historyProgramName"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="accreditationTimeline">
                        <!-- Timeline will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Next Level Info Modal -->
    <div class="modal fade" id="nextLevelModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title text-white"><i class="fas fa-arrow-up"></i> Next Level Requirements</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="nextLevelContent">
                    <!-- Requirements will be loaded here -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading requirements...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <?php if($_SESSION['user_type'] == 'admin' || $_SESSION['user_type'] == 'dean'): ?>
                    <button type="button" class="btn btn-primary" onclick="updateAccreditation(<?= $program_id ?? 'null' ?>)"
                        id="updateAccreditationBtn" style="display: none;">
                        <i class="fas fa-edit me-1"></i> Update Accreditation
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Helper functions remain the same...
function getLevelBadgeClass($level) {
    $classes = [
        '1' => 'bg-secondary',
        '2' => 'bg-primary',
        '3' => 'bg-success',
        '4' => 'bg-warning text-dark',
        'phase1' => 'bg-info',
        'phase2' => 'bg-dark'
    ];
    return $classes[$level] ?? 'bg-light text-dark';
}

function formatLevelText($level) {
    $texts = [
        '1' => 'Level I',
        '2' => 'Level II',
        '3' => 'Level III',
        '4' => 'Level IV',
        'phase1' => 'Phase 1',
        'phase2' => 'Phase 2'
    ];
    return $texts[$level] ?? $level;
}

function isUpcomingSurvey($date) {
    try {
        $today = new DateTime();
        $surveyDate = new DateTime($date);
        $interval = $today->diff($surveyDate);
        return $surveyDate > $today && $interval->days <= 60;
    } catch (Exception $e) {
        return false;
    }
}
?>
<script>
$(document).ready(function() {
    // Update summary counts for this specific program
    function updateSummaryCounts() {
        console.log("Fetching summary counts for program...");

        <?php if(isset($program_id)): ?>
        $.ajax({
            url: base_url + "authentication/action.php?action=GetProgramSummary",
            method: 'GET',
            dataType: 'json',
            data: {
                program_id: <?= $program_id ?>
            },
            success: function(response) {
                console.log("Program summary response:", response);

                if (response && response.status === 1) {
                    // For program head, we might want to show different summary
                    if (response.data) {
                        const data = response.data;
                        // Update counts or show program-specific summary
                        $('#level4Count').text(data.level4 || 0);
                        $('#level3Count').text(data.level3 || 0);
                        $('#level2Count').text(data.level2 || 0);
                        $('#level1Count').text(data.level1 || 0);
                    }
                } else {
                    console.error("Server returned error:", response);
                    showToast(response?.message || 'Failed to load program summary', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
                // Don't show error for program heads if it fails
            }
        });
        <?php endif; ?>
    }

    // Remove the college filter since program head only sees their program
    $('.college-card').show(); // Always show the single card
    
    // Add accreditation form submission
    $('#add-accreditation-form').submit(function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: base_url + "authentication/action.php?action=add_accreditation_form",
            method: 'POST',
            dataType: 'json',
            data: formData,
            success: function(response) {
                if (response.status === 1) {
                    Swal.fire({
                        title: "Success!",
                        text: response.message,
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX error:", error);
                Swal.fire({
                    title: "Connection Error",
                    text: "Please check your connection and try again.",
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false,
                });
            }
        });
    });

    // Rest of your JavaScript functions remain the same...
    // ... (keep all the existing JavaScript functions)
    
    // Initial load
    updateSummaryCounts();
});
</script>
<style>
.timeline-summary {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 10px;
    background-color: #f8f9fa;
}

/* Rest of the CSS remains the same */
.timeline {
    position: relative;
    max-width: 100%;
    margin: 0 auto;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    width: 3px;
    background-color: #dee2e6;
    top: 0;
    bottom: 0;
    left: 50%;
    margin-left: -1.5px;
}

.timeline-item {
    padding: 10px 40px;
    position: relative;
    width: 50%;
    box-sizing: border-box;
}

.timeline-item.left {
    left: 0;
    padding-right: 50px;
}

.timeline-item.right {
    left: 50%;
    padding-left: 50px;
}

.timeline-item::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    background-color: white;
    border: 3px solid #0d6efd;
    border-radius: 50%;
    top: 15px;
    z-index: 1;
}

.timeline-item.left::after {
    right: -10px;
}

.timeline-item.right::after {
    left: -10px;
}

.timeline-item.current::after {
    border-color: #198754;
    background-color: #198754;
}

.timeline-content {
    padding: 15px;
    background-color: white;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.timeline-content:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.timeline-header {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 8px;
    margin-bottom: 10px;
}

.timeline-header h6 {
    color: #0d6efd;
    margin: 0;
    font-size: 1rem;
}

.timeline-item.current .timeline-header h6 {
    color: #198754;
}

.timeline-body {
    font-size: 0.875rem;
}

.timeline-footer {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 10px;
}

@media screen and (max-width: 768px) {
    .timeline::before {
        left: 31px;
    }
    
    .timeline-item {
        width: 100%;
        padding-left: 70px;
        padding-right: 25px;
    }
    
    .timeline-item.right {
        left: 0%;
    }
    
    .timeline-item::after {
        left: 21px;
    }
    
    .timeline-item.left {
        padding-right: 25px;
    }
}
</style>