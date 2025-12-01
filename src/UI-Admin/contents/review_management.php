<?php
// Fetch all assignments that need review
$stmtAssignments = $pdo->prepare("SELECT 
    a.assignment_id,
    ac.deadline,
    a.status as assignment_status,
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
WHERE a.status IN ('submitted', 'in_progress')
ORDER BY ac.deadline ASC, a.assigned_at DESC");
$stmtAssignments->execute();
$assignments = $stmtAssignments->fetchAll(PDO::FETCH_ASSOC);

// Fetch reviewers (users with QA role)
$stmtReviewers = $pdo->prepare("SELECT user_id, CONCAT(first_name, ' ', last_name) as name, user_role 
                               FROM users 
                               WHERE user_role = 'qa' OR user_role = 'head'
                               AND is_active = 'Active'
                               ORDER BY first_name");
$stmtReviewers->execute();
$reviewers = $stmtReviewers->fetchAll(PDO::FETCH_ASSOC);

// Fetch submitted documents for review
$stmtDocuments = $pdo->prepare("SELECT 
    d.document_id,
    d.filename,
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
INNER JOIN accreditation_cycles ac ON d.cycle_id = ac.cycle_id
LEFT JOIN reviews r ON d.document_id = r.document_id
WHERE d.status IN ('submitted', 'under_review', 'needs_revision')
GROUP BY d.document_id
ORDER BY d.created_at DESC");
$stmtDocuments->execute();
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
                <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#assignments"> <i class="fa-solid fa-tasks me-2"></i> Assignments</a>
            </li>
            <li class="nav-item col-md-6">
                <a class="nav-link" data-bs-toggle="tab" data-bs-target="#documents"><i class="fa-solid fa-file-alt me-2"></i> Document Reviews</a>
            </li>
        </ul>
        <div class="col-md-6 d-flex justify-content-end">
            <button class="btn bg-gradient-primary" data-bs-toggle="modal" data-bs-target="#createAccounts">+ create assignment</button>
        </div>
    </div>
    <!-- Assign faculty -->
     <div class="modal fade" id="createAccounts" tabindex="-1" aria-labelledby="createAccountsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title text-white" id="createAccountsLabel">Create Assignment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                        onclick="location.reload()"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-1 align-items-start justify-content-start" id="register-form" method="post">
                        
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
                        $stmtPrograms = $pdo->prepare("SELECT program_id, program_code FROM programs ORDER BY program_code");
                        $stmtPrograms->execute();
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
                                    <button type="button" class="btn btn-outline-primary view-assignment-btn"
                                        data-assignment-id="<?= $assignment['assignment_id'] ?>" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-success assign-reviewer-btn"
                                        data-assignment-id="<?= $assignment['assignment_id'] ?>"
                                        data-faculty-name="<?= htmlspecialchars($assignment['faculty_name']) ?>"
                                        data-indicator-code="<?= htmlspecialchars($assignment['indicator_code']) ?>"
                                        title="Assign Reviewer">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-warning"
                                        onclick="sendReminder(<?= $assignment['assignment_id'] ?>, '<?= htmlspecialchars($assignment['faculty_name']) ?>')"
                                        title="Send Reminder">
                                        <i class="fas fa-bell"></i>
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
                    <select id="filterDocStatus" class="form-select">
                        <option value="">All Status</option>
                        <option value="submitted">Submitted</option>
                        <option value="under_review">Under Review</option>
                        <option value="needs_revision">Needs Revision</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" onclick="refreshDocuments()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- DOCUMENTS TABLE -->
            <div class="table-responsive">
                <table class="table table-sm table-hover" style="font-size: 0.875rem;">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Document</th>
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
                            <td>
                                <strong><?= htmlspecialchars($document['filename']) ?></strong><br>
                                <small
                                    class="text-muted"><?= date('M d, Y', strtotime($document['uploaded_date'])) ?></small>
                            </td>
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
                                    <a href="download.php?id=<?= $document['document_id'] ?>"
                                        class="btn btn-outline-primary" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-success assign-document-reviewer-btn"
                                        data-document-id="<?= $document['document_id'] ?>"
                                        data-filename="<?= htmlspecialchars($document['filename']) ?>"
                                        title="Assign Reviewer">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info view-reviews-btn"
                                        data-document-id="<?= $document['document_id'] ?>" title="View Reviews">
                                        <i class="fas fa-comments"></i>
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
                    <form id="quick-assign-reviewer-form">
                        <input type="hidden" id="assignItemId" name="item_id">
                        <input type="hidden" id="assignItemType" name="item_type">

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
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitReviewerAssignment()">Assign</button>
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
// Global variables
let selectedAssignmentId = null;
let selectedDocumentId = null;

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

// Submit reviewer assignment
function submitReviewerAssignment() {
    const form = document.getElementById('quick-assign-reviewer-form');
    const formData = new FormData(form);

    fetch('assign_reviewer.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 1) {
                alert('Reviewer assigned successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to assign reviewer');
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

// Send reminder to faculty
function sendReminder(assignmentId, facultyName) {
    if (confirm(`Send reminder to ${facultyName} about this assignment?`)) {
        fetch('send_reminder.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    assignment_id: assignmentId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 1) {
                    alert('Reminder sent successfully!');
                } else {
                    alert('Failed to send reminder');
                }
            });
    }
}

// Refresh documents list
function refreshDocuments() {
    location.reload();
}

// Initialize search filters
function initializeSearchFilters() {
    // Assignments search
    const searchAssignments = document.getElementById('searchAssignments');
    if (searchAssignments) {
        searchAssignments.addEventListener('input', function(e) {
            filterTable('assignmentsTable', e.target.value.toLowerCase());
        });
    }

    // Documents search
    const searchDocuments = document.getElementById('searchDocuments');
    if (searchDocuments) {
        searchDocuments.addEventListener('input', function(e) {
            filterTable('documentsTable', e.target.value.toLowerCase());
        });
    }

    // Status filter
    const filterStatus = document.getElementById('filterStatus');
    if (filterStatus) {
        filterStatus.addEventListener('change', function() {
            filterTableByStatus('assignmentsTable', this.value);
        });
    }

    // Program filter
    const filterProgram = document.getElementById('filterProgram');
    if (filterProgram) {
        filterProgram.addEventListener('change', function() {
            filterTableByProgram('assignmentsTable', this.value);
        });
    }

    // Document status filter
    const filterDocStatus = document.getElementById('filterDocStatus');
    if (filterDocStatus) {
        filterDocStatus.addEventListener('change', function() {
            filterTableByDocStatus('documentsTable', this.value);
        });
    }
}

// Table filtering functions
function filterTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const rows = table.getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const text = row.textContent.toLowerCase();

        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

function filterTableByStatus(tableId, status) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const rows = table.getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const statusCell = row.querySelector('.badge');

        if (!status || !statusCell) {
            row.style.display = '';
            continue;
        }

        const rowStatus = statusCell.textContent.toLowerCase().replace(' ', '_');

        if (rowStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

function filterTableByProgram(tableId, programCode) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const rows = table.getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const programCell = row.cells[2]; // Assuming program is in 3rd column

        if (!programCode || !programCell) {
            row.style.display = '';
            continue;
        }

        const rowProgram = programCell.textContent.toLowerCase();

        if (rowProgram.includes(programCode.toLowerCase())) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

function filterTableByDocStatus(tableId, status) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const rows = table.getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const statusCell = row.querySelector('.badge');

        if (!status || !statusCell) {
            row.style.display = '';
            continue;
        }

        const rowStatus = statusCell.textContent.toLowerCase().replace(' ', '_');

        if (rowStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

// Assign reviewer from main form
function assignReviewer() {
    const form = document.getElementById('assign-reviewer-form');
    const formData = new FormData(form);
    const selectedItem = document.getElementById('reviewItemSelect').value;

    if (!selectedItem) {
        alert('Please select an item to review');
        return;
    }

    const [type, id] = selectedItem.split('_');
    formData.append('item_type', type);
    formData.append('item_id', id);

    fetch('assign_reviewer.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 1) {
                alert('Reviewer assigned successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to assign reviewer');
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