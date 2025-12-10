<?php
$departmentSTMT = $pdo->prepare("SELECT departments.department_id FROM departments 
INNER JOIN users ON departments.department_id = users.department_id
WHERE users.user_id = :user_id");
$departmentSTMT->execute([
    'user_id' => $user_id
]);
$departmentData = $departmentSTMT->fetch(PDO::FETCH_ASSOC);

$department_id = $departmentData["department_id"];

// Fetch all assignments that need review
$stmtAssignments = $pdo->prepare("SELECT 
    a.assignment_id,
    ac.deadline,
    a.status as assignment_status,
    a.*,
    u.user_id as faculty_id,
    CONCAT(u.first_name, ' ', u.last_name) as faculty_name,
    i.indicator_code,
    i.indicator_description,
    p.program_code,
    p.program_name,
    ac.cycle_name,
    d.department_name
FROM assignments a
INNER JOIN users u ON a.user_id = u.user_id
INNER JOIN accreditation_indicators i ON a.indicator_id = i.indicator_id
INNER JOIN programs p ON a.program_id = p.program_id
INNER JOIN accreditation_cycles ac ON a.cycle_id = ac.cycle_id
INNER JOIN departments d ON u.department_id = d.department_id
WHERE d.department_id = :department_id
ORDER BY ac.deadline ASC, a.assigned_at DESC");
$stmtAssignments->execute([
    'department_id' => $department_id
]);
$assignments = $stmtAssignments->fetchAll(PDO::FETCH_ASSOC);

// Fetch reviewers (users with QA role)
$stmtReviewers = $pdo->prepare("SELECT user_id, CONCAT(first_name, ' ', last_name) as name, user_role 
                               FROM users 
                               INNER JOIN departments ON users.department_id = departments.department_id
                               WHERE user_role = 'qa' AND departments.department_id = :department_id
                               AND is_active = 'Active' 
                               ORDER BY first_name");
$stmtReviewers->execute([
    'department_id' => $department_id
]);
$reviewers = $stmtReviewers->fetchAll(PDO::FETCH_ASSOC);

// Fetch submitted documents for review
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
    ac.cycle_name,
    COUNT(r.review_id) as review_count
FROM documents d
INNER JOIN users u ON d.uploaded_by = u.user_id
INNER JOIN accreditation_indicators i ON d.indicator_id = i.indicator_id
INNER JOIN programs p ON d.program_id = p.program_id
INNER JOIN departments dp ON p.department_id = dp.department_id
INNER JOIN accreditation_cycles ac ON d.cycle_id = ac.cycle_id
LEFT JOIN reviews r ON d.document_id = r.document_id
WHERE d.status IN ('submitted', 'under_review', 'needs_revision') AND
dp.department_id = :department_id
GROUP BY d.document_id
ORDER BY d.created_at DESC");
$stmtDocuments->execute([
    'department_id' => $department_id
]);
$documents = $stmtDocuments->fetchAll(PDO::FETCH_ASSOC);

$assignmentCount = 1;
$documentCount = 1;
?>
<section>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="mx-2">
            <h4 class=""><span class=""><i class="fa-solid fa-clipboard-check me-2"></i></span>Review Management</h4>
            <small class="text-muted">Assign reviewers and manage document reviews</small>
        </div>
    </div>

    <!-- REVIEW STATISTICS CARDS -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h6 class="card-title text-dark">Pending Assignments</h6>
                    <h2 class="text-dark"><?= count($assignments) ?></h2>
                    <small class="text-muted">Need reviewer assignment</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h6 class="card-title text-dark">Documents for Review</h6>
                    <h2 class="text-dark"><?= count($documents) ?></h2>
                    <small class="text-muted">Awaiting review</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <?php
            $stmtNeedsRevision = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE status = 'needs_revision'");
            $stmtNeedsRevision->execute();
            $needsRevision = $stmtNeedsRevision->fetchColumn();
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
            $stmtCompleted = $pdo->prepare("SELECT COUNT(*) FROM assignments WHERE status = 'completed'");
            $stmtCompleted->execute();
            $completed = $stmtCompleted->fetchColumn();
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
    <!-- Assign faculty -->
    <div class="modal fade" id="createAccounts" tabindex="-1" aria-labelledby="createAccountsLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <!-- Changed to modal-lg for better display -->
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title text-white" id="createAccountsLabel">Create Assignment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                        onclick="location.reload()"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-1 align-items-start justify-content-start" id="assignment-form">
                        <div class="mx-2 col-12">
                            <?php
                            $stmt = $pdo->prepare("SELECT * FROM accreditation_cycles WHERE status = 'active'");
                            $stmt->execute();
                            $cycles = $stmt->fetch(PDO::FETCH_ASSOC);

                            $stmt = $pdo->prepare("SELECT * FROM accreditation_indicators");
                            $stmt->execute();
                            $indicators = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            $stmt = $pdo->prepare("SELECT * FROM programs 
                            INNER JOIN departments ON programs.department_id = departments.department_id
                            WHERE departments.department_id = :department_id");
                            $stmt->execute([
                                'department_id' => $department_id
                            ]);
                            $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                            <label class="form-label">Cycle</label>
                            <input type="hidden" class="form-control" name="cycle_id"
                                value="<?= htmlspecialchars($cycles["cycle_id"]) ?>">
                            <p class="form-control text-dark border" readonly>
                                <?= htmlspecialchars($cycles["cycle_name"] . '(' . $cycles["academic_year"] . ')') ?>
                            </p>
                        </div>
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
                        <div class="mx-2 col-md-12">
                            <label class="form-label">Indicator</label>
                            <select name="indicator_id" id="indicator_id" class="form-select" required>
                                <option value="">Select Indicator</option>
                                <?php foreach($indicators as $indi) : ?>
                                <option value="<?= htmlspecialchars($indi["indicator_id"]) ?>">
                                    <?= htmlspecialchars($indi["indicator_code"] . ' - ' . $indi["indicator_description"]) ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="mx-2 col-md-12">
                            <label class="form-label">Program</label>
                            <select name="program_id" id="program_id" class="form-select" required>
                                <option value="">Select Program</option>
                                <?php foreach($programs as $prog) : ?>
                                <option value="<?= htmlspecialchars($prog["program_id"]) ?>">
                                    <?= htmlspecialchars($prog["program_name"] . ' (' . $prog["program_code"] . ')') ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <!-- Sub Indicators Checkbox Section -->
                        <div class="mx-2 col-12">
                            <label class="form-label">Sub Indicators (Optional)</label>
                            <div class="border rounded p-3 bg-light" id="sub_indicators_container"
                                style="display: none;">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="select_all_sub_indicators">
                                    <label class="form-check-label fw-bold" for="select_all_sub_indicators">
                                        Select All Sub Indicators
                                    </label>
                                </div>
                                <hr class="my-2">
                                <div id="sub_indicators_checkboxes" class="row">
                                    <!-- Checkboxes will be loaded dynamically via AJAX -->
                                    <div class="col-12 text-center text-muted">
                                        <i class="bi bi-info-circle"></i> Select an indicator above to load sub
                                        indicators
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="sub_indicator_ids" id="sub_indicator_ids" value="">
                            <small class="text-muted">Note: Sub indicators are optional. You can select multiple if
                                needed.</small>
                        </div>

                        <div class="mx-2 col-md-12">
                            <label class="form-label">Faculty</label>
                            <select name="faculty_id" id="user_id" class="form-select" required disabled>
                                <option value="">Select Faculty</option>
                                <!-- Faculty options will be loaded dynamically via AJAX -->
                            </select>
                        </div>
                        <div class="mx-2 col-md-12">
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
                        <!-- Hidden assignment ID -->
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
                                    value="<?= htmlspecialchars($activeCycle['cycle_id']) ?>">
                                <input type="text" class="form-control"
                                    value="<?= htmlspecialchars($activeCycle['cycle_name'] . ' (' . $activeCycle['academic_year'] . ')') ?>"
                                    readonly>
                            </div>

                            <!-- Indicator -->
                            <div class="col-md-6">
                                <label class="form-label">Indicator *</label>
                                <select name="indicator_id" id="editIndicatorId" class="form-select" required>
                                    <option value="">Select Indicator</option>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT * FROM accreditation_indicators");
                                    $stmt->execute();
                                    $indicators = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach($indicators as $indi) : 
                                ?>
                                    <option value="<?= htmlspecialchars($indi["indicator_id"]) ?>">
                                        <?= htmlspecialchars($indi["indicator_code"] . ' - ' . $indi["indicator_description"]) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Program -->
                            <div class="col-md-6">
                                <label class="form-label">Program *</label>
                                <select name="program_id" id="editProgramId" class="form-select" required
                                    onchange="loadFacultyForProgram(this.value)">
                                    <option value="">Select Program</option>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT * FROM programs");
                                    $stmt->execute();
                                    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach($programs as $prog) : 
                                ?>
                                    <option value="<?= htmlspecialchars($prog["program_id"]) ?>">
                                        <?= htmlspecialchars($prog["program_name"] . ' (' . $prog["program_code"] . ')') ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Faculty -->
                            <div class="col-md-6">
                                <label class="form-label">Faculty *</label>
                                <select name="faculty_id" id="editFacultyId" class="form-select" required>
                                    <option value="">Select Faculty</option>
                                    <!-- Will be populated dynamically -->
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
                                    value="<?= htmlspecialchars($_SESSION['adminData']['first_name'] ?? $_SESSION['QAData']['first_name'] ?? 'System') ?>"
                                    readonly>
                                <input type="hidden" name="assigned_by"
                                    value="<?= htmlspecialchars($_SESSION['adminData']['user_id'] ?? $_SESSION['QAData']['user_id'] ?? 0) ?>">
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
    <!-- Delete assignemnt Modal -->
    <div class="modal fade" id="deleteAssignmentModal" tabindex="-1" aria-labelledby="deleteAssignmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="delete-assignment-form" class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteAssignmentModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    Are you sure you want to delete this assignemnt?
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
                <div class="col-md-6">
                    <input type="text" placeholder="Search assignments..." id="searchAssignments" class="form-control">
                </div>
                <div class="col-md-3">
                    <select id="filterStatus" class="form-select">
                        <option value="">All Status</option>
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="submitted">Submitted</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filterProgram" class="form-select">
                        <option value="">All Programs</option>
                        <?php
                        $stmtPrograms = $pdo->prepare("SELECT program_id, program_code FROM programs
                        INNER JOIN departments ON programs.department_id = departments.department_id
                        WHERE departments.department_id = :department_id
                        ORDER BY program_code");
                        $stmtPrograms->execute(['department_id' => $department_id]);
                        $programs = $stmtPrograms->fetchAll(PDO::FETCH_ASSOC);
                        foreach($programs as $program) {
                            echo '<option value="' . $program['program_code'] . '">' . htmlspecialchars($program['program_code']) . '</option>';
                        }
                        ?>
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
                            
                            // Check if overdue
                            $isOverdue = $assignment['deadline'] && strtotime($assignment['deadline']) < time() && $assignment['assignment_status'] != 'completed';
                        ?>
                        <tr <?= $isOverdue ? 'class="table-warning"' : '' ?>>
                            <td><?= $assignmentCount++ ?></td>
                            <td>
                                <strong><?= htmlspecialchars($assignment['faculty_name']) ?></strong><br>
                                <small
                                    class="text-muted"><?= htmlspecialchars($assignment['department_name']) ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($assignment['program_code']) ?></strong><br>
                                <small><?= htmlspecialchars($assignment['program_name']) ?></small>
                            </td>
                            <td>
                                <span
                                    class="badge bg-info"><?= htmlspecialchars($assignment['indicator_code']) ?></span><br>
                                <small><?= htmlspecialchars(substr($assignment['indicator_description'], 0, 50)) ?>...</small>
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
                                        '<?= $assignment['indicator_id'] ?>',
                                        '<?= $assignment['program_id'] ?>',
                                        '<?= $assignment['user_id'] ?>',
                                        '<?= htmlspecialchars($assignment['status']) ?>')">
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
                <div class="col-md-6">
                    <input type="text" placeholder="Search documents..." id="searchDocuments" class="form-control">
                </div>
                <div class="col-md-3">
                    <select id="filterDocStatusDocuments" class="form-select">
                        <option value="">All Status</option>
                        <option value="submitted">Submitted</option>
                        <option value="under_review">Under Review</option>
                        <option value="needs_revision">Needs Revision</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filterProgramDocuments" class="form-select">
                        <option value="">All Programs</option>
                        <?php
                        $stmtPrograms = $pdo->prepare("SELECT cycle_name FROM accreditation_cycles ORDER BY cycle_name");
                        $stmtPrograms->execute();
                        $programs = $stmtPrograms->fetchAll(PDO::FETCH_ASSOC);
                        foreach($programs as $program) {
                            echo '<option value="' . $program['cycle_name'] . '">' . htmlspecialchars($program['cycle_name']) . '</option>';
                        }
                        ?>
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
                            <th>Indicator</th>
                            <th>Program</th>
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
                                <span
                                    class="badge bg-info"><?= htmlspecialchars($document['indicator_code']) ?></span><br>
                                <small><?= htmlspecialchars(substr($document['indicator_description'], 0, 40)) ?>...</small>
                            </td>
                            <td>
                                <?= htmlspecialchars($document['program_name']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($document['cycle_name']) ?></small>
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

    <!-- View Reviews Modal -->
    <div class="modal fade" id="viewReviewsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Document Reviews</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="reviewsList">
                    <!-- Reviews will be loaded here via AJAX -->
                </div>
            </div>
        </div>
    </div>

</section>

<script>
$(document).ready(function() {
    // When indicator selection changes
    $('#indicator_id').on('change', function() {
        var indicatorId = $(this).val();
        loadSubIndicators(indicatorId);
    });

    // When program selection changes
    $('#program_id').on('change', function() {
        var programId = $(this).val();
        loadFacultyForProgram(programId);
    });

    // Form submission handler
    $('#assignment-form').on('submit', function(e) {
        e.preventDefault();
        submitAssignmentForm();
    });
});

// Function to load sub indicators as checkboxes
function loadSubIndicators(indicatorId, selectedSubIds = []) {
    var container = $('#sub_indicators_container');
    var checkboxesContainer = $('#sub_indicators_checkboxes');
    var hiddenInput = $('#sub_indicator_ids');

    if (!indicatorId || indicatorId === '') {
        container.hide();
        checkboxesContainer.html(
            '<div class="col-12 text-center text-muted"><i class="bi bi-info-circle"></i> Select an indicator above to load sub indicators</div>'
            );
        hiddenInput.val('');
        $('#select_all_sub_indicators').prop('checked', false).prop('disabled', true);
        return;
    }

    // Show loading state
    container.show();
    checkboxesContainer.html(`
        <div class="col-12 text-center">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            <span class="ms-2">Loading sub indicators...</span>
        </div>
    `);
    $('#select_all_sub_indicators').prop('checked', false).prop('disabled', true);

    $.ajax({
        url: base_url + "/authentication/action.php?action=get_parent_indicators",
        method: 'POST',
        dataType: 'json',
        data: {
            indicator_id: indicatorId
        },
        success: function(response) {
            console.log('Sub Indicators Response:', response);

            if (response.status == 1 && response.data && response.data.length > 0) {
                checkboxesContainer.empty();

                // Convert selectedSubIds to array if it's a string
                if (typeof selectedSubIds === 'string') {
                    selectedSubIds = selectedSubIds.split(',').map(id => id.trim()).filter(id => id !== '');
                } else if (!Array.isArray(selectedSubIds)) {
                    selectedSubIds = [];
                }

                // Create checkboxes in columns (2 columns layout)
                var indicatorsPerColumn = Math.ceil(response.data.length / 2);
                var currentColumn = 1;
                var currentCount = 0;

                var column1 = $('<div class="col-md-6"></div>');
                var column2 = $('<div class="col-md-6"></div>');

                $.each(response.data, function(index, indicator) {
                    var isChecked = selectedSubIds.includes(indicator.indicator_id.toString());
                    var checkboxId = 'sub_indicator_' + indicator.indicator_id;
                    var optionText = indicator.indicator_code;

                    // Truncate description if too long
                    var fullDescription = indicator.indicator_description || '';
                    var shortDescription = fullDescription.substring(0, 60);
                    if (fullDescription.length > 60) shortDescription += '...';

                    var checkboxHtml = `
                        <div class="form-check mb-2">
                            <input class="form-check-input sub-indicator-checkbox" 
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

                    // Distribute checkboxes between two columns
                    if (currentCount < indicatorsPerColumn) {
                        column1.append(checkboxHtml);
                    } else {
                        column2.append(checkboxHtml);
                    }
                    currentCount++;
                });

                // Add columns to container
                checkboxesContainer.append(column1);
                checkboxesContainer.append(column2);

                // Initialize tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();

                // Enable select all checkbox
                $('#select_all_sub_indicators').prop('disabled', false);

                // Update hidden input with selected values
                updateSubIndicatorIds();

                // Update select all checkbox state
                updateSelectAllState();

            } else {
                checkboxesContainer.html(`
                    <div class="col-12 text-center text-muted">
                        <i class="bi bi-info-circle"></i> No sub indicators available for this indicator
                    </div>
                `);
                hiddenInput.val('');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            checkboxesContainer.html(`
                <div class="col-12 text-center text-danger">
                    <i class="bi bi-exclamation-triangle"></i> Error loading sub indicators
                </div>
            `);
            hiddenInput.val('');
        }
    });
}

// Function to update hidden input with selected checkbox values
function updateSubIndicatorIds() {
    var selectedValues = [];
    $('.sub-indicator-checkbox:checked').each(function() {
        selectedValues.push($(this).val());
    });

    $('#sub_indicator_ids').val(selectedValues.join(','));

    // Show count badge if any are selected
    var countBadge = $('#selected_count_badge');
    if (selectedValues.length > 0) {
        if (countBadge.length === 0) {
            $('#sub_indicators_container label').append(
                ` <span class="badge bg-primary" id="selected_count_badge">${selectedValues.length} selected</span>`
            );
        } else {
            countBadge.text(selectedValues.length + ' selected');
        }
    } else {
        countBadge.remove();
    }
}

// Function to update select all checkbox state
function updateSelectAllState() {
    var totalCheckboxes = $('.sub-indicator-checkbox').length;
    var checkedCount = $('.sub-indicator-checkbox:checked').length;

    $('#select_all_sub_indicators').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCount);
}

// Select All functionality
$(document).on('change', '#select_all_sub_indicators', function() {
    var isChecked = $(this).is(':checked');
    $('.sub-indicator-checkbox').prop('checked', isChecked);
    updateSubIndicatorIds();
    updateSelectAllState();
});

// Update hidden input when individual checkboxes change
$(document).on('change', '.sub-indicator-checkbox', function() {
    updateSubIndicatorIds();
    updateSelectAllState();
});



// Global variables
let selectedAssignmentId = null;
let selectedDocumentId = null;

function editAssignment(assignmentId, indicatorId, programId, facultyId, status) {
    // Set hidden assignment ID
    document.getElementById('editAssignmentId').value = assignmentId;

    // Set form values
    document.getElementById('editIndicatorId').value = indicatorId;
    document.getElementById('editProgramId').value = programId;
    document.getElementById('editStatus').value = status;

    // Clear faculty dropdown first
    $('#editFacultyId').html('<option value="">Loading faculty...</option>').prop('disabled', true);

    // Load faculty for the selected program
    if (programId) {
        loadFacultyForProgram(programId, facultyId);
    } else {
        $('#editFacultyId').html('<option value="">Select Program First</option>').prop('disabled', true);
    }

    // Show modal
    var editModal = new bootstrap.Modal(document.getElementById('editAssignmentModal'));
    editModal.show();
}
// Function to load faculty for program
// Function to load faculty for program (for EDIT modal)
function loadFacultyForProgram(programId, selectedFacultyId = null) {
    // Use editFacultyId for the edit modal
    var userSelect = $('#editFacultyId');

    if (!programId || programId === '') {
        userSelect.html('<option value="">Select Program First</option>').prop('disabled', true);
        return;
    }

    // Show loading state
    userSelect.html('<option value="">Loading faculty...</option>').prop('disabled', true);

    $.ajax({
        url: base_url + "/authentication/action.php?action=get_faculty",
        method: 'POST',
        dataType: 'json',
        data: {
            program_id: programId
        },
        success: function(response) {
            console.log('Faculty response:', response); // Debug log
            if (response.status == 1 && response.data && response.data.length > 0) {
                userSelect.html('<option value="">Select Faculty</option>');
                $.each(response.data, function(index, faculty) {
                    var selected = (faculty.user_id == selectedFacultyId) ? 'selected' : '';
                    userSelect.append(
                        `<option value="${faculty.user_id}" ${selected}>
                            ${faculty.first_name} ${faculty.last_name}
                        </option>`
                    );
                });
                userSelect.prop('disabled', false);
            } else {
                userSelect.html('<option value="">No faculty found</option>').prop('disabled', true);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading faculty:', error);
            userSelect.html('<option value="">Error loading faculty</option>').prop('disabled', true);
        }
    });
}

// Function to load faculty for program (for CREATE modal - keep this separate)
function loadFacultyForCreateProgram(programId) {
    var userSelect = $('#user_id'); // This is for create modal

    if (!programId || programId === '') {
        userSelect.html('<option value="">Select Program First</option>').prop('disabled', true);
        return;
    }

    userSelect.html('<option value="">Loading faculty...</option>').prop('disabled', true);

    $.ajax({
        url: base_url + "/authentication/action.php?action=get_faculty",
        method: 'POST',
        dataType: 'json',
        data: {
            program_id: programId
        },
        success: function(response) {
            if (response.status == 1 && response.data.length > 0) {
                userSelect.html('<option value="">Select Faculty</option>');
                $.each(response.data, function(index, faculty) {
                    userSelect.append(
                        `<option value="${faculty.user_id}">
                            ${faculty.first_name} ${faculty.last_name}
                        </option>`
                    );
                });
                userSelect.prop('disabled', false);
            } else {
                userSelect.html('<option value="">No faculty found</option>').prop('disabled', true);
            }
        },
        error: function() {
            userSelect.html('<option value="">Error loading faculty</option>').prop('disabled', true);
        }
    });
}

function setDeleteAssignment_id(assignment_id) {
    document.getElementById('deleteAssignment_id').value = assignment_id
}

document.addEventListener('DOMContentLoaded', function() {
    initAccreditationFrameworkTabs();
});

function initAccreditationFrameworkTabs() {
    const tabButtons = document.querySelectorAll('#AccreditationFrameworkTab .nav-link');
    const tabContents = document.querySelectorAll('.tab-pane');

    // Initialize tabs - hide all except active
    tabContents.forEach(content => {
        if (!content.classList.contains('active')) {
            content.style.display = 'none';
        }
    });

    tabButtons.forEach((button) => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            // Update active button
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Hide all tab contents
            tabContents.forEach(content => {
                content.style.display = 'none';
                content.classList.remove('show', 'active');
            });

            // Show the selected tab
            const targetId = this.getAttribute('data-bs-target');
            const targetContent = document.querySelector(targetId);
            if (targetContent) {
                targetContent.style.display = 'block';
                targetContent.classList.add('show', 'active');

                if (targetId === '#assignments') {
                    console.log("Assignments tab active");
                } else if (targetId === '#documents') {
                    console.log("Documents tab active");
                } else if (targetId === '#assign-reviewer') {
                    console.log("Assign Reviewer tab active");
                }
            }
        });
    });
}

// Load document reviews via AJAX
function loadDocumentReviews(documentId) {
    fetch('get_document_reviews.php?document_id=' + documentId)
        .then(response => response.text())
        .then(html => {
            document.getElementById('reviewsList').innerHTML = html;
            const modal = new bootstrap.Modal(document.getElementById('viewReviewsModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('reviewsList').innerHTML =
                '<div class="alert alert-danger">Failed to load reviews</div>';
        });
}

// View assignment details
function viewAssignmentDetails(assignmentId) {
    fetch('get_assignment_details.php?assignment_id=' + assignmentId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 1) {
                // Create modal content
                const content = `
                <div class="card">
                    <div class="card-header">
                        <h6>Assignment Details</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Faculty:</strong> ${data.data.faculty_name}</p>
                        <p><strong>Program:</strong> ${data.data.program_name}</p>
                        <p><strong>Indicator:</strong> ${data.data.indicator_code}</p>
                        <p><strong>Status:</strong> <span class="badge bg-${data.data.status_color}">${data.data.status}</span></p>
                        <p><strong>Deadline:</strong> ${data.data.deadline}</p>
                        <p><strong>Description:</strong> ${data.data.indicator_description}</p>
                    </div>
                </div>
            `;

                Swal.fire({
                    title: 'Assignment Details',
                    html: content,
                    width: '600px',
                    showCloseButton: true
                });
            }
        });
}
</script>
<script>
$(document).ready(function() {
    // When program selection changes
    $('#program_id').on('change', function() {
        var programId = $(this).val();
        var userSelect = $('#user_id');

        if (programId) {
            // Enable faculty dropdown
            userSelect.prop('disabled', false);

            // Clear existing options except the first one
            userSelect.find('option:not(:first)').remove();

            // Show loading
            userSelect.html('<option value="">Loading faculty...</option>');

            // Fetch faculty for selected program via AJAX
            $.ajax({
                url: base_url + "/authentication/action.php?action=get_faculty",
                method: 'POST',
                dataType: 'json',
                data: {
                    program_id: programId
                },
                success: function(response) {
                    if (response.status == 1 && response.data.length > 0) {
                        // Clear loading message
                        userSelect.html('<option value="">Select Faculty</option>');

                        // Add faculty options
                        $.each(response.data, function(index, faculty) {
                            userSelect.append(
                                $('<option>', {
                                    value: faculty.user_id,
                                    text: faculty.first_name + ' ' + faculty
                                        .last_name +
                                        (faculty.email ? ' (' + faculty
                                            .email + ')' : '')
                                })
                            );
                        });
                    } else {
                        userSelect.html(
                            '<option value="">No faculty found for this program</option>'
                        );
                    }
                },
                error: function() {
                    userSelect.html('<option value="">Error loading faculty</option>');
                }
            });
        } else {
            // Disable and reset faculty dropdown if no program selected
            userSelect.prop('disabled', true);
            userSelect.html('<option value="">Select Faculty</option>');
        }
    });

});
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

            if (programCode !== programValue) {
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

// Clear filters function (optional - can be called from a clear button)
function clearFilters() {
    searchInput.value = '';
    statusFilter.value = '';
    programFilter.value = '';

    // Show all rows
    const rows = assignmentsTable.querySelectorAll('tr');
    rows.forEach(row => {
        row.style.display = '';
    });

    // Reset row numbers
    updateRowNumbers();
}
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
        const indicator = row.cells[2]?.textContent.toLowerCase() || '';
        const programCell = row.cells[3];
        const programText = programCell?.textContent.toLowerCase() || '';

        // Extract cycle name - get the small text within program cell
        let cycleName = '';
        if (programCell) {
            const smallElements = programCell.getElementsByTagName('small');
            if (smallElements.length > 0) {
                cycleName = smallElements[smallElements.length - 1].textContent.toLowerCase().trim();
            }
        }

        const statusBadge = row.cells[4]?.querySelector('.badge');
        const status = statusBadge?.textContent.toLowerCase().trim() || '';
        const reviews = row.cells[5]?.textContent.toLowerCase() || '';

        // Apply search filter
        if (searchTerm && showRow) {
            const searchableText = uploadedBy + ' ' + indicator + ' ' + programText + ' ' + status + ' ' +
                reviews;
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

        // Apply program/cycle filter
        if (programValue && showRow) {
            if (cycleName !== programValue.toLowerCase()) {
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

// Clear filters
function clearDocumentFilters() {
    searchDocumentsInput.value = '';
    statusFilterDocuments.value = '';
    programFilterDocuments.value = '';

    const rows = documentsTable.querySelectorAll('tr');
    rows.forEach((row, index) => {
        row.style.display = '';
        if (row.cells[0]) {
            row.cells[0].textContent = index + 1;
        }
    });
}
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