<?php
// Fetch user's department info
$userDeptStmt = $pdo->prepare("
    SELECT 
        u.user_id,
        u.first_name,
        u.last_name,
        u.user_role,
        d.department_id,
        d.department_name,
        d.department_code
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.department_id
    WHERE u.user_id = ?
    AND u.user_role IN ('head', 'admin', 'department_head')
");
$userDeptStmt->execute([$user_id]);
$userInfo = $userDeptStmt->fetch(PDO::FETCH_ASSOC);

if (!$userInfo || empty($userInfo['department_id'])) {
    echo "<div class='alert alert-danger'>Error: User not found or not authorized as department head.</div>";
    return;
}

$department_id = $userInfo['department_id'];
$department_name = $userInfo['department_name'];
$department_code = $userInfo['department_code'];

// Fetch all programs in this department
$programsStmt = $pdo->prepare("
    SELECT * FROM programs 
    WHERE department_id = ?
    ORDER BY program_name
");
$programsStmt->execute([$department_id]);
$programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($programs)) {
    echo "<div class='alert alert-warning'>No programs found for {$department_name} department.</div>";
}

// Get program IDs for the department
$program_ids = array_column($programs, 'program_id');

// Fetch all assignments for department programs
if (!empty($program_ids)) {
    $program_ids_placeholder = implode(',', array_fill(0, count($program_ids), '?'));
    $stmtAssignments = $pdo->prepare("SELECT 
        a.assignment_id,
        ac.deadline,
        a.status as assignment_status,
        a.*,
        u.user_id as faculty_id,
        CONCAT(u.first_name, ' ', u.last_name) as faculty_name,
        ac.deadline,
        u.user_id as faculty_id,
        i.indicator_code,
        i.indicator_description,
        p.program_code,
        p.program_name,
        p.department_id,
        ac.cycle_name,
        ac.status as cycle_status,
        d.department_name
    FROM assignments a
    INNER JOIN users u ON a.user_id = u.user_id
    INNER JOIN accreditation_indicators i ON a.indicator_id = i.indicator_id
    INNER JOIN programs p ON a.program_id = p.program_id
    INNER JOIN departments d ON p.department_id = d.department_id
    INNER JOIN accreditation_cycles ac ON a.cycle_id = ac.cycle_id
    WHERE p.department_id = ?
    GROUP BY a.assignment_id
    ORDER BY ac.deadline ASC, a.assigned_at DESC");
    $stmtAssignments->execute([$department_id]);
    $assignments = $stmtAssignments->fetchAll(PDO::FETCH_ASSOC);
} else {
    $assignments = [];
}

// Fetch reviewers (users with QA role in the department)
$stmtReviewers = $pdo->prepare("SELECT DISTINCT 
                               u.user_id, 
                               CONCAT(u.first_name, ' ', u.last_name) as name, 
                               u.user_role 
                               FROM users u
                               INNER JOIN programs p ON u.program_id = p.program_id
                               WHERE u.user_role = 'qa' 
                               AND p.department_id = ?
                               AND u.is_active = 'Active' 
                               ORDER BY u.first_name");
$stmtReviewers->execute([$department_id]);
$reviewers = $stmtReviewers->fetchAll(PDO::FETCH_ASSOC);

// Fetch submitted documents for review in department
if (!empty($program_ids)) {
    $program_ids_placeholder = implode(',', array_fill(0, count($program_ids), '?'));
    $stmtDocuments = $pdo->prepare("SELECT 
        d.document_id,
        d.filename,
        d.assignment_id,
        d.status as doc_status,
        d.created_at as uploaded_date,
        u.user_id as uploader_id,
        CONCAT(u.first_name, ' ', u.last_name) as uploader_name,
        i.indicator_code,
        i.indicator_description,
        p.program_name,
        p.program_code,
        ac.cycle_name,
        COUNT(r.review_id) as review_count
    FROM documents d
    INNER JOIN users u ON d.uploaded_by = u.user_id
    INNER JOIN accreditation_indicators i ON d.indicator_id = i.indicator_id
    INNER JOIN programs p ON d.program_id = p.program_id
    INNER JOIN accreditation_cycles ac ON d.cycle_id = ac.cycle_id
    LEFT JOIN reviews r ON d.document_id = r.document_id
    WHERE d.status IN ('submitted', 'under_review', 'needs_revision') 
    AND p.department_id = ?
    GROUP BY d.document_id
    ORDER BY d.created_at DESC");
    $stmtDocuments->execute([$department_id]);
    $documents = $stmtDocuments->fetchAll(PDO::FETCH_ASSOC);
} else {
    $documents = [];
}

$assignmentCount = 1;
$documentCount = 1;
?>
<section style="height: 88vh; overflow-y: scroll; overflow-x: hidden;">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="mx-2">
            <h4 class=""><span class=""><i class="fa-solid fa-clipboard-check me-2"></i></span>Review Management</h4>
            <small class="text-muted">Assign reviewers and manage document reviews for <?php echo htmlspecialchars($department_name); ?> Department</small>
        </div>
    </div>

    <!-- DEPARTMENT INFO -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($department_name); ?> Department</h5>
                    <p class="card-text text-dark">
                        <i class="fa fa-building text-dark"></i> 
                        <strong><?php echo htmlspecialchars($department_code); ?></strong> | 
                        <i class="fa fa-graduation-cap text-dark ms-3"></i>
                        <strong><?php echo count($programs); ?> Programs</strong> | 
                        <i class="fa fa-users text-dark ms-3"></i>
                        <strong><?php echo count($assignments); ?> Assignments</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- REVIEW STATISTICS CARDS -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h6 class="card-title text-dark">Pending Assignments</h6>
                    <h2 class="text-dark"><?= count($assignments) ?></h2>
                    <small class="text-muted">All programs in department</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h6 class="card-title text-dark">Documents for Review</h6>
                    <h2 class="text-dark"><?= count($documents) ?></h2>
                    <small class="text-muted">All programs in department</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <?php
            if (!empty($program_ids)) {
                $program_ids_placeholder = implode(',', array_fill(0, count($program_ids), '?'));
                $stmtNeedsRevision = $pdo->prepare("SELECT COUNT(*) FROM documents d
                                                   INNER JOIN programs p ON d.program_id = p.program_id
                                                   WHERE d.status = 'needs_revision' AND p.department_id = ?");
                $stmtNeedsRevision->execute([$department_id]);
                $needsRevision = $stmtNeedsRevision->fetchColumn();
            } else {
                $needsRevision = 0;
            }
            ?>
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h6 class="card-title text-dark">Needs Revision</h6>
                    <h2 class="text-dark"><?= $needsRevision ?></h2>
                    <small class="text-muted">Require resubmission</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <?php
            if (!empty($program_ids)) {
                $program_ids_placeholder = implode(',', array_fill(0, count($program_ids), '?'));
                $stmtCompleted = $pdo->prepare("SELECT COUNT(*) FROM assignments a
                                               INNER JOIN programs p ON a.program_id = p.program_id
                                               WHERE a.status = 'completed' AND p.department_id = ?");
                $stmtCompleted->execute([$department_id]);
                $completed = $stmtCompleted->fetchColumn();
            } else {
                $completed = 0;
            }
            ?>
            <div class="card border-success">
                <div class="card-body text-center">
                    <h6 class="card-title text-dark">Completed</h6>
                    <h2 class="text-dark"><?= $completed ?></h2>
                    <small class="text-muted">Fully reviewed</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body col-md-12 col-12 d-flex justify-content-between">
        <ul class="nav nav-tabs col-md-6 col-12" id="AccreditationFrameworkTab">
            <li class="nav-item col-md-6">
                <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#assignments"> <i
                        class="fa-solid fa-tasks me-2"></i> Assignments</a>
            </li>
            <li class="nav-item col-md-6">
                <a class="nav-link" data-bs-toggle="tab" data-bs-target="#documents"><i
                        class="fa-solid fa-file-alt me-2"></i> Document Reviews</a>
            </li>
        </ul>
        <div class="col-md-6 d-flex justify-content-end">
            <button class="btn bg-gradient-primary" data-bs-toggle="modal" data-bs-target="#createAccounts">+ create
                assignment</button>
        </div>
    </div>

    <!-- Edit Assignment Modal -->
    <div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-labelledby="editAssignmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAssignmentModalLabel">Edit Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-assignment-form">
                        <input type="hidden" id="editAssignmentId" name="assignment_id">

                        <div class="row g-3">
                            <!-- Cycle (Read-only) -->
                            <div class="col-md-6">
                                <label class="form-label">Cycle</label>
                                <?php
                                $stmt = $pdo->prepare("SELECT * FROM accreditation_cycles WHERE status = 'active'");
                                $stmt->execute();
                                $activeCycle = $stmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                                <input type="hidden" name="cycle_id"
                                    value="<?= htmlspecialchars($activeCycle['cycle_id'] ?? '') ?>">
                                <input type="text" class="form-control"
                                    value="<?= htmlspecialchars(($activeCycle['cycle_name'] ?? '') . ' (' . ($activeCycle['academic_year'] ?? '') . ')') ?>"
                                    readonly>
                            </div>

                            <!-- Program Selection (only from department) -->
                            <div class="col-md-6">
                                <label class="form-label">Program *</label>
                                <select name="program_id" id="editProgramId" class="form-select" required
                                    onchange="loadFacultyForProgram(this.value)">
                                    <option value="">Select Program</option>
                                    <?php foreach($programs as $prog) : ?>
                                    <option value="<?= htmlspecialchars($prog["program_id"]) ?>">
                                        <?= htmlspecialchars($prog["program_name"] . ' (' . $prog["program_code"] . ')') ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Faculty (will be loaded based on program) -->
                            <div class="col-md-6">
                                <label class="form-label">Faculty *</label>
                                <select name="faculty_id" id="editFacultyId" class="form-select" required>
                                    <option value="">Select Faculty</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label class="form-label">Status *</label>
                                <select name="status" id="editStatus" class="form-select" required>
                                    <option value="not_started">Not Started</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>

                            <!-- Assigned By (Read-only) -->
                            <div class="col-md-6">
                                <label class="form-label">Assigned By</label>
                                <input type="text" class="form-control"
                                    value="<?= htmlspecialchars($userInfo['first_name'] . ' ' . $userInfo['last_name']) ?>"
                                    readonly>
                                <input type="hidden" name="assigned_by"
                                    value="<?= htmlspecialchars($userInfo['user_id']) ?>">
                            </div>
                        </div>

                        <div class="modal-footer mt-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="editAssignmentSubmitBtn">
                                <i class="fas fa-save me-1"></i> Update Assignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete assignment Modal -->
    <div class="modal fade" id="deleteAssignmentModal" tabindex="-1" aria-labelledby="deleteAssignmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="delete-assignment-form" class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteAssignmentModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    Are you sure you want to delete this assignment?
                    <input type="hidden" name="assignment_id" id="deleteAssignment_id">
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB CONTENT -->
    <div class="tab-content" id="reviewTabContent">

        <!-- TAB 1: ASSIGNMENTS TAB -->
        <div class="tab-pane fade show active" id="assignments" role="tabpanel">
            <!-- Search and Filters -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" placeholder="Search assignments..." id="searchAssignments" class="form-control">
                </div>
                <div class="col-md-4">
                    <select id="filterStatus" class="form-select">
                        <option value="">All Status</option>
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="submitted">Submitted</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="filterProgram" class="form-select">
                        <option value="">All Programs</option>
                        <?php foreach($programs as $program) {
                            echo '<option value="' . htmlspecialchars($program['program_code']) . '">' . htmlspecialchars($program['program_code']) . '</option>';
                        } ?>
                    </select>
                </div>
            </div>

            <!-- ASSIGNMENTS TABLE -->
            <div class="table-responsive">
                <table class="table table-sm table-hover" style="font-size: 0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Faculty</th>
                            <th>Program</th>
                            <th>Indicator</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="assignmentsTable">
                        <?php foreach($assignments as $assignment) : 
                            $statusBadge = '';
                            switch($assignment['assignment_status']) {
                                case 'submitted': $statusBadge = 'badge bg-success'; break;
                                case 'in_progress': $statusBadge = 'badge bg-primary'; break;
                                case 'not_started': $statusBadge = 'badge bg-secondary'; break;
                                default: $statusBadge = 'badge bg-warning';
                            }
                            
                            $isOverdue = $assignment['deadline'] && strtotime($assignment['deadline']) < time() && $assignment['assignment_status'] != 'completed';
                        ?>
                        <tr <?= $isOverdue ? 'class="table-warning"' : '' ?>>
                            <td><?= $assignmentCount++ ?></td>
                            <td>
                                <strong><?= htmlspecialchars($assignment['faculty_name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($assignment['department_name']) ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($assignment['program_code']) ?></strong><br>
                                <small><?= htmlspecialchars($assignment['program_name']) ?></small>
                            </td>
                            <td style="width: 3rem !important;">
                                <span class="badge bg-info"><?= htmlspecialchars($assignment['indicator_code']) ?></span><br>
                                <small><?= htmlspecialchars(substr($assignment['indicator_description'], 0, 20)) ?>...</small>
                            </td>
                            <td>
                                <?= htmlspecialchars($assignment['deadline'] ?? 'No deadline') ?>
                                <?php if($isOverdue): ?>
                                <br><span class="badge bg-danger">Overdue</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="<?= $statusBadge ?>">
                                    <?= ucfirst(str_replace('_', ' ', $assignment['assignment_status'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-sm btn-primary" onclick="editAssignment('<?= $assignment['assignment_id'] ?>',
                                        '<?= $assignment['program_id'] ?>',
                                        '<?= $assignment['faculty_id'] ?>',
                                        '<?= htmlspecialchars($assignment['assignment_status']) ?>')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>

                                    <button type="button" class="btn btn-sm btn-danger"
                                        onclick="setDeleteAssignment_id('<?= $assignment['assignment_id'] ?>')"
                                        data-bs-toggle="modal" data-bs-target="#deleteAssignmentModal">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: DOCUMENT REVIEWS TAB -->
        <div class="tab-pane fade" id="documents" role="tabpanel">
            <!-- Document Status Filter -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" placeholder="Search documents..." id="searchDocuments" class="form-control">
                </div>
                <div class="col-md-4">
                    <select id="filterDocStatusDocuments" class="form-select">
                        <option value="">All Status</option>
                        <option value="submitted">Submitted</option>
                        <option value="under_review">Under Review</option>
                        <option value="needs_revision">Needs Revision</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="filterProgramDocuments" class="form-select">
                        <option value="">All Programs</option>
                        <?php foreach($programs as $program) {
                            echo '<option value="' . htmlspecialchars($program['program_code']) . '">' . htmlspecialchars($program['program_code']) . '</option>';
                        } ?>
                    </select>
                </div>
            </div>

            <!-- DOCUMENTS TABLE -->
            <div class="table-responsive">
                <table class="table table-sm table-hover" style="font-size: 0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Uploaded By</th>
                            <th>Program</th>
                            <th>Indicator</th>
                            <th>Status</th>
                            <th>Reviews</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="documentsTable">
                        <?php foreach($documents as $document) : 
                            $statusBadge = '';
                            switch($document['doc_status']) {
                                case 'approved': $statusBadge = 'badge bg-success'; break;
                                case 'under_review': $statusBadge = 'badge bg-primary'; break;
                                case 'needs_revision': $statusBadge = 'badge bg-warning'; break;
                                case 'submitted': $statusBadge = 'badge bg-info'; break;
                                default: $statusBadge = 'badge bg-secondary';
                            }
                        ?>
                        <tr>
                            <td><?= $documentCount++ ?></td>
                            <td><?= htmlspecialchars($document['uploader_name']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($document['program_code']) ?></strong><br>
                                <small><?= htmlspecialchars($document['program_name']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= htmlspecialchars($document['indicator_code']) ?></span><br>
                                <small><?= htmlspecialchars(substr($document['indicator_description'], 0, 40)) ?>...</small>
                            </td>
                            <td>
                                <span class="<?= $statusBadge ?>">
                                    <?= ucfirst(str_replace('_', ' ', $document['doc_status'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if($document['review_count'] > 0): ?>
                                <span class="badge bg-secondary"><?= $document['review_count'] ?> reviews</span>
                                <?php else: ?>
                                <span class="badge bg-light text-dark">No reviews</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-success assign-document-reviewer-btn"
                                        data-assignment-id="<?= $document['assignment_id'] ?>"
                                        data-document-id="<?= $document['document_id'] ?>"
                                        data-faculty-id="<?= $document['uploader_id'] ?>" data-bs-toggle="modal"
                                        data-bs-target="#assignReviewerModal" id="getDocumentId">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODALS -->
    <!-- Assign Reviewer Modal -->
    <div class="modal fade" id="assignReviewerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Assign Reviewer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="assign-reviewer-form">
                        <input type="hidden" id="get_assignment_id" name="assignment_id">
                        <input type="hidden" id="get_document_id" name="document_id">
                        <input type="hidden" id="get_faculty_id" name="faculty_id">

                        <div class="mb-3">
                            <label class="form-label">Reviewer</label>
                            <select id="modalReviewerSelect" name="reviewer_id" class="form-select" required>
                                <option value="">Select Reviewer</option>
                                <?php foreach($reviewers as $reviewer): ?>
                                <option value="<?= $reviewer['user_id'] ?>">
                                    <?= htmlspecialchars($reviewer['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deadline</label>
                            <input type="date" id="modalReviewDeadline" name="deadline" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary"
                                onclick="submitReviewerAssignment()">Assign</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- assign faculty Modal -->
    <div class="modal fade" id="createAccounts" tabindex="-1" aria-labelledby="createAccountsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title text-white" id="createAccountsLabel">Create Assignment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                        onclick="location.reload()"></button>
                </div>
                <?php
                $stmt = $pdo->prepare("SELECT * FROM accreditation_cycles WHERE status = 'active'");
                $stmt->execute();
                $cycles = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Fetch all areas
                $stmt = $pdo->prepare("SELECT * FROM accreditation_areas ORDER BY area_number");
                $stmt->execute();
                $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $cycle_name = $cycles["cycle_name"] ?? '';
                $academic_year = $cycles["academic_year"] ?? '';
                $cycle_id = $cycles["cycle_id"] ?? '';
                ?>
                <div class="modal-body">
                    <form class="row g-1 align-items-start justify-content-start" id="assignment-form">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
                        <input type="hidden" name="department_id" value="<?= htmlspecialchars($department_id) ?>">
                        
                        <label class="form-label">Cycle</label>
                        <div class="col-12">
                            <input type="hidden" class="form-control" name="cycle_id"
                                value="<?= htmlspecialchars($cycle_id) ?>">
                            <p class="form-control text-dark border" readonly>
                                <?php 
                                    if(empty($cycle_name)){
                                        echo 'No active cycle';
                                    } else { 
                                        echo htmlspecialchars($cycle_name . ' (' . $academic_year . ')');
                                    } 
                                ?>
                            </p>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Program *</label>
                            <select name="program_id" id="program_id" class="form-select" required onchange="loadFacultyForProgram(this.value)">
                                <option value="">Select Program</option>
                                <?php foreach($programs as $prog) : ?>
                                <option value="<?= htmlspecialchars($prog["program_id"]) ?>">
                                    <?= htmlspecialchars($prog["program_name"] . ' (' . $prog["program_code"] . ')') ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <!-- Faculty Selection (will be loaded dynamically) -->
                        <div class="col-12">
                            <label class="form-label">Faculty *</label>
                            <select name="faculty_id" id="faculty_id" class="form-select" required disabled>
                                <option value="">Select Faculty</option>
                            </select>
                        </div>

                        <!-- Area Selection -->
                        <div class="col-12">
                            <label class="form-label">Area</label>
                            <select name="area_id" id="area_id" class="form-select" required>
                                <option value="">Select Area</option>
                                <?php foreach($areas as $area) : ?>
                                <option value="<?= htmlspecialchars($area["area_id"]) ?>">
                                    <?= htmlspecialchars($area["area_number"] . ' - ' . $area["area_name"]) ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <!-- Parameter Selection (will be loaded dynamically) -->
                        <div class="col-12">
                            <label class="form-label">Parameter</label>
                            <select name="parameter_id" id="parameter_id" class="form-select" required disabled>
                                <option value="">Select Parameter</option>
                            </select>
                        </div>

                        <!-- Indicators Checkbox Section -->
                        <div class="col-12">
                            <label class="form-label">Indicators <span class="text-danger">*</span></label>
                            <div class="border rounded p-3 bg-light" id="indicators_container" style="display: none;">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="select_all_indicators">
                                    <label class="form-check-label fw-bold" for="select_all_indicators">
                                        Select All Indicators
                                    </label>
                                </div>
                                <hr class="my-2">
                                <div id="indicators_checkboxes" class="row">
                                    <div class="col-12 text-center text-muted">
                                        <i class="bi bi-info-circle"></i> Select a parameter above to load indicators
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="indicator_ids" id="indicator_ids" value="">
                            <small class="text-muted">Select one or more indicators for this assignment.</small>
                            <div id="indicator_error" class="text-danger mt-1" style="display: none;">
                                Please select at least one indicator
                            </div>
                        </div>

                        <!-- Status Selection -->
                        <div class="col-12">
                            <label class="form-label">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="">Select status</option>
                                <option value="not_started">Not Started</option>
                                <option value="in_progress">In Progress</option>
                                <option value="submitted">Submitted</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-primary px-5" id="submit-btn">
                                <i class="bi bi-person-plus-fill me-1"></i> Create Assignment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ASSIGN FACULTY JS ========================================================================== -->
<script>
$(document).ready(function() {
    // When area selection changes, load parameters
    $('#area_id').on('change', function() {
        var areaId = $(this).val();
        loadParameters(areaId);
    });

    // When parameter selection changes, load indicators
    $('#parameter_id').on('change', function() {
        var parameterId = $(this).val();
        loadIndicators(parameterId);
    });
    $('#program_id').on('change', function() {
        var programId = $(this).val();
        loadFacultyForProgram(programId);
    });
});

// Function to load parameters based on selected area
function loadParameters(areaId) {
    var parameterSelect = $('#parameter_id');

    if (!areaId || areaId === '') {
        parameterSelect.html('<option value="">Select Parameter</option>').prop('disabled', true);
        $('#indicators_container').hide();
        $('#indicators_checkboxes').html(
            '<div class="col-12 text-center text-muted"><i class="bi bi-info-circle"></i> Select a parameter above to load indicators</div>'
            );
        return;
    }

    parameterSelect.html('<option value="">Loading parameters...</option>').prop('disabled', true);

    $.ajax({
        url: base_url + "/authentication/action.php?action=get_parameters_by_area",
        method: 'POST',
        dataType: 'json',
        data: {
            area_id: areaId
        },
        success: function(response) {
            if (response.status == 1 && response.data && response.data.length > 0) {
                var options = '<option value="">Select Parameter</option>';
                $.each(response.data, function(index, parameter) {
                    options +=
                        `<option value="${parameter.parameter_id}">${parameter.parameter_code} - ${parameter.parameter_name}</option>`;
                });
                parameterSelect.html(options).prop('disabled', false);
            } else {
                parameterSelect.html('<option value="">No parameters available</option>').prop('disabled',
                    true);
            }

            $('#indicators_container').hide();
            $('#indicators_checkboxes').html(
                '<div class="col-12 text-center text-muted"><i class="bi bi-info-circle"></i> Select a parameter above to load indicators</div>'
                );
            $('#indicator_ids').val('');
        },
        error: function(xhr, status, error) {
            parameterSelect.html('<option value="">Error loading parameters</option>').prop('disabled',
                true);
        }
    });
}

// Function to load indicators as checkboxes based on selected parameter
function loadIndicators(parameterId, selectedIndicatorIds = []) {
    var container = $('#indicators_container');
    var checkboxesContainer = $('#indicators_checkboxes');
    var hiddenInput = $('#indicator_ids');

    if (!parameterId || parameterId === '') {
        container.hide();
        checkboxesContainer.html(
            '<div class="col-12 text-center text-muted"><i class="bi bi-info-circle"></i> Select a parameter above to load indicators</div>'
        );
        hiddenInput.val('');
        $('#select_all_indicators').prop('checked', false).prop('disabled', true);
        return;
    }

    container.show();
    checkboxesContainer.html(`
        <div class="col-12 text-center">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            <span class="ms-2">Loading indicators...</span>
        </div>
    `);
    $('#select_all_indicators').prop('checked', false).prop('disabled', true);

    $.ajax({
        url: base_url + "/authentication/action.php?action=get_indicators_by_parameter",
        method: 'POST',
        dataType: 'json',
        data: {
            parameter_id: parameterId
        },
        success: function(response) {
            if (response.status == 1 && response.data && response.data.length > 0) {
                checkboxesContainer.empty();

                if (typeof selectedIndicatorIds === 'string') {
                    selectedIndicatorIds = selectedIndicatorIds.split(',').map(id => id.trim()).filter(id =>
                        id !== '');
                } else if (!Array.isArray(selectedIndicatorIds)) {
                    selectedIndicatorIds = [];
                }

                var indicatorsPerColumn = Math.ceil(response.data.length / 2);
                var currentCount = 0;

                var column1 = $('<div class="col-md-6"></div>');
                var column2 = $('<div class="col-md-6"></div>');

                $.each(response.data, function(index, indicator) {
                    var isChecked = selectedIndicatorIds.includes(indicator.indicator_id.toString());
                    var checkboxId = 'indicator_' + indicator.indicator_id;
                    var fullDescription = indicator.indicator_description || '';
                    var shortDescription = fullDescription.substring(0, 60);
                    if (fullDescription.length > 60) shortDescription += '...';

                    var checkboxHtml = `
                        <div class="form-check mb-2">
                            <input class="form-check-input indicator-checkbox" 
                                   type="checkbox" 
                                   id="${checkboxId}"
                                   value="${indicator.indicator_id}"
                                   ${isChecked ? 'checked' : ''}>
                            <label class="form-check-label" for="${checkboxId}" 
                                   title="${fullDescription}"
                                   data-bs-toggle="tooltip">
                                <strong>${indicator.indicator_code}</strong>: ${shortDescription}
                            </label>
                        </div>
                    `;

                    if (currentCount < indicatorsPerColumn) {
                        column1.append(checkboxHtml);
                    } else {
                        column2.append(checkboxHtml);
                    }
                    currentCount++;
                });

                checkboxesContainer.append(column1);
                checkboxesContainer.append(column2);

                $('[data-bs-toggle="tooltip"]').tooltip();
                $('#select_all_indicators').prop('disabled', false);
                updateIndicatorIds();
                updateSelectAllIndicatorState();

            } else {
                checkboxesContainer.html(`
                    <div class="col-12 text-center text-muted">
                        <i class="bi bi-info-circle"></i> No indicators available for this parameter
                    </div>
                `);
                hiddenInput.val('');
            }
        },
        error: function(xhr, status, error) {
            checkboxesContainer.html(`
                <div class="col-12 text-center text-danger">
                    <i class="bi bi-exclamation-triangle"></i> Error loading indicators
                </div>
            `);
            hiddenInput.val('');
        }
    });
}

// Function to load faculty for selected program in department
function loadFacultyForProgram(programId) {
    var facultySelect = $('#faculty_id');
    var editFacultySelect = $('#editFacultyId');

    if (!programId || programId === '') {
        facultySelect.html('<option value="">Select Faculty</option>').prop('disabled', true);
        editFacultySelect.html('<option value="">Select Faculty</option>').prop('disabled', true);
        return;
    }

    // Show loading state for create modal
    facultySelect.html('<option value="">Loading faculty...</option>').prop('disabled', true);
    editFacultySelect.html('<option value="">Loading faculty...</option>').prop('disabled', true);

    $.ajax({
        url: base_url + "/authentication/action.php?action=get_faculty",
        method: 'POST',
        dataType: 'json',
        data: {
            program_id: programId,
            department_id: <?= $department_id ?>
        },
        success: function(response) {
            console.log('Faculty Response:', response);

            if (response.status == 1 && response.data && response.data.length > 0) {
                var options = '<option value="">Select Faculty</option>';
                $.each(response.data, function(index, faculty) {
                    options +=
                        `<option value="${faculty.user_id}">${faculty.first_name} ${faculty.last_name} (${faculty.user_role})</option>`;
                });
                
                facultySelect.html(options).prop('disabled', false);
                editFacultySelect.html(options).prop('disabled', false);
            } else {
                var noFacultyMsg = '<option value="">No faculty available for this program</option>';
                facultySelect.html(noFacultyMsg).prop('disabled', true);
                editFacultySelect.html(noFacultyMsg).prop('disabled', true);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            var errorMsg = '<option value="">Error loading faculty</option>';
            facultySelect.html(errorMsg).prop('disabled', true);
            editFacultySelect.html(errorMsg).prop('disabled', true);
        }
    });
}

// Function to update hidden input with selected indicator values
function updateIndicatorIds() {
    var selectedValues = [];
    $('.indicator-checkbox:checked').each(function() {
        selectedValues.push($(this).val());
    });

    $('#indicator_ids').val(selectedValues.join(','));

    var countBadge = $('#selected_indicators_count_badge');
    if (selectedValues.length > 0) {
        if (countBadge.length === 0) {
            $('#indicators_container label').append(
                ` <span class="badge bg-primary" id="selected_indicators_count_badge">${selectedValues.length} selected</span>`
            );
        } else {
            countBadge.text(selectedValues.length + ' selected');
        }
        $('#indicator_error').hide();
    } else {
        countBadge.remove();
    }
}

// Function to update select all indicators checkbox state
function updateSelectAllIndicatorState() {
    var totalCheckboxes = $('.indicator-checkbox').length;
    var checkedCount = $('.indicator-checkbox:checked').length;

    $('#select_all_indicators').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCount);
}

// Select All indicators functionality
$(document).on('change', '#select_all_indicators', function() {
    var isChecked = $(this).is(':checked');
    $('.indicator-checkbox').prop('checked', isChecked);
    updateIndicatorIds();
    updateSelectAllIndicatorState();
});

// Update hidden input when individual indicator checkboxes change
$(document).on('change', '.indicator-checkbox', function() {
    updateIndicatorIds();
    updateSelectAllIndicatorState();
});

// Form validation
function validateForm() {
    var indicatorIds = $('#indicator_ids').val();
    var isValid = true;

    if (!indicatorIds || indicatorIds.trim() === '') {
        $('#indicator_error').show();
        isValid = false;
    } else {
        $('#indicator_error').hide();
    }

    return isValid;
}
</script>
<!-- END OF ASSIGNING FACULTY JS ============================================================================================================================= -->
<script>
// Global variables
let selectedAssignmentId = null;
let selectedDocumentId = null;

function editAssignment(assignmentId, programId, facultyId, status) {
    // Set hidden assignment ID
    document.getElementById('editAssignmentId').value = assignmentId;

    // Set form values
    document.getElementById('editProgramId').value = programId;
    document.getElementById('editStatus').value = status;

    // Load faculty for this program
    loadFacultyForProgram(programId);

    // Set selected faculty after a short delay to ensure dropdown is loaded
    setTimeout(function() {
        $('#editFacultyId').val(facultyId);
    }, 500);

    // Show modal
    var editModal = new bootstrap.Modal(document.getElementById('editAssignmentModal'));
    editModal.show();
}

function setDeleteAssignment_id(assignment_id) {
    document.getElementById('deleteAssignment_id').value = assignment_id;
}

document.addEventListener('DOMContentLoaded', function() {
    initAccreditationFrameworkTabs();
});

function initAccreditationFrameworkTabs() {
    const tabButtons = document.querySelectorAll('#AccreditationFrameworkTab .nav-link');
    const tabContents = document.querySelectorAll('.tab-pane');

    tabContents.forEach(content => {
        if (!content.classList.contains('active')) {
            content.style.display = 'none';
        }
    });

    tabButtons.forEach((button) => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            tabContents.forEach(content => {
                content.style.display = 'none';
                content.classList.remove('show', 'active');
            });

            const targetId = this.getAttribute('data-bs-target');
            const targetContent = document.querySelector(targetId);
            if (targetContent) {
                targetContent.style.display = 'block';
                targetContent.classList.add('show', 'active');
            }
        });
    });
}
</script>
<!-- filtering js assignments -->
<script>
// DOM elements
const searchInput = document.getElementById('searchAssignments');
const statusFilter = document.getElementById('filterStatus');
const programFilter = document.getElementById('filterProgram');
const assignmentsTable = document.getElementById('assignmentsTable');

// Original assignments data (store for filtering)
let originalAssignmentsData = [];

// Store original table rows on page load
document.addEventListener('DOMContentLoaded', function() {
    // Store original rows data
    const rows = assignmentsTable.querySelectorAll('tr');
    rows.forEach(row => {
        const rowData = {
            html: row.outerHTML,
            faculty: row.cells[1]?.textContent.toLowerCase() || '',
            program: row.cells[2]?.textContent.toLowerCase() || '',
            indicator: row.cells[3]?.textContent.toLowerCase() || '',
            deadline: row.cells[4]?.textContent.toLowerCase() || '',
            status: row.cells[5]?.textContent.toLowerCase() || ''
        };
        originalAssignmentsData.push(rowData);
    });
});

// Search and filter function
function filterAssignments() {
    const searchTerm = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value.toLowerCase();
    const programValue = programFilter.value.toLowerCase();

    // Get all table rows
    const rows = assignmentsTable.querySelectorAll('tr');

    // Loop through each row and check filters
    rows.forEach((row, index) => {
        const rowData = originalAssignmentsData[index];
        let showRow = true;

        // Apply search filter
        if (searchTerm) {
            const matchesSearch =
                rowData.faculty.includes(searchTerm) ||
                rowData.program.includes(searchTerm) ||
                rowData.indicator.includes(searchTerm) ||
                rowData.deadline.includes(searchTerm);

            if (!matchesSearch) {
                showRow = false;
            }
        }

        // Apply status filter
        if (statusValue && showRow) {
            const statusBadge = row.cells[5]?.querySelector('.badge');
            const rowStatus = statusBadge?.textContent.toLowerCase().trim() || '';

            if (!rowStatus.includes(statusValue.replace('_', ' '))) {
                showRow = false;
            }
        }

        // Apply program filter
        if (programValue && showRow) {
            const programCell = row.cells[2];
            const programCode = programCell?.querySelector('strong')?.textContent.toLowerCase() || '';

            if (programCode !== programValue.toLowerCase()) {
                showRow = false;
            }
        }

        // Show/hide row based on filter results
        row.style.display = showRow ? '' : 'none';

        // Update row numbering for visible rows
        updateRowNumbers();
    });
}

// Update row numbers after filtering
function updateRowNumbers() {
    let visibleRowNumber = 1;
    const rows = assignmentsTable.querySelectorAll('tr');

    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const firstCell = row.cells[0];
            if (firstCell) {
                firstCell.textContent = visibleRowNumber;
            }
            visibleRowNumber++;
        }
    });
}

// Add event listeners
searchInput.addEventListener('keyup', filterAssignments);
statusFilter.addEventListener('change', filterAssignments);
programFilter.addEventListener('change', filterAssignments);
</script>
<!-- filter js documents -->
<script>
// DOM elements for documents
const searchDocumentsInput = document.getElementById('searchDocuments');
const statusFilterDocuments = document.getElementById('filterDocStatusDocuments');
const programFilterDocuments = document.getElementById('filterProgramDocuments');
const documentsTable = document.getElementById('documentsTable');

// Simple filter function
function filterDocumentsSimple() {
    const searchTerm = searchDocumentsInput.value.toLowerCase();
    const statusValue = statusFilterDocuments.value.toLowerCase();
    const programValue = programFilterDocuments.value.toLowerCase();

    // Get all table rows
    const rows = documentsTable.querySelectorAll('tr');
    let visibleCount = 0;

    // Loop through each row
    rows.forEach(row => {
        let showRow = true;

        // Get row data
        const uploadedBy = row.cells[1]?.textContent.toLowerCase() || '';
        const programCell = row.cells[2];
        const programCode = programCell?.querySelector('strong')?.textContent.toLowerCase() || '';
        const indicator = row.cells[3]?.textContent.toLowerCase() || '';
        const statusBadge = row.cells[4]?.querySelector('.badge');
        const status = statusBadge?.textContent.toLowerCase().trim() || '';

        // Apply search filter
        if (searchTerm && showRow) {
            const searchableText = uploadedBy + ' ' + programCode + ' ' + indicator + ' ' + status;
            if (!searchableText.includes(searchTerm)) {
                showRow = false;
            }
        }

        // Apply status filter
        if (statusValue && showRow) {
            const normalizedStatus = statusValue.replace('_', ' ');
            if (status !== normalizedStatus) {
                showRow = false;
            }
        }

        // Apply program filter
        if (programValue && showRow) {
            if (programCode !== programValue.toLowerCase()) {
                showRow = false;
            }
        }

        // Show/hide row
        row.style.display = showRow ? '' : 'none';
        if (showRow) visibleCount++;

        // Update row number if visible
        if (showRow && row.cells[0]) {
            row.cells[0].textContent = visibleCount;
        }
    });
}

// Add event listeners
searchDocumentsInput.addEventListener('keyup', filterDocumentsSimple);
statusFilterDocuments.addEventListener('change', filterDocumentsSimple);
programFilterDocuments.addEventListener('change', filterDocumentsSimple);
</script>

<style>
/* Custom styles for Review Management */
.card {
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.nav-tabs .nav-link {
    border-radius: 8px 8px 0 0;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    background-color: #f8f9fa;
    border-bottom-color: #f8f9fa;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.badge {
    font-size: 0.75em;
    padding: 4px 8px;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.table th {
    font-weight: 600;
    color: #495057;
}

/* Status colors */
.badge.bg-success {
    background-color: #28a745 !important;
}

.badge.bg-primary {
    background-color: #007bff !important;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #212529;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
}

.badge.bg-info {
    background-color: #17a2b8 !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
}
</style>