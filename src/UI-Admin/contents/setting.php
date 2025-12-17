<?php
// Fetch Cycles
    $stmtCycles = $pdo->prepare("SELECT cycle_id, cycle_name, academic_year, status, deadline, created_at
                FROM accreditation_cycles
                ORDER BY created_at DESC");
    $stmtCycles->execute();
    $cycles = $stmtCycles->fetchAll(PDO::FETCH_ASSOC);

    $stmtAccre = $pdo->prepare("SELECT u.first_name, u.last_name, u.user_id as user_id, u.is_active, 
                ac.cyclaccreditor_cycles_id, ac.cycle_id as assigned_cycle_id,
                a.cycle_name, a.academic_year, a.status as cycle_status
                FROM users u
                LEFT JOIN accreditor_cycles ac ON u.user_id = ac.user_id
                LEFT JOIN accreditation_cycles a ON ac.cycle_id = a.cycle_id
                WHERE u.user_role = 'accreditor'
                ORDER BY u.first_name DESC");
    $stmtAccre->execute();
    $Accreditors = $stmtAccre->fetchAll(PDO::FETCH_ASSOC);

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
                <li class="nav-item col-md-3 col-12">
                    <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#Personal">
                        <i class="fa-solid fa-circle-info me-2"></i> Cycles activation
                    </a>
                </li>
                <li class="nav-item col-md-3 col-12">
                    <a class="nav-link" data-bs-toggle="tab" data-bs-target="#accreditor">
                        <i class="fa-solid fa-circle-info me-2"></i> Accreditor activation
                    </a>
                </li>
                <li class="nav-item col-md-3 col-12">
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
                    <strong class="py-1 px-5 text-white mb-2" style="
                        border-radius: 50%;
                        background-color: #303030ff;
                        font-size: 5rem;
                    "><?= 'A' ?></strong>
                    <span class="text-center" id="employeeDept">System Admin</span>
                </div>
            </div>
        </div>

        <!-- PERSONAL INFORMATION TAB -->
        <div class="column p-2 m-0 rounded-2 col-12 col-md-8 height tab-pane fade show active" role="tabpanel"
            id="Personal">
            <table class="table table-sm table-hover text-center" style="font-size: 0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Cycle Name</th>
                        <th>Academic Year</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="text-center" id="cyclesTable" style="color: #666;">
                    <?php 
                                    $AccreditorCount = 1;
                                    foreach($cycles as $cycle) : 
                                        $statusClass = '';
                                        switch($cycle['status']) {
                                            case 'active': $statusClass = 'badge bg-success'; break;
                                            case 'upcoming': $statusClass = 'badge bg-info'; break;
                                            case 'closed': $statusClass = 'badge bg-danger'; break;
                                        }
                                    ?>
                    <tr>
                        <td><?= $AccreditorCount++ ?></td>
                        <td><?= htmlspecialchars($cycle["cycle_name"]) ?></td>
                        <td><?= htmlspecialchars($cycle["academic_year"]) ?></td>
                        <td><span class="<?= $statusClass ?>"><?= ucfirst($cycle["status"]) ?></span></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary edit-cycle-btn"
                                data-id="<?= $cycle['cycle_id'] ?>" data-bs-toggle="modal"
                                data-bs-target="#activateCycleModal" id="getCycleId">
                                <i class="fas fa-edit"></i> Activate
                            </button>

                            <button type="button" class="btn btn-sm btn-danger delete-cycle-btn"
                                data-id="<?= $cycle['cycle_id'] ?>" data-bs-toggle="modal"
                                data-bs-target="#closeCycleModal" id="getCycleId">
                                <i class="fas fa-trash"></i> Close
                            </button>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <div class="column p-2 m-0 rounded-2 col-12 col-md-8 height tab-pane fade" role="tabpanel"
            id="accreditor">
            <table class="table table-sm table-hover text-center" style="font-size: 0.875rem;">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Cycle Year</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="text-center" id="cyclesTable" style="color: #666;">
                    
                    <?php 
                                    $cycleCount = 1;
                                    foreach($Accreditors as $accre) : 
                                        $statusClass = '';
                                        switch($accre['is_active']) {
                                            case 'Active': $statusClass = 'badge bg-success'; break;
                                            case 'Inactive': $statusClass = 'badge bg-info'; break;
                                        }
                                    ?>
                    <tr>
                        <td><?= $cycleCount++ ?></td>
                        <td><?= htmlspecialchars($accre["first_name"] . ' ' . $accre["last_name"]) ?></td>
                        <td>
                            <?= 
                                !empty($accre["cycle_name"]) || !empty($accre["academic_year"]) 
                                ? htmlspecialchars($accre["cycle_name"] . '(' . $accre["academic_year"] . ')') 
                                : 'No cycle yet'
                            ?>
                        </td>
                        <td><span class="<?= $statusClass ?>"><?= ucfirst($accre["is_active"]) ?></span></td>
                        <td class="d-flex justify-content-center gap-2">
                            <button class="btn btn-danger btn-sm py-0 m-0"
                            data-bs-toggle="modal" data-bs-target="#addCycles"
                            onclick="getAccreditor_id(<?= $accre['user_id'] ?>)"
                            > <i class="fas fa-edit"></i> Add Cycles</button>
                            <form class="form_select d-flex align-items-center">
                                <input type="hidden" name="user_id" value="<?= $accre['user_id'] ?? 'No id wtf' ?>">
                                <select class="form-select m-0 p-2 select_status" name="is_active">
                                    <option value="" disabled>Select Status</option>
                                    <option value="Active" <?= ($accre['is_active'] === 'Active') ? 'selected' : '' ?>>
                                        Active
                                    </option>
                                    <option value="Inactive"
                                        <?= ($accre['is_active'] === 'Inactive') ? 'selected' : '' ?>>
                                        Inactive</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
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
                        $stmtHistory = $pdo->prepare("SELECT * FROM login_history WHERE user_id = '$admin_id' ORDER BY login_time DESC");
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

    <div class="modal fade" id="addCycles" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="assign-cycle-for-accreditor" class="modal-content" autocomplete="off">

                <div class="modal-header bg-gradient-primary">
                    <h5 class="modal-title text-white" id="passwordModalLabel">
                        Assign a cycle to accreditor
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <input type="hidden" name="user_id" id="accreditor_id">
                <div class="mx-2">
                    <label class="form-label">Choose Cycles</label>
                    <select name="cycle_id" class="form-select">
                        <option value="" disabled>Select cycles</option>
                        <?php foreach ($cycles as $cy) : ?>
                            <option value="<?= $cy["cycle_id"] ?>"><?= htmlspecialchars($cy["cycle_name"] . '(' . $cy["academic_year"] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- CHANGE PASSWORD MODAL -->
    <div class="modal fade" id="changePassword" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="changePass_form" class="modal-content" autocomplete="off">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($admin_id) ?>">

                <div class="modal-header bg-gradient-primary">
                    <h5 class="modal-title text-white" id="passwordModalLabel">
                        Change Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="modal-body">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_pass" class="form-control" minlength="8" required>
                </div>

                <div class="modal-body">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_pass" class="form-control" minlength="8" required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </div>
            </form>
        </div>
    </div>

</section>
<script>
    function getAccreditor_id(user_id){
        document.getElementById('accreditor_id').value = user_id;
    }
</script>