<?php
// Fetch compliance data - FIXED QUERY
$complianceStmt = $pdo->prepare("
    SELECT 
        ct.*,
        p.program_code,
        p.program_name,
        ac.cycle_name,
        ac.academic_year,
        ai.indicator_code,
        ai.indicator_description,
        COALESCE(ap.parameter_name, 'Not Assigned') as parameter_name,
        COALESCE(aa.area_number, '0') as area_number,
        COALESCE(aa.area_name, 'Not Categorized') as area_name,
        u.first_name as reviewer_first,
        u.last_name as reviewer_last,
        (SELECT COUNT(*) FROM documents d 
         WHERE d.program_id = ct.program_id 
         AND d.indicator_id = ct.indicator_id
         AND d.cycle_id = ct.cycle_id) as evidence_count
    FROM compliance_tracking ct
    LEFT JOIN programs p ON ct.program_id = p.program_id
    LEFT JOIN accreditation_cycles ac ON ct.cycle_id = ac.cycle_id
    LEFT JOIN accreditation_indicators ai ON ct.indicator_id = ai.indicator_id
    LEFT JOIN accreditation_parameters ap ON ai.parameter_id = ap.parameter_id 
    LEFT JOIN accreditation_areas aa ON ap.area_id = aa.area_id 
    LEFT JOIN users u ON ct.reviewer_id = u.user_id
    ORDER BY 
        CASE 
            WHEN aa.area_number IS NULL OR aa.area_number = '' THEN 999 
            ELSE CAST(aa.area_number AS UNSIGNED) 
        END,
        COALESCE(ai.indicator_code, 'ZZZ'),
        ct.program_id
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
            <small class="text-muted">Showing <?= $totalRecords ?> compliance records</small>
        </div>
    </div>

    <div class="row">
        <!-- Overall Compliance Score -->
        <div class="col-md-3 mb-3">
            <div class="card rounded-1">
                <div class="card-body text-center">
                    <h5 class="card-title">Overall Compliance</h5>
                    <h1 class="display-4 fw-bold"><?= $compliancePercentage ?>%</h1>
                    <p class="card-text mb-0"><?= $compliantCount ?> of <?= $totalRecords ?> criteria compliant</p>
                    <small><?= $totalAssessed ?> assessed, <?= $notAssessedCount ?> pending</small>
                </div>
            </div>
        </div>

        <!-- Compliance Status Breakdown -->
        <div class="col-md-9 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Compliance Status Breakdown</h5>
                    <div class="progress mb-3" style="height: 30px;">
                        <?php if ($compliantCount > 0): ?>
                        <div class="progress-bar bg-success" style="width: <?= ($totalRecords > 0 ? ($compliantCount/$totalRecords)*100 : 0) ?>%"
                            title="Compliant: <?= $compliantCount ?> criteria">
                            Compliant (<?= $compliantCount ?>)
                        </div>
                        <?php endif; ?>

                        <?php if ($partialCount > 0): ?>
                        <div class="progress-bar bg-warning" style="width: <?= ($totalRecords > 0 ? ($partialCount/$totalRecords)*100 : 0) ?>%"
                            title="Partial: <?= $partialCount ?> criteria">
                            Partial (<?= $partialCount ?>)
                        </div>
                        <?php endif; ?>

                        <?php if ($nonCompliantCount > 0): ?>
                        <div class="progress-bar bg-danger"
                            style="width: <?= ($totalRecords > 0 ? ($nonCompliantCount/$totalRecords)*100 : 0) ?>%"
                            title="Non-compliant: <?= $nonCompliantCount ?> criteria">
                            Non-compliant (<?= $nonCompliantCount ?>)
                        </div>
                        <?php endif; ?>

                        <?php if ($notAssessedCount > 0): ?>
                        <div class="progress-bar bg-secondary"
                            style="width: <?= ($totalRecords > 0 ? ($notAssessedCount/$totalRecords)*100 : 0) ?>%"
                            title="Not assessed: <?= $notAssessedCount ?> criteria">
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
                    $programsStmt = $pdo->prepare("SELECT program_id, program_code, program_name FROM programs ORDER BY program_code");
                    $programsStmt->execute();
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
                <select class="form-select form-select-sm" style="width: auto;" id="filterArea">
                    <option value="">All Areas</option>
                    <?php 
                    $areasStmt = $pdo->prepare("SELECT DISTINCT area_number, area_name FROM accreditation_areas ORDER BY CAST(area_number AS UNSIGNED)");
                    $areasStmt->execute();
                    $areas = $areasStmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($areas as $area): ?>
                    <option value="<?= htmlspecialchars($area['area_number']) ?>">Area <?= htmlspecialchars($area['area_number']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-bordered table-hover mb-0" id="complianceTable" style="table-layout: fixed; width: 100%;">
                    <thead class="table-light sticky-top" style="top: 0; z-index: 1020;">
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th style="width: 120px;">Program</th>
                            <th style="width: 200px;">Area/Indicator</th>
                            <th style="width: 80px;">Status</th>
                            <th style="width: 70px;">Score</th>
                            <th style="width: 80px;">Evidence</th>
                            <th style="width: 100px;">Review Date</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 1;
                        $currentArea = null;
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
                            $scoreDisplay = !empty($record['compliance_score']) ? 
                                number_format($record['compliance_score'], 1) . '%' : 'N/A';
                                
                            // Get evidence count (calculated in query)
                            $evidenceCount = $record['evidence_count'] ?? 0;
                            
                            // Handle area information
                            $areaNumber = $record['area_number'];
                            $areaName = $record['area_name'];
                            $areaDisplay = $areaNumber !== '0' ? 
                                "Area {$areaNumber}: {$areaName}" : 
                                "Not Categorized";
                            
                            // Truncate long text
                            $programName = htmlspecialchars($record['program_name']);
                            $areaNameDisplay = htmlspecialchars($areaName);
                            $indicatorDesc = htmlspecialchars($record['indicator_description']);
                            
                            if (strlen($programName) > 30) {
                                $programName = substr($programName, 0, 27) . '...';
                            }
                            if (strlen($areaNameDisplay) > 40) {
                                $areaNameDisplay = substr($areaNameDisplay, 0, 37) . '...';
                            }
                            if (strlen($indicatorDesc) > 50) {
                                $indicatorDesc = substr($indicatorDesc, 0, 47) . '...';
                            }
                            
                            // Highlight area changes
                            $areaGroupClass = '';
                            if ($currentArea !== $areaNumber) {
                                $currentArea = $areaNumber;
                                $areaGroupClass = 'area-group';
                            }
                        ?>
                        <tr class="<?= $rowClass . ' ' . $areaGroupClass ?>" 
                            data-program-id="<?= $record['program_id'] ?>"
                            data-status="<?= $record['status'] ?>" 
                            data-area="<?= $areaNumber ?>"
                            data-indicator-id="<?= $record['indicator_id'] ?>">
                            <td style="width: 40px;"><?= $count++ ?></td>
                            <td style="width: 120px;">
                                <div class="fw-bold" title="<?= htmlspecialchars($record['program_code']) ?>">
                                    <?= htmlspecialchars($record['program_code']) ?>
                                </div>
                                <small class="text-muted" title="<?= htmlspecialchars($record['program_name']) ?>">
                                    <?= $programName ?>
                                </small>
                            </td>
                            <td style="width: 200px; max-width: 200px;">
                                <div class="small">
                                    <div class="text-truncate" title="<?= htmlspecialchars($areaDisplay) ?>">
                                        <strong><?= $areaNumber !== '0' ? "Area {$areaNumber}:" : "Category:" ?></strong> <?= $areaNameDisplay ?>
                                    </div>
                                    <div class="text-truncate mt-1" title="<?= htmlspecialchars($record['indicator_code']) ?>: <?= htmlspecialchars($record['indicator_description']) ?>">
                                        <strong>Indicator:</strong> <?= htmlspecialchars($record['indicator_code']) ?>: <?= $indicatorDesc ?>
                                    </div>
                                    <?php if ($record['parameter_name'] && $record['parameter_name'] !== 'Not Assigned'): ?>
                                    <div class="text-truncate mt-1" title="Parameter: <?= htmlspecialchars($record['parameter_name']) ?>">
                                        <small><strong>Parameter:</strong> <?= htmlspecialchars($record['parameter_name']) ?></small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="width: 80px;">
                                <span class="<?= $statusBadge ?>">
                                    <?= ucfirst(str_replace('_', ' ', $record['status'])) ?>
                                </span>
                            </td>
                            <td style="width: 70px;">
                                <div class="fw-bold"><?= $scoreDisplay ?></div>
                            </td>
                            <td style="width: 80px;">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-file me-1 text-muted"></i>
                                    <span class="badge bg-light text-dark">
                                        <?= $evidenceCount ?>
                                    </span>
                                </div>
                            </td>
                            <td style="width: 100px;">
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
                            <td style="width: 100px;">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-dark edit-compliance-btn"
                                        data-compliance-id="<?= $record['compliance_id'] ?>"
                                        data-program-id="<?= $record['program_id'] ?>"
                                        data-cycle-id="<?= $record['cycle_id'] ?>"
                                        data-indicator-id="<?= $record['indicator_id'] ?>"
                                        data-status="<?= $record['status'] ?>"
                                        data-score="<?= $record['compliance_score'] ?>"
                                        data-evidence-count="<?= $evidenceCount ?>"
                                        data-reviewer-id="<?= $record['reviewer_id'] ?>"
                                        data-review-date="<?= $record['review_date'] ?>"
                                        data-comments="<?= htmlspecialchars($record['comments'] ?? '') ?>"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info view-compliance-btn"
                                        data-compliance-id="<?= $record['compliance_id'] ?>"
                                        data-program-code="<?= htmlspecialchars($record['program_code']) ?>"
                                        data-program-name="<?= htmlspecialchars($record['program_name']) ?>"
                                        data-cycle-name="<?= htmlspecialchars($record['cycle_name']) ?>"
                                        data-academic-year="<?= htmlspecialchars($record['academic_year']) ?>"
                                        data-area-number="<?= $areaNumber ?>"
                                        data-area-name="<?= htmlspecialchars($areaName) ?>"
                                        data-indicator-code="<?= htmlspecialchars($record['indicator_code']) ?>"
                                        data-indicator-description="<?= htmlspecialchars($record['indicator_description']) ?>"
                                        data-parameter-name="<?= htmlspecialchars($record['parameter_name']) ?>"
                                        data-status="<?= $record['status'] ?>"
                                        data-score="<?= $record['compliance_score'] ?>"
                                        data-evidence-count="<?= $evidenceCount ?>"
                                        data-reviewer-name="<?= htmlspecialchars(($record['reviewer_first'] ?? '') . ' ' . ($record['reviewer_last'] ?? '')) ?>"
                                        data-review-date="<?= $record['review_date'] ?>"
                                        data-comments="<?= htmlspecialchars($record['comments'] ?? '') ?>"
                                        title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <!-- <button type="button" class="btn btn-outline-danger delete-compliance-btn"
                                        data-compliance-id="<?= $record['compliance_id'] ?>"
                                        data-program="<?= htmlspecialchars($record['program_code']) ?>"
                                        title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button> -->
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (empty($complianceData)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-database fa-2x mb-3 d-block"></i>
                                No compliance records found. 
                                <div class="mt-2">
                                    <a href="?page=add_compliance" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus me-1"></i> Add Compliance Record
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-2">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">Showing <?= count($complianceData) ?> records (<?= $compliantCount ?> compliant, <?= $partialCount ?> partial, <?= $nonCompliantCount ?> non-compliant, <?= $notAssessedCount ?> not assessed)</small>
                <?php if ($totalRecords > 20): ?>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Add CSS for better table handling -->
<style>
.compliance-dashboard table {
    font-size: 0.85rem;
}

.compliance-dashboard table td, 
.compliance-dashboard table th {
    padding: 0.5rem !important;
    vertical-align: middle !important;
}

.compliance-dashboard .table-responsive {
    border-radius: 0.375rem;
}

.compliance-dashboard .text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.compliance-dashboard .btn-group-sm > .btn {
    padding: 0.25rem 0.4rem;
}

/* Ensure table doesn't expand */
.compliance-dashboard .table {
    min-width: 100%;
    margin-bottom: 0;
}

/* Make the table header sticky */
.compliance-dashboard .sticky-top {
    background-color: #f8f9fa;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Area grouping highlight */
.area-group {
    border-top: 2px solid #0d6efd;
}

.area-group td {
    border-top-color: #0d6efd !important;
}
</style>

<!-- View Compliance Details Modal -->
<div class="modal fade" id="viewComplianceModal" tabindex="-1" aria-labelledby="viewComplianceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewComplianceModalLabel">Compliance Assessment Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <style>
                                        .truncate-text {
                                            display: inline-block;
                                            max-width: 15rem;     /* your desired width */
                                            white-space: nowrap; 
                                            overflow: hidden;
                                            text-overflow: ellipsis;
                                        }
                                    </style>
                        <h6>Program & Cycle Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>Program:</th>
                                <td id="complianceProgram" class="truncate-text"></td>
                            </tr>
                            <tr>
                                <th>Cycle:</th>
                                <td id="complianceCycle" class="truncate-text"></td>
                            </tr>
                            <tr>
                                <th>Evidence Count:</th>
                                <td id="complianceEvidence"></td>
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
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Accreditation Framework</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="text-dark"><strong>Area:</strong> <br><span id="complianceArea"></span></p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="text-dark"><strong>Parameter:</strong> <br><span id="complianceParameter"></span></p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="text-dark"><strong>Indicator:</strong> <br><span id="complianceIndicator"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card">
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
                <button type="button" class="btn btn-primary" id="printComplianceBtn">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Compliance Modal -->
<div class="modal fade" id="editComplianceModal" tabindex="-1" aria-labelledby="editComplianceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <form id="edit-compliance-form" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editComplianceModalLabel">Edit Compliance Assessment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="compliance_id" id="editComplianceId">
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" id="editStatus" class="form-select" required>
                        <option value="not_assessed">Not Assessed</option>
                        <option value="compliant">Compliant</option>
                        <option value="partial">Partial</option>
                        <option value="non_compliant">Non-compliant</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Compliance Score (0-100)</label>
                    <input type="number" class="form-control" id="editScore" name="compliance_score" min="0"
                        max="100" step="0.1" placeholder="Enter score if applicable">
                </div>
                <div class="mb-3">
                    <label class="form-label">Reviewer</label>
                    <select name="reviewer_id" id="editReviewerId" class="form-select">
                        <option value="">Select Reviewer</option>
                        <?php 
                        $reviewersStmt = $pdo->prepare("SELECT user_id, first_name, last_name FROM users WHERE user_role IN ('qa', 'admin', 'head') AND is_active = 'Active'");
                        $reviewersStmt->execute();
                        $reviewers = $reviewersStmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach($reviewers as $reviewer): ?>
                        <option value="<?= $reviewer['user_id'] ?>">
                            <?= htmlspecialchars($reviewer['first_name'] . ' ' . $reviewer['last_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Review Date</label>
                    <input type="date" class="form-control" id="editReviewDate" name="review_date" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Comments</label>
                    <textarea class="form-control" id="editComments" name="comments" rows="3" placeholder="Enter assessment comments..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Update
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Compliance Modal -->
<div class="modal fade" id="deleteComplianceModal" tabindex="-1" aria-labelledby="deleteComplianceModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteComplianceModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="deleteComplianceMessage">
                    Are you sure you want to delete this compliance assessment?
                </div>
                <input type="hidden" name="compliance_id" id="deleteComplianceId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-1"></i> Yes, Delete
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const filterProgram = document.getElementById('filterProgram');
    const filterStatus = document.getElementById('filterStatus');
    const filterArea = document.getElementById('filterArea');

    function filterTable() {
        const programFilter = filterProgram ? filterProgram.value : '';
        const statusFilter = filterStatus ? filterStatus.value : '';
        const areaFilter = filterArea ? filterArea.value : '';
        const rows = document.querySelectorAll('#complianceTable tbody tr');

        rows.forEach(row => {
            const programId = row.getAttribute('data-program-id');
            const status = row.getAttribute('data-status');
            const area = row.getAttribute('data-area');

            let showRow = true;

            if (programFilter && programId !== programFilter) {
                showRow = false;
            }

            if (statusFilter && status !== statusFilter) {
                showRow = false;
            }

            if (areaFilter && area !== areaFilter) {
                showRow = false;
            }

            row.style.display = showRow ? '' : 'none';
        });
    }

    if (filterProgram) filterProgram.addEventListener('change', filterTable);
    if (filterStatus) filterStatus.addEventListener('change', filterTable);
    if (filterArea) filterArea.addEventListener('change', filterTable);

    // Edit button functionality
    document.querySelectorAll('.edit-compliance-btn').forEach(button => {
        button.addEventListener('click', function() {
            const complianceId = this.getAttribute('data-compliance-id');
            const status = this.getAttribute('data-status');
            const score = this.getAttribute('data-score');
            const reviewerId = this.getAttribute('data-reviewer-id');
            const reviewDate = this.getAttribute('data-review-date');
            const comments = this.getAttribute('data-comments');

            // Set form values
            document.getElementById('editComplianceId').value = complianceId;
            document.getElementById('editStatus').value = status;
            document.getElementById('editScore').value = score;
            document.getElementById('editReviewerId').value = reviewerId || '';
            document.getElementById('editReviewDate').value = reviewDate || '<?= date('Y-m-d') ?>';
            document.getElementById('editComments').value = comments || '';

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editComplianceModal'));
            modal.show();
        });
    });

    // View button functionality
    document.querySelectorAll('.view-compliance-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Get data attributes
            const programCode = this.getAttribute('data-program-code');
            const programName = this.getAttribute('data-program-name');
            const cycleName = this.getAttribute('data-cycle-name');
            const academicYear = this.getAttribute('data-academic-year');
            const areaNumber = this.getAttribute('data-area-number');
            const areaName = this.getAttribute('data-area-name');
            const parameterName = this.getAttribute('data-parameter-name');
            const indicatorCode = this.getAttribute('data-indicator-code');
            const indicatorDescription = this.getAttribute('data-indicator-description');
            const status = this.getAttribute('data-status');
            const score = this.getAttribute('data-score');
            const evidenceCount = this.getAttribute('data-evidence-count');
            const reviewerName = this.getAttribute('data-reviewer-name');
            const reviewDate = this.getAttribute('data-review-date');
            const comments = this.getAttribute('data-comments');

            // Update modal content
            document.getElementById('complianceProgram').textContent = `${programCode} - ${programName}`;
            document.getElementById('complianceCycle').textContent = `${cycleName} (${academicYear})`;
            document.getElementById('complianceEvidence').textContent = `${evidenceCount} document${evidenceCount != 1 ? 's' : ''}`;
            
            const statusText = status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
            let statusClass = '';
            switch (status) {
                case 'compliant': statusClass = 'badge bg-success'; break;
                case 'partial': statusClass = 'badge bg-warning'; break;
                case 'non_compliant': statusClass = 'badge bg-danger'; break;
                case 'not_assessed': statusClass = 'badge bg-secondary'; break;
            }
            document.getElementById('complianceStatus').innerHTML = `<span class="${statusClass}">${statusText}</span>`;
            
            document.getElementById('complianceScore').textContent = score && score !== '0' && score !== '' ? `${score}%` : 'Not scored';
            document.getElementById('complianceReviewer').textContent = reviewerName.trim() ? reviewerName.trim() : 'Not assigned';
            document.getElementById('complianceReviewDate').textContent = reviewDate ? new Date(reviewDate).toLocaleDateString() : 'Not reviewed';
            document.getElementById('complianceArea').textContent = areaNumber !== '0' ? `Area ${areaNumber}: ${areaName}` : 'Not Categorized';
            document.getElementById('complianceParameter').textContent = parameterName && parameterName !== 'Not Assigned' ? parameterName : 'Not Assigned';
            document.getElementById('complianceIndicator').textContent = `${indicatorCode}: ${indicatorDescription}`;
            
            // Set comments
            const commentsElement = document.getElementById('complianceComments');
            if (comments && comments.trim()) {
                commentsElement.innerHTML = `<div class="alert alert-info p-3">${comments}</div>`;
            } else {
                commentsElement.innerHTML = '<p class="text-muted text-center py-3"><em>No comments provided</em></p>';
            }

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('viewComplianceModal'));
            modal.show();
        });
    });

    // Delete button functionality
    document.querySelectorAll('.delete-compliance-btn').forEach(button => {
        button.addEventListener('click', function() {
            const complianceId = this.getAttribute('data-compliance-id');
            const programName = this.getAttribute('data-program');

            document.getElementById('deleteComplianceId').value = complianceId;
            document.getElementById('deleteComplianceMessage').innerHTML =
                `Are you sure you want to delete the compliance assessment for <strong>${programName}</strong>?<br><br>
                 <small class="text-danger">This action cannot be undone.</small>`;

            const modal = new bootstrap.Modal(document.getElementById('deleteComplianceModal'));
            modal.show();
        });
    });

    // Print button
    const printBtn = document.getElementById('printComplianceBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            const modalTitle = document.getElementById('viewComplianceModalLabel').innerText;
            const printContent = document.querySelector('#viewComplianceModal .modal-body').innerHTML;
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>${modalTitle}</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        h3 { color: #0d6efd; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                        th, td { padding: 8px; border: 1px solid #ddd; }
                        th { background-color: #f8f9fa; }
                        .badge { padding: 4px 8px; border-radius: 4px; color: white; }
                        .alert-info { background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 4px; }
                        .text-muted { color: #6c757d; }
                        @media print { 
                            .no-print { display: none; }
                            body { padding: 0; }
                        }
                    </style>
                </head>
                <body>
                    <h3>${modalTitle}</h3>
                    ${printContent}
                    <div class="no-print" style="margin-top: 30px; text-align: center;">
                        <button onclick="window.print()" style="padding: 10px 20px; background: #0d6efd; color: white; border: none; border-radius: 4px; cursor: pointer;">Print</button>
                        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">Close</button>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
        });
    }

});
</script>