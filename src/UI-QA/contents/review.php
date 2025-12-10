<?php
// Fetch Documents with related data
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
        ac.cycle_name,
        ac.academic_year,
        r.review_id,
        dept.department_name
    FROM documents d
    LEFT JOIN users u ON d.uploaded_by = u.user_id
    LEFT JOIN accreditation_indicators ai ON d.indicator_id = ai.indicator_id
    LEFT JOIN programs p ON d.program_id = p.program_id
    LEFT JOIN accreditation_cycles ac ON d.cycle_id = ac.cycle_id
    LEFT JOIN departments dept ON p.department_id = dept.department_id
    INNER JOIN reviews r ON d.document_id = r.document_id
    WHERE r.reviewer_id = :reviewer_id
    ORDER BY d.created_at DESC
");
$stmtDocuments->execute([
    'reviewer_id' => $user_id
]);
$documents = $stmtDocuments->fetchAll(PDO::FETCH_ASSOC);

// Fetch data for dropdowns
$stmtIndicators = $pdo->query("SELECT indicator_id, indicator_code, indicator_description FROM accreditation_indicators ORDER BY indicator_code");
$indicators = $stmtIndicators->fetchAll(PDO::FETCH_ASSOC);

$stmtPrograms = $pdo->query("SELECT program_id, program_code, program_name FROM programs ORDER BY program_name");
$programs = $stmtPrograms->fetchAll(PDO::FETCH_ASSOC);

$stmtCycles = $pdo->query("SELECT cycle_id, cycle_name, academic_year FROM accreditation_cycles ORDER BY academic_year DESC");
$cycles = $stmtCycles->fetchAll(PDO::FETCH_ASSOC);
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
    </div>
    <!-- approve Document Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="approve-document-form" class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title text-white" id="approvalModalLabel">Confirm Approval</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="deleteDocumentMessage">
                        Are you sure you want to approve this document? This action cannot be undone.
                    </div>
                    <input type="hidden" name="document_id" id="approveDocumentId">
                    <input type="hidden" name="review_id" id="approvereviewId">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn bg-gradient-primary">
                        <i class="fas fa-trash me-1"></i> Yes, Approve
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- revision Document Modal -->
    <div class="modal fade" id="revisionModal" tabindex="-1" aria-labelledby="revisionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="revise-document-form" class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title text-white" id="revisionModalLabel">Confirm Revision</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="deleteDocumentMessage">
                        Are you sure you want this document to be revise? This action cannot be undone.
                    </div>
                    <input type="hidden" name="document_id" id="reviseDocumentId">
                    <input type="hidden" name="review_id" id="revisereviewId">
                    <div class="mt-2" style="border-top: solid 1px #000;">
                        <label class="form-label">Revision Comment</label>
                        <textarea name="comments" class="form-control"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn bg-gradient-primary">
                        <i class="fas fa-trash me-1"></i> Yes, revise
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
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
                                <style>
                                        .truncate-text {
                                            display: inline-block;
                                            max-width: 20rem;     /* your desired width */
                                            white-space: nowrap; 
                                            overflow: hidden;
                                            text-overflow: ellipsis;
                                        }
                                    </style>
                                <tr>
                                    <th>Filename:</th>
                                    <td class="truncate-text" id="viewFilename"></td>
                                </tr>
                                 <tr>
                                    <th>File Type:</th>
                                    <td class="truncate-text" id="fileType"></td>
                                </tr>
                                <tr>
                                    <th>File Size:</th>
                                    <td class="truncate-text" id="viewFileSize"></td>
                                </tr>
                                <tr>
                                    <th>Uploaded By:</th>
                                    <td class="truncate-text" id="viewUploadedBy"></td>
                                </tr>
                                <tr>
                                    <th>Uploaded On:</th>
                                    <td class="truncate-text" id="viewCreatedAt"></td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td class="truncate-text" id="viewUpdatedAt"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Accreditation Details</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Indicator:</th>
                                    <td class="truncate-text" id="viewIndicator"></td>
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
                                    <th>Version:</th>
                                    <td class="truncate-text" id="viewVersion"></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td class="truncate-text" id="viewStatus"></td>
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
                        $statusIcon = 'APPROVED';
                        break;
                    case 'submitted': 
                        $statusClass = 'bg-primary'; 
                        $statusIcon = 'SUBMITTED';
                        break;
                    case 'under_review': 
                        $statusClass = 'bg-info'; 
                        $statusIcon = 'UNDER_REVIEW';
                        break;
                    case 'needs_revision': 
                        $statusClass = 'bg-info'; 
                        $statusIcon = 'NEEDS_REVISION';
                        break;
                    case 'draft': 
                        $statusClass = 'bg-secondary'; 
                        $statusIcon = 'DRAFT';
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
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-sm btn-light view-document-btn"
                                    data-bs-toggle="tooltip" data-bs-title="View Details"
                                    data-document-id="<?= $document['document_id'] ?>"
                                    data-filename="<?= htmlspecialchars($document['filename']) ?>"
                                    data-file-path="<?= htmlspecialchars($document['file_path']) ?>"
                                    data-file-size="<?= $document['file_size'] ?>"
                                    data-uploaded-by="<?= htmlspecialchars($uploadedByName) ?>"
                                    data-created-at="<?= htmlspecialchars($document['created_at']) ?>"
                                    data-updated-at="<?= htmlspecialchars($document['updated_at']) ?>"
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

                                <button type="button" class="btn btn-sm btn-danger"
                                    data-document-id="<?= $document['document_id'] ?>"
                                    data-review-id="<?= htmlspecialchars($document['review_id']) ?>"
                                    id="get_docuemntAndreview_id"
                                    data-bs-toggle="modal" data-bs-target="#approvalModal">
                                    approve
                                </button>
                                <button type="button" class="btn btn-sm btn-dark text-white"
                                    data-document-id="<?= $document['document_id'] ?>"
                                    data-review-id="<?= htmlspecialchars($document['review_id']) ?>"
                                    id="get_docuemntAndreview_id"
                                    data-bs-toggle="modal" data-bs-target="#revisionModal">
                                    revision
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</section>

<script>
// Document View Function
function viewDocument(documentId, filename, filePath, fileSize, uploadedBy, createdAt, updatedAt,
    indicator, program, department, cycle, version, status) {

    // Helper function to get correct path
    function getCorrectFilePath(path) {
        // Remove any date-based folders from the path and just get the filename
        const filename = path.split('/').pop();
        // Return correct path - always in uploads/documents/
        return '../../authentication/uploads/' + filename;
    }

    // Get file extension
    function getFileExtension(filename) {
        return filename.split('.').pop().toLowerCase();
    }

    // Get file type name
    function getFileTypeName(extension) {
        const fileTypes = {
            'pdf': 'PDF Document',
            'doc': 'Microsoft Word Document',
            'docx': 'Microsoft Word Document',
            'xls': 'Microsoft Excel Spreadsheet',
            'xlsx': 'Microsoft Excel Spreadsheet',
            'ppt': 'Microsoft PowerPoint Presentation',
            'pptx': 'Microsoft PowerPoint Presentation',
            'zip': 'ZIP Archive',
            'jpg': 'JPEG Image',
            'jpeg': 'JPEG Image',
            'png': 'PNG Image',
            'gif': 'GIF Image',
            'txt': 'Text File'
        };
        return fileTypes[extension] || 'File';
    }

    // Set document information
    document.getElementById('viewFilename').textContent = filename;
    document.getElementById('viewFileSize').textContent = formatFileSize(fileSize);
    document.getElementById('viewUploadedBy').textContent = uploadedBy;
    document.getElementById('viewCreatedAt').textContent = new Date(createdAt).toLocaleString();
    document.getElementById('viewUpdatedAt').textContent = new Date(updatedAt).toLocaleString();
    document.getElementById('viewIndicator').textContent = indicator;
    document.getElementById('viewProgram').textContent = program;
    document.getElementById('viewDepartment').textContent = department;
    document.getElementById('viewCycle').textContent = cycle;
    document.getElementById('viewVersion').textContent = version;

    // Get file extension and set file type
    const fileExtension = getFileExtension(filename);
    const fileTypeName = getFileTypeName(fileExtension);
    document.getElementById('fileType').textContent = fileTypeName + ' (.' + fileExtension + ')';

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
            statusClass = 'badge bg-warning';
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
    console.log('File extension:', fileExtension);

    // Now set the download link
    const downloadLink = document.getElementById('downloadDocumentLink');
    downloadLink.href = webFilePath;
    downloadLink.download = filename;

    // Set the preview link based on file type
    const previewLink = document.getElementById('previewDocumentLink');
    const basePath = '../../authentication/uploads/';
    const actualFilename = filename.split('/').pop(); // In case filename has path

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
                const baseUrl = window.location.origin;
                const projectPath = window.location.pathname.split('/').slice(0, -2).join('/') + '/';
                const absolutePath = baseUrl + projectPath + webFilePath.replace('../../', '');
                previewLink.href = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(absolutePath)}`;
                previewLink.setAttribute('target', '_blank');
                previewLink.innerHTML = '<i class="fas fa-external-link-alt"></i> Open in Office Online';
            }
        },
        'docx': {
            type: 'office',
            icon: 'fa-file-word',
            color: 'btn-primary',
            action: function() {
                const baseUrl = window.location.origin;
                const projectPath = window.location.pathname.split('/').slice(0, -2).join('/') + '/';
                const absolutePath = baseUrl + projectPath + webFilePath.replace('../../', '');
                previewLink.href = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(absolutePath)}`;
                previewLink.setAttribute('target', '_blank');
                previewLink.innerHTML = '<i class="fas fa-external-link-alt"></i> Open in Office Online';
            }
        },
        'xls': {
            type: 'office',
            icon: 'fa-file-excel',
            color: 'btn-success',
            action: function() {
                const baseUrl = window.location.origin;
                const projectPath = window.location.pathname.split('/').slice(0, -2).join('/') + '/';
                const absolutePath = baseUrl + projectPath + webFilePath.replace('../../', '');
                previewLink.href = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(absolutePath)}`;
                previewLink.setAttribute('target', '_blank');
                previewLink.innerHTML = '<i class="fas fa-external-link-alt"></i> Open in Office Online';
            }
        },
        'xlsx': {
            type: 'office',
            icon: 'fa-file-excel',
            color: 'btn-success',
            action: function() {
                const baseUrl = window.location.origin;
                const projectPath = window.location.pathname.split('/').slice(0, -2).join('/') + '/';
                const absolutePath = baseUrl + projectPath + webFilePath.replace('../../', '');
                previewLink.href = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(absolutePath)}`;
                previewLink.setAttribute('target', '_blank');
                previewLink.innerHTML = '<i class="fas fa-external-link-alt"></i> Open in Office Online';
            }
        },
        'ppt': {
            type: 'office',
            icon: 'fa-file-powerpoint',
            color: 'btn-warning',
            action: function() {
                const baseUrl = window.location.origin;
                const projectPath = window.location.pathname.split('/').slice(0, -2).join('/') + '/';
                const absolutePath = baseUrl + projectPath + webFilePath.replace('../../', '');
                previewLink.href = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(absolutePath)}`;
                previewLink.setAttribute('target', '_blank');
                previewLink.innerHTML = '<i class="fas fa-external-link-alt"></i> Open in Office Online';
            }
        },
        'pptx': {
            type: 'office',
            icon: 'fa-file-powerpoint',
            color: 'btn-warning',
            action: function() {
                const baseUrl = window.location.origin;
                const projectPath = window.location.pathname.split('/').slice(0, -2).join('/') + '/';
                const absolutePath = baseUrl + projectPath + webFilePath.replace('../../', '');
                previewLink.href = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(absolutePath)}`;
                previewLink.setAttribute('target', '_blank');
                previewLink.innerHTML = '<i class="fas fa-external-link-alt"></i> Open in Office Online';
            }
        },
        'zip': {
            type: 'archive',
            icon: 'fa-file-archive',
            color: 'btn-secondary',
            action: function() {
                // ZIP files can't be previewed, make it a download button
                previewLink.className = 'btn btn-secondary';
                previewLink.innerHTML = '<i class="fas fa-download"></i> Download ZIP';
                previewLink.href = webFilePath;
                previewLink.setAttribute('download', filename);
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
            color: 'btn-light',
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
        previewLink.innerHTML = `<i class="fas ${config.icon}"></i> ${config.type === 'office' ? 'Open Online' : 'Preview'}`;
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
function editDocument(documentId, filename, indicatorId, programId, cycleId, version, status) {
    document.getElementById('editDocumentId').value = documentId;
    document.getElementById('currentFileName').textContent = filename;
    document.getElementById('editIndicatorId').value = indicatorId;
    document.getElementById('editProgramId').value = programId;
    document.getElementById('editCycleId').value = cycleId;
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
            const indicator = this.getAttribute('data-indicator');
            const program = this.getAttribute('data-program');
            const department = this.getAttribute('data-department');
            const cycle = this.getAttribute('data-cycle');
            const version = this.getAttribute('data-version');
            const status = this.getAttribute('data-status');

            // Call the view function
            viewDocument(documentId, filename, filePath, fileSize, uploadedBy, createdAt,
                updatedAt,
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
            const version = this.getAttribute('data-version');
            const status = this.getAttribute('data-status');

            editDocument(documentId, filename, indicatorId, programId, cycleId, version,
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