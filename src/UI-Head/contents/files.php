<?php
$employee_id = $_GET["id"];
// Get employee data
$stmtEmployee = $pdo->prepare("SELECT firstname, lastname FROM employee_data WHERE employee_id = :employee_id");
$stmtEmployee->execute(['employee_id' => $employee_id]);
$dataEmployee = $stmtEmployee->fetch(PDO::FETCH_ASSOC);

// Define file types
$file_types = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
$file_data = [];

// Get all files data in one query
$stmt = $pdo->prepare("SELECT * FROM files WHERE employee_id = :employee_id");
$stmt->execute(['employee_id' => $employee_id]);
$all_files = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize files by type
foreach ($all_files as $file) {
    $file_data[$file['type']][] = $file;
}

?>

<style>
.nav-tabs .nav-link {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid #dee2e6;
    margin: 0 2px;
    border-radius: 4px 4px 0 0;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link.active {
    background-color: #dc3545;
    color: white;
    border-color: #dc3545;
}

.nav-tabs .nav-link:hover:not(.active) {
    background-color: #e9ecef;
    border-color: #adb5bd;
}

.file-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
}

.badge-file-count {
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    margin-left: 5px;
}

.tab-content {
    min-height: 400px;
}

.file-actions .btn {
    margin: 2px;
    font-size: 0.8rem;
}

@media (max-width: 768px) {
    .nav-tabs .nav-link {
        font-size: 0.75rem;
        padding: 0.4rem 0.5rem;
        margin: 1px;
    }
    
    .file-actions .btn {
        display: block;
        width: 100%;
        margin-bottom: 5px;
    }
}
</style>

<section>
    <div class="row mb-4">
        <div class="col-md-8">
            <h4 class="text-dark">
                <i class="fa-solid fa-folder-open me-2"></i>
                <?= htmlspecialchars($dataEmployee["firstname"]) . ' ' . htmlspecialchars($dataEmployee["lastname"]) ?> - 201 FILES
            </h4>
            <small class="text-muted">Manage employee 201 files - Create, update and view documents</small>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?page=contents/201" class="btn btn-danger" id="getID" >
                go back to 201 files
            </a>
        </div>
    </div>

    <!-- File Categories Navigation -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fa-solid fa-folders me-2"></i>File Categories</h6>
        </div>
        <div class="card-body p-3">
            <ul class="nav nav-tabs justify-content-center" id="fileTabs" role="tablist">
                <?php foreach ($file_types as $index => $type): 
                    $file_count = isset($file_data[$type]) ? count($file_data[$type]) : 0;
                ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $index === 0 ? 'active' : '' ?>" 
                            id="tab-<?= $type ?>"
                            data-bs-toggle="tab" 
                            data-bs-target="#pane-<?= $type ?>" 
                            type="button" role="tab"
                            aria-controls="pane-<?= $type ?>"
                            aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                        <i class="fa-solid fa-folder me-2"></i><?= $type ?>
                        <?php if ($file_count > 0): ?>
                            <span class="badge-file-count"><?= $file_count ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="card-body pt-0">
            <div class="tab-content mt-4" id="fileTabContent">
                <?php 
                $titles = [
                    'A' => 'Personal Information Documents',
                    'B' => 'Pre-Employment Requirements',
                    'C' => 'Employment Documents',
                    'D' => 'Payroll & Compensation Documents',
                    'E' => 'Attendance & Leave Documents',
                    'F' => 'Training & Development',
                    'G' => 'Performance Management',
                    'H' => 'Disciplinary Records',
                    'I' => 'Benefits & Company Property',
                    'J' => 'Separation / Offboarding Documents'
                ];
                
                foreach ($file_types as $index => $type): 
                    $files_in_category = isset($file_data[$type]) ? $file_data[$type] : [];
                ?>
                <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" 
                     id="pane-<?= $type ?>" 
                     role="tabpanel" 
                     aria-labelledby="tab-<?= $type ?>"
                     tabindex="0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-dark">
                            <i class="fa-solid fa-folder me-2"></i>
                            <?= $type ?>. <?= $titles[$type] ?>
                        </h5>
                        <span class="badge bg-secondary"><?= count($files_in_category) ?> file(s)</span>
                    </div>
                    
                    <?php if (!empty($files_in_category)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered file-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="40%">File Title</th>
                                    <th width="25%">Date Added</th>
                                    <th width="30%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counts = 1; ?>
                                <?php foreach ($files_in_category as $files): ?>
                                <tr>
                                    <td class="text-center"><?= $counts++ ?></td>
                                    <td>
                                        <i class="fa-solid fa-file me-2 text-muted"></i>
                                        <?= htmlspecialchars($files["file_title"]) ?>
                                    </td>
                                    <td>
                                        <i class="fa-solid fa-calendar me-2 text-muted"></i>
                                        <?= date("M d, Y h:i A", strtotime($files["added_at"])) ?>
                                    </td>
                                    <td class="file-actions">
                                        <button class="btn btn-outline-dark btn-sm downloadBtn"
                                            data-file="<?= '../../authentication/uploads/' . $files["201file"] ?>"
                                            title="Download File">
                                            <i class="fa-solid fa-download me-1"></i>Download
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm delete-file"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteFile"
                                            data-id="<?= htmlspecialchars($files["files_id"]) ?>"
                                            title="Delete File">
                                            <i class="fa-solid fa-trash me-1"></i>Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fa-solid fa-folder-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Files Found</h5>
                        <p class="text-muted">No files have been added to this category yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteFile" tabindex="-1" aria-labelledby="deleteFileLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <form id="file-delete-form" class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white">
                    <i class="fa-solid fa-trash me-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fa-solid fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                <h6>Are you sure you want to delete this file?</h6>
                <p class="text-muted small">This action cannot be undone.</p>
                <input type="hidden" name="files_id" id="files_id">
            </div>
            <div class="modal-footer justify-content-center">
                <button type="submit" class="btn btn-danger">
                    <i class="fa-solid fa-trash me-2"></i>Yes, Delete
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-times me-2"></i>Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    console.log("DOM loaded - initializing tabs");
    
    // Set employee ID in modal
    document.getElementById('getID').addEventListener('click', function() {
        const employeeId = this.getAttribute('data-id');
        document.getElementById('employee_id').value = employeeId;
    });

    // Download functionality
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("downloadBtn") || e.target.closest('.downloadBtn')) {
            const button = e.target.classList.contains("downloadBtn") ? e.target : e.target.closest('.downloadBtn');
            const file = button.getAttribute("data-file");
            console.log("Downloading file:", file);
            
            const a = document.createElement("a");
            a.href = file;
            a.download = "";
            a.style.display = "none";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    });

    // Delete file modal setup
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("delete-file") || e.target.closest('.delete-file')) {
            const button = e.target.classList.contains("delete-file") ? e.target : e.target.closest('.delete-file');
            const fileId = button.getAttribute("data-id");
            document.getElementById("files_id").value = fileId;
            console.log("Setting file ID for deletion:", fileId);
        }
    });

    // Form submission handling
    const fileForm = document.getElementById('file-form');
    if (fileForm) {
        fileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('File form submitted');
            // Add your form submission logic here
        });
    }

    const deleteForm = document.getElementById('file-delete-form');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Delete form submitted');
            // Add your delete logic here
        });
    }

    // Debug: Check if all tabs are properly set up
    const tabs = document.querySelectorAll('#fileTabs .nav-link');
    const panes = document.querySelectorAll('.tab-pane');
    
    console.log('Found tabs:', tabs.length);
    console.log('Found panes:', panes.length);
    
    tabs.forEach(tab => {
        console.log('Tab:', tab.id, 'target:', tab.getAttribute('data-bs-target'));
    });
    
    panes.forEach(pane => {
        console.log('Pane:', pane.id, 'display:', window.getComputedStyle(pane).display);
    });

    // Manual tab switching as fallback
    document.querySelectorAll('#fileTabs .nav-link').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs and panes
            document.querySelectorAll('#fileTabs .nav-link').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => {
                p.classList.remove('show', 'active');
                p.style.display = 'none';
            });
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Show corresponding pane
            const targetId = this.getAttribute('data-bs-target');
            const targetPane = document.querySelector(targetId);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
                targetPane.style.display = 'block';
                console.log('Showing pane:', targetId);
            }
        });
    });
});
</script>