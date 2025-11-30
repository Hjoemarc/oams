<?php
   // Fetch Active Employees
$stmtOfficial = $pdo->prepare("
    SELECT 
        ed.employee_id, 
        ed.firstname, 
        ed.middlename, 
        ed.lastname, 
        ed.suffix,
        d.Department_name AS department,
        hd.employeeID,
        jt.jobTitle,
        jt.salary,
        ed.status
    FROM employee_data ed
    INNER JOIN hr_data hd ON ed.employee_id = hd.employee_id
    LEFT JOIN jobTitles jt ON hd.jobtitle_id = jt.jobTitles_id
    LEFT JOIN departments d ON hd.Department_id = d.Department_id
    WHERE ed.status = 'Active' AND ed.user_role = 'EMPLOYEE'
    ORDER BY ed.lastname, ed.firstname
");
$stmtOfficial->execute();
$officialEmployees = $stmtOfficial->fetchAll(PDO::FETCH_ASSOC);

$countOfficials = 1;
?>
<section>
    <div class="d-flex justify-content-between align-items-center mb-2 col-md-12 col-12 flex-wrap">
        <div class=" col-md-6 col-12">
            <h4 class=""><i class="fa-solid fa-users me-2"></i>201 Management</h4>
            <small class="text-muted ">Create, Update And view employee 201 files</small>
        </div>
        <div class="col-md-4">
            <input type="text" id="searchEmployee" placeholder="Search employees by name or ID....." class="form-control">
        </div>

    </div>
    
    <!-- EMPLOYEE ACCOUNT DISPLAYS =============================================================================================== -->
    <div class="card">
        <div class="table-responsive table-body-201">
            <table class="table-body-file text-center table table-bordered text-center table-sm">
                <thead class="table-light col-md-12">
                    <tr class="col-md-12">
                        <th>#</th>
                        <th>Employee ID</th>
                        <th>Complete Name</th>
                        <th>Department</th>
                        <th>Job Title</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-center" style="color: #666;">
                    <?php 
                                    if($officialEmployees){
                                        foreach ($officialEmployees as $officials) : ?>
                    <tr>
                        <th><?= $countOfficials++ ?></th>
                        <th><?= htmlspecialchars($officials["employeeID"]) ?></th>
                        <th><?= htmlspecialchars($officials["firstname"]) . ' ' . htmlspecialchars($officials["lastname"]) ?>
                        </th>
                        <th><?= htmlspecialchars($officials["department"]) ?></th>
                        <th><?= htmlspecialchars($officials["jobTitle"]) ?></th>
                        <td class="d-flex justify-content-center flex-wrap gap-1">
                            <a
                                href="index.php?page=contents/files&id=<?= htmlspecialchars($officials["employee_id"]) ?>">
                                <button class="btn btn-sm btn-danger px-3 py-2 m-0">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </a>
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" id="getID" data-bs-target="#createAccounts" data-id="<?= htmlspecialchars($officials["employee_id"]) ?>">Add file</button>
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
        <div class="modal fade" id="createAccounts" tabindex="-1" aria-labelledby="createAccountsLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white" id="createAccountsLabel">Add employee 201 file</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close" onclick="location.reload()"></button>
                    </div>
                    <div class="modal-body">
                        <form class="row g-3" id="file-form" method="post" enctype="multipart/form-data">
                            <div class="mx-2">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="file_title" placeholder="File title">
                            </div>
                            <div class="mx-2">
                                <label class="form-label">201 type</label>
                                <select name="type" id="" class="form-select">
                                    <option value="">Select Type</option>
                                    <option value="A">A. Personal Information Documents</option>
                                    <option value="B">B. Pre-Employment Requirements</option>
                                    <option value="C">C. Employment Documents</option>
                                    <option value="D">D. Payroll & Compensation Documents</option>
                                    <option value="E">E. Attendance & Leave Documents</option>
                                    <option value="F">F. Training & Development</option>
                                    <option value="G">G. Performance Management</option>
                                    <option value="H">H. Disciplinary Records</option>
                                    <option value="I">I. Benefits & Company Property</option>
                                    <option value="J">J. Separation / Offboarding Documents</option>
                                </select>
                            </div>
                            <div class="mx-2">
                                <label class="form-label">Employee file</label>
                                <input type="file" name="201file" class="form-control">
                                <input type="hidden" name="employee_id" id="employee_id">
                            </div>
                            
                            <!-- Form Submission -->
                            <div class="col-12 text-center mt-3">
                                <button type="submit" class="btn btn-primary px-5">
                                    <i class="bi bi-person-plus-fill me-1"></i> Submit File
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    document.getElementById("searchEmployee").addEventListener("keyup", function () {
        const searchValue = this.value.toLowerCase();
        const tableRows = document.querySelectorAll("table tbody tr");

        tableRows.forEach(row => {
            let text = row.textContent.toLowerCase();

            if (text.includes(searchValue)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
</script>
