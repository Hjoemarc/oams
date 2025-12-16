<?php
// Main assignments query
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
WHERE u.user_id = :user_id
ORDER BY ac.deadline ASC, a.assigned_at DESC");

$stmtAssignments->execute(['user_id' => $user_id]);
$assignments = $stmtAssignments->fetchAll(PDO::FETCH_ASSOC);

$assignmentCount = 1;

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
<section style="height: 85vh; overflow-y: scroll; overflow-x: hidden;">
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
        </ul>
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
                            // Determine status badge
                            $statusBadge = '';
                            switch($assignment['assignment_status']) {
                                case 'submitted': $statusBadge = 'badge bg-success'; break;
                                case 'in_progress': $statusBadge = 'badge bg-primary'; break;
                                case 'not_started': $statusBadge = 'badge bg-secondary'; break;
                                case 'completed': $statusBadge = 'badge bg-success'; break;
                                default: $statusBadge = 'badge bg-warning';
                            }
                            
                            // Check if overdue
                            $isOverdue = false;
                            if (!empty($assignment['deadline']) && $assignment['assignment_status'] != 'completed') {
                                $deadlineTimestamp = strtotime($assignment['deadline']);
                                if ($deadlineTimestamp !== false && $deadlineTimestamp < time()) {
                                    $isOverdue = true;
                                }
                            }
                        ?><style>
                                        .truncate-text {
                                            display: inline-block;
                                            max-width: 13rem;     /* your desired width */
                                            white-space: nowrap; 
                                            overflow: hidden;
                                            text-overflow: ellipsis;
                                        }
                                    </style>
                        <tr <?= $isOverdue ? 'class="table-warning"' : '' ?>>
                            <td><?= $assignmentCount++ ?></td>
                            <td class="truncate-text">
                                <strong><?= htmlspecialchars($assignment['faculty_name']) ?></strong><br>
                                <small
                                    class="text-muted"><?= htmlspecialchars($assignment['department_name']) ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($assignment['program_code']) ?></strong><br>
                                <small><?= htmlspecialchars($assignment['program_name']) ?></small>
                            </td>
                            <td class="truncate-text">
                                
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
                                    <button type="button" class="btn btn-sm btn-primary view-assignment-btn" 
                                        data-assignment-id="<?= $assignment['assignment_id'] ?>">
                                        <i class="fas fa-eye"></i> View
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

</section>
<div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-labelledby="editAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary">
                <h5 class="modal-title text-white" id="editAssignmentModalLabel">View Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-assignment-form">
                    <!-- Hidden assignment ID -->
                    <input type="hidden" id="editAssignmentId" name="assignment_id">

                    <div class="row g-3">
                        <!-- Cycle -->
                        <div class="col-md-6">
                            <label class="form-label">Cycle</label>
                            <input type="text" class="form-control" id="editCycleDisplay" readonly>
                        </div>

                        <!-- Area -->
                        <div class="col-md-6">
                            <label class="form-label">Area</label>
                            <input type="text" class="form-control" id="editAreaDisplay" readonly>
                        </div>

                        <!-- Parameter -->
                        <div class="col-md-6">
                            <label class="form-label">Parameter</label>
                            <input type="text" class="form-control" id="editParameterDisplay" readonly>
                        </div>

                        <!-- Indicator -->
                        <div class="col-md-6">
                            <label class="form-label">Indicator *</label>
                            <input type="text" class="form-control" id="editIndicatorDisplay" readonly>
                        </div>

                        <!-- Program -->
                        <div class="col-md-6">
                            <label class="form-label">Program *</label>
                            <input type="text" class="form-control" id="editProgramDisplay" readonly>
                        </div>

                        <!-- Faculty -->
                        <div class="col-md-6">
                            <label class="form-label">Faculty *</label>
                            <input type="text" class="form-control" id="editFacultyDisplay" readonly>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label">Status *</label>
                            <input type="text" class="form-control" id="editStatusDisplay" readonly>
                        </div>

                        <!-- Assigned By -->
                        <div class="col-md-6">
                            <label class="form-label">Assigned By</label>
                            <input type="text" class="form-control" id="editAssignedByDisplay" readonly>
                        </div>

                        <!-- Assigned Date -->
                        <div class="col-md-6">
                            <label class="form-label">Assigned Date</label>
                            <input type="text" class="form-control" id="editAssignedDateDisplay" readonly>
                        </div>

                        <!-- Last Updated -->
                        <div class="col-md-6">
                            <label class="form-label">Last Updated</label>
                            <input type="text" class="form-control" id="editUpdatedDateDisplay" readonly>
                        </div>

                        <!-- Indicator Description (Full Width) -->
                        <div class="col-12">
                            <label class="form-label">Indicator Description</label>
                            <textarea class="form-control" id="editIndicatorDescription" rows="3" readonly></textarea>
                        </div>
                    </div>

                    <div class="modal-footer mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
// Function to format date
function formatDate(dateString) {
    if (!dateString || dateString === '0000-00-00 00:00:00' || dateString === 'N/A') {
        return 'N/A';
    }
    
    try {
        var date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return dateString;
        }
        
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        console.error('Date formatting error:', e);
        return dateString;
    }
}

// Function to format status
function formatStatus(status) {
    if (!status) return 'Not Started';
    
    return status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
}

// Function to view assignment
function viewAssignment(assignmentId) {
    // Reset form
    document.getElementById('editAssignmentId').value = assignmentId;
    
    // Clear all fields
    document.getElementById('editCycleDisplay').value = 'Loading...';
    document.getElementById('editAreaDisplay').value = 'Loading...';
    document.getElementById('editParameterDisplay').value = 'Loading...';
    document.getElementById('editIndicatorDisplay').value = 'Loading...';
    document.getElementById('editProgramDisplay').value = 'Loading...';
    document.getElementById('editFacultyDisplay').value = 'Loading...';
    document.getElementById('editStatusDisplay').value = 'Loading...';
    document.getElementById('editAssignedByDisplay').value = 'Loading...';
    document.getElementById('editAssignedDateDisplay').value = 'Loading...';
    document.getElementById('editUpdatedDateDisplay').value = 'Loading...';
    document.getElementById('editIndicatorDescription').value = 'Loading...';

    // Load assignment details via AJAX
    $.ajax({
        url: base_url + "/authentication/action.php?action=get_assignment_details",
        method: 'POST',
        dataType: 'json',
        data: {
            assignment_id: assignmentId
        },
        success: function(response) {
            console.log('AJAX Response:', response); // Debug log
            
            if (response.status == 1 && response.data) {
                var data = response.data;
                
                // Populate all fields
                document.getElementById('editCycleDisplay').value = 
                    (data.cycle_name || '') + ' (' + (data.academic_year || '') + ')';
                document.getElementById('editAreaDisplay').value = 
                    (data.area_number || '') + ' - ' + (data.area_name || '');
                document.getElementById('editParameterDisplay').value = 
                    (data.parameter_code || '') + ' - ' + (data.parameter_name || '');
                document.getElementById('editIndicatorDisplay').value = 
                    data.indicator_code || 'No code';
                document.getElementById('editProgramDisplay').value = 
                    (data.program_name || '') + ' (' + (data.program_code || '') + ')';
                document.getElementById('editFacultyDisplay').value = 
                    (data.faculty_firstname || '') + ' ' + (data.faculty_lastname || '');
                document.getElementById('editStatusDisplay').value = formatStatus(data.status);
                document.getElementById('editAssignedByDisplay').value = 
                    (data.assigned_by_firstname || '') + ' ' + (data.assigned_by_lastname || '') || 'System';
                document.getElementById('editAssignedDateDisplay').value = formatDate(data.assigned_at);
                document.getElementById('editUpdatedDateDisplay').value = formatDate(data.updated_at || data.assigned_at);
                document.getElementById('editIndicatorDescription').value = data.indicator_description || 'No description available';
            } else {
                // Show error message
                var errorMsg = response.message || 'Failed to load assignment details';
                alert('Error: ' + errorMsg);
                
                // Reset to placeholder
                document.getElementById('editCycleDisplay').value = 'Error loading data';
                document.getElementById('editAreaDisplay').value = 'N/A';
                document.getElementById('editParameterDisplay').value = 'N/A';
                document.getElementById('editIndicatorDisplay').value = 'N/A';
                document.getElementById('editProgramDisplay').value = 'N/A';
                document.getElementById('editFacultyDisplay').value = 'N/A';
                document.getElementById('editStatusDisplay').value = 'N/A';
                document.getElementById('editAssignedByDisplay').value = 'N/A';
                document.getElementById('editAssignedDateDisplay').value = 'N/A';
                document.getElementById('editUpdatedDateDisplay').value = 'N/A';
                document.getElementById('editIndicatorDescription').value = 'N/A';
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText, status, error);
            alert('Failed to load assignment details. Please try again.');
            
            // Reset to placeholder
            document.getElementById('editCycleDisplay').value = 'Network Error';
            document.getElementById('editAreaDisplay').value = 'N/A';
            document.getElementById('editParameterDisplay').value = 'N/A';
            document.getElementById('editIndicatorDisplay').value = 'N/A';
            document.getElementById('editProgramDisplay').value = 'N/A';
            document.getElementById('editFacultyDisplay').value = 'N/A';
            document.getElementById('editStatusDisplay').value = 'N/A';
            document.getElementById('editAssignedByDisplay').value = 'N/A';
            document.getElementById('editAssignedDateDisplay').value = 'N/A';
            document.getElementById('editUpdatedDateDisplay').value = 'N/A';
            document.getElementById('editIndicatorDescription').value = 'N/A';
        }
    });

    // Show modal
    var editModal = new bootstrap.Modal(document.getElementById('editAssignmentModal'));
    editModal.show();
}

// Update button click handler
$(document).ready(function() {
    // If you're using buttons with onclick attributes, update them to:
    // onclick="viewAssignment('<?= $assignment['assignment_id'] ?>')"
    
    // Or if using event delegation:
    $('body').on('click', '.view-assignment-btn', function() {
        var assignmentId = $(this).data('assignment-id');
        viewAssignment(assignmentId);
    });
});
</script>
<script>
// Global variables
let selectedAssignmentId = null;
let selectedDocumentId = null;


function loadFacultyForProgram(programId, selectedFacultyId = null) {
    var userSelect = $('#editFacultyId');

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
                    var selected = (faculty.user_id == selectedFacultyId) ? 'selected' : '';
                    userSelect.append(
                        `<option value="${faculty.user_id}" ${selected}>
                            ${faculty.first_name} ${faculty.last_name}
                        </option>`
                    );
                });
            } else {
                userSelect.html('<option value="">No faculty found</option>');
            }
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