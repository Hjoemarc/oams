<section>
    <div class="d-flex justify-content-between align-items-center mb-2 col-md-12 col-12 flex-wrap">
        <div class=" col-md-6 col-12">
            <h4 class=""><i class="fa-solid fa-users me-2"></i>Leave Management</h4>
            <small class="text-muted ">Accept, Reject and Update Employee Leave Request</small>
        </div>
    </div>
    <!-- PENDING ACCOUNT PROFILE -->
    <div class="modal fade" id="viewAccount" tabindex="-1" aria-labelledby="viewAccountLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="viewAccountLabel">Employee Account Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>
    
    <!-- EMPLOYEE COUNTS DISPLAYS ================================================================================================ -->
    <div class="row mb-2">
        <?php
            $official = $pdo->query("SELECT COUNT(*) FROM leavereq WHERE leaveStatus = 'Recommended'")->fetchColumn();
            $pending = $pdo->query("SELECT COUNT(*) FROM leavereq WHERE leaveStatus = 'Recommended'")->fetchColumn();
            $inactive = $pdo->query("SELECT COUNT(*) FROM leavereq WHERE leaveStatus = 'Disapproved'")->fetchColumn();
        ?>
        <!-- <div class="col-md-3">
                <div class="card-header shadow  bg-white text-center p-4 ">
                    <h5 id="totalChildren"><?php echo get_total() ?></h5>
                    <small>Application request</small>
                </div>
            </div> -->
        <div class="col-md-4">
            <div class="card-header shadow bg-white text-center p-4 ">

                <h5 id="pendingEnrollments"><?= $pending ?></h5>
                <small>Recommended Leave</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-header shadow bg-white text-center p-4 ">
                <h5 id="approvedEnrollments"><?= $official ?></h5>
                <small>Approved Leaves</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-header shadow bg-white text-center p-4 ">
                <h5 id="rejectedEnrollments"><?= $inactive ?></h5>
                <small>Disapproved Leaves</small>
            </div>
        </div>
    </div>

    <!-- EMPLOYEE ACCOUNT DISPLAYS =============================================================================================== -->
    <div class="card">
        <!-- NAVIAGATIONS OF TABS -->
        <div class="card-body col-md-12 col-12 d-flex justify-content-between">
            <ul class="nav nav-tabs col-md-7 col-12" id="LeaveRequestTabs">
                <li class="nav-item col-md-4">
                    <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#Pending_Leave"><i
                            class="fa-solid fa-user-tie me-2"></i>Pending Leaves</a>
                </li>
                <li class="nav-item col-md-4">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#Approved_leave"><i
                            class="fa-solid fa-user-plus me-2"></i>Recommend Leaves</a>
                </li>
                <li class="nav-item col-md-4">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#Rejected_Leave"><i
                            class="fa-solid fa-user-minus me-2"></i>Disapproved Leaves</a>
                </li>
            </ul>
            <!-- <div class="col-md-4 col-12">
                <input type="text" id="searchLeaves" placeholder="search by... Employee name, ID and leave_type" class="form-control">
            </div> -->
        </div>
        <!-- CONTENTS -->
        <div class="card-body">
            <div class="tab-content">
                <!-- Pending Leaves -->
                <div class="tab-pane fade show active table-body" id="Pending_Leave" role="tabpanel">
                    <table class="table text-center table-body table-bordered table-sm">
                        <thead class="table-light col-md-12">
                            <tr class="col-md-12">
                                <th>#</th>
                                <th>Complete Name</th>
                                <th>Leave Type</th>
                                <th>From-To</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody style="color: #666;">
                            <?php 
                            $stmt = $pdo->prepare("SELECT 
                                    lr.leave_id,
                                    lr.leaveType,
                                    lr.leaveStatus,
                                    lr.Purpose,
                                    lr.InclusiveFrom,
                                    lr.InclusiveTo,
                                    lr.numberOfDays,
                                    lr.contact,
                                    lr.request_date,
                                    ed.employee_id,
                                    ed.firstname,
                                    ed.middlename,
                                    ed.lastname,
                                    ed.suffix,
                                    hd.employeeID
                                FROM leaveReq lr
                                INNER JOIN employee_data ed ON lr.employee_id = ed.employee_id
                                INNER JOIN hr_data hd ON ed.employee_id = hd.employee_id
                                WHERE lr.leaveStatus = 'Pending'
                                ORDER BY lr.request_date DESC");
                            $stmt->execute();
                            $recommendedLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            $count = 1;
                            foreach($recommendedLeave as $recommended) : ?>
                            <tr>
                                <th><?= $count++ ?></th>
                                <th><?= htmlspecialchars($recommended["firstname"]) . " " . htmlspecialchars(substr($recommended["middlename"], 0, 1)) . ". " . htmlspecialchars($recommended["lastname"]) ?></th>
                                <th><?= htmlspecialchars($recommended["leaveType"]) ?></th>
                                <th><?= htmlspecialchars($recommended["InclusiveFrom"]) . " to " . htmlspecialchars($recommended["InclusiveTo"]) ?></th>
                                <th><?= htmlspecialchars($recommended["leaveStatus"]) ?></th>
                                <th><a href="index.php?page=contents/viewLeave&leave_id=<?= htmlspecialchars($recommended["leave_id"]) ?>" ><button class="btn btn-sm btn-danger px-3 view-leave-details" data-id="${leave.leave_id}">
                                        <i class="fas fa-eye"></i> View
                                    </button></a>
                                </th>
                            </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>

                <!-- Approved Leaves -->
                <div class="tab-pane fade" id="Approved_leave" role="tabpanel">
                    <div class="table-responsive table-body">
                        <table class="table text-center table-body table-bordered table-sm">
                            <thead class="table-light col-md-12">
                                <tr class="col-md-12">
                                    <th>#</th>
                                    <th>Complete Name</th>
                                    <th>Leave Type</th>
                                    <th>From-To</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                                                    <tbody style="color: #666;">
                            <?php 
                            $stmt = $pdo->prepare("SELECT 
                                    lr.leave_id,
                                    lr.leaveType,
                                    lr.leaveStatus,
                                    lr.Purpose,
                                    lr.InclusiveFrom,
                                    lr.InclusiveTo,
                                    lr.numberOfDays,
                                    lr.contact,
                                    lr.request_date,
                                    ed.employee_id,
                                    ed.firstname,
                                    ed.middlename,
                                    ed.lastname,
                                    ed.suffix,
                                    hd.employeeID
                                FROM leaveReq lr
                                INNER JOIN employee_data ed ON lr.employee_id = ed.employee_id
                                INNER JOIN hr_data hd ON ed.employee_id = hd.employee_id
                                WHERE lr.leaveStatus = 'Recommended'
                                ORDER BY lr.request_date DESC");
                            $stmt->execute();
                            $ApprovedLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach($ApprovedLeave as $approved) : ?>
                            <tr>
                                <th><?= $count++ ?></th>
                                <th><?= htmlspecialchars($approved["firstname"]) . " " . htmlspecialchars(substr($approved["middlename"], 0, 1)) . ". " . htmlspecialchars($approved["lastname"]) ?></th>
                                <th><?= htmlspecialchars($approved["leaveType"]) ?></th>
                                <th><?= htmlspecialchars($approved["InclusiveFrom"]) . " to " . htmlspecialchars($approved["InclusiveTo"]) ?></th>
                                <th><?= htmlspecialchars($approved["leaveStatus"]) ?></th>
                                <th><a href="index.php?page=contents/reviewLeave&leave_id=<?= htmlspecialchars($approved["leave_id"]) ?>" ><button class="btn btn-sm btn-danger px-3 view-leave-details" data-id="${leave.leave_id}">
                                        <i class="fas fa-eye"></i> Review Leave
                                    </button></a>
                                </th>
                            </tr>
                            <?php endforeach ?>
                        </tbody>
                        </table>
                    </div>
                </div>



                <!-- Rejected Leaves -->
                <div class="tab-pane fade" id="Rejected_Leave" role="tabpanel">
                    <table class="table text-center table-body table-bordered table-sm">
                        <thead class="table-light col-md-12">
                            <tr class="col-md-12">
                                <th>#</th>
                                <th>Complete Name</th>
                                <th>Leave Type</th>
                                <th>From-To</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                         <tbody style="color: #666;">
                            <?php 
                            $stmt = $pdo->prepare("SELECT 
                                    lr.leave_id,
                                    lr.leaveType,
                                    lr.leaveStatus,
                                    lr.Purpose,
                                    lr.InclusiveFrom,
                                    lr.InclusiveTo,
                                    lr.numberOfDays,
                                    lr.contact,
                                    lr.request_date,
                                    ed.employee_id,
                                    ed.firstname,
                                    ed.middlename,
                                    ed.lastname,
                                    ed.suffix,
                                    hd.employeeID
                                FROM leaveReq lr
                                INNER JOIN employee_data ed ON lr.employee_id = ed.employee_id
                                INNER JOIN hr_data hd ON ed.employee_id = hd.employee_id
                                WHERE lr.leaveStatus = 'Disapproved'
                                ORDER BY lr.request_date DESC");
                            $stmt->execute();
                            $disapprovedLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            $count = 1;
                            foreach($disapprovedLeave as $disapproved) : ?>
                            <tr>
                                <th><?= $count++ ?></th>
                                <th><?= htmlspecialchars($disapproved["firstname"]) . " " . htmlspecialchars(substr($disapproved["middlename"], 0, 1)) . ". " . htmlspecialchars($disapproved["lastname"]) ?></th>
                                <th><?= htmlspecialchars($disapproved["leaveType"]) ?></th>
                                <th><?= htmlspecialchars($disapproved["InclusiveFrom"]) . " to " . htmlspecialchars($disapproved["InclusiveTo"]) ?></th>
                                <th><?= htmlspecialchars($disapproved["leaveStatus"]) ?></th>
                                <th><a href="index.php?page=contents/reviewLeave&leave_id=<?= htmlspecialchars($disapproved["leave_id"]) ?>" ><button class="btn btn-sm btn-danger px-3 view-leave-details" data-id="${leave.leave_id}">
                                        <i class="fas fa-eye"></i> Review Leave
                                    </button></a>
                                </th>
                            </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchLeaves');
    
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase().trim();
        
        // Reset if search is empty
        if (searchTerm === '') {
            resetSearch();
            return;
        }
        
        // Search and highlight in all three tables
        searchAndHighlightTable('Leave_Pending', searchTerm);
        searchAndHighlightTable('official_LeaveRquest', searchTerm);
        searchAndHighlightTable('Reject_LeaveRequest', searchTerm);
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
    
    function resetSearch() {
        const tables = ['Leave_Pending', 'official_LeaveRquest', 'Reject_LeaveRequest'];
        tables.forEach(tableId => {
            const table = document.getElementById(tableId);
            if (!table) return;
            
            const rows = table.getElementsByTagName('tr');
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                resetRowHighlights(row);
                row.style.display = '';
            }
            hideNoResultsMessage(tableId);
        });
    }
    
    function showNoResultsMessage(tableId, hasVisibleRows) {
        let noResultsRow = document.getElementById(`no-results-${tableId}`);
        
        if (!hasVisibleRows && !noResultsRow) {
            const table = document.getElementById(tableId);
            noResultsRow = document.createElement('tr');
            noResultsRow.id = `no-results-${tableId}`;
            noResultsRow.innerHTML = `<td colspan="6" class="text-center text-muted py-3">No leave requests found matching your search</td>`;
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
});
</script>