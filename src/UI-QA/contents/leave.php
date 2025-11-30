<section>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="mx-2 col-md-7">
            <h4><i class="fa-solid fa-person-through-window me-2"></i>Leave Management</h4>
            <small class="text-muted">View all leave request and history</small>
        </div>
        <div class="col-md-2 d-flex justify-content-end">
            <button class="btn btn-danger m-0" data-bs-toggle="modal" data-bs-target="#create_leave">Request
                Leave</button>
        </div>
    </div>
    <!-- create leave modal -->
    <div class="modal fade" id="create_leave" tabindex="-1" aria-labelledby="create_leaveLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="create_leaveLabel">Request a leave</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3" id="leave-form">
                        <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee_id) ?>">
                        <input type="hidden" name="leaveStatus" value="Pending">
                        <h5 class="w-100 text-center m-0">ZAMBOANGA PUERICULTURE CENTER ORG. NO.144 INC.</h5>
                        <strong class="w-100 text-center m-0">APPLICATION FOR LEAVE</strong>
                        <?php 
                            date_default_timezone_set('Asia/Manila');
                            $date = date('Y-m-d');
                       ?>
                        <input type="hidden" name="leaveDate" value="<?= $date ?>" class="form-control">

                        <div class="col-md-2">
                            <label class="form-label">LEAVE APPLIED FOR <span class="text-danger">(required)</span></label>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <input type="radio" name="leaveType" value="Vacation_leave" required> 
                            <span class="d-flex align-items-center ms-2">Vacation Leave</span>
                        </div>
                        <div class="col-md-2 d-flex align-items-center">
                            <input type="radio" name="leaveType" value="Sick_leave" required> 
                            <span class="d-flex align-items-center ms-2">Sick Leave</span>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <input type="radio" name="leaveType" value="Special_leave" required> 
                            <span class="d-flex align-items-center ms-2">Special Leave</span>
                        </div>
                        <div class="col-md-2 d-flex align-items-center">
                            <input type="radio" name="leaveType" value="Others" id="leaveTypeOthers" required>
                            <span class="d-flex align-items-center ms-2">Others</span>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Others Specify</label>
                            <input type="text" name="Others" class="form-control" id="othersInput" disabled>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">COURSE/PURPOSE <span class="text-danger">(required)</span></label>
                            <input type="text" required name="Purpose" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">INCLUSIVE DATE FROM: <span class="text-danger">(required)</span></label>
                            <input type="date" required name="InclusiveFrom" class="form-control" id="InclusiveFrom">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">INCLUSIVE DATE TO: <span class="text-danger">(required)</span></label>
                            <input type="date" required name="InclusiveTo" class="form-control" id="InclusiveTo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">NO. OF DAYS <span class="text-danger">(required)</span></label>
                            <input type="number" required name="numberOfDays" class="form-control" id="numberOfDays" readonly>
                            <div id="daysError" class="text-danger small mt-1"></div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">CONTACT NO. WHILE ON LEAVE <span class="text-danger">(required)</span></label>
                            <input type="text" name="contact" required class="form-control">
                        </div>
                        <p class="text-start w-100 text-dark">I hereby pledge to report for work immediately the
                            following day after expiration of my approved leave of absence unless
                            otherwise duly extended. My failure to do so shall subject me to disciplinary action</p>
                       
                        <div class="col-md-6">
                            <label class="form-label">Section Head<span class="text-dark"> (optional)</span></label>
                            <input type="text" name="sectionHead" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department Head<span class="text-dark"> (optional)</span></label>
                            <input type="text" name="departmentHead" class="form-control">
                        </div>
                        <!-- Form Submission -->
                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="bi bi-person-plus-fill me-1"></i> Submit request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <?php
            $official = $pdo->query("SELECT COUNT(*) FROM leaveReq WHERE leaveStatus = 'Approved' AND employee_id = '$employee_id'")->fetchColumn();
            $pending = $pdo->query("SELECT COUNT(*) FROM leaveReq WHERE leaveStatus = 'Pending' AND employee_id = '$employee_id'")->fetchColumn();
            $recommended = $pdo->query("SELECT COUNT(*) FROM leaveReq WHERE leaveStatus = 'Recommended' AND employee_id = '$employee_id'")->fetchColumn();
            $inactive = $pdo->query("SELECT COUNT(*) FROM leaveReq WHERE leaveStatus = 'Disapproved' AND employee_id = '$employee_id'")->fetchColumn();
            
            // Total pending + recommended
            $totalPendingRecommended = $pending + $recommended;
        ?>
        <div class="col-md-3">
            <div class="card shadow bg-white text-center p-4">
                <h4><?= $totalPendingRecommended ?></h4><span>Pending & Recommended</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow bg-white text-center p-4">
                <h4><?= $official ?></h4><span>Approved Leave</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow bg-white text-center p-4">
                <h4><?= $inactive ?></h4><span>Rejected Leave</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow bg-white text-center p-4">
                <h4><?= $official + $totalPendingRecommended + $inactive ?></h4><span>Total Leaves</span>
            </div>
        </div>
    </div>

    <!-- EMPLOYEE ACCOUNT DISPLAYS =============================================================================================== -->
    <div class="card">
        <!-- NAVIAGATIONS OF TABS -->
        <div class="card-body col-md-12 col-12 d-flex justify-content-between">
            <ul class="nav nav-tabs col-md-10 col-12" id="LeaveRequestTabs">
                <li class="nav-item col-md-3">
                    <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#Pending_Leave"><i
                            class="fa-solid fa-clock me-2"></i>Pending Leaves</a>
                </li>
                <li class="nav-item col-md-3">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#Recommended_Leave"><i
                            class="fa-solid fa-user-tie me-2"></i>Recommended Leaves</a>
                </li>
                <li class="nav-item col-md-3">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#Approved_leave"><i
                            class="fa-solid fa-user-plus me-2"></i>Approved Leaves</a>
                </li>
                <li class="nav-item col-md-3">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#Rejected_Leave"><i
                            class="fa-solid fa-user-minus me-2"></i>Disapproved Leaves</a>
                </li>
            </ul>
            <!-- <div class="col-md-2 col-12">
                <input type="text" id="searchLeaves" placeholder="search by... Employee name, ID and leave_type" class="form-control">
            </div> -->
        </div>
        <!-- CONTENTS -->
        <div class="card-body table-body">
            <div class="tab-content">
                <!-- Pending Leaves -->
                <div class="tab-pane text-center table-body fade show active" id="Pending_Leave" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Employee Name</th>
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
                                    WHERE lr.leaveStatus = 'Pending' AND ed.employee_id = :employee_id
                                    ORDER BY lr.request_date DESC");
                                $stmt->execute([
                                    'employee_id' => $employee_id
                                ]);
                                $pendingLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                $count = 1;
                                if (empty($pendingLeave)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No pending leaves found</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach($pendingLeave as $pending) : ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td><?= htmlspecialchars($pending["firstname"]) . " " . htmlspecialchars(substr($pending["middlename"], 0, 1)) . ". " . htmlspecialchars($pending["lastname"]) ?></td>
                                    <td><?= htmlspecialchars($pending["leaveType"]) ?></td>
                                    <td><?= htmlspecialchars($pending["InclusiveFrom"]) . " to " . htmlspecialchars($pending["InclusiveTo"]) ?></td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($pending["leaveStatus"]) ?></span></td>
                                    <td>
                                        <a href="index.php?page=contents/viewLeave&leave_id=<?= htmlspecialchars($pending["leave_id"]) ?>" class="btn btn-sm btn-danger px-3">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recommended Leaves -->
                <div class="tab-pane text-center table-body fade" id="Recommended_Leave" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Employee Name</th>
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
                                    WHERE lr.leaveStatus = 'Recommended' AND ed.employee_id = :employee_id
                                    ORDER BY lr.request_date DESC");
                                $stmt->execute([
                                    'employee_id' => $employee_id
                                ]);
                                $recommendedLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                $count = 1;
                                if (empty($recommendedLeave)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No recommended leaves found</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach($recommendedLeave as $recommended) : ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td><?= htmlspecialchars($recommended["firstname"]) . " " . htmlspecialchars(substr($recommended["middlename"], 0, 1)) . ". " . htmlspecialchars($recommended["lastname"]) ?></td>
                                    <td><?= htmlspecialchars($recommended["leaveType"]) ?></td>
                                    <td><?= htmlspecialchars($recommended["InclusiveFrom"]) . " to " . htmlspecialchars($recommended["InclusiveTo"]) ?></td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($recommended["leaveStatus"]) ?></span></td>
                                    <td>
                                        <a href="index.php?page=contents/viewLeave&leave_id=<?= htmlspecialchars($recommended["leave_id"]) ?>" class="btn btn-sm btn-danger px-3">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Approved Leaves -->
                <div class="tab-pane text-center table-body fade" id="Approved_leave" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Employee Name</th>
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
                                    WHERE lr.leaveStatus = 'Approved' AND ed.employee_id = :employee_id
                                    ORDER BY lr.request_date DESC");
                                $stmt->execute([
                                    'employee_id' => $employee_id
                                ]);
                                $ApprovedLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                $count = 1;
                                if (empty($ApprovedLeave)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No approved leaves found</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach($ApprovedLeave as $approved) : ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td><?= htmlspecialchars($approved["firstname"]) . " " . htmlspecialchars(substr($approved["middlename"], 0, 1)) . ". " . htmlspecialchars($approved["lastname"]) ?></td>
                                    <td><?= htmlspecialchars($approved["leaveType"]) ?></td>
                                    <td><?= htmlspecialchars($approved["InclusiveFrom"]) . " to " . htmlspecialchars($approved["InclusiveTo"]) ?></td>
                                    <td><span class="badge bg-success"><?= htmlspecialchars($approved["leaveStatus"]) ?></span></td>
                                    <td>
                                        <a href="index.php?page=contents/reviewLeave&leave_id=<?= htmlspecialchars($approved["leave_id"]) ?>" class="btn btn-sm btn-danger px-3">
                                            <i class="fas fa-eye"></i> Review Leave
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Rejected Leaves -->
                <div class="tab-pane text-center table-body fade" id="Rejected_Leave" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Employee Name</th>
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
                                    WHERE lr.leaveStatus = 'Disapproved' AND ed.employee_id = :employee_id
                                    ORDER BY lr.request_date DESC");
                                $stmt->execute([
                                    'employee_id' => $employee_id
                                ]);
                                $disapprovedLeave = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                $count = 1;
                                if (empty($disapprovedLeave)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No disapproved leaves found</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach($disapprovedLeave as $disapproved) : ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td><?= htmlspecialchars($disapproved["firstname"]) . " " . htmlspecialchars(substr($disapproved["middlename"], 0, 1)) . ". " . htmlspecialchars($disapproved["lastname"]) ?></td>
                                    <td><?= htmlspecialchars($disapproved["leaveType"]) ?></td>
                                    <td><?= htmlspecialchars($disapproved["InclusiveFrom"]) . " to " . htmlspecialchars($disapproved["InclusiveTo"]) ?></td>
                                    <td><span class="badge bg-danger"><?= htmlspecialchars($disapproved["leaveStatus"]) ?></span></td>
                                    <td>
                                        <a href="index.php?page=contents/reviewLeave&leave_id=<?= htmlspecialchars($disapproved["leave_id"]) ?>" class="btn btn-sm btn-danger px-3">
                                            <i class="fas fa-eye"></i> Review Leave
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Date calculation
    const fromInput = document.getElementById('InclusiveFrom');
    const toInput = document.getElementById('InclusiveTo');
    const daysInput = document.getElementById('numberOfDays');
    const daysError = document.getElementById('daysError');

    function calculateDays() {
        const fromDate = new Date(fromInput.value);
        const toDate = new Date(toInput.value);

        if (fromInput.value && toInput.value) {
            if (toDate < fromDate) {
                daysInput.value = '';
                daysError.textContent = "Inclusive Date To cannot be earlier than Inclusive Date From.";
                return;
            }

            daysError.textContent = "";
            const timeDiff = toDate - fromDate;
            const daysDiff = Math.floor(timeDiff / (1000 * 60 * 60 * 24)) + 1;
            daysInput.value = daysDiff;
        } else {
            daysInput.value = '';
        }
    }

    fromInput.addEventListener('change', calculateDays);
    toInput.addEventListener('change', calculateDays);

    // Others input toggle
    const othersRadio = document.getElementById('leaveTypeOthers');
    const othersInput = document.getElementById('othersInput');
    const leaveTypeRadios = document.querySelectorAll('input[name="leaveType"]');

    function toggleOthersInput() {
        if (othersRadio.checked) {
            othersInput.disabled = false;
            othersInput.required = true;
            othersInput.focus();
        } else {
            othersInput.disabled = true;
            othersInput.required = false;
            othersInput.value = '';
        }
    }

    leaveTypeRadios.forEach(radio => {
        radio.addEventListener('change', toggleOthersInput);
    });

    // Form submission
    const leaveForm = document.getElementById('leave-form');
    leaveForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate leave type
        const leaveTypeSelected = document.querySelector('input[name="leaveType"]:checked');
        if (!leaveTypeSelected) {
            alert('Please select a leave type');
            return;
        }

        // Validate others field if "Others" is selected
        if (leaveTypeSelected.value === 'Others' && !othersInput.value.trim()) {
            alert('Please specify the leave type in "Others Specify" field');
            othersInput.focus();
            return;
        }

        // Submit form via AJAX
        const formData = new FormData(this);
        
        fetch('submit_leave.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Leave request submitted successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    });
});
</script>