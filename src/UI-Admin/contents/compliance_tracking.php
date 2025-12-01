<?php
// Fetch compliance data with related information
$complianceStmt = $pdo->prepare("
    SELECT 
        ct.*,
        p.program_code,
        p.program_name,
        ac.cycle_name,
        ac.academic_year,
        ai.indicator_code,
        ai.indicator_description,
        aa.area_number,
        aa.area_name,
        u.first_name as reviewer_first,
        u.last_name as reviewer_last,
        -- Get criteria info if available
        cr.criteria_number,
        cr.criteria_description
    FROM compliance_tracking ct
    JOIN programs p ON ct.program_id = p.program_id
    JOIN accreditation_cycles ac ON ct.cycle_id = ac.cycle_id
    JOIN accreditation_indicators ai ON ct.indicator_id = ai.indicator_id
    JOIN accreditation_areas aa ON ai.area_id = aa.area_id
    LEFT JOIN accreditation_criteria cr ON ct.criteria_id = cr.criteria_id
    LEFT JOIN users u ON ct.reviewer_id = u.user_id
    ORDER BY aa.area_number, ai.indicator_code, ct.program_id
");
$complianceStmt->execute();
$complianceData = $complianceStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate overall statistics
$totalRecords = count($complianceData);
$compliantCount = 0;
$nonCompliantCount = 0;
$partialCount = 0;
$notAssessedCount = 0;

foreach ($complianceData as $record) {
    switch ($record['status']) {
        case 'compliant':
            $compliantCount++;
            break;
        case 'non_compliant':
            $nonCompliantCount++;
            break;
        case 'partial':
            $partialCount++;
            break;
        case 'not_assessed':
            $notAssessedCount++;
            break;
    }
}

$compliancePercentage = $totalRecords > 0 ? round(($compliantCount / $totalRecords) * 100) : 0;
$totalAssessed = $compliantCount + $nonCompliantCount + $partialCount;
?>

<!-- Compliance Dashboard -->
<section class="compliance-dashboard">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4><i class="fas fa-chart-line me-2"></i>Compliance Tracking Dashboard</h4>
            <small class="text-muted">Monitor accreditation compliance status across programs</small>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addComplianceModal">
                <i class="fas fa-plus me-1"></i> Add Compliance Record
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Overall Compliance Score -->
        <div class="col-md-3 mb-3">
            <div class="card rounded-1 text-white">
                <div class="card-body text-center">
                    <h5 class="card-title text-dark">Overall Compliance</h5>
                    <h1 class="display-4 fw-bold text-dark"><?= $compliancePercentage ?>%</h1>
                    <p class="card-text mb-0 text-dark"><?= $compliantCount ?> of <?= $totalRecords ?> criteria
                        compliant</p>
                    <small class="text-dark"><?= $totalAssessed ?> assessed, <?= $notAssessedCount ?> pending</small>
                </div>
            </div>
        </div>

        <!-- Compliance Status Breakdown -->
        <div class="col-md-9 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Compliance Status Breakdown</h5>
                    <div class="progress mb-3 text-dark" style="height: 30px;">
                        <?php if ($compliantCount > 0): ?>
                        <div class="progress-bar bg-success" style="width: <?= ($compliantCount/$totalRecords)*100 ?>%"
                            data-bs-toggle="tooltip" data-bs-title="Compliant: <?= $compliantCount ?> criteria">
                            Compliant (<?= $compliantCount ?>)
                        </div>
                        <?php endif; ?>

                        <?php if ($partialCount > 0): ?>
                        <div class="progress-bar bg-warning" style="width: <?= ($partialCount/$totalRecords)*100 ?>%"
                            data-bs-toggle="tooltip" data-bs-title="Partial: <?= $partialCount ?> criteria">
                            Partial (<?= $partialCount ?>)
                        </div>
                        <?php endif; ?>

                        <?php if ($nonCompliantCount > 0): ?>
                        <div class="progress-bar bg-danger"
                            style="width: <?= ($nonCompliantCount/$totalRecords)*100 ?>%" data-bs-toggle="tooltip"
                            data-bs-title="Non-compliant: <?= $nonCompliantCount ?> criteria">
                            Non-compliant (<?= $nonCompliantCount ?>)
                        </div>
                        <?php endif; ?>

                        <?php if ($notAssessedCount > 0): ?>
                        <div class="progress-bar bg-secondary"
                            style="width: <?= ($notAssessedCount/$totalRecords)*100 ?>%" data-bs-toggle="tooltip"
                            data-bs-title="Not assessed: <?= $notAssessedCount ?> criteria">
                            Not Assessed (<?= $notAssessedCount ?>)
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="row text-center">
                        <div class="col-3">
                            <div class="border rounded p-2">
                                <div class="h4 mb-0 text-success"><?= $compliantCount ?></div>
                                <small class="text-muted">Compliant</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-2">
                                <div class="h4 mb-0 text-warning"><?= $partialCount ?></div>
                                <small class="text-muted">Partial</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-2">
                                <div class="h4 mb-0 text-danger"><?= $nonCompliantCount ?></div>
                                <small class="text-muted">Non-compliant</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-2">
                                <div class="h4 mb-0 text-secondary"><?= $notAssessedCount ?></div>
                                <small class="text-muted">Not Assessed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Compliance Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Detailed Compliance Tracking</h5>
            <div class="d-flex gap-2">
                <select class="form-select form-select-sm" style="width: auto;" id="filterProgram">
                    <option value="">All Programs</option>
                    <?php 
                    $programsStmt = $pdo->query("SELECT program_id, program_code, program_name FROM programs ORDER BY program_code");
                    $programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($programs as $program): ?>
                    <option value="<?= $program['program_id'] ?>"><?= htmlspecialchars($program['program_code']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <select class="form-select form-select-sm" style="width: auto;" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="compliant">Compliant</option>
                    <option value="partial">Partial</option>
                    <option value="non_compliant">Non-compliant</option>
                    <option value="not_assessed">Not Assessed</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="complianceTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Program</th>
                            <th width="15%">Area/Indicator</th>
                            <th width="15%">Criteria</th>
                            <th width="10%">Status</th>
                            <th width="10%">Score</th>
                            <th width="10%">Evidence</th>
                            <th width="10%">Review Date</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 1;
                        foreach ($complianceData as $record): 
                            // Determine row class based on status
                            $rowClass = '';
                            $statusBadge = '';
                            switch ($record['status']) {
                                case 'compliant':
                                    $rowClass = 'table-success';
                                    $statusBadge = 'badge bg-success';
                                    break;
                                case 'partial':
                                    $rowClass = 'table-warning';
                                    $statusBadge = 'badge bg-warning';
                                    break;
                                case 'non_compliant':
                                    $rowClass = 'table-danger';
                                    $statusBadge = 'badge bg-danger';
                                    break;
                                case 'not_assessed':
                                    $rowClass = 'table-secondary';
                                    $statusBadge = 'badge bg-secondary';
                                    break;
                            }
                            
                            // Get reviewer name
                            $reviewerName = $record['reviewer_first'] ? 
                                $record['reviewer_first'] . ' ' . substr($record['reviewer_last'], 0, 1) . '.' : 
                                'Not reviewed';
                                
                            // Format score
                            $scoreDisplay = $record['compliance_score'] ? 
                                number_format($record['compliance_score'], 1) . '%' : 'N/A';
                        ?>
                        <tr class="<?= $rowClass ?>" data-program-id="<?= $record['program_id'] ?>"
                            data-status="<?= $record['status'] ?>">
                            <td><?= $count++ ?></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($record['program_code']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($record['program_name']) ?></small>
                            </td>
                            <td>
                                <div class="small">
                                    <div><strong>Area <?= $record['area_number'] ?>:</strong>
                                        <?= htmlspecialchars($record['area_name']) ?></div>
                                    <div><strong>Indicator:</strong> <?= htmlspecialchars($record['indicator_code']) ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($record['criteria_number']): ?>
                                <div class="small">
                                    <div><strong><?= htmlspecialchars($record['criteria_number']) ?>:</strong></div>
                                    <div><?= htmlspecialchars(substr($record['criteria_description'], 0, 50)) ?>...
                                    </div>
                                </div>
                                <?php else: ?>
                                <span class="text-muted">No specific criteria</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="<?= $statusBadge ?>">
                                    <?= ucfirst(str_replace('_', ' ', $record['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold"><?= $scoreDisplay ?></div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file me-2 text-muted"></i>
                                    <span class="badge bg-light text-dark">
                                        <?= $record['evidence_count'] ?>
                                        file<?= $record['evidence_count'] != 1 ? 's' : '' ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php if ($record['review_date']): ?>
                                <div class="small">
                                    <?= htmlspecialchars($record['review_date']) ?>
                                    <br>
                                    <small class="text-muted">by <?= htmlspecialchars($reviewerName) ?></small>
                                </div>
                                <?php else: ?>
                                <span class="text-muted">Not reviewed</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary edit-compliance-btn"
                                        data-compliance-id="<?= $record['compliance_id'] ?>"
                                        data-program-id="<?= $record['program_id'] ?>"
                                        data-cycle-id="<?= $record['cycle_id'] ?>"
                                        data-indicator-id="<?= $record['indicator_id'] ?>"
                                        data-criteria-id="<?= $record['criteria_id'] ?>"
                                        data-status="<?= $record['status'] ?>"
                                        data-score="<?= $record['compliance_score'] ?>"
                                        data-evidence-count="<?= $record['evidence_count'] ?>"
                                        data-reviewer-id="<?= $record['reviewer_id'] ?>"
                                        data-review-date="<?= $record['review_date'] ?>"
                                        data-comments="<?= htmlspecialchars($record['comments']) ?>">
                                        <i class="fas fa-edit text-dark"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info view-compliance-btn"
                                        data-compliance-id="<?= $record['compliance_id'] ?>"
                                        data-program-code="<?= htmlspecialchars($record['program_code']) ?>"
                                        data-program-name="<?= htmlspecialchars($record['program_name']) ?>"
                                        data-cycle-name="<?= htmlspecialchars($record['cycle_name']) ?>"
                                        data-academic-year="<?= htmlspecialchars($record['academic_year']) ?>"
                                        data-area-number="<?= $record['area_number'] ?>"
                                        data-area-name="<?= htmlspecialchars($record['area_name']) ?>"
                                        data-indicator-code="<?= htmlspecialchars($record['indicator_code']) ?>"
                                        data-indicator-description="<?= htmlspecialchars($record['indicator_description']) ?>"
                                        data-criteria-number="<?= htmlspecialchars($record['criteria_number'] ?? '') ?>"
                                        data-criteria-description="<?= htmlspecialchars($record['criteria_description'] ?? '') ?>"
                                        data-status="<?= $record['status'] ?>"
                                        data-score="<?= $record['compliance_score'] ?>"
                                        data-evidence-count="<?= $record['evidence_count'] ?>"
                                        data-reviewer-name="<?= htmlspecialchars(($record['reviewer_first'] ?? '') . ' ' . ($record['reviewer_last'] ?? '')) ?>"
                                        data-review-date="<?= $record['review_date'] ?>"
                                        data-comments="<?= htmlspecialchars($record['comments'] ?? '') ?>"
                                        data-created-at="<?= $record['created_at'] ?>"
                                        data-updated-at="<?= $record['updated_at'] ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger delete-compliance-btn"
                                        data-compliance-id="<?= $record['compliance_id'] ?>"
                                        data-program="<?= htmlspecialchars($record['program_code']) ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (empty($complianceData)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-database fa-2x mb-3 d-block"></i>
                                No compliance records found. Add your first compliance assessment.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Add Compliance Modal (DONE!)-->
<div class="modal fade" id="addComplianceModal" tabindex="-1" aria-labelledby="addComplianceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="add-compliance-form" class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title text-white" id="addComplianceModalLabel">Add Compliance Assessment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    onclick="location.reload()"></button>
            </div>
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($admin_id) ?>">
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Program</label>
                        <select name="program_id" class="form-select" required>
                            <option value="">Select Program</option>
                            <?php foreach($programs as $program): ?>
                            <option value="<?= $program['program_id'] ?>">
                                <?= htmlspecialchars($program['program_code']) ?> -
                                <?= htmlspecialchars($program['program_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Accreditation Cycle</label>
                        <select name="cycle_id" class="form-select" required>
                            <option value="">Select Cycle</option>
                            <?php 
                            $cyclesStmt = $pdo->query("SELECT cycle_id, cycle_name, academic_year FROM accreditation_cycles ORDER BY academic_year DESC");
                            $cycles = $cyclesStmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach($cycles as $cycle): ?>
                            <option value="<?= $cycle['cycle_id'] ?>">
                                <?= htmlspecialchars($cycle['cycle_name']) ?>
                                (<?= htmlspecialchars($cycle['academic_year']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Indicator</label>
                        <select name="indicator_id" class="form-select" required>
                            <option value="">Select Indicator</option>
                            <?php 
                            $indicatorsStmt = $pdo->query("
                                SELECT ai.indicator_id, ai.indicator_code, ai.indicator_description, 
                                       aa.area_number, aa.area_name
                                FROM accreditation_indicators ai
                                JOIN accreditation_areas aa ON ai.area_id = aa.area_id
                                ORDER BY aa.area_number, ai.indicator_code
                            ");
                            $indicators = $indicatorsStmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach($indicators as $indicator): ?>
                            <option value="<?= $indicator['indicator_id'] ?>">
                                Area <?= $indicator['area_number'] ?>:
                                <?= htmlspecialchars($indicator['indicator_code']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Specific Criteria (Optional)</label>
                        <select name="criteria_id" class="form-select">
                            <option value="">Select Criteria (Optional)</option>
                            <?php 
                            $criteriaStmt = $pdo->query("
                                SELECT criteria_id, criteria_number, criteria_description 
                                FROM accreditation_criteria 
                                ORDER BY criteria_number
                            ");
                            $criteria = $criteriaStmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach($criteria as $criterion): ?>
                            <option value="<?= $criterion['criteria_id'] ?>">
                                <?= htmlspecialchars($criterion['criteria_number']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Compliance Status</label>
                        <select name="status" class="form-select" required>
                            <option value="not_assessed">Not Assessed</option>
                            <option value="compliant">Compliant</option>
                            <option value="partial">Partial</option>
                            <option value="non_compliant">Non-compliant</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Compliance Score (0-100)</label>
                        <input type="number" class="form-control" name="compliance_score" min="0" max="100" step="0.1"
                            placeholder="e.g., 85.5">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Evidence Count</label>
                        <input type="number" class="form-control" name="evidence_count" min="0" value="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reviewer (Optional)</label>
                        <select name="reviewer_id" class="form-select">
                            <option value="">Select Reviewer</option>
                            <?php 
                            $reviewersStmt = $pdo->query("
                                SELECT user_id, first_name, last_name 
                                FROM users 
                                WHERE user_role IN ('admin', 'QA', 'head')
                                ORDER BY first_name
                            ");
                            $reviewers = $reviewersStmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach($reviewers as $reviewer): ?>
                            <option value="<?= $reviewer['user_id'] ?>">
                                <?= htmlspecialchars($reviewer['first_name'] . ' ' . $reviewer['last_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Review Date</label>
                        <input type="date" class="form-control" name="review_date">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Comments/Remarks</label>
                        <textarea class="form-control" name="comments" rows="3"
                            placeholder="Enter comments, observations, or recommendations..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn bg-gradient-primary">
                    <i class="fas fa-save me-1"></i> Save Assessment
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- View Compliance Details Modal -->
<div class="modal fade" id="viewComplianceModal" tabindex="-1" aria-labelledby="viewComplianceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title text-white" id="viewComplianceModalLabel">Compliance Assessment Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Program & Cycle Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>Program:</th>
                                <td id="complianceProgram"></td>
                            </tr>
                            <tr>
                                <th>Cycle:</th>
                                <td id="complianceCycle"></td>
                            </tr>
                            <tr>
                                <th>Evidence Count:</th>
                                <td id="complianceEvidence"></td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td id="complianceCreated"></td>
                            </tr>
                            <tr>
                                <th>Updated:</th>
                                <td id="complianceUpdated"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Assessment Details</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>Status:</th>
                                <td id="complianceStatus"></td>
                            </tr>
                            <tr>
                                <th>Score:</th>
                                <td id="complianceScore"></td>
                            </tr>
                            <tr>
                                <th>Reviewer:</th>
                                <td id="complianceReviewer"></td>
                            </tr>
                            <tr>
                                <th>Review Date:</th>
                                <td id="complianceReviewDate"></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Accreditation Framework</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-dark"><strong>Area:</strong> <span id="complianceArea"></span></p>
                                <p class="text-dark"><strong>Indicator:</strong> <span id="complianceIndicator"></span></p>
                                <div id="complianceCriteriaContainer" class="text-dark">
                                    <!-- Will be populated if criteria exists -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="mb-0">Review Comments</h6>
                            </div>
                            <div class="card-body">
                                <div id="complianceComments"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printComplianceBtn"
                    onclick="printComplianceDetails()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Compliance Modal (DONE!)-->
<div class="modal fade" id="editComplianceModal" tabindex="-1" aria-labelledby="editComplianceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="edit-compliance-form" class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title text-white" id="editComplianceModalLabel">Edit Compliance Assessment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($admin_id) ?>">
            <div class="modal-body">
                <input type="hidden" name="compliance_id" id="editComplianceId">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Program</label>
                        <select name="program_id" id="editProgramId" class="form-select" required>
                            <option value="">Select Program</option>
                            <?php foreach($programs as $program): ?>
                            <option value="<?= $program['program_id'] ?>">
                                <?= htmlspecialchars($program['program_code']) ?> -
                                <?= htmlspecialchars($program['program_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Accreditation Cycle</label>
                        <select name="cycle_id" id="editCycleId" class="form-select" required>
                            <option value="">Select Cycle</option>
                            <?php foreach($cycles as $cycle): ?>
                            <option value="<?= $cycle['cycle_id'] ?>">
                                <?= htmlspecialchars($cycle['cycle_name']) ?>
                                (<?= htmlspecialchars($cycle['academic_year']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Other fields same as add modal, with id="editFieldName" -->
                    <div class="col-md-6">
                        <label class="form-label">Indicator</label>
                        <select name="indicator_id" id="editIndicatorId" class="form-select" required>
                            <option value="">Select Indicator</option>
                            <?php foreach($indicators as $indicator): ?>
                            <option value="<?= $indicator['indicator_id'] ?>">
                                Area <?= $indicator['area_number'] ?>:
                                <?= htmlspecialchars($indicator['indicator_code']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Specific Criteria</label>
                        <select name="criteria_id" id="editCriteriaId" class="form-select">
                            <option value="">Select Criteria (Optional)</option>
                            <?php foreach($criteria as $criterion): ?>
                            <option value="<?= $criterion['criteria_id'] ?>">
                                <?= htmlspecialchars($criterion['criteria_number']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" id="editStatus" class="form-select" required>
                            <option value="not_assessed">Not Assessed</option>
                            <option value="compliant">Compliant</option>
                            <option value="partial">Partial</option>
                            <option value="non_compliant">Non-compliant</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Compliance Score</label>
                        <input type="number" class="form-control" id="editScore" name="compliance_score" min="0"
                            max="100" step="0.1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Evidence Count</label>
                        <input type="number" class="form-control" id="editEvidenceCount" name="evidence_count" min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reviewer</label>
                        <select name="reviewer_id" id="editReviewerId" class="form-select">
                            <option value="">Select Reviewer</option>
                            <?php foreach($reviewers as $reviewer): ?>
                            <option value="<?= $reviewer['user_id'] ?>">
                                <?= htmlspecialchars($reviewer['first_name'] . ' ' . $reviewer['last_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Review Date</label>
                        <input type="date" class="form-control" id="editReviewDate" name="review_date">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Comments</label>
                        <textarea class="form-control" id="editComments" name="comments" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save me-1"></i> Update Assessment
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Compliance Modal (DONE!) -->
<div class="modal fade" id="deleteComplianceModal" tabindex="-1" aria-labelledby="deleteComplianceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <form id="delete-compliance-form" class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title text-white" id="deleteComplianceModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($admin_id) ?>">
            <div class="modal-body">
                <div id="deleteComplianceMessage">
                    Are you sure you want to delete this compliance assessment?
                </div>
                <input type="hidden" name="compliance_id" id="deleteComplianceId">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i> Yes, Delete
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Remove the duplicate DOMContentLoaded wrapper and fix the structure
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Filter functionality
    const filterProgram = document.getElementById('filterProgram');
    const filterStatus = document.getElementById('filterStatus');

    function filterTable() {
        const programFilter = filterProgram.value;
        const statusFilter = filterStatus.value;
        const rows = document.querySelectorAll('#complianceTable tbody tr');

        rows.forEach(row => {
            const programId = row.getAttribute('data-program-id');
            const status = row.getAttribute('data-status');

            let showRow = true;

            if (programFilter && programId !== programFilter) {
                showRow = false;
            }

            if (statusFilter && status !== statusFilter) {
                showRow = false;
            }

            row.style.display = showRow ? '' : 'none';
        });
    }

    if (filterProgram) filterProgram.addEventListener('change', filterTable);
    if (filterStatus) filterStatus.addEventListener('change', filterTable);

    // Event listeners for compliance buttons
    document.querySelectorAll('.edit-compliance-btn').forEach(button => {
        button.addEventListener('click', function() {
            const complianceId = this.getAttribute('data-compliance-id');
            const programId = this.getAttribute('data-program-id');
            const cycleId = this.getAttribute('data-cycle-id');
            const indicatorId = this.getAttribute('data-indicator-id');
            const criteriaId = this.getAttribute('data-criteria-id');
            const status = this.getAttribute('data-status');
            const score = this.getAttribute('data-score');
            const evidenceCount = this.getAttribute('data-evidence-count');
            const reviewerId = this.getAttribute('data-reviewer-id');
            const reviewDate = this.getAttribute('data-review-date');
            const comments = this.getAttribute('data-comments');

            // Set form values
            document.getElementById('editComplianceId').value = complianceId;
            document.getElementById('editProgramId').value = programId;
            document.getElementById('editCycleId').value = cycleId;
            document.getElementById('editIndicatorId').value = indicatorId;
            document.getElementById('editCriteriaId').value = criteriaId || '';
            document.getElementById('editStatus').value = status;
            document.getElementById('editScore').value = score;
            document.getElementById('editEvidenceCount').value = evidenceCount;
            document.getElementById('editReviewerId').value = reviewerId || '';
            document.getElementById('editReviewDate').value = reviewDate;
            document.getElementById('editComments').value = comments;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editComplianceModal'));
            modal.show();
        });
    });

    // Add view compliance button event listeners HERE (not inside another DOMContentLoaded)
    document.querySelectorAll('.view-compliance-btn').forEach(button => {
        button.addEventListener('click', function() {
            console.log('View compliance button clicked');
            
            // Get all data attributes from the button
            const complianceId = this.getAttribute('data-compliance-id');
            const programCode = this.getAttribute('data-program-code');
            const programName = this.getAttribute('data-program-name');
            const cycleName = this.getAttribute('data-cycle-name');
            const academicYear = this.getAttribute('data-academic-year');
            const areaNumber = this.getAttribute('data-area-number');
            const areaName = this.getAttribute('data-area-name');
            const indicatorCode = this.getAttribute('data-indicator-code');
            const indicatorDescription = this.getAttribute('data-indicator-description');
            const criteriaNumber = this.getAttribute('data-criteria-number');
            const criteriaDescription = this.getAttribute('data-criteria-description');
            const status = this.getAttribute('data-status');
            const score = this.getAttribute('data-score');
            const evidenceCount = this.getAttribute('data-evidence-count');
            const reviewerName = this.getAttribute('data-reviewer-name');
            const reviewDate = this.getAttribute('data-review-date');
            const comments = this.getAttribute('data-comments');
            const createdAt = this.getAttribute('data-created-at');
            const updatedAt = this.getAttribute('data-updated-at');
            
            // Debug log to check values
            console.log('Compliance ID:', complianceId);
            console.log('Program:', programCode, programName);
            
            // Call the viewCompliance function
            if (typeof viewCompliance === 'function') {
                viewCompliance(
                    complianceId, programCode, programName, cycleName, academicYear,
                    areaNumber, areaName, indicatorCode, indicatorDescription,
                    criteriaNumber, criteriaDescription, status, score, evidenceCount,
                    reviewerName, reviewDate, comments, createdAt, updatedAt
                );
            } else {
                console.error('viewCompliance function is not defined!');
                alert('Error: viewCompliance function not found. Please check JavaScript.');
            }
        });
    });

    document.querySelectorAll('.delete-compliance-btn').forEach(button => {
        button.addEventListener('click', function() {
            const complianceId = this.getAttribute('data-compliance-id');
            const programName = this.getAttribute('data-program');

            document.getElementById('deleteComplianceId').value = complianceId;
            document.getElementById('deleteComplianceMessage').innerHTML =
                `Are you sure you want to delete the compliance assessment for <strong>${programName}</strong>?`;

            const modal = new bootstrap.Modal(document.getElementById('deleteComplianceModal'));
            modal.show();
        });
    });

    // Make sure print button works - THIS WAS ADDED IN A SEPARATE DOMContentLoaded
    const printBtn = document.getElementById('printComplianceBtn');
    if (printBtn) {
        printBtn.addEventListener('click', printComplianceDetails);
    }
}); // <-- This closes the DOMContentLoaded event listener

// Move the viewCompliance function OUTSIDE of DOMContentLoaded (make it global)
function viewCompliance(complianceId, programCode, programName, cycleName, academicYear,
                    areaNumber, areaName, indicatorCode, indicatorDescription,
                    criteriaNumber, criteriaDescription, status, score, evidenceCount,
                    reviewerName, reviewDate, comments, createdAt, updatedAt) {
    
    console.log('Opening compliance view for ID:', complianceId);
    
    // Check if modal elements exist
    const modalElement = document.getElementById('viewComplianceModal');
    if (!modalElement) {
        console.error('Modal element #viewComplianceModal not found!');
        alert('Error: Compliance modal not found. Please check HTML.');
        return;
    }
    
    // Set basic information
    document.getElementById('complianceProgram').textContent = `${programCode} - ${programName}`;
    document.getElementById('complianceCycle').textContent = `${cycleName} (${academicYear})`;
    document.getElementById('complianceEvidence').textContent = `${evidenceCount} document${evidenceCount != 1 ? 's' : ''}`;
    document.getElementById('complianceCreated').textContent = new Date(createdAt).toLocaleString();
    document.getElementById('complianceUpdated').textContent = new Date(updatedAt).toLocaleString();
    
    // Set assessment details
    const statusText = status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
    let statusClass = '';
    switch (status) {
        case 'compliant':
            statusClass = 'badge bg-success';
            break;
        case 'partial':
            statusClass = 'badge bg-warning';
            break;
        case 'non_compliant':
            statusClass = 'badge bg-danger';
            break;
        case 'not_assessed':
            statusClass = 'badge bg-secondary';
            break;
    }
    document.getElementById('complianceStatus').innerHTML = `<span class="${statusClass}">${statusText}</span>`;
    
    document.getElementById('complianceScore').textContent = score ? `${score}%` : 'Not scored';
    document.getElementById('complianceReviewer').textContent = reviewerName.trim() || 'Not assigned';
    document.getElementById('complianceReviewDate').textContent = reviewDate ? new Date(reviewDate).toLocaleDateString() : 'Not reviewed';
    
    // Set accreditation framework
    document.getElementById('complianceArea').textContent = `Area ${areaNumber}: ${areaName}`;
    document.getElementById('complianceIndicator').textContent = `${indicatorCode}: ${indicatorDescription}`;
    
    // Set criteria if exists
    const criteriaContainer = document.getElementById('complianceCriteriaContainer');
    if (criteriaContainer) {
        if (criteriaNumber && criteriaDescription) {
            criteriaContainer.innerHTML = `<p><strong>Criteria:</strong> ${criteriaNumber}: ${criteriaDescription}</p>`;
        } else {
            criteriaContainer.innerHTML = '<p class="text-muted"><em>No specific criteria selected</em></p>';
        }
    }
    
    // Set comments
    const commentsElement = document.getElementById('complianceComments');
    if (commentsElement) {
        if (comments && comments.trim()) {
            commentsElement.innerHTML = `<div class="alert alert-info">${comments}</div>`;
        } else {
            commentsElement.innerHTML = '<p class="text-muted text-center py-3"><em>No comments provided</em></p>';
        }
    }
    
    // Store data for printing
    window.currentComplianceData = {
        complianceId, programCode, programName, cycleName, academicYear,
        areaNumber, areaName, indicatorCode, indicatorDescription,
        criteriaNumber, criteriaDescription, status, score, evidenceCount,
        reviewerName, reviewDate, comments, createdAt, updatedAt
    };
    
    // Open modal
    try {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        console.log('Modal opened successfully');
    } catch (error) {
        console.error('Error opening modal:', error);
        alert('Error opening modal: ' + error.message);
    }
}

// Print function (also make it global)
function printComplianceDetails() {
    if (!window.currentComplianceData) {
        alert('No compliance data to print. Please view a compliance record first.');
        return;
    }
    
    const data = window.currentComplianceData;
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <html>
        <head>
            <title>Compliance Assessment - ${data.programCode}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #333; border-bottom: 2px solid #333; padding-bottom: 10px; }
                .section { margin: 20px 0; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f5f5f5; }
                .badge { padding: 4px 8px; border-radius: 4px; color: white; }
                .badge-success { background-color: #28a745; }
                .badge-warning { background-color: #ffc107; }
                .badge-danger { background-color: #dc3545; }
                .badge-secondary { background-color: #6c757d; }
                .comments { background-color: #f8f9fa; padding: 15px; border-left: 4px solid #17a2b8; margin: 20px 0; }
            </style>
        </head>
        <body>
            <h1>Compliance Assessment Report</h1>
            
            <div class="section">
                <h3>Program & Cycle Information</h3>
                <table>
                    <tr><th width="30%">Program</th><td>${data.programCode} - ${data.programName}</td></tr>
                    <tr><th>Accreditation Cycle</th><td>${data.cycleName} (${data.academicYear})</td></tr>
                    <tr><th>Evidence Count</th><td>${data.evidenceCount} documents</td></tr>
                    <tr><th>Created</th><td>${new Date(data.createdAt).toLocaleString()}</td></tr>
                    <tr><th>Last Updated</th><td>${new Date(data.updatedAt).toLocaleString()}</td></tr>
                </table>
            </div>
            
            <div class="section">
                <h3>Assessment Details</h3>
                <table>
                    <tr><th width="30%">Status</th><td>${data.status.replace('_', ' ').toUpperCase()}</td></tr>
                    <tr><th>Compliance Score</th><td>${data.score || 'N/A'}</td></tr>
                    <tr><th>Reviewer</th><td>${data.reviewerName || 'Not assigned'}</td></tr>
                    <tr><th>Review Date</th><td>${data.reviewDate ? new Date(data.reviewDate).toLocaleDateString() : 'Not reviewed'}</td></tr>
                </table>
            </div>
            
            <div class="section">
                <h3>Accreditation Framework</h3>
                <table class="bg-gradient-primary">
                    <tr class="text-dark"><th width="30%">Area</th><td class="text-dark">Area ${data.areaNumber}: ${data.areaName}</td></tr>
                    <tr class="text-dark"><th>Indicator</th><td class="text-dark">${data.indicatorCode}: ${data.indicatorDescription}</td></tr>
                    ${data.criteriaNumber ? `<tr><th><p class="text-dark">Criteria</th><t>${data.criteriaNumber}: ${data.criteriaDescription}</td></p></tr>` : ''}
                </table>
            </div>
            
            ${data.comments ? `
            <div class="section">
                <h3>Review Comments</h3>
                <div class="comments">${data.comments}</div>
            </div>
            ` : ''}
            
            <div style="margin-top: 50px; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
                Report generated: ${new Date().toLocaleString()}
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
}

</script>