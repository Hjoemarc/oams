<?php
// Fetch Cycles
    $stmtCycles = $pdo->prepare("SELECT cycle_id, cycle_name, academic_year, status, deadline, created_at
                FROM accreditation_cycles
                ORDER BY created_at DESC");
    $stmtCycles->execute();
    $cycles = $stmtCycles->fetchAll(PDO::FETCH_ASSOC);

    $counts = 1;
?>
<section>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="col-md-5">
            <h4 class="mb-0"><i class="fa fa-cog text-dark me-2"></i>Account Settings</h4>
            <small class="text-muted">Manage your account information and preferences</small>
        </div>
        <div class="col-md-7 d-flex justify-content-end">
            <button class="m-0 btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#changePassword">
                <i class="fa-solid fa-key me-2"></i>Change Password
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-0 col-md-12 col-12 flex-wrap">
        <div class="card-body col-md-8 col-12">
            <ul class="nav nav-tabs justify-content-start align-items-start col-md-12 col-12" id="ProfileInfoTabs">
                <li class="nav-item col-md-2 col-12">
                    <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#Personal">
                        <i class="fa-solid fa-circle-info me-2"></i> Personal
                    </a>
                </li>
                <li class="nav-item col-md-2 col-12">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#history">
                        <i class="fa-solid fa-clock me-2"></i>Login History
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <!-- Activate Cycle Modal -->
    <div class="modal fade" id="activateCycleModal" tabindex="-1" aria-labelledby="activateCycleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="activate-cycle-form" class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="activateCycleModalLabel">Confirm Activation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="deleteCycleMessage">
                        Are you sure you want to Activate this accreditation cycle?
                    </div>
                    <input type="hidden" name="cycle_id" id="activateCycleId">
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Yes, Activate</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- close Cycle Modal -->
    <div class="modal fade" id="closeCycleModal" tabindex="-1" aria-labelledby="closeCycleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="close-cycle-form" class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="closeCycleModalLabel">Confirm Closing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="deleteCycleMessage">
                        Are you sure you want to Close this accreditation cycle?
                    </div>
                    <input type="hidden" name="cycle_id" id="closeCycleId">
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Yes, Close</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <!-- Profile Card -->
        <div class="column p-2 m-0 rounded-2 col-12 col-md-4">
            <div class="card rounded-2">
                 <div class="d-flex flex-column w-100 align-items-center justify-content-center p-2">
                    <div class="w-100 d-flex justify-content-start ps-3 pt-1">
                    </div>
                    <strong class="py-1 px-5 text-white mb-2" style="
                        border-radius: 50%;
                        background-color: #303030ff;
                        font-size: 5rem;
                    "><?= htmlspecialchars(substr($getEmployee["first_name"], 0,1)) ?></strong>
                    <strong><?= strtoupper(htmlspecialchars($getEmployee["user_role"])) ?></strong>
                    <span
                        id="employeeName"><?= htmlspecialchars($getEmployee["first_name"]) . " " .  substr(htmlspecialchars($getEmployee["middle_name"]), 0, 1) . ". " . htmlspecialchars($getEmployee["last_name"]) ?></span>
                    <span class="text-center"
                        id="employeeDept"><?= htmlspecialchars($getEmployee["department_name"]) . ' (' . htmlspecialchars($getEmployee["department_code"]) . ')'?></span>
                    <span class="text-center"
                        id="employeeDept"><?= htmlspecialchars($getEmployee["program_name"]) . ' (' . htmlspecialchars($getEmployee["program_code"]) . ')'?></span>
                </div>
            </div>
        </div>

        <!-- PERSONAL INFORMATION TAB -->
        <div class="column p-2 m-0 rounded-2 col-12 col-md-8 height tab-pane fade show active" role="tabpanel" id="Personal">
            <form id="profile-update">
                <div class="card rounded-2 profile-contents" style="overflow-y: scroll;">
                    <input type="hidden" name="user_id" value="<?= $user_id ?>">

                    <!-- PERSONAL INFORMATION HEADER -->
                    <div class="col-md-12 d-flex">
                        <div class="header ps-3 pt-3 col-md-5">
                            <h5 class="m-0 p-0">
                                <i class="fa-solid fa-circle-info me-2"></i>Personal Information
                            </h5>
                        </div>

                        <?php if ($getEmployee['is_active'] == 'Active') { ?>
                        <div class="col-md-7 d-flex justify-content-end me-5">
                            <button type="submit" class="btn btn-sm btn-danger px-5 mt-3 me-5">Update</button>
                        </div>
                        <?php } ?>
                    </div>

                    <!-- PERSONAL INFORMATION CONTENTS -->
                    <div class="row flex-wrap col-md-12 col-12 p-3">

                        <div class="col-md-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="firstname" class="form-control"
                                value="<?= htmlspecialchars($getEmployee['first_name'] ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middlename" class="form-control"
                                value="<?= htmlspecialchars($getEmployee['middl_ename'] ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="lastname" class="form-control"
                                value="<?= htmlspecialchars($getEmployee['last_name'] ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Suffix</label>
                            <select name="suffix" class="form-select">
                                <option value="">Select suffix (optional)</option>
                                <option value="Jr" <?= ($getEmployee['suffix'] ?? '') == 'Jr' ? 'selected' : '' ?>>Jr
                                </option>
                                <option value="Sr" <?= ($getEmployee['suffix'] ?? '') == 'Sr' ? 'selected' : '' ?>>Sr
                                </option>
                                <option value="II" <?= ($getEmployee['suffix'] ?? '') == 'II' ? 'selected' : '' ?>>II
                                </option>
                                <option value="III" <?= ($getEmployee['suffix'] ?? '') == 'III' ? 'selected' : '' ?>>III
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Citizenship</label>
                            <input type="text" name="citizenship" class="form-control"
                                value="<?= htmlspecialchars($getEmployee['citizenship'] ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select Gender</option>
                                <option value="MALE" <?= ($getEmployee['gender'] ?? '') == 'MALE' ? 'selected' : '' ?>>
                                    Male</option>
                                <option value="FEMALE"
                                    <?= ($getEmployee['gender'] ?? '') == 'FEMALE' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Civil Status</label>
                            <select name="civil_Status" class="form-select">
                                <option value="">Select Civil Status</option>
                                <option value="Single"
                                    <?= ($getEmployee['civil_Status'] ?? '') == 'Single' ? 'selected' : '' ?>>Single
                                </option>
                                <option value="Married"
                                    <?= ($getEmployee['civil_Status'] ?? '') == 'Married' ? 'selected' : '' ?>>Married
                                </option>
                                <option value="Widowed"
                                    <?= ($getEmployee['civil_Status'] ?? '') == 'Widowed' ? 'selected' : '' ?>>Widowed
                                </option>
                                <option value="Separated"
                                    <?= ($getEmployee['civil_Status'] ?? '') == 'Separated' ? 'selected' : '' ?>>
                                    Separated</option>
                                <option value="Divorced"
                                    <?= ($getEmployee['civil_Status'] ?? '') == 'Divorced' ? 'selected' : '' ?>>Divorced
                                </option>
                                <option value="Annulled"
                                    <?= ($getEmployee['civil_Status'] ?? '') == 'Annulled' ? 'selected' : '' ?>>Annulled
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Religion</label>
                            <select name="religion" class="form-select">
                                <option value="">Select Religion</option>
                                <?php
                    $religions = [
                        "Roman Catholic", "Islam", "Iglesia ni Cristo", "Protestant",
                        "Born Again Christian", "Seventh-day Adventist", "Buddhist",
                        "Jehovah's Witness", "Mormon", "Aglipayan", "None", "Others"
                    ];
                    foreach ($religions as $rel) {
                        $sel = ($getEmployee["religion"] ?? "") == $rel ? "selected" : "";
                        echo "<option value='$rel' $sel>$rel</option>";
                    }
                    ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Birthday</label>
                            <input type="date" name="birthday" class="form-control"
                                value="<?= htmlspecialchars($formattedBirthday) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Age</label>
                            <input type="text" name="age" readonly class="form-control"
                                value="<?= htmlspecialchars($getEmployee['age'] ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Birth Place</label>
                            <input type="text" name="birth_place" class="form-control"
                                value="<?= htmlspecialchars($getEmployee['birth_place'] ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact" class="form-control"
                                value="<?= htmlspecialchars($getEmployee['contact'] ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                value="<?= htmlspecialchars($getEmployee['email'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- ADDRESS INFORMATION HEADER -->
                    <div class="header ps-3 pt-3">
                        <h5 class="m-0 p-0">
                            <i class="fa-solid fa-circle-info me-2"></i>Address Information
                        </h5>
                    </div>

                    <!-- ADDRESS INFORMATION CONTENTS -->
                    <div class="row flex-wrap col-md-12 p-3 pb-5">

                        <div class="col-md-3">
                            <label class="form-label">House/Block No.</label>
                            <input type="text" name="block" class="form-control"
                                value="<?= htmlspecialchars($getEmployee['block'] ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Street</label>
                            <input type="text" name="street" class="form-control"
                                value="<?= htmlspecialchars($getEmployee['street'] ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Subdivision</label>
                            <input type="text" name="subdivision" class="form-control"
                                value="<?= htmlspecialchars($getEmployee['subdivision'] ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Barangay</label>
                            <input type="text" name="barangay" class="form-control"
                                value="<?= htmlspecialchars($getEmployee['barangay'] ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">City / Municipality</label>
                            <input type="text" name="city" class="form-control"
                                value="<?= htmlspecialchars($getEmployee['city'] ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Province</label>
                            <input type="text" name="province" class="form-control"
                                value="<?= htmlspecialchars($getEmployee['province'] ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Zip Code</label>
                            <input type="text" name="zip_code" class="form-control"
                                value="<?= htmlspecialchars($getEmployee['zip_code'] ?? '') ?>">
                        </div>

                        <?php if ($getEmployee['is_active'] == 'Active') { ?>
                        <div class="col-md-12 d-flex justify-content-end pb-5">
                            <button type="submit" class="btn btn-sm btn-danger px-5 mt-3 me-2">Update</button>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </form>
        </div>
        <!-- LOGIN HISTORY TAB -->
        <div class="column p-2 m-0 rounded-2 col-12 col-md-8 height tab-pane fade" role="tabpanel" id="history">
            <div class="card rounded-2 profile-contents" style="overflow-y: scroll;">
                <!-- LOGIN HISTORY HEADER -->
                <div class="header ps-3 pt-3">
                    <h5 class="m-0 p-0">
                        <i class="fa-solid fa-circle-info me-2"></i>Login History
                    </h5>
                </div>

                <!-- LOGIN HISTORY CONTENTS -->
                <div class="row flex-wrap col-md-12 col-12 p-3">
                    <div class="responsive-table">
                        <?php
                        $stmtHistory = $pdo->prepare("SELECT * FROM login_history WHERE user_id = '$user_id' ORDER BY login_time DESC");
                        $stmtHistory->execute();
                        $history = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);  
                    ?>
                        <table class="table table-responsive table-bordered text-center">
                            <thead>
                                <tr>
                                    <th>Login Time</th>
                                    <th>Logout Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($history as $his) : ?>
                                <tr>
                                    <th><?= 'Login at: <strong>' . date("M d Y g:iA", strtotime($his["login_time"])) . '</strong>' ?>
                                    </th>
                                    <th>
                                        <p class="w-100 text-danger mb-0 mb-2">
                                            <?php if (!empty($his["logout_time"])): ?>
                                            <?= 'Logout at: <strong>' . date("M d Y g:i A", strtotime($his["logout_time"])) . '</strong>' ?>
                                            <?php else: ?>

                                            <?php endif; ?>
                                        </p>
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

    <!-- CHANGE PASSWORD MODAL -->
    <div class="modal fade" id="changePassword" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="changePass_form" class="modal-content">
                <input type="hidden" name="user_id" value="<?= $user_id ?>">
                <div class="modal-header bg-gradient-primary">
                    <h5 class="modal-title text-start text-white w-100" id="passwordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="currentPassword">Current Password:</label>
                    <input type="password" name="current_password" id="currentPassword" class="form-control" required>
                </div>
                <div class="modal-body">
                    <label for="newPassword">New Password:</label>
                    <input type="password" name="new_pass" id="newPassword" class="form-control" required>
                </div>
                <div class="modal-body">
                    <label for="confirmPassword">Confirm Password:</label>
                    <input type="password" name="confirm_pass" id="confirmPassword" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</section>