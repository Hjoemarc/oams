<?php include '../../header.php'; ?>
    <style>
        .hrNavI {
            padding: .5rem;
            border: solid .2rem #fff !important;
            border-radius: 50%;
        }

        .stepper {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            width: 100%;
        }

        .step {
            width: 40px;
            height: 40px;
            background-color: white;
            border: 2px solid #333;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            position: relative;
            z-index: 1;
        }

        .step.active {
            background-color: #444;
            color: white;
        }

        .line {
            height: 2px;
            width: 60px;
            background-color: #333;
            margin: 0 5px;
            z-index: 0;
        }

        @media (max-width: 576px) {

            /* General adjustments */
            .main-body {
                overflow-x: auto;
            }

            .usersButton span.fw-bold {
                display: none;
            }

            .usersButton a {
                margin-left: 5px !important;
            }

            .sideNav i {
                margin-right: 0 !important;
            }

            .contents {
                padding: 5px !important;
            }

            .linkToEmployeeManagement {
                margin-top: 10px !important;
                margin-bottom: 10px !important;
            }

            .stepper {
                flex-wrap: wrap;
                justify-content: center;
            }

            .step {
                width: 25px;
                height: 25px;
                font-size: 12px;
            }

            .lines {
                width: 15px;
            }

            /* Tables */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                width: 20rem;
            }

            table {
                font-size: 12px;
            }

            th,
            td {
                padding: 4px !important;
            }

            /* Form inputs */
            .form-control {
                font-size: 12px;
                padding: 4px 8px;
                width: auto;
            }

            textarea.form-control {
                min-height: 60px;
            }

            /* Radio buttons and checkboxes */
            .form-check {
                margin-right: 5px !important;
            }

            .form-check-label {
                font-size: 12px;
            }

            /* Buttons */
            .btn {
                font-size: 12px;
                padding: 5px 10px;
            }

            /* Modal */
            .modal-dialog {
                margin: 10px;
            }

            /* Specific table adjustments */
            #personal-info th,
            #family-bg th,
            #education-table th,
            #work-experience th,
            #seminar-training th,
            #others-section th {
                font-size: 11px;
                white-space: nowrap;
            }

            /* Hide less important columns on small screens */
            #education-table th:nth-child(4),
            #education-table td:nth-child(4) {
                display: none;
            }

            #work-experience th:nth-child(4),
            #work-experience td:nth-child(4) {
                display: none;
            }

            /* Signature section */
            #declaration-section td {
                padding: 2px !important;
            }

            #declaration-section .border {
                height: 50px !important;
            }

            #declaration-section small {
                font-size: 10px;
            }

            /* Navigation buttons */
            .nextButtons,
            .backsButtons {
                flex-wrap: wrap;
            }

            .nextButtons button,
            .backsButtons button {
                margin: 3px;
            }

            /* Loading animation */
            .loading-lines .line {
                width: 20px;
                height: 3px;
            }
        }
    </style>
<?php
    require_once '../../authentication/config.php';
    $employee_id = $_GET["employee_id"];

    // Get PDS ID
    $stmtPDS = $pdo->prepare("SELECT pds_id FROM personal_data_sheet WHERE employee_id = ?");
    $stmtPDS->execute([$employee_id]);
    $pdsData = $stmtPDS->fetch(PDO::FETCH_ASSOC);

    if (!$pdsData) {
        die("No Personal Data Sheet found for this employee");
    }

    $pds_id = $pdsData['pds_id'];

    // Main query for employee, HR, and single-record tables
    $query = "SELECT 
        ed.*,
        hr.*,
        pds.pds_id,
        pds.accomplished_on,
        ug.*,
        si.*,
        p_father.*,
        p_mother.*,
        oi.*
    FROM personal_data_sheet pds
    INNER JOIN employee_data ed ON pds.employee_id = ed.employee_id
    INNER JOIN hr_data hr ON ed.employee_id = hr.employee_id
    LEFT JOIN userGovIDs ug ON pds.pds_id = ug.pds_id
    LEFT JOIN spouseInfo si ON pds.pds_id = si.pds_id
    LEFT JOIN parents p_father ON pds.pds_id = p_father.pds_id AND p_father.relation = 'Father'
    LEFT JOIN parents p_mother ON pds.pds_id = p_mother.pds_id AND p_mother.relation = 'Mother'
    LEFT JOIN otherInfo oi ON pds.pds_id = oi.pds_id
    WHERE pds.employee_id = ?";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$employee_id]);
    $pds = $stmt->fetch(PDO::FETCH_ASSOC);

    $multiTables = [
        'children' => "SELECT * FROM children WHERE pds_id = ? ORDER BY id",
        'siblings' => "SELECT * FROM siblings WHERE pds_id = ? ORDER BY birth_order", 
        'education' => "SELECT * FROM educationInfo WHERE pds_id = ? ORDER BY FIELD(level, 'Elementary', 'Secondary', 'Vocational', 'College', 'Graduate')",
        'work_experience' => "SELECT * FROM workExperience WHERE pds_id = ? ORDER BY date_from DESC",
        'seminars_trainings' => "SELECT * FROM seminarsTrainings WHERE pds_id = ? ORDER BY id"
    ];

    foreach ($multiTables as $key => $sql) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$pds_id]);
        $pds[$key] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if (isset($pds['education'])) {
        $educationByLevel = [];
        foreach ($pds['education'] as $edu) {
            $educationByLevel[strtolower($edu['level'])] = $edu;
        }
        $pds['education_by_level'] = $educationByLevel;
    }
?>
 <div class="bg-gradient-primary h-auto p-3 d-flex  align-items-center justify-content-between" style="background-image: linear-gradient(300deg,#E32126, #FF6F8F,#E32126);
    color: #fff;">
        <div class="col-md-7 d-flex">
            <img src="../../assets/image/system_logo/pueri-logo.png" class="me-2" style="height: auto; width: 50px;">
            <h2 class="m-0 d-flex align-items-center fw-bold text-white">Zamboanga Puericulture Center</h2>
        </div>  
        <div class="col-md-5 d-flex justify-content-end">
            <a href="pending.php"><button class="btn btn-sm btn-info m-0">Back to hompage</button></a>
        </div>
    </div>
<main>
    <div class="main-body w-100 h-100 m-0 p-0">
        <div class="d-flex w-100 align-items-start" style="height: 91%">
            <div class="contents  w-100 h-100 d-flex flex-column align-items-center p-0 m-0">
                <div class=" m-0 d-flex flex-row justify-content-between align-items-center col-md-11 col-11">
                    <div class="h1 text-start col-md-6 col-11 m-0 p-0">
                        <h3 class="m-0">PERSONAL DATA SHEET</h3>
                        <p class="m-0 p-0 text-dark">Manage your personal data sheet here</p>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <a class="btn btn-primary" href="#">Create PDF</a>
                    </div>
                </div>
                <div
                    class="contents d-flex flex-column align-items-center p-3 m-0 col-md-11 shadow rounded-2 scroll leave-height">
                    <div class="stepper" id="stepOne" style="display:flex;">
                        <div class="step active">1</div>
                        <div class="line"></div>
                        <div class="step">2</div>
                        <div class="line"></div>
                        <div class="step">3</div>
                        <div class="line"></div>
                        <div class="step">4</div>
                    </div>
                    <div class="stepper" id="stepTwo" style="display:none;">
                        <div class="step">1</div>
                        <div class="line"></div>
                        <div class="step active">2</div>
                        <div class="line"></div>
                        <div class="step">3</div>
                        <div class="line"></div>
                        <div class="step">4</div>
                    </div>
                    <div class="stepper" id="stepThree" style="display:none;">
                        <div class="step">1</div>
                        <div class="line"></div>
                        <div class="step">2</div>
                        <div class="line"></div>
                        <div class="step active">3</div>
                        <div class="line"></div>
                        <div class="step">4</div>
                    </div>
                    <div class="stepper" id="stepFour" style="display:none;">
                        <div class="step">1</div>
                        <div class="line"></div>
                        <div class="step">2</div>
                        <div class="line"></div>
                        <div class="step">3</div>
                        <div class="line"></div>
                        <div class="step active">4</div>
                    </div>
                    <form id="pds-update" method="post" class="col-md-12">
                        <input type="hidden" name="employee_id" value="<?= $pds["employee_id"] ?>">
                        <!-- ============================== PERSONAL INFORMATION ========================================= -->
                        <div id="personalInfo"
                            class="personalInfo flex-row align-items-center p-0 m-0 mt-3 flex-wrap col-md-12 gap-1"
                            style="display: flex; height: auto;">
                            <div class="table-responsive mb-4 col-md-12">
                                <table class="table table-bordered table-sm align-middle" id="personal-info">
                                    <thead class="table-light">
                                        <tr>
                                            <th colspan="4" class="text-start">PERSONAL INFORMATION</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <th class="fw-bold">SURNAME</th>
                                            <td><input type="text" class="form-control" name="lname"
                                                    value="<?= $pds["lastname"] ?>"></td>
                                            <th class="fw-bold">NICKNAME</th>
                                            <td><input type="text" class="form-control" name="nickname"
                                                    value="<?= $pds["nickname"] ?>"></td>
                                        </tr>

                                        <tr>
                                            <th class="fw-bold">FIRST NAME</th>
                                            <td colspan="3"><input type="text" class="form-control" name="fname"
                                                    value="<?= $pds["firstname"] ?>"></td>
                                        </tr>

                                        <tr>
                                            <th class="fw-bold">MIDDLE NAME</th>
                                            <td><input type="text" class="form-control" name="mname"
                                                    value="<?= $pds["middlename"] ?>"></td>
                                            <th class="fw-bold">NAME EXTENSION</th>
                                            <td><input type="text" class="form-control" name="suffix"
                                                    value="<?= $pds["suffix"] ?>"></td>
                                        </tr>

                                        <tr>
                                            <th class="fw-bold">DATE OF BIRTH</th>
                                            <td><input type="date" class="form-control" name="birthday"
                                                    value="<?= $pds["birthday"] ?>"></td>
                                            <th class="fw-bold">PLACE OF BIRTH</th>
                                            <td><input type="text" class="form-control" name="birthPlace"
                                                    value="<?= $pds["birthPlace"] ?>"></td>
                                        </tr>

                                        <tr>
                                            <th class="fw-bold">RESIDENTIAL ADDRESS</th>
                                            <td colspan="3"><textarea class="form-control" name="res_address"
                                                    rows="2"></textarea></td>
                                        </tr>

                                        <tr>
                                            <th class="fw-bold">AGE</th>
                                            <td><input type="number" class="form-control" name="age" min="0"
                                                    value="<?= $pds["age"] ?>">
                                            </td>
                                            <th class="fw-bold">ZIP CODE</th>
                                            <td><input type="text" class="form-control" name="zip_code"
                                                    value="<?= $pds["zip_code"] ?>"></td>
                                        </tr>

                                        <tr>
                                            <th class="fw-bold">GENDER</th>
                                            <td><input type="text" class="form-control" name="gender"
                                                    value="<?= $pds["gender"] ?>"></td>
                                            <th class="fw-bold">TELEPHONE NO.</th>
                                            <td><input type="number" class="form-control" name="tel_no"
                                                    value="<?= $pds["tel_no"] ?>"></td>
                                        </tr>

                                        <tr>
                                            <th class="fw-bold">CIVIL STATUS</th>
                                            <td><input type="text" class="form-control" name="civil_status"
                                                    value="<?= $pds["civil_status"] ?>">
                                            </td>
                                            <th class="fw-bold">CELLPHONE NO.</th>
                                            <td><input type="number" class="form-control" name="contact"
                                                    value="<?= $pds["contact"] ?>"></td>
                                        </tr>

                                        <tr>
                                            <th class="fw-bold">CITIZENSHIP</th>
                                            <td><input type="text" class="form-control" name="citizenship"
                                                    value="<?= $pds["citizenship"] ?>">
                                            </td>
                                            <th class="fw-bold">EMAIL ADDRESS</th>
                                            <td><input type="email" class="form-control" name="email"
                                                    value="<?= $pds["email"] ?>"></td>
                                        </tr>

                                        <tr>
                                            <th class="fw-bold">RELIGION</th>
                                            <td><input type="text" class="form-control" name="religion"
                                                    value="<?= $pds["religion"] ?>"></td>
                                            <th class="fw-bold">PAG‑IBIG NO.</th>
                                            <td><input type="text" class="form-control" name="pagibig_no"
                                                    value="<?= $pds["pagibig_no"] ?>"></td>
                                        </tr>

                                        <tr>
                                            <th class="fw-bold">HEIGHT (m)</th>
                                            <td><input type="number" class="form-control" name="height"
                                                    value="<?= $pds["height"] ?>"></td>
                                            <th class="fw-bold">WEIGHT (kg)</th>
                                            <td><input type="number" class="form-control" name="weight"
                                                    value="<?= $pds["weight"] ?>"></td>
                                        </tr>

                                        <tr>
                                            <th class="fw-bold">PHILHEALTH NO.</th>
                                            <td><input type="text" class="form-control" name="philhealth_no"
                                                    value="<?= $pds["philhealth_no"] ?>"></td>
                                            <th class="fw-bold">BLOOD TYPE</th>
                                            <td><input type="text" class="form-control" name="blood_type"
                                                    value="<?= $pds["blood_type"] ?>"></td>
                                        </tr>

                                        <tr>
                                            <th class="fw-bold">SSS NO.</th>
                                            <td><input type="text" class="form-control" name="sss_no"
                                                    value="<?= $pds["sss_no"] ?>"></td>
                                            <th class="fw-bold">TIN NO.</th>
                                            <td><input type="text" class="form-control" name="tin_no"
                                                    value="<?= $pds["tin_no"] ?>"></td>
                                        </tr>

                                        <tr>
                                            <th class="fw-bold" style="font-size:12px;">IN CASE OF EMERGENCY CALL</th>
                                            <td colspan="3"><input type="text" class="form-control"
                                                    name="emergency_contact" value="<?= $pds["emergency_contact"] ?>">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- ============================== DAMILY BACKGROUND ========================================= -->
                        <div id="familyBackground"
                            class="familyBackground flex-row align-items-center p-0 m-0 mt-3 flex-wrap col-md-12 gap-1"
                            style="display: none; height: auto;">
                            <div class="table-responsive col-md-12">
                                <table id="family-bg" class="table table-bordered align-middle table-sm">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th colspan="4" class="fw-bold">FAMILY BACKGROUND</th>
                                        </tr>
                                        <tr>
                                            <th style="width:30%;">&nbsp;</th>
                                            <th style="width:25%;">&nbsp;</th>
                                            <th style="width:30%;">NAME OF CHILD <br><small>(write full name&list
                                                    all)</small></th>
                                            <th style="width:15%;">DATE OF BIRTH</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <!-- Children Rows -->
                                        <?php for ($i = 1; $i <= 7; $i++): ?>
                                        <input type="hidden" name="child_id_<?= $i ?>"
                                            value="<?= $pds['children'][$i-1]['id'] ?? 0 ?>">
                                        <tr>
                                            <?php if ($i == 1): ?>
                                            <td class="line-label">SPOUSE'S SURNAME</td>
                                            <td>
                                                <input class="form-control" name="spouse_surname"
                                                    value="<?= $pds['spouseInfo']['spouse_surname'] ?? '' ?>">
                                            </td>
                                            <?php elseif ($i == 2): ?>
                                            <td class="line-label">FIRST NAME</td>
                                            <td>
                                                <input class="form-control" name="spouse_first"
                                                    value="<?= $pds['spouseInfo']['spouse_first'] ?? '' ?>">
                                            </td>
                                            <?php elseif ($i == 3): ?>
                                            <td class="line-label">MIDDLE NAME</td>
                                            <td>
                                                <input class="form-control" name="spouse_middle"
                                                    value="<?= $pds['spouseInfo']['spouse_middle'] ?? '' ?>">
                                            </td>
                                            <?php elseif ($i == 4): ?>
                                            <td class="line-label">OCCUPATION</td>
                                            <td>
                                                <input class="form-control" name="spouse_occupation"
                                                    value="<?= $pds['spouseInfo']['spouse_occupation'] ?? '' ?>">
                                            </td>
                                            <?php elseif ($i == 5): ?>
                                            <td class="line-label">EMPLOYER / BUS. NAME</td>
                                            <td>
                                                <input class="form-control" name="spouse_employer"
                                                    value="<?= $pds['spouseInfo']['spouse_employer'] ?? '' ?>">
                                            </td>
                                            <?php elseif ($i == 6): ?>
                                            <td class="line-label">BUSINESS ADDRESS</td>
                                            <td>
                                                <input class="form-control" name="spouse_business_address"
                                                    value="<?= $pds['spouseInfo']['spouse_business_address'] ?? '' ?>">
                                            </td>
                                            <?php elseif ($i == 7): ?>
                                            <td class="line-label">TELEPHONE NO.</td>
                                            <td>
                                                <input class="form-control" name="spouse_tel"
                                                    value="<?= $pds['spouseInfo']['spouse_tel'] ?? '' ?>">
                                            </td>
                                            <?php endif; ?>

                                            <td>
                                                <input class="form-control" name="child_full_name_<?= $i ?>"
                                                    value="<?= $pds['children'][$i-1]['full_name'] ?? '' ?>">
                                            </td>
                                            <td>
                                                <input class="form-control" type="date" name="child_dob_<?= $i ?>"
                                                    value="<?= $pds['children'][$i-1]['dob'] ?? '' ?>">
                                            </td>
                                        </tr>
                                        <?php endfor; ?>

                                        <tr>
                                            <td colspan="4" class="bg-white p-1"></td>
                                        </tr>

                                        <!-- Parents Section -->
                                        <input type="hidden" name="father_id"
                                            value="<?= $pds['parents_father']['id'] ?? 0 ?>">
                                        <input type="hidden" name="mother_id"
                                            value="<?= $pds['parents_mother']['id'] ?? 0 ?>">

                                        <tr>
                                            <td class="line-label">FATHER'S SURNAME</td>
                                            <td><input class="form-control" name="father_surname"
                                                    value="<?= $pds['parents_father']['surname'] ?? '' ?>"></td>
                                            <td class="line-label">OCCUPATION</td>
                                            <td><input class="form-control" name="father_occupation"
                                                    value="<?= $pds['parents_father']['occupation'] ?? '' ?>"></td>
                                        </tr>

                                        <tr>
                                            <td class="line-label">FIRST NAME</td>
                                            <td><input class="form-control" name="father_first_name"
                                                    value="<?= $pds['parents_father']['first_name'] ?? '' ?>"></td>
                                            <td class="line-label">ADDRESS</td>
                                            <td><input class="form-control" name="father_address"
                                                    value="<?= $pds['parents_father']['address'] ?? '' ?>"></td>
                                        </tr>

                                        <tr>
                                            <td class="line-label">MIDDLE NAME</td>
                                            <td><input class="form-control" name="father_middle_name"
                                                    value="<?= $pds['parents_father']['middle_name'] ?? '' ?>"></td>
                                            <td></td>
                                            <td></td>
                                        </tr>

                                        <tr>
                                            <td class="line-label fw-bold">MOTHER'S MAIDEN NAME</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>

                                        <tr>
                                            <td class="line-label ps-3">SURNAME</td>
                                            <td><input class="form-control" name="mother_surname"
                                                    value="<?= $pds['parents_mother']['surname'] ?? '' ?>"></td>
                                            <td class="line-label">OCCUPATION</td>
                                            <td><input class="form-control" name="mother_occupation"
                                                    value="<?= $pds['parents_mother']['occupation'] ?? '' ?>"></td>
                                        </tr>

                                        <tr>
                                            <td class="line-label ps-3">FIRST NAME</td>
                                            <td><input class="form-control" name="mother_first_name"
                                                    value="<?= $pds['parents_mother']['first_name'] ?? '' ?>"></td>
                                            <td class="line-label">ADDRESS</td>
                                            <td><input class="form-control" name="mother_address"
                                                    value="<?= $pds['parents_mother']['address'] ?? '' ?>"></td>
                                        </tr>

                                        <tr>
                                            <td class="line-label ps-3">MIDDLE NAME</td>
                                            <td><input class="form-control" name="mother_middle_name"
                                                    value="<?= $pds['parents_mother']['middle_name'] ?? '' ?>"></td>
                                            <td></td>
                                            <td></td>
                                        </tr>

                                        <tr>
                                            <td colspan="4" class="bg-white p-1"></td>
                                        </tr>

                                        <!-- Siblings Section -->
                                        <tr class="table-light text-center">
                                            <th>NAME OF BROTHER / SISTER <br><small>(write in full name & list all from
                                                    eldest to youngest)</small></th>
                                            <th>AGE</th>
                                            <th>OCCUPATION</th>
                                            <th>ADDRESS</th>
                                        </tr>

                                        <?php for ($i = 1; $i <= 8; $i++): ?>
                                        <input type="hidden" name="sibling_id_<?= $i ?>"
                                            value="<?= $pds['siblings'][$i-1]['id'] ?? 0 ?>">
                                        <tr>
                                            <td><input class="form-control" name="sibling_full_name_<?= $i ?>"
                                                    value="<?= $pds['siblings'][$i-1]['full_name'] ?? '' ?>"></td>
                                            <td><input class="form-control" name="sibling_age_<?= $i ?>"
                                                    value="<?= $pds['siblings'][$i-1]['age'] ?? '' ?>"></td>
                                            <td><input class="form-control" name="sibling_occupation_<?= $i ?>"
                                                    value="<?= $pds['siblings'][$i-1]['occupation'] ?? '' ?>"></td>
                                            <td><input class="form-control" name="sibling_address_<?= $i ?>"
                                                    value="<?= $pds['siblings'][$i-1]['address'] ?? '' ?>"></td>
                                        </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- ============================== Educcation BG and Work Exp ========================================= -->
                        <div id="EducBG_WorkExp"
                            class="EducBG_WorkExp flex-row align-items-start justify-content-start p-0 m-0 mt-3 flex-wrap col-md-12 gap-1"
                            style="display: none; height: auto;">
                            <div class="title w-100">
                                <h4 class="w-100 text-start my-2">EDUCATIONAL BACKGROUND</h4>
                            </div>
                            <div class="table-responsive col-md-12">
                                <table class="table table-bordered align-middle mb-3" id="education-table">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th style="width:12%;">LEVEL</th>
                                            <th style="width:26%;">NAME&nbsp;OF&nbsp;SCHOOL</th>
                                            <th style="width:24%;">DEGREE / COURSE</th>
                                            <th style="width:26%;">SCHOOL&nbsp;ADDRESS</th>
                                            <th style="width:12%;">YEAR GRADUATED</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <input type="hidden" name="edu_elem_id" value="0">
                                        <tr>
                                            <th class="text-center">ELEMENTARY</th>
                                            <td><input type="text" class="form-control" name="elem_school_name"
                                                    value="<?= $pds["elem_school_name"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="elem_degree_course"
                                                    value="<?= $pds["elem_degree_course"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="elem_school_address"
                                                    value="<?= $pds["elem_school_address"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="elem_year_grad"
                                                    placeholder="YYYY" value="<?= $pds["elem_year_grad"] ?? '' ?>"></td>
                                        </tr>

                                        <input type="hidden" name="edu_sec_id" value="0">
                                        <tr>
                                            <th class="text-center">SECONDARY</th>
                                            <td><input type="text" class="form-control" name="sec_school_name"
                                                    value="<?= $pds["sec_school_name"] ?? '' ?>"></td>

                                            <td><input type="text" class="form-control" name="sec_degree_course"
                                                    value="<?= $pds["sec_degree_course"] ?? '' ?>"></td>

                                            <td><input type="text" class="form-control" name="sec_school_address"
                                                    value="<?= $pds["sec_school_address"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="sec_year_grad"
                                                    placeholder="YYYY" value="<?= $pds["sec_year_grad"] ?? '' ?>"></td>
                                        </tr>

                                        <input type="hidden" name="edu_voc_id" value="0">
                                        <tr>
                                            <th class="text-center">VOCATIONAL</th>
                                            <td><input type="text" class="form-control" name="voc_school_name"
                                                    value="<?= $pds["voc_school_name"] ?? '' ?>"></td>

                                            <td><input type="text" class="form-control" name="voc_degree_course"
                                                    value="<?= $pds["voc_degree_course"] ?? '' ?>"></td>

                                            <td><input type="text" class="form-control" name="voc_school_address"
                                                    value="<?= $pds["voc_school_address"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="voc_year_grad"
                                                    placeholder="YYYY" value="<?= $pds["voc_year_grad"] ?? '' ?>"></td>
                                        </tr>

                                        <input type="hidden" name="edu_college_id" value="0">
                                        <tr>
                                            <th class="text-center">COLLEGE</th>
                                            <td><input type="text" class="form-control" name="college_school_name"
                                                    value="<?= $pds["college_school_name"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="college_degree_course"
                                                    value="<?= $pds["college_degree_course"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="college_school_address"
                                                    value="<?= $pds["college_school_address"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="college_year_grad"
                                                    placeholder="YYYY" value="<?= $pds["college_year_grad"] ?? '' ?>">
                                            </td>
                                        </tr>

                                        <input type="hidden" name="edu_grad_id" value="0">
                                        <tr>
                                            <th class="text-center">GRADUATE</th>
                                            <td><input type="text" class="form-control" name="grad_school_name"
                                                    value="<?= $pds["grad_school_name"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="grad_degree_course"
                                                    value="<?= $pds["grad_degree_course"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="grad_school_address"
                                                    value="<?= $pds["grad_school_address"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="grad_year_grad"
                                                    placeholder="YYYY" value="<?= $pds["grad_year_grad"] ?? '' ?>"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="title w-100">
                                <h4 class="w-100 text-start my-2">WORK EXPERIENCE</h4>
                            </div>
                            <div class="table-responsive mb-4 col-md-12">
                                <table class="table table-bordered align-middle" id="work-experience">
                                    <thead class="table-light">
                                        <tr>
                                            <th colspan="5" class="text-center">WORK EXPERIENCE</th>
                                        </tr>
                                        <tr>
                                            <th style="width:20%;">
                                                Inclusive Dates <br><small>(mm/dd/yyyy)</small>
                                            </th>
                                            <th style="width:20%;">Position Title<br><small>(write in full)</small></th>
                                            <th style="width:40%;">Dept. / Agency / Office / Company<br><small>(write in
                                                    full)</small></th>
                                            <th style="width:20%;">Monthly Salary</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <input type="hidden" name="exp_1_id" value="0">
                                        <tr>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <input type="date" class="form-control" name="exp_1_date_from"
                                                        placeholder="From" value="<?= $pds["exp_1_date_from"] ?? '' ?>">

                                                    <input type="date" class="form-control" name="exp_1_date_to"
                                                        placeholder="To" value="<?= $pds["exp_1_date_to"] ?? '' ?>">
                                                </div>
                                            </td>

                                            <td><input type="text" class="form-control" name="exp_1_position_title"
                                                    value="<?= $pds["exp_1_position_title"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="exp_1_department"
                                                    value="<?= $pds["exp_1_department"] ?? '' ?>"></td>

                                            <td><input type="number" class="form-control" name="exp_1_monthly_salary"
                                                    min="0" step="0.01"
                                                    value="<?= $pds["exp_1_monthly_salary"] ?? '' ?>"></td>
                                        </tr>

                                        <input type="hidden" name="exp_2_id" value="0">
                                        <tr>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <input type="date" class="form-control" name="exp_2_date_from"
                                                        placeholder="From" value="<?= $pds["exp_2_date_from"] ?? '' ?>">

                                                    <input type="date" class="form-control" name="exp_2_date_to"
                                                        placeholder="To" value="<?= $pds["exp_2_date_to"] ?? '' ?>">
                                                </div>
                                            </td>

                                            <td><input type="text" class="form-control" name="exp_2_position_title"
                                                    value="<?= $pds["exp_2_position_title"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="exp_2_department"
                                                    value="<?= $pds["exp_2_department"] ?? '' ?>"></td>

                                            <td><input type="number" class="form-control" name="exp_2_monthly_salary"
                                                    min="0" step="0.01"
                                                    value="<?= $pds["exp_2_monthly_salary"] ?? '' ?>"></td>
                                        </tr>

                                        <input type="hidden" name="exp_3_id" value="0">
                                        <tr>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <input type="date" class="form-control" name="exp_3_date_from"
                                                        placeholder="From" value="<?= $pds["exp_3_date_from"] ?? '' ?>">

                                                    <input type="date" class="form-control" name="exp_3_date_to"
                                                        placeholder="To" value="<?= $pds["exp_3_date_to"] ?? '' ?>">
                                                </div>
                                            </td>

                                            <td><input type="text" class="form-control" name="exp_3_position_title"
                                                    value="<?= $pds["exp_3_position_title"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="exp_3_department"
                                                    value="<?= $pds["exp_3_department"] ?? '' ?>"></td>

                                            <td><input type="number" class="form-control" name="exp_3_monthly_salary"
                                                    min="0" step="0.01"
                                                    value="<?= $pds["exp_3_monthly_salary"] ?? '' ?>"></td>
                                        </tr>

                                        <input type="hidden" name="exp_4_id" value="0">
                                        <tr>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <input type="date" class="form-control" name="exp_4_date_from"
                                                        placeholder="From" value="<?= $pds["exp_4_date_from"] ?? '' ?>">

                                                    <input type="date" class="form-control" name="exp_4_date_to"
                                                        placeholder="To" value="<?= $pds["exp_4_date_to"] ?? '' ?>">
                                                </div>
                                            </td>

                                            <td><input type="text" class="form-control" name="exp_4_position_title"
                                                    value="<?= $pds["exp_4_position_title"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="exp_4_department"
                                                    value="<?= $pds["exp_4_department"] ?? '' ?>"></td>

                                            <td><input type="number" class="form-control" name="exp_4_monthly_salary"
                                                    min="0" step="0.01"
                                                    value="<?= $pds["exp_4_monthly_salary"] ?? '' ?>"></td>
                                        </tr>

                                        <input type="hidden" name="exp_5_id" value="0">
                                        <tr>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <input type="date" class="form-control" name="exp_5_date_from"
                                                        placeholder="From" value="<?= $pds["exp_5_date_from"] ?? '' ?>">

                                                    <input type="date" class="form-control" name="exp_5_date_to"
                                                        placeholder="To" value="<?= $pds["exp_5_date_to"] ?? '' ?>">
                                                </div>
                                            </td>

                                            <td><input type="text" class="form-control" name="exp_5_position_title"
                                                    value="<?= $pds["exp_5_position_title"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="exp_5_department"
                                                    value="<?= $pds["exp_5_department"] ?? '' ?>"></td>

                                            <td><input type="number" class="form-control" name="exp_5_monthly_salary"
                                                    min="0" step="0.01"
                                                    value="<?= $pds["exp_5_monthly_salary"] ?? '' ?>"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="table-responsive col-md-12">
                                <table class="table table-bordered align-middle" id="seminar-training">
                                    <thead class="table-light">
                                        <tr>
                                            <th colspan="3" class="text-center">
                                                SEMINARS / WORKSHOPS / TRAININGS ATTENDED
                                                <br><small>(Use additional sheet of paper if necessary)</small>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th style="width:25%;">Inclusive Dates</th>
                                            <th style="width:55%;">Title of Seminar / Training</th>
                                            <th style="width:20%;">Place</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <input type="hidden" name="seminar_1_id" value="0">
                                        <tr>
                                            <td><input type="text" class="form-control" name="seminar_1_inclusive_dates"
                                                    placeholder="e.g., Jan – Mar 2024"
                                                    value="<?= $pds["seminar_1_inclusive_dates"] ?? '' ?>"></td>

                                            <td><input type="text" class="form-control" name="seminar_1_title"
                                                    value="<?= $pds["seminar_1_title"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="seminar_1_place"
                                                    value="<?= $pds["seminar_1_place"] ?? '' ?>">
                                            </td>
                                        </tr>

                                        <input type="hidden" name="seminar_2_id" value="0">
                                        <tr>
                                            <td><input type="text" class="form-control" name="seminar_2_inclusive_dates"
                                                    placeholder="e.g., Jan – Mar 2024"
                                                    value="<?= $pds["seminar_2_inclusive_dates"] ?? '' ?>"></td>

                                            <td><input type="text" class="form-control" name="seminar_2_title"
                                                    value="<?= $pds["seminar_2_title"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="seminar_2_place"
                                                    value="<?= $pds["seminar_2_place"] ?? '' ?>">
                                            </td>
                                        </tr>

                                        <input type="hidden" name="seminar_3_id" value="0">
                                        <tr>
                                            <td><input type="text" class="form-control" name="seminar_3_inclusive_dates"
                                                    placeholder="e.g., Jan – Mar 2024"
                                                    value="<?= $pds["seminar_3_inclusive_dates"] ?? '' ?>"></td>

                                            <td><input type="text" class="form-control" name="seminar_3_title"
                                                    value="<?= $pds["seminar_3_title"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="seminar_3_place"
                                                    value="<?= $pds["seminar_3_place"] ?? '' ?>">
                                            </td>
                                        </tr>

                                        <input type="hidden" name="seminar_4_id" value="0">
                                        <tr>
                                            <td><input type="text" class="form-control" name="seminar_4_inclusive_dates"
                                                    placeholder="e.g., Jan – Mar 2024"
                                                    value="<?= $pds["seminar_4_inclusive_dates"] ?? '' ?>"></td>

                                            <td><input type="text" class="form-control" name="seminar_4_title"
                                                    value="<?= $pds["seminar_4_title"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="seminar_4_place"
                                                    value="<?= $pds["seminar_4_place"] ?? '' ?>">
                                            </td>
                                        </tr>

                                        <input type="hidden" name="seminar_5_id" value="0">
                                        <tr>
                                            <td><input type="text" class="form-control" name="seminar_5_inclusive_dates"
                                                    placeholder="e.g., Jan – Mar 2024"
                                                    value="<?= $pds["seminar_5_inclusive_dates"] ?? '' ?>"></td>

                                            <td><input type="text" class="form-control" name="seminar_5_title"
                                                    value="<?= $pds["seminar_5_title"] ?? '' ?>">
                                            </td>

                                            <td><input type="text" class="form-control" name="seminar_5_place"
                                                    value="<?= $pds["seminar_5_place"] ?? '' ?>">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- ============================== OTHERS ========================================= -->
                        <div id="Others"
                            class="Others flex-row align-items-center p-0 m-0 mt-3 flex-wrap col-md-12 gap-1"
                            style="display: none; height: auto; width: 71.5vw !important;">
                            <div class="table-responsive mb-3 col-md-12">
                                <table class="table table-bordered table-sm align-middle" id="others-section">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th colspan="4">OTHERS</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                    $other = $getPersonalData['otherInfo'] ?? [];
                                    ?>
                                        <tr>
                                            <th style="width:25%;">What are your special skills / hobbies?</th>
                                            <td colspan="3">
                                                <textarea class="form-control" name="special_skills"
                                                    rows="2"></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Do you own / rent the house you live in?</th>
                                            <td colspan="3">
                                                <div class="d-flex flex-wrap gap-3 align-items-center">

                                                    <div class="form-check me-2">
                                                        <input class="form-check-input" type="radio" name="house_status"
                                                            id="houseOwned" value="owned">
                                                        <label class="form-check-label" for="houseOwned">Owned</label>
                                                    </div>

                                                    <div class="form-check me-2">
                                                        <input class="form-check-input" type="radio" name="house_status"
                                                            id="houseRented" value="rented">
                                                        <label class="form-check-label" for="houseRented">Rented</label>
                                                    </div>

                                                    <span class="align-self-center">If rented, amount per month
                                                        (PHP):</span>
                                                    <input type="number" min="0" class="form-control ms-2"
                                                        style="max-width:140px;" name="rental_amount"
                                                        value="<?= htmlspecialchars($other['rental_amount'] ?? '') ?>">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Type of House</th>
                                            <td colspan="3">
                                                <div class="d-flex flex-wrap gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="house_type"
                                                            id="typeLight" value="light">
                                                        <label class="form-check-label"
                                                            for="typeLight">Light&nbsp;Materials</label>
                                                    </div>

                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="house_type"
                                                            id="typeSemi" value="semi_concrete">
                                                        <label class="form-check-label"
                                                            for="typeSemi">Semi‑concrete</label>
                                                    </div>

                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="house_type"
                                                            id="typeConcrete" value="concrete">
                                                        <label class="form-check-label"
                                                            for="typeConcrete">Concrete</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>
                                                Who stays with you at home?<br>
                                                <small>(State number of persons &amp; relationship to employee.)</small>
                                            </th>
                                            <td colspan="3">
                                                <textarea class="form-control" name="household_members"
                                                    rows="2"><?= htmlspecialchars($other['household_members'] ?? '') ?></textarea>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="declaration-section">
                                    <tbody>

                                        <tr>
                                            <td colspan="3">
                                                <p class="mb-2 text-dark" style="font-size:13px;">
                                                    I declare under oath that this Personal Data Sheet has been
                                                    accomplished by me,
                                                    and is a true, correct and complete statement pursuant to the
                                                    provisions of
                                                    pertinent laws, rules and regulations of the Republic of the
                                                    Philippines.<br>
                                                    I also authorize the head/authorized representative to
                                                    verify/validate the contents
                                                    stated herein. I trust that this information shall remain
                                                    confidential.
                                                </p>
                                            </td>
                                            <td rowspan="2" class="text-center align-middle" style="width:140px;">
                                                <div class="border p-3" style="height:160px;">PHOTO</div>
                                            </td>
                                        </tr>

                                        <tr class="text-center">
                                            <td style="width:40%;">
                                                <div class="border mb-1" style="height:70px;"></div>
                                                <small>Signature (sign inside the box)</small>
                                            </td>
                                            <td style="width:25%;">
                                                <div class="border mb-1" style="height:70px;"></div>
                                                <small>Right Thumbmark</small>
                                            </td>
                                            <td style="width:25%;">
                                                <div class="border mb-1" style="height:70px;"></div>
                                                <small>Left Thumbmark</small>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th style="width:20%;">Date Accomplished</th>
                                            <td colspan="3">
                                                <input type="date" class="form-control" name="date_accomplished">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                        <!-- ============================== SUBMIT MODAL ========================================= -->
                        <div class="modal fade" id="updateModalEBG" tabindex="-1" aria-labelledby="updateModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-md modal-dialog-start">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateModalLabel">Update Employee Profile</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modalConfirmation px-3 py-4 text-center">
                                        <h5 class="mb-0">Are you sure you want to update this profile?</h5>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">Save Changes</button>
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- ============================== BUTTONS ========================================= -->
                    <div class="next col-md-12 col-12 d-flex justify-content-between">
                        <div class="backsButtons">
                            <button class="btn btn-danger" id="buttonSecondB" onclick="buttonSecondB()"
                                style="display: none;">BACK</button>
                            <button class="btn btn-danger" id="buttonThirdB" onclick="buttonThirdB()"
                                style="display: none;">BACK</button>
                            <button class="btn btn-danger" id="buttonFourthB" onclick="buttonFourthB()"
                                style="display: none;">BACK</button>
                        </div>
                        <div class="nextButtons">
                            <button class="btn btn-success" id="buttonFirstN" onclick="buttonFirstN()"
                                style="display: flex;">NEXT</button>
                            <button class="btn btn-success" id="buttonSecondN" onclick="buttonSecondN()"
                                style="display: none;">NEXT</button>
                            <button class="btn btn-success" id="buttonThirdN" onclick="buttonThirdN()"
                                style="display: none;">NEXT</button>
                            <button type="button" id="updateButtonEBG" class="btn btn-success" style="display: none;"
                                data-bs-toggle="modal" data-bs-target="#updateModalEBG">
                                Update
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<div id="loadingAnimation" style="display:none;">
    <div class="loading-lines">
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
    </div>
</div>
<script>
    function showLoadingAndRun(callback) {
        showLoader();
        setTimeout(() => {
            hideLoader();
            callback();
        }, 500);
    }

    function showLoader() {
        document.getElementById('loadingAnimation').style.display = 'flex';
    }

    function hideLoader() {
        document.getElementById('loadingAnimation').style.display = 'none';
    }

    window.addEventListener('pageshow', hideLoader);

    function goToStepTwo() {
        document.getElementById('stepOne').style.display = 'none';
        document.getElementById('personalInfo').style.display = 'none';
        document.getElementById('buttonFirstN').style.display = 'none';

        document.getElementById('stepTwo').style.display = 'flex';
        document.getElementById('familyBackground').style.display = 'flex';
        document.getElementById('buttonSecondN').style.display = 'flex';
        document.getElementById('buttonSecondB').style.display = 'flex';

        document.getElementById('stepThree').style.display = 'none';
        document.getElementById('EducBG_WorkExp').style.display = 'none';
        document.getElementById('buttonThirdN').style.display = 'none';
        document.getElementById('buttonThirdB').style.display = 'none';

        document.getElementById('stepFour').style.display = 'none';
        document.getElementById('Others').style.display = 'none';
        document.getElementById('updateButtonEBG').style.display = 'none';
        document.getElementById('buttonFourthB').style.display = 'none';
    }

    function goToStepOne() {
        document.getElementById('stepOne').style.display = 'flex';
        document.getElementById('personalInfo').style.display = 'flex';
        document.getElementById('buttonFirstN').style.display = 'flex';

        document.getElementById('stepTwo').style.display = 'none';
        document.getElementById('familyBackground').style.display = 'none';
        document.getElementById('buttonSecondN').style.display = 'none';
        document.getElementById('buttonSecondB').style.display = 'none';

        document.getElementById('stepThree').style.display = 'none';
        document.getElementById('EducBG_WorkExp').style.display = 'none';
        document.getElementById('buttonThirdN').style.display = 'none';
        document.getElementById('buttonThirdB').style.display = 'none';

        document.getElementById('stepFour').style.display = 'none';
        document.getElementById('Others').style.display = 'none';
        document.getElementById('updateButtonEBG').style.display = 'none';
        document.getElementById('buttonFourthB').style.display = 'none';
    }

    function goToStepThree() {
        document.getElementById('stepOne').style.display = 'none';
        document.getElementById('personalInfo').style.display = 'none';
        document.getElementById('buttonFirstN').style.display = 'none';

        document.getElementById('stepTwo').style.display = 'none';
        document.getElementById('familyBackground').style.display = 'none';
        document.getElementById('buttonSecondN').style.display = 'none';
        document.getElementById('buttonSecondB').style.display = 'none';

        document.getElementById('stepThree').style.display = 'flex';
        document.getElementById('EducBG_WorkExp').style.display = 'flex';
        document.getElementById('buttonThirdN').style.display = 'flex';
        document.getElementById('buttonThirdB').style.display = 'flex';

        document.getElementById('stepFour').style.display = 'none';
        document.getElementById('Others').style.display = 'none';
        document.getElementById('buttonFourthB').style.display = 'none';
        document.getElementById('updateButtonEBG').style.display = 'none';
    }

    function goToStepFour() {
        document.getElementById('stepOne').style.display = 'none';
        document.getElementById('personalInfo').style.display = 'none';
        document.getElementById('buttonFirstN').style.display = 'none';

        document.getElementById('stepTwo').style.display = 'none';
        document.getElementById('familyBackground').style.display = 'none';
        document.getElementById('buttonSecondN').style.display = 'none';
        document.getElementById('buttonSecondB').style.display = 'none';

        document.getElementById('stepThree').style.display = 'none';
        document.getElementById('EducBG_WorkExp').style.display = 'none';
        document.getElementById('buttonThirdN').style.display = 'none';
        document.getElementById('buttonThirdB').style.display = 'none';

        document.getElementById('stepFour').style.display = 'flex';
        document.getElementById('Others').style.display = 'flex';
        document.getElementById('buttonFourthB').style.display = 'flex';
        document.getElementById('updateButtonEBG').style.display = 'flex';
    }

    function buttonFirstN() {
        showLoadingAndRun(goToStepTwo);
    }

    function buttonSecondN() {
        showLoadingAndRun(goToStepThree);
    }

    function buttonThirdN() {
        showLoadingAndRun(goToStepFour);
    }

    function buttonSecondB() {
        showLoadingAndRun(goToStepOne);
    }

    function buttonThirdB() {
        showLoadingAndRun(goToStepTwo);
    }

    function buttonFourthB() {
        showLoadingAndRun(goToStepThree);
    }
</script>
<?php include '../../footer.php'; ?>