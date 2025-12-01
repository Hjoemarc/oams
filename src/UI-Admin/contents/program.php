<?php
    $stmtDepartments = $pdo->prepare("SELECT *
                FROM departments
                ORDER BY department_name ASC");
    $stmtDepartments->execute();
    $Departments = $stmtDepartments->fetchAll(PDO::FETCH_ASSOC);

    $stmtPrograms = $pdo->prepare("SELECT 
                p.program_id,
                p.program_code,
                p.program_name,
                p.department_id, 
                d.department_name, 
                d.department_code
            FROM programs AS p
            LEFT JOIN departments AS d ON p.department_id = d.department_id
            ORDER BY p.program_name ASC;");
    $stmtPrograms->execute();
    $programsData = $stmtPrograms->fetchAll(PDO::FETCH_ASSOC);

    $counts = 1;
?>
<section>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="mx-2">
            <h4 class=""><i class="fa-solid fa-building-user me-2"></i>Departments & Programs Management</h4>
            <small class="text-muted ">Manage departments and Programs</small>
        </div>
    </div>

    <div class="row mb-2">
        <!-- <div class="col-md-3">
                <div class="card-header shadow  bg-white text-center p-4 ">
                    <h5 id="totalChildren"><?php echo get_total() ?></h5>
                    <small>Application request</small>
                </div>
            </div> -->
        <?php
            $departments = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
            $jobtitles = $pdo->query("SELECT COUNT(*) FROM programs")->fetchColumn();
        ?>
        <div class="col-md-6">
            <div class="card-header shadow bg-white text-center p-4 ">

                <h5 id="pendingEnrollments"><?= $departments ?></h5>
                <small>Departments</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-header shadow bg-white text-center p-4 ">
                <h5 id="approvedEnrollments"><?= $jobtitles ?></h5>
                <small>PROGRAMS</small>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body col-md-12 col-12 d-flex justify-content-between">
            <ul class="nav nav-tabs col-md-12 col-12" id="DepartmentsJobsInfoTab">
                <li class="nav-item col-md-2">
                    <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#departmentsInfo"><i
                            class="fa-solid me-2 fa-building"></i>Departments</a>
                </li>
                <li class="nav-item col-md-2">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#JobTitlesInfno"><i
                            class="fa-solid me-2 fa-user-doctor"></i>Programs</a>
                </li>
            </ul>
        </div>
        <div class="card-body pt-0">
            <div class="tab-content">
                <!-- departments -->
                <div class="tab-pane fade show active" id="departmentsInfo" role="tabpanel">
                    <!-- DEPARTMENTS ADD AND SEARCH -->
                    <div class="col-md-12 col-12 d-flex justify-content-between">
                        <div class="col-md-4 col-12">
                            <input type="text" placeholder="search by... Department name and department code"
                                id="searchDepartments" class="form-control">
                        </div>
                        <div class="col-md-3 d-flex justify-content-end">
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                data-bs-target="#addDepartments" id="add_new">
                                <i class="fa fa-plus"></i> Add new Departments
                            </button>
                        </div>
                    </div>
                    <!-- ADD departments -->
                    <div class="modal fade" id="addDepartments" tabindex="-1" aria-labelledby="addDepartmentsLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-md">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title text-white" id="createAccountsLabel">Add New Departments
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close" onclick="location.reload()"></button>
                                </div>
                                <div class="modal-body">
                                    <form class="row g-3" id="department-form">
                                        <div class="m-2">
                                            <label class="form-label">Department name</label>
                                            <input type="text" class="form-control" name="department_name">
                                        </div>
                                        <div class="m-2">
                                            <label class="form-label">Department code</label>
                                            <input type="text" class="form-control" name="department_code">
                                        </div>
                                        <div class="col-12 text-center mt-3">
                                            <button type="submit" class="btn btn-danger px-5">
                                                <i class="bi bi-person-plus-fill me-1"></i> Add Department
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- edit departments -->
                    <div class="modal fade" id="editdepartmentsModal" tabindex="-1"
                        aria-labelledby="editdepartmentsModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form id="edit-department">
                                <div class="modal-content">
                                    <div class="modal-header bg-gradient-primary text-white">
                                        <h5 class="modal-title text-white" id="editdepartmentsModalLabel">Edit
                                            Department
                                            Information</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <input type="hidden" name="department_id" id="editDepartmentId">

                                        <div class="mb-3">
                                            <label for="editDepartmentName" class="form-label">Department Name</label>
                                            <input type="text" class="form-control" id="editDepartmentName" value=""
                                                name="department_name">
                                        </div>

                                        <div class="mb-3">
                                            <label for="editDepartmentCode" class="form-label">Department Code</label>
                                            <input type="text" class="form-control" id="editDepartmentCode" value=""
                                                name="department_code">
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-danger">Update Department</button>
                                        <button type="button" class="btn btn-dark"
                                            data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- delete Departments -->
                    <div class="modal fade" id="deleteDepartmentModal" tabindex="-1"
                        aria-labelledby="deleteJobModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form id="delete-department" class="modal-content">
                                <div class="modal-header bg-gradient-primary text-white">
                                    <h5 class="modal-title text-white" id="deleteJobModalLabel">Confirmation Delete</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete this Department?
                                    <input type="hidden" name="department_id" id="deleteDepartmentId">
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- DEPARTMENTS TABLE -->
                    <div class="table-responsive">
                        <table class="table table-sm table-hover text-center" style="font-size: 0.875rem;">
                            <thead class="table-light">
                                <tr style="color: #555;">
                                    <th>#</th>
                                    <th>Department Name</th>
                                    <th>Department Code</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-center" id="departments" style="color: #666;">
                                <?php foreach($Departments as $dept) : ?>
                                <tr>
                                    <th><?= $counts++ ?></th>
                                    <th><?= htmlspecialchars($dept["department_name"]) ?></th>
                                    <th><?= htmlspecialchars($dept["department_code"]) ?></th>
                                    <th>
                                        <button type="button" class="btn px-3 btn-sm btn-danger" onclick="edit_department(
                                                <?= $dept['department_id'] ?>,
                                                '<?= addslashes($dept['department_name']) ?>',
                                                '<?= addslashes($dept['department_code']) ?>'
                                            )">
                                            <i class="fas fa-eye"></i> Edit
                                        </button>

                                        <button type="button" class="btn px-3 btn-sm btn-dark" data-bs-toggle="modal"
                                            data-bs-target="#deleteDepartmentModal"
                                            onclick="setDeleteDepartmentId(<?= $dept['department_id'] ?>)">
                                            <i class="fas fa-eye"></i> Delete
                                        </button>
                                    </th>
                                </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>

                    </div>
                </div>
                <!-- Job Titles -->
                <div class="tab-pane fade" id="JobTitlesInfno" role="tabpanel">
                    <!-- PROGRAMS ADD AND SEARCH -->
                    <div class="col-md-12 col-12 d-flex justify-content-between">
                        <div class="col-md-4 col-12">
                            <input type="text" placeholder="search by... job title" id="searchJobtitles"
                                class="form-control">
                        </div>
                        <div class="col-md-3 d-flex justify-content-end">
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                data-bs-target="#addJobTitles" id="add_new">
                                <i class="fa fa-plus"></i> Add Programs
                            </button>
                        </div>
                    </div>
                    <!-- ADD programs  -->
                    <div class="modal fade" id="addJobTitles" tabindex="-1" aria-labelledby="addJobTitlesLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-md">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title text-white" id="createAccountsLabel">Add New Job Titles
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close" onclick="location.reload()"></button>
                                </div>
                                <div class="modal-body">
                                    <form class="row g-3" id="programs-form">
                                        <div class="m-2">
                                            <label class="form-label">Program name</label>
                                            <input type="text" class="form-control" name="program_name">
                                        </div>
                                        <div class="m-2">
                                            <label class="form-label">Program code</label>
                                            <input type="text" class="form-control" name="program_code">
                                        </div>
                                        <div class="m-2">
                                            <label class="form-label">Department</label>
                                            <select name="department_id" id="" class="form-select">
                                                <option value="">Select Departments</option>
                                                <?php foreach($Departments as $dept) : ?>
                                                    <option value="<?= htmlspecialchars($dept["department_id"]) ?>">
                                                        <?= htmlspecialchars($dept["department_name"]) . '(' . htmlspecialchars($dept["department_code"]) . ')' ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12 text-center mt-3">
                                            <button type="submit" class="btn btn-danger px-5">
                                                <i class="bi bi-person-plus-fill me-1"></i> Add Job Title
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- edit programs -->
                    <div class="modal fade" id="editJobTitlesModal" tabindex="-1"
                        aria-labelledby="editJobTitlesModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form id="update-programs">
                                <div class="modal-content">
                                    <div class="modal-header bg-gradient-primary text-white">
                                        <h5 class="modal-title text-white" id="editJobTitlesModalLabel">Edit Job
                                            Information</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <input type="hidden" name="program_id" id="editProgram_id">

                                        <div class="mb-3">
                                            <label for="jobTitle" class="form-label">Job Title</label>
                                            <input type="text" class="form-control" id="program_name" value=""
                                                name="program_name">
                                        </div>
                                        <div class="mb-3">
                                            <label for="salary" class="form-label">Official Salary</label>
                                            <input type="text" class="form-control" id="program_code" value="" name="program_code">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Department</label>
                                            <select name="department_id" id="department_ids" class="form-select">
                                                <option value="">Select Departments</option>
                                                <?php foreach($Departments as $dept) : ?>
                                                    <option value="<?= htmlspecialchars($dept["department_id"]) ?>">
                                                        <?= htmlspecialchars($dept["department_name"]) . '(' . htmlspecialchars($dept["department_code"]) . ')' ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-danger">Update Job Title</button>
                                        <button type="button" class="btn btn-dark"
                                            data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- programs JOtTitles -->
                    <div class="modal fade" id="deleteJobModal" tabindex="-1" aria-labelledby="deleteJobModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <form id="delete-program" class="modal-content">
                                <div class="modal-header bg-gradient-primary text-white">
                                    <h5 class="modal-title text-white" id="deleteJobModalLabel">Confirmation Delete</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete this program?
                                    <input type="hidden" name="program_id" id="deleteJobTitleId">
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- PROGRAMS TABLE -->
                    <div class="table-responsive">
                        <table class="table table-sm table-hover text-center" style="font-size: 0.875rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Program name</th>
                                    <th>Program code</th>
                                    <th>Department</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                           <tbody class="text-center" id="jobtitles" style="color: #666;">
                                <?php foreach($programsData as $job) : ?>
                                    <tr>
                                        <th><?= $counts++ ?></th>
                                        <th><?= htmlspecialchars($job["program_name"]) ?></th>
                                        <th><?= htmlspecialchars($job["program_code"]) ?></th>
                                        <th><?= htmlspecialchars($job["department_code"]) ?></th>
                                        <th>
                                            <button type="button" class="btn px-3 btn-sm btn-danger" 
                                                onclick="edit_jobTitle(
                                                    <?= $job['program_id'] ?>,
                                                    '<?= addslashes($job['program_name']) ?>',
                                                    '<?= addslashes($job['program_code']) ?>',
                                                    '<?= $job['department_id'] ?>'
                                                )">
                                                <i class="fas fa-eye"></i> Edit
                                            </button>

                                            <button type="button" class="btn px-3 btn-sm btn-dark" 
                                                data-bs-toggle="modal" data-bs-target="#deleteJobModal" 
                                                onclick="setDeletejobtTitleId(<?= $job['program_id'] ?>)">
                                                <i class="fas fa-eye"></i> Delete
                                            </button>
                                        </th>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>
<script>
//Search functionality for Departments
document.addEventListener('DOMContentLoaded', function() {
    const searchDepartments = document.getElementById('searchDepartments');

    searchDepartments.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase().trim();

        // Reset if search is empty
        if (searchTerm === '') {
            resetDepartmentsSearch();
            return;
        }

        // Search and highlight in departments table
        searchAndHighlightTable('departments', searchTerm);
    });

    function searchAndHighlightTable(tableId, searchTerm) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const rows = table.getElementsByTagName('tr');
        let hasVisibleRows = false;

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            if (cells.length === 0) continue;

            // Reset cell contents first
            resetRowHighlights(row);

            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];

                // Skip action cells
                if (cell.querySelector('button') || cell.querySelector('a')) {
                    continue;
                }

                const originalText = cell.textContent;
                const lowerText = originalText.toLowerCase();

                if (lowerText.includes(searchTerm)) {
                    found = true;
                    hasVisibleRows = true;

                    // Highlight the matched text
                    const highlightedText = originalText.replace(
                        new RegExp(searchTerm, 'gi'),
                        match => `<span class="search-highlight">${match}</span>`
                    );
                    cell.innerHTML = highlightedText;
                }
            }

            row.style.display = found ? '' : 'none';
        }

        // Show "no results" message if needed
        showNoResultsMessage(tableId, hasVisibleRows);
    }

    function resetRowHighlights(row) {
        const cells = row.getElementsByTagName('td');
        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (cell.querySelector('.search-highlight')) {
                cell.innerHTML = cell.textContent; // Remove HTML tags, keep text
            }
        }
    }

    function resetDepartmentsSearch() {
        const table = document.getElementById('departments');
        if (!table) return;

        const rows = table.getElementsByTagName('tr');
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            resetRowHighlights(row);
            row.style.display = '';
        }
        hideNoResultsMessage('departments');
    }

    function showNoResultsMessage(tableId, hasVisibleRows) {
        let noResultsRow = document.getElementById(`no-results-${tableId}`);

        if (!hasVisibleRows && !noResultsRow) {
            const table = document.getElementById(tableId);
            noResultsRow = document.createElement('tr');
            noResultsRow.id = `no-results-${tableId}`;
            noResultsRow.innerHTML =
                `<td colspan="5" class="text-center text-muted py-3">No departments found matching your search</td>`;
            table.appendChild(noResultsRow);
        } else if (hasVisibleRows && noResultsRow) {
            noResultsRow.remove();
        }
    }

    function hideNoResultsMessage(tableId) {
        const noResultsRow = document.getElementById(`no-results-${tableId}`);
        if (noResultsRow) {
            noResultsRow.remove();
        }
    }
});

// Search functionality for Job Titles
document.addEventListener('DOMContentLoaded', function() {
    const searchJobtitles = document.getElementById('searchJobtitles');

    searchJobtitles.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase().trim();

        // Reset if search is empty
        if (searchTerm === '') {
            resetJobTitlesSearch();
            return;
        }

        // Search and highlight in job titles table
        searchAndHighlightTable('jobTitlesData', searchTerm);
    });

    function searchAndHighlightTable(tableId, searchTerm) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const rows = table.getElementsByTagName('tr');
        let hasVisibleRows = false;

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            if (cells.length === 0) continue;

            // Reset cell contents first
            resetRowHighlights(row);

            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];

                // Skip action cells
                if (cell.querySelector('button') || cell.querySelector('a')) {
                    continue;
                }

                const originalText = cell.textContent;
                const lowerText = originalText.toLowerCase();

                if (lowerText.includes(searchTerm)) {
                    found = true;
                    hasVisibleRows = true;

                    // Highlight the matched text
                    const highlightedText = originalText.replace(
                        new RegExp(searchTerm, 'gi'),
                        match => `<span class="search-highlight">${match}</span>`
                    );
                    cell.innerHTML = highlightedText;
                }
            }

            row.style.display = found ? '' : 'none';
        }

        // Show "no results" message if needed
        showNoResultsMessage(tableId, hasVisibleRows);
    }

    function resetRowHighlights(row) {
        const cells = row.getElementsByTagName('td');
        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (cell.querySelector('.search-highlight')) {
                cell.innerHTML = cell.textContent; // Remove HTML tags, keep text
            }
        }
    }

    function resetJobTitlesSearch() {
        const table = document.getElementById('jobTitlesData');
        if (!table) return;

        const rows = table.getElementsByTagName('tr');
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            resetRowHighlights(row);
            row.style.display = '';
        }
        hideNoResultsMessage('jobTitlesData');
    }

    function showNoResultsMessage(tableId, hasVisibleRows) {
        let noResultsRow = document.getElementById(`no-results-${tableId}`);

        if (!hasVisibleRows && !noResultsRow) {
            const table = document.getElementById(tableId);
            noResultsRow = document.createElement('tr');
            noResultsRow.id = `no-results-${tableId}`;
            noResultsRow.innerHTML =
                `<td colspan="5" class="text-center text-muted py-3">No job titles found matching your search</td>`;
            table.appendChild(noResultsRow);
        } else if (hasVisibleRows && noResultsRow) {
            noResultsRow.remove();
        }
    }

    function hideNoResultsMessage(tableId) {
        const noResultsRow = document.getElementById(`no-results-${tableId}`);
        if (noResultsRow) {
            noResultsRow.remove();
        }
    }
});

// Search functionality for Career Paths
document.addEventListener('DOMContentLoaded', function() {
    const searchCareerPaths = document.getElementById('searchCareerPaths');

    searchCareerPaths.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase().trim();

        // Reset if search is empty
        if (searchTerm === '') {
            resetCareerPathsSearch();
            return;
        }

        // Search and highlight in career paths table
        searchAndHighlightTable('careerPathEmployees', searchTerm);
    });

    function searchAndHighlightTable(tableId, searchTerm) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const rows = table.getElementsByTagName('tr');
        let hasVisibleRows = false;

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            if (cells.length === 0) continue;

            // Reset cell contents first
            resetRowHighlights(row);

            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];

                // Skip action cells
                if (cell.querySelector('button') || cell.querySelector('a')) {
                    continue;
                }

                const originalText = cell.textContent;
                const lowerText = originalText.toLowerCase();

                if (lowerText.includes(searchTerm)) {
                    found = true;
                    hasVisibleRows = true;

                    // Highlight the matched text
                    const highlightedText = originalText.replace(
                        new RegExp(searchTerm, 'gi'),
                        match => `<span class="search-highlight">${match}</span>`
                    );
                    cell.innerHTML = highlightedText;
                }
            }

            row.style.display = found ? '' : 'none';
        }

        // Show "no results" message if needed
        showNoResultsMessage(tableId, hasVisibleRows);
    }

    function resetRowHighlights(row) {
        const cells = row.getElementsByTagName('td');
        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (cell.querySelector('.search-highlight')) {
                cell.innerHTML = cell.textContent; // Remove HTML tags, keep text
            }
        }
    }

    function resetCareerPathsSearch() {
        const table = document.getElementById('careerPathEmployees');
        if (!table) return;

        const rows = table.getElementsByTagName('tr');
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            resetRowHighlights(row);
            row.style.display = '';
        }
        hideNoResultsMessage('careerPathEmployees');
    }

    function showNoResultsMessage(tableId, hasVisibleRows) {
        let noResultsRow = document.getElementById(`no-results-${tableId}`);

        if (!hasVisibleRows && !noResultsRow) {
            const table = document.getElementById(tableId);
            noResultsRow = document.createElement('tr');
            noResultsRow.id = `no-results-${tableId}`;
            noResultsRow.innerHTML =
                `<td colspan="7" class="text-center text-muted py-3">No employees found matching your search</td>`;
            table.appendChild(noResultsRow);
        } else if (hasVisibleRows && noResultsRow) {
            noResultsRow.remove();
        }
    }

    function hideNoResultsMessage(tableId) {
        const noResultsRow = document.getElementById(`no-results-${tableId}`);
        if (noResultsRow) {
            noResultsRow.remove();
        }
    }
});

// Add CSS for highlighting (only once)
if (!document.querySelector('#search-highlight-style')) {
    const style = document.createElement('style');
    style.id = 'search-highlight-style';
    style.textContent = `
        .search-highlight {
            background-color: #ffeb3b;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
            color: #333;
        }
    `;
    document.head.appendChild(style);
}
</script>