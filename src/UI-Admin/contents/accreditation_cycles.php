<?php
// Fetch Cycles
   $stmtCycles = $pdo->prepare("
    SELECT ac.cycle_id, ac.cycle_name, ac.academic_year, ac.status, ac.deadline, ac.created_at, p.program_name, p.program_id
    FROM accreditation_cycles ac
    INNER JOIN programs p ON ac.program_id = p.program_id
    ORDER BY ac.created_at DESC
");
$stmtCycles->execute();
$cycles = $stmtCycles->fetchAll(PDO::FETCH_ASSOC);


    $counts = 1;
?>
<section>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="mx-2">
            <h4 class=""><span class=""><i class="fa-solid fa-calendar-alt me-2"></i></span>Accreditation Cycles
            </h4>
            <small class="text-muted ">Manage cycles</small>
        </div>
    </div>
    <!-- Cycles -->
    <!-- CYCLES ADD AND SEARCH -->
    <div class="col-md-12 col-12 d-flex justify-content-between mb-3">
        <div class="col-md-4 col-12">
            <input type="text" placeholder="Search by cycle name or academic year" id="searchCycles"
                class="form-control">
        </div>
        <div class="col-md-3 d-flex justify-content-end">
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addCycleModal"
                id="add_new">
                <i class="fa fa-plus"></i> Add New Cycle
            </button>
        </div>
    </div>
    <!-- ADD Cycle Modal -->
    <div class="modal fade" id="addCycleModal" tabindex="-1" aria-labelledby="addCycleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="addCycleModalLabel">Add New Cycle</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                        onclick="location.reload()"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="cycle-form">
                        <div class="col-md-6">
                            <label class="form-label">Cycle Name</label>
                            <input type="text" class="form-control" name="cycle_name" required
                                placeholder="e.g., Cycle 2024, Accreditation Round 1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Academic Year</label>
                            <input type="text" class="form-control" name="academic_year" required
                                placeholder="e.g., 2024-2025">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Programs</label>
                            <select name="program_id" class="form-select" required>
                                <option value="">Select Program</option>
                                <?php 
                                    $stmt = $pdo->prepare("SELECT * FROM programs");
                                    $stmt->execute();
                                    $getPrograms = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach($getPrograms as $prog) :
                                ?>
                                    <option value="<?= $prog["program_id"] ?>">
                                        <?= $prog["program_name"] . '(' . $prog["program_code"] . ')' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="">Select Status</option>
                                <option value="upcoming">Upcoming</option>
                                <option value="active">Active</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Deadline</label>
                            <input type="date" class="form-control" name="deadline">
                        </div>
                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-danger px-5">
                                <i class="bi bi-plus-circle me-1"></i> Add Cycle
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Cycle Modal -->
    <div class="modal fade" id="editCycleModal" tabindex="-1" aria-labelledby="editCycleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="edit-cycle-form" class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="editCycleModalLabel">Edit Accreditation Cycle
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="cycle_id" id="editCycleId">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Cycle Name</label>
                            <input type="text" class="form-control" id="editCycleName" name="cycle_name" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Programs</label>
                            <select name="program_id" id="editProgramId" class="form-select" required>
                                <option value="">Select Program</option>
                                <?php 
                                    $stmt = $pdo->prepare("SELECT * FROM programs");
                                    $stmt->execute();
                                    $getPrograms = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach($getPrograms as $prog) :
                                ?>
                                    <option value="<?= $prog["program_id"] ?>">
                                        <?= $prog["program_name"] . '(' . $prog["program_code"] . ')' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Academic Year</label>
                            <input type="text" class="form-control" id="editAcademicYear" name="academic_year"
                                placeholder="YYYY-YYYY" required>
                        </div>

                        <div class="col-md-6 d-none">
                            <label class="form-label">Status</label>
                            <select name="status" id="editCycleStatus" class="form-select" required>
                                <option value="upcoming">Upcoming</option>
                                <option value="active">Active</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Deadline (Optional)</label>
                            <input type="date" class="form-control" id="editDeadline" name="deadline">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Cycle</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Delete Cycle Modal -->
    <div class="modal fade" id="deleteCycleModal" tabindex="-1" aria-labelledby="deleteCycleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="delete-cycle-form" class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteCycleModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="deleteCycleMessage">
                        Are you sure you want to delete this accreditation cycle? This action cannot be
                        undone.
                    </div>
                    <input type="hidden" name="cycle_id" id="deleteCycleId">
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- CYCLES TABLE -->
    <div class="table-responsive">
        <table class="table table-sm table-hover text-center" style="font-size: 0.875rem;">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Cycle Name</th>
                    <th>Program</th>
                    <th>Academic Year</th>
                    <th>Status</th>
                    <th>Deadline</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="text-center" id="cyclesTable" style="color: #666;">
                <?php 
                                $cycleCount = 1;
                                foreach($cycles as $cycle) : 
                                    $statusClass = '';
                                    switch($cycle['status']) {
                                        case 'active': $statusClass = 'badge bg-success'; break;
                                        case 'upcoming': $statusClass = 'badge bg-info'; break;
                                        case 'closed': $statusClass = 'badge bg-danger'; break;
                                    }
                                ?>
                <tr>
                    <td><?= $cycleCount++ ?></td>
                    <td><?= htmlspecialchars($cycle["cycle_name"]) ?></td>
                    <td><?= htmlspecialchars($cycle["program_name"]) ?></td>
                    <td><?= htmlspecialchars($cycle["academic_year"]) ?></td>
                    <td><span class="<?= $statusClass ?>"><?= ucfirst($cycle["status"]) ?></span></td>
                    <td><?= htmlspecialchars($cycle["deadline"] ?? 'N/A') ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary edit-cycle-btn"
                            data-cycle-id="<?= $cycle['cycle_id'] ?>"
                            data-cycle-name="<?= htmlspecialchars($cycle['cycle_name']) ?>"
                            data-academic-year="<?= htmlspecialchars($cycle['academic_year']) ?>"
                            data-status="<?= htmlspecialchars($cycle['status']) ?>"
                            data-program_id="<?= $cycle['program_id'] ?>"
                            data-deadline="<?= $cycle['deadline'] ? htmlspecialchars($cycle['deadline']) : '' ?>">
                            <i class="fas fa-edit"></i> Edit
                        </button>

                        <button type="button" class="btn btn-sm btn-danger delete-cycle-btn"
                            data-cycle-id="<?= $cycle['cycle_id'] ?>"
                            data-cycle-name="<?= htmlspecialchars($cycle['cycle_name']) ?>" data-bs-toggle="modal"
                            data-bs-target="#deleteCycleModal">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</section>
<script>
// Cycle Edit and Delete Functions
function editCycle(cycleId, cycleName, academicYear, status, program_id, deadline) {
    document.getElementById('editCycleId').value = cycleId;
    document.getElementById('editCycleName').value = cycleName;
    document.getElementById('editAcademicYear').value = academicYear;
    document.getElementById('editCycleStatus').value = status;
    document.getElementById('editProgramId').value = program_id;
    document.getElementById('editDeadline').value = deadline;

    var modal = new bootstrap.Modal(document.getElementById('editCycleModal'));
    modal.show();
}

function setDeleteCycleId(cycleId, cycleName) {
    document.getElementById('deleteCycleId').value = cycleId;

    // Update just the text, not the entire modal body
    const messageElement = document.getElementById('deleteCycleMessage');
    if (messageElement) {
        messageElement.innerHTML =
            `Are you sure you want to delete the accreditation cycle "<strong>${cycleName}</strong>"? `
    }
}
// Event listeners for cycle buttons (using data attributes)
document.addEventListener('DOMContentLoaded', function() {
    // Edit cycle buttons
    document.querySelectorAll('.edit-cycle-btn').forEach(button => {
        button.addEventListener('click', function() {
            const cycleId = this.getAttribute('data-cycle-id');
            const cycleName = this.getAttribute('data-cycle-name');
            const academicYear = this.getAttribute('data-academic-year');
            const status = this.getAttribute('data-status');
            const program_id = this.getAttribute('data-program_id');
            const deadline = this.getAttribute('data-deadline');

            editCycle(cycleId, cycleName, academicYear, status, program_id, deadline);
        });
    });

    // Delete cycle buttons
    document.querySelectorAll('.delete-cycle-btn').forEach(button => {
        button.addEventListener('click', function() {
            const cycleId = this.getAttribute('data-cycle-id');
            const cycleName = this.getAttribute('data-cycle-name');
            setDeleteCycleId(cycleId, cycleName);
        });
    });
});
// Search functionality for Areas
document.addEventListener('DOMContentLoaded', function() {
    const searchAreas = document.getElementById('searchAreas');

    if (searchAreas) {
        searchAreas.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            searchTable('areasTable', searchTerm);
        });
    }

    // Add similar search functionality for other tables
    const searchIndicators = document.getElementById('searchIndicators');
    if (searchIndicators) {
        searchIndicators.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            searchTable('indicatorsTable', searchTerm);
        });
    }

    const searchCriteria = document.getElementById('searchCriteria');
    if (searchCriteria) {
        searchCriteria.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            searchTable('criteriaTable', searchTerm);
        });
    }

    const searchCycles = document.getElementById('searchCycles');
    if (searchCycles) {
        searchCycles.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            searchTable('cyclesTable', searchTerm);
        });
    }

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
                cell.innerHTML = cell.textContent;
            }
        }
    }

    function showNoResultsMessage(tableId, hasVisibleRows) {
        let noResultsRow = document.getElementById(`no-results-${tableId}`);

        if (!hasVisibleRows && !noResultsRow) {
            const table = document.getElementById(tableId);
            noResultsRow = document.createElement('tr');
            noResultsRow.id = `no-results-${tableId}`;
            noResultsRow.innerHTML =
                `<td colspan="10" class="text-center text-muted py-3">No records found matching your search</td>`;
            table.appendChild(noResultsRow);
        } else if (hasVisibleRows && noResultsRow) {
            noResultsRow.remove();
        }
    }
});

// Add CSS for highlighting
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