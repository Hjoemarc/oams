<?php
    $program_id = $_GET["program_id"];
    
    // Get program details with latest accreditation level from history
    $stmt = $pdo->prepare("SELECT 
        p.*,
        d.department_name,
        d.department_code,
        pa.accreditation_level as current_level,
        pa.survey_date as last_survey_date
    FROM programs p
    INNER JOIN departments d ON d.department_id = p.department_id
    LEFT JOIN program_accreditation pa ON p.program_id = pa.program_id
    WHERE p.program_id = :program_id
    ORDER BY pa.survey_date DESC LIMIT 1");
    
    $stmt->execute(['program_id' => $program_id]);
    $programData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get active accreditation cycle for this program
    $stmt = $pdo->prepare("SELECT * FROM accreditation_cycles 
                          WHERE program_id = :program_id AND status = 'active'");
    $stmt->execute(['program_id' => $program_id]);
    $cycle = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get all accreditation levels for display
    $stmt = $pdo->prepare("SELECT * FROM accreditation_levels ORDER BY sort_order");
    $stmt->execute();
    $allLevels = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get accreditation history for this program
    $stmt = $pdo->prepare("SELECT 
        pa.*,
        al.level_name,
        al.level_code,
        ac.cycle_name,
        ac.academic_year
    FROM program_accreditation pa
    LEFT JOIN accreditation_levels al ON pa.accreditation_level = al.level_code
    LEFT JOIN accreditation_cycles ac ON pa.cycle_id = ac.cycle_id
    WHERE pa.program_id = :program_id
    ORDER BY pa.survey_date DESC");
    $stmt->execute(['program_id' => $program_id]);
    $accreditationHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all accreditation areas
    $stmt = $pdo->prepare("SELECT * FROM accreditation_areas ORDER BY area_number");
    $stmt->execute();
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section>
    <!-- Program Header with Level Information -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="mx-2">
            <h4><span><i class="fa-solid fa-graduation-cap me-2"></i></span> 
                <?= htmlspecialchars($programData["department_name"]) . ' (' . htmlspecialchars($programData["department_code"]) . ')' ?>
            </h4>
            <small class="text-muted">
                <?= htmlspecialchars($programData["program_name"]) . ' (' . htmlspecialchars($programData["program_code"]) . ')' ?>
            </small>
            
            <!-- Current Accreditation Level Badge -->
            <?php if($programData['current_level']): ?>
            <div class="mt-2">
                <?php 
                // Find level details
                $currentLevelDetails = null;
                foreach($allLevels as $level) {
                    if($level['level_code'] == $programData['current_level']) {
                        $currentLevelDetails = $level;
                        break;
                    }
                }
                ?>
                <span class="badge bg-success text-dark fs-6 px-3 py-2">
                    <i class="fas fa-certificate me-2"></i>
                    Current Level: <?= $currentLevelDetails ? $currentLevelDetails['level_name'] : $programData['current_level'] ?>
                </span>
                <?php if($programData['last_survey_date']): ?>
                <p class="text-dark mt-1 mb-0 small">
                    Last Survey: <?= date('M d, Y', strtotime($programData['last_survey_date'])) ?>
                </p>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="mt-2">
                <span class="badge bg-secondary fs-6 px-3 py-2">
                    <i class="fas fa-certificate me-2"></i>
                    Not Yet Accredited
                </span>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if($cycle): ?>
        <div class="text-end">
            <!-- Survey Visit Level -->
            <div class="mb-2">
                <span class="badge bg-primary fs-6 px-3 py-2">
                    Survey Visit Level: <?= $cycle['cycle_name'] ?>
                </span>
                <p class="text-muted mb-0 mt-1"><?= $cycle['academic_year'] ?></p>
                <?php if($cycle['visit_start'] && $cycle['visit_end']): ?>
                <small class="text-muted">
                    Visit: <?= date('M d', strtotime($cycle['visit_start'])) ?> - <?= date('d, Y', strtotime($cycle['visit_end'])) ?>
                </small>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Accreditation Progress Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-light border-primary">
                <div class="card-body text-center">
                    <h5 class="card-title">Current Level</h5>
                    <h2 class="text-dark">
                        <?= $programData['current_level'] ?: 'N/A' ?>
                    </h2>
                    <p class="card-text text-dark small">
                        <?php 
                        if($currentLevelDetails) {
                            echo $currentLevelDetails['level_name'];
                        } elseif($programData['current_level']) {
                            echo $programData['current_level'];
                        } else {
                            echo 'Not Accredited';
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light border-success">
                <div class="card-body text-center">
                    <h5 class="card-title">Program Performance Profile</h5>
                    <p class="card-text text-dark small">Compliance tracking</p>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#pppModal">
                        View PPP
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light border-warning">
                <div class="card-body text-center">
                    <h5 class="card-title">Accreditation History</h5>
                    <p class="card-text text-dark small">
                        <?= count($accreditationHistory) ?> survey(s)
                    </p>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#historyModal">
                        View History
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light border-info">
                <div class="card-body text-center">
                    <h5 class="card-title">Deadline</h5>
                    <p class="card-text text-dark">
                        <?php if($cycle && $cycle['deadline']): ?>
                            <?= date('M d, Y', strtotime($cycle['deadline'])) ?>
                        <?php else: ?>
                            No deadline set
                        <?php endif; ?>
                    </p>
                    <?php if($cycle && $cycle['deadline']): 
                        $daysLeft = round((strtotime($cycle['deadline']) - time()) / (60 * 60 * 24));
                        $badgeClass = $daysLeft < 0 ? 'bg-danger' : ($daysLeft < 7 ? 'bg-warning' : 'bg-success');
                    ?>
                    <span class="badge <?= $badgeClass ?>">
                        <?= $daysLeft < 0 ? 'Overdue' : $daysLeft . ' days left' ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Accreditation Level Progress -->
    <!-- <?php if(count($allLevels) > 0): ?>
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-chart-line me-2"></i>
                Accreditation Levels
            </h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <?php 
                $currentLevelIndex = -1;
                foreach($allLevels as $index => $level):
                    $isCurrent = $programData['current_level'] == $level['level_code'];
                    if($isCurrent) $currentLevelIndex = $index;
                ?>
                <div class="col">
                    <div class="level-step <?= $isCurrent ? 'current' : '' ?>">
                        <div class="step-icon">
                            <?php if($isCurrent): ?>
                            <i class="fas fa-play-circle text-primary"></i>
                            <?php else: ?>
                            <i class="fas fa-circle text-secondary"></i>
                            <?php endif; ?>
                        </div>
                        <div class="step-label mt-2">
                            <strong><?= $level['level_code'] ?></strong>
                            <div class="small"><?= $level['level_name'] ?></div>
                        </div>
                        <?php if($isCurrent): ?>
                        <span class="badge bg-primary mt-1">Current</span>
                        <?php endif; ?>
                    </div>
                    <?php if($index < count($allLevels) - 1): ?>
                    <div class="timeline-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if($currentLevelIndex >= 0 && $currentLevelIndex < count($allLevels) - 1): 
                $nextLevel = $allLevels[$currentLevelIndex + 1];
            ?>
            <div class="mt-4">
                <h6>Next Level: <?= $nextLevel['level_code'] ?> - <?= $nextLevel['level_name'] ?></h6>
                <div class="progress mb-2" style="height: 20px;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: 50%"></div>
                </div>
                <small class="text-muted">Progress toward next level: 50% (estimated)</small>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?> -->

    <!-- Accreditation Areas Accordion -->
    <div class="accordion mb-4" id="accreditationAccordion">
        <?php foreach($areas as $area): ?>
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading<?= $area['area_id'] ?>">
                <button class="accordion-button collapsed" type="button" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#collapse<?= $area['area_id'] ?>" 
                        aria-expanded="false" 
                        aria-controls="collapse<?= $area['area_id'] ?>">
                    <strong>AREA <?= $area['area_number'] ?> – <?= strtoupper($area['area_name']) ?></strong>
                </button>
            </h2>
            <div id="collapse<?= $area['area_id'] ?>" 
                 class="accordion-collapse collapse" 
                 aria-labelledby="heading<?= $area['area_id'] ?>" 
                 data-bs-parent="#accreditationAccordion">
                <div class="accordion-body">
                    <!-- Area Header Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center p-3">
                                    <h6 class="card-title">Program Performance Profile (PPP)</h6>
                                    <div class="progress mb-2" style="height: 15px;">
                                        <div class="progress-bar" role="progressbar" style="width: 65%"></div>
                                    </div>
                                    <small class="text-muted">65% Complete</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center p-3">
                                    <h6 class="card-title">Compliance Report</h6>
                                    <div class="progress mb-2" style="height: 15px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 40%"></div>
                                    </div>
                                    <small class="text-muted">40% Submitted</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center p-3">
                                    <h6 class="card-title">Self-Survey Rating</h6>
                                    <h4 class="text-warning mb-0">3.5</h4>
                                    <small class="text-muted">Out of 5.0</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php 
                    // Get parameters for this area
                    $paramStmt = $pdo->prepare("SELECT * FROM accreditation_parameters 
                                               WHERE area_id = :area_id ORDER BY parameter_code");
                    $paramStmt->execute(['area_id' => $area['area_id']]);
                    $parameters = $paramStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if(count($parameters) > 0):
                        foreach($parameters as $param): 
                    ?>
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">PARAMETER <?= $param['parameter_code'] ?>: <?= $param['parameter_name'] ?></h6>
                        </div>
                        <div class="card-body">
                            <!-- SYSTEM – INPUTS AND PROCESSES -->
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2">SYSTEM – INPUTS AND PROCESSES</h6>
                                <?php 
                                // Get indicators for this parameter
                                $indStmt = $pdo->prepare("SELECT 
                                    ai.*,
                                    d.document_id,
                                    COUNT(d.document_id) as document_count,
                                    COUNT(a.assignment_id) as assignment_count
                                FROM accreditation_indicators ai
                                LEFT JOIN documents d ON ai.indicator_id = d.indicator_id 
                                    AND d.program_id = :program_id 
                                    AND (:cycle_id IS NULL OR d.cycle_id = :cycle_id2)
                                LEFT JOIN assignments a ON ai.indicator_id = a.indicator_id 
                                    AND a.program_id = :program_id2 
                                    AND a.cycle_id = :cycle_id3
                                WHERE ai.parameter_id = :param_id 
                                    AND ai.indicator_level = 1
                                GROUP BY ai.indicator_id
                                ORDER BY ai.indicator_code");
                                
                                $indStmt->execute([
                                    'param_id' => $param['parameter_id'],
                                    'program_id' => $program_id,
                                    'program_id2' => $program_id,
                                    'cycle_id' => $cycle ? $cycle['cycle_id'] : null,
                                    'cycle_id2' => $cycle ? $cycle['cycle_id'] : null,
                                    'cycle_id3' => $cycle ? $cycle['cycle_id'] : null
                                ]);
                                $indicators = $indStmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if(count($indicators) > 0):
                                    foreach($indicators as $indicator):
                                        $hasDocuments = $indicator['document_count'] > 0;
                                        $isAssigned = $indicator['assignment_count'] > 0;
                                ?>
                                <div class="indicator-item mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="indicator_<?= $indicator['indicator_id'] ?>"
                                                       <?= $hasDocuments ? 'checked' : '' ?>
                                                       disabled>
                                                <label class="form-check-label fw-medium" for="indicator_<?= $indicator['indicator_id'] ?>">
                                                    <strong><?= $indicator['indicator_code'] ?>:</strong> 
                                                    <?= htmlspecialchars($indicator['indicator_description']) ?>
                                                </label>
                                            </div>
                                            
                                            <!-- Indicator Status -->
                                            <div class="mt-2">
                                                <?php if($hasDocuments): ?>
                                                <span class="badge bg-success me-2">
                                                    <i class="fas fa-check-circle me-1"></i> Documents Uploaded
                                                </span>
                                                <?php endif; ?>
                                                
                                                <?php if($isAssigned): ?>
                                                <span class="badge bg-primary me-2">
                                                    <i class="fas fa-user-check me-1"></i> Assigned
                                                </span>
                                                <?php endif; ?>
                                                
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-file me-1"></i> 
                                                    <?= $indicator['document_count'] ?> document(s)
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ms-3">
                                            <button class="btn btn-sm btn-danger text-white" 
                                                    onclick="viewIndicatorDetails(<?= $indicator['document_id'] ?>)">
                                                <i class="fas fa-eye text-white"></i> View
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; 
                                else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No indicators found for this parameter.
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- IMPLEMENTATION -->
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2">IMPLEMENTATION</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-title">Documentation Status</h6>
                                                <div class="progress mb-3" style="height: 20px;">
                                                    <div class="progress-bar bg-info" role="progressbar" style="width: 75%"></div>
                                                </div>
                                                <div class="d-flex justify-content-between small">
                                                    <span>Required: 12 files</span>
                                                    <span>Submitted: 9 files</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-title">Compliance Timeline</h6>
                                                <div class="d-flex justify-content-between small mb-2">
                                                    <span>Start Date</span>
                                                    <span>Deadline</span>
                                                </div>
                                                <div class="progress mb-2" style="height: 8px;">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 60%"></div>
                                                </div>
                                                <div class="text-center small text-muted">
                                                    60% of timeline elapsed
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- OUTCOME/S -->
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2">OUTCOME/S</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card text-center">
                                            <div class="card-body">
                                                <h3 class="text-success mb-0">85%</h3>
                                                <small class="text-muted">Target Achievement</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card text-center">
                                            <div class="card-body">
                                                <h3 class="text-primary mb-0">4.2</h3>
                                                <small class="text-muted">Average Rating</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card text-center">
                                            <div class="card-body">
                                                <h3 class="text-warning mb-0">92%</h3>
                                                <small class="text-muted">Stakeholder Satisfaction</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; 
                    else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No parameters found for this area.
                    </div>
                    <?php endif; ?>
                    
                    <!-- Exhibit Documents Section -->
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-folder-open me-2"></i>
                                Exhibit Documents for Area <?= $area['area_number'] ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-download me-2"></i>Download All
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <?php 
                                    // Get documents for this area
                                    $docStmt = $pdo->prepare("SELECT 
                                        d.*,
                                        ai.indicator_code,
                                        u.first_name,
                                        u.last_name
                                    FROM documents d
                                    INNER JOIN accreditation_indicators ai ON d.indicator_id = ai.indicator_id
                                    INNER JOIN accreditation_parameters ap ON ai.parameter_id = ap.parameter_id
                                    LEFT JOIN users u ON d.uploaded_by = u.user_id
                                    WHERE ap.area_id = :area_id 
                                        AND d.program_id = :program_id
                                        AND (:cycle_id IS NULL OR d.cycle_id = :cycle_id)
                                    ORDER BY d.created_at DESC LIMIT 5");
                                    
                                    $docStmt->execute([
                                        'area_id' => $area['area_id'],
                                        'program_id' => $program_id,
                                        'cycle_id' => $cycle ? $cycle['cycle_id'] : null
                                    ]);
                                    $documents = $docStmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if(count($documents) > 0):
                                        foreach($documents as $doc):
                                            $fileIcon = '';
                                            $ext = strtolower(pathinfo($doc['filename'], PATHINFO_EXTENSION));
                                            if(in_array($ext, ['pdf'])) $fileIcon = 'fa-file-pdf text-danger';
                                            elseif(in_array($ext, ['doc', 'docx'])) $fileIcon = 'fa-file-word text-primary';
                                            elseif(in_array($ext, ['xls', 'xlsx'])) $fileIcon = 'fa-file-excel text-success';
                                            elseif(in_array($ext, ['ppt', 'pptx'])) $fileIcon = 'fa-file-powerpoint text-warning';
                                            else $fileIcon = 'fa-file text-secondary';
                                    ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="fas <?= $fileIcon ?> me-3 fa-lg"></i>
                                            <div>
                                                <div class="fw-medium">
                                                    <?= htmlspecialchars($doc['filename']) ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <span class="badge bg-secondary me-2"><?= $doc['indicator_code'] ?></span>
                                                    <?= $doc['first_name'] ?> <?= $doc['last_name'] ?> • 
                                                    <?= date('M d, Y', strtotime($doc['created_at'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="viewDocument(<?= $doc['document_id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php endforeach; 
                                    else: ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-folder-open fa-2x mb-3"></i>
                                        <p>No documents uploaded for this area yet.</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- PPP Modal -->
<div class="modal fade" id="pppModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Program Performance Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- PPP content would go here -->
                <p>Program Performance Profile details will be displayed here.</p>
            </div>
        </div>
    </div>
</div>

<!-- Accreditation History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Accreditation History - <?= htmlspecialchars($programData["program_name"]) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Survey Date</th>
                                <th>Level Achieved</th>
                                <th>Cycle</th>
                                <th>Accrediting Body</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($accreditationHistory) > 0): ?>
                            <?php foreach($accreditationHistory as $history): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($history['survey_date'])) ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= $history['level_code'] ?: $history['accreditation_level'] ?>
                                    </span>
                                    <?php if($history['level_name']): ?>
                                    <br><small><?= $history['level_name'] ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $history['cycle_name'] ?: 'N/A' ?><br>
                                    <small class="text-muted"><?= $history['academic_year'] ?: '' ?></small>
                                </td>
                                <td><?= $history['accrediting_body'] ?></td>
                                <td><?= $history['remarks'] ? htmlspecialchars(substr($history['remarks'], 0, 50)) . '...' : '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    No accreditation history found for this program
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.level-step {
    padding: 15px;
    border-radius: 10px;
    text-align: center;
    transition: all 0.3s;
    position: relative;
}

.level-step.current {
    background-color: #e3f2fd;
    border: 2px solid #2196f3;
}

.step-icon {
    font-size: 2rem;
    margin-bottom: 5px;
}

.timeline-arrow {
    position: absolute;
    right: -15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 1.2rem;
}

.timeline .row {
    position: relative;
}

.timeline .col {
    position: relative;
}

.accordion-button:not(.collapsed) {
    background-color: #e7f1ff;
    color: #0c63e4;
}

.indicator-item {
    background-color: #f8f9fa;
    transition: background-color 0.2s;
}

.indicator-item:hover {
    background-color: #e9ecef;
}
</style>

<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Auto-open first area if available
    setTimeout(function() {
        if($('.accordion-button').length > 0) {
            $('.accordion-button').first().click();
        }
    }, 500);
});

function viewIndicatorDetails(indicatorId) {
    // Load and display indicator details
    alert('Viewing indicator details for ID: ' + indicatorId);
    // In a real implementation, you would load details via AJAX
}

function uploadAreaDocuments(areaId) {
    // Show document upload modal for specific area
    alert('Upload documents for Area ID: ' + areaId);
    // In a real implementation, you would show a modal with upload form
}

function viewDocument(documentId) {
    // View document details or open preview
    alert('Viewing document ID: ' + documentId);
    // In a real implementation, you would open a document viewer
}
</script>