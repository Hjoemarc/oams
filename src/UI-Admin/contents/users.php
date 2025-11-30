<?php
   // Fetch Active Employees
$stmtOfficial = $pdo->prepare("
    SELECT 
    us.user_id, 
    us.first_name, 
    us.middle_name, 
    us.last_name, 
    us.suffix,
    us.email,
    us.is_active,
    us.user_role
FROM users us
WHERE us.is_active = 'Active' 
    AND us.user_role != 'admin'
ORDER BY us.first_name ASC;
");
$stmtOfficial->execute();
$officialEmployees = $stmtOfficial->fetchAll(PDO::FETCH_ASSOC);

// Fetch Pending Employees
$stmtPending = $pdo->prepare("
    SELECT 
        us.user_id, 
        us.first_name, 
        us.middle_name, 
        us.last_name, 
        us.suffix,
        us.email,
        us.is_active,
        us.user_role
    FROM users us
    WHERE us.is_active = 'Pending' AND us.user_role != 'admin'
    ORDER BY us.first_name
");
$stmtPending->execute();
$pendingEmployees = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

// Fetch Inactive Employees
$stmtInactive = $pdo->prepare("
    SELECT 
        us.user_id, 
        us.first_name, 
        us.middle_name, 
        us.last_name, 
        us.suffix,
        us.email,
        us.is_active,
        us.user_role
    FROM users us
    WHERE us.is_active = 'Inactive' AND us.user_role != 'admin'
    ORDER BY us.first_name
");
$stmtInactive->execute();
$inactiveEmployees = $stmtInactive->fetchAll(PDO::FETCH_ASSOC);


    $countOfficials = 1;
    $countPending = 1;
?>
<section>
    <div class="d-flex justify-content-between align-items-center mb-2 col-md-12 col-12 flex-wrap">
        <div class=" col-md-6 col-12">
            <h4 class=""><i class="fa-solid fa-users me-2"></i>Employees Management</h4>
            <small class="text-muted ">Create, Update And view employee Accounts</small>
        </div>
        <div class="col-md-6 col-12 d-flex justify-content-end">
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#createAccounts"
                id="add_new">
                <i class="fa fa-plus"></i> Create Employee Account
            </button>
        </div>
    </div>

    <!-- AUTHENTICATIONS OF EMPLOYEES HERE USING MODALS ====================================================================== -->

    <!-- ADD ACCOUNTS MODAL  ================================================ ON PROCESS...........-->
    <div class="modal fade" id="createAccounts" tabindex="-1" aria-labelledby="createAccountsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="createAccountsLabel">Create New Employee Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                        onclick="location.reload()"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-1 align-items-start justify-content-start" id="register-form" method="post">
                        <!-- Name -->
                        <div class="col-md-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input required type="text" class="form-control" name="lastName" id="lastName">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input required type="text" class="form-control" name="firstName" id="firstName">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" name="middleName" id="middleName">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Suffix</label>
                            <select class="form-select" name="suffix" id="suffix">
                                <option value="">None</option> <!-- ADD THIS DEFAULT OPTION -->
                                <option value="Jr">Jr</option>
                                <option value="Sr">Sr</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                            </select>
                        </div>
                        <input type="hidden" name="is_active" value="Active">
                        <div class="col-md-4">
                            <label class="form-label">Account Role <span class="text-danger">*</span></label>
                            <select required class="form-select" name="user_role">
                                <option value="">Select account role</option>
                                <option value="QA">QA Officer</option>
                                <option value="head">Program Head</option>
                                <option value="faculty">Faculty/Accreditor</option>
                            </select>
                        </div>
                        <!-- Gender -->
                        <div class="col-md-4">
                            <label class="form-label">Sex <span class="text-danger">*</span></label>
                            <select required class="form-select" name="gender" id="gender">
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <!-- Contact Information -->
                        <div class="col-md-4">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input required type="email" class="form-control" name="email" id="email">
                        </div>

                        <!-- Account Info -->
                        <div class="col-md-4">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input required type="text" class="form-control" name="username" id="username">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input required type="password" class="form-control" name="password" id="password">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input required type="password" class="form-control" name="cpassword" id="cpassword">
                            <div id="password-feedback" class="password-feedback"></div>
                        </div>

                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-primary px-5" id="submit-btn">
                                <i class="bi bi-person-plus-fill me-1"></i> Create Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending accounts approval and rejection modals ================================================ -->
    <!-- PENDING APPROVAL -->
    <div class="modal fade" id="aprrovalEmployee" tabindex="-1" aria-labelledby="aprrovalEmployeeLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" id="approval-form">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title text-white" id="aprrovalEmployeeLabel">Confirmation Approval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to Approved this Account?
                    <input type="hidden" name="user_id" id="employeeID">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Yes, Approved</button>
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- REJECTION OF EMPLOYEE -->
    <div class="modal fade" id="rejectionEmployee" tabindex="-1" aria-labelledby="rejectionEmployeeLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="rejection-form" class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title text-white" id="rejectionEmployeeLabel">Confirmation Rejection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to Reject this Account?
                    <input type="hidden" name="user_id" id="employeeIDRejection">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Yes, Reject</button>
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EMPLOYEE COUNTS DISPLAYS ================================================================================================ -->
    <div class="row mb-2">
        <?php
            $official = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 'Active' AND user_role != 'admin'")->fetchColumn();
            $pending = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 'Pending' AND user_role != 'admin'")->fetchColumn();
            $inactive = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 'Inactive' AND user_role != 'admin'")->fetchColumn();
        ?>
        <div class="col-md-4">
            <div class="card-header shadow bg-white text-center p-4 ">
                <h5 id="pendingEnrollments"><?php echo $pending ?></h5>
                <strong>Pending Request</strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-header shadow bg-white text-center p-4 ">
                <h5 id="approvedEnrollments"><?php echo $official ?? 0 ?></h5>
                <strong>Approved</strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-header shadow bg-white text-center p-4 ">
                <h5 id="rejectedEnrollments"><?php echo $inactive ?? 0 ?></h5>
                <strong>Inactive</strong>
            </div>
        </div>
    </div>

    <!-- EMPLOYEE ACCOUNT DISPLAYS =============================================================================================== -->
    <div class="card">
        <!-- NAVIAGATIONS OF TABS -->
        <div class="card-body col-md-12 col-12 d-flex justify-content-between flex-wrap">
            <ul class="nav nav-tabs col-md-7 col-12" id="employeesTabs" role="tablist">
                <li class="nav-item col-md-4" role="presentation">
                    <button class="nav-link w-100 h-100 active" id="approved-tab" data-bs-toggle="tab"
                        data-bs-target="#Approved_Employees" type="button" role="tab" aria-controls="Approved_Employees"
                        aria-selected="true">
                        <i class="fa-solid fa-user-tie me-2"></i>Official Employees
                    </button>
                </li>
                <li class="nav-item col-md-4" role="presentation">
                    <div
                        style="background-color: #fff !important; z-index: 3 !important; position: absolute !important; transform: translateX(11.8rem) !important; ">
                        <strong class="px-1 text-dark"><?php echo $pending ?></strong>
                    </div>
                    <button class="nav-link w-100 h-100" id="pending-tab" data-bs-toggle="tab"
                        data-bs-target="#Pending_Accounts" type="button" role="tab" aria-controls="Pending_Accounts"
                        aria-selected="false">
                        <i class="fa-solid fa-user-plus me-2"></i>Accounts Pending
                    </button>
                </li>
                <li class="nav-item col-md-4" role="presentation">
                    <button class="nav-link w-100 h-100" id="rejected-tab" data-bs-toggle="tab"
                        data-bs-target="#Rejected_Accounts" type="button" role="tab" aria-controls="Rejected_Accounts"
                        aria-selected="false">
                        <i class="fa-solid fa-user-minus me-2"></i>Accounts Inactive
                    </button>
                </li>
            </ul>
            <div class="col-md-4 col-12 mt-2 mt-md-0">
                <input type="text" id="searchEmployees" placeholder="search by... Employee name, ID and Job Title"
                    class="form-control">
            </div>
        </div>

        <!-- CONTENTS -->
        <div class="card-body pt-0">
            <div class="tab-content" id="employeesTabContent">
                <!-- Approved Employees -->
                <div class="tab-pane fade show active" id="Approved_Employees" role="tabpanel"
                    aria-labelledby="approved-tab" tabindex="0">
                    <div class="table-responsive table-body">
                        <table class="text-center table table-bordered text-center table-sm">
                            <thead class="table-light col-md-12">
                                <tr class="col-md-12">
                                    <th>#</th>
                                    <th>Complete Name</th>
                                    <th>email</th>
                                    <th>Account Role</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-center" id="Accounts_approved" style="color: #666;">
                                <?php 
                                    if($officialEmployees){
                                        foreach ($officialEmployees as $officials) : ?>
                                <tr>
                                    <th><?= $countOfficials++ ?></th>
                                    <th><?= htmlspecialchars($officials["first_name"]) . ' ' . htmlspecialchars($officials["last_name"]) ?>
                                    </th>
                                    <th><?= htmlspecialchars($officials["email"]) ?></th>
                                    <th><?= htmlspecialchars($officials["user_role"]) ?></th>
                                    <td class="d-flex justify-content-center flex-wrap gap-1">
                                        <a
                                            href="index.php?page=contents/profile&id=<?= htmlspecialchars($officials["user_id"]) ?>">
                                            <button class="btn btn-sm btn-danger px-3 py-2 m-0">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </a>

                                        <form class="form_select d-flex align-items-center">
                                            <input type="hidden" name="user_id"
                                                value="<?= htmlspecialchars($officials['user_id']) ?>">
                                            <select class="form-select m-0 p-2 select_status" name="is_active">
                                                <option value="" disabled>Select Status</option>
                                                <option value="Active"
                                                    <?= ($officials['is_active'] === 'Active') ? 'selected' : '' ?>>Active
                                                </option>
                                                <option value="Inactive"
                                                    <?= ($officials['is_active'] === 'Inactive') ? 'selected' : '' ?>>
                                                    Inactive</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                                <?php 
                                        endforeach; 
                                    }else {
                                        echo '<tr><td colspan="6" class="text-center">No employees found</td></tr>';
                                    }  
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pending Accounts -->
                <div class="tab-pane fade" id="Pending_Accounts" role="tabpanel" aria-labelledby="pending-tab"
                    tabindex="0">
                    <div class="table-responsive table-body">
                        <table class="text-center text-center table table-bordered table-sm">
                            <thead class="table-light">
                                <tr style="color: #555;">
                                    <th>#</th>
                                    <th>Complete Name</th>
                                    <th>email</th>
                                    <th>Account Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-center" id="Accounts_pending" style="color: #666;">
                                <?php 
                                    if($pendingEmployees){
                                        foreach ($pendingEmployees as $pendings) : ?>
                                <tr>
                                    <th><?= $countPending++ ?></th>
                                    <th><?= htmlspecialchars($pendings["first_name"]) . ' ' . htmlspecialchars($pendings["last_name"]) ?>
                                    </th>
                                    <th><?= htmlspecialchars($pendings["email"]) ?></th>
                                    <th><?= htmlspecialchars($pendings["user_role"]) ?></th>
                                    <td class="d-flex justify-content-center flex-wrap gap-1">
                                        <a
                                            href="index.php?page=contents/profile&id=<?= htmlspecialchars($pendings["user_id"]) ?>">
                                            <button class="btn btn-sm btn-info m-0">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </a>
                                        <button class="btn btn-dark btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#aprrovalEmployee"
                                            data-id="<?= htmlspecialchars($pendings["user_id"]) ?>">
                                            Approve
                                        </button>
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#rejectionEmployee"
                                            data-id="<?= htmlspecialchars($pendings["user_id"]) ?>">
                                            Reject
                                        </button>
                                    </td>
                                </tr>
                                <?php 
                                        endforeach; 
                                    }else {
                                        echo '<tr><td colspan="6" class="text-center">No employees found</td></tr>';
                                    }  
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Rejected Accounts -->
                <div class="tab-pane fade" id="Rejected_Accounts" role="tabpanel" aria-labelledby="rejected-tab"
                    tabindex="0">
                    <div class="table-responsive table-body">
                        <table class="text-center table table-bordered table-sm">
                            <thead class="table-light">
                                <tr style="color: #555;">
                                    <th>#</th>
                                    <th>Complete Name</th>
                                    <th>email</th>
                                    <th>Account Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-center" id="Accounts_rejected" style="color: #666;">
                                <?php 
                                    if($inactiveEmployees){
                                        foreach ($inactiveEmployees as $inactive) : ?>
                                <tr>
                                    <th><?= $countOfficials++ ?></th>
                                    <th><?= htmlspecialchars($inactive["first_name"]) . ' ' . htmlspecialchars($inactive["last_name"]) ?>
                                    </th>
                                    <th><?= htmlspecialchars($inactive["email"]) ?></th>
                                    <th><?= htmlspecialchars($inactive["user_role"]) ?></th>
                                    <td class="d-flex justify-content-center flex-wrap gap-1">
                                        <a
                                            href="index.php?page=contents/profile&id=<?= htmlspecialchars($inactive["user_id"]) ?>">
                                            <button class="btn btn-sm btn-danger px-3 m-0">
                                                <i class="fas fa-eye"></i> Review Account
                                            </button>
                                        </a>
                                    </td>
                                </tr>
                                <?php 
                                        endforeach; 
                                    }else {
                                        echo '<tr><td colspan="6" class="text-center">No employees found</td></tr>';
                                    }  
                                ?>
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
    // Handle Approve button click
    document.querySelectorAll('[data-bs-target="#aprrovalEmployee"]').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const employeeID = $(this).data('id');
            document.getElementById('employeeID').value = employeeID;
        });
    });

    // Handle Reject button click
    document.querySelectorAll('[data-bs-target="#rejectionEmployee"]').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const employeeID = $(this).data('id');
            document.getElementById('employeeIDRejection').value = employeeID;
        });
    });
});
document.addEventListener('DOMContentLoaded', function() {
    employeePageTab();
});

function employeePageTab() {
    const tabButtons = document.querySelectorAll('#employeesTabs .nav-link');
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

                // Load data for the active tab
                if (targetId === '#Pending_Accounts') {
                    // loadEmployeeData_hr_pending();
                } else if (targetId === '#Approved_Employees') {
                    // loadEmployeeData_hr();
                } else if (targetId === '#Rejected_Accounts') {
                    // loadEmployeeData_hr_rejected();
                }
            }
        });
    });
}
</script>
<script>
document.getElementById("searchEmployees").addEventListener("keyup", function() {
    let keyword = this.value.toLowerCase().trim();

    // IDs of the 3 tables
    let tableIDs = ["Accounts_rejected", "Accounts_pending", "Accounts_approved"];

    tableIDs.forEach(function(tableID) {
        let rows = document.querySelectorAll(`#${tableID} tr`);

        rows.forEach(row => {
            let rowText = row.innerText.toLowerCase();

            // Show row if it matches the search, hide if not
            if (rowText.includes(keyword)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    const employeeIDInputs = document.querySelectorAll('input[id="employeeID"]');
    if (employeeIDInputs.length > 1) {
        employeeIDInputs.forEach((input, index) => {
            if (index > 0) {
                input.id = `employeeID-${index + 1}`;
            }
        });
    }
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('cpassword');
    const passwordFeedback = document.getElementById('password-feedback');

    // Create show password toggle for password field
    const passwordToggle = document.createElement('button');
    passwordToggle.type = 'button';
    passwordToggle.innerHTML = 'Show';
    passwordToggle.style.position = 'absolute';
    passwordToggle.style.right = '10px';
    passwordToggle.style.top = '50%';
    passwordToggle.style.transform = 'translateY(5px)';
    passwordToggle.style.background = 'none';
    passwordToggle.style.border = 'none';
    passwordToggle.style.cursor = 'pointer';
    passwordToggle.style.fontSize = '12px';

    // Create show password toggle for confirm password field
    const confirmPasswordToggle = document.createElement('button');
    confirmPasswordToggle.type = 'button';
    confirmPasswordToggle.innerHTML = 'Show';
    confirmPasswordToggle.style.position = 'absolute';
    confirmPasswordToggle.style.right = '10px';
    confirmPasswordToggle.style.top = '50%';
    confirmPasswordToggle.style.transform = 'translateY(5px)';
    confirmPasswordToggle.style.background = 'none';
    confirmPasswordToggle.style.border = 'none';
    confirmPasswordToggle.style.cursor = 'pointer';
    confirmPasswordToggle.style.fontSize = '12px';

    // Add toggle buttons to password fields
    passwordInput.parentNode.style.position = 'relative';
    passwordInput.parentNode.appendChild(passwordToggle);

    confirmPasswordInput.parentNode.style.position = 'relative';
    confirmPasswordInput.parentNode.appendChild(confirmPasswordToggle);

    // Toggle password visibility
    passwordToggle.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        passwordToggle.innerHTML = type === 'password' ? 'Show' : 'Hide';
    });

    confirmPasswordToggle.addEventListener('click', function() {
        const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordInput.setAttribute('type', type);
        confirmPasswordToggle.innerHTML = type === 'password' ? 'Show' : 'Hide';
    });

    // Password validation
    function validatePassword() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        const hasMinLength = password.length >= 8;
        const hasUppercase = /[A-Z]/.test(password);
        const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
        const passwordsMatch = password === confirmPassword;

        let messages = [];

        // Password requirements
        if (!hasMinLength) messages.push('at least 8 characters');
        if (!hasUppercase) messages.push('one capital letter');
        if (!hasSpecialChar) messages.push('one special character');

        // Confirm password check
        if (confirmPassword && !passwordsMatch) {
            messages.push('passwords do not match');
        }

        // Update styling and feedback
        if (messages.length > 0) {
            passwordInput.style.borderColor = '#dc3545';
            confirmPasswordInput.style.borderColor = '#dc3545';
            passwordFeedback.style.color = '#dc3545';
            passwordFeedback.style.position = 'absolute';
            passwordFeedback.textContent = `Password must contain: ${messages.join(', ')}`;
        } else if (password.length > 0) {
            passwordInput.style.borderColor = '#28a745';
            confirmPasswordInput.style.borderColor = '#28a745';
            passwordFeedback.style.color = '#28a745';
            passwordFeedback.style.position = 'absolute';
            passwordFeedback.textContent = 'Password meets all requirements';
        } else {
            passwordInput.style.borderColor = '';
            confirmPasswordInput.style.borderColor = '';
            passwordFeedback.textContent = '';
        }

        return messages.length === 0;
    }

    // Real-time validation
    passwordInput.addEventListener('input', validatePassword);
    confirmPasswordInput.addEventListener('input', validatePassword);

    // Form submission validation
    const form = passwordInput.closest('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validatePassword()) {
                e.preventDefault();
            }
        });
    }
});
</script>