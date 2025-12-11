<?php
$stmtDocuments = $pdo->prepare("
    SELECT 
        d.*,
        u.first_name,
        u.last_name,
        u.middle_name,
        u.suffix,
        ai.indicator_code,
        ai.indicator_description,
        p.program_code,
        p.program_name,
        ap.parameter_code,
        ap.parameter_name,
        aa.area_number,
        aa.area_name,
        ac.cycle_name,
        ac.academic_year,
        dept.department_name
    FROM documents d
    INNER JOIN users u ON d.uploaded_by = u.user_id
    INNER JOIN accreditation_indicators ai ON d.indicator_id = ai.indicator_id
    INNER JOIN accreditation_parameters ap ON ai.parameter_id = ap.parameter_id
    INNER JOIN accreditation_areas aa ON ap.area_id = aa.area_id
    INNER JOIN programs p ON d.program_id = p.program_id
    INNER JOIN accreditation_cycles ac ON d.cycle_id = ac.cycle_id
    INNER JOIN departments dept ON p.department_id = dept.department_id
    WHERE u.user_id = :user_id
    ORDER BY d.created_at DESC
");
$stmtDocuments->execute([
    'user_id' => $user_id
]);
$documents = $stmtDocuments->fetchAll(PDO::FETCH_ASSOC);

// DEBUG: Add this after fetching documents
echo "<!-- DEBUG: User ID = " . $user_id . " -->\n";
echo "<!-- DEBUG: Number of documents found = " . count($documents) . " -->\n";
if (count($documents) === 0) {
    echo "<!-- DEBUG: No documents found for user ID " . $user_id . " -->\n";
    echo "<!-- DEBUG: Query executed successfully: " . ($stmtDocuments->rowCount() ? 'Yes' : 'No') . " -->\n";
}

// Fetch data for dropdowns
$stmtIndicators = $pdo->prepare("
    SELECT DISTINCT 
        accreditation_indicators.indicator_id, 
        accreditation_indicators.indicator_code, 
        accreditation_indicators.indicator_description 
    FROM accreditation_indicators
    INNER JOIN assignments ON accreditation_indicators.indicator_id = assignments.indicator_id
    INNER JOIN users ON assignments.user_id = users.user_id
    WHERE users.user_id = :user_id
    ORDER BY accreditation_indicators.indicator_code
");

$stmtIndicators->execute([
    ':user_id' => $user_id
]);

$indicators = $stmtIndicators->fetchAll(PDO::FETCH_ASSOC);

$stmtPrograms = $pdo->prepare("
    SELECT 
        programs.program_id, 
        programs.program_code, 
        programs.program_name 
    FROM programs
    INNER JOIN users ON programs.program_id = users.program_id
    WHERE users.user_id = :user_id
");
$stmtPrograms->execute([
    ':user_id' => $user_id  
]);
$programs = $stmtPrograms->fetch(PDO::FETCH_ASSOC);

$stmtCycles = $pdo->query("SELECT cycle_id, cycle_name, academic_year FROM accreditation_cycles WHERE status = 'active'");
$cycles = $stmtCycles->fetch(PDO::FETCH_ASSOC);
 $cycle_name = $cycles["cycle_name"] ?? '';
                            $academic_year = $cycles["academic_year"] ?? '';
                            $cycle_id = $cycles["cycle_id"] ?? '';
?>
<section>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="mx-2">
            <h4 class=""><span class=""><i class="fa-solid fa-file-alt me-2"></i></span>Document Management</h4>
            <small class="text-muted">Manage accreditation documents</small>
        </div>
    </div>

    <!-- DOCUMENTS ADD AND SEARCH -->
    <div class="col-md-12 col-12 d-flex justify-content-between mb-3">
        <div class="col-md-4 col-12">
            <input type="text" placeholder="Search by filename, program, or indicator" id="searchDocuments"
                class="form-control">
        </div>
        <div class="col-md-4">
            <select id="filterCycles" class="form-select">
                <option value="">All Cycles</option>
                <?php
                        $stmtPrograms = $pdo->prepare("SELECT academic_year FROM accreditation_cycles ORDER BY academic_year");
                        $stmtPrograms->execute();
                        $accreditation_cycles = $stmtPrograms->fetchAll(PDO::FETCH_ASSOC);
                        foreach($accreditation_cycles as $program) {
                            echo '<option value="' . $program['academic_year'] . '">' . htmlspecialchars($program['academic_year']) . '</option>';
                        }
                        ?>
            </select>
        </div>
        <div class="col-md-3 d-flex justify-content-end">
                <button type="button" class="btn bg-gradient-primary" data-bs-toggle="modal"
                    data-bs-target="#addDocumentModal" id="add_new_document">
                    <i class="fa fa-upload"></i> Upload Document
                </button>
        </div>
    </div>

    <!-- ADD Document Modal -->
    <div class="modal fade" id="addDocumentModal" tabindex="-1" aria-labelledby="addDocumentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title text-white" id="addDocumentModalLabel">Upload New Document</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                        onclick="location.reload()"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="document-form" enctype="multipart/form-data">
                        <input type="hidden" name="user_id" value="<?= $user_id ?>">
                        <div class="col-md-6">
                            <label class="form-label">Document File</label>
                           <input type="file" class="form-control" name="document_file"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/zip,application/x-zip-compressed" 
                                required />
                                <small class="text-muted">Accepted formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Indicator</label>
                            <select name="indicator_id" class="form-select" required>
                                <option value="">Select Indicator</option>
                                <?php foreach($indicators as $indicator) : ?>
                                <option value="<?= $indicator['indicator_id'] ?>">
                                    <?= htmlspecialchars($indicator['indicator_code']) . ' - ' . htmlspecialchars($indicator['indicator_description']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Program</label>
                            <input type="hidden" class="form-control" name="program_id" value="<?= htmlspecialchars($programs["program_id"]) ?>" read_only>
                            <p class="form-control" readonly><?= htmlspecialchars($programs["program_name"] . '(' . $programs["program_code"]) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Accreditation Cycle</label>
                            <input type="hidden" class="form-control" name="cycle_id" value="<?= htmlspecialchars($cycles["cycle_id"]) ?>" read_only>
                            <p class="form-control" readonly><?php 
                                    if(empty($cycle_name)){
                                        echo 'No active cycle';
                                    } else { 
                                        echo htmlspecialchars($cycle_name . ' (' . $academic_year . ')');
                                    } 
                                ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <input type="text" name="status" class="form-control" value="draft" readonly>
                        </div>
                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn bg-gradient-primary px-5">
                                <i class="bi bi-upload me-1"></i> Upload Document
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Document Modal -->
    <div class="modal fade" id="editDocumentModal" tabindex="-1" aria-labelledby="editDocumentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="edit-document-form" class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title text-white" id="editDocumentModalLabel">Edit Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="document_id" id="editDocumentId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Current File</label>
                            <div class="form-control" id="currentFileName"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Replace File (Optional)</label>
                            <input type="file" class="form-control" id="editDocumentFile" name="document_file"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                            <small class="text-muted">Leave empty to keep current file</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Indicator</label>
                            <select name="indicator_id" id="editIndicatorId" class="form-select" required>
                                <option value="">Select Indicator</option>
                                <?php foreach($indicators as $indicator) : ?>
                                <option value="<?= $indicator['indicator_id'] ?>">
                                    <?= htmlspecialchars($indicator['indicator_code']) . ' - ' . htmlspecialchars($indicator['indicator_description']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Program</label>
                            <input type="hidden" name="program_id" class="form-control" value="<?= $programs["program_id"] ?>">
                            <p class="form-control" readonly><?= htmlspecialchars($programs['program_name']) . ' (' . htmlspecialchars($programs['program_code']) . ')' ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Accreditation Cycle</label>
                            <input type="hidden" class="form-control" name="cycle_id" value="<?= htmlspecialchars($cycles["cycle_id"]) ?>" read_only>
                            <p class="form-control" readonly><?php 
                                    if(empty($cycle_name)){
                                        echo 'No active cycle';
                                    } else { 
                                        echo htmlspecialchars($cycle_name . ' (' . $academic_year . ')');
                                    } 
                                ?></p>
                        </div>
                        <div class="col-md-3 d-none">
                            <label class="form-label">Version</label>
                            <input type="number" class="form-control" id="editVersion" name="version" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="editStatus" class="form-select" disabled required>
                                <option value="draft">Draft</option>
                                <option value="submitted">Submitted</option>
                                <option value="under_review">Under Review</option>
                                <option value="needs_revision">Needs Revision</option>
                                <option value="approved">Approved</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn bg-gradient-primary">Update Document</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Document Modal -->
    <div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white" id="viewDocumentModalLabel">Document Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Document Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Filename:</th>
                                    <style>
                                        .truncate-text {
                                            display: inline-block;
                                            max-width: 20rem;     /* your desired width */
                                            white-space: nowrap; 
                                            overflow: hidden;
                                            text-overflow: ellipsis;
                                        }
                                    </style>
                                    <td id="viewFilename" class="truncate-text">
                                    </td>
                                </tr>
                                <tr>
                                    <th>File Size:</th>
                                    <td id="viewFileSize"></td>
                                </tr>
                                <tr>
                                    <th>Uploaded By:</th>
                                    <td id="viewUploadedBy"></td>
                                </tr>
                                <tr>
                                    <th>Uploaded On:</th>
                                    <td id="viewCreatedAt"></td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td id="viewUpdatedAt"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Accreditation Details</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Area:</th>
                                    <td id="viewArea" class="truncate-text"></td>
                                </tr>
                                <tr>
                                    <th>Indicator:</th>
                                    <td id="viewIndicator" class="truncate-text"></td>
                                </tr>
                                <tr>
                                    <th>Program:</th>
                                    <td class="truncate-text" id="viewProgram"></td>
                                </tr>
                                <tr>
                                    <th>Department:</th>
                                    <td class="truncate-text" id="viewDepartment"></td>
                                </tr>
                                <tr>
                                    <th>Cycle:</th>
                                    <td class="truncate-text" id="viewCycle"></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td id="viewStatus"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <!-- Fixed Download Link -->
                        <a href="#" id="downloadDocumentLink" class="btn bg-gradient-primary" download>
                            <i class="fas fa-download"></i> Download Document
                        </a>
                        <!-- Fixed Preview Link -->
                        <a href="#" id="previewDocumentLink" class="btn btn-secondary" target="_blank">
                            <i class="fas fa-eye"></i> Preview
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DOCUMENTS TABLE -->
    <div class="table-responsive table-body">
        <table class="table table-bordered table-sm table-hover">
            <thead class="table-light">
                <tr>
                    <th width="5%">#</th>
                    <th width="25%">Document</th>
                    <th width="15%">Details</th>
                    <th width="10%">Cycle</th>
                    <th width="10%">Status</th>
                    <th width="15%">Upload Info</th>
                    <th width="20%">Actions</th>
                </tr>
            </thead>
            <tbody id="documentsTable">
                <?php 
            $documentCount = 1;
            foreach($documents as $document) : 
                $statusClass = '';
                $statusIcon = '';
                switch($document['status']) {
                    case 'approved': 
                        $statusClass = 'bg-success'; 
                        $statusIcon = '<i class="fas fa-check-circle"></i>';
                        break;
                    case 'submitted': 
                        $statusClass = 'bg-primary'; 
                        $statusIcon = '<i class="fas fa-paper-plane"></i>';
                        break;
                    case 'under_review': 
                        $statusClass = 'bg-info'; 
                        $statusIcon = '<i class="fas fa-search"></i>';
                        break;
                    case 'needs_revision': 
                        $statusClass = 'bg-info'; 
                        $statusIcon = '<i class="fas fa-exclamation-circle"></i>';
                        break;
                    case 'draft': 
                        $statusClass = 'bg-secondary'; 
                        $statusIcon = '<i class="fas fa-edit"></i>';
                        break;
                }
                
                // File icon based on extension
                $file_ext = strtolower(pathinfo($document['filename'], PATHINFO_EXTENSION));
                $file_icon = 'fa-file';
                if (in_array($file_ext, ['pdf'])) $file_icon = 'fa-file-pdf text-danger';
                elseif (in_array($file_ext, ['doc', 'docx'])) $file_icon = 'fa-file-word text-primary';
                elseif (in_array($file_ext, ['xls', 'xlsx'])) $file_icon = 'fa-file-excel text-success';
                elseif (in_array($file_ext, ['ppt', 'pptx'])) $file_icon = 'fa-file-powerpoint text-warning';
                
                // Short uploaded by name
                $uploadedByName = substr($document['first_name'], 0, 1) . '. ' . $document['last_name'];
            ?>
                <tr>
                    <td class="text-center"><?= $documentCount++ ?></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="fas <?= $file_icon ?> fa-lg me-2"></i>
                            <div class="text-truncate" style="max-width: 200px;"
                                title="<?= htmlspecialchars($document['filename']) ?>">
                                <div class="fw-bold"><?= htmlspecialchars($document['filename']) ?></div>
                                <small class="text-muted">
                                     • <?= formatFileSize($document['file_size']) ?>
                                </small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="small">
                            <div><strong>Indicator:</strong> <?= htmlspecialchars($document['indicator_code']) ?></div>
                            <div><strong>Program:</strong> <?= htmlspecialchars($document['program_code']) ?></div>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark">
                            <?= htmlspecialchars($document['academic_year']) ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge <?= $statusClass ?>" data-bs-toggle="tooltip"
                            data-bs-title="<?= str_replace('_', ' ', ucfirst($document['status'])) ?>">
                            <?= $statusIcon ?>
                        </span>
                    </td>
                    <td>
                        <div class="small">
                            <div class="text-truncate" style="max-width: 120px;">
                                <i class="fas fa-user me-1"></i>
                                <?= htmlspecialchars($uploadedByName) ?>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('M d, Y', strtotime($document['created_at'])) ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-light view-document-btn"
                                data-bs-toggle="tooltip" data-bs-title="View Details"
                                data-document-id="<?= $document['document_id'] ?>"
                                data-filename="<?= htmlspecialchars($document['filename']) ?>"
                                data-file-path="<?= htmlspecialchars($document['file_path']) ?>"
                                data-file-size="<?= $document['file_size'] ?>"
                                data-uploaded-by="<?= htmlspecialchars($uploadedByName) ?>"
                                data-created-at="<?= htmlspecialchars($document['created_at']) ?>"
                                data-updated-at="<?= htmlspecialchars($document['updated_at']) ?>"
                                data-area="<?= htmlspecialchars($document['area_number'] . ' - ' . $document['area_name']) ?>"
                                data-indicator="<?= htmlspecialchars($document['indicator_code'] . ' - ' . $document['indicator_description']) ?>"
                                data-program="<?= htmlspecialchars($document['program_code'] . ' - ' . $document['program_name']) ?>"
                                data-department="<?= htmlspecialchars($document['department_name']) ?>"
                                data-cycle="<?= htmlspecialchars($document['cycle_name'] . ' (' . $document['academic_year'] . ')') ?>"

                                data-status="<?= htmlspecialchars($document['status']) ?>">
                                <i class="fas fa-eye"></i>
                            </button>

                            <a href="<?= htmlspecialchars('../../authentication/uploads/' . basename($document['file_path'])) ?>"
                                class="btn btn-sm btn-light" data-bs-toggle="tooltip" data-bs-title="Download"
                                download="<?= htmlspecialchars($document['filename']) ?>">
                                <i class="fas fa-download"></i>
                            </a>

                            <button type="button" class="btn btn-sm btn-light edit-document-btn"
                                data-bs-toggle="tooltip" data-bs-title="Edit"
                                data-document-id="<?= $document['document_id'] ?>"
                                data-filename="<?= htmlspecialchars($document['filename']) ?>"
                                data-indicator-id="<?= $document['indicator_id'] ?>"
                                data-cycle-id="<?= $document['cycle_id'] ?>"
                                data-status="<?= htmlspecialchars($document['status']) ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-light" id="get_comments"
                                data-target-id="#commentsModal"
                                data-bs-toggle="tooltip"
                                data-id="<?= $document["document_id"] ?>"
                            >
                                <i class="fas fa-comment-dots"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <!-- comment Document Modal -->
    <div class="modal fade" id="commentsModal" tabindex="-1" aria-labelledby="commentsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title text-white" id="commentsModalLabel">Document Comments & Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="document_id" id="documentCommentId">
                    
                    <!-- QA/Reviewer Comments Section -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">📋 QA/Reviewer Comments</label>
                        <div id="qaCommentsContainer" class="border rounded p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                            <div id="qaCommentsList">
                                <div class="text-center text-muted">
                                    <i class="bi bi-info-circle"></i> No QA comments found
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Accreditor Comments Section -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">👥 Accreditor Comments</label>
                        <div id="accreditorCommentsContainer" class="border rounded p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                            <div id="accreditorCommentsList">
                                <div class="text-center text-muted">
                                    <i class="bi bi-info-circle"></i> No accreditor comments found
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- VIEW COMMENTS JS -->
 <script>
    $(document).ready(function() {
    // When comment button is clicked
    $(document).on('click', '#get_comments', function() {
        const documentId = $(this).data('id');
        $('#documentCommentId').val(documentId);
        $('#newCommentDocumentId').val(documentId);
        
        // Load comments
        loadDocumentComments(documentId);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('commentsModal'));
        modal.show();
    });
    
    // Toggle review fields based on comment type
    $('#commentTypeSelect').on('change', function() {
        if ($(this).val() === 'review') {
            $('#reviewFields').show();
        } else {
            $('#reviewFields').hide();
        }
    });
    
    // Handle new comment submission
    $('#newCommentForm').on('submit', function(e) {
        e.preventDefault();
        submitNewComment();
    });
});

// Function to load document comments
function loadDocumentComments(documentId) {
    // Show loading state
    $('#qaCommentsList').html('<div class="text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading QA comments...</div>');
    $('#accreditorCommentsList').html('<div class="text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading accreditor comments...</div>');
    
    $.ajax({
        url: base_url + "/authentication/action.php?action=get_document_comments",
        method: 'POST',
        dataType: 'json',
        data: {
            document_id: documentId
        },
        success: function(response) {
            if (response.status == 1) {
                // Display QA/Reviewer comments
                displayQAComments(response.data.reviews || []);
                
                // Display Accreditor comments
                displayAccreditorComments(response.data.feedbacks || []);
            } else {
                $('#qaCommentsList').html('<div class="text-center text-danger">Error loading comments</div>');
                $('#accreditorCommentsList').html('<div class="text-center text-danger">Error loading comments</div>');
            }
        },
        error: function() {
            $('#qaCommentsList').html('<div class="text-center text-danger">Error loading comments</div>');
            $('#accreditorCommentsList').html('<div class="text-center text-danger">Error loading comments</div>');
        }
    });
}

// Function to display QA/Reviewer comments
function displayQAComments(comments) {
    const container = $('#qaCommentsList');
    
    if (!comments || comments.length === 0) {
        container.html('<div class="text-center text-muted"><i class="bi bi-info-circle"></i> No QA comments found</div>');
        return;
    }
    
    let html = '';
    comments.forEach(comment => {
        const date = new Date(comment.reviewed_at).toLocaleString();
        const actionClass = comment.review_action === 'approved' ? 'dark' : 'dark';
        const priorityClass = comment.priority === 'urgent' ? 'dark' : 
                             comment.priority === 'high' ? 'dark' : 'dark';
        
        html += `
            <div class="comment-item mb-3 p-2 border-bottom">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>${comment.reviewer_name || 'Unknown Reviewer'}</strong>
                        <span class="badge bg-${actionClass} ms-2">${comment.review_action}</span>
                        <span class="badge bg-${priorityClass} ms-1">${comment.priority}</span>
                    </div>
                    <small class="text-muted">${date}</small>
                </div>
                ${comment.deadline ? `<div class="mt-1"><small><strong>Deadline:</strong> ${comment.deadline}</small></div>` : ''}
                ${comment.revision_reason ? `<div class="mt-1"><small><strong>Reason:</strong> ${comment.revision_reason}</small></div>` : ''}
                <div class="mt-2">${comment.comments || 'No comment'}</div>
            </div>
        `;
    });
    
    container.html(html);
}

// Function to display Accreditor comments
function displayAccreditorComments(comments) {
    const container = $('#accreditorCommentsList');
    
    if (!comments || comments.length === 0) {
        container.html('<div class="text-center text-muted"><i class="bi bi-info-circle"></i> No accreditor comments found</div>');
        return;
    }
    
    let html = '';
    comments.forEach(comment => {
        const date = new Date(comment.created_at).toLocaleString();
        const updated = comment.updated_at && comment.updated_at !== comment.created_at ? 
                       `<small class="text-muted">(Updated: ${new Date(comment.updated_at).toLocaleDateString()})</small>` : '';
        
        html += `
            <div class="comment-item mb-3 p-2 border-bottom">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>${comment.user_name || 'Unknown User'}</strong>
                        <small class="text-muted ms-2">${comment.user_role || ''}</small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">${date}</small>
                        ${updated}
                    </div>
                </div>
                <div class="mt-2">${comment.feedback}</div>
            </div>
        `;
    });
    
    container.html(html);
}

 </script>
<script>
    // Document View Function
    function viewDocument(documentId, filename, filePath, fileSize, uploadedBy, createdAt, updatedAt,
        area, indicator, program, department, cycle, version, status) {

        // Helper function to get correct path
        function getCorrectFilePath(path) {
            // Extract filename
            const filename = path.split('/').pop();
            // Return correct path
            return '../../authentication/uploads/' + filename;
        }

        // Set document information
        document.getElementById('viewFilename').textContent = filename;
        document.getElementById('viewFileSize').textContent = formatFileSize(fileSize);
        document.getElementById('viewUploadedBy').textContent = uploadedBy;
        document.getElementById('viewCreatedAt').textContent = new Date(createdAt).toLocaleString();
        document.getElementById('viewUpdatedAt').textContent = new Date(updatedAt).toLocaleString();
        document.getElementById('viewArea').textContent = area;
        document.getElementById('viewIndicator').textContent = indicator;
        document.getElementById('viewProgram').textContent = program;
        document.getElementById('viewDepartment').textContent = department;
        document.getElementById('viewCycle').textContent = cycle;

        // Set status with appropriate badge
        const statusText = status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
        let statusClass = '';
        switch (status) {
            case 'approved':
                statusClass = 'badge bg-success';
                break;
            case 'submitted':
                statusClass = 'badge bg-primary';
                break;
            case 'under_review':
                statusClass = 'badge bg-info';
                break;
            case 'needs_revision':
                statusClass = 'badge bg-info';
                break;
            case 'draft':
                statusClass = 'badge bg-secondary';
                break;
        }
        document.getElementById('viewStatus').innerHTML = `<span class="${statusClass}">${statusText}</span>`;

        // Get the CORRECT web-accessible path
        const webFilePath = getCorrectFilePath(filePath);

        // Debug log
        console.log('Original path:', filePath);
        console.log('Corrected path:', webFilePath);

        // Now set the download link
        const downloadLink = document.getElementById('downloadDocumentLink');
        downloadLink.href = webFilePath;
        downloadLink.download = filename;

        // Set the preview link based on file type
        const previewLink = document.getElementById('previewDocumentLink');
        const fileExtension = filename.split('.').pop().toLowerCase();

        // Always show the button
        previewLink.style.display = 'inline-block';

        // Reset button to default
        previewLink.className = 'btn btn-secondary';
        previewLink.innerHTML = '<i class="fas fa-eye"></i> Preview';
        previewLink.onclick = null;
        previewLink.removeAttribute('download');

        // File type mapping
        const fileTypeConfig = {
            'pdf': {
                type: 'preview',
                icon: 'fa-file-pdf',
                color: 'btn-danger',
                action: function() {
                    previewLink.href = webFilePath;
                    previewLink.setAttribute('target', '_blank');
                }
            },
            'doc': {
                type: 'office',
                icon: 'fa-file-word',
                color: 'btn-primary',
                action: function() {
                    // Get absolute URL for Office Online Viewer
                    const absoluteUrl = window.location.origin + '/OAMS-ZPPSU/' + webFilePath.replace(/^\.\.\//g,
                        '');
                    previewLink.href =
                        `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(absoluteUrl)}`;
                    previewLink.setAttribute('target', '_blank');
                    previewLink.innerHTML = '<i class="fas fa-external-link-alt"></i> Open in Office Online';
                }
            },
            'docx': {
                type: 'office',
                icon: 'fa-file-word',
                color: 'btn-primary',
                action: function() {
                    const absoluteUrl = window.location.origin + '/OAMS-ZPPSU/' + webFilePath.replace(/^\.\.\//g,
                        '');
                    previewLink.href =
                        `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(absoluteUrl)}`;
                    previewLink.setAttribute('target', '_blank');
                    previewLink.innerHTML = '<i class="fas fa-external-link-alt"></i> Open in Office Online';
                }
            },
            'xls': {
                type: 'office',
                icon: 'fa-file-excel',
                color: 'btn-success',
                action: function() {
                    const absoluteUrl = window.location.origin + '/OAMS-ZPPSU/' + webFilePath.replace(/^\.\.\//g,
                        '');
                    previewLink.href =
                        `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(absoluteUrl)}`;
                    previewLink.setAttribute('target', '_blank');
                    previewLink.innerHTML = '<i class="fas fa-external-link-alt"></i> Open in Office Online';
                }
            },
            'xlsx': {
                type: 'office',
                icon: 'fa-file-excel',
                color: 'btn-success',
                action: function() {
                    const absoluteUrl = window.location.origin + '/OAMS-ZPPSU/' + webFilePath.replace(/^\.\.\//g,
                        '');
                    previewLink.href =
                        `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(absoluteUrl)}`;
                    previewLink.setAttribute('target', '_blank');
                    previewLink.innerHTML = '<i class="fas fa-external-link-alt"></i> Open in Office Online';
                }
            },
            'ppt': {
                type: 'office',
                icon: 'fa-file-powerpoint',
                color: 'btn-warning',
                action: function() {
                    const absoluteUrl = window.location.origin + '/OAMS-ZPPSU/' + webFilePath.replace(/^\.\.\//g,
                        '');
                    previewLink.href =
                        `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(absoluteUrl)}`;
                    previewLink.setAttribute('target', '_blank');
                    previewLink.innerHTML = '<i class="fas fa-external-link-alt"></i> Open in Office Online';
                }
            },
            'pptx': {
                type: 'office',
                icon: 'fa-file-powerpoint',
                color: 'btn-warning',
                action: function() {
                    const absoluteUrl = window.location.origin + '/OAMS-ZPPSU/' + webFilePath.replace(/^\.\.\//g,
                        '');
                    previewLink.href =
                        `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(absoluteUrl)}`;
                    previewLink.setAttribute('target', '_blank');
                    previewLink.innerHTML = '<i class="fas fa-external-link-alt"></i> Open in Office Online';
                }
            },
            'jpg': {
                type: 'image',
                icon: 'fa-file-image',
                color: 'btn-info',
                action: function() {
                    previewLink.onclick = function(e) {
                        e.preventDefault();
                        previewImage(webFilePath, filename);
                        return false;
                    };
                }
            },
            'jpeg': {
                type: 'image',
                icon: 'fa-file-image',
                color: 'btn-info',
                action: function() {
                    previewLink.onclick = function(e) {
                        e.preventDefault();
                        previewImage(webFilePath, filename);
                        return false;
                    };
                }
            },
            'png': {
                type: 'image',
                icon: 'fa-file-image',
                color: 'btn-info',
                action: function() {
                    previewLink.onclick = function(e) {
                        e.preventDefault();
                        previewImage(webFilePath, filename);
                        return false;
                    };
                }
            },
            'gif': {
                type: 'image',
                icon: 'fa-file-image',
                color: 'btn-info',
                action: function() {
                    previewLink.onclick = function(e) {
                        e.preventDefault();
                        previewImage(webFilePath, filename);
                        return false;
                    };
                }
            },
            'txt': {
                type: 'text',
                icon: 'fa-file-alt',
                color: 'btn-secondary',
                action: function() {
                    previewLink.href = webFilePath;
                    previewLink.setAttribute('target', '_blank');
                }
            }
        };

        // Apply configuration based on file extension
        if (fileTypeConfig[fileExtension]) {
            const config = fileTypeConfig[fileExtension];
            previewLink.className = 'btn ' + config.color;
            previewLink.innerHTML =
                `<i class="fas ${config.icon}"></i> ${config.type === 'office' ? 'Open Online' : 'Preview'}`;
            config.action();
        } else {
            // For unknown file types, make it a download button
            previewLink.className = 'btn btn-info';
            previewLink.innerHTML = '<i class="fas fa-download"></i> Download';
            previewLink.href = webFilePath;
            previewLink.setAttribute('download', filename);
        }

        // Open modal
        var modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
        modal.show();
    }

    // Helper function to preview images in a lightbox - UPDATED
    function previewImage(imageUrl, title) {
        // Create lightbox modal
        const lightboxHTML = `
            <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="${imageUrl}" class="img-fluid" alt="${title}" style="max-height: 70vh;">
                        </div>
                        <div class="modal-footer">
                            <a href="${imageUrl}" class="btn btn-primary" download="${title}">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing lightbox if any
        const existingLightbox = document.getElementById('imagePreviewModal');
        if (existingLightbox) {
            existingLightbox.remove();
        }

        // Add lightbox to body
        document.body.insertAdjacentHTML('beforeend', lightboxHTML);

        // Show lightbox
        const lightboxModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        lightboxModal.show();

        // Remove modal from DOM when hidden
        document.getElementById('imagePreviewModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    // Helper function to format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Document Edit Function
    function editDocument(documentId, filename, indicatorId, cycleId, version, status) {
        document.getElementById('editDocumentId').value = documentId;
        document.getElementById('currentFileName').textContent = filename;
        document.getElementById('editIndicatorId').value = indicatorId;
        document.getElementById('editVersion').value = version;
        document.getElementById('editStatus').value = status;

        var modal = new bootstrap.Modal(document.getElementById('editDocumentModal'));
        modal.show();
    }
    // Document delete Function
    function setDeleteDcoumentId(indicatorId) {
        document.getElementById('deleteDocumentId').value = indicatorId;
    }


    // Event listeners for document buttons
    document.addEventListener('DOMContentLoaded', function() {
        // View document buttons
        // Event listeners for view document buttons
        document.querySelectorAll('.view-document-btn').forEach(button => {
            button.addEventListener('click', function() {
                const documentId = this.getAttribute('data-document-id');
                const filename = this.getAttribute('data-filename');
                const filePath = this.getAttribute('data-file-path');
                const fileSize = this.getAttribute('data-file-size');
                const uploadedBy = this.getAttribute('data-uploaded-by');
                const createdAt = this.getAttribute('data-created-at');
                const updatedAt = this.getAttribute('data-updated-at');
                const area = this.getAttribute('data-area');
                const indicator = this.getAttribute('data-indicator');
                const program = this.getAttribute('data-program');
                const department = this.getAttribute('data-department');
                const cycle = this.getAttribute('data-cycle');
                const version = this.getAttribute('data-version');
                const status = this.getAttribute('data-status');

                // Call the view function
                viewDocument(documentId, filename, filePath, fileSize, uploadedBy, createdAt,
                    updatedAt, area,
                    indicator, program, department, cycle, version, status);
            });
        });

        // Edit document buttons
        document.querySelectorAll('.edit-document-btn').forEach(button => {
            button.addEventListener('click', function() {
                const documentId = this.getAttribute('data-document-id');
                const filename = this.getAttribute('data-filename');
                const indicatorId = this.getAttribute('data-indicator-id');
                const programId = this.getAttribute('data-program-id');
                const cycleId = this.getAttribute('data-cycle-id');
                const status = this.getAttribute('data-status');

                editDocument(documentId, filename, indicatorId, programId, cycleId,
                    status);
            });
        });

        // Search functionality for Documents
        const searchDocuments = document.getElementById('searchDocuments');
        if (searchDocuments) {
            searchDocuments.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase().trim();
                searchTable('documentsTable', searchTerm);
            });
        }

        // Search table function
        function searchTable(tableId, searchTerm) {
            const table = document.getElementById(tableId);
            if (!table) return;

            const rows = table.getElementsByTagName('tr');
            let hasVisibleRows = false;

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                if (cells.length === 0) continue;

                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];

                    // Skip action cells
                    if (cell.querySelector('button') || cell.querySelector('a')) {
                        continue;
                    }

                    const cellText = cell.textContent.toLowerCase();
                    if (cellText.includes(searchTerm)) {
                        found = true;
                        hasVisibleRows = true;
                        break;
                    }
                }

                row.style.display = found ? '' : 'none';
            }

            // Show "no results" message if needed
            showNoResultsMessage(tableId, hasVisibleRows);
        }

        function showNoResultsMessage(tableId, hasVisibleRows) {
            let noResultsRow = document.getElementById(`no-results-${tableId}`);

            if (!hasVisibleRows && !noResultsRow) {
                const table = document.getElementById(tableId);
                noResultsRow = document.createElement('tr');
                noResultsRow.id = `no-results-${tableId}`;
                noResultsRow.innerHTML =
                    `<td colspan="11" class="text-center text-muted py-3">No documents found matching your search</td>`;
                table.appendChild(noResultsRow);
            } else if (hasVisibleRows && noResultsRow) {
                noResultsRow.remove();
            }
        }
    });
</script>
<script>
    const filterCyclesSelect = document.getElementById('filterCycles');
    const searchDocumentsInput = document.getElementById('searchDocuments');
    const documentsTable = document.getElementById('documentsTable');

    // Store original table data
    let originalDocumentsData = [];

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Store original table data
        const rows = documentsTable.querySelectorAll('tr');
        rows.forEach((row, index) => {
            const cells = row.cells;
            if (cells.length === 0) return; // Skip header row
            
            // Extract academic year from cycle cell (4th cell, index 3)
            let academicYear = '';
            if (cells[3]) {
                const badge = cells[3].querySelector('.badge');
                academicYear = badge ? badge.textContent.trim() : cells[3].textContent.trim();
            }
            
            // Extract all text for search
            let allText = '';
            for (let i = 0; i < cells.length; i++) {
                if (cells[i]) {
                    allText += cells[i].textContent + ' ';
                }
            }
            
            const rowData = {
                element: row,
                academicYear: academicYear.toLowerCase(),
                allText: allText.toLowerCase()
            };
            originalDocumentsData.push(rowData);
        });
        
        console.log('Documents table initialized with', originalDocumentsData.length, 'rows');
    });

    // Filter function for documents table
    function filterDocumentsTable() {
        const cycleValue = filterCyclesSelect.value.toLowerCase();
        const searchTerm = searchDocumentsInput ? searchDocumentsInput.value.toLowerCase().trim() : '';
        
        console.log('Filtering - Cycle:', cycleValue, 'Search:', searchTerm);
        
        let visibleCount = 0;
        
        // Loop through each row data
        originalDocumentsData.forEach((rowData, index) => {
            let showRow = true;
            
            // Apply search filter if there's a search term
            if (searchTerm && showRow) {
                if (!rowData.allText.includes(searchTerm)) {
                    showRow = false;
                }
            }
            
            // Apply cycle filter
            if (cycleValue && showRow) {
                if (rowData.academicYear !== cycleValue) {
                    showRow = false;
                }
            }
            
            // Show/hide row
            rowData.element.style.display = showRow ? '' : 'none';
            
            // Update row number if visible
            if (showRow && rowData.element.cells[0]) {
                rowData.element.cells[0].textContent = ++visibleCount;
            }
        });
        
        // Show message if no results
        showNoResultsMessage(visibleCount);
    }

    // Show "no results" message
    function showNoResultsMessage(visibleCount) {
        // Remove existing no results message
        const existingMessage = documentsTable.querySelector('.no-results-message');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        // Add message if no rows visible
        if (visibleCount === 0 && (filterCyclesSelect.value || (searchDocumentsInput && searchDocumentsInput.value))) {
            const noResultsRow = document.createElement('tr');
            noResultsRow.className = 'no-results-message';
            noResultsRow.innerHTML = `
                <td colspan="7" class="text-center py-4 text-muted">
                    <i class="fas fa-search fa-2x mb-2"></i>
                    <p class="mb-0">No documents found matching your criteria.</p>
                    <small>Try adjusting your search or filters.</small>
                </td>
            `;
            documentsTable.appendChild(noResultsRow);
        }
    }

    // Clear all filters
    function clearDocumentFilters() {
        filterCyclesSelect.value = '';
        if (searchDocumentsInput) {
            searchDocumentsInput.value = '';
        }
        
        // Reset table to show all rows
        originalDocumentsData.forEach((rowData, index) => {
            rowData.element.style.display = '';
            if (rowData.element.cells[0]) {
                rowData.element.cells[0].textContent = index + 1;
            }
        });
        
        // Remove no results message
        const existingMessage = documentsTable.querySelector('.no-results-message');
        if (existingMessage) {
            existingMessage.remove();
        }
    }

    // Event listeners
    filterCyclesSelect.addEventListener('change', filterDocumentsTable);
    
    // Add search input listener if it exists
    if (searchDocumentsInput) {
        searchDocumentsInput.addEventListener('input', filterDocumentsTable);
    }

    // Initialize tooltips
    function initTooltips() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        if (tooltipTriggerList.length > 0 && typeof bootstrap !== 'undefined') {
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }
    }

    // Initialize everything when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initTooltips();
        console.log('Document filtering system initialized');
    });
</script>
<?php
// Helper function to format file size
function formatFileSize($bytes) {
    if ($bytes == 0) return "0 Bytes";
    $k = 1024;
    $sizes = ["Bytes", "KB", "MB", "GB"];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . " " . $sizes[$i];
}
?>