<?php
    $program_id = $_GET["program_id"];
    
    // Initialize variables to avoid undefined warnings
    $allLevels = [];
    $currentLevelDetails = null;
    
    try {
        // Get program details with latest accreditation level from history
        $stmt = $pdo->prepare("SELECT 
            p.*,
            d.department_name,
            d.department_code,
            pl.accreditation_level as current_level,
            pa.survey_date as last_survey_date
        FROM programs p
        INNER JOIN departments d ON d.department_id = p.department_id
        LEFT JOIN program_accreditation pa ON p.program_id = pa.program_id
        LEFT JOIN program_levels pl ON p.program_id = pl.program_id
        WHERE p.program_id = :program_id
        ORDER BY pa.survey_date DESC LIMIT 1");
        
        $stmt->execute(['program_id' => $program_id]);
        $programData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if program exists
        if(!$programData) {
            die("<div class='alert alert-danger'>Program not found!</div>");
        }

        // Get active accreditation cycle for this program
        $stmt = $pdo->prepare("SELECT * FROM accreditation_cycles 
                              WHERE program_id = :program_id AND status = 'active'");
        $stmt->execute(['program_id' => $program_id]);
        $cycle = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get all accreditation levels for display
        $stmt = $pdo->prepare("SELECT * FROM accreditation_levels ORDER BY sort_order");
        $stmt->execute();
        $allLevels = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Get accreditation history for this program
        $stmt = $pdo->prepare("SELECT 
            pa.*,
            al.level_name,
            al.level_code,
            ac.cycle_name,
            ac.academic_year
        FROM program_accreditation pa
        LEFT JOIN program_levels pl ON pl.program_id = pa.program_id
        LEFT JOIN accreditation_levels al ON pl.accreditation_level = al.level_code
        LEFT JOIN accreditation_cycles ac ON pa.cycle_id = ac.cycle_id
        WHERE pa.program_id = :program_id
        ORDER BY pa.survey_date DESC");
        $stmt->execute(['program_id' => $program_id]);
        $accreditationHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get all accreditation areas for Level I and II
        $stmt = $pdo->prepare("SELECT * FROM accreditation_areas ORDER BY area_number");
        $stmt->execute();
        $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Find level details for current level
        if(isset($programData['current_level']) && $programData['current_level']) {
            foreach($allLevels as $level) {
                if($level['level_code'] == $programData['current_level']) {
                    $currentLevelDetails = $level;
                    break;
                }
            }
        }
        
        // Get indicators for Level III and IV
        $level34Stmt = $pdo->prepare("
            SELECT 
                ai.*,
                COUNT(DISTINCT d.document_id) as document_count,
                COUNT(DISTINCT a.assignment_id) as assignment_count
            FROM accreditation_indicators ai
            LEFT JOIN documents d ON ai.indicator_id = d.indicator_id 
                AND d.program_id = :program_id 
                AND (:cycle_id IS NULL OR d.cycle_id = :cycle_id)
            LEFT JOIN assignments a ON ai.indicator_id = a.indicator_id 
                AND a.program_id = :program_id2
                AND (:cycle_id2 IS NULL OR a.cycle_id = :cycle_id2)
            WHERE ai.indicator_level IN ('III', 'IV', 'III(Phase 2)', 'IV(Phase 2)')
            GROUP BY ai.indicator_id
            ORDER BY 
                CASE ai.indicator_level 
                    WHEN 'IV' THEN 1
                    WHEN 'IV(Phase 2)' THEN 2
                    WHEN 'III' THEN 3
                    WHEN 'III(Phase 2)' THEN 4
                    ELSE 5
                END,
                ai.indicator_code
        ");
        
        $level34Stmt->execute([
            'program_id' => $program_id,
            'program_id2' => $program_id,
            'cycle_id' => $cycle ? $cycle['cycle_id'] : null,
            'cycle_id2' => $cycle ? $cycle['cycle_id'] : null
        ]);
        $level34Indicators = $level34Stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        die("<div class='alert alert-danger'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>");
    }
?>

<section style="height: 85vh; overflow-y: scroll; overflow-x: hidden;">
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
                <strong class="text-dark mt-1 mb-0">
                    Last Survey: <?= date('M d, Y', strtotime($programData['last_survey_date'])) ?>
                </strong>
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
                    <h5 class="card-title">Documents</h5>
                    <?php 
                    // Count total documents for this program
                    $docCountStmt = $pdo->prepare("SELECT COUNT(*) as total_docs FROM documents WHERE program_id = ?");
                    $docCountStmt->execute([$program_id]);
                    $docCount = $docCountStmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <h2 class="text-dark"><?= $docCount['total_docs'] ?? 0 ?></h2>
                    <p class="card-text text-dark small">Total Documents</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light border-warning">
                <div class="card-body text-center">
                    <h5 class="card-title">Accreditation History</h5>
                    <h2 class="text-dark"><?= count($accreditationHistory) ?></h2>
                    <p class="card-text text-dark small">
                        Survey(s) Recorded
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

    <?php 
    // Separate Level IV and III indicators
    $level4Indicators = array_filter($level34Indicators, function($indicator) {
        return in_array($indicator['indicator_level'], ['IV', 'IV(Phase 2)']);
    });

    $level3Indicators = array_filter($level34Indicators, function($indicator) {
        return in_array($indicator['indicator_level'], ['III', 'III(Phase 2)']);
    });
    ?>


    <!-- LEVEL I & II INDICATORS (With Areas/Parameters) -->
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
                    <!-- Area Statistics -->
                    <div class="row mb-4">
                        <?php 
                        // Count parameters for this area
                        $paramCountStmt = $pdo->prepare("SELECT COUNT(*) as param_count FROM accreditation_parameters WHERE area_id = ?");
                        $paramCountStmt->execute([$area['area_id']]);
                        $paramCount = $paramCountStmt->fetch(PDO::FETCH_ASSOC);
                        
                        // Count indicators for this area (Level I and II only)
                        $indCountStmt = $pdo->prepare("
                            SELECT COUNT(DISTINCT ai.indicator_id) as ind_count 
                            FROM accreditation_indicators ai
                            JOIN accreditation_parameters ap ON ai.parameter_id = ap.parameter_id
                            WHERE ap.area_id = ? 
                            AND ai.indicator_level IN ('I', 'II')
                        ");
                        $indCountStmt->execute([$area['area_id']]);
                        $indCount = $indCountStmt->fetch(PDO::FETCH_ASSOC);
                        
                        // Count documents for this area and program (Level I and II only)
                        $areaDocStmt = $pdo->prepare("
                            SELECT COUNT(DISTINCT d.document_id) as doc_count 
                            FROM documents d
                            JOIN accreditation_indicators ai ON d.indicator_id = ai.indicator_id
                            JOIN accreditation_parameters ap ON ai.parameter_id = ap.parameter_id
                            WHERE ap.area_id = ? 
                            AND d.program_id = ?
                            AND ai.indicator_level IN ('I', 'II')
                        ");
                        $areaDocStmt->execute([$area['area_id'], $program_id]);
                        $areaDocCount = $areaDocStmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center p-3">
                                    <h6 class="card-title">Parameters</h6>
                                    <h2 class="text-dark mb-0"><?= $paramCount['param_count'] ?? 0 ?></h2>
                                    <small class="text-muted">Total Parameters</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center p-3">
                                    <h6 class="card-title">Indicators</h6>
                                    <h2 class="text-dark mb-0"><?= $indCount['ind_count'] ?? 0 ?></h2>
                                    <small class="text-muted">Level I & II Indicators</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center p-3">
                                    <h6 class="card-title">Documents</h6>
                                    <h2 class="text-dark mb-0"><?= $areaDocCount['doc_count'] ?? 0 ?></h2>
                                    <small class="text-muted">Submitted</small>
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
                                // Get indicators for this parameter (Level I and II only)
                                $indStmt = $pdo->prepare("
                                    SELECT 
                                        ai.*,
                                        COUNT(DISTINCT d.document_id) as document_count,
                                        COUNT(DISTINCT a.assignment_id) as assignment_count
                                    FROM accreditation_indicators ai
                                    LEFT JOIN documents d ON ai.indicator_id = d.indicator_id 
                                        AND d.program_id = :program_id 
                                        AND (:cycle_id IS NULL OR d.cycle_id = :cycle_id)
                                    LEFT JOIN assignments a ON ai.indicator_id = a.indicator_id 
                                        AND a.program_id = :program_id2 
                                        AND (:cycle_id2 IS NULL OR a.cycle_id = :cycle_id2)
                                    WHERE ai.parameter_id = :param_id 
                                        AND ai.indicator_level IN ('I', 'II')
                                    GROUP BY ai.indicator_id
                                    ORDER BY ai.indicator_code
                                ");
                                
                                $indStmt->execute([
                                    'param_id' => $param['parameter_id'],
                                    'program_id' => $program_id,
                                    'program_id2' => $program_id,
                                    'cycle_id' => $cycle ? $cycle['cycle_id'] : null,
                                    'cycle_id2' => $cycle ? $cycle['cycle_id'] : null
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
                                            <?php 
                                            // Get document info for this indicator
                                            $docInfoStmt = $pdo->prepare("
                                                SELECT d.* FROM documents d 
                                                WHERE d.indicator_id = ? 
                                                AND d.program_id = ?
                                                LIMIT 1
                                            ");
                                            $docInfoStmt->execute([$indicator['indicator_id'], $program_id]);
                                            $docInfo = $docInfoStmt->fetch(PDO::FETCH_ASSOC);
                                            ?>
                                            
                                            <?php if(!$docInfo || empty($docInfo['file_path'])): ?>
                                            <span class="bg-danger p-2 rounded text-white">No Document</span>
                                            <?php else: ?>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="window.open('<?= base_url() . 'authentication/' . htmlspecialchars($docInfo['file_path']) ?>', '_blank')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; 
                                else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No Level I or II indicators found for this parameter.
                                </div>
                                <?php endif; ?>
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
                            <?php 
                            // Get documents for this area
                            $docStmt = $pdo->prepare("SELECT 
                                d.*,
                                ai.indicator_code,
                                ai.indicator_level,
                                u.first_name,
                                u.last_name
                            FROM documents d
                            INNER JOIN accreditation_indicators ai ON d.indicator_id = ai.indicator_id
                            INNER JOIN accreditation_parameters ap ON ai.parameter_id = ap.parameter_id
                            LEFT JOIN users u ON d.uploaded_by = u.user_id
                            WHERE ap.area_id = :area_id 
                                AND d.program_id = :program_id
                            ORDER BY d.created_at DESC LIMIT 5");
                            
                            $docStmt->execute([
                                'area_id' => $area['area_id'],
                                'program_id' => $program_id
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
                                            <span class="badge bg-info me-2">Level <?= $doc['indicator_level'] ?></span>
                                            <?= $doc['first_name'] ?> <?= $doc['last_name'] ?> • 
                                            <?= date('M d, Y', strtotime($doc['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <?php if(empty($doc['file_path'])): ?>
                                    <span class="bg-danger p-2 rounded text-white">No Document</span>
                                    <?php else: ?>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="window.open('<?= base_url() . 'authentication/' . htmlspecialchars($doc['file_path']) ?>', '_blank')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php endif; ?>
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
        <?php endforeach; ?>
    </div>
    <?php if(count($level3Indicators) > 0): ?>
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0 text-dark">
                <i class="fas fa-star me-2 text-dark"></i>
                <strong class="text-dark">SURVERY VISIT LEVEL III</strong>
            </h5>
        </div>
        <div class="card-body">
            <?php 
            // Group Level III indicators by sub-level
            $groupedLevel3Indicators = [];
            foreach($level3Indicators as $indicator) {
                $level = $indicator['indicator_level'];
                if(!isset($groupedLevel3Indicators[$level])) {
                    $groupedLevel3Indicators[$level] = [];
                }
                $groupedLevel3Indicators[$level][] = $indicator;
            }
            
            // Display in order: III, III(Phase 2)
            $displayOrder = ['III', 'III(Phase 2)'];
            
            foreach($displayOrder as $level):
                if(isset($groupedLevel3Indicators[$level]) && count($groupedLevel3Indicators[$level]) > 0):
                    $levelIndicators = $groupedLevel3Indicators[$level];
            ?>
            <div class="mb-4">
                <h6 class="border-bottom pb-2 text-dark">
                    <i class="fas fa-chevron-right me-2"></i>
                    LEVEL <?= $level ?> 
                </h6>
                
                <?php foreach($levelIndicators as $indicator): 
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
                            <?php 
                            // Get document info for this indicator
                            $docInfoStmt = $pdo->prepare("
                                SELECT d.* FROM documents d 
                                WHERE d.indicator_id = ? 
                                AND d.program_id = ?
                                LIMIT 1
                            ");
                            $docInfoStmt->execute([$indicator['indicator_id'], $program_id]);
                            $docInfo = $docInfoStmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                            
                            <?php if(!$docInfo || empty($docInfo['file_path'])): ?>
                            <span class="bg-danger p-2 rounded text-white">No Document</span>
                            <?php else: ?>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="window.open('<?= base_url() . 'authentication/' . htmlspecialchars($docInfo['file_path']) ?>', '_blank')">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php if(count($level4Indicators) > 0): ?>
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0">
                <i class="fas fa-trophy me-2"></i>
                LEVEL IV VISIT
            </h5>
        </div>
        <div class="card-body">
            <?php 
            // Group Level IV indicators by sub-level
            $groupedLevel4Indicators = [];
            foreach($level4Indicators as $indicator) {
                $level = $indicator['indicator_level'];
                if(!isset($groupedLevel4Indicators[$level])) {
                    $groupedLevel4Indicators[$level] = [];
                }
                $groupedLevel4Indicators[$level][] = $indicator;
            }
            
            // Display in order: IV, IV(Phase 2)
            $displayOrder = ['IV', 'IV(Phase 2)'];
            
            foreach($displayOrder as $level):
                if(isset($groupedLevel4Indicators[$level]) && count($groupedLevel4Indicators[$level]) > 0):
                    $levelIndicators = $groupedLevel4Indicators[$level];
            ?>
            <div class="mb-4">
                <h6 class="border-bottom pb-2 text-dark">
                    <i class="fas fa-chevron-right me-2"></i>
                    LEVEL <?= $level ?>
                </h6>
                
                <?php foreach($levelIndicators as $indicator): 
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
                            <?php 
                            // Get document info for this indicator
                            $docInfoStmt = $pdo->prepare("
                                SELECT d.* FROM documents d 
                                WHERE d.indicator_id = ? 
                                AND d.program_id = ?
                                LIMIT 1
                            ");
                            $docInfoStmt->execute([$indicator['indicator_id'], $program_id]);
                            $docInfo = $docInfoStmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                            
                            <?php if(!$docInfo || empty($docInfo['file_path'])): ?>
                            <span class="bg-danger p-2 rounded text-white">No Document</span>
                            <?php else: ?>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="window.open('<?= base_url() . 'authentication/' . htmlspecialchars($docInfo['file_path']) ?>', '_blank')">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
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
                <div class="text-center py-4">
                    <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                    <h5>Program Performance Profile</h5>
                    <p class="text-muted">Feature coming soon...</p>
                </div>
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
</script>